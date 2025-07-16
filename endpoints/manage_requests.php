<?php
// This script runs silently in the background to clean up download requests.
// It should be included at the very beginning of files that load your main UI (e.g., panel.php).

// Include your database connection file.
// This file should establish a connection and make the $conexion variable available.
// Ensure 'db/conn.php' does not output anything.
include_once 'db/conn.php'; 

// Use try-catch block for robust error handling without breaking page rendering.
try {
    // Start a database transaction to ensure atomicity of cleanup operations.
    $conexion->begin_transaction();

    // --- Cleanup: Deactivate requests created more than 24 hours ago ---
    $twentyFourHoursAgo = date('Y-m-d H:i:s', strtotime('-24 hours'));
    
    // 1. Get IDs of requests to be cleaned up by age
    $stmtGetOldIds = $conexion->prepare("SELECT id FROM download_requests WHERE created_at <= ? AND active = 1");
    if ($stmtGetOldIds === false) {
        throw new Exception("Error al preparar la consulta de IDs de solicitudes antiguas: " . $conexion->error);
    }
    $stmtGetOldIds->bind_param('s', $twentyFourHoursAgo);
    $stmtGetOldIds->execute();
    $oldRequestIdsResult = $stmtGetOldIds->get_result();
    $oldRequestIds = [];
    while ($row = $oldRequestIdsResult->fetch_assoc()) {
        $oldRequestIds[] = $row['id'];
    }
    $stmtGetOldIds->close();

    // 2. Deactivate these old requests in download_requests table
    if (!empty($oldRequestIds)) {
        $placeholders = implode(',', array_fill(0, count($oldRequestIds), '?'));
        $types = str_repeat('i', count($oldRequestIds));
        $stmtDeactivateOldRequests = $conexion->prepare("UPDATE download_requests SET active = 0 WHERE id IN ($placeholders)");
        if ($stmtDeactivateOldRequests === false) {
            throw new Exception("Error al preparar la desactivación de solicitudes antiguas: " . $conexion->error);
        }
        $stmtDeactivateOldRequests->bind_param($types, ...$oldRequestIds);
        $stmtDeactivateOldRequests->execute();
        $stmtDeactivateOldRequests->close();

        // 3. Delete associated items from download_request_items
        $stmtDeleteOldItems = $conexion->prepare("DELETE FROM download_request_items WHERE request_id IN ($placeholders)");
        if ($stmtDeleteOldItems === false) {
            throw new Exception("Error al preparar la eliminación de ítems de solicitudes antiguas: " . $conexion->error);
        }
        $stmtDeleteOldItems->bind_param($types, ...$oldRequestIds);
        $stmtDeleteOldItems->execute();
        $stmtDeleteOldItems->close();
    }

    // --- Cleanup: Deactivate requests with 'aprobado' or 'rechazado' status ---
    
    // 1. Get IDs of requests to be cleaned up by status
    $stmtGetResolvedIds = $conexion->prepare("SELECT id FROM download_requests WHERE status IN ('aprobado', 'rechazado') AND active = 1");
    if ($stmtGetResolvedIds === false) {
        throw new Exception("Error al preparar la consulta de IDs de solicitudes resueltas: " . $conexion->error);
    }
    $stmtGetResolvedIds->execute();
    $resolvedRequestIdsResult = $stmtGetResolvedIds->get_result();
    $resolvedRequestIds = [];
    //while ($row = $resolvedRequestIdsResult->fetch_assoc()) {
    //    $resolvedRequestIds[] = $row['id'];
    //}
    $stmtGetResolvedIds->close();

    // 2. Deactivate these resolved requests in download_requests table
    if (!empty($resolvedRequestIds)) {
        $placeholders = implode(',', array_fill(0, count($resolvedRequestIds), '?'));
        $types = str_repeat('i', count($resolvedRequestIds));
        $stmtDeactivateResolvedRequests = $conexion->prepare("UPDATE download_requests SET active = 0 WHERE id IN ($placeholders)");
        if ($stmtDeactivateResolvedRequests === false) {
            throw new Exception("Error al preparar la desactivación de solicitudes resueltas: " . $conexion->error);
        }
        $stmtDeactivateResolvedRequests->bind_param($types, ...$resolvedRequestIds);
        $stmtDeactivateResolvedRequests->execute();
        $stmtDeactivateResolvedRequests->close();

        // 3. Delete associated items from download_request_items
        $stmtDeleteResolvedItems = $conexion->prepare("DELETE FROM download_request_items WHERE request_id IN ($placeholders)");
        if ($stmtDeleteResolvedItems === false) {
            throw new Exception("Error al preparar la eliminación de ítems de solicitudes resueltas: " . $conexion->error);
        }
        $stmtDeleteResolvedItems->bind_param($types, ...$resolvedRequestIds);
        $stmtDeleteResolvedItems->execute();
        $stmtDeleteResolvedItems->close();
    }

    $conexion->commit(); // Commit the transaction if all operations were successful

} catch (Exception $e) {
    // If an error occurs, roll back the transaction to undo any changes
    $conexion->rollback(); 
    // Log the error for server-side debugging. This will not affect the user's page.
    error_log("Error en el procesamiento de limpieza de solicitudes al cargar: " . $e->getMessage()); 
} finally {
    // Ensure the database connection is closed.
    if (isset($conexion) && $conexion->ping()) {
        $conexion->close();
    }
}
?>
