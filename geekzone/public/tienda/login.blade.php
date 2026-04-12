<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>GeekZone — Iniciar Sesión</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/login.css" />
</head>

<body>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-card-header">
                <div class="nav-logo" style="font-size:2.2rem;display:block;text-align:center">Geek<span>Zone</span>
                </div>
                <p style="color:rgba(255,255,255,.75);font-size:.9rem;margin-top:.4rem;text-align:center">Bienvenido de
                    vuelta. Entra a tu universo.</p>
            </div>
            <div class="auth-card-body">
                <div class="alert alert-info" style="margin-bottom:1.5rem">
                    <span>🔐</span> Inicia sesión para acceder a tus pedidos y favoritos.
                </div>
                <div class="form-group">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" placeholder="tu@email.com" />
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <input type="password" class="form-control" placeholder="••••••••" />
                </div>
                <div class="remember-row">
                    <label class="checkbox-label">
                        <input type="checkbox" /> Recordarme
                    </label>
                    <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                </div>
                <a href="panel-usuario.html" class="btn btn-primary btn-block btn-lg" style="text-align:center">Iniciar
                    sesión</a>
            </div>
            <div class="auth-footer">
                ¿No tienes cuenta? <a href="register.html">Regístrate aquí</a>
            </div>
        </div>
    </div>
</body>

</html>