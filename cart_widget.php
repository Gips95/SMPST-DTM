<?php
// cart_widget.php
include 'db/conn.php';

// Obtener carrito de sesión
$cart = $_SESSION['cart'] ?? [];

// Traer la última solicitud del usuario
$stmt = $conexion->prepare("
    SELECT id, status, created_at, approved_at
    FROM download_requests
    WHERE user_id = ? and active = 1
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$ultima = $stmt->get_result()->fetch_assoc();

// Extraer valores (o null si no existe)
$status      = $ultima['status']      ?? null;
$requestId   = $ultima['id']          ?? null;
$sentAt      = $ultima['created_at']  ?? null;
$approvedAt  = $ultima['approved_at'] ?? null;

// Banderas de estado
$pendiente  = ($status === 'pendiente');
$aprobada   = ($status === 'aprobado');
$rechazada  = ($status === 'rechazado');
?>

<link href="styles/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/fontawesome/css/all.css">

    <!-- Importar la fuente Poppins de Google Fonts -->
    <link href="styles/googlefonts.css" rel="stylesheet">
<link rel="stylesheet" href="styles/cart_widget.css">
  <div id="cart-wrapper">
  
    <!-- Icono flotante -->
    <div id="cart-icon">
      <i class="fas fa-download"></i>
      <?php if (count($cart) > 0): ?>
        <div id="cart-count"><?= count($cart) ?></div>
      <?php endif; ?>
    </div>

    <!-- Panel lateral -->
    <div id="cart-panel">
      <div class="panel-header">
        <h4>Mis Descargas</h4>
        <button id="close-cart">&times;</button>
      </div>

      <!-- Alertas según estado -->
      <?php if ($aprobada): ?>
        <div class="alert alert-success mb-3">
          ✅ Solicitud aprobada (<?= date('d/m/Y g:i a', strtotime($approvedAt)) ?>).
          <a href="endpoints/carrito_confirm.php?req=<?= $requestId ?>" class="alert-link">Ver archivos</a>
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

      <?php if (!empty($cart)): ?>
        <?php
        // Cargar detalles de archivos
        $ids    = implode(',', array_map('intval', $cart));
        $query  = "
        SELECT a.id, a.nombre, p.titulo AS proyecto
        FROM archivos a
        JOIN proyectos p ON a.proyecto_id = p.id
        WHERE a.id IN ($ids)
      ";
        $res    = $conexion->query($query);
        $files  = $res->fetch_all(MYSQLI_ASSOC);
        $files  = array_column($files, null, 'id');
        ?>
        <div id="cart-items">
          <?php foreach ($cart as $fid):
            if (!isset($files[$fid])) continue;
            $f = $files[$fid];
          ?>
            <div class="cart-item" data-id="<?= $fid ?>">
              <span>
                <?= htmlspecialchars($f['nombre']) ?>
                <small>(Proyecto: <?= htmlspecialchars($f['proyecto']) ?>)</small>
              </span>
              <button class="remove-item">&times;</button>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="actions">
          <button id="send-request" class="btn btn-primary"
            <?= ($pendiente || $aprobada)
              ? 'disabled title="No puedes enviar otra solicitud hasta que revisen la actual"'
              : '' ?>>
            Enviar a aprobación
          </button>
        </div>
      <?php else: ?>
        <p>Tu carrito está vacío.</p>
      <?php endif; ?>
    </div>
  </div>
  
  <script>
    

    function attachCartEvents() {
      // Reasignar eventos a los nuevos elementos
      document.querySelectorAll('.remove-item').forEach(btn =>
        btn.addEventListener('click', /*... mismo handler de eliminación ...*/ )
      );

      // Reasignar otros eventos necesarios...
    }
    // Mostrar/ocultar panel
    const icon = document.getElementById('cart-icon'),
      panel = document.getElementById('cart-panel'),
      closeBtn = document.getElementById('close-cart');

    icon.addEventListener('click', () => panel.classList.add('open'));
    closeBtn.addEventListener('click', () => panel.classList.remove('open'));

    // Eliminar item
    document.querySelectorAll('.remove-item').forEach(btn =>
      btn.addEventListener('click', e => {
        const id = e.target.closest('.cart-item').dataset.id;
        fetch('endpoints/remove_from_cart.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            file_id: id
          })
        }).then(() => location.reload());
      })
    );

    // Enviar solicitud
    const sendBtn = document.getElementById('send-request');
    if (sendBtn) sendBtn.addEventListener('click', () => {
      fetch('endpoints/create_request.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            archivos: <?= json_encode($cart) ?>
          })
        })
        .then(res => res.json())
        .then(d => {
          if (d.ok) location.href = 'endpoints/carrito_confirm.php?req=' + d.request_id;
        });
    });
  </script>
