<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>GeekZone — Registrarse</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/register.css" />
</head>

<body>
    <div class="auth-page">
        <div class="auth-card" style="max-width:500px">
            <div class="auth-card-header">
                <div class="nav-logo" style="font-size:2.2rem;display:block;text-align:center">Geek<span>Zone</span>
                </div>
                <p style="color:rgba(255,255,255,.75);font-size:.9rem;margin-top:.4rem;text-align:center">Únete a la
                    comunidad friki más grande.</p>
            </div>
            <div class="auth-card-body">
                <form id="registerForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nombre</label>
                            <input id="name" type="text" class="form-control" placeholder="Tu nombre" required/>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Apellidos</label>
                            <input id="surname" type="text" class="form-control" placeholder="Tus apellidos" required/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nombre de usuario</label>
                        <input id="username" type="text" class="form-control" placeholder="@usuario_friki" required/>
                        <p class="form-hint">Visible en tu perfil público</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo electrónico</label>
                        <input id="email" type="email" class="form-control" placeholder="tu@email.com" required/>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Contraseña</label>
                            <input id="password" type="password" class="form-control" placeholder="Min. 6 caracteres" required/>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirmar contraseña</label>
                            <input id="passwordConfirm" type="password" class="form-control" placeholder="Repite la contraseña" required/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label" style="margin-bottom:.8rem">
                            <input type="checkbox" style="accent-color:var(--cobalt)" required/>
                            <span style="font-size:.85rem;color:var(--grey)">Acepto los <a href="#"
                                    style="color:var(--cobalt-light)">Términos de uso</a> y la <a href="#"
                                    style="color:var(--cobalt-light)">Política de privacidad</a></span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-gold btn-block btn-lg" style="text-align:center">Crear cuenta</button>
                </form>
            </div>
            <div class="auth-footer">
                ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault(); // Evita que la página se recargue

            const data = {
                name: document.getElementById('name').value,
                surname: document.getElementById('surname').value,
                username: document.getElementById('username').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                password_confirmation: document.getElementById('passwordConfirm').value
            };

            // Llamada a tu ruta de API
            const response = await fetch('/api/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                // GUARDAR EL TOKEN: Esto es lo más importante
                localStorage.setItem('token', result.token);

                // Redireccionar al perfil o home
                window.location.href = "{{ route('shop') }}";
            } else {
                alert('Error en el register: ' + result.error);
            }
        });
    </script>
</body>

</html>
