<?php
session_start();
require_once 'clases/solicitudsoftware.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'docente') {
    header("Location: index.php");
    exit();
}

$solObj = new SolicitudSoftware();
$mensaje = "";

// Crear solicitud
if (isset($_POST['crear'])) {
    $id_docente = $_SESSION['id_usuario'];
    $nombre = $_POST['nombre_software'];
    $version = $_POST['version'];
    $solObj->crearSolicitud($id_docente, $nombre, $version);
    $mensaje = "Solicitud creada correctamente.";
}

// Listar solicitudes del docente
$solicitudes = $solObj->listarPorDocente($_SESSION['id_usuario']);
?>

<h2>Solicitar Software</h2>
<?php if($mensaje != "") echo "<p style='color:green;'>$mensaje</p>"; ?>
<form method="POST">
    <label>Nombre del Software:</label>
    <input type="text" name="nombre_software" required>
    <br><br>
    <label>Versión:</label>
    <input type="text" name="version" required>
    <br><br>
    <input type="submit" name="crear" value="Solicitar">
</form>

<h3>Mis Solicitudes</h3>
<table border="1">
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
