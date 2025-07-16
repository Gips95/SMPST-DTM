<?php
session_start();

include 'db/conn.php';

// Consulta para obtener todas las instituciones (ente) registradas, sin duplicados
$queryInstituciones = "SELECT DISTINCT ente FROM proyectos ORDER BY ente ASC";
$resultInstituciones = $conexion->query($queryInstituciones);
$instituciones = [];
if ($resultInstituciones && $resultInstituciones->num_rows > 0) {
    while ($row = $resultInstituciones->fetch_assoc()) {
        $instituciones[] = $row['ente'];
    }
}
$queryTutores = "SELECT DISTINCT tutor FROM proyectos ORDER BY tutor ASC";
$resultTutores = $conexion->query($queryTutores);
$tutores = [];
if ($resultTutores && $resultTutores->num_rows > 0) {
    while ($row = $resultTutores->fetch_assoc()) {
        $tutores[] = $row['tutor'];
    }
}
// Consulta para obtener todas las líneas de investigación, sin duplicados
$queryLineas = "SELECT DISTINCT linea_investigacion FROM proyectos ORDER BY linea_investigacion ASC";
$resultLineas = $conexion->query($queryLineas);
$lineas = [];
if ($resultLineas && $resultLineas->num_rows > 0) {
    while ($row = $resultLineas->fetch_assoc()) {
        $lineas[] = $row['linea_investigacion'];
    }
}
$queryFechas = 'SELECT DISTINCT YEAR(p.fecha) as fechas FROM proyectos p ORDER BY fechas DESC';
$resultFechas = $conexion->query($queryFechas);
$fechas = [];
if($resultLineas && $resultFechas->num_rows > 0){
    while ($row = $resultFechas->fetch_assoc()){
        $fechas[] = $row['fechas'];
    }
}

