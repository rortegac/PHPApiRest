<?php
include(ROOT . "/application/controllers/class.ApiController.php");


class UsuariosController extends ApiController {

    public function __construct($model, $controller, $action, $query, $apiName) {
        // incluimos el modelo y el template
        //require_once('../models/class.' . strtolower($model) . '.php');
        //require_once('../views/class.ApiTemplate.php');
        parent::__construct($model, $controller, $action, $query, $apiName);
    }

    public function add() {

    }

    public function get() {
        $this->set("usuarios", $this->Usuarios->getListaUsuariosParaPrueba($this->query["{idusuario}"]));
    }

    public function update() {

    }

    public function delete() {

    }
    
}

?>