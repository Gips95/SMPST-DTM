<?php
session_start();

$_SESSION['user'] = $_SESSION['user'] ?? null;
$_SESSION['rol'] = $_SESSION['rol'] ?? 'invitado';
include 'db/conn.php';
include 'endpoints/manage_requests.php';
if ($_SESSION['rol'] === 'estudiante'):
    include 'cart_widget.php';
endif;
if ($_SESSION['rol'] === 'admin'):
    echo '<script src="js/download_notifications.js"></script>';
    endif;

include 'panel.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Página Principal</title>
 
    <link rel="stylesheet" href="styles/fontawesome/css/all.css">
<!-- DataTables CSS -->
<link rel="stylesheet" href="styles/jquery.dataTables.min.css">
<link rel="stylesheet" href="styles/buttons.dataTables.min.css">
<link rel="stylesheet" href="styles/dash.css">
<style>
    /* Styles for DataTables filter/search input (hidden as per previous request) */
.dataTables_filter label {
  display: none; 
}
    </style>
<!-- jQuery -->
<script src="js/jquery.js"></script>

<!-- DataTables JS -->
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.buttons.min.js"></script>
<script src="js/buttons.html5.min.js"></script>
<script src="js/buttons.print.min.js"></script>

<script src="js/pdfmake.min.js"></script>
<script src="js/vfs_fonts.js"></script>

<script src="js/buttons.html5.min.js"></script>
</head>

<body>
    <!-- Menú lateral modernizado -->


    </div>

    <!-- Contenido principal actualizado -->
    <div class="main-content">
        <!-- Header superior -->
        <div class="content-header">
        <div class="content-left">  <!-- Nuevo contenedor para el contenido de la izquierda -->
            <!-- Mensajes de estado -->
            <?php if (!isset($_SESSION['user'])): ?>
                <div class="notification-card warning">
                    <i class="fas fa-exclamation-circle"></i>
                    <div class="notification-content">
                        <h3>Acceso limitado</h3>
                        <p>Inicia sesión para acceder a todas las funciones del sistema.</p>
                    </div>
                    <a href="login.php" class="btn login-prompt-btn">Iniciar Sesión</a>
                </div>
            <?php endif; ?>

            <?php if ($_SESSION['rol'] === 'estudiante'): ?>
                <div class="notification-card info">
                    <i class="fas fa-info-circle"></i>
                    <div class="notification-content">
                        <h3>Acceso de Estudiante</h3>
                        <p>Para solicitar permisos adicionales, contacta al administrador del sistema.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="search-container">
                <input type="text" id="buscar" placeholder="Buscar proyectos..."
                    class="modern-search" onkeyup="buscarProyectos()">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>

        <div class="user-status">
            <?php if (isset($_SESSION['user'])): ?>
                <div class="user-badge">
                    <i class="fas fa-user-circle"></i>
                    <div class="user-info">
                        <span class="username"><?= $_SESSION['user'] ?></span>
                        <span class="user-id">ROL: <?= $_SESSION['rol'] ?? 'N/A' ?></span>
                        <span class="user-id">ID: <?= $_SESSION['user_id'] ?? 'N/A' ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="guest-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Modo de visita</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

      <!-- Projects Section -->
<section class="projects">
    <div class="container">
        <div class="1" >
        <h2 class="section-title"  >Lista de Proyectos </h2></div>
        <div class="items-per-page">
            Mostrar:
            <select id="items-per-page" onchange="cambiarCantidadPorPagina()">
           
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            proyectos por página
        </div>



        <div id="tablaContainer">
    <table id="tabla_proyectos" class="display">
        <thead>
            <tr>
                <th>Título</th>
                <th>Fecha</th>
                <th>Autores</th>
                <th>Tutor</th>
                <th>Institución</th>
                <th>Línea de investigación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <tr>
            <td>Proyecto 1</td>
            <td>01/01/2023</td>
            <td>Autor 1</td>
            <td>Tutor 1</td>
            <td>Institución 1</td>
            <td>Línea 1</td>
            <td><button class="btn-action">Acción</button></td>
        </tr>
    </tbody>
    </table>
</div>

        <!-- Contenedor para la paginación -->
        <div id="pagination-container"></div>
    </div>
</section>

    <!-- Scripts actualizados -->

    <script src="js/global.js"></script>

    <script src="js/solicitud.js"></script>
    <script>
        const userRole = "<?php echo $_SESSION['rol'] ?? ''; ?>";
        // Función de búsqueda actualizada para tarjetas
        function cambiarCantidadPorPagina() {
    // Reinicia la paginación a la página 1 al cambiar la cantidad de elementos por página
    buscarProyectos(1);
}

