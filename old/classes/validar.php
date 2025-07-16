<?php
session_start();
include '../bd/conn.php'; // Incluir la conexión a la BD

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST["usuario"];
    $password = $_POST["password"];  

    // Preparar la consulta SQL para evitar inyecciones
    $stmt = $conexion->prepare("SELECT pass FROM usuarios WHERE user = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($password_hash);
        $stmt->fetch();

        // Verificar la contraseña
        if (password_verify($password, $password_hash)) {
            $_SESSION["user"] = $usuario;
            header("Location: ../dashboard.php");
            exit();
        } else {
            echo "Usuario o contraseña incorrectos";
        }
    } else {
        echo "Usuario no encontrado";
    }

    $stmt->close();
}
$conexion->close();
?>
