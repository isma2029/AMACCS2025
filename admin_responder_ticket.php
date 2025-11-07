<?php
session_start();
require_once 'clases/ticket.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit();
}

$ticketObj = new Ticket();

// Verificar que se envió ID de ticket
if (!isset($_GET['id'])) {
    header("Location: admin_tickets.php");
    exit();
}

$id_ticket = $_GET['id'];
$ticket = $ticketObj->obtenerPorId($id_ticket);

if (!$ticket) {
    echo "Ticket no encontrado.";
    exit();
}

// Procesar actualización de estado
if (isset($_POST['actualizar'])) {
    $nuevo_estado = $_POST['estado'];
    $comentario = $_POST['comentario']; // opcional, si quieres registrar
    $ticketObj->actualizarEstado($id_ticket, $nuevo_estado, $comentario ?? null);
    header("Location: admin_tickets.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Responder Ticket - Admin</title>
    <link rel="stylesheet" type="text/css" href="css/estilos.css">

</head>
<body>
    <h2>Responder Ticket</h2>

    <p><strong>ID Ticket:</strong> <?php echo $ticket['id_ticket']; ?></p>
    <p><strong>Docente:</strong> <?php echo $ticket['nombre_completo']; ?></p>
    <p><strong>Equipo:</strong> <?php echo $ticket['nombre_equipo']; ?> (Serie: <?php echo $ticket['numero_serie']; ?>)</p>
    <p><strong>Descripción:</strong> <?php echo $ticket['descripcion']; ?></p>
    <p><strong>Urgencia:</strong> <?php echo $ticket['urgencia']; ?></p>
    <p><strong>Categoría:</strong> <?php echo $ticket['categoria']; ?></p>
    <p><strong>Estado actual:</strong> <?php echo $ticket['estado']; ?></p>
    <p><strong>Fecha creación:</strong> <?php echo $ticket['fecha_creacion']; ?></p>
    <p><strong>Fecha cierre:</strong> <?php echo $ticket['fecha_cierre']; ?></p>
    <hr>

    <form method="POST">
        <label>Seleccionar estado:</label>
        <select name="estado" required>
            <option value="pendiente" <?php if($ticket['estado']=='pendiente') echo 'selected'; ?>>Pendiente</option>
            <option value="en proceso" <?php if($ticket['estado']=='en proceso') echo 'selected'; ?>>En Proceso</option>
            <option value="resuelto">Resuelto</option>
        </select>
        <br><br>
        <label>Comentario (opcional):</label><br>
        <textarea name="comentario" rows="4" cols="50" placeholder="Agrega un comentario"></textarea>
        <br><br>
        <input type="submit" name="actualizar" value="Actualizar Ticket">
    </form>

    <br>
    <a href="admin_tickets.php">Volver a Tickets</a>
</body>
</html>
