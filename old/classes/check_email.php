<?php
include('../const.php');
include('mailer.php');
include('conn.php');
include('./methods/encryption.php');
session_start();

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])){
    try {
        $email = $_POST['email'];
        $query = $conexion->prepare("SELECT id, email, user FROM usuarios WHERE email=?");
        $query->bind_param("s",$email);
        $query->execute();
        if(!$query) throw new Exception('MYSQLI error: '. $conexion->error);
        $results = $query->get_result();

        if($results->num_rows != 1){
            throw new Exception("Este correo no existe");
        }
    
        $user = $results->fetch_assoc();
        $code = gen_code($conexion);

        if(gettype($code) != 'integer'){
            throw new Exception($code);
        }

        $encryptedCode = encrypt_data($code, RPW_KEY);

        $rpwquery = $conexion->prepare("INSERT INTO rpwtokens(id_usuario, codigo) VALUES(?,?)");
        $rpwquery->bind_param("is", $user['id'], $encryptedCode);
        $rpwquery->execute();
        if(!$rpwquery) throw new Exception('MYSQLI error: '. $conexion->error);

        date_default_timezone_set('America/Caracas');
        $currentDate = new Datetime();

        if($recovery = recovery_pass_email($email, 'Tu codigo de verificacion - '.$currentDate->format("d/m/Y - H:i:s"), $code, $user['user']) != true){
            throw new Exception($recovery);
        }

        $query->close();
        $rpwquery->close();
        $conexion->close();
        
        print json_encode(array(
            'status'=>200,
            'id'=>$user['id'],
            'type'=>'check_email'
        ));

    } catch (Exception $e) {
        print json_encode(array(
              'status'=>400,
              'msg'=>$e->getMessage(),
              'type'=>'check_email'
        ));
    }
  }else{
    header('location: ../login.php');
  }

?>