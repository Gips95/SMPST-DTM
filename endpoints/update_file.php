<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../db/conn.php';
include_once("../classes/Files.class.php");
include_once('../classes/Projects.class.php');
include_once('../classes/Logs.class.php');

header('Content-Type: application/json');
ob_start();

$response = ['success' => false, 'message' => 'Error desconocido'];

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception('Usuario no autenticado');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido. Solo POST');
    }

    if (!isset($_POST['proyecto_id']) || !isset($_POST['fileType']) || !isset($_FILES['fileInput'])) {
        throw new Exception('Datos incompletos');
    }

    $proyecto_id = (int)$_POST['proyecto_id'];
    $fileType = $_POST['fileType'];
    $file = $_FILES['fileInput'];

    if (!in_array($fileType, ['documento', 'referencia'])) {
        throw new Exception('Tipo de archivo inválido');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        // Manejo de errores de subida
        // [Mantén tu código existente aquí]
    }

    global $conexion;
    $proyecto = Project::getProject($proyecto_id, $conexion);
    if (!$proyecto) {
        throw new Exception('Proyecto no encontrado');
    }

    // Crear nombre de carpeta igual que en Project::CreateProject()
    $nombreCarpeta = preg_replace('/[^A-Za-z0-9_-]/', '_', $proyecto['titulo']);
    $baseDir = "../uploads/$nombreCarpeta/";

    // Crear directorios como en Project::CreateProject()
    if (!file_exists($baseDir)) {
        mkdir($baseDir, 0777, true);
    }
    
    // Directorio para referencias (igual que en Project)
    $rutaReferencias = $baseDir . "referencias/";
    if ($fileType === 'referencia' && !file_exists($rutaReferencias)) {
        mkdir($rutaReferencias, 0777, true);
    }

    // Determinar directorio final según tipo
    $targetDir = ($fileType === 'referencia') ? $rutaReferencias : $baseDir;

    $nombreOriginal = basename($file['name']);
    
    // 1. NO generar nombre único - usar lógica consistente con CreateProject
    // $nombreUnico = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $nombreOriginal);
    
    // 2. Usar el nombre original como en CreateProject
    $nombreArchivo = $nombreOriginal;
    
    // Ruta completa para almacenamiento físico
    $rutaFisica = $targetDir . $nombreArchivo;
    
    // 3. NO mover el archivo aquí - dejar que CreateProjectFile lo haga
    // if (!move_uploaded_file($file['tmp_name'], $rutaFisica)) {
    //     throw new Exception('Error al mover el archivo');
    // }

    // Crear instancia de ProjectFile con parámetros consistentes
    $projectFile = new ProjectFile(
        $nombreArchivo,       // Nombre original
        $file['size'],        // Tamaño
        $fileType,            // Tipo (documento/referencia)
        $targetDir            // Directorio base (sin nombre archivo)
    );

    // 4. Usar CreateProjectFile que moverá el archivo
    $archivo_id = $projectFile->CreateProjectFile(
        $proyecto_id, 
        $file['tmp_name'],   // Ruta temporal del archivo
        $conexion
    );
    
    if ($archivo_id) {
        try {
            Log::CreateLog('upload', 'archivos', $archivo_id, $_SESSION['user'], $conexion);
        } catch (Exception $e) {
            error_log("Error en registro de log: " . $e->getMessage());
        }
        
        $response = [
            'success' => true,
            'message' => 'Archivo subido exitosamente',
            'file' => [
                'id' => $archivo_id,
                'nombre' => $nombreOriginal,
                'size' => $file['size'],
                'tipo' => $fileType,
                'ruta' => str_replace('../', '', $rutaFisica)
            ]
        ];
    } else {
        throw new Exception('Error al guardar la información del archivo');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    if (isset($conexion) && $conexion instanceof mysqli) {
        $conexion->close();
    }
    ob_clean(); 
    echo json_encode($response);
    exit();
}