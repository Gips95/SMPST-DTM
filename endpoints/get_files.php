<?php
include '../db/conn.php';
include('../classes/Files.class.php');

if(!isset($_GET['project_id']) || $_SERVER['REQUEST_METHOD'] != 'GET') header('../dashboard.php');
try {
    $response = ['success' => false, 'message' => 'Error desconocido'];
    $files = ProjectFile::getProjectFiles($_GET['project_id'], $conexion);
    echo json_encode($files);

    $conexion->close();
} catch (Exception $e) {
    echo $e->getMessage();
}
?>