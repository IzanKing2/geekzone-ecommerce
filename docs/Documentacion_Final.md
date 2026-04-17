# GeekZone — Documentación Final del Proyecto

*(TFG — 2º DAW, IES Villa de Agüimes, Curso 2025/2026)*
*(Actualizado — Abril 2026)*

---

## 1. Introducción y Objetivos

**¿Qué es GeekZone?**
GeekZone es una plataforma de e-commerce especializada en productos de cultura geek: franquicias de Marvel, grupos musicales como Stray Kids y equipos de fútbol. Ha sido desarrollado como Proyecto Final (TFG) del ciclo de 2º DAW.

**¿Por qué se ha desarrollado?**
El objetivo es demostrar competencia técnica en el desarrollo de aplicaciones web **Full-Stack** aplicando buenas prácticas, seguridad, arquitectura escalable y diseño responsivo.

**¿Para qué sirve?**
- Los **clientes** pueden registrarse, explorar el catálogo, filtrar por categorías, ver el detalle de cada producto, añadir al carrito, gestionar una lista de favoritos y realizar pedidos.
- Los **administradores** disponen de un panel de control con métricas del negocio y herramientas para crear, editar y eliminar productos y categorías.

---

## 2. Stack Tecnológico

| Capa | Tecnología | Justificación |
|------|-----------|---------------|
| **Backend** | Laravel (PHP 8.2) | Framework MVC profesional; Eloquent ORM, routing y seguridad integrados |
| **Base de datos** | MySQL 8.0 | Relacional, garantiza integridad de datos entre pedidos, usuarios y productos |
| **Autenticación** | JWT (`php-open-source-saver/jwt-auth`) | Stateless; adecuado para APIs REST sin sesiones en servidor |
| **Frontend** | Blade + HTML5 + CSS nativo + JS (async/await) | Sin frameworks JS externos; demuestra dominio de las tecnologías base |
| **Infraestructura** | Docker + Docker Compose + Nginx | Entorno reproducible en cualquier máquina (Windows, Mac, Linux) |
| **Documentación API** | Swagger UI (`darkaonline/l5-swagger`) | Genera documentación interactiva OpenAPI 3 a partir de anotaciones PHP |
| **Seguridad extra** | Google reCAPTCHA v2 | Protección anti-bot en el formulario de login |

---

## 3. Arquitectura y Estructura del Código

El patrón principal es **MVC (Modelo-Vista-Controlador)**:

- **Modelos** (`app/Models`): representan las tablas de la BD.
- **Vistas** (`resources/views`): plantillas Blade que el navegador renderiza.
- **Controladores** (`app/Http/Controllers`): lógica que une modelos y vistas.

### Estructura de directorios clave

```
geekzone/
├── app/Http/Controllers/
│   ├── Api/                    ← Controladores de la API REST
│   ├── AdminDashboardController.php
│   ├── AplicationController.php
│   └── AuthController.php
├── app/Models/                 ← User, Product, Category, Cart, Order, OrderDetail, Favorite
├── app/Http/Middleware/        ← JwtMiddleware, CheckAdminRole
├── database/migrations/        ← Historial de cambios de la BD
├── database/seeders/           ← Datos de prueba (categorías, productos, usuarios)
├── resources/views/
│   ├── layouts/                ← Plantilla base, cabecera y pie de página
│   ├── auth/                   ← Login (con reCAPTCHA), registro
│   ├── admin/                  ← Dashboard, CRUD productos, CRUD categorías
│   ├── cart/                   ← Carrito de compra
│   ├── shop.blade.php          ← Tienda principal
│   ├── catalog.blade.php       ← Catálogo con filtros
│   ├── product.blade.php       ← Detalle de producto
│   ├── favorites.blade.php     ← Lista de favoritos
│   └── userPanel.blade.php     ← Panel de usuario (perfil, pedidos, favoritos)
├── public/css/                 ← Hojas de estilo por sección
├── public/js/auth.js           ← Gestión del token JWT en el cliente
├── routes/api.php              ← Endpoints REST
├── routes/web.php              ← Rutas de vistas
└── storage/api-docs/           ← Documentación OpenAPI generada
```

---

## 4. Base de Datos y Modelos

Diseño relacional con siete entidades principales:

| Modelo | Descripción |
|--------|-------------|
| `User` | Usuarios con campo `role` (cliente / admin) |
| `Category` | Categorías de productos (Marvel, Fútbol, Stray Kids…) |
| `Product` | Pertenece a una categoría; tiene nombre, precio, stock e imagen |
| `Cart` | Ítems del carrito por usuario con cantidad |
| `Order` | Pedido confirmado con estado: pendiente / procesando / enviado / entregado / cancelado |
| `OrderDetail` | Línea de pedido; precio **congelado** en el momento de la compra |
| `Favorite` | Relación usuario-producto para la lista de favoritos |

