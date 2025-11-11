<?php
// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['mensaje'] = [
        'tipo' => 'warning',
        'texto' => 'Debe iniciar sesión para acceder a esta sección.'
    ];
    header('Location: index.php');
    exit();
}

// Incluir archivos necesarios
require_once 'clases/TicketManager.php';
require_once 'clases/conexion.php';

// Obtener ID del ticket
$id_ticket = $_GET['id'] ?? null;
if (!$id_ticket) {
    $_SESSION['mensaje'] = [
        'tipo' => 'danger',
        'texto' => 'No se especificó un ticket para visualizar.'
    ];
    header('Location: mis_tickets.php');
    exit();
}

// Obtener información del ticket
$ticketManager = new TicketManager();
$ticket = $ticketManager->obtenerTicketPorId($id_ticket);

// Verificar que el ticket exista y el usuario tenga permisos
if (!$ticket) {
    $_SESSION['mensaje'] = [
        'tipo' => 'danger',
        'texto' => 'El ticket solicitado no existe.'
    ];
    header('Location: mis_tickets.php');
    exit();
}

// Verificar permisos (solo el dueño, admin o técnico pueden ver)
$esAdmin = in_array($_SESSION['rol'], ['admin', 'tecnico']);
$esDuenio = ($ticket['id_usuario'] == $_SESSION['id_usuario']);

if (!$esAdmin && !$esDuenio) {
    $_SESSION['mensaje'] = [
        'tipo' => 'danger',
        'texto' => 'No tiene permisos para ver este ticket.'
    ];
    header('Location: mis_tickets.php');
    exit();
}

// Obtener historial del ticket
$historial = $ticketManager->obtenerHistorialTicket($id_ticket);

// Obtener respuestas del ticket
$respuestas = $ticketManager->obtenerRespuestas($id_ticket);

// Procesar formulario de respuesta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['responder'])) {
    $respuesta = trim($_POST['respuesta']);
    
    if (!empty($respuesta)) {
        $ticketManager->agregarRespuesta($id_ticket, $_SESSION['id_usuario'], $respuesta);
        
        // Actualizar estado si es un técnico respondiendo
        if ($esAdmin && $ticket['estado'] === 'pendiente') {
            $ticketManager->actualizarEstado($id_ticket, 'en_proceso', $_SESSION['id_usuario']);
        }
        
        $_SESSION['mensaje'] = [
            'tipo' => 'success',
            'texto' => 'Respuesta enviada correctamente.'
        ];
        
        // Redirigir para evitar reenvío del formulario
        header('Location: ver_ticket.php?id=' . $id_ticket);
        exit();
    }
}

// Procesar cambio de estado (solo para admin/tecnico)
if ($esAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $nuevo_estado = $_POST['nuevo_estado'];
    $ticketManager->actualizarEstado($id_ticket, $nuevo_estado, $_SESSION['id_usuario']);
    
    $_SESSION['mensaje'] = [
        'tipo' => 'success',
        'texto' => 'Estado del ticket actualizado correctamente.'
    ];
    
    header('Location: ver_ticket.php?id=' . $id_ticket);
    exit();
}

// Función para obtener el ícono según el estado
function obtenerIconoEstado($estado) {
    switch ($estado) {
        case 'pendiente':
            return '<i class="fas fa-clock text-warning"></i>';
        case 'en_proceso':
            return '<i class="fas fa-tools text-primary"></i>';
        case 'resuelto':
            return '<i class="fas fa-check-circle text-success"></i>';
        case 'cerrado':
            return '<i class="fas fa-lock text-secondary"></i>';
        default:
            return '<i class="fas fa-question-circle"></i>';
    }
}

