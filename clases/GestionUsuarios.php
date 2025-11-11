<?php
require_once 'conexion.php';

class GestionUsuarios {

    private $conexion;

    public function __construct() {
        // ✅ Usamos la instancia única del Singleton
        $this->conexion = Conexion::obtenerInstancia()->getConexion();
    }

    /**
     * 📋 Listar todos los usuarios
     */
    public function listarUsuarios() {
        try {
            $sql = "SELECT id_usuario, nombre_completo, correo, usuario, rol, primer_inicio, created_at 
                    FROM usuarios ORDER BY id_usuario DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al listar usuarios: " . $e->getMessage());
        }
    }

    /**
     * ➕ Agregar un nuevo usuario
     */
    public function agregarUsuario($nombre_completo, $correo, $usuario, $contrasena, $rol = 'docente') {
        try {
            $sql = "INSERT INTO usuarios (nombre_completo, correo, usuario, contrasena, rol, primer_inicio)
                    VALUES (:nombre_completo, :correo, :usuario, :contrasena, :rol, 1)";
            $stmt = $this->conexion->prepare($sql);
            $hash = password_hash($contrasena, PASSWORD_BCRYPT);
            $stmt->bindParam(':nombre_completo', $nombre_completo);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':contrasena', $hash);
            $stmt->bindParam(':rol', $rol);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return "Error: el usuario o correo ya existe.";
            }
            die("Error al agregar usuario: " . $e->getMessage());
        }
    }

    /**
     * ✏️ Obtener un usuario por ID
     */
    public function obtenerUsuarioPorId($id_usuario) {
        try {
            $sql = "SELECT * FROM usuarios WHERE id_usuario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id_usuario);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener usuario: " . $e->getMessage());
        }
    }

    /**
     * 🛠️ Actualizar usuario (con o sin cambio de contraseña)
     */
    public function actualizarUsuario($id_usuario, $nombre_completo, $correo, $rol, $contrasena = null) {
        try {
            if ($contrasena) {
                $sql = "UPDATE usuarios 
                        SET nombre_completo = :nombre_completo, correo = :correo, rol = :rol, contrasena = :contrasena, updated_at = NOW()
                        WHERE id_usuario = :id";
                $stmt = $this->conexion->prepare($sql);
                $hash = password_hash($contrasena, PASSWORD_BCRYPT);
                $stmt->bindParam(':contrasena', $hash);
            } else {
                $sql = "UPDATE usuarios 
                        SET nombre_completo = :nombre_completo, correo = :correo, rol = :rol, updated_at = NOW()
                        WHERE id_usuario = :id";
                $stmt = $this->conexion->prepare($sql);
            }

            $stmt->bindParam(':nombre_completo', $nombre_completo);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':rol', $rol);
            $stmt->bindParam(':id', $id_usuario);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            die("Error al actualizar usuario: " . $e->getMessage());
        }
    }

    /**
     * ❌ Eliminar usuario
     */
    public function eliminarUsuario($id_usuario) {
        try {
            $sql = "DELETE FROM usuarios WHERE id_usuario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id_usuario);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            die("Error al eliminar usuario: " . $e->getMessage());
        }
    }
}
?>
