<?php 
class View {
    public static function render($name, $inputs = []) {
        $name = str_replace(".", "/", $name);
        require_once "resources/views/$name.php";
    }
}