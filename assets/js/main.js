// Configuración API
const INCIDENCIAS_API = 'api/controllers/incidencias.php';
const TIPOS_API = 'api/controllers/tipos_incidencias.php';
const CHAT_API = 'api/controllers/chat.php';

// Estado Global
let currentUser = null;

// Elementos DOM
const loginBtn = document.getElementById('login-btn');
const guestButtons = document.getElementById('guest-buttons');
const userSection = document.getElementById('user-section');
const userNameSpan = document.getElementById('user-name');
const userRoleSpan = document.getElementById('user-role');
const dashboardBtn = document.getElementById('dashboard-btn');
const logoutBtn = document.getElementById('logout-btn');

const toggleFormBtn = document.getElementById('toggle-form-btn');
const reportFormContainer = document.getElementById('report-form-container');
const toggleText = document.getElementById('toggle-text');
const incidentForm = document.getElementById('incident-form');
const cancelBtn = document.getElementById('cancel-btn');
const submitBtn = document.getElementById('submit-btn');
const submitText = document.getElementById('submit-text');
const submitLoading = document.getElementById('submit-loading');
const messageContainer = document.getElementById('message-container');
const statsSection = document.getElementById('stats-section');
const toastContainer = document.getElementById('toast-container');

// Chat Elements
const chatToggle = document.getElementById('chat-toggle');
const chatContainer = document.getElementById('chat-container');
const chatClose = document.getElementById('chat-close');
const chatMessages = document.getElementById('chat-messages');
const chatInput = document.getElementById('chat-input');
const chatSend = document.getElementById('chat-send');

// Funciones de UI
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };
    const icon = type === 'success' ? 'fa-check-circle' :
        type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';

    toast.className = `${colors[type] || colors.info} text-white px-6 py-3 rounded-xl shadow-2xl transform transition-all duration-300 translate-y-[-100%] z-50 flex items-center space-x-3 mb-3`;
    toast.innerHTML = `<i class="fas ${icon}"></i><span class="font-medium">${message}</span>`;

    toastContainer.appendChild(toast);

    requestAnimationFrame(() => toast.classList.remove('translate-y-[-100%]'));

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-[-100%]');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function showMessage(message, type = 'error') {
    messageContainer.classList.remove('hidden');
    messageContainer.className = `mb-6 p-4 rounded-xl border ${type === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-green-50 border-green-200 text-green-700'
        }`;
    messageContainer.innerHTML = `
        <div class="flex items-center space-x-3">
            <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'} text-xl"></i>
            <p class="font-medium">${message}</p>
        </div>
    `;
}

function hideMessage() {
    messageContainer.classList.add('hidden');
}

// Gestión de Sesión
function checkUserSession() {
    const session = localStorage.getItem('user_session') || sessionStorage.getItem('user_session');
    if (session) {
        try {
            // Intentar parsear si es un objeto JSON almacenado como string
            if (session.startsWith('{')) {
                currentUser = JSON.parse(session);
            } else {
                // Si es un token, decodificar payload (asumiendo JWT simple)
                const payload = JSON.parse(atob(session.split('.')[1]));
                currentUser = payload.data || payload;
            }
            updateAuthUI(true);
        } catch (e) {
            console.error('Error de sesión:', e);
            // No limpiar sesión automáticamente para evitar bucles si el formato es diferente
            // pero marcar como no logueado
            currentUser = null;
            updateAuthUI(false);
        }
    } else {
        updateAuthUI(false);
    }
}

const reportBtnContainer = document.getElementById('report-btn-container');

function updateAuthUI(isLoggedIn) {
    if (isLoggedIn && currentUser) {
        guestButtons.classList.add('hidden');
        userSection.classList.remove('hidden');
        if (userNameSpan) userNameSpan.textContent = currentUser.nombre || 'Usuario';
        if (userRoleSpan) userRoleSpan.textContent = currentUser.rol || 'Rol';
        if (reportBtnContainer) reportBtnContainer.classList.remove('hidden');
    } else {
        guestButtons.classList.remove('hidden');
        userSection.classList.add('hidden');
        currentUser = null;
        if (reportBtnContainer) reportBtnContainer.classList.add('hidden');
    }
}

function clearUserSession() {
    localStorage.removeItem('user_session');
    sessionStorage.removeItem('user_session');
    currentUser = null;
    updateAuthUI(false);
    window.location.reload();
}

// Carga de Datos
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

        tipoSelect.addEventListener('change', loadSubtiposByTipo);
    } catch (error) {
        console.error('Error al cargar tipos:', error);
    }
}

