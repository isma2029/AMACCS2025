<?php
/**
 * Clase para manejar la conexión a la base de datos
 */
class Conexion {
    // Configuración de la base de datos
    private $host = "localhost";
    private $usuario = "root";
    private $contrasena = "";
    private $base_datos = "soporte_local";
    private $puerto = 3306;
    private $charset = "utf8mb4";
    
    // Objeto de conexión PDO
    protected $conexion;
    
    // Instancia única (para patrón Singleton)
    private static $instancia = null;

    /**
     * Constructor privado para implementar patrón Singleton
     */
    private function __construct() {
        $this->conectar();
    }
    
    /**
     * Método para obtener la instancia única
     * @return Conexion Instancia de la clase Conexion
     */
    public static function obtenerInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }
    
    /**
     * Establece la conexión a la base de datos
     * @throws PDOException Si hay un error al conectar
     */
    private function conectar() {
        try {
            $dsn = "mysql:host={$this->host};port={$this->puerto};dbname={$this->base_datos};charset={$this->charset}";
            
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->conexion = new PDO($dsn, $this->usuario, $this->contrasena, $opciones);
            
        } catch (PDOException $e) {
            // Registrar el error en un archivo de log
            $this->registrarError($e->getMessage());
            
            // Mostrar un mensaje genérico al usuario
            if (defined('MODO_DESARROLLO') && MODO_DESARROLLO === true) {
                die("❌ Error de conexión: " . $e->getMessage());
            } else {
                die("❌ Lo sentimos, ha ocurrido un error al conectar con la base de datos. Por favor, inténtelo más tarde.");
            }
        }
    }
    
    /**
     * Registra errores en un archivo de log
     * @param string $mensaje Mensaje de error a registrar
     */
    private function registrarError($mensaje) {
        // Asegurarse de que el directorio de logs exista
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $fecha = date('Y-m-d H:i:s');
        $log = "[$fecha] Error de base de datos: $mensaje" . PHP_EOL;
        file_put_contents($logDir . '/errores_db.log', $log, FILE_APPEND);
    }
    
    /**
     * Obtiene la conexión PDO
     * @return PDO Instancia de PDO
     */
    public function getConexion() {
        // Verificar si la conexión sigue activa
        try {
            $this->conexion->query('SELECT 1');
        } catch (PDOException $e) {
            // Reconectar si la conexión se perdió
            $this->conectar();
        }
        
        return $this->conexion;
    }
    
    /**
     * Prevenir la clonación del objeto
     */
    private function __clone() {}
    
    /**
     * Prevenir la deserialización del objeto
     */
    private function __wakeup() {}
    
    /**
     * Cierra la conexión a la base de datos
     */
    public function cerrarConexion() {
        $this->conexion = null;
        self::$instancia = null;
    }
}
?>
