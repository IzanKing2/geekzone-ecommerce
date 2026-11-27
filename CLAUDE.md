# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

GeekZone: e-commerce (Marvel, Stray Kids, Fútbol) — 2º DAW intermodular project. Laravel 12 app in `geekzone/`, run via Docker from the repo root. Docs are in Spanish (`docs/`, code comments too); keep that convention. Project memoria/diagrams/SQL live in `docs/`.

## Commands

Everything runs in Docker from the repo root (`geekzone/` is mounted at `/var/www/html` in the `app` container). The app's env template is `geekzone/.env.example` (a **development** template: `APP_ENV=local`); the root `.env.example` only holds the Docker MySQL passwords (optional, defaults work).

```bash
docker-compose up -d --build
docker-compose exec app composer install
cp geekzone/.env.example geekzone/.env
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan jwt:secret --force   # JWT_SECRET starts empty: without it every login fails
docker-compose exec app php artisan migrate --seed
docker-compose exec app php artisan storage:link         # otherwise uploaded images are not served
docker-compose exec app php artisan l5-swagger:generate   # regenerate Swagger JSON (L5_SWAGGER_GENERATE_ALWAYS=false)

# Tests (PHPUnit 11; SQLite in-memory per phpunit.xml, so no DB container needed)
docker-compose exec app php artisan test
docker-compose exec app php artisan test --filter=test_add_product_to_cart   # single test
docker-compose exec app php artisan test tests/Feature/CartTest.php          # single file

# Quality gates (the same ones CI runs: .github/workflows/ci.yml)
docker-compose exec app ./vendor/bin/pint --test          # style (Laravel preset); drop --test to fix
docker-compose exec app ./vendor/bin/phpstan analyse --memory-limit=1G   # Larastan level 5, no baseline
docker-compose exec app composer audit                    # known vulnerabilities in dependencies
```

URLs: shop `localhost:8080`, phpMyAdmin `localhost:8081` (dev only; MySQL and phpMyAdmin listen on 127.0.0.1 only), Swagger `localhost:8080/api/documentation`. Seeded users exist **only with `APP_ENV=local`/`testing`**: `admin@geekzone.com`/`admin123`, `user@geekzone.com`/`user123` (elsewhere set `ADMIN_EMAIL`/`ADMIN_PASSWORD`).

