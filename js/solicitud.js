// Función para actualizar el contador
function actualizarContadorSolicitudes() {
    fetch('endpoints/endpointNotificaciones.php?obtener_dato=1')
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta');
            return response.json();
        })
        .then(data => {
            const badge = document.getElementById('solicitudes-badge');

            if (!badge) {
                console.warn('Elemento solicitudes-badge no encontrado en el DOM.');
                return;
            }

            if (data.count > 0) {
                badge.textContent = data.count;
                badge.classList.add('activo');
                badge.setAttribute('aria-label', `${data.count} solicitudes pendientes`);
            } else {
                badge.textContent = '';
                badge.classList.remove('activo');
                badge.removeAttribute('aria-label');
            }
        })
        .catch(error => {
            console.error('Error al obtener solicitudes:', error);
        });
}


// Actualizar cada 60 segundos con backoff exponencial
function programarActualizacion() {
    let intervalo = 60000;
    setTimeout(() => {
        actualizarContadorSolicitudes()
            .finally(programarActualizacion);
        intervalo = Math.min(intervalo * 1.5, 300000); // Máximo 5 minutos
    }, intervalo);
}

// Iniciar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    actualizarContadorSolicitudes();
    programarActualizacion();
});