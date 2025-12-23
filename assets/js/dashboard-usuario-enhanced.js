// assets/js/dashboard-usuario-enhanced.js
// Mejoras para el dashboard de usuario con confirmación de incidencias

/**
 * Renderizar lista de incidencias con botón de confirmación
 */
function renderIncidentsEnhanced(incidents) {
    const container = document.getElementById('incidents-list');

    if (incidents.length === 0) {
        container.innerHTML = `
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-clipboard-check text-4xl mb-4 text-gray-300"></i>
                <p>No tienes incidencias reportadas</p>
                <a href="../index.php" class="text-teal-600 hover:text-teal-700 font-medium mt-2 inline-block">
                    Crear mi primer reporte
                </a>
            </div>
        `;
        return;
    }

    container.innerHTML = incidents.map(incident => {
        // Determinar si mostrar botón de confirmación
        const mostrarConfirmacion = incident.estado === 'en_verificacion' &&
            (!incident.confirmacion_usuario || incident.confirmacion_usuario === 'pendiente');

        // Badge de confirmación
        let badgeConfirmacion = '';
        if (incident.confirmacion_usuario === 'solucionado') {
            badgeConfirmacion = `
                <span class="text-xs text-green-600 font-semibold flex items-center gap-1 bg-green-50 px-3 py-1 rounded-full">
                    <i class="fas fa-check-double"></i>
                    Confirmado
                </span>
            `;
        } else if (incident.confirmacion_usuario === 'no_solucionado') {
            badgeConfirmacion = `
                <span class="text-xs text-orange-600 font-semibold flex items-center gap-1 bg-orange-50 px-3 py-1 rounded-full">
                    <i class="fas fa-exclamation-triangle"></i>
                    Reabierto
                </span>
            `;
        }

        return `
            <div class="p-6 hover:bg-gray-50 transition duration-150 border-b border-gray-100 last:border-0">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="bg-gray-100 text-gray-600 text-xs font-mono px-2 py-1 rounded">#${incident.id_incidencia}</span>
                            <h4 class="font-semibold text-gray-900">${incident.titulo}</h4>
                        </div>
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">${incident.descripcion}</p>
                        
                        ${incident.respuesta_solucion ? `
                            <div class="bg-teal-50 border-l-4 border-teal-500 p-3 mb-3 rounded">
                                <p class="text-xs font-semibold text-teal-900 mb-1">
                                    <i class="fas fa-comment-dots mr-1"></i>Respuesta del técnico:
                                </p>
                                <p class="text-sm text-teal-800">${incident.respuesta_solucion}</p>
                            </div>
                        ` : ''}
                        
                        <div class="flex flex-wrap gap-3 text-xs text-gray-500">
                            <span class="flex items-center">
                                <i class="far fa-calendar-alt mr-1"></i>
                                ${new Date(incident.fecha_reporte).toLocaleDateString()}
                            </span>
                            <span class="flex items-center">
                                <i class="fas fa-tag mr-1"></i>
                                ${incident.tipo_nombre || 'General'}
                            </span>
                            ${incident.tecnico_asignado ? `
                                <span class="flex items-center text-teal-600">
                                    <i class="fas fa-user-shield mr-1"></i>
                                    ${incident.tecnico_asignado}
                                </span>
                            ` : ''}
                        </div>
                    </div>
                    
                    <div class="flex flex-row md:flex-col items-center md:items-end gap-3">
                        <span class="px-3 py-1 rounded-full text-xs font-medium ${getStatusColor(incident.estado)}">
                            ${formatStatus(incident.estado)}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-medium ${getPriorityColor(incident.prioridad)}">
                            ${incident.prioridad.toUpperCase()}
                        </span>
                        
                        ${mostrarConfirmacion ? `
                            <button class="btn-confirmar-solucion bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white px-4 py-2 rounded-lg text-xs font-semibold transition duration-200 flex items-center gap-2 shadow-lg shadow-teal-500/30 transform hover:scale-105"
                                data-id-incidencia="${incident.id_incidencia}">
                                <i class="fas fa-check-circle"></i>
                                ¿Está Solucionado?
                            </button>
                        ` : badgeConfirmacion}
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Helpers de color (si no existen en el archivo principal)
function getStatusColor(status) {
    const colors = {
        'abierta': 'bg-red-100 text-red-800',
        'en_proceso': 'bg-amber-100 text-amber-800',
        'en_verificacion': 'bg-blue-100 text-blue-800',
        'cerrada': 'bg-emerald-100 text-emerald-800',
        'cancelada': 'bg-gray-100 text-gray-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function getPriorityColor(priority) {
    const colors = {
        'baja': 'bg-blue-100 text-blue-800',
        'media': 'bg-indigo-100 text-indigo-800',
        'alta': 'bg-orange-100 text-orange-800',
        'critica': 'bg-rose-100 text-rose-800'
    };
    return colors[priority] || 'bg-gray-100 text-gray-800';
}

function formatStatus(status) {
    return status.replace('_', ' ').toUpperCase();
}

// Exportar para uso global
if (typeof window !== 'undefined') {
    window.renderIncidentsEnhanced = renderIncidentsEnhanced;
    window.getStatusColor = getStatusColor;
    window.getPriorityColor = getPriorityColor;
    window.formatStatus = formatStatus;
}
