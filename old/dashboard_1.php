<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}
include './db/conn.php'; // Conexión a la base de datos

// Consulta para obtener los proyectos
$sql = "SELECT id, titulo, autores, tipo_proyecto, linea_investigacion FROM proyectos";
$result = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Lista de Proyectos</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
            margin: 0;
            overflow: hidden; /* Evita el scroll en el fondo */
        }

        /* Cuadro fijo */
        .container {
            width: 60%;
            height: 70vh; /* Alto del cuadro */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        h2 {
            color: #333;
        }

        /* Área con scroll */
        .table-container {
            flex-grow: 1;
            overflow-y: auto;
            border-top: 2px solid #007bff;
            border-bottom: 2px solid #007bff;
            padding: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
            position: sticky;
            top: 0;
        }

        .btn {
            background-color: #28a745;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }

        .btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<h2>Bienvenido, <?php echo $_SESSION["user"]; ?>!</h2>
<a href="upload.php">Subir</a>
<a href="Registrar_Proyecto.php">Registrar Proyecto</a>
<a href="classes/logout.php">Cerrar sesión</a>

<div class="container">
    <h2>Lista de Proyectos</h2>
    
    <div class="table-container"> <!-- Área desplazable -->
        <table>
            <tr>
                <th>Título</th>
                <th>Autores</th>
                <th>Tipo</th>
                <th>Línea de Investigación</th>
                <th>Acción</th>
            </tr>

            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["titulo"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["autores"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["tipo_proyecto"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["linea_investigacion"]) . "</td>";
                    echo "<td><a href='classes/detalle_proyecto.php?id=" . $row["id"] . "' class='btn'>Ver Detalles</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No hay proyectos registrados.</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>

<?php $conexion->close(); ?>
