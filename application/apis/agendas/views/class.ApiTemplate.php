<?php
include_once(ROOT . "/application/views/class.template.php");

class ApiTemplate extends Template {
     
    function __construct($controller,$action) {
        parent::__construct($controller,$action);
        $this->templatePath = ROOT . "application/agendas/" .strtolower($this->_controller) . "/" . strtolower($this->_action) . '.php';
    }
 
}