<?php
session_start();
include 'panel.php';
include 'db/conn.php';
include_once('classes/Requests.class.php');
include_once('classes/Users.class.php');

// Verificar si el usuario es admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado");
}

// Manejo de aprobaciones o rechazos
if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['user_id'], $_POST['request_id'], $_POST['action'])) {

    $user_id    = intval($_POST['user_id']);
    $request_id = intval($_POST['request_id']);
    $action     = $_POST['action']; // "aprobar" o "rechazar"

    $new_status = $action === 'aprobar' ? 'activo' : 'rechazado';

    // 1) Actualizar usuarios
    $stmt = $conexion->prepare("UPDATE usuarios SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $user_id);
    $stmt->execute();
    $stmt->close();

    // 2) Marcar solicitud como aprobada
    $stmt = $conexion->prepare("UPDATE requests SET aproved = 1 WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->close();

    // Puedes redirigir o mostrar un mensaje de éxito
    //header('Location: solicitudes.php?msg=Solicitud procesada');
    //exit;
}
$solicitudes = Request::getR($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprobar Usuarios</title>
    <link href="styles/googlefonts.css" rel="stylesheet">
    <link href="styles/bootstrap.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 95%;
            max-width: 1400px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #007bff;
            font-weight: 600;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
          
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            
        }

        th {
            background-color: #007bff;
            color: white;
            font-weight: 500;
            font-size: 20px;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-weight: 500;
            transition: opacity 0.3s ease;
            margin: 2px;
        }

        .btn-approve {
            background-color: #28a745; /* Verde */
        }

        .btn-reject {
            background-color: #dc3545; /* Rojo */
        }

        .btn:hover {
            opacity: 0.8;
        }

        .volver {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
  

    <div class="container">
    <h2>Solicitudes de Registro</h2>
        <table>
            <thead>
                <tr>
                    <th>Elemento</th>
                    <th>Tipo de peticion</th>
                    <th>Fecha de generacion</th>
                    <th>Detalles</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($solicitudes as $sol): 
    $disabled = $sol['aproved'] == 1 ? 'disabled' : '';
?>
<tr>
  <td colspan="5">
    <form method="POST" class="request-form">
      <input type="hidden" name="user_id"    value="<?= htmlspecialchars($sol['id_element']) ?>">
      <input type="hidden" name="request_id" value="<?= htmlspecialchars($sol['id']) ?>">
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
          <strong>ID:</strong> <?= htmlspecialchars($sol['id_element']) ?><br>
          <strong>Tipo:</strong> <?= htmlspecialchars($sol['request_type']) ?><br>
          <strong>Fecha:</strong> <?= htmlspecialchars($sol['requested_at']) ?>
        </div>
        <div>
          <button 
            type="button" 
            class="btn btn-approve" 
            <?= $disabled ?> 
            data-action="aprobar"
          >Aprobar</button>
          <button 
            type="button" 
            class="btn btn-reject" 
            <?= $disabled ?> 
            data-action="rechazar"
          >Rechazar</button>
        </div>
      </div>
    </form>
  </td>
</tr>
<?php endforeach; ?>
            </tbody>
        </table>
        
        
    </div>  
    <script src="js/sweetalert2@11.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.request-form').forEach(form => {
    // buscamos ambos botones dentro del form
    const btnApprove = form.querySelector('.btn-approve');
    const btnReject  = form.querySelector('.btn-reject');

    [btnApprove, btnReject].forEach(btn => {
      btn.addEventListener('click', async (e) => {
        const action = btn.dataset.action;  // "aprobar" o "rechazar"
        const title  = action === 'aprobar'
                       ? '¿Aprobar solicitud?'
                       : '¿Rechazar solicitud?';
        const text   = action === 'aprobar'
                       ? 'Se actualizará el estado del usuario a activo.'
                       : 'Se actualizará el estado del usuario a rechazado.';
        const icon   = action === 'aprobar' ? 'success' : 'warning';
        const confirmText = action === 'aprobar' ? 'Sí, aprobar' : 'Sí, rechazar';

        const { isConfirmed } = await Swal.fire({
          title: title,
          text: text,
          icon: icon,
          showCancelButton: true,
          confirmButtonText: confirmText,
          cancelButtonText: 'Cancelar'
        });

        if (!isConfirmed) return;

        // Inyectamos el campo action al form
        let inputAction = form.querySelector('input[name="action"]');
        if (!inputAction) {
          inputAction = document.createElement('input');
          inputAction.type = 'hidden';
          inputAction.name = 'action';
          form.appendChild(inputAction);
        }
        inputAction.value = action;

        // Enviamos el form
        form.submit();
      });
    });
  });
});
</script>
    <?php require('includes/footer.php'); ?>
</body>
</html>

<?php $conexion->close(); ?>