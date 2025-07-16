<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/DRT/classes/Encrypt.class.php');

class RPWcode extends Encrypt{

    public static function genRPWcode($conn, $key){
            $arr = [];
            $res = $conn->query('SELECT id, codigo FROM rpwtokens'); //extrae todos los codigos
            if(!$res) throw new Exception($conn->error);
    
            if($res->num_rows > 0){
                while($code = $res->fetch_assoc()){
                    $arr[] = parent::DecryptData($code['codigo'], $key);
                } 
            }

            do {
                $randomNumber = rand(10000, 99999); //genera un numero aleatorio de 5 digitos
            } while (in_array($randomNumber, $arr)); //verifica si el numero aleatorio esta presente en el array con los demas codigos de la base de datos o no
            $res->close();
            return $randomNumber;
    }

    public static function getRPWcodes($userid, $conexion){
        $query = $conexion->prepare("SELECT * FROM rpwtokens WHERE id_usuario=?");
        $query->bind_param("i",$userid);
        $query->execute();

        if(!$query) throw new Exception('error: '.$conexion->error);
        $res = $query->get_result();
    
        if($res->num_rows <= 0){
            throw new Exception("No hay codigos asociados a este usuario");
        }

        $codes = [];
        while($res_codes = $res->fetch_assoc()){
            $codes[] = $res_codes;
        }
        $query->close();
        return $codes;
    }

    public static function DeleteRPWcodes($userid, $conexion){
        $stmt = $conexion->prepare("DELETE FROM rpwtokens WHERE id_usuario = ?");
        $stmt->bind_param('i', $userid);
        if(!$stmt->execute()) throw new Exception('Error al eliminar codigos de verificacion del usuario');
        $stmt->close();
    }

    public static function InsertRPWcode($userid, $encryptedCode, $conexion){
        $rpwquery = $conexion->prepare("INSERT INTO rpwtokens(id_usuario, codigo) VALUES(?,?)");
        $rpwquery->bind_param("is", $userid, $encryptedCode);
        $rpwquery->execute();
        if(!$rpwquery) throw new Exception('MYSQLI error: '. $conexion->error);
        $rpwquery->close();
    }

    public static function checkRPWcode($codelist, $code, $key, $conexion){
        $code_found = null;
        
        foreach($codelist as $uniqcode){
            $desciphercode = parent::DecryptData($uniqcode['codigo'], $key);

            if($desciphercode == $code){
                $code_found = $uniqcode;
                break;
            }
        }
            
        if($code_found == null) throw new Exception("Codigo incorrecto");

        if($code_found['expirado']) throw new Exception("El codigo ha expirado");

        if(!$code_found['activo']) throw new Exception("El codigo ya ha sido usado");

        $query_close_rpwtoken = $conexion->prepare("UPDATE rpwtokens SET activo=0 WHERE id=?");
        $query_close_rpwtoken->bind_param('i', $code_found['id']);
        $query_close_rpwtoken->execute();

        $query_close_rpwtoken->close();
    }
}
?>