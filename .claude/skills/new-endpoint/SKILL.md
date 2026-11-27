---
name: new-endpoint
description: Añadir un endpoint nuevo a la API REST de GeekZone (ruta, controlador con anotaciones Swagger, validación, test, colección Bruno y regeneración de docs). Úsala cuando el usuario pida crear, ampliar o modificar un endpoint de /api.
---

# Nuevo endpoint de la API GeekZone

Sigue estos pasos en orden. No te saltes ninguno: el flujo toca varios sitios y el olvido típico es el test, la anotación Swagger o la colección Bruno.

## Skills de apoyo

- Antes de escribir el controlador, aplica `laravel-best-practices` (en especial sus reglas de validación, seguridad y routing), respetando los patrones ya existentes en este proyecto.
- Si un test falla o el endpoint se comporta de forma inesperada, usa `systematic-debugging` antes de tocar código.
- Si el cambio afecta a una página que consume el endpoint, comprueba el flujo con `webapp-testing` cuando sea viable.

## 0. Decidir antes de escribir

- **Nivel de acceso**: público, JWT (cualquier usuario) o admin. Define en qué grupo de `geekzone/routes/api.php` va:
  - público: fuera de grupos (login/register llevan `throttle`);
  - JWT: grupo `Route::middleware(JwtMiddleware::class)`;
  - admin: grupo `[JwtMiddleware::class, 'admin']`.
- **Nombres en español** para la URL (`/carrito`, `/pedidos`, `/favoritos`…), plural y sin verbos.
- **Estilo de acceso a datos**: Eloquent por defecto. Solo usa `DB::` si el controlador que editas ya lo hace (`CategoryController`, `AdminDashboardController`). Si es raw SQL con DTO, sigue el patrón de `app/DTOs/CategoryDTO.php` (`fromRow`).

## 1. Ruta

Añádela en `geekzone/routes/api.php` dentro del grupo correcto, con el mismo estilo de comentarios (`// ———— Sección ———`).

## 2. Controlador (`geekzone/app/Http/Controllers/Api/`)

- Extiende el `Controller` abstracto de `Api/` (ojo: existen controladores con el mismo nombre fuera de `Api/`; no los confundas).
- Usuario actual: `Auth::id()` / `Auth::user()`.
- Validación con `$request->validate([...], [mensajes en español])`, como en `FavoriteController`.
- Códigos HTTP: 200 lectura/actualización, 201 creación, 401 sin token, 403 sin rol, 404 no existe, 409 conflicto/duplicado, 422 validación.
- Mensajes de respuesta en español. Respuestas con `response()->json([...])` (o los helpers de éxito de `Api/Controller.php` si el controlador ya los usa).
- Un usuario solo debe poder tocar **sus propios** recursos (filtra por `user_id = Auth::id()`).

## 3. Anotaciones Swagger (obligatorio)

Cada método público lleva atributos `OpenApi\Attributes as OA` encima, igual que en `FavoriteController`:

- `OA\Get/Post/Put/Delete` con `path: "/api/..."`, `summary`, `description`, `tags: [...]`.
- `security: [["bearerAuth" => []]]` si requiere JWT.
- `OA\RequestBody` con `required` y `properties` (con `example`) si recibe cuerpo.
- Un `OA\Response` por cada código que el método pueda devolver.

## 4. Test (obligatorio)

Añade tests en `geekzone/tests/Feature/` (en el fichero de la entidad, o uno nuevo con `use RefreshDatabase, CreateBaseData;`).

- Reutiliza el trait `CreateBaseData`: `createCategoriesForTest()`, `createProductsForTest($catId)`, `loginAsUser()`, `loginAsAdmin()` (devuelven los headers con el token).
- Cubre como mínimo: caso correcto, validación fallida (422), sin token (401) y, si es admin, usuario normal (403).
- Nombres: `test_snake_case_descriptivo`.

## 5. Colección Bruno

Crea el `.yml` en la carpeta correspondiente de `geekzone_api_test/` copiando el formato de uno existente (por ejemplo `orders/store.yml`): `info` (name, type: http, `seq` siguiente libre), `http` (method, url `http://localhost:8080/api/...`, `auth: inherit`) y `settings`.

No pegues tokens JWT reales en los ficheros nuevos; usa `auth: inherit` o una variable de entorno.

## 6. Verificar

```bash
docker-compose exec app php artisan test --filter=<nombre_del_test>
docker-compose exec app php artisan test
docker-compose exec app php artisan l5-swagger:generate
docker-compose exec app ./vendor/bin/pint
```

Comprueba que el test nuevo pasa, que no se rompe nada y que el endpoint aparece en `http://localhost:8080/api/documentation`.

## 7. Documentación

Si el endpoint es público de cara al proyecto, añádelo a las tablas de endpoints del `README.md` (Públicos / Protegidos / Administración).

## Checklist final

- [ ] Ruta en el grupo de middleware correcto
- [ ] Validación con mensajes en español
- [ ] Anotaciones Swagger con todos los códigos de respuesta
- [ ] Tests (éxito, 422, 401 y 403 si aplica)
- [ ] Fichero Bruno sin tokens reales
- [ ] `php artisan test` en verde y `l5-swagger:generate` ejecutado
- [ ] README actualizado
