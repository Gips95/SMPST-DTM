
<?php
include 'conn.php'; 
// Asegúrate de que conn.php inicia $conexion correctamente
function log_action($user, $action_type, $details = '') {
    global $conexion; // Asegurar que usamos la conexión global de MySQLi

    if (!$conexion instanceof mysqli) {
        error_log("Error crítico: La conexión no es una instancia de MySQLi");
        return false;
    }

    // Obtener la IP de manera segura
    $ip = filter_var(
        $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'],
        FILTER_VALIDATE_IP
    ) ?: 'IP inválida';

    try {
        $sql = "INSERT INTO log (user, action_type, action_details, ip_address) 
                VALUES (?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            error_log("Error al preparar la consulta: " . $conexion->error);
            return false;
        }

        // Bind de parámetros
        $stmt->bind_param("ssss", $user, $action_type, $details, $ip);
        $resultado = $stmt->execute();

        if (!$resultado) {
            error_log("Error al ejecutar la consulta: " . $stmt->error);
            return false;
        }

        $stmt->close(); // Cerramos el statement
        return true;

    } catch (Exception $e) {
        error_log("Excepción en log_action: " . $e->getMessage());
        return false;
    }
}
?>
