<?php
session_start();
include 'tools/conn.php';
include_once('classes/Projects.class.php');
include_once('classes/Files.class.php');

if (isset($_GET['id'])) {
    try{
        $proyecto_id = intval($_GET['id']); // Convertir a entero para seguridad
        $proyecto = Project::getProject($proyecto_id, $conexion);
        $archivos = ProjectFile::getProjectFiles($proyecto_id, $conexion);

        $documentos = [];
        $referencias = [];

        foreach($archivos as $archivo){
            if ($archivo['tipo'] == "documento") {
                $documentos[] = $archivo;
            } elseif ($archivo['tipo'] == "referencia") {
                $referencias[] = $archivo;
            }
        }
    }catch(Exception $e){
        echo $e->getMessage();
        exit();
    }
}

/*
// Consultar datos del proyecto
    $sqlProyecto = "SELECT * FROM proyectos WHERE id = ?";
    $stmtProyecto = $conexion->prepare($sqlProyecto);
    $stmtProyecto->bind_param("i", $proyecto_id);
    $stmtProyecto->execute();
    $resultado = $stmtProyecto->get_result();
    $proyecto = $resultado->fetch_assoc();

    if (!$proyecto) {
        echo "Proyecto no encontrado.";
        exit();
    }

    // Consultar archivos asociados y clasificarlos
    $sqlArchivos = "SELECT * FROM archivos WHERE proyecto_id = ?";
    $stmtArchivos = $conexion->prepare($sqlArchivos);
    $stmtArchivos->bind_param("i", $proyecto_id);
    $stmtArchivos->execute();
    $resultadoArchivos = $stmtArchivos->get_result();

    $documentos = [];
    $referencias = [];

    while ($archivo = $resultadoArchivos->fetch_assoc()) {
        if ($archivo['tipo'] == "documento") {
            $documentos[] = $archivo;
        } elseif ($archivo['tipo'] == "referencia") {
            $referencias[] = $archivo;
        }
    }
} else {
    echo "ID de proyecto no válido.";
    exit();
}
*/
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Proyecto</title>
    <link href="../styles/bootstrap.css" rel="stylesheet">
   <link rel="stylesheet" href="../styles/fontawesome/css/all.css">
    
    <!-- Importar la fuente Poppins de Google Fonts -->
    <link href="../styles/googlefonts.css" rel="stylesheet">
     <!-- Dropzone.js -->
    <link rel="stylesheet" href="../styles/dropzone.css">
    <style>
        /* Aplicar la fuente Poppins a todo el cuerpo */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e9ecef;
        }
        .container {
            margin-top: 50px;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h2, h3 {
            color: #007bff; /* Azul más claro */
            font-weight: 600; /* Poppins semibold */
        }
        .table {
            margin-top: 20px;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border: 2px solid #007bff; /* Borde azul alrededor de la tabla */
        }
        .table th, .table td {
            padding: 15px;
            border-top: 1px solid #dee2e6;
        }
        .table th {
            background-color: transparent; /* Fondo transparente */
            color: #007bff; /* Texto azul */
            font-weight: 500; /* Poppins medium */
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .archivo-lista {
            list-style: none;
            padding: 0;
        }
        .archivo-lista li {
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        .archivo-lista li:hover {
            background-color: #e2e6ea;
        }
        .archivo-lista a {
            color: #007bff; /* Azul más claro */
            text-decoration: none;
            font-weight: 500; /* Poppins medium */
        }
        .archivo-lista a:hover {
            text-decoration: underline;
        }
        .btn-primary {
            background-color: #007bff; /* Azul más claro */
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
            font-weight: 500; /* Poppins medium */
        }
        .btn-primary:hover {
            background-color: #0056b3; /* Azul un poco más oscuro al hacer hover */
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">Detalles del Proyecto</h2>
    
    <table class="table">
        <tbody>
            <tr>
                <th>Título</th>
                <td><?php echo htmlspecialchars($proyecto["titulo"]); ?></td>
            </tr>
            <tr>
                <th>Descripción</th>
                <td><?php echo nl2br(htmlspecialchars($proyecto["descripcion"])); ?></td>
            </tr>
            <tr>
                <th>Autores</th>
                <td><?php echo htmlspecialchars($proyecto["autores"]); ?></td>
            </tr>
            <tr>
                <th>Tipo de Proyecto</th>
                <td><?php echo htmlspecialchars($proyecto["tipo_proyecto"]); ?></td>
            </tr>
            <tr>
                <th>Línea de Investigación</th>
                <td><?php echo htmlspecialchars($proyecto["linea_investigacion"]); ?></td>
            </tr>
            <tr>
                <th>Estatus</th>
                <td><?php echo htmlspecialchars($proyecto["estatus"]); ?></td>
            </tr>
            <tr>
                <th>Ente/Institución</th>
                <td><?php echo htmlspecialchars($proyecto["ente"]); ?></td>
            </tr>
        </tbody>
    </table>


                <!-- Listado de Archivos con Pestañas -->
                <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos" type="button" role="tab">
                            Documentos <span class="badge bg-primary"><?php echo count($documentos); ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="referencias-tab" data-bs-toggle="tab" data-bs-target="#referencias" type="button" role="tab">
                            Referencias <span class="badge bg-success"><?php echo count($referencias); ?></span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Documentos -->
                    <div class="tab-pane fade show active" id="documentos" role="tabpanel">
                        <?php if (!empty($documentos)): ?>
                            <div class="list-group">
                                <?php foreach ($documentos as $archivo): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                            <?php echo htmlspecialchars($archivo['nombre']); ?>
                                            <small class="text-muted ms-2"><?php echo formatSizeUnits($archivo['size']); ?></small>
                                        </div>
                                        <div>
                                            <a href="<?php echo htmlspecialchars($archivo['ruta']); ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               download 
                                               title="Descargar">
                                                <img src='../styles/icons/download.svg'>
                                            </a>
                                          
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No hay documentos principales</div>
                        <?php endif; ?>
                    </div>

                    <!-- Referencias -->
                    <div class="tab-pane fade" id="referencias" role="tabpanel">
                        <?php if (!empty($referencias)): ?>
                            <div class="list-group">
                                <?php foreach ($referencias as $archivo): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-file-alt text-success me-2"></i>
                                            <?php echo htmlspecialchars($archivo['nombre']); ?>
                                            <small class="text-muted ms-2"><?php echo formatSizeUnits($archivo['size']); ?></small>
                                        </div>
                                        <div>
                                            <a href="<?php echo htmlspecialchars($archivo['ruta']); ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               download
                                               title="Descargar">
                                                <img src='../styles/icons/download.svg'>
                                            </a>
                                            
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No hay referencias adjuntas</div>
                        <?php endif; ?>
                    </div>
                </div>
    <br>
    <a href="../dashboard.php" class="btn btn-primary">Volver a la Lista</a>
</div>

<script src="../js/jquery.js"></script>
<script src="../js/popper.js"></script>
<script src="../js/bootstrap.js"></script>

    <script src="../js/dropzone.js"></script>
    <script>
        // Configurar Dropzone
        Dropzone.autoDiscover = false;
        const dz = new Dropzone('.dropzone', {
            paramName: "file",
            maxFilesize: 25, // MB
            acceptedFiles: ".pdf,.doc,.docx,.xls,.xlsx",
            dictDefaultMessage: "Arrastra archivos aquí",
            init: function() {
                this.on("success", function(file, response) {
                    setTimeout(() => location.reload(), 1500);
                });
            }
        });

      
    </script>
<?php
function formatSizeUnits($bytes) {
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

$stmtProyecto->close();
$stmtArchivos->close();
$conexion->close();
?>

</body>
</html>