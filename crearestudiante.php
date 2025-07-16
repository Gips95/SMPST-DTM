<?php
session_start();
// Sólo muestra formulario; la lógica de registro está en endpoints/send_registration_request.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Estudiante</title>
    <link href="styles/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/fontawesome/css/all.css">
    <link rel="stylesheet" href="./styles/validation.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #e9ecef; min-height: 100vh; display: flex; flex-direction: column; }
        .container-main { flex:1; width:90%; max-width:800px; margin:30px auto; padding:0; }
        .card-registro { background:white; border-radius:15px; box-shadow:0 4px 20px rgba(0,0,0,0.1); overflow:hidden; }
        .card-header { background:#007bff; color:white; padding:25px 40px; border-bottom:3px solid #0056b3; }
        .card-header h2 { margin:0; font-size:28px; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; }
        .card-body { padding:40px; }
        .form-group { margin-bottom:25px; }
        .form-label { font-weight:600; color:#333; margin-bottom:8px; font-size:16px; display:block; }
        .form-control { width:100%; padding:12px 20px; border:2px solid #007bff; border-radius:25px; font-size:16px; transition:all .3s ease; background-color:rgba(0,123,255,0.05); }
        .form-control:focus { border-color:#0056b3; box-shadow:0 0 10px rgba(0,123,255,0.2); }
        small.rule { display:none; font-size:12px; color:#555; margin-top:5px; }
        .button-container { display:flex; gap:15px; margin-top:30px; }
        .btn-primary { background-color:#007bff!important; border:none; padding:15px 30px!important; border-radius:25px!important; font-size:16px; font-weight:700; text-transform:uppercase; letter-spacing:1px; transition:all .3s ease; }
        .btn-primary:hover { background-color:#0056b3!important; transform:translateY(-2px); box-shadow:0 5px 15px rgba(0,123,255,0.3); }
        .btn-secondary { background-color:#6c757d!important; border-radius:25px!important; }
    </style>
</head>
<body>
    <div class="container-main">
        <div class="card-registro">
            <div class="card-header">
                <h2>Registro de Estudiante</h2>
            </div>
            <div class="card-body">
                <form id="studentForm" method="post" action="endpoints/send_registration_request.php" novalidate>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="id">Cédula <strong class='text-danger'>*</strong></label>
                                <input type="number" id="id" name="id" class="form-control" placeholder="Ej: 12345678" pattern="\d{7,8}" required>
                                <small class="rule">7 u 8 dígitos numéricos.</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="nombre">Nombre Completo <strong class='text-danger'>*</strong></label>
                                <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ej: Ana Gómez" pattern="^[A-Za-zÀ-ÿ\u00f1\u00d1 ]+$" required>
                                <small class="rule">Solo letras y espacios, sin caracteres especiales.</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="email">Correo Electrónico <strong class='text-danger'>*</strong></label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="usuario@dominio.com" required>
                                <small class="rule">Debe ser un correo válido.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="password">Contraseña <strong class='text-danger'>*</strong></label>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres" required>
                                <small class="rule">Al menos 8 caracteres, 1 mayúscula y 1 carácter especial.</small>
                            </div>
                            <div class='form-group'>

                            <label class="form-label" for="password">Confirmar contraseña <strong class='text-danger'>*</strong></label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Mínimo 8 caracteres" required>
                                <small class="rule">Al menos 8 caracteres, 1 mayúscula y 1 carácter especial.</small>
                            </div>

                            <div class='password-rules'>
                            <p><strong>-</strong>Entre 8 y 40 caracteres<br>
                                <strong>-</strong>Almenos una letra mayuscula y minuscula<br>
                                <strong>-</strong>Almenos un numero<br>
                                <strong>-</strong>Almenos un caracter especial <strong>< @#$!%?& ></strong><br><br>

                                <strong>Las contraseñas de ambos campos deben de ser iguales</strong>
                            </p>
                        </div>
                        </div>

                        
                        <div class="col-12">
                            <div class="button-container">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-user-check me-2"></i>Enviar Solicitud
                                </button>
                                <a href="login.php" class="btn btn-secondary w-100">
                                    <i class="fas fa-arrow-left me-2"></i>Volver al Login
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script type='module'>
        import {ValidateWithRegex, validateForm, inputSuccess, inputError, createSpan} from './js/validator.js'

        const validationRules = {
            'id': {
                required: {
                    value: true,
                    msg: 'La cedula es obligatoria'
                },
                stringLength: {
                    min:7,
                    max:8,
                    minmsg: "La Cedula debe de ser de almenos 7 digitos",
                    maxmsg: "La Cedula debe de tener un maximo de 8 digitos"
                }
            },
            'nombre': {
                required: {
                    value: true,
                    msg: 'Introduce tu nombre completo'
                },
                stringLength: {
                    min:3,
                    max:25,
                    minmsg: "El nombre debe de tener almenos 3 caracteres",
                    maxmsg: "El nombre debe de tener un maximo de 25 caracteres"
                },
                Regex: {
                    value: '[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]+$',
                    msg: 'Solo se permiten letras y espacios'
                }
            },
            'email': {
                required: {
                    value: true,
                    msg: 'Introduce un correo electronico valido'
                },
                Regex: {
                    value: '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}',
                    msg: 'Formato de email invalido'
                }
            },
            'password': {
                isPassword: true,
                required: {
                    value: true,
                    msg: 'La contraseña es obligatoria'
                },
                stringLength: {
                    min:8,
                    max:40,
                    minmsg: "La contraseña es demasiado corta (almenos 8 caracteres)",
                    maxmsg: "La contraseña es demasiado larga (maximo 40 caracteres)"
                },
                matchField: {
                    value: 'confirm_password',
                    msg: 'Las contraseñas no coinciden'
                }
            },
            'confirm_password': {
                isPassword: true,
                required: {
                    value: true,
                    msg: 'La contraseña es obligatoria'
                },
                stringLength: {
                    min:8,
                    max:40,
                    minmsg: "La contraseña es demasiado corta (almenos 8 caracteres)",
                    maxmsg: "La contraseña es demasiado larga (maximo 40 caracteres)"
                },
                matchField: {
                    value: 'password',
                    msg: 'Las contraseñas no coinciden'
                }
            }
        }

        document.querySelectorAll('.form-group').forEach(g => {
            const input = g.querySelector('.form-control');
            const rule = g.querySelector('.rule');
            input.addEventListener('focus', () => rule.style.display = 'block');
            input.addEventListener('blur', () => rule.style.display = 'none');
        });

        const form =  document.getElementById('studentForm')

       form.addEventListener('submit', e => {
            if(!validateForm(e.target, validationRules)) e.preventDefault();
            /*
            if (!e.target.checkValidity()) {
                e.preventDefault();
                e.target.querySelectorAll(':invalid').forEach(f => f.classList.add('is-invalid'));
            }
            */
        });

        form.addEventListener('input', function(e){
            const field = e.target;
            const name = field.name || field.id;
            if (validationRules[name]) {
                validateForm(form, { [name]: validationRules[name] }, validationRules);
            }           
        })
    </script>
    <script src="js/bootstrap.js"></script>
</body>
</html>
