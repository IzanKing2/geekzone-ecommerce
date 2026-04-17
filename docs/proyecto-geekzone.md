## Documentación técnica de `geekzone-ecommerce`

*(Actualizado — Abril 2026)*

---

### 1. Visión general del proyecto

GeekZone es un **e-commerce de productos geek** centrado en temáticas como **Marvel**, **Stray Kids** y **Fútbol**.

Proyecto intermodular de **2º DAW (IES Villa de Agüimes, curso 2025/2026)**, desarrollado por **Izan, Saúl y Marisa**.

La plataforma cuenta con un **backend en Laravel que expone una API REST JSON** y un **frontend completo** en Blade + JavaScript nativo que la consume, cubriendo:

- Autenticación con JWT y Google reCAPTCHA v2
- Catálogo de productos y categorías con filtros
- Carrito de compra y checkout
- Gestión de pedidos
- Lista de favoritos
- Panel de usuario (perfil, historial, favoritos)
- Panel de administración con métricas, CRUD de productos y categorías
- Documentación interactiva de la API (Swagger UI)

---

### 2. Stack tecnológico

| Capa | Tecnología | Justificación |
|------|-----------|---------------|
| **Backend** | Laravel (PHP 8.2) | Framework MVC profesional con ORM, routing y seguridad incluidos |
| **Base de datos** | MySQL 8.0 | Relacional, garantiza integridad referencial entre pedidos, usuarios y productos |
| **Autenticación** | JWT (`php-open-source-saver/jwt-auth`) | Stateless, adecuado para APIs REST; el token se almacena en el cliente |
| **Frontend** | Blade + HTML5 + CSS nativo + JS (async/await) | Sin frameworks JS externos; demuestra dominio de las tecnologías base |
| **Servidor web** | Nginx | Reverse proxy hacia PHP-FPM |
| **Contenedores** | Docker + Docker Compose | Entorno reproducible en cualquier máquina |
| **Documentación API** | `darkaonline/l5-swagger` (OpenAPI 3) | Genera Swagger UI a partir de anotaciones PHP 8 en los controladores |
| **Seguridad extra** | Google reCAPTCHA v2 | Protección anti-bot en el formulario de login |

---

### 3. Arquitectura y estructura del código

El patrón base es **MVC**:

```
geekzone/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/                    ← Controladores de la API REST
│   │   │   ├── Controller.php      ← Base abstracta con OA\Info y helpers de respuesta
│   │   │   ├── AuthController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── ProductController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── CartController.php
│   │   │   ├── OrderController.php
│   │   │   ├── FavoriteController.php
│   │   │   └── ImageController.php
│   │   ├── AdminDashboardController.php  ← API admin + renderiza vistas admin
│   │   ├── AplicationController.php      ← Renderiza vistas públicas
│   │   └── AuthController.php            ← Renderiza vistas de login/registro
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Cart.php
│   │   ├── Order.php
│   │   ├── OrderDetail.php
│   │   └── Favorite.php
│   └── Http/Middleware/
│       ├── JwtMiddleware.php       ← Valida token JWT en rutas protegidas
│       └── CheckAdminRole.php      ← Restringe rutas al rol admin
├── database/
│   ├── migrations/                 ← Historial de cambios de la BD
│   └── seeders/                    ← CategorySeeder, ProductSeeder, UserSeeder
├── resources/views/
│   ├── layouts/                    ← layout.blade.php, header.blade.php, footer.blade.php
│   ├── auth/                       ← login.blade.php, register.blade.php
│   ├── admin/                      ← dashboard.blade.php, products.blade.php, categories.blade.php
│   ├── cart/                       ← cart.blade.php
│   ├── shop.blade.php
│   ├── catalog.blade.php
│   ├── product.blade.php
│   ├── favorites.blade.php
│   ├── userPanel.blade.php
│   └── welcome.blade.php
├── public/
│   ├── css/                        ← Hojas de estilo por sección
│   └── js/auth.js                  ← Gestión del token JWT en el cliente
├── routes/
│   ├── api.php                     ← Todos los endpoints REST
│   └── web.php                     ← Rutas de vistas Blade
├── storage/api-docs/api-docs.json  ← JSON OpenAPI generado por l5-swagger
└── tests/                          ← Feature y Unit tests (PHPUnit)
```

---

### 4. Base de datos

