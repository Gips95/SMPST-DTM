<?php
include '../db/conn.php';
session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
}

// Validar parámetro
$archivo_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$archivo_id || $archivo_id < 1) {
    header('HTTP/1.1 400 Bad Request');
    exit('ID inválido');
}

try {
    // Obtener ruta segura
    $stmt = $conexion->prepare("
        SELECT a.ruta 
        FROM archivos a
        INNER JOIN download_request_items i ON a.id = i.archivo_id
        INNER JOIN download_requests r ON i.request_id = r.id
        WHERE r.user_id = ? 
        AND r.status = 'aprobado'
        AND a.id = ?
    ");
    $stmt->bind_param('ii', $_SESSION['user_id'], $archivo_id);
    $stmt->execute();
    $stmt->bind_result($ruta_relativa);
    
    if (!$stmt->fetch()) {
        header('HTTP/1.1 404 Not Found');
        exit('Recurso no encontrado');
    }
    $stmt->close();

    // Construir ruta absoluta segura
    $base_dir = realpath($_SERVER['DOCUMENT_ROOT'] . '/DRT/uploads');
    $ruta_absoluta = realpath($base_dir . '/' . str_replace('NOmbre', 'Nombre', $ruta_relativa));

    // Verificaciones finales
    if (!$ruta_absoluta || !file_exists($ruta_absoluta)) {
        error_log("Archivo perdido: $ruta_absoluta");
        header('HTTP/1.1 404 Not Found');
        exit('El archivo no existe físicamente');
    }

    // Forzar descarga
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($ruta_absoluta) . '"');
    header('Content-Length: ' . filesize($ruta_absoluta));
    header('Cache-Control: must-revalidate');
    
    readfile($ruta_absoluta);
    exit;

} catch (Exception $e) {
    error_log('Error en descarga: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    exit('Error interno');
}