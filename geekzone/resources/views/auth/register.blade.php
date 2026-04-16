<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>GeekZone — Registrarse</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/register.css') }}" />
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="{{ asset('js/auth.js') }}"></script>
</head>

<body>

    <!-- Loader de página (mismo que el resto del sitio) -->
    <div id="page-loader" role="status" aria-label="Cargando">
        <div class="loader-logo">Geek<span>Zone</span></div>
        <div class="loader-spinner" aria-hidden="true"></div>
        <p class="loader-text">Cargando…</p>
    </div>

    <div class="auth-page">
        <div class="auth-card" style="max-width:500px">
            <div class="auth-card-header">
                <div class="nav-logo" style="font-size:2.2rem;display:block;text-align:center">Geek<span>Zone</span></div>
                <p style="color:rgba(255,255,255,.75);font-size:.9rem;margin-top:.4rem;text-align:center">Únete a la comunidad friki más grande.</p>
            </div>
            <div class="auth-card-body">

                {{-- Error general --}}
                <div id="alert-error" class="alert alert-error" role="alert">
                    <span>⚠️</span>
                    <span id="alert-error-msg"></span>
                </div>

                <form id="registerForm" novalidate>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nombre</label>
                            <input id="name" type="text" class="form-control" placeholder="Tu nombre" autocomplete="given-name" />
                            <span class="field-error" id="err-name"></span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Apellidos</label>
                            <input id="surname" type="text" class="form-control" placeholder="Tus apellidos" autocomplete="family-name" />
                            <span class="field-error" id="err-surname"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nombre de usuario</label>
                        <input id="username" type="text" class="form-control" placeholder="@usuario_friki" autocomplete="username" />
                        <span class="form-hint">Visible en tu perfil público</span>
                        <span class="field-error" id="err-username"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo electrónico</label>
                        <input id="email" type="email" class="form-control" placeholder="tu@email.com" autocomplete="email" />
                        <span class="field-error" id="err-email"></span>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Contraseña</label>
                            <input id="password" type="password" class="form-control" placeholder="Mín. 6 caracteres" autocomplete="new-password" />
                            <span class="field-error" id="err-password"></span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirmar contraseña</label>
                            <input id="passwordConfirm" type="password" class="form-control" placeholder="Repite la contraseña" autocomplete="new-password" />
                            <span class="field-error" id="err-passwordConfirm"></span>
                        </div>
                    </div>
                    <div class="captcha" style="width:100%; display:flex; justify-content:center; margin:1.5rem 0;">
                        <div class="g-recaptcha" data-sitekey="6LdKObcsAAAAAIhT2WoE0dKLNcU8uDHdq2GdGAHp"></div>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label" style="margin-bottom:.8rem">
                            <input type="checkbox" id="terms" style="accent-color:var(--cobalt)" />
                            <span style="font-size:.85rem;color:var(--grey)">Acepto los
                                <a href="#" style="color:var(--cobalt-light)">Términos de uso</a> y la
                                <a href="#" style="color:var(--cobalt-light)">Política de privacidad</a>
                            </span>
                        </label>
                        <span class="field-error" id="err-terms"></span>
                    </div>
                    <button type="submit" id="submit-btn" class="btn btn-gold btn-block btn-lg" style="text-align:center">
                        Crear cuenta
                    </button>
                </form>
            </div>
            <div class="auth-footer">
                ¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
            </div>
        </div>
    </div>

    <script>
        // ─── Ocultar loader cuando la página está lista ───
        const registerLoader = document.getElementById('page-loader');
        if (registerLoader) {
            function ocultarLoader() {
                registerLoader.classList.add('loader-hidden');
                setTimeout(() => registerLoader.style.display = 'none', 600);
            }
            if (document.readyState === 'complete') {
                ocultarLoader();
            } else {
                window.addEventListener('load', ocultarLoader);
            }
        }

        // ─── Lógica del formulario de registro ───
        const form      = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submit-btn');

        // ——— Helpers ———
        function clearErrors() {
            document.getElementById('alert-error').style.display = 'none';
            document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
        }

        function showFieldError(fieldId, msg) {
            const input = document.getElementById(fieldId);
            const span  = document.getElementById('err-' + fieldId);
            if (input) input.classList.add('is-invalid');
            if (span)  span.textContent = msg;
        }

        function showGeneralError(msg) {
            const box = document.getElementById('alert-error');
            document.getElementById('alert-error-msg').textContent = msg;
            box.style.display = 'flex';
        }

        function setLoading(loading) {
            submitBtn.disabled = loading;
            submitBtn.textContent = loading ? 'Creando cuenta…' : 'Crear cuenta';
        }

        // ——— Validación cliente ———
        function validateClient() {
            let valid = true;

            const fields = ['name', 'surname', 'username', 'email', 'password', 'passwordConfirm'];
            fields.forEach(id => {
                if (!document.getElementById(id).value.trim()) {
                    const labels = {
                        name: 'El nombre es obligatorio.',
                        surname: 'Los apellidos son obligatorios.',
                        username: 'El nombre de usuario es obligatorio.',
                        email: 'El email es obligatorio.',
                        password: 'La contraseña es obligatoria.',
                        passwordConfirm: 'Confirma tu contraseña.',
                    };
                    showFieldError(id, labels[id]);
                    valid = false;
                }
            });

            const pwd  = document.getElementById('password').value;
            const pwd2 = document.getElementById('passwordConfirm').value;

            if (pwd && pwd.length < 6) {
                showFieldError('password', 'La contraseña debe tener al menos 6 caracteres.');
                valid = false;
            }

            if (pwd && pwd2 && pwd !== pwd2) {
                showFieldError('passwordConfirm', 'Las contraseñas no coinciden.');
                valid = false;
            }

            if (!document.getElementById('terms').checked) {
                showFieldError('terms', 'Debes aceptar los términos para continuar.');
                valid = false;
            }

            return valid;
        }

        // ——— Submit ———
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearErrors();

            if (!validateClient()) return;

            setLoading(true);

            if (typeof grecaptcha === "undefined" || !grecaptcha.getResponse()) {
                showGeneralError("Por favor, completa el captcha.");
                setLoading(false);
                return;
            }

            const data = {
                name:                  document.getElementById('name').value.trim(),
                surname:               document.getElementById('surname').value.trim(),
                username:              document.getElementById('username').value.trim(),
                email:                 document.getElementById('email').value.trim(),
                password:              document.getElementById('password').value,
                password_confirmation: document.getElementById('passwordConfirm').value,
            };

            try {
                const response = await fetch('/api/register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (response.ok) {
                    Auth.setToken(result.token, result.user?.role ?? 'user', false);
                    window.location.href = "{{ route('shop') }}";
                    return;
                }

                // Errores de validación del servidor (422)
                if (result.errors) {
                    // La API devuelve 'password' para ambos campos de contraseña
                    Object.entries(result.errors).forEach(([field, msgs]) => {
                        showFieldError(field, msgs[0]);
                    });
                } else {
                    showGeneralError(result.message || 'Error al crear la cuenta.');
                }

            } catch {
                showGeneralError('No se pudo conectar con el servidor. Inténtalo de nuevo.');
            } finally {
                setLoading(false);
            }
        });

        // Limpiar error de campo al escribir
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', () => {
                input.classList.remove('is-invalid');
                const errSpan = document.getElementById('err-' + input.id);
                if (errSpan) errSpan.textContent = '';
            });
        });
    </script>
</body>

</html>
