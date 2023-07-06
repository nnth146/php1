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

        if (!empty($conditions["orderBy"])) {
            $sql .= " ORDER BY p." . $conditions["orderBy"];
        }

        $offset = $perPage * ($currentPage - 1);
        $sql .= " LIMIT $perPage OFFSET $offset";

        $fetchData = $this->db->query($sql, true);
        $total = $this->db->query("SELECT FOUND_ROWS() total", true)[0]["total"];

        $paginator = new Paginator($fetchData, $total, $region, $perPage);
        $paginator->setPage($currentPage);

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
    public function getProductFromId($id)
    {
        $sql = "SELECT p.*,
                GROUP_CONCAT(DISTINCT g.image_url SEPARATOR '|') gallery,
                GROUP_CONCAT(DISTINCT c.name) categories,
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
        $sql = "SELECT id,
            FROM products
            where id = '$id'
            GROUP BY p.id";

        $fetchData = $this->db->query($sql, true);
        return !empty($fetchData) ? $fetchData[0] : null;
    }
    public function storeProduct($product)
    {
        $columns_values = [
            "name" => $product["name"],
            "sku" => $product["sku"],
            "price" => $product["price"],
            "feature_image" => $product["feature_image"]
        ];

        $gallery_columns = [
            "product_id",
            "image_url"
        ];

        $this->db->insert($this->table, array_keys($columns_values), array_values($columns_values));

        $productId = $this->db->getLastInsertId();

        //Add gallery if exists
        foreach ($product["gallery"] as $url) {
            $this->db->insert("gallery", $gallery_columns, [$productId, $url]);
        }
        //Add category if exists
        $category_product_columns = [
            "category_id",
            "product_id"
        ];

        foreach ($product["categories"] as $categoryName) {
            $category = $this->db->selectWhere("categories", "id", "name = '$categoryName'");

            if (!empty($category)) {
                $this->db->insert("category_product", $category_product_columns, [$category[0]["id"], $productId]);
            } else {
                $this->addCategory(["name" => $categoryName]);
                $categoryId = $this->db->getLastInsertId();
                $this->db->insert("category_product", $category_product_columns, [$categoryId, $productId]);
            }
        }
        //Add tag if exists
        $product_tag_columns = [
            "product_id",
            "tag_id"
        ];
        foreach ($product["tags"] as $tagName) {
            $tag = $this->db->selectWhere("tags", "id", "name = '$tagName'");

            if (!empty($tag)) {
                $this->db->insert("product_tag", $product_tag_columns, [$productId, $tag[0]["id"]]);
            } else {
                $this->addTag(["name" => $tagName]);
                $tagId = $this->db->getLastInsertId();
                $this->db->insert("product_tag", $product_tag_columns, [$productId, $tagId]);
            }
        }
        return true;
    }
    public function updateProduct($product)
    {
        $columns_values = [
            "name" => $product["name"],
            "sku" => $product["sku"],
            "price" => $product["price"],
        ];

        if (isset($product["feature_image"])) {
            $columns_values["feature_image"] = $product["feature_image"];
        }
        $productId = $product["id"];

        $this->db->updateWhere($this->table, $columns_values, "id = '$productId'");

        if (isset($product["gallery"])) {
            $this->db->deleteWhere("gallery", "product_id = '$productId'");

            foreach ($product["gallery"] as $url) {
                $this->db->insert(
                    "gallery",
                    [
                        "product_id",
                        "image_url"
                    ],
                    [$productId, $url]
                );
            }
        }

        $this->updateProductCategory(
            array_diff($product["categories"], $product["old-categories"]),
            array_diff($product["old-categories"], $product["categories"]),
            $productId
        );

        $this->updateProductTag(
            array_diff($product["tags"], $product["old-tags"]),
            array_diff($product["old-tags"], $product["tags"]),
            $productId
        );

        return true;
    }
    private function updateProductCategory($add, $remove, $productId)
    {
        foreach ($add as $categoryName) {
            $category = $this->db->selectWhere("categories", "id", "name = '$categoryName'");
            $this->db->insert(
                "category_product",
                [
                    "category_id",
                    "product_id"
                ],
                [$category[0]["id"], $productId]
            );
        }
        foreach ($remove as $categoryName) {
            $category = $this->db->selectWhere("categories", "id", "name = '$categoryName'");
            $this->db->deleteWhere(
                "category_product",
                " category_id = " . $category[0]["id"] . " AND product_id = $productId"
            );
        }
    }
    private function updateProductTag($add, $remove, $productId)
    {
        foreach ($add as $tagName) {
            $tag = $this->db->selectWhere("tags", "id", "name = '$tagName'");
            $this->db->insert(
                "product_tag",
                [
                    "tag_id",
                    "product_id",
                ],
                [$tag[0]["id"], $productId]
            );
        }
        foreach ($remove as $tagName) {
            $tag = $this->db->selectWhere("tags", "id", "name = '$tagName'");
            $this->db->deleteWhere(
                "product_tag",
                " tag_id = " . $tag[0]["id"] . " AND product_id = $productId"
            );
        }
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