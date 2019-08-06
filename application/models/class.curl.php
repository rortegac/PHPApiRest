<?php

    class Curl {

        private $url;
        private $cliente;
        private $postFields;

        /**
         * @var mixed Contiene la respuesta de la llamada CURL efectuada, permite acceso público directamente.
         */
        public $response;

        /**
         * @var int Contiene el estado de la respuesta después de realizar la llamada CURL.
         */
        public $httpStatusCode;


        /**
         * Instancia la clase, si se le pasa una URL por parámetro podrá hacer una llamada GET a esta.
         *
         * @param string $url url a la cual hacer la llamada mediante get
         */
        public function __construct($url = null) {
            $this->cliente = curl_init();
            $this->postFields = "";
            if(is_null($url)) {

            } else {
                $this->get($url);
            }
        }

        
        /**
         * Realiza la llamada Curl a la URL mediante el método get
         *
         * @param string $url dirección de la cual se quiere obtener los datos
         * @return Curl devuelve la misma instanica de la clase.
         */
        public function get($url) {
            $this->setOption(CURLOPT_URL, $url);
            $this->setOption(CURLOPT_RETURNTRANSFER, 1);
            $this->response = curl_exec($this->cliente);
            $this->httpStatusCode = curl_getinfo($this->cliente, CURLINFO_HTTP_CODE);
            return $this;
        }


        /**
         * Realiza una llamada Curl mediante el método post
         *
         * @param string $url
         * @param array $params parametros post que se envian con la llamada (clave => valor).
         * @return Curl devuelve la misma instanica de la clase.
         */
        public function post($url, $params = []) {
            $this->setOption(CURLOPT_URL, $url);
            $this->setOption(CURLOPT_RETURNTRANSFER, 1);
            $this->setOption(CURLOPT_POST, 1);

            if(is_array($params) && count($params) > 0) {
                $this->setPostParams($params);
            }

            // Se elimina el último caracter & de la query (porque sobra).
            if(strlen($this->postFields) > 0) {
                $this->postFields = substr($this->postFields, 0, -1);
            }
            
            $this->setOption(CURLOPT_POSTFIELDS, $this->postFields);
            $this->response = curl_exec($this->cliente);
            $this->httpStatusCode = curl_getinfo($this->cliente, CURLINFO_HTTP_CODE);
            return $this;
        }


        /**
         * Añade una opción a la llamada Curl actual.
         *
         * @param int $option Constante de opción
         * @param mixed $value Valor de la opción
         * @return void
         */
        public function setOption($option, $value) {
            curl_setopt($this->cliente, $option, $value);
        }


        /**
         * Añade parámetros post a la llamada
         *
         * @param mixed $key si es un Array añadirá la claves valor a los valores. Si no lo es usará como clave.
         * @param string $value valor del parámetro, solo se usa si el primer parámetro no es un array
         * @return void
         */
        public function setPostParams($key, $value = null) {
            if(is_array($key)) {
                foreach($key as $k => $v) {
                    $this->postFields .= $k . "=" . $v . "&";
                }
            } else {
                if(!is_null($value))
                    $this->postFields .= $key . "=" . $value . "&";
            }
        }


        /**
         * Aplica un timeout máximo en segundos a la llamada CURL.
         *
         * @param int Segundos de timeout
         * @return void
         */
        public function setTimeOut($timeout) {
            if(is_numeric($timeout))
                $this->setOption(CURLOPT_TIMEOUT, $timeout);
        }


        /**
         * Se llama al destruirse la clase, cierra la conexión CURL.
         * 
         * @return void
         */
        public function __destruct() {
            curl_close($this->cliente);
        }

    }

?>