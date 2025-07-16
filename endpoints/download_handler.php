<?php
session_start();
include '../db/conn.php';
include '../db/logs.php';

// Verifica que haya sesión y un archivo solicitado
if (!isset($_SESSION['user']) || !isset($_GET['file'])) {
    header("Location: index.php");
    exit();
}

$archivo = $_GET['file'];
$ruta = ChangeToGeneralRoute($archivo);

// Verifica si el archivo realmente existe
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $ruta)) {
    die("Error: El archivo no existe en la ruta: " . htmlspecialchars($ruta));
}
$a='descarga';
// Registrar la acción en el log
log_action($_SESSION['user'], $a, "Descargó el archivo: " . basename($archivo));

// Forzar la descarga del archivo
header("Location: " . $ruta);
exit();

// Función para cambiar la ruta
function ChangeToGeneralRoute($route){
    $ruta_original = $route;
    $ruta_dividida = explode('/', $ruta_original, 2);
    $nueva_ruta = '/DRT/' . $ruta_dividida[1];
    return $nueva_ruta;
}
