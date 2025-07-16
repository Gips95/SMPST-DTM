<?php
session_start();
header('Content-Type: application/json'); // 👈 Indica que la respuesta es JSON

// Si 'cart' no existe en la sesión, usa un array vacío
$cart = $_SESSION['cart'] ?? [];
echo json_encode(['count' => count($cart)]);