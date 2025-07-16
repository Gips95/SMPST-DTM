<?php
include '../db/conn.php'; 
session_start();
$rid = intval($_GET['req']);
$userId = $_SESSION['user_id'] ?? 0;

// Validar permisos y estado aprobado
$stmtCheck = $conexion->prepare("
    SELECT status 
    FROM download_requests 
    WHERE id = ? AND user_id = ? AND status = 'aprobado'
");
$stmtCheck->bind_param('ii', $rid, $userId);
$stmtCheck->execute();
if ($stmtCheck->get_result()->num_rows === 0) {
    die("Acceso denegado.");
}

// Crear ZIP
$zip = new ZipArchive();
$tmpFile = tempnam(sys_get_temp_dir(), 'zip');
if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Error al crear el archivo ZIP");
}

$stmt = $conexion->prepare("
    SELECT i.archivo_id,
           a.ruta,
           a.nombre,
           a.tipo       -- asumo que aquí indicas 'documento' o 'referencia'
    FROM download_request_items i
    JOIN archivos a ON a.id = i.archivo_id
    WHERE i.request_id = ?
");
$stmt->bind_param('i', $rid);
$stmt->execute();
$result = $stmt->get_result();

// Array para contar ocurrencias de cada nombre en el ZIP
$contadorNombres = [];

while ($f = $result->fetch_assoc()) {
    $ruta         = $f['ruta'];
    $nombreReal   = $f['nombre'];
    $tipo         = $f['tipo'];  // 'referencia' o 'documento'

    // 1) Carpeta interna
    $carpetaZip = ($tipo === 'referencia')
                ? 'referencias/'
                : 'documentos/';

    // 2) Normalizar nombre (sin extensiones duplicadas raras)
    $ext         = pathinfo($nombreReal, PATHINFO_EXTENSION);
    $base        = pathinfo($nombreReal, PATHINFO_FILENAME);

    // 3) Generar nombre único con contador
    if (!isset($contadorNombres[$base])) {
        $contadorNombres[$base] = 0;
    }
    $contadorNombres[$base]++;
    $num = $contadorNombres[$base] > 1
         ? '_(' . $contadorNombres[$base] . ')'
         : '';

    $nombreZip = $carpetaZip . $base . $num . '.' . $ext;

    // 4) Añadir al ZIP si existe
    if (file_exists($ruta)) {
        if (!$zip->addFile($ruta, $nombreZip)) {
            error_log("No se pudo añadir al ZIP: $ruta como $nombreZip");
        }
    } else {
        error_log("Ruta no encontrada: $ruta");
    }
}

$zip->close();

// Enviar ZIP
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="archivos_solicitud_' . $rid . '.zip"');
header('Content-Length: ' . filesize($tmpFile));
readfile($tmpFile);
unlink($tmpFile);
exit;