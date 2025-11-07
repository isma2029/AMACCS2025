<?php
session_start();
require_once 'clases/solicitudsoftware.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit();
}

$solObj = new SolicitudSoftware();

// Verificar que se envió ID de solicitud
if (!isset($_GET['id'])) {
    header("Location: admin_solicitudes.php");
    exit();
}

$id_solicitud = $_GET['id'];
$solicitud = $solObj->obtenerPorId($id_solicitud);

if (!$solicitud) {
    echo "Solicitud no encontrada.";
    exit();
}

// Procesar respuesta
if (isset($_POST['responder'])) {
    $nuevo_estado = $_POST['estado'];
    $comentario = $_POST['comentario'];
    $solObj->actualizarEstado($id_solicitud, $nuevo_estado, $comentario);
    header("Location: admin_solicitudes.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Responder Solicitud - Admin</title>
    <link rel="stylesheet" type="text/css" href="css/estilos.css">

</head>
<body>
    <h2>Responder Solicitud de Software</h2>

    <p><strong>ID Solicitud:</strong> <?php echo $solicitud['id_solicitud']; ?></p>
    <p><strong>Docente:</strong> <?php echo $solicitud['nombre_completo']; ?></p>
    <p><strong>Software solicitado:</strong> <?php echo $solicitud['nombre_software']; ?></p>
    <p><strong>Versión:</strong> <?php echo $solicitud['version_solicitada']; ?></p>
    <p><strong>Estado actual:</strong> <?php echo $solicitud['estado']; ?></p>
    <p><strong>Comentario admin:</strong> <?php echo $solicitud['comentario_admin']; ?></p>
    <hr>

    <form method="POST">
        <label>Seleccionar acción:</label>
        <select name="estado" required>
            <option value="aceptado">Aceptar</option>
            <option value="rechazado">Rechazar</option>
        </select>
        <br><br>
        <label>Comentario (opcional):</label><br>
        <textarea name="comentario" rows="4" cols="50" placeholder="Agrega un comentario"></textarea>
        <br><br>
        <input type="submit" name="responder" value="Enviar Respuesta">
    </form>

    <br>
    <a href="admin_solicitudes.php">Volver a Solicitudes</a>
</body>
</html>
