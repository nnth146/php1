<?php

class FileHandler
{
    private $files;
    private $dir;
    function __construct($files, $dir = "storage/")
    {
        $this->files = $files;
        $this->dir = $dir;
    }
    public static function unlink($files) {
        if(!is_array($files)) {
            unlink($files);
            return;
        }

        foreach($files as $file) {
            unlink($file);
        }
    }
    protected function createPath($tmp_name, $name)
    {
        if (file_exists($tmp_name) || (isset($this->files['https']) && $this->files['https'])) {
            $filename = pathinfo($name, PATHINFO_FILENAME);
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            $target_file = $this->dir . $filename . "_uid_" . uniqid() . ".$extension";

            return $target_file;
        }
        return false;
    }
    public function getFilePath()
    {
        if (!isset($this->files) || !isset($this->files["name"]) || !isset($this->files["tmp_name"])) {
            return null;
        }

        if (!is_array($this->files["name"])) {
            $isSingleValue = true;
            $names = [$this->files["name"]];
            $tmp_names = [$this->files["tmp_name"]];
        }else {
            $isSingleValue = false;
            $names = $this->files["name"];
            $tmp_names = $this->files["tmp_name"];
        }

        $quantity = count($names);

        $filePaths = [];

        for ($i = 0; $i < $quantity; $i++) {
            if ($path = $this->createPath($tmp_names[$i], $names[$i])) {
                array_push($filePaths, $path);
            }
        }

        if (count($filePaths) > 0) {
            return $isSingleValue ? $filePaths[0] : $filePaths;
        }

        return null;
    }
    public function getTmpFilePath()
    {
        if (!isset($this->files) || !isset($this->files["tmp_name"])) {
            return null;
        }

        return $this->files["tmp_name"];
    }
    public function store($paths) {
        $files = $this->getTmpFilePath();

        if(!isset($paths) || !isset($files)) {
            return false;
        }

        if(!is_array($paths) && !is_array($files)) {
            $files = [$files];
            $paths = [$paths];
        }

        $quantity = count($files);

        $result = true;

        for($i = 0; $i < $quantity; $i++) {
            if(isset($this->files['https']) && $this->files['https']) {
                $result = file_put_contents($paths[$i], $files[$i]) && $result;
                continue;
            }
            $result = move_uploaded_file($files[$i], $paths[$i]) && $result;
        }

        return $result;
    }
}
