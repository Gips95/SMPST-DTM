<?php
include('../db/conn.php');
include_once('../classes/Requests.class.php');
include_once('../classes/Notifications.class.php');
include_once('../classes/Users.class.php');
include_once('../tools/mailer.php');
session_start();

try{
    if(!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') throw new Exception('No cumples con los permisos necesarios');

    $conexion->begin_transaction();

    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    Request::ModifyApproval($request_id, $action, $conexion);
    $request = Request::getRequest($request_id, $conexion);

    if($request['request_type'] == 'registro'){
        if($request['aproved'] == 1){
            User::UpdateUserState($request['id_element'], 'activo', $conexion);
            $user = User::getUser($request['id_element'], $conexion);

            date_default_timezone_set('America/Caracas');
            $currentDate = new Datetime();
            notify_registration($user['email'], $currentDate->format("d/m/Y - H:i:s").' / Usuario Registrado con exito', $user['user'], $user['user']);
        }else{
            User::UpdateUserState($request['id_element'], 'rechazado', $conexion);
        }
    }
    
    $conexion->commit();
    $conexion->close();

    echo json_encode([
        'status'=>200
    ]);

}catch(Exception $e){
    $conexion->rollback();

    echo json_encode([
        'status'=>'400',
        'msg'=>$e->getMessage()
    ]);
}



?>