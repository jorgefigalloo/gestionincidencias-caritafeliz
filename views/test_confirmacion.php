<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test API - Confirmación</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #0D9488; }
        pre { background: #f0f0f0; padding: 15px; border-radius: 5px; overflow-x: auto; }
        button { background: #0D9488; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0F766E; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test API - Campos de Confirmación</h1>
        <p>Esta página te ayudará a verificar si la API está devolviendo los campos de confirmación correctamente.</p>
        
        <button onclick="testAPI()">🔍 Probar API</button>
        <button onclick="testEnhancedFunction()">🎨 Probar Función Enhanced</button>
        
        <h2>Resultado:</h2>
        <div id="result"></div>
        
        <h2>Datos Completos:</h2>
        <pre id="raw-data">Haz clic en "Probar API" para ver los datos...</pre>
    </div>

    <script src="../assets/js/dashboard-usuario-enhanced.js"></script>
    <script>
        async function testAPI() {
            const resultDiv = document.getElementById('result');
            const rawDataPre = document.getElementById('raw-data');
            
            try {
                resultDiv.innerHTML = '<p>⏳ Cargando datos de la API...</p>';
                
                // Obtener ID de usuario de la sesión
                const userSession = localStorage.getItem('user_session') || sessionStorage.getItem('user_session');
                if (!userSession) {
                    resultDiv.innerHTML = '<p class="error">❌ No hay sesión activa. Por favor inicia sesión primero.</p>';
                    return;
                }
                
                const currentUser = JSON.parse(userSession);
                const userId = currentUser.id_usuario || currentUser.id;
                
                // Llamar a la API
                const response = await fetch(`../api/controllers/incidencias.php?action=by_user&id_usuario=${userId}`);
                const data = await response.json();
                
                // Mostrar datos completos
                rawDataPre.textContent = JSON.stringify(data, null, 2);
                
                // Analizar resultados
                if (data.records && data.records.length > 0) {
                    let html = '<div class="success">✅ API respondió correctamente</div>';
                    html += `<p><strong>Total de incidencias:</strong> ${data.total}</p>`;
                    
                    // Buscar incidencias cerradas
                    const cerradas = data.records.filter(inc => inc.estado === 'cerrada');
                    html += `<p><strong>Incidencias cerradas:</strong> ${cerradas.length}</p>`;
                    
                    if (cerradas.length > 0) {
                        html += '<h3>Incidencias Cerradas:</h3><ul>';
                        cerradas.forEach(inc => {
                            html += `<li>
                                <strong>#${inc.id_incidencia} - ${inc.titulo}</strong><br>
                                Estado: ${inc.estado}<br>
                                Confirmación: ${inc.confirmacion_usuario || '❌ NO EXISTE'}<br>
                                Comentario: ${inc.comentario_usuario || 'N/A'}<br>
                                ${inc.confirmacion_usuario ? '✅ Campo existe' : '❌ Campo NO existe en la respuesta'}
                            </li>`;
                        });
                        html += '</ul>';
                    }
                    
                    // Verificar si existe el campo en al menos una incidencia
                    const tieneConfirmacion = data.records.some(inc => 'confirmacion_usuario' in inc);
                    if (tieneConfirmacion) {
                        html += '<p class="success">✅ El campo "confirmacion_usuario" está presente en la API</p>';
                    } else {
                        html += '<p class="error">❌ El campo "confirmacion_usuario" NO está en la respuesta de la API</p>';
                        html += '<p><strong>Solución:</strong> Necesitas actualizar el archivo <code>api/models/Incidencia.php</code></p>';
                    }
                    
                    resultDiv.innerHTML = html;
                } else {
                    resultDiv.innerHTML = '<p class="error">❌ No se encontraron incidencias</p>';
                }
                
            } catch (error) {
                resultDiv.innerHTML = `<p class="error">❌ Error: ${error.message}</p>`;
                rawDataPre.textContent = error.stack;
            }
        }
        
        function testEnhancedFunction() {
            const resultDiv = document.getElementById('result');
            
            if (typeof renderIncidentsEnhanced === 'function') {
                resultDiv.innerHTML = '<p class="success">✅ La función renderIncidentsEnhanced está cargada correctamente</p>';
            } else {
                resultDiv.innerHTML = '<p class="error">❌ La función renderIncidentsEnhanced NO está disponible</p>' +
                    '<p><strong>Solución:</strong> Verifica que el archivo <code>dashboard-usuario-enhanced.js</code> esté cargando correctamente</p>';
            }
        }
    </script>
</body>
</html>
