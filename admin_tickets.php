<?php
session_start();
require_once 'clases/ticket.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit();
}

$ticketObj = new Ticket();
$tickets = $ticketObj->listarTickets();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Todos los Tickets - Admin</title>
    <link rel="stylesheet" type="text/css" href="css/estilos.css">

</head>
<body>
<h2>Todos los Tickets</h2>

<table border="1">
    <tr>
        <th>ID</th>
        <th>Docente</th>
        <th>Equipo</th>
        <th>Serie</th>
        <th>Descripción</th>
        <th>Urgencia</th>
        <th>Categoría</th>
        <th>Estado</th>
        <th>Fecha Creación</th>
        <th>Fecha Cierre</th>
        <th>Acciones</th>
    </tr>

    <?php foreach ($tickets as $t) { ?>
    <tr>
        <td><?php echo $t['id_ticket']; ?></td>
        <td><?php echo $t['nombre_completo']; ?></td>
        <td><?php echo $t['nombre_equipo']; ?></td>
        <td><?php echo $t['numero_serie']; ?></td>
        <td><?php echo $t['descripcion']; ?></td>
        <td><?php echo $t['urgencia']; ?></td>
        <td><?php echo $t['categoria']; ?></td>
        <td><?php echo $t['estado']; ?></td>
        <td><?php echo $t['fecha_creacion']; ?></td>
        <td><?php echo $t['fecha_cierre']; ?></td>
        <td>
            <?php if($t['estado'] != 'resuelto') { ?>
                <!-- Botón Responder -->
                <a href="admin_responder_ticket.php?id=<?php echo $t['id_ticket']; ?>">Responder</a>
                <!-- Botón Marcar Resuelto -->
                | <a href="admin_ticket_resolver.php?id=<?php echo $t['id_ticket']; ?>&accion=resuelto">Marcar Resuelto</a>
            <?php } else { 
                echo "Resuelto"; 
            } ?>
        </td>
    </tr>
    <?php } ?>
</table>

<br>
<a href="dashboard.php">Volver al Dashboard</a>
</body>
</html>
