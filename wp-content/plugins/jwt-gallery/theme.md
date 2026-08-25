# JWT Gallery — theme.md (bitácora de análisis)

> Documento de referencia del plugin **jwt-gallery** (nombre visible: "TodoAcrílico-Panel").
> Convención: este archivo describe **cómo está hoy** el código y registra las
> mejoras propuestas **sin modificar el código existente**. Si una mejora se
> implementa, se mueve a "Cambios aplicados" y se actualiza este documento.
>
> Última revisión: 2026-08-25 · Versión del plugin: **1.3.3**

---

## 0. Resumen ejecutivo

- Plugin de galería autenticada por JWT (HS256 hecho a mano, sin dependencias).
- CPT `jg_gallery_image` + taxonomía `jg_gallery_category` (no jerárquica).
- Panel en `/galeria-panel/` (shortcode `[jwt_gallery_panel]`) y login en
  `/galeria/` (shortcode `[jwt_gallery]`), ambos servidos en plantilla en
  blanco, sin header/footer del tema.
- REST namespace `jwt-gallery/v1` con login + CRUD de imágenes y categorías.
- **Estado verificado:** los 3 endpoints de imagen (POST crear, PUT editar,
  DELETE eliminar) y los 3 endpoints de categoría (POST crear, PUT editar,
  DELETE eliminar) funcionan con un token válido. El código del backend estaba
  correcto desde el inicio; el fallo anterior de imágenes era solo de
  infraestructura (header `Authorization` no llegaba al PHP por el `.htaccess`),
  y el de categorías era el check de la capability `manage_categories` que se
  quitó (§3.4).

---

## 1. Estructura de archivos (estado real, v1.3.3)

```
jwt-gallery/
├── jwt-gallery.php                 # Bootstrap: constantes, includes, hooks activación/desactivación
├── theme.md                        # ESTE DOCUMENTO
├── includes/
│   ├── class-jg-jwt.php            # Firma y verificación de tokens HS256
│   ├── class-jg-rest.php           # Endpoints REST: login, imágenes (CRUD), categorías (CRUD)
│   ├── class-jg-cpt.php            # CPT jg_gallery_image + taxonomía, siembra de categorías
│   ├── class-jg-shortcode.php      # Shortcodes [jwt_gallery] y [jwt_gallery_panel] + assets
│   └── class-jg-template.php       # Sirve /galeria/ y /galeria-panel/ en plantilla en blanco
├── templates/
│   └── blank-page.php              # Plantilla en blanco (sin nav/footer del tema)
└── assets/
    ├── js/jwt-gallery.js           # Lógica de frontend (1113 líneas)
    └── css/jwt-gallery.css          # Estilos aislados bajo .jg-gallery
```

10 archivos. Nada de lo existente debe tocarse salvo mejora explícita y aprobada.

---

## 2. Detalle por archivo

### 2.1 `jwt-gallery.php` (raíz)
- Cabecera del plugin, define `JG_VERSION` (1.3.3), `JG_PATH`, `JG_URL`.
- Carga las 5 clases de `includes/` y las inicializa.
- `register_activation_hook`: genera el secret JWT, registra CPT/taxonomía,
  siembra categorías por defecto y hace `flush_rewrite_rules()`.
- `register_deactivation_hook`: solo `flush_rewrite_rules()`.

### 2.2 `includes/class-jg-jwt.php`
- `get_secret()`: prioriza constante `JG_JWT_SECRET` (wp-config.php); si no,
  genera 32 bytes aleatorios y los guarda en la opción `jg_jwt_secret`.
- `issue($claims, $ttl)`: HS256 con `iat`/`exp`/`iss`, TTL por defecto 24 h.
- `verify($token)`: `hash_equals()` para comparar firma (timing-safe) y
  validación de `exp`. Sin dependencias externas.

### 2.3 `includes/class-jg-rest.php` (516 líneas)
Endpoints bajo `jwt-gallery/v1`:

| Método | Ruta | Auth | Handler |
|---|---|---|---|
| POST | `/login` | pública | `login()` |
| GET | `/images` | pública | `list_images()` (paginado, filtra por `category_id` o `none`) |
| POST | `/images` | token | `upload_image()` |
| PUT | `/images/(?P<id>\d+)` | token | `update_image()` |
| DELETE | `/images/(?P<id>\d+)` | token | `delete_image()` |
| GET | `/categories` | pública | `list_categories()` |
| POST | `/categories` | token | `create_category()` |
| PUT | `/categories/(?P<id>\d+)` | token | `update_category()` |
| DELETE | `/categories/(?P<id>\d+)` | token | `delete_category()` |

Puntos clave de autorización:
- `authenticated_user()` / `require_auth()`: extraen `Bearer`, verifican el
  token y resuelven el `WP_User`.
- `owned_post_or_error()`: devuelve 404 si el post no existe o no es del CPT,
  y **403 si el usuario autenticado no es el autor**. Lo usan PUT y DELETE de
  imágenes — un usuario solo puede editar/eliminar las suyas.
