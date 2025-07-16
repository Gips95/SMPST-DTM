<?php
session_start();
include('../const.php');
include('./conn.php');
include('./methods/encryption.php');

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['code']) && isset($_POST['id'])){
    try{
        $code = $_POST['code'];

        $query = $conexion->prepare("SELECT * FROM rpwtokens WHERE id_usuario=?");
        $query->bind_param("i",$_POST['id']);
        $query->execute();

        if(!$query) throw new Exception('error: '.$conexion->error);
        $res = $query->get_result();
    

        if($res->num_rows <= 0){
            throw new Exception("No hay codigos asociados a este usuario");
        }

        $band = false;
        $code_found;
        
        while(($tokens = $res->fetch_assoc()) && !$band ){
            //if(!$ciphercode = decrypt_data($tokens['codigo'], RPW_KEY)) throw new Exception(openssl_error_string());
            $ciphercode = decrypt_data($tokens['codigo'], RPW_KEY);

            if($ciphercode == $code){
                $band = true;
                $code_found = $tokens;
            }
        }
            
        if(!$band) throw new Exception("Codigo incorrecto");

        if($code_found['expirado']) throw new Exception("El codigo ha expirado");

        if(!$code_found['activo']) throw new Exception("El codigo ya ha sido usado");

        $query_close_rpwtoken = $conexion->prepare("UPDATE rpwtokens SET activo=0 WHERE codigo=?");
        $query_close_rpwtoken->bind_param('s', $code_found['codigo']);
        $query_close_rpwtoken->execute();

        $query->close();
        $query_close_rpwtoken->close();
        $conexion->close();

        $_SESSION['user_id_password_reset'] = $_POST['id'];

        //header('location: ../authentication/reset_password.php');

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
}else{
    header('location: ../login.php');
}

?>
