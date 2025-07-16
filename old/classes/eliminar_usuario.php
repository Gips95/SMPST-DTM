<?php
include 'conn.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $conexion->begin_transaction();

        // 1. Obtener datos del usuario
        $sql_usuario = "SELECT id,user FROM usuarios WHERE id = ?";
        $stmt_usuario = $conexion->prepare($sql_usuario);
        $stmt_usuario->bind_param("i", $id);
        $stmt_usuario->execute();
        $usuario = $stmt_usuario->get_result()->fetch_assoc();

        if (!$usuario) {
            throw new Exception("Usuario no encontrado");
        }

        $nombre_usuario = $usuario['user'];


        // 4. Eliminar el usuario de la base de datos
        $sql_delete_usuario = "DELETE FROM usuarios WHERE id = ?";
        $stmt_delete_usuario = $conexion->prepare($sql_delete_usuario);
        $stmt_delete_usuario->bind_param("i", $id);
        $stmt_delete_usuario->execute();

        $conexion->commit();
        header("Location: ../panel_admin.php?msg=Usuario+y+datos+relacionados+eliminados");
        exit();

    } catch (Exception $e) {
        $conexion->rollback();
        echo "Error: " . $e->getMessage();
    } finally {
        if (isset($stmt_usuario)) $stmt_usuario->close();
        if (isset($stmt_delete_proyectos)) $stmt_delete_proyectos->close();
        if (isset($stmt_delete_usuario)) $stmt_delete_usuario->close();
        $conexion->close();
    }
} else {
    echo "ID no especificado.";
}

// Función para eliminar directorios recursivamente
function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    
    $files = array_diff(scandir($dir), ['.','..']);
    foreach ($files as $file) {
        $path = "$dir/$file";
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    
    return rmdir($dir);
}
?>