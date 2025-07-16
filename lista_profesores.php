<?php
session_start();
include 'db/conn.php';
include 'panel.php';

// Verificar si el usuario está logueado y tiene el rol adecuado
if (!isset($_SESSION["user"])) {
    // Si se desea, se puede guardar la URL para redirigir después del login
    // $_SESSION['return_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}
// Se podría añadir una comprobación de rol si fuera necesario
// if ($_SESSION['rol'] !== 'admin') {
//     header('Location: no_autorizado.php');
//     exit();
// }

// Consulta SQL para obtener todos los profesores. DataTables se encargará del filtrado en el lado del cliente.
$sql = "SELECT id, user, email, fecha_registro FROM usuarios WHERE rol = 'profesor'";
$result = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Profesores</title>
    <!-- Estilos unificados del primer archivo -->
    <link href="styles/googlefonts.css" rel="stylesheet">
    <link href="styles/sweetalert2.min.css" rel="stylesheet">
    <link href="styles/jquery.dataTables.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 0;
        }
        .container {
            overflow-x: auto; /* Mejorado para responsive */
            width: 96%;
            max-width: 1600px;
            margin: 20px auto; /* Margen ajustado */
            background: white;
            padding: 32px;
            border-radius: 15px; /* Borde añadido */
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #007bff;
            font-weight: 600;
            margin-bottom: 20px;
        }
        /* Estilos de DataTables y botones unificados */
        table.dataTable thead th {
            background-color: #007bff;
            color: white;
            font-size: 16px; /* Tamaño ajustado */
        }
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            color: white;
            font-weight: 500;
            transition: opacity 0.3s ease;
            margin: 2px;
            cursor: pointer;
            text-decoration: none; /* Añadido para los enlaces */
            display: inline-block; /* Añadido para los enlaces */
        }
        .btn-edit { background-color: #ffc107; }
        .btn-delete { background-color: #dc3545; }
        .btn:hover { opacity: 0.8; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Docentes Registrados</h2>
        
        <!-- La tabla ahora tiene un ID y clases para DataTables -->
        <table id="profesoresTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Cédula</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Fecha de Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <!-- Se añade data-id a la fila para facilitar la manipulación con JS -->
                        <tr data-id="<?= htmlspecialchars($row['id']) ?>">
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['user']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars(date_format(date_create($row['fecha_registro']), 'd-m-Y')) ?></td>
                            
                            <td>
                                <a href="editar_usuario.php?id=<?= $row['id'] ?>" class="btn btn-edit">Editar</a>
                                <button class="btn btn-delete btn-delete-user">Eliminar</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No hay profesores registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Scripts necesarios: jQuery, SweetAlert y DataTables -->
    <script src="js/jquery.js"></script>
    <script src="js/sweetalert2@11.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicialización de DataTables con traducción al español
            $('#profesoresTable').DataTable({
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
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50]
            });

            // Manejador de eventos para el botón de eliminar, usando la sintaxis de jQuery
            $('#profesoresTable').on('click', '.btn-delete-user', function() {
                const row = $(this).closest('tr');
                const userId = row.data('id'); // Obtenemos el ID desde el data-id de la fila

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Esta acción eliminará permanentemente al profesor.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545', // Color rojo para el botón de confirmar
                    cancelButtonColor: '#6c757d' // Color gris para cancelar
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch('endpoints/delete_user.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: userId })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // Usamos la API de DataTables para eliminar la fila de la tabla
                                $('#profesoresTable').DataTable().row(row).remove().draw();
                                Swal.fire('Eliminado', data.message, 'success');
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'No se pudo completar la solicitud.', 'error'));
                    }
                });
            });
        });
    </script>
</body>
</html>
