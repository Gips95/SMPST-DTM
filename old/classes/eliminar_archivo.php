<?php
session_start();
if (!isset($_SESSION["user"])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}
include 'conn.php';

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no especificado']);
    exit();
}

$idArchivo = intval($_POST['id']);

// Primero, obtener la ruta del archivo a eliminar
$sqlSelect = "SELECT ruta FROM archivos WHERE id = ?";
$stmtSelect = $conexion->prepare($sqlSelect);
$stmtSelect->bind_param("i", $idArchivo);
$stmtSelect->execute();
$result = $stmtSelect->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Archivo no encontrado']);
    exit();
}
$row = $result->fetch_assoc();
$rutaArchivo = $row['ruta'];
$stmtSelect->close();

// Eliminar el archivo físico
if (file_exists($rutaArchivo)) {
    if (!unlink($rutaArchivo)) {
        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el archivo físico']);
        exit();
    }
}

// Eliminar el registro de la base de datos
$sqlDelete = "DELETE FROM archivos WHERE id = ?";
$stmtDelete = $conexion->prepare($sqlDelete);
$stmtDelete->bind_param("i", $idArchivo);
if ($stmtDelete->execute()) {
    echo json_encode(['success' => true, 'message' => 'Archivo eliminado']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el archivo: ' . $stmtDelete->error]);
}
$stmtDelete->close();
$conexion->close();
?>
