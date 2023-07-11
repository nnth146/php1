<?php

require_once "database/PdoDB.php";
require_once "core/Paginator.php";
require_once "core/FileHandler.php";

class ProductModel
{
    private $db;
    private $table;
    private $categoriesTable;
    private $tagsTable;
    function __construct()
    {
        $this->db = new PdoDB();
        $this->table = "products";
        $this->categoriesTable = "categories";
        $this->tagsTable = "tags";
    }
    public function getPaginatedProducts($conditions, $perPage, $region, $currentPage)
    {
        $sql = "SELECT SQL_CALC_FOUND_ROWS *, 
        p.*,
        GROUP_CONCAT(DISTINCT g.image_url SEPARATOR '|') gallery,
        GROUP_CONCAT(DISTINCT c.name) categories,
        GROUP_CONCAT(DISTINCT t.name) tags
        FROM $this->table p
        LEFT JOIN category_product cp ON cp.product_id = p.id
        LEFT JOIN product_tag pt ON pt.product_id = p.id
        LEFT JOIN categories c ON c.id = cp.category_id
        LEFT JOIN tags t ON t.id = pt.tag_id
        LEFT JOIN gallery g ON g.product_id = p.id";

        if ($whereQuery = $this->resolveWhereQuery($conditions)) {
            $sql .= $whereQuery;
        }

        $sql .= " GROUP BY p.id";

        if ($havingQuery = $this->resolveHavingQuery($conditions)) {
            $sql .= $havingQuery;
        }

        $fetchData = $this->db->query($sql, true);

        $total = $this->db->query("SELECT FOUND_ROWS() total", true)[0]["total"];

        $paginator = new Paginator($fetchData, $total, $region, $perPage);
        $paginator->setPage($currentPage);
        $paginator->sort($conditions["field"], $conditions["order"]);

        return $paginator;
    }
    private function resolveWhereQuery($conditions)
    {
        $where = [];

        if (!empty($conditions["search"])) {
            array_push($where, " INSTR(p.name, '" . $conditions["search"] . "')");
        }

        if ($dateQuery = $this->resolveFromToQuery("p.date", $conditions["dateFrom"], $conditions["dateTo"])) {
            array_push($where, $dateQuery);
        }

        if ($priceQuery = $this->resolveFromToQuery("p.price", $conditions["priceFrom"], $conditions["priceTo"])) {
            array_push($where, $priceQuery);
        }

        if (count($where) > 0) {
            $whereSql = " WHERE" . implode("AND", $where);
            return $whereSql;
        }

        return false;
    }
    private function resolveHavingQuery($conditions)
    {
        $having = [];

        if (!empty($conditions["category"])) {
            array_push($having, " FIND_IN_SET('" . $conditions["category"] . "', categories)");
        }

        if (!empty($conditions["tag"])) {
            array_push($having, " FIND_IN_SET('" . $conditions["tag"] . "', tags)");
        }

        if (count($having) > 0) {
            $havingSql = " HAVING " . implode("AND", $having);
            return $havingSql;
        }

        return false;
    }
    protected function resolveFromToQuery($field, $from, $to)
    {
        if (!empty($from) && empty($to)) {
            $sql = " $field >= '$from'";
        } else if (!empty($from) && !empty($to)) {
            $sql = " $field BETWEEN '$from' AND '$to'";
        } else if (empty($from) && !empty($to)) {
            $sql = " $field <= '$to'";
        }
        return $sql ?? false;
    }
    protected function mapWithId($arr, $id)
    {
        return array_map(function ($itemId) use ($id) {
            return [$itemId, $id];
        }, $arr);
    }
    public function getProductFromId($id)
    {
        $sql = "SELECT p.*,
                GROUP_CONCAT(DISTINCT g.image_url SEPARATOR '|') gallery,
                GROUP_CONCAT(DISTINCT c.id) categoryIds,
                GROUP_CONCAT(DISTINCT c.name) categories,
                GROUP_CONCAT(DISTINCT t.id) tagIds,
                GROUP_CONCAT(DISTINCT t.name) tags
            FROM products p
                LEFT JOIN category_product cp ON cp.product_id = p.id
                LEFT JOIN product_tag pt ON pt.product_id = p.id
                LEFT JOIN categories c ON c.id = cp.category_id
                LEFT JOIN tags t ON t.id = pt.tag_id
                LEFT JOIN gallery g ON g.product_id = p.id
            where p.id = '$id'
            GROUP BY p.id";

        $fetchData = $this->db->query($sql, true);
        return !empty($fetchData) ? $fetchData[0] : null;
    }
    public function getSimpleProductFromField($field, $value)
    {
        $sql = "SELECT
        p.id,
        p.feature_image,
        GROUP_CONCAT(DISTINCT g.image_url SEPARATOR '|') gallery
        FROM
            products p
        LEFT JOIN 
            gallery g
        ON
            g.product_id = p.id
        WHERE
            p.$field = '$value'
        GROUP BY
            p.id";

        $fetchData = $this->db->query($sql, true);
        return !empty($fetchData) ? $fetchData[0] : null;
    }
    public function getSimpleProductFromId($id)
    {
        return $this->getSimpleProductFromField("id", $id);
    }
    protected function mapValueToArray($arr)
    {
        return array_map(function ($v) {
            return [$v];
        }, $arr);
    }
    public function storeProductSync($product)
    {
        $properties = ["categories", "tags"];

        foreach ($properties as $property) {
            if (!empty($product[$property]) && is_array($product[$property])) {
                $values = $this->mapValueToArray($product[$property]);
                $this->db->insertMulti($property, ["name"], $values, true);
            }
        }

        foreach ($properties as $property) {
            $where = [];
            foreach ($product[$property] as $name) {
                array_push($where, "name = '$name'");
            }
            $where = implode(" OR ", $where);
            $propertyIds = $this->db->selectWhere($property, ["id"], $where);
            if(!empty($propertyIds)) {
                $product[$property] = array_map(function($e) {
                    return $e['id'];
                }, $propertyIds);
            }
        }

        $oldProduct = $this->getSimpleProductFromField("sku", $product["sku"]);

        $featureImage = $this->resolveFileFromUrl($product["feature_image-src"]);

        $gallery = ["name" => [], "tmp_name" => []];

        foreach ($product["gallery-src"] as $url) {
            $file = $this->resolveFileFromUrl($url);
            array_push($gallery["name"], $file["name"]);
            array_push($gallery["tmp_name"], $file["tmp_name"]);
        }

        if ($oldProduct) {
            $product["id"] = $oldProduct["id"];

            $product["feature_image"]["new"] = $featureImage;
            $product["feature_image"]["old"] = $oldProduct["feature_image"];
            $product["gallery"]["new"] = $gallery;
            $product["gallery"]["old"] = explode("|", $oldProduct["gallery"]);

            $this->updateProduct($product);
        } else {
            $product["feature_image"] = $featureImage;
            $product["gallery"] = $gallery;

            $this->storeProduct($product);
        }

        $this->unlinkTmpFile($featureImage["tmp_name"], $gallery["tmp_name"]);

        return true;
    }
    protected function unlinkTmpFile(...$parameters)
    {
        foreach ($parameters as $files) {
            if (empty($files)) {
                return false;
            }

            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                unlink($file);
            }
        }
    }
    protected function getFileNameFromUrl($url)
    {
        $arr = explode("/", $url);
        return $arr[count($arr) - 1];
    }
    protected function resolveFileFromUrl($url)
    {
        $file = file_get_contents($url);

        if (!$file) {
            return null;
        }

        $tmp = "tmp/tmp" . uniqid();

        if (!file_put_contents($tmp, $file)) {
            return false;
        }

        return ["name" => $this->getFileNameFromUrl($url), "tmp_name" => $tmp];
    }
    public function storeProduct($product)
    {
        $columns_values = [
            "name" => $product["name"],
            "sku" => empty($product["sku"]) ? null : $product["sku"],
            "price" => $product["price"],
        ];

        $handler = new FileHandler($product["feature_image"]);

        $columns_values["feature_image"] = $handler->getFilePath();

        $result = $this->db->insert($this->table, array_keys($columns_values), array_values($columns_values));

        if ($result && $columns_values["feature_image"]) {
            $handler->store($columns_values["feature_image"]);
        }

        $productId = $this->db->getLastInsertId();

        $handler = new FileHandler($product["gallery"]);

        if ($gallery = $handler->getFilePath()) {
            $result = $this->db->insertMulti(
                "gallery",
                [
                    "image_url",
                    "product_id"
                ],
                $this->mapWithId($gallery, $productId)
            );

            if ($result && $gallery) {
                $handler->store($gallery);
            }
        }

        if (isset($product["categories"]) && is_array($product["categories"])) {
            $this->db->insertMulti(
                "category_product",
                [
                    "category_id",
                    "product_id"
                ],
                $this->mapWithId($product["categories"], $productId)
            );
        }

        if (isset($product["tags"]) && is_array($product["tags"])) {
            $this->db->insertMulti(
                "product_tag",
                [
                    "tag_id",
                    "product_id"
                ],
                $this->mapWithId($product["tags"], $productId)
            );
        }
        return true;
    }
    public function updateProduct($product)
    {
        $columns_values = [
            "name" => $product["name"],
            "sku" => empty($product["sku"]) ? null : $product["sku"],
            "price" => $product["price"],
        ];

        $handler = new FileHandler($product["feature_image"]["new"]);

        if ($path = $handler->getFilePath()) {
            $columns_values["feature_image"] = $path;
        }

        $productId = $product["id"];

        $result = $this->db->updateWhere($this->table, $columns_values, "id = '$productId'");

        if ($result && isset($columns_values["feature_image"])) {
            if ($product["feature_image"]["old"]) {
                FileHandler::unlink($product["feature_image"]["old"]);
            }

            $handler->store($columns_values["feature_image"]);
        }

        $handler = new FileHandler($product["gallery"]["new"]);

        if ($gallery = $handler->getFilePath()) {
            $result = $this->updateProductSubTable(
                "gallery",
                [
                    "image_url",
                    "product_id"
                ],
                $this->mapWithId($gallery, $productId),
                $productId
            );
            if ($result) {
                if ($product["gallery"]["old"]) {
                    FileHandler::unlink($product["gallery"]["old"]);
                }

                $handler->store($gallery);
            }
        }

        if (isset($product["categories"]) && is_array($product["categories"])) {
            $this->updateProductSubTable(
                "category_product",
                [
                    "category_id",
                    "product_id"
                ],
                $this->mapWithId($product["categories"], $productId),
                $productId
            );
        }

        if (isset($product["tags"]) && is_array($product["tags"])) {
            $this->updateProductSubTable(
                "product_tag",
                [
                    "tag_id",
                    "product_id"
                ],
                $this->mapWithId($product["tags"], $productId),
                $productId
            );
        }

        return true;
    }
    private function updateProductSubTable($table, $columns, $values, $id)
    {
        return $this->db->deleteWhere($table, "product_id = '$id'") && $this->db->insertMulti($table, $columns, $values);
    }
    public function deleteProduct($product)
    {
        $this->db->delete($this->table, $product["id"]);
    }
    public function addCategory($category)
    {
        return $this->db->insert($this->categoriesTable, ["name"], $category["name"]);
    }
    public function deleteCategory($category)
    {
        return $this->db->delete($this->categoriesTable, $category["id"]);
    }
    public function getCategories()
    {
        $fetchData = $this->db->select($this->categoriesTable, "*");
        return $fetchData;
    }
    public function addTag($tag)
    {
        return $this->db->insert($this->tagsTable, ["name"], $tag["name"]);
    }
    public function deleteTag($tag)
    {
        return $this->db->delete($this->tagsTable, $tag["id"]);
    }
    public function getTags()
    {
        $fetchData = $this->db->select($this->tagsTable, "*");
        return $fetchData;
    }
}