<?php
session_start();
include 'db/conn.php';
include 'db/logs.php';
include 'cart_widget.php';
include_once('classes/Projects.class.php');
include_once('classes/Files.class.php');
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Debes iniciar sesión para ver este proyecto";
    header("Location: /DRT/login.php?return_to=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

if (isset($_GET['id'])) {
    try {
        $proyecto_id = intval($_GET['id']); // Convertir a entero para seguridad
        $proyecto = Project::getProject($proyecto_id, $conexion);
        $archivos = ProjectFile::getProjectFiles($proyecto_id, $conexion);

        $documentos = [];
        $referencias = [];

        foreach ($archivos as $archivo) {
            if ($archivo['tipo'] == "documento") {
                $documentos[] = $archivo;
            } elseif ($archivo['tipo'] == "referencia") {
                $referencias[] = $archivo;
            }
        }
    } catch (Exception $e) {
        echo $e->getMessage();
        exit();
    }

    function ChangeToGeneralRoute($route)
    {
        $ruta_original = $route;
        $ruta_dividida = explode('/', $ruta_original, 2);
        $nueva_ruta = '/DRT/' . $ruta_dividida[1];
        return $nueva_ruta;
    }
}


?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Proyecto</title>
    <link href="styles/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/fontawesome/css/all.css">

    <!-- Importar la fuente Poppins de Google Fonts -->
    <link href="styles/googlefonts.css" rel="stylesheet">
    <!-- Dropzone.js -->
    <link rel="stylesheet" href="styles/dropzone.css">
    <style>
        /* Estilos actualizados */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .main-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }

        .project-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }

        .project-title {
            color: #2c3e50;
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 1rem;
        }

        .metadata-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .metadata-item {
            margin-bottom: 0.8rem;
            font-size: 0.95rem;
        }

        .metadata-label {
            color: #007bff;
            font-weight: 500;
            min-width: 160px;
            display: inline-block;
        }

        .file-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            transition: transform 0.2s;
            margin-bottom: 1rem;
        }

        .file-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .badge-count {
            font-size: 0.8em;
            margin-left: 0.5rem;
        }

        .metric-badge {
            background: #f1f8ff;
            color: #007bff;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin-right: 1rem;
        }
        /* Agregar estas transiciones */
