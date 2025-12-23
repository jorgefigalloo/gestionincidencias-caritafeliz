<?php
session_start();

// Verificar autenticación y rol de administrador
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración SMTP - Clínica Carita Feliz</title>
    <link rel="icon" href="../assets/images/logo.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php include '../includes/header.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-6 text-sm">
            <a href="dashboard.php" class="text-teal-600 hover:text-teal-700">Dashboard</a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-600">Configuración SMTP</span>
        </nav>

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                <i class="fas fa-envelope-open-text text-teal-600 mr-3"></i>
                Configuración de Email (SMTP)
            </h1>
            <p class="text-gray-600">Configura el servidor SMTP para el envío de notificaciones por email</p>
        </div>

        <!-- Mensajes -->
        <div id="message-container" class="hidden mb-6"></div>

        <!-- Formulario de Configuración -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form id="smtp-form">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Servidor SMTP -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-server text-teal-600 mr-2"></i>Servidor SMTP
                        </label>
                        <input type="text" id="smtp_host" name="smtp_host" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            placeholder="smtp.gmail.com">
                        <small class="text-gray-500">Ejemplo: smtp.gmail.com, smtp.office365.com</small>
                    </div>

                    <!-- Puerto -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-network-wired text-teal-600 mr-2"></i>Puerto
                        </label>
                        <input type="number" id="smtp_port" name="smtp_port" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            placeholder="587">
                        <small class="text-gray-500">TLS: 587, SSL: 465</small>
                    </div>

                    <!-- Usuario SMTP -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user text-teal-600 mr-2"></i>Usuario SMTP
                        </label>
                        <input type="email" id="smtp_usuario" name="smtp_usuario" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            placeholder="tu-email@gmail.com">
                        <small class="text-gray-500">Email que se usará para autenticación</small>
                    </div>

                    <!-- Contraseña SMTP -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-key text-teal-600 mr-2"></i>Contraseña SMTP
                        </label>
                        <div class="relative">
                            <input type="password" id="smtp_password" name="smtp_password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 pr-10"
                                placeholder="••••••••">
                            <button type="button" id="toggle-password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-gray-500">Para Gmail, usa "Contraseña de aplicación"</small>
                    </div>

                    <!-- Email Remitente -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-at text-teal-600 mr-2"></i>Email Remitente
                        </label>
                        <input type="email" id="email_remitente" name="email_remitente" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            placeholder="sistemas@clinicacaritafeliz.com">
                        <small class="text-gray-500">Email que aparecerá como remitente</small>
                    </div>

                    <!-- Nombre Remitente -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-signature text-teal-600 mr-2"></i>Nombre Remitente
                        </label>
                        <input type="text" id="nombre_remitente" name="nombre_remitente" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                            placeholder="Clínica Carita Feliz - Soporte TI">
                    </div>

                    <!-- Seguridad -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-shield-alt text-teal-600 mr-2"></i>Tipo de Seguridad
                        </label>
                        <select id="smtp_seguridad" name="smtp_seguridad"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                            <option value="tls">TLS (Recomendado)</option>
                            <option value="ssl">SSL</option>
                            <option value="none">Ninguna</option>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-toggle-on text-teal-600 mr-2"></i>Estado
                        </label>
                        <select id="activo" name="activo"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <!-- Botones -->
                <div class="mt-8 flex flex-col sm:flex-row gap-4">
                    <button type="submit" id="save-btn"
                        class="flex-1 bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>
                        <span id="save-text">Guardar Configuración</span>
                        <div id="save-loading" class="hidden ml-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </button>
                    
                    <button type="button" id="test-btn"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Enviar Email de Prueba
                    </button>
                </div>
            </form>
        </div>

        <!-- Guía de Configuración -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg">
            <h3 class="text-lg font-bold text-blue-900 mb-3">
                <i class="fas fa-info-circle mr-2"></i>Guía de Configuración para Gmail
            </h3>
            <ol class="list-decimal list-inside space-y-2 text-blue-800">
                <li>Activa la <strong>verificación en 2 pasos</strong> en tu cuenta de Gmail</li>
                <li>Ve a <a href="https://myaccount.google.com/apppasswords" target="_blank" class="underline hover:text-blue-600">https://myaccount.google.com/apppasswords</a></li>
                <li>Genera una <strong>"Contraseña de aplicación"</strong></li>
                <li>Usa esa contraseña (no tu contraseña normal) en el campo "Contraseña SMTP"</li>
                <li>Servidor: <code class="bg-blue-100 px-2 py-1 rounded">smtp.gmail.com</code></li>
                <li>Puerto: <code class="bg-blue-100 px-2 py-1 rounded">587</code> (TLS)</li>
            </ol>
        </div>
    </div>

    <script>
        const API_URL = '../api/controllers/configuracion_email.php';

        // Cargar configuración actual
        async function loadConfig() {
            try {
                const response = await fetch(`${API_URL}?action=get`);
                const result = await response.json();
                
                if (result.success) {
                    const config = result.config;
                    document.getElementById('smtp_host').value = config.smtp_host;
                    document.getElementById('smtp_port').value = config.smtp_port;
                    document.getElementById('smtp_usuario').value = config.smtp_usuario;
                    document.getElementById('smtp_password').value = config.smtp_password;
                    document.getElementById('email_remitente').value = config.email_remitente;
                    document.getElementById('nombre_remitente').value = config.nombre_remitente;
                    document.getElementById('smtp_seguridad').value = config.smtp_seguridad;
                    document.getElementById('activo').value = config.activo;
                }
            } catch (error) {
                console.error('Error al cargar configuración:', error);
            }
        }

        // Guardar configuración
        document.getElementById('smtp-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const saveBtn = document.getElementById('save-btn');
            const saveText = document.getElementById('save-text');
            const saveLoading = document.getElementById('save-loading');
            
            saveBtn.disabled = true;
            saveText.textContent = 'Guardando...';
            saveLoading.classList.remove('hidden');
            
            const formData = {
                smtp_host: document.getElementById('smtp_host').value,
                smtp_port: document.getElementById('smtp_port').value,
                smtp_usuario: document.getElementById('smtp_usuario').value,
                smtp_password: document.getElementById('smtp_password').value,
                email_remitente: document.getElementById('email_remitente').value,
                nombre_remitente: document.getElementById('nombre_remitente').value,
                smtp_seguridad: document.getElementById('smtp_seguridad').value,
                activo: document.getElementById('activo').value
            };
            
            try {
                const response = await fetch(`${API_URL}?action=update`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message, 'success');
                } else {
                    showMessage(result.message, 'error');
                }
            } catch (error) {
                showMessage('Error al guardar configuración', 'error');
            } finally {
                saveBtn.disabled = false;
                saveText.textContent = 'Guardar Configuración';
                saveLoading.classList.add('hidden');
            }
        });

        // Enviar email de prueba
        document.getElementById('test-btn').addEventListener('click', async () => {
            const email = prompt('Ingresa el email de destino para la prueba:', document.getElementById('email_remitente').value);
            
            if (!email) return;
            
            try {
                const response = await fetch(`${API_URL}?action=test`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ email_destino: email })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage(result.message, 'success');
                } else {
                    showMessage(result.message, 'error');
                }
            } catch (error) {
                showMessage('Error al enviar email de prueba', 'error');
            }
        });

        // Toggle password visibility
        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('smtp_password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Mostrar mensajes
        function showMessage(message, type = 'success') {
            const container = document.getElementById('message-container');
            const bgColor = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            container.className = `flex items-center p-4 mb-6 border-l-4 rounded ${bgColor}`;
            container.innerHTML = `
                <i class="fas ${icon} mr-3 text-xl"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.classList.add('hidden')" class="ml-auto">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.classList.remove('hidden');
            
            setTimeout(() => container.classList.add('hidden'), 5000);
        }

        // Cargar configuración al iniciar
        loadConfig();
    </script>
</body>
</html>
