<?php
// api/models/LocalChat.php

class LocalChat {
    private $pdo;

    public function __construct($pdo = null) {
        $this->pdo = $pdo;
    }

    public function processMessage($message, $context = []) {
        $msg = mb_strtolower(trim($message));
        
        // 1. Saludos y Cortesía
        if ($this->isGreeting($msg)) return $this->getGreetingResponse($context);
        if ($this->isPolite($msg)) return $this->getPoliteResponse($msg);

        // 2. Contexto de Usuario
        if ($this->matchesFuzzy($msg, ['quien soy', 'mi nombre', 'mi rol', 'mi usuario', 'mis datos'])) {
            return $this->getUserContextResponse($context);
        }

        // 3. Consultas de Base de Datos
        if ($this->pdo) {
            // Estadísticas
            if ($this->matchesFuzzy($msg, ['cuantas', 'cantidad', 'estadisticas', 'reportes', 'total', 'resumen'])) {
                return $this->getGlobalStats();
            }
            // Listar por Estado
            $status = $this->detectStatus($msg);
            if ($status) return $this->getTicketsByStatus($status);
            // Mis Tickets
            if ($this->matchesFuzzy($msg, ['mis tickets', 'mis incidencias', 'mis casos', 'mios', 'listar mis'])) {
                return $this->getMyTickets($context['user']['id'] ?? null);
            }
            // Ticket Específico
            if (preg_match('/(ticket|incidencia|caso)\s*#?(\d+)/', $msg, $matches)) {
                return $this->getTicketStatus($matches[2]);
            }
        }

        // 4. Guía de Acciones
        if ($this->matchesFuzzy($msg, ['como crear', 'nuevo ticket', 'reportar', 'subir incidencia', 'cerrar ticket'])) {
            return $this->getActionGuide($msg);
        }

        // 5. Respuestas Específicas (Preguntas puntuales)
        $specific = $this->getSpecificResponse($msg);
        if ($specific) {
            return $specific;
        }

        // 6. Base de Conocimiento "Inteligente" (Ponderada)
        $topic = $this->detectTopicWeighted($msg);
        if ($topic) {
            return $this->getKnowledgeResponse($topic);
        }

        // 7. Fallback
        return [
            'success' => true,
            'message' => "Entiendo que me hablas de '$message', pero necesito ser más preciso.\n\nPrueba con:\n🔹 \"Mi PC está lenta\"\n🔹 \"No abre el Excel\"\n🔹 \"No tengo internet\"\n🔹 \"Mis tickets\""
        ];
    }

    // --- Respuestas Específicas (Q&A) ---
    private function getSpecificResponse($text) {
        // Modo Seguro
        if ($this->matchesFuzzy($text, ['modo seguro', 'safe mode'])) {
            return ['success' => true, 'message' => "🛡️ **Cómo activar Modo Seguro en Office:**\n\n1. Mantén presionada la tecla **CTRL** en tu teclado.\n2. Sin soltarla, haz doble clic en el icono de Word o Excel.\n3. Te preguntará si quieres abrir en modo seguro, di que **SÍ**."];
        }
        
        // Reiniciar Router
        if ($this->matchesFuzzy($text, ['reiniciar router', 'apagar router', 'resetear router'])) {
            return ['success' => true, 'message' => "🌐 **Cómo reiniciar el router:**\n\n1. Desconecta el cable de corriente del router.\n2. Espera **10 segundos** (cuenta despacio).\n3. Vuelve a conectarlo y espera 2 minutos a que las luces se estabilicen."];
        }

        // Conversacional
        if ($this->matchesFuzzy($text, ['como estas', 'que tal estas', 'como te va'])) {
            return ['success' => true, 'message' => "🤖 Estoy funcionando al 100%, listo para ayudarte con tus problemas informáticos. ¿Tú qué tal?"];
        }

        // Limpieza y Temporales
        if ($this->matchesFuzzy($text, ['temporales', 'borrar temporales', 'borro temporales', 'eliminar temporales', 'disco lleno', 'sin espacio', 'liberar espacio', 'limpiar pc', 'basura'])) {
            return ['success' => true, 'message' => "🧹 **Cómo liberar espacio y borrar temporales:**\n\n1. Presiona las teclas **Windows + R**.\n2. Escribe `%temp%` y pulsa Enter.\n3. Borra todos los archivos de esa carpeta (son basura).\n4. Vacía la **Papelera de Reciclaje**."];
        }

        // Quién te creó
        if ($this->matchesFuzzy($text, ['quien te creo', 'quien te hizo', 'tu creador'])) {
            return ['success' => true, 'message' => "Fui desarrollado por el equipo de TI para ayudarte a resolver incidencias rápidamente."];
        }

        return null;
    }

