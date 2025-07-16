<?php
include 'conn.php';

// Recoger y limpiar parámetros
$buscar = isset($_POST['buscar']) ? trim($_POST['buscar']) : '';
$tipo = isset($_POST['tipo_proyecto']) ? trim($_POST['tipo_proyecto']) : '';
$estatus = isset($_POST['estatus']) ? trim($_POST['estatus']) : '';
$ente = isset($_POST['ente']) ? trim($_POST['ente']) : '';

// Construir la consulta de manera dinámica
$query = "SELECT * FROM proyectos WHERE 1";
$params = array();
$types = "";

// Búsqueda general en múltiples campos
if (!empty($buscar)) {
    $query .= " AND (titulo LIKE ? OR descripcion LIKE ? OR autores LIKE ? OR linea_investigacion LIKE ?)";
    $searchTerm = '%' . $buscar . '%';
    $params = array_merge($params, array($searchTerm, $searchTerm, $searchTerm, $searchTerm));
    $types .= "ssss";
}

// Filtrar por tipo de proyecto
if (!empty($tipo)) {
    $query .= " AND tipo_proyecto = ?";
    $params[] = $tipo;
    $types .= "s";
}

// Filtrar por estatus
if (!empty($estatus)) {
    $query .= " AND estatus = ?";
    $params[] = $estatus;
    $types .= "s";
}

// Filtrar por ente/institución
if (!empty($ente)) {
    $query .= " AND ente = ?";
    $params[] = $ente;
    $types .= "s";
}

// Ordenar los resultados (por ejemplo, alfabéticamente por título)
$query .= " ORDER BY titulo ASC";

$stmt = $conexion->prepare($query);
if ($stmt === false) {
    die("Error en la preparación: " . $conexion->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$output = "";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $output .= "<tr>";
        $output .= "<td>" . htmlspecialchars($row['titulo']) . "</td>";
        $output .= "<td>" . htmlspecialchars($row['autores']) . "</td>";
        $output .= "<td>" . htmlspecialchars($row['tipo_proyecto']) . "</td>";
        $output .= "<td>" . htmlspecialchars($row['linea_investigacion']) . "</td>";
        $output .= "<td>" . htmlspecialchars($row['estatus']) . "</td>";
        $output .= "<td>" . htmlspecialchars($row['ente']) . "</td>";
        $output .= "<td>";
        $output .= "<a class='btn btn-ver' href='classes/detalle_proyecto.php?id=" . $row['id'] . "'>Ver</a> ";
        
        $output .= "</td>";
        $output .= "</tr>";
    }
} else {
    $output .= "<tr><td colspan='7'>No se encontraron proyectos.</td></tr>";
}
echo $output;

$stmt->close();
$conexion->close();
?>
