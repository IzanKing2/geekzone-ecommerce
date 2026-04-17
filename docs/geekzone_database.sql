-- ============================================================
--  GeekZone — Script completo de Base de Datos
--  MySQL 8.0
--  IES Villa de Agüimes · 2º DAW · Curso 2025/2026
-- ============================================================


-- ============================================================
--  1. CREACIÓN DE LA BASE DE DATOS
-- ============================================================

CREATE DATABASE IF NOT EXISTS geekzone
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE geekzone;


-- ============================================================
--  2. CREACIÓN DE TABLAS (con PKs, FKs e índices)
-- ============================================================

-- ------------------------------------------------------------
--  Tabla: users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name              VARCHAR(255)     NOT NULL,
    surname           VARCHAR(255)     NOT NULL,
    username          VARCHAR(255)     NOT NULL,
    email             VARCHAR(255)     NOT NULL,
    email_verified_at TIMESTAMP        NULL,
    password          VARCHAR(255)     NOT NULL,
    role              ENUM('user','admin') NOT NULL DEFAULT 'user',
    remember_token    VARCHAR(100)     NULL,
    created_at        TIMESTAMP        NULL,
    updated_at        TIMESTAMP        NULL,
    deleted_at        TIMESTAMP        NULL,

    CONSTRAINT pk_users          PRIMARY KEY (id),
    CONSTRAINT uq_users_email    UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
