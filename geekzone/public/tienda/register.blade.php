<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>GeekZone — Registrarse</title>
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
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" placeholder="Tu nombre" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellidos</label>
                        <input type="text" class="form-control" placeholder="Tus apellidos" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre de usuario</label>
                    <input type="text" class="form-control" placeholder="@usuario_friki" />
                    <p class="form-hint">Visible en tu perfil público</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" placeholder="tu@email.com" />
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" placeholder="Min. 8 caracteres" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" class="form-control" placeholder="Repite la contraseña" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Intereses</label>
                    <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-top:.4rem">
                        <label
                            style="display:flex;align-items:center;gap:.4rem;font-size:.88rem;color:var(--grey);cursor:pointer">
                            <input type="checkbox" style="accent-color:var(--cobalt)" /> 🕷️ Marvel
                        </label>
                        <label
                            style="display:flex;align-items:center;gap:.4rem;font-size:.88rem;color:var(--grey);cursor:pointer">
                            <input type="checkbox" style="accent-color:var(--cobalt)" /> 🎵 K-Pop
                        </label>
                        <label
                            style="display:flex;align-items:center;gap:.4rem;font-size:.88rem;color:var(--grey);cursor:pointer">
                            <input type="checkbox" style="accent-color:var(--cobalt)" /> ⚽ Deportes
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="checkbox-label" style="margin-bottom:.8rem">
                        <input type="checkbox" style="accent-color:var(--cobalt)" />
                        <span style="font-size:.85rem;color:var(--grey)">Acepto los <a href="#"
                                style="color:var(--cobalt-light)">Términos de uso</a> y la <a href="#"
                                style="color:var(--cobalt-light)">Política de privacidad</a></span>
                    </label>
                </div>
                <a href="panel-usuario.html" class="btn btn-gold btn-block btn-lg" style="text-align:center">Crear
                    cuenta</a>
            </div>
            <div class="auth-footer">
                ¿Ya tienes cuenta? <a href="login.html">Inicia sesión</a>
            </div>
        </div>
    </div>
</body>

</html>