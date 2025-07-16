<?php

include '../db/conn.php';
include_once('../classes/Files.class.php');
include_once('../classes/Projects.class.php');
include_once('../classes/Logs.class.php');

session_start();
// Configurar cabeceras para JSON
header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Error desconocido'];
file_put_contents('debug.log', print_r($_POST, true)); 
try {
    // Validar datos requeridos
    $requiredFields = ['titulo', 'descripcion', 'autores', 'linea_investigacion', 'ente', 'tutor', 'fecha'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }
    
    // Sanitizar datos
    $titulo = htmlspecialchars(strtoupper(trim($_POST['titulo'])));
    $descripcion = htmlspecialchars(trim($_POST['descripcion']));
    $autores = htmlspecialchars(trim($_POST['autores']));
    $linea_investigacion = htmlspecialchars(trim($_POST['linea_investigacion']));
    $ente = htmlspecialchars(trim($_POST['ente']));
    $tutores = htmlspecialchars(trim($_POST['tutor']));
    $fecha = htmlspecialchars(trim($_POST['fecha']));

    if (!isset($conexion)) {
        throw new Exception("Error en la conexión a la base de datos.");
    }

    $conexion->begin_transaction();

    // Corregir instanciación de la clase Project
    $n_project = new Project($titulo, $descripcion, $autores, $tutores, $linea_investigacion, $ente, $fecha);
    [$proyectid, $docsDir, $refDir] = $n_project->CreateProject($conexion);

    Log::CreateLog('create', 'projectos', $proyectid, $_SESSION['user'], $conexion);


     // Subida de documentos
    if (!empty($_FILES['documentos']['name'][0])) {
        foreach ($_FILES['documentos']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['documentos']['error'][$key] === UPLOAD_ERR_OK) {
                $project_file = new ProjectFile($_FILES['documentos']['name'][$key], $_FILES['documentos']['size'][$key], 'documento', $docsDir);
                $project_file->CreateProjectFile($proyectid, $tmp_name, $conexion);
            }
        }
    }

    // Subida de referencias
    if (!empty($_FILES['referencias']['name'][0])) {
        foreach ($_FILES['referencias']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['referencias']['error'][$key] === UPLOAD_ERR_OK) {
                $project_file = new ProjectFile($_FILES['referencias']['name'][$key], $_FILES['referencias']['size'][$key], 'referencia', $refDir);
                $project_file->CreateProjectFile($proyectid, $tmp_name, $conexion);
            }
        }
    }
    

    $conexion->commit();
    $conexion->close();
    $response = ['success' => true, 'message' => 'Proyecto creado exitosamente'];
} catch (Exception $e) {
    if (isset($conexion)) {
        $conexion->rollback();
    }
    $response['message'] = $e->getMessage();
}

// Devolver respuesta JSON
echo json_encode($response);
?>
