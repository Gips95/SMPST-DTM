<?php
include 'db/conn.php';
include_once('classes/Users.class.php');
$redirect = (str_contains($_SERVER['HTTP_REFERER'], 'lista_profesores')) ? 'lista_profesores.php' : 'lista_estudiantes.php';
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("ID de usuario no especificado.");
}
try{
    $id = intval($_GET['id']);
    // Obtener datos del usuario
    $usuario = User::getUser($id, $conexion);
    $conexion->close();
}catch(Exception $e){
    die($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
     <link href="styles/bootstrap.css" rel="stylesheet">
    
    <link rel="stylesheet" href="styles/fontawesome/css/all.css">
   <style>
        /* Estilos unificados */
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
            max-width: 800px;
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

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #7f8c8d;
        }

        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            display: none;
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

        .button-container {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container-main">
        <div class="card-registro">
            <div class="card-header">
                <h2>Editar Usuario</h2>
            </div>
            <div class="card-body">
                <form id="editForm" action="endpoints/update_user.php" method="POST" onsubmit="return validateForm()">
                    <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Usuario</label>
                                <input type="text" name="user" class="form-control" 
                                       value="<?php echo htmlspecialchars($usuario['user']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Rol</label>
                                <select name="rol" class="form-select" required>
                                    <option value="estudiante" <?php echo ($usuario['rol'] == 'estudiante') ? 'selected' : ''; ?>>Estudiante</option>
                                    <option value="profesor" <?php echo ($usuario['rol'] == 'profesor') ? 'selected' : ''; ?>>Profesor</option>
                                    <option value="admin" <?php echo ($usuario['rol'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Fecha de Registro</label>
                                <input type="text" class="form-control"
                                       value="<?php echo htmlspecialchars($usuario['fecha_registro']); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Nueva Contraseña</label>
                                <div class="password-container">
                                    <input type="password" name="pass" id="pass" class="form-control" 
                                           placeholder="Dejar en blanco para no cambiar"
                                           onfocus="this.placeholder=''" 
                                           onblur="this.placeholder='Dejar en blanco para no cambiar'">
                                    <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('pass')"></i>
                                </div>
                                <div class="error-message" id="passError"></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Confirmar Contraseña</label>
                                <div class="password-container">
                                    <input type="password" name="confirm_pass" id="confirm_pass" class="form-control" 
                                           placeholder="Repite la nueva contraseña">
                                    <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('confirm_pass')"></i>
                                </div>
                                <div class="error-message" id="confirmError"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="button-container">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-2"></i>Guardar Cambios
                                </button>
                                <a href=" <?php echo $redirect;  ?>" class="btn btn-secondary w-100">
                                    <i class="fas fa-times me-2 "></i>Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<script src="../js/bootstrap.js"></script>
    <script>
        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === 'password' ? 'text' : 'password';
        }

        function validateForm() {
            const pass = document.getElementById('pass').value;
            const confirmPass = document.getElementById('confirm_pass').value;
            const passError = document.getElementById('passError');
            const confirmError = document.getElementById('confirmError');
            let isValid = true;

            // Reset errores
            passError.style.display = 'none';
            confirmError.style.display = 'none';

            // Validar si se llenó alguno de los campos de contraseña
            if (pass !== '' || confirmPass !== '') {
                if (pass === '') {
                    passError.textContent = 'Por favor ingresa la nueva contraseña';
                    passError.style.display = 'block';
                    isValid = false;
                }
                
                if (confirmPass === '') {
                    confirmError.textContent = 'Por favor confirma la contraseña';
                    confirmError.style.display = 'block';
                    isValid = false;
                }
                
                if (pass !== confirmPass) {
                    confirmError.textContent = 'Las contraseñas no coinciden';
                    confirmError.style.display = 'block';
                    isValid = false;
                }
                
                if (pass.length > 0 && pass.length < 8) {
                    passError.textContent = 'La contraseña debe tener al menos 8 caracteres';
                    passError.style.display = 'block';
                    isValid = false;
                }
            }

            return isValid;
        }
    </script>
</body>
</html>

<?php

?>