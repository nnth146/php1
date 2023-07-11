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
    case "index":
    case "create":
    case "edit":
    case "sync":
        route("ProductController", $action);
        break;
    case "delete":
        route("ProductController", "destroy");
        break;
    case "properties":
        route("PropertiesController", "create");
        break;
    default:
        require_once "resources/views/404.php";
        break;
}