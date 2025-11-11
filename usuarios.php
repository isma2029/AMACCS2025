<?php
session_start();
require_once 'clases/GestionUsuarios.php';
require_once 'clases/conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$gestion = new GestionUsuarios();
$mensaje = '';
$tipoMensaje = '';

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Procesar eliminación de usuario
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    if (isset($_GET['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        $id = (int)$_GET['id'];
        if ($id !== $_SESSION['id_usuario']) { // Prevenir auto-eliminación
            $resultado = $gestion->eliminarUsuario($id);
            if ($resultado === true) {
                $mensaje = 'Usuario eliminado correctamente';
                $tipoMensaje = 'success';
            } else {
                $mensaje = $resultado;
                $tipoMensaje = 'danger';
            }
        } else {
            $mensaje = 'No puedes eliminar tu propio usuario';
            $tipoMensaje = 'warning';
        }
    } else {
        $mensaje = 'Token de seguridad inválido';
        $tipoMensaje = 'danger';
    }
}

// Obtener datos para edición
$usuarioEditar = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $usuarioEditar = $gestion->obtenerUsuarioPorId((int)$_GET['editar']);
    if (!$usuarioEditar) {
        $mensaje = 'Usuario no encontrado';
        $tipoMensaje = 'warning';
    }
}

// Procesar creación/actualización de usuarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && 
    hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    
    $id = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
    $nombre = trim($_POST['nombre_completo']);
    $correo = trim($_POST['correo']);
    $usuario = trim($_POST['usuario']);
    $rol = $_POST['rol'] ?? 'docente';
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
    
    // Validaciones básicas
    if (empty($nombre) || empty($correo) || empty($usuario)) {
        $mensaje = 'Todos los campos son obligatorios';
        $tipoMensaje = 'danger';
    } 
    // Si es un nuevo usuario o se está cambiando la contraseña
    elseif (($id === 0 || !empty($contrasena)) && (empty($contrasena) || strlen($contrasena) < 6)) {
        $mensaje = 'La contraseña debe tener al menos 6 caracteres';
        $tipoMensaje = 'danger';
    }
    elseif (!empty($contrasena) && $contrasena !== $confirmar_contrasena) {
        $mensaje = 'Las contraseñas no coinciden';
        $tipoMensaje = 'danger';
    } 
    else {
        if ($id > 0) {
            // Actualizar usuario existente
            $resultado = $gestion->actualizarUsuario(
                $id, 
                $nombre, 
                $correo, 
                $rol,
                !empty($contrasena) ? $contrasena : null
            );
            
            if ($resultado === true) {
                $mensaje = 'Usuario actualizado correctamente';
                $tipoMensaje = 'success';
                // Si es el usuario actual, actualizar datos de sesión
                if ($id == $_SESSION['id_usuario']) {
                    $_SESSION['nombre'] = $nombre;
                    $_SESSION['usuario'] = $usuario;
                }
                $usuarioEditar = null; // Limpiar formulario de edición
            } else {
                $mensaje = 'Error al actualizar el usuario';
                $tipoMensaje = 'danger';
            }
        } else {
            // Crear nuevo usuario
            $resultado = $gestion->agregarUsuario($nombre, $correo, $usuario, $contrasena, $rol);
            
            if ($resultado === true) {
                $mensaje = 'Usuario creado correctamente';
                $tipoMensaje = 'success';
                // Limpiar el formulario
                $_POST = [];
            } else {
                $mensaje = $resultado; // Mostrar mensaje de error de la función
                $tipoMensaje = 'danger';
            }
            // Actualizar usuario existente
            $resultado = $gestion->actualizarUsuario($id, $nombre, $correo, $rol);
            if ($resultado === true) {
                $mensaje = 'Usuario actualizado correctamente';
                $tipoMensaje = 'success';
                // Si es el usuario actual, actualizar datos de sesión
                if ($id == $_SESSION['id_usuario']) {
                    $_SESSION['nombre'] = $nombre;
                    $_SESSION['usuario'] = $usuario;
                }
                $usuarioEditar = null; // Limpiar formulario de edición
            } else {
                $mensaje = $resultado;
                $tipoMensaje = 'danger';
            }
    }
}

