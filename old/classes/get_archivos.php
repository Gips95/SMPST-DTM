<?php
include 'conn.php';

$proyecto_id = intval($_GET['proyecto_id']);
$sql = "SELECT id, nombre, ruta, tipo, size FROM archivos WHERE proyecto_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $proyecto_id);
$stmt->execute();
$result = $stmt->get_result();

$archivos = [];
while ($row = $result->fetch_assoc()) {
    $archivos[] = $row;
}

echo json_encode($archivos);

$stmt->close();
$conexion->close();
?>
