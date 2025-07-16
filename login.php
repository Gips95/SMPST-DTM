<?php
session_start();

if (isset($_GET['return_to'])) {
    $_SESSION['return_url'] = $_GET['return_to'];
}

?>
<link rel="stylesheet" href="./styles/login.css">
<link rel="stylesheet" href="./styles/validation.css">
<!-- Asegúrate de incluir Font Awesome si lo estás usando -->
<link rel="stylesheet" href="styles/fontawesome/css/all.css">
<div class="background"></div> <!-- Fondo borroso -->
<div class='login-container'>
    <form id='login-form' action="endpoints/validar.php" method="post" class='login-form' novalidate>
        <h2 class='login-tittle'>Iniciar Sesión</h2>

        <div class="form-group">
            <label for="Cedula">Cédula:</label>
            <i class="fas fa-user"></i> <!-- Icono de usuario -->
            <input type="number" name="Cedula" id='Cedula' required>
            <span hidden id='Cedula-span' class='message'></span>
        </div>

        <div class="form-group">
            <label for="password">Contraseña:</label>
            <div class="password-input-wrapper">
                <i class="fas fa-lock"></i> <!-- Icono de candado -->
                <input type="password" name="password" id='password' required>
                <i class="fas fa-eye-slash toggle-password" id="togglePassword"></i> <!-- Icono para mostrar/ocultar contraseña -->
            </div>
            <span hidden id='password-span' class='message'></span>
        </div>

        <button type="submit">Ingresar</button>
        <a href="crearestudiante.php">¿No tienes una cuenta? Crea una</a>
        <a href="authentication/email_verification.php">¿Olvidaste tu contraseña?</a>
        <p id="general-error-message" class="message" style="color: red; margin-top: 10px;" hidden></p>
    </form>
</div>

<style>
    /* Basic styling for the password toggle icon */
    .password-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-input-wrapper input {
        width: 100%; /* Ensure input takes full width */
        padding-right: 40px; /* Make space for the icon */
    }

    .password-input-wrapper .fa-lock {
        position: absolute;
        left: 10px; /* Adjust as needed */
        color: #555;
        z-index: 1; /* Ensure icon is above input */
    }

    .password-input-wrapper input {
        padding-left: 35px; /* Adjust input padding to accommodate the lock icon */
    }


    .toggle-password {
        position: absolute;
        right: 10px;
        cursor: pointer;
        color: #555;
    }
</style>

<script type='module'>
    document.addEventListener('DOMContentLoaded', () => {
        const loginForm = document.getElementById('login-form');
        const cedulaInput = document.getElementById('Cedula');
        const passwordInput = document.getElementById('password');
        const cedulaSpan = document.getElementById('Cedula-span');
        const passwordSpan = document.getElementById('password-span');
        const generalErrorMessage = document.getElementById('general-error-message');
        const togglePassword = document.getElementById('togglePassword'); // Get the toggle icon

        // Function to show an error message
        const showErrorMessage = (element, message) => {
            element.textContent = message;
            element.hidden = false;
        };

        // Function to hide an error message
        const hideErrorMessage = (element) => {
            element.textContent = '';
            element.hidden = true;
        };

        // Event listener for password toggle
        togglePassword.addEventListener('click', () => {
            // Toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle the eye icon
            togglePassword.classList.toggle('fa-eye');
            togglePassword.classList.toggle('fa-eye-slash');
        });

        // Listen for form submission
        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault(); // Prevent default form submission

            // Hide previous error messages
            hideErrorMessage(cedulaSpan);
            hideErrorMessage(passwordSpan);
            hideErrorMessage(generalErrorMessage);

            // Basic client-side validation
            let isValid = true;
            if (cedulaInput.value.trim() === '') {
                showErrorMessage(cedulaSpan, 'La cédula es requerida.');
                isValid = false;
            }
            if (passwordInput.value.trim() === '') {
                showErrorMessage(passwordSpan, 'La contraseña es requerida.');
                isValid = false;
            }

            if (!isValid) {
                return; // If client-side validation fails, do not send the request
            }

            const formData = new FormData(loginForm);

            try {
                const response = await fetch(loginForm.action, {
                    method: 'POST',
                    body: formData
                });

                // Check if the response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    // If not JSON, it might be a server error or unexpected redirect
                    showErrorMessage(generalErrorMessage, 'Error inesperado del servidor. Inténtelo de nuevo.');
                    console.error('Respuesta no JSON:', await response.text());
                    return;
                }

                const data = await response.json();

                if (data.success) {
                    // If login is successful, redirect the user
                    window.location.href = data.redirect;
                } else {
                    // If there's an error, show the message received from the backend
                    showErrorMessage(generalErrorMessage, data.message || 'Credenciales inválidas. Por favor, inténtelo de nuevo.');
                }
            } catch (error) {
                console.error('Error al enviar el formulario:', error);
                showErrorMessage(generalErrorMessage, 'Error de conexión. Por favor, inténtelo de nuevo más tarde.');
            }
        });
    });
</script>

<?php require('includes/footer.php') ?>
