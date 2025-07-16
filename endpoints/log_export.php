<?php
session_start();
require_once ('../classes/Logs.class.php');
require_once ('../db/conn.php'); // Tu archivo de conexión a la BD

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'No autenticado']));
}

$action = $_POST['action_type'] ?? '';
$elementType = $_POST['element_type'] ?? 'proyectos';
$details = $_POST['details'] ?? '';

// Validar acción
$allowedActions = ['print', 'csv_export', 'pdf_export'];
if (!in_array($action, $allowedActions)) {
    die(json_encode(['success' => false, 'message' => 'Acción no válida']));
}

try {
    $logId = Log::CreateLog(
        $action,
        $elementType,
        0, // elementid: 0 porque no es un elemento específico
        $_SESSION['user_id'],
        $conexion,
        $details
    );
    echo json_encode(['success' => true, 'log_id' => $logId]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}