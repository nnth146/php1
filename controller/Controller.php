<?php 

require_once 'controller/ProductController.php';
require_once 'controller/PropertiesController.php';
class Controller {
    use ProductController, PropertiesController;
    private $model;
    function __construct()
    {
        $this->model = new ProductModel();
    }
}

call_user_func([new Controller(), $method]);