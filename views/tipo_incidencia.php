<?php
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tipos de Incidencia</h1>
            <p class="text-sm text-gray-500 mt-1">Categoriza los problemas reportados en el sistema</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <div class="relative flex-1 lg:w-80 group">
                <input 
                    type="text" 
                    id="search-input" 
                    placeholder="Buscar tipos..." 
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
            
            <button id="add-tipo-btn" class="bg-teal-600 hover:bg-teal-700 text-white font-medium py-2.5 px-5 rounded-xl shadow-lg shadow-teal-500/30 transition-all duration-300 flex items-center justify-center whitespace-nowrap transform hover:-translate-y-0.5">
                <i class="fas fa-tags mr-2"></i> Nuevo Tipo
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div id="stats-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 hidden">
        <!-- Injected via JS -->
    </div>

    <!-- Search Info -->
    <div id="search-info" class="hidden bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 flex items-center justify-between">
        <p id="search-results-text" class="text-sm text-gray-600"></p>
        <button id="reset-search" class="text-xs text-teal-600 hover:text-teal-700 font-medium transition-colors">
            Ver todos
        </button>
    </div>

    <!-- Loading State -->
    <div id="loading" class="hidden py-12 flex flex-col items-center justify-center text-teal-600">
        <i class="fas fa-circle-notch fa-spin text-3xl mb-3"></i>
        <p class="text-sm font-medium text-gray-500">Cargando tipos...</p>
    </div>

    <!-- Table -->
    <div id="tipos-table-container" class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Nombre del Tipo</th>
                        <th class="px-6 py-4">Incidencias</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tipos-table-body" class="divide-y divide-gray-100">
                    <!-- Data injected via JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty States -->
    <div id="no-data-message" class="hidden bg-white shadow-sm border border-gray-100 rounded-2xl p-12 text-center">
        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-tags text-3xl text-gray-300"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">No hay tipos registrados</h3>
        <p class="text-gray-500 mb-6">Comienza creando categorías para las incidencias.</p>
        <button onclick="document.getElementById('add-tipo-btn').click()" class="text-teal-600 font-medium hover:text-teal-700">
            Crear primer tipo &rarr;
        </button>
    </div>

    <div id="no-search-results" class="hidden bg-white shadow-sm border border-gray-100 rounded-2xl p-12 text-center">
        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-search text-3xl text-gray-300"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">Sin resultados</h3>
        <p class="text-gray-500 mb-6">No encontramos tipos que coincidan con tu búsqueda.</p>
        <button id="clear-search-from-message" class="text-teal-600 font-medium hover:text-teal-700">
            Limpiar búsqueda &rarr;
        </button>
    </div>
</div>

