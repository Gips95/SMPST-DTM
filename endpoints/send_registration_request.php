<?php
session_start();

include('../db/conn.php');
include_once('../classes/Requests.class.php');
include_once('../classes/Users.class.php');

// Verificar permisos y datos requeridos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'invitado'
    || !isset($_POST['id'])
    || !isset($_POST['nombre'])
    || !isset($_POST['email'])
    || !isset($_POST['password'])) {
    exit('No cuentas con los permisos necesarios o faltan datos.');
}

try {
    $conexion->begin_transaction();

    // Obtener y sanitizar datos
    $id = (int) trim($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    // Crear usuario en estado 'pendiente'
    $user = new User($id, $nombre, $email, 'estudiante', $password);
    $user_id = $user->CreateUser($conexion, 'pendiente');

    // Registrar solicitud
    $request_id = Request::CreateRequest($user_id, 'registro', $conexion);

    // Confirmar transacción
    $conexion->commit();
    $conexion->close();

    // Redirigir al dashboard
    header('Location: ../dashboard.php');
    exit;

} catch (Exception $e) {
    $conexion->rollback();
    $conexion->close();
    die('Error: ' . $e->getMessage());
}
?>
