<?php
require_once 'conexion.php';

class SolicitudSoftware {
    private $conexion;

    public function __construct() {
        $this->conexion = Conexion::obtenerInstancia()->getConexion();
    }

    // Listar todas las solicitudes con info del docente
    public function listarSolicitudes() {
        $sql = "SELECT s.*, u.nombre_completo 
                FROM solicitudes_software s 
                JOIN usuarios u ON s.id_docente = u.id_usuario
                ORDER BY s.fecha_solicitud DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener datos de una solicitud
    public function obtenerSolicitud($id_solicitud) {
        $sql = "SELECT s.*, u.nombre_completo 
                FROM solicitudes_software s 
                JOIN usuarios u ON s.id_docente = u.id_usuario
                WHERE s.id_solicitud = :id_solicitud LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_solicitud', $id_solicitud);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function listarPorDocente($id_docente) {
    $sql = "SELECT s.*, u.nombre_completo 
            FROM solicitudes_software s
            JOIN usuarios u ON s.id_docente = u.id_usuario
            WHERE s.id_docente = :id_docente
            ORDER BY s.fecha_solicitud DESC";
    $stmt = $this->conexion->prepare($sql);
    $stmt->bindParam(':id_docente', $id_docente);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Actualizar estado y agregar comentario
    public function actualizarEstado($id_solicitud, $estado, $comentario_admin = '') {
        try {
            // Actualiza la solicitud
            $sql = "UPDATE solicitudes_software 
                    SET estado = :estado, 
                        comentario_admin = :comentario_admin, 
                        fecha_respuesta = NOW()
                    WHERE id_solicitud = :id_solicitud";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':comentario_admin', $comentario_admin);
            $stmt->bindParam(':id_solicitud', $id_solicitud);
            $stmt->execute();

            // Registrar en historial
            $detalle = "Solicitud $id_solicitud cambiada a '$estado'. Comentario: $comentario_admin";
            $histSql = "INSERT INTO historial (id_usuario, accion, detalle, fecha)
                        VALUES (:id_usuario, 'responder_solicitud', :detalle, NOW())";
            $hstmt = $this->conexion->prepare($histSql);
            $admin_id = $_SESSION['id_usuario'];
            $hstmt->bindParam(':id_usuario', $admin_id);
            $hstmt->bindParam(':detalle', $detalle);
            $hstmt->execute();

            return true;
        } catch (PDOException $e) {
            die("Error en actualizarEstado: " . $e->getMessage());
        }
    }
}
?>
