<?php 
class View {
    public static function render($name, $inputs = []) {
        ob_start();

        $name = str_replace(".", "/", $name);
        require_once "resources/views/$name.php";

        $html = ob_get_contents();

        ob_end_clean();
        
        return $html;
    }
}