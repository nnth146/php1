<?php

require_once "core/View.php";
require_once "core/Validator.php";
require_once "model/ProductModel.php";

trait PropertiesController
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
    public function createProperties()
    {
        $inputs["categories"] = $this->model->getCategories();
        $inputs["tags"] = $this->model->getTags();

        $isCategoryError = false;
        $isTagError = false;

        if (isset($_POST["category"])) {
            $validator = $this->createCategoriesValidator();

            $errors = $validator->validate();

            if (count($errors) > 0) {
                $isCategoryError = true;
                $inputs["category"] = $_POST["category"];
                $inputs["category-error"] = $errors["category"];
            } else {
                $this->model->addCategory(["name" => $_POST["category"]]);
            }
        }

        if (isset($_POST["tag"])) {
            $validator = $this->createTagsValidator();

            $errors = $validator->validate();

            if (count($errors) > 0) {
                $isTagError = true;
                $inputs["tag"] = $_POST["tag"];
                $inputs["tag-error"] = $errors["tag"];
            } else {
                $this->model->addTag(["name" => $_POST["tag"]]);
            }
        }

        if((!$isCategoryError || !$isTagError) && count($_POST) > 0) {
            $this->index(true);
            exit;
        }

        echo json_encode(["result" => "error", "html" => View::render("properties", $inputs)]);
    }
}