<?php
session_start();
include '../bd/conn.php';

// Configurar cabeceras para JSON
header('Content-Type: application/json');

// Respuesta de error genérica
$response = ['success' => false, 'message' => 'Error desconocido'];

try {
    // Validar datos requeridos
    $requiredFields = ['titulo', 'descripcion', 'autores', 'tipo_proyecto', 'linea_investigacion', 'estatus', 'ente'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }

    // Sanitizar datos
    $titulo = htmlspecialchars(trim($_POST['titulo']));
    $descripcion = htmlspecialchars(trim($_POST['descripcion']));
    $autores = htmlspecialchars(trim($_POST['autores']));
    //$tipo_proyecto = htmlspecialchars(trim($_POST['tipo_proyecto']));
    $linea_investigacion = htmlspecialchars(trim($_POST['linea_investigacion']));
    //$estatus = htmlspecialchars(trim($_POST['estatus']));
    $ente = htmlspecialchars(trim($_POST['ente']));

    // Preparar carpeta para guardar archivos
    $nombreCarpeta = preg_replace('/[^A-Za-z0-9_-]/', '_', $titulo);
    $uploadDir = "../uploads/" . $nombreCarpeta . "/";
    
    // Crear directorio principal
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    // Directorio para referencias (si es necesario)
    $rutaReferencias = $uploadDir . "referencias/";
    if (!file_exists($rutaReferencias)) {
        mkdir($rutaReferencias, 0777, true);
    }

    // Insertar proyecto
    $sqlProyecto = "INSERT INTO proyectos (titulo, descripcion, autores, tipo_proyecto, linea_investigacion, estatus, ente) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sqlProyecto);
    if (!$stmt) {
        throw new Exception("Error preparando la consulta del proyecto: " . $conexion->error);
    }
    $stmt->bind_param("sssssss", $titulo, $descripcion, $autores, $tipo_proyecto, $linea_investigacion, $estatus, $ente);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al insertar el proyecto: " . $stmt->error);
    }
    // Obtener el ID del proyecto recién insertado
    $proyecto_id = $conexion->insert_id;

    // Función para procesar archivos (documentos o referencias)
    function CreateProjectFile($campo, $rutaDestino, $proyecto_id, $conexion) {
        if (!empty($_FILES[$campo]['name'][0])) {
            foreach ($_FILES[$campo]['tmp_name'] as $key => $tmp_name) {
                $nombreArchivoOriginal = basename($_FILES[$campo]['name'][$key]);
                // Generar un nombre único para evitar sobrescrituras
                $nombreArchivo = uniqid() . '_' . $nombreArchivoOriginal;
                $rutaFinal = $rutaDestino . $nombreArchivo;
                $fileSize = $_FILES[$campo]['size'][$key];

                if (move_uploaded_file($tmp_name, $rutaFinal)) {
                    $tipoArchivo = ($campo == "documentos") ? "documento" : "referencia";
                    $sqlArchivo = "INSERT INTO archivos (proyecto_id, nombre, ruta, tipo, size) VALUES (?, ?, ?, ?, ?)";
                    $stmtArchivo = $conexion->prepare($sqlArchivo);
                    if (!$stmtArchivo) {
                        throw new Exception("Error preparando la consulta de archivos: " . $conexion->error);
                    }
                    $stmtArchivo->bind_param("isssi", $proyecto_id, $nombreArchivoOriginal, $rutaFinal, $tipoArchivo, $fileSize);
                    if (!$stmtArchivo->execute()) {
                        throw new Exception("Error al insertar archivo: " . $stmtArchivo->error);
                    }
                    $stmtArchivo->close();
                } else {
                    throw new Exception("Error al mover el archivo: " . $nombreArchivoOriginal);
                }
            }
        }
    }

    // Procesar archivos para documentos y referencias
    procesarArchivos('documentos', $uploadDir, $proyecto_id, $conexion);
    procesarArchivos('referencias', $rutaReferencias, $proyecto_id, $conexion);

    $response = ['success' => true, 'message' => 'Proyecto creado exitosamente'];
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Cerrar conexiones
if (isset($stmt)) {
    $stmt->close();
}
$conexion->close();
?>
