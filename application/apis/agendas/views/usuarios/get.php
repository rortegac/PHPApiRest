<?php
    if(is_null($usuarios)) {
        $json = json_encode(array("Error" => "Usuario no encontrado"));
    } else {
        $json = json_encode($usuarios);
    }

    
    echo $json
?>