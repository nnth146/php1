<?php 

require_once "core/View.php";

class PropertiesController {
    public function create() {
        View::render("properties");
    }
}

call_user_func([new PropertiesController(), $method]);