    // --- Lógica de Coincidencia Difusa ---
    private function matchesFuzzy($text, $keywords) {
        foreach ($keywords as $word) {
            if (strpos($text, $word) !== false) return true;
            if (strlen($word) > 4) {
                $wordsInText = explode(' ', $text);
                foreach ($wordsInText as $w) {
                    if (levenshtein($w, $word) <= 1) return true;
                }
            }
        }
        return false;
    }

    // --- Detección de Tema Ponderada (Weighted Scoring) ---
    private function detectTopicWeighted($text) {
        // Definición de Temas con Palabras Clave y Pesos
        // Peso 5: Específico / Producto
        // Peso 3: Categoría
        // Peso 1: Síntoma Genérico
        $topics = [
            'software_office' => [
                'excel' => 5, 'word' => 5, 'powerpoint' => 5, 'outlook' => 5, 'office' => 4, 
                'licencia' => 3, 'activar' => 3, 'correo' => 3, 'archivo' => 2
            ],
            'software_general' => [
                'sap' => 5, 'erp' => 5, 'teams' => 5, 'zoom' => 5, 'antivirus' => 5, 'virus' => 4,
                'programa' => 3, 'aplicacion' => 3, 'sistema' => 2, 'instalar' => 3
            ],
            'hardware_pc' => [
                'pc' => 5, 'computadora' => 5, 'ordenador' => 5, 'laptop' => 5, 'portatil' => 5, 
                'pantalla' => 4, 'monitor' => 4, 'encender' => 3, 'apagado' => 3, 'azul' => 2
            ],
            'hardware_perifericos' => [
                'mouse' => 5, 'raton' => 5, 'teclado' => 5, 'auriculares' => 5, 'microfono' => 5, 
                'camara' => 5, 'webcam' => 5, 'usb' => 4, 'cable' => 3
            ],
            'red_internet' => [
                'internet' => 5, 'wifi' => 5, 'wi-fi' => 5, 'red' => 4, 'conexion' => 4, 
                'navegar' => 3, 'offline' => 3, 'desconectado' => 3, 'lento' => 1 // Lento tiene poco peso aquí si no se dice "internet"
            ],
            'red_vpn' => [
                'vpn' => 5, 'forticlient' => 5, 'cisco' => 5, 'remoto' => 4, 'teletrabajo' => 4, 
                'conectar' => 2
            ],
            'red_carpetas' => [
                'carpeta' => 5, 'compartida' => 5, 'disco' => 4, 'unidad' => 4, 'acceso' => 3, 
                'permiso' => 3, 'archivos' => 2
            ],
            'impresion' => [
                'impresora' => 5, 'imprimir' => 5, 'escaner' => 5, 'toner' => 5, 'papel' => 4, 
                'atasco' => 4, 'tinta' => 4, 'copiadora' => 4
            ],
            'cuentas' => [
                'contraseña' => 5, 'clave' => 5, 'password' => 5, 'bloqueado' => 5, 'desbloquear' => 5, 
                'usuario' => 4, 'login' => 4, 'entrar' => 3, 'acceder' => 3
            ],
            'mantenimiento' => [
                'lenta' => 5, 'lento' => 5, 'rapida' => 4, 'optimizar' => 5, 'limpiar' => 4, 
                'basura' => 4, 'ccleaner' => 4, 'temporales' => 4, 'cookies' => 3
            ]
        ];

        // Palabras de contexto que modifican el score
        // Si el usuario dice "lento", suma puntos a categorías donde "lento" es común
        $symptoms = [
            'lento' => ['hardware_pc' => 2, 'red_internet' => 2, 'software_general' => 1],
            'no abre' => ['software_office' => 2, 'software_general' => 2],
            'no funciona' => ['hardware_perifericos' => 2, 'hardware_pc' => 1],
            'error' => ['software_general' => 1, 'cuentas' => 1]
        ];

        $bestTopic = null;
        $maxScore = 0;

        foreach ($topics as $topic => $keywords) {
            $score = 0;
            
            // 1. Calcular score por palabras clave del tema
            foreach ($keywords as $word => $weight) {
                // Exacta
                if (strpos($text, $word) !== false) {
                    $score += $weight;
                } 
                // Aproximada (solo para palabras largas y peso alto)
                elseif (strlen($word) > 4 && $weight >= 4) {
                    $wordsInText = explode(' ', $text);
                    foreach ($wordsInText as $w) {
                        if (levenshtein($w, $word) <= 1) {
                            $score += $weight - 1; // Penaliza un poco por error
                        }
                    }
                }
            }

            // 2. Sumar score por síntomas
            foreach ($symptoms as $symptom => $affectedTopics) {
                if (strpos($text, $symptom) !== false) {
                    if (isset($affectedTopics[$topic])) {
                        $score += $affectedTopics[$topic];
                    }
                }
            }

            if ($score > $maxScore) {
                $maxScore = $score;
                $bestTopic = $topic;
            }
        }

        // Umbral mínimo para evitar falsos positivos débiles
        return $maxScore >= 3 ? $bestTopic : null;
    }

