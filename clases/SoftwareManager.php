<?php
require_once 'conexion.php';

class SoftwareManager {
    private $db;
    public function __construct() {
        $this->db = Conexion::obtenerInstancia()->getConexion();
    }

    // Listar software por equipo
    public function listarSoftwarePorEquipo($id_equipo) {
        $sql = "SELECT * FROM software_instalado WHERE id_equipo = :id_equipo ORDER BY fecha_registro DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_equipo', $id_equipo);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Registrar solicitud de software (docente)
    public function solicitarSoftware($id_docente, $nombre_software, $version_solicitada) {
        $sql = "INSERT INTO solicitudes_software (id_docente, nombre_software, version_solicitada, estado, fecha_solicitud)
                VALUES (:id_docente, :nombre_software, :version_solicitada, 'pendiente', NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_docente', $id_docente);
        $stmt->bindParam(':nombre_software', $nombre_software);
        $stmt->bindParam(':version_solicitada', $version_solicitada);
        $stmt->execute();
        $id = $this->db->lastInsertId();
        // notificar admin si hace falta
        return $id;
    }

    public function listarSolicitudes($estado = null) {
        $sql = "SELECT s.*, u.usuario, u.nombre_completo FROM solicitudes_software s
                LEFT JOIN usuarios u ON s.id_docente = u.id_usuario ";
        if ($estado) {
            $sql .= " WHERE s.estado = :estado ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estado', $estado);
            $stmt->execute();
        } else {
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cambiarEstadoSolicitud($id_solicitud, $estado, $comentario_admin = null, $id_admin = null) {
        $sql = "UPDATE solicitudes_software SET estado = :estado, comentario_admin = :comentario_admin, fecha_respuesta = NOW() WHERE id_solicitud = :id_solicitud";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':comentario_admin', $comentario_admin);
        $stmt->bindParam(':id_solicitud', $id_solicitud);
        $stmt->execute();

        // opcional: agregar al historial general
        $histSql = "INSERT INTO historial (id_usuario, accion, detalle, fecha) VALUES (:id_usuario, 'solicitud_soporte', :detalle, NOW())";
        $hstmt = $this->db->prepare($histSql);
        $detalle = "Solicitud $id_solicitud cambiada a $estado. Comentario: $comentario_admin";
        $hstmt->bindParam(':id_usuario', $id_admin);
        $hstmt->bindParam(':detalle', $detalle);
        $hstmt->execute();
    }
}
