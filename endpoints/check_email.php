<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/DRT/db/conn.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/DRT/tools/mailer.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/DRT/classes/RPWcodes.class.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/DRT/classes/Users.class.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/DRT/tools/const.php');

if($_SERVER['REQUEST_METHOD'] != 'POST' && !isset($_POST['email'])) header('location: ../login.php');
try{
    $conexion->begin_transaction();
    $email = trim($_POST['email']);
    $user = User::getUserByEmail($email, $conexion);
    $code = RPWcode::genRPWcode($conexion, RPW_KEY);
    $encrypted_code = Encrypt::EncryptData($code, RPW_KEY);

    RPWcode::InsertRPWcode($user['id'], $encrypted_code, $conexion);

    date_default_timezone_set('America/Caracas');
    $currentDate = new Datetime();

    if($recovery = recovery_pass_email($email, 'Tu codigo de verificacion - '.$currentDate->format("d/m/Y - H:i:s"), $code, $user['user']) != true){
        throw new Exception($recovery);
    }
    $conexion->commit();
    $conexion->close();

    print json_encode(array(
        'status'=>200,
        'id'=>$user['id'],
        'type'=>'check_email'
    ));

}catch(Exception $e){
    $conexion->rollback();
    print json_encode(array(
        'status'=>400,
        'msg'=>$e->getMessage(),
        'type'=>'check_email'
  ));
}
?>