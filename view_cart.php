<?php
session_start();
include 'db/conn.php'; // Asegúrate de que esta ruta sea correcta para tu conexión a la DB

// Verificar sesión de usuario
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];

// --- Traer la última solicitud del usuario (similar a cart_widget.php) ---
$status      = null;
$requestId   = null;
$sentAt      = null;
$approvedAt  = null;
$pendiente   = false;
$aprobada    = false;
$rechazada   = false;

if (isset($_SESSION['user_id'])) {
    $stmt = $conexion->prepare("
        SELECT id, status, created_at, approved_at
        FROM download_requests
        WHERE user_id = ? AND active = 1
        ORDER BY created_at DESC
        LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $ultima = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($ultima) {
            $status      = $ultima['status'];
            $requestId   = $ultima['id'];
            $sentAt      = $ultima['created_at'];
            $approvedAt  = $ultima['approved_at'];

            $pendiente  = ($status === 'pendiente');
            $aprobada   = ($status === 'aprobado');
            $rechazada  = ($status === 'rechazado');
        }
    } else {
        // Log error if prepare statement fails
        error_log("Error al preparar la consulta de última solicitud en view_cart.php: " . $conexion->error);
    }
}
// --- Fin de lógica para última solicitud ---

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Carrito de Descargas</title>
    <!-- Incluye tus estilos CSS aquí. Asumo que tienes bootstrap.css, fontawesome, etc. -->
    <link href="styles/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/fontawesome/css/all.css">
    <link href="styles/googlefonts.css" rel="stylesheet">
    <!-- Si tienes un CSS específico para view_cart o carrito, inclúyelo aquí -->
    <!-- <link rel="stylesheet" href="styles/view_cart.css"> --> 
    <style>
        /* Estilos básicos para el carrito si no tienes un archivo específico */
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: 20px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #007bff; margin-bottom: 20px; }
        ul { list-style: none; padding: 0; }
        li { background-color: #e9f5ff; padding: 10px 15px; margin-bottom: 8px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        li button { background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        li button:hover { background-color: #c82333; }
        .empty-cart-message { text-align: center; color: #6c757d; }
        .btn-submit-request { background-color: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 1rem; margin-top: 20px; }
        .btn-submit-request:hover:not(:disabled) { background-color: #218838; }
        .btn-submit-request:disabled { background-color: #a8d7b4; cursor: not-allowed; }
        .alert { padding: 10px 15px; border-radius: 5px; margin-bottom: 15px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-link { color: #004085; text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <h2>Tu carrito de descargas</h2>

    <!-- Alertas según estado de la última solicitud (de cart_widget.php) -->
    <?php if ($aprobada): ?>
        <div class="alert alert-success mb-3">
          ✅ Solicitud aprobada (<?= date('d/m/Y g:i a', strtotime($approvedAt)) ?>).
          <a href="endpoints/carrito_confirm.php?req=<?= htmlspecialchars($requestId) ?>" class="alert-link">Ver archivos</a>
        </div>
    <?php elseif ($pendiente): ?>
        <div class="alert alert-info mb-3">
          ⏳ Solicitud pendiente (enviada <?= date('d/m/Y g:i a', strtotime($sentAt)) ?>).
        </div>
    <?php elseif ($rechazada): ?>
        <div class="alert alert-danger mb-3">
          ❌ Solicitud rechazada (<?= date('d/m/Y g:i a', strtotime($sentAt)) ?>).
        </div>
    <?php endif; ?>

    <?php if(empty($cart)): ?>
        <p class="empty-cart-message">El carrito está vacío.</p>
    <?php else: 
        // Optimizar: obtener todos los datos en una sola consulta
        $ids = implode(",", array_map('intval', $cart)); // Asegura que los IDs sean enteros para seguridad
        $result = $conexion->query("SELECT id, titulo FROM proyectos WHERE id IN ($ids)");
        $proyectos = [];
        if ($result) {
            $proyectos = $result->fetch_all(MYSQLI_ASSOC);
            $proyectos = array_column($proyectos, 'titulo', 'id');
        } else {
            error_log("Error al consultar proyectos en view_cart.php: " . $conexion->error);
        }
    ?>
        <ul>
        <?php foreach($cart as $fid): 
            $titulo = $proyectos[$fid] ?? 'Archivo eliminado';
        ?>
            <li>
                <?= htmlspecialchars($titulo) ?>
                <button onclick="remove(<?= htmlspecialchars($fid) ?>)">Quitar</button>
            </li>
        <?php endforeach; ?>
        </ul>
        
        <!-- Botón para enviar solicitud (AJAX) -->
        <button type="button" id="send-request" class="btn-submit-request"
            <?= ($pendiente || $aprobada)
                ? 'disabled title="No puedes enviar otra solicitud hasta que revisen la actual"'
                : '' ?>>
            Enviar solicitud para aprobación
        </button>
    <?php endif; ?>
</div>

<script>
// Función para escapar HTML para evitar XSS
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Función para quitar un archivo del carrito (utiliza AJAX)
function remove(id) {
    fetch('endpoints/remove_from_cart.php', { // Asegúrate de que esta ruta sea correcta
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({file_id: id})
    })
    .then(r => {
        if (!r.ok) {
            // Manejo de errores de HTTP (ej. 404, 500)
            return r.text().then(text => { throw new Error('Error de red o servidor: ' + text); });
        }
        return r.json(); // Esperamos una respuesta JSON del endpoint
    })
    .then(data => {
        if (data.success) {
            // Recargar la página o actualizar el DOM para reflejar el cambio
            location.reload(); 
        } else {
            // Mostrar un mensaje de error si el servidor indica que no fue exitoso
            console.error('Error al quitar del carrito:', data.message || 'Error desconocido');
            alert('Error al quitar del carrito: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error en la operación de quitar del carrito:', error);
        alert('Hubo un problema al quitar el archivo. Intente de nuevo.');
    });
}

// Lógica para enviar la solicitud de aprobación (AJAX)
document.addEventListener('DOMContentLoaded', function() {
    const sendBtn = document.getElementById('send-request');
    if (sendBtn) {
        sendBtn.addEventListener('click', function() {
            // Deshabilitar el botón para evitar múltiples envíos
            sendBtn.disabled = true;
            sendBtn.textContent = 'Enviando...';

            fetch('endpoints/create_request.php', { // Asegúrate de que esta ruta sea correcta
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    archivos: <?= json_encode(array_map('intval', $cart)) ?> // Asegura que los IDs sean enteros
                })
            })
            .then(res => {
                if (!res.ok) {
                    return res.text().then(text => { throw new Error('Error de red o servidor: ' + text); });
                }
                return res.json();
            })
            .then(data => {
                if (data.ok) {
                    // Redirigir a la página de confirmación con el ID de la solicitud
                    location.href = 'carrito_confirm.php?req=' + data.request_id;
                } else {
                    // Mostrar mensaje de error si el servidor indica que no fue exitoso
                    console.error('Error al enviar solicitud:', data.message || 'Error desconocido');
                    alert('Error al enviar la solicitud: ' + (data.message || 'Error desconocido'));
                    sendBtn.disabled = false; // Habilitar el botón nuevamente
                    sendBtn.textContent = 'Enviar solicitud para aprobación';
                }
            })
            .catch(error => {
                console.error('Error en la operación de enviar solicitud:', error);
                alert('Hubo un problema al enviar la solicitud. Intente de nuevo.');
                sendBtn.disabled = false; // Habilitar el botón nuevamente
                sendBtn.textContent = 'Enviar solicitud para aprobación';
            });
        });
    }
});
</script>

</body>
</html>
