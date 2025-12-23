<?php
/*
 * PARCHE PARA incidencias.php
 * 
 * INSTRUCCIONES:
 * 1. Abre: c:\xampp\htdocs\gestion-incidencias\api\controllers\incidencias.php
 * 2. Busca la línea 259 que dice: case 'POST':
 * 3. DESPUÉS de la línea 260 (try {), PEGA todo el código de abajo
 */

// ============ CÓDIGO PARA PEGAR ============

// Manejar acción de confirmación de solución
if ($action === 'confirmar_solucion') {
    $data = getJsonInput();
    
    if (empty($data->id_incidencia) || !is_numeric($data->id_incidencia)) {
        sendResponse(400, array("message" => "ID de incidencia inválido"));
    }
    
    if (empty($data->confirmacion) || !in_array($data->confirmacion, ['solucionado', 'no_solucionado'])) {
        sendResponse(400, array("message" => "Confirmación inválida"));
    }
    
    try {
        require_once '../models/IncidenciaExtensions.php';
        $incExt = new IncidenciaNotificaciones($db);
        
        $resultado = $incExt->confirmarSolucion(
            intval($data->id_incidencia),
            $data->confirmacion,
            isset($data->comentario_usuario) ? trim($data->comentario_usuario) : null
        );
        
        if ($resultado) {
            sendResponse(200, array(
                "success" => true,
                "message" => "Confirmación registrada exitosamente"
            ));
        } else {
            sendResponse(500, array("message" => "Error al registrar confirmación"));
        }
    } catch (Exception $e) {
        error_log("Error en confirmar_solucion: " . $e->getMessage());
        sendResponse(500, array("message" => "Error: " . $e->getMessage()));
    }
}

// ============ FIN DEL CÓDIGO ============

/*
 * RESULTADO ESPERADO:
 * 
 * case 'POST':
 *     try {
 *         // Manejar acción de confirmación de solución  <-- NUEVO CÓDIGO AQUÍ
 *         if ($action === 'confirmar_solucion') {
 *             ...
 *         }
 *         
 *         $data = getJsonInput();  <-- CÓDIGO ORIGINAL CONTINÚA
 *         ...
 */
?>
