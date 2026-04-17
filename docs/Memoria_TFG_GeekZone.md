# MEMORIA DEL PROYECTO FINAL DE MÓDULO

---

**Título del proyecto:** GeekZone — Plataforma de E-Commerce de Productos Geek

**Ciclo formativo:** Desarrollo de Aplicaciones Web (DAW) — 2º Curso

**Centro educativo:** IES Villa de Agüimes

**Curso académico:** 2025/2026

**Autores:** Izan García · Saúl · Marisa

**Fecha:** Abril 2026

---

---

## ÍNDICE

1. [Introducción y motivación](#1-introducción-y-motivación)
2. [Objetivos del proyecto](#2-objetivos-del-proyecto)
3. [Planificación y metodología](#3-planificación-y-metodología)
4. [Stack tecnológico](#4-stack-tecnológico)
5. [Arquitectura del sistema](#5-arquitectura-del-sistema)
6. [Base de datos](#6-base-de-datos)
7. [Backend — API REST](#7-backend--api-rest)
8. [Frontend — Interfaz de usuario](#8-frontend--interfaz-de-usuario)
9. [Seguridad](#9-seguridad)
10. [Documentación de la API (Swagger)](#10-documentación-de-la-api-swagger)
11. [Tests automatizados](#11-tests-automatizados)
12. [Infraestructura y despliegue](#12-infraestructura-y-despliegue)
13. [Decisiones técnicas destacadas](#13-decisiones-técnicas-destacadas)
14. [Resultados y pruebas funcionales](#14-resultados-y-pruebas-funcionales)
15. [Mejoras futuras](#15-mejoras-futuras)
16. [Conclusiones](#16-conclusiones)
17. [Referencias](#17-referencias)

---

---

## 1. Introducción y motivación

GeekZone es una plataforma de comercio electrónico especializada en productos de cultura geek: merchandising de franquicias de Marvel, grupos de K-Pop como Stray Kids, y artículos relacionados con el fútbol. El proyecto ha sido desarrollado como trabajo final del ciclo formativo de Desarrollo de Aplicaciones Web (2º DAW) en el IES Villa de Agüimes, durante el curso 2025/2026.

La motivación principal del proyecto surge de la necesidad de integrar en un único producto todos los conocimientos adquiridos a lo largo del ciclo: diseño de bases de datos relacionales, desarrollo de APIs REST, autenticación segura, diseño de interfaces de usuario responsivas, contenedorización de aplicaciones y documentación profesional.

Se eligió el sector del e-commerce porque representa uno de los escenarios más completos desde el punto de vista técnico: requiere gestión de usuarios con distintos roles, catálogos de productos con filtros, carritos de compra con lógica transaccional, sistemas de pedidos con trazabilidad de estado y paneles de administración con métricas de negocio. Cubrir estos requisitos obliga a aplicar patrones de diseño reales como MVC, DTO, middleware de autenticación y autorización, y arquitecturas de separación entre frontend y backend.

El resultado es una aplicación web Full-Stack completamente funcional, con una API REST documentada con OpenAPI 3 (Swagger), una interfaz de usuario en Blade + JavaScript nativo, y un entorno de ejecución reproducible mediante Docker.

---

## 2. Objetivos del proyecto

### 2.1 Objetivo general

Desarrollar una aplicación web de e-commerce completa y funcional que demuestre competencia técnica en el desarrollo Full-Stack, aplicando buenas prácticas de seguridad, arquitectura limpia, diseño responsivo y documentación profesional.

### 2.2 Objetivos específicos

**Backend:**
- Implementar una API REST con Laravel que cubra todos los recursos del dominio (usuarios, categorías, productos, carrito, pedidos, favoritos).
- Proteger los endpoints mediante autenticación JWT stateless.
- Restringir las operaciones de administración mediante un middleware de control de roles.
- Aplicar rate limiting para prevenir ataques de fuerza bruta en los endpoints de autenticación.
- Implementar el patrón DTO (Data Transfer Object) para mapear resultados de consultas SQL puras a objetos PHP tipados.
- Documentar todos los endpoints con anotaciones OpenAPI 3.

**Base de datos:**
- Diseñar un esquema relacional normalizado con integridad referencial (claves foráneas, restricciones UNIQUE, soft deletes).
- Implementar el concepto de precio congelado en los detalles de pedido para garantizar la inmutabilidad del historial de compras.
- Escribir consultas SQL complejas con JOINs múltiples para los informes del panel de administración.

**Frontend:**
- Construir una interfaz completa en Blade + HTML5 + CSS nativo + JavaScript (sin frameworks JS externos).
- Consumir la API REST mediante `fetch` + `async/await` con gestión correcta del token JWT en el cliente.
- Implementar diseño responsivo con Flexbox y media queries.

**Infraestructura:**
- Contenerizar toda la aplicación con Docker y Docker Compose para garantizar reproducibilidad del entorno.
- Preparar variables de entorno y configuración diferenciada para desarrollo y producción.

**Calidad:**
- Cubrir los flujos principales con tests automatizados (PHPUnit + Feature tests).
- Proporcionar colecciones de prueba manual de la API (Bruno/Postman).

---

## 3. Planificación y metodología

### 3.1 Equipo de trabajo

El proyecto ha sido desarrollado por un equipo de tres personas:

| Miembro | Responsabilidades principales |
|---------|------------------------------|
| **Izan** | Backend API REST, autenticación JWT, middleware, tests, Docker, Swagger |
| **Saúl** | Modelos Eloquent, migraciones, seeders, CRUD productos y categorías |
| **Marisa** | Frontend Blade, CSS responsivo, JavaScript cliente, vistas de usuario y admin |

### 3.2 Metodología

Se ha seguido una metodología de desarrollo iterativo e incremental. El proyecto se organizó en las siguientes fases:

**Fase 1 — Análisis y diseño (semanas 1-2)**
- Definición de requisitos funcionales y no funcionales.
- Diseño del esquema de base de datos (modelo E/R).
- Diseño de los endpoints de la API (contrato REST).
- Configuración del entorno Docker inicial.

**Fase 2 — Backend (semanas 3-6)**
- Creación de migraciones y modelos Eloquent.
- Implementación de la autenticación JWT.
- Desarrollo de todos los controladores de la API.
- Implementación de middleware de seguridad.
- Documentación Swagger de los endpoints.

**Fase 3 — Frontend (semanas 5-9)**
- Diseño y maquetación de las vistas Blade.
- Integración con la API mediante JavaScript.
- Implementación del sistema de sesión JWT en el cliente.
- Diseño responsivo para móvil y escritorio.

**Fase 4 — Testing y refinamiento (semanas 8-10)**
- Escritura de tests Feature con PHPUnit.
- Corrección de errores detectados en pruebas.
- Optimización de rendimiento y seguridad.
- Documentación final.

### 3.3 Control de versiones

El proyecto se ha gestionado con Git, utilizando un repositorio con las ramas:

- `master` — rama de producción
- `develop` — rama de integración continua
- Ramas de características por módulo (feature branches)

---

## 4. Stack tecnológico

### 4.1 Tabla de tecnologías

| Capa | Tecnología | Versión | Justificación |
|------|-----------|---------|---------------|
| **Backend** | Laravel | PHP 8.2 | Framework MVC profesional con ORM, routing, validación y seguridad integrados |
| **Base de datos** | MySQL | 8.0 | SGBD relacional que garantiza integridad referencial entre pedidos, usuarios y productos |
| **Autenticación** | jwt-auth (`php-open-source-saver`) | ^2.2 | Autenticación stateless, adecuada para APIs REST; no requiere sesiones en servidor |
| **Frontend** | Blade + HTML5 + CSS + JS | — | Sin frameworks JS externos; demuestra dominio completo de las tecnologías base |
| **Servidor web** | Nginx | Alpine | Reverse proxy hacia PHP-FPM; configuración de rutas y cabeceras HTTP |
| **Contenedores** | Docker + Docker Compose | — | Entorno reproducible en cualquier sistema operativo (Windows, Mac, Linux) |
| **Documentación API** | l5-swagger (`darkaonline`) | ^11.0 | Genera Swagger UI a partir de anotaciones PHP 8 `#[OA\...]` en los controladores |
| **Seguridad anti-bot** | Google reCAPTCHA v2 | — | Protección del formulario de login contra bots y ataques automatizados |
| **Gestión BD (dev)** | phpMyAdmin | latest | Interfaz visual para inspeccionar y gestionar la base de datos durante el desarrollo |

### 4.2 Dependencias PHP (composer.json)

```json
"require": {
    "php": "^8.2",
    "laravel/framework": "^11.0",
    "php-open-source-saver/jwt-auth": "^2.2",
    "darkaonline/l5-swagger": "^11.0"
}
```

### 4.3 Justificación de decisiones clave

**¿Por qué Laravel y no Symfony u otro framework?**
Laravel ofrece una curva de aprendizaje más accesible para el nivel del ciclo, una comunidad muy activa y herramientas integradas (Eloquent ORM, Artisan CLI, sistema de migraciones, middleware) que permiten un desarrollo ágil. Su sistema de validación y el soporte nativo para JSON en los controladores lo convierten en una opción óptima para construir APIs REST.

**¿Por qué JWT y no sesiones?**
La arquitectura elegida separa completamente el frontend del backend: las vistas Blade son servidas por Laravel pero consumen la API mediante JavaScript. El uso de JWT permite que el estado de autenticación sea gestionado íntegramente por el cliente (guardado en `localStorage` / cookies), sin que el servidor deba mantener sesiones. Esto hace la API completamente stateless y escalable horizontalmente.

**¿Por qué JavaScript nativo y no React/Vue?**
La elección de no utilizar frameworks JavaScript externos responde a un objetivo pedagógico: demostrar dominio de las tecnologías fundamentales (DOM, Fetch API, async/await, manejo de eventos). El resultado es un frontend completamente funcional con un bundle de cero dependencias JS de terceros.

**¿Por qué Docker?**
Docker garantiza que el entorno de desarrollo sea idéntico al de producción, eliminando el problema "funciona en mi máquina". Un único comando (`docker-compose up`) levanta los cuatro servicios necesarios (app, webserver, db, phpmyadmin) en cualquier sistema operativo.

---

## 5. Arquitectura del sistema

### 5.1 Visión general

GeekZone sigue el patrón arquitectónico **MVC (Modelo-Vista-Controlador)** con una clara separación entre la capa de API REST y la capa de presentación:

```
┌─────────────────────────────────────────────────────────┐
│                    CLIENTE (navegador)                   │
│  Blade (HTML renderizado) + JavaScript fetch/async-await│
└────────────────────────┬────────────────────────────────┘
                         │ HTTP/JSON
┌────────────────────────▼────────────────────────────────┐
│                    NGINX (puerto 8080)                   │
│              Reverse proxy → PHP-FPM                    │
└────────────────────────┬────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────┐
│               LARAVEL (PHP 8.2)                          │
│  ┌─────────────┐  ┌──────────────┐  ┌───────────────┐  │
│  │  Middleware  │  │ Controladores│  │    Modelos    │  │
│  │  JWT + Admin │→ │  API + Vistas│→ │   Eloquent    │  │
│  └─────────────┘  └──────────────┘  └───────┬───────┘  │
│                                              │           │
└──────────────────────────────────────────────┼──────────┘
                                               │
┌──────────────────────────────────────────────▼──────────┐
│                   MySQL 8.0 (puerto 3306)                │
│         geekzone DB — 7 tablas relacionadas              │
└─────────────────────────────────────────────────────────┘
```

### 5.2 Estructura de directorios

```
geekzone-ecommerce/
├── docker-compose.yml           ← Orquestación: app, webserver, db, phpmyadmin
├── Dockerfile                   ← Imagen PHP 8.2-FPM personalizada
├── nginx/default.conf           ← Configuración del servidor web
├── .env.example                 ← Plantilla de variables de entorno
├── docs/                        ← Documentación, diagramas, scripts SQL
└── geekzone/                    ← Código fuente Laravel
    ├── app/
    │   ├── DTOs/
    │   │   └── CategoryDTO.php          ← Patrón DTO para SQL puro
    │   ├── Http/
    │   │   ├── Controllers/
    │   │   │   ├── Api/                 ← 8 controladores REST
    │   │   │   ├── AdminDashboardController.php
    │   │   │   ├── AplicationController.php
    │   │   │   └── AuthController.php
    │   │   └── Middleware/
    │   │       ├── JwtMiddleware.php
    │   │       └── CheckAdminRole.php
    │   └── Models/                      ← 7 modelos Eloquent
    ├── database/
    │   ├── migrations/                  ← 9 migraciones
    │   └── seeders/                     ← Datos de prueba
    ├── resources/views/                 ← 11 vistas Blade
    ├── public/
    │   ├── css/                         ← Hojas de estilo por sección
    │   └── js/auth.js                   ← Gestión JWT en cliente
    ├── routes/
    │   ├── api.php                      ← 25 endpoints REST
    │   └── web.php                      ← 11 rutas de vistas
    ├── storage/api-docs/api-docs.json   ← Especificación OpenAPI generada
    └── tests/Feature/                   ← 7 suites de tests
```

### 5.3 Flujo de una petición API

1. El navegador envía una petición `fetch` con cabecera `Authorization: Bearer <token>`.
2. Nginx recibe la petición y la redirige a PHP-FPM.
3. Laravel enruta la petición al controlador correspondiente (`routes/api.php`).
4. `JwtMiddleware` valida el token JWT; si es inválido, devuelve `401`.
5. Si la ruta requiere rol admin, `CheckAdminRole` verifica el claim `role`; si no es admin, devuelve `403`.
6. El controlador ejecuta la lógica de negocio, consulta la BD mediante Eloquent y devuelve una respuesta JSON con la estructura estándar: `{ success, message, data, errors }`.
7. El JavaScript del cliente procesa la respuesta y actualiza el DOM.

---

## 6. Base de datos

### 6.1 Diseño del esquema

La base de datos consta de **7 tablas** con relaciones bien definidas. El diseño sigue las formas normales 1FN, 2FN y 3FN para evitar redundancia y garantizar la integridad de los datos.

#### Diagrama E/R

*(Ver archivo `docs/diagrama_er.md` — renderizable en GitHub o mermaid.live)*

Las entidades y sus relaciones principales son:

```
users ──────────┬──── orders ──── order_details ──┐
                ├──── carts ──────────────────────── products ──── categories
                └──── favorites ──────────────────────────────────────────────
```

### 6.2 Descripción de tablas

#### Tabla: `users`

Almacena los usuarios del sistema. El campo `role` (ENUM: `user` / `admin`) determina los permisos de acceso. Implementa soft delete mediante `deleted_at`.

| Campo | Tipo | Restricción | Descripción |
|-------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| `name` | VARCHAR(255) | NOT NULL | Nombre del usuario |
| `surname` | VARCHAR(255) | NOT NULL | Apellidos |
| `username` | VARCHAR(255) | NOT NULL | Nombre de usuario |
| `email` | VARCHAR(255) | NOT NULL, UNIQUE | Correo electrónico |
| `password` | VARCHAR(255) | NOT NULL | Hash bcrypt |
| `role` | ENUM | NOT NULL, DEFAULT 'user' | `user` o `admin` |
| `deleted_at` | TIMESTAMP | NULL | Soft delete |

#### Tabla: `categories`

Agrupa los productos en franquicias o temáticas. El nombre es único para evitar duplicados.

| Campo | Tipo | Restricción | Descripción |
|-------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK | Identificador único |
| `name` | VARCHAR(255) | NOT NULL, UNIQUE | Nombre de la categoría |
| `description` | TEXT | NULL | Descripción opcional |
| `image_url` | VARCHAR(500) | NULL | URL o ruta de la imagen |

#### Tabla: `products`

Catálogo de productos. Implementa soft delete; los productos eliminados no aparecen en el catálogo pero sus referencias en `order_details` se mantienen intactas.

| Campo | Tipo | Restricción | Descripción |
|-------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK | Identificador único |
| `name` | VARCHAR(255) | NOT NULL | Nombre del producto |
| `price` | DECIMAL(10,2) | NOT NULL | Precio actual |
| `stock` | INT UNSIGNED | NOT NULL, DEFAULT 0 | Unidades disponibles |
| `featured` | TINYINT(1) | NOT NULL, DEFAULT 0 | Producto destacado |
| `category_id` | BIGINT UNSIGNED | FK → categories | Categoría a la que pertenece |
| `deleted_at` | TIMESTAMP | NULL | Soft delete |

#### Tabla: `carts`

Carrito de compra. Una restricción UNIQUE sobre `(user_id, product_id)` garantiza que un usuario no puede añadir el mismo producto dos veces; en su lugar, incrementa la cantidad.

| Campo | Tipo | Restricción |
|-------|------|-------------|
| `user_id` | BIGINT UNSIGNED | FK → users, UNIQUE con product_id |
| `product_id` | BIGINT UNSIGNED | FK → products, UNIQUE con user_id |
| `quantity` | INT UNSIGNED | NOT NULL, DEFAULT 1 |

#### Tabla: `orders`

Pedidos confirmados. El campo `status` refleja el ciclo de vida del pedido.

| Estado | Descripción |
|--------|-------------|
| `pendiente` | Pedido creado, pendiente de procesar |
| `procesando` | El pedido está siendo preparado |
| `enviado` | El pedido ha sido enviado al cliente |
| `entregado` | El cliente ha recibido el pedido |
| `cancelado` | El pedido ha sido cancelado |

#### Tabla: `order_details`

Líneas de cada pedido. El campo `price` almacena el **precio en el momento de la compra**, no una referencia al precio actual del producto. Esto garantiza que el historial de pedidos sea inmutable ante futuras modificaciones de precios.

#### Tabla: `favorites`

Lista de deseos del usuario. Restricción UNIQUE sobre `(user_id, product_id)` impide duplicados.

### 6.3 Migraciones

Laravel gestiona el esquema mediante migraciones, que proporcionan un historial versionado de cambios en la base de datos:

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

### 6.4 Consultas SQL complejas

Para el panel de administración y los informes del dashboard se han implementado consultas con múltiples JOINs. Ejemplos:

**Top 5 productos más vendidos:**

```sql
SELECT
    p.id,
    p.name                          AS producto,
    c.name                          AS categoria,
    SUM(od.quantity)                AS unidades_vendidas,
    SUM(od.quantity * od.price)     AS ingresos
FROM products p
INNER JOIN categories c     ON c.id = p.category_id
INNER JOIN order_details od ON od.product_id = p.id
INNER JOIN orders o         ON o.id = od.order_id
WHERE o.status != 'cancelado'
  AND p.deleted_at IS NULL
GROUP BY p.id, p.name, c.name
ORDER BY unidades_vendidas DESC
LIMIT 5;
```

**Resumen de ventas por categoría:**

```sql
SELECT
    c.name                              AS categoria,
    COUNT(DISTINCT o.id)                AS num_pedidos,
    SUM(od.quantity)                    AS unidades_vendidas,
    SUM(od.quantity * od.price)         AS ingresos_totales
FROM categories c
INNER JOIN products p       ON p.category_id = c.id
INNER JOIN order_details od ON od.product_id = p.id
INNER JOIN orders o         ON o.id = od.order_id
WHERE o.status != 'cancelado'
GROUP BY c.id, c.name
ORDER BY ingresos_totales DESC;
```

El script SQL completo con todas las tablas, datos de prueba y consultas se encuentra en `docs/geekzone_database.sql`.

---

## 7. Backend — API REST

### 7.1 Estructura de respuestas

Todos los endpoints devuelven respuestas JSON con una estructura uniforme definida en el controlador base (`Api/Controller.php`):

```json
{
  "success": true,
  "message": "Descripción del resultado",
  "data": { ... },
  "errors": null
}
```

En caso de error:

```json
{
  "success": false,
  "message": "Descripción del error",
  "data": null,
  "errors": { "campo": ["mensaje de error"] }
}
```

### 7.2 Endpoints públicos

| Método | Endpoint | Throttle | Descripción |
|--------|----------|----------|-------------|
| `POST` | `/api/register` | 5 req/min | Registro de usuario nuevo |
| `POST` | `/api/login` | 10 req/min | Login — devuelve token JWT |
| `GET` | `/api/categorias` | — | Listado de categorías con contador de productos |
| `GET` | `/api/productos` | — | Listado de productos (con nombre de categoría) |
| `GET` | `/api/productos/{id}` | — | Detalle de un producto |

### 7.3 Endpoints protegidos (JWT requerido)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `POST` | `/api/logout` | Invalida el token JWT |
| `GET` | `/api/perfil` | Ver datos del usuario autenticado |
| `PUT` | `/api/perfil` | Actualizar nombre, apellidos, email o contraseña |
| `GET` | `/api/carrito` | Ver ítems del carrito con totales |
| `POST` | `/api/carrito` | Añadir producto (o incrementar cantidad) |
| `PUT` | `/api/carrito/{id}` | Cambiar cantidad de un ítem |
| `DELETE` | `/api/carrito/{id}` | Eliminar ítem del carrito |
| `GET` | `/api/pedidos` | Historial de pedidos con líneas de detalle |
| `POST` | `/api/pedidos` | Confirmar pedido (consume el carrito, descuenta stock) |
| `GET` | `/api/favoritos` | Lista de favoritos del usuario |
| `POST` | `/api/favoritos` | Añadir producto a favoritos |
| `DELETE` | `/api/favoritos/{productId}` | Quitar producto de favoritos |
| `POST` | `/api/imagenes` | Subir imagen de producto (multipart/form-data) |

### 7.4 Endpoints de administración (JWT + rol admin)

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/api/admin/dashboard/resumen` | Totales: usuarios, productos, pedidos, ingresos |
| `GET` | `/api/admin/dashboard/ingresos` | Ingresos agrupados por período |
| `GET` | `/api/admin/dashboard/top-productos` | Productos más vendidos |
| `GET` | `/api/admin/dashboard/pedidos-por-cliente` | Pedidos agrupados por cliente |
| `POST` | `/api/categorias` | Crear categoría |
| `PUT` | `/api/categorias/{id}` | Editar categoría |
| `DELETE` | `/api/categorias/{id}` | Eliminar categoría |
| `POST` | `/api/productos` | Crear producto |
| `PUT` | `/api/productos/{id}` | Editar producto |
| `DELETE` | `/api/productos/{id}` | Eliminar producto (soft delete) |

### 7.5 Modelos Eloquent

Los siete modelos de la aplicación extienden `Illuminate\Database\Eloquent\Model` y definen relaciones entre sí:

**User** — implementa además `JWTSubject` para la integración con el paquete jwt-auth:
```php
public function getJWTCustomClaims(): array {
    return ['role' => $this->role];
}
public function orders()    { return $this->hasMany(Order::class); }
public function carts()     { return $this->hasMany(Cart::class); }
public function favorites() { return $this->hasMany(Favorite::class); }
```

**Product** — accessor que normaliza las URLs de imagen (relativas o absolutas):
```php
protected function imageUrl(): Attribute {
    return Attribute::make(
        get: fn($v) => $v && !str_starts_with($v, 'http') ? asset($v) : $v
    );
}
```

**Order** — incluye el helper `details()` para acceder a las líneas del pedido:
```php
public function details() { return $this->hasMany(OrderDetail::class); }
```

### 7.6 Patrón DTO — CategoryDTO

El CRUD de categorías utiliza SQL puro en lugar de Eloquent (como ejercicio de uso de `DB::select`). Para mapear los resultados `stdClass` de `DB::select()` a objetos PHP tipados se implementa el patrón **DTO (Data Transfer Object)**:

```php
class CategoryDTO {
    public function __construct(
        public readonly int     $id,
        public readonly string  $name,
        public readonly ?string $description,
        public readonly ?string $image_url,
        public readonly string  $created_at,
        public readonly string  $updated_at,
        public readonly int     $products_count = 0,
    ) {}

    public static function fromRow(object $row): self {
        return new self(
            id:             (int) $row->id,
            name:           $row->name,
            description:    $row->description ?? null,
            image_url:      $row->image_url   ?? null,
            created_at:     $row->created_at,
            updated_at:     $row->updated_at,
            products_count: isset($row->products_count) ? (int) $row->products_count : 0,
        );
    }

    public function toArray(): array {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'description'    => $this->description,
            'image_url'      => $this->image_url,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'products_count' => $this->products_count,
        ];
    }
}
```

El `CategoryController` construye el DTO así:
```php
$dtos = array_map(
    fn(object $row) => CategoryDTO::fromRow($row)->toArray(),
    $categories
);
```

### 7.7 Middleware de seguridad

**JwtMiddleware** — valida el token JWT en cada petición protegida:

```php
public function handle(Request $request, Closure $next): Response {
    try {
        $token = JWTAuth::parseToken()->authenticate();
    } catch (\Exception $e) {
        return response()->json(['message' => 'Token no encontrado'], 401);
    }
    return $next($request);
}
```

**CheckAdminRole** — verifica que el usuario autenticado tenga rol de administrador:

```php
public function handle(Request $request, Closure $next): Response {
    $user = auth('api')->user();
    if (!$user || $user->role !== 'admin') {
        return response()->json(['message' => 'No tienes permisos de administrador.'], 403);
    }
    return $next($request);
}
```

---

## 8. Frontend — Interfaz de usuario

### 8.1 Vistas implementadas

| Ruta | Vista Blade | Descripción |
|------|-------------|-------------|
| `/` | `shop.blade.php` | Tienda principal con productos destacados y categorías |
| `/catalogo` | `catalog.blade.php` | Catálogo completo con filtros por categoría |
| `/producto/{id}` | `product.blade.php` | Detalle: imagen, precio, stock, añadir al carrito y a favoritos |
| `/login` | `auth/login.blade.php` | Login con reCAPTCHA v2 |
| `/register` | `auth/register.blade.php` | Registro de nuevo usuario |
| `/cart` | `cart/cart.blade.php` | Carrito: ver ítems, modificar cantidades, confirmar pedido |
| `/panel` | `userPanel.blade.php` | Panel de usuario: perfil, pedidos, favoritos, seguridad |
| `/favoritos` | `favorites.blade.php` | Lista de productos guardados |
| `/admin` | `admin/dashboard.blade.php` | Dashboard con métricas de negocio |
| `/admin/productos` | `admin/products.blade.php` | CRUD completo de productos |
| `/admin/categorias` | `admin/categories.blade.php` | CRUD completo de categorías |

### 8.2 Gestión del token JWT en el cliente

El archivo `public/js/auth.js` centraliza todas las operaciones relacionadas con el token JWT:

- **Almacenamiento:** el token se guarda en el cliente tras un login exitoso.
- **Inyección:** todas las peticiones protegidas incluyen automáticamente la cabecera `Authorization: Bearer <token>`.
- **Expiración:** si una petición devuelve 401, el usuario es redirigido al login.

El patrón de petición estándar en el frontend:

```javascript
const response = await fetch('/api/carrito', {
    method: 'GET',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
    }
});
const data = await response.json();
```

### 8.3 Diseño responsivo

El frontend utiliza **Flexbox** y **media queries** para adaptar el diseño a diferentes tamaños de pantalla. El punto de ruptura principal es `900px`.

El panel de usuario (`userPanel.blade.php`) presenta un sidebar lateral en escritorio y una barra de navegación horizontal en tablet/móvil. Un caso técnico particular es el uso de `display: contents` en el wrapper `.sidebar-bottom` para que sus elementos hijos participen directamente en el flujo flex del contenedor padre, evitando que aparezcan en una segunda línea.

### 8.4 Envolvente de respuesta API

Todas las respuestas de la API siguen la estructura `{ success, message, data, errors }`. Esto implica que el JavaScript cliente debe acceder a los datos con la ruta `data.data.X` (siendo el primer `data` el objeto JSON parseado y el segundo el campo `data` de la respuesta):

```javascript
const result = await response.json();
const order = result.data?.order ?? {};
document.getElementById('order-id').textContent = `#${order.id}`;
```

---

## 9. Seguridad

### 9.1 Capas de seguridad implementadas

| Capa | Mecanismo | Descripción |
|------|-----------|-------------|
| **Autenticación** | JWT stateless | Token firmado con clave secreta; se invalida en logout |
| **Autorización** | Middleware `CheckAdminRole` | Rutas de administración verifican `role = admin` |
| **Rate limiting** | `throttle:5,1` y `throttle:10,1` | Protege `/register` y `/login` contra fuerza bruta |
| **Anti-bot** | Google reCAPTCHA v2 | Validación en el formulario de login y registro |
| **CSRF** | `@csrf` en formularios Blade | Protección contra peticiones falsificadas |
| **SQL Injection** | Eloquent ORM + Query Builder parametrizado | Todas las consultas usan parámetros `?` o bindings |
| **Validación** | Laravel `Validator` + JS cliente | Doble validación: servidor y cliente |
| **Variables de entorno** | `config()` helper | Nunca `env()` directamente en vistas |
| **Producción** | `APP_DEBUG=false`, `LOG_LEVEL=error` | Sin stack traces expuestos al cliente |

### 9.2 Configuración de reCAPTCHA

Un problema común en entornos con configuración cacheada (`php artisan config:cache`) es que `env()` devuelve `null` en las vistas porque el `.env` no es reprocesado. La solución correcta es siempre pasar las variables por `config/services.php`:

```php
// config/services.php
'recaptcha' => [
    'site_key'   => env('RECAPTCHA_SITE_KEY'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),
],
```

Y en la vista Blade, usar el helper `config()`:

```blade
<div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
```

### 9.3 Variables de entorno en producción

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
L5_SWAGGER_GENERATE_ALWAYS=false
```

---

## 10. Documentación de la API (Swagger)

### 10.1 Implementación

La API está completamente documentada con **anotaciones PHP 8 (`#[OA\...]`)** integradas directamente en los controladores. El paquete `darkaonline/l5-swagger ^11.0` procesa estas anotaciones y genera una especificación OpenAPI 3 en `storage/api-docs/api-docs.json`.

La interfaz Swagger UI está disponible en `/api/documentation`.

### 10.2 Cobertura de anotaciones

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

### 10.3 Ejemplo de anotación

```php
#[OA\Post(
    path: "/api/login",
    summary: "Inicio de sesión",
    description: "Autentica al usuario y devuelve un token JWT",
    tags: ["Autenticación"]
)]
#[OA\RequestBody(
    required: true,
    content: new OA\JsonContent(
        required: ["email", "password"],
        properties: [
            new OA\Property(property: "email",    type: "string", example: "user@geekzone.com"),
            new OA\Property(property: "password", type: "string", example: "user123"),
        ]
    )
)]
#[OA\Response(response: 200, description: "Login exitoso — devuelve token JWT")]
#[OA\Response(response: 401, description: "Credenciales incorrectas")]
public function login(Request $request): JsonResponse { ... }
```

### 10.4 Regenerar la documentación

```bash
docker-compose exec app php artisan l5-swagger:generate
```

---

## 11. Tests automatizados

### 11.1 Configuración

- **Motor:** PHPUnit vía `php artisan test`
- **Base de datos de prueba:** SQLite en memoria (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`)
- **Trait `RefreshDatabase`:** recrea el esquema completo antes de cada test
- **Trait `CreateBaseData`:** helper compartido para crear usuarios, categorías y productos de prueba

### 11.2 Suites de tests Feature

| Suite | Archivo | Tests principales |
|-------|---------|-------------------|
| Autenticación | `AuthTest.php` | Registro exitoso, email duplicado, login correcto, contraseña incorrecta, logout, token inválido |
| Carrito | `CartTest.php` | Obtener carrito, añadir producto, actualizar cantidad, eliminar ítem, sin autenticación |
| Pedidos | `OrderTest.php` | Crear pedido desde carrito, verificar stock descontado, carrito vaciado, historial |
| Productos | `ProductTest.php` | Listar productos, detalle, filtro por categoría |
| Categorías | `CategoryTest.php` | CRUD completo, nombre único |
| Admin | `AdminTest.php` | Crear producto (admin), usuario no puede crear (403), editar producto |

### 11.3 Ejemplo de test

```php
public function test_create_order_from_cart(): void
{
    [$marvel] = $this->createCategoriesForTest();
    [$product1, $product2] = $this->createProductsForTest($marvel->id);
    $headers = $this->loginAsUser();

    $this->postJson('/api/carrito', ['product_id' => $product1->id, 'quantity' => 2], $headers);
    $this->postJson('/api/carrito', ['product_id' => $product2->id], $headers);

    $response = $this->postJson('/api/pedidos', [], $headers);

    $response->assertStatus(201)
        ->assertJsonStructure(['message', 'data' => ['order' => ['id', 'status', 'total']]]);

    // El carrito debe haberse vaciado
    $this->assertEquals(0, Cart::count());

    // El stock debe haberse descontado: producto1 tenía 10, se pidieron 2
    $product1->refresh();
    $this->assertEquals(8, $product1->stock);
}
```

### 11.4 Ejecución

```bash
docker-compose exec app php artisan test --testdox
```

---

## 12. Infraestructura y despliegue

### 12.1 Arquitectura Docker

La aplicación se ejecuta en cuatro contenedores orquestados con Docker Compose:

| Servicio | Imagen | Puerto | Descripción |
|----------|--------|--------|-------------|
| `app` | Dockerfile personalizado (PHP 8.2-FPM) | — | Motor de la aplicación Laravel |
| `webserver` | `nginx:alpine` | 8080:80 | Servidor web / reverse proxy |
| `db` | `mysql:8.0` | 3306:3306 | Base de datos MySQL |
| `phpmyadmin` | `phpmyadmin:latest` | 8081:80 | Gestión visual de la BD |

Todos los servicios comparten la red interna `geekzone_network` (tipo `bridge`). Los datos de MySQL persisten en el volumen `geekzone-db-data`.

### 12.2 Puesta en marcha

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio>
cd geekzone-ecommerce

# 2. Configurar variables de entorno
cp .env.example geekzone/.env
# Editar geekzone/.env con las claves reales

# 3. Levantar los contenedores
docker-compose up -d --build

# 4. Instalar dependencias PHP
docker-compose exec app composer install

# 5. Configurar Laravel
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan jwt:secret

# 6. Base de datos
docker-compose exec app php artisan migrate --seed

# 7. Documentación Swagger
docker-compose exec app php artisan vendor:publish \
  --provider "L5Swagger\L5SwaggerServiceProvider" --tag=config
docker-compose exec app php artisan vendor:publish \
  --provider "L5Swagger\L5SwaggerServiceProvider" --tag=swagger-ui-assets
docker-compose exec app php artisan l5-swagger:generate
```

### 12.3 Accesos tras la instalación

| Servicio | URL | Descripción |
|----------|-----|-------------|
| Tienda GeekZone | `http://localhost:8080` | Aplicación web completa |
| phpMyAdmin | `http://localhost:8081` | Gestión de la base de datos |
| Swagger UI | `http://localhost:8080/api/documentation` | Documentación interactiva API |

### 12.4 Usuarios de prueba

| Rol | Email | Contraseña |
|-----|-------|-----------|
| Administrador | admin@geekzone.com | admin123 |
| Cliente | user@geekzone.com | user123 |

---

## 13. Decisiones técnicas destacadas

### 13.1 `config()` vs `env()` en vistas Blade

En producción, Laravel cachea la configuración con `php artisan config:cache`. Una vez cacheada, la función `env()` devuelve `null` porque el archivo `.env` ya no se lee. Si las vistas acceden directamente a `env('RECAPTCHA_SITE_KEY')`, el widget de reCAPTCHA falla silenciosamente.

La solución correcta es siempre registrar las variables en `config/services.php` y acceder a ellas con `config('services.recaptcha.site_key')`. El helper `config()` lee del caché cuando está disponible, garantizando el comportamiento correcto en todos los entornos.

### 13.2 Precio congelado en `order_details`

El precio de cada línea de pedido se guarda en el campo `price` de `order_details` en el momento de la confirmación, en lugar de referenciar el precio actual de `products.price`. Esto garantiza que el historial de pedidos sea inmutable: si el precio de un producto cambia en el futuro, los pedidos anteriores mantienen el precio que el cliente pagó en su momento.

### 13.3 `display: contents` para el sidebar responsivo

En la vista del panel de usuario, los elementos de la parte inferior del sidebar (divisor, enlace de seguridad, botón de cerrar sesión) están envueltos en un `<div class="sidebar-bottom">`. En escritorio, este div se muestra al final del sidebar con `margin-top: auto`. En móvil, el sidebar es una barra horizontal flex y el div envolvente rompía el flujo, enviando los elementos a una segunda línea. La solución fue aplicar `display: contents` al div en el breakpoint móvil: los navegadores tratan a los hijos como si el div no existiera, por lo que participan directamente en el flujo flex del contenedor padre.

### 13.4 Envolvente de respuesta API (`data.data.order`)

Todos los endpoints devuelven la estructura `{ success, message, data, errors }`. Cuando el dato principal es un objeto (por ejemplo, el pedido creado), la respuesta tiene la forma `{ data: { order: {...} } }`. El JavaScript cliente debe acceder como `result.data.order`, no como `result.order`. Este patrón debe tenerse en cuenta en todo el código frontend.

### 13.5 SQL puro para categorías

El CRUD de categorías se implementa deliberadamente con `DB::select()`, `DB::insert()`, etc., en lugar de Eloquent. El motivo es demostrar competencia en SQL puro y en el patrón DTO para transformar los resultados `stdClass` en objetos tipados. El `CategoryDTO` actúa como capa de mapeo explícita, equivalente a los DTOs que se usarían en una arquitectura hexagonal o en una integración con una base de datos sin ORM.

---

## 14. Resultados y pruebas funcionales

### 14.1 Funcionalidades implementadas y verificadas

| Funcionalidad | Estado |
|---------------|--------|
| Registro de usuarios | Funcionando |
| Login con JWT + reCAPTCHA | Funcionando |
| Catálogo de productos con filtros | Funcionando |
| Detalle de producto | Funcionando |
| Carrito de compra (añadir, modificar, eliminar) | Funcionando |
| Confirmación de pedido (descuento de stock, vaciado de carrito) | Funcionando |
| Historial de pedidos | Funcionando |
| Lista de favoritos | Funcionando |
| Panel de usuario (perfil, contraseña, pedidos, favoritos) | Funcionando |
| Panel de administración — métricas | Funcionando |
| CRUD de productos (admin) | Funcionando |
| CRUD de categorías — SQL puro + DTO (admin) | Funcionando |
| Subida de imágenes | Funcionando |
| Swagger UI | Funcionando |
| Tests automatizados | Funcionando |

### 14.2 Colecciones de prueba manual

La carpeta `geekzone_api_test/` contiene colecciones YAML compatibles con **Bruno** y Thunder Client, que cubren todos los módulos de la API. Permiten probar los endpoints de forma interactiva sin necesidad de Swagger.

### 14.3 Resumen de tests

Los tests Feature cubren:
- 4 tests de autenticación (register, login, logout, token inválido)
- 5 tests de carrito (get, add, update, delete, sin auth)
- 3 tests de pedidos (crear, verificar stock, historial)
- 3 tests de administración (crear producto, autorización, editar)
- Tests de categorías y productos

---

## 15. Mejoras futuras

Las siguientes funcionalidades quedaron fuera del alcance del proyecto por limitaciones de tiempo, pero representan líneas de trabajo viables para una versión futura:

1. **Paginación del catálogo** — actualmente se devuelven todos los productos; con catálogos grandes sería necesario implementar paginación con cursor o por página.

2. **Filtros avanzados** — filtro por rango de precio, por puntuación, por disponibilidad de stock.

3. **Sistema de cupones y descuentos** — tabla `coupons` con códigos, porcentajes de descuento y fechas de validez.

4. **Notificaciones por email** — envío de confirmación de pedido al cliente y aviso al administrador mediante Laravel Mail + Mailgun o SMTP.

5. **Caché con Redis** — cachear los listados de productos y categorías para reducir la carga en la base de datos. Laravel tiene integración nativa con Redis mediante el driver `cache`.

6. **Logs estructurados** — integrar un sistema de logging con contexto (Monolog + Logtail / Papertrail) para monitorizar errores en producción.

7. **Pasarela de pago real** — integrar Stripe o PayPal para procesar pagos reales en lugar de simular el pago.

8. **Valoraciones y comentarios** — permitir a los clientes puntuar y reseñar los productos que han comprado.

9. **Optimización de imágenes** — convertir imágenes subidas a WebP y generar thumbnails de distintos tamaños.

---

## 16. Conclusiones

GeekZone es una aplicación web Full-Stack completa que integra todas las competencias trabajadas durante el ciclo de 2º DAW: diseño de bases de datos relacionales, desarrollo de APIs REST seguras, implementación de patrones de diseño (MVC, DTO, middleware), construcción de interfaces responsivas sin frameworks externos, containerización con Docker y documentación profesional con OpenAPI.

Desde el punto de vista técnico, los aspectos más destacables del proyecto son:

- La **separación clara** entre la capa API y la capa de presentación, lo que permite que el backend sea consumido por cualquier cliente (aplicación móvil, SPA externa, herramientas de testing).
- La **autenticación stateless con JWT**, que elimina la dependencia de sesiones en servidor y hace la API horizontalmente escalable.
- El **patrón DTO** implementado en el CRUD de categorías, que demuestra cómo mapear resultados de SQL puro a objetos PHP tipados de forma explícita y controlada.
- Las **medidas de seguridad en capas**: JWT + middleware de roles + rate limiting + reCAPTCHA + validación doble + uso correcto del helper `config()` en lugar de `env()`.
- La **suite de tests automatizados** que cubre los flujos críticos del negocio (autenticación, carrito, pedidos, administración) con aserciones sobre la base de datos, no solo sobre el código.

Desde el punto de vista personal, el proyecto ha supuesto un reto significativo en cuanto a coordinación de equipo, gestión de ramas en Git y depuración de problemas que solo se manifiestan en entornos específicos (como el comportamiento de `env()` con la caché de configuración). Estas situaciones son precisamente las que más aprendizaje generan, porque obligan a entender no solo el código, sino el entorno de ejecución completo.

---

## 17. Referencias

- **Laravel — Documentación oficial:** https://laravel.com/docs/11.x
- **JWT Auth para Laravel:** https://jwt-auth.readthedocs.io
- **OpenAPI 3.0 Specification:** https://spec.openapis.org/oas/v3.0.3
- **l5-swagger — Documentación:** https://github.com/DarkaOnLine/L5-Swagger
- **Docker — Documentación oficial:** https://docs.docker.com
- **MySQL 8.0 — Reference Manual:** https://dev.mysql.com/doc/refman/8.0/en/
- **Google reCAPTCHA v2 — Guía de integración:** https://developers.google.com/recaptcha/docs/display
- **Mermaid — Documentación de diagramas:** https://mermaid.js.org/syntax/entityRelationshipDiagram.html
- **PHP 8.2 — Manual oficial:** https://www.php.net/manual/en/
- **MDN Web Docs — Fetch API:** https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API

---

*Proyecto académico — IES Villa de Agüimes © 2025-2026*

*Documento generado en Abril 2026*
