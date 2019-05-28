<?php
// definimos la ubicación de la raiz
define('ROOT', dirname(__FILE__));

include_once(ROOT . "/application/models/class.Api.php");

$api = new Api("agendas");
$api->createRequest();
$api->setResource("/usuarios/{idusuario}/agendas/{idagenda}");
$api->getResource();




?>