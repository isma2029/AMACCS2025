<?php
session_start();
require_once 'clases/TicketManager.php';
require_once 'clases/GestionUsuarios.php';
require_once 'clases/SoftwareManager.php';

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['usuario']['id_usuario'] ?? null;
if (!$user_id) {
    header('Location: index.php');
    exit;
}

// solo docentes o admins pueden crear
$tm = new TicketManager();

// Buscar equipos para select
require_once 'clases/EquipoManager.php'; // (opcional) o consulta directa
// Por simplicidad: obtener lista de equipos:
$pdo = Conexion::obtenerInstancia()->getConexion();
$equipos = $pdo->query("SELECT id_equipo, numero_serie, nombre_equipo FROM equipos")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_equipo = $_POST['id_equipo'] ?: null; // permitir nulo
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $urgencia = $_POST['urgencia'];
    $categoria = $_POST['categoria'];
    $id_ticket = $tm->crearTicket($_SESSION['user_id'], $id_equipo, $titulo, $descripcion, $urgencia, $categoria);
    header("Location: mis_tickets.php?created=1");
    exit;
}
?>
<!-- simple HTML form -->
<form method="POST">
    <label>Equipo (selecciona o deja vacío)</label>
    <select name="id_equipo">
        <option value="">-- No especificar --</option>
        <?php foreach($equipos as $e): ?>
            <option value="<?= $e['id_equipo'] ?>"><?= htmlspecialchars($e['numero_serie']) ?> - <?= htmlspecialchars($e['nombre_equipo']) ?></option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="titulo" placeholder="Título" required>
    <textarea name="descripcion" placeholder="Describe la falla..." required></textarea>
    <select name="urgencia">
        <option value="baja">Baja</option>
        <option value="media" selected>Media</option>
        <option value="alta">Alta</option>
    </select>
    <select name="categoria">
        <option value="hardware">Hardware</option>
        <option value="software">Software</option>
        <option value="otro">Otro</option>
    </select>
    <button type="submit">Generar Ticket</button>
</form>
