<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/DRT/db/conn.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/DRT/classes/RPWcodes.class.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/DRT/tools/const.php');

if($_SERVER["REQUEST_METHOD"] != "POST" && !isset($_POST['code']) && !isset($_POST['id'])) header('location: ../login.php');
try{
    $code = intval($_POST['code']);
    $userid = intval($_POST['id']);

    $codes = RPWcode::getRPWcodes($userid, $conexion);
    RPWcode::checkRPWcode($codes, $code, RPW_KEY, $conexion);
    $_SESSION['user_id_password_reset'] = $_POST['id'];
    $conexion->close();

    print json_encode(array(
        'status'=>200,
        'id'=>$_POST['id'],
        'type'=>'check_verification_code'
    ));
    

}catch(Exception $e){
    print json_encode(array(
        'status'=>400,
        'msg'=>$e->getMessage(),
        'type'=>'check_verification_code'
    ));
}
?>