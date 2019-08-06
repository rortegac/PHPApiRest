<?php
include_once("class.PDOFactory.php");
abstract class Model {
    protected $_model;
    protected $Id;
    protected $PDO;
    protected $tableName;
    protected $relationMap;
    protected $arrModifiedRelations;
    protected $isLoaded;
    protected $forDelete;
    protected $idAutoDefined;
    protected $objectExist;
    abstract protected function defineTableName();
    abstract protected function defineRelationMap();
 
    function __construct($PDO) { 
        $this->_model = get_class($this);
        $this->tableName = $this->defineTableName();
        $this->relationMap = $this->defineRelationMap();
        $this->PDO = $PDO;
        $this->arrModifiedRelations = array();
        $this->isLoaded = false;
        $this->idAutoDefined = false;
        $this->objectExist = null;
    }
    
    /**
     * Carga un objeto en la clase desde la base de datos si se ha definido el id de este,
     * guardando cada valor de la columna en la propiedad de la clase correspondiente (Definida en defineRelationMap())
     * 
     * @return void
     */
    public function load() {
        if($this->existObject() && !$this->isLoaded) {
            $strQuery = 'SELECT * ';
            $strQuery .= 'FROM ' . $this->tableName;
            $strQuery .= 'WHERE id = :id';
            $objectStatement = $this->PDO->prepare($strQuery);
            $objectStatement->bindValue(':id', $this->ID);
            $result = $objectStatement->execute();
            if(!$result) {
                Logger::log(Logger::ERROR, "Fallo al ejecutar query SELECT. Error: " . $objectStatement->errorCode() . " " . $objectStatement->errorInfo()[2]);
            }
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
     * 
     * @return void
     */
    public function save() {

        if((isset($this->ID) || $this->isLoaded) && !$this->idAutoDefined) {
            $this->updateDB();
        } else if(!$this->existObject()) {
            $this->insertDB();
        } else {
            $this->updateDB();
        }
    }


    /**
     * Inserta en la Base de Datos una nueva fila con los valores ya definidos.
     * Se encarga también de guardar el ID del objeto insertado.
     *
     * @return void
     */
    protected function insertDB() {
        $strValueList = "";
        $strQuery = 'INSERT INTO ' . $this->tableName . ' (';
        foreach($this->relationMap as $key => $value) {
            $actualVal = $this->$value;
            if(isset($actualVal)) {
                if(array_key_exists($value, $this->arrModifiedRelations)) {
                    $strQuery .= $key . ', ';
                    $strValueList .= ":$value, ";
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
                        $objectStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_INT);
                    } else {
                        $objectStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_STR);
                    }
                }
            }
        }
        
        $result = $objectStatement->execute();

        if(!$result) {
            Logger::log(Logger::ERROR, "Fallo al ejecutar query query de INSERT. Error: " . $objectStatement->errorCode() . " " . $objectStatement->errorInfo()[2]);
            //exit;
        }

        if(!$this->idAutoDefined) {
            $this->ID = $this->PDO->lastInsertId();
        }

        $this->objectExist = true;
        
    }


    /**
     * Actualiza el objeto en la base de datos con los valores modificados
     *
     * @return void
     */
    protected function updateDB() {
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
                    $objectStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_INT);
                } else {
                    $objectStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_STR);
                }
            }
        }
        $result = $objectStatement->execute();

        if(!$result) {
            Logger::log(Logger::ERROR, "Fallo al ejecutar query de UPDATE. Error: " . $objectStatement->errorCode() . " " . $objectStatement->errorInfo()[2]);
        }
    }


    /**
     * Comprueba si el objeto con el id especificado existe en la base de datos
     *
     * @return boolean True si el objeto existe, false si no.
     */
    protected function existObject() {
        if($this->objectExist === null) {
            if(isset($this->ID)) {         
                $strQuery = 'SELECT count(id) FROM ' . $this->tableName . 'WHERE id = :id';
                $objectStatement = $this->PDO->prepare($strQuery);
                $objectStatement->bindValue(':id', $this->ID);
                $result = $objectStatement->execute();
                if(!$result) {
                    Logger::log(Logger::ERROR, "Fallo al ejecutar query SELECT. Error: " . $objectStatement->errorCode() . " " . $objectStatement->errorInfo()[2]);
                    //exit;
                }
                $row = $objectStatement->fetchColumn();
    
                if($row > 0) {
                    $this->objectExist = true;
                } 
                $this->objectExist = false;
            }
        }

        return $this->objectExist;
        
    }


    /**
     * Indica que el objeto se va a borrar d ela base de datos
     * 
     * @return void
     */
    public function markForDelete() {
        $this->forDelete = true;
    }


    /**
     * Método mágico __call. Se encargar de controlar las consultas o modificaciones de miembros de
     * la clase devolviendo o seteando el adecuado.
     * 
     * @param string $strFunction nombre del miembro al que se llama
     * @param array $arguments, argumentos con los que se llama al mimebro. El primero [0] será el valor de este al setearlo
     * @return mixed Devuleve una llamada al método que se encarga de setear o devolver el miembro.
     */
    public function __call($strFunction, $arguments) {
        $methodType = substr($strFunction, 0, 3);
        $strMethodMember = substr($strFunction, 3);
        switch($methodType) {
            case "set":
                return $this->setAccessor($strMethodMember, $arguments[0]);
                break;
            case "get":
                return $this->getAccessor($strMethodMember);
                break;
        }
        return null;
    }


    /**
     * Setea un miembro de la clase con un valor dado y notifica que ha sido modificado para posterior 
     * guardado en base de datos. Si se setea el ID se indicarára para que al guardar haga un insert y no un update.
     * 
     * @param string $strMember mimebro de la clase al que añadimos un valor
     * @param string $newValue Valor definido al miembro
     * @return void
     */
    protected function setAccessor($strMember, $newValue) {
        if(property_exists($this, $strMember)) {
            if(!$this->isLoaded || ($this->isLoaded && $newValue !== $this->$strMember)) {
                $this->arrModifiedRelations[$strMember] = "1";
            }
            $this->$strMember = $newValue;   
            

            if($strMember == "ID") {
                $this->idAutoDefined = true;
            }

        }
    }


    /** 
     * Devuleve el valor de un miembro al llamarlo. Cargando los datos de la base de datos si 
     * aún no se han cargado.
     * 
     * @param string $strMember miembro al que se pide el valor
     * @return mixed valor del miembro.
    */
    protected function getAccessor($strMember) {
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
     * 
     * @return void
     */
    function __destruct() {
        if(isset($this->ID)) {
            if($this->forDelete === true) {
                $strQuery = 'DELETE FROM ' . $this->tableName . ' WHERE id = :id';
                $objectStatement = $this->PDO->prepare($strQuery);
                $objectStatement->bindParam(':id', $this->ID, PDO::PARAM_INT);
                $result = $objectStatement->execute();

                if(!$result) {
                    Logger::log(Logger::ERROR, "Fallo al ejecutar query de DELETE. Error: " . $objectStatement->errorCode() . " " . $objectStatement->errorInfo()[2]);
                }
            }
        }
    }
}
?>