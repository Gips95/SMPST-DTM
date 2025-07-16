<?php
include '../db/conn.php';
include_once('../classes/Files.class.php');
include_once('../classes/Projects.class.php');
session_start();

if($_SERVER['REQUEST_METHOD'] != 'POST' || $_SESSION['rol'] != 'admin' || !isset($_POST['project_id'])) header('location: ../index.php');
header('Content-Type: application/json');

if (!function_exists('formatSize')) {
    function formatSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
try{

    $proyectid = intval($_POST['project_id']);
    $conexion->begin_transaction();
    $projecto = Project::getProject($proyectid, $conexion);

    $nombreCarpeta = preg_replace('/[^A-Za-z0-9_-]/', '_', $projecto['titulo']);
    $uploadDir = "../uploads/" . $nombreCarpeta . "/";

        // Crear directorio principal si no existe
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Directorio para referencias si no existe
        $rutaReferencias = $uploadDir . "referencias/";
        if (!file_exists($rutaReferencias)) {
            mkdir($rutaReferencias, 0777, true);
        }

        $docs = [];
        $refs = [];

    if (!empty($_FILES['documentos']['name'][0])) {
        foreach ($_FILES['documentos']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['documentos']['error'][$key] === UPLOAD_ERR_OK) {
                $project_file = new ProjectFile($_FILES['documentos']['name'][$key], $_FILES['documentos']['size'][$key], 'documento', $uploadDir);
                $file_id =  $project_file->CreateProjectFile($proyectid, $tmp_name, $conexion);

                $docs[] = ['id' => $file_id,
                           'name' => $project_file->getName(),
                           'size' => formatSize($project_file->getSize()),
                           'uploadDir' => $project_file->getRoute()];
            }
        }
    }

    // Subida de referencias
    if (!empty($_FILES['referencias']['name'][0])) {
        foreach ($_FILES['referencias']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['referencias']['error'][$key] === UPLOAD_ERR_OK) {
                $project_file = new ProjectFile($_FILES['referencias']['name'][$key], $_FILES['referencias']['size'][$key], 'referencia', $rutaReferencias);
                $file_id = $project_file->CreateProjectFile($proyectid, $tmp_name, $conexion);

                $refs[] =   ['id' => $file_id,
                            'name' => $project_file->getName(),
                            'size' => formatSize($project_file->getSize()),
                            'uploadDir' => $project_file->getRoute()];
            }
        }
    }

    $conexion->commit();
    $conexion->close();

    echo json_encode([
       'state' => 200,
       'message' => 'Archivos agregados',
       'body' => [
        'docs' => $docs,
        'refs' => $refs
       ]
    ]);

}catch(Exception $e){
    $conexion->rollback();
    echo $e->getMessage();

    echo json_encode([
        'state' => 400,
        'message' => $e->getMessage()
    ]);
    
}
?>