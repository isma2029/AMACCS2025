<?php
require_once 'clases/GestionUsuarios.php';
$gestion = new GestionUsuarios();

// Si se envió un formulario para agregar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre_completo'];
    $correo = $_POST['correo'];
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];
    $rol = $_POST['rol'];
    $mensaje = $gestion->agregarUsuario($nombre, $correo, $usuario, $contrasena, $rol);
}

$usuarios = $gestion->listarUsuarios();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <style>
        body { font-family: Arial; background-color: #f4f4f9; }
        h2 { text-align: center; color: #333; }
        table { width: 90%; margin: 20px auto; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #007bff; color: white; }
        form { width: 90%; margin: 20px auto; background: white; padding: 15px; border-radius: 6px; }
        input, select { padding: 6px; margin: 5px; width: 20%; }
        button { padding: 8px 15px; background: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background: #218838; }
    </style>
</head>
<body>
    <h2>Gestión de Usuarios</h2>

    <?php if (!empty($mensaje)): ?>
        <p style="color: red; text-align:center;"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="nombre_completo" placeholder="Nombre completo" required>
        <input type="email" name="correo" placeholder="Correo" required>
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="contrasena" placeholder="Contraseña" required>
        <select name="rol">
            <option value="docente">Docente</option>
            <option value="admin">Admin</option>
        </select>
        <button type="submit">Agregar Usuario</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Primer Inicio</th>
            <th>Creado</th>
        </tr>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= $u['id_usuario'] ?></td>
                <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
                <td><?= htmlspecialchars($u['correo']) ?></td>
                <td><?= htmlspecialchars($u['usuario']) ?></td>
                <td><?= htmlspecialchars($u['rol']) ?></td>
                <td><?= $u['primer_inicio'] ? 'Sí' : 'No' ?></td>
                <td><?= $u['created_at'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
