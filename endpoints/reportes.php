<?php
session_start();
require_once('../db/conn.php');
require_once('../classes/Reporte.class.php');

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'profesor'])) {
        // Redirigir al dashboard si el rol no es válido
        header('Location: ../dashboard.php');
        exit;
    }

    $stats = new Statistics($conexion);
    $action = $_GET['type'] ?? 'main';
    $filters = json_decode($_GET['filters'] ?? '{}', true);

    switch($action) {
        case 'main':
            $data = $stats->getMainStats($filters);
            break;
        case 'lines':
            $data = $stats->getProjectsByLine($filters);
            break;
        case 'timeline':
            // también podrías pasar un intervalo, pero añadimos filtros
            $interval = $_GET['interval'] ?? 'month';
            $data = $stats->getProjectsTimeline($filters, $interval);
            break;
        case 'files':
            $data = $stats->getFilesByType($filters);
            break;
        default:
            throw new Exception('Tipo de estadística no válido', 400);
    }
    

    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'filters' => $filters
    ]);

} catch(Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>