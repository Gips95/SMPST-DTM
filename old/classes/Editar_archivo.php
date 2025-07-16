<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}
include 'tools/conn.php';
include_once('classes/Files.class.php');
include_once('classes/Projects.class.php');

if (!isset($_GET['id'])) {
    die("ID de proyecto no especificado.");
}
// Obtener información del proyecto (para mostrar título, etc.)
try {
    $proyecto_id = intval($_GET['id']);
    $projecto = Project::getProject($proyecto_id, $conexion);
    $resultArchivos = ProjectFile::getProjectFiles($proyecto_id, $conexion);
    $conexion->close();

} catch (Exception $e) {
    echo 'Error: '.$e->getMessage();
}

/*
$sqlProyecto = "SELECT titulo FROM proyectos WHERE id = ?";
$stmtP = $conexion->prepare($sqlProyecto);
$stmtP->bind_param("i", $proyecto_id);
$stmtP->execute();
$resultProyecto = $stmtP->get_result();
$proyecto = $resultProyecto->fetch_assoc();
$stmtP->close();

// Obtener archivos asociados al proyecto
$sqlArchivos = "SELECT id, nombre, ruta, tipo, size FROM archivos WHERE proyecto_id = ?";
$stmtA = $conexion->prepare($sqlArchivos);
$stmtA->bind_param("i", $proyecto_id);
$stmtA->execute();
$resultArchivos = $stmtA->get_result();

$archivos = [];
while ($row = $resultArchivos->fetch_assoc()) {
    $archivos[] = $row;
}
$stmtA->close();
$conexion->close();
*/

// Función para formatear tamaños, solo se define si no existe
if (!function_exists('formatSize')) {
    function formatSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Archivos - <?php echo htmlspecialchars($proyecto['titulo']); ?></title>
    <link href="../styles/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/fontawesome">
    <style>
        /* Estilos basados en el formulario de registro */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 20px;
        }
        .container-main {
            width: 90%;
            max-width: 1200px;
            margin: auto;
        }
        .card-registro {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .card-header {
            background: #007bff;
            color: white;
            padding: 25px 40px;
            border-bottom: 3px solid #0056b3;
        }
        .card-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .card-body {
            padding: 40px;
        }
        /* Estilos para pestañas con Bootstrap 5 */
        .nav-tabs .nav-link {
            font-weight: 600;
            color: #007bff;
        }
        .nav-tabs .nav-link.active {
            background-color: #007bff;
            color: white;
            border-color: #0056b3 #0056b3 #fff;
        }
        /* Tabla de archivos */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        /* Botones de acción */
        .btn {
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: opacity 0.3s ease;
        }
        .btn-editar {
            background-color: #ffc107;
        }
        .btn-editar:hover {
            opacity: 0.8;
        }
        .btn-eliminar {
            background-color: #dc3545;
        }
        .btn-eliminar:hover {
            opacity: 0.8;
        }
        .btn-agregar {
            background-color: #007bff;
            margin-bottom: 20px;
        }
        .btn-agregar:hover {
            opacity: 0.8;
        }
    </style>
    <!-- Bootstrap 5 CSS (si no lo tienes incluido en bootstrap.css, usa el CDN) -->
  
</head>
<body>
    <div class="container-main">
        <div class="card-registro">
            <div class="card-header">
                <h2>Gestionar Archivos - <?php echo htmlspecialchars($proyecto['titulo']); ?></h2>
            </div>
            <div class="card-body">
                <!-- Botón para agregar nuevos archivos (redirige a formulario o modal) -->
                <a href="agregar_archivos.php?id=<?php echo $proyecto_id; ?>" class="btn btn-agregar">Agregar Archivos</a>
                
                <!-- Pestañas para Documentos y Referencias -->
                <ul class="nav nav-tabs" id="archivoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos" type="button" role="tab">Documentos</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="referencias-tab" data-bs-toggle="tab" data-bs-target="#referencias" type="button" role="tab">Referencias</button>
                    </li>
                </ul>
                <div class="tab-content" id="archivoTabsContent">
                    <!-- Pestaña Documentos -->
                    <div class="tab-pane fade show active" id="documentos" role="tabpanel">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tamaño</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $existenDocumentos = false;
                                foreach ($archivos as $archivo):
                                    if ($archivo['tipo'] == 'documento'):
                                        $existenDocumentos = true;
                                ?>
                                <tr id="archivo-<?php echo $archivo['id']; ?>">
                                    <td><?php echo htmlspecialchars($archivo['nombre']); ?></td>
                                    <td><?php echo formatSize($archivo['size']); ?></td>
                                    <td>
                                        <a href="editar_archivo.php?id=<?php echo $archivo['id']; ?>&proyecto_id=<?php echo $proyecto_id; ?>" class="btn btn-editar">Editar</a>
                                        <button class="btn btn-eliminar" onclick="eliminarArchivo(<?php echo $archivo['id']; ?>)">Eliminar</button>
                                    </td>
                                </tr>
                                <?php 
                                    endif;
                                endforeach;
                                if (!$existenDocumentos) {
                                    echo "<tr><td colspan='3'>No hay documentos registrados.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pestaña Referencias -->
                    <div class="tab-pane fade" id="referencias" role="tabpanel">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tamaño</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $existenReferencias = false;
                                foreach ($archivos as $archivo):
                                    if ($archivo['tipo'] == 'referencia'):
                                        $existenReferencias = true;
                                ?>
                                <tr id="archivo-<?php echo $archivo['id']; ?>">
                                    <td><?php echo htmlspecialchars($archivo['nombre']); ?></td>
                                    <td><?php echo formatSize($archivo['size']); ?></td>
                                    <td>
                                        <a href="editar_archivo.php?id=<?php echo $archivo['id']; ?>&proyecto_id=<?php echo $proyecto_id; ?>" class="btn btn-editar">Editar</a>
                                        <button class="btn btn-eliminar" onclick="eliminarArchivo(<?php echo $archivo['id']; ?>)">Eliminar</button>
                                    </td>
                                </tr>
                                <?php 
                                    endif;
                                endforeach;
                                if (!$existenReferencias) {
                                    echo "<tr><td colspan='3'>No hay referencias registradas.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- Fin card-body -->
        </div><!-- Fin card-registro -->
    </div><!-- Fin container-main -->

    <!-- jQuery para AJAX -->
    <script src="../js/jquery.js"></script>
    <!-- Bootstrap 5 Bundle (incluye Popper) -->
    <script src="../js/bootstrap.js"></script>
    <script>
        function eliminarArchivo(idArchivo) {
            if (!confirm("¿Estás seguro de eliminar este archivo?")) {
                return;
            }
            $.ajax({
                url: 'eliminar_archivo.php',
                method: 'POST',
                data: { id: idArchivo },
                success: function(response) {
                    try {
                        var res = JSON.parse(response);
                        if (res.success) {
                            // Eliminar la fila correspondiente
                            $("#archivo-" + idArchivo).remove();
                        } else {
                            alert("Error: " + res.message);
                        }
                    } catch (e) {
                        console.error("Respuesta inesperada: " + response);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert("Error al eliminar el archivo: " + textStatus);
                }
            });
        }
    </script>
</body>
</html>
