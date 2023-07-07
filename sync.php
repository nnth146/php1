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

    $srcPattern = "/<div class=\"woocommerce-product-gallery__wrapper\">[\n].*?<img.*?src=\"(\S+)\"/";
    $product["src"] = getDataFromHtml($srcPattern, $html);

    $namePattern = "/<h1 class=\"product_title entry-title\">(.*?)<\/h1>/";
    $product["name"] = getDataFromHtml($namePattern, $html);

    $pricePattern = "/<p class=\"price\">.*?<bdi>.*?<\/bdi>.*?<bdi>.*?<\/span>(.*?)<\/bdi>/";
    $product["price"] = getDataFromHtml($pricePattern, $html);

    $skuPattern = "/<span class=\"sku\">(.*?)<\/span>/";
    $product["sku"] = getDataFromHtml($skuPattern, $html);

    $categoryPattern = "/<span class=\"posted_in\">.*?<a.*?>(.*?)<\/a>/";
    $product["category"] = getDataFromHtml($categoryPattern, $html);

    $tagPattern1 = "/<span class=\"tagged_as\">(.*?)<\/span>/";
    $tagPattern2 = "/<a.*?>(.*?)<\/a>/";

    $product["tags"] = getDatasFromHtml($tagPattern2, getDataFromHtml($tagPattern1, $html));

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

foreach ($links as $link) {
    $product = getProductFromLink($link);
    echo "src: " . $product["src"] . "<br>";
    echo "name: " . $product["name"] . "<br>";
    echo "price: " . $product["price"] . "<br>";
    echo "sku: " . $product["sku"] . "<br>";
    echo "category: " . $product["category"] . "<br>";
    echo "tag: " . implode(",", $product["tags"]) . "<br><br>";
}