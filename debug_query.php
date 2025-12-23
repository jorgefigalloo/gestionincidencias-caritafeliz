<?php
require_once 'api/models/database.php';
require_once 'api/models/Incidencia.php';

$database = new Database();
$db = $database->getConnection();

$userId = 3; // Usuario Final
$table_name = "incidencias";

echo "Testing FULL readByUser query for User ID: $userId\n";

$query = "SELECT 
            i.id_incidencia,
            i.titulo,
            i.descripcion,
            i.respuesta_solucion,
            i.id_tipo_incidencia,
            i.id_subtipo_incidencia,
            i.id_usuario_reporta,
            i.nombre_reporta,
            i.email_reporta,
            i.estado,
            i.prioridad,
            i.fecha_reporte,
            i.fecha_cierre,
            i.id_usuario_tecnico,
            ti.nombre as tipo_nombre,
            si.nombre as subtipo_nombre,
            ur.nombre_completo as reporta_usuario,
            ut.nombre_completo as tecnico_asignado,
            a.nombre_area,
            s.nombre_sede
          FROM " . $table_name . " i
          LEFT JOIN tipos_incidencia ti ON i.id_tipo_incidencia = ti.id_tipo_incidencia
          LEFT JOIN subtipos_incidencias si ON i.id_subtipo_incidencia = si.id_subtipo_incidencia
          LEFT JOIN usuarios ur ON i.id_usuario_reporta = ur.id_usuario
          LEFT JOIN usuarios ut ON i.id_usuario_tecnico = ut.id_usuario
          LEFT JOIN areas a ON ur.id_area = a.id_area
          LEFT JOIN sedes s ON a.id_sede = s.id_sede
          WHERE i.id_usuario_reporta = ? 
             OR (i.id_usuario_reporta IS NULL AND i.nombre_reporta = (SELECT nombre_completo FROM usuarios WHERE id_usuario = ?))
          ORDER BY i.fecha_reporte DESC";

try {
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $userId);
    $stmt->bindParam(2, $userId);
    $stmt->execute();

    $count = $stmt->rowCount();
    echo "Found $count records.\n";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id_incidencia'] . " - Title: " . $row['titulo'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
