<?php
session_start();
require_once 'clases/ticket.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['accion'])) {
    $ticketObj = new Ticket();
    $id = $_GET['id'];
    $accion = $_GET['accion'];

    if ($accion == 'resuelto') {
        $ticketObj->actualizarEstado($id, 'resuelto');
    }

    header("Location: admin_tickets.php");
    exit();
}
