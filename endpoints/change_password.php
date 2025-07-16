<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/DRT/db/conn.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/DRT/classes/Users.class.php');
if(!isset($_SESSION['user']) || $_SERVER['REQUEST_METHOD'] != 'POST') header('location: ..login.php');
try{
    $conexion->begin_transaction();
    $actual_password = $_POST['actual-pass'];
    $new_password = $_POST['first-pass'];

    $actual_user = User::getUserByEmail($_SESSION['user'], $conexion);
    if(!password_verify($actual_password, $actual_user['pass'])) throw new Exception('La contraseña no es correcta');
    User::ChangePassword($actual_user['id'], $new_password, $conexion);

    $conexion->commit();
    $conexion_>close();

    print json_encode(array(
        'status'=>200,
        'type'=>'change_password'
    ));

}catch(Exception $e){
    $conexion->rollback();
    print json_encode(array(
        'status'=>400,
        'msg'=> $e->getMessage(),
        'type'=>'change_password'
    ));
}
?>