<?php
session_start();
include 'db/conn.php';
include 'panel.php';

// Validar que el usuario sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Se requiere rol de administrador.']);
    exit;
}

// Manejo de la acción de aprobar/rechazar desde el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Elimina la validación de isset($_POST['request_id'], $_POST['action'])
    // Recibir los datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Error al decodificar los datos JSON.']);
        exit;
    }

    if (!isset($data['request_id'], $data['action'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Faltan datos en la solicitud.']);
        exit;
    }

    // Validar conexión a la base de datos
    if (!$conexion) {
        http_response_code(500); // Internal Server Error
        error_log("Error: No se pudo conectar a la base de datos.");
        echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
        exit;
    }

    $request_id = intval($data['request_id']);
    $new_status = ($data['action'] === 'aprobar') ? 'aprobado' : 'rechazado';

    // Validar sesión
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401); // Unauthorized
        error_log("Error: El ID del administrador no está definido en la sesión.");
        echo json_encode(['success' => false, 'message' => 'Sesión inválida. Por favor, inicie sesión nuevamente.']);
        exit;
    }

    // Depuración: Verificar valores
    error_log("Request ID: " . $request_id);
    error_log("Action: " . $new_status);
    error_log("Admin ID: " . $_SESSION['user_id']); // Agregar esta línea

    $sql = "UPDATE download_requests 
            SET status = ?, approved_at = NOW(), admin_id = ? 
            WHERE id = ?";

    $stmt = $conexion->prepare($sql);

    if ($stmt === false) {
        http_response_code(500); // Internal Server Error
        error_log("Error al preparar la consulta: " . $conexion->error);
        echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta. Por favor, intente nuevamente.']);
        exit;
    }

    $stmt->bind_param("sii", $new_status, $_SESSION['user_id'], $request_id);

    if ($stmt->execute()) {
        // Verificar si la actualización fue exitosa
        if ($stmt->affected_rows === 0) {
            http_response_code(404); // Not Found
            error_log("Error: No se actualizó ninguna fila. Request ID: " . $request_id);
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la solicitud. Verifique el ID.']);
            exit;
        }
    } else {
        http_response_code(500); // Internal Server Error
        error_log("Error al ejecutar la consulta: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Error al procesar la solicitud. Por favor, intente nuevamente.']);
        exit;
    }

    $stmt->close();

    // Enviar respuesta JSON de éxito
    echo json_encode(['success' => true, 'message' => 'Solicitud ' . $new_status . ' con éxito.']);
    exit;
}

// Consulta para obtener las solicitudes pendientes con sus proyectos y archivos asociados
$sql = "SELECT 
    r.id,
    r.user_id,
    r.created_at,
    u.user AS username,
    u.email,
    GROUP_CONCAT(p.titulo ORDER BY ri.id SEPARATOR '||') AS proyectos,
    GROUP_CONCAT(a.ruta ORDER BY ri.id SEPARATOR '||') AS archivos
FROM download_requests r
LEFT JOIN usuarios u ON u.id = r.user_id
LEFT JOIN download_request_items ri ON ri.request_id = r.id
LEFT JOIN archivos a ON a.id = ri.archivo_id
LEFT JOIN proyectos p ON p.id = a.proyecto_id
WHERE r.status = 'pendiente'
GROUP BY r.id
ORDER BY r.created_at DESC";

$stmt = $conexion->prepare($sql);

if ($stmt === false) {
    error_log("Error al preparar la consulta: " . $conexion->error);
    exit("Error al preparar la consulta. Por favor, intente nuevamente.");
}

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $solicitudes = $result->fetch_all(MYSQLI_ASSOC);
    $num_solicitudes = count($solicitudes);
    $result->free();
} else {
    error_log("Error al ejecutar la consulta: " . $stmt->error);
    exit("Error al cargar las solicitudes. Por favor, intente nuevamente."); // Importante: Mostrar un mensaje al usuario
}

$stmt->close();

