<?php
require_once 'conexion.php';

class SolicitudSoftware extends Conexion {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Crear una nueva solicitud de software por un docente
     * @param int $id_docente
     * @param string $nombre_software
     * @param string|null $version_solicitada
     * @return bool
     */
    public function crearSolicitud($id_docente, $nombre_software, $version_solicitada = null) {
        try {
            $sql = "INSERT INTO solicitudes_software (id_docente, nombre_software, version_solicitada, estado, fecha_solicitud)
                    VALUES (:id_docente, :nombre_software, :version_solicitada, 'pendiente', NOW())";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_docente', $id_docente, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_software', $nombre_software);
            $stmt->bindParam(':version_solicitada', $version_solicitada);
            return $stmt->execute();
        } catch (PDOException $e) {
            // En producción, mejor loggear en vez de die()
            die("Error en crearSolicitud: " . $e->getMessage());
        }
    }

    /**
     * Listar todas las solicitudes (para admin)
     * @return array
     */
    public function listarSolicitudes() {
        try {
            $sql = "SELECT s.*, u.nombre_completo, u.usuario
                    FROM solicitudes_software s
                    LEFT JOIN usuarios u ON s.id_docente = u.id_usuario
                    ORDER BY s.fecha_solicitud DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error en listarSolicitudes: " . $e->getMessage());
        }
    }

    /**
     * Listar solicitudes de un docente específico
     * @param int $id_docente
     * @return array
     */
    public function listarPorDocente($id_docente) {
        try {
            $sql = "SELECT * FROM solicitudes_software WHERE id_docente = :id_docente ORDER BY fecha_solicitud DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_docente', $id_docente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error en listarPorDocente: " . $e->getMessage());
        }
    }

    /**
     * Obtener una solicitud por su id
     * @param int $id_solicitud
     * @return array|false
     */
    public function obtenerPorId($id_solicitud) {
        try {
            $sql = "SELECT s.*, u.nombre_completo, u.usuario
                    FROM solicitudes_software s
                    LEFT JOIN usuarios u ON s.id_docente = u.id_usuario
                    WHERE s.id_solicitud = :id_solicitud
                    LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error en obtenerPorId: " . $e->getMessage());
        }
    }

    /**
     * Actualizar estado de la solicitud (aceptado / rechazado / pendiente)
     * y opcionalmente agregar comentario del admin
     * @param int $id_solicitud
     * @param string $nuevo_estado  -> 'pendiente'|'aceptado'|'rechazado'
     * @param string|null $comentario_admin
     * @return bool
     */
    public function actualizarEstado($id_solicitud, $nuevo_estado, $comentario_admin = null) {
        try {
            $sql = "UPDATE solicitudes_software
                    SET estado = :estado,
                        comentario_admin = :comentario_admin,
                        fecha_respuesta = CASE WHEN :estado IN ('aceptado','rechazado') THEN NOW() ELSE fecha_respuesta END
                    WHERE id_solicitud = :id_solicitud";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':estado', $nuevo_estado);
            $stmt->bindParam(':comentario_admin', $comentario_admin);
            $stmt->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error en actualizarEstado: " . $e->getMessage());
        }
    }

    /**
     * Eliminar una solicitud (opcional — solo si tu política lo permite)
     * @param int $id_solicitud
     * @return bool
     */
    public function eliminarSolicitud($id_solicitud) {
        try {
            $sql = "DELETE FROM solicitudes_software WHERE id_solicitud = :id_solicitud";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error en eliminarSolicitud: " . $e->getMessage());
        }
    }
}
?>
