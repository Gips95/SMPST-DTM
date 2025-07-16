<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit();
}
include 'db/conn.php';
include_once("classes/Files.class.php");
include_once('classes/Projects.class.php');

if (!isset($_GET['id'])) {
    die("ID de proyecto no especificado.");
}
// Obtener información del proyecto (para mostrar título, etc.)
try {
    $proyecto_id = intval($_GET['id']);
    $proyecto = Project::getProject($proyecto_id, $conexion);
    $archivos = ProjectFile::getProjectFiles($proyecto_id, $conexion);
    $conexion->close();
    

} catch (Exception $e) {
    die('Error: '.$e->getMessage());
}

// Función para formatear tamaños, solo se define si no existe
if (!function_exists('formatSize')) {
    function formatSize($bytes) {
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
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Archivos - <?php echo htmlspecialchars($proyecto['titulo']); ?></title>
    <link href="styles/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/fontawesome">
    <link rel="stylesheet" href="styles/modal.css">
    <link href="styles/sweetalert2.min.css" rel="stylesheet">
    <style>
        /* Estilos basados en el formulario de registro */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 20px;
        }
        .container-main {
            width: 90%;
            max-width: 1200px;
            margin: auto;
        }
        .card-registro {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .card-header {
            background: #007bff;
            color: white;
            padding: 25px 40px;
            border-bottom: 3px solid #0056b3;
        }
        .card-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .card-body {
            padding: 40px;
        }
        /* Estilos para pestañas con Bootstrap 5 */
        .nav-tabs .nav-link {
            font-weight: 600;
            color: #007bff;
        }
        .nav-tabs .nav-link.active {
            background-color: #007bff;
            color: white;
            border-color: #0056b3 #0056b3 #fff;
        }
        /* Tabla de archivos */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        /* Botones de acción */
        .btn {
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: opacity 0.3s ease;
        }
        .btn-editar {
            background-color: #ffc107;
        }
        .btn-editar:hover {
            opacity: 0.8;
        }
        .btn-eliminar {
            background-color: #dc3545;
        }
        .btn-eliminar:hover {
            opacity: 0.8;
        }
        .btn-agregar {
            background-color: #007bff;
            margin-bottom: 20px;
        }
        .btn-agregar:hover {
            opacity: 0.8;
        }


         /* Custom File Input Preview Styling */
         .file-input-wrapper {
            margin-top: 10px;
            margin-bottom: 15px; /* Add some space below the input */
        }

        .file-preview-container {
            margin-top: 15px;
            border: 1px dashed #ced4da;
            border-radius: 8px;
            padding: 15px;
            min-height: 80px; /* Adjust as needed */
            background-color: #f8f9fa;
            display: flex;
            flex-wrap: wrap; /* Allows items to wrap to the next line */
            gap: 10px; /* Space between file items */
            align-content: flex-start; /* Aligns content to the start of the cross axis */
        }

        .file-preview-container.empty:before {
            content: "Arrastra y suelta archivos aquí o haz clic para seleccionar.";
            color: #6c757d;
            font-size: 16px;
            text-align: center;
            width: 100%;
            display: block;
            padding-top: 20px;
        }

        .file-preview-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            font-size: 14px;
            flex-basis: calc(50% - 5px); /* Two items per row with gap */
            box-sizing: border-box; /* Include padding and border in the width */
            position: relative; /* For error message positioning */
        }

        @media (max-width: 768px) {
            .file-preview-item {
                flex-basis: 100%; /* One item per row on smaller screens */
            }
        }

        .file-preview-name {
            font-weight: bold;
            color: #333;
            word-break: break-all;
            flex-grow: 1;
            padding-right: 10px; /* Space before size */
        }

        .file-preview-size {
            color: #6c757d;
            flex-shrink: 0;
        }

        .file-preview-remove {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 14px;
            margin-left: 15px;
            flex-shrink: 0;
            padding: 0 5px;
            transition: color 0.2s ease;
        }

        .file-preview-remove:hover {
            color: #bd2130;
        }

        .file-error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            width: 100%;
            position: absolute;
            bottom: -20px; /* Position below the item */
            left: 0;
            text-align: center;
        }
        .file-preview-item.error {
            border-color: #dc3545;
            background-color: #f8d7da;
        }

        /* Custom Modal for Alerts */
        .modal-custom-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            visibility: hidden;
            opacity: 0;
            transition: visibility 0s, opacity 0.3s ease;
        }
        .modal-custom-overlay.show {
            visibility: visible;
            opacity: 1;
        }
        .modal-custom-content {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 400px;
            width: 90%;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }
        .modal-custom-overlay.show .modal-custom-content {
            transform: translateY(0);
        }
        .modal-custom-title {
            font-size: 22px;
            margin-bottom: 15px;
            color: #333;
        }
        .modal-custom-message {
            font-size: 16px;
            color: #555;
            margin-bottom: 25px;
        }
        .modal-custom-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .modal-custom-button:hover {
            background-color: #0056b3;
        }
        .registro-invalid-feedback { display: none; font-size: 12px; color: #dc3545; }

    </style>
    <!-- Bootstrap 5 CSS (si no lo tienes incluido en bootstrap.css, usa el CDN) -->
  
</head>
<body>
<div id="customAlertModal" class="modal-custom-overlay">
        <div class="modal-custom-content">
            <h4 id="customAlertTitle" class="modal-custom-title"></h4>
            <p id="customAlertMessage" class="modal-custom-message"></p>
            <button id="customAlertCloseBtn" class="modal-custom-button">OK</button>
        </div>
    </div>
    <div class='modal'>
        <form action="./endpoints/add_file.php" id='add-file-form' method='post' enctype="multipart/form-data" multipart=''>
                                <input type="hidden" name='project_id' id='project_id' value=<?= $proyecto_id ?>>
                            <div class="add-document form-group">
                                <h4 class="registro form-label">Documentos Principales</h4>
                                <!-- Custom file input for main documents -->
                                <div class="file-input-wrapper">
                                    <input type="file" id="documentosInput" name="documentos[]" multiple class="registro form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
                                </div>
                                <div id="documentosPreview" class="file-preview-container empty"></div>
                                <small class="registro-rule">Solo archivos PDF, DOC, DOCX, PPT, PPTX, XLS o XLSX (máx. 50MB).</small>
                                <!--<div id="docError" class="registro-invalid-feedback">Sube al menos un documento principal.</div> -->
                            </div>
                            <div class="add-reference">
                                <h4 class="registro form-label mt-4">Referencias (opcional)</h4>
                                <!-- Custom file input for references -->
                                <div class="file-input-wrapper">
                                    <input type="file" id="referenciasInput" name="referencias[]" multiple class="registro form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip">
                                </div>
                                <div id="referenciasPreview" class="file-preview-container empty"></div>
                                <small class="registro-rule">Solo archivos PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX o ZIP (máx. 50MB).</small>
                            </div>
                            <div id="docError" class="registro-invalid-feedback">Sube al menos un documento principal.</div>
                            <div class='btns-modal mt-5'>
                                <button type='submit' class='btn btn-primary btns-modal'>Confirmar</button>
                                <a class='btn btn-secondary btns-modal' id='close-modal'>Cerrar</a>
                            </div>
        </form>
    </div>

    <div class="container-main">
        <div class="card-registro">
            <div class="card-header">
                <h2>Gestionar Archivos - <?php echo htmlspecialchars($proyecto['titulo']); ?></h2>
            </div>
            <div class="card-body">
                <!-- Botón para agregar nuevos archivos (redirige a formulario o modal) -->
                <a class="btn btn-agregar" id='add-files-btn'>Agregar Archivos</a>
                
                <!-- Pestañas para Documentos y Referencias -->
                <ul class="nav nav-tabs" id="archivoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos" type="button" role="tab">Documentos</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="referencias-tab" data-bs-toggle="tab" data-bs-target="#referencias" type="button" role="tab">Referencias</button>
                    </li>
                </ul>
                <div class="tab-content" id="archivoTabsContent">
                    <!-- Pestaña Documentos -->
                    <div class="tab-pane fade show active" id="documentos" role="tabpanel">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tamaño</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $existenDocumentos = false;
                                foreach ($archivos as $archivo):
                                    if ($archivo['tipo'] == 'documento'):
                                        $existenDocumentos = true;
                                ?>
                                <tr id="archivo-<?php echo $archivo['id']; ?>">
                                    <td><?php echo htmlspecialchars($archivo['nombre']); ?></td>
                                    <td><?php echo formatSize($archivo['size']); ?></td>
                                    <td>
                                        <!--<a href="editar_archivo.php?id=<?php echo $archivo['id']; ?>&proyecto_id=<?php echo $proyecto_id; ?>" class="btn btn-editar">Editar</a> -->
                                        <button class="btn btn-eliminar" data-file-id=<?php echo $archivo['id']; ?> >Eliminar</button>
                                    </td>
                                </tr>
                                <?php 
                                    endif;
                                endforeach;
                                if (!$existenDocumentos) {
                                    echo "<tr class='no-elements'><td colspan='3'>No hay documentos registrados.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pestaña Referencias -->
                    <div class="tab-pane fade" id="referencias" role="tabpanel">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tamaño</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $existenReferencias = false;
                                foreach ($archivos as $archivo):
                                    if ($archivo['tipo'] == 'referencia'):
                                        $existenReferencias = true;
                                ?>
                                <tr id="archivo-<?php echo $archivo['id']; ?>">
                                    <td><?php echo htmlspecialchars($archivo['nombre']); ?></td>
                                    <td><?php echo formatSize($archivo['size']); ?></td>
                                    <td>
                                       <!-- <a href="editar_archivo.php?id=<?php echo $archivo['id']; ?>&proyecto_id=<?php echo $proyecto_id; ?>" class="btn btn-editar">Editar</a> -->
                                        <button class="btn btn-eliminar" data-file-id=<?php echo $archivo['id']; ?> >Eliminar</button>
                                    </td>
                                </tr>
                                <?php 
                                    endif;
                                endforeach;
                                if (!$existenReferencias) {
                                    echo "<tr class='no-elements'><td colspan='3'>No hay referencias registradas.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- Fin card-body -->
        </div><!-- Fin card-registro -->
    </div><!-- Fin container-main -->

    <!-- jQuery para AJAX -->
    <script src="js/jquery.js"></script>
    <!-- Bootstrap 5 Bundle (incluye Popper) -->
    <script src="js/bootstrap.js"></script>
    <script src="js/sweetalert2@11.js"></script>
    <script type='module'>

        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', (e) => eliminarArchivo(e.target.dataset.fileId))
        });

        const addFilesModal = document.querySelector('.modal');
        const modal = document.getElementById('customAlertModal');

        const closeBtnModal = document.getElementById('close-modal');
        const addFilesBtn = document.getElementById('add-files-btn')

        closeBtnModal.addEventListener('click', (e) => {
            addFilesModal.classList.remove('open')
            addFilesBtn.removeAttribute('disabled')
        })

        addFilesBtn.addEventListener('click', (e) => {
            addFilesModal.classList.add('open')
            e.target.setAttribute('disabled', '');
        })


        function showMessageModal(title, message) {
            //const modal = document.getElementById('customAlertModal');
            document.getElementById('customAlertTitle').textContent = title;
            document.getElementById('customAlertMessage').textContent = message;
            modal.classList.add('show');
        }

        document.getElementById('customAlertCloseBtn').addEventListener('click', (e) => modal.classList.remove('show'))

        import { ACCEPTED_DOC_TYPES, MAX_FILE_SIZE_BYTES, ACCEPTED_REF_TYPES, selectedDocuments, selectedReferences, formatFileSize, renderFilePreviews, handleFileInputChange} from './js/handleFiles.js'
        import fetchDataJson from './js/fetching.js';
            
            const documentosInput = document.getElementById('documentosInput');
            const referenciasInput = document.getElementById('referenciasInput');
            const documentosPreview = document.getElementById('documentosPreview');
            const referenciasPreview = document.getElementById('referenciasPreview');
            
            documentosInput.addEventListener('change', (e) => handleFileInputChange(e, selectedDocuments, 'documentosPreview', ACCEPTED_DOC_TYPES, MAX_FILE_SIZE_BYTES));
            referenciasInput.addEventListener('change', (e) => handleFileInputChange(e, selectedReferences, 'referenciasPreview', ACCEPTED_REF_TYPES, MAX_FILE_SIZE_BYTES));
            
            // Drag and drop functionality for custom areas
            ['dropzoneDocumentos', 'dropzoneReferencias'].forEach(id => {
                const dropArea = document.getElementById(id.replace('dropzone', '') + 'Preview'); // Use the preview container as drop area
                const fileInput = document.getElementById(id.replace('dropzone', '') + 'Input');

                if (dropArea && fileInput) {
                    dropArea.addEventListener('dragover', (e) => {
                        e.preventDefault(); // Prevent default to allow drop
                        dropArea.classList.add('is-dragover');
                    });

                    dropArea.addEventListener('dragleave', () => {
                        dropArea.classList.remove('is-dragover');
                    });

                    dropArea.addEventListener('drop', (e) => {
                        e.preventDefault();
                        dropArea.classList.remove('is-dragover');
                        // Simulate file input change event with dropped files
                        const dataTransfer = new DataTransfer();
                        Array.from(e.dataTransfer.files).forEach(file => dataTransfer.items.add(file));
                        fileInput.files = dataTransfer.files;

                        // Manually trigger the change event
                        const changeEvent = new Event('change', { bubbles: true });
                        fileInput.dispatchEvent(changeEvent);
                    });

                    // Add click to open file dialog
                    dropArea.addEventListener('click', () => {
                        fileInput.click();
                    });
                }
            });
            
            const form = document.getElementById('add-file-form');
            
            form.addEventListener('submit', async e => { // Marked as async because fetch returns a Promise
                e.preventDefault();
                
                let valid = form.checkValidity();
        
                // Comprobar al menos un documento principal
                if (selectedDocuments.length < 1 && selectedReferences.length < 1) {
                    document.getElementById('docError').style.display = 'block'; 
                    valid = false;
                } else {
                    document.getElementById('docError').style.display='none';
                }

                // Check for errors in selected files (size/type that might have slipped past initial check if directly appended without validation)
                const hasDocErrors = selectedDocuments.some(file => {
                    const isValidType = ACCEPTED_DOC_TYPES.includes(file.type) || (file.name.match(/\.([0-9a-z]+)(?:[\?#]|$)/i) && ACCEPTED_DOC_TYPES.some(type => file.name.endsWith(type.split('/')[1])));
                    const isValidSize = file.size <= MAX_FILE_SIZE_BYTES;
                    return !isValidType || !isValidSize;
                });
                const hasRefErrors = selectedReferences.some(file => {
                    const isValidType = ACCEPTED_REF_TYPES.includes(file.type) || (file.name.match(/\.([0-9a-z]+)(?:[\?#]|$)/i) && ACCEPTED_REF_TYPES.some(type => file.name.endsWith(type.split('/')[1])));
                    const isValidSize = file.size <= MAX_FILE_SIZE_BYTES;
                    return !isValidType || !isValidSize;
                });

                if (hasDocErrors || hasRefErrors) {
                    showMessageModal('Error de Archivos', 'Por favor, corrige los errores en los archivos seleccionados (tipo/tamaño).');
                    valid = false;
                    renderFilePreviews(selectedDocuments, 'documentosPreview', ACCEPTED_DOC_TYPES, MAX_FILE_SIZE_BYTES); // Re-render to show errors
                    renderFilePreviews(selectedReferences, 'referenciasPreview', ACCEPTED_REF_TYPES, MAX_FILE_SIZE_BYTES); // Re-render to show errors
                }

                if (!valid) {
                    form.querySelectorAll(':invalid').forEach(f => f.classList.add('registro-is-invalid'));
                    return;
                }

                    const documentInput = document.getElementById('documentosInput')
                    const referenceInput = document.getElementById('referenciasInput')

                    const dataTD = new DataTransfer()
                    const dataTR = new DataTransfer()
                    selectedDocuments.forEach(items => {
                        dataTD.items.add(items)
                    })
                    documentInput.files = dataTD.files

                    selectedReferences.forEach(items => {
                        dataTR.items.add(items)
                    })
                    referenceInput.files = dataTR.files
                    
               try {
                    const data = new FormData(e.target);
                             
                    const res = await fetchDataJson('./endpoints/add_file.php', data, 'POST');             
                    if(res.state != 200) throw new Error(res.message)
                    const modal = document.querySelector('.modal').classList.remove('open')

                    Swal.fire('Exito', 'Archivo/s de proyecto añadido/s', 'success')
                    addFilesModal.classList.remove('open')
                    addFilesBtn.removeAttribute('disabled')

                    let html_text = ''
                    const body = res.body

                    Object.keys(body).forEach(item => {
                        const type = item == 'docs' ? 'documentos' : 'referencias'

                        const tabPanel = document.getElementById(type)
                        const project_id = document.getElementById('project_id').value

                    if(body[item].length > 0){
                        let tbody = tabPanel.querySelector('tbody')
                        if(tbody.querySelector('.no-elements') != null) tbody.innerHTML = ''

                            body[item].forEach(file => {
                            html_text = `<tr id="archivo-${file.id}">`+
                                              `<td>${file.name}</td>`+
                                              `<td>${file.size}</td>`+
                                              `<td>`+
                                                `<!-- <a href="editar_archivo.php?id=${file.id}&proyecto_id=${project_id}" class="btn btn-editar">Editar</a> -->`+
                                                `<button class="btn btn-eliminar" data-file-id=${file.id}>Eliminar</button>`+
                                              `</td>`+
                                        `</tr>`
                        
                            tbody.insertAdjacentHTML('beforeend', html_text)
                            tbody = tabPanel.querySelector('tbody') //obtener cuerpo de la tabla actualizado

                            const tableRow = tbody.querySelector(`#archivo-${file.id}`) //obtener tablerow del nuevo archivo de la tabla
                            
                            tableRow.querySelector('.btn-eliminar').addEventListener('click', (e) => eliminarArchivo(e.target.dataset.fileId))
                        })
                    }
                    })

               } catch (error) {
                    showMessageModal('Error', error.message)
               }
            });
        
         function eliminarArchivo(idArchivo) {
            Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Esta acción eliminara el archivo.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(async result => {
                    if(result.isConfirmed){
                        try {
                            const data = new FormData()
                            data.append('id', idArchivo)
                            const res = await fetchDataJson('./endpoints/delete_file.php', data, 'POST')
                            if(!res.success) throw new Error(res.message)

                            Swal.fire('Eliminado', data.message, 'success');
                            $("#archivo-" + idArchivo).remove();

                        } catch (error) {
                            Swal.fire('Error', error.message, 'error');
                        }
                    }
                }).catch((err) => Swal.fire('Error al procesar solicitud', err.message, 'error'))
        }
    </script>
</body>
</html>
