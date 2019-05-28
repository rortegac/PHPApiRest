<?php

class Request {

    private $httpRequestMethod;

    public function __construct() {
        $this->setHttpRequestMethod();
        $this->setUriProperties();
        $this->setInputData();
    }
    
    protected function setHttpRequestMethod() {
        $this->httpRequestMethod = $this->validateHttpRequestMethod($_SERVER['REQUEST_METHOD']);
    }
    
    protected function validateHttpRequestMethod($input) {
        if(empty($input)) {
            throw new InvalidArgumentException('I need valid value');
        }
        switch ($input) {
            case 'GET':
            case 'POST':
            case 'PUT':
            case 'DELETE':
            case 'HEAD':
                return $input;
                break;
            default:
                throw new InvalidArgumentException('Unexpected value.');
                break;
        }
    }
    
    protected function setInputData() {
        switch ($this->httpRequestMethod) {
            case 'GET':
            case 'HEAD':
                $this->setDataFromGet();
                break;
            case 'POST':
                $this->setDataFromPost();
                break;
            case 'PUT':
                $this->setDataFromPut();
                break;
            case 'DELETE':
                // do nothing, no data to set
                break;
            default:
                throw new Exception("Unmapped httpActionMethod. Value provided: " . $this->httpRequestMethod);
        }
    }
            
}

?>