// Incluir el panel de navegación o cabecera (si existe)
// include 'panel.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes de Descarga</title>

    <link href="styles/googlefonts.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/fontawesome/css/all.css">

    <script src="js/sweetalert2@11.js"></script>

    <style>
        /* Estilos CSS unificados */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e9ecef;
            color: #333;
            line-height: 1.6;
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
            font-size: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
           
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
            font-weight: 500;
            letter-spacing: 0.5px;
            font-size: 20px;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        tr.request-row {
            cursor: pointer;
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
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-approve {
            background-color: #28a745;
        }

        .btn-reject {
            background-color: #dc3545;
        }

        .btn:hover {
            opacity: 0.8;
        }

        .volver {
            text-align: center;
            margin-top: 30px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #d1ecf1;
            color: #0c5460;
            text-align: center;
            font-weight: 500;
        }

        /* Estilos para el modal */
        .request-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            max-width: 800px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            max-height: 90vh;
            overflow-y: auto;
            font-family: 'Poppins', sans-serif;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            color: #007bff;
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .modal-content {
            padding: 1.5rem;
        }

        .project-file-group {
            margin: 1rem 0;
            padding: 1rem;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            margin: 0.5rem 0;
            background: #f8f9fa;
            border-radius: 6px;
        }

        /* Estilos para botones de SweetAlert */
        .swal-confirm-approve {
            background-color: #28a745 !important;
        }

        .swal-confirm-reject {
            background-color: #dc3545 !important;
        }

        .swal-cancel {
            background-color: #6c757d !important;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 20px auto;
            }

            .table-responsive {
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Solicitudes de Descarga</h2>

        <?php if ($num_solicitudes > 0): ?>
            <div class="alert">
                <i class="fas fa-info-circle"></i> Tienes <?= $num_solicitudes ?> solicitud(es) pendiente(s) de revisión.
            </div>
        <?php else: ?>
            <div class="alert" style="background-color: #e2e3e5; color: #383d41;">
                No hay solicitudes pendientes por el momento.
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes as $sol): ?>
                        <tr class="request-row"
                            data-id="<?= $sol['id'] ?>"
                            data-usuario="<?= htmlspecialchars($sol['username']) ?>"
                            data-email="<?= htmlspecialchars($sol['email']) ?>"
                            data-fecha="<?= date('d/m/Y H:i', strtotime($sol['created_at'])) ?>"
                            data-proyectos="<?= htmlspecialchars($sol['proyectos']) ?>"
                            data-archivos="<?= htmlspecialchars($sol['archivos']) ?>">

                            <td><?= $sol['id'] ?></td>
                            <td><?= htmlspecialchars($sol['username']) ?></td>
                            <td><?= htmlspecialchars($sol['email']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($sol['created_at'])) ?></td>
                            <td>
                                <form method="POST" action="solicitudes_descargas.php" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?= $sol['id'] ?>">
                                    <button type="submit" name="action" value="aprobar" class="btn btn-approve">
                                        <i class="fas fa-check"></i> Aprobar
                                    </button>
                                </form>
                                <form method="POST" action="solicitudes_descargas.php" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?= $sol['id'] ?>">
                                    <button type="submit" name="action" value="rechazar" class="btn btn-reject">
                                        <i class="fas fa-times"></i> Rechazar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        
    </div>

    <div class="modal-overlay" id="modalOverlay"></div>
    <div class="request-modal" id="requestModal">
        <div class="modal-header">
            <h3>Detalles de la Solicitud</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-content" id="detallesContenido">
        </div>
    </div>

    <script>
        // --- SCRIPT PARA MANEJAR EL MODAL ---
        document.querySelectorAll('.request-row').forEach(row => {
            row.addEventListener('click', (e) => {
                // Evitar abrir el modal si se hace clic en un botón dentro de la fila
                if (e.target.closest('.btn')) {
                    return;
                }

                const data = row.dataset;
                const proyectos = data.proyectos.split('||');
                const archivos = data.archivos.split('||');

                const contenido = document.createElement('div');

                // Agregar información básica de la solicitud al modal
                const solicitudInfo = document.createElement('div');
                solicitudInfo.style.marginBottom = '2rem';
                solicitudInfo.innerHTML = `
                    <p><strong>ID de Solicitud:</strong> ${data.id}</p>
                    <p><strong>Usuario:</strong> ${data.usuario}</p>
                    <p><strong>Email:</strong> ${data.email}</p>
                    <p><strong>Fecha:</strong> ${data.fecha}</p>
                `;
                contenido.appendChild(solicitudInfo);

                // Agregar los proyectos y archivos asociados
                proyectos.forEach((proyecto, index) => {
                    const grupo = document.createElement('div');
                    grupo.className = 'project-file-group';
                    const nombreArchivo = archivos[index] ? archivos[index].split('/').pop() : 'Archivo no disponible';
                    const rutaArchivo = archivos[index] || '';

                    grupo.innerHTML = `
                        <div>
                            <strong style="color: #007bff;">Proyecto:</strong>
                            <span>${proyecto}</span>
                        </div>
                        <div class="file-item">
                            <span><i class="fas fa-file" style="margin-right: 8px; color: #6c757d;"></i>${nombreArchivo}</span>
                            ${rutaArchivo ?
                                `<a href="${escapeHTML(rutaArchivo)}" class="btn btn-approve btn-sm" download>
                                    <i class="fas fa-download"></i> Descargar
                                </a>` : ''
                            }
                        </div>
                    `;
                    contenido.appendChild(grupo);
                });

                document.getElementById('detallesContenido').innerHTML = '';
                document.getElementById('detallesContenido').appendChild(contenido);

                document.getElementById('modalOverlay').style.display = 'block';
                document.getElementById('requestModal').style.display = 'block';
            });
        });

        function closeModal() {
            document.getElementById('modalOverlay').style.display = 'none';
            document.getElementById('requestModal').style.display = 'none';
        }

        document.getElementById('modalOverlay').addEventListener('click', closeModal);

        // --- SCRIPT PARA CONFIRMAR ACCIONES CON SWEETALERT ---
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Evita el envío inmediato del formulario
                console.log("Formulario submit interceptado"); // Depuración: Verificar si el evento se está interceptando

                const requestId = this.querySelector('input[name="request_id"]').value;
                const action = this.querySelector('button[name="action"]').value;
                const actionText = action === 'aprobar' ? 'Aprobar' : 'Rechazar';

                console.log("Request ID:", requestId, "Action:", action); // Depuración: Verificar los valores

                Swal.fire({
                    title: `¿Estás seguro que deseas ${actionText} esta solicitud?`,
                    text: "Esta acción no se puede deshacer.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: `Sí, ${actionText}`,
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log("Usuario confirmó la acción"); // Depuración: Verificar si el usuario confirmó

                        // Enviar la solicitud POST con fetch
                        fetch('solicitudes_descargas.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        request_id: requestId,
        action: action
    })
})
.then(response => response.text())  // Primero obtener como texto
.then(text => {
    // Buscar el inicio del JSON (última llave { del documento)
    const jsonStart = text.lastIndexOf('{');
    
    if (jsonStart === -1) {
        throw new Error('No se encontró JSON en la respuesta');
    }
    
    // Extraer solo la parte JSON
    const jsonString = text.substring(jsonStart);
    
    // Intentar parsear el JSON
    return JSON.parse(jsonString);
})
.then(data => {
    // Manejar la respuesta JSON normalmente
    if (data.success) {
        Swal.fire('Éxito', data.message, 'success').then(() => {
            window.location.reload();
        });
    } else {
        Swal.fire('Error', data.message, 'error');
    }
})
.catch(error => {
    console.error('Error:', error);
    Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
});
                    } else {
                        console.log("Usuario canceló la acción"); // Depuración: Verificar si el usuario canceló
                    }
                });
            });
        });
        // Función para escapar contenido HTML dinámico
        function escapeHTML(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    </script>
</body>

</html>