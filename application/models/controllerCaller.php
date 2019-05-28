<?php

class ControllerCaller {
    private $controller;
    private $model;
    private $nombreController;
    private $action;
    private $query;
    private $dispatch;
    private $api;

    public function __construct($controller, $action, $query = array()) {

        $this->controller = ucwords(strtolower($controller));
        $this->model = ucwords(strtolower($controller));
        $this->nombreController = $this->controller . "Controller";
        $this->action = $action;
        $this->query = $query;
    }

    public function init() {
        if(!is_null($this->api)) {
            if(file_exists(ROOT . '/application/apis/' . $this->api . '/controllers/class.' . $this->nombreController . '.php')) {       
                require_once(ROOT . '/application/apis/' . $this->api . '/controllers/class.' . $this->nombreController . '.php');             
            }
                require_once(ROOT . '/application/apis/' . $this->api . '/models/class.' . strtolower($this->model) . '.php');

                require_once(ROOT . '/application/apis/' . $this->api . '/views/class.ApiTemplate.php');

        } else {
            if (file_exists(ROOT . '/application/controllers/class_' . $this->nombreController . '.php')) {
                require_once(ROOT . '/application/controllers/class_' . $this->nombreController . '.php');
            }
        }
        $nombreController = $this->nombreController;
        
        if(!is_null($this->api)) {
            $dispatch = new $nombreController($this->model, $this->controller, $this->action, $this->query, $this->api);
        } else {
            $dispatch = new $nombreController($this->model, $this->controller, $this->action, $this->query);
        }
        
        call_user_func_array(array($dispatch, $this->action), $this->query);
    }

    public function setApi($api) {
        $this->api = $api;
    }

}

?>