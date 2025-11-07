<?php
require_once 'conexion.php';

class Usuario extends Conexion {

    public function __construct() {
        parent::__construct(); // Llama al constructor de la clase padre (Conexion)
    }

    // Método para verificar usuario y contraseña
    public function validarLogin($usuario, $contrasena) {
        try {
            $sql = "SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $data = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verificamos la contraseña usando password_verify
                if (password_verify($contrasena, $data['contrasena'])) {
                    return $data; // Devuelve los datos del usuario si es correcto
                } else {
                    return false; // Contraseña incorrecta
                }
            } else {
                return false; // Usuario no encontrado
            }
        } catch (PDOException $e) {
            die("Error en validarLogin: " . $e->getMessage());
        }
    }

    // Método para actualizar si fue el primer inicio
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
?>