#### Modelos principales

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| `User` | `users` | Usuarios con campo `role` (`cliente` / `admin`) |
| `Category` | `categories` | Categorías de productos (Marvel, Fútbol, Stray Kids…) |
| `Product` | `products` | Pertenece a una categoría; tiene nombre, precio, stock e imagen |
| `Cart` | `carts` | Ítem del carrito: relación usuario-producto con cantidad |
| `Order` | `orders` | Pedido confirmado; estado: pendiente/procesando/enviado/entregado/cancelado |
| `OrderDetail` | `order_details` | Línea de pedido; precio "congelado" en el momento de la compra |
| `Favorite` | `favorites` | Relación usuario-producto para la lista de favoritos |

#### Migraciones

```
0001_01_01_000000  create_users_table
0001_01_01_000001  create_cache_table
0001_01_01_000002  create_jobs_table
2025_01_02_000001  create_categories_table
2025_01_02_000002  create_products_table
2025_01_02_000003  create_carts_table
2025_01_02_000004  create_orders_table
2026_03_11_125219  create_personal_access_tokens_table
2026_04_15_181746  create_favorites_table
```

---

### 5. Frontend — Vistas implementadas

| Ruta web | Vista Blade | Descripción |
|----------|-------------|-------------|
| `/` | `shop.blade.php` | Tienda principal con productos destacados |
| `/catalogo` | `catalog.blade.php` | Catálogo completo con filtros por categoría |
| `/producto/{id}` | `product.blade.php` | Detalle de producto (imagen, precio, stock, añadir al carrito/favoritos) |
| `/login` | `auth/login.blade.php` | Login + validación reCAPTCHA v2 |
| `/register` | `auth/register.blade.php` | Registro de nuevo usuario |
| `/cart` | `cart/cart.blade.php` | Carrito: ver, modificar cantidades, eliminar, hacer pedido |
| `/panel` | `userPanel.blade.php` | Panel de usuario: perfil, pedidos, favoritos, seguridad |
| `/favoritos` | `favorites.blade.php` | Lista de favoritos del usuario |
| `/admin` | `admin/dashboard.blade.php` | Dashboard admin con métricas |
| `/admin/productos` | `admin/products.blade.php` | CRUD de productos |
| `/admin/categorias` | `admin/categories.blade.php` | CRUD de categorías |

El token JWT se almacena en el cliente mediante `public/js/auth.js` y se adjunta automáticamente a todas las peticiones protegidas mediante `Authorization: Bearer <token>`.

---

### 6. API REST — Endpoints completos

#### 6.1 Públicos

| Método | Endpoint | Throttle | Descripción |
|--------|----------|----------|-------------|
| `POST` | `/api/register` | 5/min | Registro de usuario |
| `POST` | `/api/login` | 10/min | Login — devuelve token JWT |
| `GET` | `/api/categorias` | — | Listado de categorías |
| `GET` | `/api/productos` | — | Listado de productos (con categoría) |
| `GET` | `/api/productos/{id}` | — | Detalle de producto |

#### 6.2 Protegidos (JWT requerido)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `POST` | `/api/logout` | Invalida el token |
| `GET` | `/api/perfil` | Ver datos del usuario autenticado |
| `PUT` | `/api/perfil` | Actualizar nombre, apellidos, username, email o contraseña |
| `GET` | `/api/carrito` | Ver ítems del carrito |
| `POST` | `/api/carrito` | Añadir producto (o incrementar cantidad) |
| `PUT` | `/api/carrito/{id}` | Cambiar cantidad de un ítem |
| `DELETE` | `/api/carrito/{id}` | Eliminar ítem del carrito |
| `GET` | `/api/pedidos` | Historial de pedidos con detalles |
| `POST` | `/api/pedidos` | Confirmar pedido (consume el carrito) |
| `GET` | `/api/favoritos` | Listar favoritos del usuario |
| `POST` | `/api/favoritos` | Añadir producto a favoritos |
| `DELETE` | `/api/favoritos/{productId}` | Quitar producto de favoritos |
| `POST` | `/api/imagenes` | Subir imagen de producto (multipart/form-data) |

