<?php
include_once("class.PDOFactory.php");

abstract class Model {
    protected $_model;
    protected $ID;
    protected $PDO;
    protected $tableName;
    protected $relationMap;
    protected $arrModifiedRelations;
    protected $isLoaded;
    protected $forDelete;

    abstract protected function defineTableName();
    abstract protected function defineRelationMap();
 
    function __construct($PDO) { 
        $this->_model = get_class($this);
        $this->tableName = $this->defineTableName();
        $this->relationMap = $this->defineRelationMap();
        $this->PDO = $PDO;
        $this->arrModifiedRelations = array();
        $this->isLoaded = false;
    }
    
    /**
     * Carga un objeto en la clase desde la base de datos si se ha definido el id de este.
     */
    public function load() {
        if(isset($this->id)) {
            $strQuery = 'SELECT * ';
            $strQuery .= 'FROM ' . $this->tableName;
            $strQuery .= 'WHERE id = :id';

            $objectStatement = $this->PDO->prepare($strQuery);
            $objectStatement->bindParam(':id', $this->ID);
            $objectStatement->execute();
            $arRow = $objectStatement->fetch(PDO::FETCH_ASSOC);

            foreach($arRow as $key => $value) {
                $strMember = $this->relationMap[$key];
                if(property_exists($this, $strMember)) {
                    $this->$strMember = $value;
                }
            }

            $this->isLoaded = true;
        }
    } 

    /**
     * Comprueba si hay datos para guardar y los modifica en la base de datos
     * En caso que no se haya especificado un id crea un nuevo registro.
     */
    public function save() {
        if(isset($this->ID)) {
            $strQuery = 'UPDATE ' . $this->tableName . ' SET ';

            foreach ($this->relationMap as $key => $value) {
                if(array_key_exists($value, $this->arrModifiedRelations)) {
                    $strQuery .=  $key . ' = :' . $value . '. ';
                }
            }
            $strQuery = substr($strQuery, 0, strlen($strQuery) -2);
            $strQuery .= ' WHERE id = :id'; 

            unset($objectStatement);

            $objectStatement = $this->PDO->prepare($strQuery);
            $objectStatement->bindParam(':id', $this->ID, PDO::PARAM_INT);

            foreach($this->relationMap as $key => $value) {
                $actualVal = $this->$value;
                if(array_key_exists($value, $this->arrModifiedRelations)) {
                    if(is_int($actualVal) || is_null($actualVal)) {
                        $objectStatement->bindParam(':' . $value, $actualVal, PDO::PARAM_INT);
                    } else {
                        $objectStatement->bindParam(':' . $value, $actualVal, PDO::PARAM_STR);
                    }
                }
            }

            $objectStatement->execute();

        } else {

            $strValueList = "";
            $strQuery = 'INSERT INTO ' . $this->tableName . ' (';
            foreach($this->relationMap as $key => $value) {
                $actualVal = $this->$value;
                if(isset($actualVal)) {
                    if(array_key_exists($value, $this->arrModifiedRelations)) {
                        $strQuery .= $key . ', ';
                        $strValueList .= ':$value';
                    }
                }
                
            }

            $strQuery = substr($strQuery, 0, strlen($strQuery) - 2);
            $strValueList = substr($strValueList, 0, strlen($strValueList) - 2);
            $strQuery .= ') VALUES (';
            $strQuery .= $strValueList;
            $strQuery .= ')';

            unset($objectStatement);

            $objectStatement = $this->PDO->prepare($strQuery);

            foreach($this->relationMap as $key => $value) {
                $actualVal = $this->$value;
                if(isset($actualVal)) {
                    if(array_key_exists($value, $this->arrModifiedRelations)) {
                        if(is_int($actualVal) || is_null($actualVal)) {
                            $objectStatement->bindParam(':' . $value, $actualVal, PDO::PARAM_INT);
                        } else {
                            $objectStatement->bindParam(':' . $value, $actualVal, PDO::PARAM_STR);
                        }
                    }
                }
            }
            
            $objectStatement->execute();
            $this->ID = $this->PDO->lastInsertId($this->tableName . '_id_seq');
        }
    }

    /**
     * Indica que el registro se debe borrar
     */
    public function markForDelete() {
        $this->forDelete = true;
    }

    /**
     * Méotodo mágico __call. Se encargar de controlar las consultas o modificaciones de miembros de
     * la clase devolviendo o seteando el adecuado.
     * @param String $strFunction nombre del miembro al que se llama
     * @param Array $arguments, argumentos con los que se llama al mimebro. El primero [0] será el valor de este al setearlo
     * @return Devuleve una llamada al método que se encarga de setear o devolver el miembro.
     */
    public function __call($strFunction, $arguments) {
        $methodType = substr($strFunction, 0, 3);
        $strMethodMember = substr($strFunction, 3);

        switch($methodType) {
            case "get":
                return $this->setAccessor($strMethodMember, $arguments[0]);
                break;
            case "set":
                return $this->getAccessor($strMethodMember);
                break;
        }
        return null;
    }

    /**
     * Setea un miembro de la clase con un valor dado y notifica que ha sido modificado para posterior 
     * guardado en base de datos
     * @param String $strMember mimebro de la clase al que añadimos un valor
     * @param String $newValue Valor definido al miembro
     */
    private function setAccessor($strMember, $newValue) {
        if(property_exists($this, $strMember)) {
            $this->$strMember = $newValue;
            $this->arrModifiedRelations[$strMember] = "1";
        }
    }

    /** 
     * Devuleve el valor de un miembro al llamarlo. Cargando los datops de la base de datos si 
     * aún no se han cargado.
     * @param String $strMember miembro al que se pide el valor
     * @return Object valor del miembro.
    */
    private function getAccessor($strMember) {
        if(!$this->isLoaded) {
            $this->load();
        }

        if(property_exists($this, $strMember)) {
            return $this->$strMember;
        } else {
            return null;
        }
    }

    /**
     * Se llama al destruir la instancia del objeto, si se ha marcado para que sae borre en base
     * de datos mediante el método markForDelete, se ejecuta el borrado.
     */
    function __destruct() {
        if(isset($this->ID)) {
            if($this->forDelete === true) {
                $strQuery = 'DELETE FROM ' . $this->tableName . ' WHERE id = :id';
                $objectStatement = $this->PDO->prepare($strQuery);
                $objectStatement->bindParam(':id', $this->ID, PDO::PARAM_INT);
                $objectStatement->execute();
            }
        }
    }
}

?>