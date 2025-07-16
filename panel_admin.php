<?php
session_start();
include 'panel.php'; // Assuming this file handles general panel layout/includes
if (!isset($_SESSION['user'])) {
    $_SESSION['return_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}
if ($_SESSION['rol'] !== 'admin') {
    header('Location: no_autorizado.php');
    exit();
}
include 'db/conn.php'; // Database connection
include_once 'classes/Files.class.php'; // Assuming this class is used elsewhere or for future features

// Query to fetch active projects from the database
$sql = "SELECT id, titulo, autores, linea_investigacion FROM proyectos WHERE activo = 1";
$result = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <!-- Google Fonts Poppins -->
    <link href="styles/googlefonts.css" rel="stylesheet">
    <!-- SweetAlert2 for beautiful alerts -->
    <link href="styles/sweetalert2.min.css" rel="stylesheet">
    <!-- DataTables CSS for table functionalities -->
    <link href="styles/jquery.dataTables.min.css" rel="stylesheet">
    <style>
        /* Basic body styling */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 0;
        }
        /* Container for the table, allowing overflow for responsiveness */
        .container {
            overflow: scroll; /* Allows horizontal scrolling for large tables on small screens */
            width: 96%;
            max-width: 1600px;
            margin: 0.1px auto;
            background: white;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-radius: 8px; /* Added rounded corners for better aesthetics */
        }
        /* Title styling */
        h2 {
            text-align: center;
            color: #007bff;
            font-weight: 600;
            margin-bottom: 20px;
        }
        /* Back link styling */
        .back-link {
            display: block;
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        /* DataTables header styling */
        table.dataTable thead th {
            background-color: #007bff;
            color: white;
            font-size: 16px; /* Adjusted font size for better fit */
            padding: 12px 18px; /* Added padding */
            border-bottom: 2px solid #0056b3; /* Darker border for separation */
        }
        /* Button general styling */
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            color: white;
            font-weight: 500;
            transition: opacity 0.3s ease;
            margin: 2px;
            cursor: pointer;
            text-decoration: none; /* Ensure links look like buttons */
            display: inline-block; /* Allow buttons to sit next to each other */
            text-align: center;
        }
        /* Button specific colors */
        .btn-edit { background-color: #ffc107; } /* Warning yellow */
        .btn-archivos { background-color: #28a745; } /* Success green */
        .btn-delete { background-color: #dc3545; } /* Danger red */
        /* Button hover effect */
        .btn:hover { opacity: 0.9; transform: translateY(-1px); box-shadow: 0 2px 5px rgba(0,0,0,0.2); } /* Added subtle hover effect */

        /* DataTables specific styling adjustments for better appearance */
        #projectsTable_wrapper {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
        }
        #projectsTable_filter input {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 8px 12px;
            margin-left: 5px;
            width: 250px; /* Adjust width as needed */
        }
        #projectsTable_length select {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 6px 10px;
        }
        table.dataTable tbody td {
            padding: 10px 18px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }
        table.dataTable tbody tr:nth-child(even) {
            background-color: #f2f2f2; /* Zebra striping for readability */
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Panel de Administración - Gestionar Proyectos</h2>

        <table id="projectsTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Autores</th>
                    <th>Línea de Investigación</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr data-id="<?= htmlspecialchars($row['id']) ?>">
                            <td><?= htmlspecialchars($row['titulo']) ?></td>
                            <td><?= htmlspecialchars($row['autores']) ?></td>
                            <td><?= htmlspecialchars($row['linea_investigacion']) ?></td>
                            <td>
                                <a href="editar_proyecto.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-edit">Editar</a>
                                <a href="Editar_archivo.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-archivos">Archivos</a>
                                <button class="btn btn-delete btn-soft-delete">Desactivar</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">No hay proyectos registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- jQuery library -->
    <script src="js/jquery.js"></script>
    <!-- SweetAlert2 library -->
    <script src="js/sweetalert2@11.js"></script>
    <!-- DataTables library -->
    <script src="js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#projectsTable').DataTable({
                language: {
                    processing: "Procesando...",
                    search: "Buscar:", // This enables the search input and sets its label
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
                pageLength: 10, // Default number of rows per page
                lengthMenu: [5, 10, 25, 50] // Options for rows per page
            });

            // Event listener for the "Desactivar" (soft delete) button
            $('#projectsTable').on('click', '.btn-soft-delete', function() {
                const row = $(this).closest('tr'); // Get the table row
                const id = row.data('id'); // Get the project ID from the data-id attribute

                // Show a confirmation dialog using SweetAlert2
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Esta acción desactivará el proyecto. Podrás reactivarlo más tarde si es necesario.', // More descriptive text
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545', // Red color for confirm button
                    cancelButtonColor: '#6c757d' // Gray color for cancel button
                }).then(result => {
                    if (result.isConfirmed) {
                        // If confirmed, send a POST request to the delete endpoint
                        fetch('endpoints/delete_project.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({ id: id }) // Send the project ID as JSON
                        })
                        .then(res => res.json()) // Parse the JSON response
                        .then(data => {
                            if (data.success) {
                                // If successful, remove the row from the DataTable and redraw
                                $('#projectsTable').DataTable().row(row).remove().draw();
                                Swal.fire('Desactivado', data.message, 'success'); // Show success message
                            } else {
                                Swal.fire('Error', data.message, 'error'); // Show error message from backend
                            }
                        })
                        .catch(error => {
                            console.error('Error during fetch:', error); // Log fetch errors
                            Swal.fire('Error', 'Error al procesar la solicitud. Por favor, intente de nuevo.', 'error'); // Generic error message
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
