<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - Clínica Carita Feliz</title>
    <link rel="icon" href="../assets/images/logo.ico" type="image/x-icon">
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Componente de Confirmación de Usuario -->
    <script src="../assets/js/confirmacion-usuario.js" defer></script>
    <script src="../assets/js/dashboard-usuario-enhanced.js" defer></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-md border-b border-teal-100 fixed w-full top-0 z-30">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo y título -->
                <div class="flex items-center space-x-3">
                    <img src="../assets/images/logo.png" alt="Logo" class="h-10 w-10 rounded-xl shadow-lg">
                    <div>
                        <h1 class="text-xl font-bold text-teal-900">Clínica Carita Feliz</h1>
                        <p class="text-xs text-teal-600">Panel de Usuario</p>
                    </div>
                </div>
                
                <!-- Usuario y controles -->
                <div class="flex items-center space-x-4">
                    <div id="user-info" class="flex items-center space-x-2 bg-teal-50 text-teal-800 px-3 py-2 rounded-xl border border-teal-200">
                        <div class="w-8 h-8 bg-gradient-to-br from-teal-400 to-teal-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <span id="user-name" class="font-medium text-sm">Usuario</span>
                    </div>
                    <a href="../index.php" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl transition duration-200 shadow-lg shadow-teal-500/30 flex items-center">
                        <i class="fas fa-plus-circle mr-2"></i>
                        <span class="hidden sm:inline">Nuevo Reporte</span>
                    </a>
                    <!-- Notification Badge -->
                    <div id="notification-badge-container"></div>
                    <script src="../assets/js/notification-badge.js" defer></script>
                    <button id="logout-btn" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-xl transition duration-200 shadow-lg shadow-red-500/30">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 pt-24 pb-12">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Mis Incidencias</h2>
            <p class="text-gray-600">Consulta el estado de tus reportes técnicos</p>
        </div>

        <!-- Stats Cards -->
        <div id="stats-cards" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Loading state -->
            <div class="col-span-full text-center py-8 text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2"></i>Cargando estadísticas...
            </div>
        </div>

        <!-- Incidents List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Historial de Reportes</h3>
                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full" id="total-records">0 registros</span>
            </div>
            <div id="incidents-list" class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                <!-- Loading state -->
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Cargando incidencias...
                </div>
            </div>
        </div>
    </main>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50"></div>

    <script>
        const INCIDENCIAS_API = '../api/controllers/incidencias.php';
        let currentUser = null;

        // Verificar sesión
        function checkSession() {
            const userSession = localStorage.getItem('user_session') || sessionStorage.getItem('user_session');
            if (!userSession) {
                window.location.href = '../views/login.php';
                return;
            }

            try {
                currentUser = JSON.parse(userSession);
                if (currentUser.rol !== 'usuario') {
                    // Si es admin o tecnico, redirigir al dashboard principal
                    window.location.href = 'dashboard.php';
                    return;
                }
                
                // Validar que exista ID de usuario
                if (!currentUser.id_usuario && !currentUser.id) {
                    console.error('Sesión inválida: Falta ID de usuario');
                    localStorage.removeItem('user_session');
                    sessionStorage.removeItem('user_session');
                    window.location.href = '../views/login.php';
                    return;
                }

                document.getElementById('user-name').textContent = currentUser.nombre || currentUser.username;
                loadData();
            } catch (e) {
                console.error('Error de sesión:', e);
                window.location.href = '../views/login.php';
            }
        }

        // Cargar datos
        async function loadData() {
            await Promise.all([
                loadStats(),
                loadIncidents()
            ]);
        }

        // Cargar estadísticas
        async function loadStats() {
            try {
                const response = await fetch(`${INCIDENCIAS_API}?action=stats&id_usuario=${currentUser.id_usuario || currentUser.id}`);
                const result = await response.json();
                
                if (result.stats) {
                    renderStats(result.stats);
                }
            } catch (error) {
                console.error('Error cargando stats:', error);
            }
        }

        // Renderizar estadísticas
        function renderStats(stats) {
            let abiertas = 0, proceso = 0, cerradas = 0;
            
            if (stats.por_estado) {
                stats.por_estado.forEach(item => {
                    if (item.estado === 'abierta') abiertas = item.count;
                    if (item.estado === 'en_proceso') proceso = item.count;
                    if (item.estado === 'cerrada') cerradas = item.count;
                });
            }

            const container = document.getElementById('stats-cards');
            container.innerHTML = `
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-red-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Abiertas</p>
                            <h3 class="text-3xl font-bold text-gray-800">${abiertas}</h3>
                        </div>
                        <div class="bg-red-50 p-3 rounded-full text-red-500">
                            <i class="fas fa-exclamation-circle text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-amber-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">En Proceso</p>
                            <h3 class="text-3xl font-bold text-gray-800">${proceso}</h3>
                        </div>
                        <div class="bg-amber-50 p-3 rounded-full text-amber-500">
                            <i class="fas fa-spinner text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-emerald-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Resueltas</p>
                            <h3 class="text-3xl font-bold text-gray-800">${cerradas}</h3>
                        </div>
                        <div class="bg-emerald-50 p-3 rounded-full text-emerald-500">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                    </div>
                </div>
            `;
        }

        // Cargar lista de incidencias
        async function loadIncidents() {
            try {
                const response = await fetch(`${INCIDENCIAS_API}?action=by_user&id_usuario=${currentUser.id_usuario || currentUser.id}`);
                const result = await response.json();
                
                renderIncidents(result.records || []);
                document.getElementById('total-records').textContent = `${result.total || 0} registros`;
            } catch (error) {
                console.error('Error cargando incidencias:', error);
                document.getElementById('incidents-list').innerHTML = `
                    <div class="p-8 text-center text-red-500">
                        <i class="fas fa-exclamation-triangle mb-2"></i>
                        <p>Error al cargar los datos</p>
                    </div>
                `;
            }
        }

        // Renderizar lista (usar función mejorada)
        function renderIncidents(incidents) {
            // Usar la función mejorada del archivo dashboard-usuario-enhanced.js
            if (typeof renderIncidentsEnhanced === 'function') {
                renderIncidentsEnhanced(incidents);
            } else {
                // Fallback básico si el script no carga
                console.error('renderIncidentsEnhanced no está disponible');
                const container = document.getElementById('incidents-list');
                container.innerHTML = incidents.map(incident => `
                    <div class="p-6 hover:bg-gray-50 transition duration-150">
                        <h4 class="font-semibold">#${incident.id_incidencia} - ${incident.titulo}</h4>
                        <p class="text-sm text-gray-600">${incident.descripcion}</p>
                        <span class="text-xs">${incident.estado}</span>
                    </div>
                `).join('');
            }
        }

        // Helpers de color y formato
        function getStatusColor(status) {
            const colors = {
                'abierta': 'bg-red-100 text-red-800',
                'en_proceso': 'bg-amber-100 text-amber-800',
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

        // Logout
        document.getElementById('logout-btn').addEventListener('click', () => {
            if(confirm('¿Cerrar sesión?')) {
                localStorage.removeItem('user_session');
                sessionStorage.removeItem('user_session');
                localStorage.removeItem('remember_user');
                window.location.href = '../index.php';
            }
        });

        // Iniciar
        checkSession();
    </script>
</body>
</html>
