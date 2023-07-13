<?php

require_once "core/View.php";
require_once "core/Validator.php";
require_once "model/ProductModel.php";
require_once "core/SyncVillatheme.php";

class ProductController
{
    private $model;
    function __construct()
    {
        $this->model = new ProductModel();
    }
    private function createValidator()
    {
        return new Validator(
            array_merge($_POST, $_FILES),
            [
                "name" => ["require"],
                "price" => ["min:0"],
                "feature_image" => ["image", "filemax:1024"],
                "gallery" => ["image", "filemax:1024"],
                "sku" => ["unique:products.sku"]
            ],
            [
                "name" => [
                    "require" => "Tên sản phẩm không thể bỏ trống",
                ],
                "sku" => [
                    "unique" => "SKU đã tồn tại! Vui lòng nhập SKU khác"
                ],
                "price" => [
                    "require" => "Giá sản phẩm không được bỏ trống",
                    "min" => "Giá phải sản phẩm phải lớn hơn 0",
                ],
                "feature_image" => [
                    "image" => "File tải lên phải là file hình ảnh có đuôi là .jpg, .jpeg, .png",
                    "filemax" => "File tải lên không được lớn hơn 1 MB"
                ],
                "gallery" => [
                    "image" => "File tải lên phải có đuôi là .jpg, .jpeg, .png",
                    "filemax" => "File tải lên không được lớn hơn 1 MB"
                ]
            ]
        );
    }
    protected function gets($name, $fail = "")
    {
        return $_GET[$name] ?? $fail;
    }
    public function index()
    {
        $conditions = [
            "field" => $this->gets("orderby", "date"),
            "order" => $this->gets("order", "asc"),
            "search" => $this->gets("search"),
            "category" => $this->gets("category"),
            "tag" => $this->gets("tag"),
            "dateFrom" => $this->gets("datefrom"),
            "dateTo" => $this->gets("dateto"),
            "priceFrom" => $this->gets("pricefrom"),
            "priceTo" => $this->gets("priceto"),
        ];

        $paginator = $this->model->getPaginatedProducts($conditions, 5, 3, $this->gets("page", 1));

        if ($this->gets("page", false) && !$paginator->isCurrentPage($this->gets("page"))) {
            $_GET["page"] = $paginator->getCurrentPage();
            redirect("/php1", http_build_query($_GET));
        }

        $inputs = [
            "products" => $paginator->getDatas(),
            "links" => $paginator->getLinks(),
            "currentPage" => $paginator->getCurrentPage(),
            "nextPage" => $paginator->nextPage(),
            "prevPage" => $paginator->prevPage(),
            "categories" => $this->model->getCategories(),
            "tags" => $this->model->getTags(),
            "noPageQuery" => http_build_query(array_diff_key($_GET, ["page" => ""])),
            "noActionQuery" => http_build_query(array_diff_key($_GET, ["action" => ""])),
            "page" => $paginator->getCurrentPage()
        ];

        $inputs = array_merge($inputs, $_GET);

        View::render("welcome", $inputs);
    }
    public function create()
    {
        $inputs["header"] = "Create Product";

        if (count($_POST) > 0) {
            $validator = $this->createValidator();

            $errors = $validator->validate();

            if (count($errors) <= 0) {
                $product = [
                    "name" => $_POST["name"],
                    "sku" => $_POST["sku"],
                    "price" => $_POST["price"],
                    "categories" => isset($_POST["category"]) ? $_POST["category"] : [],
                    "tags" => isset($_POST["tag"]) ? $_POST["tag"] : [],
                    "feature_image" => $_FILES["feature_image"],
                    "gallery" => $_FILES["gallery"]
                ];

                $this->model->storeProduct($product);

                redirect("/php1");
            } else {
                $inputs = array_merge($inputs, $_POST);

                foreach ($errors as $name => $error) {
                    $inputs["$name-error"] = $error;
                }
            }
        }

        $inputs["categories"] = $this->model->getCategories();
        $inputs["tags"] = $this->model->getTags();

        View::render("products", $inputs);
    }
    public function edit()
    {
        $inputs["header"] = "Edit Product";

        $oldProduct = $this->gets("id", false) ? $this->model->getProductFromId($this->gets("id")) : null;

        if (!isset($oldProduct)) {
            View::render("404");
            return;
        }

        $inputs = array_merge($inputs, [
            "id" => $oldProduct["id"],
            "name" => $_POST["name"] ?? $oldProduct["name"],
            "sku" => $_POST["sku"] ?? $oldProduct["sku"],
            "price" => $_POST["price"] ?? $oldProduct["price"],
            "category" => $_POST["category"] ?? explode(",", $oldProduct["categoryIds"]),
            "tag" => $_POST["tag"] ?? explode(",", $oldProduct["tagIds"]),
            "feature_image" => $oldProduct["feature_image"],
            "gallery" => isset($oldProduct["gallery"]) ? explode("|", $oldProduct["gallery"]) : null,
            "categories" => $this->model->getCategories(),
            "tags" => $this->model->getTags()
        ]);

        if (count($_POST) > 0) {
            $validator = $this->createValidator();

            $oldSku = isset($oldProduct["sku"]) ? "=" . $oldProduct["sku"] : "";

            $validator->setRule("sku", ["unique:products.sku$oldSku"]);

            $errors = $validator->validate();

            if (count($errors) <= 0) {
                $product = [
                    "id" => $oldProduct["id"],
                    "name" => $_POST["name"],
                    "sku" => $_POST["sku"],
                    "price" => $_POST["price"],
                    "categories" => $_POST["category"] ?? [],
                    "tags" => $_POST["tag"] ?? [],
                    "feature_image" => ["new" => $_FILES["feature_image"], "old" => $oldProduct["feature_image"]],
                    "gallery" => ["new" => $_FILES["gallery"], "old" => explode("|", $oldProduct["gallery"])]
                ];

                $this->model->updateProduct($product);

                redirect("/php1");
            } else {
                foreach ($errors as $name => $error) {
                    $inputs["$name-error"] = $error;
                }
            }
        }

        View::render("products", $inputs);
    }

