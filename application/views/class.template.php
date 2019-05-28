<?php
class Template {
     
    protected $variables = array();
    protected $_controller;
    protected $_action;
    protected $templatePath;
     
    function __construct($controller,$action) {
        $this->_controller = $controller;
        $this->_action = $action;
        $this->templatePath = './' . strtolower($this->_controller) . "/" . strtolower($this->_action) . '.php';
    }
 
    // Seteamos las variables
    function set($name,$value) {
        $this->variables[$name] = $value;
    }

    public function setTemplatePath($templatePath) {
        $this->templatePath = $templatePath;
    }
 
    // Se llama a este método para cargar las vistas correspondientes y mostrarlas    
    function render() {
        extract($this->variables);
        require_once($this->templatePath);       
    }
 
}