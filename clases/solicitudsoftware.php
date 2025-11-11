<?php
require_once 'conexion.php';

class SolicitudSoftware {
    private $conexion;

    public function __construct() {
        $this->conexion = Conexion::obtenerInstancia()->getConexion();
    }

    /**
     * Listar todas las solicitudes con info del docente
     * @param string $estado Filtro opcional por estado (pendiente, aceptado, rechazado)
     * @return array Lista de solicitudes
     */
    public function listarSolicitudes($estado = '') {
        try {
            $sql = "SELECT s.*, u.nombre_completo 
                    FROM solicitudes_software s 
                    JOIN usuarios u ON s.id_docente = u.id_usuario
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($estado)) {
                $sql .= " AND s.estado = :estado";
                $params[':estado'] = $estado;
            }
            
            $sql .= " ORDER BY s.fecha_solicitud DESC";
            
            $stmt = $this->conexion->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarSolicitudes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una solicitud por su ID
     */
    public function obtenerSolicitud($id_solicitud) {
        try {
            $sql = "SELECT s.*, u.nombre_completo 
                    FROM solicitudes_software s 
                    JOIN usuarios u ON s.id_docente = u.id_usuario
                    WHERE s.id_solicitud = :id_solicitud 
                    LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':id_solicitud', (int)$id_solicitud, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerSolicitud: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista las solicitudes de un docente específico
     */
    public function listarPorDocente($id_docente) {
        try {
            $sql = "SELECT s.*, u.nombre_completo 
                    FROM solicitudes_software s
                    JOIN usuarios u ON s.id_docente = u.id_usuario
                    WHERE s.id_docente = :id_docente
                    ORDER BY s.fecha_solicitud DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':id_docente', (int)$id_docente, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarPorDocente: " . $e->getMessage());
            return [];
        }
    }
    
    
    /**
     * Actualiza una solicitud existente
     */
    public function actualizarSolicitud($id_solicitud, $nombre_software, $version_solicitada) {
        try {
            $sql = "UPDATE solicitudes_software 
                    SET nombre_software = :nombre_software, 
                        version_solicitada = :version_solicitada,
                        fecha_solicitud = NOW()
                    WHERE id_solicitud = :id_solicitud";
                    
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':nombre_software', $nombre_software, PDO::PARAM_STR);
            $stmt->bindValue(':version_solicitada', $version_solicitada, PDO::PARAM_STR);
            $stmt->bindValue(':id_solicitud', (int)$id_solicitud, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en actualizarSolicitud: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina una solicitud
     */
    public function eliminarSolicitud($id_solicitud) {
        try {
            $sql = "DELETE FROM solicitudes_software WHERE id_solicitud = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':id', (int)$id_solicitud, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en eliminarSolicitud: " . $e->getMessage());
            return false;
        }
    }

    // Crear una nueva solicitud de software
    public function crearSolicitud($id_docente, $nombre_software, $version) {
        try {
            // Verificar si la tabla existe
            $tableCheck = $this->conexion->query("SHOW TABLES LIKE 'solicitudes_software'")->rowCount() > 0;
            if (!$tableCheck) {
                error_log("Error: La tabla 'solicitudes_software' no existe");
                return false;
            }

            // Verificar los datos de entrada
            if (empty($id_docente) || empty($nombre_software) || empty($version)) {
                error_log("Error: Datos de entrada inválidos");
                return false;
            }

            $sql = "INSERT INTO solicitudes_software (id_docente, nombre_software, version_solicitada, estado, fecha_solicitud) 
                    VALUES (:id_docente, :nombre_software, :version_solicitada, 'pendiente', NOW())";
            
            $stmt = $this->conexion->prepare($sql);
            if ($stmt === false) {
                $error = $this->conexion->errorInfo();
                error_log("Error en la preparación de la consulta: " . ($error[2] ?? 'Error desconocido'));
                return false;
            }

            $stmt->bindValue(':id_docente', (int)$id_docente, PDO::PARAM_INT);
            $stmt->bindValue(':nombre_software', $nombre_software, PDO::PARAM_STR);
            $stmt->bindValue(':version_solicitada', $version, PDO::PARAM_STR);
            
            $result = $stmt->execute();
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("Error al ejecutar la consulta: " . ($errorInfo[2] ?? 'Error desconocido'));
                return false;
            }
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Error en crearSolicitud: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
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
