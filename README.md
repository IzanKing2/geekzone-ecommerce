# GeekZone — E-commerce de Productos Geek

E-commerce especializado en productos de **Marvel**, **Stray Kids** y **Fútbol**.

Proyecto intermodular de **2º DAW** — IES Villa de Agüimes (Curso 2025/2026).

**Equipo**: Izan, Saúl, Marisa

---

## Documentación del proyecto

- **Memoria TFG (documento principal)**: [`docs/Memoria_TFG_GeekZone.md`](docs/Memoria_TFG_GeekZone.md)
- **Documentación técnica completa**: [`docs/proyecto-geekzone.md`](docs/proyecto-geekzone.md)
- **Documentación final (TFG)**: [`docs/Documentacion_Final.md`](docs/Documentacion_Final.md)
- **Diagrama E/R**: [`docs/diagrama_er.md`](docs/diagrama_er.md)
- **Diagrama UML de clases**: [`docs/diagrama_uml_clases.md`](docs/diagrama_uml_clases.md)
- **Script SQL completo**: [`docs/geekzone_database.sql`](docs/geekzone_database.sql)
- **Documentación API interactiva**: `http://localhost:8080/api/documentation` (Swagger UI)

---

## Stack Tecnológico

| Componente | Tecnología |
|------------|-----------|
| **Backend** | Laravel (PHP 8.2) |
| **Base de datos** | MySQL 8.0 |
| **Autenticación** | JWT (`php-open-source-saver/jwt-auth`) |
| **Frontend** | Blade + HTML5 + CSS nativo + JavaScript (async/await) |
| **Servidor web** | Nginx |
| **Contenedores** | Docker + Docker Compose |
| **Documentación API** | Swagger UI (`darkaonline/l5-swagger`) |
| **Seguridad extra** | Google reCAPTCHA v2 en login |

---

## Instalación y Puesta en Marcha

### Requisitos previos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado
- Git instalado

### Pasos

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio>
cd geekzone-ecommerce

# 2. Copiar el archivo de configuración
cp .env.example geekzone/.env

# 3. Levantar los contenedores Docker
docker-compose up -d --build

# 4. Instalar dependencias de PHP
docker-compose exec app composer install

# 5. Generar la clave de la aplicación Laravel
docker-compose exec app php artisan key:generate

# 6. Generar la clave secreta para JWT
docker-compose exec app php artisan jwt:secret

# 7. Ejecutar las migraciones y seeders
docker-compose exec app php artisan migrate --seed

