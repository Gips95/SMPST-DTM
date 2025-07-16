<?php
session_start();
include '../db/conn.php';
include_once '../classes/Projects.class.php';
include_once '../classes/Files.class.php';
include_once('../classes/Logs.class.php');

if (!isset($_SESSION['user']) || $_SESSION['rol'] !== 'admin') {
   http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}
// Leer el cuerpo JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !is_numeric($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}
try {
    $id = intval($data['id']);

    $conexion->begin_transaction();

    // 1) Obtener todos los archivos del proyecto
    $archivos = ProjectFile::getProjectFiles($id, $conexion);

    // 2) Para cada archivo:
    foreach ($archivos as $archivo) {
        $archivoId = (int)$archivo['id'];
        // 2a) Eliminar registros en download_request_items
        $stmt = $conexion->prepare(
            "DELETE FROM download_request_items WHERE archivo_id = ?"
        );
        $stmt->bind_param("i", $archivoId);
        $stmt->execute();
        $stmt->close();

        // 2b) Eliminar el archivo físicamente y su registro en 'archivos'
        ProjectFile::DeleteProjectFile($archivoId, $archivo['ruta'], $conexion);
    }
   // 4) Borrar carpeta en disco si existe
   $projectinfo = Project::getProject($id, $conexion);
   $nombre_proyecto = $projectinfo['titulo'];
   $dir = __DIR__ . '/../uploads/' . rawurlencode($nombre_proyecto) . '/';
   if (is_dir($dir)) {
       ProjectFile::DeleteDir($dir);
   }
    // 3) Finalmente, desactivar/eliminar el proyecto
    
    Project::DeleteProject($id, $conexion);
    Log::CreateLog('delete', 'projectos', $id, $_SESSION['user'], $conexion, null, $projectinfo, null);
 

    $conexion->commit();
    $conexion->close();

    echo json_encode([
        'success' => true,
        'message' => 'Proyecto y archivos eliminados correctamente.'
    ]);
    exit;


} catch (Exception $e) {
    $conexion->rollback();
    $conexion->close();
    die("Error al eliminar el proyecto: " . $e->getMessage());
}
?>
