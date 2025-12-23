<?php
// generate_report.php - Versión con diseño mejorado
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Crear directorio font si no existe y crear archivos básicos
if (!is_dir('font')) {
    mkdir('font', 0755, true);
}

// Crear archivos de fuentes básicos vacíos para evitar errores
$fontFiles = [
    'helvetica.php', 'helveticab.php', 'helveticai.php', 'helveticabi.php',
    'times.php', 'timesb.php', 'timesi.php', 'timesbi.php',
    'courier.php', 'courierb.php', 'courieri.php', 'courierbi.php'
];

foreach ($fontFiles as $fontFile) {
    $filePath = 'font/' . $fontFile;
    if (!file_exists($filePath)) {
        file_put_contents($filePath, '<?php
$name = "' . ucfirst(str_replace('.php', '', $fontFile)) . '";
$type = "Core";
$cw = array();
for($i=0; $i<=255; $i++) $cw[chr($i)] = 600;
$up = -100;
$ut = 50;
?>');
    }
}

try {
    // Verificar archivos básicos
    if (!file_exists('fpdf.php')) {
        throw new Exception('fpdf.php no encontrado');
    }
    
    if (!file_exists('api/models/database.php')) {
        throw new Exception('database.php no encontrado');
    }
    
    // Obtener input
    $input = json_decode(file_get_contents('php://input'), true);
    $userInfo = $input['user_id'] ?? null;
    
    if (!$userInfo) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Usuario requerido']);
        exit();
    }
    
    // Incluir archivos
    require_once 'fpdf.php';
    require_once 'api/models/database.php';
    
    // Conectar a BD
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Verificar usuario
    $userQuery = "SELECT u.nombre_completo, ru.nombre_rol as rol 
                  FROM usuarios u 
                  INNER JOIN rol_usuario ru ON u.ID_ROL_USUARIO = ru.id_rol 
                  WHERE u.username = ?";
    $stmt = $pdo->prepare($userQuery);
    $stmt->execute([$userInfo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !in_array($user['rol'], ['admin', 'tecnico'])) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Sin permisos']);
        exit();
    }
    
    // Crear PDF básico
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetMargins(20, 20, 20);
    
    // ENCABEZADO PRINCIPAL CON MARCO
    $pdf->SetFillColor(230, 230, 250); // Color de fondo azul claro
    $pdf->Rect(10, 10, 190, 35, 'F'); // Rectángulo de fondo
    $pdf->Rect(10, 10, 190, 35, 'D'); // Borde del rectángulo
    
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetXY(20, 18);
    $pdf->Cell(170, 8, 'CLINICA CARITA FELIZ', 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetX(20);
    $pdf->Cell(170, 6, 'SISTEMA DE GESTION DE TECNOLOGIA', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetX(20);
    $pdf->Cell(170, 6, 'REPORTE DE INCIDENCIAS TECNICAS', 0, 1, 'C');
    
    $pdf->Ln(15);
    
    // INFORMACIÓN DEL REPORTE CON MARCO
    $pdf->SetFillColor(245, 245, 245); // Gris muy claro
    $pdf->Rect(20, 55, 170, 25, 'F');
    $pdf->Rect(20, 55, 170, 25, 'D');
    
    $pdf->SetXY(25, 60);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 5, 'Fecha de generacion:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(60, 5, date('d/m/Y H:i:s'), 0, 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(30, 5, 'Periodo:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(35, 5, 'Ultimos 30 dias', 0, 1);
    
    $pdf->SetX(25);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(40, 5, 'Generado por:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(60, 5, $user['nombre_completo'], 0, 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(30, 5, 'Tipo de usuario:', 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(35, 5, strtoupper($user['rol']), 0, 1);
    
    $pdf->Ln(20);
    
    // Obtener estadísticas básicas
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'abierta' THEN 1 ELSE 0 END) as abiertas,
                SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN estado = 'cerrada' THEN 1 ELSE 0 END) as cerradas,
                SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as canceladas
              FROM incidencias 
              WHERE fecha_reporte >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // SECCIÓN DE ESTADÍSTICAS CON CUADROS SEPARADOS
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'RESUMEN ESTADISTICO', 0, 1, 'C');
    $pdf->Ln(5);
    
    // Cuadros de estadísticas en filas
    $y_start = $pdf->GetY();
    
    // Fila 1: Total y Abiertas
    $pdf->SetFillColor(200, 220, 255); // Azul claro
    $pdf->Rect(20, $y_start, 80, 20, 'F');
    $pdf->Rect(20, $y_start, 80, 20, 'D');
    $pdf->SetXY(25, $y_start + 5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(70, 5, 'TOTAL DE INCIDENCIAS', 0, 1, 'C');
    $pdf->SetX(25);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(70, 10, $stats['total'], 0, 1, 'C');
    
    $pdf->SetFillColor(255, 200, 200); // Rojo claro
    $pdf->Rect(110, $y_start, 80, 20, 'F');
    $pdf->Rect(110, $y_start, 80, 20, 'D');
    $pdf->SetXY(115, $y_start + 5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(70, 5, 'INCIDENCIAS ABIERTAS', 0, 1, 'C');
    $pdf->SetX(115);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(70, 10, $stats['abiertas'], 0, 1, 'C');
    
    // Fila 2: En Proceso y Cerradas
    $y_start += 25;
    $pdf->SetFillColor(255, 255, 200); // Amarillo claro
    $pdf->Rect(20, $y_start, 80, 20, 'F');
    $pdf->Rect(20, $y_start, 80, 20, 'D');
    $pdf->SetXY(25, $y_start + 5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(70, 5, 'EN PROCESO', 0, 1, 'C');
    $pdf->SetX(25);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(70, 10, $stats['en_proceso'], 0, 1, 'C');
    
    $pdf->SetFillColor(200, 255, 200); // Verde claro
    $pdf->Rect(110, $y_start, 80, 20, 'F');
    $pdf->Rect(110, $y_start, 80, 20, 'D');
    $pdf->SetXY(115, $y_start + 5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(70, 5, 'CERRADAS', 0, 1, 'C');
    $pdf->SetX(115);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(70, 10, $stats['cerradas'], 0, 1, 'C');
    
    // Fila 3: Canceladas (centrado)
    if ($stats['canceladas'] > 0) {
        $y_start += 25;
        $pdf->SetFillColor(220, 220, 220); // Gris claro
        $pdf->Rect(65, $y_start, 80, 20, 'F');
        $pdf->Rect(65, $y_start, 80, 20, 'D');
        $pdf->SetXY(70, $y_start + 5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(70, 5, 'CANCELADAS', 0, 1, 'C');
        $pdf->SetX(70);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(70, 10, $stats['canceladas'], 0, 1, 'C');
        $pdf->SetY($y_start + 30);
    } else {
        $pdf->SetY($y_start + 30);
    }
    
    // Obtener últimas incidencias
    $query = "SELECT i.id_incidencia, i.titulo, i.estado, i.prioridad, i.fecha_reporte,
                     COALESCE(i.nombre_reporta, 'Anonimo') as reportante
              FROM incidencias i
              ORDER BY i.fecha_reporte DESC 
              LIMIT 10";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $incidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($incidencias) > 0) {
        $pdf->Ln(15);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'DETALLE DE INCIDENCIAS RECIENTES', 0, 1, 'C');
        $pdf->Ln(5);
        
        // Headers de tabla con colores - MEJOR CENTRADO
        $table_start_x = 25; // Posición X consistente para toda la tabla
        $pdf->SetX($table_start_x);
        $pdf->SetFillColor(100, 100, 150); // Azul oscuro
        $pdf->SetTextColor(255, 255, 255); // Texto blanco
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(15, 8, 'ID', 1, 0, 'C', true);
        $pdf->Cell(45, 8, 'TITULO', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'ESTADO', 1, 0, 'C', true);
        $pdf->Cell(22, 8, 'PRIORIDAD', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'FECHA', 1, 0, 'C', true);
        $pdf->Cell(33, 8, 'REPORTANTE', 1, 1, 'C', true);
        
        // Datos con colores alternos - ALINEADOS
        $pdf->SetTextColor(0, 0, 0); // Texto negro
        $pdf->SetFont('Arial', '', 8);
        $fill = false;
        
        foreach ($incidencias as $inc) {
            $fill = !$fill;
            $pdf->SetFillColor(245, 245, 245); // Gris muy claro para filas alternas
            
            $fecha = date('d/m/Y', strtotime($inc['fecha_reporte']));
            $titulo = strlen($inc['titulo']) > 28 ? substr($inc['titulo'], 0, 25) . '...' : $inc['titulo'];
            $reportante = strlen($inc['reportante']) > 18 ? substr($inc['reportante'], 0, 15) . '...' : $inc['reportante'];
            
            $pdf->SetX($table_start_x); // Misma posición X que los headers
            $pdf->Cell(15, 6, $inc['id_incidencia'], 1, 0, 'C', $fill);
            $pdf->Cell(45, 6, $titulo, 1, 0, 'L', $fill);
            $pdf->Cell(25, 6, ucfirst(str_replace('_', ' ', $inc['estado'])), 1, 0, 'C', $fill);
            $pdf->Cell(22, 6, ucfirst($inc['prioridad']), 1, 0, 'C', $fill);
            $pdf->Cell(25, 6, $fecha, 1, 0, 'C', $fill);
            $pdf->Cell(33, 6, $reportante, 1, 1, 'L', $fill);
        }
    } else {
        $pdf->Ln(15);
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell(0, 10, 'No hay incidencias registradas en los ultimos 30 dias', 0, 1, 'C');
    }
    
    // Estadísticas por tipo en cuadro separado
    $queryTipos = "SELECT ti.nombre, COUNT(i.id_incidencia) as total
                   FROM tipos_incidencia ti
                   LEFT JOIN incidencias i ON ti.id_tipo_incidencia = i.id_tipo_incidencia 
                   AND i.fecha_reporte >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                   GROUP BY ti.id_tipo_incidencia, ti.nombre
                   HAVING total > 0
                   ORDER BY total DESC";
    
    $stmt = $pdo->prepare($queryTipos);
    $stmt->execute();
    $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($tipos) > 0) {
        $pdf->Ln(15);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'DISTRIBUCION POR TIPO DE INCIDENCIA', 0, 1, 'C');
        $pdf->Ln(5);
        
        // Cuadro para tipos de incidencia - CENTRADO
        $y_tipos = $pdf->GetY();
        $height_tipos = count($tipos) * 8 + 10;
        
        $pdf->SetFillColor(250, 250, 250);
        $pdf->Rect(20, $y_tipos, 170, $height_tipos, 'F');
        $pdf->Rect(20, $y_tipos, 170, $height_tipos, 'D');
        
        $pdf->SetXY(30, $y_tipos + 5);
        $pdf->SetFont('Arial', 'B', 11);
        
        foreach ($tipos as $tipo) {
            $pdf->SetX(30);
            $pdf->Cell(120, 6, $tipo['nombre'] . ':', 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(30, 6, $tipo['total'], 0, 1, 'C');
            $pdf->SetFont('Arial', '', 11);
        }
        
        $pdf->SetY($y_tipos + $height_tipos + 5);
    }
    
    // Footer solo si es necesario - SIN CUADRO INNECESARIO
    if ($pdf->GetY() < 250) { // Solo si hay espacio en la página
        $pdf->Ln(20);
        
        // Solo texto del footer, sin cuadro
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->Cell(0, 4, 'Reporte generado automaticamente por el Sistema de Gestion de Tecnologia', 0, 1, 'C');
        $pdf->Cell(0, 4, 'Clinica Carita Feliz - Departamento de Tecnologia', 0, 1, 'C');
        $pdf->Cell(0, 4, 'Documento confidencial - Solo para uso interno', 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    }
    
    // Preparar output
    $filename = 'reporte_incidencias_' . date('Y-m-d_H-i-s') . '.pdf';
    
    // Limpiar buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Headers
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    
    // Enviar PDF
    $pdf->Output('D', $filename);
    exit();
    
} catch (Exception $e) {
    error_log("Error en reporte: " . $e->getMessage());
    
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Error generando reporte',
        'details' => $e->getMessage()
    ]);
    exit();
}
?>