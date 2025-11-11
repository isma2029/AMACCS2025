<?php
require_once 'clases/conexion.php';

try {
    // Obtener instancia de la conexión
    $conexion = Conexion::obtenerInstancia();
    $pdo = $conexion->getConexion();
    
    echo "<h2>Verificación de Base de Datos</h2>";
    
    // Verificar si la base de datos existe
    $stmt = $pdo->query("SELECT DATABASE() AS db");
    $db = $stmt->fetch();
    echo "<p>Base de datos actual: <strong>" . htmlspecialchars($db['db']) . "</strong></p>";
    
    // Verificar tablas existentes
    echo "<h3>Tablas en la base de datos:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<div style='color: red;'>No se encontraron tablas en la base de datos.</div>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
        
        // Verificar estructura de la tabla tickets si existe
        if (in_array('tickets', $tables)) {
            echo "<h3>Estructura de la tabla 'tickets':</h3>";
            $stmt = $pdo->query("DESCRIBE tickets");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($columns)) {
                echo "<div style='color: red;'>No se pudo obtener la estructura de la tabla 'tickets'.</div>";
            } else {
                echo "<table border='1' cellpadding='5' cellspacing='0'>";
                echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Valor por defecto</th><th>Extra</th></tr>";
                foreach ($columns as $column) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
                    echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
                    echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
                    echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
                    echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
                    echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                // Mostrar algunos registros de ejemplo
                echo "<h3>Registros de ejemplo en 'tickets':</h3>";
                $stmt = $pdo->query("SELECT * FROM tickets LIMIT 5");
                $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($tickets)) {
                    echo "<div>No hay registros en la tabla 'tickets'.</div>";
                } else {
                    echo "<pre>" . htmlspecialchars(print_r($tickets, true)) . "</pre>";
                }
            }
        } else {
            echo "<div style='color: red;'>La tabla 'tickets' no existe en la base de datos.</div>";
        }
    }
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>Error al conectar a la base de datos: " . htmlspecialchars($e->getMessage()) . "</div>";
    
    // Mostrar información de depuración
    echo "<h3>Información de depuración:</h3>";
    echo "<pre>Error: " . htmlspecialchars($e->getMessage()) . "\n";
    echo "Código: " . $e->getCode() . "\n";
    echo "Archivo: " . $e->getFile() . " (Línea: " . $e->getLine() . ")\n";
    echo "Trace:\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Mostrar información del servidor
echo "<h3>Información del servidor:</h3>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "MySQL Client: " . (function_exists('mysqli_get_client_info') ? mysqli_get_client_info() : 'No disponible') . "\n";
echo "PDO Drivers: " . print_r(PDO::getAvailableDrivers(), true) . "\n";
echo "</pre>";
?>
