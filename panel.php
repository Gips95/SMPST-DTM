<?php
// Inicia la sesión para todas las páginas que incluyan panel.php


// Obtener el nombre del archivo actual
$current_page = basename($_SERVER['PHP_SELF']);

// Lista de páginas para cada submenú
$proyecto_pages = ['Registrar_Proyecto.php', 'panel_admin.php'];
$usuario_pages = ['crear_usuarios.php', 'lista_estudiantes.php', 'lista_profesores.php'];
$busqueda_pages = ['buscar_proyectos.php'];

// Determinar estados activos para resaltar en el menú
$is_proyecto_active = in_array($current_page, $proyecto_pages);
$is_usuario_active = in_array($current_page, $usuario_pages);
$is_busqueda_active = in_array($current_page, $busqueda_pages);

// Incluir el script de notificaciones si el usuario es admin
if ($_SESSION['rol'] === 'admin'):
    echo '<script src="js/download_notifications.js"></script>';
endif;

// El inicio de las etiquetas HTML completas se mueve aquí
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DRT</title> <!-- El título puede ser dinámico o genérico aquí -->
    
    <!-- Incluye el nuevo CSS para el panel lateral -->
    <link rel="stylesheet" href="styles/panel.css"> 
    <link href="styles/googlefonts.css" rel="stylesheet">
    <link href="styles/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/fontawesome/css/all.css">
    <!-- También incluye los CSS globales o de layout aquí, como dash.css -->
    <link rel="stylesheet" href="styles/dash.css"> 
    <link rel="stylesheet" href="styles/jquery.dataTables.min.css">
    <link rel="stylesheet" href="styles/buttons.dataTables.min.css">

    <!-- jQuery debe cargarse en el head o al inicio del body -->
    <script src="js/jquery.js"></script> 
    <!-- Otros DataTables JS si son necesarios globalmente -->
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.buttons.min.js"></script>
    <script src="js/buttons.html5.min.js"></script>
    <script src="js/buttons.print.min.js"></script>
    <script src="js/pdfmake.min.js"></script>
    <script src="js/vfs_fonts.js"></script>
    <script src="js/buttons.html5.min.js"></script>
