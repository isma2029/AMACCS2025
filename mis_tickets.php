<?php
session_start();
require_once 'clases/ticket.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'docente') {
    header("Location: index.php");
    exit();
}

$ticketObj = new Ticket();
$id_usuario = $_SESSION['id_usuario'];
$mis_tickets = $ticketObj->listarTicketsPorUsuario($id_usuario);
?>

<a href="dashboard.php">Volver al Dashboard</a>


<h2>Mis Tickets</h2>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Equipo</th>
        <th>Serie</th>
        <th>Descripción</th>
        <th>Urgencia</th>
        <th>Categoría</th>
        <th>Estado</th>
        <th>Fecha Creación</th>
        <th>Fecha Cierre</th>
    </tr>
    <?php foreach ($mis_tickets as $t) { ?>
    <tr>
        <td><?php echo $t['id_ticket']; ?></td>
        <td><?php echo $t['nombre_equipo']; ?></td>
        <td><?php echo $t['numero_serie']; ?></td>
        <td><?php echo $t['descripcion']; ?></td>
        <td><?php echo $t['urgencia']; ?></td>
        <td><?php echo $t['categoria']; ?></td>
        <td><?php echo $t['estado']; ?></td>
        <td><?php echo $t['fecha_creacion']; ?></td>
        <td><?php echo $t['fecha_cierre']; ?></td>
    </tr>
    <?php } ?>
</table>
