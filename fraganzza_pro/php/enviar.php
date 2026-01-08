<?php
session_start();
//obtener valores del formulario

//comprobar antes que existan los datos
//Cambio de isset por consultas preparadas, prueba viernes 12
$usuario = $_POST['usuario'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

//Hasheo de la contraseña, como dijo jab cuando el password es por defecto
//utiliza Bcrypt.
$passwordHash = password_hash($password, PASSWORD_DEFAULT);


require_once("conexion.php");

//guardar los datos en la tabla
$sql="INSERT INTO usuarios(usuario, password, email) VALUES (?, ?, ?)";

$stmt = $conexion->prepare($sql);

if(!$stmt){
    die("No va bien".$conexion->error);
}

$stmt->bind_param("sss", $usuario, $passwordHash, $email);

//si va todo bien...
//si va todo bien...
if($stmt->execute()){
    
    // Obtener el ID del nuevo usuario insertado
    $new_user_id = $stmt->insert_id;

    // Establecer variables de sesión
    $_SESSION['id'] = $new_user_id;
    $_SESSION['usuario'] = $usuario;
    $_SESSION['user_id'] = $new_user_id; // Por compatibilidad si se usa asi en login
    $_SESSION['email'] = $email;
    $_SESSION['rol'] = 'user'; // Asumimos rol por defecto 'user' ya que es un registro público

    header('Location: ../index.php');
    exit;
} else {
    //si no..
    echo "Error al registrar: ".$stmt->error;
}

//cerramos conexion
$stmt->close();
$conexion->close();

?>