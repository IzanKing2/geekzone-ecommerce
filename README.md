# 🛒 GeekZone — E-commerce de Productos Geek

E-commerce especializado en productos de **Marvel**, **Stray Kids** y **Fútbol**.

Proyecto intermodular de **2º DAW** — IES Villa de Agüimes (Curso 2025/2026).

**Equipo**: Izan, Saúl, Marisa

---

## 🛠️ Stack Tecnológico

| Componente | Tecnología |
|------------|-----------|
| **Backend** | Laravel (PHP 8.2) |
| **Base de datos** | MySQL 8.0 |
| **Autenticación** | JWT (`php-open-source-saver/jwt-auth`) |
| **Frontend** | HTML5 + CSS + JavaScript (async/await) |
| **Servidor web** | Nginx |
| **Contenedores** | Docker + Docker Compose |

---

## 🚀 Instalación y Puesta en Marcha

### Requisitos previos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado
- Git instalado

### Pasos

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio>
cd "Proyecto final"

# 2. Copiar el archivo de configuración
cp .env.example src/.env

# 3. Levantar los contenedores Docker
docker-compose up -d --build

# 4. Instalar dependencias de PHP (Composer)
docker-compose exec app composer install

# 5. Generar la clave de la aplicación Laravel
docker-compose exec app php artisan key:generate

# 6. Instalar y generar la clave secreta para JWT
docker-compose exec app composer require php-open-source-saver/jwt-auth

docker-compose exec app php artisan jwt:secret

# 7. Ejecutar las migraciones (crear tablas en la BD)
docker-compose exec app php artisan migrate

# 8. Ejecutar los seeders (datos de prueba)
docker-compose exec app php artisan db:seed
```

### Accesos

| Servicio | URL | Descripción |
|----------|-----|------------|
| 🌐 GeekZone | <http://localhost:8080> | Aplicación web |
| 📊 phpMyAdmin | <http://localhost:8081> | Gestión de base de datos |
| 📖 API Docs | <http://localhost:8080/api/documentation> | Documentación Swagger |

### Usuarios de prueba

| Rol | Email | Contraseña |
|-----|-------|-----------|
| Admin | <admin@geekzone.com> | password |
| Cliente | <user@geekzone.com> | password |

---

## 📁 Estructura del Proyecto

```
Proyecto final/
├── docker-compose.yml       # Orquestación de contenedores
├── Dockerfile               # Imagen PHP personalizada
├── nginx/default.conf       # Configuración de Nginx
├── .env.example             # Plantilla de variables de entorno
├── src/                     # Código fuente Laravel
│   ├── app/Controllers/     # Controladores de la API
│   ├── app/Models/          # Modelos Eloquent
│   ├── app/Middleware/       # JWT y CheckAdminRole
│   ├── database/migrations/ # Migraciones de la BD
│   ├── database/seeders/    # Datos de prueba
│   ├── routes/api.php       # Rutas de la API
│   ├── public/              # Frontend (HTML/CSS/JS)
│   └── tests/               # Tests PHPUnit
├── docs/                    # Documentación del proyecto
└── Recursos/                # Recursos del proyecto
```

---

## 🧪 Tests

```bash
# Ejecutar todos los tests
docker-compose exec app php artisan test --testdox
```

---

## 📝 API — Endpoints Principales

### Públicos

- `POST /api/register` — Registro de usuario
- `POST /api/login` — Inicio de sesión (devuelve JWT)
- `GET /api/productos` — Listado de productos
- `GET /api/productos/{id}` — Detalle de producto
- `GET /api/categorias` — Listado de categorías

### Protegidos (JWT)

- `GET /api/perfil` — Ver perfil
- `PUT /api/perfil` — Actualizar perfil
- `GET/POST/PUT/DELETE /api/carrito` — Carrito de compras
- `GET/POST /api/pedidos` — Pedidos

### Administración (JWT + Admin)

- `GET /api/admin/dashboard/*` — Analíticas
- `POST/PUT/DELETE /api/admin/productos/*` — CRUD Productos
- `POST/PUT/DELETE /api/admin/categorias/*` — CRUD Categorías

---

## 📄 Licencia

Proyecto académico — IES Villa de Agüimes © 2025-2026