    // --- Respuestas de Base de Conocimiento ---
    private function getKnowledgeResponse($topic) {
        $responses = [
            'software_office' => "💾 **Problemas de Office/Correo**\n1. **Outlook:** Si no envía, verifica tu internet. Si está lleno, archiva correos antiguos.\n2. **Excel/Word:** Si se traba, intenta abrir en 'Modo Seguro' o repara la instalación desde Panel de Control.\n3. **Licencias:** Si pide activación, conecta a la VPN y reinicia.",
            
            'software_general' => "💿 **Software y Aplicaciones**\n1. **SAP/ERP:** Si tienes error de conexión, verifica tu VPN.\n2. **Antivirus:** Si detecta algo, no lo abras y avisa a seguridad.\n3. **Lentitud:** Cierra programas pesados (Chrome, Teams) si no los usas.",
            
            'hardware_pc' => "💻 **Problemas de PC/Laptop**\n1. **Lentitud General:** Reinicia el equipo (soluciona memoria caché).\n2. **Pantalla Negra:** Verifica cables de energía y video.\n3. **No enciende:** Revisa si el enchufe tiene corriente.\n\nSi huele a quemado o hace ruidos raros, apaga y reporta urgente.",
            
            'hardware_perifericos' => "🖱️ **Periféricos (Mouse/Teclado)**\n1. Desconecta y vuelve a conectar el USB.\n2. Prueba en otro puerto USB diferente.\n3. Si es inalámbrico, revisa las pilas.\n4. Prueba el dispositivo en otra PC para descartar fallo físico.",
            
            'red_internet' => "🌐 **Conectividad e Internet**\n1. **Cable:** Revisa que el cable de red tenga la luz parpadeando.\n2. **WiFi:** Desconecta y reconecta. Olvida la red y vuelve a poner la clave.\n3. **Navegación Lenta:** Cierra pestañas de streaming o descargas.\n4. **Sin Red:** Reinicia el router si estás en casa.",
            
            'red_vpn' => "🛡️ **VPN y Acceso Remoto**\n1. **FortiClient/Cisco:** Verifica que tengas internet antes de conectar.\n2. **Token:** Revisa que tu token no haya expirado.\n3. **Error:** Cierra el cliente VPN totalmente y ábrelo como Administrador.",
            
            'red_carpetas' => "📂 **Carpetas Compartidas**\n1. Asegúrate de estar conectado a la red (cable o VPN).\n2. Si dice 'Acceso Denegado', solicita permisos a tu jefe directo.\n3. Si no aparece la unidad (Z:, X:), reinicia la sesión.",
            
            'impresion' => "🖨️ **Impresoras y Escáner**\n1. **Atasco:** Abre las tapas con cuidado y retira el papel sin romperlo.\n2. **Tóner:** Agita el tóner suavemente si imprime claro.\n3. **Cola de impresión:** Si no imprime, borra los documentos pendientes en la cola.",
            
            'cuentas' => "🔐 **Usuarios y Contraseñas**\n1. **Bloqueo:** Si fallaste 3 veces, espera 15-30 minutos.\n2. **Olvido:** Debes solicitar el reseteo por ticket o llamar a la mesa de ayuda.\n3. **SAP:** La clave de SAP suele ser diferente a la de Windows.",
            
            'mantenimiento' => "🚀 **Mantenimiento y Optimización**\n1. **Lentitud:** Reinicia tu PC al menos una vez por semana.\n2. **Limpieza:** Borra archivos de la carpeta 'Descargas' y vacía la papelera.\n3. **Navegador:** Borra el historial y caché de Chrome/Edge si las páginas cargan mal."
        ];
        return ['success' => true, 'message' => $responses[$topic] ?? "No tengo información detallada sobre ese tema específico."];
    }