// panel.php ya incluye panel.css, googlefonts.css, bootstrap.css, fontawesome.css
include 'panel.php';

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Proyectos</title>
    
    <!-- CSS Específico de esta página (buscar.css) -->
    <link rel="stylesheet" href="styles/buscar.css"> 
    
    <!-- Otros CSS específicos de tabla o layout que no estén en panel.php -->
    <link rel="stylesheet" href="styles/index.css"> <!-- Si index.css es necesario y no está en panel.php -->
    <link rel="stylesheet" href="styles/jquery.dataTables.min.css">
    <link rel="stylesheet" href="styles/buttons.dataTables.min.css">

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
<!-- sidebar.js debe cargarse después de jQuery y el DOM del sidebar esté disponible -->
<script src="js/sidebar.js"></script>    

    <!-- Contenido principal -->
    <div class="main-content">
        
        <!-- Tarjeta de búsqueda -->
        <div class="card-busqueda">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2>Buscar Proyectos</h2>
            </div>
            <div class="card-body">
                <!-- Filtros -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <input type="text" id="buscar" class="form-control" placeholder="Buscar por título, autores, descripción...">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <select id="ente" class="form-select">
                                <option value="">Todas las instituciones</option>
                                <?php foreach ($instituciones as $inst): ?>
                                    <option value="<?php echo htmlspecialchars($inst); ?>"><?php echo htmlspecialchars($inst); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <select id="linea_investigacion" class="form-select">
                                <option value="">Todas las líneas de investigación</option>
                                <?php foreach ($lineas as $linea): ?>
                                    <option value="<?php echo htmlspecialchars($linea); ?>"><?php echo htmlspecialchars($linea); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <select id="tutor" class="form-select">
                                    <option value="">Todos los tutores</option>
                                    <?php foreach ($tutores as $tutor): ?>
                                        <option value="<?php echo htmlspecialchars($tutor); ?>">
                                            <?php echo htmlspecialchars($tutor); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <input type="date" id="fecha" class="form-control" placeholder="Filtrar por fecha">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <select id="orden_fecha" class="form-select">
                                    <option value="">Ordenar por fecha</option>
                                    <option value="ASC">Más antiguos primero</option>
                                    <option value="DESC">Más recientes primero</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <select id="seleccion_año" class="form-select">
                                    <option value="">Año</option>
                                    <?php foreach($fechas as $fecha): ?>
                                        <option value=<?= $fecha ?>><?= $fecha ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
        <!-- Tabla de resultados -->
        <div class="table-responsive mt-4">
            <table id="tabla_proyectos" class="display" style="width:100%">
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
                    <!-- Los datos se cargarán con AJAX -->
                </tbody>
            </table>
        </div>
        
        <!-- Contenedor para la paginación -->
        <div id="pagination-container"></div>

        <!-- jQuery y script de búsqueda -->
        <script src="js/global.js"></script>
        <script src="js/solicitud.js"></script>
        <script>
            function registrarAccionExportacion(actionType, detallesAdicionales = '') {
                const filtros = {
                    buscar: $("#buscar").val(),
                    ente: $("#ente").val(),
                    linea: $("#linea_investigacion").val(),
                    tutor: $("#tutor").val()
                };
                
                const detalles = `Exportación de proyectos | Filtros: ${JSON.stringify(filtros)}`;
                
                $.ajax({
                    url: 'endpoints/log_export.php',
                    method: 'POST',
                    data: {
                        action_type: actionType,
                        element_type: 'proyectos',
                        details: detalles
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (!response.success) {
                            console.error('Error al registrar log:', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la petición de log:', error);
                    }
                });
            }

            // Variables globales
            let dataTableInstance = null;
            const userRole = "<?php echo $_SESSION['rol'] ?? ''; ?>";

            function cambiarCantidadPorPagina() {
                buscarProyectos(1);
            }

            function buscarProyectos(page = 1) {
                const params = {
                    buscar: $("#buscar").val(),
                    ente: $("#ente").val(),
                    linea_investigacion: $("#linea_investigacion").val(),
                    tutor: $("#tutor").val(),
                    fecha: $("#fecha").val(),
                    año: $("#seleccion_año").val(),
                    orden_fecha: $("#orden_fecha").val(),
                    page: page,
                    per_page: $("#items-per-page").val()
                };

                $.ajax({
                    url: "endpoints/filter_projects.php",
                    method: "POST",
                    dataType: 'json',
                    data: params,
                    success: function(data) {
                        // Verificar si la respuesta es exitosa
                        if (!data.success) {
                            // Manejo mejorado de errores
                            const errorMsg = data.message || "Error desconocido en el servidor";
                            console.error("Error del servidor:", errorMsg);
                            return;
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
                            const buttons = (userRole === 'admin' || userRole === 'profesor')
                                ? [
                                    {
                                        extend: 'copy',
                                        text: 'Copiar',
                                        className: 'btn btn-sm btn-secondary',
                                        action: function(e, dt, node, config) {
                                            // Call the original DataTables copy action FIRST
                                            $.fn.dataTable.ext.buttons.copyHtml5.action.call(this, e, dt, node, config);
                                            // Then call your custom logging function
                                            registrarAccionExportacion('copy');
                                        }
                                    },
                                    {
                                        extend: 'csv',
                                        text: 'Exportar a CSV',
                                        className: 'btn btn-sm btn-secondary',
                                        action: function(e, dt, node, config) {
                                            // Call the original DataTables CSV export action FIRST
                                            $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, node, config);
                                            // Then call your custom logging function
                                            registrarAccionExportacion('csv_export');
                                        }
                                    },
                                    {
                                        extend: 'excel',
                                        text: 'Exportar a Excel',
                                        className: 'btn btn-sm btn-secondary',
                                        action: function(e, dt, node, config) {
                                            // Call the original DataTables Excel export action FIRST
                                            $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, node, config);
                                            // Then call your custom logging function
                                            registrarAccionExportacion('excel_export');
                                        }
                                    },
                                    {
                                        extend: 'pdf',
                                        text: 'Exportar a PDF',
                                        className: 'btn btn-sm btn-secondary',
                                        action: function(e, dt, node, config) {
                                            // Call the original DataTables PDF export action FIRST
                                            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, node, config);
                                            // Then call your custom logging function
                                            registrarAccionExportacion('pdf_export');
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        text: 'Imprimir',
                                        className: 'btn btn-sm btn-secondary',
                                        action: function(e, dt, node, config) {
                                            // Call the original DataTables print action FIRST
                                            $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, node, config);
                                            // Then call your custom logging function
                                            registrarAccionExportacion('print');
                                        }
                                    }
                                ]
                                : [];

                            dataTableInstance = $('#tabla_proyectos').DataTable({
                                dom: 'Bfrtip',
                                buttons: buttons,
                                language: {
                                    processing: "Procesando...",
                                    search: "Buscar:",
                                    lengthMenu: "Mostrar _MENU_ registros",
                                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                                    infoEmpty: "Mostrando 0 a 0 de 0 registros",
                                    infoFiltered: "(filtrado de _MAX_ registros totales)",
                                    loadingRecords: "Cargando...",
                                    zeroRecords: "No se encontraron resultados",
                                    emptyTable: "No hay datos disponibles en la tabla",
                                    paginate: {
                                        first: "Primero",
                                        previous: "Anterior",
                                        next: "Siguiente",
                                        last: "Último"
                                    },
                                    aria: {
                                        sortAscending: ": activar para ordenar la columna de manera ascendente",
                                        sortDescending: ": activar para ordenar la columna de manera descendente"
                                    }
                                },
                                paging: false,
                                searching: false,
                                ordering: true,
                                info: false,
                                autoWidth: true // Ensure autoWidth is true
                            });

                            // Force DataTables to adjust its columns after rendering
                            // This often fixes the "half-width" issue on initial load
                            if (dataTableInstance) {
                                dataTableInstance.columns.adjust().draw();
                            }

                        }

                        // Actualizar paginación personalizada
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

            // Inicialización
            $(document).ready(function() {
                // Eventos de cambio en los filtros
                $("#buscar, #ente, #linea_investigacion, #tutor, #fecha, #orden_fecha, #seleccion_año")
                    .on("input change", function() {
                        buscarProyectos(1);
                    });

                $("#items-per-page").on("change", cambiarCantidadPorPagina);

                // Carga inicial
                buscarProyectos(1);
            });
        </script>
        
    </div>
    <?php require('includes/footer.php'); ?>
</body>

</html>
