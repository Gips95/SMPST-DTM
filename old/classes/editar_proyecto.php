<?php
include 'tools/conn.php';
include_once('classes/Projects.class.php');
session_start();
if (!isset($_GET['id'])) {
    die("ID de proyecto no especificado.");
}
$id = intval($_GET['id']); // Convertir a entero para evitar inyecciones SQL

// Obtener datos del proyecto
try {
    $projecto = Project::getProject($id, $conexion);
    $conexion->close();
} catch (Exception $e) {
    die($e->getMessage());
}
/*
$sql = "SELECT * FROM proyectos WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$proyecto = $result->fetch_assoc();

if (!$proyecto) {
    die("Proyecto no encontrado.");
}
*/
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proyecto</title>
    <!-- Puedes ajustar las rutas de los CSS según tu estructura de carpetas -->
    <link href="../styles/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/dropzone.css">
    <link rel="stylesheet" href="../styles/fontawesome">
    <style>
        /* Estilos combinados e inspirados en el formulario de registro */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #e9ecef;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .container-main {
            flex: 1;
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 0;
        }
        .card-registro {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
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
        .form-group {
            margin-bottom: 25px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 16px;
        }
        .form-control, .form-select {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #007bff;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: rgba(0, 123, 255, 0.05);
        }
        .form-control:focus, .form-select:focus {
            border-color: #0056b3;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.2);
        }
        textarea.form-control {
            height: 120px;
            resize: vertical;
        }
        .btn-primary {
            background-color: #007bff !important;
            border: none;
            padding: 15px 30px !important;
            border-radius: 25px !important;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3 !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }
        .btn-secondary {
            background-color: #6c757d !important;
            border-radius: 25px !important;
        }
        .d-grid {
            display: grid;
            gap: 20px;
        }
    </style>
</head>
<body>
    <div class="container-main">
        <div class="card-registro">
            <div class="card-header">
                <h2>Editar Proyecto</h2>
            </div>
            <div class="card-body">
                <form action="actualizar_proyecto.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $proyecto['id']; ?>">
                    <div class="row g-4">
                        <!-- Datos Básicos -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="titulo">Título del Proyecto</label>
                                <input type="text" name="titulo" id="titulo" class="form-control" value="<?php echo htmlspecialchars($proyecto['titulo']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="autores">Autor(es)</label>
                                <input type="text" name="autores" id="autores" class="form-control" value="<?php echo htmlspecialchars($proyecto['autores']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="ente">Ente/Institución</label>
                                <input type="text" name="ente" id="ente" class="form-control" value="<?php echo htmlspecialchars($proyecto['ente']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="tipo_proyecto">Tipo de Proyecto</label>
                                <select name="tipo_proyecto" id="tipo_proyecto" class="form-select" required>
                                    <option value="proyecto" <?php echo ($proyecto['tipo_proyecto'] == 'proyecto') ? 'selected' : ''; ?>>Proyecto</option>
                                    <option value="proyecto_y_pasantia" <?php echo ($proyecto['tipo_proyecto'] == 'proyecto_y_pasantia') ? 'selected' : ''; ?>>Proyecto y Pasantía</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="linea_investigacion">Línea de Investigación</label>
                                <input type="text" name="linea_investigacion" id="linea_investigacion" class="form-control" value="<?php echo htmlspecialchars($proyecto['linea_investigacion']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="estatus">Estatus</label>
                                <select name="estatus" id="estatus" class="form-select" required>
                                    <option value="pendiente" <?php echo ($proyecto['estatus'] == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="aprobado" <?php echo ($proyecto['estatus'] == 'aprobado') ? 'selected' : ''; ?>>Aprobado</option>
                                    <option value="rechazado" <?php echo ($proyecto['estatus'] == 'rechazado') ? 'selected' : ''; ?>>Rechazado</option>
                                </select>
                            </div>
                        </div>
                        <!-- Descripción -->
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label" for="descripcion">Descripción</label>
                                <textarea name="descripcion" id="descripcion" class="form-control" required><?php echo htmlspecialchars($proyecto['descripcion']); ?></textarea>
                            </div>
                        </div>
                        <!-- Botones de Acción -->
                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Guardar Cambios</button>
                            <a href="../panel_admin.php" class="btn btn-secondary btn-lg">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../js/bootstrap.js"></script>
</body>
</html>
<?php
$stmt->close();
$conexion->close();
?>
