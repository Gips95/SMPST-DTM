<?php
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitización de entrada
    $id = intval($_POST["id"]);
    $titulo = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $autores = trim($_POST["autores"]);
    $tipo = trim($_POST["tipo_proyecto"]);
    $linea = trim($_POST["linea_investigacion"]);
    $estatus = trim($_POST["estatus"]);
    $ente = trim($_POST["ente"]);

    // Verificar que todos los campos requeridos están presentes
    if (empty($titulo) || empty($descripcion) || empty($autores) || empty($tipo) || empty($linea) || empty($estatus) || empty($ente)) {
        die("Todos los campos son obligatorios.");
    }

    // Consulta de actualización
    $sql = "UPDATE proyectos 
            SET titulo=?, descripcion=?, autores=?, tipo_proyecto=?, linea_investigacion=?, estatus=?, ente=? 
            WHERE id=?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssssi", $titulo, $descripcion, $autores, $tipo, $linea, $estatus, $ente, $id);

    if ($stmt->execute()) {
        header("Location: ../panel_admin.php?msg=Proyecto actualizado con éxito");
        exit();
    } else {
        echo "Error al actualizar el proyecto: " . $stmt->error;
    }

    $stmt->close();
}

$conexion->close();
?>
