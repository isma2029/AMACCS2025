<?php
session_start();
require_once 'clases/solicitudsoftware.php';

// Verificar sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Verificar rol
if ($_SESSION['rol'] !== 'docente') {
    $_SESSION['mensaje'] = "Acceso denegado. Solo los docentes pueden acceder a esta sección.";
    header("Location: dashboard.php");
    exit();
}

// Inicializar variables
$mensaje = '';
$solObj = new SolicitudSoftware();

// Obtener ID del docente
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['mensaje'] = "Error: No se pudo identificar al usuario.";
    header("Location: dashboard.php");
    exit();
}

$id_docente = (int)$_SESSION['id_usuario'];

// Manejo de acciones CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crear solicitud
    if (isset($_POST['crear'])) {
        $nombre = trim($_POST['nombre_software']);
        $version = trim($_POST['version']);
        if ($solObj->crearSolicitud($id_docente, $nombre, $version)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?mensaje=Solicitud creada correctamente");
            exit();
        } else {
            $mensaje = "Error al crear la solicitud. Intente nuevamente.";
        }
    }
    
    // Actualizar solicitud
    if (isset($_POST['actualizar']) && isset($_POST['id_solicitud'])) {
        $id_solicitud = (int)$_POST['id_solicitud'];
        $nombre = trim($_POST['nombre_software']);
        $version = trim($_POST['version']);
        
        // Verificar que la solicitud pertenezca al usuario actual
        $solicitud = $solObj->obtenerSolicitud($id_solicitud);
        if ($solicitud && $solicitud['id_docente'] == $id_docente) {
            if ($solObj->actualizarSolicitud($id_solicitud, $nombre, $version)) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?mensaje=Solicitud actualizada correctamente");
                exit();
            }
        }
        $mensaje = "Error al actualizar la solicitud.";
    }
}

// Eliminar solicitud
if (isset($_GET['eliminar'])) {
    $id_solicitud = (int)$_GET['eliminar'];
    
    // Verificar que la solicitud pertenezca al usuario actual
    $solicitud = $solObj->obtenerSolicitud($id_solicitud);
    if ($solicitud && $solicitud['id_docente'] == $id_docente) {
        if ($solObj->eliminarSolicitud($id_solicitud)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?mensaje=Solicitud eliminada correctamente");
            exit();
        }
    }
    $mensaje = "Error al eliminar la solicitud.";
}

// Mostrar mensaje si existe en la URL
if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
}

// Listar solicitudes del docente
$solicitudes = $solObj->listarPorDocente($id_docente);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Software - Sistema de Soporte Técnico</title>
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
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
            font-weight: 600;
        }
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .btn i {
            font-size: 0.9em;
        }
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }
        .search-box {
            max-width: 300px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Botón de regreso -->
        <div class="row mb-4">
            <div class="col-12">
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
                </a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-center mb-4">
                    <i class="fas fa-laptop-code me-2"></i>Solicitudes de Software
                </h2>
            </div>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo strpos($mensaje, 'Error') !== false ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Lista de Solicitudes -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-list me-2"></i>Mis Solicitudes</span>
                        <div class="input-group search-box">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Buscar solicitudes...">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Software</th>
                                        <th>Versión</th>
                                        <th>Estado</th>
                                        <th>Comentario</th>
                                        <th>Fecha</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="solicitudesTable">
                                <?php if (empty($solicitudes)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="mb-0">No hay solicitudes registradas</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($solicitudes as $s): 
                                        $estado = strtolower($s['estado'] ?? 'pendiente');
                                        $esEditable = ($estado === 'pendiente');
                                        $badgeClass = match($estado) {
                                            'aceptado' => 'bg-success',
                                            'rechazado' => 'bg-danger',
                                            default => 'bg-warning text-dark'
                                        };
                                    ?>
                                    <tr class="solicitud-item">
                                        <td><?php echo htmlspecialchars($s['id_solicitud']); ?></td>
                                        <td><?php echo htmlspecialchars($s['nombre_software']); ?></td>
                                        <td><?php echo htmlspecialchars($s['version_solicitada'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $badgeClass; ?>">
                                                <?php echo ucfirst($estado); ?>
                                            </span>
                                        </td>
                                        <td class="small">
                                            <?php 
                                                $comentario = $s['comentario_admin'] ?? 'Ninguno';
                                                echo strlen($comentario) > 30 ? 
                                                    '<span title="' . htmlspecialchars($comentario) . '">' . 
                                                    htmlspecialchars(substr($comentario, 0, 30)) . '...</span>' : 
                                                    htmlspecialchars($comentario); 
                                            ?>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($s['fecha_solicitud'])); ?></td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" 
                                                        class="btn btn-outline-primary btn-editar" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editarSolicitudModal"
                                                        data-id="<?php echo $s['id_solicitud']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($s['nombre_software']); ?>"
                                                        data-version="<?php echo htmlspecialchars($s['version_solicitada']); ?>"
                                                        <?php echo !$esEditable ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#confirmarEliminarModal"
                                                        data-id="<?php echo $s['id_solicitud']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($s['nombre_software']); ?>"
                                                        <?php echo !$esEditable ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
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
        </div>

        <!-- Formulario de Nueva Solicitud -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-plus-circle me-2"></i>Nueva Solicitud de Software
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nombre_software" class="form-label">Nombre del Software</label>
                                    <input type="text" class="form-control" id="nombre_software" name="nombre_software" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="version" class="form-label">Versión</label>
                                    <input type="text" class="form-control" id="version" name="version" required>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" name="crear" class="btn btn-primary w-100">
                                        <i class="fas fa-paper-plane me-1"></i> Enviar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Solicitud -->
    <div class="modal fade" id="editarSolicitudModal" tabindex="-1" aria-labelledby="editarSolicitudModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Editar Solicitud
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <input type="hidden" name="id_solicitud" id="editar_id">
                        <div class="mb-3">
                            <label for="editar_nombre" class="form-label">Nombre del Software</label>
                            <input type="text" class="form-control" id="editar_nombre" name="nombre_software" required>
                        </div>
                        <div class="mb-3">
                            <label for="editar_version" class="form-label">Versión</label>
                            <input type="text" class="form-control" id="editar_version" name="version" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="submit" name="actualizar" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="confirmarEliminarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar la solicitud de <strong id="nombreSolicitudEliminar"></strong>?</p>
                    <p class="text-muted small mb-0">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <a href="#" id="confirmarEliminarBtn" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-1"></i> Eliminar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manejar clic en botón de editar
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-editar')) {
                const button = e.target.closest('.btn-editar');
                const id = button.getAttribute('data-id');
                const nombre = button.getAttribute('data-nombre');
                const version = button.getAttribute('data-version');
                
                document.getElementById('editar_id').value = id;
                document.getElementById('editar_nombre').value = nombre;
                document.getElementById('editar_version').value = version;
            }
        });

        // Configurar modal de eliminación
        const eliminarModal = document.getElementById('confirmarEliminarModal');
        if (eliminarModal) {
            eliminarModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const nombre = button.getAttribute('data-nombre');
                
                document.getElementById('nombreSolicitudEliminar').textContent = nombre;
                document.getElementById('confirmarEliminarBtn').href = `?eliminar=${id}`;
            });
        }

        // Búsqueda en tiempo real
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('.solicitud-item');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    });
    </script>
</body>
</html>
