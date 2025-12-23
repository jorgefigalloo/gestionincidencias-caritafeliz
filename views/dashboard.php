<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica Carita Feliz - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        teal: {
                            50: '#F0FDFA',
                            100: '#CCFBF1',
                            200: '#99F6E4',
                            300: '#5EEAD4',
                            400: '#2DD4BF',
                            500: '#14B8A6',
                            600: '#0D9488',
                            700: '#0F766E',
                            800: '#115E59',
                            900: '#134E4A',
                        }
                    }
                }
            }
        }
    </script>
    <link href="../assets/css/design-system.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link rel="icon" href="../assets/images/logo.ico" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gradient-to-br from-teal-50 to-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-md border-b border-teal-100 fixed w-full top-0 z-30">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo y título -->
                <div class="flex items-center space-x-4">
                    <button id="sidebar-toggle" class="lg:hidden text-teal-600 hover:text-teal-800">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div class="flex items-center space-x-3">
                        <img src="../assets/images/logo.png" alt="Logo" class="h-10 w-10 rounded-xl shadow-lg">
                        <div>
                            <h1 class="text-xl font-bold text-teal-900">Clínica Carita Feliz</h1>
                            <p class="text-xs text-teal-600">Dashboard de Gestión TI</p>
                        </div>
                    </div>
                </div>
                
                <!-- Usuario y controles -->
                <div class="flex items-center space-x-4">
                    <!-- Notificaciones -->
                    <div id="notification-badge-container"></div>

                    <div id="user-info" class="flex items-center space-x-2 bg-teal-50 text-teal-800 px-3 py-2 rounded-xl border border-teal-200">
                        <div class="w-8 h-8 bg-gradient-to-br from-teal-400 to-teal-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <span id="user-name" class="font-medium text-sm">Usuario</span>
                        <span id="user-role" class="text-xs bg-teal-200 text-teal-800 px-2 py-1 rounded-full font-medium">ROL</span>
                    </div>
                    <button id="chat-toggle" class="bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white px-4 py-2 rounded-xl transition duration-200 shadow-lg shadow-teal-500/30">
                        <i class="fas fa-robot mr-2"></i>
                        <span class="hidden sm:inline">Asistente</span>
                    </button>
                    <button id="index-btn" class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white px-4 py-2 rounded-xl transition duration-200 shadow-lg shadow-emerald-500/30">
                        <i class="fas fa-home mr-2"></i>
                        <span class="hidden sm:inline">Inicio</span>
                    </button>
                    <button id="logout-btn" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-xl transition duration-200 shadow-lg shadow-red-500/30">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed left-0 top-16 bottom-0 w-64 bg-white shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-20 border-r border-teal-100 overflow-y-auto">
        <div class="p-6">
            <h2 class="text-lg font-bold text-teal-900 mb-6">Menú de Gestión</h2>
            <nav class="space-y-2">
                <!-- Dashboard - siempre visible -->
                <a href="#" id="dashboard-tab" class="nav-link active" data-tab="dashboard">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                
                <!-- Responder Incidencias - admin y técnico -->
                <a href="#" id="respuesta-tab" class="nav-link" data-tab="respuesta">
                    <i class="fas fa-reply mr-3"></i>
                    Responder Incidencias
                </a>
                
                <!-- Reportes Avanzados - admin y técnico -->
                <a href="#" id="reportes-tab" class="nav-link" data-tab="reportes">
                    <i class="fas fa-chart-line mr-3"></i>
                    Reportes Avanzados
                </a>

                <!-- Secciones solo para admin -->
                <div id="admin-section" class="hidden">
                    <h3 class="text-sm font-semibold text-teal-500 uppercase tracking-wider mt-6 mb-3">Administración</h3>
                    
                    <a href="#" class="nav-link" data-tab="incidencias">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        Gestión Incidencias
                    </a>
                    
                    <a href="#" class="nav-link" data-tab="usuario">
                        <i class="fas fa-users mr-3"></i>
                        Usuarios
                    </a>
                    
                    <a href="#" class="nav-link" data-tab="sedes">
                        <i class="fas fa-building mr-3"></i>
                        Sedes
                    </a>
                    
                    <a href="#" class="nav-link" data-tab="areas">
                        <i class="fas fa-map-marker-alt mr-3"></i>
                        Áreas
                    </a>
                    
                    <a href="#" class="nav-link" data-tab="tipo_incidencia">
                        <i class="fas fa-tags mr-3"></i>
                        Tipos de Incidencia
                    </a>

                    <a href="#" class="nav-link" data-tab="subtipo_incidencia">
                        <i class="fas fa-tag mr-3"></i>
                        SubTipos de Incidencia
                    </a>
                    
                    <a href="#" class="nav-link" data-tab="rol_usuario">
                        <i class="fas fa-user-tag mr-3"></i>
                        Roles de Usuario
                    </a>
                </div>
                
                <!-- Reportes -->
                <h3 class="text-sm font-semibold text-teal-500 uppercase tracking-wider mt-6 mb-3">Reportes</h3>
                <button id="generate-report" class="nav-link w-full text-left">
                    <i class="fas fa-file-pdf mr-3"></i>
                    Generar Reporte PDF
                </button>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 pt-16 min-h-screen flex flex-col">
        <div class="p-6 flex-grow">
            <!-- Dashboard Tab -->
            <div id="dashboard-content" class="tab-content">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Dashboard Principal</h2>
                    <p class="text-gray-600">Resumen general del estado de incidencias técnicas</p>
                </div>

                <!-- Estadísticas Cards -->
                <div id="stats-cards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Las cards se llenarán dinámicamente -->
                    <div class="col-span-full text-center py-8 text-gray-500">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Cargando estadísticas...
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Gráfico de Estados -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Incidencias por Estado</h3>
                        <div class="h-64 flex justify-center">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Gráfico de Prioridades -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Incidencias por Prioridad</h3>
                        <div class="h-64 w-full">
                            <canvas id="priorityChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Incidencias Recientes -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Incidencias Recientes</h3>
                    <div id="recent-incidents" class="space-y-4">
                        <!-- Se llenarán dinámicamente -->
                        <div class="text-center py-4 text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Cargando incidencias recientes...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido dinámico para otras pestañas -->
            <div id="dynamic-content" class="tab-content hidden">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-center h-64">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-500">Cargando contenido...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <!-- Footer -->
        <footer class="bg-gray-200 border-t border-gray-300 mt-auto font-bold text-gray-800">
            <div class="container mx-auto px-6 py-4">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="flex items-center space-x-4 mb-4 md:mb-0">
                        <img src="../assets/images/logo.ico" alt="Logo" class="h-8 w-8">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Clínica Carita Feliz</h4>
                            <p class="text-xs text-gray-700">Sistema de Gestión TI</p>
                        </div>
                    </div>
                    <div class="text-center md:text-right">
                        <p class="text-sm text-gray-800">© 2025 Todos los derechos reservados.</p>
                        <p class="text-xs text-gray-700">Departamento de Tecnología de la Información</p>
                    </div>
                </div>
            </div>
        </footer>
    </main>

    <!-- Chat Assistant -->
    <div id="chat-container" class="fixed bottom-4 right-4 w-80 bg-white rounded-lg shadow-2xl hidden z-40">
        <div class="bg-blue-600 text-white p-4 rounded-t-lg flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fas fa-robot"></i>
                <span class="font-medium">Asistente Técnico</span>
            </div>
            <button id="chat-close" class="text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="chat-messages" class="h-80 overflow-y-auto p-4 space-y-3">
            <div class="flex items-start space-x-2">
                <div class="bg-blue-100 text-blue-800 p-2 rounded-full">
                    <i class="fas fa-robot text-sm"></i>
                </div>
                <div class="bg-gray-100 rounded-lg p-3 max-w-xs">
                    <p class="text-sm">¡Hola! Soy tu asistente técnico. Puedo ayudarte con problemas de hardware, software, conectividad y mantenimiento. ¿En qué puedo ayudarte?</p>
                </div>
            </div>
        </div>
        
        <div class="p-4 border-t">
            <div class="flex space-x-2">
                <input 
                    type="text" 
                    id="chat-input" 
                    placeholder="Escribe tu pregunta..." 
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                <button id="chat-send" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Overlay para cerrar sidebar en mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-10 hidden lg:hidden"></div>

    <!-- Access Denied Message -->
    <div id="access-denied" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center">
            <i class="fas fa-lock text-red-500 text-4xl mb-4"></i>
            <h2 class="text-xl font-bold text-gray-800 mb-2">Acceso Denegado</h2>
            <p class="text-gray-600 mb-4">Solo administradores y técnicos pueden acceder al dashboard.</p>
            <button onclick="window.location.href='../views/login.php'" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Iniciar Sesión
            </button>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50"></div>

    <style>
        .nav-link {
            @apply flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition duration-200 cursor-pointer;
        }
        .nav-link.active {
            @apply bg-blue-100 text-blue-600 font-medium;
        }
        .tab-content {
            @apply block;
        }
        .tab-content.hidden {
            @apply hidden;
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    <script>
        // URLs de las APIs
        const INCIDENCIAS_API = '../api/controllers/incidencias.php';
        const CHAT_API = '../api/controllers/chat.php';
        
        // Variables globales
        let currentUser = null;
        let incidenciasData = [];
        let chatMessagesData = [];
        let isAdmin = false;

        // Elementos del DOM
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const userInfo = document.getElementById('user-info');
        const userName = document.getElementById('user-name');
        const userRole = document.getElementById('user-role');
        const accessDenied = document.getElementById('access-denied');
        
        // Chat elements
        const chatToggle = document.getElementById('chat-toggle');
        const chatContainer = document.getElementById('chat-container');
        const chatClose = document.getElementById('chat-close');
        const chatMessages = document.getElementById('chat-messages');
        const chatInput = document.getElementById('chat-input');
        const chatSend = document.getElementById('chat-send');
        
        // Tabs
        const dashboardTab = document.getElementById('dashboard-tab');
        const respuestaTab = document.getElementById('respuesta-tab');
        const reportesTab = document.getElementById('reportes-tab');
        const adminSection = document.getElementById('admin-section');
        
        // Content areas
        const dashboardContent = document.getElementById('dashboard-content');
        const dynamicContent = document.getElementById('dynamic-content');

        // Función para mostrar toast
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            
            toast.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg mb-4 transform transition-all duration-300 translate-x-full`;
            toast.textContent = message;
            
            const container = document.getElementById('toast-container');
            if (container) {
                container.appendChild(toast);
                setTimeout(() => toast.classList.remove('translate-x-full'), 100);
                setTimeout(() => {
                    toast.classList.add('translate-x-full');
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            } else {
                console.error('Toast container not found');
                alert(message);
            }
        }

        // Verificar autenticación y permisos
        function checkAuthentication() {
            const userSession = localStorage.getItem('user_session') || sessionStorage.getItem('user_session');
            
            if (!userSession) {
                accessDenied.classList.remove('hidden');
                return false;
            }

            try {
                const session = JSON.parse(userSession);
                
                if (!session.username) {
                    accessDenied.classList.remove('hidden');
                    return false;
                }
                
                if (session.loginTime) {
                    const currentTime = new Date().getTime();
                    const timeDiff = currentTime - session.loginTime;
                    
                    if (timeDiff >= 24 * 60 * 60 * 1000) {
                        clearUserSession();
                        accessDenied.classList.remove('hidden');
                        return false;
                    }
                }
                
                // Permitir acceso a admin, tecnico y usuario
                const allowedRoles = ['admin', 'tecnico', 'usuario'];
                if (!allowedRoles.includes(session.rol)) {
                    showToast('Rol de usuario no autorizado', 'error');
                    setTimeout(() => window.location.href = '../index.php', 2000);
                    return false;
                }
                
                currentUser = session;
                isAdmin = session.rol === 'admin';
                updateUI();
                return true;
            } catch (e) {
                console.error('Error en checkAuthentication:', e);
                clearUserSession();
                accessDenied.classList.remove('hidden');
                return false;
            }
        }

        // Actualizar UI según permisos
        function updateUI() {
            if (!currentUser) return;
            
            let displayName = 'Usuario Desconocido';
            if (currentUser.nombre_completo) displayName = currentUser.nombre_completo;
            else if (currentUser.nombre) displayName = currentUser.nombre;
            else if (currentUser.username) displayName = currentUser.username;
            
            if (userName) userName.textContent = displayName;
            if (userRole) {
                userRole.textContent = currentUser.rol.toUpperCase();
                
                const roleColors = {
                    'admin': 'bg-red-100 text-red-800',
                    'tecnico': 'bg-blue-100 text-blue-800'
                };
                
                userRole.className = `text-xs px-2 py-1 rounded-full ${roleColors[currentUser.rol] || 'bg-gray-100 text-gray-800'}`;
            }
            
            if (isAdmin && adminSection) {
                adminSection.classList.remove('hidden');
            } else if (adminSection) {
                adminSection.classList.add('hidden');
            }
            
            // Ocultar tabs restringidos para usuario
            if (currentUser.rol === 'usuario') {
                if (respuestaTab) respuestaTab.classList.add('hidden');
                if (reportesTab) reportesTab.classList.add('hidden');
            } else {
                if (respuestaTab) respuestaTab.classList.remove('hidden');
                if (reportesTab) reportesTab.classList.remove('hidden');
            }
            
            if (accessDenied) accessDenied.classList.add('hidden');
        }

        // Limpiar sesión de usuario
        function clearUserSession() {
            localStorage.removeItem('user_session');
            sessionStorage.removeItem('user_session');
            localStorage.removeItem('remember_user');
            currentUser = null;
            isAdmin = false;
        }

        // Cargar estadísticas del dashboard
        async function loadDashboardStats() {
            try {
                let url = `${INCIDENCIAS_API}?action=stats`;
                // Solo filtrar por usuario si NO es admin Y NO es técnico
                if (!isAdmin && currentUser && currentUser.rol !== 'tecnico') {
                    url += `&id_usuario=${currentUser.id_usuario}`;
                }
                
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.stats) {
                    renderStatsCards(result.stats);
                    renderCharts(result.stats);
                } else {
                    console.error('No stats data in response:', result);
                    showToast('Error: Datos de estadísticas vacíos', 'error');
                }
            } catch (error) {
                console.error('Error cargando estadísticas:', error);
                showToast('Error cargando estadísticas: ' + error.message, 'error');
                document.getElementById('stats-cards').innerHTML = `
                    <div class="col-span-full text-center py-8 text-red-500">
                        <i class="fas fa-exclamation-circle mr-2"></i>Error al cargar datos. Verifique la conexión.
                    </div>
                `;
            }
        }

        // Renderizar cards de estadísticas
        function renderStatsCards(stats) {
            let total = 0, abiertas = 0, proceso = 0, cerradas = 0;
            
            if (stats.por_estado) {
                stats.por_estado.forEach(estado => {
                    total += parseInt(estado.count);
                    switch(estado.estado) {
                        case 'abierta': abiertas = estado.count; break;
                        case 'en_proceso': proceso = estado.count; break;
                        case 'cerrada': cerradas = estado.count; break;
                    }
                });
            }

            const container = document.getElementById('stats-cards');
            if (!container) return;

            // Helper para cards
            const createCard = (title, count, icon, colorClass, bgClass) => `
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 p-6 border border-gray-100 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="${bgClass} p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                            <i class="${icon} ${colorClass} text-xl"></i>
                        </div>
                        <span class="text-xs font-semibold ${colorClass} bg-opacity-10 bg-current px-2 py-1 rounded-full">
                            ${title === 'Total' ? 'General' : 'Estado'}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-gray-500 text-sm font-medium mb-1">${title}</h3>
                        <p class="text-3xl font-bold text-gray-800 tracking-tight">${count}</p>
                    </div>
                </div>
            `;

            container.innerHTML = `
                ${createCard('Total Incidencias', total, 'fas fa-clipboard-list', 'text-teal-600', 'bg-teal-50')}
                ${createCard('Abiertas', abiertas, 'fas fa-exclamation-circle', 'text-red-500', 'bg-red-50')}
                ${createCard('En Proceso', proceso, 'fas fa-spinner', 'text-amber-500', 'bg-amber-50')}
                ${createCard('Cerradas', cerradas, 'fas fa-check-circle', 'text-emerald-500', 'bg-emerald-50')}
            `;
        }

        // Variables globales para guardar las instancias de Chart.js
        let priorityChartInstance = null;
        let statusChartInstance = null;

        // Renderizar gráficos
        function renderCharts(stats) {
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded');
                return;
            }

            if (!stats.por_estado) return;

            // Configuración común
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = '#64748b';

            // Gráfico de estados
            const statusCanvas = document.getElementById('statusChart');
            if (statusCanvas) {
                const statusCtx = statusCanvas.getContext('2d');
                const statusData = {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: ['#ef4444', '#f59e0b', '#10b981', '#94a3b8'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                };

                stats.por_estado.forEach(estado => {
                    statusData.labels.push(estado.estado.replace('_', ' ').toUpperCase());
                    statusData.datasets[0].data.push(estado.count);
                });

                if (statusChartInstance) {
                    statusChartInstance.destroy();
                }

                statusChartInstance = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: statusData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Gráfico de prioridades
            const priorityCanvas = document.getElementById('priorityChart');
            if (priorityCanvas && stats.por_prioridad) {
                const priorityCtx = priorityCanvas.getContext('2d');
                const priorityData = {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: ['#10b981', '#f59e0b', '#f97316', '#ef4444'],
                        borderRadius: 6,
                        barThickness: 30
                    }]
                };

                stats.por_prioridad.forEach(prioridad => {
                    priorityData.labels.push(prioridad.prioridad.toUpperCase());
                    priorityData.datasets[0].data.push(prioridad.count);
                });

                if (priorityChartInstance) {
                    priorityChartInstance.destroy();
                }

                priorityChartInstance = new Chart(priorityCtx, {
                    type: 'bar',
                    data: priorityData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: true,
                                    drawBorder: false,
                                    color: '#f1f5f9'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        }

        // Cargar incidencias recientes
        async function loadRecentIncidents() {
            try {
                let url = `${INCIDENCIAS_API}?limit=5`;
                // Solo filtrar por usuario si NO es admin Y NO es técnico
                if (!isAdmin && currentUser && currentUser.rol !== 'tecnico') {
                    url = `${INCIDENCIAS_API}?action=by_user&id_usuario=${currentUser.id_usuario}`;
                }
                
                const response = await fetch(url);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                
                const result = await response.json();
                
                if (result.records) {
                    renderRecentIncidents(result.records);
                } else {
                    document.getElementById('recent-incidents').innerHTML = '<p class="text-gray-500 text-center py-4">No hay incidencias recientes</p>';
                }
            } catch (error) {
                console.error('Error cargando incidencias recientes:', error);
                document.getElementById('recent-incidents').innerHTML = '<p class="text-red-500 text-center py-4">Error al cargar incidencias</p>';
            }
        }

        // Renderizar incidencias recientes
        function renderRecentIncidents(incidents) {
            const container = document.getElementById('recent-incidents');
            if (!container) return;
            
            if (!incidents || incidents.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">No hay incidencias recientes</p>';
                return;
            }
            
            container.innerHTML = incidents.map(incident => {
                const tipoHtml = incident.tipo_nombre ? 
                    `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-50 text-teal-700 border border-teal-100">
                        <i class="fas fa-tag mr-1.5 text-[10px]"></i>${incident.tipo_nombre}
                     </span>` : '';
                
                const subtipoHtml = incident.subtipo_nombre ? 
                    `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                        <i class="fas fa-hashtag mr-1.5 text-[10px]"></i>${incident.subtipo_nombre}
                     </span>` : '';
                
                return `
                    <div class="group relative bg-white border border-gray-100 rounded-xl p-5 hover:shadow-md transition-all duration-300 hover:border-teal-100">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 text-gray-500 font-mono text-xs font-bold border border-gray-200">
                                        #${incident.id_incidencia}
                                    </span>
                                    <h4 class="text-base font-semibold text-gray-900 truncate group-hover:text-teal-600 transition-colors">
                                        ${incident.titulo}
                                    </h4>
                                </div>
                                
                                <p class="text-sm text-gray-500 mb-3 line-clamp-2 pl-11">
                                    ${incident.descripcion}
                                </p>
                                
                                <div class="flex flex-wrap items-center gap-3 pl-11">
                                    <div class="flex items-center text-xs text-gray-400">
                                        <i class="far fa-calendar-alt mr-1.5"></i>
                                        ${formatDate(incident.fecha_reporte)}
                                    </div>
                                    
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getEstadoClass(incident.estado)}">
                                        ${incident.estado.replace('_', ' ').toUpperCase()}
                                    </span>
                                    
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getPrioridadClass(incident.prioridad)}">
                                        ${incident.prioridad.toUpperCase()}
                                    </span>
                                </div>
                                
                                <div class="flex flex-wrap gap-2 mt-3 pl-11">
                                    ${tipoHtml}
                                    ${subtipoHtml}
                                </div>
                            </div>
                            
                            <div class="flex-shrink-0 self-center sm:self-start">
                                <button onclick="viewIncident(${incident.id_incidencia})" 
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-teal-700 bg-teal-50 hover:bg-teal-100 transition-colors duration-200">
                                    Ver Detalle
                                    <i class="fas fa-arrow-right ml-2 text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Funciones auxiliares
        function getEstadoClass(estado) {
            const classes = {
                'abierta': 'bg-red-50 text-red-700 border border-red-100',
                'en_proceso': 'bg-amber-50 text-amber-700 border border-amber-100',
                'en_verificacion': 'bg-blue-50 text-blue-700 border border-blue-100',
                'cerrada': 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                'cancelada': 'bg-gray-50 text-gray-700 border border-gray-100'
            };
            return classes[estado] || 'bg-gray-50 text-gray-700';
        }

        function getPrioridadClass(prioridad) {
            const classes = {
                'baja': 'bg-blue-50 text-blue-700 border border-blue-100',
                'media': 'bg-indigo-50 text-indigo-700 border border-indigo-100',
                'alta': 'bg-orange-50 text-orange-700 border border-orange-100',
                'critica': 'bg-rose-50 text-rose-700 border border-rose-100'
            };
            return classes[prioridad] || 'bg-gray-50 text-gray-700';
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Navegación entre tabs
        function switchTab(tabName) {
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            if (dashboardContent) dashboardContent.classList.add('hidden');
            if (dynamicContent) dynamicContent.classList.add('hidden');
            
            if (tabName === 'dashboard') {
                if (dashboardContent) dashboardContent.classList.remove('hidden');
                if (dashboardTab) dashboardTab.classList.add('active');
                loadDashboardStats();
                loadRecentIncidents();
            } else if (tabName === 'respuesta') {
                loadExternalContent('respuesta.php');
                if (respuestaTab) respuestaTab.classList.add('active');
            } else if (tabName === 'reportes') {
                loadExternalContent('graficos_reporte.php');
                if (reportesTab) reportesTab.classList.add('active');
            } else {
                if (isAdmin) {
                    loadExternalContent(`${tabName}.php`);
                    const tab = document.querySelector(`[data-tab="${tabName}"]`);
                    if (tab) tab.classList.add('active');
                } else {
                    showToast('Sin permisos para acceder a esta sección', 'error');
                }
            }
        }

        // Cargar contenido externo en iframe
        function loadExternalContent(fileName) {
            if (dynamicContent) {
                dynamicContent.classList.remove('hidden');
                dynamicContent.innerHTML = `
                    <div class="bg-white rounded-lg shadow-md h-full">
                        <iframe src="${fileName}" class="w-full h-screen border-0 rounded-lg"></iframe>
                    </div>
                `;
            }
        }

        // Ver detalles de incidencia
        function viewIncident(id) {
            switchTab('respuesta');
        }

        // Generar reporte PDF
        async function generateReport() {
            if (!currentUser) {
                showToast('Error: No hay usuario autenticado', 'error');
                return;
            }

            try {
                const reportBtn = document.getElementById('generate-report');
                const originalText = reportBtn.innerHTML;
                reportBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generando...';
                reportBtn.disabled = true;

                const response = await fetch('../generate_report.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        type: 'general',
                        user_id: currentUser.username
                    })
                });

                reportBtn.innerHTML = originalText;
                reportBtn.disabled = false;

                if (!response.ok) {
                    showToast('Error generando reporte', 'error');
                    return;
                }

                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);

                const a = document.createElement('a');
                a.href = url;
                a.download = 'reporte_incidencias.pdf';
                document.body.appendChild(a);
                a.click();

                a.remove();
                window.URL.revokeObjectURL(url);

                showToast('Reporte generado correctamente', 'success');
            } catch (error) {
                console.error('Error en generateReport:', error);
                showToast('Error al generar reporte', 'error');
            }
        }

        // Chat functionality
        let isTyping = false;

        function toggleChat() {
            if (chatContainer) {
                chatContainer.classList.toggle('hidden');
                if (!chatContainer.classList.contains('hidden') && chatInput) {
                    chatInput.focus();
                }
            }
        }

        function closeChat() {
            if (chatContainer) chatContainer.classList.add('hidden');
        }

        function addChatMessage(message, isUser = false) {
            if (!chatMessages) return;

            const messageDiv = document.createElement('div');
            messageDiv.className = `flex items-start space-x-2 ${isUser ? 'justify-end' : ''}`;
            
            const avatarClass = isUser ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600';
            const messageClass = isUser ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800';
            
            messageDiv.innerHTML = `
                ${!isUser ? `<div class="${avatarClass} p-2 rounded-full flex-shrink-0">
                    <i class="fas fa-robot text-sm"></i>
                </div>` : ''}
                <div class="${messageClass} rounded-lg p-3 max-w-xs">
                    <p class="text-sm whitespace-pre-wrap">${message}</p>
                </div>
                ${isUser ? `<div class="${avatarClass} p-2 rounded-full flex-shrink-0">
                    <i class="fas fa-user text-sm"></i>
                </div>` : ''}
            `;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function showTypingIndicator() {
            if (isTyping || !chatMessages) return;
            isTyping = true;
            
            const typingDiv = document.createElement('div');
            typingDiv.id = 'typing-indicator';
            typingDiv.className = 'flex items-start space-x-2';
            typingDiv.innerHTML = `
                <div class="bg-gray-100 text-gray-600 p-2 rounded-full">
                    <i class="fas fa-robot text-sm"></i>
                </div>
                <div class="bg-gray-100 rounded-lg p-3">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
            `;
            
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function hideTypingIndicator() {
            const typingIndicator = document.getElementById('typing-indicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
            isTyping = false;
        }

        async function sendChatMessage() {
            if (!chatInput) return;
            const message = chatInput.value.trim();
            if (!message) return;
            
            addChatMessage(message, true);
            chatInput.value = '';
            showTypingIndicator();
            
            try {
                const userSession = localStorage.getItem('user_session') || sessionStorage.getItem('user_session');
                
                if (!userSession) {
                    hideTypingIndicator();
                    addChatMessage('Error: No hay sesión activa. Por favor inicia sesión nuevamente.', false);
                    return;
                }
                
                const token = btoa(userSession);
                
                const response = await fetch(CHAT_API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({ message })
                });
                
                const responseText = await response.text();
                hideTypingIndicator();
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (jsonError) {
                    console.error('Error parseando JSON:', jsonError);
                    addChatMessage('Error del servidor. Revisa la consola para más detalles.', false);
                    return;
                }
                
                if (result.success) {
                    addChatMessage(result.reply, false);
                } else {
                    addChatMessage(result.message || 'Lo siento, no pude procesar tu mensaje.', false);
                }
            } catch (error) {
                hideTypingIndicator();
                console.error('Error en chat:', error);
                addChatMessage('Error de conexión. Por favor, intenta más tarde.', false);
            }
        }

        // Event Listeners
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                if (sidebar) sidebar.classList.toggle('-translate-x-full');
                if (sidebarOverlay) sidebarOverlay.classList.toggle('hidden');
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                if (sidebar) sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });
        }

        // Navigation
        if (dashboardTab) dashboardTab.addEventListener('click', () => switchTab('dashboard'));
        if (respuestaTab) respuestaTab.addEventListener('click', () => switchTab('respuesta'));
        if (reportesTab) reportesTab.addEventListener('click', () => switchTab('reportes'));

        // Admin navigation
        document.querySelectorAll('[data-tab]').forEach(tab => {
            tab.addEventListener('click', (e) => {
                const tabName = e.currentTarget.getAttribute('data-tab');
                switchTab(tabName);
            });
        });

        // Chat events
        if (chatToggle) chatToggle.addEventListener('click', toggleChat);
        if (chatClose) chatClose.addEventListener('click', closeChat);
        if (chatSend) chatSend.addEventListener('click', sendChatMessage);
        if (chatInput) {
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendChatMessage();
                }
            });
        }

        // Report generation
        const reportBtn = document.getElementById('generate-report');
        if (reportBtn) reportBtn.addEventListener('click', generateReport);

        // Navigation buttons
        const indexBtn = document.getElementById('index-btn');
        if (indexBtn) {
            indexBtn.addEventListener('click', () => {
                window.location.href = '../index.php';
            });
        }

        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                    clearUserSession();
                    showToast('Sesión cerrada correctamente', 'success');
                    setTimeout(() => {
                        window.location.href = '../index.php';
                    }, 1000);
                }
            });
        }

        // Make functions global for onclick handlers
        window.viewIncident = viewIncident;
        window.switchTab = switchTab;

        // Inicialización
        document.addEventListener('DOMContentLoaded', () => {
            if (checkAuthentication()) {
                switchTab('dashboard');
            }
        });
    </script>
    <script src="../assets/js/notification-badge.js"></script>
</body>
</html>