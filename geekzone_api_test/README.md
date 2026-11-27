# Colección Bruno de GeekZone

Pruebas **manuales** de la API (no son tests automáticos; esos están en `geekzone/tests/`).

## Configuración

1. En Bruno: **Environments → Configure → New** y crea el entorno `local` con la variable `token` (vacía).
2. Ejecuta `auth/login` y copia el `token` de la respuesta en la variable `token` del entorno.
   Las demás peticiones usan `Authorization: Bearer {{token}}`. **No pegues tokens en los `.yml`**: se versionan.
3. Los endpoints de administración (`admin/*`, `images/*`, escritura de productos y categorías) necesitan el token de un usuario con rol `admin`.

## reCAPTCHA

`login` y `register` verifican el captcha en el servidor (campo `recaptcha_token`). Para probar en local:

- **Opción A (recomendada):** `RECAPTCHA_ENABLED=false` en `geekzone/.env`. El campo se ignora.
- **Opción B:** claves de test de Google en `RECAPTCHA_SITE_KEY` / `RECAPTCHA_SECRET_KEY` (están en `geekzone/.env.example`). Con ellas cualquier valor, como el `test-token` de las peticiones, se acepta.

## Usuarios

Solo en entorno `local`, `php artisan migrate --seed` crea `admin@geekzone.com` / `admin123` y `user@geekzone.com` / `user123`.