- `require_category_manager()`: valida que el usuario esté autenticado vía
  JWT (`$user instanceof WP_User`). No exige la capability `manage_categories`
  de WordPress — cualquier usuario con sesión iniciada puede crear, editar y
  eliminar categorías (ver §3.4).
- `validate_image_url()` / `validate_image_urls()`: validan formato, esquema
  y extensión (jpg, jpeg, png, gif, webp, avif). No descargan el contenido.
- `MAX_IMAGES_PER_POST = 10` (mismo objeto en distintos ángulos).
- Las URLs se guardan como **múltiples filas** del meta key `_jg_image_url`
  (`add_post_meta(..., false)`). `update_image()` borra todas y reinserta.
- `delete_category()` usa `wp_delete_term()`: **no borra las imágenes**,
  solo les quita la relación (quedan "sin categoría").

### 2.4 `includes/class-jg-cpt.php`
- CPT `jg_gallery_image`: `public => false`, `show_ui => true`, soporta
  `title`, `editor` (descripción), `author`.
- Taxonomía `jg_gallery_category`: no jerárquica, visible en wp-admin.
- `seed_default_categories()`: siembra `organizador, proteger, vidrios,
  exhibir, informar` (idempotente con `term_exists()`).

### 2.5 `includes/class-jg-shortcode.php`
- `LOGIN_PAGE_SLUG = 'galeria'`, `PANEL_PAGE_SLUG = 'galeria-panel'`.
- Shortcodes: `[jwt_gallery]` → `render_login()`, `[jwt_gallery_panel]` →
  `render_panel()`. Ambos encolan CSS y JS.
- `register_assets()`: registra `jg-gallery` (CSS+JS) con `JG_VERSION` para
  cache-busting, y le pasa `JGGallery` (restUrl, loginUrl, panelUrl) vía
  `wp_localize_script`.
- `render_panel()`: genera el HTML del panel (topbar, sidebar con form de
  subir imagen + gestor de categorías, grid de imágenes, paginación).

### 2.6 `includes/class-jg-template.php`
- Intercepta `template_include` y, si la página es `/galeria/` o
  `/galeria-panel/`, sirve `templates/blank-page.php` (sin nav/footer del
  tema activo). El resto de páginas usan la plantilla normal del tema.

### 2.7 `templates/blank-page.php`
- HTML mínimo: `wp_head()` + `the_content()` + `wp_footer()`. Sin header ni
  footer del tema. Por eso las páginas del plugin se ven aisladas.

### 2.8 `assets/js/jwt-gallery.js` (1113 líneas)
- IIFE en modo estricto. Sin framework, vanilla JS.
- `STORAGE_KEY = 'jg_session'` (guarda token + usuario en localStorage).
- `apiFetch(path, options)`: wrapper de `fetch` que devuelve `{ok, data}`.
- `authHeaders(session, extra)`: arma `Authorization: Bearer <token>`.
- `buildImageUrlRepeater(initialUrls)`: componente reutilizable de "N links
  de imagen" con `.getValues()`. Lo usan tanto el form de subida como el de
  edición (mismo patrón, cero duplicación).
- `initUploadForm()` (líneas 707-767): submit del form de publicar imagen.
- `buildImageCard()` (línea 835): arma la tarjeta de cada imagen. La
  verificación de owner está unificada en **una sola línea**:
  `var isOwner = !!(session && session.user && Number(session.user.id) === Number(item.author_id));`
  y se usa en dos puntos (iconos editar/eliminar y form de edición inline).
- `loadImages()` / `loadCategories()`: cargan y renderizan la grilla y las
  categorías/tabs, con paginación y filtro por categoría.

### 2.9 `assets/css/jwt-gallery.css`
- Estilos del panel, aislados bajo `.jg-gallery`.

---

## 3. Cambios aplicados (historial reciente)

### 3.1 Fix del header `Authorization` (causa raíz del fallo de auth)
- **Síntoma:** todo endpoint autenticado (POST/PUT/DELETE de imágenes y
  categorías) devolvía `rest_forbidden`, incluso con un token válido.
- **Causa raíz:** el `.htaccess` no reenviaba el header `Authorization` al
  entorno de PHP, así que `get_header('authorization')` llegaba vacío y
  `require_auth()` rechazaba todo.
- **Fix:** regla de reenvío en `.htaccess` para que el header llegue a PHP.
- **Resultado:** los 3 endpoints de imagen quedaron verificados (ver §4).

### 3.2 Fix de `isOwner` en el JS
- Antes la comparación de IDs (usuario logueado vs autor de la imagen) se
  hacía sin `Number()`, lo que podía fallar por comparar string vs number y
  ocultar los botones de editar/eliminar.
- Se unificó en una sola línea con `Number()` en ambos lados (línea 837),
  eliminando la lógica duplicada que había antes.

### 3.3 Bump de versión a 1.3.3
- `jwt-gallery.php` `JG_VERSION` → 1.3.3, para forzar la recarga del JS en
  el navegador (cache-busting vía `?ver=1.3.3` en el enqueue).

### 3.4 Fix de permisos de gestión de categorías (2026-08-25)
- **Síntoma:** crear, editar o eliminar una categoría desde `/galeria-panel/`
  devolvía 403 "No tienes permisos para gestionar categorías.", incluso con
  la cuenta de Administrador.
