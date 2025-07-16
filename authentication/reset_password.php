<?php 
include('../db/conn.php');
session_start();

if(!isset($_SESSION['user_id_password_reset'])) {header('location: ../login.php');}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    try {
        $password = $_POST['first-pass'];
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $query = $conexion->prepare('UPDATE usuarios SET pass=? WHERE id=?');
        $query->bind_param('si', $hashedPassword, $_SESSION['user_id_password_reset']);
        $query->execute();
        unset($_SESSION['user_id_password_reset']);
        
        header('location: ../login.php');

    } catch (Exception $e) {
        die($e->getMessage());
    }
}

include('../includes/header.php');?>
<link href="styles/bootstrap.css" rel="stylesheet">
<link rel="stylesheet" href="../styles/login.css">
<link rel="stylesheet" href="../styles/reset_password.css">
<link rel="stylesheet" href="../styles/validation.css">


<div class='login-container'>
    <div class='pass-container login-form'>
    <form id='reset-pass-form' action=<?= $_SERVER['PHP_SELF']; ?> method="post" class='pass-form resetpass-form'>
        <h1>Crear Contraseña</h1>

        <div class='pass-reset-content'>

        <div class='pass-reset-inputs'>
            <div class='first-pass-div'>
                <label for="first-pass">Contraseña</label>
                <input type="password" name="first-pass" id="first-pass">
                <span hidden id="first-pass-span" class='message'></span>
            </div>

            <div class='second-pass-div'>
                <label for="second-pass">Repetir contraseña</label>
                <input type="password" name="second-pass" id="second-pass">
                <span hidden id="second-pass-span" class='message'></span>
            </div>

            <button type="submit" class='btn-submit'>Confirmar</button>

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

    </form>
    </div>
</div>

<script type='module'>
    import {ValidateWithRegex, validateForm, inputSuccess, inputError, createSpan} from '../js/validator.js'
    import fetchDataJson from '../js/fetching.js'

    const validationRules = {
  "first-pass": {
    isPassword: true,
    required: {
      value: true,
      msg: 'La contraseña es obligatoria'
    },
    stringLength: {
      min:8,
      max:40,
      minmsg: "La contraseña es demasiado corta",
      maxmsg: "La contraseña es demasiado larga"
    },
    matchField: {
      value: 'second-pass',
      msg: 'Las contraseñas no coinciden'
    } 
  },
  "second-pass": {
    isPassword: true,
    required: {
      value: true,
      msg: 'La contraseña es obligatoria'
    },
    stringLength: {
      min:8,
      max:40,
      minmsg: "La contraseña es demasiado corta",
      maxmsg: "La contraseña es demasiado larga"
    },
    matchField: {
      value: 'first-pass',
      msg: 'Las contraseñas no coinciden'
    }
  },
  "actual-pass": {
    isPassword: true,
    required: {
      value: true,
      msg: 'La nueva contraseña es obligatoria'
    },
    stringLength: {
      min:8,
      max:40,
      minmsg: "La contraseña es demasiado corta",
      maxmsg: "La contraseña es demasiado larga"
    },
  }
}



async function ValidatePassForm(e) {
if(e.target.tagName == 'FORM'){

if(!validateForm(e.target, validationRules)) e.preventDefault;

if(e.target.matches(".changepass-form")){
  if(!validateForm(e.target, validationRules)) e.preventDefault();

  const changePassData = await fetchDataJson('../endpoints/change_password.php', e.target, 'POST')

  if(changePassData.status == 400) throw new Error(changePassData.msg)

  window.location.href = '../dashboard.php'
      }
    }
  }


const form = document.getElementById('reset-pass-form')

document.addEventListener('submit', ValidatePassForm)

form.addEventListener('input', function(e){
  const field = e.target;
  const name = field.name || field.id;
  if (validationRules[name]) {
    validateForm(form, { [name]: validationRules[name] }, validationRules);
  }
})
</script>

<!-- <script type='module' src="../js/validator.js"></script>
<script type='module' src="../js/fetching.js"></script>  -->

<?php include('../includes/footer.php'); ?>
