<?php
require_once 'conexion.php';

class TicketManager {
    private $db;

    public function __construct() {
        $this->db = Conexion::obtenerInstancia()->getConexion();
    }

    // Crear ticket (docente)
    public function crearTicket($id_usuario, $id_equipo, $titulo, $descripcion, $urgencia='media', $categoria='otro') {
        try {
            $sql = "INSERT INTO tickets (id_usuario, id_equipo, titulo, descripcion, urgencia, categoria, estado, fecha_creacion)
                    VALUES (:id_usuario, :id_equipo, :titulo, :descripcion, :urgencia, :categoria, 'pendiente', NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->bindParam(':id_equipo', $id_equipo);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':urgencia', $urgencia);
            $stmt->bindParam(':categoria', $categoria);
            $stmt->execute();
            $id_ticket = $this->db->lastInsertId();

            // registrar historial inicial
            $this->agregarHistorial($id_ticket, $id_usuario, 'creado', 'Ticket creado por usuario.');

            // notificar admin (función simple)
            $this->notificarAdminNuevoTicket($id_ticket, $titulo);

            return $id_ticket;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function agregarHistorial($id_ticket, $id_usuario, $accion, $detalle = null) {
        $sql = "INSERT INTO ticket_historial (id_ticket, id_usuario, accion, detalle, fecha)
                VALUES (:id_ticket, :id_usuario, :accion, :detalle, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_ticket', $id_ticket);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':accion', $accion);
        $stmt->bindParam(':detalle', $detalle);
        $stmt->execute();
    }

    public function listarTicketsPorUsuario($id_usuario) {
        $sql = "SELECT t.*, e.numero_serie, e.nombre_equipo FROM tickets t
                LEFT JOIN equipos e ON t.id_equipo = e.id_equipo
                WHERE t.id_usuario = :id_usuario ORDER BY t.fecha_creacion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTicketsTodos() {
        $sql = "SELECT t.*, u.usuario AS creador, e.numero_serie, e.nombre_equipo
                FROM tickets t
                LEFT JOIN usuarios u ON t.id_usuario = u.id_usuario
                LEFT JOIN equipos e ON t.id_equipo = e.id_equipo
                ORDER BY t.fecha_creacion DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function asignarTicket($id_ticket, $asignado_a, $id_admin) {
        $sql = "UPDATE tickets SET asignado_a = :asignado_a, estado = 'en proceso', fecha_actualizacion = NOW() WHERE id_ticket = :id_ticket";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':asignado_a', $asignado_a);
        $stmt->bindParam(':id_ticket', $id_ticket);
        $stmt->execute();

        $this->agregarHistorial($id_ticket, $id_admin, 'asignado', "Asignado a usuario ID $asignado_a");
        $this->notificarUsuarioAsignado($asignado_a, $id_ticket);
        return true;
    }

    public function cambiarEstado($id_ticket, $estado, $id_usuario, $detalle = null) {
        $sql = "UPDATE tickets SET estado = :estado, fecha_actualizacion = NOW() WHERE id_ticket = :id_ticket";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id_ticket', $id_ticket);
        $stmt->execute();

        $this->agregarHistorial($id_ticket, $id_usuario, 'cambio_estado', $detalle);
        return true;
    }

    // Notificaciones básicas (puedes adaptar a SMTP real)
    private function notificarAdminNuevoTicket($id_ticket, $titulo) {
        // Obtener correo del admin (primer admin encontrado)
        $sql = "SELECT correo FROM usuarios WHERE rol = 'admin' LIMIT 1";
        $stmt = $this->db->query($sql);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin && !empty($admin['correo'])) {
            $to = $admin['correo'];
            $subject = "Nuevo ticket #$id_ticket: $titulo";
            $message = "Se ha creado un nuevo ticket. ID: $id_ticket\nTítulo: $titulo\nEntra al sistema para ver detalles.";
            // mail($to, $subject, $message); // comentar/descomentar según configuración
        }
    }

    private function notificarUsuarioAsignado($id_usuario, $id_ticket) {
        $sql = "SELECT correo FROM usuarios WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id_usuario);
        $stmt->execute();
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($u && !empty($u['correo'])) {
            $to = $u['correo'];
            $subject = "Se te ha asignado el ticket #$id_ticket";
            $message = "Te han asignado el ticket $id_ticket. Ingresa al sistema para más información.";
            // mail($to, $subject, $message);
        }
    }

    // Método para agregar adjunto (guardar registro en ticket_attachments)
    public function agregarAdjunto($id_ticket, $filename, $filepath, $mime_type, $uploaded_by = null) {
        $sql = "INSERT INTO ticket_attachments (id_ticket, filename, filepath, mime_type, uploaded_by, uploaded_at)
                VALUES (:id_ticket, :filename, :filepath, :mime_type, :uploaded_by, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_ticket', $id_ticket);
        $stmt->bindParam(':filename', $filename);
        $stmt->bindParam(':filepath', $filepath);
        $stmt->bindParam(':mime_type', $mime_type);
        $stmt->bindParam(':uploaded_by', $uploaded_by);
        $stmt->execute();
    }
}
