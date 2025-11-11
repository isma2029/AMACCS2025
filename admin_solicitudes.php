<?php
session_start();
require_once 'clases/solicitudsoftware.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit();
}

$solObj = new SolicitudSoftware();
$solicitudes = $solObj->listarSolicitudes();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Solicitudes de Software</title>
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
            background-color: #007BFF;
            padding: 8px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: inline-block;
        }

        h2 {
            color: #333;
            margin-bottom: 15px;
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
            background-color: #007BFF;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #d6eaff;
        }

        a.responder {
            background-color: #28a745;
            padding: 5px 10px;
            border-radius: 5px;
        }

        a.responder:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<a href="dashboard.php">Volver al Dashboard</a>

<h2>Gestión de Solicitudes de Software</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Docente</th>
        <th>Software</th>
        <th>Versión</th>
        <th>Estado</th>
        <th>Comentario Admin</th>
        <th>Fecha Solicitud</th>
        <th>Acciones</th>
    </tr>
    <?php foreach($solicitudes as $s) { ?>
    <tr>
        <td><?php echo $s['id_solicitud']; ?></td>
        <td><?php echo $s['nombre_completo']; ?></td>
        <td><?php echo $s['nombre_software']; ?></td>
        <td><?php echo $s['version_solicitada']; ?></td>
        <td><?php echo ucfirst($s['estado']); ?></td>
        <td><?php echo $s['comentario_admin'] ?? '-'; ?></td>
        <td><?php echo $s['fecha_solicitud']; ?></td>
        <td>
            <?php if($s['estado'] == 'pendiente') { ?>
                <a class="responder" href="admin_responder.php?id=<?php echo $s['id_solicitud']; ?>">Responder</a>
            <?php } else { 
                echo "Procesado"; 
            } ?>
        </td>
    </tr>
    <?php } ?>
</table>

</body>
</html>
