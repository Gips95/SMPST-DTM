<?php
session_start();
include '../db/conn.php';
include_once('../classes/Projects.class1.php');

if($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../dashboard.php');
    exit();
}

try {
    $buscar = $_POST['buscar'] ?? '';
    $ente = $_POST['ente'] ?? null;
    $tutor = $_POST['tutor'] ?? null;
    $fecha = $_POST['fecha'] ?? null;
    $orden = $_POST['orden_fecha'] ?? 'ASC';
    $page = $_POST['page'] ?? 1; // Obtener el número de página
    $per_page = $_POST['per_page'] ?? 5; // Obtener la cantidad por página

    // Obtener el array con el HTML y la información de paginación
    $result = Project::FilterProjects(
        $buscar,
        $ente,
        $conexion,
        $tutor,
        $fecha,
        $orden,
        $page,
        $per_page
    );

    // Enviar la respuesta como JSON
    header('Content-Type: application/json');
    echo json_encode($result);

} catch(Exception $e) {
    error_log("Error en filter_projects: " . $e->getMessage());
    // Enviar un error como JSON
    header('Content-Type: application/json');
    echo json_encode(array(
        'html' => '<div class="error">Error en la solicitud</div>',
        'current_page' => 1,
        'total_pages' => 1
    ));
}
?>