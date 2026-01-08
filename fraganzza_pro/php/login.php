<?php
session_start();
// Obtener valores del formulario
//??son como el if pero simplificado y seguero, 
//quiere decir que en caso de no haber email poner una cadena vacia
//Si la variable existe, email, usala, sino, usa cadena vacia!!!!!!!!!

    $email = $_POST['email'] ?? '';

    $password = $_POST['password'] ?? '';


require_once("conexion.php");

// Consulta SQL
$sql = "SELECT id, email, usuario, password, rol
        FROM usuarios 
        WHERE email = ? ";
        
//consultas preparadas 
$stmt = $conexion->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();

$resultado = $stmt->get_result();

if($resultado && $resultado->num_rows > 0){

    $tabla = $resultado->fetch_assoc(); // <-- lee datos

    $hashBD = $tabla['password'];

    if(password_verify($password, $hashBD)) {
        //echo "Usuario correcto. Hola, ".$tabla['usuario'];

        $_SESSION['id']      = $tabla['id'];
        $_SESSION['usuario'] = $tabla['usuario'];
        $_SESSION['user_id'] = $tabla['id'];
        $_SESSION['email']   = $tabla['email'];
        $_SESSION['rol']     = $tabla['rol'];



        if($tabla['rol'] === 'admin'){
            header('Location: panelAdmin.php');
            exit;
        } else {
            header('Location: ../index.php');
            exit;
        }

    } else {
        header("Location:../login.html");
        exit;
    }

} else {
    header("Location:../login.html");
    exit;
}
$stmt->close();
$conexion->close();

?>

