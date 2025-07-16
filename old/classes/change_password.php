<?php
include('conn.php');
if(!isset($_SESSION['user']) || $_SERVER['REQUEST_METHOD'] != 'POST') header('location: ..login.php');

try {
    $actual_password = $_POST['actual-pass'];
    $new_password = $_POST['first-pass'];

    $checkpass_query = $conexion->prepare('SELECT pass FROM usuarios WHERE id = ?');
    $checkpass_query->bind_param('i', $_SESSION['user']);
    $checkpass_query->execute();

    if(!$checkpass_query) throw new Exception($conexion->error);

    $userpass = $checkpass_query->fetch_assoc();

    if(!password_verify($actual_password, $userpass['pass'])) throw new Exception('La contraseña no es correcta');

    $changepass_query = $conexion->prepare('UPDATE usuarios SET pass = ? WHERE id = ?');
    $changepass_query->bind_param('si', $new_password, $_SESSION['user']);
    $changepass_query->execute();

    if(!$changepass_query) throw new Exception($conexion->error);

    //se devuelve una respuesta en json
    print json_encode(array(
        'status'=>200,
        'type'=>'change_password'
    ));

} catch (Exception $e) {
    print json_encode(array(
        'status'=>400,
        'msg'=> $e->getMessage(),
        'type'=>'change_password'
    ));
}
?>