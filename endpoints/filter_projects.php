<?php
session_start();
include '../db/conn.php';
include_once('../classes/Projects.class.php');

if($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../dashboard.php');
    exit();
}

// Limpiar cualquier salida previa
ob_clean();

try {
    $buscar = $_POST['buscar'] ?? '';
    $ente = $_POST['ente'] ?? null;
    $tutor = $_POST['tutor'] ?? null;
    $fecha = $_POST['fecha'] ?? null;
    $año = $_POST['año'] ?? null;
    $linea = $_POST['linea_investigacion'] ?? null;
    $orden = $_POST['orden_fecha'] ?? 'ASC';
    $page = intval($_POST['page'] ?? 1);
    $per_page = intval($_POST['per_page'] ?? 5);

    $result = Project::FilterProjects(
        $buscar,
        $ente,
        $conexion,
        $tutor,
        $fecha,
        $año,
        $linea,
        $orden,
        $page,
        $per_page
    );

    // Verificar si hubo error en la obtención de datos
    if(isset($result['error'])) {
        throw new Exception($result['error']);
    }

    // Generar botones de acción
    $projectsWithActions = [];
    foreach ($result['projects'] as $project) {
        $detailUrl = isset($_SESSION['user']) 
            ? "detalle_proyecto.php?id={$project['id']}" 
            : "login.php?return_to=../detalle_proyecto.php?id={$project['id']}";
        
        $actions = '<div class="table-actions">';
        $actions .= '<a href="' . $detailUrl . '" class="btn action-btn"><i class="fas fa-eye"></i></a>';
        
        if (!empty($project['archivo'])) {
            $actions .= '<a href="' . htmlspecialchars($project['archivo']) . '" class="btn action-btn" download><i class="fas fa-download"></i></a>';
        }
        
        $actions .= '</div>';
        
        $project['acciones'] = $actions;
        $projectsWithActions[] = $project;
    }

    // Construir respuesta JSON
    $response = [
        'success' => true,
        'projects' => $projectsWithActions,
        'current_page' => $result['current_page'],
        'total_pages' => $result['total_pages'],
        'total_records' => $result['total_records']
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;

} catch(Exception $e) {
    // Respuesta de error
    $response = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'projects' => [],
        'current_page' => 1,
        'total_pages' => 1,
        'total_records' => 0
    ];
    
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode($response);
    exit;
}