# GeekZone — Diagrama UML de Clases

> Renderizable en: GitHub, VS Code (extensión Mermaid), [mermaid.live](https://mermaid.live)

```mermaid
classDiagram
    direction TB

    %% ───────────────────────────────────────────
    %% MODELOS (capa de dominio)
    %% ───────────────────────────────────────────

    class User {
        +int id
        +string name
        +string surname
        +string username
        +string email
        +string password
        +string role
        +datetime email_verified_at
        +datetime deleted_at
        +getJWTIdentifier() mixed
        +getJWTCustomClaims() array
        +esAdmin() bool
        +orders() HasMany
        +carts() HasMany
        +favorites() HasMany
    }

    class Category {
        +int id
        +string name
        +string description
        +string image_url
        +imageUrl() Attribute
        +products() HasMany
    }

    class Product {
        +int id
        +string name
        +string description
        +decimal price
        +int stock
        +bool featured
        +string image_url
        +int category_id
        +datetime deleted_at
        +imageUrl() Attribute
        +category() BelongsTo
        +favorites() HasMany
    }

    class Cart {
        +int id
        +int user_id
        +int product_id
        +int quantity
        +user() BelongsTo
        +product() BelongsTo
    }

    class Order {
        +int id
        +int user_id
        +string status
        +decimal total
        +user() BelongsTo
        +details() HasMany
    }

    class OrderDetail {
        +int id
        +int order_id
        +int product_id
        +int quantity
        +decimal price
        +order() BelongsTo
        +product() BelongsTo
    }

    class Favorite {
        +int id
        +int user_id
        +int product_id
        +user() BelongsTo
        +product() BelongsTo
    }

    %% ───────────────────────────────────────────
    %% DTO
    %% ───────────────────────────────────────────

    class CategoryDTO {
        +int id
        +string name
        +string description
        +string image_url
        +string created_at
        +string updated_at
        +int products_count
        +fromRow(object row)$ CategoryDTO
        +toArray() array
    }

    %% ───────────────────────────────────────────
    %% CONTROLADORES API
    %% ───────────────────────────────────────────

    class Controller {
        #successResponse(data, message, status) JsonResponse
        #errorResponse(message, status, errors) JsonResponse
        #validationErrorResponse(errors) JsonResponse
    }

    class AuthController {
        +register(Request) JsonResponse
        +login(Request) JsonResponse
        +logout(Request) JsonResponse
        +me(Request) JsonResponse
    }

    class CategoryController {
        +index() JsonResponse
        +store(Request) JsonResponse
        +update(Request, int id) JsonResponse
        +destroy(int id) JsonResponse
    }

    class ProductController {
        +index(Request) JsonResponse
        +show(int id) JsonResponse
        +store(Request) JsonResponse
        +update(Request, int id) JsonResponse
        +destroy(int id) JsonResponse
    }

    class CartController {
        +index() JsonResponse
        +store(Request) JsonResponse
        +update(Request, int id) JsonResponse
        +destroy(int id) JsonResponse
    }

    class OrderController {
        +index() JsonResponse
        +store(Request) JsonResponse
    }

    class FavoriteController {
        +index() JsonResponse
        +store(Request) JsonResponse
        +destroy(int productId) JsonResponse
    }

    class ProfileController {
        +show() JsonResponse
        +update(Request) JsonResponse
    }

    class ImageController {
        +store(Request) JsonResponse
    }

    %% ───────────────────────────────────────────
    %% MIDDLEWARE
    %% ───────────────────────────────────────────

    class JwtMiddleware {
        +handle(Request, Closure next) Response
    }

    class CheckAdminRole {
        +handle(Request, Closure next) Response
    }

    %% ───────────────────────────────────────────
    %% RELACIONES DE HERENCIA
    %% ───────────────────────────────────────────

    Controller <|-- AuthController
    Controller <|-- CategoryController
    Controller <|-- ProductController
    Controller <|-- CartController
    Controller <|-- OrderController
    Controller <|-- FavoriteController
    Controller <|-- ProfileController
    Controller <|-- ImageController

    %% ───────────────────────────────────────────
    %% RELACIONES DE DOMINIO (Modelos)
    %% ───────────────────────────────────────────

    Category "1" --> "0..*" Product : tiene
    User "1" --> "0..*" Order : realiza
    User "1" --> "0..*" Cart : tiene
    User "1" --> "0..*" Favorite : guarda
    Order "1" --> "1..*" OrderDetail : contiene
    Product "1" --> "0..*" OrderDetail : referenciado en
    Product "1" --> "0..*" Cart : añadido a
    Product "1" --> "0..*" Favorite : marcado en

    %% ───────────────────────────────────────────
    %% USO DE DTO
    %% ───────────────────────────────────────────

    CategoryController ..> CategoryDTO : usa
    CategoryDTO ..> Category : mapea

    %% ───────────────────────────────────────────
    %% USO DE MIDDLEWARE
    %% ───────────────────────────────────────────

    JwtMiddleware ..> AuthController : protege
    CheckAdminRole ..> CategoryController : protege (admin)
    CheckAdminRole ..> ProductController : protege (admin)
```

## Descripción de capas

### Modelos (`app/Models`)
Representan las entidades de la base de datos usando Eloquent ORM. `User` implementa `JWTSubject` para la autenticación stateless. `Product` y `User` usan `SoftDeletes` (campo `deleted_at`).

### DTO (`app/DTOs`)
`CategoryDTO` mapea los resultados crudos de `DB::select()` (stdClass) a un objeto PHP tipado. El `CategoryController` usa SQL puro en lugar de Eloquent, por lo que el DTO actúa como capa de transformación.

### Controladores API (`app/Http/Controllers/Api`)
Todos extienden `Controller` base que centraliza los helpers de respuesta JSON (`successResponse`, `errorResponse`, `validationErrorResponse`).

### Middleware (`app/Http/Middleware`)
- `JwtMiddleware`: valida el token JWT en cada request protegido.
- `CheckAdminRole`: verifica que el usuario autenticado tenga `role = admin`.
