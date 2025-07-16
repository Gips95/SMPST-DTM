<?php
session_start();
header('Content-Type: application/json');

include_once('../classes/Requests.class.php');

try {
    // Verificar autenticación
  

    // Conexión reusable
    $host = "localhost";
    $usuario = "root";
    $password = "";
    $base_datos = "dr";
    
    $conexion = new mysqli($host, $usuario, $password, $base_datos);
    
    if ($conexion->connect_error) {
        throw new Exception("Error de conexión: " . $conexion->connect_error);
    }

    // Obtener conteo
    $count = Request::getcountrequest($conexion);
    
    echo json_encode([
        'success' => true,
        'count' => $count
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() >= 400 ? $e->getCode() : 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conexion) )$conexion->close();
    
}