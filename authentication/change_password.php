<?php
session_start();

if(!isset($_SESSION['user'])) header('location: ../login.php');

include('../includes/header.php');?>
<link rel="stylesheet" href="../styles/reset_password.css">
<div class='container'>
    <div class='resetpass-container'>
    <form action='../endpoints/change_password.php' method="post" class='pass-form changepass-form'>
        <h1>Cambiar Contraseña</h1>

        <label for="actual-pass">Contraseña actual</label>
        <input type="password" name="actual-pass" id="actual-pass">
        <span hidden id="actual-pass-span"></span>

        <label for="first-pass">Nueva contraseña</label>
        <input type="password" name="first-pass" id="first-pass">
        <span hidden id="first-pass-span"></span>

        <label for="second-pass">Repetir nueva contraseña</label>
        <input type="password" name="second-pass" id="second-pass">
        <span hidden id="second-pass-span"></span>

        <button type="submit" class='btn-submit'>Confirmar</button>
    </form>
    </div>
</div>
<script src="../js/reset_password.js"></script>
<?php include('../includes/footer.php'); ?>