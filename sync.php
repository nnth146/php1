<?php

function getProductLinks()
{
    $linkPattern = "/<h2 class=\"woocommerce-loop-product__title\"><a href=\"(\S+)\">/";

    $links = getDatasFromHtml($linkPattern, file_get_contents("https://villatheme.com/extensions/"));

    return $links;
}
function getProductFromLink($link)
{
    $html = file_get_contents($link);

    $product["feature_image-src"] = getDataFromHtml("/<div class=\"woocommerce-product-gallery__wrapper\">[\n].*?<img.*?src=\"(\S+)\"/", $html);

    $product["gallery-src"] = getDatasFromHtml("/<div.*?<a href=\"(.*?)\"/", getDataFromHtml("/<div class=\"woocommerce-product-gallery__wrapper\">([\n\s\S]+)?<div class=\"summary entry-summary\">/", $html));

    $product["name"] = getDataFromHtml("/<h1 class=\"product_title entry-title\">(.*?)<\/h1>/", $html);

    $product["price"] = getDataFromHtml("/<p class=\"price\">.*?<bdi>.*?<\/bdi>.*?<bdi>.*?<\/span>(.*?)<\/bdi>/", $html);
    
    if(empty($product["price"])) {
        $product["price"] = "0";
    }

    $product["sku"] = getDataFromHtml("/<span class=\"sku\">(.*?)<\/span>/", $html);

    $product["category"] = getDataFromHtml("/<span class=\"posted_in\">.*?<a.*?>(.*?)<\/a>/", $html);

    $product["tags"] = getDatasFromHtml("/<a.*?>(.*?)<\/a>/", getDataFromHtml("/<span class=\"tagged_as\">(.*?)<\/span>/", $html));

    return $product;
}

function getDatasFromHtml($pattern, $html)
{
    preg_match_all($pattern, $html, $matches);

    if (count($matches) > 1) {
        return $matches[1];
    }

    return false;
}

function getDataFromHtml($pattern, $html)
{
    preg_match($pattern, $html, $matches);

    if (count($matches) > 1) {
        return $matches[1];
    }

    return false;
}

$links = getProductLinks();
$products = [];
foreach ($links as $link) {
    $product = getProductFromLink($link);
    array_push($products, $product);
}

print_r($products);