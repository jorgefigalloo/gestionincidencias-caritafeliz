<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica Carita Feliz - Sistema de Gestión TI</title>
    <link rel="icon" href="assets/images/logo.ico" type="image/x-icon">
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
                    animation: {
                        'fade-in-up': 'fadeInUp 0.3s ease-out',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-teal-50 to-teal-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-md border-b border-teal-100">
        <div class="container mx-auto px-4 py-5">
            <div class="flex items-center justify-between">
                <!-- Logo y título -->
                <div class="flex items-center space-x-4">
                    <img src="assets/images/logo.png" alt="Logo de la Clínica" class="h-12 w-12 rounded-xl shadow-lg">
                    <div>
                        <h1 class="text-2xl font-bold text-teal-900">Clínica Carita Feliz</h1>
                        <p class="text-sm text-teal-600">Sistema de Gestión de Tecnología</p>
                    </div>
                </div>
                
                <!-- Área de autenticación -->
                <div id="auth-section" class="flex items-center space-x-3">
                    <!-- Mostrar cuando NO hay usuario logueado -->
                    <div id="guest-buttons" class="flex space-x-3">
                        <button id="login-btn" class="bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white font-medium py-2.5 px-5 rounded-xl transition duration-200 flex items-center space-x-2 shadow-lg shadow-teal-500/30 hover:shadow-teal-600/40 transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            <span>Iniciar Sesión</span>
                        </button>
                    </div>
                    
                    <!-- Mostrar cuando hay usuario logueado -->
                    <div id="user-section" class="hidden flex items-center space-x-3">
                        <div class="flex items-center space-x-2 bg-teal-50 text-teal-800 px-4 py-2 rounded-xl border border-teal-200">
                            <div class="w-8 h-8 bg-gradient-to-br from-teal-400 to-teal-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <span id="user-name" class="font-medium">Usuario</span>
                            <span id="user-role" class="text-xs bg-teal-200 text-teal-800 px-2 py-1 rounded-full font-medium">Rol</span>
                        </div>
                        <button id="dashboard-btn" class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-medium py-2.5 px-4 rounded-xl transition duration-200 flex items-center space-x-2 shadow-lg shadow-emerald-500/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <span>Dashboard</span>
                        </button>

                        <button id="chat-toggle" class="bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white px-4 py-2.5 rounded-xl transition duration-200 shadow-lg shadow-teal-500/30">
                            <i class="fas fa-robot mr-2"></i>
                            <span class="hidden sm:inline">Asistente</span>
                        </button>


                        <button id="logout-btn" class="bg-red-500 hover:bg-red-600 text-white font-medium py-2.5 px-3 rounded-xl transition duration-200 shadow-lg shadow-red-500/30" title="Cerrar Sesión">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                        </button>

                                   
                    </div>
                </div>
            </div>
        </div>
    </header>




      <!-- Chat Assistant -->
      <div id="chat-container" class="fixed bottom-6 right-6 w-[380px] bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl hidden z-50 border border-teal-100 overflow-hidden transition-all duration-300 transform origin-bottom-right">
        <!-- Header -->
        <div class="bg-teal-700 text-white p-4 flex items-center justify-between shadow-md">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <div class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-md border border-white/20">
                        <i class="fas fa-robot text-lg"></i>
                    </div>
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-teal-700 rounded-full"></span>
                </div>
                <div>
                    <h4 class="font-bold text-sm tracking-wide">Asistente Virtual</h4>
                    <p class="text-xs text-teal-200 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span> En línea
                    </p>
                </div>
            </div>
            <button id="chat-close" class="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-full transition duration-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Messages Area -->
        <div id="chat-messages" class="h-[400px] overflow-y-auto p-5 space-y-4 bg-gray-50 scroll-smooth">
            <!-- Welcome Message -->
            <div class="flex items-start space-x-3 animate-fade-in-up">
                <div class="w-8 h-8 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center flex-shrink-0 border border-teal-200">
                    <i class="fas fa-robot text-xs"></i>
                </div>
                <div class="bg-white border border-gray-100 text-gray-700 rounded-2xl rounded-tl-none p-4 shadow-sm max-w-[85%]">
                    <p class="text-sm leading-relaxed">¡Hola! �Y'< Soy tu asistente técnico. Puedo ayudarte con problemas de hardware, software, conectividad y más. ¿En qué te ayudo hoy?</p>
                </div>
            </div>
        </div>
        
        <!-- Input Area -->
        <div class="p-4 bg-white border-t border-gray-100">
            <div class="relative flex items-center">
                <input 
                    type="text" 
                    id="chat-input" 
                    placeholder="Escribe tu consulta..." 
                    class="w-full pl-4 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded-full focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all text-sm shadow-inner"
                >
                <button id="chat-send" class="absolute right-2 p-2 bg-teal-600 hover:bg-teal-700 text-white rounded-full w-9 h-9 flex items-center justify-center transition duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                    <i class="fas fa-paper-plane text-xs ml-0.5"></i>
                </button>
            </div>
            <div class="text-center mt-2">
                <p class="text-[10px] text-gray-400">Powered by LocalChat AI v3.0</p>
            </div>
        </div>
    </div>







    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50"></div>

    <!-- Contenido principal -->
    <main class="container mx-auto px-4 py-10">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center bg-teal-100 text-teal-700 px-4 py-2 rounded-full text-sm font-medium mb-4">
                <i class="fas fa-headset mr-2"></i>
                Soporte Técnico 24/7
            </div>
            <h2 class="text-4xl md:text-5xl font-bold text-teal-900 mb-4">Reporta una Incidencia Técnica</h2>
            <p class="text-xl text-teal-700 max-w-2xl mx-auto">
                ¿Tienes un problema técnico? Reporta tu incidencia y nuestro equipo de soporte te ayudará a resolverlo de manera rápida y eficiente.
            </p>
        </div>

        <!-- Sección de estadísticas rápidas -->
        <div id="stats-section" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <!-- Las estadísticas se cargarán aquí -->
        </div>

        <!-- Toggle para mostrar/ocultar formulario -->
        <div id="report-btn-container" class="text-center mb-10 hidden">
            <button id="toggle-form-btn" class="bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white font-bold py-4 px-10 rounded-2xl shadow-xl shadow-teal-500/30 transition duration-300 transform hover:scale-105 hover:-translate-y-1">
                <span id="toggle-text"><i class="fas fa-plus-circle mr-2"></i>Reportar Nueva Incidencia</span>
            </button>
        </div>

        <!-- Formulario de reporte (inicialmente oculto) -->
        <div id="report-form-container" class="hidden">
            <div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-xl p-8 border border-teal-100">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-teal-400 to-teal-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-teal-500/30">
                        <i class="fas fa-clipboard-list text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-teal-900">Formulario de Reporte</h3>
                    <p class="text-teal-600 text-sm mt-1">Completa todos los campos requeridos</p>
                </div>
                
                <!-- Mensajes -->
                <div id="message-container" class="hidden mb-6"></div>
                
                <form id="incident-form" class="space-y-6">
                    <!-- Título -->
                    <div>
                        <label for="titulo" class="block text-sm font-semibold text-teal-800 mb-2">
                            Título del Problema <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="titulo" 
                            name="titulo" 
                            required
                            maxlength="100"
                            class="w-full px-4 py-3 border-2 border-teal-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition duration-200 bg-teal-50/50"
                            placeholder="Ej: No puedo acceder al sistema de facturación">
                        <small class="text-teal-500">Máximo 100 caracteres</small>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label for="descripcion" class="block text-sm font-semibold text-teal-800 mb-2">
                            Descripción Detallada <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="descripcion" 
                            name="descripcion" 
                            required
                            rows="4"
                            class="w-full px-4 py-3 border-2 border-teal-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition duration-200 bg-teal-50/50"
                            placeholder="Describe detalladamente el problema que estás experimentando..."></textarea>
                    </div>

                    <!-- Información del reportante -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nombre_reporta" class="block text-sm font-semibold text-teal-800 mb-2">
                                Tu Nombre Completo <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="nombre_reporta" 
                                name="nombre_reporta" 
                                required
                                maxlength="100"
                                class="w-full px-4 py-3 border-2 border-teal-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition duration-200 bg-teal-50/50"
                                placeholder="Nombre completo">
                        </div>
                        
                        <div>
                            <label for="email_reporta" class="block text-sm font-semibold text-teal-800 mb-2">
                                Tu Email <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="email_reporta" 
                                name="email_reporta" 
                                required
                                maxlength="100"
                                class="w-full px-4 py-3 border-2 border-teal-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition duration-200 bg-teal-50/50"
                                placeholder="tu.email@clinica.com">
                        </div>
                    </div>

                                    <!-- Tipo de incidencia y prioridad -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="tipo_incidencia" class="block text-sm font-semibold text-teal-800 mb-2">
                                                Tipo de Problema
                                            </label>
                                            <select 
                                                id="tipo_incidencia" 
                                                name="tipo_incidencia"
                                                class="w-full px-4 py-3 border-2 border-teal-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition duration-200 bg-teal-50/50">
                                                <option value="">Selecciona el tipo...</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label for="prioridad" class="block text-sm font-semibold text-teal-800 mb-2">
                                                Prioridad
                                            </label>
                                            <select 
                                                id="prioridad" 
                                                name="prioridad"
                                                class="w-full px-4 py-3 border-2 border-teal-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition duration-200 bg-teal-50/50">
                                                <option value="baja">�YY� Baja - No es urgente</option>
                                                <option value="media" selected>�YY� Media - Moderadamente urgente</option>
                                                <option value="alta">�YY� Alta - Urgente</option>
                                                <option value="critica">�Y"� Crítica - Muy urgente</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- NUEVO: Campo de Subtipo (inicialmente oculto) -->
                                    <div id="subtipo-container" class="hidden">
                                        <label for="subtipo_incidencia" class="block text-sm font-semibold text-teal-800 mb-2">
                                            Subtipo Específico
                                        </label>
                                        <select 
                                            id="subtipo_incidencia" 
                                            name="subtipo_incidencia"
                                            class="w-full px-4 py-3 border-2 border-teal-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition duration-200 bg-teal-50/50">
                                            <option value="">Selecciona el subtipo...</option>
                                        </select>
                                        <small class="text-teal-500">Selecciona el problema específico</small>
                                    </div>


                    <!-- Botones -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-6">
                        <button 
                            type="button" 
                            id="cancel-btn"
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3.5 px-6 rounded-xl transition duration-300 border-2 border-gray-200">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </button>
                        <button 
                            type="submit" 
                            id="submit-btn"
                            class="flex-1 bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white font-bold py-3.5 px-6 rounded-xl transition duration-300 transform hover:scale-105 shadow-lg shadow-teal-500/30">
                            <span id="submit-text"><i class="fas fa-paper-plane mr-2"></i>Enviar Reporte</span>
                            <div id="submit-loading" class="hidden inline-block ml-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="mt-20 max-w-5xl mx-auto">
            <div class="text-center mb-10">
                <h3 class="text-3xl font-bold text-teal-900 mb-3">¿Qué tipos de problemas podemos ayudarte a resolver?</h3>
                <p class="text-teal-600">Nuestro equipo está capacitado para atender todas tus necesidades tecnológicas</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Tarjeta Hardware -->
                <div class="bg-white rounded-2xl shadow-lg p-6 text-center hover:shadow-xl transition duration-300 border border-teal-100 group hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-rose-400 to-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-rose-500/30 group-hover:scale-110 transition">
                        <i class="fas fa-desktop text-white text-2xl"></i>
                    </div>
                    <h4 class="font-bold text-teal-900 mb-2 text-lg">Hardware</h4>
                    <p class="text-sm text-teal-600">Computadoras, impresoras, equipos médicos</p>
                </div>

                <!-- Tarjeta Software -->
                <div class="bg-white rounded-2xl shadow-lg p-6 text-center hover:shadow-xl transition duration-300 border border-teal-100 group hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-sky-400 to-sky-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-sky-500/30 group-hover:scale-110 transition">
                        <i class="fas fa-code text-white text-2xl"></i>
                    </div>
                    <h4 class="font-bold text-teal-900 mb-2 text-lg">Software</h4>
                    <p class="text-sm text-teal-600">Sistemas, aplicaciones, actualizaciones</p>
                </div>

                <!-- Tarjeta Red -->
                <div class="bg-white rounded-2xl shadow-lg p-6 text-center hover:shadow-xl transition duration-300 border border-teal-100 group hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition">
                        <i class="fas fa-wifi text-white text-2xl"></i>
                    </div>
                    <h4 class="font-bold text-teal-900 mb-2 text-lg">Conectividad</h4>
                    <p class="text-sm text-teal-600">Internet, red interna, WiFi</p>
                </div>

                <!-- Tarjeta Mantenimiento -->
                <div class="bg-white rounded-2xl shadow-lg p-6 text-center hover:shadow-xl transition duration-300 border border-teal-100 group hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-400 to-amber-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-amber-500/30 group-hover:scale-110 transition">
                        <i class="fas fa-tools text-white text-2xl"></i>
                    </div>
                    <h4 class="font-bold text-teal-900 mb-2 text-lg">Mantenimiento</h4>
                    <p class="text-sm text-teal-600">Preventivo, correctivo, optimización</p>
                </div>
            </div>
        </div>

    <script>
            const INCIDENCIAS_API = 'api/controllers/incidencias.php';
            const TIPOS_API = 'api/controllers/tipos_incidencias.php';
            updateAuthUI(false);
        }

        // Cargar tipos de incidencia



                    async function loadTiposIncidencia() {
                        try {
                            const response = await fetch(TIPOS_API);
                            if (!response.ok) throw new Error('Error al cargar tipos');
                            
                            const result = await response.json();
                            const tipoSelect = document.getElementById('tipo_incidencia');
                            
                            tipoSelect.innerHTML = '<option value="">Selecciona el tipo...</option>';
                            
                            if (result.records && result.records.length > 0) {
                                result.records.forEach(tipo => {
                                    const option = document.createElement('option');
                                    option.value = tipo.id_tipo_incidencia;
                                    option.textContent = tipo.nombre;
                                    tipoSelect.appendChild(option);
                                });
                            }
                            
                            // NUEVO: Agregar event listener para cargar subtipos
                            tipoSelect.addEventListener('change', loadSubtiposByTipo);
                        } catch (error) {
                            console.error('Error al cargar tipos:', error);
                        }
                    }

                    // NUEVA FUNCI�"N: Cargar subtipos según el tipo seleccionado
                    async function loadSubtiposByTipo() {
                        const tipoSelect = document.getElementById('tipo_incidencia');
                        const subtipoSelect = document.getElementById('subtipo_incidencia');
                        const subtipoContainer = document.getElementById('subtipo-container');
                        
                        const tipoId = tipoSelect.value;
                        
                        // Si no hay tipo seleccionado, ocultar subtipos
                        if (!tipoId) {
                            subtipoContainer.classList.add('hidden');
                            subtipoSelect.innerHTML = '<option value="">Selecciona primero un tipo...</option>';
                            return;
                        }
                        
                        try {
                           // const response = await fetch(`../api/controllers/subtipos_incidencias.php?action=by_tipo&id_tipo=${tipoId}`);
                           const response = await fetch(`api/controllers/subtipos_incidencias.php?action=by_tipo&id_tipo=${tipoId}`);
                            const result = await response.json();
                            
                            subtipoSelect.innerHTML = '<option value="">Selecciona el subtipo...</option>';
                            
                            if (result.subtipos && result.subtipos.length > 0) {
                                result.subtipos.forEach(subtipo => {
                                    const option = document.createElement('option');
                                    option.value = subtipo.id_subtipo_incidencia;
                                    option.textContent = subtipo.nombre;
                                    subtipoSelect.appendChild(option);
                                });
                                subtipoContainer.classList.remove('hidden');
                            } else {
                                subtipoContainer.classList.add('hidden');
                                showToast('No hay subtipos disponibles para este tipo', 'info');
                            }
                        } catch (error) {
                            console.error('Error al cargar subtipos:', error);
                            subtipoContainer.classList.add('hidden');
                        }
                    }





        // Cargar estadísticas básicas
        async function loadStats() {
            try {
                const response = await fetch(`${INCIDENCIAS_API}?action=stats`);
                if (!response.ok) throw new Error('Error al cargar estadísticas');
                
                const result = await response.json();
                if (result.stats) {
                    renderStats(result.stats);
                }
            } catch (error) {
                console.error('Error al cargar estadísticas:', error);
            }
        }

        function renderStats(stats) {
            let abiertas = 0, proceso = 0, cerradas = 0;
            
            if (stats.por_estado) {
                stats.por_estado.forEach(estado => {
                    switch(estado.estado) {
                        case 'abierta':
                            abiertas = estado.count;
                            break;
                        case 'en_proceso':
                            proceso = estado.count;
                            break;
                        case 'cerrada':
                            cerradas = estado.count;
                            break;
                    }
                });
            }

            statsSection.innerHTML = `
                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <div class="text-3xl font-bold text-red-600 mb-2">${abiertas}</div>
                    <div class="text-sm font-medium text-gray-600">Incidencias Abiertas</div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <div class="text-3xl font-bold text-yellow-600 mb-2">${proceso}</div>
                    <div class="text-sm font-medium text-gray-600">En Proceso</div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <div class="text-3xl font-bold text-green-600 mb-2">${cerradas}</div>
                    <div class="text-sm font-medium text-gray-600">Resueltas</div>
                </div>
            `;
        }

        // Toggle del formulario
        toggleFormBtn.addEventListener('click', () => {
            const isHidden = reportFormContainer.classList.contains('hidden');
            
            if (isHidden) {
                reportFormContainer.classList.remove('hidden');
                toggleText.textContent = '�O Cancelar Reporte';
                reportFormContainer.scrollIntoView({ behavior: 'smooth' });
            } else {
                reportFormContainer.classList.add('hidden');
                toggleText.textContent = '�Y"� Reportar Nueva Incidencia';
                hideMessage();
                incidentForm.reset();
            }
        });

        // Botón cancelar
        cancelBtn.addEventListener('click', () => {
            reportFormContainer.classList.add('hidden');
            toggleText.textContent = '�Y"� Reportar Nueva Incidencia';
            hideMessage();
            incidentForm.reset();
        });

        // Event listeners de navegación
        loginBtn.addEventListener('click', () => {
            window.location.href = 'views/login.php';
        });

        dashboardBtn.addEventListener('click', () => {
            if (currentUser && currentUser.rol === 'usuario') {
                window.location.href = 'views/dashboard_usuario.php';
            } else {
                window.location.href = 'views/dashboard.php';
            }
        });

        logoutBtn.addEventListener('click', () => {
            if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                clearUserSession();
                showToast('Sesión cerrada correctamente', 'success');
            }
        });

        // Manejo del formulario
                        incidentForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const formData = new FormData(incidentForm); // ESTA LÍNEA DEBE ESTAR AQUÍ
                    const data = {
                        titulo: formData.get('titulo').trim(),
                        descripcion: formData.get('descripcion').trim(),
                        nombre_reporta: formData.get('nombre_reporta').trim(),
                        email_reporta: formData.get('email_reporta').trim(),
                        id_tipo_incidencia: formData.get('tipo_incidencia') || null,
                        id_subtipo_incidencia: formData.get('subtipo_incidencia') || null,
                        prioridad: formData.get('prioridad') || 'media',
                        estado: 'abierta',
                        id_usuario_reporta: currentUser ? currentUser.id : null
                    };

            // Validaciones
            if (!data.titulo) {
                showMessage('El título es requerido');
                return;
            }
            if (!data.descripcion) {
                showMessage('La descripción es requerida');
                return;
            }
            if (!data.nombre_reporta) {
                showMessage('Tu nombre es requerido');
                return;
            }
            if (!data.email_reporta) {
                showMessage('Tu email es requerido');
                return;
            }

            // Mostrar loading
            submitText.textContent = '';
            submitLoading.classList.remove('hidden');
            submitBtn.disabled = true;
            hideMessage();

            try {
                const response = await fetch(INCIDENCIAS_API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showMessage('¡Incidencia reportada exitosamente! Te contactaremos pronto.', 'success');
                    incidentForm.reset();
                    showToast('Reporte enviado exitosamente', 'success');
                    
                    // Actualizar estadísticas
                    loadStats();
                    
                    // Ocultar formulario después de 3 segundos
                    setTimeout(() => {
                        reportFormContainer.classList.add('hidden');
                        toggleText.textContent = '�Y"� Reportar Nueva Incidencia';
                        hideMessage();
                    }, 3000);
                } else {
                    showMessage(result.message || 'Error al enviar el reporte. Inténtalo nuevamente.');
                }
            } catch (error) {
                console.error('Error al enviar reporte:', error);
                showMessage('Error de conexión. Por favor, inténtalo nuevamente.');
            } finally {
                // Ocultar loading
                submitText.textContent = 'Enviar Reporte';
                submitLoading.classList.remove('hidden');
                submitBtn.disabled = false;
            }
        });

        // Inicializar la aplicación
        async function init() {
            // Verificar sesión del usuario
            checkUserSession();
            
            // Cargar datos iniciales
            await Promise.all([
                loadTiposIncidencia(),
                loadStats()
            ]);
            
            // Si hay usuario logueado, pre-llenar algunos campos
            if (currentUser) {
                const nombreInput = document.getElementById('nombre_reporta');
                if (nombreInput && currentUser.nombre) {
                    nombreInput.value = currentUser.nombre;
                }
            }
        }

        // Inicializar cuando la página esté cargada
        window.addEventListener('load', init);

        // Verificar sesión periódicamente (cada 5 minutos)
        setInterval(checkUserSession, 5 * 60 * 1000);


        // Chat Assistant Logic
        

                        // ============================================
                // CHAT ASSISTANT FUNCTIONALITY
                // ============================================



                const CHAT_API = 'api/controllers/chat.php';


                // Elementos del chat
                const chatToggle = document.getElementById('chat-toggle');
                const chatContainer = document.getElementById('chat-container');
                const chatClose = document.getElementById('chat-close');
                const chatMessages = document.getElementById('chat-messages');
                const chatInput = document.getElementById('chat-input');
                const chatSend = document.getElementById('chat-send');

                let isTyping = false;

                // Función para mostrar/ocultar el chat
                function toggleChat() {
                    chatContainer.classList.toggle('hidden');
                    if (!chatContainer.classList.contains('hidden')) {
                        chatInput.focus();
                    }
                }

                // Función para cerrar el chat
                function closeChat() {
                    chatContainer.classList.add('hidden');
                }

                // Función para agregar mensaje al chat
                function addChatMessage(message, isUser = false) {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `flex items-start space-x-3 ${isUser ? 'justify-end' : ''} animate-fade-in-up`;
                    
                    // Estilos de burbujas
                    const userBubble = 'bg-teal-600 text-white rounded-2xl rounded-tr-none shadow-md';
                    const botBubble = 'bg-white border border-gray-100 text-gray-700 rounded-2xl rounded-tl-none shadow-sm';
                    
                    messageDiv.innerHTML = `
                        ${!isUser ? `<div class="w-8 h-8 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center flex-shrink-0 border border-teal-200">
                            <i class="fas fa-robot text-xs"></i>
                        </div>` : ''}
                        
                        <div class="${isUser ? userBubble : botBubble} p-4 max-w-[85%]">
                            <p class="text-sm leading-relaxed whitespace-pre-wrap">${message}</p>
                        </div>
                        
                        ${isUser ? `<div class="w-8 h-8 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-xs"></i>
                        </div>` : ''}
                    `;
                    
                    chatMessages.appendChild(messageDiv);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }

                // Función para mostrar indicador de escritura
                function showTypingIndicator() {
                    if (isTyping) return;
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

                // Función para ocultar indicador de escritura
                function hideTypingIndicator() {
                    const typingIndicator = document.getElementById('typing-indicator');
                    if (typingIndicator) {
                        typingIndicator.remove();
                    }
                    isTyping = false;
                }

                // Función para enviar mensaje
                async function sendChatMessage() {
                    const message = chatInput.value.trim();
                    if (!message) return;
                    
                    // Verificar si el usuario está logueado
                    if (!currentUser) {
                        addChatMessage('Debes iniciar sesión para usar el asistente.', false);
                        return;
                    }
                    
                    addChatMessage(message, true);
                    chatInput.value = '';
                    showTypingIndicator();
                    
                    try {
                        const userSession = localStorage.getItem('user_session') || sessionStorage.getItem('user_session');
                        
                        if (!userSession) {
                            hideTypingIndicator();
                            addChatMessage('Error: No hay sesión activa. Por favor inicia sesión.', false);
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
                            console.error('Respuesta recibida:', responseText);
                            addChatMessage('Error del servidor. Por favor, intenta más tarde.', false);
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

                // Event listeners del chat
                if (chatToggle) {
                    chatToggle.addEventListener('click', toggleChat);
                }

                if (chatClose) {
                    chatClose.addEventListener('click', closeChat);
                }

                if (chatSend) {
                    chatSend.addEventListener('click', sendChatMessage);
                }

                if (chatInput) {
                    chatInput.addEventListener('keypress', (e) => {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            sendChatMessage();
                        }
                    });
                }
    </script>
<?php include 'includes/footer.php'; ?>
