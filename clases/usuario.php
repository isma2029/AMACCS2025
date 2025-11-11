<?php
require_once 'conexion.php';

class Usuario {

    private $conexion;

    public function __construct() {
        // ✅ Obtiene la conexión del Singleton
        $this->conexion = Conexion::obtenerInstancia()->getConexion();
    }

    public function validarLogin($usuario, $contrasena) {
        try {
            $sql = "SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($contrasena, $data['contrasena'])) {
                    return $data;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (PDOException $e) {
            die("Error en validarLogin: " . $e->getMessage());
        }
    }

    public function marcarPrimerInicio($id_usuario) {
        try {
            $sql = "UPDATE usuarios SET primer_inicio = 0 WHERE id_usuario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id_usuario);
            $stmt->execute();
        } catch (PDOException $e) {
            die("Error al actualizar primer inicio: " . $e->getMessage());
        }
    }
}
