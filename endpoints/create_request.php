<?php
session_start();
include '../db/conn.php';
$cart = $_SESSION['cart'] ?? [];
if(empty($cart)) exit(json_encode(['ok'=>false]));
$user = $_SESSION['user_id'];
// 1) Insertar solicitud
$stmt = $conexion->prepare("INSERT INTO download_requests (user_id) VALUES (?)");
$stmt->execute([$_SESSION['user_id']]);
$reqId = $conexion->insert_id;

// 2) Insertar items (archivos)
$stmt2 = $conexion->prepare("
    INSERT INTO download_request_items (request_id, archivo_id) 
    VALUES (?, ?)
");
foreach ($_SESSION['cart'] as $archivo_id) {
    $stmt2->execute([$reqId, $archivo_id]);
}
unset($_SESSION['cart']);
exit(json_encode(['ok'=>true,'request_id'=>$reqId]));
