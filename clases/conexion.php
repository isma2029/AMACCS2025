<?php
class Conexion {
    private $host = "localhost";
    private $usuario = "root";
    private $contrasena = "";
    private $base_datos = "soporte_local"; // <-- usar el nombre correcto
    protected $conexion;

    public function __construct() {
        try {
            $this->conexion = new PDO(
                "mysql:host={$this->host};dbname={$this->base_datos};charset=utf8mb4",
                $this->usuario,
                $this->contrasena
            );
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("❌ Error de conexión: " . $e->getMessage());
        }
    }

    public function getConexion() {
        return $this->conexion;
    }
}
?>
