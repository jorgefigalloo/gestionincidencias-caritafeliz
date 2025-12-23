<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica Carita Feliz - Responder Incidencias</title>
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
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/design-system.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link rel="icon" href="../assets/images/logo.ico" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-30">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-teal-50 p-2 rounded-xl">
                        <img src="../assets/images/logo.png" alt="Logo" class="h-8 w-8 rounded-lg">
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">Responder Incidencias</h1>
                        <p class="text-xs text-teal-600 font-medium">Gestión de Tickets</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div id="user-info" class="hidden md:flex items-center space-x-2 bg-gray-50 px-3 py-1.5 rounded-full border border-gray-100">
                        <div class="w-6 h-6 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center text-xs">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-600" id="current-user-name">Usuario</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Access Denied -->
        <div id="access-denied" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center">
            <div class="bg-white rounded-2xl p-8 max-w-md mx-4 shadow-2xl text-center">
                <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-2xl"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Acceso Restringido</h2>
                <p class="text-gray-500 mb-6">Esta sección es exclusiva para personal técnico y administrativo.</p>
                <a href="../index.php" class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-xl text-white bg-gray-900 hover:bg-gray-800 transition-colors">
                    Volver al Inicio
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div id="stats-section" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Loading Skeleton -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 animate-pulse h-32"></div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 animate-pulse h-32"></div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 animate-pulse h-32"></div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 animate-pulse h-32"></div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <!-- Search -->
                <div class="md:col-span-4 lg:col-span-5">
                    <label class="block text-xs font-medium text-gray-500 mb-1.5 uppercase tracking-wider">Búsqueda</label>
                    <div class="relative group">
                        <input type="text" id="search-input" 
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all"
                            placeholder="Buscar por título, ID, o usuario...">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 group-focus-within:text-teal-500 transition-colors"></i>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="md:col-span-8 lg:col-span-7 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5 uppercase tracking-wider">Estado</label>
                        <select id="filter-estado" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                            <option value="">Todos</option>
                            <option value="abierta">Abierta</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="en_verificacion">En Verificación</option>
                            <option value="cerrada">Cerrada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5 uppercase tracking-wider">Prioridad</label>
                        <select id="filter-prioridad" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                            <option value="">Todas</option>
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                            <option value="critica">Crítica</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5 uppercase tracking-wider">Técnico</label>
                        <select id="filter-tecnico" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                            <option value="">Todos</option>
                            <option value="sin-asignar">Sin asignar</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 flex justify-end">
                <button id="clear-filters" class="text-sm text-gray-500 hover:text-teal-600 font-medium flex items-center transition-colors">
                    <i class="fas fa-times-circle mr-1.5"></i>
                    Limpiar Filtros
                </button>
            </div>
        </div>

        <!-- Incidents List -->
        <div class="space-y-4">
            <div class="flex items-center justify-between px-2">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    Incidencias
                    <span id="total-incidencias" class="ml-3 px-2.5 py-0.5 rounded-full bg-teal-100 text-teal-700 text-xs font-bold">0</span>
                </h3>
                <div id="loading" class="hidden text-teal-600 text-sm font-medium flex items-center">
                    <i class="fas fa-circle-notch fa-spin mr-2"></i> Actualizando...
                </div>
            </div>

            <div id="incidencias-list" class="space-y-4">
                <!-- Incidents will be injected here -->
            </div>

            <!-- Empty State -->
            <div id="no-incidencias" class="hidden bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clipboard-check text-3xl text-gray-300"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No se encontraron incidencias</h3>
                <p class="text-gray-500 text-sm">Intenta ajustar los filtros de búsqueda</p>
            </div>
        </div>
    </main>

    <!-- Response Modal -->
    <div id="response-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                <!-- Modal Header -->
                <div class="bg-white px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900" id="modal-title">Responder Incidencia</h3>
                    <button id="close-modal" class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                    <!-- Incident Info -->
                    <div id="modal-incidencia-info" class="bg-gray-50 rounded-xl p-5 mb-6 border border-gray-100">
                        <!-- Dynamic Content -->
                    </div>

                    <!-- Response Form -->
                    <form id="response-form" class="space-y-6">
                        <input type="hidden" id="incidencia-id-modal">
                        
                        <div>
                            <label for="respuesta-modal" class="block text-sm font-medium text-gray-700 mb-2">
                                Respuesta / Solución Técnica <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <textarea id="respuesta-modal" name="respuesta" rows="5" required
                                    class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all placeholder-gray-400"
                                    placeholder="Detalla las acciones realizadas y la solución aplicada..."></textarea>
                                <div class="absolute bottom-3 right-3 text-gray-400 text-xs">
                                    <i class="fas fa-pen"></i>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="estado-modal" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nuevo Estado <span class="text-red-500">*</span>
                                </label>
                                <select id="estado-modal" name="estado" required
                                    class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                                    <option value="abierta">Abierta</option>
                                    <option value="en_proceso">En Proceso</option>
                                    <option value="en_verificacion">En Verificación (Solicitar Confirmación)</option>
                                    <option value="cerrada">Cerrada</option>
                                    <option value="cancelada">Cancelada</option>
                                </select>
                            </div>

                            <div>
                                <label for="prioridad-modal" class="block text-sm font-medium text-gray-700 mb-2">
                                    Prioridad
                                </label>
                                <select id="prioridad-modal" name="prioridad"
                                    class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                                    <option value="baja">Baja</option>
                                    <option value="media">Media</option>
                                    <option value="alta">Alta</option>
                                    <option value="critica">Crítica</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="tecnico-modal" class="block text-sm font-medium text-gray-700 mb-2">
                                Asignar Técnico
                            </label>
                            <select id="tecnico-modal" name="tecnico"
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                                <option value="">Sin asignar</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                    <button type="button" id="cancel-response"
                        class="w-full sm:w-auto px-6 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all">
                        Cancelar
                    </button>
                    <button type="button" id="submit-response"
                        class="w-full sm:w-auto px-6 py-2.5 bg-teal-600 border border-transparent text-white font-medium rounded-xl hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 shadow-lg shadow-teal-500/30 transition-all flex items-center justify-center">
                        <span id="submit-text">Guardar Cambios</span>
                        <i id="submit-loading" class="fas fa-circle-notch fa-spin ml-2 hidden"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Modal -->
    <div id="email-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="email-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <!-- Modal Header -->
                <div class="bg-white px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900" id="email-modal-title">Enviar Email al Usuario</h3>
                    <button id="close-email-modal" class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-6">
                    <!-- Incident Info -->
                    <div id="email-modal-incidencia-info" class="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-100">
                        <!-- Dynamic Content -->
                    </div>

                    <!-- Email Form -->
                    <form id="email-form" class="space-y-4">
                        <input type="hidden" id="email-incidencia-id">
                        
                        <div>
                            <label for="email-asunto" class="block text-sm font-medium text-gray-700 mb-2">
                                Asunto <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="email-asunto" required
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all"
                                placeholder="Asunto del correo...">
                        </div>

                        <div>
                            <label for="email-mensaje" class="block text-sm font-medium text-gray-700 mb-2">
                                Mensaje <span class="text-red-500">*</span>
                            </label>
                            <textarea id="email-mensaje" rows="6" required
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all placeholder-gray-400"
                                placeholder="Escribe tu mensaje personalizado al usuario..."></textarea>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                    <button type="button" id="cancel-email"
                        class="w-full sm:w-auto px-6 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all">
                        Cancelar
                    </button>
                    <button type="button" id="send-email"
                        class="w-full sm:w-auto px-6 py-2.5 bg-teal-600 border border-transparent text-white font-medium rounded-xl hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 shadow-lg shadow-teal-500/30 transition-all flex items-center justify-center">
                        <span id="send-email-text">Enviar Email</span>
                        <i id="send-email-loading" class="fas fa-circle-notch fa-spin ml-2 hidden"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2"></div>

    <script>
        // API Configuration
        const INCIDENCIAS_API = '../api/controllers/incidencias.php';
        
        // State
        let currentUser = null;
        let incidenciasData = [];
        let filteredIncidencias = [];
        let tecnicos = [];
        
        // DOM Elements
        const dom = {
            accessDenied: document.getElementById('access-denied'),
            loading: document.getElementById('loading'),
            incidenciasList: document.getElementById('incidencias-list'),
            noIncidencias: document.getElementById('no-incidencias'),
            totalIncidencias: document.getElementById('total-incidencias'),
            statsSection: document.getElementById('stats-section'),
            filters: {
                estado: document.getElementById('filter-estado'),
                prioridad: document.getElementById('filter-prioridad'),
                tecnico: document.getElementById('filter-tecnico'),
                search: document.getElementById('search-input'),
                clear: document.getElementById('clear-filters')
            },
            modal: {
                self: document.getElementById('response-modal'),
                title: document.getElementById('modal-title'),
                close: document.getElementById('close-modal'),
                cancel: document.getElementById('cancel-response'),
                submit: document.getElementById('submit-response'),
                form: document.getElementById('response-form'),
                info: document.getElementById('modal-incidencia-info'),
                inputs: {
                    id: document.getElementById('incidencia-id-modal'),
                    respuesta: document.getElementById('respuesta-modal'),
                    estado: document.getElementById('estado-modal'),
                    prioridad: document.getElementById('prioridad-modal'),
                    tecnico: document.getElementById('tecnico-modal')
                },
                submitText: document.getElementById('submit-text'),
                loading: document.getElementById('submit-loading')
            },
            userInfo: document.getElementById('user-info'),
            currentUserName: document.getElementById('current-user-name')
        };

        // Toast Notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const colors = {
                success: 'bg-teal-500',
                error: 'bg-red-500',
                info: 'bg-blue-500'
            };
            
            toast.className = `${colors[type]} text-white px-6 py-4 rounded-xl shadow-lg transform transition-all duration-300 translate-x-full flex items-center min-w-[300px]`;
            toast.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-3 text-lg"></i>
                <span class="font-medium">${message}</span>
            `;
            
            document.getElementById('toast-container').appendChild(toast);
            
            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-full');
            });
            
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Authentication Check
        function checkAuth() {
            const session = JSON.parse(localStorage.getItem('user_session') || sessionStorage.getItem('user_session') || 'null');
            
            if (!session || (session.rol !== 'admin' && session.rol !== 'tecnico')) {
                dom.accessDenied.classList.remove('hidden');
                return false;
            }
            
            // Check session expiry (24h)
            if (session.loginTime && (new Date().getTime() - session.loginTime > 86400000)) {
                dom.accessDenied.classList.remove('hidden');
                return false;
            }

            currentUser = session;
            if (dom.currentUserName) dom.currentUserName.textContent = session.nombre_completo || session.username;
            if (dom.userInfo) dom.userInfo.classList.remove('hidden');
            return true;
        }

        // Data Loading
        async function loadData() {
            dom.loading.classList.remove('hidden');
            try {
                const [incidenciasRes, tecnicosRes, statsRes] = await Promise.all([
                    fetch(INCIDENCIAS_API),
                    fetch(`${INCIDENCIAS_API}?action=tecnicos`),
                    fetch(`${INCIDENCIAS_API}?action=stats`)
                ]);

                const incidencias = await incidenciasRes.json();
                const tecnicosData = await tecnicosRes.json();
                const stats = await statsRes.json();

                incidenciasData = incidencias.records || [];
                tecnicos = tecnicosData.tecnicos || [];
                
                updateTecnicosSelects();
                renderStats(stats.stats || {});
                applyFilters();

            } catch (error) {
                console.error('Error loading data:', error);
                showToast('Error al cargar datos del sistema', 'error');
            } finally {
                dom.loading.classList.add('hidden');
            }
        }

        function updateTecnicosSelects() {
            const options = `<option value="">Todos</option><option value="sin-asignar">Sin asignar</option>` + 
                tecnicos.map(t => `<option value="${t.id_usuario}">${t.nombre_completo}</option>`).join('');
            
            dom.filters.tecnico.innerHTML = options;
            
            const modalOptions = `<option value="">Sin asignar</option>` + 
                tecnicos.map(t => `<option value="${t.id_usuario}">${t.nombre_completo}</option>`).join('');
            dom.modal.inputs.tecnico.innerHTML = modalOptions;
        }

        // Rendering
        function renderStats(stats) {
            const createCard = (title, count, icon, color) => `
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between group hover:shadow-md transition-all duration-300">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">${title}</p>
                        <h3 class="text-3xl font-bold text-gray-800 group-hover:text-${color}-600 transition-colors">${count}</h3>
                    </div>
                    <div class="w-12 h-12 bg-${color}-50 rounded-xl flex items-center justify-center text-${color}-500 group-hover:scale-110 transition-transform">
                        <i class="${icon} text-xl"></i>
                    </div>
                </div>
            `;

            let counts = { total: stats.total || 0, abierta: 0, en_proceso: 0, cerrada: 0 };
            if (stats.por_estado) {
                stats.por_estado.forEach(s => {
                    if (counts.hasOwnProperty(s.estado)) counts[s.estado] = s.count;
                });
            }

            dom.statsSection.innerHTML = `
                ${createCard('Total', counts.total, 'fas fa-clipboard-list', 'teal')}
                ${createCard('Abiertas', counts.abierta, 'fas fa-exclamation-circle', 'red')}
                ${createCard('En Proceso', counts.en_proceso, 'fas fa-clock', 'amber')}
                ${createCard('Cerradas', counts.cerrada, 'fas fa-check-circle', 'emerald')}
            `;
        }

        function renderIncidencias() {
            dom.incidenciasList.innerHTML = '';
            dom.totalIncidencias.textContent = filteredIncidencias.length;

            if (filteredIncidencias.length === 0) {
                dom.noIncidencias.classList.remove('hidden');
                return;
            }

            dom.noIncidencias.classList.add('hidden');
            
            const html = filteredIncidencias.map(incidencia => {
                const statusColors = {
                    abierta: 'bg-red-50 text-red-700 border-red-100',
                    en_proceso: 'bg-amber-50 text-amber-700 border-amber-100',
                    en_verificacion: 'bg-blue-50 text-blue-700 border-blue-100',
                    cerrada: 'bg-emerald-50 text-emerald-700 border-emerald-100',
                    cancelada: 'bg-gray-50 text-gray-700 border-gray-100'
                };

                const priorityColors = {
                    baja: 'bg-blue-50 text-blue-700 border-blue-100',
                    media: 'bg-indigo-50 text-indigo-700 border-indigo-100',
                    alta: 'bg-orange-50 text-orange-700 border-orange-100',
                    critica: 'bg-rose-50 text-rose-700 border-rose-100'
                };

                const tecnicoName = tecnicos.find(t => t.id_usuario == incidencia.id_usuario_tecnico)?.nombre_completo || 'Sin asignar';

                return `
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 group">
                        <div class="flex flex-col lg:flex-row gap-6">
                            <!-- Left: Status & ID -->
                            <div class="lg:w-48 flex-shrink-0 flex flex-col gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-mono font-bold text-gray-400">#${incidencia.id_incidencia}</span>
                                    <span class="text-xs font-medium px-2.5 py-0.5 rounded-full border ${statusColors[incidencia.estado]}">
                                        ${incidencia.estado.replace('_', ' ').toUpperCase()}
                                    </span>
                                </div>
                                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full border w-fit ${priorityColors[incidencia.prioridad]}">
                                    ${incidencia.prioridad.toUpperCase()}
                                </span>
                                <div class="text-xs text-gray-400 mt-auto">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    ${new Date(incidencia.fecha_reporte).toLocaleDateString()}
                                </div>
                            </div>

                            <!-- Middle: Content -->
                            <div class="flex-1 min-w-0">
                                <h4 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-teal-600 transition-colors truncate">
                                    ${incidencia.titulo}
                                </h4>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                    ${incidencia.descripcion}
                                </p>
                                
                                <div class="flex flex-wrap gap-y-2 gap-x-6 text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <i class="fas fa-tag text-teal-400 mr-2"></i>
                                        <span>${incidencia.tipo_nombre || 'Sin tipo'}</span>
                                        ${incidencia.subtipo_nombre ? `<span class="mx-1 text-gray-300">/</span><span class="text-gray-400">${incidencia.subtipo_nombre}</span>` : ''}
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-user text-teal-400 mr-2"></i>
                                        <span>${incidencia.nombre_reporta || 'Anónimo'}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-tools text-teal-400 mr-2"></i>
                                        <span class="${!incidencia.id_usuario_tecnico ? 'text-amber-500 italic' : ''}">${tecnicoName}</span>
                                    </div>
                                </div>

                                ${incidencia.respuesta_solucion ? `
                                    <div class="mt-4 bg-teal-50/50 rounded-xl p-3 border border-teal-100">
                                        <p class="text-xs text-teal-800 font-medium mb-1">
                                            <i class="fas fa-reply mr-1"></i> Respuesta:
                                        </p>
                                        <p class="text-sm text-gray-600 line-clamp-2 italic">
                                            "${incidencia.respuesta_solucion}"
                                        </p>
                                    </div>
                                ` : ''}
                            </div>

                            <!-- Right: Actions -->
                            <div class="flex flex-row lg:flex-col gap-2 justify-end lg:justify-start lg:w-32 flex-shrink-0">
                                <button onclick="openModal(${incidencia.id_incidencia})" 
                                    class="flex-1 lg:flex-none bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors shadow-sm hover:shadow flex items-center justify-center">
                                    <i class="fas fa-edit mr-2"></i> Responder
                                </button>
                                ${incidencia.email_reporta ? `
                                    <button onclick="openEmailModal(${incidencia.id_incidencia})" 
                                        class="flex-1 lg:flex-none bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-xl text-sm font-medium transition-colors flex items-center justify-center"
                                        title="Enviar email a ${incidencia.nombre_reporta}">
                                        <i class="fas fa-envelope mr-2 text-gray-400"></i> Email
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            dom.incidenciasList.innerHTML = html;
        }

        // Filtering
        function applyFilters() {
            const search = dom.filters.search.value.toLowerCase();
            const estado = dom.filters.estado.value;
            const prioridad = dom.filters.prioridad.value;
            const tecnico = dom.filters.tecnico.value;

            filteredIncidencias = incidenciasData.filter(item => {
                const matchesSearch = !search || 
                    item.titulo.toLowerCase().includes(search) || 
                    item.descripcion.toLowerCase().includes(search) ||
                    item.id_incidencia.toString().includes(search) ||
                    (item.nombre_reporta && item.nombre_reporta.toLowerCase().includes(search));

                const matchesEstado = !estado || item.estado === estado;
                const matchesPrioridad = !prioridad || item.prioridad === prioridad;
                
                let matchesTecnico = true;
                if (tecnico === 'sin-asignar') matchesTecnico = !item.id_usuario_tecnico;
                else if (tecnico) matchesTecnico = item.id_usuario_tecnico == tecnico;

                return matchesSearch && matchesEstado && matchesPrioridad && matchesTecnico;
            });

            renderIncidencias();
        }

        // Modal Handling
        window.openModal = function(id) {
            const incidencia = incidenciasData.find(i => i.id_incidencia == id);
            if (!incidencia) return;

            // Populate Info
            dom.modal.info.innerHTML = `
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Incidencia #${incidencia.id_incidencia}</span>
                        <h4 class="text-xl font-bold text-gray-900 mt-1">${incidencia.titulo}</h4>
                    </div>
                    <span class="px-3 py-1 bg-gray-100 rounded-full text-xs font-medium text-gray-600">
                        ${new Date(incidencia.fecha_reporte).toLocaleDateString()}
                    </span>
                </div>
                <p class="text-gray-600 text-sm bg-white p-4 rounded-lg border border-gray-100 mb-4">
                    ${incidencia.descripcion}
                </p>
                <div class="flex flex-wrap gap-4 text-xs text-gray-500">
                    <span class="flex items-center"><i class="fas fa-user mr-2"></i> ${incidencia.nombre_reporta || 'Anónimo'}</span>
                    <span class="flex items-center"><i class="fas fa-envelope mr-2"></i> ${incidencia.email_reporta || 'N/A'}</span>
                    <span class="flex items-center"><i class="fas fa-tag mr-2"></i> ${incidencia.tipo_nombre || 'General'}</span>
                </div>
            `;

            // Populate Form
            dom.modal.inputs.id.value = id;
            dom.modal.inputs.respuesta.value = incidencia.respuesta_solucion || '';
            dom.modal.inputs.estado.value = incidencia.estado;
            dom.modal.inputs.prioridad.value = incidencia.prioridad;
            dom.modal.inputs.tecnico.value = incidencia.id_usuario_tecnico || '';

            dom.modal.self.classList.remove('hidden');
        };

        function closeModal() {
            dom.modal.self.classList.add('hidden');
        }

        // Event Listeners
        dom.filters.search.addEventListener('input', applyFilters);
        dom.filters.estado.addEventListener('change', applyFilters);
        dom.filters.prioridad.addEventListener('change', applyFilters);
        dom.filters.tecnico.addEventListener('change', applyFilters);
        dom.filters.clear.addEventListener('click', () => {
            dom.filters.search.value = '';
            dom.filters.estado.value = '';
            dom.filters.prioridad.value = '';
            dom.filters.tecnico.value = '';
            applyFilters();
        });

        dom.modal.close.addEventListener('click', closeModal);
        dom.modal.cancel.addEventListener('click', closeModal);
        
        dom.modal.submit.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const id = dom.modal.inputs.id.value;
            const respuesta = dom.modal.inputs.respuesta.value.trim();
            const estado = dom.modal.inputs.estado.value;
            
            if (!respuesta) {
                showToast('Por favor ingrese una respuesta o solución', 'error');
                dom.modal.inputs.respuesta.focus();
                return;
            }

            // Prepare Data
            const incidencia = incidenciasData.find(i => i.id_incidencia == id);
            const data = {
                id_incidencia: id,
                respuesta_solucion: respuesta,
                estado: estado,
                prioridad: dom.modal.inputs.prioridad.value,
                id_usuario_tecnico: dom.modal.inputs.tecnico.value || null,
                // Required fields for API validation
                titulo: incidencia.titulo,
                descripcion: incidencia.descripcion,
                id_tipo_incidencia: incidencia.id_tipo_incidencia,
                id_subtipo_incidencia: incidencia.id_subtipo_incidencia,
                nombre_reporta: incidencia.nombre_reporta,
                email_reporta: incidencia.email_reporta
            };

            // UI Loading State
            dom.modal.submit.disabled = true;
            dom.modal.submitText.textContent = 'Guardando...';
            dom.modal.loading.classList.remove('hidden');

            try {
                const response = await fetch(INCIDENCIAS_API, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showToast('Incidencia actualizada correctamente', 'success');
                    closeModal();
                    loadData(); // Reload all data
                } else {
                    throw new Error(result.message || 'Error al actualizar');
                }
            } catch (error) {
                console.error(error);
                showToast(error.message, 'error');
            } finally {
                dom.modal.submit.disabled = false;
                dom.modal.submitText.textContent = 'Guardar Cambios';
                dom.modal.loading.classList.add('hidden');
            }
        });

        // Email Modal Handling
        const emailModal = {
            self: document.getElementById('email-modal'),
            close: document.getElementById('close-email-modal'),
            cancel: document.getElementById('cancel-email'),
            send: document.getElementById('send-email'),
            info: document.getElementById('email-modal-incidencia-info'),
            inputs: {
                id: document.getElementById('email-incidencia-id'),
                asunto: document.getElementById('email-asunto'),
                mensaje: document.getElementById('email-mensaje')
            },
            sendText: document.getElementById('send-email-text'),
            loading: document.getElementById('send-email-loading')
        };

        window.openEmailModal = function(id) {
            const incidencia = incidenciasData.find(i => i.id_incidencia == id);
            if (!incidencia || !incidencia.email_reporta) return;

            // Populate Info
            emailModal.info.innerHTML = `
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Incidencia #${incidencia.id_incidencia}</span>
                        <h4 class="text-lg font-bold text-gray-900 mt-1">${incidencia.titulo}</h4>
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap gap-3 text-xs text-gray-500">
                    <span class="flex items-center"><i class="fas fa-user mr-2"></i> ${incidencia.nombre_reporta || 'Anónimo'}</span>
                    <span class="flex items-center"><i class="fas fa-envelope mr-2"></i> ${incidencia.email_reporta}</span>
                    <span class="flex items-center"><i class="fas fa-tag mr-2"></i> ${incidencia.tipo_nombre || 'General'}</span>
                </div>
            `;

            // Set default subject
            emailModal.inputs.id.value = id;
            emailModal.inputs.asunto.value = `Actualización de tu Incidencia #${incidencia.id_incidencia} - ${incidencia.titulo}`;
            emailModal.inputs.mensaje.value = '';

            emailModal.self.classList.remove('hidden');
        };

        function closeEmailModal() {
            emailModal.self.classList.add('hidden');
            document.getElementById('email-form').reset();
        }

        emailModal.close.addEventListener('click', closeEmailModal);
        emailModal.cancel.addEventListener('click', closeEmailModal);
        
        emailModal.send.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const id = emailModal.inputs.id.value;
            const asunto = emailModal.inputs.asunto.value.trim();
            const mensaje = emailModal.inputs.mensaje.value.trim();
            
            if (!asunto) {
                showToast('Por favor ingrese un asunto', 'error');
                emailModal.inputs.asunto.focus();
                return;
            }
            
            if (!mensaje) {
                showToast('Por favor ingrese un mensaje', 'error');
                emailModal.inputs.mensaje.focus();
                return;
            }

            // UI Loading State
            emailModal.send.disabled = true;
            emailModal.sendText.textContent = 'Enviando...';
            emailModal.loading.classList.remove('hidden');

            try {
                const response = await fetch(`${INCIDENCIAS_API}?action=enviar_email_manual`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_incidencia: id,
                        asunto: asunto,
                        mensaje: mensaje
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showToast('Email enviado exitosamente al usuario', 'success');
                    closeEmailModal();
                } else {
                    throw new Error(result.message || 'Error al enviar email');
                }
            } catch (error) {
                console.error(error);
                showToast(error.message, 'error');
            } finally {
                emailModal.send.disabled = false;
                emailModal.sendText.textContent = 'Enviar Email';
                emailModal.loading.classList.add('hidden');
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            if (checkAuth()) {
                loadData();
            }
        });
    </script>
</body>
</html>