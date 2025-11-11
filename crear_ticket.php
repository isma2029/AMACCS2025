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
require_once 'clases/GestionUsuarios.php';
require_once 'clases/SoftwareManager.php';

// Obtener ID de usuario de la sesión
$user_id = $_SESSION['id_usuario'];

// Inicializar variables
$tm = new TicketManager();
$mensaje = '';
$categorias = [];
$equipos = [];

// Manejar mensajes de la sesión
if (isset($_SESSION['mensaje'])) {
    $mensaje = sprintf(
        '<div class="alert alert-%s alert-dismissible fade show" role="alert">%s<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>',
        $_SESSION['mensaje']['tipo'],
        $_SESSION['mensaje']['texto']
    );
    unset($_SESSION['mensaje']);
}

// Obtener lista de equipos
$pdo = Conexion::obtenerInstancia()->getConexion();
$equipos = $pdo->query("SELECT id_equipo, numero_serie, nombre_equipo FROM equipos")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_equipo = $_POST['id_equipo'] ?: null; // permitir nulo
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $urgencia = $_POST['urgencia'];
    $categoria = $_POST['categoria'];
    $id_ticket = $tm->crearTicket($_SESSION['id_usuario'], $id_equipo, $titulo, $descripcion, $urgencia, $categoria);
    header("Location: mis_tickets.php?created=1");
    exit;
}
?>
<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Nuevo Ticket de Soporte</h4>
                </div>
                <div class="card-body">
                    <?= $mensaje ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="id_equipo" class="form-label">Equipo (opcional)</label>
                            <select class="form-select" id="id_equipo" name="id_equipo">
                                <option value="" selected>-- Selecciona un equipo (opcional) --</option>
                                <?php foreach($equipos as $e): ?>
                                    <option value="<?= $e['id_equipo'] ?>">
                                        <?= htmlspecialchars($e['numero_serie']) ?> - <?= htmlspecialchars($e['nombre_equipo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Si el problema está relacionado con un equipo específico, selecciónalo.</div>
                        </div>

                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título del problema</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Ej: No enciende la computadora" required>
                            <div class="invalid-feedback">
                                Por favor ingresa un título para el ticket.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción detallada</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" placeholder="Describe el problema con el mayor detalle posible..." required></textarea>
                            <div class="invalid-feedback">
                                Por favor describe el problema.
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="urgencia" class="form-label">Nivel de urgencia</label>
                                <select class="form-select" id="urgencia" name="urgencia" required>
                                    <option value="baja">Baja</option>
                                    <option value="media" selected>Media</option>
                                    <option value="alta">Alta</option>
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> Selecciona la urgencia del problema.
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="categoria" name="categoria" required>
                                    <option value="hardware">Hardware</option>
                                    <option value="software">Software</option>
                                    <option value="red">Red</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="mis_tickets.php" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Enviar Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación del formulario
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()
</script>

<?php include 'includes/footer.php'; ?>
