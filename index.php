<?php

function redirect($uri, $queryString = "")
{
    if (!empty($queryString)) {
        $uri .= "?$queryString";
    }
    header("Location: $uri");
}

function inArray($arr, $find)
{
    foreach ($arr as $value) {
        if ($find == $value) {
            return true;
        }
    }
    return false;
}

$action = $_GET["action"] ?? "index";

switch ($action) {
    case "create":
        $method = "create";
        require_once "controller/ProductController.php";
        break;
    case "edit":
        $method = "edit";
        require_once "controller/ProductController.php";
        break;
    case "delete":
        $method = "destroy";
        require_once "controller/ProductController.php";
        break;
    case "properties":
        $method = "create";
        require_once "controller/PropertiesController.php";
        break;
    case "filter":
    case "index":
        $method = "index";
        require_once "controller/ProductController.php";
        break;
    default:
        require_once "resources/views/404.php";
        break;
}