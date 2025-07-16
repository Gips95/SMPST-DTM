<?php
session_start();
include '../db/conn.php';
include_once('../classes/Files.class.php');
include_once('../classes/Logs.class.php');
if(!isset($_POST['id']) || $_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['user'])) header('../dashboard');

try {
    $conexion->begin_transaction();
    $idArchivo = intval($_POST['id']);
    $archivo = ProjectFile::getFile($idArchivo, $conexion);
    ProjectFile::DeleteProjectFile($idArchivo, $archivo['ruta'], $conexion);
    Log::CreateLog('delete', 'archivos', $archivo['id'], $_SESSION['user'], $conexion, null, $archivo);

    $conexion->commit();
    $conexion->close();
    echo json_encode(['success' => true, 'message' => 'Archivo eliminado']);

} catch (Exception $e) {
    $conexion->rollback();
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el archivo: ' . $e->getMessage()]);
}
