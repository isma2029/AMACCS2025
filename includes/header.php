<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Tickets - AMACCS</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
        }
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }
        .card-header {
            border-bottom: none;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 8px 20px;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
        .form-text {
            color: var(--secondary-color);
            font-size: 0.875em;
        }
        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875em;
        }
        .was-validated .form-control:invalid ~ .invalid-feedback,
        .was-validated .form-select:invalid ~ .invalid-feedback {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-tools me-2"></i>AMACCS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isset($_SESSION['id_usuario'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-1"></i> Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="crear_ticket.php"><i class="fas fa-plus-circle me-1"></i> Nuevo Ticket</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mis_tickets.php"><i class="fas fa-ticket-alt me-1"></i> Mis Tickets</a>
                        </li>
                        <?php if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'tecnico'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin_tickets.php"><i class="fas fa-tasks me-1"></i> Administrar Tickets</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['id_usuario'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user-edit me-2"></i>Mi Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php"><i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php
        // Mostrar mensajes de sesión
        if (isset($_SESSION['mensaje'])) {
            echo '<div class="alert alert-' . $_SESSION['mensaje']['tipo'] . ' alert-dismissible fade show" role="alert">';
            echo $_SESSION['mensaje']['texto'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            unset($_SESSION['mensaje']);
        }
        ?>
