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
        .equipo-card {
            transition: transform 0.2s;
        }
        .equipo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .search-box {
            max-width: 400px;
            margin-bottom: 20px;
        }
        .filters {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .equipo-img {
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-laptop me-2"></i>Gestión de Equipos
            </h2>
            <div>
                <a href="dashboard.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> Volver al Panel
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoEquipoModal">
                    <i class="fas fa-plus me-1"></i> Nuevo Equipo
                </button>
            </div>
        </div>

        <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Filtros y Búsqueda -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" id="buscarEquipo" class="form-control" placeholder="Buscar equipo...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="filtroEstado" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="mantenimiento">En Mantenimiento</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="filtroTipo" class="form-select">
                            <option value="">Todos los tipos</option>
                            <option value="laptop">Laptop</option>
                            <option value="desktop">Computadora de Escritorio</option>
                            <option value="monitor">Monitor</option>
                            <option value="impresora">Impresora</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button id="btnLimpiarFiltros" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-undo me-1"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

<!-- Lista de Equipos -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-list me-2"></i>Lista de Equipos
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>N° Serie</th>
                        <th>Tipo</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($equipos)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay equipos registrados</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($equipos as $equipo): ?>
                            <tr>
                                <td><?= $equipo['id_equipo'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($equipo['nombre_equipo']) ?></strong>
                                    <?php if (!empty($equipo['marca']) || !empty($equipo['modelo'])): ?>
                                        <div class="text-muted small">
                                            <?= htmlspecialchars(trim($equipo['marca'] . ' ' . $equipo['modelo'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($equipo['numero_serie']) ?></td>
                                <td>
                                    <?php 
                                    $tipo = [
                                        'laptop' => 'Laptop',
                                        'desktop' => 'Computadora',
                                        'monitor' => 'Monitor',
                                        'impresora' => 'Impresora',
                                        'otro' => 'Otro'
                                    ][$equipo['tipo_equipo'] ?? 'otro'] ?? 'Otro';
                                    echo $tipo;
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($equipo['ubicacion'] ?? 'No especificada') ?></td>
                                <td>
                                    <?php
                                    $estadoClases = [
                                        'activo' => 'bg-success',
                                        'inactivo' => 'bg-secondary',
                                        'mantenimiento' => 'bg-warning text-dark'
                                    ];
                                    $estadoText = [
                                        'activo' => 'Activo',
                                        'inactivo' => 'Inactivo',
                                        'mantenimiento' => 'Mantenimiento'
                                    ][$equipo['estado']] ?? 'Desconocido';
                                    ?>
                                    <span class="badge rounded-pill <?= $estadoClases[$equipo['estado']] ?? 'bg-secondary' ?>">
                                        <?= $estadoText ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="?editar=<?= $equipo['id_equipo'] ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           data-bs-toggle="tooltip" 
                                           title="Editar equipo">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirmarEliminar(<?= $equipo['id_equipo'] ?>, '<?= addslashes(htmlspecialchars($equipo['nombre_equipo'])) ?>')"
                                           data-bs-toggle="tooltip" 
                                           title="Eliminar equipo">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                        <a href="crear_ticket.php?equipo=<?= $equipo['id_equipo'] ?>" 
                                           class="btn btn-sm btn-outline-info"
                                           data-bs-toggle="tooltip" 
                                           title="Reportar problema">
                                            <i class="fas fa-ticket-alt"></i>
                                        </a>
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

<!-- Modal Nuevo Equipo -->
<div class="modal fade" id="nuevoEquipoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>
                    <?= $equipoEditar ? 'Editar Equipo' : 'Nuevo Equipo' ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="formEquipo">
                <input type="hidden" name="accion" value="<?= $equipoEditar ? 'actualizar' : 'crear' ?>">
                <input type="hidden" name="id_equipo" value="<?= $equipoEditar['id_equipo'] ?? '' ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre_equipo" class="form-label">Nombre del Equipo *</label>
                                <input type="text" class="form-control" id="nombre_equipo" name="nombre_equipo" 
                                       value="<?= htmlspecialchars($equipoEditar['nombre_equipo'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="numero_serie" class="form-label">Número de Serie *</label>
                                <input type="text" class="form-control" id="numero_serie" name="numero_serie" 
                                       value="<?= htmlspecialchars($equipoEditar['numero_serie'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="tipo_equipo" class="form-label">Tipo de Equipo *</label>
                                <select class="form-select" id="tipo_equipo" name="tipo_equipo" required>
                                    <option value="laptop" <?= ($equipoEditar['tipo_equipo'] ?? '') === 'laptop' ? 'selected' : '' ?>>Laptop</option>
                                    <option value="desktop" <?= ($equipoEditar['tipo_equipo'] ?? '') === 'desktop' ? 'selected' : '' ?>>Computadora de Escritorio</option>
                                    <option value="monitor" <?= ($equipoEditar['tipo_equipo'] ?? '') === 'monitor' ? 'selected' : '' ?>>Monitor</option>
                                    <option value="impresora" <?= ($equipoEditar['tipo_equipo'] ?? '') === 'impresora' ? 'selected' : '' ?>>Impresora</option>
                                    <option value="otro" <?= empty($equipoEditar['tipo_equipo']) || ($equipoEditar['tipo_equipo'] ?? '') === 'otro' ? 'selected' : '' ?>>Otro</option>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="marca" class="form-label">Marca</label>
                                        <input type="text" class="form-control" id="marca" name="marca" 
                                               value="<?= htmlspecialchars($equipoEditar['marca'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="modelo" class="form-label">Modelo</label>
                                        <input type="text" class="form-control" id="modelo" name="modelo" 
                                               value="<?= htmlspecialchars($equipoEditar['modelo'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ubicacion" class="form-label">Ubicación *</label>
                                <input type="text" class="form-control" id="ubicacion" name="ubicacion" 
                                       value="<?= htmlspecialchars($equipoEditar['ubicacion'] ?? '') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="estado" class="form-label">Estado *</label>
                                <select class="form-select" id="estado" name="estado" required>
                                    <option value="activo" <?= ($equipoEditar['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                                    <option value="inactivo" <?= ($equipoEditar['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                    <option value="mantenimiento" <?= ($equipoEditar['estado'] ?? '') === 'mantenimiento' ? 'selected' : '' ?>>En Mantenimiento</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="especificaciones" class="form-label">Especificaciones Técnicas</label>
                                <textarea class="form-control" id="especificaciones" name="especificaciones" 
                                          rows="5"><?= htmlspecialchars($equipoEditar['especificaciones'] ?? '') ?></textarea>
                                <div class="form-text">Procesador, RAM, almacenamiento, etc.</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar
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
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar el equipo <strong id="nombreEquipoEliminar"></strong>?
                <p class="text-danger mt-2">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="btnConfirmarEliminar" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Mostrar modal de edición si hay un equipo para editar
        <?php if ($equipoEditar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = new bootstrap.Modal(document.getElementById('nuevoEquipoModal'));
            myModal.show();
        });
        <?php endif; ?>

        // Función para confirmar eliminación
        function confirmarEliminar(id, nombre) {
            document.getElementById('nombreEquipoEliminar').textContent = nombre;
            var btnEliminar = document.getElementById('btnConfirmarEliminar');
            btnEliminar.href = `?accion=eliminar&id=${id}&csrf_token=<?= $_SESSION['csrf_token'] ?>`;
            
            var modal = new bootstrap.Modal(document.getElementById('confirmarEliminarModal'));
            modal.show();
            
            return false;
        }

        // Filtrado de equipos
        document.addEventListener('DOMContentLoaded', function() {
            const buscarInput = document.getElementById('buscarEquipo');
            const filtroEstado = document.getElementById('filtroEstado');
            const filtroTipo = document.getElementById('filtroTipo');
            const btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
            const filas = document.querySelectorAll('tbody tr');

            function filtrarEquipos() {
                const textoBusqueda = buscarInput.value.toLowerCase();
                const estadoSeleccionado = filtroEstado.value;
                const tipoSeleccionado = filtroTipo.value;

                filas.forEach(fila => {
                    if (fila.cells.length < 2) return; // Saltar filas vacías

                    const nombre = fila.cells[1].textContent.toLowerCase();
                    const tipo = fila.cells[3].textContent.toLowerCase();
                    const estado = fila.cells[5].textContent.toLowerCase();

                    const coincideTexto = nombre.includes(textoBusqueda) || 
                                        fila.cells[2].textContent.toLowerCase().includes(textoBusqueda) ||
                                        fila.cells[4].textContent.toLowerCase().includes(textoBusqueda);
                    
                    const coincideEstado = !estadoSeleccionado || estado.includes(estadoSeleccionado);
                    const coincideTipo = !tipoSeleccionado || tipo.includes(tipoSeleccionado);

                    if (coincideTexto && coincideEstado && coincideTipo) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                });
            }

            // Event listeners para los filtros
            buscarInput.addEventListener('input', filtrarEquipos);
            filtroEstado.addEventListener('change', filtrarEquipos);
            filtroTipo.addEventListener('change', filtrarEquipos);
            
            // Limpiar filtros
            btnLimpiarFiltros.addEventListener('click', function() {
                buscarInput.value = '';
                filtroEstado.value = '';
                filtroTipo.value = '';
                filtrarEquipos();
            });
        });

        // Validación del formulario
        document.getElementById('formEquipo')?.addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre_equipo').value.trim();
            const numeroSerie = document.getElementById('numero_serie').value.trim();
            
            if (!nombre || !numeroSerie) {
                e.preventDefault();
                alert('Por favor complete todos los campos requeridos.');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>
