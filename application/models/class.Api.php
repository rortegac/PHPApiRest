<?php

include_once("request.php");
include_once("controllerCaller.php");
include_once("class.ApiResource.php");
include_once("class.AccessControl.php");

class Api {

    /**
     * Nombre de la API, también sera el la URL el primer directorio donde se llamará al resto de metodos
     */
    private $name;

    /**
     * Request con las llamadas HTTP
     */
    private $request;

    /**
     * Parametros de la api, metodos a los que se llama para obtener los resultados
     */
    private $resources;


    public function __construct($name) {
        $this->name = $name;
        $this->resources = array();
    }

    /**
     * Devuelve el objeto Request
     * @return Request
     */
    public function setRequest($request) {
        $this->request = $request;
    }

    /**
     * Devuelve el request de la api
     * @return Request
     */
    public function getRequest() {
        return $this->request;
    }


    /**
     * Crea un objeto Request del momento el cual lo guarda en la clase
     */
    public function createRequest() {
        $this->setRequest(Request::getRequest());   
    }

    /**
     * Añade un recurso meduate una uri, este acepta parámetros entre corchetes
     * @param $resourceString URI del recurso
     * @param $accessControll AccessControl asociado al recurso
     * @return ApiResource 
     */
    public function setResource($resourceString, $accessControll = null) {
        $regExpSearchResources = "/\/([a-zA-Z]+)\/(\{[a-zA-Z]+\})$/";
        preg_match_all($regExpSearchResources, $resourceString, $resourceArray);
        if(isset($resourceArray[0][0])) {       
            $resource = new ApiResource($resourceArray[1][0]);
            $resource->setIdQuery($resourceArray[2][0]);
            $resourceString = str_replace($resourceArray[0][0], '', $resourceString);
             
            if(strlen($resourceString) > 1 && !$resource->hasParenResource()) {
                
                $parentResource = $this->setResource($resourceString);
                if($parentResource) {
                    $resource->setParentResource($parentResource);
                }
            }
            if($accessControll) {
                $resource->setAccessControl($accessControll);
            }
            $this->registerResource($resource);
        } else {
            $resource = null;
        }
        return $resource;
    }
    
    /**
     * Añade el recurso a la lista de recursos accesibles de la api
     * @param Resource
     */
    public function registerResource($resource) {
        if($this->resourceExist($resource->getNameResource())) {
            $actualResource = getApiResource($resource->getNameResource());
            if(!$actualResource->hasAccessControl() && $resource->hasAccessControl()) {
                $actualResource->setAccessControl($resource->getAccessControl());
            }
        } else {
            $this->resources[$resource->getNameResource()] = $resource;
        }
    }

    /**
     * Devuelve el acceso a un recurso accesible por la API
     * @param String $nameResource nombre del recurso
     */
    public function getApiResource($nameResource) {
        if(isset($this->resources[$nameResource])) {
            return $this->resources[$nameResource];
        }
            return null;
    }


    /**
     * Comprueba si existe un acceso al recurso
     * @param String $nameResource nombre del recurso
     */
    public function resourceExist($nameResource) {
        
        if($this->getApiResource($nameResource) !== null) {
            return true;
        }
            return false;
    }
     /**
      * Mediante la URI realiza la llamada para devolver el recurso solicitado
      */
    public function getResource() {
        $url = $this->getRequest()->getString("url");
        $parameters = $this->getValuesResource($url);
        $resource = $this->getResourceByUri($url);
        $action = $this->geMethodByRequestMethod();
        // Comprobaciones AccessControl
        if (!is_null($resource)){ 
            if(!is_null($resource->getAccessControl()) && !$resource->getAccessControl()->getBooleanResult()) {
                // Acceso no permitido
            } else {      
                $controllerCaller = new ControllerCaller($resource->getNameResource(), $action, $parameters);
                $controllerCaller->setApi($this->name);
                $controllerCaller->init();
            }
        } else {
            http_response_code(404);
        }
        
    }