**reCAPTCHA** is verified server-side on login/register (`App\Rules\Recaptcha`, field `recaptcha_token`). For local work set `RECAPTCHA_ENABLED=false` in `geekzone/.env` (or use Google's public test keys listed in `.env.example`). Keys: `RECAPTCHA_SITE_KEY` / `RECAPTCHA_SECRET_KEY` (via `config/services.php`).

**PHP changes can take up to 60 s to show** (OPcache `revalidate_freq=60`, see `docker/php/opcache.ini`; it removes a ~1–3 s per-request penalty on Docker/Windows). Reload instantly with `docker-compose exec app sh -c 'kill -USR2 1'` (`kill` must run through `sh -c`). Blade views are compiled PHP, so the same applies to them. Tests run on the CLI and are unaffected.

`geekzone_api_test/` is a Bruno/Postman collection for manual API testing (not automated tests).

## Architecture

**Two-layer app: Blade shells + JWT REST API.**
- `routes/web.php` only serves Blade views (`AplicationController`, `AuthController` for login/register pages, `AdminDashboardController` for admin pages). Pages contain no server-side auth: cart, panel, favorites and `/admin*` routes are plain views.
- All data and auth go through `routes/api.php` (prefix `/api`, Spanish endpoint names: `/carrito`, `/pedidos`, `/favoritos`, `/perfil`, `/productos`, `/categorias`). `/imagenes` and `/admin/dashboard/*` are admin-only.
- **Every `/api` response uses one envelope: `{success, message, data, errors}`** (`App\Http\Concerns\ApiResponses`: `successResponse`, `errorResponse`, `validationErrorResponse`; used by `Api\Controller` and the JWT/admin middleware). `bootstrap/app.php` gives framework errors (404, 405, 429 with `Retry-After`, 401, validation, 500 without details outside debug) the same shape. Payloads live in `data` (e.g. login → `data.token`, `data.user`; cart → `data.cart`). Never return a bare `response()->json([...])`.
- The admin dashboard page (`/admin`) is an **empty shell**: its data loads from `/api/admin/dashboard/*` (JWT + admin). Do not render sensitive data in Blade, since the web routes have no auth.
- Frontend JS (all in `public/js/`, loaded by `layouts/layout.blade.php`): `auth.js` (JWT/role in `localStorage` or `sessionStorage`), `api.js` (`Api.fetch` → `Response`, `Api.request` → `{ok, status, data}`; adds the token, JSON headers, and on 401 with a token clears the session and redirects private pages to `/login`), `utils.js` (`Utils.esc()`/`Utils.safeUrl()` and the event delegation below), `admin.js` (`AdminUI`: admin guard + safe DOM helpers). Login/register use plain `fetch` (no token yet).
- **XSS rules** (the JWT is in localStorage): escape every API value put into HTML with `Utils.esc()` (text/attributes) or `Utils.safeUrl()` (src/href), or build nodes with `textContent`/`createElement`. In Blade pass data to JS with `@js()` or `json_encode` inside `data-args`, never `addslashes`.
- **No inline event handlers** (`onclick=`, `onchange=`, `onerror=`…): declare behaviour with `data-*` and let `utils.js` delegate it (`data-action` + `data-args`, `data-change`, `data-overlay-close`, `data-click-target`, `data-stop`, `data-prevent-default`, `data-autosubmit[-delay]`, `data-fallback`). Functions called this way are globals and get the element as `this` (not the global `event`). `InlineHandlersTest` fails if one comes back. This is the prerequisite for a nonce-based CSP (not enabled yet, see below).
- Auth is JWT via `php-open-source-saver/jwt-auth` (`config/jwt.php`). `logout` and password change blacklist the token (`JWTAuth::invalidate()` with **no arguments**; passing a token there means `$forceForever`). Middleware: `JwtMiddleware` (any logged-in user, 401) and `admin` alias → `CheckAdminRole` (403). Aliases are registered in `bootstrap/app.php`, which also applies the general throttle (120/min per IP, limiter `api` in `AppServiceProvider`). Login/register have their own limits. `config/cors.php` allows only `APP_URL` by default.
- API controllers live in `app/Http/Controllers/Api/`; the non-Api controllers in `app/Http/Controllers/` are web/view controllers — beware same-named classes (`AuthController` exists in both; `AdminDashboardController` serves both views and its API endpoints from the non-Api namespace). `User::$role` is not mass-assignable (set it explicitly).

**Mixed data-access styles** (intentional, part of the course assignment): most controllers use Eloquent (`app/Models`: User, Product, Category, Cart, Order, OrderDetail, Favorite), but `Api/CategoryController` and `AdminDashboardController` use raw SQL via `DB::` (and `OrderController` partly). Category raw-SQL rows are mapped through `app/DTOs/CategoryDTO` (`fromRow`). Match the style of the file you're editing.

**Swagger**: endpoints are documented with OpenAPI annotations in controllers (l5-swagger); regenerate after changing them.

## Tests

Feature tests in `geekzone/tests/Feature/` hit `/api/*` with `RefreshDatabase`. Shared fixtures are in the `CreateBaseData` trait (`createCategoriesForTest`, `createProductsForTest`, `loginAsUser`, etc.) — reuse it rather than building data inline. It builds on the factories in `database/factories` (`User::factory()->admin()`, `Product::factory()->outOfStock()`, `Order::factory()->status('entregado')`…); `FactoriesTest` keeps them in sync with the schema. `getTokenForUser` reads `data.token`. SQLite runs the tests, MySQL runs the app: schema or raw-SQL changes (indexes, foreign keys) need a manual `migrate`/`migrate:rollback` check against MySQL.

CI (`.github/workflows/ci.yml`) runs `composer audit`, Pint, Larastan, the tests and a Swagger-drift check. When adding or changing an endpoint, regenerate Swagger and commit `storage/api-docs/api-docs.json`.

## Pendiente (hardening en curso)

- **CSP**: añadir un middleware que emita `Content-Security-Policy` con nonce (`script-src 'self' 'nonce-…' https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/`, `style-src 'self' 'unsafe-inline' https://fonts.googleapis.com`, `font-src https://fonts.gstatic.com`, `frame-src https://www.google.com/recaptcha/`) y un `nonce` en los 17 `<script>` inline (directiva Blade `@nonce`). Los manejadores inline ya están eliminados.
- Frontend: CSS duplicado y `shop.css` cargado en todas las páginas, accesibilidad (labels, aria, modales), SEO/meta, responsive (cart/login/register sin `@media`), Vite instalado pero sin usar (`vite`, `tailwind`, `axios` sobran). Los rediseños, con Claude Design.
- Backend opcional: paginación de `/pedidos`, `/favoritos` y `/categorias` (cambia el formato que consumen las vistas), FormRequests/Resources, separar `AdminDashboardController` en Web/Api.
- Docs: actualizar `docs/` (SQL con `favorites`, versión de PHP, endpoints) y `geekzone/README.md` (es el de Laravel).

## Git

Branch flow: feature branches → `develop` → `production` → `master`. Commit messages use gitmoji + conventional style in Spanish (e.g. `✨ feat(docs): ...`).

## Skills del proyecto

Las skills instaladas viven en `.agents/skills/` (versionadas, con `skills-lock.json`); `new-endpoint` es propia y vive en `.claude/skills/`. Los enlaces en `.claude/skills/` de las instaladas están ignorados por git: tras clonar, recréalos desde la raíz del repo con `npx skills add <repo> --skill <nombre>` (ver `skills-lock.json` para origen de cada una). Ejecuta siempre `npx skills add` desde la raíz del repo, o se instala anidado.

Invoca estas skills cuando corresponda, sin esperar a que el usuario las nombre:

- `new-endpoint`: al crear, ampliar o modificar cualquier endpoint de `/api` (ruta + controlador + Swagger + test + Bruno).
- `laravel-best-practices`: al escribir, revisar o refactorizar PHP/Laravel (controladores, modelos, migraciones, validación, consultas). Prevalece el patrón que ya use el código (p. ej. SQL puro en `CategoryController`) sobre sus reglas por defecto. Algunos ficheros de `rules/` se instalaron como `.blade.php` (security, config, mail, migrations, scheduling, events-notifications), aunque el índice los enlace como `.md`. No hay herramienta `search-docs`; consulta `vendor/` o la documentación.
- `systematic-debugging`: ante cualquier bug, test que falla o comportamiento inesperado, antes de proponer un arreglo. Investigar causa raíz primero.
- `webapp-testing`: para comprobar el flujo real en navegador (login, carrito, admin) con Playwright contra `http://localhost:8080` (la app ya corre en Docker, no hace falta `with_server.py`). Requiere Python + Playwright, que pueden no estar instalados. El reCAPTCHA del login bloquea la automatización: usa claves de test de Google o inyecta el token en `localStorage`.
- `frontend-design`: solo al crear páginas/secciones nuevas o un rediseño pedido. Para retoques en vistas existentes, respeta el CSS actual de `public/css/`.
