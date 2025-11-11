<?php
session_start();
require_once 'clases/equipo.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit();
}

$equipoObj = new Equipo();

// Crear equipo
if (isset($_POST['crear'])) {
    $nombre = $_POST['nombre_equipo'];
    $serie = $_POST['numero_serie'];
    $ubicacion = $_POST['ubicacion'];
    $estado = $_POST['estado'];
    $equipoObj->crearEquipo($nombre, $serie, $ubicacion, $estado);
}

// Actualizar equipo
if (isset($_POST['actualizar'])) {
    $id = $_POST['id_equipo'];
    $nombre = $_POST['nombre_equipo'];
    $serie = $_POST['numero_serie'];
    $ubicacion = $_POST['ubicacion'];
    $estado = $_POST['estado'];
    $equipoObj->actualizarEquipo($id, $nombre, $serie, $ubicacion, $estado);
}

// Eliminar equipo
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $equipoObj->eliminarEquipo($id);
}

// Listar equipos
$equipos = $equipoObj->listarEquipos();
?>
<a href="dashboard.php">Volver al Dashboard</a>

<h2>Gestión de Equipos</h2>

<h3>Crear Nuevo Equipo</h3>
<form method="POST">
    <label>Nombre:</label>
    <input type="text" name="nombre_equipo" required>
    <label>Número de Serie:</label>
    <input type="text" name="numero_serie" required>
    <label>Ubicación:</label>
    <input type="text" name="ubicacion">
    <label>Estado:</label>
    <select name="estado">
        <option value="activo">Activo</option>
        <option value="en reparacion">En reparación</option>
        <option value="fuera de servicio">Fuera de servicio</option>
    </select>
    <input type="submit" name="crear" value="Crear">
</form>

<hr>

<h3>Lista de Equipos</h3>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Serie</th>
        <th>Ubicación</th>
        <th>Estado</th>
        <th>Acciones</th>
    </tr>
    <?php foreach ($equipos as $e) { ?>
    <tr>
        <td><?php echo $e['id_equipo']; ?></td>
        <td><?php echo $e['nombre_equipo']; ?></td>
        <td><?php echo $e['numero_serie']; ?></td>
        <td><?php echo $e['ubicacion']; ?></td>
        <td><?php echo $e['estado']; ?></td>
        <td>
            <!-- Para simplificar, edición se puede hacer con un formulario emergente o similar -->
            <a href="?eliminar=<?php echo $e['id_equipo']; ?>" onclick="return confirm('¿Eliminar este equipo?');">Eliminar</a>
        </td>
    </tr>
    <?php } ?>
</table>
