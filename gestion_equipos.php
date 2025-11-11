<?php
session_start();
require_once 'clases/equipo.php';
require_once 'clases/conexion.php';

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$equipoObj = new Equipo();
$mensaje = '';
$tipoMensaje = '';

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Crear equipo
if (isset($_POST['accion']) && $_POST['accion'] === 'crear' && isset($_POST['csrf_token'])) {
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $nombre = trim($_POST['nombre_equipo']);
        $serie = trim($_POST['numero_serie']);
        $ubicacion = trim($_POST['ubicacion']);
        $estado = $_POST['estado'];
        $tipo = $_POST['tipo_equipo'];
        $marca = trim($_POST['marca']);
        $modelo = trim($_POST['modelo']);
        $especificaciones = trim($_POST['especificaciones']);
        
        if ($equipoObj->crearEquipo($nombre, $serie, $ubicacion, $estado, $tipo, $marca, $modelo, $especificaciones)) {
            $mensaje = 'Equipo creado correctamente';
            $tipoMensaje = 'success';
        } else {
            $mensaje = 'Error al crear el equipo';
            $tipoMensaje = 'danger';
        }
    } else {
        $mensaje = 'Token de seguridad inválido';
        $tipoMensaje = 'danger';
    }
}

// Actualizar equipo
if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar' && isset($_POST['csrf_token'])) {
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $id = (int)$_POST['id_equipo'];
        $nombre = trim($_POST['nombre_equipo']);
        $serie = trim($_POST['numero_serie']);
        $ubicacion = trim($_POST['ubicacion']);
        $estado = $_POST['estado'];
        $tipo = $_POST['tipo_equipo'];
        $marca = trim($_POST['marca']);
        $modelo = trim($_POST['modelo']);
        $especificaciones = trim($_POST['especificaciones']);
        
        if ($equipoObj->actualizarEquipo($id, $nombre, $serie, $ubicacion, $estado, $tipo, $marca, $modelo, $especificaciones)) {
            $mensaje = 'Equipo actualizado correctamente';
            $tipoMensaje = 'success';
        } else {
            $mensaje = 'Error al actualizar el equipo';
            $tipoMensaje = 'danger';
        }
    } else {
        $mensaje = 'Token de seguridad inválido';
        $tipoMensaje = 'danger';
    }
}

// Eliminar equipo
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    if (isset($_GET['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        $id = (int)$_GET['id'];
        if ($equipoObj->eliminarEquipo($id)) {
            $mensaje = 'Equipo eliminado correctamente';
            $tipoMensaje = 'success';
        } else {
            $mensaje = 'Error al eliminar el equipo';
            $tipoMensaje = 'danger';
        }
    } else {
        $mensaje = 'Token de seguridad inválido';
        $tipoMensaje = 'danger';
    }
}

// Obtener datos para edición
$equipoEditar = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $equipoEditar = $equipoObj->obtenerPorId((int)$_GET['editar']);
    if (!$equipoEditar) {
        $mensaje = 'Equipo no encontrado';
        $tipoMensaje = 'warning';
    }
}

// Listar equipos
$equipos = $equipoObj->listarEquipos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Equipos - Sistema de Soporte Técnico</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
            border-radius: 10px 10px 0 0 !important;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-edit {
            color: #0d6efd;
            padding: 0.25rem 0.5rem;
        }
        .btn-delete {
            color: #dc3545;
            padding: 0.25rem 0.5rem;
        }
        .table th {
            background-color: #f1f8ff;
        }
        .badge-activo {
            background-color: #198754;
        }
        .badge-inactivo {
            background-color: #6c757d;
        }
        .badge-mantenimiento {
            background-color: #ffc107;
            color: #000;
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

        a.eliminar {
            background-color: #dc3545;
            padding: 5px 10px;
            border-radius: 5px;
            color: #fff;
        }

        a.eliminar:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<a href="dashboard.php">Volver al Dashboard</a>

<h2>Gestión de Equipos</h2>

<h3>Crear Nuevo Equipo</h3>
<form method="POST">
    <label>Nombre del Equipo:</label>
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

    <input type="submit" name="crear" value="Crear Equipo">
</form>

<h3>Lista de Equipos</h3>
<table>
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
        <td><?php echo ucfirst($e['estado']); ?></td>
        <td>
            <a class="eliminar" href="?eliminar=<?php echo $e['id_equipo']; ?>" onclick="return confirm('¿Eliminar este equipo?');">Eliminar</a>
        </td>
    </tr>
    <?php } ?>
</table>

</body>
</html>
