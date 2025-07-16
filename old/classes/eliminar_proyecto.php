<?php
include 'conn.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $conexion->begin_transaction();

        // 1. Obtener nombre del proyecto
        $sql_nombre = "SELECT titulo FROM proyectos WHERE id = ?";
        $stmt_nombre = $conexion->prepare($sql_nombre);
        $stmt_nombre->bind_param("i", $id);
        $stmt_nombre->execute();
        $proyecto = $stmt_nombre->get_result()->fetch_assoc();

        if (!$proyecto) {
            throw new Exception("Proyecto no encontrado");
        }

        $nombre_proyecto = $proyecto['titulo'];
        $directorio_proyecto = "uploads/" . rawurlencode($nombre_proyecto) . "/"; // Codificar nombre para coincidir con la ruta

        // 2. Obtener archivos asociados
        $sql_archivos = "SELECT ruta FROM archivos WHERE proyecto_id = ?";
        $stmt_archivos = $conexion->prepare($sql_archivos);
        $stmt_archivos->bind_param("i", $id);
        $stmt_archivos->execute();
        $resultado = $stmt_archivos->get_result();

        // 3. Eliminar archivos físicos
        while ($archivo = $resultado->fetch_assoc()) {
            $ruta_archivo = $archivo['ruta'];
            if (file_exists($ruta_archivo)) {
                if (!unlink($ruta_archivo)) {
                    throw new Exception("Error eliminando archivo: $ruta_archivo");
                }
            }
        }

        // 4. Eliminar registros de la base de datos
        $sql_delete_archivos = "DELETE FROM archivos WHERE proyecto_id = ?";
        $stmt_delete_archivos = $conexion->prepare($sql_delete_archivos);
        $stmt_delete_archivos->bind_param("i", $id);
        $stmt_delete_archivos->execute();

        // 5. Eliminar el proyecto
        $sql_delete_proyecto = "DELETE FROM proyectos WHERE id = ?";
        $stmt_delete_proyecto = $conexion->prepare($sql_delete_proyecto);
        $stmt_delete_proyecto->bind_param("i", $id);
        $stmt_delete_proyecto->execute();

        // 6. Eliminar directorio (si existe y está vacío)
        if (file_exists($directorio_proyecto) && is_dir($directorio_proyecto)) {
            if (!rmdir($directorio_proyecto)) {
                // Si el directorio no está vacío, eliminar recursivamente
                deleteDirectory($directorio_proyecto);
            }
        }

        $conexion->commit();
        header("Location: ../panel_admin.php?msg=Proyecto+y+archivos+eliminados");
        exit();

    } catch (Exception $e) {
        $conexion->rollback();
        echo "Error: " . $e->getMessage();
    } finally {
        if (isset($stmt_nombre)) $stmt_nombre->close();
        if (isset($stmt_archivos)) $stmt_archivos->close();
        if (isset($stmt_delete_archivos)) $stmt_delete_archivos->close();
        if (isset($stmt_delete_proyecto)) $stmt_delete_proyecto->close();
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