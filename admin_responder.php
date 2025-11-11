<?php
session_start();
require_once 'clases/solicitudsoftware.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit();
}

$solObj = new SolicitudSoftware();

if (!isset($_GET['id'])) {
    header("Location: admin_solicitudes.php");
    exit();
}

$id_solicitud = $_GET['id'];
$solicitud = $solObj->obtenerSolicitud($id_solicitud);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_estado = $_POST['accion'];
    $comentario = $_POST['comentario'] ?? '';
    
    $solObj->actualizarEstado($id_solicitud, $nuevo_estado, $comentario);
    header("Location: admin_solicitudes.php"); // redirige después de actualizar
    exit();
}
?>

<h2>Responder Solicitud de Software</h2>
<p><strong>ID Solicitud:</strong> <?php echo $solicitud['id_solicitud']; ?></p>
<p><strong>Docente:</strong> <?php echo $solicitud['nombre_completo']; ?></p>
<p><strong>Software solicitado:</strong> <?php echo $solicitud['nombre_software']; ?></p>
<p><strong>Versión:</strong> <?php echo $solicitud['version_solicitada']; ?></p>
<p><strong>Estado actual:</strong> <?php echo $solicitud['estado']; ?></p>

<form method="post">
    <label>Seleccionar acción:</label>
    <select name="accion" required>
        <option value="aceptado">Aceptar</option>
        <option value="rechazado">Rechazar</option>
    </select>
    <br><br>
    <label>Comentario (opcional):</label><br>
    <textarea name="comentario" rows="4" cols="50"></textarea>
    <br><br>
    <button type="submit">Enviar</button>
</form>

<br>
<a href="admin_solicitudes.php">Volver a Solicitudes</a>
