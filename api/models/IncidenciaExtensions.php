<?php
// api/models/IncidenciaExtensions.php
// Extensión de métodos para el sistema de notificaciones

require_once 'Incidencia.php';
require_once 'EmailNotifier.php';

/**
 * Métodos adicionales para Incidencia
 * Estos métodos se pueden incluir directamente en Incidencia.php
 * o usar como referencia para agregar funcionalidad
 */

class IncidenciaNotificaciones {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Asignar técnico y enviar notificación
     */
    public function asignarTecnico($idIncidencia, $idTecnico, $idUsuarioCambio) {
        try {
            // Obtener estado anterior
            $stmt = $this->conn->prepare("SELECT id_usuario_tecnico FROM incidencias WHERE id_incidencia = ?");
            $stmt->execute([$idIncidencia]);
            $tecnicoAnterior = $stmt->fetchColumn();
            
            // Actualizar técnico asignado
            $stmt = $this->conn->prepare("UPDATE incidencias SET id_usuario_tecnico = ? WHERE id_incidencia = ?");
            $stmt->execute([$idTecnico, $idIncidencia]);
            
            // Registrar en historial (manual, sin trigger)
            if ($tecnicoAnterior != $idTecnico) {
                $this->registrarHistorial($idIncidencia, 'asignacion', null, $idUsuarioCambio, 
                    "Técnico asignado: ID $idTecnico");
            }
            
            // Enviar notificación
            $notifier = new AppEmailNotifier($this->conn);
            $notifier->notificarAsignacion($idIncidencia, $idTecnico);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en asignarTecnico(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cambiar estado y enviar notificación
     */
    public function cambiarEstado($idIncidencia, $nuevoEstado, $idUsuarioCambio, $comentario = null) {
        try {
            // Obtener estado anterior
            $stmt = $this->conn->prepare("SELECT estado FROM incidencias WHERE id_incidencia = ?");
            $stmt->execute([$idIncidencia]);
            $estadoAnterior = $stmt->fetchColumn();
            
            // Actualizar estado
            $query = "UPDATE incidencias SET estado = ?, respuesta_solucion = ?";
            if ($nuevoEstado === 'cerrada') {
                $query .= ", fecha_cierre = NOW()";
            }
            $query .= " WHERE id_incidencia = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$nuevoEstado, $comentario, $idIncidencia]);
            
            // Registrar en historial (manual, reemplaza al trigger)
            if ($estadoAnterior != $nuevoEstado) {
                $this->registrarHistorial($idIncidencia, $estadoAnterior, $nuevoEstado, $idUsuarioCambio, $comentario);
            }
            
            // Enviar notificación al usuario reportante
            $notifier = new AppEmailNotifier($this->conn);
            $notifier->notificarCambioEstado($idIncidencia, $nuevoEstado, $comentario);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en cambiarEstado(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Confirmación del usuario sobre la solución
     */
    public function confirmarSolucion($idIncidencia, $confirmacion, $comentarioUsuario = null) {
        try {
            // Validar confirmación
            if (!in_array($confirmacion, ['solucionado', 'no_solucionado'])) {
                throw new Exception("Confirmación inválida");
            }
            
            // Actualizar confirmación
            $stmt = $this->conn->prepare("
                UPDATE incidencias 
                SET confirmacion_usuario = ?, 
                    comentario_usuario = ?, 
                    fecha_confirmacion = NOW() 
                WHERE id_incidencia = ?
            ");
            $stmt->execute([$confirmacion, $comentarioUsuario, $idIncidencia]);
            
            // Si el usuario confirma que está solucionado, cerrar la incidencia
            if ($confirmacion === 'solucionado') {
                $stmt = $this->conn->prepare("UPDATE incidencias SET estado = 'cerrada', fecha_cierre = NOW() WHERE id_incidencia = ?");
                $stmt->execute([$idIncidencia]);
                
                // Registrar historial de cierre
                $this->registrarHistorial($idIncidencia, 'en_verificacion', 'cerrada', null, 'Cierre automático por confirmación de usuario');
            }
            
            // Si el usuario dice que NO está solucionado, volver a EN_PROCESO
            if ($confirmacion === 'no_solucionado') {
                $stmt = $this->conn->prepare("UPDATE incidencias SET estado = 'en_proceso' WHERE id_incidencia = ?");
                $stmt->execute([$idIncidencia]);
                
                // Registrar historial de reapertura
                $this->registrarHistorial($idIncidencia, 'en_verificacion', 'en_proceso', null, 'Usuario reporta problema no resuelto: ' . $comentarioUsuario);
            }
            
            // Enviar notificación al técnico
            $notifier = new AppEmailNotifier($this->conn);
            $notifier->notificarConfirmacion($idIncidencia, $confirmacion, $comentarioUsuario);
            
            return true;
        } catch (Exception $e) {
            error_log("Error en confirmarSolucion(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registrar cambio en historial (reemplaza al trigger)
     */
    public function registrarHistorial($idIncidencia, $estadoAnterior, $estadoNuevo, $idUsuarioCambio, $comentario = null) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO historial_estados 
                (id_incidencia, estado_anterior, estado_nuevo, id_usuario_cambio, comentario)
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$idIncidencia, $estadoAnterior, $estadoNuevo, $idUsuarioCambio, $comentario]);
        } catch (PDOException $e) {
            error_log("Error en registrarHistorial(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener historial de una incidencia
     */
    public function getHistorial($idIncidencia) {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    h.*,
                    u.nombre_completo as usuario_nombre
                FROM historial_estados h
                LEFT JOIN usuarios u ON h.id_usuario_cambio = u.id_usuario
                WHERE h.id_incidencia = ?
                ORDER BY h.fecha_cambio DESC
            ");
            $stmt->execute([$idIncidencia]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getHistorial(): " . $e->getMessage());
            return [];
        }
    }
}
?>
