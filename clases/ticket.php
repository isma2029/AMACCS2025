<?php
require_once 'conexion.php';

class Ticket extends Conexion {

    public function __construct() {
        parent::__construct(); // hereda la conexión
    }

    // 🟢 Crear un nuevo ticket
    public function crearTicket($id_usuario, $id_equipo, $descripcion, $urgencia, $categoria) {
        try {
            $sql = "INSERT INTO tickets (id_usuario, id_equipo, descripcion, urgencia, categoria, estado, fecha_creacion)
                    VALUES (:id_usuario, :id_equipo, :descripcion, :urgencia, :categoria, 'pendiente', NOW())";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->bindParam(':id_equipo', $id_equipo);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':urgencia', $urgencia);
            $stmt->bindParam(':categoria', $categoria);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error al crear ticket: " . $e->getMessage());
        }
    }

    // 🟡 Listar todos los tickets (admin)
    public function listarTickets() {
        try {
            $sql = "SELECT t.*, u.nombre_completo, e.nombre_equipo, e.numero_serie
                    FROM tickets t
                    INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                    INNER JOIN equipos e ON t.id_equipo = e.id_equipo
                    ORDER BY t.fecha_creacion DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al listar tickets: " . $e->getMessage());
        }
    }

    // 🟣 Listar tickets por usuario (docente)
    public function listarTicketsPorUsuario($id_usuario) {
        try {
            $sql = "SELECT t.*, e.nombre_equipo
                    FROM tickets t
                    INNER JOIN equipos e ON t.id_equipo = e.id_equipo
                    WHERE t.id_usuario = :id_usuario
                    ORDER BY t.fecha_creacion DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al listar tickets del usuario: " . $e->getMessage());
        }
    }

    // 🔵 Actualizar estado de un ticket
    public function actualizarEstado($id_ticket, $nuevo_estado) {
        try {
            $sql = "UPDATE tickets SET estado = :estado, fecha_cierre = 
                    CASE WHEN :estado = 'resuelto' THEN NOW() ELSE fecha_cierre END
                    WHERE id_ticket = :id_ticket";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':estado', $nuevo_estado);
            $stmt->bindParam(':id_ticket', $id_ticket);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error al actualizar estado: " . $e->getMessage());
        }
    }

    // 🔍 Ver detalles de un ticket
    public function obtenerTicketPorId($id_ticket) {
        try {
            $sql = "SELECT t.*, u.nombre_completo, e.nombre_equipo, e.numero_serie
                    FROM tickets t
                    INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                    INNER JOIN equipos e ON t.id_equipo = e.id_equipo
                    WHERE t.id_ticket = :id_ticket";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_ticket', $id_ticket);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener ticket: " . $e->getMessage());
        }
    }
}
?>
