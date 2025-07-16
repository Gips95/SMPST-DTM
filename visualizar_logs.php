<?php
session_start();

// Redirigir si no está logueado o no es admin
if (!isset($_SESSION['user'])) {
    $_SESSION['return_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

if ($_SESSION['rol'] !== 'admin') {
    header('Location: no_autorizado.php');
    exit();
}

include 'db/conn.php';
include 'panel.php'; // Asegúrate de que 'panel.php' no contenga etiquetas <html>, <head>, <body>
// Tipos de acción disponibles

$actions = [
    'create' => 'Creación',
    'update' => 'Modificación',
    'delete' => 'Eliminación',
    'login' => 'Inicio de sesión',
    'logout' => 'Cierre de sesión',
    'print' => 'Impresión',
    'csv_export' => 'Exportar CSV',
    'excel_export' => 'Exportar Excel',
    'pdf_export' => 'Exportar PDF',
    'copy' => 'Copiar'
];

$action_types = [
    'todos' => ['icon' => 'fas fa-list', 'label' => 'Todos'],
    'create' => ['icon' => 'fas fa-plus-circle', 'label' => 'Creaciones'],
    'update' => ['icon' => 'fas fa-edit', 'label' => 'Actualizaciones'], 
    'delete' => ['icon' => 'fas fa-trash-alt', 'label' => 'Eliminaciones'],
    'login' => ['icon' => 'fas fa-sign-in-alt', 'label' => 'Accesos'],
    'export' => ['icon' => 'fas fa-file-export', 'label' => 'Exportaciones'] // Nueva categoría
];

// Procesar búsqueda
$fecha_busqueda_inicial = isset($_GET['fecha_ini']) ? $_GET['fecha_ini'] : '';
$fecha_busqueda_final = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$tipo_accion = isset($_GET['tipo_accion']) ? $_GET['tipo_accion'] : 'todos';

// Construir consulta SQL
$sql = "SELECT l.user, l.action_type, l.action_details, l.element_id, l.element_type, l.ip_address, l.changes, l.created_at 
        FROM log l";

$where = [];
$params = [];

if (!empty($fecha_busqueda_inicial) && !empty($fecha_busqueda_final)) {
    $where[] = "DATE(l.created_at) BETWEEN ? AND ?";
    $params[] = $fecha_busqueda_inicial;
    $params[] = $fecha_busqueda_final;
}
if (!empty($fecha_busqueda_inicial) && empty($fecha_busqueda_final)) {
    $current_date = new DateTime();
    $current_date = $current_date->format('Y-m-d');
    
    $where[] = "DATE(l.created_at) BETWEEN ? AND ?";
    $params[] = $fecha_busqueda_inicial;
    $params[] = $current_date;
}
if (empty($fecha_busqueda_inicial) && !empty($fecha_busqueda_final)) {
    $mindate_res = $conexion->query("SELECT DATE(MIN(l.created_at)) as mindate FROM log l");
    $mindate = '1970-01-01'; // Default value if no date found
    if($mindate_res && $row = $mindate_res->fetch_assoc()){
        $mindate = $row['mindate'];
    }

    $where[] = "DATE(l.created_at) BETWEEN ? AND ?";
    $params[] = $mindate;
    $params[] = $fecha_busqueda_final;
}

if ($tipo_accion != 'todos') {
    if ($tipo_accion == 'export') {
        // Incluir todos los tipos de exportación
        $exportActions = ['print', 'csv_export', 'excel_export', 'pdf_export', 'copy'];
        $placeholders = implode(',', array_fill(0, count($exportActions), '?'));
        $where[] = "l.action_type IN ($placeholders)";
        $params = array_merge($params, $exportActions);
    } else {
        $where[] = "l.action_type = ?";
        $params[] = $tipo_accion;
    }
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY l.created_at DESC"; // Removed LIMIT 100 to let DataTables handle pagination

$stmt = $conexion->prepare($sql);

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Actividades</title>
    <link href="styles/css2.css" rel="stylesheet">
    <link href="styles/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/fontawesome/css/all.css">
    <link rel="stylesheet" href="styles/visualizar_logs.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="styles/dataTables.dataTables.min.css"/>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Registro de Actividades</h1>
            <p class="page-subtitle">Monitor completo de todas las acciones realizadas en el sistema</p>
        </div>
        
        <!-- Pestañas de acciones -->
        <div class="action-tabs">
            <?php foreach ($action_types as $key => $type): ?>
                <div class="action-tab <?= $tipo_accion == $key ? 'active' : '' ?>" 
                     onclick="filterByAction('<?= $key ?>')">
                    <i class="<?= $type['icon'] ?>"></i>
                    <span><?= $type['label'] ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Filtros -->
        <form method="get" action="" id="filterForm">
               <input type="hidden" name="tipo_accion" id="tipo_accion" value="<?= $tipo_accion ?>">
            
            <div class="filter-container">
                <div class="filter-group">
                    <label for="fecha_ini">Buscar a partir de esta fecha:</label>
                    <input type="date" id="fecha_ini" name="fecha_ini" value="<?= htmlspecialchars($fecha_busqueda_inicial) ?>">
                </div>
                <div class="filter-group">
                    <label for="fecha_fin">Buscar al final de esta fecha:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($fecha_busqueda_final) ?>">
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn-search">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <button type="button" class="btn-reset" onclick="resetFilters()">
                        <i class="fas fa-undo"></i> Limpiar
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Tabla de resultados -->
        <div class="table-container">
        <table id="logTable" class="display"> <!-- Added id="logTable" and class="display" -->
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Elemento</th>
                    <th>Tipo</th>
                    <th>Detalles</th>
                    <th>Cambios</th>
                    <th>IP</th>
                    <th>Fecha/Hora</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($log = $result->fetch_assoc()): 
                        $datetime = new DateTime($log['created_at']);
                        $created_at = $datetime->format('d-m-Y / H:i:s');  
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($log['user']) ?></td>
                            <td>
                                <?php 
                                $badge_class = '';
                                switch($log['action_type']) {
                                    case 'create': $badge_class = 'badge-create'; break;
                                    case 'update': $badge_class = 'badge-update'; break;
                                    case 'delete': $badge_class = 'badge-delete'; break;
                                    case 'login': $badge_class = 'badge-login'; break;
                                    case 'print': $badge_class = 'badge-print'; break;
                                    case 'csv_export':
                                    case 'excel_export':
                                    case 'pdf_export':
                                    case 'copy':
                                        $badge_class = 'badge-export'; 
                                        break;
                                    default: $badge_class = '';
                                }
                                ?>
                                <span class="action-badge <?= $badge_class ?>">
                                    <?= isset($actions[$log['action_type']]) ? $actions[$log['action_type']] : htmlspecialchars($log['action_type']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($log['element_id']) ?></td>
                            <td><?= htmlspecialchars($log['element_type']) ?></td>
                            
                            <!-- Mejorar visualización de detalles de exportación -->
                            <td>
                                <?php if(in_array($log['action_type'], ['print', 'csv_export', 'excel_export', 'pdf_export', 'copy'])): ?>
                                    <div class="export-details">
                                        <i class="fas fa-file-export"></i>
                                        <?= htmlspecialchars($log['action_details']) ?>
                                    </div>
                                <?php else: ?>
                                    <?= htmlspecialchars($log['action_details']) ?>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Actualizar lógica de cambios para exportaciones -->
                            <td>
                                <?php if(($log['action_type'] == 'update' || $log['action_type'] == 'delete') && !empty($log['changes'])): 
                                    $changes = json_decode($log['changes'], true); ?>
                                    <div class="changes-container">
                                        <?php foreach($changes as $key => $change): ?>
                                            <div class="change-item">
                                                <span class="change-field"><?= htmlspecialchars($key) ?>:</span>
                                                <?php if(is_array($change) && count($change) >= 2): 
                                                            if($log['action_type'] == 'update'): ?>
                                                                <span class="change-old"><?= htmlspecialchars($change[0]) ?></span>
                                                                <i class="fas fa-arrow-right change-arrow"></i>
                                                                <span class="change-new"><?= htmlspecialchars($change[1]) ?></span>
                                                    <?php elseif($log['action_type'] == 'delete'): ?>
                                                        <span class="change-old"><?= htmlspecialchars($change[0]) ?></span>
                                                    <?php   endif; ?>
                                                    <?php   else: ?>
                                                        <span class="change-new"><?= htmlspecialchars(print_r($change, true)) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php elseif(in_array($log['action_type'], ['print', 'csv_export', 'excel_export', 'pdf_export', 'copy'])): ?>
                                    <div class="export-info">
                                        <i class="fas fa-check-circle"></i> Exportación realizada
                                    </div>
                                <?php endif; ?>
                            </td>
                            
                            <td><?= htmlspecialchars($log['ip_address']) ?></td>
                            <td><?= $created_at; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="no-results">
                            <i class="fas fa-info-circle fa-lg"></i><br><br>
                            No se encontraron registros con los criterios seleccionados
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
       


    <?php require('includes/footer.php'); ?>
    
    <!-- jQuery (required for DataTables) -->
    <script src="js/jquery.js"></script>
    <!-- DataTables JS -->
    <script src="js/dataTables.min.js"></script>
    <script>
        console.log(location)
        const filtersForm = document.getElementById('filterForm')

        function submitFiltersForm(form, action = null){
            if(action != null) document.getElementById('tipo_accion').value = action

            let filterValues = []
            form.querySelectorAll('input').forEach((input) => {
                filterValues.push(input.id+'='+input.value)
            })
            let url = location.pathname+'?'+filterValues.join('&')
            location.replace(url)
        }

        function filterByAction(action) {
            submitFiltersForm(filtersForm, action)
        }

        filtersForm.addEventListener('submit', (e) => {
            e.preventDefault();
            submitFiltersForm(e.target)
        })

        function resetFilters() {
            // Resetear los campos de fecha
            document.getElementById('fecha_ini').value = '';
            document.getElementById('fecha_fin').value = '';
            
            // Resetear el tipo de acción a 'todos'
            document.getElementById('tipo_accion').value = 'todos';
            
            // Resetear las pestañas activas
            const tabs = document.querySelectorAll('.action-tab');
            tabs.forEach(tab => {
                tab.classList.remove('active');
                if (tab.innerText.includes("Todos")) {
                    tab.classList.add('active');
                }
            });
            
            // Enviar el formulario
            submitFiltersForm(filtersForm)
        }
        
        // Establecer fecha máxima como hoy
        document.addEventListener('DOMContentLoaded', function() {
            if(location.pathname != location.href) history.replaceState(null, '', location.pathname)
            const today = new Date().toISOString().split('T')[0];
            // Updated to reflect the correct ID in the HTML
            const fechaIniInput = document.getElementById('fecha_ini');
            const fechaFinInput = document.getElementById('fecha_fin');

            if (fechaIniInput) {
                fechaIniInput.max = today;
            }
            if (fechaFinInput) {
                fechaFinInput.max = today;
            }

            // Initialize DataTables
            $('#logTable').DataTable({
                "language": {
                    "url": "js/Spanish.json"
                },
                "paging": true,
                "searching": true,
                "ordering": true,
                "info": true
            });
        });
    </script>
    
    <?php 
    if ($result) {
        $result->free();
    }
    if (isset($stmt)) {
        $stmt->close();
    }
    $conexion->close(); 
    ?>
</body>
</html>