    // --- Helpers (Saludos, Status, DB) ---
    private function isGreeting($text) {
        return $this->matchesFuzzy($text, ['hola', 'buenos dias', 'buenas tardes', 'buenas noches', 'que tal', 'hey', 'saludos']);
    }
    private function isPolite($text) {
        return $this->matchesFuzzy($text, ['gracias', 'muchas gracias', 'adios', 'chao', 'hasta luego', 'excelente']);
    }
    private function getGreetingResponse($context) {
        $nombre = $context['user']['nombre'] ?? 'Usuario';
        return ['success' => true, 'message' => "¡Hola $nombre! 👋 Soy tu asistente técnico v3.0. Pregúntame sobre cualquier problema de TI (Hardware, Software, Redes, etc)."];
    }
    private function getPoliteResponse($text) {
        return ['success' => true, 'message' => "¡Es un placer ayudarte! 👨‍💻"];
    }
    private function getUserContextResponse($context) {
        $user = $context['user'];
        return ['success' => true, 'message' => "👤 **Perfil:** {$user['nombre']} ({$user['rol']}) - ID: {$user['id']}"];
    }
    private function getActionGuide($text) {
        return ['success' => true, 'message' => "📝 **Gestión de Tickets:** Usa el botón 'Nueva Incidencia' en el menú lateral para reportar problemas."];
    }
    private function detectStatus($text) {
        if ($this->matchesFuzzy($text, ['cerrada', 'cerradas', 'cerrado', 'solucionado', 'resuelto', 'listo'])) return 'cerrada';
        if ($this->matchesFuzzy($text, ['proceso', 'pendiente', 'curso', 'trabajando', 'atendiendo'])) return 'en_proceso';
        if ($this->matchesFuzzy($text, ['abierta', 'abiertas', 'nuevo', 'pendientes', 'nuevas'])) return 'abierta';
        return null;
    }
    
    // --- DB Methods (Keep existing logic) ---
    private function getGlobalStats() {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN estado = 'abierta' THEN 1 ELSE 0 END) as abiertas, SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as proceso, SUM(CASE WHEN estado = 'cerrada' THEN 1 ELSE 0 END) as cerradas FROM incidencias");
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$stats) return ['success' => true, 'message' => "No pude obtener estadísticas."];
            return ['success' => true, 'message' => "📊 **Estadísticas:**\nTotal: {$stats['total']}\n🔴 Abiertas: {$stats['abiertas']}\n🟡 Proceso: {$stats['proceso']}\n🟢 Cerradas: {$stats['cerradas']}"];
        } catch (Exception $e) { return ['success' => true, 'message' => "Error DB."]; }
    }
    private function getMyTickets($userId) {
        if (!$userId) return ['success' => true, 'message' => 'Usuario no identificado.'];
        try {
            $stmt = $this->pdo->prepare("SELECT id_incidencia, titulo, estado, prioridad FROM incidencias WHERE id_usuario_reporta = ? OR nombre_reporta = (SELECT nombre_completo FROM usuarios WHERE id_usuario = ?) ORDER BY fecha_reporte DESC LIMIT 5");
            $stmt->execute([$userId, $userId]);
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($tickets)) return ['success' => true, 'message' => "No tienes tickets recientes."];
            $msg = "Tus últimos tickets:\n";
            foreach ($tickets as $t) {
                $icon = $t['estado'] == 'abierta' ? '🔴' : ($t['estado'] == 'en_proceso' ? '🟡' : '🟢');
                $msg .= "$icon #{$t['id_incidencia']}: {$t['titulo']} ({$t['estado']})\n";
            }
            return ['success' => true, 'message' => $msg];
        } catch (Exception $e) { return ['success' => true, 'message' => "Error DB."]; }
    }
    private function getTicketsByStatus($status) {
        try {
            $stmt = $this->pdo->prepare("SELECT id_incidencia, titulo, prioridad FROM incidencias WHERE estado = ? ORDER BY fecha_reporte DESC LIMIT 5");
            $stmt->execute([$status]);
            $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($tickets)) return ['success' => true, 'message' => "No hay incidencias " . strtoupper($status)];
            $msg = "Incidencias **" . strtoupper($status) . "**:\n";
            foreach ($tickets as $t) $msg .= "🔹 #{$t['id_incidencia']}: {$t['titulo']}\n";
            return ['success' => true, 'message' => $msg];
        } catch (Exception $e) { return ['success' => true, 'message' => "Error DB."]; }
    }
    private function getTicketStatus($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT titulo, estado, prioridad, tecnico_asignado, respuesta_solucion FROM incidencias WHERE id_incidencia = ?");
            $stmt->execute([$id]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$ticket) return ['success' => true, 'message' => "No existe ticket #$id"];
            $msg = "📋 **Ticket #$id**\n**Título:** {$ticket['titulo']}\n**Estado:** {$ticket['estado']}\n**Prioridad:** {$ticket['prioridad']}";
            if ($ticket['tecnico_asignado']) $msg .= "\n**Técnico:** {$ticket['tecnico_asignado']}";
            if ($ticket['respuesta_solucion']) $msg .= "\n**Solución:** {$ticket['respuesta_solucion']}";
            return ['success' => true, 'message' => $msg];
        } catch (Exception $e) { return ['success' => true, 'message' => "Error DB."]; }
    }
}
?>
