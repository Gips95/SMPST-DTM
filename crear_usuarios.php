<?php

session_start();

include 'db/conn.php';

include 'panel.php';

include_once('classes/Users.class.php');

include_once('classes/Logs.class.php');

// Variables por defecto
$id = '';
$username = '';
$email = '';
$rol = '';
$password_required = 'required';
$action = 'Crear';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    // --- VALIDACIONES DEL LADO DEL SERVIDOR ---
    // Validar cédula: debe tener 7 u 8 dígitos numéricos
    if (!preg_match('/^\d{7,8}$/', $_POST['cedula'] ?? '')) {
        $errors[] = "Cédula inválida: debe tener 7 u 8 dígitos numéricos.";
    }

    // Validar username: solo letras y espacios (sin caracteres especiales)
    if (!preg_match('/^[\p{L} ]+$/u', $_POST['username'] ?? '')) {
        $errors[] = "Nombre de usuario inválido: solo letras y espacios permitidos.";
    }

    // Validar email
    if (!filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido.";
    }

    // Validar rol
    $allowed_roles = ['profesor', 'estudiante'];
    if (!in_array($_POST['rol'] ?? '', $allowed_roles)) {
        $errors[] = "Rol inválido.";
    }

    // Validar contraseña solo si es creación o si se proporciona en edición
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($action === 'Crear' || !empty($password)) {
        if (!preg_match('/^(?=.*[A-Z])(?=.*\W).{8,}$/', $password)) {
            $errors[] = "Contraseña inválida: mínimo 8 caracteres, 1 mayúscula y 1 carácter especial.";
        }
        // NUEVA VALIDACIÓN: Verificar que las contraseñas coincidan
        if ($password !== $password_confirm) {
            $errors[] = "Las contraseñas no coinciden.";
        }
    }

    if (!empty($errors)) {
        $message = "<div class='error-message'>" . implode("<br>", $errors) . "</div>";
    } else {
        try {
            $id = trim($_POST['cedula']);
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $rol = $_POST['rol'];
            $pass_input = $_POST['password'];
            // Hashear la contraseña solo si se proporcionó una nueva
            $hashed_password = !empty($pass_input) ? password_hash($pass_input, PASSWORD_BCRYPT) : '';

            $user = new User($id, $username, $email, $rol, $hashed_password);
            if ($action === 'Crear') {
                $user_id = $user->CreateUser($conexion);
                Log::CreateLog('create', 'usuarios', $user_id, $_SESSION['user'], $conexion);
                $message = "<div class='success-message'>Usuario registrado correctamente.</div>";
                $swal_message = "Usuario registrado correctamente."; // Mensaje para SweetAlert
                $swal_icon = "success"; // Icono para SweetAlert
            } else {
                $user->UpdateUser($conexion);
                Log::CreateLog('update', 'usuarios', $id, $_SESSION['user'], $conexion);
                $message = "<div class='success-message'>Usuario actualizado correctamente.</div>";
                $swal_message = "Usuario actualizado correctamente."; // Mensaje para SweetAlert
                $swal_icon = "success"; // Icono para SweetAlert
            }
            $conexion->close();
        } catch (Exception $e) {
            $message = "<div class='error-message'>" . $e->getMessage() . "</div>";
            $swal_message = $e->getMessage(); // Mensaje de error para SweetAlert
            $swal_icon = "error"; // Icono para SweetAlert
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action; ?> Usuario</title>
    <link href="styles/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/fontawesome/css/all.css">
    <link rel="stylesheet" href="styles/usuarios_form.css">
    <link rel="stylesheet" href="styles/sweetalert2.min.css">
    <style>
        /* Estilos para el botón de visibilidad de la contraseña */
        .input-group {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            border: none;
            background: none;
            padding-top: 8px;
        }

        /* Alineación mejorada para inputs y selects */
        .form-group {
            margin-bottom: 15px;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Estilos para mensajes de error inline */
        .error-message {
            color: red;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
</head>

<body>
    <div class="container-main">
        <div class="card-registro">
            <div class="card-header">
                <h2><?php echo $action; ?> Usuario</h2>
            </div>
            <div class="card-body">
                <?php if (isset($message)) echo $message; ?>
                <form id="userForm" action="" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="cedula">Cédula <strong class='text-danger'>*</strong></label>
                                <input type="text" id="cedula" name="cedula" placeholder="Ej: 12345678" class="form-control" value="<?php echo htmlspecialchars($id); ?>" pattern="\d{7,8}" required>
                                <small class="rule">Debe tener 7 u 8 dígitos numéricos.</small>
                                <div id="cedula-error" class="error-message"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="username">Nombre de Usuario <strong class='text-danger'>*</strong></label>
                                <input type="text" id="username" name="username" placeholder="Ej: Juan Perez" class="form-control" value="<?php echo htmlspecialchars($username); ?>" pattern="^[\p{L} ]+$" required>
                                <small class="rule">Solo letras y espacios.</small>
                                <div id="username-error" class="error-message"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="email">Email <strong class='text-danger'>*</strong></label>
                                <input type="email" id="email" name="email" placeholder="usuario@dominio.com" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                                <small class="rule">Debe ser un correo válido.</small>
                                <div id="email-error" class="error-message"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="password">Contraseña  <strong class='text-danger'>*</strong> <?php echo $action === 'Crear' ? '' : '(dejar en blanco para no cambiar)'; ?></label>
                                <div class="input-group">
                                    <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" class="form-control" <?php echo $password_required; ?> pattern="(?=.*[A-Z])(?=.*\W).{8,}">
                                    <button type="button" id="togglePassword" class="toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="rule">Mínimo 8 caracteres, 1 mayúscula y 1 carácter especial.</small>
                                <div id="password-error" class="error-message"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="password_confirm">Confirmar Contraseña</label>
                                <input type="password" id="password_confirm" name="password_confirm" placeholder="Repita la contraseña" class="form-control" <?php echo $password_required; ?>>
                                <small class="rule">Las contraseñas deben coincidir.</small>
                                <div id="password_confirm-error" class="error-message"></div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="rol">Rol <strong class='text-danger'>*</strong></label>
                                <select id="rol" name="rol" class="form-select" required>
                                    <option value="profesor" <?php echo $rol === 'profesor' ? 'selected' : ''; ?>>Docente</option>
                                    <option value="estudiante" <?php echo $rol === 'estudiante' ? 'selected' : ''; ?>>Estudiante</option>
                                </select>
                                <div id="rol-error" class="error-message"></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="button-container">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-user-<?php echo $action === 'Crear' ? 'plus' : 'edit'; ?> me-2"></i><?php echo $action; ?> Usuario
                                </button>
                               
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/sweetalert2@11.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- LÓGICA PARA MOSTRAR/OCULTAR CONTRASEÑA ---
            const togglePassword = document.getElementById('togglePassword');
            const passwordInputToggle = document.getElementById('password'); // Renombrar para evitar conflicto

            togglePassword.addEventListener('click', function() {
                // Alternar el tipo de input
                const type = passwordInputToggle.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInputToggle.setAttribute('type', type);

                // Alternar el ícono del botón
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

            // --- LÓGICA PARA MOSTRAR/OCULTAR REGLAS DE VALIDACIÓN ---
            document.querySelectorAll('.form-group').forEach(group => {
                const input = group.querySelector('.form-control');
                if (input) {
                    const rule = group.querySelector('.rule');
                    if (rule) {
                        input.addEventListener('focus', () => rule.style.display = 'block');
                        input.addEventListener('blur', () => rule.style.display = 'none');
                    }
                }
            });

            // --- VALIDACIÓN EN TIEMPO REAL Y AL ENVIAR CON SWEETALERT2 ---
            const form = document.getElementById('userForm');
            const cedulaInput = document.getElementById('cedula');
            const usernameInput = document.getElementById('username');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password'); // No renombrar aquí, es el mismo campo
            const passwordConfirmInput = document.getElementById('password_confirm');
            const rolInput = document.getElementById('rol');

            // Función para mostrar errores inline
            function showError(element, message) {
                const errorDiv = document.getElementById(element.id + '-error');
                if (errorDiv) {
                    errorDiv.textContent = message;
                }

                element.classList.add('is-invalid');
            }

            // Función para limpiar errores
            function clearError(element) {
                const errorDiv = document.getElementById(element.id + '-error');
                if (errorDiv) {
                    errorDiv.textContent = '';
                }
                element.classList.remove('is-invalid');
            }

            // Validaciones en tiempo real
            cedulaInput.addEventListener('input', function() {
                if (!/^\d{7,8}$/.test(this.value)) {
                    showError(this, "Cédula inválida: debe tener 7 u 8 dígitos numéricos.");
                } else {
                    clearError(this);
                }
            });

            usernameInput.addEventListener('input', function() {
                if (!/^[\p{L} ]+$/u.test(this.value)) {
                    showError(this, "Nombre de usuario inválido: solo letras y espacios permitidos.");
                } else {
                    clearError(this);
                }
            });

            emailInput.addEventListener('input', function() {
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) {
                    showError(this, "Email inválido.");
                } else {
                    clearError(this);
                }
            });

            passwordInput.addEventListener('input', function() {
                if (this.value && !/^(?=.*[A-Z])(?=.*\W).{8,}$/.test(this.value)) {
                    showError(this, "Contraseña inválida: mínimo 8 caracteres, 1 mayúscula y 1 carácter especial.");
                } else {
                    clearError(this);
                }
            });

            passwordConfirmInput.addEventListener('input', function() {
                if (passwordInput.value !== this.value) {
                    showError(this, "Las contraseñas no coinciden.");
                } else {
                    clearError(this);
                }
            });

            // Validación final al enviar el formulario
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevenir el envío normal del formulario

                let isValid = true;

                // Limpiar todos los errores antes de validar
                document.querySelectorAll('.form-control, .form-select').forEach(clearError);

                // Realizar validaciones
                if (!cedulaInput.value) {
                    showError(cedulaInput, "Este campo es requerido.");
                    isValid = false;
                } else if (!/^\d{7,8}$/.test(cedulaInput.value)) {
                    showError(cedulaInput, "Cédula inválida: debe tener 7 u 8 dígitos numéricos.");
                    isValid = false;
                }

                if (!usernameInput.value) {
                    showError(usernameInput, "Este campo es requerido.");
                    isValid = false;
                } else if (!/^[\p{L} ]+$/u.test(usernameInput.value)) {
                    showError(usernameInput, "Nombre de usuario inválido: solo letras y espacios permitidos.");
                    isValid = false;
                }

                if (!emailInput.value) {
                    showError(emailInput, "Este campo es requerido.");
                    isValid = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
                    showError(emailInput, "Email inválido.");
                    isValid = false;
                }

                if ('<?php echo $action; ?>' === 'Crear' || passwordInput.value) {
                    if (!passwordInput.value) {
                        showError(passwordInput, "Este campo es requerido.");
                        isValid = false;
                    } else if (!/^(?=.*[A-Z])(?=.*\W).{8,}$/.test(passwordInput.value)) {
                        showError(passwordInput, "Contraseña inválida: mínimo 8 caracteres, 1 mayúscula y 1 carácter especial.");
                        isValid = false;
                    }

                    if (!passwordConfirmInput.value) {
                        showError(passwordConfirmInput, "Este campo es requerido.");
                        isValid = false;
                    } else if (passwordInput.value !== passwordConfirmInput.value) {
                        showError(passwordConfirmInput, "Las contraseñas no coinciden.");
                        isValid = false;
                    }
                }

                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Por favor, corrige los errores en el formulario.',
                        confirmButtonText: 'Aceptar' // Traducir el botón
                    });
                    return;
                }

                // Si todo es válido, enviar el formulario usando AJAX o de la manera tradicional
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¿Deseas " + '<?php echo $action; ?>' + " este usuario?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, ¡Adelante!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        //form.submit(); // Envía el formulario si el usuario confirma
                        // Enviar el formulario y mostrar el mensaje de SweetAlert después
                        form.submit();
                    }
                });
            });
        });

        <?php if (isset($swal_message)): ?>
            Swal.fire({
                icon: '<?php echo $swal_icon; ?>',
                title: '<?php echo ($swal_icon == "success") ? "Éxito" : "Error"; ?>',
                text: '<?php echo $swal_message; ?>',
                confirmButtonText: 'Aceptar'
            });
        <?php endif; ?>
    </script>
    <script src="js/bootstrap.js"></script>
</body>

</html>

<?php require('includes/footer.php'); ?>