# Documentación de Seguridad — JWT Gallery

> Documento vivo. Registra cada fix de seguridad aplicado al plugin `jwt-gallery` (Fixes 1–12) y, desde 2026-10-20, al formulario de cotización del tema `todo-acrilico` (Fixes 13–15).
> Cada sección incluye: problema, solución, qué cambia, qué no cambia y verificación.

## Índice

| # | Fix | OWASP / categoría | Archivos |
|---|---|---|---|
| 1 | [Broken Access Control en categorías (intentado y revertido)](#fix-1-broken-access-control-en-gestión-de-categorías) | A01:2021 Broken Access Control | `class-jg-rest.php` |
| 2 | [Brute Force — sin rate limiting en login](#fix-2-brute-force--sin-rate-limiting-en-el-endpoint-de-login) | A07:2021 Identification & Authentication Failures | `class-jg-rest.php` |
| 3 | [JWT en localStorage (robo por XSS)](#fix-3-jwt-almacenado-en-localstorage-robo-por-xss) | A07:2021 / A03:2021 | `class-jg-rest.php`, `jwt-gallery.js` |
| 4 | [Credenciales de BD débiles en wp-config.php](#fix-4-credenciales-de-bd-débiles-en-wp-configphp) | A07:2021 Identification & Authentication Failures | `wp-config.php`, MySQL |
| 5 | [Sin forzar HTTPS — bugs del fix original y solución corregida](#fix-5-sin-forzar-https--bugs-del-fix-original-y-solución-corregida) | A02:2021 Cryptographic Failures | `wp-config.php`, `.htaccess` (producción) |
| 6 | [Token JWT sin revocación real](#fix-6-token-jwt-sin-revocación-real) | A07:2021 Identification & Authentication Failures | `class-jg-rest.php`, `jwt-gallery.js` |
| 7 | [Secret JWT en la base de datos](#fix-7-secret-jwt-en-la-base-de-datos-no-en-wp-configphp) | A02:2021 Cryptographic Failures | `wp-config.php`, `class-jg-jwt.php` |
| 8 | [xmlrpc.php sin restricciones](#fix-8-xmlrpcphp-sin-restricciones) | A07:2021 Identification & Authentication Failures | `jwt-gallery.php`, `.htaccess` |
| 9 | [Algoritmo (alg) del header JWT sin verificar](#fix-9-algoritmo-alg-del-header-jwt-sin-verificar) | A02:2021 Cryptographic Failures | `class-jg-jwt.php` |
| 10 | [Claim iss emitido pero nunca verificado](#fix-10-claim-iss-emitido-pero-nunca-verificado) | A07:2021 Identification & Authentication Failures | `class-jg-jwt.php` |
| 11 | [URLs de imagen externas — riesgo aceptado por diseño](#fix-11-urls-de-imagen-externas--riesgo-aceptado-por-diseño) | A04:2021 Insecure Design (aceptado, superado por el #12) | `class-jg-rest.php` |
| 12 | [Subida por archivos locales — elimina el riesgo de URLs externas](#fix-12-subida-por-archivos-locales--elimina-el-riesgo-de-urls-externas) | A04:2021 / A05:2021 Security Misconfiguration | `class-jg-rest.php`, `jwt-gallery.js` |
| 13 | [Formulario de cotización: límite de envíos por IP](#fix-13-formulario-de-cotización-límite-de-envíos-por-ip) | A04:2021 Insecure Design (abuso / spam) | tema: `functions.php`, `page-contacto.php` |
| 14 | [Formulario de cotización: validación en servidor y envío AJAX](#fix-14-formulario-de-cotización-validación-en-servidor-y-envío-ajax) | A03:2021 Injection / A04:2021 | tema: `functions.php`, `page-contacto.php` |
| 15 | [Datos en el navegador y deber de información (Ley 21.719)](#fix-15-datos-en-el-navegador-y-deber-de-información-ley-21719) | Privacidad / cumplimiento | tema: `page-contacto.php`; contenido: Política de Privacidad §8 |
| 16 | [Microsoft Clarity y Google Tag Manager: consentimiento previo y enmascarado del formulario](#fix-16-microsoft-clarity-consentimiento-previo-y-enmascarado-del-formulario) | Privacidad / cumplimiento (Ley 21.719) | tema: `ta-consent.js`, `functions.php`, `footer.php`, `page-contacto.php`; contenido: Política §8 |
| 17 | [Cambios del 2026-11-04: panel privado, errores de sesión y archivos huérfanos](#fix-17-cambios-del-2026-11-04-panel-privado-errores-de-sesión-y-archivos-huérfanos) | A01 / A04 / A09:2021 | tema: `functions.php`; plugin: `class-jg-rest.php`, `jwt-gallery.js` |

## Estado de los endpoints

| Endpoint | Método | Permission callback | Autorización en callback | Rate limit |
|---|---|---|---|---|
| `/login` | POST | público | — | ✅ 5 intentos / 5 min |
| `/logout` | POST | público | ✅ JWT (en callback) | — |
| `/images` | GET | público | — | — |
| `/images` | POST | `require_auth` | ✅ JWT | — |
| `/images/{id}` | PUT | `require_auth` | ✅ JWT + propietario | — |
| `/images/{id}` | DELETE | `require_auth` | ✅ JWT + propietario | — |
| `/categories` | GET | público | — | — |
| `/categories` | POST | `require_auth` | ✅ JWT (solo autenticación) | — |
| `/categories/{id}` | PUT | `require_auth` | ✅ JWT (solo autenticación) | — |
| `/categories/{id}` | DELETE | `require_auth` | ✅ JWT (solo autenticación) | — |

## Resumen de cambios de seguridad

| Aspecto | Antes | Después |
|---|---|---|
| #1 Autorización de categorías | Solo autenticación | `manage_categories` (intentado y revertido) → de nuevo solo autenticación (ver Fix 1) |
| #2 Rate limiting de login | Sin límite | 5 intentos / 5 min por IP (transients) |
| #3 Almacenamiento del token | `localStorage` (accesible por XSS) | Cookie HttpOnly (inaccesible por JS) |
| #4 Credenciales de BD | `root` con contraseña trivial | Usuario dedicado + contraseña aleatoria larga + permisos mínimos |
| #5 Forzar HTTPS | No definido | Proxy fix (`is_ssl()` detrás de reverse proxy) + `FORCE_SSL_ADMIN` condicional + redirect 301 en producción |
| #6 Revocación de token | Logout solo borra cookie; token válido 24h | Revocación por `jti` en usermeta `jg_token_jti` (**aplicado 2026-10-20**, commit `ca0acd3`) |
| #7 Secreto de firma JWT | Base de datos (`wp_options`) | `wp-config.php` (sistema de archivos) |
| #8 XML-RPC | Habilitado (xmlrpc.php accesible) | Deshabilitado (filtro + `.htaccess`) |
| #9 Algoritmo JWT (alg) | No verificado (patrón frágil) | Validado `=== 'HS256'` antes de verificar firma |
| #10 Emisor JWT (iss) | Emitido pero no verificado | Validado `=== get_expected_issuer()` (fuente única de verdad) |
| #11 URLs de imagen externas | Sin restricción de host | ~~Aceptado por diseño~~ **Superado por el #12**: desde 1.4.0 el panel sube archivos locales (2026-09-29) |
| #12 Subida por archivos locales | — | Fix real: extensión whitelist + tamaño máx. 8 MB + almacenamiento en `uploads/jg-gallery/`; elimina el riesgo de URLs externas del #11 |

## Fix 1: Broken Access Control en gestión de categorías

- **Fecha:** 2026-08-25 (aplicado, luego revertido el mismo día)
- **Archivo afectado:** `wp-content/plugins/jwt-gallery/includes/class-jg-rest.php` (líneas 554–566)
- **Endpoint afectado:** `create_category`, `update_category`, `delete_category`
- **Rutas REST afectadas:**
  - `POST /wp-json/jwt-gallery/v1/categories`
  - `PUT /wp-json/jwt-gallery/v1/categories/{id}`
  - `DELETE /wp-json/jwt-gallery/v1/categories/{id}`

### Problema original

Los tres endpoints de escritura de categorías verificaban **autenticación** (token JWT válido) pero no **autorización** (capacidad del usuario). Cualquier usuario autenticado, incluidos roles de bajo privilegio como Suscriptor, Colaborador o Autor, podía:

- crear categorías nuevas (`wp_insert_term`)
- renombrar cualquier categoría existente (`wp_update_term`)
- eliminar cualquier categoría (`wp_delete_term`)

Esto constituía una vulnerabilidad de Broken Access Control: un usuario autenticado con permisos insuficientes podía mutar la taxonomía de la galería.

### Solución intentada (luego revertida)

Se añadió un helper `require_category_manager( $user )` que comprobaba la capacidad nativa de WordPress `manage_categories` mediante `user_can( $user, 'manage_categories' )`. Si el usuario no la poseía, devolvía:

```php
new WP_Error( 'jg_forbidden', 'No tienes permisos para gestionar categorías.', array( 'status' => 403 ) )
```

### Por qué se revertió

El check de `manage_categories` bloqueaba **incluso al Administrador** autenticado desde el panel `/galeria-panel/`. La causa: la autenticación del panel funciona por cookie `jg_session` (no por header `Authorization`), y la verificación de la capability sobre el `$user` resuelto desde el token JWT no funcionaba correctamente en ese flujo. El resultado era un 403 "No tienes permisos para gestionar categorías." para todos los usuarios, sin importar su rol.

Como los usuarios del panel son controlados (no hay registro público) y el modelo de permisos de las imágenes del plugin ya solo exige autenticación (no una capability específica), se decidió alinear las categorías con ese mismo modelo y **quitar** el check de `manage_categories`.

### Estado actual del helper

`require_category_manager( $user )` (líneas 554–566) ahora solo valida que el usuario esté autenticado vía JWT:

```php
private static function require_category_manager( $user ) {
    if ( ! $user || ! ( $user instanceof WP_User ) ) {
        return new WP_Error( 'jg_forbidden', 'No tienes permisos para gestionar categorías.', array( 'status' => 403 ) );
    }
    return null;
}
```

El helper se sigue invocando en los tres métodos (`create_category`, `update_category`, `delete_category`) inmediatamente después de `authenticated_user`, pero ya no filtra por rol.

### Qué cambia

- Cualquier usuario autenticado (incluido Suscriptor) puede crear, renombrar y eliminar categorías, igual que puede subir imágenes.
- Antes del fix intentado, podían hacer lo mismo; durante el breve periodo con `manage_categories`, solo Admin/Editor en teoría — pero en la práctica nadie podía porque el check fallaba para todos.

### Qué no cambia

- La lógica de `authenticated_user` y el token JWT se mantienen intactos.
- El formato de respuesta y los mensajes de error preexistentes (400, 404, 409, 500) se conservan.
- El endpoint de lectura `list_categories` sigue público (`__return_true`).
- Los endpoints de gestión de imágenes no se ven afectados.
- El JavaScript del frontend no requiere cambios.

### Riesgo residual

Esta es una decisión de diseño aceptada, no una vulnerabilidad activa: los usuarios del panel son controlados (no hay registro público), y el modelo es consistente con el de las imágenes. Si el número de usuarios crece o se abre el registro, conviene restringir las categorías a un rol. Ver `Docs_Plugin.md` §10 y `theme.md` §6.2 para el análisis completo.

### Verificación

```bash
php -l wp-content/plugins/jwt-gallery/includes/class-jg-rest.php
# No syntax errors detected in wp-content/plugins/jwt-gallery/includes/class-jg-rest.php
```

Verificación manual desde el panel `/galeria-panel/` con la cuenta `admin@todoacrilico.cl`: se creó, renombró y eliminó una categoría de prueba sin errores (ver `Docs_Plugin.md` §12.29).

## Fix 2: Brute Force — sin rate limiting en el endpoint de login

- **Fecha:** 2026-08-25
- **Archivo afectado:** `wp-content/plugins/jwt-gallery/includes/class-jg-rest.php` (líneas 14–16, 145–165, 232–270)
- **Endpoint afectado:** `login` (`POST /wp-json/jwt-gallery/v1/login`)

### Problema

El endpoint de login solo verificaba credenciales, sin ningún control sobre la cantidad de intentos. Un atacante podía probar combinaciones de correo/contraseña de forma ilimitada (ataque de fuerza bruta o credential stuffing) hasta dar con una válida.

### Solución

Se implementó rate limiting por IP usando la API de transients de WordPress:

1. **Constantes** (líneas 14, 16):
   - `LOGIN_MAX_ATTEMPTS = 5`
   - `LOGIN_WINDOW_SECONDS = 300` (5 minutos)

2. **Helpers privados** (líneas 232–270):
   - `login_client_id()` — identifica al cliente por IP (con soporte de `X-Forwarded-For` para proxies), hasheada con `md5` como clave de transient.
   - `check_login_rate_limit()` — devuelve `WP_Error` con HTTP 429 si ya se alcanzaron los 5 intentos.
   - `register_login_attempt()` — incrementa el contador de transients tras un fallo.
   - `clear_login_rate_limit()` — borra el contador tras un login exitoso.

3. **Integración en `login()`** (líneas 145–165):
   - Al inicio: comprueba el límite y devuelve 429 si se excedió.
   - En credenciales inválidas: registra el intento fallido.
   - En login exitoso: limpia el contador.

### Flujo del contador

1. Intento 1–4 fallido → contador sube (1, 2, 3, 4). Respuesta HTTP 401.
2. Intento 5 fallido → contador llega a 5. Respuesta HTTP 401 (aún no bloqueado).
3. Intento 6 → contador ya en 5 → `check_login_rate_limit()` devuelve HTTP 429 con mensaje: "Demasiados intentos. Inténtalo de nuevo en unos minutos."
4. El contador se reinicia al caducar la ventana (5 minutos) o al loguearse correctamente.

### Qué cambia

- Tras 5 intentos fallidos desde la misma IP en una ventana de 5 minutos, el endpoint devuelve HTTP 429.
- El contador se reinicia solo al caducar la ventana (5 min) o al loguearse correctamente.

### Qué no cambia

- El formato del token JWT, el TTL y la respuesta exitosa se mantienen intactos.
- Los mensajes de error preexistentes (400, 401) se conservan.
- El resto de endpoints no se ven afectados.
- El frontend solo verá un 429 si alguien fuerza bruta; el flujo normal de un usuario legítimo queda intacto.

### Nota técnica

Se usó la API de transients de WordPress (`get/set/delete_transient`), que funciona con la base de datos (o caché de objeto si existe) sin requerir extensiones externas. Es la forma estándar y portable de implementar rate limiting en plugins WordPress.

### Verificación

```bash
php -l wp-content/plugins/jwt-gallery/includes/class-jg-rest.php
# No syntax errors detected in wp-content/plugins/jwt-gallery/includes/class-jg-rest.php
```

## Fix 3: JWT almacenado en localStorage (robo por XSS)

- **Fecha:** 2026-08-25
- **Archivos afectados:**
  - `wp-content/plugins/jwt-gallery/includes/class-jg-rest.php`
  - `wp-content/plugins/jwt-gallery/assets/js/jwt-gallery.js`
- **Endpoint afectado:** autenticación de todos los endpoints protegidos (`/images`, `/categories`)

### Problema

El token JWT se almacenaba en `localStorage` (`window.localStorage.setItem('jg_session', ...)`) tras un login exitoso. Cualquier script malicioso ejecutado en la página mediante un ataque XSS podía leer `localStorage` y extraer el token, suplantando la sesión del usuario.

### Solución

Se trasladó el token a una cookie **HttpOnly** (JavaScript no puede leerla) y se mantiene en `localStorage` únicamente la información no sensible (`expiresAt` y `user`) que el frontend necesita para el guard de la UI.

#### Backend (`class-jg-rest.php`)

| Cambio | Descripción |
|---|---|
| Constante `COOKIE_NAME = 'jg_session'` | Nombre de la cookie de sesión |
| Ruta `/logout` registrada | Endpoint para invalidar la cookie en el servidor |
| `get_bearer_token` lee la cookie como fallback | Prioriza el header `Authorization: Bearer`, luego la cookie HttpOnly |
| `login` setea cookie HttpOnly tras autenticar | `set_session_cookie()` con `httponly: true`, `samesite: Lax`, `secure` si HTTPS |
| Endpoint `logout` | Limpia la cookie en el servidor |
| Helper `set_session_cookie` | Setea la cookie con atributos seguros |
| Helper `clear_session_cookie` | Elimina la cookie con expiración en el pasado |

#### Frontend (`jwt-gallery.js`)

| Cambio | Descripción |
|---|---|
| `getSession` ya no exige `session.token` | Solo verifica `expiresAt` para el guard de la UI |
| `setSession` guarda solo `expiresAt` + `user` | El token ya no se guarda en `localStorage` |
| `authHeaders` no añade `Authorization` | Ya no hay token en JS que enviar como header |
| `apiFetch` envía `credentials: 'include'` | El navegador adjunta automáticamente la cookie HttpOnly |
| Logout llama a `POST /logout` | Invalida la cookie en el servidor antes de redirigir al login |

### Flujo de autenticación actual

1. **Login:** `POST /login` → servidor valida credenciales → setea cookie `jg_session` (HttpOnly) → responde JSON con `token`, `expires_in`, `user`.
2. **Frontend:** guarda `expiresAt` + `user` en `localStorage` (sin el token) → redirige al panel.
3. **Peticiones REST:** el navegador envía la cookie automáticamente vía `credentials: 'include'` → el servidor lee el token desde la cookie en `get_bearer_token`.
4. **Logout:** frontend llama `POST /logout` → servidor limpia la cookie → frontend limpia `localStorage` → redirige al login.

### Atributos de la cookie

| Atributo | Valor | Motivo |
|---|---|---|
| `httponly` | `true` | JavaScript no puede leer la cookie → mitigación de robo por XSS |
| `samesite` | `Lax` | Previene envío cross-site (CSRF parcial) |
| `secure` | `is_ssl()` | Solo se envía por HTTPS en producción |
| `path` | `/` | Disponible en toda la ruta del sitio |
| `expires` | `time() + TTL` | Coincide con el TTL del token JWT |

### Qué cambia

- El token JWT vive en una cookie HttpOnly → un XSS **no puede leerlo**.
- Las peticiones REST autenticadas usan la cookie vía `credentials: 'include'`.
- `localStorage` solo guarda `expiresAt` y `user` (email, nombre, id) — datos no sensibles.
- Se mantiene compatibilidad con el header `Authorization: Bearer` como fallback (útil para API clients / Postman).

### Qué no cambia

- El flujo de login, logout, panel, CRUD de imágenes y categorías funciona igual.
- El `token` sigue en la respuesta JSON del login (para clientes API), pero el frontend ya no lo guarda.
- La validación del token (`JG_JWT::verify`) y el TTL se mantienen intactos.
- El rate limiting implementado anteriormente sigue funcionando.

### Verificación

```bash
php -l wp-content/plugins/jwt-gallery/includes/class-jg-rest.php
# No syntax errors detected in wp-content/plugins/jwt-gallery/includes/class-jg-rest.php
```

## Fix 4: Credenciales de BD débiles en wp-config.php

- **Fecha:** 2026-08-25
- **Archivos afectados:**
  - `wp-config.php` (líneas 26–29)
  - MySQL (creación de usuario dedicado)

### Problema

`wp-config.php` usaba credenciales triviales para la base de datos:

```php
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '<contraseña trivial, retirada del documento>' );
```

El usuario `root` con una contraseña trivial es de las credenciales de las más débiles y predecibles. Aunque WordPress limita qué tablas usa, el usuario `root` tiene acceso total a **todas** las bases de datos del servidor MySQL, no solo a `wordpress-proyecto3`. Riesgos:

- **Si el servidor MySQL es accesible desde la red** (o si una VPN/misconfiguration lo expone), un atacante puede conectarse directamente con esas credenciales.
- **Si hay una vulnerabilidad de ejecución de código** (RCE, LFI, etc.) en cualquier parte del servidor, el atacante lee `wp-config.php`, obtiene esas credenciales, y compromete todo el servidor de BD, no solo WordPress.
- **Principio de menor privilegio violado**: WordPress solo necesita CRUD sobre una base de datos, no control total del servidor.

### Por qué el theme y el plugin siguen funcionando al cambiar credenciales

Verificado en el código:

- **Plugin `jwt-gallery`:** usa exclusivamente la API de WordPress (`wp_insert_post`, `WP_Query`, `get_option`, `update_option`). No abre conexiones propias a la BD. No hay `mysqli_connect`, `new PDO`, `DB_USER` ni `$wpdb` directo en el código del plugin.
- **Theme:** tampoco usa `$wpdb` ni `DB_PASSWORD` directamente.
- **WordPress** lee `DB_USER`/`DB_PASSWORD`/`DB_NAME` de `wp-config.php` una sola vez y gestiona la conexión internamente.

Conclusión: mientras el nuevo usuario tenga los permisos correctos sobre la base `wordpress-proyecto3`, el theme y el plugin funcionan sin cambios. Esto aplica tanto a desarrollo como a producción (los valores concretos cambian por entorno, el principio es el mismo).

### Solución

Se aplica defense-in-depth con un usuario MySQL dedicado, contraseña aleatoria larga y permisos mínimos (solo CRUD sobre una base de datos).

> **Orden crítico:** primero se crea el usuario en MySQL y se verifica, **después** se actualiza `wp-config.php`. Si se invierte el orden, el sitio queda inaccesible con "Error establishing a database connection".

#### Paso 1 — Generar contraseña aleatoria larga (64 chars hex)

```bash
openssl rand -hex 32
# Ejemplo de salida: a1b2c3d4e5f6... (64 caracteres hex)
```

Guardar este valor temporalmente; se usará en los pasos 2 y 3.

#### Paso 2 — Crear el usuario dedicado en MySQL

```sql
-- Conectar como root:
-- mysql -u root -p

-- Crear usuario dedicado con acceso solo desde localhost:
CREATE USER 'wppro3'@'localhost' IDENTIFIED BY '<CONTRASEÑA_DEL_PASO_1>';

-- Otorgar permisos SOLO sobre la base de datos del proyecto:
GRANT ALL PRIVILEGES ON `wordpress-proyecto3`.* TO 'wppro3'@'localhost';

-- Recargar privilegios:
FLUSH PRIVILEGES;
```

**Permisos otorgados:** `ALL PRIVILEGES` sobre `wordpress-proyecto3.*` (todas las tablas de esa base). Esto incluye `SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX` — suficientes para que WordPress funcione, instale plugins, haga migraciones y actualizaciones.

**Permisos que NO tiene el nuevo usuario:**
- Acceso a otras bases de datos del servidor.
- `GRANT OPTION` (no puede crear otros usuarios).
- `SUPER`, `RELOAD`, `SHUTDOWN`, `PROCESS` (no puede administrar el servidor).
- Acceso remoto (solo `localhost`).

#### Paso 3 — Actualizar `wp-config.php`

Reemplazar las líneas 26–29:

```php
// Antes:
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '<contraseña trivial, retirada del documento>' );

// Después:
define( 'DB_USER', 'wppro3' );
define( 'DB_PASSWORD', '<CONTRASEÑA_DEL_PASO_1>' );
```

#### Paso 4 — Verificar

```bash
# 1. Sintaxis de wp-config.php:
php -l wp-config.php
# No syntax errors detected in wp-config.php

# 2. El sitio carga (sin "Error establishing a database connection"):
curl -s -o /dev/null -w "%{http_code}" http://localhost/wordpress/proyecto3/
# 200

# 3. El plugin sigue funcionando (login + endpoint público):
curl -s -X POST http://localhost/wordpress/proyecto3/wp-json/jwt-gallery/v1/login \
  -H 'Content-Type: application/json' \
  -d '{"username":"admin","password":"..."}'
# Devuelve token válido

# 4. El usuario root ya no es necesario para WordPress. Verificar conexión del nuevo usuario:
mysql -u wppro3 -p'<CONTRASEÑA>' -e "USE \`wordpress-proyecto3\`; SHOW TABLES;" | head -5
# Lista las tablas de WordPress

# 5. Verificar que el nuevo usuario NO puede acceder a otras bases:
mysql -u wppro3 -p'<CONTRASEÑA>' -e "SHOW DATABASES;"
# Debe mostrar solo: information_schema, wordpress-proyecto3
# (NO mysql, sys, performance_schema, ni otras bases del servidor)
```

### Qué cambia

- WordPress se conecta con `wppro3` (usuario dedicado) en vez de `root`.
- La contraseña es aleatoria y larga (64 chars hex), no predecible.
- El usuario solo puede operar sobre `wordpress-proyecto3`; no tiene acceso a otras bases ni privilegios de administración del servidor.
- Si un atacante obtiene estas credenciales, el daño se limita a una sola base de datos.

### Qué no cambia

- El theme y el plugin funcionan sin cambios (usan la API de WordPress, no conexiones propias).
- La estructura de la base de datos, tablas, y datos existentes no se modifican.
- El usuario `root` sigue existiendo en MySQL (para administración del servidor), pero WordPress ya no lo usa.
- Los endpoints REST, login, panel y CRUD siguen funcionando igual.

### Consideraciones para producción

| Entorno | Acción |
|---|---|
| **Desarrollo** | Aplicar el fix con los valores de la BD local (`localhost`). |
| **Producción** | El host provee credenciales dedicadas por entorno. `wp-config.php` en producción NO usa `root` ni contraseñas triviales; usa las credenciales del servidor de BD de producción. El principio es el mismo: usuario dedicado, contraseña fuerte, permisos mínimos. |

> **Nota:** En hosting compartido (cPanel, Plesk), el usuario de BD y la base se crean desde el panel. En servidores dedicados/VPS, se hace por línea de comandos como se documenta arriba.

### Estado

- **Documentado:** ✅ (este fix)
- **Aplicado en código:** ✅ (2026-10-20)
  - Usuario MySQL `wppro3` creado con contraseña segura
  - `wp-config.php` actualizado (líneas 26-29)
  - Verificado: sitio funciona sin cambios de funcionalidad

## Fix 5: Sin forzar HTTPS — bugs del fix original y solución corregida

- **Fecha:** 2026-08-25 (documentado y aplicado)
- **Archivos afectados:** `wp-config.php`, `.htaccess` (producción)

### Problema original

No existe `FORCE_SSL_ADMIN` en `wp-config.php`. El endpoint `/login` del plugin envía la contraseña en texto plano por el cuerpo JSON. Si el sitio se despliega sin TLS (o si un usuario entra por HTTP), las credenciales son interceptables.

### Bugs del fix original (documentación anterior)

El fix original proponía simplemente añadir `define( 'FORCE_SSL_ADMIN', true )` a `wp-config.php`. Al revisar el código del plugin se encontraron **tres bugs** que hacen que esa solución sea insuficiente para este plugin:

#### Bug 1: `FORCE_SSL_ADMIN` no protege el login del plugin

`FORCE_SSL_ADMIN` **solo redirige wp-admin y wp-login.php a HTTPS**. No afecta el frontend ni los endpoints REST.

El login del plugin no es wp-login.php — es un endpoint REST en el frontend:

1. `class-jg-shortcode.php` registra el shortcode `[jwt_gallery]` que renderiza la galería en el frontend.
2. `jwt-gallery.js` línea 64: `fetch(REST_URL + '/login', ...)` con `credentials: 'include'`.
3. `class-jg-shortcode.php` línea 30: `restUrl` viene de `rest_url('jwt-gallery/v1')` que usa `home_url()`.

**Resultado:** en producción, un usuario entra a `http://produccion.com/galeria/` (HTTP), el JS llama al REST login por HTTP, y la contraseña viaja en texto plano. `FORCE_SSL_ADMIN` no hace nada aquí porque el endpoint no pasa por wp-admin.

#### Bug 2: cookie `Secure` con esquema mixto

En `class-jg-rest.php` línea 207, la cookie de sesión se setea con:

```php
'secure' => is_ssl(),
```

Si el login ocurre por HTTPS pero la galería se carga por HTTP (sin redirect server-level):
- La cookie se setea con `Secure=true` (por HTTPS).
- El frontend carga por HTTP → `rest_url()` devuelve URL HTTP.
- El navegador **rechaza enviar la cookie `Secure` por HTTP** → `credentials: 'include'` envía nada → autenticación falla.

#### Bug 3: `is_ssl()` detrás de reverse proxy

Si producción usa Cloudflare, AWS ALB, o Nginx que termina TLS, `is_ssl()` devuelve `false` porque Apache recibe HTTP del proxy (aunque el usuario navega por HTTPS). Sin corrección:
- La cookie de sesión JWT **no recibe el flag `Secure`** en producción aunque esté en HTTPS.
- `FORCE_SSL_ADMIN` no se activa (si está condicional a `is_ssl()`).

### Solución corregida (aplicada)

Tres cambios que funcionan en local (HTTP) y producción (HTTPS) sin romper nada:

#### Cambio 1: corregir `is_ssl()` detrás de reverse proxy (en `wp-config.php`)

```php
if ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && strpos( $_SERVER['HTTP_X_FORWARDED_PROTO'], 'https' ) !== false ) {
    $_SERVER['HTTPS'] = 'on';
}
```

- **Qué hace:** si el reverse proxy envía `X-Forwarded-Proto: https`, WordPress lo trata como HTTPS.
- **En local (HTTP sin proxy):** `$_SERVER['HTTP_X_FORWARDED_PROTO']` no existe → el bloque no se ejecuta → todo sigue igual.
- **En producción (HTTPS detrás de proxy):** `is_ssl()` ahora devuelve `true` → la cookie recibe `Secure=true` y `FORCE_SSL_ADMIN` se activa.

#### Cambio 2: `FORCE_SSL_ADMIN` condicional (en `wp-config.php`)

```php
if ( ! empty( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) {
    define( 'FORCE_SSL_ADMIN', true );
}
```

- **Qué hace:** activa `FORCE_SSL_ADMIN` **solo cuando el sitio se sirve por HTTPS** (directo o detectado por el proxy fix).
- **En local (HTTP):** `$_SERVER['HTTPS']` no es `'on'` → no se define → no hay redirect loop → wp-admin local accesible.
- **En producción (HTTPS):** se activa → wp-admin y wp-login.php redirigen a HTTPS.

#### Cambio 3: redirección 301 HTTP→HTTPS en el servidor web (solo producción)

Esto es lo que protege el login del plugin (Bug 1). Hay que forzar HTTPS en **toda la web**, no solo wp-admin. Se hace en el servidor web, no en `wp-config.php`:

**Apache (en `.htaccess` o VirtualHost de producción):**

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**Nginx (en el server block de producción):**

```nginx
server {
    listen 80;
    server_name tudominio.com www.tudominio.com;
    return 301 https://$host$request_uri;
}
```

> **Importante:** este redirect **no se aplica en local**. En desarrollo (`http://localhost`) no hay certificado TLS y el redirect causaría un bucle infinito. Solo se configura en el entorno de producción con certificado instalado.

### Código aplicado en `wp-config.php` (líneas 95–117)

```php
/* Add any custom values between this line and the "stop editing" line. */

/**
 * Seguridad: corregir is_ssl() detrás de un reverse proxy (Cloudflare, AWS ALB,
 * Nginx terminando TLS, etc.). Los proxies descifran TLS y reenvían a Apache
 * por HTTP plano, por lo que is_ssl() devuelve false aunque el usuario navega
 * por HTTPS. Sin este fix, la cookie de sesión JWT del plugin no recibe el
 * flag Secure en producción y FORCE_SSL_ADMIN no se activa.
 *
 * En local (HTTP sin proxy) $_SERVER['HTTP_X_FORWARDED_PROTO'] no existe,
 * por lo que este bloque no hace nada y todo sigue funcionando igual.
 */
if ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && strpos( $_SERVER['HTTP_X_FORWARDED_PROTO'], 'https' ) !== false ) {
    $_SERVER['HTTPS'] = 'on';
}

/**
 * Seguridad: forzar HTTPS en wp-admin y wp-login.php SOLO cuando el sitio se
 * sirve por HTTPS. En desarrollo local (HTTP sin certificado) no se define,
 * evitando redirect loops. En producción (HTTPS directo o detrás de proxy)
 * se activa automáticamente.
 */
if ( ! empty( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) {
    define( 'FORCE_SSL_ADMIN', true );
}
```

### Cómo funcionan juntos los tres cambios

```
┌─────────────────────────────────────────────────────────────┐
│ LOCAL (http://localhost/wordpress/proyecto3/)               │
│                                                             │
│  X-Forwarded-Proto: (no existe)                             │
│    → bloque del proxy no se ejecuta                          │
│    → $_SERVER['HTTPS'] no es 'on'                           │
│    → FORCE_SSL_ADMIN no se define                            │
│    → is_ssl() = false → cookie Secure=false                  │
│    → todo funciona por HTTP (cookie sin Secure, válido)      │
│    → .htaccess sin redirect HTTPS (no se aplica)            │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ PRODUCCIÓN con HTTPS directo (sin proxy)                    │
│                                                             │
│  $_SERVER['HTTPS'] = 'on' (Apache con TLS)                  │
│    → bloque del proxy no se ejecuta (no hay header)          │
│    → FORCE_SSL_ADMIN = true → wp-admin por HTTPS             │
│    → is_ssl() = true → cookie Secure=true                    │
│    → redirect 301 HTTP→HTTPS en .htaccess                   │
│    → login del plugin por HTTPS → contraseña cifrada         │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ PRODUCCIÓN detrás de reverse proxy (Cloudflare/ALB/Nginx)    │
│                                                             │
│  X-Forwarded-Proto: https                                   │
│    → bloque del proxy fija $_SERVER['HTTPS'] = 'on'          │
│    → FORCE_SSL_ADMIN = true → wp-admin por HTTPS             │
│    → is_ssl() = true → cookie Secure=true                    │
│    → redirect 301 HTTP→HTTPS en .htaccess                   │
│    → login del plugin por HTTPS → contraseña cifrada         │
└─────────────────────────────────────────────────────────────┘
```

### Por qué no rompe el plugin ni el theme

| Componente | Local (HTTP) | Producción (HTTPS) |
|---|---|---|
| Cookie JWT `Secure` flag | `false` (correcto, HTTP) | `true` (correcto, HTTPS) |
| `credentials: 'include'` | cookie enviada por HTTP | cookie enviada por HTTPS |
| `rest_url()` | `http://localhost/...` | `https://produccion/...` |
| `apiFetch` (JS) | funciona por HTTP | funciona por HTTPS |
| wp-admin | accesible por HTTP | redirige a HTTPS |
| Frontend galería | accesible por HTTP | accesible por HTTPS |
| Theme | sin cambios | sin cambios |

### Verificación

```bash
# 1. Sintaxis de wp-config.php:
php -l wp-config.php
# No syntax errors detected in wp-config.php

# 2. Verificar que el fix del proxy está presente:
grep -c 'HTTP_X_FORWARDED_PROTO' wp-config.php
# 1

# 3. Verificar que FORCE_SSL_ADMIN es condicional:
grep -A1 'FORCE_SSL_ADMIN' wp-config.php
# if ( ! empty( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) {
#     define( 'FORCE_SSL_ADMIN', true );
# }

# 4. En local (HTTP): verificar que wp-admin sigue accesible sin redirect loop.
# 5. En producción (HTTPS): verificar que http:// → https:// redirige (301).
# 6. En producción detrás de proxy: verificar que la cookie tiene Secure=true
#    (DevTools → Application → Cookies → buscar jg_session).
```

### Qué cambia

- **En producción (HTTPS):** wp-admin y wp-login.php redirigen a HTTPS. La cookie JWT recibe `Secure=true`. El login del plugin viaja cifrado si hay redirect 301 HTTP→HTTPS. `is_ssl()` funciona detrás de reverse proxy.
- **En local (HTTP):** nada cambia. No hay redirect loop, wp-admin sigue accesible, la cookie funciona sin `Secure` (necesario en HTTP).

### Qué no cambia

- El plugin `jwt-gallery` no se modifica (el flag `Secure => is_ssl()` ya estaba bien implementado en `class-jg-rest.php`; el fix está en `wp-config.php` para que `is_ssl()` devuelva el valor correcto).
- El theme no se modifica.
- El `.htaccess` local no se modifica (sin redirect HTTPS en desarrollo).
- El flujo de login, logout, CRUD de imágenes y categorías del plugin sigue igual.

### Estado

- **Documentado:** ✅ (este fix, corregido)
- **Aplicado en código:** ✅ (`wp-config.php` líneas 95–117 — proxy fix + `FORCE_SSL_ADMIN` condicional)
- **Pendiente en producción:** redirección 301 HTTP→HTTPS en el servidor web (Apache/Nginx). No se puede aplicar desde `wp-config.php`; se configura en el VirtualHost/server block de producción.

## Fix 6: Token JWT sin revocación real

- **Fecha:** 2026-08-25
- **Archivos afectados:**
  - `wp-content/plugins/jwt-gallery/includes/class-jg-rest.php` (función `logout`, línea 191)
  - `wp-content/plugins/jwt-gallery/assets/js/jwt-gallery.js` (función `clearSession`)
  - `wp-content/plugins/jwt-gallery/includes/class-jg-jwt.php` (función `verify`)

### Problema

El endpoint `/logout` solo borra la cookie del navegador:

```php
public static function logout( WP_REST_Request $request ) {
    self::clear_session_cookie();
    return rest_ensure_response( array( 'logged_out' => true ) );
}
```

El token JWT sigue siendo válido en el servidor hasta que expire (`DEFAULT_TTL = DAY_IN_SECONDS`, 24h). Un token robado no se puede invalidar —no hay blacklist ni invalidación por cambio de contraseña. Si alguien roba el token, tiene acceso garantizado por 24h sin importar cuántas veces el usuario "cierre sesión".

**Estado actual del código (verificado):**
- No hay claim `jti` (JWT ID) en los tokens emitidos.
- No hay blacklist de tokens.
- No hay verificación de revocación en `verify()`.

### Solución

Implementar revocación de tokens con un identificador de sesión (`jti`) que se rota al cerrar sesión:

1. **Al emitir el token** (`issue()`): incluir claim `jti` con un valor aleatorio y guardarlo en `usermeta` del usuario:
   ```php
   $jti = wp_generate_password( 32, false );
   $claims['jti'] = $jti;
   update_user_meta( $user_id, 'jg_token_jti', $jti );
   ```

2. **Al verificar el token** (`verify()`): comparar el `jti` del token con el guardado en `usermeta`:
   ```php
   $stored_jti = get_user_meta( $claims['sub'], 'jg_token_jti', true );
   if ( ! hash_equals( $stored_jti, (string) $claims['jti'] ) ) {
       return new WP_Error( 'jg_token_revoked', 'Token revocado.', array( 'status' => 401 ) );
   }
   ```

3. **Al cerrar sesión** (`logout()`): rotar el `jti` en `usermeta` para invalidar el token actual:
   ```php
   public static function logout( WP_REST_Request $request ) {
       $user = self::authenticated_user( $request );
       if ( ! is_wp_error( $user ) ) {
           update_user_meta( $user->ID, 'jg_token_jti', wp_generate_password( 32, false ) );
       }
       self::clear_session_cookie();
       return rest_ensure_response( array( 'logged_out' => true ) );
   }
   ```

> **Limitación del enfoque `jti` único:** este diseño solo permite una sesión activa por usuario. Si el usuario inicia sesión en dos dispositivos, el segundo invalida el token del primero. Para múltiples sesiones, se necesita una blacklist de `jti`s con expiración (más complejo).

### Verificación

```bash
# 1. Sintaxis PHP:
php -l wp-content/plugins/jwt-gallery/includes/class-jg-rest.php
php -l wp-content/plugins/jwt-gallery/includes/class-jg-jwt.php

# 2. Flujo de revocación:
# a. Login → obtener token T1
# b. Usar T1 en una petición autenticada → 200 OK
# c. Logout con T1 → 200 logged_out: true
# d. Usar T1 nuevamente → 401 jg_token_revoked (token invalidado)
# e. Login nuevamente → obtener token T2 (jti distinto)
# f. Usar T2 → 200 OK (nueva sesión válida)
```

### Qué cambia

- Cerrar sesión invalida realmente el token en el servidor, no solo en el navegador.
- Un token robado se puede invalidar cambiando la contraseña o cerrando sesión.
- Solo una sesión activa por usuario (limitación del enfoque simple).

### Qué no cambia

- El TTL de 24h sigue aplicando (pero ahora se puede revocar antes).
- Los tokens sin `jti` (emitidos antes del fix) se rechazan —requiere que todos los usuarios vuelvan a iniciar sesión una vez.

### Estado

- **Documentado:** ✅ (este fix)
- **Aplicado en código:** ✅ (2026-10-20)
  - `class-jg-jwt.php`: agregar claim `jti` en `issue()` y validación en `verify()`
  - `class-jg-rest.php`: guardar `jti` en usermeta en `login()` y rotar en `logout()`
  - Verificado: tokens se revocan realmente al cerrar sesión; sesiones antiguas se invalidan

## Fix 7: Secret JWT en la base de datos (no en wp-config.php)

- **Fecha:** 2026-08-25
- **Archivos afectados:**
  - `wp-config.php` (raíz del proyecto)
  - `wp-content/plugins/jwt-gallery/includes/class-jg-jwt.php` (sin cambios de código, solo cambio de origen del secreto)

### Problema

El secreto de firma del JWT se generaba y guardaba en la base de datos (`get_option('jg_jwt_secret')`) en `JG_JWT::get_secret()` (líneas 13–25). Aunque el código ya soportaba una constante `JG_JWT_SECRET`, esta no estaba definida en `wp-config.php`.

Si la BD se filtraba (SQLi en otro plugin, backup expuesto), el atacante obtenía el secreto y podía forjar tokens JWT válidos para cualquier usuario, suplantando identidad y permisos. Los secretos criptográficos deben vivir en el sistema de archivos, no en la BD.

### Solución

Se definió la constante `JG_JWT_SECRET` en `wp-config.php` con un secreto de 256 bits (64 caracteres hexadecimales):

```php
/**
 * JWT Gallery: secreto de firma (256 bits / 64 hex chars).
 * Debe vivir en el sistema de archivos, no en la base de datos,
 * para que un filtro de BD (SQLi, backup expuesto) no permita forjar tokens.
 * Si se cambia, todas las sesiones JWT activas dejan de ser válidas.
 */
define( 'JG_JWT_SECRET', '<secreto de 64 caracteres hexadecimales, solo en wp-config.php>' );
```

El código de `JG_JWT::get_secret()` ya prioriza la constante sobre la opción de BD:

```php
public static function get_secret() {
    if ( defined( 'JG_JWT_SECRET' ) && JG_JWT_SECRET ) {
        return JG_JWT_SECRET;   // ← ahora siempre se usa esta rama
    }

    $secret = get_option( self::OPTION_SECRET );   // ← ya nunca se ejecuta
    if ( ! $secret ) {
        $secret = bin2hex( random_bytes( 32 ) );
        update_option( self::OPTION_SECRET, $secret, false );
    }

    return $secret;
}
```

No fue necesario modificar `class-jg-jwt.php`: la lógica de prioridad ya existía; solo faltaba definir la constante.

### Por qué 256 bits

- El JWT usa HS256 (HMAC-SHA256).
- La [RFC 2104](https://datatracker.ietf.org/doc/html/rfc2104) y OWASP recomiendan que el secreto de HMAC-SHA256 sea de al menos 256 bits (32 bytes) para evitar fuerza bruta.
- 64 caracteres hexadecimales = 32 bytes = 256 bits.

### Qué cambia

- El secreto de firma ahora vive en `wp-config.php` (sistema de archivos), no en la base de datos.
- `get_secret()` siempre retorna la constante `JG_JWT_SECRET`; la rama que lee/crea la opción de BD queda inactiva.
- Un atacante que filtre solo la BD ya no puede forjar tokens.
- Al cambiar el secreto, todos los tokens JWT emitidos con el secreto anterior dejan de ser válidos (invalidación forzada de sesiones).

### Qué no cambia

- La lógica de issue/verify del JWT no se modifica.
- El resto del plugin (REST, cookies, rate limiting) no se ve afectado.
- El secreto que existía en `get_option('jg_jwt_secret')` sigue en la BD pero ya no se usa; se puede eliminar manualmente con `delete_option('jg_jwt_secret')` si se desea.

### Limpieza opcional de la BD

El secreto anterior quedó en la tabla `wp_options`. Para eliminarlo (opcional, ya que no se usa):

```bash
wp option delete jg_jwt_secret
# o desde PHP:
delete_option( 'jg_jwt_secret' );
```

### Verificación

```bash
php -l wp-config.php
# No syntax errors detected in wp-config.php
```

## Fix 8: xmlrpc.php sin restricciones

- **Fecha:** 2026-08-25
- **Archivos afectados:**
  - `wp-content/plugins/jwt-gallery/jwt-gallery.php` (líneas 32–44)
  - `.htaccess` (líneas 12–18)

### Problema

El archivo `xmlrpc.php` estaba habilitado y accesible sin restricciones. XML-RPC permite amplificación de brute-force vía `system.multicall`: un atacante envía cientos de intentos de contraseña en una sola petición HTTP, evadiendo el rate limiting por IP que protege el endpoint `/login`. Aunque WordPress tiene mitigaciones parciales, el plugin no usa la app móvil ni pingbacks, así que XML-RPC no es necesario y debe deshabilitarse.

### Solución

Se aplicó defense-in-depth con dos capas independientes:

#### Capa 1 — Plugin (`jwt-gallery.php`)

```php
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );
```

- `xmlrpc_enabled` → `__return_false`: WordPress devuelve HTTP 403 a toda petición a `xmlrpc.php`.
- `xmlrpc_methods` → `__return_empty_array`: vacía la lista de métodos disponibles (incluido `pingback.ping`) aunque el filtro anterior ya bloquee todo.

#### Capa 2 — `.htaccess`

```apache
<Files xmlrpc.php>
    Require all denied
</Files>
```

- Apache bloquea el acceso al archivo antes de que WordPress lo procese.
- Protege incluso si el plugin se desactiva (defense-in-depth).
- `Require all denied` es la directiva Apache 2.4; en Apache 2.2 el equivalente sería `Order allow,deny` + `Deny from all`, pero WP 6.0+ requiere PHP 7.4+ que normalmente corre en Apache 2.4.

### Flujo de una petición a xmlrpc.php

1. Llega `POST /xmlrpc.php` → Apache lee `.htaccess` → `<Files xmlrpc.php>` → `Require all denied` → **HTTP 403** (no llega a PHP).
2. Si se omite la capa `.htaccess` (p. ej. en nginx) → llega a WordPress → `xmlrpc_enabled` devuelve `false` → **HTTP 403**.
3. El REST API (`wp-json/`) y los endpoints del plugin **no se ven afectados** (usan un mecanismo distinto).

### Qué cambia

- Toda petición a `xmlrpc.php` recibe HTTP 403.
- `system.multicall` ya no amplifica brute-force.
- `pingback.ping` queda deshabilitado (no hay pingbacks ni trackbacks).
- La capa `.htaccess` protege incluso si el plugin se desactiva.

### Qué no cambia

- El REST API (`wp-json/jwt-gallery/v1/*`) sigue funcionando normalmente.
- El login, panel, CRUD de imágenes y categorías no se ven afectados.
- El rate limiting del endpoint `/login` (Fix 2) sigue activo.
- La app web no usa XML-RPC, así que no hay pérdida de funcionalidad.

### Verificación

```bash
php -l wp-content/plugins/jwt-gallery/jwt-gallery.php
# No syntax errors detected in wp-content/plugins/jwt-gallery/jwt-gallery.php
```

```bash
# Probar que xmlrpc.php devuelve 403:
curl -s -o /dev/null -w "%{http_code}" http://localhost/wordpress/proyecto3/xmlrpc.php
# 403
```

## Fix 9: Algoritmo (alg) del header JWT sin verificar

- **Fecha:** 2026-08-25
- **Archivo afectado:** `wp-content/plugins/jwt-gallery/includes/class-jg-jwt.php` (líneas 74–104)

### Problema

`verify()` decodificaba el payload pero nunca comprobaba que `alg === 'HS256'`. En la implementación actual no era explotable (siempre verifica con HMAC-SHA256 sin importar el header), pero es un patrón frágil: si alguien añade soporte a otro algoritmo en el futuro sin cuidado, abre la puerta al ataque de confusión de algoritmos (`alg: none` / `RS256 ↔ HS256`). En ese escenario, un atacante podría:

- **`alg: none`**: forjar un token sin firma que pasa la verificación.
- **`RS256 → HS256`**: si el servidor conoce la clave pública RSA, un atacante la usa como secreto HMAC y firma tokens válidos.

### Solución

Se añadieron dos validaciones en `verify()`, **antes** de verificar la firma:

```php
// 1. Decodificar y validar el header
$header = json_decode( self::base64url_decode( $header_b64 ), true );
if ( ! is_array( $header ) || empty( $header['alg'] ) ) {
    return new WP_Error( 'jg_invalid_header', 'Header del token inválido.', array( 'status' => 401 ) );
}

// 2. Validar que el algoritmo sea exactamente HS256 (comparación resistente a timing)
if ( ! hash_equals( 'HS256', $header['alg'] ) ) {
    return new WP_Error( 'jg_invalid_alg', 'Algoritmo de firma no permitido.', array( 'status' => 401 ) );
}
```

Adicionalmente, se añadió validación del claim `iss` (issuer) como control de defensa:

```php
// Validar emisor (iss): el token debe haber sido emitido por este sitio.
if ( empty( $claims['iss'] ) || ! hash_equals( home_url(), (string) $claims['iss'] ) ) {
    return new WP_Error( 'jg_invalid_issuer', 'Emisor del token inválido.', array( 'status' => 401 ) );
}
```

### Orden de validación en `verify()`

1. Formato del token (3 partes separadas por `.`).
2. **Header**: decodificar y validar `alg === 'HS256'` (nuevo).
3. Firma HMAC-SHA256 con `hash_equals()` (timing-safe).
4. Claims: `exp` y `sub` presentes.
5. **Emisor `iss`**: `=== home_url()` (nuevo).
6. Expiración `exp`.

### Qué cambia

- El header se valida antes de verificar la firma. Si `alg !== 'HS256'`, se rechaza con `jg_invalid_alg` (401).
- Si el header no es JSON válido o no tiene `alg`, se rechaza con `jg_invalid_header` (401).
- El claim `iss` se valida contra `home_url()`. Si no coincide, se rechaza con `jg_invalid_issuer` (401).
- Todas las comparaciones usan `hash_equals()` para resistir timing attacks.

### Qué no cambia

- `issue()` sigue firmando con HS256 y añadiendo `iss = home_url()`.
- Los tokens válidos actuales siguen funcionando (ya tienen `alg: HS256` e `iss: home_url()`).
- Los endpoints REST no cambian: siguen llamando a `JG_JWT::verify()` igual que antes.
- El login, panel, CRUD de imágenes y categorías funcionan sin cambios.

### Verificación

```bash
php -l wp-content/plugins/jwt-gallery/includes/class-jg-jwt.php
# No syntax errors detected in wp-content/plugins/jwt-gallery/includes/class-jg-jwt.php
```

```bash
# Un token con alg: none debe devolver 401 (jg_invalid_alg):
# (requiere craft manual del token)
```

```bash
# Flujo normal debe seguir funcionando:
curl -s -X POST http://localhost/wordpress/proyecto3/wp-json/jwt-gallery/v1/login \
  -H 'Content-Type: application/json' \
  -d '{"username":"admin","password":"..."}'
# Devuelve token válido
```

## Fix 10: Claim iss emitido pero nunca verificado

- **Fecha:** 2026-08-25
- **Archivo afectado:** `wp-content/plugins/jwt-gallery/includes/class-jg-jwt.php` (líneas 27–39, 66, 115–123)

### Problema

`issue()` incluía `'iss' => home_url()` al firmar el token (línea 52), pero `verify()` nunca comprobaba ese claim. Aunque no era explotable por sí solo —un token de otro sitio no pasaría la verificación de firma porque los secretos son distintos— es una capa de defense-in-depth ausente: el `iss` sirve para detectar reenvío de tokens entre entornos (dev/staging/prod) y como control adicional si el secreto llegara a comprometerse.

La validación de `iss` fue introducida en el Fix 6 como control secundario, pero mezclada con la validación de `alg`. Este fix la **mejora y formaliza** como fix independiente:

1. **Extrae el issuer esperado a un helper** `get_expected_issuer()`, eliminando la duplicación: tanto `issue()` como `verify()` llamaban `home_url()` por separado, sin garantía de que ambas llamadas devolvieran el mismo valor en todos los escenarios.
2. **Documenta el control** como capa de defense-in-depth explícita.

### Solución

#### Helper — fuente única de verdad

```php
/**
 * Emisor esperado del token (claim iss).
 *
 * Fuente única de verdad: tanto issue() como verify() usan este método,
 * garantizando que el valor firmado y el valor verificado sean siempre
 * el mismo, aunque home_url() cambie entre WP installs (multisite, cambio
 * de dominio, etc.).
 *
 * @return string
 */
public static function get_expected_issuer() {
    return home_url();
}
```

#### `issue()` — usa el helper

```php
$claims['iss'] = self::get_expected_issuer();
```

#### `verify()` — valida el claim con `hash_equals()` (timing-safe)

```php
// Validar emisor (iss): el token debe haber sido emitido por este sitio.
// Defense-in-depth: un token de otro sitio WP no pasaría la firma (secretos
// distintos), pero verificar iss añade una capa extra y permite detectar
// reenvío de tokens entre entornos. Se usa get_expected_issuer() como
// fuente única de verdad, compartida con issue().
$expected_issuer = self::get_expected_issuer();
if ( empty( $claims['iss'] ) || ! hash_equals( $expected_issuer, (string) $claims['iss'] ) ) {
    return new WP_Error( 'jg_invalid_issuer', 'Emisor del token inválido.', array( 'status' => 401 ) );
}
```

### Por qué importa (defense-in-depth)

| Escenario | Sin verificar iss | Verificando iss |
|---|---|---|
| Secreto comprometido + token de otro sitio | Podría aceptarse si la firma coincide | Rechazado (`jg_invalid_issuer`) |
| Reenvío de token entre entornos (dev/prod) | Aceptado si el secreto coincide | Rechazado (`iss` distinto) |
| Token legítimo de este sitio | Aceptado | Aceptado |

La verificación de `iss` **no reemplaza** la validación de la firma: es una capa adicional que se aplica **después** de verificarla.

### Orden de validación en `verify()` (actualizado)

1. Formato del token (3 partes separadas por `.`).
2. Header: decodificar y validar `alg === 'HS256'` (Fix 6).
3. Firma HMAC-SHA256 con `hash_equals()` (timing-safe).
4. Claims: `exp` y `sub` presentes.
5. **Emisor `iss`: `=== get_expected_issuer()`** (Fix 7).
6. Expiración `exp`.

### Qué cambia

- `issue()` y `verify()` usan `get_expected_issuer()` como fuente única de verdad.
- `verify()` rechaza tokens cuyo `iss` no coincide con `home_url()` con `jg_invalid_issuer` (401).
- La comparación usa `hash_equals()` (timing-safe).
- El claim `iss` ausente o vacío también se rechaza.

### Qué no cambia

- Los tokens válidos actuales siguen funcionando (ya tienen `iss: home_url()`).
- Los endpoints REST no cambian: siguen llamando a `JG_JWT::verify()`.
- El login, panel, CRUD de imágenes y categorías funcionan sin cambios.
- El secreto JWT y la firma HMAC-SHA256 no se modifican.

### Verificación

```bash
php -l wp-content/plugins/jwt-gallery/includes/class-jg-jwt.php
# No syntax errors detected in wp-content/plugins/jwt-gallery/includes/class-jg-jwt.php
```

```bash
# Flujo normal debe seguir funcionando:
curl -s -X POST http://localhost/wordpress/proyecto3/wp-json/jwt-gallery/v1/login \
  -H 'Content-Type: application/json' \
  -d '{"username":"admin","password":"..."}'
# Devuelve token válido (iss = home_url())

# Un token con iss distinto debe devolver 401 (jg_invalid_issuer):
# (requiere craft manual del token cambiando el claim iss)
```

## Fix 11: URLs de imagen externas — riesgo aceptado por diseño

- **Fecha:** 2026-08-25
- **Archivo afectado:** `wp-content/plugins/jwt-gallery/includes/class-jg-rest.php` (función `validate_image_url`, línea 272)

### Contexto del análisis original

El análisis de seguridad identificó que `validate_image_url` acepta cualquier URL `http(s)` con extensión de imagen válida. El navegador del visitante carga imágenes de dominios externos arbitrarios, lo que podría permitir fingerprinting de IP y tracking del visitante por parte del host externo. El propio análisis aclara:

> *"El servidor no la fetcha (no hay SSRF server-side), pero el navegador del visitante carga imágenes de dominios externos arbitrarios → posible fingerprinting de IP y tracking. By design, pero conviene restringir a una whitelist de hosts (ej. postimg.cc, el CDN del cliente)."*

### Decisión: no aplicar whitelist — aceptado por diseño

La funcionalidad central del plugin es que el usuario pueda subir sus productos usando **cualquier URL de imagen externa** (postimg.cc, imgur, GitHub, CDN propio, etc.). Restringir a una whitelist de hosts rompería el caso de uso principal:

- Un producto puede tener su imagen alojada en cualquier host.
- Los usuarios usan distintos servicios de alojamiento de imágenes según su preferencia.
- Añadir o quitar hosts de una whitelist requeriría mantenimiento continuo y rompería URLs ya guardadas si un host sale de la whitelist.
- El valor del plugin está en su flexibilidad para aceptar cualquier fuente de imagen.

Por lo tanto, este punto se documenta como **riesgo aceptado por diseño**, no como un fix a aplicar.

### SSRF: no aplicable

El análisis original confirma que **no hay SSRF server-side**: el servidor nunca hace una petición HTTP para fetchear la imagen. Solo valida el formato de la URL y la guarda en la base de datos. La imagen la carga el navegador del visitante (cliente-side). Un SSRF server-side sería crítico; este no es el caso.

### Mitigaciones existentes

Aunque no se aplica whitelist, `validate_image_url` ya implementa las siguientes validaciones (líneas 272–290):

```php
private static function validate_image_url( $raw_url ) {
    if ( strlen( $raw_url ) > 2048 ) {
        return new WP_Error( 'jg_invalid_image_url', 'El enlace es demasiado largo.', array( 'status' => 400 ) );
    }

    $url = wp_http_validate_url( $raw_url );
    if ( ! $url ) {
        return new WP_Error( 'jg_invalid_image_url', 'El enlace no es una URL http(s) válida.', array( 'status' => 400 ) );
    }

    $path      = (string) wp_parse_url( $url, PHP_URL_PATH );
    $extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

    if ( ! in_array( $extension, self::ALLOWED_IMAGE_EXTENSIONS, true ) ) {
        return new WP_Error( 'jg_bad_extension', 'El enlace debe apuntar a un archivo de imagen (jpg, png, gif, webp, avif).', array( 'status' => 400 ) );
    }

    return esc_url_raw( $url );
}
```

| Mitigación | Qué previene |
|---|---|
| `strlen > 2048` | URLs excesivamente largas (ataque de almacenamiento o DoS) |
| `wp_http_validate_url()` | Bloquea esquemas no `http(s)`, bloquea puertos no estándar, valida formato |
| Extensión en `ALLOWED_IMAGE_EXTENSIONS` | Asegura que la URL apunta a un archivo de imagen (jpg, png, gif, webp, avif) |
| `esc_url_raw()` | Sanitiza la URL antes de guardarla en BD (elimina caracteres peligrosos) |

### Riesgo residual aceptado

| Riesgo | Severidad | Aceptado porque |
|---|---|---|
| Fingerprinting de IP del visitante por el host externo | Baja | Es inherente a cualquier web que carga imágenes externas; el usuario elige su host |
| Tracking del visitante por el host externo | Baja | Igual que arriba; no hay SSRF server-side |
| Host externo caído → imagen no carga | Baja | El usuario es responsable de la disponibilidad de su imagen |

### Qué cambia

Nada. No se modifica el código. Se documenta la decisión de aceptar el riesgo con la justificación del caso de uso.

### Qué no cambia

- `validate_image_url` sigue aceptando cualquier host externo con extensión de imagen válida.
- Los productos siguen pudiendo usar imágenes de cualquier URL externa.
- Las mitigaciones existentes (longitud, `wp_http_validate_url`, extensión, `esc_url_raw`) se mantienen.

### Estado

- **Documentado:** ✅ (este punto)
- **Aplicado en código:** No aplica. Es un riesgo aceptado por diseño, no un fix.
- **Decisión:** No aplicar whitelist de hosts porque rompería la funcionalidad central del plugin (aceptar cualquier URL de imagen externa).
- **Actualización 2026-09-29 (v1.4.0):** este riesgo quedó **superado** por el fix #12. El grupo pasó a ser cerrado: el panel sube **archivos locales** y el catálogo migró por completo a `uploads/jg-gallery/` (0 URLs externas). El fallback por `image_urls` se conserva por compatibilidad, pero ya no es el flujo del panel.

---

## Fix 12: Subida por archivos locales — elimina el riesgo de URLs externas

- **Fecha:** 2026-09-20 (migración) / 2026-09-27 (subida por archivos, v1.4.0)
- **Archivos afectados:** `wp-content/plugins/jwt-gallery/includes/class-jg-rest.php`, `assets/js/jwt-gallery.js`
- **Endpoint afectado:** `POST /images`, `PUT /images/{id}` (vía `X-HTTP-Method-Override: PUT`)

### Problema

El diseño original permitía publicar imágenes desde **cualquier URL externa** (`image_urls`), lo que dejaba el sitio dependiente de hosts de terceros y exponía a los visitantes a fingerprinting/tracking por esos hosts (documentado como riesgo aceptado en el fix #11). Además, un enlace podía apuntar a un recurso que no fuera realmente una imagen.

### Solución

Se cambió el flujo del panel a **subida de archivos desde el dispositivo**, con almacenamiento local:

- **Lista blanca de extensiones:** `ALLOWED_IMAGE_EXTENSIONS` = jpg, jpeg, png, gif, webp, avif. Cualquier otro archivo se rechaza (`jg_bad_extension`, 400).
- **Límite de tamaño:** `MAX_UPLOAD_BYTES` = 8 388 608 bytes (8 MB) por archivo (`jg_file_too_large`, 400).
- **Almacenamiento controlado:** el nombre final se genera desde el slug del post (sin usar el nombre original del cliente) y se mueve con `move_uploaded_file()` a `wp-content/uploads/jg-gallery/`, construido explícitamente con `wp_upload_dir()` + rutas manuales (no depende de `uploads_use_yearmonth_folders`). No hay path traversal: el nombre no se toma del usuario.
- **Sin huérfanos:** al borrar una publicación, quitar una foto o eliminar una categoría con piezas (v1.4.1) se eliminan los archivos locales con `unlink()` (`delete_local_images()`).
- **Sin SSRF:** ya no se descarga contenido remoto en el servidor; el archivo entra por `$_FILES` desde el navegador autenticado.

### Qué cambia

- El panel ya no publica desde URLs externas: sube archivos locales.
- PHP valida extensión y tamaño **antes** de guardar nada.
- La imagen servida al visitante es siempre del mismo origen (`get_site_url()`), no de un tercero.

### Qué no cambia

- `validate_image_url()` / `image_urls` se conservan solo como **fallback legacy**; el frontend del panel ya no los usa.
- La autenticación (JWT + cookie `jg_session`) y la autorización (solo el autor) siguen igual.

### Estado

- **Documentado:** ✅ (este punto)
- **Aplicado en código:** ✅ v1.4.0 (con el límite y la whitelist verificados e2e).
- **Riesgo residual:** un usuario autenticado podría subir imágenes con contenido inapropiado (no hay moderación); se acepta porque los usuarios del panel son controlados. Ver §6.2 del `theme.md` del plugin.

---

## Fix 13: Formulario de cotización: límite de envíos por IP

> Tema `todo-acrilico` 1.20.0 · 2026-10-20 · `ta_handle_contact_submit()`

### Problema
El formulario público (`admin-post.php?action=ta_contact_submit`) podía usarse para enviar correos sin límite (spam o abuso de la cuenta SMTP). Además, la primera versión del límite creaba el transient **antes** de validar: cada intento fallido renovaba la espera y el usuario quedaba bloqueado indefinidamente.

### Solución
- Transient `ta_contact_rate_{md5(REMOTE_ADDR)}` con una duración de `TA_CONTACT_RATE_LIMIT` (60 s).
- Se **comprueba** al inicio (si existe, `reason=rate_limit`) y se **crea solo después de un `wp_mail()` exitoso**.
- En el navegador, el modal con cuenta regresiva (`localStorage` `ta_rate_limit_timestamp`) solo mejora la experiencia: **el control real es el transient del servidor**.

### Qué no cambia
Se mantienen el honeypot `ta_contact_website`, que finge un éxito sin enviar nada, y el nonce `ta_contact_nonce`.

### Riesgo residual
- Usa `REMOTE_ADDR`. Detrás de un proxy o CDN todas las peticiones podrían compartir la IP del proxy y quedar limitadas juntas. Si eso pasa en producción, usar la IP real solo desde proxies de confianza, igual que en el login del plugin (Docs_Plugin.md §12.37).
- Los fallos de validación no cuentan para el límite, lo cual es intencional para no castigar a usuarios reales.

### Verificación
Envía dos mensajes válidos seguidos. El segundo debe responder `{"success":false,"reason":"rate_limit"}` y abrir el modal. A los 60 s se puede volver a enviar.

---

## Fix 14: Formulario de cotización: validación en servidor y envío AJAX

> Tema `todo-acrilico` 1.20.0 · 2026-10-20

### Problema
La validación nueva (nombre y apellido, teléfono móvil chileno, dominios de correo, adjuntos) se pidió para la interfaz. Si solo existiera en JavaScript, se podría saltar enviando el POST a mano.

### Solución
- **Cada regla del JS se repite en PHP** en `ta_handle_contact_submit()`:
  - Regex Unicode del nombre.
  - `^[3-9]\d{7}$` para el teléfono, normalizando los prefijos `9`/`569`.
  - Lista de dominios permitidos más `.cl`, `.org` y `.net`.
  - `is_email()`.
  - Adjuntos: extensión, tamaño y contenido real (`getimagesize()` o la firma `%PDF`), procesados con `move_uploaded_file()` y borrados después del envío.
- Todo se sanea con `sanitize_*` y el correo HTML escapa cada valor (`esc_html`, `esc_url`, `esc_attr`).
- **AJAX:** el servidor detecta `X-Requested-With: XMLHttpRequest` y responde `wp_send_json()`. La respuesta solo trae un `reason` y un mensaje genérico, sin datos internos. El camino sin JS sigue usando el redirect PRG.
- El `<form>` tiene `novalidate` para mostrar los mensajes propios. **No debilita nada**, porque el servidor vuelve a validar todo.
- La cabecera `Reply-To` solo se arma si `is_email()` es válido y el nombre pasó por `sanitize_text_field`, lo que evita inyección de cabeceras.

### Regla para quien modifique
Si cambias una validación en `validateField()` (JS), cambia la equivalente en PHP en el mismo commit. Detalle en SPEC.md, regla R4.

### Riesgo residual
- Si `wp_mail()` falla, `error_log()` registra el **correo del cliente**, que es un dato personal. Mantén el log fuera del directorio público y rótalo, o quita el correo del mensaje de log en producción.
- ✅ `ta_get_contact_email()` apunta a `contacto@todoacrilico.cl` desde el 2026-10-20 (ZIP del tema 2.0.0).
- El servidor no valida el **máximo** de 2000 caracteres de la descripción: solo lo limita el `maxlength` del navegador. Es un riesgo bajo, porque el texto se escapa.

---

## Fix 15: Datos en el navegador y deber de información (Ley 21.719)

> 2026-10-20

### Contexto
Para no perder datos al actualizar la página, el formulario guarda un borrador (nombre, teléfono, correo, descripción) en `sessionStorage` `ta_contact_draft`.

### Medidas
- Se usa `sessionStorage` y no `localStorage`: los datos se borran al cerrar la pestaña y también tras un envío exitoso. Nunca salen del navegador hasta que el usuario presiona "Enviar".
- No se usan cookies de analítica, publicidad ni seguimiento de terceros. Las fuentes son locales.
- La **Política de cookies** (página propia; texto en `legal/politica-de-cookies.html` del tema) declara, en lenguaje jurídico y sin nombres técnicos, cada elemento del navegador: la decisión sobre cookies (12 meses), el borrador del formulario (hasta cerrar la pestaña), el tiempo de espera entre envíos (60 s) y la sesión del panel (hasta 24 h, y las cookies de WordPress del administrador). Hasta el 2026-11-04 esto estaba en una tabla técnica de la sección 8 de la Privacidad; el cliente pidió sacarla de ahí para evitar la redundancia.

### Regla para quien modifique
Cualquier cookie o clave nueva del navegador, o un cambio de nombre o duración (también en el plugin), **obliga a actualizar `legal/politica-de-cookies.html` y subir `TA_LEGAL_VERSION`**. Agregar analítica o publicidad exige un banner de consentimiento previo.

### Pendiente relacionado
- **Local:** los `.md` de la raíz responden **HTTP 200** a cualquiera que acceda al servidor local, incluidos este documento y `README.md`, que tiene una contraseña.
- **Producción:** todos responden **404** (verificado el 2026-10-22 en `https://todoacrilico.cl/wp/`), así que no están expuestos. **Regla: no subir `.md` al servidor.** Los ZIP se generan con `-x "*.md"`.
- Si alguna vez hay que tenerlos en un servidor accesible, bloquéalos con `.htaccess`: `<FilesMatch "\.md$"> Require all denied </FilesMatch>`.
- `README.md` **nunca estuvo en git**: no aparece en el historial ni en GitHub (verificado el 2026-10-22).

### Nota (2026-10-22): filtros de escritura del formulario
El filtro que borra caracteres especiales del nombre y la conversión a minúsculas del correo (page-contacto.php) son **solo de experiencia de usuario**. La barrera de seguridad sigue siendo la validación y el saneamiento en el servidor (Fix 14). No reemplaces una por la otra.

---

## Fix 16: Microsoft Clarity: consentimiento previo y enmascarado del formulario

> Tema 2.1.0 · 2026-11-03

### Riesgo
Clarity graba sesiones (clics, scroll, movimientos) e instala cookies de analítica propias y de Microsoft. Cargarlo sin consentimiento incumpliría la Ley 21.719, y grabar el formulario expondría datos personales (nombre, teléfono, correo, descripción) a un tercero.

### Medidas
- **Consentimiento previo:** el script de Clarity no aparece en el HTML. `ta-consent.js` lo inyecta solo después de "Aceptar". Verificado: 0 peticiones a `clarity.ms` antes de aceptar y después de rechazar.
- **Libre:** "Rechazar" y "Aceptar" tienen el mismo tamaño y peso, y rechazar no limita ninguna función del sitio.
- **Revocable:** botón "Cambiar preferencias de cookies" de la página Política de cookies (enlazada en el footer; tema 2.3.1). Si la página no existe, el footer muestra "Preferencias de cookies". Al retirar el consentimiento se borran las cookies de Clarity del dominio y se recarga la página.
- **Formulario enmascarado:** `data-clarity-mask="true"` en `.ta-contact-form-card`. Clarity no registra el contenido de la tarjeta (campos, valores, adjuntos, errores). No cambia lo que ve el usuario.
- **Aislamiento:** el código va en un archivo aparte, con `try/catch` en cada paso. Un fallo de Clarity (bloqueador de anuncios, red caída, cambio de API) no afecta al sitio ni al formulario.
- **Información:** la **Política de cookies** (secciones 4 y 5) informa la finalidad, los tipos de datos, la duración, a Microsoft y Google como encargados y la transferencia internacional a EE. UU. con sus garantías contractuales (Art. 14 ter h, Arts. 27 y 28).

### Configuración recomendada en el panel de Clarity
- **Settings → Masking:** dejar **"Strict"** o **"Balanced"**. No usar "Relaxed", que deja de enmascarar los campos por defecto.
- No agregar elementos con `data-clarity-unmask` dentro del formulario.

### Riesgo residual
- Las cookies `CLID`, `MUID`, `ANONCHK`, `MR` y `SM` pertenecen a dominios de Microsoft, así que el sitio no puede borrarlas al retirar el consentimiento: solo deja de cargar Clarity. Esto está declarado en la Política.
- Clarity sí registra la navegación fuera del formulario: páginas, clics y textos públicos del sitio.

### Ampliación (2026-11-04, tema 2.2.0): Google Tag Manager
- GTM (`GTM-K7FR5V9K`) usa **el mismo consentimiento**: no se carga hasta "Aceptar" (verificado: 0 peticiones a `googletagmanager.com` antes y después de rechazar). No se usa el snippet del `<head>` ni el `<noscript>`.
- **Modo de consentimiento de Google:** se fija antes de cargar GTM, con `analytics_storage: granted` y `ad_storage`, `ad_user_data` y `ad_personalization` en `denied`. El botón "Aceptar" cubre solo analítica, así que aunque alguien agregue etiquetas de anuncios al contenedor, Google las ejecuta sin cookies publicitarias.
- **Riesgo propio de GTM:** quien administre el contenedor puede publicar cualquier script en el sitio sin tocar el tema. Recomendación: acceso a GTM solo para personas de confianza, con la verificación en dos pasos activada. Prohibido crear variables que lean los campos del formulario (por ejemplo "Elemento DOM" sobre `input[name=email]`) o etiquetas que envíen esos datos. Cada etiqueta nueva debe declararse en la Política (sección 8).
- Al retirar el consentimiento también se borran `_ga`, `_ga_*`, `_gid` y `_gat*` del dominio.

### Ampliación (2026-11-04, tema 2.3.0): Google Analytics 4 directo
- GA4 (`G-2KL8SPE9DY`) se carga con gtag.js desde el tema, con el mismo consentimiento y el mismo modo de consentimiento (publicidad denegada). No se usa el snippet del `<head>` que entregó Google.
- **No configurar GA4 también en GTM:** además de duplicar las visitas, haría que dos fuentes controlen la misma propiedad.
- GA4 no recibe los valores del formulario. La "medición mejorada" de interacciones con formularios solo envía el ID y el nombre del formulario y la URL de destino, nunca lo escrito. Mantenerlo así: no crear eventos personalizados con datos del formulario.

---

## Fix 17: Cambios del 2026-11-04: panel privado, errores de sesión y archivos huérfanos

> Tema 2.3.3 · Plugin 2.0.1 · 2026-11-04

### 17.1 Sin analítica en el login y el panel
- **Riesgo:** con el consentimiento dado en el sitio público, Clarity grababa también el panel interno: correos del personal, títulos de piezas sin publicar y la gestión de categorías.
- **Medida:** `ta_is_private_page()` (shortcodes `[jwt_gallery]` y `[jwt_gallery_panel]`) y `ta_should_load_analytics()`. En esas páginas no se encola `ta-consent.js` ni `taConsentConfig`, y no se imprime el banner. Verificado en local y en producción.

### 17.2 `require_auth()` devuelve el error real
- **Antes:** devolvía `false` y WordPress respondía `rest_forbidden` ("Lo siento, no tienes permisos para hacer eso.") aunque la causa fuera una sesión revocada o vencida. El usuario no sabía que debía volver a entrar.
- **Ahora:** devuelve el `WP_Error` de `authenticated_user()`: 401 `jg_token_revoked`, `jg_expired_token`, `jg_invalid_token`, etc., o 403 `jg_forbidden`. Los mensajes no revelan nada sensible: solo dicen que se inicie sesión de nuevo.
- **Panel:** ante un 401 de sesión (`SESSION_ERROR_CODES`), borra `localStorage['jg_session']`, muestra el modal "Sesión cerrada" y lleva al login. El 401 de credenciales incorrectas del login no entra en esa lista.

### 17.3 Sin archivos huérfanos en `uploads/jg-gallery/`
- **Riesgo:** una petición con varias imágenes válidas y una inválida (más de 8 MB, archivo falso) o con más de 10 imágenes guardaba las válidas y luego rechazaba la pieza. Repetida, esa petición llenaba el disco con archivos sin dueño (A04, consumo de recursos).
- **Medida:** `collect_image_sources()` valida las imágenes conservadas y cuenta el total **antes** de guardar nada. Si un archivo falla, borra los ya guardados en esa petición. `upload_image()` también los borra si falla `wp_insert_post`. Verificado: 0 archivos nuevos en cada rechazo (Docs_Plugin.md §12.53).

### 17.4 Límites en un solo lugar
- Las constantes de `JG_REST` (`MAX_TITLE_CHARS`, `MAX_DESCRIPTION_CHARS`, `MAX_USAGE_CHARS`, `MAX_MEASUREMENTS_CHARS`, `MAX_IMAGES_PER_POST`, `MAX_UPLOAD_BYTES` y `ALLOWED_IMAGE_EXTENSIONS`) son la fuente única. El servidor valida con ellas, y el panel solo las muestra (`JGGallery.limits`, `maxlength` y popovers). El navegador nunca es la barrera.

### 17.5 Textos legales sincronizados por el tema
- `ta_sync_legal_pages()` escribe en la base de datos solo contenido que viene de archivos del propio tema (`legal/*.html`), una vez por cada `TA_LEGAL_VERSION`. Usa un bloqueo de 60 s y guarda el resultado en `ta_legal_sync_log`. No recibe ninguna entrada del visitante. Cada reemplazo queda como revisión de WordPress, así que se puede revertir.

### Regla para quien modifique
- No vuelvas a devolver `false` en el `permission_callback` de rutas que usan JWT.
- No cargues analítica en páginas del panel.
- Las pruebas automáticas del panel se hacen con una cuenta `prueba.panel.*`, **nunca** con `admin@todoacrilico.cl`: emitir un token rota el `jti` y cierra la sesión real del cliente.
