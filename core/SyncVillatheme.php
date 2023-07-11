<?php

class SyncVillatheme
{
    public function getProductLinks()
    {
        $linkPattern = "/<h2 class=\"woocommerce-loop-product__title\"><a href=\"(\S+)\">/";

        $links = $this->getDatasFromHtml($linkPattern, file_get_contents("https://villatheme.com/extensions/"));

        return $links;
    }
    public function getProductFromLink($link)
    {
        $html = file_get_contents($link);

        $product["feature_image-src"] = $this->getDataFromHtml("/<div class=\"woocommerce-product-gallery__wrapper\">[\n].*?<img.*?src=\"(\S+)\"/", $html);

        $product["gallery-src"] = $this->getDatasFromHtml("/<div.*?<a href=\"(.*?)\"/", $this->getDataFromHtml("/<div class=\"woocommerce-product-gallery__wrapper\">([\n\s\S]+)?<div class=\"summary entry-summary\">/", $html));

        $product["name"] = $this->getDataFromHtml("/<h1 class=\"product_title entry-title\">(.*?)<\/h1>/", $html);

        $product["price"] = $this->getDataFromHtml("/<p class=\"price\">.*?<bdi>.*?<\/bdi>.*?<bdi>.*?<\/span>(.*?)<\/bdi>/", $html);

        if (empty($product["price"])) {
            $product["price"] = "0";
        }

        $product["sku"] = $this->getDataFromHtml("/<span class=\"sku\">(.*?)<\/span>/", $html);

        $product["categories"] = [$this->getDataFromHtml("/<span class=\"posted_in\">.*?<a.*?>(.*?)<\/a>/", $html)];

        $product["tags"] = $this->getDatasFromHtml("/<a.*?>(.*?)<\/a>/", $this->getDataFromHtml("/<span class=\"tagged_as\">(.*?)<\/span>/", $html));

        return $product;
    }
    public function getProducts() {
        $links = $this->getProductLinks();
        $products = [];

        foreach($links as $link) {
            array_push($products, $this->getProductFromLink($link));
        }

        return $products;
    }
    public function getDatasFromHtml($pattern, $html)
    {
        preg_match_all($pattern, $html, $matches);

        if (count($matches) > 1) {
            return $matches[1];
        }

        return false;
    }

    public function getDataFromHtml($pattern, $html)
    {
        preg_match($pattern, $html, $matches);

        if (count($matches) > 1) {
            return $matches[1];
        }

        return false;
    }
}