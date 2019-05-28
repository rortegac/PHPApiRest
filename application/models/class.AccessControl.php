<?php

class AccessControll {

    private $booleanResult;

    public function __construct() {

    }

    public static function setBoolean($boolean) {
        $instancia = new self();
        $instancia->setBooleanResult($boolean);
        return instancia();
    }

    public function setBooleanResult($booleanResult) {
        $this->booleanResult = $booleanResult;
    }

    public function getBooleanResult() {
        return $this->booleanResult;      
    }

}

?>