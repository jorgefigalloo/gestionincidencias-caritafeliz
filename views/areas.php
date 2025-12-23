<?php
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestión de Áreas</h1>
            <p class="text-sm text-gray-500 mt-1">Administra los departamentos y zonas de cada sede</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <div class="relative flex-1 lg:w-80 group">
                <input 
                    type="text" 
                    id="search-input" 
                    placeholder="Buscar por nombre de área..." 
                    class="w-full pl-10 pr-10 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all shadow-sm group-hover:shadow"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 group-focus-within:text-teal-500 transition-colors"></i>
                </div>
                <button 
                    id="clear-search" 
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 hidden transition-colors"
                >
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
            
            <button id="add-area-btn" class="bg-teal-600 hover:bg-teal-700 text-white font-medium py-2.5 px-5 rounded-xl shadow-lg shadow-teal-500/30 transition-all duration-300 flex items-center justify-center whitespace-nowrap transform hover:-translate-y-0.5">
                <i class="fas fa-map-marker-alt mr-2"></i> Nueva Área
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center">
                <i class="fas fa-filter mr-2"></i>Filtros
            </h3>
            <button id="reset-filters" class="text-xs text-gray-500 hover:text-teal-600 font-medium transition-colors flex items-center">
                Limpiar todo
            </button>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="relative">
                <label class="block text-xs font-medium text-gray-500 mb-1.5 ml-1">Sede</label>
                <select id="sede-filter" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all appearance-none">
                    <option value="">Todas las sedes</option>
                </select>
                <div class="absolute right-3 top-[2.1rem] pointer-events-none text-gray-400">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>
        </div>
        
        <div id="search-info" class="hidden mt-4 pt-4 border-t border-gray-100">
            <p id="search-results-text" class="text-sm text-gray-600"></p>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loading" class="hidden py-12 flex flex-col items-center justify-center text-teal-600">
        <i class="fas fa-circle-notch fa-spin text-3xl mb-3"></i>
        <p class="text-sm font-medium text-gray-500">Cargando áreas...</p>
    </div>

    <!-- Table -->
    <div id="areas-table-container" class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Área</th>
                        <th class="px-6 py-4">Sede</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="areas-table-body" class="divide-y divide-gray-100">
                    <!-- Data injected via JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty States -->
    <div id="no-data-message" class="hidden bg-white shadow-sm border border-gray-100 rounded-2xl p-12 text-center">
        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-map text-3xl text-gray-300"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">No hay áreas</h3>
        <p class="text-gray-500 mb-6">Comienza registrando una nueva área en el sistema.</p>
        <button onclick="document.getElementById('add-area-btn').click()" class="text-teal-600 font-medium hover:text-teal-700">
            Crear primera área &rarr;
        </button>
    </div>

    <div id="no-search-results" class="hidden bg-white shadow-sm border border-gray-100 rounded-2xl p-12 text-center">
        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-search text-3xl text-gray-300"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">Sin resultados</h3>
        <p class="text-gray-500 mb-6">No encontramos áreas que coincidan con tu búsqueda.</p>
        <button id="clear-search-from-message" class="text-teal-600 font-medium hover:text-teal-700">
            Limpiar filtros &rarr;
        </button>
    </div>
</div>

