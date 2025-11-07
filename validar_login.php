<?php
session_start();
require_once 'clases/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    try {
        $conexion = new Conexion();
        $db = $conexion->getConexion();

        $sql = "SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Si la contraseña en la BD está con MD5:
            if ($user['contrasena'] === md5($contrasena)) {
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['id_usuario'] = $user['id_usuario'];

                if ($user['rol'] === 'admin') {
                    header("Location: admin_inicio.php");
                } else {
                    header("Location: docente_inicio.php");
                }
                exit();
            } else {
                echo "<script>alert('Contraseña incorrecta'); window.location='index.php';</script>";
            }
        } else {
            echo "<script>alert('Usuario no encontrado'); window.location='index.php';</script>";
        }
    } catch (PDOException $e) {
        echo "Error en conexión: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
