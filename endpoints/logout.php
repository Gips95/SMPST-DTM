<?php
include('../db/logs.php');
session_start();
if(isset($_SESSION['user'])) {
    log_action($_SESSION['user'], 'logout', 'Cierre de sesión');
}
$_SESSION['user'] = null;
$_SESSION['rol'] = 'invitado';
session_destroy(); // Opcional según necesidades
header('Location: ../index.php');

exit();
?>