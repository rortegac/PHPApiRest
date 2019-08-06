<?php

class Logger {

    const DEBUG     = 80;
    const INFO      = 60;
    const WARNNING  = 40;
    const ERROR     = 20;
    const CRITICAL  = 1;

    const NIVEL_TRAZAS = self::DEBUG;

    /**
     * Añade una linea al Log
     *
     * @param int $level Nivel de severidad de la traza
     * @param string $msg Mensaje de la traza
     * @return void
     */
    public static function log($level, $msg) {
        if(is_numeric($level) && $level <= self::NIVEL_TRAZAS) {
            $fecha = date("d/m/Y H:i:s");
            $backTrace =  debug_backtrace();
            $backTraceCallFile = $backTrace[1];
            $traza = $fecha . "\t" . self::levelToString($level) . "\t" . $backTraceCallFile["file"] . ":" . $backTraceCallFile["line"] . "\t" . $msg . "\n";
            self::saveLog($traza);
        }
    }

    /**
     * Guarda la traza de log en el fichero indicado
     *
     * @param string $traza traza a guardar
     * @return void
     */
    private  function saveLog($traza) {
        $nombreFichero = "trazas" . date("dmY") . ".log";
        $fichero = fopen($nombreFichero, "a");
        fwrite($fichero, $traza);
        fclose($fichero);
    }

    /**
     * Realiza la conversión a cadena de los valores del nivel de trazas
     *
     * @param int $level nivel de trazas
     * @return string cadena correspondiente al valor de nivel de trazas
     */
    private static function levelToString($level) {
        if ($level === self::DEBUG) return "DEBUG";
        if ($level === self::INFO) return "INFO";
        if ($level === self::WARNNING) return "WARNNING";
        if ($level === self::ERROR) return "ERROR";
        if ($level === self::CRITICAL) return "CRITICAL";
        return strval($level);
    }

}

?>