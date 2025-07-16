<?php
include 'conn.php';

$buscar = isset($_POST["buscar"]) ? "%" . $_POST["buscar"] . "%" : "%%";

$sql = "SELECT id, titulo, autores, tipo_proyecto, linea_investigacion 
        FROM proyectos 
        WHERE titulo LIKE ? 
        OR autores LIKE ?
        OR tipo_proyecto LIKE ? 
        OR linea_investigacion LIKE ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssss", $buscar, $buscar, $buscar, $buscar);
$stmt->execute();
$result = $stmt->get_result();

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
    echo "<tr><td colspan='5'>No hay proyectos encontrados.</td></tr>";
}

$stmt->close();
$conexion->close();
