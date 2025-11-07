<?php
session_start();
require_once 'clases/solicitudsoftware.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit();
}

$solObj = new SolicitudSoftware();

// Actualizar estado desde este listado (opcional)
if (isset($_POST['cambiar_estado'])) {
    $id = $_POST['id_solicitud'];
    $estado = $_POST['nuevo_estado'];
    $comentario = $_POST['comentario'];
    $solObj->actualizarEstado($id, $estado, $comentario);
}

$solicitudes = $solObj->listarSolicitudes();
?>

<h2>Gestión de Solicitudes de Software</h2>

<table border="1">
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
        <td><?php echo $s['estado']; ?></td>
        <td><?php echo $s['comentario_admin']; ?></td>
        <td><?php echo $s['fecha_solicitud']; ?></td>
        <td>
            <?php if($s['estado'] == 'pendiente') { ?>
                <!-- Botón para abrir admin_responder.php -->
                <a href="admin_responder.php?id=<?php echo $s['id_solicitud']; ?>">Responder</a>
            <?php } else { 
                echo "Procesado"; 
            } ?>
        </td>
    </tr>
    <?php } ?>
</table>

<br>
<a href="dashboard.php">Volver al Dashboard</a>
