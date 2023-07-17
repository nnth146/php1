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

$action = $_REQUEST["action"] ?? "index";

switch ($action) {
    case "index":
    case "create":
    case "edit":
    case "fetchLinks":
    case "syncData":
        route("Controller", $action);
        break;
    case "delete":
        route("Controller", "destroy");
        break;
    case "properties":
        route("Controller", "createProperties");
        break;
    default:
        require_once "resources/views/404.php";
        break;
}