<?php 

class PDOFactory {

    private static $instancias = array();

    public static function getSqlitePDO($strData) {
        $strKey = md5($strData);
        
        if(!array_key_exists($strKey, self::$instancias) || !self::$instancias[$strkey] instanceof PDO) {
            try {
                self::$instancias[$strkey] = new PDO("sqlite:" . ROOT . "/bbdd/" . $strData . ".db");
            } catch(Exception $e) {
                $e->getMessage();
            }
            
        }
        return self::$instancias[$strkey];
    }

}

?>