// Obtener lista de usuarios
$usuarios = $gestion->listarUsuarios();
$usuarioActual = $gestion->obtenerUsuarioPorId($_SESSION['id_usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Sistema de Soporte Técnico</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 2rem;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
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
        .badge-admin {
            background-color: #6610f2;
        }
        .badge-docente {
            background-color: #198754;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-center mb-4">
                    <i class="fas fa-users-cog me-2"></i>Gestión de Usuarios
                </h2>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver al panel
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#usuarioModal">
                        <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
                    </button>
                </div>
            </div>
        </div>

        <?php if ($mensaje): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($mensaje) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Lista de Usuarios -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list me-2"></i>Lista de Usuarios
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Usuario</th>
                                        <th>Correo</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($usuarios)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No hay usuarios registrados</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($usuarios as $u): ?>
                                            <tr>
                                                <td><?= $u['id_usuario'] ?></td>
                                                <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
                                                <td><?= htmlspecialchars($u['usuario']) ?></td>
                                                <td><?= htmlspecialchars($u['correo']) ?></td>
                                                <td>
                                                    <span class="badge rounded-pill <?= $u['rol'] === 'admin' ? 'bg-primary' : 'bg-success' ?>">
                                                        <?= ucfirst(htmlspecialchars($u['rol'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($u['primer_inicio']): ?>
                                                        <span class="badge bg-warning text-dark">Pendiente activación</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Activo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="?editar=<?= $u['id_usuario'] ?>" class="btn btn-sm btn-outline-primary me-1" 
                                                       data-bs-toggle="tooltip" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($u['id_usuario'] != $_SESSION['id_usuario']): ?>
                                                        <a href="#" class="btn btn-sm btn-outline-danger" 
                                                           onclick="confirmarEliminar(<?= $u['id_usuario'] ?>, '<?= addslashes(htmlspecialchars($u['nombre_completo'])) ?>')"
                                                           data-bs-toggle="tooltip" title="Eliminar">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted" data-bs-toggle="tooltip" title="No puedes eliminarte a ti mismo">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Edición -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-<?= $usuarioEditar ? 'edit' : 'user-plus' ?> me-2"></i>
                        <?= $usuarioEditar ? 'Editar Usuario' : 'Nuevo Usuario' ?>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="usuarioForm">
                            <input type="hidden" name="id_usuario" id="id_usuario" 
                                   value="<?= $usuarioEditar ? $usuarioEditar['id_usuario'] : '' ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <div class="mb-3">
                                <label for="nombre_completo" class="form-label">Nombre completo</label>
                                <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" 
                                       value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar['nombre_completo']) : '' ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Nombre de usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" 
                                       value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar['usuario']) : '' ?>" 
                                       <?= $usuarioEditar ? '' : 'required' ?>>
                            </div>
                            
                            <div class="mb-3">
                                <label for="correo" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo" 
                                       value="<?= $usuarioEditar ? htmlspecialchars($usuarioEditar['correo']) : '' ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="rol" class="form-label">Rol</label>
                                <select class="form-select" id="rol" name="rol" required>
                                    <option value="docente" <?= ($usuarioEditar && $usuarioEditar['rol'] === 'docente') ? 'selected' : '' ?>>Docente</option>
                                    <option value="admin" <?= ($usuarioEditar && $usuarioEditar['rol'] === 'admin') ? 'selected' : '' ?>>Administrador</option>
                                </select>
                            </div>
                            
                            <?php if (!$usuarioEditar): ?>
                            <!-- Campos para nuevo usuario -->
                            <div class="mb-3">
                                <label for="contrasena" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="contrasena" name="contrasena" 
                                           required minlength="6">
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="togglePassword('contrasena')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Mínimo 6 caracteres</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirmar_contrasena" 
                                           name="confirmar_contrasena" required>
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="togglePassword('confirmar_contrasena')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <?php else: ?>
                            <!-- Campo para cambiar contraseña (edición) -->
                            <div class="mb-3">
                                <label class="form-label">Cambiar contraseña</label>
                                <div class="input-group mb-2">
                                    <input type="password" class="form-control" id="contrasena" name="contrasena" 
                                           placeholder="Nueva contraseña (dejar en blanco para no cambiar)" minlength="6">
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="togglePassword('contrasena')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirmar_contrasena" 
                                           name="confirmar_contrasena" placeholder="Confirmar nueva contraseña">
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="togglePassword('confirmar_contrasena')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Dejar en blanco para mantener la contraseña actual</div>
                            </div>
                            <?php endif; ?>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> <?= $usuarioEditar ? 'Actualizar' : 'Guardar' ?>
                                </button>
                                <?php if ($usuarioEditar): ?>
                                    <a href="usuarios.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Cancelar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div class="modal fade" id="confirmarEliminarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmar eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar al usuario <strong id="nombreUsuarioEliminar"></strong>?
                    <p class="text-danger mt-2">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" id="btnConfirmarEliminar" class="btn btn-danger">Eliminar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Mostrar modal de edición si hay un usuario para editar
        <?php if ($usuarioEditar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // Desplazarse al formulario
            document.getElementById('usuarioForm').scrollIntoView({ behavior: 'smooth' });
        });
        <?php endif; ?>

        // Función para confirmar eliminación
        function confirmarEliminar(id, nombre) {
            document.getElementById('nombreUsuarioEliminar').textContent = nombre;
            var btnEliminar = document.getElementById('btnConfirmarEliminar');
            btnEliminar.href = `?accion=eliminar&id=${id}&csrf_token=<?= $_SESSION['csrf_token'] ?>`;
            
            var modal = new bootstrap.Modal(document.getElementById('confirmarEliminarModal'));
            modal.show();
            
            return false;
        }

        // Mostrar/ocultar contraseña
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Validación del formulario
        document.getElementById('usuarioForm').addEventListener('submit', function(e) {
            const contrasena = document.getElementById('contrasena');
            if (contrasena && contrasena.value && contrasena.value.length < 8) {
                alert('La contraseña debe tener al menos 8 caracteres');
                e.preventDefault();
                return false;
            }
            return true;
        });
    </script>
</body>
</html>