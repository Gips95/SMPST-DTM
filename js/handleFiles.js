// Custom File Upload Logic
export const MAX_FILE_SIZE_MB = 50; // 50 MB
export const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024; // Convert to bytes

export const ACCEPTED_DOC_TYPES = [
    'application/pdf', 
    'application/msword', // .doc
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
    'application/vnd.ms-powerpoint', // .ppt
    'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
    'application/vnd.ms-excel', // .xls
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' // .xlsx
];

export const ACCEPTED_REF_TYPES = [
    ...ACCEPTED_DOC_TYPES,
    'application/zip', // .zip
    'application/x-zip-compressed' // .zip common alternative
];

export let selectedDocuments = [];
export let selectedReferences = [];

export function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

export function renderFilePreviews(filesArray, previewContainerId, acceptedTypes, maxSize, inputFiles) {
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
                const documentInput = document.getElementById('documentosInput')
                const referenceInput = document.getElementById('referenciasInput')

                let files = []

                if (previewContainerId === 'documentosPreview') {
                    selectedDocuments.splice(indexToRemove, 1);

                    files = Array.from(documentInput.files)
                    files.splice(indexToRemove, 1)
                    let dataT = new DataTransfer()
                    files.forEach(file => {
                        dataT.items.add(file)
                    })
                    documentInput.files = dataT.files;

                    renderFilePreviews(selectedDocuments, 'documentosPreview', ACCEPTED_DOC_TYPES, MAX_FILE_SIZE_BYTES);
                } else if (previewContainerId === 'referenciasPreview') {
                    selectedReferences.splice(indexToRemove, 1);

                    files = Array.from(referenceInput.files)
                    files.splice(indexToRemove, 1)
                    let dataT = new DataTransfer()
                    files.forEach(file => {
                        dataT.items.add(file)
                    })
                    referenceInput.files = dataT.files;

                    renderFilePreviews(selectedReferences, 'referenciasPreview', ACCEPTED_REF_TYPES, MAX_FILE_SIZE_BYTES);
                }
            });
        });
    }
}


export function handleFileInputChange(event, filesArray, previewContainerId, acceptedTypes, maxSize) {
    const files = Array.from(event.target.files);
    
    files.forEach(file => {
        const isValidType = acceptedTypes.includes(file.type) || (file.name.match(/\.([0-9a-z]+)(?:[\?#]|$)/i) && acceptedTypes.some(type => file.name.endsWith(type.split('/')[1])));
        const isValidSize = file.size <= maxSize;

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