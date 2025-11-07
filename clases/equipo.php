<?php
require_once 'conexion.php';

class Equipo extends Conexion {

    public function __construct() {
        parent::__construct();
    }

    // Crear nuevo equipo
    public function crearEquipo($nombre_equipo, $numero_serie, $ubicacion = null, $estado = 'activo') {
        try {
            $sql = "INSERT INTO equipos (nombre_equipo, numero_serie, ubicacion, estado, created_at)
                    VALUES (:nombre_equipo, :numero_serie, :ubicacion, :estado, NOW())";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':nombre_equipo', $nombre_equipo);
            $stmt->bindParam(':numero_serie', $numero_serie);
            $stmt->bindParam(':ubicacion', $ubicacion);
            $stmt->bindParam(':estado', $estado);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error en crearEquipo: " . $e->getMessage());
        }
    }

    // Listar todos los equipos
    public function listarEquipos() {
        try {
            $sql = "SELECT * FROM equipos ORDER BY created_at DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error en listarEquipos: " . $e->getMessage());
        }
    }

    // Buscar equipo por id
    public function obtenerPorId($id_equipo) {
        try {
            $sql = "SELECT * FROM equipos WHERE id_equipo = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id_equipo, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error en obtenerPorId: " . $e->getMessage());
        }
    }

    // Buscar equipo por numero de serie
    public function obtenerPorSerie($numero_serie) {
        try {
            $sql = "SELECT * FROM equipos WHERE numero_serie = :serie LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':serie', $numero_serie);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error en obtenerPorSerie: " . $e->getMessage());
        }
    }

    // Actualizar equipo
    public function actualizarEquipo($id_equipo, $nombre_equipo, $numero_serie, $ubicacion = null, $estado = 'activo') {
        try {
            $sql = "UPDATE equipos SET
                        nombre_equipo = :nombre_equipo,
                        numero_serie = :numero_serie,
                        ubicacion = :ubicacion,
                        estado = :estado,
                        updated_at = NOW()
                    WHERE id_equipo = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':nombre_equipo', $nombre_equipo);
            $stmt->bindParam(':numero_serie', $numero_serie);
            $stmt->bindParam(':ubicacion', $ubicacion);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id_equipo, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error en actualizarEquipo: " . $e->getMessage());
        }
    }

    // Eliminar equipo (si lo permites) — mejor usar estado 'fuera de servicio'
    public function eliminarEquipo($id_equipo) {
        try {
            $sql = "DELETE FROM equipos WHERE id_equipo = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id_equipo, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error en eliminarEquipo: " . $e->getMessage());
        }
    }
}
?>
