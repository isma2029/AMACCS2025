<?php
session_start();
require_once 'clases/usuario.php';
require_once 'clases/ticket.php';

// Verificar sesión y rol
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit();
}

$usuarioObj = new Usuario();
$ticketObj = new Ticket();

// Obtener lista de docentes para asignar tickets
$docentes = $usuarioObj->listarUsuariosPorRol('docente');

// Procesar asignación de ticket
if (isset($_POST['asignar_ticket'])) {
    $id_ticket = $_POST['id_ticket'];
    $id_docente = $_POST['id_docente'];

    if ($id_docente != "") {
        $ticketObj->asignarTicket($id_ticket, $id_docente); // método que debes tener en tu clase Ticket
        $mensaje = "Ticket asignado correctamente.";
    } else {
        $mensaje = "Selecciona un docente para asignar el ticket.";
    }
}

// Listar todos los tickets
$tickets = $ticketObj->listarTickets();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Todos los Tickets - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 20px;
        }

        a.button {
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

        select, input[type="submit"] {
            padding: 5px 8px;
            margin-top: 2px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .mensaje {
            color: green;
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<a href="dashboard.php" class="button">Volver al Dashboard</a>

<h2>Todos los Tickets</h2>

<?php if (isset($mensaje)) echo "<p class='mensaje'>$mensaje</p>"; ?>

<table>
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

    <?php foreach ($tickets as $t): ?>
    <tr>
        <td><?= $t['id_ticket'] ?></td>
        <td><?= $t['nombre_completo'] ?? 'No asignado' ?></td>
        <td><?= $t['nombre_equipo'] ?></td>
        <td><?= $t['numero_serie'] ?></td>
        <td><?= $t['descripcion'] ?></td>
        <td><?= $t['urgencia'] ?></td>
        <td><?= $t['categoria'] ?></td>
        <td><?= $t['estado'] ?></td>
        <td><?= $t['fecha_creacion'] ?></td>
        <td><?= $t['fecha_cierre'] ?? '-' ?></td>
        <td>
            <?php if($t['estado'] == 'pendiente'): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="id_ticket" value="<?= $t['id_ticket'] ?>">
                    <select name="id_docente" required>
                        <option value="">--Asignar Docente--</option>
                        <?php foreach($docentes as $d): ?>
                            <option value="<?= $d['id_usuario'] ?>"><?= $d['nombre_completo'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="submit" name="asignar_ticket" value="Asignar">
                </form>
            <?php else: ?>
                <?= $t['estado'] == 'resuelto' ? "Resuelto" : "En Proceso" ?>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
