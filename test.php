<?php

require_once "core/SyncVillatheme.php";

$file_name = 'sync.txt';

if (!file_exists($file_name)) {
    $file = fopen($file_name, 'w') or die('Unable to open file!');
    $sync = new SyncVillatheme();
    $productLinks = $sync->getProductLinks();
    $productLinks = implode("\n", $productLinks);
    fwrite($file, $productLinks);
    fclose($file);
}

$file = fopen($file_name, 'r') or die('Unable to open file!');
$links = fread($file, filesize($file_name));
print_r($links);