<?php

abstract class Controller {
     
    protected $_model;
    protected $_controller;
    protected $_action;
    protected $_template;
    protected $query;
    protected static $_instancia;

    abstract protected function add();
    abstract protected function get();
    abstract protected function update();
    abstract protected function delete();
 
    public function __construct($model, $controller, $action, $query) {
        $this->_controller = $controller;
        $this->_action = $action;
        $this->_model = $model;
        $this->query = $query;
        $this->$model = new $model;
        $this->_template = new Template($controller,$action);
 
    }
 
    function set($name,$value) {
        $this->_template->set($name,$value);
    }
 
    function __destruct() {
            // En el destrucor, que llama después de ejecutar las operaciones, crea las vistas.
            $this->_template->render();
    }
         
}

?>