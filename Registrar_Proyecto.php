<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REGISTRO DE PROYECTOS</title>
    <link href="styles/bootstrap.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="styles/dropzone.css"> REMOVED DROPZONE CSS -->
    <link rel="stylesheet" href="styles/fontawesome/css/all.css">
    <link rel="stylesheet" href="styles/validation.css">
    <link rel="stylesheet" href="styles/registrar_proyecto.css">
    <style>
      
    </style>
</head>
<body>
    <div class="main-container">
        <?php include 'panel.php'; ?>
        <div class="registro-container-main">
            <div class="registro-card-registro">
                <div class="registro-card-header">
                    <h2>Registrar Nuevo Proyecto</h2>
                </div>
                <div class="registro-card-body">
                    <form id="formProyecto" action="endpoints/create_project.php" method="POST" enctype="multipart/form-data" novalidate>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="registro-form-group">
                                    <label class="registro-form-label" for="titulo">Título del Proyecto <strong class='text-danger'>*</strong></label>
                                    <input type="text" id="titulo" name="titulo" class="registro-form-control text-uppercase" maxlength="150" placeholder="Escribe el título en mayúsculas" required>
                                    <small class="registro-rule">Obligatorio. </small>
                                    <div class="registro-invalid-feedback">El título es obligatorio.</div>
                                </div>
                                <div class="registro-form-group">
                                    <label class="registro-form-label" for="autores">Autor(es) <strong class='text-danger'>*</strong></label>
                                    <input type="text" id="autores" name="autores" class="registro-form-control" maxlength="150" placeholder="Separar por comas: Juan, María" pattern="^([^,]+)(,\s*[^,]+)*$" required>
                                    <small class="registro-rule">Obligatorio. Separe autores con comas.</small>
                                    <div class="registro-invalid-feedback">Formato incorrecto. Separe nombres con comas.</div>
                                </div>
                                <div class="registro-form-group">
                                    <label class="registro-form-label" for="ente">Ente/Institución <strong class='text-danger'>*</strong></label>
                                    <input type="text" id="ente" name="ente" class="registro-form-control" maxlength="100" placeholder="Nombre de la institución" required>
                                    <small class="registro-rule">Obligatorio.</small>
                                    <div class="registro-invalid-feedback">Este campo es obligatorio.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="registro-form-group">
                                    <label class="registro-form-label" for="tutor">Tutor(es) <strong class='text-danger'>*</strong></label>
                                    <input type="text" id="tutor" name="tutor" class="registro-form-control" maxlength="150" placeholder="Separar por comas" pattern="^([^,]+)(,\s*[^,]+)*$" required>
                                    <small class="registro-rule">Obligatorio. Separe tutores con comas.</small>
                                    <div class="registro-invalid-feedback">Formato incorrecto.</div>
                                </div>
                                <div class="registro-form-group">
    <label class="registro-form-label" for="fecha">Fecha del Proyecto <strong class='text-danger'>*</strong></label>
    <input type="date" id="fecha" name="fecha" class="registro-form-control" min="2000-01-01" max="<?php echo date('Y-m-d'); ?>" required>
    <small class="registro-rule">Obligatorio. Formato: dd-mm-yyyy.</small>
    <div class="registro-invalid-feedback">Seleccione una fecha.</div>
