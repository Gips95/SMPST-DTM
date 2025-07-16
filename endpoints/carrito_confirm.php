<?php
include '../db/conn.php';
session_start();

// Verificar sesión y permisos
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso denegado');
}

// Obtener y validar parámetro
$rid = filter_input(INPUT_GET, 'req', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$userId = $_SESSION['user_id'] ?? 0;

if (!$rid || !$userId) {
    header('HTTP/1.1 400 Bad Request');
    exit('Solicitud inválida');
}

// Consulta mejorada con manejo de errores
try {
  $stmt = $conexion->prepare("
      SELECT status, approved_at, created_at 
      FROM download_requests 
      WHERE id = ? 
      AND user_id = ?
  ");
    $stmt->bind_param('ii', $rid, $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        throw new Exception('Solicitud no encontrada');
    }

    $stmt->bind_result($status, $approved_at, $created_at);
    $stmt->fetch();
    $stmt->close();
   

} catch (Exception $e) {
    error_log('Error en solicitud: ' . $e->getMessage());
    exit('Error procesando la solicitud');
}

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Calcular expiración
$expiration_time = (new DateTime($created_at))->add(new DateInterval('PT24H'));
$expiration_js = $expiration_time->format(DateTime::ATOM);  // Formato ISO para JS
$statusMessages = [
  'aprobado' => [
      'icon' => '✅',
      'title' => 'Solicitud Aprobada',
      'message' => "Aprobada el " . date('d/m/Y', strtotime($approved_at)),
      'class' => 'alert-success'
  ],
  'pendiente' => [
      'icon' => '⏳',
      'title' => 'Solicitud en Revisión',
      'message' => 'En proceso de revisión',
      'class' => 'alert-warning'
  ],
  'rechazado' => [
      'icon' => '❌',
      'title' => 'Solicitud Rechazada',
      'message' => 'No cumplió con los requisitos',
      'class' => 'alert-danger'
  ]
];

// Asegurar valor por defecto
$currentStatus = $statusMessages[$status] ?? [
  'icon' => '❓',
  'title' => 'Estado Desconocido',
  'message' => 'Estado no reconocido',
  'class' => 'alert-secondary'
];
if ($status !== 'aprobado') {
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Estado de Solicitud</title>
        <link href="../styles/bootstrap.css" rel="stylesheet">
        <link rel="stylesheet" href="../styles/fontawesome/css/all.css">
        <style>
            .status-card {
                max-width: 600px;
                margin: 5rem auto;
                padding: 2rem;
                border-radius: 15px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                text-align: center;
            }
            .status-icon {
                font-size: 4rem;
                margin-bottom: 1.5rem;
            }
            .timeline {
                position: relative;
                margin: 2rem 0;
                padding-left: 30px;
            }
            .timeline::before {
                content: "";
                position: absolute;
                left: 7px;
                top: 0;
                width: 2px;
                height: 100%;
                background: #dee2e6;
            }
            .timeline-item {
                position: relative;
                margin-bottom: 1.5rem;
                padding-left: 30px;
            }
            .timeline-badge {
                position: absolute;
                left: 0;
                top: 0;
                width: 16px;
                height: 16px;
                border-radius: 50%;
                background: #fff;
                border: 3px solid;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="status-card alert-'.$currentStatus['class'].'">
                <div class="status-icon">'.$currentStatus['icon'].'</div>
                <h2 class="mb-3">'.$currentStatus['title'].'</h2>
                <p class="lead">'.$currentStatus['message'].'</p>
                
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-badge border-'.$currentStatus['class'].'"></div>
                        <h5>Creación de solicitud</h5>
                        <small class="text-muted">'.date('d/m/Y H:i', strtotime($created_at)).'</small>
                    </div>';
                    
    if ($status === 'pendiente') {
        echo '<div class="timeline-item">
                <div class="timeline-badge border-secondary"></div>
                <h5>En proceso de revisión</h5>
                <small class="text-muted">Nuestro equipo está verificando tu solicitud</small>
              </div>';
    }
    
    echo '</div>
            <div class="mt-4">
                <div class="alert alert-light border">
                    <i class="fas fa-clock me-2"></i>
                    Eliminación automática: 
                    <span class="expiration-time">'.$expiration_time->format('d/m/Y H:i').'</span>
                    <div class="countdown text-primary mt-1"></div>
                </div>
                <a href="../dashboard.php" class="btn btn-primary mt-3">
                    <i class="fas fa-arrow-left me-2"></i>Volver al panel
                </a>
            </div>
        </div>
        <script>
            // Script de countdown (similar al anterior)
        </script>
    </body>
    </html>';
    $conexion->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Descarga Autorizada</title>
    <link href="../styles/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/fontawesome/css/all.css">
    <style>
        .expiration-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
        }
        .countdown {
            font-weight: bold;
            color: #856404;
        }
        .status-alert {
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem auto;
            max-width: 600px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .alert-success { background: #d4edda; border: 2px solid #c3e6cb; color: #155724; }
        .alert-warning { background: #fff3cd; border: 2px solid #ffeeba; color: #856404; }
        .alert-danger { background: #f8d7da; border: 2px solid #f5c6cb; color: #721c24; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <?php
        $statusMessages = [
            'aprobado' => [
                'icon' => '✅',
                'title' => 'Solicitud Aprobada',
                'message' => "Aprobada el " . date('d/m/Y', strtotime($approved_at)),
                'class' => 'alert-success'
            ],
            'pendiente' => [
                'icon' => '⏳',
                'title' => 'Solicitud en Revisión',
                'message' => 'En proceso de revisión',
                'class' => 'alert-warning'
            ],
            'rechazado' => [
                'icon' => '❌',
                'title' => 'Solicitud Rechazada',
                'message' => 'No cumplió con los requisitos',
                'class' => 'alert-danger'
            ]
        ];

        $currentStatus = $statusMessages[$status] ?? $statusMessages['rechazado'];
        ?>
        
        <div class="status-alert <?= $currentStatus['class'] ?>">
            <i class="alert-icon"><?= $currentStatus['icon'] ?></i>
            <div>
                <h3><?= $currentStatus['title'] ?></h3>
                <p><?= $currentStatus['message'] ?></p>
                <div class="expiration-warning">
                    <i class="fas fa-clock"></i> 
                    Eliminación automática: 
                    <span class="expiration-time"><?= $expiration_time->format('d/m/Y H:i') ?></span>
                    <div class="countdown"></div>
                </div>
            </div>
        </div>

        <h2 class="mt-4">Descarga autorizada</h2>
        
        <?php if ($status !== 'aprobado'): ?>
            <div class="mb-4">
                <p>Antes de continuar, lee y acepta el uso responsable:</p>
                <textarea class="form-control" rows="6" readonly>
                    Los archivos son confidenciales. Su uso está sujeto a las políticas de la institución.
                    Cualquier uso no autorizado será penalizado.
                </textarea>
                <button id="aceptar" class="btn btn-primary mt-3">Acepto los términos</button>
            </div>
        <?php endif; ?>

        <div id="options" style="display: <?= $status === 'aprobado' ? 'block' : 'none' ?>;">
            <div class="mb-4">
                <a href="download_zip.php?req=<?= $rid ?>" class="btn btn-success mb-3">
                    <i class="fas fa-file-archive"></i> Descargar todo (ZIP)
                </a>

                <button class="btn btn-danger mb-3" onclick="eliminarSolicitud()">
                    <i class="fas fa-trash-alt"></i> Eliminar solicitud
                </button>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                Las solicitudes se eliminan automáticamente después de 24 horas.
            </div>

            <ul class="list-group">
                <?php
                try {
                  $stmt2 = $conexion->prepare("
                  SELECT a.id, a.nombre, p.titulo 
                  FROM download_request_items i
                  INNER JOIN download_requests r ON i.request_id = r.id
                  INNER JOIN archivos a ON a.id = i.archivo_id
                  INNER JOIN proyectos p ON p.id = a.proyecto_id
                  WHERE r.id = ? 
                  AND r.status = 'aprobado'
              ");
                    $stmt2->bind_param('i', $rid);
                    $stmt2->execute();
                    $result = $stmt2->get_result();

                    while ($f = $result->fetch_assoc()):
                ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <?= htmlspecialchars($f['nombre']) ?>
                            <small class="text-muted"><?= htmlspecialchars($f['titulo']) ?></small>
                        </span>
                        <a href="download_file.php?id=<?= $f['id'] ?>" 
                           class="btn btn-outline-primary btn-sm"
                           download>
                            <i class="fas fa-download"></i> Descargar
                        </a>
                    </li>
                <?php
                    endwhile;
                    $stmt2->close();
                } catch (Exception $e) {
                    error_log('Error al obtener archivos: ' . $e->getMessage());
                }
                $conexion->close();
                ?>
            </ul>
        </div>
    </div>

    <script>
        // Configuración de expiración
        const expiration = new Date('<?= $expiration_js ?>').getTime();
        
        function updateCountdown() {
            const now = Date.now();
            const diff = expiration - now;
            
            if (diff <= 0) {
                document.querySelectorAll('.countdown').forEach(el => {
                    el.textContent = 'Tiempo expirado';
                });
                document.querySelectorAll('.btn').forEach(btn => {
                    btn.disabled = true;
                    btn.title = 'Solicitud expirada';
                });
                return;
            }
            
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            
            document.querySelectorAll('.countdown').forEach(el => {
                el.textContent = `Tiempo restante: ${hours}h ${minutes}m`;
            });
        }

        // Actualizar cada segundo
        setInterval(updateCountdown, 1000);
        updateCountdown();

        // Manejar aceptación de términos
        document.getElementById('aceptar')?.addEventListener('click', () => {
            document.getElementById('options').style.display = 'block';
        });

        // Función de eliminación mejorada
        async function eliminarSolicitud() {
            const confirmMsg = `¿Eliminar esta solicitud?\n\nSe autoeliminará el ${new Date(expiration).toLocaleString()}`;
            
            if (!confirm(confirmMsg)) return;
            
            try {
                const response = await fetch('delete_request.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ request_id: <?= $rid ?> })
                });
                
                const data = await response.json();
                
                if (!response.ok) throw new Error(data.error || 'Error desconocido');
                
                window.location.href = '../dashboard.php';
            } catch (error) {
                console.error('Error:', error);
                alert('No se pudo eliminar: ' + error.message);
            }
        }
    </script>
</body>
</html>