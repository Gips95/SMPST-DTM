<?php
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... (código existente para actualizar el proyecto)
    $id = $_POST['id'];

    try {
        $conexion->beginTransaction();
       // Procesar eliminación de archivos
if (isset($_POST['eliminar_archivos'])) {
    foreach ($_POST['eliminar_archivos'] as $archivo_id) {
        // 1. Eliminar archivo físico
        $stmt = $conexion->prepare("SELECT ruta FROM archivos WHERE id = ?");
        $stmt->bind_param("i", $archivo_id);
        $stmt->execute();
        $ruta = $stmt->get_result()->fetch_column();
        
        if (file_exists($ruta)) {
            unlink($ruta);
        }

        // 2. Eliminar registro de la BD
        $stmt = $conexion->prepare("DELETE FROM archivos WHERE id = ?");
        $stmt->bind_param("i", $archivo_id);
        $stmt->execute();
    }
}

// Actualizar tipos de archivo
if (isset($_POST['tipo_archivo_existente'])) {
    foreach ($_POST['tipo_archivo_existente'] as $archivo_id => $nuevo_tipo) {

        $stmt = $conexion->prepare("UPDATE archivos SET tipo_archivo = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_tipo, $archivo_id);
        $stmt->execute();
    }
}
        // Procesar nuevos archivos
        if (!empty($_FILES['nuevos_archivos']['name'][0])) {
            $directorio = "uploads/proyecto_$id/";
            if (!file_exists($directorio)) {
                mkdir($directorio, 0755, true);
            }

            foreach ($_FILES['nuevos_archivos']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['nuevos_archivos']['error'][$key] === UPLOAD_ERR_OK) {
                    $nombre_original = basename($_FILES['nuevos_archivos']['name'][$key]);
                    $nuevo_nombre = uniqid() . '_' . $nombre_original;
                    $ruta_archivo = $directorio . $nuevo_nombre;
                    $tipo_archivo = $_POST['tipo_nuevos_archivos'][$key];

                    if (move_uploaded_file($tmp_name, $ruta_archivo)) {
                        $stmt = $conexion->prepare("INSERT INTO archivos (proyecto_id, tipo_archivo, nombre_archivo, ruta_archivo) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("isss", $id, $tipo_archivo, $nombre_original, $ruta_archivo);
                        $stmt->execute();
                    }
                }
            }
        }

        $conexion->commit();
        header("Location: editar_proyecto.php?id=$id&success=1");
        exit();
    } catch (Exception $e) {
        $conexion->rollBack();
        header("Location: editar_proyecto.php?id=$id&error=1");
        exit();
    }
}