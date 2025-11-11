<?php
session_start();
require_once 'clases/solicitudsoftware.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'docente') {
    header("Location: index.php");
    exit();
}

$mensaje = "";

$solObj = new SolicitudSoftware();
$id_docente = $_SESSION['id_usuario'];

// Crear solicitud
if (isset($_POST['crear'])) {
    $nombre = $_POST['nombre_software'];
    $version = $_POST['version'];
    $solObj->crearSolicitud($id_docente, $nombre, $version);
    $mensaje = "Solicitud creada correctamente.";
}

// Listar solicitudes del docente
$solicitudes = $solObj->listarPorDocente($id_docente);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitar Software</title>
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

        h2, h3 {
            color: #333;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 500px;
            margin-bottom: 30px;
        }

        form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        form input[type="text"] {
            width: 100%;
            padding: 8px 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        form input[type="submit"] {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 10px 20px;
            margin-top: 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        form input[type="submit"]:hover {
            background-color: #45a049;
        }

        .mensaje {
            color: green;
            font-weight: bold;
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

<h2>Solicitar Software</h2>
<?php if(!empty($mensaje)) echo "<p class='mensaje'>$mensaje</p>"; ?>
<form method="POST">
    <label>Nombre del Software:</label>
    <input type="text" name="nombre_software" required>

    <label>Versión:</label>
    <input type="text" name="version" required>

    <input type="submit" name="crear" value="Solicitar">
</form>

<h3>Mis Solicitudes</h3>
<table>
    <tr>
        <th>ID</th>
        <th>Software</th>
        <th>Versión</th>
        <th>Estado</th>
        <th>Comentario Admin</th>
        <th>Fecha Solicitud</th>
    </tr>
    <?php foreach($solicitudes as $s) { ?>
    <tr>
        <td><?php echo $s['id_solicitud']; ?></td>
        <td><?php echo $s['nombre_software']; ?></td>
        <td><?php echo $s['version_solicitada']; ?></td>
        <td><?php echo $s['estado']; ?></td>
        <td><?php echo $s['comentario_admin']; ?></td>
        <td><?php echo $s['fecha_solicitud']; ?></td>
    </tr>
    <?php } ?>
</table>

</body>
</html>
