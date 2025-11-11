<?php
// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Iniciar sesión
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

// Verificar rol de administrador
if ($_SESSION['rol'] !== 'admin') {
    $_SESSION['mensaje'] = [
        'tipo' => 'danger',
        'texto' => 'No tiene permisos para acceder a esta sección.'
    ];
    header('Location: dashboard.php');
    exit();
}

// Incluir clases
require_once 'clases/Usuario.php';
require_once 'clases/TicketManager.php';

// Inicializar variables
$mensaje = '';
$estados = [
    'todos' => 0,
    'pendiente' => 0,
    'en progreso' => 0,
    'resuelto' => 0,
    'cerrado' => 0
];

try {
    $ticketManager = new TicketManager();
    $usuarioManager = new Usuario();

    // POST: cambiar estado
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['cambiar_estado'])) {
            $id_ticket = filter_input(INPUT_POST, 'id_ticket', FILTER_VALIDATE_INT);
            $nuevo_estado = filter_input(INPUT_POST, 'nuevo_estado', FILTER_SANITIZE_STRING);
            $comentario = filter_input(INPUT_POST, 'comentario_admin', FILTER_SANITIZE_STRING) ?? '';

            if ($id_ticket && $nuevo_estado) {
                if ($ticketManager->cambiarEstado($id_ticket, $nuevo_estado, $_SESSION['id_usuario'], $comentario)) {
                    $_SESSION['mensaje'] = ['tipo' => 'success','texto' => 'El estado del ticket se ha actualizado correctamente.'];
                } else {
                    $_SESSION['mensaje'] = ['tipo' => 'danger','texto' => 'Error al actualizar el estado del ticket.'];
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }

        // POST: asignar docente
        if (isset($_POST['asignar_docente'])) {
            $id_ticket = filter_input(INPUT_POST, 'id_ticket', FILTER_VALIDATE_INT);
            $id_docente = filter_input(INPUT_POST, 'id_docente', FILTER_VALIDATE_INT);

            if ($id_ticket && $id_docente) {
                if ($ticketManager->asignarTicket($id_ticket, $id_docente, $_SESSION['id_usuario'])) {
                    $_SESSION['mensaje'] = ['tipo' => 'success','texto' => 'El ticket se ha asignado correctamente.'];
                } else {
                    $_SESSION['mensaje'] = ['tipo' => 'danger','texto' => 'Error al asignar el ticket.'];
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }

    // Obtener tickets
    $tickets = $ticketManager->listarTicketsTodos();
    if (!is_array($tickets)) $tickets = [];

    // Calcular estadísticas
    foreach ($tickets as $ticket) {
        $estado = strtolower($ticket['estado']);
        if (isset($estados[$estado])) $estados[$estado]++;
        $estados['todos']++;
    }

    // Obtener docentes para asignación
    $docentes = $usuarioManager->listarUsuariosPorRol('docente');

} catch (Exception $e) {
    error_log("Error en admin_tickets.php: " . $e->getMessage());
    $mensaje = "<div class='alert alert-danger'>Error al cargar los datos: " . htmlspecialchars($e->getMessage()) . "</div>";
    $tickets = [];
    $docentes = [];
}

// Mostrar mensaje de sesión
if (isset($_SESSION['mensaje'])) {
    $mensaje = sprintf(
        '<div class="alert alert-%s alert-dismissible fade show" role="alert">%s
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>',
        $_SESSION['mensaje']['tipo'],
        $_SESSION['mensaje']['texto']
    );
    unset($_SESSION['mensaje']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; padding: 2rem 0; font-family: Arial, sans-serif; }
        .ticket-table th, .ticket-table td { padding: 10px; }
        .ticket-table tr:hover { background-color: #d6eaff; }
        .usuario-avatar { display: inline-block; width: 28px; height: 28px; line-height: 28px; border-radius: 50%; background: #007BFF; color: white; text-align: center; margin-right: 5px; font-size: 0.8rem; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm mb-3"><i class="fas fa-arrow-left"></i> Volver al Dashboard</a>
    <h2>Panel de Administración de Tickets</h2>

    <?php echo $mensaje; ?>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3"><div class="alert alert-warning">Pendientes: <?php echo $estados['pendiente']; ?></div></div>
        <div class="col-md-3"><div class="alert alert-info">En Progreso: <?php echo $estados['en progreso']; ?></div></div>
        <div class="col-md-3"><div class="alert alert-success">Resueltos: <?php echo $estados['resuelto']; ?></div></div>
        <div class="col-md-3"><div class="alert alert-secondary">Cerrados: <?php echo $estados['cerrado']; ?></div></div>
    </div>

    <!-- Tickets -->
    <?php if (count($tickets) > 0): ?>
    <table class="table table-bordered ticket-table">
        <thead>
            <tr>
                <th>ID</th><th>Título</th><th>Usuario</th><th>Estado</th><th>Fecha</th><th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tickets as $ticket):
                $iniciales = '';
                $nombreUsuario = $ticket['nombre_usuario'] ?? 'N/A';
                if ($nombreUsuario !== 'N/A') {
                    $nombres = explode(' ', $nombreUsuario);
                    $iniciales = strtoupper(substr($nombres[0],0,1).(isset($nombres[1])?substr($nombres[1],0,1):''));
                }
            ?>
            <tr>
                <td>#<?php echo $ticket['id_ticket']; ?></td>
                <td><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                <td><span class="usuario-avatar"><?php echo $iniciales; ?></span><?php echo htmlspecialchars($nombreUsuario); ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="id_ticket" value="<?php echo $ticket['id_ticket']; ?>">
                        <input type="hidden" name="cambiar_estado" value="1">
                        <select name="nuevo_estado" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="pendiente" <?php echo strtolower($ticket['estado'])=='pendiente'?'selected':''; ?>>Pendiente</option>
                            <option value="en progreso" <?php echo strtolower($ticket['estado'])=='en progreso'?'selected':''; ?>>En progreso</option>
                            <option value="resuelto" <?php echo strtolower($ticket['estado'])=='resuelto'?'selected':''; ?>>Resuelto</option>
                            <option value="cerrado" <?php echo strtolower($ticket['estado'])=='cerrado'?'selected':''; ?>>Cerrado</option>
                        </select>
                    </form>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])); ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="id_ticket" value="<?php echo $ticket['id_ticket']; ?>">
                        <input type="hidden" name="asignar_docente" value="1">
                        <select name="id_docente" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Asignar a...</option>
                            <?php foreach ($docentes as $docente): ?>
                                <option value="<?php echo $docente['id_usuario']; ?>" <?php echo (isset($ticket['id_docente_asignado']) && $ticket['id_docente_asignado']==$docente['id_usuario'])?'selected':''; ?>>
                                    <?php echo htmlspecialchars($docente['nombre_completo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-info">No hay tickets en el sistema.</div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