#### 6.3 Administración (JWT + rol admin)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/api/admin/dashboard/resumen` | Totales: usuarios, productos, pedidos, ingresos |
| `GET` | `/api/admin/dashboard/ingresos` | Ingresos por período |
| `GET` | `/api/admin/dashboard/top-productos` | Productos más vendidos |
| `GET` | `/api/admin/dashboard/pedidos-por-cliente` | Pedidos agrupados por cliente |
| `POST` | `/api/categorias` | Crear categoría (nombre único validado) |
| `PUT` | `/api/categorias/{id}` | Editar categoría |
| `DELETE` | `/api/categorias/{id}` | Eliminar categoría |
| `POST` | `/api/productos` | Crear producto |
| `PUT` | `/api/productos/{id}` | Editar producto |
| `DELETE` | `/api/productos/{id}` | Eliminar producto |

---

### 7. Seguridad

| Capa | Mecanismo |
|------|-----------|
| **Autenticación** | JWT (stateless); el token se invalida en logout |
| **Autorización** | Middleware `CheckAdminRole` — rutas de administración requieren `role = admin` |
| **Rate limiting** | `throttle:5,1` en `/register`, `throttle:10,1` en `/login` |
| **Anti-bot** | Google reCAPTCHA v2 en el formulario de login |
| **CSRF** | Directiva `@csrf` en todos los formularios Blade |
| **SQL injection** | Consultas via Eloquent ORM y query builder parametrizado |
| **Validación** | Validación en servidor (Laravel `validate()`) + validación JS en cliente |

---

### 8. Documentación Swagger / OpenAPI

La API está completamente documentada con **anotaciones PHP 8 (`#[OA\...]`)** en todos los controladores.

- **Paquete**: `darkaonline/l5-swagger ^11.0`
- **Especificación generada**: `storage/api-docs/api-docs.json`
- **UI disponible en**: `/api/documentation`

#### Controladores anotados

| Controlador | Anotaciones `#[OA\...]` |
|-------------|------------------------|
| `ProductController` | 29 |
| `CartController` | 22 |
| `CategoryController` | 20 |
| `FavoriteController` | 13 |
| `AuthController` | 12 |
| `ProfileController` | 9 |
| `OrderController` | 8 |
| `ImageController` | 5 |
| `Controller` (base) | 3 (Info, Server, SecurityScheme) |

#### Regenerar la documentación

```bash
docker-compose exec app php artisan l5-swagger:generate
```

Para regenerar automáticamente en cada petición (solo desarrollo), añadir a `.env`:

```env
L5_SWAGGER_GENERATE_ALWAYS=true
```

---

### 9. Entorno y despliegue

```bash
# 1. Clonar y configurar entorno
git clone <url-del-repositorio>
cd geekzone-ecommerce
cp .env.example geekzone/.env

# 2. Levantar contenedores
docker-compose up -d --build

# 3. Instalar dependencias y configurar Laravel
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan jwt:secret

# 4. Base de datos
docker-compose exec app php artisan migrate --seed

# 5. Publicar assets de Swagger y generar documentación
docker-compose exec app php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider" --tag=config
docker-compose exec app php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider" --tag=swagger-ui-assets
docker-compose exec app php artisan l5-swagger:generate
```

#### Servicios y URLs

| Servicio | URL |
|----------|-----|
| Tienda (GeekZone) | <http://localhost:8080> |
| phpMyAdmin | <http://localhost:8081> |
| Swagger UI | <http://localhost:8080/api/documentation> |

#### Usuarios de prueba

| Rol | Email | Contraseña |
|-----|-------|-----------|
| Admin | admin@geekzone.com | admin123 |
| Cliente | user@geekzone.com | user123 |

---

### 10. Tests

```bash
# Todos los tests con salida descriptiva
docker-compose exec app php artisan test --testdox
```

- Configuración: `geekzone/phpunit.xml`
- BD de prueba: SQLite en memoria (`DB_DATABASE=:memory:`)
- Suites: `tests/Unit` y `tests/Feature`

Los tests de **Feature** cubren: autenticación, catálogo, carrito, pedidos, favoritos y administración.

---

### 11. Colecciones de prueba manual

La carpeta `geekzone_api_test/` contiene colecciones YAML compatibles con **Bruno**, Thunder Client y Postman (previa conversión), cubriendo todos los módulos de la API.

---

### 12. Posibles mejoras futuras

- Paginación y filtros avanzados en el catálogo (precio, valoraciones)
- Sistema de cupones y descuentos
- Notificaciones por email al crear/actualizar pedidos
- Logs estructurados y monitorización (Grafana/Prometheus)
- Caché de listados de productos con Redis
- Optimización de consultas en endpoints del dashboard