.add-cart {
    transition: all 0.3s ease;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

.add-cart.added {
    animation: bounce 0.5s ease;
    background-color: #28a745 !important;
    border-color: #28a745 !important;
}
    </style>
</head>

<body>
    <div class="main-container">
        <!-- Encabezado estilo ResearchGate -->
        <div class="project-header">
            <h1 class="project-title"><?php echo htmlspecialchars($proyecto["titulo"]); ?></h1>

            <div class="d-flex align-items-center mb-3">
                <div class="me-3">
                    <i class="fas fa-user-circle fa-2x text-secondary"></i>
                </div>
                <div>
                    <div class="fw-500">Autores:</div>
                    <div class="text-muted"><?php echo htmlspecialchars($proyecto["autores"]); ?></div>
                </div>
            </div>

            <!-- Métricas (puedes implementar la lógica posteriormente) -->


            <!-- Sección de Metadatos -->
            <div class="metadata-card">
                <div class="metadata-item">
                    <span class="metadata-label">Fecha de publicación:</span>
                    <?php echo htmlspecialchars($proyecto["fecha"]); ?>
                </div>
                <div class="metadata-item">
                    <span class="metadata-label">Línea de investigación:</span>
                    <?php echo htmlspecialchars($proyecto["linea_investigacion"]); ?>
                </div>
                <div class="metadata-item">
                    <span class="metadata-label">Institución:</span>
                    <?php echo htmlspecialchars($proyecto["ente"]); ?>
                </div>
                <div class="metadata-item">
                    <span class="metadata-label">Tutor(es):</span>
                    <?php echo htmlspecialchars($proyecto["tutor"]); ?>
                </div>
            </div>

            <!-- Descripción tipo Abstract -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary mb-3">Descripción del Proyecto</h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($proyecto["descripcion"])); ?></p>
                </div>
            </div>

            <!-- Archivos con nuevo diseño -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-4" id="filesTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents">
                                <i class="fas fa-file-pdf me-2"></i>Documentos
                                <span class="badge-count"><?php echo count($documentos); ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="references-tab" data-bs-toggle="tab" data-bs-target="#references">
                                <i class="fas fa-book me-2"></i>Referencias
                                <span class="badge-count "><?php echo count($referencias); ?></span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
    <!-- Documentos -->
    <div class="tab-content">
                    <!-- Documentos -->
                    <div class="tab-pane fade show active" id="documents">
                        <?php if (!empty($documentos)): ?>
                            <?php foreach ($documentos as $archivo): 
                                $ext = strtolower(pathinfo($archivo['nombre'], PATHINFO_EXTENSION));
                                $icon = 'fa-file'; $color='text-secondary';
                                switch ($ext) {
                                    case 'pdf': $icon='fa-file-pdf'; $color='text-danger'; break;
                                    case 'doc': case 'docx': $icon='fa-file-word'; $color='text-primary'; break;
                                    case 'ppt': case 'pptx': $icon='fa-file-powerpoint'; $color='text-warning'; break;
                                    case 'xls': case 'xlsx': $icon='fa-file-excel'; $color='text-success'; break;
                                    case 'txt': $icon='fa-file-alt'; break;
                                    case 'zip': case 'rar': case '7z': $icon='fa-file-archive'; break;
                                }
                            ?>
                                <div class="file-card p-3 mb-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas <?= $icon ?> <?= $color ?> me-3"></i>
                                        <span><?= htmlspecialchars($archivo['nombre']) ?></span>
                                        <small class="text-muted ms-2"><?= formatSizeUnits($archivo['size']) ?></small>
                                    </div>
                                    <div>
                                    <?php if ($_SESSION['rol'] === 'estudiante' ): ?>
                                        <button class="btn btn-sm btn-outline-success add-cart" data-file-id="<?= $archivo['id'] ?>">
                                            <i class="fas fa-cart-plus me-1"></i>Añadir
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($_SESSION['rol'] === 'profesor' || $_SESSION['rol'] === 'admin'): ?>
                                        <a href="endpoints/download_handler.php?file=<?= urlencode($archivo['ruta']) ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download me-1"></i>Descargar
                                        </a>
                                        <?php endif; ?>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-light">No hay documentos disponibles</div>
                        <?php endif; ?>
                    </div>

                    <!-- Referencias -->
                    <div class="tab-pane fade" id="references">
                        <?php if (!empty($referencias)): ?>
                            <?php foreach ($referencias as $archivo): 
                                /* mismo ícono */
                            ?>
                                <div class="file-card p-3 mb-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas <?= $icon ?> <?=$color ?> me-3"></i>
                                        <span><?= htmlspecialchars($archivo['nombre']) ?></span>
                                        <small class="text-muted ms-2"><?= formatSizeUnits($archivo['size']) ?></small>
                                    </div>
                                    <div>
                                    <?php if ($_SESSION['rol'] === 'estudiante' ): ?>
                                        <button class="btn btn-sm btn-outline-success add-cart" data-file-id="<?= $archivo['id'] ?>">
                                            <i class="fas fa-cart-plus me-1"></i>Añadir
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($_SESSION['rol'] === 'profesor' || $_SESSION['rol'] === 'admin'): ?>
                                        <a href="endpoints/download_handler.php?file=<?= urlencode($archivo['ruta']) ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download me-1"></i>Descargar
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-light">No hay referencias disponibles</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
 <!-- Cierre final de tab-content -->

        <!-- Sección de comentarios (puedes implementar después) -->
  <!--      <div class="card mt-4 border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-primary mb-4"><i class="fas fa-comments me-2"></i>Discusión (3)</h5>
                <div class="alert alert-info">Funcionalidad de comentarios en desarrollo</div>
            </div>
        </div>-->

       
    </div>
    <div class="mt-4 text-center">
            <a href="dashboard.php" class="btn btn-primary px-4">
                <i class="fas fa-arrow-left me-2"></i>Volver al listado
            </a>
        </div>

    <script src="js/jquery.js"></script>
    <script src="js/popper.js"></script>
    <script src="js/bootstrap.js"></script>

    <script src="js/dropzone.js"></script>
    <script>
        function initializeCartWidgetEvents() {
  const icon    = document.getElementById('cart-icon');
  const panel   = document.getElementById('cart-panel');
  const closeBtn= document.getElementById('close-cart');

  if (icon && panel && closeBtn) {
    icon.addEventListener('click', () => {
      refreshCartPanel(); // Actualiza contenido
      panel.classList.add('open'); // Muestra el panel
    });

    closeBtn.addEventListener('click', () => panel.classList.remove('open'));
  }

  attachCartEvents();
}
        function updateCartCount(count) {
  const cartCount = document.getElementById('cart-count');
  if (count > 0) {
    if (!cartCount) {
      const countElement = document.createElement('div');
      countElement.id = 'cart-count';
      countElement.textContent = count;
      document.getElementById('cart-icon').appendChild(countElement);
    } else {
      cartCount.textContent = count;
    }
  } else if (cartCount) {
    cartCount.remove();
  }
}


function attachCartEvents() {
  // Reasignar eventos a los nuevos elementos
  document.querySelectorAll('.remove-item').forEach(btn =>
    btn.addEventListener('click', /*... mismo handler de eliminación ...*/)
  );
  
  // Reasignar otros eventos necesarios...
}
         // actualizar contador en widget
         document.querySelectorAll('.add-cart').forEach(btn => {
    btn.addEventListener('click', async () => {
        const fid = btn.dataset.fileId;
        
        btn.disabled = true;
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Añadiendo...';

        try {
            const response = await fetch('endpoints/add_to_cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'file_id=' + fid
            });
            
            const data = await response.json();
            
            if (data.reload) {
                // Recarga suave con comportamiento de caché controlado
                window.location.reload(true);
            } else {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            }

        } catch (error) {
            console.error('Error:', error);
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }
    });
});
async function refreshCartPanel() {
    try {
        const response = await fetch('cart_widget.php?_=' + Date.now()); // 👈 forzar que no cachee
        const html = await response.text();
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;

        const newPanel = tempDiv.querySelector('#cart-panel');
        document.getElementById('cart-panel').replaceWith(newPanel);

        initializeCartWidgetEvents(); // volver a conectar eventos
    } catch (error) {
        console.error('Error actualizando carrito:', error);
    }
}

        // Configurar Dropzone
        Dropzone.autoDiscover = false;
        const dz = new Dropzone('.dropzone', {
            paramName: "file",
            maxFilesize: 25, // MB
            acceptedFiles: ".pdf,.doc,.docx,.xls,.xlsx",
            dictDefaultMessage: "Arrastra archivos aquí",
            init: function() {
                this.on("success", function(file, response) {
                    setTimeout(() => location.reload(), 1500);
                });
            }
        });
        if (data.is_duplicate) {
    btn.innerHTML = '<i class="fas fa-exclamation-circle"></i> Ya en carrito';
    setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-cart-plus me-1"></i>Añadir';
        btn.disabled = false;
    }, 2000);
}
    </script>
    <?php
    function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    $conexion->close();

    /*
// Consultar datos del proyecto
    $sqlProyecto = "SELECT * FROM proyectos WHERE id = ?";
    $stmtProyecto = $conexion->prepare($sqlProyecto);
    $stmtProyecto->bind_param("i", $proyecto_id);
    $stmtProyecto->execute();
    $resultado = $stmtProyecto->get_result();
    $proyecto = $resultado->fetch_assoc();

    if (!$proyecto) {
        echo "Proyecto no encontrado.";
        exit();
    }

    // Consultar archivos asociados y clasificarlos
    $sqlArchivos = "SELECT * FROM archivos WHERE proyecto_id = ?";
    $stmtArchivos = $conexion->prepare($sqlArchivos);
    $stmtArchivos->bind_param("i", $proyecto_id);
    $stmtArchivos->execute();
    $resultadoArchivos = $stmtArchivos->get_result();

    $documentos = [];
    $referencias = [];

    while ($archivo = $resultadoArchivos->fetch_assoc()) {
        if ($archivo['tipo'] == "documento") {
            $documentos[] = $archivo;
        } elseif ($archivo['tipo'] == "referencia") {
            $referencias[] = $archivo;
        }
    }
} else {
    echo "ID de proyecto no válido.";
    exit();
}
*/
    ?>

</body>

</html>