    public function destroy()
    {
        $product = isset($_POST["id"]) ? $this->model->getSimpleProductFromId($_POST["id"]) : null;

        if (!isset($product)) {
            View::render("404");
            return;
        }

        if (!empty($product["feature_image"])) {
            unlink($product["feature_image"]);
        }

        if (isset($product["gallery"])) {
            $gallery_editing = explode("|", $product["gallery"]);
            foreach ($gallery_editing as $gallery_image) {
                unlink($gallery_image);
            }
        }

        $this->model->deleteProduct($product);

        $queryString = htmlspecialchars(http_build_query(array_diff_key($_GET, ["action" => ""])));

        if (empty($queryString)) {
            $uri = "/php1";
        } else {
            $uri = "/php1?$queryString";
        }
        ;

        redirect("$uri");
    }
    public function fetchLinks()
    {
        $sync = new SyncVillatheme();
        $productLinks = $sync->getProductLinks();
        echo json_encode(["result" => $productLinks, "status" => "success"]);
    }
    public function syncData()
    {
        $link = $_POST["link"] ?? '';

        if(empty($link)) {
            echo json_encode([
                "result" => null,
                "status" => "fail",
                "message" => "Link is empty"
            ]);
            exit;
        }

        $sync = new SyncVillatheme();
        $product = $sync->getProductFromLink($link);

        if($product) {
            //$this->model->storeProductSync($product);
            echo json_encode(["result" => $product, "status" => "success"]);
        }else{
            echo json_encode(["result" => null, "status" => "error", "message" => "No products found from the link"]);
        }
    }
}

call_user_func([new ProductController(), $method]);