El precio se almacena en `OrderDetail` en lugar de referenciarse desde `Product` para garantizar que el historial de pedidos sea inmutable ante futuros cambios de precio.

---

## 5. Frontend — Experiencia de Usuario

El frontend consume la API REST via JavaScript (fetch + async/await). El token JWT se almacena en el cliente (`auth.js`) y se adjunta automáticamente a cada petición protegida.

### Páginas disponibles

| Ruta | Descripción |
|------|-------------|
| `/` | Tienda principal |
| `/catalogo` | Catálogo con filtros por categoría |
| `/producto/{id}` | Detalle: imagen, precio, stock, añadir al carrito y a favoritos |
| `/login` | Login con validación Google reCAPTCHA v2 |
| `/register` | Registro de nuevo usuario |
| `/cart` | Carrito: modificar cantidades, eliminar ítems, confirmar pedido |
| `/panel` | Panel de usuario: perfil, historial de pedidos, favoritos, cambio de contraseña |
| `/favoritos` | Lista de productos guardados |
| `/admin` | Dashboard admin con métricas de negocio |
| `/admin/productos` | CRUD completo de productos |
| `/admin/categorias` | CRUD completo de categorías |

### Diseño responsivo

Las interfaces se adaptan a móvil mediante **Flexbox** y **media queries**. El panel de usuario pasa de un sidebar lateral en escritorio a una barra de navegación horizontal en tablet/móvil.

---

## 6. Seguridad y Autenticación

| Capa | Mecanismo |
|------|-----------|
| **Autenticación** | JWT stateless; token invalidado en logout |
| **Autorización** | Middleware `CheckAdminRole` — rutas admin requieren `role = admin` |
| **Rate limiting** | 5 req/min en `/register`, 10 req/min en `/login` |
| **Anti-bot** | Google reCAPTCHA v2 en el login |
| **CSRF** | Directiva `@csrf` en todos los formularios Blade |
| **SQL injection** | Eloquent ORM y query builder parametrizado |
| **Validación** | Doble validación: servidor (Laravel) + cliente (JavaScript) |

---

## 7. Documentación Swagger / OpenAPI

Todos los controladores de la API tienen **anotaciones PHP 8 (`#[OA\...]`)** que generan automáticamente una especificación OpenAPI 3.

- **UI accesible en**: `http://localhost:8080/api/documentation`
- **JSON generado en**: `storage/api-docs/api-docs.json`
- **Paquete**: `darkaonline/l5-swagger ^11.0`

Regenerar documentación:
```bash
docker-compose exec app php artisan l5-swagger:generate
```

---

## 8. Despliegue y Puesta en Marcha

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio>
cd geekzone-ecommerce

# 2. Configurar variables de entorno
cp .env.example geekzone/.env

# 3. Levantar los contenedores Docker
docker-compose up -d --build

# 4. Instalar dependencias y configurar Laravel
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan jwt:secret

# 5. Base de datos
docker-compose exec app php artisan migrate --seed

# 6. Swagger UI
docker-compose exec app php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider" --tag=config
docker-compose exec app php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider" --tag=swagger-ui-assets
docker-compose exec app php artisan l5-swagger:generate
```

### Accesos tras la instalación

| Servicio | URL |
|----------|-----|
| Tienda GeekZone | <http://localhost:8080> |
| phpMyAdmin | <http://localhost:8081> |
| Swagger UI | <http://localhost:8080/api/documentation> |

### Usuarios de prueba

| Rol | Email | Contraseña |
|-----|-------|-----------|
| Admin | admin@geekzone.com | admin123 |
| Cliente | user@geekzone.com | user123 |

---

## 9. Tests Automatizados

```bash
docker-compose exec app php artisan test --testdox
```

- **Motor**: PHPUnit vía Laravel
- **BD de prueba**: SQLite en memoria (`DB_DATABASE=:memory:`)
- **Suites**: `tests/Unit` y `tests/Feature`
- Los tests de Feature cubren los flujos completos de auth, catálogo, carrito, pedidos, favoritos y administración.

---

## 10. Posibles Mejoras Futuras

- Paginación y filtros avanzados en el catálogo (precio, valoraciones)
- Sistema de cupones y descuentos
- Notificaciones por email al crear o actualizar pedidos
- Logs estructurados y monitorización
- Caché de listados de productos (Redis)
- Optimización de consultas en el dashboard de administración

---

*Proyecto académico — IES Villa de Agüimes © 2025-2026*
