<?php
session_start();
require_once 'clases/solicitudsoftware.php';

// Verificar sesión y rol de administrador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit();
}

$solObj = new SolicitudSoftware();
$mensaje = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Actualizar estado de la solicitud
    if (isset($_POST['actualizar_estado'])) {
        $id_solicitud = (int)$_POST['id_solicitud'];
        $estado = $_POST['estado'];
        $comentario = trim($_POST['comentario_admin']);
        
        if ($solObj->actualizarEstado($id_solicitud, $estado, $comentario)) {
            $mensaje = 'Estado actualizado correctamente';
        } else {
            $mensaje = 'Error al actualizar el estado';
        }
    }
    
    // Eliminar solicitud
    if (isset($_POST['eliminar'])) {
        $id_solicitud = (int)$_POST['id_solicitud'];
        if ($solObj->eliminarSolicitud($id_solicitud)) {
            $mensaje = 'Solicitud eliminada correctamente';
        } else {
            $mensaje = 'Error al eliminar la solicitud';
        }
    }
}

// Obtener todas las solicitudes con filtros
$filtro_estado = $_GET['estado'] ?? '';
$solicitudes = $solObj->listarSolicitudes($filtro_estado);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Solicitudes de Software - Administración</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 2rem 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
            font-weight: 600;
            padding: 1rem 1.25rem;
        }
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
            padding: 1rem 1.25rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
            font-size: 0.9rem;
            border-color: #f1f3f5;
        }
        .badge {
            font-weight: 500;
            padding: 0.4em 0.8em;
            font-size: 0.8em;
            border-radius: 50rem;
            text-transform: capitalize;
        }
        .badge-pendiente {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-aceptado {
            background-color: #198754;
            color: white;
        }
        .badge-rechazado {
            background-color: #dc3545;
            color: white;
        }
        .search-box {
            max-width: 300px;
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.85rem;
            border-radius: 0.25rem;
        }
        .btn-responder {
            color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.1);
            border: 1px solid rgba(13, 110, 253, 0.2);
        }
        .btn-responder:hover {
            background-color: rgba(13, 110, 253, 0.2);
            color: #0a58ca;
        }
        .btn-eliminar {
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        .btn-eliminar:hover {
            background-color: rgba(220, 53, 69, 0.2);
            color: #b02a37;
        }
        .filtros-card {
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }
        .filtros-header {
            background-color: #f8f9fa;
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid rgba(0,0,0,.125);
            font-weight: 600;
        }
        .filtros-body {
            padding: 1.25rem;
        }
        .page-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
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

        <!-- Título -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="page-title">
                    <i class="fas fa-tasks me-2"></i>Gestión de Solicitudes de Software
                </h1>
            </div>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo strpos($mensaje, 'Error') !== false ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="filtros-card">
            <div class="filtros-header">
                <i class="fas fa-filter me-2"></i>Filtros
            </div>
            <div class="filtros-body">
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="estado" class="form-label fw-medium">Estado</label>
                        <select name="estado" id="estado" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" <?= ($filtro_estado == 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
                            <option value="aceptado" <?= ($filtro_estado == 'aceptado') ? 'selected' : '' ?>>Aceptado</option>
                            <option value="rechazado" <?= ($filtro_estado == 'rechazado') ? 'selected' : '' ?>>Rechazado</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i> Filtrar
                        </button>
                        <a href="admin_solicitudes.php" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-1"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de solicitudes -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2"></i>Lista de Solicitudes</span>
                <div class="input-group search-box" style="max-width: 300px;">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar solicitudes...">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Docente</th>
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
                                    <td colspan="8" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <p class="mb-0">No hay solicitudes para mostrar</p>
                                            <?php if (!empty($filtro_estado)): ?>
                                                <p class="small mt-2">Intenta con otros filtros o <a href="admin_solicitudes.php">muestra todas las solicitudes</a></p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($solicitudes as $s): 
                                    $estado = strtolower($s['estado'] ?? 'pendiente');
                                    $badgeClass = 'badge-pendiente';
                                    if ($estado === 'aceptado') $badgeClass = 'badge-aceptado';
                                    if ($estado === 'rechazado') $badgeClass = 'badge-rechazado';
                                ?>
                                <tr class="solicitud-item">
                                    <td class="fw-medium">#<?= $s['id_solicitud'] ?></td>
                                    <td><?= htmlspecialchars($s['nombre_completo']) ?></td>
                                    <td><?= htmlspecialchars($s['nombre_software']) ?></td>
                                    <td class="text-nowrap"><?= htmlspecialchars($s['version_solicitada'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= ucfirst($estado) ?>
                                        </span>
                                    </td>
                                    <td class="small">
                                        <?php 
                                            $comentario = $s['comentario_admin'] ?? 'Ninguno';
                                            echo strlen($comentario) > 20 ? 
                                                '<span data-bs-toggle="tooltip" title="' . htmlspecialchars($comentario) . '">' . 
                                                htmlspecialchars(substr($comentario, 0, 20)) . '...</span>' : 
                                                htmlspecialchars($comentario); 
                                        ?>
                                    </td>
                                    <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($s['fecha_solicitud'])) ?></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                    class="btn btn-action btn-responder" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#responderModal"
                                                    data-id="<?= $s['id_solicitud'] ?>"
                                                    data-software="<?= htmlspecialchars($s['nombre_software']) ?>"
                                                    data-version="<?= htmlspecialchars($s['version_solicitada']) ?>"
                                                    data-estado="<?= $estado ?>"
                                                    data-comentario="<?= htmlspecialchars($s['comentario_admin'] ?? '') ?>">
                                                <i class="fas fa-reply"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-action btn-eliminar" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#eliminarModal"
                                                    data-id="<?= $s['id_solicitud'] ?>"
                                                    data-software="<?= htmlspecialchars($s['nombre_software']) ?>">
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

    <!-- Modal Responder/Actualizar -->
    <div class="modal fade" id="responderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-reply me-2"></i>Responder Solicitud
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="id_solicitud" id="responder_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Software</label>
                            <input type="text" class="form-control" id="modal_software" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Versión</label>
                            <input type="text" class="form-control" id="modal_version" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label fw-medium">Estado</label>
                            <select class="form-select" id="modal_estado" name="estado" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="aceptado">Aceptado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label for="comentario_admin" class="form-label fw-medium">Comentario</label>
                            <textarea class="form-control" id="comentario_admin" name="comentario_admin" rows="3" required></textarea>
                            <div class="form-text">Este comentario será visible para el docente.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="submit" name="actualizar_estado" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar -->
    <div class="modal fade" id="eliminarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="id_solicitud" id="eliminar_id">
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas eliminar la solicitud de <strong id="modal_eliminar_software"></strong>?</p>
                        <p class="text-muted small mb-0">Esta acción no se puede deshacer.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="submit" name="eliminar" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-1"></i> Eliminar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Configurar modal de respuesta
        const responderModal = document.getElementById('responderModal');
        if (responderModal) {
            responderModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const software = button.getAttribute('data-software');
                const version = button.getAttribute('data-version');
                const estado = button.getAttribute('data-estado');
                const comentario = button.getAttribute('data-comentario');
                
                document.getElementById('responder_id').value = id;
                document.getElementById('modal_software').value = software;
                document.getElementById('modal_version').value = version;
                document.getElementById('modal_estado').value = estado;
                document.getElementById('comentario_admin').value = comentario || '';
            });
        }

        // Configurar modal de eliminación
        const eliminarModal = document.getElementById('eliminarModal');
        if (eliminarModal) {
            eliminarModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const software = button.getAttribute('data-software');
                
                document.getElementById('eliminar_id').value = id;
                document.getElementById('modal_eliminar_software').textContent = software;
            });
        }

        // Búsqueda en tiempo real
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('.solicitud-item');
                
                let hasResults = false;
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                        hasResults = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Mostrar mensaje si no hay resultados
                const noResultsRow = document.querySelector('.no-results');
                if (!hasResults) {
                    if (!noResultsRow) {
                        const tbody = document.querySelector('#solicitudesTable');
                        const tr = document.createElement('tr');
                        tr.className = 'no-results';
                        tr.innerHTML = `
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-search fa-2x text-muted mb-2"></i>
                                <p class="mb-0">No se encontraron resultados para "${searchTerm}"</p>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    }
                } else if (noResultsRow) {
                    noResultsRow.remove();
                }
            });
        }
    });
    </script>
</body>
</html>