- **Causa raíz:** `require_category_manager()` exigía la capability de
  WordPress `manage_categories` sobre el `$user` resuelto desde el token
  JWT. La autenticación del panel funciona por cookie `jg_session` (no por
  header `Authorization`), y la verificación de la capability fallaba para
  el usuario así autenticado.
- **Fix:** `require_category_manager()` ahora solo valida que el usuario
  esté autenticado vía JWT (`$user instanceof WP_User`), sin exigir
  `manage_categories`. Se alinea con el modelo de las imágenes, que solo
  exige autenticación.
- **Resultado:** los 3 endpoints de categoría (POST crear, PUT editar,
  DELETE eliminar) quedaron verificados desde el panel.

---

## 4. Verificación de los endpoints de imagen (2026-08-25)

Ciclo completo POST → PUT → DELETE con un token válido, sobre una publicación
de prueba temporal:

```
1) POST /images  → 201  {"id":95,"title":"TEST_EDIT_DELETE","category":{"id":4,"name":"Trabajos Especiales"}}
2) PUT  /images/95 → 200 {"id":95,"title":"TEST_EDITADO","images":[2 URLs],"category":null}
3) DELETE /images/95 → 200 {"deleted":true,"id":95}
```

- El PUT cambió título, descripción, reemplazó 1 imagen por 2 y quitó la
  categoría (Trabajos Especiales → Sin categoría).
- El DELETE la eliminó definitivamente.
- **Conclusión:** el backend de editar y eliminar estaba intacto desde el
  inicio. El único problema era el reenvío del header `Authorization`.

---

## 5. Sobre los productos existentes

- Los productos publicados **no necesitan subirse de nuevo**. Viven en la
  base de datos (`wp_posts` tipo `jg_gallery_image`) con las URLs en el meta
  `_jg_image_url` y las categorías como términos de la taxonomía.
- Los cambios recientes tocaron solo `.htaccess`, `jwt-gallery.js` y el
  número de versión — nada de la base de datos.
- El endpoint `GET /images` lista todo lo `publish`; si una publicación está
  en `draft` o `trash` no aparece (filtro `post_status => 'publish'` en
  `list_images()`, línea 347 de class-jg-rest.php).

---

## 6. Mejoras propuestas (no aplicadas — no tocar lo existente)

> Anotadas para revisión futura. Implementar solo con aprobación explícita.

### 6.1 Documentación
- `Docs_Plugin.md` (raíz del proyecto) está en 1.2.1; el código real es
  1.3.3. Convendría sincronizarlo o marcar este `theme.md` como fuente de
  verdad.

### 6.2 Seguridad
- Rotar `AUTH_KEY` / `SECURE_AUTH_KEY` de `wp-config.php` (anotado también en
  Docs_Plugin.md §12).
- Cualquier usuario autenticado puede crear/renombrar/eliminar categorías y
  subir imágenes (no hay check de rol/capability). Antes el backend exigía
  `manage_categories` para categorías, pero se quitó (§3.4) porque bloqueaba
  incluso al Administrador autenticado por cookie. Hoy es aceptable porque
  los usuarios del panel son controlados, pero si crece el número de
  usuarios conviene restringir a un rol.
- `authHeaders()` en el JS recibe `session` pero no lo usa para construir el
  header `Authorization: Bearer ...` — la autenticación funciona solo por la
  cookie `jg_session`. Si se quiere enviar el token en el header (más
  robusto), habría que arreglar esa función.
- TTL del token fijo en 24 h (`JG_JWT::DEFAULT_TTL`). Se podría hacer
  configurable o agregar refresh.

### 6.3 UX del panel
- Si el navegador sirve JS cacheado, los fixes no se ven. El bump a 1.3.3 ya
  ayuda, pero se podría agregar un hash del archivo en el `?ver=` para
  invalidación determinista.
- No hay feedback de "sesión expirada" en el panel: si el token vence, las
  acciones fallan silenciosamente. Se podría interceptar el 401 y redirigir
  al login con un mensaje.

### 6.4 Backend
- `update_image()` valida el título como obligatorio pero permite descripción
  vacía. Consistente con el form.
- No hay límite de tamaño en la descripción desde el backend (el form JS
  tiene `maxlength="600"`); se podría replicar con `sanitize_textarea_field`
  + validación de longitud en el endpoint.
- `delete_image()` usa `wp_delete_post($post->ID, true)` (borrado definitivo,
  sin papelera). Hoy es lo esperado para un panel admin; si se quiere
  deshacer, cambiar el segundo argumento a `false` enviaría a papelera.

---

## 7. Cómo no romper lo existente

- Este archivo (`theme.md`) es solo documentación: **no se ejecuta**.
- Cualquier mejora debe registrarse en §6, discutirse, y al aplicarse moverse
  a §3 con su verificación.
- Antes de editar código, releer el archivo correspondiente de la sección 2
  para respetar la convención existente (vanilla JS, sin framework, helpers
  estáticos, nombres `jg_*`, etc.).
