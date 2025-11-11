<?php
require_once 'clases/GestionUsuarios.php';
$gestion = new GestionUsuarios();

// Listar usuarios
$usuarios = $gestion->listarUsuarios();

// Agregar usuario (ejemplo)
// $gestion->agregarUsuario("juan123", "Juan Pérez", "12345", "ventas");

// Actualizar usuario (ejemplo)
// $gestion->actualizarUsuario(3, "Juan P. Actualizado", "admin");

// Eliminar usuario (ejemplo)
// $gestion->eliminarUsuario(5);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <a href="dashboard.php">Volver al Dashboard</a>

    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <style>
        table { border-collapse: collapse; width: 70%; margin: 20px auto; }
        th, td { padding: 8px 12px; border: 1px solid #ccc; text-align: center; }
        th { background-color: #f3f3f3; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Lista de Usuarios</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Nombre</th>
                <th>Rol</th>
                <th>Primer Inicio</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= $u['id_usuario'] ?></td>
                    <td><?= $u['usuario'] ?></td>
                    <td><?= $u['nombre_completo'] ?></td>
                    <td><?= $u['rol'] ?></td>
                    <td><?= $u['primer_inicio'] ? 'Sí' : 'No' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