function buscarProyectos(page = 1) {
    const busqueda = $("#buscar").val();
    const per_page = $("#items-per-page").val();

    $.ajax({
        url: "endpoints/filter_projects.php",
        method: "POST",
        dataType: 'json',
        data: {
            buscar: busqueda,
            page: page,
            per_page: per_page
        },
        success: function(data) {
            // Verificar si la respuesta es exitosa
            if (!data.success) {
                throw new Error(data.message || 'Error en el servidor');
            }

            // Destruir instancia previa de DataTables si existe
            if (dataTableInstance) {
                dataTableInstance.destroy();
                dataTableInstance = null;
            }

            // Limpiar y actualizar la tabla
            $("#tabla_proyectos tbody").empty();
            
            if (data.projects && data.projects.length > 0) {
                const tableBody = $("#tabla_proyectos tbody");
                
                data.projects.forEach(project => {
                    const row = `
                        <tr>
                            <td>${escapeHtml(project.titulo)}</td>
                            <td>${project.fecha_formatted}</td>
                            <td>${escapeHtml(project.autores)}</td>
                            <td>${escapeHtml(project.tutor)}</td>
                            <td>${escapeHtml(project.ente)}</td>
                            <td>${escapeHtml(project.linea_investigacion)}</td>
                            <td>${project.acciones}</td>
                        </tr>
                    `;
                    tableBody.append(row);
                });
            } else {
                $("#tabla_proyectos tbody").html(
                    '<tr><td colspan="7" class="no-results">No se encontraron proyectos</td></tr>'
                );
            }

            // Inicializar DataTables solo si hay datos
            if (data.projects.length > 0) {
                const buttons =  [];
                dataTableInstance = $('#tabla_proyectos').DataTable({
                    dom: 'Bfrtip',
                    buttons: buttons,
                    language: {
                        // ... (configuración de idioma) ...
                    },
                    paging: false,
                    searching: false,
                    ordering: true,
                    info: false
                });
            }

            // Actualizar paginación
            buildPagination(data.current_page, data.total_pages);
        },
        error: function(xhr, status, error) {
            console.error("Error en la petición AJAX:", error, xhr.responseText);
            
            // Mostrar mensaje de error
            $("#tabla_proyectos tbody").html(
                '<tr><td colspan="7" class="error-message">Error al cargar proyectos: ' + 
                (xhr.responseJSON?.message || error) + '</td></tr>'
            );
            
            // Limpiar paginación
            $("#pagination-container").empty();
            
            // Reiniciar DataTables si es necesario
            if (dataTableInstance) {
                dataTableInstance.destroy();
                dataTableInstance = null;
            }
        }
    });
}

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
let dataTableInstance = null;
// Función para determinar el enlace de detalle según la sesión
function getSessionLink(projectId) {
    // Aquí asumo que tienes una forma de saber si el usuario está logueado o no en el frontend JS.
    // Esto podría ser a través de una variable global JS inyectada por PHP, o una clase en el body, etc.
    // Por simplicidad, aquí lo haremos asumiendo que `_SESSION['user']` está disponible de alguna manera en JS,
    // o que puedes replicar la lógica de PHP aquí.
    // UNA MEJOR PRÁCTICA sería que el backend ya envíe el enlace completo en el JSON para cada proyecto.
    // Por ahora, lo replicamos aquí.
    const loggedIn = <?php echo isset($_SESSION['user']) ? 'true' : 'false'; ?>; // PHP inyecta valor aquí
    if (loggedIn) {
        return `detalle_proyecto.php?id=${projectId}`;
    } else {
        return `login.php?return_to=../detalle_proyecto.php?id=${projectId}`;
    }
}

// Nueva función para construir la paginación dinámicamente
function buildPagination(currentPage, totalPages) {
    const paginationContainer = $("#pagination-container");
    paginationContainer.empty(); // Limpiar paginación anterior

    if (totalPages <= 1) {
        return; // No mostrar paginación si solo hay una página
    }

    let paginationHtml = '<div class="pagination">';

    // Botón "Anterior"
    if (currentPage > 1) {
        paginationHtml += `<button onclick="buscarProyectos(${currentPage - 1})"><i class="fas fa-chevron-left"></i> Anterior</button>`;
    } else {
        paginationHtml += `<button disabled><i class="fas fa-chevron-left"></i> Anterior</button>`;
    }

    // Números de página
    const maxPageButtons = 5; // Número máximo de botones de página a mostrar
    let startPage = Math.max(1, currentPage - Math.floor(maxPageButtons / 2));
    let endPage = Math.min(totalPages, startPage + maxPageButtons - 1);

    if (endPage - startPage + 1 < maxPageButtons) {
        startPage = Math.max(1, endPage - maxPageButtons + 1);
    }

    if (startPage > 1) {
        paginationHtml += `<button onclick="buscarProyectos(1)">1</button>`;
        if (startPage > 2) {
            paginationHtml += `<span>...</span>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        if (i === currentPage) {
            paginationHtml += `<button class="active">${i}</button>`;
        } else {
            paginationHtml += `<button onclick="buscarProyectos(${i})">${i}</button>`;
        }
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHtml += `<span>...</span>`;
        }
        paginationHtml += `<button onclick="buscarProyectos(${totalPages})">${totalPages}</button>`;
    }

    // Botón "Siguiente"
    if (currentPage < totalPages) {
        paginationHtml += `<button onclick="buscarProyectos(${currentPage + 1})">Siguiente <i class="fas fa-chevron-right"></i></button>`;
    } else {
        paginationHtml += `<button disabled>Siguiente <i class="fas fa-chevron-right"></i></button>`;
    }

    paginationHtml += '</div>';
    paginationContainer.html(paginationHtml);
}
        // Control del menú responsive
        $(document).ready(function() {
            $("#menu-icon").click(function() {
                $(".sidebar").toggleClass("active");
                $(".main-content").toggleClass("menu-active");
            });

            // Cierre automático del menú en móviles
            $(window).resize(function() {
                if ($(window).width() > 768) {
                    $(".sidebar").removeClass("active");
                    $(".main-content").removeClass("menu-active");
                }
            });

            // Cargar proyectos iniciales
            buscarProyectos();
        });
        $(document).ready(function() {
            // Cerrar todos los submenús al inicio
            $(".submenu").hide();

            $("#menu-icon").click(function() {
                $(".sidebar").toggleClass("active");
                $(".main-content").toggleClass("menu-active");
            });

            // Función de submenús mejorada
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

            window.toggleSubmenu = toggleSubmenu;
        });

       
        //console.log($.fn.DataTable); 
    </script>
</body>

</html>