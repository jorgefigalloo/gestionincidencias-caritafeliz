<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Incidencias</title>
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
    <style>
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestión de Incidencias</h1>
                <p class="text-sm text-gray-500 mt-1">Administra y supervisa todos los tickets del sistema</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                <div class="relative flex-1 lg:w-80 group">
                    <input type="text" id="search-input" placeholder="Buscar por título, ID o usuario..." 
                        class="w-full pl-10 pr-10 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all shadow-sm group-hover:shadow">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 group-focus-within:text-teal-500 transition-colors"></i>
                    </div>
                    <button id="clear-search" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 hidden transition-colors">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </div>
                
                <button id="add-incidencia-btn" class="bg-teal-600 hover:bg-teal-700 text-white font-medium py-2.5 px-5 rounded-xl shadow-lg shadow-teal-500/30 transition-all duration-300 flex items-center justify-center whitespace-nowrap transform hover:-translate-y-0.5">
                    <i class="fas fa-plus mr-2"></i> Nueva Incidencia
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div id="stats-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 hidden"></div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center">
                    <i class="fas fa-filter mr-2"></i>Filtros
                </h3>
                <button id="clear-filters" class="text-xs text-gray-500 hover:text-teal-600 font-medium transition-colors flex items-center">
                    Limpiar todo
                </button>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="relative">
                    <label class="block text-xs font-medium text-gray-500 mb-1.5 ml-1">Estado</label>
                    <select id="filter-estado" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all appearance-none">
                        <option value="">Todos los estados</option>
                        <option value="abierta">Abierta</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="cerrada">Cerrada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                    <div class="absolute right-3 top-[2.1rem] pointer-events-none text-gray-400"><i class="fas fa-chevron-down text-xs"></i></div>
                </div>
                
                <div class="relative">
                    <label class="block text-xs font-medium text-gray-500 mb-1.5 ml-1">Prioridad</label>
                    <select id="filter-prioridad" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all appearance-none">
                        <option value="">Todas las prioridades</option>
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                        <option value="critica">Crítica</option>
                    </select>
                    <div class="absolute right-3 top-[2.1rem] pointer-events-none text-gray-400"><i class="fas fa-chevron-down text-xs"></i></div>
                </div>
                
                <div class="relative">
                    <label class="block text-xs font-medium text-gray-500 mb-1.5 ml-1">Tipo</label>
                    <select id="filter-tipo" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all appearance-none">
                        <option value="">Todos los tipos</option>
                    </select>
                    <div class="absolute right-3 top-[2.1rem] pointer-events-none text-gray-400"><i class="fas fa-chevron-down text-xs"></i></div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading" class="hidden py-12 flex flex-col items-center justify-center text-teal-600">
            <i class="fas fa-circle-notch fa-spin text-3xl mb-3"></i>
            <p class="text-sm font-medium text-gray-500">Cargando incidencias...</p>
        </div>

        <!-- Table -->
        <div id="incidencias-table-container" class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Incidencia</th>
                            <th class="px-4 py-3">Clasificación</th>
                            <th class="px-4 py-3">Reportado por</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Prioridad</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Técnico</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="incidencias-table-body" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>

        <!-- Empty States -->
        <div id="no-data-message" class="hidden bg-white shadow-sm border border-gray-100 rounded-2xl p-12 text-center">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-clipboard-list text-3xl text-gray-300"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">No hay incidencias</h3>
            <p class="text-gray-500 mb-6">Comienza registrando una nueva incidencia en el sistema.</p>
            <button onclick="document.getElementById('add-incidencia-btn').click()" class="text-teal-600 font-medium hover:text-teal-700">
                Crear primera incidencia &rarr;
            </button>
        </div>

        <div id="no-search-results" class="hidden bg-white shadow-sm border border-gray-100 rounded-2xl p-12 text-center">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-search text-3xl text-gray-300"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-1">Sin resultados</h3>
            <p class="text-gray-500 mb-6">No encontramos incidencias que coincidan con tu búsqueda.</p>
            <button id="clear-search-from-message" class="text-teal-600 font-medium hover:text-teal-700">
                Limpiar filtros &rarr;
            </button>
        </div>
    </div>

    <!-- Modal -->
    <div id="incidencia-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900" id="modal-title">Nueva Incidencia</h3>
                    <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form id="incidencia-form" class="px-6 py-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                    <input type="hidden" id="incidencia-id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Título <span class="text-red-500">*</span></label>
                            <input type="text" id="incidencia-titulo" required maxlength="100" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all" placeholder="Resumen breve del problema">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tipo</label>
                            <select id="incidencia-tipo" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                                <option value="">Seleccionar tipo...</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="modal-subtipo-container" class="mb-6 hidden">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Subtipo</label>
                        <select id="incidencia-subtipo" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                            <option value="">Seleccionar subtipo...</option>
                        </select>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Descripción <span class="text-red-500">*</span></label>
                        <textarea id="incidencia-descripcion" rows="4" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all" placeholder="Detalles completos de la incidencia..."></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Reportante</label>
                            <input type="text" id="incidencia-nombre-reporta" maxlength="100" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all" placeholder="Nombre completo">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Email</label>
                            <input type="email" id="incidencia-email-reporta" maxlength="100" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all" placeholder="correo@ejemplo.com">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Estado</label>
                            <select id="incidencia-estado" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                                <option value="abierta">Abierta</option>
                                <option value="en_proceso">En Proceso</option>
                                <option value="cerrada">Cerrada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Prioridad</label>
                            <select id="incidencia-prioridad" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                                <option value="baja">Baja</option>
                                <option value="media" selected>Media</option>
                                <option value="alta">Alta</option>
                                <option value="critica">Crítica</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Técnico Asignado</label>
                        <select id="incidencia-tecnico" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                            <option value="">Sin asignar</option>
                        </select>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Solución / Respuesta</label>
                        <textarea id="incidencia-solucion" rows="3" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all" placeholder="Acciones tomadas..."></textarea>
                    </div>
                </form>
                
                <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                    <button type="button" id="close-modal-btn-2" class="w-full sm:w-auto px-6 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all">Cancelar</button>
                    <button type="submit" id="save-btn" form="incidencia-form" class="w-full sm:w-auto px-6 py-2.5 bg-teal-600 text-white font-medium rounded-xl hover:bg-teal-700 shadow-lg shadow-teal-500/30 transition-all flex items-center justify-center">
                        <span id="save-btn-text">Guardar Incidencia</span>
                        <i id="save-btn-loading" class="fas fa-circle-notch fa-spin ml-2 hidden"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div id="view-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">Detalles de la Incidencia</h3>
                    <button id="close-view-modal-btn" class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="px-6 py-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                    <div class="flex items-center justify-between mb-6">
                        <span id="view-id" class="text-sm font-mono font-bold text-gray-400"></span>
                        <div class="flex gap-2">
                            <span id="view-estado" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"></span>
                            <span id="view-prioridad" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"></span>
                        </div>
                    </div>

                    <h4 id="view-titulo" class="text-xl font-bold text-gray-900 mb-2"></h4>
                    <div id="view-tipo-container" class="flex flex-wrap gap-2 mb-6">
                        <span id="view-tipo" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100"></span>
                        <span id="view-subtipo" class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100 hidden"></span>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 mb-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Descripción</label>
                        <p id="view-descripcion" class="text-sm text-gray-700 whitespace-pre-wrap"></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Reportado por</label>
                            <p id="view-reporta" class="text-sm font-medium text-gray-900"></p>
                            <p id="view-email" class="text-xs text-gray-500"></p>
                            <p id="view-fecha" class="text-xs text-gray-400 mt-1"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Técnico Asignado</label>
                            <p id="view-tecnico" class="text-sm font-medium text-gray-900"></p>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Solución / Respuesta</label>
                        <div id="view-solucion-container" class="bg-teal-50 rounded-xl p-4 border border-teal-100">
                            <p id="view-solucion" class="text-sm text-gray-700 whitespace-pre-wrap"></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-6 py-4 flex justify-end border-t border-gray-100">
                    <button id="close-view-modal-btn-2" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="delete-confirm-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full p-6 text-center">
                <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 text-red-500">
                    <i class="fas fa-trash-alt text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">¿Eliminar incidencia?</h3>
                <p class="text-sm text-gray-500 mb-6">Esta acción no se puede deshacer. La incidencia será eliminada permanentemente del sistema.</p>
                
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button type="button" id="cancel-delete-btn" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all">Cancelar</button>
                    <button type="button" id="confirm-delete-btn" class="w-full sm:w-auto px-5 py-2.5 bg-red-600 text-white font-medium rounded-xl hover:bg-red-700 shadow-lg shadow-red-500/30 transition-all flex items-center justify-center">
                        <span id="delete-btn-text">Sí, eliminar</span>
                        <i id="delete-btn-loading" class="fas fa-circle-notch fa-spin ml-2 hidden"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2"></div>

    <script>
    const API_URL = '../api/controllers/incidencias.php';
    
    // State
    let allIncidencias = []; 
    let filteredIncidencias = []; 
    let currentFilters = { search: '', estado: '', prioridad: '', tipo: '' };
    let tiposIncidencia = [];
    let tecnicos = [];
    let incidenciaIdToDelete = null;
    
    // DOM Elements
    const dom = {
        tableBody: document.getElementById('incidencias-table-body'),
        tableContainer: document.getElementById('incidencias-table-container'),
        noData: document.getElementById('no-data-message'),
        noSearch: document.getElementById('no-search-results'),
        loading: document.getElementById('loading'),
        search: document.getElementById('search-input'),
        clearSearch: document.getElementById('clear-search'),
        stats: document.getElementById('stats-container'),
        filters: {
            estado: document.getElementById('filter-estado'),
            prioridad: document.getElementById('filter-prioridad'),
            tipo: document.getElementById('filter-tipo'),
            clear: document.getElementById('clear-filters')
        },
        modal: {
            self: document.getElementById('incidencia-modal'),
            title: document.getElementById('modal-title'),
            form: document.getElementById('incidencia-form'),
            close: document.getElementById('close-modal-btn'),
            close2: document.getElementById('close-modal-btn-2'),
            saveBtn: document.getElementById('save-btn'),
            saveText: document.getElementById('save-btn-text'),
            saveLoading: document.getElementById('save-btn-loading'),
            subtipoContainer: document.getElementById('modal-subtipo-container'),
            inputs: {
                id: document.getElementById('incidencia-id'),
                titulo: document.getElementById('incidencia-titulo'),
                tipo: document.getElementById('incidencia-tipo'),
                subtipo: document.getElementById('incidencia-subtipo'),
                descripcion: document.getElementById('incidencia-descripcion'),
                nombre: document.getElementById('incidencia-nombre-reporta'),
                email: document.getElementById('incidencia-email-reporta'),
                estado: document.getElementById('incidencia-estado'),
                prioridad: document.getElementById('incidencia-prioridad'),
                tecnico: document.getElementById('incidencia-tecnico'),
                solucion: document.getElementById('incidencia-solucion')
            }
        },
        deleteModal: {
            self: document.getElementById('delete-confirm-modal'),
            confirm: document.getElementById('confirm-delete-btn'),
            cancel: document.getElementById('cancel-delete-btn'),
            text: document.getElementById('delete-btn-text'),
            loading: document.getElementById('delete-btn-loading')
        },
        viewModal: {
            self: document.getElementById('view-modal'),
            close: document.getElementById('close-view-modal-btn'),
            close2: document.getElementById('close-view-modal-btn-2'),
            fields: {
                id: document.getElementById('view-id'),
                estado: document.getElementById('view-estado'),
                prioridad: document.getElementById('view-prioridad'),
                titulo: document.getElementById('view-titulo'),
                tipo: document.getElementById('view-tipo'),
                subtipo: document.getElementById('view-subtipo'),
                descripcion: document.getElementById('view-descripcion'),
                reporta: document.getElementById('view-reporta'),
                email: document.getElementById('view-email'),
                fecha: document.getElementById('view-fecha'),
                tecnico: document.getElementById('view-tecnico'),
                solucion: document.getElementById('view-solucion'),
                solucionContainer: document.getElementById('view-solucion-container')
            }
        }
    };

    // Toast Notification
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        const colors = { success: 'bg-teal-500', error: 'bg-red-500', info: 'bg-blue-500' };
        
        toast.className = `${colors[type]} text-white px-6 py-4 rounded-xl shadow-lg transform transition-all duration-300 translate-x-full flex items-center min-w-[300px] z-50`;
        toast.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-3 text-lg"></i>
            <span class="font-medium">${message}</span>
        `;
        
        document.getElementById('toast-container').appendChild(toast);
        
        requestAnimationFrame(() => toast.classList.remove('translate-x-full'));
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Helpers
    function setLoading(show) {
        dom.loading.classList.toggle('hidden', !show);
        dom.tableContainer.classList.toggle('hidden', show);
        dom.noData.classList.add('hidden');
        dom.noSearch.classList.add('hidden');
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('es-ES', { 
            year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' 
        });
    }

    function getEstadoClass(estado) {
        const classes = {
            'abierta': 'bg-red-50 text-red-700 border border-red-100',
            'en_proceso': 'bg-amber-50 text-amber-700 border border-amber-100',
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

    async function loadSubtipos(tipoId, selectedSubtipoId = null) {
        if (!tipoId) {
            dom.modal.subtipoContainer.classList.add('hidden');
            return;
        }

        try {
            const res = await fetch(`../api/controllers/subtipos_incidencias.php?action=by_tipo&id_tipo=${tipoId}`);
            const data = await res.json();

            if (data.subtipos && data.subtipos.length > 0) {
                dom.modal.inputs.subtipo.innerHTML = '<option value="">Seleccionar subtipo...</option>' + 
                    data.subtipos.map(s => `<option value="${s.id_subtipo_incidencia}">${escapeHtml(s.nombre)}</option>`).join('');
                dom.modal.subtipoContainer.classList.remove('hidden');
                if (selectedSubtipoId) dom.modal.inputs.subtipo.value = selectedSubtipoId;
            } else {
                dom.modal.subtipoContainer.classList.add('hidden');
            }
        } catch (error) {
            console.error(error);
        }
    }

    // Main Logic
    async function loadData() {
        setLoading(true);
        try {
            const [incidenciasRes, statsRes, tiposRes, tecnicosRes] = await Promise.all([
                fetch(API_URL),
                fetch(`${API_URL}?action=stats`),
                fetch(`${API_URL}?action=tipos`),
                fetch(`${API_URL}?action=tecnicos`)
            ]);

            const incidenciasData = await incidenciasRes.json();
            const statsData = await statsRes.json();
            const tiposData = await tiposRes.json();
            const tecnicosData = await tecnicosRes.json();

            allIncidencias = incidenciasData.records || [];
            const stats = statsData.stats || null;
            tiposIncidencia = tiposData.tipos || [];
            tecnicos = tecnicosData.tecnicos || [];

            // Populate Filters & Modals
            const tipoOptions = tiposIncidencia.map(t => `<option value="${t.id_tipo_incidencia}">${escapeHtml(t.nombre)}</option>`).join('');
            dom.filters.tipo.innerHTML = '<option value="">Todos los tipos</option>' + tipoOptions;
            dom.modal.inputs.tipo.innerHTML = '<option value="">Seleccionar tipo...</option>' + tipoOptions;

            const tecnicoOptions = tecnicos.map(t => `<option value="${t.id_usuario}">${escapeHtml(t.nombre_completo)}</option>`).join('');
            dom.modal.inputs.tecnico.innerHTML = '<option value="">Sin asignar</option>' + tecnicoOptions;

            renderStats(stats);
            filterIncidencias();

        } catch (error) {
            console.error(error);
            showToast('Error al cargar datos', 'error');
        } finally {
            setLoading(false);
        }
    }

    function renderStats(stats) {
        if (!stats) return;

        const createCard = (title, count, icon, color) => `
            <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 group">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-10 h-10 rounded-xl bg-${color}-50 flex items-center justify-center text-${color}-500 group-hover:scale-110 transition-transform">
                        <i class="${icon} text-lg"></i>
                    </div>
                    <span class="text-2xl font-bold text-gray-800">${count}</span>
                </div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">${title}</p>
            </div>
        `;

        let cards = createCard('Total', stats.total, 'fas fa-layer-group', 'teal');
        
        if (stats.por_estado) {
            stats.por_estado.forEach(s => {
                const map = {
                    'abierta': { icon: 'fas fa-exclamation-circle', color: 'red' },
                    'en_proceso': { icon: 'fas fa-clock', color: 'amber' },
                    'cerrada': { icon: 'fas fa-check-circle', color: 'emerald' },
                    'cancelada': { icon: 'fas fa-ban', color: 'gray' }
                };
                const conf = map[s.estado] || { icon: 'fas fa-circle', color: 'gray' };
                cards += createCard(s.estado.replace('_', ' '), s.count, conf.icon, conf.color);
            });
        }

        dom.stats.innerHTML = cards;
        dom.stats.classList.remove('hidden');
    }

    function filterIncidencias() {
        let filtered = allIncidencias;
        
        if (currentFilters.search) {
            const term = currentFilters.search.toLowerCase();
            filtered = filtered.filter(i => 
                i.titulo.toLowerCase().includes(term) ||
                i.descripcion.toLowerCase().includes(term) ||
                (i.nombre_reporta && i.nombre_reporta.toLowerCase().includes(term)) ||
                (i.reporta_usuario && i.reporta_usuario.toLowerCase().includes(term)) ||
                i.id_incidencia.toString().includes(term)
            );
        }
        
        if (currentFilters.estado) filtered = filtered.filter(i => i.estado === currentFilters.estado);
        if (currentFilters.prioridad) filtered = filtered.filter(i => i.prioridad === currentFilters.prioridad);
        if (currentFilters.tipo) filtered = filtered.filter(i => i.id_tipo_incidencia == currentFilters.tipo);

        filteredIncidencias = filtered;
        renderTable();
    }

    function renderTable() {
        dom.tableBody.innerHTML = '';
        
        if (filteredIncidencias.length === 0) {
            if (currentFilters.search || currentFilters.estado || currentFilters.prioridad || currentFilters.tipo) {
                dom.noSearch.classList.remove('hidden');
                dom.tableContainer.classList.add('hidden');
            } else {
                dom.noData.classList.remove('hidden');
                dom.tableContainer.classList.add('hidden');
            }
            return;
        }

        dom.noData.classList.add('hidden');
        dom.noSearch.classList.add('hidden');
        dom.tableContainer.classList.remove('hidden');

        filteredIncidencias.forEach(inc => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50/50 transition-colors border-b border-gray-50 last:border-0';
            
            const estadoClass = getEstadoClass(inc.estado);
            const prioridadClass = getPrioridadClass(inc.prioridad);
            const fecha = formatDate(inc.fecha_reporte);
            
            row.innerHTML = `
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="font-mono text-xs font-bold text-gray-400">#${inc.id_incidencia}</span>
                </td>
                <td class="px-4 py-3">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-900 line-clamp-1" title="${escapeHtml(inc.titulo)}">${escapeHtml(inc.titulo)}</span>
                        <span class="text-xs text-gray-500 line-clamp-1" title="${escapeHtml(inc.descripcion)}">${escapeHtml(inc.descripcion)}</span>
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex flex-col gap-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100 w-fit">
                            ${escapeHtml(inc.tipo_nombre || 'Sin tipo')}
                        </span>
                        ${inc.subtipo_nombre ? `
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100 w-fit">
                            ${escapeHtml(inc.subtipo_nombre)}
                        </span>` : ''}
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex flex-col">
                        <span class="text-sm font-medium text-gray-900">${escapeHtml(inc.reporta_usuario || inc.nombre_reporta || 'N/A')}</span>
                        <span class="text-xs text-gray-500">${escapeHtml(inc.email_reporta || '')}</span>
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${estadoClass}">
                        <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5"></span>
                        ${inc.estado.replace('_', ' ').toUpperCase()}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${prioridadClass}">
                        ${inc.prioridad.toUpperCase()}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                    ${fecha}
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    ${inc.tecnico_asignado ? 
                        `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            <i class="fas fa-user-shield mr-1.5 text-gray-500"></i>${escapeHtml(inc.tecnico_asignado)}
                        </span>` : 
                        `<span class="text-xs text-gray-400 italic">Sin asignar</span>`
                    }
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                        <button class="view-btn p-2 text-gray-400 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition-all" title="Ver detalles" data-id="${inc.id_incidencia}">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="edit-btn p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Editar" data-id="${inc.id_incidencia}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="delete-btn p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Eliminar" data-id="${inc.id_incidencia}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            `;
            dom.tableBody.appendChild(row);
        });
    }

    // Event Listeners
    dom.search.addEventListener('input', (e) => {
        currentFilters.search = e.target.value;
        dom.clearSearch.classList.toggle('hidden', !e.target.value);
        filterIncidencias();
    });

    dom.clearSearch.addEventListener('click', () => {
        dom.search.value = '';
        currentFilters.search = '';
        dom.clearSearch.classList.add('hidden');
        filterIncidencias();
    });

    ['estado', 'prioridad', 'tipo'].forEach(filter => {
        dom.filters[filter].addEventListener('change', (e) => {
            currentFilters[filter] = e.target.value;
            filterIncidencias();
        });
    });

    dom.filters.clear.addEventListener('click', () => {
        dom.search.value = '';
        dom.clearSearch.classList.add('hidden');
        ['estado', 'prioridad', 'tipo'].forEach(f => {
            dom.filters[f].value = '';
            currentFilters[f] = '';
        });
        currentFilters.search = '';
        filterIncidencias();
    });

    // Modal Actions
    document.getElementById('add-incidencia-btn').addEventListener('click', () => {
        dom.modal.form.reset();
        dom.modal.inputs.id.value = '';
        dom.modal.subtipoContainer.classList.add('hidden');
        dom.modal.title.textContent = 'Nueva Incidencia';
        dom.modal.self.classList.remove('hidden');
    });

    [dom.modal.close, dom.modal.close2].forEach(btn => 
        btn.addEventListener('click', () => dom.modal.self.classList.add('hidden'))
    );

    // View Modal Actions
    [dom.viewModal.close, dom.viewModal.close2].forEach(btn => 
        btn.addEventListener('click', () => dom.viewModal.self.classList.add('hidden'))
    );

    dom.modal.inputs.tipo.addEventListener('change', (e) => loadSubtipos(e.target.value));

    dom.modal.form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = {
            id_incidencia: dom.modal.inputs.id.value,
            titulo: dom.modal.inputs.titulo.value,
            id_tipo_incidencia: dom.modal.inputs.tipo.value,
            id_subtipo_incidencia: dom.modal.inputs.subtipo.value || null,
            descripcion: dom.modal.inputs.descripcion.value,
            nombre_reporta: dom.modal.inputs.nombre.value,
            email_reporta: dom.modal.inputs.email.value,
            estado: dom.modal.inputs.estado.value,
            prioridad: dom.modal.inputs.prioridad.value,
            id_usuario_tecnico: dom.modal.inputs.tecnico.value || null,
            respuesta_solucion: dom.modal.inputs.solucion.value
        };

        dom.modal.saveBtn.disabled = true;
        dom.modal.saveText.textContent = 'Guardando...';
        dom.modal.saveLoading.classList.remove('hidden');

        try {
            const method = formData.id_incidencia ? 'PUT' : 'POST';
            const res = await fetch(API_URL, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const result = await res.json();

            if (result.success || result.message.includes('éxito')) {
                showToast('Incidencia guardada correctamente', 'success');
                dom.modal.self.classList.add('hidden');
                loadData();
            } else {
                throw new Error(result.message || 'Error al guardar');
            }
        } catch (error) {
            showToast(error.message, 'error');
        } finally {
            dom.modal.saveBtn.disabled = false;
            dom.modal.saveText.textContent = 'Guardar Incidencia';
            dom.modal.saveLoading.classList.add('hidden');
        }
    });

    // Table Actions
    dom.tableBody.addEventListener('click', async (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;
        
        const id = btn.dataset.id;
        
        if (btn.classList.contains('view-btn')) {
            const inc = allIncidencias.find(i => i.id_incidencia == id);
            if (!inc) return;

            const f = dom.viewModal.fields;
            f.id.textContent = `#${inc.id_incidencia}`;
            f.titulo.textContent = inc.titulo;
            f.descripcion.textContent = inc.descripcion;
            f.reporta.textContent = inc.reporta_usuario || inc.nombre_reporta || 'Sin especificar';
            f.email.textContent = inc.email_reporta || '';
            f.fecha.textContent = formatDate(inc.fecha_reporte);
            f.tecnico.textContent = inc.tecnico_asignado || 'Sin asignar';
            
            f.estado.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getEstadoClass(inc.estado)}`;
            f.estado.textContent = inc.estado.replace('_', ' ').toUpperCase();
            
            f.prioridad.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getPrioridadClass(inc.prioridad)}`;
            f.prioridad.textContent = inc.prioridad.toUpperCase();
            
            f.tipo.textContent = inc.tipo_nombre || 'Sin tipo';
            if (inc.subtipo_nombre) {
                f.subtipo.textContent = inc.subtipo_nombre;
                f.subtipo.classList.remove('hidden');
            } else {
                f.subtipo.classList.add('hidden');
            }
            
            if (inc.respuesta_solucion) {
                f.solucion.textContent = inc.respuesta_solucion;
                f.solucionContainer.classList.remove('hidden');
            } else {
                f.solucion.textContent = '';
                f.solucionContainer.classList.add('hidden');
            }

            dom.viewModal.self.classList.remove('hidden');
        }

        if (btn.classList.contains('edit-btn')) {
            const inc = allIncidencias.find(i => i.id_incidencia == id);
            if (!inc) return;

            dom.modal.inputs.id.value = inc.id_incidencia;
            dom.modal.inputs.titulo.value = inc.titulo;
            dom.modal.inputs.tipo.value = inc.id_tipo_incidencia;
            dom.modal.inputs.descripcion.value = inc.descripcion;
            dom.modal.inputs.nombre.value = inc.nombre_reporta || inc.reporta_usuario || '';
            dom.modal.inputs.email.value = inc.email_reporta || '';
            dom.modal.inputs.estado.value = inc.estado;
            dom.modal.inputs.prioridad.value = inc.prioridad;
            dom.modal.inputs.tecnico.value = inc.id_usuario_tecnico || '';
            dom.modal.inputs.solucion.value = inc.respuesta_solucion || '';
            
            await loadSubtipos(inc.id_tipo_incidencia, inc.id_subtipo_incidencia);
            
            dom.modal.title.textContent = `Editar Incidencia #${id}`;
            dom.modal.self.classList.remove('hidden');
        }
        
        if (btn.classList.contains('delete-btn')) {
            incidenciaIdToDelete = id;
            dom.deleteModal.self.classList.remove('hidden');
        }
    });

    // Delete Actions
    dom.deleteModal.cancel.addEventListener('click', () => dom.deleteModal.self.classList.add('hidden'));
    
    dom.deleteModal.confirm.addEventListener('click', async () => {
        if (!incidenciaIdToDelete) return;
        
        dom.deleteModal.confirm.disabled = true;
        dom.deleteModal.loading.classList.remove('hidden');
        
        try {
            const res = await fetch(API_URL, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_incidencia: incidenciaIdToDelete })
            });
            const result = await res.json();
            
            if (result.success) {
                showToast('Incidencia eliminada', 'success');
                dom.deleteModal.self.classList.add('hidden');
                loadData();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            showToast(error.message, 'error');
        } finally {
            dom.deleteModal.confirm.disabled = false;
            dom.deleteModal.loading.classList.add('hidden');
        }
    });

    // Init
    document.addEventListener('DOMContentLoaded', loadData);
    </script>
</body>
</html>