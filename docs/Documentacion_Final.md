# 🛒 GeekZone — Documentación Completa del Proyecto

*(Actualizado a la última versión con Frontend Integrado)*

---

## 1. Introducción y Objetivos
**¿Qué es GeekZone?**
GeekZone es una plataforma integral de e-commerce (tienda virtual) especializada en productos de cultura geek, abarcando franquicias como Marvel, grupos musicales como Stray Kids y equipos de Fútbol. Ha sido desarrollado como Proyecto Final (TFG) del ciclo de 2º DAW.

**¿Por qué se ha desarrollado?**
El objetivo principal es demostrar la competencia técnica en el desarrollo de aplicaciones web "Full-Stack" (cliente y servidor) aplicando buenas prácticas, seguridad, arquitectura escalable y un diseño responsivo (adaptable a móviles).

**¿Para qué sirve?**
Permite a **clientes** registrarse, explorar el catálogo, buscar productos, añadir favoritos, agregar al carrito, y tramitar pedidos. A su vez, ofrece a los **administradores** un panel de control para crear/editar productos, subir sus imágenes, manejar categorías y revisar las ventas.

---

## 2. Stack Tecnológico (Tecnologías Usadas)
Se ha optado por una arquitectura moderna basada en contenedores para un despliegue seguro y reproducible.

*   **Backend (Servidor):** Laravel y PHP 8.2. 
    *   *¿Por qué?* Laravel es un framework MVC (Modelo-Vista-Controlador) muy utilizado en el ámbito profesional, que provee herramientas para interactuar con bases de datos de forma fácil (Eloquent ORM), enrutamiento y seguridad incluida.
*   **Frontend (Cliente):** HTML5, CSS3 Nativo, JavaScript (Vanilla JS) y plantillas Blade.
    *   *¿Por qué?* Blade es el motor de plantillas de Laravel que junta HTML con datos del servidor sin esfuerzo. Se utiliza CSS y JS nativo en vez de librerías para tener un control total y demostrar las capacidades fundamentales de desarrollo sin dependencias innecesarias.
*   **Base de Datos:** MySQL 8.0.
    *   *¿Por qué?* Es un sistema de bases de datos relacional sólido y fiable para garantizar la coherencia de datos (ej. un pedido siempre debe pertenecer a un usuario que existe).
*   **Autenticación:** JWT (JSON Web Tokens).
    *   *¿Por qué?* En vez de usar sesiones tradicionales acopladas al servidor, un Token permite una comunicación segura a través de APIs de manera "stateless" (sin estado), la forma correcta para aplicaciones modernas.
*   **Infraestructura:** Docker, Docker Compose y Nginx.
    *   *¿Por qué?* Docker permite encapsular todo el entorno (servidor web Nginx, PHP y MySQL) en "contenedores", por lo cual el proyecto funcionará exactamente igual en cualquier ordenador (Windows, Mac o Linux) sin instalar los lenguajes en el propio sistema.

---

## 3. Arquitectura y Estructura del Código
El patrón principal en el que se basa Laravel es el **MVC (Modelo-Vista-Controlador)**.
*   **Modelos (`app/Models`):** Representan las tablas de la base de datos (Usuario, Producto, Pedido).
*   **Vistas (`resources/views`):** Las pantallas que ve el usuario (ej. `welcome.blade.php`, `userPanel.blade.php`).
*   **Controladores (`app/Http/Controllers`):** El "cerebro" que une los Modelos y las Vistas.

### Estructura de Directorios Clave:
```text
/geekzone
  ├── app/
  │   ├── Http/Controllers/    # La lógica de las distintas secciones (Products, Cart, Admin).
  │   ├── Models/              # Lógica de datos (BD).
  │   └── Middleware/          # Capas de seguridad intermedias (Ej: ¿Es este usuario Admin?).
  ├── database/
  │   ├── migrations/          # Archivos que crean las estructuras de la BD iterativamente.
  │   └── seeders/             # "Semillas" para rellenar la BD con datos iniciales (productos y usuarios falsos).
  ├── resources/
  │   ├── views/               # Plantillas Blade (Frontend - userPanel, catalog, admin...).
  │   └── css/                 # Hojas de estilo estructuradas.
  ├── routes/
  │   ├── api.php              # Rutas de backend (Endpoints para consumir datos).
  │   └── web.php              # Rutas del entorno de las Vistas HTML.
  └── piblic/                  # Assets (Javascript y subida de imágenes - /img).
```

