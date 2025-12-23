<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Avanzados</title>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { margin: 0; padding: 0; }
        .chart-container { position: relative; height: 300px; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-sans text-gray-800">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-30">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-teal-50 p-2 rounded-xl">
                        <img src="../assets/images/logo.png" alt="Logo" class="h-8 w-8 rounded-lg">
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">Reportes Avanzados</h1>
                        <p class="text-xs text-teal-600 font-medium">Análisis y estadísticas con filtros</p>
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

    <main class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Filtros -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-800 flex items-center uppercase tracking-wider">
                    <i class="fas fa-filter mr-2 text-teal-500"></i>Filtros de Búsqueda
                </h3>
                <span id="filtros-activos" class="text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded-lg border border-gray-100">
                    Sin filtros aplicados
                </span>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5 uppercase tracking-wider">Fecha Inicio</label>
                    <input type="date" id="fecha_inicio" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5 uppercase tracking-wider">Fecha Fin</label>
                    <input type="date" id="fecha_fin" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5 uppercase tracking-wider">Estado</label>
                    <select id="filtro_estado" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                        <option value="">Todos</option>
                        <option value="abierta">Abierta</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="cerrada">Cerrada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5 uppercase tracking-wider">Prioridad</label>
                    <select id="filtro_prioridad" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                        <option value="">Todas</option>
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                        <option value="critica">Crítica</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5 uppercase tracking-wider">Tipo</label>
                    <select id="filtro_tipo" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5 uppercase tracking-wider">Subtipo</label>
                    <select id="filtro_subtipo" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all" disabled>
                        <option value="">Primero selecciona tipo</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end items-center mt-4 pt-4 border-t border-gray-100 gap-3">
                <button onclick="limpiarFiltros()" class="text-gray-500 hover:text-gray-700 text-sm font-medium px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center">
                    <i class="fas fa-eraser mr-2"></i>Limpiar
                </button>
                <button onclick="aplicarFiltros()" class="bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium px-6 py-2 rounded-xl shadow-sm hover:shadow transition-all flex items-center">
                    <i class="fas fa-search mr-2"></i>Aplicar Filtros
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div id="stats-summary" class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <!-- Injected via JS -->
        </div>

        <!-- Chart Controls -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
            <div class="flex justify-between items-center mb-4">
                <label class="text-sm font-bold text-gray-800 flex items-center uppercase tracking-wider">
                    <i class="fas fa-chart-pie mr-2 text-teal-500"></i>Gráficos a Mostrar:
                </label>
                <button onclick="actualizarVistaGraficos()" class="text-teal-600 hover:text-teal-700 text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-lg hover:bg-teal-50 transition-colors flex items-center">
                    <i class="fas fa-sync mr-1.5"></i>Actualizar
                </button>
            </div>
            <div class="flex flex-wrap gap-3">
                <label class="flex items-center space-x-2 text-xs font-medium bg-blue-50 text-blue-700 px-3 py-2 rounded-lg cursor-pointer hover:bg-blue-100 transition-colors border border-blue-100 select-none">
                    <input type="checkbox" id="show_trend" checked class="rounded text-blue-600 focus:ring-blue-500 border-gray-300">
                    <span><i class="fas fa-chart-line mr-1.5"></i>Tendencia</span>
                </label>
                <label class="flex items-center space-x-2 text-xs font-medium bg-emerald-50 text-emerald-700 px-3 py-2 rounded-lg cursor-pointer hover:bg-emerald-100 transition-colors border border-emerald-100 select-none">
                    <input type="checkbox" id="show_status" checked class="rounded text-emerald-600 focus:ring-emerald-500 border-gray-300">
                    <span><i class="fas fa-chart-pie mr-1.5"></i>Estados</span>
                </label>
                <label class="flex items-center space-x-2 text-xs font-medium bg-amber-50 text-amber-700 px-3 py-2 rounded-lg cursor-pointer hover:bg-amber-100 transition-colors border border-amber-100 select-none">
                    <input type="checkbox" id="show_priority" checked class="rounded text-amber-600 focus:ring-amber-500 border-gray-300">
                    <span><i class="fas fa-chart-bar mr-1.5"></i>Prioridad</span>
                </label>
                <label class="flex items-center space-x-2 text-xs font-medium bg-purple-50 text-purple-700 px-3 py-2 rounded-lg cursor-pointer hover:bg-purple-100 transition-colors border border-purple-100 select-none">
                    <input type="checkbox" id="show_type" checked class="rounded text-purple-600 focus:ring-purple-500 border-gray-300">
                    <span><i class="fas fa-tags mr-1.5"></i>Tipos</span>
                </label>
                <label class="flex items-center space-x-2 text-xs font-medium bg-indigo-50 text-indigo-700 px-3 py-2 rounded-lg cursor-pointer hover:bg-indigo-100 transition-colors border border-indigo-100 select-none">
                    <input type="checkbox" id="show_subtype" checked class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                    <span><i class="fas fa-tag mr-1.5"></i>Subtipos</span>
                </label>
            </div>
        </div>

        <!-- Charts Grid -->
        <div id="charts-container" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Injected via JS -->
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-sm font-bold text-gray-800 flex items-center uppercase tracking-wider">
                    <i class="fas fa-table mr-2 text-teal-500"></i>Datos Detallados 
                    <span id="total-registros" class="ml-3 px-2.5 py-0.5 rounded-full bg-teal-100 text-teal-700 text-xs font-bold">0</span>
                </h3>
                <button onclick="exportarExcel()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium px-4 py-2 rounded-lg shadow-sm hover:shadow transition-all flex items-center">
                    <i class="fas fa-file-excel mr-2"></i>Excel
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider">Título</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider">Subtipo</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider">Prioridad</th>
                            <th class="px-6 py-3 text-xs uppercase tracking-wider">Fecha</th>
                        </tr>
                    </thead>
                    <tbody id="detailTableBody" class="divide-y divide-gray-100">
                        <tr><td colspan="7" class="text-center py-8 text-gray-500">Cargando datos...</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="pagination" class="px-6 py-4 border-t border-gray-100 flex justify-center gap-2 bg-gray-50/30"></div>
        </div>
    </main>

    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2"></div>

    <script>
        const API_BASE = '../api/controllers/incidencias.php';
        const TIPOS_API = '../api/controllers/tipos_incidencias.php';
        const SUBTIPOS_API = '../api/controllers/subtipos_incidencias.php';
        const USUARIOS_API = '../api/controllers/usuario.php';
        
        let chartInstances = {};
        let currentData = [];
        let filteredData = [];
        let currentPage = 1;
        const itemsPerPage = 15;
        let allTipos = [];
        let allSubtipos = [];

        // Chart defaults
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748b';
        Chart.defaults.scale.grid.color = '#f1f5f9';

        function displayCurrentUser() {
            const userSession = localStorage.getItem('user_session') || sessionStorage.getItem('user_session');
            if (userSession) {
                try {
                    const session = JSON.parse(userSession);
                    document.getElementById('current-user-name').textContent = (session.nombre_completo || session.username).substring(0, 20);
                    document.getElementById('user-info').classList.remove('hidden');
                    document.getElementById('user-info').classList.add('flex');
                } catch(e) {}
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            displayCurrentUser();
            const hoy = new Date();
            const hace30dias = new Date(hoy.getTime() - (30 * 24 * 60 * 60 * 1000));
            document.getElementById('fecha_fin').valueAsDate = hoy;
            document.getElementById('fecha_inicio').valueAsDate = hace30dias;
            cargarFiltros();
            aplicarFiltros();
        });

        async function cargarFiltros() {
            try {
                const [tiposResp, subtiposResp, usuariosResp] = await Promise.all([
                    fetch(TIPOS_API), 
                    fetch(SUBTIPOS_API), 
                    fetch(USUARIOS_API)
                ]);
                
                const tipos = await tiposResp.json();
                const subtipos = await subtiposResp.json();
                const usuarios = await usuariosResp.json();
                
                allTipos = tipos.records || [];
                allSubtipos = subtipos.records || [];
                
                const tipoSelect = document.getElementById('filtro_tipo');
                allTipos.forEach(tipo => {
                    tipoSelect.innerHTML += `<option value="${tipo.id_tipo_incidencia}">${tipo.nombre}</option>`;
                });
                
                tipoSelect.addEventListener('change', cargarSubtiposPorTipo);
                
            } catch (error) {
                console.error('Error cargando filtros:', error);
                showToast('Error al cargar filtros', 'error');
            }
        }

        function cargarSubtiposPorTipo() {
            const tipoId = document.getElementById('filtro_tipo').value;
            const subtipoSelect = document.getElementById('filtro_subtipo');
            
            if (!tipoId) {
                subtipoSelect.disabled = true;
                subtipoSelect.innerHTML = '<option value="">Primero selecciona tipo</option>';
                return;
            }
            
            const subtiposFiltrados = allSubtipos.filter(s => s.id_tipo_incidencia == tipoId);
            
            subtipoSelect.disabled = false;
            subtipoSelect.innerHTML = '<option value="">Todos los subtipos</option>';
            
            subtiposFiltrados.forEach(subtipo => {
                subtipoSelect.innerHTML += `<option value="${subtipo.id_subtipo_incidencia}">${subtipo.nombre}</option>`;
            });
        }

        async function aplicarFiltros() {
            try {
                const response = await fetch(API_BASE);
                const data = await response.json();
                currentData = data.records || [];
                
                filteredData = currentData.filter(inc => {
                    const fechaInicio = document.getElementById('fecha_inicio').value;
                    const fechaFin = document.getElementById('fecha_fin').value;
                    const fechaInc = inc.fecha_reporte.split(' ')[0];
                    
                    if (fechaInicio && fechaInc < fechaInicio) return false;
                    if (fechaFin && fechaInc > fechaFin) return false;
                    
                    const estadoFiltro = document.getElementById('filtro_estado').value;
                    if (estadoFiltro && inc.estado !== estadoFiltro) return false;
                    
                    const prioridadFiltro = document.getElementById('filtro_prioridad').value;
                    if (prioridadFiltro && inc.prioridad !== prioridadFiltro) return false;
                    
                    const tipoFiltro = document.getElementById('filtro_tipo').value;
                    if (tipoFiltro && inc.id_tipo_incidencia != tipoFiltro) return false;
                    
                    const subtipoFiltro = document.getElementById('filtro_subtipo').value;
                    if (subtipoFiltro && inc.id_subtipo_incidencia != subtipoFiltro) return false;
                    
                    return true;
                });
                
                actualizarTextoFiltros();
                actualizarEstadisticas(filteredData);
                actualizarVistaGraficos();
                actualizarTabla(filteredData);
                
                showToast(`${filteredData.length} incidencias encontradas`, 'success');
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al cargar datos', 'error');
            }
        }

        function actualizarTextoFiltros() {
            const filtros = [];
            
            if (document.getElementById('fecha_inicio').value) filtros.push('Fecha inicio');
            if (document.getElementById('fecha_fin').value) filtros.push('Fecha fin');
            if (document.getElementById('filtro_estado').value) filtros.push('Estado');
            if (document.getElementById('filtro_prioridad').value) filtros.push('Prioridad');
            if (document.getElementById('filtro_tipo').value) filtros.push('Tipo');
            if (document.getElementById('filtro_subtipo').value) filtros.push('Subtipo');
            
            const texto = filtros.length > 0 ? 
                `${filtros.length} filtro(s) activo(s): ${filtros.join(', ')}` : 
                'Sin filtros aplicados';
            
            document.getElementById('filtros-activos').textContent = texto;
        }

        function limpiarFiltros() {
            document.querySelectorAll('select').forEach(el => el.value = '');
            document.getElementById('filtro_subtipo').disabled = true;
            document.getElementById('filtro_subtipo').innerHTML = '<option value="">Primero selecciona tipo</option>';
            
            const hoy = new Date();
            const hace30dias = new Date(hoy.getTime() - (30 * 24 * 60 * 60 * 1000));
            document.getElementById('fecha_fin').valueAsDate = hoy;
            document.getElementById('fecha_inicio').valueAsDate = hace30dias;
            
            aplicarFiltros();
        }

        function actualizarEstadisticas(data) {
            const stats = {
                total: data.length,
                abiertas: data.filter(d => d.estado === 'abierta').length,
                proceso: data.filter(d => d.estado === 'en_proceso').length,
                cerradas: data.filter(d => d.estado === 'cerrada').length,
                canceladas: data.filter(d => d.estado === 'cancelada').length
            };

            const createStatCard = (title, count, color, icon) => `
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

            document.getElementById('stats-summary').innerHTML = `
                ${createStatCard('Total', stats.total, 'blue', 'fas fa-clipboard-list')}
                ${createStatCard('Abiertas', stats.abiertas, 'red', 'fas fa-exclamation-circle')}
                ${createStatCard('Proceso', stats.proceso, 'amber', 'fas fa-clock')}
                ${createStatCard('Cerradas', stats.cerradas, 'emerald', 'fas fa-check-circle')}
                ${createStatCard('Canceladas', stats.canceladas, 'gray', 'fas fa-ban')}
            `;
        }

        function actualizarVistaGraficos() {
            Object.values(chartInstances).forEach(chart => chart?.destroy());
            chartInstances = {};
            
            const container = document.getElementById('charts-container');
            container.innerHTML = '';
            
            const graficos = [
                { id: 'show_trend', canvasId: 'trendChart', title: 'Tendencia Temporal', icon: 'fa-chart-line', color: 'blue', func: crearGraficoTendencia },
                { id: 'show_status', canvasId: 'statusChart', title: 'Por Estado', icon: 'fa-chart-pie', color: 'emerald', func: crearGraficoEstado },
                { id: 'show_priority', canvasId: 'priorityChart', title: 'Por Prioridad', icon: 'fa-chart-bar', color: 'amber', func: crearGraficoPrioridad },
                { id: 'show_type', canvasId: 'typeChart', title: 'Por Tipo', icon: 'fa-tags', color: 'purple', func: crearGraficoTipo },
                { id: 'show_subtype', canvasId: 'subtypeChart', title: 'Tipo y Subtipo', icon: 'fa-tag', color: 'indigo', func: crearGraficoTipoSubtipo }
            ];
            
            graficos.forEach(g => {
                if (document.getElementById(g.id).checked) {
                    const div = document.createElement('div');
                    div.className = 'bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-all duration-300';
                    div.innerHTML = `
                        <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center uppercase tracking-wider">
                            <i class="fas ${g.icon} mr-2 text-${g.color}-500"></i>${g.title}
                        </h3>
                        <div class="chart-container h-64"><canvas id="${g.canvasId}"></canvas></div>
                    `;
                    container.appendChild(div);
                    setTimeout(() => g.func(g.canvasId), 100);
                }
            });
        }

        function crearGraficoTendencia(canvasId) {
            const trendData = {};
            filteredData.forEach(inc => {
                const fecha = inc.fecha_reporte.split(' ')[0];
                trendData[fecha] = (trendData[fecha] || 0) + 1;
            });
            
            chartInstances.trend = new Chart(document.getElementById(canvasId), {
                type: 'line',
                data: {
                    labels: Object.keys(trendData).sort(),
                    datasets: [{
                        label: 'Incidencias',
                        data: Object.keys(trendData).sort().map(k => trendData[k]),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#3b82f6',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        function crearGraficoEstado(canvasId) {
            const estadoData = {};
            filteredData.forEach(inc => estadoData[inc.estado] = (estadoData[inc.estado] || 0) + 1);
            
            chartInstances.status = new Chart(document.getElementById(canvasId), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(estadoData).map(e => e.replace('_', ' ').toUpperCase()),
                    datasets: [{ 
                        data: Object.values(estadoData), 
                        backgroundColor: ['#ef4444', '#f59e0b', '#10b981', '#94a3b8'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { 
                        legend: { 
                            position: 'bottom',
                            labels: { usePointStyle: true, padding: 20 }
                        } 
                    }
                }
            });
        }

        function crearGraficoPrioridad(canvasId) {
            const prioridadData = {};
            filteredData.forEach(inc => prioridadData[inc.prioridad] = (prioridadData[inc.prioridad] || 0) + 1);
            
            chartInstances.priority = new Chart(document.getElementById(canvasId), {
                type: 'bar',
                data: {
                    labels: Object.keys(prioridadData).map(p => p.toUpperCase()),
                    datasets: [{ 
                        label: 'Cantidad', 
                        data: Object.values(prioridadData), 
                        backgroundColor: ['#10b981', '#f59e0b', '#f97316', '#ef4444'],
                        borderRadius: 6,
                        barThickness: 40
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        function crearGraficoTipo(canvasId) {
            const tipoData = {};
            filteredData.forEach(inc => {
                const tipo = inc.tipo_nombre || 'Sin tipo';
                tipoData[tipo] = (tipoData[tipo] || 0) + 1;
            });
            
            const labels = Object.keys(tipoData);
            const colors = generarColores(labels.length);
            
            chartInstances.type = new Chart(document.getElementById(canvasId), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{ 
                        label: 'Cantidad', 
                        data: Object.values(tipoData), 
                        backgroundColor: colors,
                        borderRadius: 6
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        function crearGraficoTipoSubtipo(canvasId) {
            const tipoSubtipoData = {};
            
            filteredData.forEach(inc => {
                const tipo = inc.tipo_nombre || 'Sin tipo';
                const subtipo = inc.subtipo_nombre ? ` → ${inc.subtipo_nombre}` : '';
                const key = `${tipo}${subtipo}`;
                
                tipoSubtipoData[key] = (tipoSubtipoData[key] || 0) + 1;
            });

            const labels = Object.keys(tipoSubtipoData);
            const data = Object.values(tipoSubtipoData);
            const colors = generarColores(labels.length);

            chartInstances.subtype = new Chart(document.getElementById(canvasId), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Cantidad',
                        data: data,
                        backgroundColor: colors,
                        borderRadius: 4,
                        barThickness: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                        y: { grid: { display: false } }
                    }
                }
            });
        }

        function generarColores(count) {
            const colors = [];
            for (let i = 0; i < count; i++) {
                const hue = (i * 360 / count) % 360;
                colors.push(`hsl(${hue}, 70%, 60%)`);
            }
            return colors;
        }

        function actualizarTabla(data) {
            const tbody = document.getElementById('detailTableBody');
            const start = (currentPage - 1) * itemsPerPage;
            const pageData = data.slice(start, start + itemsPerPage);
            document.getElementById('total-registros').textContent = data.length;
            
            if (pageData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-gray-500">No hay datos para mostrar</td></tr>';
                return;
            }
            
            tbody.innerHTML = pageData.map(inc => `
                <tr class="hover:bg-gray-50 transition-colors group">
                    <td class="px-6 py-4 font-mono text-xs font-bold text-gray-400 group-hover:text-teal-600">#${inc.id_incidencia}</td>
                    <td class="px-6 py-4 font-medium text-gray-900">${inc.titulo.substring(0, 30)}${inc.titulo.length > 30 ? '...' : ''}</td>
                    <td class="px-6 py-4">
                        ${inc.tipo_nombre ? `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">${inc.tipo_nombre}</span>` : '<span class="text-gray-400 text-xs italic">Sin tipo</span>'}
                    </td>
                    <td class="px-6 py-4">
                        ${inc.subtipo_nombre ? `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100">${inc.subtipo_nombre}</span>` : '<span class="text-gray-400 text-xs">-</span>'}
                    </td>
                    <td class="px-6 py-4"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${getEstadoClass(inc.estado)}">${inc.estado.replace('_',' ').toUpperCase()}</span></td>
                    <td class="px-6 py-4"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${getPrioridadClass(inc.prioridad)}">${inc.prioridad.toUpperCase()}</span></td>
                    <td class="px-6 py-4 text-gray-500">${new Date(inc.fecha_reporte).toLocaleDateString('es-ES')}</td>
                </tr>
            `).join('');
            
            const totalPages = Math.ceil(data.length / itemsPerPage);
            const paginationHTML = totalPages > 1 ?
                Array.from({length: totalPages}, (_, i) => i + 1).map(i => 
                    `<button onclick="cambiarPagina(${i})" class="w-8 h-8 rounded-lg text-xs font-medium transition-colors ${i === currentPage ? 'bg-teal-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'}">${i}</button>`
                ).join('') : '';
            
            document.getElementById('pagination').innerHTML = paginationHTML;
        }

        function cambiarPagina(page) {
            currentPage = page;
            actualizarTabla(filteredData);
        }

        function exportarExcel() {
            if (filteredData.length === 0) {
                showToast('No hay datos para exportar', 'error');
                return;
            }
            
            let csv = 'ID,Título,Tipo,Subtipo,Estado,Prioridad,Fecha\n';
            
            filteredData.forEach(inc => {
                csv += `${inc.id_incidencia},"${inc.titulo}","${inc.tipo_nombre || 'Sin tipo'}","${inc.subtipo_nombre || 'Sin subtipo'}",${inc.estado},${inc.prioridad},${inc.fecha_reporte}\n`;
            });
            
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `incidencias_${Date.now()}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            
            showToast('Excel exportado exitosamente', 'success');
        }

        function getEstadoClass(estado) {
            return {
                'abierta': 'bg-red-50 text-red-700 border-red-100', 
                'en_proceso': 'bg-amber-50 text-amber-700 border-amber-100', 
                'cerrada': 'bg-emerald-50 text-emerald-700 border-emerald-100',
                'cancelada': 'bg-gray-50 text-gray-700 border-gray-100'
            }[estado] || 'bg-gray-50 text-gray-700 border-gray-100';
        }

        function getPrioridadClass(prioridad) {
            return {
                'baja': 'bg-blue-50 text-blue-700 border-blue-100', 
                'media': 'bg-indigo-50 text-indigo-700 border-indigo-100', 
                'alta': 'bg-orange-50 text-orange-700 border-orange-100', 
                'critica': 'bg-rose-50 text-rose-700 border-rose-100'
            }[prioridad] || 'bg-gray-50 text-gray-700 border-gray-100';
        }

        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            const colors = {
                success: 'bg-teal-500', 
                error: 'bg-red-500', 
                info: 'bg-blue-500'
            };
            toast.className = `${colors[type]} text-white px-6 py-4 rounded-xl shadow-lg transform transition-all duration-300 translate-x-full flex items-center min-w-[300px] z-50`;
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
    </script>
</body>
</html>