<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - Clínica Carita Feliz</title>
    <link rel="icon" href="../assets/images/logo.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include '../includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-6 text-sm">
            <a href="dashboard_usuario.php" class="text-teal-600 hover:text-teal-700">Dashboard</a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-600">Notificaciones</span>
        </nav>

        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    <i class="fas fa-bell text-teal-600 mr-3"></i>
                    Mis Notificaciones
                </h1>
                <p class="text-gray-600">Historial completo de notificaciones</p>
            </div>
            <button id="mark-all-read-btn" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-check-double mr-2"></i>
                Marcar todas como leídas
            </button>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="flex gap-4">
                <button onclick="filterNotifications('all')" class="filter-btn active px-4 py-2 rounded-lg transition duration-200">
                    Todas
                </button>
                <button onclick="filterNotifications('unread')" class="filter-btn px-4 py-2 rounded-lg transition duration-200">
                    No leídas
                </button>
                <button onclick="filterNotifications('read')" class="filter-btn px-4 py-2 rounded-lg transition duration-200">
                    Leídas
                </button>
            </div>
        </div>

        <!-- Lista de Notificaciones -->
        <div id="notifications-container" class="space-y-4">
            <div class="text-center py-12">
                <i class="fas fa-spinner fa-spin text-4xl text-teal-600 mb-4"></i>
                <p class="text-gray-600">Cargando notificaciones...</p>
            </div>
        </div>
    </div>

    <style>
        .filter-btn {
            background: #f3f4f6;
            color: #6b7280;
        }
        .filter-btn:hover {
            background: #e5e7eb;
        }
        .filter-btn.active {
            background: #0D9488;
            color: white;
        }
    </style>

    <script>
        const API_URL = '../api/controllers/notificaciones.php';
        let currentFilter = 'all';
        let allNotifications = [];

        async function loadNotifications() {
            try {
                const response = await fetch(`${API_URL}?action=list`);
                const result = await response.json();
                
                if (result.success) {
                    allNotifications = result.notificaciones || [];
                    renderNotifications();
                } else {
                    showError('Error al cargar notificaciones');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Error de conexión');
            }
        }

        
        function getCleanMessage(htmlMessage) {
            if (!htmlMessage) return '';
            
            // Create a temporary div to parse HTML
            const temp = document.createElement('div');
            temp.innerHTML = htmlMessage;
            
            // Extract only the text content, which will properly decode HTML entities
            let text = temp.textContent || temp.innerText || '';
            
            // Remove extra whitespace and newlines
            text = text.replace(/\s+/g, ' ').trim();
            
            // Limit length for display - REMOVED LIMIT
            // if (text.length > 200) {
            //    text = text.substring(0, 200) + '...';
            // }
            
            return text;
        }
        
        function renderNotifications() {
            const container = document.getElementById('notifications-container');
            
            let filtered = allNotifications;
            if (currentFilter === 'unread') {
                filtered = allNotifications.filter(n => !n.leida);
            } else if (currentFilter === 'read') {
                filtered = allNotifications.filter(n => n.leida);
            }
            
            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="bg-white rounded-lg shadow-md p-12 text-center">
                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay notificaciones</h3>
                        <p class="text-gray-500">No tienes notificaciones ${currentFilter === 'unread' ? 'sin leer' : currentFilter === 'read' ? 'leídas' : ''}</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = filtered.map(notif => `
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-200 ${!notif.leida ? 'border-l-4 border-teal-500' : ''}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                ${getNotificationIcon(notif.tipo_notificacion)}
                                <h3 class="text-lg font-semibold text-gray-900">${notif.asunto}</h3>
                                ${!notif.leida ? '<span class="bg-teal-100 text-teal-800 text-xs px-2 py-1 rounded-full">No leída</span>' : ''}
                            </div>
                            <div class="text-gray-700 mb-3">${getCleanMessage(notif.mensaje)}</div>
                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                <span><i class="far fa-clock mr-1"></i>${formatDate(notif.fecha_envio)}</span>
                                ${notif.id_incidencia ? `<span><i class="fas fa-ticket-alt mr-1"></i>Incidencia #${notif.id_incidencia}</span>` : ''}
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            ${!notif.leida ? `
                                <button onclick="markAsRead(${notif.id_notificacion})" class="text-teal-600 hover:text-teal-700 text-sm">
                                    <i class="fas fa-check mr-1"></i>Marcar leída
                                </button>
                            ` : ''}
                            <button onclick="deleteNotification(${notif.id_notificacion})" class="text-red-600 hover:text-red-700 text-sm">
                                <i class="fas fa-trash mr-1"></i>Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function getNotificationIcon(tipo) {
            const icons = {
                'asignacion': '<i class="fas fa-user-plus text-blue-500 text-2xl"></i>',
                'cambio_estado': '<i class="fas fa-sync-alt text-orange-500 text-2xl"></i>',
                'confirmacion': '<i class="fas fa-check-circle text-green-500 text-2xl"></i>',
                'cierre': '<i class="fas fa-times-circle text-gray-500 text-2xl"></i>'
            };
            return icons[tipo] || '<i class="fas fa-bell text-gray-500 text-2xl"></i>';
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);

            if (minutes < 1) return 'Ahora mismo';
            if (minutes < 60) return `Hace ${minutes} minuto${minutes > 1 ? 's' : ''}`;
            if (hours < 24) return `Hace ${hours} hora${hours > 1 ? 's' : ''}`;
            if (days < 7) return `Hace ${days} día${days > 1 ? 's' : ''}`;
            return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }

        function filterNotifications(filter) {
            currentFilter = filter;
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            renderNotifications();
        }

        async function markAsRead(id) {
            try {
                const response = await fetch(`${API_URL}?action=mark_read`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id_notificacion: id })
                });
                
                if (response.ok) {
                    await loadNotifications();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function deleteNotification(id) {
            if (!confirm('¿Eliminar esta notificación?')) return;
            
            try {
                const response = await fetch(`${API_URL}?action=delete`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id_notificacion: id })
                });
                
                if (response.ok) {
                    await loadNotifications();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        document.getElementById('mark-all-read-btn').addEventListener('click', async () => {
            try {
                const response = await fetch(`${API_URL}?action=mark_all_read`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'}
                });
                
                if (response.ok) {
                    await loadNotifications();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });

        function showError(message) {
            document.getElementById('notifications-container').innerHTML = `
                <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-3"></i>
                        <p class="text-red-700">${message}</p>
                    </div>
                </div>
            `;
        }

        // Cargar al iniciar
        loadNotifications();
    </script>
</body>
</html>