# 8. Publicar y generar la documentación Swagger
docker-compose exec app php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider" --tag=config
docker-compose exec app php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider" --tag=swagger-ui-assets
docker-compose exec app php artisan l5-swagger:generate
```

### Accesos

| Servicio | URL | Descripción |
|----------|-----|------------|
| GeekZone (tienda) | <http://localhost:8080> | Aplicación web completa |
| phpMyAdmin | <http://localhost:8081> | Gestión de base de datos |
| Swagger UI | <http://localhost:8080/api/documentation> | Documentación interactiva de la API |

### Usuarios de prueba

| Rol | Email | Contraseña |
|-----|-------|-----------|
| Admin | admin@geekzone.com | admin123 |
| Cliente | user@geekzone.com | user123 |

---

## Estructura del Proyecto

```
geekzone-ecommerce/
├── docker-compose.yml           # Orquestación de contenedores
├── Dockerfile                   # Imagen PHP personalizada
├── nginx/default.conf           # Configuración de Nginx
├── .env.example                 # Plantilla de variables de entorno
├── docs/                        # Documentación del proyecto
├── geekzone/                    # Código fuente Laravel
│   ├── app/
│   │   ├── Http/Controllers/    # Controladores web y API
│   │   │   ├── Api/             # Controladores de la API REST
│   │   │   ├── AdminDashboardController.php
│   │   │   ├── AplicationController.php
│   │   │   └── AuthController.php
│   │   ├── Models/              # User, Product, Category, Cart, Order, OrderDetail, Favorite
│   │   └── Http/Middleware/     # JwtMiddleware, CheckAdminRole
│   ├── database/
│   │   ├── migrations/          # Migraciones de la BD
│   │   └── seeders/             # Datos de prueba
│   ├── resources/views/
│   │   ├── layouts/             # Layout base, header, footer
│   │   ├── auth/                # Login, registro
│   │   ├── admin/               # Dashboard, productos, categorías (admin)
│   │   ├── cart/                # Carrito
│   │   ├── shop.blade.php       # Tienda principal
│   │   ├── catalog.blade.php    # Catálogo filtrado
│   │   ├── product.blade.php    # Detalle de producto
│   │   ├── favorites.blade.php  # Favoritos
│   │   ├── userPanel.blade.php  # Panel de usuario
│   │   └── welcome.blade.php    # Página de inicio
│   ├── public/css/              # Hojas de estilo por sección
│   ├── routes/
│   │   ├── api.php              # Endpoints REST
│   │   └── web.php              # Rutas de vistas
│   ├── storage/api-docs/        # JSON generado por Swagger
│   └── tests/                   # Tests PHPUnit
└── geekzone_api_test/           # Colecciones de prueba (Bruno/Postman)
```

---

## Páginas del Frontend

| Ruta web | Vista | Descripción |
|----------|-------|-------------|
| `/` | `shop.blade.php` | Tienda principal |
| `/catalogo` | `catalog.blade.php` | Catálogo con filtros |
| `/producto/{id}` | `product.blade.php` | Detalle de producto |
| `/login` | `auth/login.blade.php` | Login con reCAPTCHA |
| `/register` | `auth/register.blade.php` | Registro |
| `/cart` | `cart/cart.blade.php` | Carrito de compra |
| `/panel` | `userPanel.blade.php` | Perfil, pedidos y favoritos |
| `/favoritos` | `favorites.blade.php` | Lista de favoritos |
| `/admin` | `admin/dashboard.blade.php` | Panel admin — métricas |
| `/admin/productos` | `admin/products.blade.php` | CRUD de productos |
| `/admin/categorias` | `admin/categories.blade.php` | CRUD de categorías |

---

## API — Endpoints

### Públicos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `POST` | `/api/register` | Registro (límite: 5 req/min) |
| `POST` | `/api/login` | Login — devuelve JWT (límite: 10 req/min) |
| `GET` | `/api/categorias` | Listado de categorías |
| `GET` | `/api/productos` | Listado de productos |
| `GET` | `/api/productos/{id}` | Detalle de producto |

### Protegidos (JWT)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `POST` | `/api/logout` | Cierre de sesión |
| `GET` | `/api/perfil` | Ver perfil del usuario |
| `PUT` | `/api/perfil` | Actualizar perfil (nombre, email, contraseña…) |
| `GET` | `/api/carrito` | Ver carrito |
| `POST` | `/api/carrito` | Añadir producto al carrito |
| `PUT` | `/api/carrito/{id}` | Actualizar cantidad de un ítem |
| `DELETE` | `/api/carrito/{id}` | Eliminar ítem del carrito |
| `GET` | `/api/pedidos` | Historial de pedidos |
| `POST` | `/api/pedidos` | Crear pedido desde el carrito |
| `GET` | `/api/favoritos` | Ver favoritos |
| `POST` | `/api/favoritos` | Añadir producto a favoritos |
| `DELETE` | `/api/favoritos/{productId}` | Quitar producto de favoritos |
| `POST` | `/api/imagenes` | Subir imagen de producto (multipart/form-data) |

### Administración (JWT + rol admin)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/api/admin/dashboard/resumen` | Estadísticas generales |
| `GET` | `/api/admin/dashboard/ingresos` | Ingresos totales |
| `GET` | `/api/admin/dashboard/top-productos` | Productos más vendidos |
| `GET` | `/api/admin/dashboard/pedidos-por-cliente` | Pedidos agrupados por cliente |
| `POST` | `/api/categorias` | Crear categoría |
| `PUT` | `/api/categorias/{id}` | Editar categoría |
| `DELETE` | `/api/categorias/{id}` | Eliminar categoría |
| `POST` | `/api/productos` | Crear producto |
| `PUT` | `/api/productos/{id}` | Editar producto |
| `DELETE` | `/api/productos/{id}` | Eliminar producto |

---

## Tests

```bash
# Ejecutar todos los tests
docker-compose exec app php artisan test --testdox
```

---

## Documentación Swagger

La API está completamente documentada con anotaciones OpenAPI 3 en los controladores. Para regenerar el JSON:

```bash
docker-compose exec app php artisan l5-swagger:generate
```

Para regenerar automáticamente en cada request (solo desarrollo):

```env
L5_SWAGGER_GENERATE_ALWAYS=true
```

---

## Licencia

Proyecto académico — IES Villa de Agüimes © 2025-2026
