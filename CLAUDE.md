# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

GeekZone: e-commerce (Marvel, Stray Kids, Fútbol) — 2º DAW intermodular project. Laravel 12 app in `geekzone/`, run via Docker from the repo root. Docs are in Spanish (`docs/`, code comments too); keep that convention. Project memoria/diagrams/SQL live in `docs/`.

## Commands

Everything runs in Docker from the repo root (`geekzone/` is mounted at `/var/www/html` in the `app` container). Note: there is no root `.env.example`; it's at `geekzone/.env.example` (the README's `cp .env.example geekzone/.env` path is outdated).

```bash
docker-compose up -d --build
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan jwt:secret
docker-compose exec app php artisan migrate --seed
docker-compose exec app php artisan l5-swagger:generate   # regenerate Swagger JSON (L5_SWAGGER_GENERATE_ALWAYS=false)

# Tests (PHPUnit 11; SQLite in-memory per phpunit.xml, so no DB container needed)
docker-compose exec app php artisan test
docker-compose exec app php artisan test --filter=test_add_product_to_cart   # single test
docker-compose exec app php artisan test tests/Feature/CartTest.php          # single file

docker-compose exec app ./vendor/bin/pint   # code style (Laravel Pint)
```

URLs: shop `localhost:8080`, phpMyAdmin `localhost:8081`, Swagger `localhost:8080/api/documentation`. Seeded users: `admin@geekzone.com`/`admin123`, `user@geekzone.com`/`user123`. reCAPTCHA v2 keys go in `RECAPTCHA_SITE_KEY` / `RECAPTCHA_SECRET_KEY` (read via `config/services.php`).

`geekzone_api_test/` is a Bruno/Postman collection for manual API testing (not automated tests).

## Architecture

**Two-layer app: Blade shells + JWT REST API.**
- `routes/web.php` only serves Blade views (`AplicationController`, `AuthController` for login/register pages, `AdminDashboardController` for admin pages). Pages contain no server-side auth: cart, panel, favorites and `/admin*` routes are plain views.
- All data and auth go through `routes/api.php` (prefix `/api`, Spanish endpoint names: `/carrito`, `/pedidos`, `/favoritos`, `/perfil`, `/productos`, `/categorias`). Page JavaScript calls these with `fetch` and `Authorization: Bearer <jwt>`.
- Client-side auth helper is `public/js/auth.js`: stores the JWT/role in `localStorage` (remember me) or `sessionStorage`, with localStorage taking priority. Views gate UI on it.
- Auth is JWT via `php-open-source-saver/jwt-auth`. Middleware: `JwtMiddleware` (any logged-in user) and `admin` alias → `CheckAdminRole` (checks `user->role === 'admin'`, returns JSON 403). Aliases are registered in `bootstrap/app.php`. Admin-only API routes (product/category writes, `/admin/dashboard/*`) are in the `[JwtMiddleware, 'admin']` group; login/register are throttled.
- API controllers live in `app/Http/Controllers/Api/`; the non-Api controllers in `app/Http/Controllers/` are web/view controllers — beware same-named classes (`AuthController`, `CartController` exist in both; `AdminDashboardController` serves both views and its API endpoints from the non-Api namespace).

**Mixed data-access styles** (intentional, part of the course assignment): most controllers use Eloquent (`app/Models`: User, Product, Category, Cart, Order, OrderDetail, Favorite), but `Api/CategoryController` and `AdminDashboardController` use raw SQL via `DB::` (and `OrderController` partly). Category raw-SQL rows are mapped through `app/DTOs/CategoryDTO` (`fromRow`). Match the style of the file you're editing.

**Swagger**: endpoints are documented with OpenAPI annotations in controllers (l5-swagger); regenerate after changing them.

## Tests

Feature tests in `geekzone/tests/Feature/` hit `/api/*` with `RefreshDatabase`. Shared fixtures are in the `CreateBaseData` trait (`createCategoriesForTest`, `createProductsForTest`, `loginAsUser`, etc.) — reuse it rather than building data inline.

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
