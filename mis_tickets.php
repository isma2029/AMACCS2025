<?php
session_start();
require_once 'clases/TicketManager.php';
require_once 'clases/conexion.php';

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$ticketManager = new TicketManager();
$id_usuario = $_SESSION['usuario']['id_usuario'] ?? null;

// Obtener tickets del usuario
$mis_tickets = $ticketManager->listarTicketsPorUsuario($id_usuario);

// Mensajes de éxito/error
$mensaje = '';
if (isset($_GET['created']) && $_GET['created'] == 1) {
    $mensaje = '<div class="alert alert-success">Ticket creado exitosamente!</div>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Tickets de Soporte</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --secondary-color: #6c757d;
        }
        
        body {
            background-color: #f8f9fa;
            padding: 2rem 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0,0,0,.125);
            font-weight: 600;
            padding: 1rem 1.25rem;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.4em 0.8em;
            font-size: 0.8em;
            border-radius: 50rem;
        }
        
        .badge-pendiente {
            background-color: #f39c12;
            color: #fff;
        }
        
        .badge-en-progreso {
            background-color: #3498db;
            color: #fff;
        }
        
        .badge-resuelto {
            background-color: #2ecc71;
            color: #fff;
        }
        
        .badge-cerrado {
            background-color: #7f8c8d;
            color: #fff;
        }
        
        .ticket-priority {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .priority-alta { background-color: #e74c3c; }
        .priority-media { background-color: #f39c12; }
        .priority-baja { background-color: #2ecc71; }
        
        .ticket-card {
            border-left: 4px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .ticket-card:hover {
            border-left-color: var(--primary-color);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        
        .ticket-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .ticket-meta {
            font-size: 0.85rem;
            color: #7f8c8d;
        }
        
        .ticket-desc {
            color: #34495e;
            font-size: 0.9rem;
            line-height: 1.5;
            margin: 0.5rem 0;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
        
        .btn-new-ticket {
            background-color: var(--primary-color);
            border: none;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
        }
        
        .btn-new-ticket:hover {
            background-color: #3a56d4;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
        }

        table th {
            background-color: #4CAF50;
            color: white;
        }
        
        .badge-urgencia {
            text-transform: capitalize;
        }
        
        .ticket-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
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