<!-- Modal -->
<div id="tipo-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <div class="bg-white px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900" id="modal-title">Nuevo Tipo</h3>
                <button id="close-modal-btn" class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="tipo-form" class="px-6 py-6">
                <input type="hidden" id="tipo-id">
                
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                        Nombre del Tipo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="tipo-nombre" required maxlength="50"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all"
                        placeholder="Ej: Hardware, Software, Redes...">
                </div>
            </form>
            
            <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                <button type="button" id="close-modal-btn-2" class="w-full sm:w-auto px-6 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-all">
                    Cancelar
                </button>
                <button type="submit" id="save-btn" form="tipo-form" class="w-full sm:w-auto px-6 py-2.5 bg-teal-600 text-white font-medium rounded-xl hover:bg-teal-700 shadow-lg shadow-teal-500/30 transition-all flex items-center justify-center">
                    <span id="save-btn-text">Guardar Tipo</span>
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
            <h3 class="text-lg font-bold text-gray-900 mb-2">¿Eliminar tipo?</h3>
            <p class="text-sm text-gray-500 mb-6">Esta acción no se puede deshacer. Asegúrate de que no haya incidencias activas asociadas.</p>
            
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
    const API_URL = '../api/controllers/tipos_incidencias.php';
    
    // State
    let allTipos = []; 
    let allStats = [];
    let filteredTipos = []; 
    let currentSearchTerm = ''; 
    let tipoIdToDelete = null;
    
    // DOM Elements
    const dom = {
        tableBody: document.getElementById('tipos-table-body'),
        tableContainer: document.getElementById('tipos-table-container'),
        noData: document.getElementById('no-data-message'),
        noSearch: document.getElementById('no-search-results'),
        loading: document.getElementById('loading'),
        search: document.getElementById('search-input'),
        clearSearch: document.getElementById('clear-search'),
        searchInfo: document.getElementById('search-info'),
        searchResultsText: document.getElementById('search-results-text'),
        resetSearch: document.getElementById('reset-search'),
        clearMsg: document.getElementById('clear-search-from-message'),
        stats: document.getElementById('stats-container'),
        modal: {
            self: document.getElementById('tipo-modal'),
            title: document.getElementById('modal-title'),
            form: document.getElementById('tipo-form'),
            close: document.getElementById('close-modal-btn'),
            close2: document.getElementById('close-modal-btn-2'),
            saveBtn: document.getElementById('save-btn'),
            saveText: document.getElementById('save-btn-text'),
            saveLoading: document.getElementById('save-btn-loading'),
            inputs: {
                id: document.getElementById('tipo-id'),
                nombre: document.getElementById('tipo-nombre')
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
    function filterTipos() {
        if (!currentSearchTerm.trim()) {
            filteredTipos = allTipos;
        } else {
            const term = currentSearchTerm.toLowerCase().trim();
            filteredTipos = allTipos.filter(t => t.nombre.toLowerCase().includes(term));
        }
        renderTipos(filteredTipos);
        updateSearchInfo();
    }

    function updateSearchInfo() {
        if (currentSearchTerm) {
            const count = filteredTipos.length;
            dom.searchResultsText.textContent = `Mostrando ${count} resultado${count !== 1 ? 's' : ''} para "${currentSearchTerm}"`;
            dom.searchInfo.classList.remove('hidden');
        } else {
            dom.searchInfo.classList.add('hidden');
        }
    }

    function renderStats(stats) {
        if (!stats || stats.length === 0) {
            dom.stats.classList.add('hidden');
            return;
        }

        const totals = stats.reduce((acc, curr) => ({
            total: acc.total + parseInt(curr.incidencias_count),
            abiertas: acc.abiertas + parseInt(curr.abiertas),
            proceso: acc.proceso + parseInt(curr.en_proceso),
            cerradas: acc.cerradas + parseInt(curr.cerradas)
        }), { total: 0, abiertas: 0, proceso: 0, cerradas: 0 });

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

        dom.stats.innerHTML = 
            createCard('Total Incidencias', totals.total, 'fas fa-layer-group', 'teal') +
            createCard('Abiertas', totals.abiertas, 'fas fa-exclamation-circle', 'red') +
            createCard('En Proceso', totals.proceso, 'fas fa-clock', 'amber') +
            createCard('Cerradas', totals.cerradas, 'fas fa-check-circle', 'emerald');
            
        dom.stats.classList.remove('hidden');
    }

    function getIncidenciasCount(tipoId) {
        const stat = allStats.find(s => s.id_tipo_incidencia == tipoId);
        return stat ? parseInt(stat.incidencias_count) : 0;
    }

    function renderTipos(tipos) {
        dom.tableBody.innerHTML = '';
        
        if (tipos.length > 0) {
            dom.tableContainer.classList.remove('hidden');
            dom.noData.classList.add('hidden');
            dom.noSearch.classList.add('hidden');
            
            tipos.forEach(t => {
                const count = getIncidenciasCount(t.id_tipo_incidencia);
                
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition-colors duration-150 group';
                row.innerHTML = `
                    <td class="px-6 py-4 text-xs font-mono font-bold text-gray-400 group-hover:text-teal-600">#${t.id_tipo_incidencia}</td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-gray-900">${escapeHtml(t.nombre)}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${count > 0 ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-gray-50 text-gray-500 border border-gray-100'}">
                            ${count} incidencia${count !== 1 ? 's' : ''}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button class="edit-btn p-2 text-teal-600 hover:bg-teal-50 rounded-lg transition-colors" 
                                data-id="${t.id_tipo_incidencia}" 
                                data-nombre="${escapeHtml(t.nombre)}"
                                title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="delete-btn p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors ${count > 0 ? 'opacity-50 cursor-not-allowed' : ''}" 
                                data-id="${t.id_tipo_incidencia}"
                                ${count > 0 ? 'disabled' : ''}
                                title="${count > 0 ? 'No se puede eliminar: tiene incidencias asociadas' : 'Eliminar'}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                `;
                dom.tableBody.appendChild(row);
            });
        } else {
            dom.tableContainer.classList.add('hidden');
            if (currentSearchTerm) {
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
            const [tiposRes, statsRes] = await Promise.all([
                fetch(API_URL),
                fetch(API_URL + '?action=stats')
            ]);

            const tiposData = await tiposRes.json();
            const statsData = await statsRes.json();

            allTipos = tiposData.records || [];
            allStats = statsData.stats || [];

            renderStats(allStats);
            filterTipos();

        } catch (error) {
            console.error(error);
            showToast('Error al cargar datos', 'error');
        } finally {
            setLoading(false);
        }
    }

    // Event Listeners
    dom.search.addEventListener('input', (e) => {
        currentSearchTerm = e.target.value;
        dom.clearSearch.classList.toggle('hidden', !e.target.value);
        filterTipos();
    });

    const clearSearch = () => {
        dom.search.value = '';
        currentSearchTerm = '';
        dom.clearSearch.classList.add('hidden');
        filterTipos();
    };

    dom.clearSearch.addEventListener('click', clearSearch);
    dom.resetSearch.addEventListener('click', clearSearch);
    dom.clearMsg.addEventListener('click', clearSearch);

    // Modal Actions
    document.getElementById('add-tipo-btn').addEventListener('click', () => {
        dom.modal.form.reset();
        dom.modal.inputs.id.value = '';
        dom.modal.title.textContent = 'Nuevo Tipo';
        dom.modal.self.classList.remove('hidden');
    });

    [dom.modal.close, dom.modal.close2].forEach(btn => 
        btn.addEventListener('click', () => dom.modal.self.classList.add('hidden'))
    );

    dom.modal.form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const id = dom.modal.inputs.id.value;
        const formData = { nombre: dom.modal.inputs.nombre.value };
        if (id) formData.id_tipo_incidencia = parseInt(id);

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
                showToast(result.message || 'Tipo guardado', 'success');
                dom.modal.self.classList.add('hidden');
                loadData();
            } else {
                throw new Error(result.message || 'Error al guardar');
            }
        } catch (error) {
            showToast(error.message, 'error');
        } finally {
            dom.modal.saveBtn.disabled = false;
            dom.modal.saveText.textContent = 'Guardar Tipo';
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
            dom.modal.title.textContent = 'Editar Tipo';
            dom.modal.self.classList.remove('hidden');
        }
        
        if (btn.classList.contains('delete-btn') && !btn.disabled) {
            tipoIdToDelete = btn.dataset.id;
            dom.deleteModal.self.classList.remove('hidden');
        }
    });

    // Delete Actions
    dom.deleteModal.cancel.addEventListener('click', () => dom.deleteModal.self.classList.add('hidden'));
    
    dom.deleteModal.confirm.addEventListener('click', async () => {
        if (!tipoIdToDelete) return;
        
        dom.deleteModal.confirm.disabled = true;
        dom.deleteModal.loading.classList.remove('hidden');
        
        try {
            const res = await fetch(API_URL, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_tipo_incidencia: parseInt(tipoIdToDelete) })
            });
            const result = await res.json();
            
            if (res.ok) {
                showToast('Tipo eliminado', 'success');
                dom.deleteModal.self.classList.add('hidden');
                loadData();
            } else {
                if (result.code === 'TIPO_IN_USE') {
                    showToast('No se puede eliminar: en uso', 'error');
                } else {
                    throw new Error(result.message);
                }
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