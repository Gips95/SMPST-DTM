<?php
session_start();
if (!isset($_SESSION["user"])) {
    header('HTTP/1.1 401 Unauthorized');
    exit(json_encode(['error' => 'Unauthorized']));
}

include '../db/conn.php';
include_once("../classes/Files.class.php");
include_once('classes/Projects.class.php');

function formatSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' bytes';
}

try {
    // Obtener datos del proyecto
    $proyecto_id = isset($_GET['id']) ? intval($_GET['id']) : null;
    if (!$proyecto_id) throw new Exception("ID de proyecto no especificado");
    
    $proyecto = Project::getProject($proyecto_id, $conexion);
    if (!$proyecto) throw new Exception("Proyecto no encontrado");
    
    // Obtener archivos
    $archivos = ProjectFile::getProjectFiles($proyecto_id, $conexion);
    
    // Preparar datos para la vista
    $documentos = array_filter($archivos, fn($a) => $a['tipo'] === 'documento');
    $referencias = array_filter($archivos, fn($a) => $a['tipo'] === 'referencia');
    
    // Cerrar conexión temprano
    $conexion->close();
    
    // Devolver datos estructurados
    return [
        'success' => true,
        'proyecto' => [
            'id' => $proyecto_id,
            'titulo' => $proyecto['titulo']
        ],
        'archivos' => [
            'documentos' => $documentos,
            'referencias' => $referencias
        ]
    ];
} catch (Exception $e) {
    http_response_code(500);
    return ['error' => $e->getMessage()];
}