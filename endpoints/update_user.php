<?php
session_start();
include '../db/conn.php';
require_once('../classes/Users.class.php');
include_once('../classes/Logs.class.php');

if($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['user']) || !isset($_SESSION['user'])) header('../dashboard.php');
try {
    $id = intval($_POST['id']);
    $user = $_POST['user'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    $password = (isset($_POST['pass'])) ? $_POST['pass'] : null;

    $redirect = ($rol == 'profesor') ? 'Location: ../lista_profesores.php' : 'Location: ../lista_estudiantes.php';

    $conexion->begin_transaction();
    $old_user = User::getUser($id, $conexion);
    $user = new User($id, $user, $email, $rol, $password);
    $user->UpdateUser($conexion);
    $new_user = User::getUser($id, $conexion);

    Log::CreateLog('update', 'usuarios', $id, $_SESSION['user'], $conexion, null, $old_user, $new_user);

    $conexion->commit();
    $conexion->close();

    header($redirect."?success=1");

} catch (Exception $e) {
    $conexion->rollback();
    header($redirect."?error=1");
}
?>