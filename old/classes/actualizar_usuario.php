
<?php
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $user = $_POST['user'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    $fecha_registro = $_POST['fecha_registro'];
    
    // Manejo condicional de la contraseña
    $updatePassword = !empty($_POST['pass']);
    $password = $updatePassword ? password_hash($_POST['pass'], PASSWORD_DEFAULT) : null;

    if ($updatePassword) {
        // Si se actualiza la contraseña
        $sql = "UPDATE usuarios SET 
                user = ?, 
                email = ?, 
                pass = ?, 
                rol = ?, 
                fecha_registro = ? 
                WHERE id = ?";
                
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssi", $user, $email, $password, $rol, $fecha_registro, $id);
    } else {
        // Si NO se actualiza la contraseña
        $sql = "UPDATE usuarios SET 
                user = ?, 
                email = ?, 
                rol = ?, 
                fecha_registro = ? 
                WHERE id = ?";
                
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssi", $user, $email, $rol, $fecha_registro, $id);
    }

    if ($stmt->execute()) {
        header("Location: ../lista_estudiantes.php?success=1");
    } else {
        header("Location: ../lista_estudiantes.php?error=1");
    }
    
    $stmt->close();
}
$conexion->close();
?>


