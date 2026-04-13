<?php

define('SERVIDOR',    getenv('DB_HOST')     ?: 'db');
define('USUARIO',     getenv('DB_USER')     ?: 'fraganzza');
define('PASSWORD',    getenv('DB_PASSWORD') ?: 'fraganzza2025');
define('BASEDEDATOS', getenv('DB_NAME')     ?: 'login');

$conexion = new mysqli(SERVIDOR, USUARIO, PASSWORD, BASEDEDATOS);

if ($conexion->connect_error) {
    die("Error de conexion: " . $conexion->connect_error);
}
?>
