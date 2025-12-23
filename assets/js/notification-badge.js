// assets/js/notification-badge.js
// Componente de badge de notificaciones para el header

class NotificationBadge {
    constructor(containerId = 'notification-badge-container') {
        this.containerId = containerId;
        this.API_URL = '../api/controllers/notificaciones.php';
        this.unreadCount = 0;
        this.notifications = [];
        this.updateInterval = null;
        this.init();
    }

    init() {
        this.createBadgeHTML();
        this.loadUnreadCount();

        // Actualizar cada 30 segundos
        this.updateInterval = setInterval(() => {
            this.loadUnreadCount();
        }, 30000);

        // Event listeners
        document.addEventListener('click', (e) => {
            if (e.target.closest('#notification-bell')) {
                e.preventDefault();
                this.toggleDropdown();
            } else if (!e.target.closest('#notification-dropdown')) {
                this.closeDropdown();
            }
        });
    }

    createBadgeHTML() {
        const container = document.getElementById(this.containerId);
        if (!container) return;

        container.innerHTML = `
            <div class="relative">
                <button id="notification-bell" class="relative p-2 text-gray-600 hover:text-teal-600 transition duration-200">
                    <i class="fas fa-bell text-xl"></i>
                    <span id="notification-count" class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center animate-pulse">
                        0
                    </span>
                </button>
                
                <!-- Dropdown -->
                <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-2xl border border-gray-200 z-50 max-h-96 overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-teal-500 to-teal-600 text-white p-4 flex justify-between items-center">
                        <h3 class="font-bold flex items-center gap-2">
                            <i class="fas fa-bell"></i>
                            Notificaciones
                        </h3>
                        <div class="flex gap-3 items-center">
                            <a href="notificaciones.php" class="text-xs hover:underline text-teal-50">
                                Ver todas
                            </a>
                            <button id="mark-all-read" class="text-xs hover:underline">
                                Marcar todas
                            </button>
                        </div>
                    </div>
                    
                    <!-- Lista de notificaciones -->
                    <div id="notification-list" class="max-h-80 overflow-y-auto">
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                            <p class="text-sm">Cargando...</p>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="border-t border-gray-200 p-3 text-center">
                        <a href="notificaciones.php" class="text-teal-600 hover:text-teal-700 text-sm font-semibold">
                            Ver todas las notificaciones
                        </a>
                    </div>
                </div>
            </div>
        `;

        // Event listener para marcar todas como leídas
        document.getElementById('mark-all-read')?.addEventListener('click', () => {
            this.markAllAsRead();
        });
    }

    async loadUnreadCount() {
        try {
            const response = await fetch(`${this.API_URL}?action=unread_count`);
            const result = await response.json();

            if (result.success) {
                this.unreadCount = result.count;
                this.updateBadge();
            }
        } catch (error) {
            console.error('Error cargando contador de notificaciones:', error);
        }
    }

    updateBadge() {
        const badge = document.getElementById('notification-count');
        if (!badge) return;

        if (this.unreadCount > 0) {
            badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    async toggleDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        if (!dropdown) return;

        if (dropdown.classList.contains('hidden')) {
            await this.loadNotifications();
            dropdown.classList.remove('hidden');
            dropdown.classList.add('animate-fade-in');
        } else {
            this.closeDropdown();
        }
    }

    closeDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
    }

    async loadNotifications() {
        const listContainer = document.getElementById('notification-list');
        if (!listContainer) return;

        try {
            const response = await fetch(`${this.API_URL}?action=list&no_leidas=1`);
            const result = await response.json();

            if (result.success) {
                this.notifications = result.notificaciones;
                this.renderNotifications();
            }
        } catch (error) {
            console.error('Error cargando notificaciones:', error);
            listContainer.innerHTML = `
                <div class="p-8 text-center text-red-500">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <p class="text-sm">Error al cargar notificaciones</p>
                </div>
            `;
        }
    }

    renderNotifications() {
        const listContainer = document.getElementById('notification-list');
        if (!listContainer) return;

        if (this.notifications.length === 0) {
            listContainer.innerHTML = `
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-check-circle text-4xl mb-3 text-gray-300"></i>
                    <p class="text-sm">No tienes notificaciones nuevas</p>
                </div>
            `;
            return;
        }

        listContainer.innerHTML = this.notifications.map(notif => `
            <div class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition duration-150"
                onclick="notificationBadge.markAsRead(${notif.id_notificacion})">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        ${this.getNotificationIcon(notif.tipo_notificacion)}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            ${notif.asunto}
                        </p>
                        <p class="text-xs text-gray-600 line-clamp-2 mt-1">
                            ${notif.mensaje ? this.stripHTML(notif.mensaje).substring(0, 100) + '...' : ''}
                        </p>
                        <p class="text-xs text-gray-400 mt-2">
                            <i class="far fa-clock mr-1"></i>
                            ${this.formatDate(notif.fecha_envio)}
                        </p>
                    </div>
                    ${!notif.leida ? '<div class="w-2 h-2 bg-teal-500 rounded-full flex-shrink-0"></div>' : ''}
                </div>
            </div>
        `).join('');
    }

    getNotificationIcon(tipo) {
        const icons = {
            'asignacion': '<i class="fas fa-user-plus text-blue-500 text-lg"></i>',
            'cambio_estado': '<i class="fas fa-sync-alt text-orange-500 text-lg"></i>',
            'confirmacion': '<i class="fas fa-check-circle text-green-500 text-lg"></i>',
            'cierre': '<i class="fas fa-times-circle text-gray-500 text-lg"></i>'
        };
        return icons[tipo] || '<i class="fas fa-bell text-gray-500 text-lg"></i>';
    }

    async markAsRead(idNotificacion) {
        try {
            const response = await fetch(`${this.API_URL}?action=mark_read`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_notificacion: idNotificacion })
            });

            if (response.ok) {
                await this.loadUnreadCount();
                await this.loadNotifications();
            }
        } catch (error) {
            console.error('Error marcando notificación como leída:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch(`${this.API_URL}?action=mark_all_read`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });

            if (response.ok) {
                await this.loadUnreadCount();
                await this.loadNotifications();
            }
        } catch (error) {
            console.error('Error marcando todas como leídas:', error);
        }
    }

    stripHTML(html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText || '';
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Ahora';
        if (minutes < 60) return `Hace ${minutes}m`;
        if (hours < 24) return `Hace ${hours}h`;
        if (days < 7) return `Hace ${days}d`;
        return date.toLocaleDateString();
    }

    destroy() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
    }
}

// Inicializar automáticamente si existe el contenedor
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('notification-badge-container')) {
        window.notificationBadge = new NotificationBadge();
    }
});

// Añadir estilos de animación
const style = document.createElement('style');
style.textContent = `
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-fade-in {
        animation: fade-in 0.2s ease-out;
    }
`;
document.head.appendChild(style);
