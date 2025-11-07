<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$nombre = $_SESSION['nombre'];
$rol = $_SESSION['rol'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Soporte Local</title>
    <link rel="stylesheet" type="text/css" href="css/estilos.css">

</head>
<body>
    <h2>Bienvenido, <?php echo $nombre; ?>!</h2>
    <p>Tu rol: <?php echo $rol; ?></p>
    <hr>

    <?php if ($rol == 'docente') { ?>
        <ul>
            <li><a href="solicitar_software.php">Solicitar Software</a></li>
            <li><a href="mis_tickets.php">Mis Tickets</a></li>
        </ul>
    <?php } elseif ($rol == 'admin') { ?>
        <ul>
            <li><a href="admin_solicitudes.php">Ver Solicitudes de Software</a></li>
            <li><a href="admin_tickets.php">Ver Tickets</a></li>
            <li><a href="gestion_equipos.php">Gestionar Equipos</a></li>
            <li><a href="usuarios.php">Gestionar Usuarios</a></li>
        </ul>
    <?php } ?>

    <hr>
    <a href="logout.php">Cerrar Sesión</a>
</body>
</html>
