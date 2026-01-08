<?php
session_start();

// 1. Vaciar datos de sesión
$_SESSION = [];
session_unset();

// 2. Borrar cookie PHPSESSID en el path correcto
$cookie = session_name();

// Borra en /fraganzza_pro
setcookie($cookie, '', time() - 3600, '/fraganzza_pro');
// Borra también en /fraganzza_pro/
setcookie($cookie, '', time() - 3600, '/fraganzza_pro/');
// Y por seguridad en raíz
setcookie($cookie, '', time() - 3600, '/');

// 3. Destruir sesión
session_destroy();

// 4. Redirigir
header("Location: ../index.php");
exit;
