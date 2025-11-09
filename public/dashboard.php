<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

require_once '../config/database.php';

// Obtener información del usuario
$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['nombre'];
$rol_usuario = $_SESSION['rol'];

// Obtener estadísticas para el dashboard
$estadisticas = [];

try {
    // Total de tickets del usuario (o todos si es administrador)
    $sql = "SELECT COUNT(*) as total FROM tickets";
    if ($rol_usuario !== 'administrador') {
        $sql .= " WHERE id_usuario_solicitante = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario_id]);
    } else {
        $stmt = $conn->query($sql);
    }
    $estadisticas['total_tickets'] = $stmt->fetchColumn();

    // Tickets abiertos
    $sql = "SELECT COUNT(*) as abiertos FROM tickets WHERE estado = 'abierto'";
    if ($rol_usuario !== 'administrador') {
        $sql .= " AND id_usuario_solicitante = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario_id]);
    } else {
        $stmt = $conn->query($sql);
    }
    $estadisticas['tickets_abiertos'] = $stmt->fetchColumn();

    // Tickets en progreso
    $sql = "SELECT COUNT(*) as en_progreso FROM tickets WHERE estado = 'en_progreso'";
    if ($rol_usuario !== 'administrador') {
        $sql .= " AND id_tecnico_asignado = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario_id]);
    } else {
        $stmt = $conn->query($sql);
    }
    $estadisticas['en_progreso'] = $stmt->fetchColumn();

    // Últimos tickets
    $sql = "SELECT t.*, u.nombre as nombre_solicitante, u.apellido as apellido_solicitante 
            FROM tickets t 
            JOIN usuarios u ON t.id_usuario_solicitante = u.id_usuario";
    
    if ($rol_usuario === 'usuario') {
        $sql .= " WHERE t.id_usuario_solicitante = ?";
    } elseif ($rol_usuario === 'tecnico') {
        $sql .= " WHERE t.id_tecnico_asignado = ? OR t.estado = 'abierto'";
    }
    
    $sql .= " ORDER BY t.fecha_creacion DESC LIMIT 5";
    
    $stmt = $conn->prepare($sql);
    if ($rol_usuario !== 'administrador') {
        $stmt->execute([$usuario_id]);
    } else {
        $stmt->execute();
    }
    $ultimos_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Error al cargar el dashboard: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AMACCS 2025</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
            color: white;
            padding: 20px 0;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            margin: 5px 0;
            border-radius: 0;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: #343a40;
            color: white;
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .main-content {
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-icon {
            font-size: 2rem;
            margin-bottom: 15px;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="text-center mb-4">
                    <h4>AMACCS 2025</h4>
                    <p class="text-muted small">Sistema de Tickets</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="nuevo_ticket.php">
                            <i class="bi bi-plus-circle"></i> Nuevo Ticket
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mis_tickets.php">
                            <i class="bi bi-ticket-detailed"></i> Mis Tickets
                        </a>
                    </li>
                    <?php if ($rol_usuario === 'administrador' || $rol_usuario === 'tecnico'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="gestion_tickets.php">
                            <i class="bi bi-gear"></i> Gestionar Tickets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="equipos.php">
                            <i class="bi bi-pc-display"></i> Equipos
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($rol_usuario === 'administrador'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="usuarios.php">
                            <i class="bi bi-people"></i> Usuarios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reportes.php">
                            <i class="bi bi-graph-up"></i> Reportes
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item mt-4">
                        <a class="nav-link" href="perfil.php">
                            <i class="bi bi-person"></i> Mi Perfil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?></h2>
                    <span class="badge bg-primary"><?php echo ucfirst($rol_usuario); ?></span>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="card-icon">
                                    <i class="bi bi-ticket-detailed"></i>
                                </div>
                                <h3><?php echo $estadisticas['total_tickets']; ?></h3>
                                <p class="text-muted">Total de Tickets</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="card-icon">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <h3><?php echo $estadisticas['tickets_abiertos']; ?></h3>
                                <p class="text-muted">Tickets Abiertos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="card-icon">
                                    <i class="bi bi-tools"></i>
                                </div>
                                <h3><?php echo $estadisticas['en_progreso']; ?></h3>
                                <p class="text-muted">En Progreso</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimos Tickets -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tickets Recientes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($ultimos_tickets) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Título</th>
                                            <th>Solicitante</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ultimos_tickets as $ticket): 
                                            $estado_clase = [
                                                'abierto' => 'bg-warning',
                                                'en_progreso' => 'bg-info',
                                                'cerrado' => 'bg-success',
                                                'cancelado' => 'bg-danger'
                                            ][$ticket['estado']] ?? 'bg-secondary';
                                        ?>
                                            <tr>
                                                <td>#<?php echo $ticket['id_ticket']; ?></td>
                                                <td><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                                                <td><?php echo htmlspecialchars($ticket['nombre_solicitante'] . ' ' . $ticket['apellido_solicitante']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $estado_clase; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $ticket['estado'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="ver_ticket.php?id=<?php echo $ticket['id_ticket']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye"></i> Ver
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No hay tickets recientes.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
