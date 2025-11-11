<?php
// Habilitar visualización de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'clases/conexion.php';

// Obtener conexión a la base de datos
try {
    $conexion = Conexion::obtenerInstancia();
    $pdo = $conexion->getConexion();
    
    echo "<h2>Verificación de la tabla 'tickets'</h2>";
    
    // Verificar si la tabla tickets existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'tickets'");
    $tablaExiste = $stmt->rowCount() > 0;
    
    if ($tablaExiste) {
        echo "<div style='color: green;'>✅ La tabla 'tickets' existe en la base de datos.</div>";
        
        // Mostrar estructura de la tabla
        echo "<h3>Estructura de la tabla 'tickets':</h3>";
        $stmt = $pdo->query("DESCRIBE tickets");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Valor por defecto</th><th>Extra</th></tr>";
        foreach ($columnas as $columna) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($columna['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($columna['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($columna['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($columna['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($columna['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($columna['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Mostrar conteo de registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tickets");
        $total = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Total de registros en 'tickets': <strong>" . $total['total'] . "</strong></p>";
        
        // Mostrar algunos registros de ejemplo
        echo "<h3>Registros de ejemplo (máx. 5):</h3>";
        $stmt = $pdo->query("SELECT * FROM tickets ORDER BY id_ticket DESC LIMIT 5");
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($tickets)) {
            echo "<div>No hay registros en la tabla 'tickets'.</div>";
        } else {
            echo "<pre>" . htmlspecialchars(print_r($tickets, true)) . "</pre>";
        }
    } else {
        echo "<div style='color: red;'>❌ La tabla 'tickets' NO existe en la base de datos.</div>";
        
        // Mostrar script SQL para crear la tabla
        echo "<h3>Script SQL para crear la tabla 'tickets':</h3>";
        echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
        echo htmlspecialchars(
"CREATE TABLE IF NOT EXISTS `tickets` (
  `id_ticket` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_equipo` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('pendiente','en progreso','resuelto','cerrado') NOT NULL DEFAULT 'pendiente',
  `prioridad` enum('baja','media','alta') NOT NULL DEFAULT 'media',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `asignado_a` int(11) DEFAULT NULL,
  `solucion` text DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_ticket`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_equipo` (`id_equipo`),
  KEY `asignado_a` (`asignado_a`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        echo "</pre>";
        
        echo "<p>Puedes ejecutar este script en phpMyAdmin o en la consola de MySQL para crear la tabla.</p>";
    }
    
    // Verificar si la tabla ticket_historial existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'ticket_historial'");
    $historialExiste = $stmt->rowCount() > 0;
    
    if ($historialExiste) {
        echo "<div style='color: green; margin-top: 20px;'>✅ La tabla 'ticket_historial' existe en la base de datos.</div>";
    } else {
        echo "<div style='color: orange; margin-top: 20px;'>⚠️ La tabla 'ticket_historial' NO existe. Es recomendable crearla para el historial de cambios.</div>";
        
        // Mostrar script SQL para crear la tabla de historial
        echo "<h3>Script SQL para crear la tabla 'ticket_historial':</h3>";
        echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
        echo htmlspecialchars(
"CREATE TABLE IF NOT EXISTS `ticket_historial` (
  `id_historial` int(11) NOT NULL AUTO_INCREMENT,
  `id_ticket` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `accion` varchar(50) NOT NULL,
  `detalle` text DEFAULT NULL,
  PRIMARY KEY (`id_historial`),
  KEY `id_ticket` (`id_ticket`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
        echo "</pre>";
    }
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>Error al conectar a la base de datos: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Mostrar información del servidor
echo "<h3>Información del servidor:</h3>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "PDO Drivers: " . print_r(PDO::getAvailableDrivers(), true) . "\n";

try {
    $version = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    echo "MySQL Version: " . $version . "\n";
} catch (Exception $e) {
    echo "No se pudo obtener la versión de MySQL: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