</head>
<body>
    <!-- Menú lateral (sidebar) -->
    <div class="sidebar">
        <h2>
            <a href="dashboard.php" id="dashboard-link">
                <i class="fas fa-bars" id="menu-icon"></i> Menú Principal
            </a>
        </h2>

        <?php if ($_SESSION['rol'] === 'profesor' || $_SESSION['rol'] === 'admin'): ?>
            <a href="#" class="has-submenu <?= $is_proyecto_active ? 'active' : '' ?>" 
               onclick="toggleSubmenu(this)">
                <i class="fas fa-folder-open"></i> Administrar Proyectos
            </a>
            <div class="submenu" style="<?= $is_proyecto_active ? 'display: block;' : 'display: none;' ?>">
                <?php if ($_SESSION['rol'] === 'profesor' || $_SESSION['rol'] === 'admin'): ?>
                    <a href="<?= isset($_SESSION['user']) ? 'Registrar_Proyecto.php' : 'login.php?return_to=Registrar_Proyecto.php' ?>" 
                       class="<?= $current_page === 'Registrar_Proyecto.php' ? 'active' : '' ?>">
                        <i class="fas fa-plus-circle"></i> Registrar Proyecto
                    </a>
                <?php endif; ?>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <a href="panel_admin.php" 
                       class="<?= $current_page === 'panel_admin.php' ? 'active' : '' ?>">
                        <i class="fas fa-tasks"></i> Gestionar Proyectos
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <a href="#" class="has-submenu <?= $is_usuario_active ? 'active' : '' ?>" 
               onclick="toggleSubmenu(this)">
                <i class="fas fa-users-cog"></i> Administrar Usuarios
            </a>
            <div class="submenu" style="<?= $is_usuario_active ? 'display: block;' : 'display: none;' ?>">
                <a href="crear_usuarios.php" 
                   class="<?= $current_page === 'crear_usuarios.php' ? 'active' : '' ?>">
                    <i class="fas fa-user-plus"></i> Crear Usuarios
                </a>
                <a href="lista_estudiantes.php" class="<?= $current_page === 'lista_estudiantes.php' ? 'active' : '' ?>">
                    <i class="fas fa-user-graduate"></i> Estudiantes
                </a>
                <a href="lista_profesores.php" class="<?= $current_page === 'lista_profesores.php' ? 'active' : '' ?>">
                    <i class="fas fa-chalkboard-teacher"></i> Docentes
                </a>
            </div>
        <?php endif; ?>

        <a href="#" class="has-submenu <?= $is_busqueda_active ? 'active' : '' ?>" 
           onclick="toggleSubmenu(this)">
            <i class="fas fa-search-plus"></i> Búsqueda Avanzada
        </a>
        <div class="submenu" style="<?= $is_busqueda_active ? 'display: block;' : 'display: none;' ?>">
            <a href="buscar_proyectos.php" 
               class="<?= $current_page === 'buscar_proyectos.php' ? 'active' : '' ?>">
                <i class="fas fa-filter"></i> Filtrar Proyectos
            </a>
        </div>
        <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'profesor'): ?>
        <a href="reportes.php" class="tool-item <?= $current_page === 'reportes.php' ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list"></i> Reportes
                </a><?php endif; ?>


        <!-- Sección de notificaciones y herramientas -->
        <div class="sidebar-tools">
            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <a href="visualizar_logs.php" class="tool-item <?= $current_page === 'visualizar_logs.php' ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list"></i> Registros
                </a>
                
                <a href="solicitudes.php" class="tool-item <?= $current_page === 'solicitudes.php' ? 'active' : '' ?>" id="solicitudes-link">
                    <i class="fas fa-inbox"></i> Solicitudes
                    <span class="notificacion-badge" id="solicitudes-badge"></span>
                </a>
                <a href="solicitudes_descargas.php" class="tool-item <?= $current_page === 'solicitudes_descargas.php' ? 'active' : '' ?>">
                    <i class="fas fa-download"></i> Descargas
                    <span class="notificacion-badge" id="pending-badge" aria-live="polite"></span>
                </a>
            <?php endif; ?>

            <?php if ($_SESSION['rol'] === 'estudiante'): ?>
                <a href="view_cart.php" class="tool-item <?= $current_page === 'view_cart.php' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i> Carrito
                    <span id="badge-cart">
                        <?php 
                        $cart = $_SESSION['cart'] ?? []; 
                        echo count($cart); 
                        ?>
                    </span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Sección de usuario -->
        <div class="user-section">
            <?php if (isset($_SESSION['user'])): ?>
                <a href="endpoints/logout.php" class="user-logout">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            <?php else: ?>
                <a href="login.php" class="user-login">
                    <i class="fas fa-sign-in-alt"></i> Acceder
                </a>
            <?php endif; ?>
        </div>
    </div> <!-- Cierre correcto del sidebar -->

    <!-- Scripts compartidos por todas las páginas que usan este panel -->
    <script src="js/sidebar.js"></script> <!-- Si tienes un JS para el sidebar -->
    <script src="js/global.js"></script>
    <script src="js/solicitud.js"></script>
    
    <script>
    // Función global para submenús
    function toggleSubmenu(element) {
        const $submenu = $(element).next(".submenu");
        const isOpening = !$(element).hasClass("active");
        
        // Cerrar otros submenús solo si se está abriendo uno nuevo
        if (isOpening) {
            $(".has-submenu").not(element).removeClass("active");
            $(".submenu").not($submenu).slideUp(200);
        }
        
        $(element).toggleClass("active");
        $submenu.slideToggle(200);
    }

    $(document).ready(function() {
        // Inicializar submenús activos
        $(".has-submenu.active").each(function() {
            $(this).next(".submenu").show();
        });
        
        // Manejar clics en enlaces
        $(".sidebar a").click(function(e) {
            // Solo manejar enlaces que no sean para submenús
            if (!$(this).hasClass("has-submenu") && !$(this).parent().hasClass("submenu")) {
                // Marcar como activo
                $(".sidebar a").removeClass("active");
                $(this).addClass("active");
                
                // Marcar el padre como activo si está en un submenú
                if ($(this).parent().hasClass("submenu")) {
                    $(this).closest(".submenu").prev(".has-submenu").addClass("active");
                }
                
                // Cerrar sidebar en móviles
                if ($(window).width() <= 768) {
                    $(".sidebar").removeClass("active");
                    $(".main-content").removeClass("menu-active");
                }
            }
        });
        
        // Marcar página actual en dashboard
        const currentPage = '<?= $current_page ?>';
        if (currentPage === 'dashboard.php') {
            $("#dashboard-link").addClass("active");
        }
    });
    </script>
    <!-- Aquí es donde las páginas específicas insertarán su contenido -->
    <!-- El cierre de </body> y </html> se hará en el archivo de la página específica,
         después de que todo el contenido principal se haya insertado. -->
