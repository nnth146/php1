<?php

require_once "database/PdoDB.php";
require_once "core/Paginator.php";

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
    public function getSimpleProductFromId($id)
    {
        $sql = "SELECT id FROM products where id = '$id'";

        $fetchData = $this->db->query($sql, true);
        return !empty($fetchData) ? $fetchData[0] : null;
    }
    public function storeProduct($product)
    {
        $columns_values = [
            "name" => $product["name"],
            "sku" => empty($product["sku"]) ? null : $product["sku"],
            "price" => $product["price"],
            "feature_image" => $product["feature_image"]
        ];

        $this->db->insert($this->table, array_keys($columns_values), array_values($columns_values));

        $productId = $this->db->getLastInsertId();

        if (isset($product["gallery"]) && is_array($product["gallery"])) {
            $this->db->insertMulti(
                "gallery",
                [
                    "image_url",
                    "product_id"
                ],
                $this->mapWithId($product["gallery"], $productId)
            );
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

        if (isset($product["feature_image"])) {
            $columns_values["feature_image"] = $product["feature_image"];
        }

        $productId = $product["id"];

        $this->db->updateWhere($this->table, $columns_values, "id = '$productId'");

        if (isset($product["gallery"]) && is_array($product["gallery"])) {
            $this->updateProductSubTable(
                "gallery",
                [
                    "image_url",
                    "product_id"
                ],
                $this->mapWithId($product["gallery"], $productId),
                $productId
            );
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
        $this->db->deleteWhere($table, "product_id = '$id'");

        $this->db->insertMulti($table, $columns, $values);
    }
    public function deleteProduct($product)
    {
        $this->db->delete($this->table, $product["id"]);
    }
    public function addCategory($category)
    {
        $columns = [
            "name"
        ];
        $values = [
            $category["name"]
        ];
        return $this->db->insert($this->categoriesTable, $columns, $values);
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
        $columns = [
            "name"
        ];
        $values = [
            $tag["name"]
        ];
        return $this->db->insert($this->tagsTable, $columns, $values);
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