<!-- Modal -->
<div id="area-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <div class="bg-white px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900" id="modal-title">Nueva Área</h3>
                <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="area-form" class="px-6 py-6">
                <input type="hidden" id="area-id">
                
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                        Nombre del Área <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="area-nombre" required maxlength="100"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all"
                        placeholder="Ej: Gerencia, Facturación...">
                </div>
                
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                        Sede <span class="text-red-500">*</span>
                    </label>
                    <select id="area-sede" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                        <option value="">Seleccionar sede...</option>
                    </select>
                </div>
            </form>
            
            <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                <button type="button" id="close-modal-btn-2" class="w-full sm:w-auto px-6 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all">
                    Cancelar
                </button>
                <button type="submit" id="save-btn" form="area-form" class="w-full sm:w-auto px-6 py-2.5 bg-teal-600 text-white font-medium rounded-xl hover:bg-teal-700 shadow-lg shadow-teal-500/30 transition-all flex items-center justify-center">
                    <span id="save-btn-text">Guardar Área</span>
                    <i id="save-btn-loading" class="fas fa-circle-notch fa-spin ml-2 hidden"></i>
                </button>
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
            <h3 class="text-lg font-bold text-gray-900 mb-2">¿Eliminar área?</h3>
            <p class="text-sm text-gray-500 mb-6">Esta acción no se puede deshacer. El área será eliminada permanentemente.</p>
            
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button type="button" id="cancel-delete-btn" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all">
                    Cancelar
                </button>
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
    const API_URL = '../api/controllers/areas.php';
    
    // State
    let allAreas = []; 
    let allSedes = [];
    let filteredAreas = []; 
    let currentFilters = { search: '', sede: '' };
    let areaIdToDelete = null;
    
    // DOM Elements
    const dom = {
        tableBody: document.getElementById('areas-table-body'),
        tableContainer: document.getElementById('areas-table-container'),
        noData: document.getElementById('no-data-message'),
        noSearch: document.getElementById('no-search-results'),
        loading: document.getElementById('loading'),
        search: document.getElementById('search-input'),
        clearSearch: document.getElementById('clear-search'),
        searchInfo: document.getElementById('search-info'),
        searchResultsText: document.getElementById('search-results-text'),
        filters: {
            sede: document.getElementById('sede-filter'),
            reset: document.getElementById('reset-filters'),
            clearMsg: document.getElementById('clear-search-from-message')
        },
        modal: {
            self: document.getElementById('area-modal'),
            title: document.getElementById('modal-title'),
            form: document.getElementById('area-form'),
            close: document.getElementById('close-modal-btn'),
            close2: document.getElementById('close-modal-btn-2'),
            saveBtn: document.getElementById('save-btn'),
            saveText: document.getElementById('save-btn-text'),
            saveLoading: document.getElementById('save-btn-loading'),
            inputs: {
                id: document.getElementById('area-id'),
                nombre: document.getElementById('area-nombre'),
                sede: document.getElementById('area-sede')
            }
        },
        deleteModal: {
            self: document.getElementById('delete-confirm-modal'),
            confirm: document.getElementById('confirm-delete-btn'),
            cancel: document.getElementById('cancel-delete-btn'),
            text: document.getElementById('delete-btn-text'),
            loading: document.getElementById('delete-btn-loading')
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

    // Core Functions
    function filterAreas() {
        let filtered = allAreas;
        
        if (currentFilters.sede) filtered = filtered.filter(a => a.id_sede.toString() === currentFilters.sede);
        
        if (currentFilters.search) {
            const term = currentFilters.search.toLowerCase().trim();
            filtered = filtered.filter(a => 
                a.nombre_area.toLowerCase().includes(term) ||
                (a.nombre_sede && a.nombre_sede.toLowerCase().includes(term))
            );
        }
        
        filteredAreas = filtered;
        renderAreas(filteredAreas);
        updateSearchInfo();
    }

    function updateSearchInfo() {
        if (currentFilters.search || currentFilters.sede) {
            const count = filteredAreas.length;
            let message = `Mostrando ${count} resultado${count !== 1 ? 's' : ''}`;
            
            const filters = [];
            if (currentFilters.search) filters.push(`"${currentFilters.search}"`);
            if (currentFilters.sede) {
                const sede = allSedes.find(s => s.id_sede.toString() === currentFilters.sede);
                if (sede) filters.push(`sede: ${sede.nombre_sede}`);
            }
            
            if (filters.length > 0) message += ` para ${filters.join(', ')}`;
            
            dom.searchResultsText.textContent = message;
            dom.searchInfo.classList.remove('hidden');
        } else {
            dom.searchInfo.classList.add('hidden');
        }
    }

    function renderAreas(areas) {
        dom.tableBody.innerHTML = '';
        
        if (areas.length > 0) {
            dom.tableContainer.classList.remove('hidden');
            dom.noData.classList.add('hidden');
            dom.noSearch.classList.add('hidden');
            
            areas.forEach(a => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition-colors duration-150 group';
                row.innerHTML = `
                    <td class="px-6 py-4 text-xs font-mono font-bold text-gray-400 group-hover:text-teal-600">#${a.id_area}</td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-gray-900">${escapeHtml(a.nombre_area)}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                            <i class="fas fa-building mr-1.5 text-[10px]"></i>${escapeHtml(a.nombre_sede || 'N/A')}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button class="edit-btn p-2 text-teal-600 hover:bg-teal-50 rounded-lg transition-colors" 
                                data-id="${a.id_area}" 
                                data-nombre="${escapeHtml(a.nombre_area)}" 
                                data-sede="${a.id_sede}"
                                title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="delete-btn p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" 
                                data-id="${a.id_area}"
                                title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                `;
                dom.tableBody.appendChild(row);
            });
        } else {
            dom.tableContainer.classList.add('hidden');
            if (currentFilters.search || currentFilters.sede) {
                dom.noSearch.classList.remove('hidden');
                dom.noData.classList.add('hidden');
            } else {
                dom.noData.classList.remove('hidden');
                dom.noSearch.classList.add('hidden');
            }
        }
    }

    // Data Loading
    async function loadData() {
        setLoading(true);
        try {
            const [areasRes, sedesRes] = await Promise.all([
                fetch(API_URL),
                fetch(API_URL + '?action=get_sedes')
            ]);

            const areasData = await areasRes.json();
            const sedesData = await sedesRes.json();

            allAreas = areasData.records || [];
            allSedes = sedesData.sedes || [];

            // Populate Selects
            const sedeOptions = allSedes.map(s => `<option value="${s.id_sede}">${escapeHtml(s.nombre_sede)}</option>`).join('');
            dom.filters.sede.innerHTML = '<option value="">Todas las sedes</option>' + sedeOptions;
            dom.modal.inputs.sede.innerHTML = '<option value="">Seleccionar sede...</option>' + sedeOptions;

            filterAreas();

        } catch (error) {
            console.error(error);
            showToast('Error al cargar datos', 'error');
        } finally {
            setLoading(false);
        }
    }

    // Event Listeners
    dom.search.addEventListener('input', (e) => {
        currentFilters.search = e.target.value;
        dom.clearSearch.classList.toggle('hidden', !e.target.value);
        filterAreas();
    });

    dom.clearSearch.addEventListener('click', () => {
        dom.search.value = '';
        currentFilters.search = '';
        dom.clearSearch.classList.add('hidden');
        filterAreas();
    });

    dom.filters.sede.addEventListener('change', (e) => {
        currentFilters.sede = e.target.value;
        filterAreas();
    });

    const clearAllFilters = () => {
        dom.search.value = '';
        dom.filters.sede.value = '';
        currentFilters = { search: '', sede: '' };
        dom.clearSearch.classList.add('hidden');
        filterAreas();
    };

    dom.filters.reset.addEventListener('click', clearAllFilters);
    dom.filters.clearMsg.addEventListener('click', clearAllFilters);

    // Modal Actions
    document.getElementById('add-area-btn').addEventListener('click', () => {
        dom.modal.form.reset();
        dom.modal.inputs.id.value = '';
        dom.modal.title.textContent = 'Nueva Área';
        dom.modal.self.classList.remove('hidden');
    });

    [dom.modal.close, dom.modal.close2].forEach(btn => 
        btn.addEventListener('click', () => dom.modal.self.classList.add('hidden'))
    );

    dom.modal.form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const id = dom.modal.inputs.id.value;
        const formData = {
            nombre_area: dom.modal.inputs.nombre.value,
            id_sede: parseInt(dom.modal.inputs.sede.value)
        };

        if (id) formData.id_area = parseInt(id);

        dom.modal.saveBtn.disabled = true;
        dom.modal.saveText.textContent = 'Guardando...';
        dom.modal.saveLoading.classList.remove('hidden');

        try {
            const method = id ? 'PUT' : 'POST';
            const res = await fetch(API_URL, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const result = await res.json();

            if (res.ok) {
                showToast(result.message || 'Área guardada', 'success');
                dom.modal.self.classList.add('hidden');
                loadData();
            } else {
                throw new Error(result.message || 'Error al guardar');
            }
        } catch (error) {
            showToast(error.message, 'error');
        } finally {
            dom.modal.saveBtn.disabled = false;
            dom.modal.saveText.textContent = 'Guardar Área';
            dom.modal.saveLoading.classList.add('hidden');
        }
    });

    // Table Actions
    dom.tableBody.addEventListener('click', (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;
        
        if (btn.classList.contains('edit-btn')) {
            dom.modal.inputs.id.value = btn.dataset.id;
            dom.modal.inputs.nombre.value = btn.dataset.nombre;
            dom.modal.inputs.sede.value = btn.dataset.sede;
            dom.modal.title.textContent = 'Editar Área';
            dom.modal.self.classList.remove('hidden');
        }
        
        if (btn.classList.contains('delete-btn')) {
            areaIdToDelete = btn.dataset.id;
            dom.deleteModal.self.classList.remove('hidden');
        }
    });

    // Delete Actions
    dom.deleteModal.cancel.addEventListener('click', () => dom.deleteModal.self.classList.add('hidden'));
    
    dom.deleteModal.confirm.addEventListener('click', async () => {
        if (!areaIdToDelete) return;
        
        dom.deleteModal.confirm.disabled = true;
        dom.deleteModal.loading.classList.remove('hidden');
        
        try {
            const res = await fetch(API_URL, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_area: parseInt(areaIdToDelete) })
            });
            const result = await res.json();
            
            if (res.ok) {
                showToast('Área eliminada', 'success');
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