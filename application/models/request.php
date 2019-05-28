<?php

class Request {

    /**
     * Guarda el Metodo HTTP usado
     */
    private $method;

    /**
     * Contiene la uri completa
     */
    private $url;

    /**
     * Contiene todos los datos del metodo POST
     */
    private $post;

    /**
     * Contiene todos los datos del método GET
     */
    private $get;

    /**
     * Contiene un conjunto de datos de GET y POST para acceder a ellos
     */
    public $data;

    /**
     * Instancia de la clase 
     */
    private static $instancia;

    public function __construct() {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->url = $_SERVER['REQUEST_URI'];
        $this->data = array_merge($this->get, $this->post);
        $this->method = $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * Devuelve la instancia de Request con los datos de los metodos guardado
     * @return Request
     */
    public static function getRequest() {
        if(!self::$instancia) {
            self::$instancia = new self;
        }

        return self::$instancia;
    }

    /**
     * Devuelve los datos de algun valor guardado en request
     * @param String data, key a buscar en el conjunto de GET y POST
     * @return String 
     */
    public function getString($data) {
        if(isset($this->data[$data])) {
            return $this->data[$data];
        }
    }

    public function getPost() {return $this->post;}
    public function getGet() {return $this->get;}
    public function getURI() {return $this->url;}
    public function getMethod() {return $this->method;}
    
}

?>