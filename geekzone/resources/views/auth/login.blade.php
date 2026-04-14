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
                <form id="loginForm">
                    <div class="form-group">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" id="email" class="form-control" placeholder="tu@email.com" />
                        <span id="err-email" style="display:none;margin-top:.35rem;font-size:.8rem;color:#f87171;font-family:'Barlow Condensed',sans-serif;letter-spacing:.5px;"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contraseña</label>
                        <input type="password" id="password" class="form-control" placeholder="••••••••" />
                        <span id="err-password" style="display:none;margin-top:.35rem;font-size:.8rem;color:#f87171;font-family:'Barlow Condensed',sans-serif;letter-spacing:.5px;"></span>
                    </div>
                    <div class="remember-row">
                        <label class="checkbox-label">
                            <input type="checkbox" id="remember" /> Recordarme
                        </label>
                    </div>
                    <p id="err-general" style="display:none;margin-bottom:.8rem;font-size:.85rem;color:#f87171;font-family:'Barlow Condensed',sans-serif;letter-spacing:.5px;"></p>
                    <button type="submit" id="submit-btn" class="btn btn-primary btn-block btn-lg" style="text-align:center">Iniciar sesión</button>
                </form>
            </div>
            <div class="auth-footer">
                ¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a>
            </div>
        </div>
    </div>
</body>

<script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        // Limpiar errores previos
        ['email', 'password'].forEach(id => {
            document.getElementById('err-' + id).style.display = 'none';
            document.getElementById(id).style.borderColor = '';
        });
        document.getElementById('err-general').style.display = 'none';

        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Iniciando sesión…';

        const data = {
            email: document.getElementById('email').value,
            password: document.getElementById('password').value
        };

        const response = await fetch('/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok) {
            const remember = document.getElementById('remember').checked;
            const storage  = remember ? localStorage : sessionStorage;
            storage.setItem('token', result.token);
            storage.setItem('role', result.user.role);
            window.location.href = "{{ route('shop') }}";
        } else {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Iniciar sesión';

            if (result.errors) {
                // Errores de validación campo por campo
                Object.entries(result.errors).forEach(([field, msgs]) => {
                    const span = document.getElementById('err-' + field);
                    const input = document.getElementById(field);
                    if (span)  { span.textContent = msgs[0]; span.style.display = 'block'; }
                    if (input) input.style.borderColor = '#ef4444';
                });
            } else {
                // Error general (credenciales incorrectas, etc.)
                const errEl = document.getElementById('err-general');
                errEl.textContent = result.message || 'Error al iniciar sesión.';
                errEl.style.display = 'block';
            }
        }
    });

    // Limpiar error de campo al escribir
    ['email', 'password'].forEach(id => {
        document.getElementById(id).addEventListener('input', () => {
            document.getElementById('err-' + id).style.display = 'none';
            document.getElementById(id).style.borderColor = '';
        });
    });
</script>

</html>
