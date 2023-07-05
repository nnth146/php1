<?php

require_once "core/View.php";
require_once "core/Validator.php";
require_once "model/ProductModel.php";

class PropertiesController
{
    private $model;
    function __construct()
    {
        $this->model = new ProductModel();
    }
    private function createCategoriesValidator()
    {
        return new Validator(
            $_POST,
            ["category" => ["require", "unique:categories"]],
            [
                "category" => [
                    "require" => "Category không thể bỏ trống",
                    "unique" => "Category đã tồn tại vui lòng nhập tên khác"
                ]
            ]
        );
    }
    private function createTagsValidator()
    {
        return new Validator(
            $_POST,
            ["tag" => ["require", "unique:tags"]],
            [
                "tag" => [
                    "require" => "Tag không thể bỏ trống",
                    "unique" => "Tag đã tồn tại vui lòng nhập tên khác"
                ]
            ]
        );
    }
    public function create()
    {
        $inputs["categories"] = $this->model->getCategories();
        $inputs["tags"] = $this->model->getTags();

        if(isset($_POST["category"])) {
            $validator = $this->createCategoriesValidator();
            
            $errors = $validator->validate();

            if(count($errors) > 0) {
                $inputs["category"] = $_POST["category"];
                $inputs["category-error"] = $errors["category"];
            }else {
                $this->model->addCategory(["name" => $_POST["category"]]);
                
                redirect("/php1");
            }
        }

        if(isset($_POST["tag"])) {
            $validator = $this->createTagsValidator();

            $errors = $validator->validate();

            if(count($errors) > 0) {
                $inputs["tag"] = $_POST["tag"];
                $inputs["tag-error"] = $errors["tag"];
            }else{
                $this->model->addTag(["name" => $_POST["tag"]]);

                redirect("/php1");
            }
        }

        View::render("properties", $inputs);
    }
}

call_user_func([new PropertiesController(), $method]);