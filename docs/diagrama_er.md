# GeekZone — Diagrama Entidad-Relación

> Renderizable en: GitHub, VS Code (extensión Mermaid), [mermaid.live](https://mermaid.live)

```mermaid
erDiagram
    users {
        BIGINT_UNSIGNED id PK
        VARCHAR(255) name
        VARCHAR(255) surname
        VARCHAR(255) username
        VARCHAR(255) email UK
        TIMESTAMP email_verified_at
        VARCHAR(255) password
        ENUM role
        VARCHAR(100) remember_token
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }

    categories {
        BIGINT_UNSIGNED id PK
        VARCHAR(255) name UK
        TEXT description
        VARCHAR(500) image_url
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }

    products {
        BIGINT_UNSIGNED id PK
        VARCHAR(255) name
        TEXT description
        DECIMAL(10-2) price
        INT_UNSIGNED stock
        TINYINT(1) featured
        VARCHAR(255) image_url
        BIGINT_UNSIGNED category_id FK
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }

    carts {
        BIGINT_UNSIGNED id PK
        BIGINT_UNSIGNED user_id FK
        BIGINT_UNSIGNED product_id FK
        INT_UNSIGNED quantity
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }

    orders {
        BIGINT_UNSIGNED id PK
        BIGINT_UNSIGNED user_id FK
        ENUM status
        DECIMAL(10-2) total
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }

    order_details {
        BIGINT_UNSIGNED id PK
        BIGINT_UNSIGNED order_id FK
        BIGINT_UNSIGNED product_id FK
        INT_UNSIGNED quantity
        DECIMAL(10-2) price
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }

    favorites {
        BIGINT_UNSIGNED id PK
        BIGINT_UNSIGNED user_id FK
        BIGINT_UNSIGNED product_id FK
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }

    %% Relaciones
    users      ||--o{ orders        : "realiza"
    users      ||--o{ carts         : "tiene"
    users      ||--o{ favorites     : "guarda"
    categories ||--o{ products      : "agrupa"
    products   ||--o{ carts         : "añadido a"
    products   ||--o{ favorites     : "marcado en"
    products   ||--o{ order_details : "aparece en"
    orders     ||--o{ order_details : "contiene"
```

## Descripción de relaciones

| Relación | Cardinalidad | Descripción |
|----------|-------------|-------------|
| Category → Product | 1..N | Una categoría agrupa muchos productos |
| User → Order | 1..N | Un usuario puede tener muchos pedidos |
| User → Cart | 1..N | Un usuario puede tener ítems en el carrito (UNIQUE user+product) |
| User → Favorite | 1..N | Un usuario puede tener muchos favoritos (UNIQUE user+product) |
| Order → OrderDetail | 1..N | Un pedido tiene una o más líneas de detalle |
| Product → OrderDetail | 1..N | Un producto puede aparecer en múltiples líneas de pedido |
| Product → Cart | 1..N | Un producto puede estar en el carrito de múltiples usuarios |
| Product → Favorite | 1..N | Un producto puede ser favorito de múltiples usuarios |

## Restricciones clave

- `users.deleted_at` — soft delete
- `products.deleted_at` — soft delete
- `carts` — UNIQUE (user_id, product_id): un usuario no puede añadir el mismo producto dos veces
- `favorites` — UNIQUE (user_id, product_id): un usuario no puede marcar el mismo producto dos veces
- `order_details.price` — precio **congelado** en el momento de la compra (inmutable ante cambios de precio)
- `orders.status` — ENUM: `pendiente | procesando | enviado | entregado | cancelado`
- `users.role` — ENUM: `user | admin`