async function loadSubtiposByTipo() {
    const tipoSelect = document.getElementById('tipo_incidencia');
    const subtipoSelect = document.getElementById('subtipo_incidencia');
    const subtipoContainer = document.getElementById('subtipo-container');

    const tipoId = tipoSelect.value;

    if (!tipoId) {
        subtipoContainer.classList.add('hidden');
        subtipoSelect.innerHTML = '<option value="">Selecciona primero un tipo...</option>';
        return;
    }

    try {
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
            switch (estado.estado) {
                case 'abierta': abiertas = estado.count; break;
                case 'en_proceso': proceso = estado.count; break;
                case 'cerrada': cerradas = estado.count; break;
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

// Event Listeners
if (toggleFormBtn) {
    toggleFormBtn.addEventListener('click', () => {
        const isHidden = reportFormContainer.classList.contains('hidden');
        if (isHidden) {
            reportFormContainer.classList.remove('hidden');
            toggleText.textContent = '❌ Cancelar Reporte';
            reportFormContainer.scrollIntoView({ behavior: 'smooth' });
        } else {
            reportFormContainer.classList.add('hidden');
            toggleText.textContent = '📝 Reportar Nueva Incidencia';
            hideMessage();
            incidentForm.reset();
        }
    });
}

if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
        reportFormContainer.classList.add('hidden');
        toggleText.textContent = '📝 Reportar Nueva Incidencia';
        hideMessage();
        incidentForm.reset();
    });
}

if (loginBtn) {
    loginBtn.addEventListener('click', () => {
        window.location.href = 'views/login.php';
    });
}

if (dashboardBtn) {
    dashboardBtn.addEventListener('click', () => {
        if (currentUser && currentUser.rol === 'usuario') {
            window.location.href = 'views/dashboard_usuario.php';
        } else {
            window.location.href = 'views/dashboard.php';
        }
    });
}

if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
        if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
            clearUserSession();
            showToast('Sesión cerrada correctamente', 'success');
        }
    });
}

// Formulario
if (incidentForm) {
    incidentForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(incidentForm);
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

        if (!data.titulo || !data.descripcion || !data.nombre_reporta || !data.email_reporta) {
            showMessage('Por favor completa todos los campos requeridos');
            return;
        }

        submitText.textContent = '';
        submitLoading.classList.remove('hidden');
        submitBtn.disabled = true;
        hideMessage();

        try {
            const response = await fetch(INCIDENCIAS_API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                showMessage('¡Incidencia reportada exitosamente! Te contactaremos pronto.', 'success');
                incidentForm.reset();
                showToast('Reporte enviado exitosamente', 'success');
                loadStats();
                setTimeout(() => {
                    reportFormContainer.classList.add('hidden');
                    toggleText.textContent = '📝 Reportar Nueva Incidencia';
                    hideMessage();
                }, 3000);
            } else {
                showMessage(result.message || 'Error al enviar el reporte.');
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('Error de conexión.');
        } finally {
            submitText.textContent = 'Enviar Reporte';
            submitLoading.classList.add('hidden');
            submitBtn.disabled = false;
        }
    });
}

// Chat Logic
let isTyping = false;

function toggleChat() {
    chatContainer.classList.toggle('hidden');
    if (!chatContainer.classList.contains('hidden')) chatInput.focus();
}

function closeChat() {
    chatContainer.classList.add('hidden');
}

function addChatMessage(message, isUser = false) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex items-start space-x-3 ${isUser ? 'justify-end' : ''} animate-fade-in-up`;

    const userBubble = 'bg-teal-600 text-white rounded-2xl rounded-tr-none shadow-md';
    const botBubble = 'bg-white border border-gray-100 text-gray-700 rounded-2xl rounded-tl-none shadow-sm';

    messageDiv.innerHTML = `
        ${!isUser ? `<div class="w-8 h-8 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center flex-shrink-0 border border-teal-200"><i class="fas fa-robot text-xs"></i></div>` : ''}
        <div class="${isUser ? userBubble : botBubble} p-4 max-w-[85%]"><p class="text-sm leading-relaxed whitespace-pre-wrap">${message}</p></div>
        ${isUser ? `<div class="w-8 h-8 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center flex-shrink-0"><i class="fas fa-user text-xs"></i></div>` : ''}
    `;

    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function showTypingIndicator() {
    if (isTyping) return;
    isTyping = true;
    const typingDiv = document.createElement('div');
    typingDiv.id = 'typing-indicator';
    typingDiv.className = 'flex items-start space-x-2';
    typingDiv.innerHTML = `
        <div class="bg-gray-100 text-gray-600 p-2 rounded-full"><i class="fas fa-robot text-sm"></i></div>
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
    const indicator = document.getElementById('typing-indicator');
    if (indicator) indicator.remove();
    isTyping = false;
}

async function sendChatMessage() {
    const message = chatInput.value.trim();
    if (!message) return;

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
            addChatMessage('Error: No hay sesión activa.', false);
            return;
        }

        const token = btoa(userSession);
        const response = await fetch(CHAT_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify({ message })
        });

        const result = await response.json();
        hideTypingIndicator();

        if (result.success) {
            addChatMessage(result.reply, false);
        } else {
            addChatMessage(result.message || 'Error al procesar mensaje.', false);
        }
    } catch (error) {
        hideTypingIndicator();
        console.error('Error chat:', error);
        addChatMessage('Error de conexión.', false);
    }
}

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

// Inicialización
async function init() {
    checkUserSession();
    await Promise.all([loadTiposIncidencia(), loadStats()]);

    if (currentUser && document.getElementById('nombre_reporta')) {
        document.getElementById('nombre_reporta').value = currentUser.nombre || '';
    }
}

window.addEventListener('load', init);
setInterval(checkUserSession, 5 * 60 * 1000);
