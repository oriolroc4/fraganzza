<?php

//conectar con la base de datos
//si no conectamos con la base de datos no podemos guardar los datos
define('SERVIDOR', 'localhost');
define('USUARIO', 'root');
define('PASSWORD', '');
define('BASEDEDATOS', 'login');
//conexion
$conexion = new mysqli(SERVIDOR, USUARIO, PASSWORD, BASEDEDATOS);
//comprobar si hay error de conexion
if($conexion->connect_error){
    die("Error de conexion");
}

?>