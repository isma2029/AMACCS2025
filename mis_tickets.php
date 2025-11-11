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

// Inicializar variables
$ticketManager = new TicketManager();
$id_usuario = $_SESSION['id_usuario'];
$mensaje = '';

// Manejar mensajes de la sesión
if (isset($_SESSION['mensaje'])) {
    $mensaje = sprintf(
        '<div class="alert alert-%s alert-dismissible fade show" role="alert">%s<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>',
        $_SESSION['mensaje']['tipo'],
        $_SESSION['mensaje']['texto']
    );
    unset($_SESSION['mensaje']);
}

// Obtener tickets del usuario
try {
    $mis_tickets = $ticketManager->listarTicketsPorUsuario($id_usuario);
    
    // Si no hay tickets, mostrar mensaje
    if (empty($mis_tickets)) {
        $mensaje = "<div class='alert alert-info'>No tienes tickets registrados.</div>";
    }
} catch (Exception $e) {
    error_log("Error al obtener tickets: " . $e->getMessage());
    $mensaje = "<div class='alert alert-danger'>Error al cargar los tickets. Por favor, intente nuevamente.</div>";
    $mis_tickets = [];
}

// Manejar mensajes de éxito/error de la URL
if (isset($_GET['created']) && $_GET['created'] == 1) {
    $mensaje = '<div class="alert alert-success">Ticket creado exitosamente!</div>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Tickets de Soporte</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --secondary-color: #6c757d;
        }
        
        body {
            background-color: #f8f9fa;
            padding: 2rem 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0,0,0,.125);
            font-weight: 600;
            padding: 1rem 1.25rem;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.4em 0.8em;
            font-size: 0.8em;
            border-radius: 50rem;
        }
        
        .badge-pendiente {
            background-color: #f39c12;
            color: #fff;
        }
        
        .badge-en-progreso {
            background-color: #3498db;
            color: #fff;
        }
        
        .badge-resuelto {
            background-color: #2ecc71;
            color: #fff;
        }
        
        .badge-cerrado {
            background-color: #7f8c8d;
            color: #fff;
        }
        
        .ticket-priority {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .priority-alta { background-color: #e74c3c; }
        .priority-media { background-color: #f39c12; }
        .priority-baja { background-color: #2ecc71; }
        
        .ticket-card {
            border-left: 4px solid #dee2e6;
            transition: all 0.3s ease;
        }
        
        .ticket-card:hover {
            border-left-color: var(--primary-color);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        
        .ticket-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .ticket-meta {
            font-size: 0.85rem;
            color: #7f8c8d;
        }
        
        .ticket-desc {
            color: #34495e;
            font-size: 0.9rem;
            line-height: 1.5;
            margin: 0.5rem 0;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
        
        .btn-new-ticket {
            background-color: var(--primary-color);
            border: none;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
        }
        
        .btn-new-ticket:hover {
            background-color: #3a56d4;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
        }

        table th {
            background-color: #4CAF50;
            color: white;
        }
        
        .badge-urgencia {
            text-transform: capitalize;
        }
        
        .ticket-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #e0f7e0;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm mb-3">
                    <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
                </a>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">
                        <i class="fas fa-ticket-alt me-2"></i>Mis Tickets de Soporte
                    </h2>
                    <a href="crear_ticket.php" class="btn btn-primary btn-new-ticket">
                        <i class="fas fa-plus-circle me-1"></i> Nuevo Ticket
                    </a>
                </div>
                
                <?php if (!empty($mensaje)): ?>
                    <?php echo $mensaje; ?>
                <?php endif; ?>
                
                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Filtrar por estado</label>
                                <select class="form-select form-select-sm" id="filtroEstado">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente">Pendientes</option>
                                    <option value="en progreso">En progreso</option>
                                    <option value="resuelto">Resueltos</option>
                                    <option value="cerrado">Cerrados</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Ordenar por</label>
                                <select class="form-select form-select-sm" id="ordenarPor">
                                    <option value="fecha_desc">Más recientes primero</option>
                                    <option value="fecha_asc">Más antiguos primero</option>
                                    <option value="prioridad">Prioridad</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted mb-1">Buscar</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="buscarTicket" placeholder="Buscar tickets...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Tickets -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-list me-2"></i>Mis Tickets</span>
                        <span class="badge bg-primary"><?php echo count($mis_tickets); ?> tickets</span>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php if (count($mis_tickets) > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($mis_tickets as $ticket): 
                                    $badgeClass = '';
                                    switch(strtolower($ticket['estado'])) {
                                        case 'pendiente':
                                            $badgeClass = 'bg-warning';
                                            break;
                                        case 'en progreso':
                                            $badgeClass = 'bg-info';
                                            break;
                                        case 'resuelto':
                                            $badgeClass = 'bg-success';
                                            break;
                                        case 'cerrado':
                                            $badgeClass = 'bg-secondary';
                                            break;
                                        default:
                                            $badgeClass = 'bg-light text-dark';
                                    }
                                    
                                    $priorityClass = '';
                                    $priorityText = isset($ticket['urgencia']) ? strtolower($ticket['urgencia']) : 'media';
                                    switch($priorityText) {
                                        case 'alta':
                                            $priorityClass = 'priority-alta';
                                            break;
                                        case 'media':
                                            $priorityClass = 'priority-media';
                                            break;
                                        case 'baja':
                                            $priorityClass = 'priority-baja';
                                            break;
                                        default:
                                            $priorityClass = 'priority-media';
                                    }
                                    
                                    $fechaCreacion = new DateTime($ticket['fecha_creacion']);
                                    $hoy = new DateTime();
                                    $diferencia = $hoy->diff($fechaCreacion);
                                    $dias = $diferencia->days;
                                    $horas = $diferencia->h;
                                    $tiempoTranscurrido = '';
                                    
                                    if ($dias > 0) {
                                        $tiempoTranscurrido = 'Hace ' . $dias . ' día' . ($dias > 1 ? 's' : '');
                                    } else if ($horas > 0) {
                                        $tiempoTranscurrido = 'Hace ' . $horas . ' hora' . ($horas > 1 ? 's' : '');
                                    } else {
                                        $tiempoTranscurrido = 'Hace unos minutos';
                                    }
                                    
                                    // Obtener primeras palabras de la descripción
                                    $descripcionCorta = strlen($ticket['descripcion']) > 150 ? 
                                        substr($ticket['descripcion'], 0, 150) . '...' : $ticket['descripcion'];
                                ?>
                                <div class="list-group-item list-group-item-action p-3 ticket-card" 
                                     data-estado="<?php echo strtolower($ticket['estado']); ?>"
                                     data-fecha="<?php echo $ticket['fecha_creacion']; ?>"
                                     data-prioridad="<?php echo $priorityText; ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1 ticket-title">
                                            <span class="ticket-priority <?php echo $priorityClass; ?>"></span>
                                            <?php echo htmlspecialchars($ticket['titulo']); ?>
                                        </h5>
                                        <div>
                                            <span class="badge <?php echo $badgeClass; ?> me-2">
                                                <?php echo ucfirst($ticket['estado']); ?>
                                            </span>
                                            <small class="text-muted"><?php echo $tiempoTranscurrido; ?></small>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="ticket-meta">
                                            <span class="badge bg-light text-dark me-2">
                                                <i class="fas fa-hashtag me-1"></i>#<?php echo $ticket['id_ticket']; ?>
                                            </span>
                                            <span class="badge bg-light text-dark me-2">
                                                <i class="fas fa-<?php echo $ticket['categoria'] === 'hardware' ? 'desktop' : 'code'; ?> me-1"></i>
                                                <?php echo ucfirst($ticket['categoria'] ?? 'otro'); ?>
                                            </span>
                                            <span class="badge bg-light text-dark badge-urgencia">
                                                <i class="fas fa-<?php echo $priorityText === 'alta' ? 'exclamation-triangle' : ($priorityText === 'media' ? 'exclamation-circle' : 'info-circle'); ?> me-1"></i>
                                                <?php echo ucfirst($priorityText); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="ticket-actions">
                                            <a href="ver_ticket.php?id=<?php echo $ticket['id_ticket']; ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               data-bs-toggle="tooltip" 
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <?php if (strtolower($ticket['estado']) === 'pendiente'): ?>
                                            <a href="editar_ticket.php?id=<?php echo $ticket['id_ticket']; ?>" 
                                               class="btn btn-sm btn-outline-secondary ms-1"
                                               data-bs-toggle="tooltip" 
                                               title="Editar ticket">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($descripcionCorta)): ?>
                                    <p class="mb-0 mt-2 ticket-desc">
                                        <?php echo nl2br(htmlspecialchars($descripcionCorta)); ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <h4>No hay tickets</h4>
                                <p>No has creado ningún ticket todavía.</p>
                                <a href="crear_ticket.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-1"></i> Crear mi primer ticket
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (count($mis_tickets) > 0): ?>
                    <div class="card-footer bg-white border-top-0 pt-0">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm justify-content-end mb-0">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Anterior</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Siguiente</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activar tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Filtrado de tickets
            const filtroEstado = document.getElementById('filtroEstado');
            const ordenarPor = document.getElementById('ordenarPor');
            const buscarTicket = document.getElementById('buscarTicket');
            const tickets = document.querySelectorAll('.ticket-card');
            
            function filtrarTickets() {
                const estadoFiltro = filtroEstado.value.toLowerCase();
                const busqueda = buscarTicket.value.toLowerCase();
                
                tickets.forEach(ticket => {
                    const estado = ticket.getAttribute('data-estado');
                    const titulo = ticket.querySelector('.ticket-title').textContent.toLowerCase();
                    const descripcion = ticket.querySelector('.ticket-desc') ? 
                                      ticket.querySelector('.ticket-desc').textContent.toLowerCase() : '';
                    
                    const coincideEstado = !estadoFiltro || estado === estadoFiltro;
                    const coincideBusqueda = !busqueda || 
                                          titulo.includes(busqueda) || 
                                          descripcion.includes(busqueda);
                    
                    if (coincideEstado && coincideBusqueda) {
                        ticket.style.display = '';
                    } else {
                        ticket.style.display = 'none';
                    }
                });
                
                // Actualizar contador de tickets visibles
                const ticketsVisibles = document.querySelectorAll('.ticket-card[style=""]').length;
                const contador = document.querySelector('.card-header .badge');
                if (contador) {
                    contador.textContent = ticketsVisibles + ' tickets';
                }
            }
            
            function ordenarTickets() {
                const contenedor = document.querySelector('.list-group');
                const ticketsArray = Array.from(tickets);
                
                ticketsArray.sort((a, b) => {
                    const valorA = a.getAttribute('data-' + ordenarPor.value.split('_')[0]);
                    const valorB = b.getAttribute('data-' + ordenarPor.value.split('_')[0]);
                    
                    if (ordenarPor.value === 'fecha_desc') {
                        return new Date(valorB) - new Date(valorA);
                    } else if (ordenarPor.value === 'fecha_asc') {
                        return new Date(valorA) - new Date(valorB);
                    } else if (ordenarPor.value === 'prioridad') {
                        const prioridades = { 'alta': 3, 'media': 2, 'baja': 1 };
                        return (prioridades[valorB] || 0) - (prioridades[valorA] || 0);
                    }
                    return 0;
                });
                
                // Reordenar en el DOM
                ticketsArray.forEach(ticket => {
                    contenedor.appendChild(ticket);
                });
            }
            
            // Event listeners
            filtroEstado.addEventListener('change', filtrarTickets);
            ordenarPor.addEventListener('change', ordenarTickets);
            buscarTicket.addEventListener('input', filtrarTickets);
        });
    </script>
</body>
</html>
