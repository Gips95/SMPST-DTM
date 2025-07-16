<?php
$host = "localhost"; // Servidor de MySQL (normalmente localhost)
$usuario = "root"; // Usuario de MySQL
$password = ""; // Contraseña (déjalo vacío si no tiene)
$base_datos = "dr"; // Nombre de la base de datos

// Crear conexión
try{
    $conexion = new mysqli($host, $usuario, $password, $base_datos);   
}catch(Exception $e){
    // Verificar conexión
    die("Error: ".$e->getMessage());
}

?>
