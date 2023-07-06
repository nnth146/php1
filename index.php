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

function route($controller, $method)
{
    require_once "controller/$controller.php";
}

$action = $_GET["action"] ?? "index";

switch ($action) {
    case "create":
        route("ProductController", "create");
        break;
    case "edit":
        route("ProductController", "edit");
        break;
    case "delete":
        route("ProductController", "destroy");
        break;
    case "properties":
        route("PropertiesController", "create");
        break;
    case "filter":
    case "index":
        route("ProductController", "index");
        break;
    default:
        require_once "resources/views/404.php";
        break;
}