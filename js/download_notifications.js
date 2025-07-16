function handleResponse(response) {
    if (!response.ok) {
        return response.text().then(text => {
            throw new Error(`HTTP ${response.status}: ${text}`);
        });
    }
    
    const contentType = response.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
        throw new TypeError('Respuesta no es JSON');
    }
    
    return response.json();
}

function updateBadge() {
    let refreshInterval = 5000;
    let errorCount = 0;
    
    fetch('endpoints/get_pending_request.php')
        .then(handleResponse)
        .then(data => {
            if (!data.success) throw new Error(data.error);
            
            const badge = document.getElementById('pending-badge');
            badge.textContent = data.total || '';
            badge.classList.toggle('has-pending', data.total > 0);
            
            refreshInterval = data.total > 0 ? 30000 : 60000;
            errorCount = 0;
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            errorCount++;
            if(errorCount > 3) {
                refreshInterval = 120000;
                console.warn('Intervalo aumentado por errores consecutivos');
            }
        })
        .finally(() => {
            setTimeout(updateBadge, refreshInterval);
        });
}

document.addEventListener('DOMContentLoaded', updateBadge);