--  Tabla: categories
--  CRUD gestionado con SQL puro (CategoryController + CategoryDTO)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(255)    NOT NULL,
    description TEXT            NULL,
    image_url   VARCHAR(500)    NULL,
    created_at  TIMESTAMP       NULL,
    updated_at  TIMESTAMP       NULL,

    CONSTRAINT pk_categories       PRIMARY KEY (id),
    CONSTRAINT uq_categories_name  UNIQUE (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
--  Tabla: products
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name        VARCHAR(255)     NOT NULL,
    description TEXT             NULL,
    price       DECIMAL(10,2)    NOT NULL,
    stock       INT UNSIGNED     NOT NULL DEFAULT 0,
    featured    TINYINT(1)       NOT NULL DEFAULT 0,
    image_url   VARCHAR(255)     NULL,
    category_id BIGINT UNSIGNED  NOT NULL,
    created_at  TIMESTAMP        NULL,
    updated_at  TIMESTAMP        NULL,
    deleted_at  TIMESTAMP        NULL,

    CONSTRAINT pk_products              PRIMARY KEY (id),
    CONSTRAINT fk_products_category_id  FOREIGN KEY (category_id)
        REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_products_category_id (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
--  Tabla: carts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS carts (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity   INT UNSIGNED    NOT NULL DEFAULT 1,
    created_at TIMESTAMP       NULL,
    updated_at TIMESTAMP       NULL,

    CONSTRAINT pk_carts                PRIMARY KEY (id),
    CONSTRAINT fk_carts_user_id        FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_carts_product_id     FOREIGN KEY (product_id)
        REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT uq_carts_user_product   UNIQUE (user_id, product_id),
    INDEX idx_carts_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
--  Tabla: orders
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id         BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id    BIGINT UNSIGNED  NOT NULL,
    status     ENUM('pendiente','procesando','enviado','entregado','cancelado')
               NOT NULL DEFAULT 'pendiente',
    total      DECIMAL(10,2)    NOT NULL,
    created_at TIMESTAMP        NULL,
    updated_at TIMESTAMP        NULL,

    CONSTRAINT pk_orders           PRIMARY KEY (id),
    CONSTRAINT fk_orders_user_id   FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_orders_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
--  Tabla: order_details
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_details (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id   BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity   INT UNSIGNED    NOT NULL,
    price      DECIMAL(10,2)   NOT NULL,   -- precio congelado en el momento de la compra
    created_at TIMESTAMP       NULL,
    updated_at TIMESTAMP       NULL,

    CONSTRAINT pk_order_details              PRIMARY KEY (id),
    CONSTRAINT fk_order_details_order_id     FOREIGN KEY (order_id)
        REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_details_product_id   FOREIGN KEY (product_id)
        REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
--  Tabla: favorites
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS favorites (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP       NULL,
    updated_at TIMESTAMP       NULL,

    CONSTRAINT pk_favorites              PRIMARY KEY (id),
    CONSTRAINT fk_favorites_user_id      FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_product_id   FOREIGN KEY (product_id)
        REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT uq_favorites_user_product UNIQUE (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  3. DATOS DE PRUEBA
-- ============================================================

-- Usuarios (contraseñas hasheadas con Bcrypt por Laravel)
INSERT INTO users (name, surname, username, email, password, role, created_at, updated_at) VALUES
('Admin',   'GeekZone', 'admin',    'admin@geekzone.com', '$2y$12$hashedAdminPassword',  'admin', NOW(), NOW()),
('Cliente', 'Prueba',   'cliente1', 'user@geekzone.com',  '$2y$12$hashedClientPassword', 'user',  NOW(), NOW()),
('María',   'García',   'maria_g',  'maria@example.com',  '$2y$12$hashedMariaPassword',  'user',  NOW(), NOW());

-- Categorías (CRUD gestionado con SQL puro)
INSERT INTO categories (name, description, image_url, created_at, updated_at) VALUES
('Marvel',     'Productos de franquicias Marvel: Spider-Man, Avengers, X-Men...', '/img/categories/marvel.jpg',     NOW(), NOW()),
('Fútbol',     'Camisetas, bufandas y accesorios de los mejores equipos del mundo', '/img/categories/futbol.jpg',    NOW(), NOW()),
('Stray Kids', 'Merchandising oficial del grupo K-Pop Stray Kids',                 '/img/categories/straykids.jpg', NOW(), NOW());

-- Productos
INSERT INTO products (name, description, price, stock, featured, image_url, category_id, created_at, updated_at) VALUES
('Camiseta Spider-Man',       'Camiseta 100% algodón con estampado de Spider-Man', 24.99,  50, 1, '/img/products/spiderman_tshirt.jpg', 1, NOW(), NOW()),
('Figura Iron Man',           'Figura coleccionable de Iron Man, 30 cm',           49.99,  20, 1, '/img/products/ironman_figure.jpg',   1, NOW(), NOW()),
('Taza Capitán América',      'Taza cerámica con escudo del Capitán América',       12.99, 100, 0, '/img/products/cap_mug.jpg',         1, NOW(), NOW()),
('Camiseta Real Madrid',      'Camiseta oficial temporada 2025/2026',               89.99,  30, 1, '/img/products/rm_shirt.jpg',        2, NOW(), NOW()),
('Bufanda FC Barcelona',      'Bufanda oficial del FC Barcelona',                   19.99,  75, 0, '/img/products/barca_scarf.jpg',     2, NOW(), NOW()),
('Álbum MIROH',               'Álbum físico de Stray Kids — MIROH Edition',         29.99,  40, 1, '/img/products/miroh_album.jpg',     3, NOW(), NOW()),
('Poster Stray Kids Stay',    'Poster oficial 60x90 cm firmado',                    14.99,  60, 0, '/img/products/sk_poster.jpg',       3, NOW(), NOW()),
('Hoodie Wolfhan Stray Kids', 'Sudadera oficial colección Wolfhan',                 59.99,  15, 1, '/img/products/wolfhan_hoodie.jpg',  3, NOW(), NOW());

-- Pedidos
INSERT INTO orders (user_id, status, total, created_at, updated_at) VALUES
(2, 'entregado', 74.98, '2026-03-10 10:00:00', '2026-03-12 14:00:00'),
(2, 'enviado',   89.99, '2026-04-01 09:00:00', '2026-04-02 11:00:00'),
(3, 'pendiente', 44.98, '2026-04-15 18:30:00', '2026-04-15 18:30:00');

-- Detalles de pedido (precio congelado en el momento de la compra)
INSERT INTO order_details (order_id, product_id, quantity, price, created_at, updated_at) VALUES
(1, 1, 2, 24.99, '2026-03-10 10:00:00', '2026-03-10 10:00:00'),  -- 2x Camiseta Spider-Man
(1, 3, 1, 12.99, '2026-03-10 10:00:00', '2026-03-10 10:00:00'),  -- 1x Taza Capitán América
(1, 7, 1, 14.99, '2026-03-10 10:00:00', '2026-03-10 10:00:00'),  -- 1x Poster SK
(2, 4, 1, 89.99, '2026-04-01 09:00:00', '2026-04-01 09:00:00'),  -- 1x Camiseta Real Madrid
(3, 6, 1, 29.99, '2026-04-15 18:30:00', '2026-04-15 18:30:00'),  -- 1x Álbum MIROH
(3, 7, 1, 14.99, '2026-04-15 18:30:00', '2026-04-15 18:30:00');  -- 1x Poster SK

-- Favoritos
INSERT INTO favorites (user_id, product_id, created_at, updated_at) VALUES
(2, 2, NOW(), NOW()),
(2, 6, NOW(), NOW()),
(3, 4, NOW(), NOW()),
(3, 8, NOW(), NOW());


-- ============================================================
--  4. CONSULTAS COMPLEJAS (JOINs)
-- ============================================================

-- ------------------------------------------------------------
--  C1. Catálogo completo: productos con nombre de categoría
--      y cantidad de veces vendido
-- ------------------------------------------------------------
SELECT
    p.id,
    p.name                              AS producto,
    p.price                             AS precio,
    p.stock,
    c.name                              AS categoria,
    COALESCE(SUM(od.quantity), 0)       AS total_vendido
FROM products p
INNER JOIN categories c    ON c.id = p.category_id
LEFT  JOIN order_details od ON od.product_id = p.id
WHERE p.deleted_at IS NULL
GROUP BY p.id, p.name, p.price, p.stock, c.name
ORDER BY total_vendido DESC, p.name ASC;


-- ------------------------------------------------------------
--  C2. Historial de pedidos de un usuario con detalle de productos
-- ------------------------------------------------------------
SELECT
    o.id                                AS pedido_id,
    o.status,
    o.total,
    o.created_at                        AS fecha,
    p.name                              AS producto,
    od.quantity                         AS cantidad,
    od.price                            AS precio_unidad,
    (od.quantity * od.price)            AS subtotal_linea
FROM orders o
INNER JOIN order_details od ON od.order_id = o.id
INNER JOIN products p       ON p.id = od.product_id
WHERE o.user_id = 2
ORDER BY o.created_at DESC, od.id ASC;


-- ------------------------------------------------------------
--  C3. Resumen de ventas por categoría (dashboard admin)
-- ------------------------------------------------------------
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


-- ------------------------------------------------------------
--  C4. Top 5 productos más vendidos
-- ------------------------------------------------------------
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


-- ------------------------------------------------------------
--  C5. Usuarios con total gastado y número de pedidos
-- ------------------------------------------------------------
SELECT
    u.id,
    CONCAT(u.name, ' ', u.surname)  AS cliente,
    u.email,
    COUNT(DISTINCT o.id)            AS num_pedidos,
    SUM(o.total)                    AS total_gastado
FROM users u
LEFT JOIN orders o ON o.user_id = u.id AND o.status != 'cancelado'
WHERE u.role = 'user'
  AND u.deleted_at IS NULL
GROUP BY u.id, u.name, u.surname, u.email
ORDER BY total_gastado DESC;


-- ------------------------------------------------------------
--  C6. Categorías con número de productos y stock total
--      (equivale al SELECT del CategoryController — SQL puro)
-- ------------------------------------------------------------
SELECT
    c.id,
    c.name,
    c.description,
    c.image_url,
    c.created_at,
    c.updated_at,
    COUNT(p.id)         AS products_count,
    COALESCE(SUM(p.stock), 0) AS stock_total
FROM categories c
LEFT JOIN products p ON p.category_id = c.id AND p.deleted_at IS NULL
GROUP BY c.id, c.name, c.description, c.image_url, c.created_at, c.updated_at
ORDER BY c.name ASC;


-- ------------------------------------------------------------
--  C7. Favoritos de un usuario con disponibilidad de stock
-- ------------------------------------------------------------
SELECT
    u.name                          AS usuario,
    p.name                          AS producto_favorito,
    p.price                         AS precio,
    p.stock,
    CASE
        WHEN p.stock = 0     THEN 'Sin stock'
        WHEN p.stock <= 3    THEN 'Últimas unidades'
        ELSE                      'Disponible'
    END                             AS disponibilidad,
    c.name                          AS categoria,
    f.created_at                    AS guardado_el
FROM favorites f
INNER JOIN users u    ON u.id = f.user_id
INNER JOIN products p ON p.id = f.product_id
INNER JOIN categories c ON c.id = p.category_id
WHERE f.user_id = 2
  AND p.deleted_at IS NULL
ORDER BY f.created_at DESC;
