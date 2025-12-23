<?php
// api/controllers/chat.php

// ⭐ HEADERS CORS MEJORADOS PARA HOSTING
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$allowed_origins = [
    'https://gestion-incidencias-caritafeliz.wuaze.com',
    'http://gestion-incidencias-caritafeliz.wuaze.com',
    'http://localhost',
    'http://127.0.0.1'
];

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Deshabilitar mostrar errores en producción
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log de errores en archivo
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/../../chat_errors.log');

session_start();

require_once '../models/database.php';
require_once '../models/LocalChat.php';

// ⚠️ NUNCA expongas tu API key en producción
// Mejor usa variables de entorno o un archivo config.php no versionado
$geminiApiKey = 'AIzaSyDMWkLeKI5z1ryyPX4h9Ka5QaJRK8Xjf7Q';

// Inicializar DB
try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
} catch (Exception $e) {
    error_log("Error DB: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Error de conexión a BD']));
}

// VALIDACIÓN DE AUTENTICACIÓN
$authValid = false;

// Opción 1: Sesión PHP existente
if (isset($_SESSION['user_id']) && isset($_SESSION['rol'])) {
    $authValid = true;
    error_log("Auth: Sesión PHP válida para usuario " . $_SESSION['user_id']);
}

// Opción 2: Token del header
if (!$authValid) {
    // Obtener headers de forma compatible
    $headers = [];
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
    } else {
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
    }
    
    // Buscar Authorization en diferentes formatos
    $authHeader = null;
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    } elseif (isset($headers['authorization'])) {
        $authHeader = $headers['authorization'];
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }
    
    if ($authHeader) {
        $token = str_replace('Bearer ', '', $authHeader);
        $decoded = base64_decode($token, true);
        
        if ($decoded) {
            $userData = json_decode($decoded, true);
            
            if ($userData && isset($userData['username'], $userData['rol'])) {
                $_SESSION['user_id'] = $userData['id'] ?? $userData['username'];
                $_SESSION['username'] = $userData['username'];
                $_SESSION['nombre_completo'] = $userData['nombre'] ?? $userData['nombre_completo'] ?? $userData['username'];
                $_SESSION['rol'] = $userData['rol'];
                $authValid = true;
                error_log("Auth: Token válido para " . $userData['username']);
            } else {
                error_log("Auth: Token decodificado pero datos inválidos");
            }
        } else {
            error_log("Auth: Error al decodificar token");
        }
    } else {
        error_log("Auth: No se encontró header Authorization");
    }
}

if (!$authValid) {
    error_log("Auth: Autenticación fallida - No se encontró sesión válida");
    http_response_code(401);
    die(json_encode([
        'success' => false, 
        'message' => 'No autorizado. Por favor inicia sesión nuevamente.'
    ]));
}

if (!in_array($_SESSION['rol'], ['admin', 'tecnico', 'usuario'])) {
    error_log("Auth: Rol no permitido: " . $_SESSION['rol']);
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Sin permisos suficientes']));
}

// MANEJO DE REQUESTS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (empty($input['message'])) {
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => 'El mensaje no puede estar vacío']));
    }
    
    try {
        $chat = new LocalChat($pdo);
        
        $context = [
            'user' => [
                'id' => $_SESSION['user_id'] ?? null,
                'nombre' => $_SESSION['nombre_completo'] ?? $_SESSION['username'],
                'rol' => $_SESSION['rol']
            ]
        ];
        
        error_log("Procesando mensaje local: " . substr($input['message'], 0, 50));
        
        $response = $chat->processMessage($input['message'], $context);
        
        if ($response['success']) {
            echo json_encode([
                'success' => true,
                'reply' => $response['message']
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al procesar el mensaje'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Exception en chat: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error procesando mensaje. Por favor intenta nuevamente.'
        ]);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>