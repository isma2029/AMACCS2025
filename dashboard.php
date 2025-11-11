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

// Obtener datos del usuario
$nombre = $_SESSION['nombre'] ?? 'Usuario';
$rol = $_SESSION['rol'] ?? '';
$totalTickets = 0;
$ticketsPendientes = 0;

// Incluir archivos necesarios según el rol
if ($rol === 'admin') {
    require_once 'clases/Usuario.php';
    require_once 'clases/TicketManager.php';
    try {
        $ticketManager = new TicketManager();
        $totalTickets = $ticketManager->contarTickets();
        $ticketsPendientes = $ticketManager->contarTicketsPorEstado('pendiente');
    } catch (Exception $e) {
        error_log("Error en dashboard: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Soporte Local</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Fuentes elegantes -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Lora:wght@400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: #f7f3ec;
            --deep-green: #234d35;
            --gold: #bfa56a;
            --text-dark: #2e2e2e;
            --soft-olive: #6b7b6b;
            --shadow: 0 6px 18px rgba(34,77,53,0.08);
        }

        body {
            background: linear-gradient(180deg, var(--bg-main) 0%, #eee7d8 100%);
            font-family: "Lora", serif;
            color: var(--text-dark);
            min-height: 100vh;
            padding: 3rem 1rem;
        }

        .container-dashboard {
            max-width: 700px;
            margin: 0 auto;
            background: #ffffffcc;
            border-radius: 14px;
            box-shadow: var(--shadow);
            padding: 2rem 2.5rem;
            border: 1px solid rgba(34,77,53,0.06);
        }

        h2 {
            font-family: "Playfair Display", serif;
            color: var(--deep-green);
            font-weight: 600;
            margin-bottom: .5rem;
        }

        p {
            color: var(--soft-olive);
            font-size: 1.05rem;
            margin-bottom: 1rem;
        }

        hr {
            border: none;
            height: 1px;
            background: rgba(34,77,53,0.12);
            margin: 1.5rem 0;
        }

        ul {
            list-style: none;
            padding-left: 0;
        }

        ul li {
            margin-bottom: .75rem;
        }

        ul li a {
            display: block;
            background: #fffdfa;
            color: var(--deep-green);
            border-left: 4px solid var(--gold);
            text-decoration: none;
            padding: .65rem 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(34,77,53,0.04);
            transition: all 0.2s ease;
            font-weight: 600;
            font-size: 1rem;
        }

        ul li a:hover {
            background: linear-gradient(90deg, rgba(191,165,106,0.08), rgba(35,77,53,0.06));
            transform: translateX(3px);
            color: #2a4b34;
        }

        .logout-link {
            text-decoration: none;
            font-weight: 600;
            color: var(--deep-green);
            border: 1px solid var(--gold);
            padding: .45rem 1rem;
            border-radius: 8px;
            background: #fefcf8;
            transition: all 0.2s ease;
        }

        .logout-link:hover {
            background: var(--gold);
            color: white;
            border-color: var(--gold);
        }

        .footer-text {
            text-align: center;
            color: var(--soft-olive);
            font-size: .9rem;
            margin-top: 1.8rem;
        }

        @media (max-width: 576px) {
            .container-dashboard {
                padding: 1.5rem;
            }
            h2 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>

<body>
    <div class="container-dashboard">
        <h2>Bienvenido, <?php echo htmlspecialchars($nombre); ?>!</h2>
        <p>Tu rol: <strong><?php echo htmlspecialchars($rol); ?></strong></p>
        <hr>

        <?php if ($rol == 'docente') { ?>
            <ul>
                <li><a href="solicitar_software.php">🧾 Solicitar Software</a></li>
                <li><a href="mis_tickets.php">🎟️ Mis Tickets</a></li>
            </ul>
        <?php } elseif ($rol == 'admin') { ?>
            <ul>
                <li><a href="admin_solicitudes.php">📋 Ver Solicitudes de Software</a></li>
                <li><a href="admin_tickets.php">🧰 Ver Tickets</a></li>
                <li><a href="gestion_equipos.php">💻 Gestionar Equipos</a></li>
                <li><a href="usuarios.php">👥 Gestionar Usuarios</a></li>
            </ul>
        <?php } ?>

        <hr>
        <a class="logout-link" href="logout.php">Cerrar Sesión</a>

        <div class="footer-text">
            <em>Soporte Local &copy; 2025 — Inspirado en el estilo clásico Old Money</em>
        </div>
    </div>
</body>
</html>
