<?php
class Log{

    public static function CreateLog($action, $elementType, $elementid, $userid, $conexion, $details = null, $oldElement = null, $updatedObject = null){
        $Echanges = null;
        $band = false;

        $ip = filter_var(
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'],
            FILTER_VALIDATE_IP
        ) ?: 'IP inválida';

        // Nuevas acciones de exportación (print, csv, pdf)
        $exportActions = ['print', 'csv_export', 'pdf_export', 'excel_export', 'copy'];
        
        
        // Procesar solo para acciones que requieren comparación
        if(in_array($action, $exportActions)) {
            // No requiere procesamiento de cambios
            $band = true; // Registrar acción
        }
        elseif($action == 'update') {
            if($oldElement == null || $updatedObject == null) 
                throw new Exception('Se necesita el elemento previo y actualizado para registrar la modificacion');

            foreach($oldElement as $key => $element){
                if($element != $updatedObject[$key]){
                    $Echanges[$key] = [$element, $updatedObject[$key]];
                    $band = true;
                }
            }
        }
        elseif($action == 'delete'){
            if($oldElement == null) 
                throw new Exception('Se necesita el elemento previo para registrar la eliminacion');
            
            foreach($oldElement as $key => $element){
                $Echanges[$key] = [$element, 0];
                $band = true;
            }
        }

        if($band){
            $Echanges = $Echanges ? json_encode($Echanges, JSON_UNESCAPED_UNICODE) : null;
        } else {
            $Echanges = null; 
        }
        
        $stmt = $conexion->prepare('INSERT INTO log (user, action_type, element_id, element_type, action_details, ip_address, changes) VALUES (?, ?, ?, ?, ?, ?, ?)');
        if(!$stmt->bind_param('ssissss', $userid, $action, $elementid, $elementType, $details, $ip, $Echanges)) 
            throw new Exception('Error al bindear parametros: '.$conexion->error);
            
        if(!$stmt->execute()) 
            throw new Exception('Error al realizar el registro: '.$conexion->error);
        
        $stmt->close();
        return $conexion->insert_id;
    }
}

?>