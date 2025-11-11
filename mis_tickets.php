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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Tickets</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 20px;
        }

        a {
            text-decoration: none;
            color: #fff;
            background-color: #4CAF50;
            padding: 8px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: inline-block;
        }

        h2 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
        }

        table th {
            background-color: #4CAF50;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #e0f7e0;
        }
    </style>
</head>
<body>

<a href="dashboard.php">Volver al Dashboard</a>

<h2>Mis Tickets</h2>
<table>
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
        <td><?php echo $t['fecha_cierre'] ?? '-'; ?></td>
    </tr>
    <?php } ?>
</table>

</body>
</html>
