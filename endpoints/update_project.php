<?php
session_start();
include('../db/conn.php');
include_once('../classes/Projects.class.php');
include_once('../classes/Logs.class.php');

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../panel_admin.php');
    exit();
}

try {
    // Iniciar transacción
    $conexion->begin_transaction();

    // Sanitizar y validar
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $autores = isset($_POST['autores']) ? trim($_POST['autores']) : '';
    $linea = isset($_POST['linea_investigacion']) ? trim($_POST['linea_investigacion']) : '';
    $ente = isset($_POST['ente']) ? trim($_POST['ente']) : '';
    $tutor = isset($_POST['tutor']) ? trim($_POST['tutor']) : '';
    $fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';

    if ($id <= 0 || !$titulo || !$descripcion || !$autores || !$linea || !$ente || !$tutor || !$fecha) {
        throw new Exception('Todos los campos son obligatorios.');
    }

    // Obtener estado anterior
    $old = Project::getProject($id, $conexion);

    // Actualizar
    $project = new Project(
         $titulo,
         $descripcion,
         $autores,
         $tutor,
         $linea,
         $ente,
         $fecha,
        
    );
    $project->UpdateProject($id, $conexion);

    // Obtener estado nuevo
    $new = Project::getProject($id, $conexion);

    // Registrar log
    Log::CreateLog(
        'update',
        'proyectos',
        $id,
        $_SESSION['user'],
        $conexion,
        null,
        $old,
        $new
    );

    // Commit
    $conexion->commit();
    $conexion->close();

    // Redirigir con mensaje
    header('Location: ../panel_admin.php?msg=' . urlencode('Proyecto actualizado con éxito'));
    exit;

}  catch (Exception $e) {
    // Rollback y error
    // Rollback en caso de error
echo 'Error: ' . $e->getMessage();
$conexion->rollback();
$conexion->close();
    // Podrías redirigir con error en querystring
    header('Location: ../panel_admin.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>
