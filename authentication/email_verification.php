<?php session_start(); 
require('../includes/header.php');
?>
<head>
    <link href="styles/bootstrap.css" rel="stylesheet">
</head>
    <div class='modal'>
        <form action="" method='post' class='login-form code-form'>

            <label for="code">Codigo de verificacion: </label>
            <input type="number" name="code" id="code">
            <span class='code-span' hidden></span>

            <p>Ingresa el codigo de verificacion enviado a su email</p>
            <div class='btns-modal'>
            <button type="submit">Confirmar</button>
            <button type='button' class='close-modal'>Cancelar</button>
            </div>
        </form>
    </div>

    <link rel="stylesheet" href="../styles/login.css">
<div class='login-container'>
    <form action="" method="post" class='login-form email-reset-form'>

        <label for="email">Correo electronico</label>
        <input type="email" name="email" id="email">
        <span class='email-span' hidden></span>

        <button type="submit">Confirmar</button>
    </form>
</div>
<script type='module' src="../js/email_verification.js"></script>
<?php
require('../includes/footer.php');
?>