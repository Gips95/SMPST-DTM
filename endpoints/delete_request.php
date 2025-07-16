<?php
include '../db/conn.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $request_id = intval($data['request_id'] ?? 0);
    $user_id = $_SESSION['user_id'] ?? 0;

    try {
        $conexion->begin_transaction(); // 👈 Iniciar transacción

        // 1. Eliminar items de la solicitud
        $stmtItems = $conexion->prepare("DELETE FROM download_request_items WHERE request_id = ?");
        $stmtItems->bind_param('i', $request_id);
        $stmtItems->execute();

        // 2. Eliminar la solicitud principal
        $stmtRequest = $conexion->prepare("DELETE FROM download_requests WHERE id = ? AND user_id = ?");
        $stmtRequest->bind_param('ii', $request_id, $user_id);
        $stmtRequest->execute();

        if ($stmtRequest->affected_rows > 0) {
            $conexion->commit(); // 👈 Confirmar cambios
            echo json_encode(['success' => true]);
        } else {
            $conexion->rollback(); // 👈 Revertir si falla
            echo json_encode(['success' => false, 'error' => 'Solicitud no encontrada']);
        }

    } catch (Exception $e) {
        $conexion->rollback(); // 👈 Revertir en caso de error
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