    /**
     * Devuelve un array con todos los valores de los parametros entrados por la URI
     * @param String $url URI
     * @return Array 
     */
    public function getValuesResource($url) {
        $resource = null;
        $controller = "";
        $parametros = array();
        $resourceString = "";
        $regExpSearchResourcesWhitQuery = "/\/([a-zA-Z]+)\/([0-9a-zA-Z]+)$/";
        $regExpSearchResourcesWhitoutQuery = "/\/([a-zA-Z]+)$/";
        preg_match_all($regExpSearchResourcesWhitQuery, $url, $resourceArrayWhitQuery);
        preg_match_all($regExpSearchResourcesWhitoutQuery, $url, $resourceArrayWhitoutQuery);

        if(isset($resourceArrayWhitQuery[0][0])) {
            $resourceString = $resourceArrayWhitQuery[0][0];
            $resourceName = $resourceArrayWhitQuery[1][0];
            $resourceParameter = $resourceArrayWhitQuery[2][0];
            if($this->resourceExist($resourceName)) {
                $resource = $this->getApiResource($resourceName);
                $parametros[$resource->getIdQuery()] = $resourceParameter;
            }
        } else if(isset($resourceArrayWhitoutQuery[0][0])) {
            $resourceString = $resourceArrayWhitoutQuery[0][0];
            $resourceName = $resourceArrayWhitoutQuery[1][0];
            if($this->resourceExist($resourceName)) {
                $resource = $this->getApiResource($resourceName);
            }
        }

        if(!is_null($resource)) {
            $controller = $resource->getNameResource();
            $parentResource = null;
            while(strlen($url) > 0) {
                $url = str_replace($resourceString, '', $url);
                if(!$resource->hasParenResource() ) {
                    // URL Incorrecta
                } else {
                    $parentResource = $resource->getParentResource();
                    preg_match_all($regExpSearchResourcesWhitQuery, $url, $resourceArrayWhitQuery);
                    preg_match_all($regExpSearchResourcesWhitoutQuery, $url, $resourceArrayWhitoutQuery);
                    $parametros = array_merge($this->getValuesResource($url, $parametros));
                }

                
            }
        } else {
            http_response_code(404);
        }

        return $parametros;

    }

    /** 
     * Devuelve un recurso por la URI
     * @param string $url URI
     * @return ApiResource
    */
    public function getResourceByUri($url) {
        $resource = null;
        $regExpSearchResourcesWhitQuery = "/\/([a-zA-Z]+)\/([0-9a-zA-Z]+)$/";
        $regExpSearchResourcesWhitoutQuery = "/\/([a-zA-Z]+)$/";
        preg_match_all($regExpSearchResourcesWhitQuery, $url, $resourceArrayWhitQuery);
        preg_match_all($regExpSearchResourcesWhitoutQuery, $url, $resourceArrayWhitoutQuery);
        if(isset($resourceArrayWhitQuery[0][0])) {
            $resourceName = $resourceArrayWhitQuery[1][0];
            if($this->resourceExist($resourceName)) {
                $resource = $this->getApiResource($resourceName);
            }
        } else if(isset($resourceArrayWhitoutQuery[0][0])) {
            $resourceName = $resourceArrayWhitoutQuery[1][0];
            if($this->resourceExist($resourceName)) {
                $resource = $this->getApiResource($resourceName);
            }
        }
        return $resource;
    }

    /**
     * Devuelve un String con el metodo HTTP el cual se ha realizado la solicitud.
     * @return String 
     */
    public function geMethodByRequestMethod() {
        $requestMethod = $this->request->getMethod();
        $callerMethod;

        switch($requestMethod) {
            case "GET":
                $callerMethod = "get";
                break;
            case "POST":
                $callerMethod = "add";
                break;
            case "PUT":
                $callerMethod = "update";
                break;
            case "DELETE":
                $callerMethod = "delete";
                break;
            default:
                break;
        }

        return $callerMethod;
    }

}

?>