<?php
session_start();
header('Content-Type: application/json');

include '../db/conn.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

// Validar entrada
if (!isset($_POST['file_id']) || !ctype_digit($_POST['file_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

$archivo_id = (int)$_POST['file_id'];

// Verificar existencia del archivo y obtener datos
$stmt = $conexion->prepare("
    SELECT a.id, a.nombre, a.proyecto_id, p.titulo AS proyecto 
    FROM archivos a
    JOIN proyectos p ON a.proyecto_id = p.id
    WHERE a.id = ?
");
$stmt->bind_param('i', $archivo_id);
$stmt->execute();
$archivo = $stmt->get_result()->fetch_assoc();

// Validar archivo
if (!$archivo) {
    echo json_encode(['success' => false, 'error' => 'Archivo no encontrado']);
    exit;
}

// Inicializar carrito
$_SESSION['cart'] = $_SESSION['cart'] ?? [];
$response = [];

// Verificar duplicados
if (in_array($archivo_id, $_SESSION['cart'])) {
    $response = [
        'success' => false,
        'error' => 'El archivo ya está en el carrito',
        'is_duplicate' => true,
        'new_count' => count($_SESSION['cart'])
    ];
} else {
    // Añadir al carrito
    $_SESSION['cart'][] = $archivo_id;
    $response = [
        'success' => true,
        'new_count' => count($_SESSION['cart']),
        'file_name' => $archivo['nombre'],
        'proyecto' => $archivo['proyecto'],
        'is_duplicate' => false,
        'reload' => true //
        
    ];
}

echo json_encode($response);