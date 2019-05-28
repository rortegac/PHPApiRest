<?php
include(ROOT . "/application/models/class.model.php");

class Usuarios extends Model {

    protected $username;

    protected function defineTableName() {
        return "usuarios";
    }

    protected function defineRelationMap() {
        return array(
            "id" => "ID",
            "username" => "username"
        );
    }

    public function __construct() {
        $pdo = PDOFactory::getSqlitePDO("agendas");
        parent::__construct($pdo);
    }

    public function getListaUsuariosParaPrueba($idusuario) {
        $datosPrueba = array(
            array("id" => 1, "username" => "primero"),
            array("id" => 2, "username" => "segundo"),
            array("id" => 3, "username" => "tercero"),
            array("id" => 4, "username" => "cuarto"),
            array("id" => 5, "username" => "quinto"),
            array("id" => 6, "username" => "sexto")
        );

        foreach($datosPrueba as $dato) {
            if($dato["id"] == $idusuario) {
                return $dato;
            }
        }

        return null;
    }
    
}

?>