<?php

class ApiResource {

    /**
     * Nombre del recurso en la aplicación
     */
    private $resource;

    private $parentResource;

    private $idQuery;

    private $querys;

    private $accessControl;


    public function __construct($resource, $parentResource = null) {
        $this->resource = $resource;
        $this->parentResource = $parentResource ? $parentResource : null;
        $this->querys = array();
    }

    public function getNameResource() {
        return $this->resource;
    }

    public function getAccessControl() {
        return $this->accessControl;
    }

    public function getIdQuery() {
        return $this->idQuery;
    }

    public function getParentResource() {
        return $this->parentResource;
    }

    public function setIdQuery($idQuery) {
        $this->idQuery = $idQuery;
    }

    public function setParentResource($parentResource) {
        $this->parentResource = $parentResource;
    }

    public function setAccessControl($accessControl) {
        $this->accessControl = $accessControl;
    }

    public function setQuery($key, $value) {
        $this->querys[$key] = $value;
    }

    public function hasParenResource() {
        return ($this->parentResource !== null);
    }

    public function hasAccessControl() {
        return ($this->accessControl !== null);
    }
}

?>