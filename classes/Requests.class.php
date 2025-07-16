<?php
class Request{
    public static function CreateRequest($id_element, $request_type, $conexion){
        $stmt = $conexion->prepare("INSERT INTO requests (id_element, request_type) VALUES (?, ?)");
        $stmt->bind_param('is', $id_element, $request_type);
        $stmt->execute();
        $stmt->close();
        
        $request_id = $conexion->insert_id;    
        return $request_id;
    }

    public static function ModifyApproval($id_request, $aprove, $conexion){
        $final_approve = intval($aprove);
        $stmt = $conexion->prepare('UPDATE requests SET aproved = ? WHERE id = ?');
        $stmt->bind_param('ii', $final_approve, $id_request);
        $stmt->execute();
        $stmt->close();
    }

    public static function getRequests($request_type=null, $aproved=null, $conexion){
        $sql = 'SELECT * FROM requests WHERE 1';
        $params = [];
        $types = '';

        if(!empty($request_type)){
            $sql.='AND request_type = ?';
            $params[] = $request_type;
            $types.='s';
        }
        
        if(!empty($aproved)){
            $apr = intval($aproved);
            $sql.='AND aproved = ?';
            $params[] = $apr;
            $types.='i';
        }

        $stmt = $conexion->prepare($sql);
        if(!empty($request_type) || !empty($aproved)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        $requests = $res->fetch_all(MYSQLI_ASSOC);
        return $requests;
    }
   public static function getR( $conexion){
        $sql = 'SELECT * FROM requests WHERE aproved = 0';
       
        $stmt = $conexion->prepare($sql);
       
        $stmt->execute();
        $res = $stmt->get_result();

        $requests = $res->fetch_all(MYSQLI_ASSOC);
        return $requests;
    }
    public static function getRequest($id, $conexion){
        $stmt = $conexion->prepare('SELECT * FROM requests WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if($res->num_rows <= 0) throw new Exception('No se encontro una peticion con este identificador');
        $request = $res->fetch_assoc();
        $stmt->close();

        return $request;
        
    }
     public static function getcountrequest($conexion){
        $sql = 'SELECT COUNT(*) as total FROM requests WHERE aproved = 0';
       
        $stmt = $conexion->prepare($sql);
       
        $stmt->execute();
        $res = $stmt->get_result();

        $request = $res->fetch_assoc();
           return (int)$request['total'];
    }
}

?>