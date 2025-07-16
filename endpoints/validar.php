<?php
session_start();
include('../db/conn.php'); // Asegúrate de que esta conexión usa sentencias preparadas
include('../db/logs.php');
include_once('../classes/Users.class.php'); // Asegúrate de que getUserByCedula usa sentencias preparadas

ini_set('display_errors', 0); // Desactivar la visualización de errores en producción
error_reporting(E_ALL);

// Configura la cabecera para indicar que la respuesta es JSON
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Si no es una solicitud POST, devuelve un error JSON
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
    exit;
}

try {
    // Recogemos y saneamos la entrada
    $cedula   = trim($_POST["Cedula"]);
    $password = $_POST["password"];

    // Validación básica de entrada
    if (empty($cedula) || empty($password)) {
        throw new Exception("Cédula y contraseña son requeridas.");
    }

    // Buscamos al usuario por cédula
    $user = User::getUserByCedula($cedula, $conexion);

    // Verificar si el usuario existe
    if (!$user) {
        // Para mayor seguridad, usa un mensaje genérico para no revelar si el usuario existe o no
        throw new Exception("Credenciales inválidas.");
    }

    // Verificamos contraseña
    if (!password_verify($password, $user['pass'])) {
        throw new Exception("Credenciales inválidas.");
    }

    // Guardamos en sesión los datos necesarios
    $_SESSION["user_id"] = $user['id'];
    $_SESSION["user"] = $user['user'];
    $_SESSION["rol"] = $user['rol'];

    // Log de inicio de sesión
    log_action($cedula, 'login', 'Inicio de sesión exitoso');

    // Redirección al lugar de origen o dashboard
    $return_url = $_SESSION['return_url'] ?? 'dashboard.php';
    unset($_SESSION['return_url']);

    // Cerramos conexión (si aplica, dependiendo de cómo manejes la conexión en conn.php)
    $conexion = null; // Asumiendo que $conexion es una variable global o devuelta por conn.php

    // Si todo fue exitoso, devuelve un JSON de éxito con la URL de redirección
    echo json_encode(['success' => true, 'redirect' => $return_url]);
    exit;

} catch (Exception $e) {
    // En caso de cualquier error, devuelve un JSON con el mensaje de error
    // En producción, podrías querer un mensaje más genérico o loggear el error completo
    log_action($cedula ?? 'N/A', 'login_error', 'Error en inicio de sesión: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
