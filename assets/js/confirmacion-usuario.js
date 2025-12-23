// assets/js/confirmacion-usuario.js
// Componente para confirmación de solución de incidencias por parte del usuario

class ConfirmacionIncidencia {
    constructor() {
        this.API_URL = '../api/controllers/incidencias.php';
        this.init();
    }

    init() {
        // Agregar event listeners a los botones de confirmación
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-confirmar-solucion')) {
                const btn = e.target.closest('.btn-confirmar-solucion');
                const idIncidencia = btn.dataset.idIncidencia;
                this.mostrarModalConfirmacion(idIncidencia);
            }
        });
    }

    /**
     * Mostrar modal de confirmación
     */
    mostrarModalConfirmacion(idIncidencia) {
        const modal = this.crearModal(idIncidencia);
        document.body.appendChild(modal);

        // Mostrar modal con animación
        setTimeout(() => modal.classList.remove('opacity-0'), 10);
    }

    /**
     * Crear el HTML del modal
     */
    crearModal(idIncidencia) {
        const modal = document.createElement('div');
        modal.id = 'modal-confirmacion';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 opacity-0 transition-opacity duration-300';

        modal.innerHTML = `
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95">
                <!-- Header -->
                <div class="bg-gradient-to-r from-teal-500 to-teal-600 text-white p-6 rounded-t-2xl">
                    <h3 class="text-xl font-bold flex items-center">
                        <i class="fas fa-check-circle mr-3"></i>
                        Confirmar Solución
                    </h3>
                    <p class="text-teal-100 text-sm mt-1">Incidencia #${idIncidencia}</p>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <p class="text-gray-700 mb-6">
                        ¿El problema de tu incidencia ha sido resuelto satisfactoriamente?
                    </p>

                    <!-- Opciones -->
                    <div class="space-y-3 mb-6">
                        <button onclick="confirmacionIncidencia.seleccionarOpcion('solucionado')" 
                            class="w-full p-4 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:bg-green-50 transition duration-200 text-left opcion-confirmacion"
                            data-opcion="solucionado">
                            <div class="flex items-center">
                                <div class="w-6 h-6 rounded-full border-2 border-gray-300 mr-3 flex items-center justify-center radio-custom">
                                    <div class="w-3 h-3 rounded-full bg-green-500 hidden check-mark"></div>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">✅ Sí, está solucionado</div>
                                    <div class="text-sm text-gray-500">El problema fue resuelto completamente</div>
                                </div>
                            </div>
                        </button>

                        <button onclick="confirmacionIncidencia.seleccionarOpcion('no_solucionado')" 
                            class="w-full p-4 border-2 border-gray-200 rounded-xl hover:border-red-500 hover:bg-red-50 transition duration-200 text-left opcion-confirmacion"
                            data-opcion="no_solucionado">
                            <div class="flex items-center">
                                <div class="w-6 h-6 rounded-full border-2 border-gray-300 mr-3 flex items-center justify-center radio-custom">
                                    <div class="w-3 h-3 rounded-full bg-red-500 hidden check-mark"></div>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">❌ No, aún persiste</div>
                                    <div class="text-sm text-gray-500">El problema no está completamente resuelto</div>
                                </div>
                            </div>
                        </button>
                    </div>

                    <!-- Comentario opcional -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Comentario adicional (opcional)
                        </label>
                        <textarea id="comentario-usuario" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            placeholder="Describe tu experiencia o cualquier detalle adicional..."></textarea>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-3">
                        <button onclick="confirmacionIncidencia.cerrarModal()" 
                            class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition duration-200">
                            Cancelar
                        </button>
                        <button id="btn-enviar-confirmacion" onclick="confirmacionIncidencia.enviarConfirmacion(${idIncidencia})" 
                            class="flex-1 px-4 py-3 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                            <span id="text-enviar">Enviar</span>
                            <div id="loading-enviar" class="hidden inline-block ml-2">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Event listener para cerrar al hacer clic fuera
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.cerrarModal();
            }
        });

        return modal;
    }

    /**
     * Seleccionar opción de confirmación
     */
    seleccionarOpcion(opcion) {
        // Remover selección anterior
        document.querySelectorAll('.opcion-confirmacion').forEach(btn => {
            btn.classList.remove('border-green-500', 'bg-green-50', 'border-red-500', 'bg-red-50');
            btn.querySelector('.check-mark').classList.add('hidden');
        });

        // Marcar nueva selección
        const btnSeleccionado = document.querySelector(`[data-opcion="${opcion}"]`);
        if (opcion === 'solucionado') {
            btnSeleccionado.classList.add('border-green-500', 'bg-green-50');
        } else {
            btnSeleccionado.classList.add('border-red-500', 'bg-red-50');
        }
        btnSeleccionado.querySelector('.check-mark').classList.remove('hidden');

        // Habilitar botón de enviar
        document.getElementById('btn-enviar-confirmacion').disabled = false;

        // Guardar opción seleccionada
        this.opcionSeleccionada = opcion;
    }

    /**
     * Enviar confirmación al servidor
     */
    async enviarConfirmacion(idIncidencia) {
        if (!this.opcionSeleccionada) {
            alert('Por favor selecciona una opción');
            return;
        }

        const btnEnviar = document.getElementById('btn-enviar-confirmacion');
        const textEnviar = document.getElementById('text-enviar');
        const loadingEnviar = document.getElementById('loading-enviar');
        const comentario = document.getElementById('comentario-usuario').value.trim();

        // Mostrar loading
        btnEnviar.disabled = true;
        textEnviar.textContent = 'Enviando...';
        loadingEnviar.classList.remove('hidden');

        try {
            const response = await fetch(`${this.API_URL}?action=confirmar_solucion`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_incidencia: idIncidencia,
                    confirmacion: this.opcionSeleccionada,
                    comentario_usuario: comentario || null
                })
            });

            const result = await response.json();

            if (result.success) {
                this.mostrarMensajeExito(this.opcionSeleccionada);
                setTimeout(() => {
                    this.cerrarModal();
                    // Recargar la página para actualizar el estado
                    window.location.reload();
                }, 2000);
            } else {
                alert('Error: ' + result.message);
                btnEnviar.disabled = false;
                textEnviar.textContent = 'Enviar';
                loadingEnviar.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error al enviar confirmación:', error);
            alert('Error de conexión. Por favor intenta nuevamente.');
            btnEnviar.disabled = false;
            textEnviar.textContent = 'Enviar';
            loadingEnviar.classList.add('hidden');
        }
    }

    /**
     * Mostrar mensaje de éxito
     */
    mostrarMensajeExito(opcion) {
        const modal = document.getElementById('modal-confirmacion');
        const mensaje = opcion === 'solucionado'
            ? '✅ ¡Gracias por confirmar! El técnico ha sido notificado.'
            : '⚠️ Hemos notificado al técnico que el problema persiste.';

        modal.querySelector('.bg-white').innerHTML = `
            <div class="p-8 text-center">
                <div class="text-6xl mb-4">${opcion === 'solucionado' ? '✅' : '⚠️'}</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Confirmación Enviada</h3>
                <p class="text-gray-600">${mensaje}</p>
            </div>
        `;
    }

    /**
     * Cerrar modal
     */
    cerrarModal() {
        const modal = document.getElementById('modal-confirmacion');
        if (modal) {
            modal.classList.add('opacity-0');
            setTimeout(() => modal.remove(), 300);
        }
        this.opcionSeleccionada = null;
    }
}

// Inicializar globalmente
const confirmacionIncidencia = new ConfirmacionIncidencia();