---

## 4. Base de Datos y Modelos
Se basa en un diseño relacional. Los modelos más importantes son:

1.  **User (Usuario):** Puede ser de tipo `cliente` o `admin`.
2.  **Category (Categoría):** Marvel, Fútbol, Stray Kids.
3.  **Product (Producto):** Pertenece a una categoría y tiene atributos como nombre, precio, stock, y la ruta de la imagen (ej: `marvel_comic.jpg`).
4.  **Cart (Carrito):** Relación temporal de qué tiene cada usuario preparado para comprar.
5.  **Order y OrderDetail (Pedido y Detalle):** Cuando el carrito se paga, los datos del producto se "congelan" aquí, ya que el precio puede variar en el futuro, pero el pedido debe ser inmutable.
6.  **Favorite (Favoritos):** Tabla pivote para guardar si un usuario ha dado *like* a un producto.

---

## 5. Diseño del Frontend: UI y Responsive
El Frontend es donde interactúa nuestro cliente, diseñado cuidando la experiencia de usuario (UX).

*   **Vistas Dinámicas (Blade):** Al utilizar Laravel, páginas como el catálogo (`shop.blade.php`) iteran sobre los productos y los muestran fácilmente.
*   **Desarrollo Responsivo (`userPanel.css`):** Mediante `flexbox` y `media queries` (`@media screen and (max-width: 768px)`), interfaces complejas como el **Panel de Usuario** pasan de un diseño lateral en ordenador a un diseño apilado fácil de navegar en móvil.
*   **Modales Globales Interactivos:** Se ha dejado atrás los obsoletos popups de "Aviso" del navegador (`alert()`) creando un sistema propio con ventanas emergentes elegantes gestionadas en JavaScript para los avisos y advertencias de la tienda.
*   **Panel de Administración / CRUD:** El administrador tiene una zona propia (`admin`) para, a través de formularios, subir nuevos productos y editar los existentes. Aquí se integran validaciones de seguridad tanto en JS como en PHP para garantizar que las imágenes sean válidas.

---

## 6. Seguridad y Autenticación
**¿Cómo nos aseguramos de que cada uno hace lo que debe?**

1.  **Protección de Rutas (Middleware):** El archivo de rutas tiene definidas barreras. Por ejemplo, el `CheckAdminRole` detendrá cualquier acción sobre un producto a menos que el usuario sea Admin.
2.  **JWT en la API:** Todas las comunicaciones de datos sensibles mediante JS van firmadas con un pequeño criptograma (Token). Así el servidor distingue y verifica quién solicita cada cosa (añadir al carrito, ver pedido...).
3.  **Protección CSRF y Sanitización:** Al renderizar formularios con Blade, siempre se utiliza la directiva `@csrf` para evitar que bots manden datos falsificados desde otras webs, y Eloquent de forma nativa filtra todo código malicioso escrito (previene "Inyecciones SQL").

---

## 7. Despliegue y Puesta en Marcha
Todo el proyecto está altamente automatizado para su instalación en pocos comandos gracias a **Docker**:

```bash
# 1. Copiar las variables de entorno de prueba a un archivo local
cp .env.example geekzone/.env

# 2. Compilar e iniciar los contenedores
docker-compose up -d --build

# 3. Instalar librerías PHP e inicializar bases de datos dentro de la máquina
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan jwt:secret
docker-compose exec app php artisan migrate --seed
```

Tras esto, la web se visualiza en `http://localhost:8080` de manera fluida y con toda la información de prueba cargada (Usuarios Administradores y Productos de muestra).

---
*Este documento conforma un resumen técnico final de la infraestructura completa del proyecto.*
