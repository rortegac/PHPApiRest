<?php

include(ROOT . "/application/controllers/class.controller.php");

abstract class ApiController extends Controller {

    protected $templatePath;
    protected $apiName;

    public function __construct($model, $controller, $action, $query, $apiName) {
        parent::__construct($model, $controller, $action, $query);
        $this->apiName = $apiName;
        $this->_template->setTemplatePath(ROOT . '/application/apis/' . $this->apiName .  '/views/' . strtolower($this->_controller) . '/' . strtolower($this->_action) . '.php');
    }

    abstract public function add();
    abstract public function get();
    abstract public function update();
    abstract public function delete();

}

?>