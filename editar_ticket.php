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
        'texto' => 'No se especificó un ticket para editar.'
    ];
    header('Location: mis_tickets.php');
    exit();
}

// Obtener información del ticket
$ticketManager = new TicketManager();
$ticket = $ticketManager->obtenerTicketPorId($id_ticket);

// Verificar que el ticket exista
if (!$ticket) {
    $_SESSION['mensaje'] = [
        'tipo' => 'danger',
        'texto' => 'El ticket solicitado no existe.'
    ];
    header('Location: mis_tickets.php');
    exit();
}

// Verificar permisos (solo el dueño o admin pueden editar)
$esAdmin = in_array($_SESSION['rol'], ['admin', 'tecnico']);
$esDuenio = ($ticket['id_usuario'] == $_SESSION['id_usuario']);

if (!$esAdmin && !$esDuenio) {
    $_SESSION['mensaje'] = [
        'tipo' => 'danger',
        'texto' => 'No tiene permisos para editar este ticket.'
    ];
    header('Location: ver_ticket.php?id=' . $id_ticket);
    exit();
}

// Obtener lista de equipos
$pdo = Conexion::obtenerInstancia()->getConexion();
$equipos = $pdo->query("SELECT id_equipo, numero_serie, nombre_equipo FROM equipos")->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y limpiar datos
    $id_equipo = !empty($_POST['id_equipo']) ? (int)$_POST['id_equipo'] : null;
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $urgencia = $_POST['urgencia'];
    $categoria = $_POST['categoria'];
    
    // Validar campos requeridos
    $errores = [];
    
    if (empty($titulo)) {
        $errores[] = 'El título es obligatorio';
    }
    
    if (empty($descripcion)) {
        $errores[] = 'La descripción es obligatoria';
    }
    
    if (empty($errores)) {
        // Actualizar el ticket directamente con SQL simple
        try {
            $sql = "UPDATE tickets SET 
                    titulo = :titulo,
                    descripcion = :descripcion,
                    urgencia = :urgencia,
                    categoria = :categoria,
                    id_equipo = :id_equipo,
                    fecha_actualizacion = NOW()
                    WHERE id_ticket = :id_ticket";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':urgencia', $urgencia);
            $stmt->bindParam(':categoria', $categoria);
            $stmt->bindValue(':id_equipo', $id_equipo, $id_equipo ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindParam(':id_ticket', $id_ticket, PDO::PARAM_INT);
            $stmt->execute();
            
            $_SESSION['mensaje'] = [
                'tipo' => 'success',
                'texto' => 'Ticket actualizado correctamente.'
            ];
            
            header('Location: ver_ticket.php?id=' . $id_ticket);
            exit();
            
        } catch (Exception $e) {
            $errores[] = 'Error al actualizar el ticket: ' . $e->getMessage();
        }
    }
    
    if (!empty($errores)) {
        $mensaje_error = '<div class="alert alert-danger"><ul class="mb-0">';
        foreach ($errores as $error) {
            $mensaje_error .= '<li>' . htmlspecialchars($error) . '</li>';
        }
        $mensaje_error .= '</ul></div>';
    }
}

// Incluir encabezado
$titulo_pagina = 'Editar Ticket #' . $id_ticket;
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Editar Ticket #<?= $id_ticket ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?= $mensaje_error ?? '' ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" 
                                   value="<?= htmlspecialchars($ticket['titulo']) ?>" required>
                            <div class="invalid-feedback">
                                Por favor ingresa un título para el ticket.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                    rows="4" required><?= htmlspecialchars($ticket['descripcion']) ?></textarea>
                            <div class="invalid-feedback">
                                Por favor describe el problema con detalle.
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="urgencia" class="form-label">Urgencia</label>
                                <select class="form-select" id="urgencia" name="urgencia" required>
                                    <option value="baja" <?= $ticket['urgencia'] === 'baja' ? 'selected' : '' ?>>Baja</option>
                                    <option value="media" <?= $ticket['urgencia'] === 'media' ? 'selected' : '' ?>>Media</option>
                                    <option value="alta" <?= $ticket['urgencia'] === 'alta' ? 'selected' : '' ?>>Alta</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="categoria" name="categoria" required>
                                    <option value="hardware" <?= $ticket['categoria'] === 'hardware' ? 'selected' : '' ?>>Hardware</option>
                                    <option value="software" <?= $ticket['categoria'] === 'software' ? 'selected' : '' ?>>Software</option>
                                    <option value="red" <?= $ticket['categoria'] === 'red' ? 'selected' : '' ?>>Red</option>
                                    <option value="otro" <?= $ticket['categoria'] === 'otro' ? 'selected' : '' ?>>Otro</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="id_equipo" class="form-label">Equipo (opcional)</label>
                            <select class="form-select" id="id_equipo" name="id_equipo">
                                <option value="">-- Sin equipo --</option>
                                <?php foreach($equipos as $equipo): ?>
                                    <option value="<?= $equipo['id_equipo'] ?>" 
                                        <?= ($ticket['id_equipo'] == $equipo['id_equipo']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($equipo['numero_serie'] . ' - ' . $equipo['nombre_equipo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="ver_ticket.php?id=<?= $id_ticket ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar cambios
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
