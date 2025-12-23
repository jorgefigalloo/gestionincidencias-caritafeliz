<?php
// api/models/Gemini.php

class Gemini {
    private $apiKey;
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent';
    private $pdo;

    public function __construct($apiKey, $pdo = null) {
        $this->apiKey = $apiKey;
        $this->pdo = $pdo;
    }

    public function askTechnicalSupport($prompt, $context = []) {
        // Enriquecer contexto con datos de incidencias si están disponibles
        if ($this->pdo) {
            $context = $this->enrichContextWithIncidents($context, $prompt);
        }
        
        $systemPrompt = $this->buildSystemPrompt($context);
        $fullPrompt = $systemPrompt . "\n\nPregunta del usuario: " . $prompt;
        return $this->ask($fullPrompt);
    }

    private function buildSystemPrompt($context) {
        $systemPrompt = "Eres un asistente especializado en soporte técnico para la Clínica Carita Feliz. 

Tu función es:
1. Ayudar con problemas técnicos de hardware, software, conectividad y mantenimiento
2. Proporcionar soluciones claras y paso a paso
3. Sugerir mejores prácticas en tecnología médica y administrativa
4. Ser conciso pero completo en tus respuestas
5. Usar un tono profesional pero amigable
6. Analizar estadísticas e historial de incidencias cuando sea relevante

Tipos de problemas que manejas:
- Fallo de Hardware (computadoras, impresoras, equipos médicos)
- Problema de Software (sistemas, aplicaciones, actualizaciones)
- Conectividad de Red (internet, red interna, WiFi)
- Mantenimiento Preventivo (optimización, limpieza, respaldos)";

        if (!empty($context['user'])) {
            $systemPrompt .= "\n\nUsuario: " . $context['user']['nombre'] . " (" . $context['user']['rol'] . ")";
        }

        // Agregar información de incidencias al contexto
        if (!empty($context['incidents_stats'])) {
            $systemPrompt .= "\n\n=== ESTADÍSTICAS DE INCIDENCIAS ===";
            $stats = $context['incidents_stats'];
            $systemPrompt .= "\n- Total incidencias: " . ($stats['total'] ?? 0);
            $systemPrompt .= "\n- Abiertas: " . ($stats['abiertas'] ?? 0);
            $systemPrompt .= "\n- En proceso: " . ($stats['proceso'] ?? 0);
            $systemPrompt .= "\n- Cerradas: " . ($stats['cerradas'] ?? 0);
        }

        if (!empty($context['recent_incidents'])) {
            $systemPrompt .= "\n\n=== INCIDENCIAS RECIENTES ===";
            foreach ($context['recent_incidents'] as $inc) {
                $systemPrompt .= "\n- ID " . $inc['id'] . ": " . $inc['titulo'] . " [" . $inc['estado'] . ", " . $inc['prioridad'] . "]";
            }
        }

        if (!empty($context['common_problems'])) {
            $systemPrompt .= "\n\n=== PROBLEMAS COMUNES DETECTADOS ===";
            foreach ($context['common_problems'] as $problem) {
                $systemPrompt .= "\n- " . $problem['tipo'] . ": " . $problem['count'] . " casos";
            }
        }

        $systemPrompt .= "\n\nBASÁNDOTE en esta información, proporciona respuestas útiles, precisas y orientadas a soluciones. Si detectas patrones en las incidencias, menciónalo.";
        return $systemPrompt;
    }

    private function enrichContextWithIncidents($context, $prompt) {
        if (!$this->pdo) return $context;

        $lowerPrompt = strtolower($prompt);
        
        // Detectar si la pregunta es sobre incidencias
        $incidentKeywords = [
            'incidencias', 'problemas', 'reportes', 'tickets', 
            'cuántas', 'estadísticas', 'resumen', 'estado',
            'más común', 'frecuente', 'repetido'
        ];
        
        $isAboutIncidents = false;
        foreach ($incidentKeywords as $keyword) {
            if (strpos($lowerPrompt, $keyword) !== false) {
                $isAboutIncidents = true;
                break;
            }
        }

        if (!$isAboutIncidents) {
            return $context; // No agregar contexto innecesario
        }

        try {
            // Obtener estadísticas generales
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'abierta' THEN 1 ELSE 0 END) as abiertas,
                    SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as proceso,
                    SUM(CASE WHEN estado = 'cerrada' THEN 1 ELSE 0 END) as cerradas
                FROM incidencias
                WHERE DATE(fecha_reporte) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ");
            $stmt->execute();
            $context['incidents_stats'] = $stmt->fetch(PDO::FETCH_ASSOC);

            // Obtener incidencias recientes (últimas 5)
            $stmt = $this->pdo->prepare("
                SELECT 
                    id_incidencia as id, 
                    titulo, 
                    estado, 
                    prioridad,
                    DATE_FORMAT(fecha_reporte, '%d/%m/%Y') as fecha
                FROM incidencias
                ORDER BY fecha_reporte DESC
                LIMIT 5
            ");
            $stmt->execute();
            $context['recent_incidents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener problemas más comunes (por tipo)
            $stmt = $this->pdo->prepare("
                SELECT 
                    ti.nombre as tipo,
                    COUNT(*) as count
                FROM incidencias i
                LEFT JOIN tipos_incidencia ti ON i.id_tipo_incidencia = ti.id_tipo_incidencia
                WHERE DATE(i.fecha_reporte) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY ti.nombre
                ORDER BY count DESC
                LIMIT 5
            ");
            $stmt->execute();
            $context['common_problems'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error enriqueciendo contexto: " . $e->getMessage());
        }

        return $context;
    }

    public function ask($prompt) {
        $url = $this->apiUrl . "?key=" . $this->apiKey;
        
        $payload = [
            "contents" => [
                ["parts" => [["text" => $prompt]]]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "topK" => 40,
                "topP" => 0.95,
                "maxOutputTokens" => 1024
            ]
        ];

        error_log("Gemini: Enviando request");

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        error_log("Gemini: HTTP Code = $httpCode");

        if ($error) {
            error_log("Gemini: cURL Error = $error");
            return ['success' => false, 'message' => 'Error de conexión con el asistente'];
        }

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return [
                    'success' => true, 
                    'message' => trim($data['candidates'][0]['content']['parts'][0]['text'])
                ];
            }

            // Detectar bloqueo por seguridad
            if (isset($data['candidates'][0]['finishReason']) && 
                $data['candidates'][0]['finishReason'] === 'SAFETY') {
                return [
                    'success' => false,
                    'message' => 'No puedo procesar esa consulta. Por favor, reformula tu pregunta.'
                ];
            }
        }

        error_log("Gemini: Error HTTP $httpCode - " . substr($response, 0, 200));
        return ['success' => false, 'message' => 'El asistente no está disponible en este momento'];
    }

    public function generateSolutionSuggestions($incidencia) {
        $prompt = "Analiza esta incidencia técnica y proporciona 3-5 sugerencias de solución ordenadas de más simple a más compleja:

Título: {$incidencia['titulo']}
Descripción: {$incidencia['descripcion']}
Tipo: {$incidencia['tipo']}
Prioridad: {$incidencia['prioridad']}

Proporciona soluciones prácticas y específicas para esta clínica.";

        return $this->ask($prompt);
    }
}
?>