<?php
session_start();
require_once 'clases/usuario.php';
require_once 'clases/conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

$usuarioObj = new Usuario();
$mensaje = '';
$tipoMensaje = '';

// Procesar el cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contrasena_actual = $_POST['contrasena_actual'] ?? '';
    $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
    
    // Validaciones
    if (empty($contrasena_actual) || empty($nueva_contrasena) || empty($confirmar_contrasena)) {
        $mensaje = 'Todos los campos son obligatorios';
        $tipoMensaje = 'danger';
    } 
    elseif (strlen($nueva_contrasena) < 6) {
        $mensaje = 'La nueva contraseña debe tener al menos 6 caracteres';
        $tipoMensaje = 'danger';
    }
    elseif ($nueva_contrasena !== $confirmar_contrasena) {
        $mensaje = 'Las contraseñas no coinciden';
        $tipoMensaje = 'danger';
    } else {
        // Verificar la contraseña actual
        $usuario = $usuarioObj->validarLogin($_SESSION['usuario'], $contrasena_actual);
        
        if ($usuario) {
            // Actualizar la contraseña
            $sql = "UPDATE usuarios SET contrasena = :contrasena, primer_inicio = 0 WHERE id_usuario = :id";
            $conexion = Conexion::obtenerInstancia()->getConexion();
            $stmt = $conexion->prepare($sql);
            $hash = password_hash($nueva_contrasena, PASSWORD_BCRYPT);
            $stmt->bindParam(':contrasena', $hash);
            $stmt->bindParam(':id', $_SESSION['id_usuario']);
            
            if ($stmt->execute()) {
                // Marcar que ya no es el primer inicio
                $usuarioObj->marcarPrimerInicio($_SESSION['id_usuario']);
                
                $mensaje = 'Contraseña actualizada correctamente. Serás redirigido al panel de control.';
                $tipoMensaje = 'success';
                
                // Redirigir después de 2 segundos
                header('Refresh: 2; URL=dashboard.php');
            } else {
                $mensaje = 'Error al actualizar la contraseña';
                $tipoMensaje = 'danger';
            }
        } else {
            $mensaje = 'La contraseña actual es incorrecta';
            $tipoMensaje = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - Sistema de Soporte Técnico</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 0 auto;
        }
        .card-header {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
            border-radius: 10px 10px 0 0 !important;
            text-align: center;
            padding: 1.5rem;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-key me-2"></i>Cambiar Contraseña</h4>
                        <p class="mb-0 small">Por seguridad, debes cambiar tu contraseña antes de continuar</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($mensaje): ?>
                            <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($mensaje) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="formCambiarContrasena">
                            <div class="mb-3">
                                <label for="contrasena_actual" class="form-label">Contraseña Actual</label>
                                <div class="password-container">
                                    <input type="password" class="form-control" id="contrasena_actual" name="contrasena_actual" required>
                                    <button type="button" class="password-toggle" onclick="togglePassword('contrasena_actual')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nueva_contrasena" class="form-label">Nueva Contraseña</label>
                                <div class="password-container">
                                    <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena" required minlength="6">
                                    <button type="button" class="password-toggle" onclick="togglePassword('nueva_contrasena')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Mínimo 6 caracteres</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirmar_contrasena" class="form-label">Confirmar Nueva Contraseña</label>
                                <div class="password-container">
                                    <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required minlength="6">
                                    <button type="button" class="password-toggle" onclick="togglePassword('confirmar_contrasena')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> Cambiar Contraseña
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Función para mostrar/ocultar contraseña
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.querySelector(`#${inputId} + .password-toggle i`);
            
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
        document.getElementById('formCambiarContrasena').addEventListener('submit', function(e) {
            const nuevaContrasena = document.getElementById('nueva_contrasena');
            const confirmarContrasena = document.getElementById('confirmar_contrasena');
            
            if (nuevaContrasena.value !== confirmarContrasena.value) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
            
            if (nuevaContrasena.value.length < 6) {
                e.preventDefault();
                alert('La nueva contraseña debe tener al menos 6 caracteres');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>
