<?php
session_start();
require_once 'clases/usuario.php';

$mensaje = "";

if (isset($_POST['login'])) {
    $usuarioObj = new Usuario();
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $data = $usuarioObj->validarLogin($usuario, $contrasena);

    if ($data) {
        // Guardar datos en sesión
        $_SESSION['id_usuario'] = $data['id_usuario'];
        $_SESSION['usuario'] = $data['usuario'];
        $_SESSION['rol'] = $data['rol'];
        $_SESSION['nombre'] = $data['nombre_completo'];

        // Si es primer inicio, puedes redirigir a cambiar contraseña
        if ($data['primer_inicio']) {
            header("Location: cambiar_contrasena.php");
            exit();
        } else {
            header("Location: dashboard.php");
            exit();
        }
    } else {
        $mensaje = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Soporte Local</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Fuentes elegantes -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Montserrat:wght@300;500;700&display=swap" rel="stylesheet">

    <style>
        :root{
            --cream: #F7F3EA;
            --deep-green: #254E3A; /* verde profundo */
            --olive: #3E6B4A;
            --gold: #C9A24A;
            --muted: #7B6E5A;
            --card-border: rgba(37,78,58,0.08);
        }

        html,body{
            height:100%;
            background: linear-gradient(180deg, var(--cream) 0%, #F1EBE0 100%);
            font-family: "Montserrat", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            color: var(--deep-green);
        }

        .login-wrapper{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:2rem;
        }

        .login-card{
            width:100%;
            max-width:520px;
            background: rgba(255,255,255,0.9);
            border-radius:14px;
            box-shadow: 0 8px 30px rgba(15, 23, 17, 0.12);
            border: 1px solid var(--card-border);
            overflow:hidden;
            display:flex;
            flex-direction:row;
        }

        /* Panel lateral con textura / color */
        .login-aside{
            background: linear-gradient(180deg, rgba(37,78,58,0.95), rgba(62,107,74,0.95));
            color: #f7f3ea;
            padding:28px;
            width:45%;
            min-width:160px;
            display:flex;
            flex-direction:column;
            justify-content:center;
            gap:12px;
            text-align:center;
        }

        .brand-title{
            font-family: "Playfair Display", serif;
            font-size:1.6rem;
            letter-spacing:0.6px;
            font-weight:700;
            margin-bottom:6px;
        }

        .brand-sub{
            font-size:0.88rem;
            opacity:0.95;
        }

        .login-form{
            padding:32px 36px;
            width:55%;
            min-width:260px;
        }

        .form-label{
            color:var(--muted);
            font-size:0.95rem;
            font-weight:600;
        }

        .form-control{
            border-radius:8px;
            border:1px solid rgba(37,78,58,0.12);
            padding:10px 12px;
            background: #fff;
            box-shadow: none;
            transition: box-shadow .15s ease, border-color .15s ease;
        }

        .form-control:focus{
            outline: none;
            border-color: var(--olive);
            box-shadow: 0 0 0 4px rgba(62,107,74,0.06);
        }

        .btn-primary.custom{
            background: linear-gradient(180deg, var(--gold), #b7892f);
            border: none;
            color: #1d1d1d;
            font-weight:700;
            padding:10px 16px;
            border-radius:8px;
            box-shadow: 0 6px 12px rgba(193,146,74,0.18);
        }

        .btn-secondary.link-plain{
            background:transparent;
            border:1px solid rgba(37,78,58,0.08);
            color: var(--deep-green);
            border-radius:8px;
            padding:8px 12px;
            font-weight:600;
        }

        .footer-note{
            font-size:0.82rem;
            color:var(--muted);
            margin-top:14px;
        }

        .help-row{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:10px;
            margin-top:14px;
        }

        /* Mensaje de error */
        .alert-custom{
            background: #fff6f6;
            color:#8b1d1d;
            border:1px solid rgba(139,29,29,0.08);
            padding:8px 12px;
            border-radius:8px;
            font-weight:600;
        }

        /* Responsive: apila columnas */
        @media (max-width:700px){
            .login-card{
                flex-direction:column;
                max-width:420px;
            }
            .login-aside{
                width:100%;
                padding:18px;
            }
            .login-form{
                width:100%;
                padding:24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-aside">
                <div>
                    <div class="brand-title">Soporte Local</div>
                    <div class="brand-sub">Portal administrativo</div>
                </div>
                <div style="font-size:0.9rem; opacity:0.95; margin-top:8px;">
                    Accede de forma segura<br>Gestiona incidencias con estilo.
                </div>
            </div>

            <div class="login-form">
                <h3 style="font-family:'Playfair Display', serif; margin-bottom:8px;">Bienvenido</h3>
                <p style="margin-bottom:18px; color:var(--muted);">Ingresa tu usuario y contraseña para continuar.</p>

                <?php if($mensaje !== ""): ?>
                    <div class="alert-custom mb-3"><?php echo htmlspecialchars($mensaje); ?></div>
                <?php endif; ?>

                <form method="POST" autocomplete="off" novalidate>
                    <div class="mb-3">
                        <label class="form-label" for="usuario">Usuario</label>
                        <input id="usuario" name="usuario" type="text" class="form-control" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="contrasena">Contraseña</label>
                        <input id="contrasena" name="contrasena" type="password" class="form-control" required>
                    </div>

                    <div class="d-grid gap-2">
                        <input type="submit" name="login" class="btn btn-primary custom" value="Ingresar">
                    </div>

                    <div class="help-row">
                        <button type="button" class="btn btn-secondary link-plain" onclick="location.href='recuperar_contrasena.php'">¿Olvidaste la contraseña?</button>
                        <small class="footer-note">Usuario y contraseñas son confidenciales.</small>
                    </div>
                </form>

                <div style="margin-top:18px; border-top:1px solid rgba(37,78,58,0.04); padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
                    <small style="color:var(--muted);">© <?php echo date('Y'); ?> Soporte Local</small>
                    <small style="font-family:'Playfair Display', serif; color:var(--muted);">Acceso seguro</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (opcional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