// Función para obtener la clase de color según la urgencia
function obtenerClaseUrgencia($urgencia) {
    switch ($urgencia) {
        case 'baja':
            return 'bg-info';
        case 'media':
            return 'bg-warning';
        case 'alta':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// Incluir encabezado
$titulo_pagina = 'Detalles del Ticket #' . $id_ticket;
include 'includes/header.php';
?>

<div class="container mt-4">
    <!-- Migas de pan -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="mis_tickets.php">Mis Tickets</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ticket #<?= $id_ticket ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <!-- Información principal del ticket -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <?= obtenerIconoEstado($ticket['estado']) ?>
                        <?= htmlspecialchars($ticket['titulo']) ?>
                    </h5>
                    <span class="badge <?= obtenerClaseUrgencia($ticket['urgencia']) ?>">
                        <?= ucfirst($ticket['urgencia']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Descripción:</h6>
                        <p class="card-text"><?= nl2br(htmlspecialchars($ticket['descripcion'])) ?></p>
                    </div>
                    
                    <?php if (!empty($ticket['equipo_info'])): ?>
                    <div class="mb-3">
                        <h6>Equipo relacionado:</h6>
                        <p class="card-text">
                            <i class="fas fa-desktop me-2"></i>
                            <?= htmlspecialchars($ticket['equipo_info']['nombre_equipo']) ?> 
                            (<?= htmlspecialchars($ticket['equipo_info']['numero_serie']) ?>)
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="far fa-calendar-alt me-1"></i>
                            Creado: <?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?>
                        </small>
                        <span class="badge bg-secondary">
                            <?= ucfirst($ticket['categoria']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Sección de respuestas -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-comments me-1"></i> Conversación</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($respuestas)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="far fa-comment-dots fa-3x mb-3"></i>
                            <p>No hay respuestas aún. Sé el primero en comentar.</p>
                        </div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($respuestas as $respuesta): ?>
                                <div class="timeline-item <?= $respuesta['es_tecnico'] ? 'timeline-item-primary' : 'timeline-item-secondary' ?>">
                                    <div class="timeline-item-marker">
                                        <div class="timeline-item-marker-indicator">
                                            <?php if ($respuesta['es_tecnico']): ?>
                                                <i class="fas fa-user-shield"></i>
                                            <?php else: ?>
                                                <i class="fas fa-user"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="timeline-item-content">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-bold">
                                                <?= htmlspecialchars($respuesta['nombre_usuario']) ?>
                                                <?php if ($respuesta['es_tecnico']): ?>
                                                    <span class="badge bg-primary">Técnico</span>
                                                <?php endif; ?>
                                            </span>
                                            <small class="text-muted">
                                                <?= date('d/m/Y H:i', strtotime($respuesta['fecha'])) ?>
                                            </small>
                                        </div>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($respuesta['respuesta'])) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de respuesta -->
                    <div class="mt-4">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="respuesta" class="form-label">Añadir respuesta</label>
                                <textarea class="form-control" id="respuesta" name="respuesta" rows="3" required></textarea>
                                <div class="invalid-feedback">
                                    Por favor escribe tu respuesta.
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" name="responder" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Enviar respuesta
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Panel de información -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-1"></i> Información del ticket</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-tag me-2"></i>Estado</span>
                            <span class="badge bg-<?= $ticket['estado'] === 'pendiente' ? 'warning' : ($ticket['estado'] === 'en_proceso' ? 'primary' : 'success') ?>">
                                <?= ucfirst(str_replace('_', ' ', $ticket['estado'])) ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-user me-2"></i>Reportado por</span>
                            <span><?= htmlspecialchars($ticket['usuario_nombre']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-calendar-day me-2"></i>Fecha de creación</span>
                            <span><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></span>
                        </li>
                        <?php if ($ticket['fecha_actualizacion']): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-sync-alt me-2"></i>Última actualización</span>
                            <span><?= date('d/m/Y H:i', strtotime($ticket['fecha_actualizacion'])) ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if ($ticket['tecnico_asignado']): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-user-tie me-2"></i>Técnico asignado</span>
                            <span><?= htmlspecialchars($ticket['tecnico_asignado']) ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Acciones (solo para admin/tecnico) -->
            <?php if ($esAdmin): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-cog me-1"></i> Acciones</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="mb-3">
                            <div class="mb-3">
                                <label for="nuevo_estado" class="form-label">Cambiar estado</label>
                                <select class="form-select" id="nuevo_estado" name="nuevo_estado">
                                    <option value="pendiente" <?= $ticket['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="en_proceso" <?= $ticket['estado'] === 'en_proceso' ? 'selected' : '' ?>>En proceso</option>
                                    <option value="resuelto" <?= $ticket['estado'] === 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                                    <option value="cerrado" <?= $ticket['estado'] === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                                </select>
                            </div>
                            <button type="submit" name="cambiar_estado" class="btn btn-primary w-100">
                                <i class="fas fa-save me-1"></i> Actualizar estado
                            </button>
                        </form>
                        
                        <a href="editar_ticket.php?id=<?= $id_ticket ?>" class="btn btn-outline-secondary w-100 mb-2">
                            <i class="fas fa-edit me-1"></i> Editar ticket
                        </a>
                        
                        <?php if ($ticket['estado'] !== 'cerrado'): ?>
                            <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#modalCerrarTicket">
                                <i class="fas fa-lock me-1"></i> Cerrar ticket
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Historial de cambios -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-history me-1"></i> Historial</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($historial)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-history fa-2x mb-2"></i>
                            <p>No hay historial de cambios</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($historial as $evento): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <small class="text-muted">
                                            <i class="fas fa-user-circle me-1"></i>
                                            <?= htmlspecialchars($evento['usuario_nombre']) ?>
                                        </small>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($evento['fecha'])) ?>
                                        </small>
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge bg-light text-dark">
                                            <?= ucfirst(str_replace('_', ' ', $evento['accion'])) ?>
                                        </span>
                                        <?php if (!empty($evento['detalle'])): ?>
                                            <small class="d-block mt-1"><?= htmlspecialchars($evento['detalle']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cerrar ticket -->
<?php if ($esAdmin && $ticket['estado'] !== 'cerrado'): ?>
<div class="modal fade" id="modalCerrarTicket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cerrar ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="acciones/cerrar_ticket.php">
                <div class="modal-body">
                    <input type="hidden" name="id_ticket" value="<?= $id_ticket ?>">
                    <div class="mb-3">
                        <label for="comentario_cierre" class="form-label">Comentario de cierre</label>
                        <textarea class="form-control" id="comentario_cierre" name="comentario_cierre" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Confirmar cierre</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Estilos personalizados -->
<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
    margin: 0 0 0 1.5rem;
    border-left: 2px solid #e9ecef;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
    padding-left: 1.5rem;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item-marker {
    position: absolute;
    left: -1.5rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background-color: #fff;
    border: 2px solid #e9ecef;
    z-index: 1;
}

.timeline-item-primary .timeline-item-marker {
    border-color: #0d6efd;
    background-color: #0d6efd;
}

.timeline-item-secondary .timeline-item-marker {
    border-color: #6c757d;
    background-color: #6c757d;
}

.timeline-item-content {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    position: relative;
}

.timeline-item:not(:last-child) .timeline-item-content::after {
    content: '';
    position: absolute;
    bottom: -0.5rem;
    left: 1rem;
    width: 1rem;
    height: 1rem;
    background-color: #f8f9fa;
    transform: rotate(45deg);
    z-index: 0;
}

.timeline-item-primary .timeline-item-content {
    background-color: #e7f1ff;
    border-left: 3px solid #0d6efd;
}

.timeline-item-secondary .timeline-item-content {
    background-color: #f8f9fa;
    border-left: 3px solid #6c757d;
}

.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
}

.bg-warning {
    color: #000;
}
</style>

<?php include 'includes/footer.php'; ?>