</div>
                                <div class="registro-form-group">
                                    <label class="registro-form-label" for="linea">Línea de Investigación <strong class='text-danger'>*</strong></label>
                                    <select id="linea" name="linea_investigacion" class="registro-form-select" required>
                                        <option value="">-- Selecciona --</option>
                                        <option>Desarrollo y Caracterización de materiales</option>
                                        <option>Materiales compuestos y avanzados</option>
                                        <option>Integridad Mecánica</option>
                                        <option>Tribología y desgaste</option>
                                        <option>Aseguramiento de calidad</option>
                                        <option>Corrosión y oxidación</option>
                                    </select>
                                    <small class="registro-rule">Obligatorio.</small>
                                    <div class="registro-invalid-feedback">Selecciona una línea.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="registro-form-group">
                                    <label class="registro-form-label" for="descripcion">Resumen <strong class='text-danger'>*</strong></label>
                                    <textarea id="descripcion" name="descripcion" class="registro-form-control" placeholder="Máximo 512 palabras" rows="5" maxlength="4000" required></textarea>
                                    <small class="registro-rule" id="contadorResumen">Obligatorio. Máximo 512 palabras, 0 de 512 palabras</small>
                                    <div class="registro-invalid-feedback">Resumen inválido o excede 512 palabras.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <h5 class="registro-form-label">Documentos Principales <span class="text-danger">*</span></h5>
                                <!-- Custom file input for main documents -->
                                <div class="file-input-wrapper">
                                    <input type="file" id="documentosInput" name="documentos[]" multiple class="registro-form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
                                </div>
                                <div id="documentosPreview" class="file-preview-container empty"></div>
                                <small class="registro-rule">Al menos un archivo PDF, DOC, DOCX, PPT, PPTX, XLS o XLSX (máx. 50MB).</small>
                                <div id="docError" class="registro-invalid-feedback">Sube al menos un documento principal.</div>
                            </div>
                            <div class="col-12">
                                <h5 class="registro-form-label mt-4">Referencias (opcional)</h5>
                                <!-- Custom file input for references -->
                                <div class="file-input-wrapper">
                                    <input type="file" id="referenciasInput" name="referencias[]" multiple class="registro-form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip">
                                </div>
                                <div id="referenciasPreview" class="file-preview-container empty"></div>
                                <small class="registro-rule">PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX o ZIP (máx. 50MB) opcional.</small>
                            </div>
                            <div class="col-12 mt-5">
                                <div class="registro-button-container">
                                    <button id="btnSubmit" type="submit" class="registro-btn-primary btn-lg w-100">
                                        <i class="fas fa-save me-2"></i>Registrar Proyecto
                                    </button>
                                    <a href="dashboard.php" class="registro-btn-secondary w-100">
                                        <i class="fas fa-times me-2"></i>Cancelar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Alert Modal -->
    <div id="customAlertModal" class="modal-custom-overlay">
        <div class="modal-custom-content">
            <h4 id="customAlertTitle" class="modal-custom-title"></h4>
            <p id="customAlertMessage" class="modal-custom-message"></p>
            <button id="customAlertCloseBtn" class="modal-custom-button">OK</button>
        </div>
    </div>

    <script src="js/bootstrap.js"></script>
    <!-- <script src="js/dropzone.js"></script> REMOVED DROPZONE JS -->
    <script type='module'>
        import {ValidateWithRegex, validateForm, inputSuccess, inputError, createSpan} from './js/validator.js'

        // Función para mostrar el modal de alerta personalizado
        function showMessageModal(title, message) {
            const modal = document.getElementById('customAlertModal');
            document.getElementById('customAlertTitle').textContent = title;
            document.getElementById('customAlertMessage').textContent = message;
            modal.classList.add('show');
        }

        // Listener para cerrar el modal de alerta
        document.getElementById('customAlertCloseBtn').addEventListener('click', () => {
            document.getElementById('customAlertModal').classList.remove('show');
        });

        const validationRules = {
            'titulo':{
                required: {
                    value: true,
                    msg: 'El titulo es obligatorio'
                },
                stringLength: {
                    max:150,
                    maxmsg: 'El titulo debe de tener 150 caracteres como maximo'
                }
            },
            'ente': {
                required: {
                    value:true, 
                    msg: 'El ente/institucion es obligatoria'
                },
                stringLength: {
                    max:100,
                    maxmsg: 'El ente/institucion debe de contar con 100 caracteres como maximo'
                }
            },
            'autores': {
                required: {
                    value: true,
                    msg: 'Ingresa al menos a un autor valido'
                },
                stringLength: {
                    max:100,
                    maxmsg: 'Los autores en conjunto deben de contar con 100 caracteres como maximo'
                },
                Regex: {
                   value: /^((?:[A-Za-zÁÉÍÓÚáéíóúÑñ]+\.)?\s?[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?:\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+)*)(?:,\s((?:[A-Za-zÁÉÍÓÚáéíóúÑñ]+\.)?\s?[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?:\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+)*))*$/u,
                    msg: 'Formato invalido (ejemplo valido: "R afael P. Sogoviano, Manuel Contreras")'
                }
            },
            'tutor': {
                required: {
                    value: true,
                    msg: 'Ingresa al menos a un tutor valido'
                },
                stringLength: {
                    max:150,
                    maxmsg: 'Los autores en conjunto deben de contar con 150 caracteres como maximo'
                },
                Regex: {
                    value: /^((?:[A-Za-zÁÉÍÓÚáéíóúÑñ]+\.)?\s?[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?:\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+)*)(?:,\s((?:[A-Za-zÁÉÍÓÚáéíóúÑñ]+\.)?\s?[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?:\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+)*))*$/u,
                    msg: 'Formato invalido (ejemplo valido: "R afael P. Sogoviano, Manuel Contreras")'
                }
            },
            'fecha': {
    required: {
        value: true,
        msg: 'Ingrese la fecha de creación'
    },
    customValidation: {
        validate: (value) => {
            const today = new Date();
            const inputDate = new Date(value);

            // Validar que la fecha no sea mayor a hoy
            return inputDate <= today;
        },
        msg: 'La fecha no puede ser mayor a la fecha actual'
    }
},
            'resumen': {
                required: {
                    value: true,
                    msg: 'El resumen es obligatorio'
                }
            }
        }
        
        const resumen = document.getElementById('descripcion');
        const contador = document.getElementById('contadorResumen');
        resumen.addEventListener('input', function () {
            const maxWords = 512;
            const text = this.value.trim();
            const wordCount = text.split(/\s+/).filter(word => word.length > 0).length;

            contador.textContent = `Obligatorio. Máximo ${maxWords} palabras, ${wordCount} de ${maxWords} palabras`;

            if (wordCount > maxWords) {
                this.setCustomValidity('Excede el límite de palabras permitido.');
            } else {
                this.setCustomValidity('');
            }
        });

        // Custom File Upload Logic
        const MAX_FILE_SIZE_MB = 50; // 50 MB
        const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024; // Convert to bytes

        const ACCEPTED_DOC_TYPES = [
            'application/pdf', 
            'application/msword', // .doc
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            'application/vnd.ms-powerpoint', // .ppt
            'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
            'application/vnd.ms-excel', // .xls
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' // .xlsx
        ];

        const ACCEPTED_REF_TYPES = [
            ...ACCEPTED_DOC_TYPES,
            'application/zip', // .zip
            'application/x-zip-compressed' // .zip common alternative
        ];

        let selectedDocuments = [];
        let selectedReferences = [];

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function renderFilePreviews(filesArray, previewContainerId, acceptedTypes, maxSize) {
            const container = document.getElementById(previewContainerId);
            container.innerHTML = ''; // Clear existing previews

            if (filesArray.length === 0) {
                container.classList.add('empty');
            } else {
                container.classList.remove('empty');
                filesArray.forEach((file, index) => {
                    const isValidType = acceptedTypes.includes(file.type) || (file.name.match(/\.([0-9a-z]+)(?:[\?#]|$)/i) && acceptedTypes.some(type => file.name.endsWith(type.split('/')[1]))); // Basic fallback for common types
                    const isValidSize = file.size <= maxSize;
                    const isError = !isValidType || !isValidSize;

                    const fileItem = document.createElement('div');
                    fileItem.classList.add('file-preview-item');
                    if (isError) {
                        fileItem.classList.add('error');
                    }
                    fileItem.dataset.index = index;

                    fileItem.innerHTML = `
                        <div class="file-preview-name">${file.name}</div>
                        <div class="file-preview-size">${formatFileSize(file.size)}</div>
                        <button type="button" class="file-preview-remove" data-index="${index}">&#x2715;</button>
                        ${isError ? `<div class="file-error-message">${!isValidType ? 'Tipo de archivo no permitido.' : ''} ${!isValidSize ? 'Archivo demasiado grande.' : ''}</div>` : ''}
                    `;
                    container.appendChild(fileItem);
                });

                // Attach remove listeners
                container.querySelectorAll('.file-preview-remove').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const indexToRemove = parseInt(e.target.dataset.index);
                        if (previewContainerId === 'documentosPreview') {
                            selectedDocuments.splice(indexToRemove, 1);
                            renderFilePreviews(selectedDocuments, 'documentosPreview', ACCEPTED_DOC_TYPES, MAX_FILE_SIZE_BYTES);
                        } else if (previewContainerId === 'referenciasPreview') {
                            selectedReferences.splice(indexToRemove, 1);
                            renderFilePreviews(selectedReferences, 'referenciasPreview', ACCEPTED_REF_TYPES, MAX_FILE_SIZE_BYTES);
                        }
                    });
                });
            }
        }

        function handleFileInputChange(event, filesArray, previewContainerId, acceptedTypes, maxSize) {
            const files = Array.from(event.target.files);
            
            files.forEach(file => {
                const isValidType = acceptedTypes.includes(file.type) || (file.name.match(/\.([0-9a-z]+)(?:[\?#]|$)/i) && acceptedTypes.some(type => file.name.endsWith(type.split('/')[1])));
                const isValidSize = file.size <= maxSize;
                const isValidExtension = (fileName, allowed) => {
    const ext = fileName.split('.').pop().toLowerCase();
    const allowedExt = allowed.map(t => t.split('/').pop());
    return allowedExt.includes(ext);
};

                if (!isValidType) {
                    showMessageModal('Error de Archivo', `El archivo "${file.name}" no es del tipo permitido.`);
                } else if (!isValidSize) {
                    showMessageModal('Error de Archivo', `El archivo "${file.name}" excede el tamaño máximo de ${MAX_FILE_SIZE_MB}MB.`);
                } else {
                    filesArray.push(file);
                }
            });
            event.target.value = ''; // Clear the input so same file can be re-selected after removal/error
            renderFilePreviews(filesArray, previewContainerId, acceptedTypes, maxSize);
        }

        document.addEventListener('DOMContentLoaded', () => {
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

            const form = document.getElementById('formProyecto');
            form.addEventListener('submit', async e => { // Marked as async because fetch returns a Promise
                e.preventDefault();
                
                let valid = form.checkValidity() || validateForm(e.target, validationRules);
                
                // Comprobar al menos un documento principal
                if (selectedDocuments.length < 1) {
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

                // Procesar envío via AJAX
                const btn = document.getElementById('btnSubmit');
                btn.disabled = true; 
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
                
                const data = new FormData();

// Añadir manualmente SOLO los campos del formulario (no archivos)
const formFields = [
    'titulo', 'descripcion', 'autores', 
    'linea_investigacion', 'ente', 'tutor', 'fecha'
];

formFields.forEach(field => {
    data.append(field, form.elements[field].value);
});

// Añadir archivos
selectedDocuments.forEach(file => data.append('documentos[]', file));
selectedReferences.forEach(file => data.append('referencias[]', file));
                try {
                    const res = await fetch(form.action, { // form.action = "endpoints/create_project.php"
  method: 'POST',
  body: data // Incluye TODOS los datos + archivos
});
                    const resp = await res.json();
                    if (resp.success) {
                        window.location='dashboard.php';
                    } else {
                        showMessageModal('Error', resp.message);
                    }
                } catch (err) {
                    showMessageModal('Error', 'Ha ocurrido un error: ' + err.message);
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save me-2"></i>Registrar Proyecto';
                }
            });

            form.addEventListener('input', function(e){
                const field = e.target;
                const name = field.name || field.id;
                if (validationRules[name]) {
                    validateForm(form, { [name]: validationRules[name] }, validationRules);
                }
            })

            document.querySelectorAll('.registro-form-control, .registro-form-select, #descripcion').forEach(inp => {
                inp.addEventListener('focus', () => inp.nextElementSibling.style.display='block');
                inp.addEventListener('blur', () => inp.nextElementSibling.style.display='none');
            });
        });
    </script>
</body>
</html>
