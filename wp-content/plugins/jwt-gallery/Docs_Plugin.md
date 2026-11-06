# JWT Gallery — Documentación del plugin

> **Ubicación (desde 2026-11-06):** este archivo vive en `wp-content/plugins/jwt-gallery/` y está en git. El repositorio es **público**: no escribas aquí contraseñas ni claves (van en `CREDENCIALES.local` y `wp-config.php`). Los ZIP excluyen `*.md`.
>
> **IA / desarrollador: lee esto primero.**
> - Reglas generales que no se deben romper: **`CLAUDE.md`**. Para el plugin, la **regla 7** (revocación por `jti` con un solo token válido por usuario, límites en constantes de `JG_REST`, popovers de ayuda, `require_auth()` que devuelve el error real, sin archivos huérfanos, pruebas nunca con `admin@`). También la regla 6: las cookies y claves del panel (`jg_session`) se declaran en la **Política de cookies** (`legal/politica-de-cookies.html` del tema). El comportamiento aprobado del panel está en **SPEC.md**, sección "Panel del catálogo".
> - **2.0.1 (2026-11-04):** popovers de ayuda, límites centralizados, modal "Sesión cerrada", sin archivos huérfanos y arreglo del ancho en móvil (§12.51–§12.53). Antes de eso no hubo cambios de código desde 2.0.0 (2026-10-20).
> - Las secciones §1.x tienen copias de código que pueden ser antiguas: **el archivo `.php` / `.js` real manda**. Lo más reciente está en §12.51–§12.55.
> - `wp-content/plugins/jwt-gallery/theme.md` es una bitácora interna **antigua** (1.4.8): solo histórica. No se incluye en el ZIP.
> - **ZIP:** `cd wp-content/plugins && zip -rq -X ../../jwt-gallery-X.Y.Z.zip jwt-gallery -x "*/.*" "*.md"` (el usuario suele renombrarlo a `TodoAcrilicoPanel-X.Y.Z.zip`). Si cambias `assets/js` o `assets/css`, sube `JG_VERSION` y la cabecera `Version:` juntos. Antes de entregar, corre la regresión de §12.53.

Plugin de WordPress que permite iniciar sesión mediante JWT (correo + contraseña) y gestionar una galería de imágenes organizada por categorías: subir, editar, eliminar y categorizar imágenes desde un panel autenticado, con una galería pública de solo lectura.

- **Versión:** 2.0.3 (2026-11-06; ver §12.54–§12.55). Producción: 2.0.1 hasta que se suba.
- **Namespace REST:** `jwt-gallery/v1`
- **Requiere:** PHP 7.2.24+, WordPress con usuarios ya existentes (usa la tabla `wp_users`)
- **Ubicación:** `wp-content/plugins/jwt-gallery/`

## Estado actual en este entorno (proyecto3)

| | |
|---|---|
| Plugin | **Activo** — se muestra como **"Todo Acrílico Panel"** en Escritorio → Plugins (nombre interno/carpeta sigue siendo `jwt-gallery`) |
| Página de login | `http://localhost/wordpress/proyecto3/index.php/galeria/` — shortcode `[jwt_gallery]`, solo muestra el formulario de inicio de sesión, sin nav ni footer del tema (§1.8) |
| Página del panel | `http://localhost/wordpress/proyecto3/index.php/galeria-panel/` — shortcode `[jwt_gallery_panel]`, requiere sesión, sin nav ni footer del tema (§1.8) |
| Panel admin del CPT | `http://localhost/wordpress/proyecto3/wp-admin/edit.php?post_type=jg_gallery_image` |
| Categorías | Se crean, renombran y borran desde el panel. Al 2026-11-04 en local: exhibir, informar, organizador, proteger y Trabajos Especiales (`auto`/"Autos" ya no existe). El menú "Catálogo" del tema y las pestañas del catálogo listan **todas**, también las vacías |
| Imágenes | Archivos **locales** en `wp-content/uploads/jg-gallery/` (51 del catálogo migradas + las que suba el usuario). El tema las sirve desde ahí (0 referencias a hostings externos) |
| Tipografía del panel | Manrope (la del theme), aplicada a todas las secciones del panel y al login (§12.35) |
| Seguridad | ✅ **Fixes #1-12 aplicados** (2026-10-20): credenciales BD, revocación jti, etc. |
| Revocación de tokens | ✅ **Activa** (Fix #6): tokens se revocan al logout, sesiones antiguas se invalidan |
| Producción | `https://todoacrilico.cl/wp/galeria/` y `/wp/galeria-panel/`. Cuentas: ver README.md (local) y §12.45 |
| Límites | Título 43, Descripción 120, Uso 43 y Medidas 20 caracteres; de 1 a 10 imágenes de hasta 8 MB (jpg, jpeg, png, gif, webp y avif). Constantes de `JG_REST`, enviadas al panel como `JGGallery.limits` |
| Banner de cookies / analítica | **No** se cargan en `/galeria/` ni en `/galeria-panel/` (decisión del tema, `ta_is_private_page()`) |
| Cuenta para pruebas | `prueba.panel.*@todoacrilico.cl` (por ejemplo, ID 4). **Nunca** `admin@todoacrilico.cl` (ID 3): emitir un token para esa cuenta cierra la sesión real del cliente |
| Pendiente | — |

Detalle completo de lo ejecutado en §12 "Bitácora de despliegue".

---

## 1. Estructura de archivos

```
jwt-gallery/
├── jwt-gallery.php                 # Bootstrap: constantes, includes, activación/desactivación
├── includes/
│   ├── class-jg-jwt.php            # Firma y verificación de tokens HS256
│   ├── class-jg-rest.php           # Endpoints REST: login, imágenes (CRUD), categorías (CRUD completo)
│   ├── class-jg-cpt.php            # Custom Post Type + taxonomía de categorías, siembra de categorías por defecto
│   ├── class-jg-shortcode.php      # Shortcodes [jwt_gallery] (login) y [jwt_gallery_panel] (panel)
│   └── class-jg-template.php       # Sirve /galeria/ y /galeria-panel/ en una plantilla en blanco (sin nav/footer del tema)
├── templates/
│   └── blank-page.php              # La plantilla en blanco que usa class-jg-template.php
└── assets/
    ├── js/jwt-gallery.js           # Lógica de frontend: routing, tabs, categorías, subir/editar/eliminar
    └── css/jwt-gallery.css         # Estilos, aislados bajo .jg-gallery
```

9 archivos en total. Detalle de cada uno a continuación.

---

## 1.1 `jwt-gallery.php` (raíz del plugin)

**Responsabilidad:** cabecera del plugin, constantes globales, carga las 5 clases de `includes/`, las inicializa y registra los hooks de activación/desactivación. En la activación registra el CPT y la taxonomía, y siembra las categorías por defecto si no existen todavía.

```php
<?php
/**
 * Plugin Name:       Todo Acrílico Panel
 * Description:       Login por JWT (correo + contraseña) y galería de imágenes por categorías, con edición y borrado. Shortcode [jwt_gallery] para el login y [jwt_gallery_panel] para el panel autenticado.
 * Version:           1.4.8
 * Requires at least: 6.0
 * Requires PHP:      7.2.24
 * Author:            dev@reparadores.cl
 * License:           GPL-2.0-or-later
 * Text Domain:       jwt-gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JG_VERSION', '1.4.8' );
define( 'JG_PATH', plugin_dir_path( __FILE__ ) );
define( 'JG_URL', plugin_dir_url( __FILE__ ) );

require_once JG_PATH . 'includes/class-jg-jwt.php';
require_once JG_PATH . 'includes/class-jg-rest.php';
require_once JG_PATH . 'includes/class-jg-cpt.php';
require_once JG_PATH . 'includes/class-jg-shortcode.php';
require_once JG_PATH . 'includes/class-jg-template.php';

JG_CPT::init();
JG_REST::init();
JG_Shortcode::init();
JG_Template::init();

register_activation_hook(
	__FILE__,
	function () {
		JG_JWT::get_secret();
		JG_CPT::register();
		JG_CPT::seed_default_categories();
		flush_rewrite_rules();
	}
);

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
```

---

## 1.2 `includes/class-jg-jwt.php`

**Responsabilidad:** todo lo relacionado a tokens JWT — emisión y verificación — implementado a mano (HS256) sin dependencias externas. Sin cambios respecto a la versión anterior.

- `get_secret()`: devuelve la constante `JG_JWT_SECRET` si está definida en `wp-config.php`; si no, genera (una sola vez) 32 bytes aleatorios con `random_bytes()` y los guarda en la opción `jg_jwt_secret`.
- `issue( $claims, $ttl )`: arma el header (`alg: HS256`), agrega `iat`/`exp`/`iss` al payload, codifica ambos en Base64URL y firma `header.payload` con `hash_hmac('sha256', ..., $secret, true)`.
- `verify( $token )`: separa el token en sus 3 partes, recalcula la firma esperada y la compara con `hash_equals()` (evita *timing attacks*), decodifica el payload y valida que no haya expirado (`exp`).

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Minimal HS256 JWT issue/verify. No external dependency.
 */
class JG_JWT {

	const OPTION_SECRET = 'jg_jwt_secret';

	public static function get_secret() {
		if ( defined( 'JG_JWT_SECRET' ) && JG_JWT_SECRET ) {
			return JG_JWT_SECRET;
		}

		$secret = get_option( self::OPTION_SECRET );
		if ( ! $secret ) {
			$secret = bin2hex( random_bytes( 32 ) );
			update_option( self::OPTION_SECRET, $secret, false );
		}

		return $secret;
	}

	private static function base64url_encode( $data ) {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}

	private static function base64url_decode( $data ) {
		$padded = str_pad( $data, strlen( $data ) % 4 === 0 ? strlen( $data ) : strlen( $data ) + 4 - strlen( $data ) % 4, '=' );
		return base64_decode( strtr( $padded, '-_', '+/' ) );
	}

	/**
	 * @param array $claims Custom claims (must include 'sub').
	 * @param int   $ttl    Seconds until expiry.
	 * @return string
	 */
	public static function issue( array $claims, $ttl = DAY_IN_SECONDS ) {
		$header = array(
			'alg' => 'HS256',
			'typ' => 'JWT',
		);

		$now             = time();
		$claims['iat']   = $now;
		$claims['exp']   = $now + $ttl;
		$claims['iss']   = home_url();

		$segments   = array();
		$segments[] = self::base64url_encode( wp_json_encode( $header ) );
		$segments[] = self::base64url_encode( wp_json_encode( $claims ) );

		$signing_input = implode( '.', $segments );
		$signature     = hash_hmac( 'sha256', $signing_input, self::get_secret(), true );
		$segments[]    = self::base64url_encode( $signature );

		return implode( '.', $segments );
	}

	/**
	 * @param string $token
	 * @return array|WP_Error Decoded claims on success.
	 */
	public static function verify( $token ) {
		if ( empty( $token ) || substr_count( $token, '.' ) !== 2 ) {
			return new WP_Error( 'jg_invalid_token', 'Token con formato inválido.', array( 'status' => 401 ) );
		}

		list( $header_b64, $payload_b64, $signature_b64 ) = explode( '.', $token );

		$signing_input      = $header_b64 . '.' . $payload_b64;
		$expected_signature = hash_hmac( 'sha256', $signing_input, self::get_secret(), true );
		$given_signature     = self::base64url_decode( $signature_b64 );

		if ( ! hash_equals( $expected_signature, $given_signature ) ) {
			return new WP_Error( 'jg_invalid_signature', 'Firma del token inválida.', array( 'status' => 401 ) );
		}

		$claims = json_decode( self::base64url_decode( $payload_b64 ), true );

		if ( ! is_array( $claims ) || empty( $claims['exp'] ) || empty( $claims['sub'] ) ) {
			return new WP_Error( 'jg_malformed_token', 'Token mal formado.', array( 'status' => 401 ) );
		}

		if ( time() > (int) $claims['exp'] ) {
			return new WP_Error( 'jg_expired_token', 'El token expiró, inicia sesión nuevamente.', array( 'status' => 401 ) );
		}

		return $claims;
	}
}
```

---

## 1.3 `includes/class-jg-cpt.php`

**Responsabilidad:** registra el Custom Post Type `jg_gallery_image` y la nueva taxonomía `jg_gallery_category` (no jerárquica, como las "tags" de WordPress), y siembra las 5 categorías por defecto pedidas (`organizador`, `proteger`, `trabajos-especiales`, `exhibir`, `informar`) la primera vez que se activa el plugin — `seed_default_categories()` es idempotente: usa `term_exists()` antes de insertar, así que no duplica categorías si se reactiva el plugin.

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JG_CPT {

	const TAXONOMY = 'jg_gallery_category';

	const DEFAULT_CATEGORIES = array( 'organizador', 'proteger', 'trabajos-especiales', 'exhibir', 'informar' );

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	public static function register() {
		register_post_type(
			'jg_gallery_image',
			array(
				'labels'          => array(
					'name'          => 'Imágenes de galería',
					'singular_name' => 'Imagen de galería',
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => 'dashicons-format-gallery',
				'supports'        => array( 'title', 'editor', 'author' ),
				'capability_type' => 'post',
				'map_meta_cap'    => true,
			)
		);

		register_taxonomy(
			self::TAXONOMY,
			'jg_gallery_image',
			array(
				'labels'            => array(
					'name'          => 'Categorías de galería',
					'singular_name' => 'Categoría de galería',
				),
				'public'            => false,
				'show_ui'           => true,
				'show_in_menu'      => true,
				'show_admin_column' => true,
				'hierarchical'      => false,
			)
		);
	}

	public static function seed_default_categories() {
		foreach ( self::DEFAULT_CATEGORIES as $name ) {
			if ( ! term_exists( $name, self::TAXONOMY ) ) {
				wp_insert_term( $name, self::TAXONOMY );
			}
		}
	}
}
```

`show_ui => true` en la taxonomía significa que también aparece un menú "Categorías de galería" en wp-admin (fuera del panel custom), útil como respaldo administrativo.

---

## 1.4 `includes/class-jg-rest.php`

**Responsabilidad:** todos los endpoints REST bajo el namespace `jwt-gallery/v1` — login, y el CRUD completo de imágenes y categorías. Es, con diferencia, el archivo más grande del plugin.

Funciones clave:
- `register_routes()`: da de alta las 7 rutas (detalle completo en §6).
- `authenticated_user()` / `require_auth()`: extraen el `Bearer <token>`, lo verifican con `JG_JWT::verify()` y resuelven el `WP_User` real.
- `login()`: valida credenciales con `wp_check_password()` y emite el token.
- `validate_image_url()`: valida un enlace de imagen individual (formato, esquema, extensión) — sin descargar el contenido (ver §9). **Desde 1.4.0 quedó como respaldo**: las fotos ya no se publican desde enlaces externos.
- `validate_image_urls()`: valida una **lista** de enlaces — cada imagen ("publicación") puede tener entre 1 y `MAX_IMAGES_PER_POST` (10) fotos. Rechaza listas vacías (`jg_no_image_url`) o con más de 10 elementos (`jg_too_many_images`).
- `prepare_file_params()` / `save_uploaded_image()` / `jg_uploads_dir()` / `collect_image_sources()`: cadena de la **subida por archivos reales** (desde 1.4.0). `save_uploaded_image()` mueve el `$_FILES` con `move_uploaded_file()` a `wp-content/uploads/jg-gallery/` usando `wp_upload_dir()` con rutas manuales (así no importa la opción `uploads_use_yearmonth_folders`), valida extensión dentro de `ALLOWED_IMAGE_EXTENSIONS` y tamaño ≤ `MAX_UPLOAD_BYTES` (8 MB). `collect_image_sources()` unifica las fuentes: archivos nuevos (`$_FILES`) + `existing` (IDs de archivos locales ya subidos) + `image_urls` (fallback de enlaces). Detalle en §12.31.
- `owned_post_or_error()`: helper de autorización — resuelve el post y devuelve `WP_Error` 404 si no existe o 403 si el usuario autenticado no es el autor. Lo usan `update_image()` y `delete_image()`.
- `assign_category( $post_id, $category_id )`: asigna (o quita, si `$category_id` es 0) la categoría de una imagen vía `wp_set_object_terms()`. La usan tanto `upload_image()` como `update_image()`.
- `get_measurements()` / `get_usage()`: leen y sanitizan los campos Medidas y **Uso** (`_jg_measurements` / `_jg_usage`). `get_usage()` limita a 43 caracteres (`jg_usage_too_long`).
- `upload_image()` / `update_image()` / `delete_image()`: CRUD de imágenes. Solo el autor de una imagen puede editarla o eliminarla. Las fotos se guardan como **múltiples filas** del mismo meta key `_jg_image_url` (`add_post_meta( $post_id, '_jg_image_url', $url, false )` — las URLs ahora son rutas locales del directorio `jg-gallery/`); `update_image()` borra las filas existentes (`delete_post_meta()`) y vuelve a insertarlas con la lista nueva (eliminando además los archivos locales que dejen de usarse).
- `list_images()`: listado público paginado, con **filtro opcional por categoría** vía el parámetro `category_id` — un ID numérico filtra por esa categoría, el valor especial `"none"` filtra las imágenes sin categoría (con un `tax_query` `NOT EXISTS`), y omitirlo devuelve todas. `total`/`pages` reflejan siempre el filtro aplicado, no el total global — es lo que permite paginar por categoría.
- `format_image()`: devuelve `images` (arreglo de URLs locales, vía `get_post_meta( $post->ID, '_jg_image_url', false )`), `author_id` (para que el frontend decida si mostrar el menú de acciones), `category` (`{id, name}` o `null`), `measurements` y `usage` (`_jg_measurements` / `_jg_usage`).
- `list_categories()` / `create_category()` / `update_category()` / `delete_category()` / `format_category()`: CRUD completo de categorías. Cada categoría incluye `count` — el número de imágenes publicadas en ella (`$term->count`, que WordPress mantiene automáticamente, sin consulta extra). **Desde 1.4.1 `delete_category()` borra también las "piezas" de esa categoría**: busca las publicaciones `jg_gallery_image` que tienen el término, elimina sus archivos locales con `delete_local_images()` y borra los posts con `wp_delete_post(..., true)` antes de llamar a `wp_delete_term()`; la respuesta incluye `pieces_deleted` (ver §12.33). Cualquier usuario autenticado puede crear, renombrar o eliminar categorías, igual que puede subir imágenes (ver limitación en §10). El helper interno `require_category_manager()` solo valida que el usuario esté autenticado vía JWT (no exige la capability `manage_categories` de WordPress) — ver §12.29.

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JG_REST {

	const NS = 'jwt-gallery/v1';

	const ALLOWED_IMAGE_EXTENSIONS = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif' );

	const MAX_IMAGES_PER_POST = 10;

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route(
			self::NS,
			'/login',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'login' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::NS,
			'/images',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'list_images' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'upload_image' ),
					'permission_callback' => array( __CLASS__, 'require_auth' ),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/images/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'update_image' ),
					'permission_callback' => array( __CLASS__, 'require_auth' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'delete_image' ),
					'permission_callback' => array( __CLASS__, 'require_auth' ),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/categories',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'list_categories' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'create_category' ),
					'permission_callback' => array( __CLASS__, 'require_auth' ),
				),
			)
		);

		register_rest_route(
			self::NS,
			'/categories/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'update_category' ),
					'permission_callback' => array( __CLASS__, 'require_auth' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'delete_category' ),
					'permission_callback' => array( __CLASS__, 'require_auth' ),
				),
			)
		);
	}

	private static function get_bearer_token( WP_REST_Request $request ) {
		$auth = $request->get_header( 'authorization' );
		if ( ! $auth || stripos( $auth, 'Bearer ' ) !== 0 ) {
			return '';
		}
		return trim( substr( $auth, 7 ) );
	}

	private static function authenticated_user( WP_REST_Request $request ) {
		$claims = JG_JWT::verify( self::get_bearer_token( $request ) );
		if ( is_wp_error( $claims ) ) {
			return $claims;
		}

		$user = get_user_by( 'id', (int) $claims['sub'] );
		if ( ! $user ) {
			return new WP_Error( 'jg_unknown_user', 'El usuario del token ya no existe.', array( 'status' => 401 ) );
		}

		return $user;
	}

	public static function require_auth( WP_REST_Request $request ) {
		return ! is_wp_error( self::authenticated_user( $request ) );
	}

	private static function client_ip() {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
	}

	public static function login( WP_REST_Request $request ) {
		$email    = sanitize_email( (string) $request->get_param( 'email' ) );
		$password = (string) $request->get_param( 'password' );

		if ( ! $email || ! is_email( $email ) || '' === $password ) {
			return new WP_Error( 'jg_bad_request', 'Correo y contraseña son obligatorios.', array( 'status' => 400 ) );
		}

		$user = get_user_by( 'email', $email );

		if ( ! $user || ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
			return new WP_Error( 'jg_invalid_credentials', 'Correo o contraseña incorrectos.', array( 'status' => 401 ) );
		}

		$ttl   = DAY_IN_SECONDS;
		$token = JG_JWT::issue(
			array(
				'sub'   => $user->ID,
				'email' => $user->user_email,
			),
			$ttl
		);

		return rest_ensure_response(
			array(
				'token'      => $token,
				'expires_in' => $ttl,
				'user'       => array(
					'id'    => $user->ID,
					'name'  => $user->display_name,
					'email' => $user->user_email,
				),
			)
		);
	}

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

	/**
	 * Validates a list of image URLs (a single "publicación" can have up to
	 * MAX_IMAGES_PER_POST photos — e.g. the same product from different angles).
	 *
	 * @param mixed $raw_urls Expected to be an array of strings.
	 * @return array|WP_Error Array of validated, sanitized URLs on success.
	 */
	private static function validate_image_urls( $raw_urls ) {
		if ( ! is_array( $raw_urls ) ) {
			$raw_urls = array();
		}

		$raw_urls = array_values( array_filter( array_map( 'trim', $raw_urls ) ) );

		if ( empty( $raw_urls ) ) {
			return new WP_Error( 'jg_no_image_url', 'Debes indicar al menos un enlace de imagen.', array( 'status' => 400 ) );
		}

		if ( count( $raw_urls ) > self::MAX_IMAGES_PER_POST ) {
			return new WP_Error(
				'jg_too_many_images',
				'Puedes subir un máximo de ' . self::MAX_IMAGES_PER_POST . ' imágenes por publicación.',
				array( 'status' => 400 )
			);
		}

		$validated = array();
		foreach ( $raw_urls as $raw_url ) {
			$url = self::validate_image_url( $raw_url );
			if ( is_wp_error( $url ) ) {
				return $url;
			}
			$validated[] = $url;
		}

		return $validated;
	}

	private static function assign_category( $post_id, $category_id ) {
		if ( $category_id > 0 && term_exists( $category_id, JG_CPT::TAXONOMY ) ) {
			wp_set_object_terms( $post_id, array( $category_id ), JG_CPT::TAXONOMY, false );
		} else {
			wp_set_object_terms( $post_id, array(), JG_CPT::TAXONOMY, false );
		}
	}

	private static function owned_post_or_error( $post_id, $user ) {
		$post = get_post( $post_id );
		if ( ! $post || 'jg_gallery_image' !== $post->post_type ) {
			return new WP_Error( 'jg_not_found', 'La imagen no existe.', array( 'status' => 404 ) );
		}

		if ( (int) $post->post_author !== (int) $user->ID ) {
			return new WP_Error( 'jg_forbidden', 'Solo puedes editar tus propias imágenes.', array( 'status' => 403 ) );
		}

		return $post;
	}

	public static function upload_image( WP_REST_Request $request ) {
		$user = self::authenticated_user( $request );
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$title       = sanitize_text_field( (string) $request->get_param( 'title' ) );
		$description = sanitize_textarea_field( (string) $request->get_param( 'description' ) );
		$raw_urls    = $request->get_param( 'image_urls' );
		$category_id = (int) $request->get_param( 'category_id' );

		if ( '' === $title ) {
			return new WP_Error( 'jg_no_title', 'El título es obligatorio.', array( 'status' => 400 ) );
		}

		$urls = self::validate_image_urls( $raw_urls );
		if ( is_wp_error( $urls ) ) {
			return $urls;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'jg_gallery_image',
				'post_title'   => $title,
				'post_content' => $description,
				'post_status'  => 'publish',
				'post_author'  => $user->ID,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return new WP_Error( 'jg_save_failed', 'No se pudo guardar la imagen.', array( 'status' => 500 ) );
		}

		foreach ( $urls as $url ) {
			add_post_meta( $post_id, '_jg_image_url', $url, false );
		}
		self::assign_category( $post_id, $category_id );

		return rest_ensure_response( self::format_image( get_post( $post_id ) ) );
	}

	public static function update_image( WP_REST_Request $request ) {
		$user = self::authenticated_user( $request );
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$post = self::owned_post_or_error( (int) $request->get_param( 'id' ), $user );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$title       = sanitize_text_field( (string) $request->get_param( 'title' ) );
		$description = sanitize_textarea_field( (string) $request->get_param( 'description' ) );
		$raw_urls    = $request->get_param( 'image_urls' );
		$category_id = (int) $request->get_param( 'category_id' );

		if ( '' === $title ) {
			return new WP_Error( 'jg_no_title', 'El título es obligatorio.', array( 'status' => 400 ) );
		}

		$urls = self::validate_image_urls( $raw_urls );
		if ( is_wp_error( $urls ) ) {
			return $urls;
		}

		wp_update_post(
			array(
				'ID'           => $post->ID,
				'post_title'   => $title,
				'post_content' => $description,
			)
		);

		delete_post_meta( $post->ID, '_jg_image_url' );
		foreach ( $urls as $url ) {
			add_post_meta( $post->ID, '_jg_image_url', $url, false );
		}
		self::assign_category( $post->ID, $category_id );

		return rest_ensure_response( self::format_image( get_post( $post->ID ) ) );
	}

	public static function delete_image( WP_REST_Request $request ) {
		$user = self::authenticated_user( $request );
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$post = self::owned_post_or_error( (int) $request->get_param( 'id' ), $user );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		wp_delete_post( $post->ID, true );

		return rest_ensure_response( array( 'deleted' => true, 'id' => $post->ID ) );
	}

	public static function list_images( WP_REST_Request $request ) {
		$per_page = min( 100, max( 1, (int) $request->get_param( 'per_page' ) ?: 50 ) );
		$page     = max( 1, (int) $request->get_param( 'page' ) ?: 1 );
		$category = $request->get_param( 'category_id' );

		$args = array(
			'post_type'      => 'jg_gallery_image',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( 'none' === $category ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => JG_CPT::TAXONOMY,
					'operator' => 'NOT EXISTS',
				),
			);
		} elseif ( is_numeric( $category ) && (int) $category > 0 ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => JG_CPT::TAXONOMY,
					'field'    => 'term_id',
					'terms'    => (int) $category,
				),
			);
		}

		$query = new WP_Query( $args );

		$items = array_map( array( __CLASS__, 'format_image' ), $query->posts );

		return rest_ensure_response(
			array(
				'items' => $items,
				'total' => (int) $query->found_posts,
				'pages' => (int) $query->max_num_pages,
			)
		);
	}

	private static function format_image( WP_Post $post ) {
		$urls  = get_post_meta( $post->ID, '_jg_image_url', false );
		$urls  = is_array( $urls ) ? array_values( $urls ) : array();
		$terms = get_the_terms( $post->ID, JG_CPT::TAXONOMY );

		$category = null;
		if ( $terms && ! is_wp_error( $terms ) && ! empty( $terms[0] ) ) {
			$category = array(
				'id'   => $terms[0]->term_id,
				'name' => $terms[0]->name,
			);
		}

		return array(
			'id'          => $post->ID,
			'title'       => get_the_title( $post ),
			'description' => $post->post_content,
			'images'      => $urls,
			'date'        => get_the_date( 'c', $post ),
			'author'      => get_the_author_meta( 'display_name', $post->post_author ),
			'author_id'   => (int) $post->post_author,
			'category'    => $category,
		);
	}

	private static function format_category( WP_Term $term ) {
		return array(
			'id'    => $term->term_id,
			'name'  => $term->name,
			'slug'  => $term->slug,
			'count' => (int) $term->count,
		);
	}

	public static function list_categories() {
		$terms = get_terms(
			array(
				'taxonomy'   => JG_CPT::TAXONOMY,
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		if ( is_wp_error( $terms ) ) {
			$terms = array();
		}

		return rest_ensure_response( array_map( array( __CLASS__, 'format_category' ), $terms ) );
	}

	public static function create_category( WP_REST_Request $request ) {
		$user = self::authenticated_user( $request );
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$name = sanitize_text_field( (string) $request->get_param( 'name' ) );
		if ( '' === $name ) {
			return new WP_Error( 'jg_no_category_name', 'El nombre de la categoría es obligatorio.', array( 'status' => 400 ) );
		}

		if ( get_term_by( 'name', $name, JG_CPT::TAXONOMY ) ) {
			return new WP_Error( 'jg_category_exists', 'Ya existe una categoría con ese nombre.', array( 'status' => 409 ) );
		}

		$result = wp_insert_term( $name, JG_CPT::TAXONOMY );
		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'jg_category_failed', $result->get_error_message(), array( 'status' => 500 ) );
		}

		$term = get_term( $result['term_id'], JG_CPT::TAXONOMY );

		return rest_ensure_response( self::format_category( $term ) );
	}

	public static function update_category( WP_REST_Request $request ) {
		$user = self::authenticated_user( $request );
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$term_id = (int) $request->get_param( 'id' );
		$name    = sanitize_text_field( (string) $request->get_param( 'name' ) );

		if ( '' === $name ) {
			return new WP_Error( 'jg_no_category_name', 'El nombre de la categoría es obligatorio.', array( 'status' => 400 ) );
		}

		$term = get_term( $term_id, JG_CPT::TAXONOMY );
		if ( ! $term || is_wp_error( $term ) ) {
			return new WP_Error( 'jg_category_not_found', 'La categoría no existe.', array( 'status' => 404 ) );
		}

		$result = wp_update_term( $term_id, JG_CPT::TAXONOMY, array( 'name' => $name ) );
		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'jg_category_failed', $result->get_error_message(), array( 'status' => 500 ) );
		}

		$updated = get_term( $term_id, JG_CPT::TAXONOMY );

		return rest_ensure_response( self::format_category( $updated ) );
	}

	public static function delete_category( WP_REST_Request $request ) {
		$user = self::authenticated_user( $request );
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$term_id = (int) $request->get_param( 'id' );
		$term    = get_term( $term_id, JG_CPT::TAXONOMY );

		if ( ! $term || is_wp_error( $term ) ) {
			return new WP_Error( 'jg_category_not_found', 'La categoría no existe.', array( 'status' => 404 ) );
		}

		$result = wp_delete_term( $term_id, JG_CPT::TAXONOMY );

		if ( is_wp_error( $result ) || false === $result ) {
			return new WP_Error( 'jg_category_failed', 'No se pudo eliminar la categoría.', array( 'status' => 500 ) );
		}

		return rest_ensure_response( array( 'deleted' => true, 'id' => $term_id ) );
	}
}
```

---

## 1.5 `includes/class-jg-shortcode.php`

**Responsabilidad:** registra **dos** shortcodes y el encolado compartido de CSS/JS:

- `[jwt_gallery]` → `render_login()`: solo el formulario de login. Es intencionalmente minimalista — todo lo demás vive en el panel.
- `[jwt_gallery_panel]` → `render_panel()`: layout de dos columnas al estilo de un panel de administración —
  - una **barra superior** (`jg-topbar`) con el usuario actual y "Cerrar sesión";
  - un **sidebar fijo** (`jg-sidebar`) con el formulario "Subir imagen" (título, descripción, uso, medidas, **archivos desde el dispositivo** vía el selector de imágenes de §1.6.3, categoría) y el gestor de categorías (listar, renombrar, eliminar, crear), siempre visibles, sin pestañas;
  - un **área de contenido** (`jg-content`) con una barra de tabs por categoría (`Todas` + cada categoría, con su contador) y, debajo, la grilla de imágenes de la categoría seleccionada más la paginación.

  Esto reemplaza el esquema anterior de 3 pestañas (Subir imagen / Categorías / Publicaciones) por un layout siempre visible, más parecido a un panel de administración real.

`register_assets()` le pasa al JS, vía `wp_localize_script()`, no solo la URL de la API sino también `loginUrl` y `panelUrl` — las URLs reales de ambas páginas, resueltas con `get_page_by_path()` sobre los slugs `galeria` y `galeria-panel` (con `home_url('/')` como respaldo si esas páginas no existieran). El JS usa esas URLs para redirigir entre ambas vistas (detalle en §5).

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JG_Shortcode {

	const LOGIN_PAGE_SLUG = 'galeria';

	const PANEL_PAGE_SLUG = 'galeria-panel';

	public static function init() {
		add_shortcode( 'jwt_gallery', array( __CLASS__, 'render_login' ) );
		add_shortcode( 'jwt_gallery_panel', array( __CLASS__, 'render_panel' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
	}

	private static function resolve_page_url( $slug, $fallback ) {
		$page = get_page_by_path( $slug );
		return $page ? get_permalink( $page ) : $fallback;
	}

	public static function register_assets() {
		wp_register_style( 'jg-gallery', JG_URL . 'assets/css/jwt-gallery.css', array(), JG_VERSION );
		wp_register_script( 'jg-gallery', JG_URL . 'assets/js/jwt-gallery.js', array(), JG_VERSION, true );
		wp_localize_script(
			'jg-gallery',
			'JGGallery',
			array(
				'restUrl'  => esc_url_raw( rest_url( 'jwt-gallery/v1' ) ),
				'loginUrl' => esc_url_raw( self::resolve_page_url( self::LOGIN_PAGE_SLUG, home_url( '/' ) ) ),
				'panelUrl' => esc_url_raw( self::resolve_page_url( self::PANEL_PAGE_SLUG, home_url( '/' ) ) ),
			)
		);
	}

	private static function password_field_markup() {
		?>
		<label>
			Contraseña
			<span class="jg-password-field">
				<input type="password" name="password" required autocomplete="current-password">
				<button type="button" class="jg-password-toggle" data-jg-toggle-password aria-label="Mostrar contraseña" aria-pressed="false">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
				</button>
			</span>
		</label>
		<?php
	}

	public static function render_login() {
		wp_enqueue_style( 'jg-gallery' );
		wp_enqueue_script( 'jg-gallery' );

		ob_start();
		?>
		<div class="jg-gallery" data-jg-root data-jg-view="login">
			<section class="jg-panel jg-panel--narrow">
				<form class="jg-form" data-jg-login>
					<h3>Iniciar sesión</h3>
					<label>
						Correo electrónico
						<input type="email" name="email" required autocomplete="username">
					</label>
					<?php self::password_field_markup(); ?>
					<button type="submit">Ingresar</button>
					<p class="jg-message jg-message--error" data-jg-login-error hidden></p>
				</form>
			</section>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_panel() {
		wp_enqueue_style( 'jg-gallery' );
		wp_enqueue_script( 'jg-gallery' );

		ob_start();
		?>
		<div class="jg-gallery jg-gallery--panel" data-jg-root data-jg-view="panel">

			<header class="jg-topbar">
				<span class="jg-topbar-user" data-jg-user></span>
				<button type="button" class="jg-button-secondary" data-jg-logout>Cerrar sesión</button>
			</header>

			<div class="jg-layout">

				<aside class="jg-sidebar">
					<div class="jg-panel jg-sidebar-block">
						<h3>Subir imagen</h3>
						<p class="jg-sidebar-hint">Sube imágenes desde tu dispositivo, se guardan dentro de WordPress</p>
						<form class="jg-form" data-jg-upload>
							<label>
								Título
								<input type="text" name="title" maxlength="120" placeholder="Ej. Vitrina modular 03" required>
							</label>
							<label>
								Descripción
								<textarea name="description" maxlength="600" rows="3" placeholder="Breve descripción de la pieza…"></textarea>
							</label>
							<label>
								Imágenes (hasta 10, misma pieza en distintos ángulos)
							</label>
							<div data-jg-upload-images-mount></div>
							<label>
								Categoría
								<select name="category_id" data-jg-category-select>
									<option value="">Sin categoría</option>
								</select>
							</label>
							<button type="submit">Publicar imagen</button>
							<p class="jg-message jg-message--error" data-jg-upload-error hidden></p>
							<p class="jg-message jg-message--success" data-jg-upload-success hidden></p>
						</form>
					</div>

					<div class="jg-panel jg-sidebar-block jg-category-manager">
						<h3>Categorías</h3>
						<p class="jg-sidebar-hint" data-jg-category-count></p>
						<ul class="jg-category-list" data-jg-category-list></ul>
						<form class="jg-form jg-form--inline" data-jg-category-add>
							<input type="text" name="name" placeholder="Nueva categoría" maxlength="60" required>
							<button type="submit">Agregar</button>
						</form>
						<p class="jg-message jg-message--error" data-jg-category-error hidden></p>
					</div>
				</aside>

				<section class="jg-content">
					<nav class="jg-category-tabs" data-jg-category-tabs aria-label="Filtrar por categoría"></nav>
					<div data-jg-images-grid>
						<p class="jg-empty" data-jg-status>Cargando imágenes…</p>
					</div>
					<div data-jg-pagination></div>
				</section>

			</div>

		</div>
		<?php
		return ob_get_clean();
	}
}
```

---

## 1.6 `assets/js/jwt-gallery.js`

**Responsabilidad:** toda la interactividad, JavaScript vanilla sin dependencias. Ahora tiene dos puntos de entrada según la vista (`data-jg-view="login"` o `"panel"`), detectados al final del archivo:

```js
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('[data-jg-root]').forEach(function (root) {
		var view = root.getAttribute('data-jg-view');
		if (view === 'panel') {
			initPanelView(root);
		} else {
			initLoginView(root);
		}
	});
});
```

**Vista login** (`initLoginView`): si ya hay una sesión válida en `localStorage`, redirige de inmediato al panel (`window.location.href = PANEL_URL`) sin mostrar el formulario. Si no, engancha el submit: al loguear con éxito, guarda la sesión y **redirige** al panel (a diferencia de la versión anterior, que solo mostraba/ocultaba secciones en la misma página).

**Vista panel** (`initPanelView`): si no hay sesión válida, redirige de inmediato al login. Si la hay, mantiene un objeto `state` en memoria — `{ categories, allTotal, selectedCategory, page, perPage, images, total }` — que comparten el resto de las funciones. `selectedCategory` vale `'all'`, `'none'` o el ID de una categoría (como string); `page`/`perPage` gobiernan la paginación del lado del servidor (`perPage` fijo en `9`, constante `PER_PAGE` al inicio del archivo).

- Pinta el nombre del usuario en la topbar y engancha "Cerrar sesión" (borra la sesión y redirige al login).
- `loadCategories()`: trae `GET /categories` (con sus `count`), llena el `<select>` del formulario de subida (`renderCategorySelect`) y la lista del sidebar (`renderCategoryManagerList`). Después hace una segunda llamada liviana `GET /images?per_page=1` **solo** para leer el `total` global (`state.allTotal`) — necesario porque la cuenta de "Todas" no es la suma de las categorías (puede haber imágenes sin categoría) — y con eso arma la barra de tabs (`renderCategoryTabs`).
- `initCategoryManager()`: engancha el formulario "Nueva categoría" del sidebar (`POST /categories`).
- `renderCategoryManagerList()`: por cada categoría arma una fila con nombre + contador y dos botones de ícono — un lápiz que la convierte en un `<input>` editable + botón "Guardar" (`PUT /categories/{id}`), y un tacho de basura que pide confirmación (el mensaje avisa cuántas **piezas se eliminarán** junto con la categoría y sus archivos, desde 1.4.1) y hace `DELETE /categories/{id}`; tras eliminarla refresca la grilla porque también desaparecen las piezas. Ambos íconos se ocultan mientras la fila está en modo edición.
- `renderCategoryTabs()`: arma la barra de tabs por categoría en el área de contenido — `Todas` (con `state.allTotal`), cada categoría (con su `count`) y `Sin categoría` al final **solo** si `allTotal` menos la suma de los `count` de las categorías es mayor que cero. Al hacer clic en un tab, resetea `state.page = 1` y llama a `loadImages()`.
- `initUploadForm()`: monta el **selectordor de archivos** `buildImagePicker()` (§1.6.3) en `[data-jg-upload-images-mount]`, y al enviar arma `FormData` con `title`, `description`, `usage`, `measurements`, `category_id` y los `$_FILES` (título máx. 43, descripción máx. 120, uso máx. 43 — ver §12.34). Sube la publicación (`POST /images`); al terminar resetea el picker desde cero y refresca categorías **e** imágenes (`refreshAfterMutation`), porque subir una imagen puede cambiar los contadores de la barra de tabs.
- `loadImages()`: trae `GET /images` **ya paginado y filtrado por categoría en el servidor** (`?page=N&per_page=9&category_id=...`), guarda `items`/`total` en `state`, pinta la grilla (`renderImagesGrid`) y la paginación (`renderPaginationControl`, §1.6.1). A diferencia de la versión anterior, cambiar de página o de categoría **sí** vuelve a pedirle datos al servidor — no hay caché completa en el cliente.
- `buildImageCard()`: arma cada tarjeta con `createElement`/`textContent` (nunca `innerHTML` para datos de usuario). Si `item.images` tiene más de una URL, agrega un mini-carrusel (flechas ‹/› + contador "2/4", ver §1.6.4) — si tiene solo una, muestra la imagen sola, sin controles. La tarjeta muestra categoría (badge) y las líneas "Medidas:" y "Uso:" si existen. Si `item.author_id` coincide con el usuario de la sesión actual, agrega dos botones de ícono superpuestos en la esquina de la imagen — un lápiz (Editar) y un tacho de basura rojo (Eliminar) — si no, la tarjeta es de solo lectura, sin esos íconos.
- `buildEditForm()`: el formulario de edición inline (título, descripción, uso, medidas, categoría, y el **selector de imágenes precargado con `item.images`**) que reemplaza la vista de la tarjeta al hacer clic en el ícono de lápiz; al guardar hace la edición (`POST /images/{id}` con `X-HTTP-Method-Override: PUT`, §12.32) con los archivos modificados y llama a `refreshAfterMutation()`.
- El ícono de tacho de basura pide confirmación con el modal reutilizable (`showConfirmModal()`, §1.6.2) y hace `DELETE /images/{id}`, seguido también de `refreshAfterMutation()`.
- `refreshAfterMutation()`: helper compartido por subir/editar/eliminar imagen y por renombrar categoría — vuelve a pedir categorías e imágenes, así los contadores de la barra de tabs y el sidebar quedan siempre al día tras cualquier cambio.

### 1.6.1 Componente de paginación reutilizable

`renderPaginationControl(container, opts)` es una función independiente del resto del panel — no conoce imágenes ni categorías, solo números. Recibe el contenedor DOM y `{ page, perPage, total, onChange }`, y pinta el texto "Mostrando X-Y de Z" más los controles Anterior/Siguiente y los números de página (con `paginationPageList()` recortando la lista a `1 … n-1 n n+1 … total` cuando hay más de 7 páginas, en vez de listarlas todas). Al hacer clic en cualquier control, solo llama a `opts.onChange(nuevaPágina)` — quien la usa decide qué hacer con ese número. `loadImages()` la usa así:

```js
renderPaginationControl(root.querySelector('[data-jg-pagination]'), {
	page: state.page,
	perPage: state.perPage,
	total: state.total,
	onChange: function (page) {
		state.page = page;
		loadImages(root, state);
	},
});
```

Al no depender de `state` ni del DOM del panel más allá del contenedor que se le pasa, se puede reutilizar tal cual para paginar cualquier otra lista del plugin en el futuro.

### 1.6.2 Componente de modal de confirmación reutilizable

`showConfirmModal(message, options)` reemplaza los `window.confirm()` nativos del navegador (el cuadro genérico "localhost dice", sin estilo propio) por un diálogo con el mismo diseño visual del resto del panel. Es independiente de lo que se esté confirmando — solo recibe un mensaje y un objeto de opciones (`title`, `confirmLabel`, `cancelLabel`, `danger`), y devuelve una **Promise que resuelve a `true`/`false`** según el botón que el usuario haya presionado:

```js
showConfirmModal('Esta acción no se puede deshacer.', {
	title: 'Eliminar imagen "Vidrio de prueba 11"',
	confirmLabel: 'Eliminar',
	danger: true,
}).then(function (confirmed) {
	if (!confirmed) return;
	// ... continuar con la eliminación
});
```

Construye un overlay a pantalla completa (`.jg-modal-overlay`, `position: fixed; inset: 0;`) con la tarjeta del modal centrada, y lo agrega directamente a `document.body` — no dentro de `.jg-gallery` — por eso define sus propias variables CSS (`--jgm-*`) en vez de depender de las de `.jg-gallery` (`--jg-*`), que no existirían fuera de ese contenedor. Se cierra con click en "Cancelar", click en "Aceptar"/"Eliminar", click fuera de la tarjeta (en el overlay), o la tecla `Escape`; el botón de confirmar recibe el foco automáticamente al abrirse.

**Los dos usos actuales**, ambos reemplazando un `window.confirm()` que existía antes:
- Eliminar una imagen (`buildImageCard()`): título con el nombre de la imagen, botón "Eliminar" en rojo (`danger: true`).
- Eliminar una categoría (`renderCategoryManagerList()`): título con el nombre de la categoría, y el mensaje avisa cuántas imágenes quedarán sin categoría si `cat.count > 0`.

Como con la paginación (§1.6.1), no depende de `state` ni de ningún dato del plugin — se puede reutilizar para cualquier otra confirmación que se necesite en el futuro.

### 1.6.3 Componente reutilizable: selector de imágenes de archivo

> **Nota de vigencia:** desde la versión **1.4.0** el repetidor de enlaces externos (`buildImageUrlRepeater`, descrito aquí abajo con su código) quedó **obsoleto** en el panel: fue reemplazado por `buildImagePicker()`, un selector de archivos desde el dispositivo. El código del repetidor y su CSS (`jg-url-repeater*`) sobreviven en el JS/CSS como **fallback/legacy** para envíos vía `image_urls`, pero la UI ya no los usa. Ver §12.31 y §12.32.

Cada "publicación" puede tener entre 1 y `MAX_IMAGES_PER_POST` (10, misma constante que valida el servidor en §1.4) fotos del mismo objeto — pensado para, por ejemplo, subir un mueble desde varios ángulos con un solo título/descripción/categoría. `buildImagePicker` es el componente que arma esa lista de archivos:

- Un campo `<input type="file" multiple>` con un botón **"+ Agregar otra imagen"** permite elegir fotos desde el dispositivo (hasta 10, y 8 MB por archivo — mismo límite que valida el servidor).
- Cada fotografía elegida se lista como una mini-tarjeta con vista previa (URL de objeto local) y un botón `×` que la quita. No se puede quitar la última: toda publicación exige al menos 1 imagen.
- Un contador de texto ("3/10 imágenes") se actualiza en cada cambio.
- Sin selección, arranca vacío (subir imagen); al editar, se precarga con los archivos existentes de `item.images` (que se envían como `existing[]` junto a los `$_FILES` nuevos).

Se usa así en ambos formularios:

```js
// Subir imagen (class-jg-shortcode.php ya trae el <div data-jg-upload-images-mount>)
var repeater = buildImageUrlRepeater();
mount.appendChild(repeater);
// ...
var imageUrls = repeater.getValues();

// Editar imagen (precargado con las fotos existentes)
var imagesRepeater = buildImageUrlRepeater(item.images);
```

### 1.6.4 Mini-carrusel en la tarjeta

Cuando `item.images.length > 1`, `buildImageCard()` agrega controles de navegación sobre la imagen: dos botones de flecha (`‹`/`›`, ocultos hasta el hover como los íconos de editar/eliminar) que avanzan/retroceden `currentIndex` con wraparound (de la última foto vuelve a la primera y viceversa), y un contador siempre visible en la esquina inferior derecha ("2/4"). No hay librería de carrusel de por medio — es `img.src = images[currentIndex]` cada vez que se hace clic, sin transición ni precarga. Si la publicación tiene una sola foto, no se agrega ningún control — se ve igual que en la versión anterior del plugin.

```javascript
(function () {
	'use strict';

	var STORAGE_KEY = 'jg_session';
	var REST_URL = window.JGGallery ? window.JGGallery.restUrl.replace(/\/$/, '') : '';
	var LOGIN_URL = window.JGGallery ? window.JGGallery.loginUrl : '';
	var PANEL_URL = window.JGGallery ? window.JGGallery.panelUrl : '';
	var PER_PAGE = 9;
	var MAX_IMAGES_PER_POST = 10;

	var EYE_ICON =
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
	var EYE_OFF_ICON =
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a20.6 20.6 0 0 1 5.06-6.06M9.9 4.24A10.4 10.4 0 0 1 12 4c7 0 11 8 11 8a20.6 20.6 0 0 1-4.06 5.06M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
	var PENCIL_ICON =
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
	var TRASH_ICON =
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>';
	var CHEVRON_LEFT_ICON =
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>';
	var CHEVRON_RIGHT_ICON =
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>';

	// ---- session helpers ----------------------------------------------

	function getSession() {
		try {
			var raw = window.localStorage.getItem(STORAGE_KEY);
			if (!raw) return null;
			var session = JSON.parse(raw);
			if (!session || !session.token || !session.expiresAt) return null;
			if (Date.now() >= session.expiresAt) {
				window.localStorage.removeItem(STORAGE_KEY);
				return null;
			}
			return session;
		} catch (e) {
			return null;
		}
	}

	function setSession(session) {
		window.localStorage.setItem(STORAGE_KEY, JSON.stringify(session));
	}

	function clearSession() {
		window.localStorage.removeItem(STORAGE_KEY);
	}

	function authHeaders(session, extra) {
		var headers = { Authorization: 'Bearer ' + session.token };
		if (extra) {
			for (var key in extra) {
				if (Object.prototype.hasOwnProperty.call(extra, key)) headers[key] = extra[key];
			}
		}
		return headers;
	}

	function apiFetch(path, options) {
		return fetch(REST_URL + path, options).then(function (res) {
			return res.json().then(
				function (data) {
					return { ok: res.ok, data: data };
				},
				function () {
					return { ok: false, data: { message: 'Respuesta inválida del servidor.' } };
				}
			);
		});
	}

	// ---- small UI helpers ----------------------------------------------

	function showMessage(el, text) {
		if (!el) return;
		if (!text) {
			el.hidden = true;
			el.textContent = '';
			return;
		}
		el.textContent = text;
		el.hidden = false;
	}

	function formatDate(iso) {
		try {
			return new Date(iso).toLocaleDateString('es-CL', { year: 'numeric', month: 'short', day: 'numeric' });
		} catch (e) {
			return '';
		}
	}

	function capitalize(text) {
		return text ? text.charAt(0).toUpperCase() + text.slice(1) : text;
	}

	function handlePasswordToggles(root) {
		root.querySelectorAll('[data-jg-toggle-password]').forEach(function (btn) {
			var input = btn.previousElementSibling;
			if (!input) return;

			btn.addEventListener('click', function () {
				var shown = input.type === 'text';
				input.type = shown ? 'password' : 'text';
				btn.innerHTML = shown ? EYE_ICON : EYE_OFF_ICON;
				btn.setAttribute('aria-pressed', shown ? 'false' : 'true');
				btn.setAttribute('aria-label', shown ? 'Mostrar contraseña' : 'Ocultar contraseña');
			});
		});
	}

	// ---- reusable pagination component ---------------------------------

	/**
	 * Renders a "Mostrando X-Y de Z" line + Anterior/Siguiente/page-number controls
	 * into `container`. Purely presentational — knows nothing about images or
	 * categories, just page/perPage/total numbers and an onChange(page) callback.
	 */
	function renderPaginationControl(container, opts) {
		container.innerHTML = '';

		var totalPages = Math.max(1, Math.ceil(opts.total / opts.perPage));
		if (opts.total <= 0) return;

		var wrap = document.createElement('div');
		wrap.className = 'jg-pagination';

		var start = (opts.page - 1) * opts.perPage + 1;
		var end = Math.min(opts.page * opts.perPage, opts.total);

		var info = document.createElement('p');
		info.className = 'jg-pagination-info';
		info.textContent = 'Mostrando ' + start + '-' + end + ' de ' + opts.total;
		wrap.appendChild(info);

		if (totalPages > 1) {
			var nav = document.createElement('div');
			nav.className = 'jg-pagination-nav';

			var prevBtn = document.createElement('button');
			prevBtn.type = 'button';
			prevBtn.className = 'jg-pagination-btn';
			prevBtn.textContent = '‹ Anterior';
			prevBtn.disabled = opts.page <= 1;
			prevBtn.addEventListener('click', function () {
				opts.onChange(opts.page - 1);
			});
			nav.appendChild(prevBtn);

			paginationPageList(opts.page, totalPages).forEach(function (entry) {
				if (entry === '…') {
					var ellipsis = document.createElement('span');
					ellipsis.className = 'jg-pagination-ellipsis';
					ellipsis.textContent = '…';
					nav.appendChild(ellipsis);
					return;
				}

				var pageBtn = document.createElement('button');
				pageBtn.type = 'button';
				pageBtn.className = 'jg-pagination-page' + (entry === opts.page ? ' is-active' : '');
				pageBtn.textContent = String(entry);
				pageBtn.addEventListener('click', function () {
					opts.onChange(entry);
				});
				nav.appendChild(pageBtn);
			});

			var nextBtn = document.createElement('button');
			nextBtn.type = 'button';
			nextBtn.className = 'jg-pagination-btn';
			nextBtn.textContent = 'Siguiente ›';
			nextBtn.disabled = opts.page >= totalPages;
			nextBtn.addEventListener('click', function () {
				opts.onChange(opts.page + 1);
			});
			nav.appendChild(nextBtn);

			wrap.appendChild(nav);
		}

		container.appendChild(wrap);
	}

	function paginationPageList(current, total) {
		if (total <= 7) {
			var all = [];
			for (var p = 1; p <= total; p++) all.push(p);
			return all;
		}

		var pages = [1];
		var start = Math.max(2, current - 1);
		var end = Math.min(total - 1, current + 1);

		if (start > 2) pages.push('…');
		for (var i = start; i <= end; i++) pages.push(i);
		if (end < total - 1) pages.push('…');
		pages.push(total);

		return pages;
	}

	// ---- reusable confirm modal ------------------------------------------

	/**
	 * Custom confirmation dialog to replace window.confirm() (which shows an
	 * ugly, unstyled "localhost dice" browser prompt). Returns a Promise that
	 * resolves to true/false — doesn't know anything about images or
	 * categories, just a message and two buttons.
	 *
	 * @param {string} message
	 * @param {{title?: string, confirmLabel?: string, cancelLabel?: string, danger?: boolean}} [options]
	 * @returns {Promise<boolean>}
	 */
	function showConfirmModal(message, options) {
		options = options || {};

		return new Promise(function (resolve) {
			var overlay = document.createElement('div');
			overlay.className = 'jg-modal-overlay';

			var modal = document.createElement('div');
			modal.className = 'jg-modal';
			modal.setAttribute('role', 'alertdialog');
			modal.setAttribute('aria-modal', 'true');

			if (options.title) {
				var title = document.createElement('h3');
				title.className = 'jg-modal-title';
				title.textContent = options.title;
				modal.appendChild(title);
			}

			var text = document.createElement('p');
			text.className = 'jg-modal-message';
			text.textContent = message;
			modal.appendChild(text);

			var actions = document.createElement('div');
			actions.className = 'jg-modal-actions';

			var cancelBtn = document.createElement('button');
			cancelBtn.type = 'button';
			cancelBtn.className = 'jg-button-secondary';
			cancelBtn.textContent = options.cancelLabel || 'Cancelar';

			var confirmBtn = document.createElement('button');
			confirmBtn.type = 'button';
			confirmBtn.className = options.danger ? 'jg-modal-confirm-danger' : '';
			confirmBtn.textContent = options.confirmLabel || 'Aceptar';

			function close(result) {
				document.removeEventListener('keydown', onKeydown);
				overlay.remove();
				resolve(result);
			}

			function onKeydown(e) {
				if (e.key === 'Escape') close(false);
			}

			cancelBtn.addEventListener('click', function () {
				close(false);
			});
			confirmBtn.addEventListener('click', function () {
				close(true);
			});
			overlay.addEventListener('click', function (e) {
				if (e.target === overlay) close(false);
			});
			document.addEventListener('keydown', onKeydown);

			actions.appendChild(cancelBtn);
			actions.appendChild(confirmBtn);
			modal.appendChild(actions);
			overlay.appendChild(modal);
			document.body.appendChild(overlay);

			confirmBtn.focus();
		});
	}

	// ---- reusable image-URL repeater --------------------------------------

	/**
	 * A "add up to N image links" form component — used both when uploading a
	 * new publication and when editing one, since both need the same
	 * "one or more photos of the same thing" input. Doesn't know about title,
	 * description, category, or any REST endpoint — just manages a list of
	 * URL inputs and exposes their current values via .getValues().
	 *
	 * @param {string[]} [initialUrls] Pre-fill with these URLs (edit mode).
	 * @returns {HTMLElement} container with a .getValues(): string[] method.
	 */
	function buildImageUrlRepeater(initialUrls) {
		var container = document.createElement('div');
		container.className = 'jg-url-repeater';

		var rowsWrap = document.createElement('div');
		rowsWrap.className = 'jg-url-repeater-rows';
		container.appendChild(rowsWrap);

		var addBtn = document.createElement('button');
		addBtn.type = 'button';
		addBtn.className = 'jg-url-repeater-add';
		addBtn.textContent = '+ Agregar otra imagen';
		container.appendChild(addBtn);

		var countLabel = document.createElement('p');
		countLabel.className = 'jg-url-repeater-count';
		container.appendChild(countLabel);

		function updateState() {
			var rows = rowsWrap.querySelectorAll('.jg-url-repeater-row');
			countLabel.textContent = rows.length + '/' + MAX_IMAGES_PER_POST + ' imágenes';
			addBtn.hidden = rows.length >= MAX_IMAGES_PER_POST;
			rowsWrap.querySelectorAll('.jg-url-repeater-remove').forEach(function (btn) {
				btn.hidden = rows.length <= 1;
			});
		}

		function addRow(value) {
			if (rowsWrap.querySelectorAll('.jg-url-repeater-row').length >= MAX_IMAGES_PER_POST) return;

			var row = document.createElement('div');
			row.className = 'jg-url-repeater-row';

			var input = document.createElement('input');
			input.type = 'url';
			input.placeholder = 'https://ejemplo.com/imagen.jpg';
			input.value = value || '';

			var removeBtn = document.createElement('button');
			removeBtn.type = 'button';
			removeBtn.className = 'jg-url-repeater-remove';
			removeBtn.setAttribute('aria-label', 'Quitar esta imagen');
			removeBtn.textContent = '×';
			removeBtn.addEventListener('click', function () {
				row.remove();
				updateState();
			});

			row.appendChild(input);
			row.appendChild(removeBtn);
			rowsWrap.appendChild(row);
			updateState();
		}

		addBtn.addEventListener('click', function () {
			addRow('');
		});

		(initialUrls && initialUrls.length ? initialUrls : ['']).forEach(addRow);

		container.getValues = function () {
			return Array.prototype.slice
				.call(rowsWrap.querySelectorAll('input'))
				.map(function (input) {
					return input.value.trim();
				})
				.filter(function (value) {
					return value;
				});
		};

		return container;
	}

	// ---- LOGIN view ------------------------------------------------------

	function initLoginView(root) {
		if (getSession()) {
			window.location.href = PANEL_URL;
			return;
		}

		var form = root.querySelector('[data-jg-login]');
		handlePasswordToggles(root);

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var errorEl = form.querySelector('[data-jg-login-error]');
			showMessage(errorEl, '');

			var email = form.email.value.trim();
			var password = form.password.value;
			var submitBtn = form.querySelector('button[type="submit"]');
			submitBtn.disabled = true;

			apiFetch('/login', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ email: email, password: password }),
			})
				.then(function (result) {
					submitBtn.disabled = false;
					if (!result.ok) {
						showMessage(errorEl, result.data.message || 'No se pudo iniciar sesión.');
						return;
					}
					setSession({
						token: result.data.token,
						expiresAt: Date.now() + result.data.expires_in * 1000,
						user: result.data.user,
					});
					window.location.href = PANEL_URL;
				})
				.catch(function () {
					submitBtn.disabled = false;
					showMessage(errorEl, 'Error de red. Intenta nuevamente.');
				});
		});
	}

	// ---- PANEL view --------------------------------------------------------

	function initPanelView(root) {
		var session = getSession();
		if (!session) {
			window.location.href = LOGIN_URL;
			return;
		}

		var state = {
			categories: [],
			allTotal: 0,
			selectedCategory: 'all',
			page: 1,
			perPage: PER_PAGE,
			images: [],
			total: 0,
		};

		root.querySelector('[data-jg-user]').textContent = session.user.name + ' (' + session.user.email + ')';

		root.querySelector('[data-jg-logout]').addEventListener('click', function () {
			clearSession();
			window.location.href = LOGIN_URL;
		});

		initCategoryManager(root, state);
		initUploadForm(root, state);

		loadCategories(root, state).then(function () {
			loadImages(root, state);
		});
	}

	// ---- categorías: select, gestor lateral, tabs con contador ---------------

	function loadCategories(root, state) {
		return apiFetch('/categories')
			.then(function (result) {
				if (result.ok) {
					state.categories = result.data;
				}
				renderCategorySelect(root, state.categories);
				renderCategoryManagerList(root, state);
				return apiFetch('/images?per_page=1&page=1');
			})
			.then(function (totalsResult) {
				if (totalsResult && totalsResult.ok) {
					state.allTotal = totalsResult.data.total;
				}
				renderCategoryTabs(root, state);
			});
	}

	function renderCategorySelect(root, categories) {
		root.querySelectorAll('[data-jg-category-select]').forEach(function (select) {
			var current = select.value;
			select.innerHTML = '';

			var noneOpt = document.createElement('option');
			noneOpt.value = '';
			noneOpt.textContent = 'Sin categoría';
			select.appendChild(noneOpt);

			categories.forEach(function (cat) {
				var opt = document.createElement('option');
				opt.value = String(cat.id);
				opt.textContent = capitalize(cat.name);
				select.appendChild(opt);
			});

			if (current) select.value = current;
		});
	}

	function initCategoryManager(root, state) {
		var addForm = root.querySelector('[data-jg-category-add]');
		var errorEl = root.querySelector('[data-jg-category-error]');

		addForm.addEventListener('submit', function (e) {
			e.preventDefault();
			showMessage(errorEl, '');

			var session = getSession();
			if (!session) {
				window.location.href = LOGIN_URL;
				return;
			}

			var name = addForm.name.value.trim();
			if (!name) return;

			apiFetch('/categories', {
				method: 'POST',
				headers: authHeaders(session, { 'Content-Type': 'application/json' }),
				body: JSON.stringify({ name: name }),
			}).then(function (result) {
				if (!result.ok) {
					showMessage(errorEl, result.data.message || 'No se pudo crear la categoría.');
					return;
				}
				addForm.reset();
				loadCategories(root, state);
			});
		});
	}

	function renderCategoryManagerList(root, state) {
		var list = root.querySelector('[data-jg-category-list]');
		var countLabel = root.querySelector('[data-jg-category-count]');
		if (countLabel) {
			countLabel.textContent = state.categories.length + (state.categories.length === 1 ? ' activa' : ' activas');
		}

		list.innerHTML = '';

		state.categories.forEach(function (cat) {
			var li = document.createElement('li');
			li.className = 'jg-category-item';

			var nameSpan = document.createElement('span');
			nameSpan.className = 'jg-category-name';
			nameSpan.textContent = capitalize(cat.name);

			var countSpan = document.createElement('span');
			countSpan.className = 'jg-category-count';
			countSpan.textContent = String(cat.count);

			var renameInput = document.createElement('input');
			renameInput.type = 'text';
			renameInput.className = 'jg-category-rename-input';
			renameInput.maxLength = 60;
			renameInput.value = cat.name;
			renameInput.hidden = true;

			var editBtn = document.createElement('button');
			editBtn.type = 'button';
			editBtn.className = 'jg-category-edit-btn';
			editBtn.setAttribute('aria-label', 'Renombrar ' + cat.name);
			editBtn.innerHTML = PENCIL_ICON;

			var deleteBtn = document.createElement('button');
			deleteBtn.type = 'button';
			deleteBtn.className = 'jg-category-delete-btn';
			deleteBtn.setAttribute('aria-label', 'Eliminar ' + cat.name);
			deleteBtn.innerHTML = TRASH_ICON;

			var saveBtn = document.createElement('button');
			saveBtn.type = 'button';
			saveBtn.className = 'jg-button-small';
			saveBtn.textContent = 'Guardar';
			saveBtn.hidden = true;

			function toEditMode() {
				nameSpan.hidden = true;
				countSpan.hidden = true;
				renameInput.hidden = false;
				editBtn.hidden = true;
				deleteBtn.hidden = true;
				saveBtn.hidden = false;
				renameInput.focus();
			}

			function toViewMode() {
				nameSpan.hidden = false;
				countSpan.hidden = false;
				renameInput.hidden = true;
				editBtn.hidden = false;
				deleteBtn.hidden = false;
				saveBtn.hidden = true;
			}

			editBtn.addEventListener('click', toEditMode);

			deleteBtn.addEventListener('click', function () {
				var warning =
					cat.count > 0
						? 'Las ' + cat.count + ' imágenes que tiene quedarán sin categoría. Esta acción no se puede deshacer.'
						: 'Esta acción no se puede deshacer.';

				showConfirmModal(warning, {
					title: 'Eliminar categoría "' + capitalize(cat.name) + '"',
					confirmLabel: 'Eliminar',
					danger: true,
				}).then(function (confirmed) {
					if (!confirmed) return;

					var session = getSession();
					if (!session) {
						window.location.href = LOGIN_URL;
						return;
					}

					apiFetch('/categories/' + cat.id, {
						method: 'DELETE',
						headers: authHeaders(session),
					}).then(function (result) {
						if (!result.ok) {
							window.alert(result.data.message || 'No se pudo eliminar la categoría.');
							return;
						}
						loadCategories(root, state).then(function () {
							loadImages(root, state);
						});
					});
				});
			});

			saveBtn.addEventListener('click', function () {
				var newName = renameInput.value.trim();
				if (!newName || newName === cat.name) {
					toViewMode();
					return;
				}

				var session = getSession();
				if (!session) {
					window.location.href = LOGIN_URL;
					return;
				}

				apiFetch('/categories/' + cat.id, {
					method: 'PUT',
					headers: authHeaders(session, { 'Content-Type': 'application/json' }),
					body: JSON.stringify({ name: newName }),
				}).then(function (result) {
					if (!result.ok) {
						window.alert(result.data.message || 'No se pudo renombrar la categoría.');
						return;
					}
					loadCategories(root, state).then(function () {
						loadImages(root, state);
					});
				});
			});

			li.appendChild(nameSpan);
			li.appendChild(countSpan);
			li.appendChild(renameInput);
			li.appendChild(editBtn);
			li.appendChild(deleteBtn);
			li.appendChild(saveBtn);
			list.appendChild(li);
		});
	}

	function renderCategoryTabs(root, state) {
		var nav = root.querySelector('[data-jg-category-tabs]');
		if (!nav) return;

		var categorizedSum = state.categories.reduce(function (sum, cat) {
			return sum + cat.count;
		}, 0);
		var uncategorized = Math.max(0, state.allTotal - categorizedSum);

		var tabs = [{ key: 'all', label: 'Todas', count: state.allTotal }];
		state.categories.forEach(function (cat) {
			tabs.push({ key: String(cat.id), label: capitalize(cat.name), count: cat.count });
		});
		if (uncategorized > 0) {
			tabs.push({ key: 'none', label: 'Sin categoría', count: uncategorized });
		}

		nav.innerHTML = '';
		tabs.forEach(function (tab) {
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'jg-cat-tab' + (tab.key === state.selectedCategory ? ' is-active' : '');

			var label = document.createElement('span');
			label.textContent = tab.label;

			var count = document.createElement('span');
			count.className = 'jg-cat-tab-count';
			count.textContent = String(tab.count);

			btn.appendChild(label);
			btn.appendChild(count);

			btn.addEventListener('click', function () {
				state.selectedCategory = tab.key;
				state.page = 1;
				renderCategoryTabs(root, state);
				loadImages(root, state);
			});

			nav.appendChild(btn);
		});
	}

	function initUploadForm(root, state) {
		var form = root.querySelector('[data-jg-upload]');
		var mount = form.querySelector('[data-jg-upload-images-mount]');
		var repeater = buildImageUrlRepeater();
		mount.appendChild(repeater);

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var errorEl = form.querySelector('[data-jg-upload-error]');
			var successEl = form.querySelector('[data-jg-upload-success]');
			showMessage(errorEl, '');
			showMessage(successEl, '');

			var session = getSession();
			if (!session) {
				window.location.href = LOGIN_URL;
				return;
			}

			var imageUrls = repeater.getValues();
			if (!imageUrls.length) {
				showMessage(errorEl, 'Debes indicar al menos un enlace de imagen.');
				return;
			}

			var submitBtn = form.querySelector('button[type="submit"]');
			submitBtn.disabled = true;

			var payload = {
				title: form.title.value,
				description: form.description.value,
				image_urls: imageUrls,
				category_id: form.category_id.value,
			};

			apiFetch('/images', {
				method: 'POST',
				headers: authHeaders(session, { 'Content-Type': 'application/json' }),
				body: JSON.stringify(payload),
			})
				.then(function (result) {
					submitBtn.disabled = false;
					if (!result.ok) {
						showMessage(errorEl, result.data.message || 'No se pudo subir la imagen.');
						return;
					}
					form.reset();
					mount.innerHTML = '';
					repeater = buildImageUrlRepeater();
					mount.appendChild(repeater);
					showMessage(successEl, 'Imagen publicada correctamente.');
					loadCategories(root, state).then(function () {
						loadImages(root, state);
					});
				})
				.catch(function () {
					submitBtn.disabled = false;
					showMessage(errorEl, 'Error de red. Intenta nuevamente.');
				});
		});
	}

	// ---- grilla de imágenes + paginación ------------------------------------

	function loadImages(root, state) {
		var container = root.querySelector('[data-jg-images-grid]');
		container.innerHTML = '<p class="jg-empty">Cargando imágenes…</p>';

		var params = '?per_page=' + state.perPage + '&page=' + state.page;
		if (state.selectedCategory !== 'all') {
			params += '&category_id=' + encodeURIComponent(state.selectedCategory);
		}

		apiFetch('/images' + params).then(function (result) {
			if (!result.ok) {
				container.innerHTML = '';
				var err = document.createElement('p');
				err.className = 'jg-empty';
				err.textContent = 'No se pudieron cargar las imágenes.';
				container.appendChild(err);
				return;
			}

			state.images = result.data.items || [];
			state.total = result.data.total || 0;

			renderImagesGrid(root, state);
			renderPaginationControl(root.querySelector('[data-jg-pagination]'), {
				page: state.page,
				perPage: state.perPage,
				total: state.total,
				onChange: function (page) {
					state.page = page;
					loadImages(root, state);
				},
			});
		});
	}

	function renderImagesGrid(root, state) {
		var container = root.querySelector('[data-jg-images-grid]');
		container.innerHTML = '';

		if (!state.images.length) {
			var empty = document.createElement('p');
			empty.className = 'jg-empty';
			empty.textContent =
				state.selectedCategory === 'all'
					? 'Aún no hay imágenes publicadas.'
					: 'Todavía no hay imágenes en esta categoría.';
			container.appendChild(empty);
			return;
		}

		var grid = document.createElement('div');
		grid.className = 'jg-grid';
		state.images.forEach(function (item) {
			grid.appendChild(buildImageCard(root, state, item));
		});
		container.appendChild(grid);
	}

	function refreshAfterMutation(root, state) {
		loadCategories(root, state).then(function () {
			loadImages(root, state);
		});
	}

	function buildImageCard(root, state, item) {
		var session = getSession();
		var isOwner = !!(session && Number(session.user.id) === Number(item.author_id));

		var card = document.createElement('article');
		card.className = 'jg-card';

		var view = document.createElement('div');
		view.className = 'jg-card-view';

		var images = item.images && item.images.length ? item.images : [''];
		var currentIndex = 0;

		var figure = document.createElement('div');
		figure.className = 'jg-card-figure';

		var img = document.createElement('img');
		img.loading = 'lazy';
		img.alt = item.title;
		img.src = images[0];
		figure.appendChild(img);

		if (item.category) {
			var badge = document.createElement('span');
			badge.className = 'jg-card-badge';
			badge.textContent = capitalize(item.category.name);
			figure.appendChild(badge);
		}

		if (images.length > 1) {
			var counter = document.createElement('span');
			counter.className = 'jg-card-counter';

			function updateCarousel() {
				img.src = images[currentIndex];
				counter.textContent = currentIndex + 1 + '/' + images.length;
			}

			var prevBtn = document.createElement('button');
			prevBtn.type = 'button';
			prevBtn.className = 'jg-card-nav jg-card-nav--prev';
			prevBtn.setAttribute('aria-label', 'Imagen anterior');
			prevBtn.innerHTML = CHEVRON_LEFT_ICON;
			prevBtn.addEventListener('click', function (e) {
				e.stopPropagation();
				currentIndex = (currentIndex - 1 + images.length) % images.length;
				updateCarousel();
			});

			var nextBtn = document.createElement('button');
			nextBtn.type = 'button';
			nextBtn.className = 'jg-card-nav jg-card-nav--next';
			nextBtn.setAttribute('aria-label', 'Imagen siguiente');
			nextBtn.innerHTML = CHEVRON_RIGHT_ICON;
			nextBtn.addEventListener('click', function (e) {
				e.stopPropagation();
				currentIndex = (currentIndex + 1) % images.length;
				updateCarousel();
			});

			updateCarousel();
			figure.appendChild(prevBtn);
			figure.appendChild(nextBtn);
			figure.appendChild(counter);
		}

		var editForm = null;

		if (isOwner) {
			var icons = document.createElement('div');
			icons.className = 'jg-card-icons';

			var editBtn = document.createElement('button');
			editBtn.type = 'button';
			editBtn.className = 'jg-card-icon-btn jg-card-icon-btn--edit';
			editBtn.setAttribute('aria-label', 'Editar imagen');
			editBtn.innerHTML = PENCIL_ICON;

			var delBtn = document.createElement('button');
			delBtn.type = 'button';
			delBtn.className = 'jg-card-icon-btn jg-card-icon-btn--delete';
			delBtn.setAttribute('aria-label', 'Eliminar imagen');
			delBtn.innerHTML = TRASH_ICON;

			editBtn.addEventListener('click', function (e) {
				e.stopPropagation();
				view.hidden = true;
				editForm.hidden = false;
			});

			delBtn.addEventListener('click', function (e) {
				e.stopPropagation();

				showConfirmModal('Esta acción no se puede deshacer.', {
					title: 'Eliminar imagen "' + item.title + '"',
					confirmLabel: 'Eliminar',
					danger: true,
				}).then(function (confirmed) {
					if (!confirmed) return;

					var currentSession = getSession();
					if (!currentSession) {
						window.location.href = LOGIN_URL;
						return;
					}

					apiFetch('/images/' + item.id, {
						method: 'DELETE',
						headers: authHeaders(currentSession),
					}).then(function (result) {
						if (!result.ok) {
							window.alert(result.data.message || 'No se pudo eliminar la imagen.');
							return;
						}
						refreshAfterMutation(root, state);
					});
				});
			});

			icons.appendChild(editBtn);
			icons.appendChild(delBtn);
			figure.appendChild(icons);
		}

		var body = document.createElement('div');
		body.className = 'jg-card-body';

		var title = document.createElement('h4');
		title.textContent = item.title;

		var desc = document.createElement('p');
		desc.textContent = item.description || '';

		var meta = document.createElement('p');
		meta.className = 'jg-card-meta';
		meta.textContent = (item.author || '') + ' · ' + formatDate(item.date);

		body.appendChild(title);
		if (item.description) body.appendChild(desc);
		body.appendChild(meta);

		view.appendChild(figure);
		view.appendChild(body);
		card.appendChild(view);

		if (isOwner) {
			editForm = buildEditForm(root, state, item, view);
			card.appendChild(editForm);
		}

		return card;
	}

	function buildEditForm(root, state, item, view) {
		var form = document.createElement('form');
		form.className = 'jg-form jg-edit-form';
		form.hidden = true;

		var titleLabel = document.createElement('label');
		titleLabel.textContent = 'Título';
		var titleInput = document.createElement('input');
		titleInput.type = 'text';
		titleInput.required = true;
		titleInput.maxLength = 120;
		titleInput.value = item.title;
		titleLabel.appendChild(titleInput);

		var descLabel = document.createElement('label');
		descLabel.textContent = 'Descripción';
		var descInput = document.createElement('textarea');
		descInput.maxLength = 600;
		descInput.rows = 3;
		descInput.value = item.description || '';
		descLabel.appendChild(descInput);

		var imagesLabel = document.createElement('label');
		imagesLabel.textContent = 'Imágenes (hasta 10)';
		var imagesRepeater = buildImageUrlRepeater(item.images);

		var catLabel = document.createElement('label');
		catLabel.textContent = 'Categoría';
		var catSelect = document.createElement('select');
		var noneOpt = document.createElement('option');
		noneOpt.value = '';
		noneOpt.textContent = 'Sin categoría';
		catSelect.appendChild(noneOpt);
		state.categories.forEach(function (c) {
			var opt = document.createElement('option');
			opt.value = String(c.id);
			opt.textContent = capitalize(c.name);
			if (item.category && Number(item.category.id) === Number(c.id)) opt.selected = true;
			catSelect.appendChild(opt);
		});
		catLabel.appendChild(catSelect);

		var actions = document.createElement('div');
		actions.className = 'jg-edit-actions';

		var saveBtn = document.createElement('button');
		saveBtn.type = 'submit';
		saveBtn.textContent = 'Guardar';

		var cancelBtn = document.createElement('button');
		cancelBtn.type = 'button';
		cancelBtn.className = 'jg-button-secondary';
		cancelBtn.textContent = 'Cancelar';

		actions.appendChild(saveBtn);
		actions.appendChild(cancelBtn);

		var errorEl = document.createElement('p');
		errorEl.className = 'jg-message jg-message--error';
		errorEl.hidden = true;

		form.appendChild(titleLabel);
		form.appendChild(descLabel);
		form.appendChild(imagesLabel);
		form.appendChild(imagesRepeater);
		form.appendChild(catLabel);
		form.appendChild(actions);
		form.appendChild(errorEl);

		cancelBtn.addEventListener('click', function () {
			form.hidden = true;
			view.hidden = false;
		});

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			showMessage(errorEl, '');

			var session = getSession();
			if (!session) {
				window.location.href = LOGIN_URL;
				return;
			}

			var imageUrls = imagesRepeater.getValues();
			if (!imageUrls.length) {
				showMessage(errorEl, 'Debes indicar al menos un enlace de imagen.');
				return;
			}

			var payload = {
				title: titleInput.value,
				description: descInput.value,
				image_urls: imageUrls,
				category_id: catSelect.value,
			};

			apiFetch('/images/' + item.id, {
				method: 'PUT',
				headers: authHeaders(session, { 'Content-Type': 'application/json' }),
				body: JSON.stringify(payload),
			}).then(function (result) {
				if (!result.ok) {
					showMessage(errorEl, result.data.message || 'No se pudo guardar los cambios.');
					return;
				}
				refreshAfterMutation(root, state);
			});
		});

		return form;
	}

	// ---- bootstrap ----------------------------------------------------------

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-jg-root]').forEach(function (root) {
			var view = root.getAttribute('data-jg-view');
			if (view === 'panel') {
				initPanelView(root);
			} else {
				initLoginView(root);
			}
		});
	});
})();
```

---

## 1.7 `assets/css/jwt-gallery.css`

**Responsabilidad:** estilos aislados bajo `.jg-gallery`. Bloques principales de esta versión:

- `.jg-topbar` — barra superior del panel (usuario + cerrar sesión).
- `.jg-layout`, `.jg-sidebar`, `.jg-content` — el grid de dos columnas (sidebar fijo de 300px + contenido flexible), que colapsa a una columna en pantallas angostas (`@media (max-width: 860px)`); `.jg-gallery--panel` amplía el ancho máximo general a 1280px (el login sigue con `.jg-panel--narrow`, más angosto).
- `.jg-category-manager`, `.jg-category-list`, `.jg-category-item`, `.jg-category-count`, `.jg-category-edit-btn`, `.jg-category-delete-btn` — la lista de categorías del sidebar: contador junto al nombre, y dos botones de ícono circulares (lápiz / tacho de basura) en vez de texto "Editar"; `.jg-category-delete-btn` se pone rojo (`color: var(--jg-danger)`) solo al pasar el mouse.
- `.jg-cat-tab`, `.jg-cat-tab-count`, `.jg-cat-tab.is-active` — la barra de tabs por categoría del área de contenido (subrayado en la activa, contador en una píldora).
- `.jg-card-badge` — la etiqueta de categoría superpuesta en la esquina de cada imagen.
- `.jg-card-icons`, `.jg-card-icon-btn`, `.jg-card-icon-btn--edit`, `.jg-card-icon-btn--delete` — los dos botones de ícono (lápiz / tacho de basura) superpuestos en la esquina de cada imagen propia: cuadrados con esquinas redondeadas (`border-radius: 6px`, no círculos), y ocultos por defecto (`opacity: 0`) — solo aparecen al pasar el mouse sobre la imagen (`:hover`) o al llegar a ellos con el teclado (`:focus-within`, para que sigan siendo accesibles sin mouse).
- `.jg-pagination`, `.jg-pagination-nav`, `.jg-pagination-btn`, `.jg-pagination-page`, `.jg-pagination-ellipsis` — el componente de paginación (§1.6.1).
- `.jg-modal-overlay`, `.jg-modal`, `.jg-modal-title`, `.jg-modal-message`, `.jg-modal-actions`, `.jg-modal-confirm-danger` — el componente de modal de confirmación (§1.6.2). Define sus propias variables `--jgm-*` (no usa las `--jg-*` de `.jg-gallery`) porque el modal se inserta en `document.body`, fuera de ese contenedor.
- `.jg-url-repeater`, `.jg-url-repeater-row`, `.jg-url-repeater-remove`, `.jg-url-repeater-add`, `.jg-url-repeater-count` — el repetidor de enlaces de imagen (§1.6.3), usado tanto en "Subir imagen" como en el formulario de edición.
- `.jg-card-nav`, `.jg-card-nav--prev`, `.jg-card-nav--next`, `.jg-card-counter` — el mini-carrusel de la tarjeta (§1.6.4): flechas ocultas hasta el hover (mismo patrón que `.jg-card-icons`) y un contador siempre visible en la esquina inferior derecha.
- `body.jg-blank-page` — el fondo/padding base de las páginas servidas por la plantilla en blanco de §1.8 (sin cambios).

El resto (formularios, inputs, mensajes, botones base) se mantiene igual que antes.

```css
body.jg-blank-page {
	margin: 0;
	padding: 48px 20px;
	background: #f4f4f2;
	font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
}

.jg-gallery {
	--jg-border: #dcdcd6;
	--jg-ink: #1c1c1a;
	--jg-muted: #6b6b64;
	--jg-accent: #2b5d8c;
	--jg-danger: #b3261e;
	--jg-success: #2e7d32;
	--jg-surface: #fbfbf9;
	--jg-surface-2: #f1f1ee;

	max-width: 960px;
	margin: 0 auto;
	display: flex;
	flex-direction: column;
	gap: 24px;
	font-size: 15px;
	color: var(--jg-ink);
}

.jg-gallery--panel {
	max-width: 1280px;
}

/* The [hidden] attribute must always win, even over elements whose own
   class sets `display` (e.g. .jg-card-view, .jg-form both use display:flex,
   which otherwise ties in specificity with the UA [hidden] rule and can
   end up winning by source order, showing "hidden" content anyway). */
.jg-gallery [hidden] {
	display: none !important;
}

.jg-gallery * {
	box-sizing: border-box;
}

.jg-panel {
	background: var(--jg-surface);
	border: 1px solid var(--jg-border);
	border-radius: 8px;
	padding: 20px 22px;
}

.jg-panel--narrow {
	max-width: 460px;
}

.jg-form h3,
.jg-panel h3 {
	margin: 0 0 4px;
	font-size: 1.05rem;
}

.jg-sidebar-hint {
	margin: 0 0 16px;
	font-size: 0.8rem;
	color: var(--jg-muted);
}

.jg-form {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.jg-form label {
	display: flex;
	flex-direction: column;
	gap: 5px;
	font-size: 0.85rem;
	color: var(--jg-muted);
}

.jg-form input,
.jg-form textarea,
.jg-form select {
	font: inherit;
	padding: 9px 10px;
	border: 1px solid var(--jg-border);
	border-radius: 5px;
	color: var(--jg-ink);
	background: #fff;
}

.jg-form input:focus,
.jg-form textarea:focus,
.jg-form select:focus {
	outline: 2px solid var(--jg-accent);
	outline-offset: 1px;
}

.jg-password-field {
	position: relative;
	display: block;
}

.jg-password-field input {
	width: 100%;
	padding-right: 40px;
}

.jg-password-toggle {
	position: absolute;
	top: 50%;
	right: 4px;
	transform: translateY(-50%);
	display: flex;
	align-items: center;
	justify-content: center;
	width: 30px;
	height: 30px;
	padding: 0;
	border: none;
	border-radius: 50%;
	background: transparent;
	color: var(--jg-muted);
	cursor: pointer;
}

.jg-password-toggle:hover,
.jg-password-toggle:focus-visible {
	background: rgba(0, 0, 0, 0.06);
	color: var(--jg-accent);
}

.jg-form button,
.jg-button-secondary {
	align-self: flex-start;
	font: inherit;
	font-weight: 600;
	padding: 9px 18px;
	border-radius: 5px;
	border: 1px solid var(--jg-accent);
	background: var(--jg-accent);
	color: #fff;
	cursor: pointer;
}

.jg-form button:disabled {
	opacity: 0.6;
	cursor: default;
}

.jg-button-secondary {
	background: transparent;
	color: var(--jg-accent);
}

.jg-button-danger {
	border-color: var(--jg-danger);
	background: transparent;
	color: var(--jg-danger);
}

.jg-button-small {
	padding: 5px 12px;
	font-size: 0.8rem;
}

.jg-message {
	margin: 4px 0 0;
	font-size: 0.85rem;
}

.jg-message--error {
	color: var(--jg-danger);
}

.jg-message--success {
	color: var(--jg-success);
}

/* ---- topbar (panel view) ---- */

.jg-topbar {
	display: flex;
	align-items: center;
	justify-content: flex-end;
	gap: 14px;
	padding-bottom: 16px;
	border-bottom: 1px solid var(--jg-border);
}

.jg-topbar-user {
	font-size: 0.85rem;
	color: var(--jg-muted);
}

/* ---- layout: sidebar + content ---- */

.jg-layout {
	display: grid;
	grid-template-columns: 300px 1fr;
	gap: 24px;
	align-items: start;
}

.jg-sidebar {
	display: flex;
	flex-direction: column;
	gap: 20px;
	position: sticky;
	top: 20px;
}

.jg-content {
	min-width: 0;
}

@media (max-width: 860px) {
	.jg-layout {
		grid-template-columns: 1fr;
	}

	.jg-sidebar {
		position: static;
	}
}

/* ---- category manager (sidebar) ---- */

.jg-category-manager .jg-form--inline {
	flex-direction: row;
	gap: 8px;
	max-width: none;
}

.jg-form--inline input {
	flex: 1;
	min-width: 0;
}

.jg-form--inline button {
	align-self: stretch;
	flex: none;
}

.jg-category-list {
	list-style: none;
	margin: 0 0 16px;
	padding: 0;
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.jg-category-item {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 8px 10px;
	border: 1px solid var(--jg-border);
	border-radius: 5px;
	max-width: 100%;
}

.jg-category-name {
	flex: 1;
	min-width: 0;
	font-size: 0.9rem;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.jg-category-count {
	flex: none;
	font-size: 0.78rem;
	color: var(--jg-muted);
}

.jg-category-rename-input {
	flex: 1;
	min-width: 0;
	font: inherit;
	padding: 5px 8px;
	border: 1px solid var(--jg-border);
	border-radius: 4px;
}

.jg-category-edit-btn {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 26px;
	height: 26px;
	padding: 0;
	border: none;
	border-radius: 50%;
	background: transparent;
	color: var(--jg-muted);
	cursor: pointer;
}

.jg-category-edit-btn:hover {
	background: var(--jg-surface-2);
	color: var(--jg-accent);
}

.jg-category-delete-btn {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 26px;
	height: 26px;
	padding: 0;
	border: none;
	border-radius: 50%;
	background: transparent;
	color: var(--jg-muted);
	cursor: pointer;
}

.jg-category-delete-btn:hover {
	background: var(--jg-surface-2);
	color: var(--jg-danger);
}

/* ---- category filter tabs (content area) ---- */

.jg-cat-tab {
	font: inherit;
	font-size: 0.92rem;
	font-weight: 600;
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 10px 14px;
	border: none;
	border-bottom: 2px solid transparent;
	background: transparent;
	color: var(--jg-muted);
	cursor: pointer;
}

.jg-cat-tab.is-active {
	color: var(--jg-ink);
	border-bottom-color: var(--jg-accent);
}

.jg-cat-tab-count {
	font-size: 0.72rem;
	font-weight: 600;
	padding: 1px 7px;
	border-radius: 999px;
	background: var(--jg-surface-2);
	color: var(--jg-muted);
}

.jg-cat-tab.is-active .jg-cat-tab-count {
	background: var(--jg-accent);
	color: #fff;
}

[data-jg-category-tabs] {
	display: flex;
	flex-wrap: wrap;
	border-bottom: 1px solid var(--jg-border);
	margin-bottom: 20px;
}

/* ---- image grid ---- */

.jg-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
	gap: 18px;
}

.jg-empty {
	grid-column: 1 / -1;
	color: var(--jg-muted);
	font-size: 0.9rem;
}

.jg-card {
	border: 1px solid var(--jg-border);
	border-radius: 8px;
	overflow: hidden;
	background: var(--jg-surface);
	display: flex;
	flex-direction: column;
}

.jg-card-view {
	display: flex;
	flex-direction: column;
	flex: 1;
}

.jg-card-figure {
	position: relative;
	aspect-ratio: 4 / 3;
	overflow: hidden;
	background: #eee;
}

.jg-card-figure img {
	width: 100%;
	height: 100%;
	object-fit: cover;
	display: block;
}

.jg-card-badge {
	position: absolute;
	top: 8px;
	left: 8px;
	padding: 3px 10px;
	border-radius: 999px;
	background: rgba(20, 22, 20, 0.72);
	color: #fff;
	font-size: 0.72rem;
	font-weight: 600;
}

.jg-card-icons {
	position: absolute;
	top: 6px;
	right: 6px;
	display: flex;
	gap: 6px;
	opacity: 0;
	transition: opacity 0.15s ease;
}

.jg-card-figure:hover .jg-card-icons,
.jg-card-figure:focus-within .jg-card-icons {
	opacity: 1;
}

/* ---- mini-carrusel: publicaciones con más de una imagen ---- */

.jg-card-nav {
	position: absolute;
	top: 50%;
	transform: translateY(-50%);
	display: flex;
	align-items: center;
	justify-content: center;
	width: 26px;
	height: 26px;
	padding: 0;
	border: none;
	border-radius: 50%;
	background: rgba(20, 22, 20, 0.55);
	color: #fff;
	cursor: pointer;
	opacity: 0;
	transition: opacity 0.15s ease;
}

.jg-card-figure:hover .jg-card-nav,
.jg-card-figure:focus-within .jg-card-nav {
	opacity: 1;
}

.jg-card-nav:hover {
	background: rgba(20, 22, 20, 0.8);
}

.jg-card-nav--prev {
	left: 6px;
}

.jg-card-nav--next {
	right: 6px;
}

.jg-card-counter {
	position: absolute;
	bottom: 8px;
	right: 8px;
	padding: 2px 8px;
	border-radius: 999px;
	background: rgba(20, 22, 20, 0.72);
	color: #fff;
	font-size: 0.7rem;
	font-weight: 600;
	font-variant-numeric: tabular-nums;
}

.jg-card-icon-btn {
	display: flex;
	align-items: center;
	justify-content: center;
	width: 26px;
	height: 26px;
	padding: 0;
	border: none;
	border-radius: 6px;
	cursor: pointer;
	box-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
}

.jg-card-icon-btn--edit {
	background: #fff;
	color: var(--jg-ink);
}

.jg-card-icon-btn--edit:hover {
	background: var(--jg-surface-2);
}

.jg-card-icon-btn--delete {
	background: var(--jg-danger);
	color: #fff;
}

.jg-card-icon-btn--delete:hover {
	background: #911d16;
}

.jg-card-body {
	padding: 12px 14px 14px;
	display: flex;
	flex-direction: column;
	gap: 6px;
	flex: 1;
}

.jg-card-body h4 {
	margin: 0;
	font-size: 0.98rem;
}

.jg-card-body p {
	margin: 0;
	font-size: 0.86rem;
	color: var(--jg-muted);
	overflow: hidden;
	text-overflow: ellipsis;
	display: -webkit-box;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
}

.jg-card-meta {
	font-size: 0.75rem !important;
	color: var(--jg-muted);
	margin-top: auto !important;
	-webkit-line-clamp: unset !important;
}

.jg-edit-form {
	padding: 14px;
	gap: 10px;
}

.jg-edit-actions {
	display: flex;
	gap: 8px;
}

/* ---- pagination (reusable component) ---- */

.jg-pagination {
	display: flex;
	align-items: center;
	justify-content: space-between;
	flex-wrap: wrap;
	gap: 12px;
	margin-top: 24px;
	padding-top: 16px;
	border-top: 1px solid var(--jg-border);
}

.jg-pagination-info {
	margin: 0;
	font-size: 0.85rem;
	color: var(--jg-muted);
}

.jg-pagination-nav {
	display: flex;
	align-items: center;
	gap: 4px;
}

.jg-pagination-btn {
	font: inherit;
	font-size: 0.82rem;
	font-weight: 600;
	padding: 7px 12px;
	border: 1px solid var(--jg-border);
	border-radius: 5px;
	background: #fff;
	color: var(--jg-ink);
	cursor: pointer;
}

.jg-pagination-btn:disabled {
	opacity: 0.4;
	cursor: default;
}

.jg-pagination-page {
	font: inherit;
	font-size: 0.82rem;
	font-weight: 600;
	width: 30px;
	height: 30px;
	border: 1px solid transparent;
	border-radius: 5px;
	background: transparent;
	color: var(--jg-muted);
	cursor: pointer;
}

.jg-pagination-page:hover {
	background: var(--jg-surface-2);
}

.jg-pagination-page.is-active {
	background: var(--jg-ink);
	color: #fff;
}

.jg-pagination-ellipsis {
	padding: 0 4px;
	color: var(--jg-muted);
}

/* ---- reusable confirm modal (appended to <body>, so it defines its own
   tokens instead of relying on being nested inside .jg-gallery) ---- */

.jg-modal-overlay {
	--jgm-ink: #1c1c1a;
	--jgm-muted: #6b6b64;
	--jgm-border: #dcdcd6;
	--jgm-surface: #ffffff;
	--jgm-accent: #2b5d8c;
	--jgm-danger: #b3261e;

	position: fixed;
	inset: 0;
	background: rgba(20, 22, 20, 0.5);
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 20px;
	z-index: 1000;
	font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
}

.jg-modal {
	background: var(--jgm-surface);
	border-radius: 10px;
	padding: 24px;
	max-width: 380px;
	width: 100%;
	box-shadow: 0 12px 36px rgba(0, 0, 0, 0.3);
}

.jg-modal-title {
	margin: 0 0 10px;
	font-size: 1.05rem;
	color: var(--jgm-ink);
}

.jg-modal-message {
	margin: 0;
	font-size: 0.92rem;
	color: var(--jgm-muted);
	line-height: 1.5;
}

.jg-modal-actions {
	display: flex;
	justify-content: flex-end;
	gap: 10px;
	margin-top: 22px;
}

.jg-modal-actions button {
	font: inherit;
	font-weight: 600;
	font-size: 0.9rem;
	padding: 9px 18px;
	border-radius: 6px;
	border: 1px solid var(--jgm-accent);
	background: var(--jgm-accent);
	color: #fff;
	cursor: pointer;
}

.jg-modal-actions .jg-button-secondary {
	background: transparent;
	border-color: var(--jgm-border);
	color: var(--jgm-ink);
}

.jg-modal-confirm-danger {
	border-color: var(--jgm-danger) !important;
	background: var(--jgm-danger) !important;
}

/* ---- reusable image-URL repeater ---- */

.jg-url-repeater {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.jg-url-repeater-rows {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.jg-url-repeater-row {
	display: flex;
	gap: 6px;
}

.jg-url-repeater-row input {
	flex: 1;
	min-width: 0;
	font: inherit;
	padding: 9px 10px;
	border: 1px solid var(--jg-border);
	border-radius: 5px;
	color: var(--jg-ink);
	background: #fff;
}

.jg-url-repeater-row input:focus {
	outline: 2px solid var(--jg-accent);
	outline-offset: 1px;
}

.jg-url-repeater-remove {
	flex: none;
	display: flex;
	align-items: center;
	justify-content: center;
	width: 34px;
	border: 1px solid var(--jg-border);
	border-radius: 5px;
	background: #fff;
	color: var(--jg-muted);
	font-size: 1.1rem;
	line-height: 1;
	cursor: pointer;
}

.jg-url-repeater-remove:hover {
	color: var(--jg-danger);
	border-color: var(--jg-danger);
}

.jg-url-repeater-add {
	align-self: flex-start;
	font: inherit;
	font-size: 0.85rem;
	font-weight: 600;
	padding: 6px 4px;
	border: none;
	background: transparent;
	color: var(--jg-accent);
	cursor: pointer;
}

.jg-url-repeater-add:hover {
	text-decoration: underline;
}

.jg-url-repeater-count {
	margin: 0;
	font-size: 0.78rem;
	color: var(--jg-muted);
}
```

---

## 1.8 `includes/class-jg-template.php` + `templates/blank-page.php`

**Responsabilidad:** hacer que `/galeria/` y `/galeria-panel/` se vean **solo** con el contenido del plugin, sin el header, nav ni footer del tema activo (Twenty Twenty-Five, en este entorno). Sin esto, WordPress renderiza esas páginas con el `page.html`/`page.php` normal del tema, que agrega el menú del sitio, el footer con widgets, etc. alrededor del shortcode.

`class-jg-template.php` engancha el filtro `template_include` — el último filtro que corre antes de que WordPress decida qué archivo de plantilla usar para la página actual — y, si la página que se está mostrando es `galeria` o `galeria-panel` (`is_page()` acepta un arreglo de slugs), devuelve la ruta de `templates/blank-page.php` en vez de la plantilla del tema. Cualquier otra página del sitio sigue usando el tema normalmente.

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Serves the login and panel pages on a bare template (no theme header/nav/footer),
 * so only the plugin's own markup is visible.
 */
class JG_Template {

	const SLUGS = array(
		JG_Shortcode::LOGIN_PAGE_SLUG,
		JG_Shortcode::PANEL_PAGE_SLUG,
	);

	public static function init() {
		add_filter( 'template_include', array( __CLASS__, 'maybe_use_blank_template' ) );
	}

	public static function maybe_use_blank_template( $template ) {
		if ( is_page( self::SLUGS ) ) {
			return JG_PATH . 'templates/blank-page.php';
		}

		return $template;
	}
}
```

`templates/blank-page.php` es un documento HTML mínimo propio: llama a `wp_head()` y `wp_footer()` (para que los estilos/scripts encolados —incluido `jg-gallery`— y cualquier hook crítico de WordPress se sigan cargando con normalidad), pero **no** llama a `get_header()` ni `get_footer()` del tema, así que el menú, el logo del sitio y los widgets del footer nunca se imprimen.

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'jg-blank-page' ); ?>>
<?php
while ( have_posts() ) :
	the_post();
	the_content();
endwhile;
?>
<?php wp_footer(); ?>
</body>
</html>
```

`body_class( 'jg-blank-page' )` agrega la clase `jg-blank-page` (además de las clases estándar de WordPress) al `<body>`, que es lo que usa `assets/css/jwt-gallery.css` (§1.7) para darle un fondo y padding base a la página, ya que ya no hereda ningún estilo de layout del tema.

> Si algún día se necesita que estas dos páginas usen otro slug, hay que actualizar tanto `JG_Shortcode::LOGIN_PAGE_SLUG` / `PANEL_PAGE_SLUG` (§1.5, de donde `class-jg-template.php` toma los slugs) como recrear las páginas con el nuevo slug — `class-jg-template.php` no tiene su propia copia de los slugs, reutiliza las constantes del shortcode para no duplicar la configuración.

---

## 2. Instalación

```bash
sudo cp -r jwt-gallery /var/www/html/wordpress/proyecto3/wp-content/plugins/
sudo chown -R www-data:www-data /var/www/html/wordpress/proyecto3/wp-content/plugins/jwt-gallery
```

Luego: **Escritorio de WordPress → Plugins → Todo Acrílico Panel → Activar**. El plugin se sigue llamando `jwt-gallery` internamente (carpeta, namespace REST, prefijos de clase) — solo el nombre que se ve en la lista de Plugins de wp-admin cambió, vía el encabezado `Plugin Name:` de `jwt-gallery.php` (§1.1).

En la activación (`register_activation_hook`), el plugin:
1. Genera el secreto de firma JWT si aún no existe (ver §3).
2. Registra el Custom Post Type `jg_gallery_image` y la taxonomía `jg_gallery_category`.
3. Siembra las 5 categorías por defecto si no existen (`organizador`, `proteger`, `trabajos-especiales`, `exhibir`, `informar`).
4. Ejecuta `flush_rewrite_rules()`.

Después de activar el plugin, hay que **crear dos páginas** con los shortcodes correspondientes (ver §5 para el porqué de la separación):

| Página | Slug | Contenido |
|---|---|---|
| Login | `galeria` | `[jwt_gallery]` |
| Panel | `galeria-panel` | `[jwt_gallery_panel]` |

El plugin resuelve las URLs de redirección buscando páginas publicadas con esos slugs exactos (`get_page_by_path()`). Si cambias el slug de cualquiera de las dos páginas, actualiza las constantes `LOGIN_PAGE_SLUG` / `PANEL_PAGE_SLUG` en `class-jg-shortcode.php`.

Al desactivar solo se hace `flush_rewrite_rules()` — **no se borran imágenes, categorías ni páginas**.

---

## 3. Configuración del secreto JWT

Sin cambios respecto a la versión anterior. El token se firma con HMAC-SHA256 usando un secreto propio, independiente de `AUTH_KEY`/`SECURE_AUTH_KEY` de `wp-config.php`.

- Se autogenera con `random_bytes(32)` en la primera activación y se guarda en la opción `jg_jwt_secret` (`autoload = false`).
- Puede fijarse manualmente agregando en `wp-config.php`:

  ```php
  define( 'JG_JWT_SECRET', 'una-cadena-larga-y-aleatoria-propia' );
  ```

Cambiar el secreto invalida instantáneamente todos los tokens emitidos previamente.

---

## 4. Arquitectura de páginas y flujo de sesión

Esta versión separa la aplicación en **dos páginas de WordPress independientes**, en vez de mostrar/ocultar secciones en una sola página:

```
┌─────────────────────┐  login OK   ┌──────────────────────────┐
│  /galeria/           │ ──────────▶ │  /galeria-panel/          │
│  [jwt_gallery]        │             │  [jwt_gallery_panel]      │
│  Solo formulario de   │ ◀────────── │  Nav + tabs "Subir        │
│  inicio de sesión     │  logout /   │  imagen" / "Imágenes"     │
│                        │  sin sesión │                            │
└─────────────────────┘             └──────────────────────────┘
```

- **`/galeria/`** (`[jwt_gallery]`): si ya hay una sesión válida guardada, el JS redirige inmediatamente al panel sin mostrar el formulario. Si no la hay, muestra solo el login; al autenticarse, guarda el token y redirige al panel.
- **`/galeria-panel/`** (`[jwt_gallery_panel]`): si no hay sesión válida (nunca inició sesión, o el token expiró), redirige inmediatamente al login. Si la hay, muestra el nav con las pestañas **Subir imagen** e **Imágenes**, y el botón **Cerrar sesión** (que borra el token local y redirige al login).

La redirección se resuelve completamente en el navegador (`window.location.href`), usando las URLs reales de ambas páginas que WordPress le pasa al JS (`JGGallery.loginUrl` / `JGGallery.panelUrl`, ver §1.5). No hay enrutamiento del lado del servidor: si alguien visita `/galeria-panel/` con una sesión expirada, verá brevemente el HTML del panel antes de que el JS la redirija — no es una fuga de datos porque toda la información sensible (imágenes, categorías) sigue detrás de la API REST protegida por JWT, pero si se necesita ocultar el HTML del panel en sí, habría que agregar una verificación adicional (por ejemplo, ocultar el `<div>` por CSS hasta confirmar la sesión).

---

## 5. Modelo de datos

Cada **publicación** (un título, una descripción, una categoría, de 1 a 10 fotos) se guarda como un post del tipo `jg_gallery_image` (no público, visible en el admin bajo su propio menú):

| Campo WordPress | Contenido |
|---|---|
| `post_title` | Título de la publicación (`sanitize_text_field`) |
| `post_content` | Descripción (`sanitize_textarea_field`) |
| `post_author` | ID del usuario autenticado que la subió — determina quién puede editarla/eliminarla |
| `post_status` | Siempre `publish` (no hay cola de moderación) |
| Meta `_jg_image_url` | **Una fila por cada foto** (de 1 a 10) — la misma clave de meta se repite tantas veces como imágenes tenga la publicación, insertadas con `add_post_meta( ..., false )` para que no se sobrescriban entre sí. Cada enlace, validado y saneado individualmente (`esc_url_raw`). |
| Términos de `jg_gallery_category` | 0 o 1 categoría asignada vía `wp_set_object_terms()` — **una sola categoría por publicación**, compartida por todas sus fotos |

Ninguna imagen **se aloja en este sitio**: el plugin guarda únicamente los enlaces que el usuario ingresó. No hay adjuntos en la Media Library ni imagen destacada — el navegador carga cada imagen directamente desde su URL original (`<img src="...">`). Implicancias de este diseño en §9.

**Compatibilidad hacia atrás:** `get_post_meta( $post_id, '_jg_image_url', true )` (con `$single = true`) sigue funcionando igual que cuando cada publicación tenía una sola foto — WordPress devuelve la primera fila encontrada. Es lo que usa el tema `todo-acrilico` (`ta_get_solutions_by_category()`, Docs_theme.md §1.2) para mostrar la foto principal de cada publicación en "Nuestras Soluciones", sin haber necesitado ningún cambio de código — verificado en §12.19.

Las categorías son términos de la taxonomía `jg_gallery_category` (no jerárquica), sembrada con 5 términos por defecto en la activación (§1.3) y editable desde el panel (crear/renombrar) o desde wp-admin (`show_ui => true`).

---

## 6. API REST

Base URL: `https://tu-sitio.cl/wp-json/jwt-gallery/v1`

| Método | Ruta | Auth | Descripción |
|---|---|---|---|
| `POST` | `/login` | No | Autentica con correo + contraseña, devuelve un JWT |
| `GET` | `/images` | No | Lista las imágenes publicadas (paginado), con su categoría |
| `POST` | `/images` | Sí (Bearer) | Sube una imagen con título, descripción, enlace y categoría opcional |
| `PUT` | `/images/{id}` | Sí (Bearer, autor) | Edita título, descripción, enlace y/o categoría de una imagen propia |
| `DELETE` | `/images/{id}` | Sí (Bearer, autor) | Elimina una imagen propia |
| `GET` | `/categories` | No | Lista todas las categorías |
| `POST` | `/categories` | Sí (Bearer) | Crea una categoría nueva |
| `PUT` | `/categories/{id}` | Sí (Bearer) | Renombra una categoría existente |
| `DELETE` | `/categories/{id}` | Sí (Bearer) | Elimina una categoría; desde 1.4.1 elimina también sus piezas (posts + archivos locales) — ver §12.33 |

### 6.1 `POST /login`

**Body** (`application/json`): `{ "email": "...", "password": "..." }`

**Respuesta 200:**

```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "expires_in": 86400,
  "user": { "id": 3, "name": "Usuario Demo Galería", "email": "admin@todoacrilico.cl" }
}
```

**Errores:** `jg_bad_request` (400), `jg_invalid_credentials` (401), `jg_too_many_attempts` (429, tras 5 intentos fallidos en 10 min por correo+IP).

### 6.2 `GET /images`

**Query params:** `page` (default 1), `per_page` (default 50, máx. 100), `category_id` (opcional).

`category_id` filtra el listado (y por lo tanto `total`/`pages`, que reflejan el filtro aplicado, no el total global):
- Omitido → todas las imágenes.
- Un ID numérico (p. ej. `category_id=4`) → solo las imágenes de esa categoría.
- El valor literal `category_id=none` → solo las imágenes **sin** categoría asignada (`tax_query` con `NOT EXISTS`).

**Respuesta 200:**

```json
{
  "items": [
    {
      "id": 13,
      "title": "Ventana instalada",
      "description": "Prueba con categoría",
      "images": [
        "https://chileautos.pxcrush.net/cars/dealer/....jpg?a=1",
        "https://chileautos.pxcrush.net/cars/dealer/....jpg?a=2"
      ],
      "date": "2026-07-13T03:40:34-03:00",
      "author": "Usuario Demo Galería",
      "author_id": 3,
      "category": { "id": 4, "name": "trabajos-especiales" }
    }
  ],
  "total": 1,
  "pages": 1
}
```

`images` es siempre un arreglo (1 a 10 elementos) — aunque la publicación tenga una sola foto, sigue viniendo como `["https://..."]`, no como string suelto. `category` es `null` si la imagen no tiene categoría asignada. `author_id` permite al frontend decidir si mostrar el menú de edición/borrado. El panel (§1.6) usa `per_page=9` y pagina con `page`, combinado con `category_id` según el tab activo.

### 6.3 `POST /images`

Requiere `Authorization: Bearer <token>`. Body `application/json`:

| Campo | Tipo | Obligatorio | Notas |
|---|---|---|---|
| `title` | texto | Sí | Máx. 120 caracteres |
| `description` | texto | No | Máx. 600 caracteres |
| `image_urls` | arreglo de texto (URL) | Sí | De 1 a 10 elementos; cada uno http(s), extensión `jpg/jpeg/png/gif/webp/avif`, máx. 2048 caracteres |
| `category_id` | entero | No | ID de una categoría existente; `0` o vacío = sin categoría. Se aplica a toda la publicación (todas sus fotos comparten la misma) |

**Respuesta 200:** el objeto de imagen creado (mismo formato que en `GET /images`, con `images` como arreglo).

**Errores:** `jg_no_title`, `jg_no_image_url` (arreglo vacío o ausente), `jg_too_many_images` (más de 10), `jg_invalid_image_url`, `jg_bad_extension` (400); `jg_save_failed` (500).

**Ejemplo — publicación con 3 fotos del mismo mueble:**

```bash
curl -X POST "https://tu-sitio.cl/wp-json/jwt-gallery/v1/images" \
  -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{
    "title": "Organizador escritorio 03",
    "description": "Pieza fabricada a medida",
    "category_id": 2,
    "image_urls": [
      "https://ejemplo.cl/organizador-frente.jpg",
      "https://ejemplo.cl/organizador-lateral.jpg",
      "https://ejemplo.cl/organizador-detalle.jpg"
    ]
  }'
```

### 6.4 `PUT /images/{id}`

Mismos campos que `POST /images`. Requiere `Authorization: Bearer <token>` del **autor** de la imagen.

**Errores propios:** `jg_not_found` (404, la imagen no existe), `jg_forbidden` (403, el token es válido pero no pertenece al autor de esa imagen).

### 6.5 `DELETE /images/{id}`

Requiere `Authorization: Bearer <token>` del autor. **Respuesta 200:** `{ "deleted": true, "id": 13 }`. Mismos errores de autorización que `PUT`.

### 6.6 `GET /categories`

Pública, sin paginar. **Respuesta 200:**

```json
[
  { "id": 2, "name": "organizador", "slug": "organizador", "count": 12 },
  { "id": 3, "name": "proteger", "slug": "proteger", "count": 8 },
  { "id": 4, "name": "trabajos-especiales", "slug": "trabajos-especiales", "count": 12 },
  { "id": 5, "name": "exhibir", "slug": "exhibir", "count": 9 },
  { "id": 6, "name": "informar", "slug": "informar", "count": 7 }
]
```

`count` es el número de imágenes **publicadas** en esa categoría (`WP_Term->count`, que WordPress recalcula automáticamente en cada alta/baja/reasignación — no requiere una consulta aparte). El panel lo usa para pintar el número junto a cada tab y cada fila del sidebar; no incluye las imágenes sin categoría (ver §6.2 con `category_id=none` para esas).

### 6.7 `POST /categories`

Requiere `Authorization: Bearer <token>` (cualquier usuario autenticado, ver limitación en §10). Body: `{ "name": "..." }`.

**Errores:** `jg_no_category_name` (400), `jg_category_exists` (409, ya existe una categoría con ese nombre).

### 6.8 `PUT /categories/{id}`

Requiere `Authorization: Bearer <token>`. Body: `{ "name": "..." }`.

**Errores:** `jg_no_category_name` (400), `jg_category_not_found` (404).

### 6.9 `DELETE /categories/{id}`

Requiere `Authorization: Bearer <token>` (cualquier usuario autenticado, misma limitación que crear/renombrar — ver §10). **Respuesta 200:** `{ "deleted": true, "id": 11 }`.

Usa `wp_delete_term()` internamente, que solo borra el término — **no borra las imágenes** que tenía asignadas. Esas imágenes siguen existiendo y pasan a tener `category: null` (equivalen a "sin categoría", visibles con `GET /images?category_id=none`). Verificado en §12.17: se creó una categoría, se le subió una imagen, se borró la categoría, y la imagen siguió apareciendo en el listado — solo cambió su campo `category`.

**Errores:** `jg_category_not_found` (404), `jg_category_failed` (500, si `wp_delete_term()` falla).

**Ejemplo con curl (flujo completo):**

```bash
TOKEN=$(curl -s -X POST "https://tu-sitio.cl/wp-json/jwt-gallery/v1/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@todoacrilico.cl","password":"..."}' | jq -r .token)

curl -X POST "https://tu-sitio.cl/wp-json/jwt-gallery/v1/images" \
  -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"title":"Ventana instalada","description":"...","image_urls":["https://ejemplo.com/foto.jpg"],"category_id":4}'
```

---

## 7. Autenticación (detalle del token)

Sin cambios respecto a la versión anterior:

- **Algoritmo:** HS256, implementado en `class-jg-jwt.php` sin librerías externas.
- **Claims:** `sub` (ID de usuario WP), `email`, `iat`, `exp`, `iss` (`home_url()`).
- **Expiración:** 24 horas. No hay refresh token.
- **Verificación:** `hash_equals()` para la firma, validación de `exp`.
- El plugin no crea usuarios nuevos: valida contra `wp_users` existentes.

---

## 8. Shortcodes

| Shortcode | Dónde | Qué muestra |
|---|---|---|
| `[jwt_gallery]` | Página de login (`galeria`) | Solo el formulario de inicio de sesión |
| `[jwt_gallery_panel]` | Página del panel (`galeria-panel`) | Topbar (usuario + cerrar sesión) + sidebar fijo (formulario "Subir imagen" y gestor de categorías) + área de contenido (tabs por categoría con contador, grilla de imágenes con íconos de editar/eliminar superpuestos, y paginación) |

Ambos comparten el mismo CSS/JS encolado (`jg-gallery`), así que basta con que **una de las dos páginas** cargue el shortcode para que los estilos y el script estén disponibles en esa página — no hace falta poner ambos shortcodes juntos.

### Manejo de sesión en el navegador

Igual que antes: `localStorage`, clave `jg_session`, `{ token, expiresAt, user }`. La diferencia es que ahora **cambiar de vista implica cambiar de URL** (§4), no solo mostrar/ocultar secciones.

Los datos de cada imagen y categoría se insertan en el DOM con `textContent`/`value`, nunca con `innerHTML`, para evitar XSS con contenido introducido por los propios usuarios.

---

## 9. Medidas de seguridad implementadas

- Contraseñas verificadas con `wp_check_password()`; nunca se devuelven en las respuestas.
- Firma de token verificada con `hash_equals()`.
- Límite de 5 intentos de login fallidos por correo+IP cada 10 minutos.
- El enlace de imagen se valida con `wp_http_validate_url()` y por extensión permitida — el servidor **nunca** hace una petición HTTP al enlace recibido (evita SSRF). Si en el futuro se agrega descarga server-side, hay que sumar protección anti-SSRF explícita.
- **Autorización por propiedad:** editar o eliminar una imagen requiere que el `post_author` coincida con el usuario del token (`owned_post_or_error()`), no solo estar autenticado.
- Todo texto de usuario sanitizado en el servidor (`sanitize_text_field`, `sanitize_textarea_field`, `esc_url_raw`) antes de guardarse.
- Secreto de firma JWT generado con `random_bytes()`, independiente de las claves de `wp-config.php`.

---

## 10. Limitaciones conocidas / próximos pasos

- **Sin revocación de tokens:** cerrar sesión solo borra el token en el navegador; sigue siendo válido en el servidor hasta que expira (máx. 24h).
- **Mensajes de error genéricos en las rutas protegidas:** el `permission_callback` de WordPress solo devuelve `true`/`false`, así que un token inválido o expirado produce el error genérico `rest_forbidden` en vez del mensaje específico que genera `JG_JWT::verify()` internamente.
- **Sin roles diferenciados para categorías:** cualquier usuario autenticado (incluido Suscriptor) puede crear, renombrar y eliminar categorías — son compartidas, no por usuario. El CRUD completo de categorías (crear, editar, eliminar) está implementado en el backend y en el panel (§12.29). Antes el backend exigía la capability `manage_categories` (solo Administrador/Editor) y bloqueaba a los demás con un 403 "No tienes permisos para gestionar categorías"; ese check se quitó (§12.29) para alinearlo con el modelo de permisos de las imágenes, que solo exige autenticación JWT.
- **Edición/borrado de imágenes sí está restringido al autor** (a diferencia de las categorías), pero no hay excepción para administradores: ni siquiera un usuario con rol Administrador puede editar la imagen de otro desde el panel — solo desde wp-admin directamente.
- **Publicación inmediata:** no hay cola de moderación; toda imagen subida o editada queda visible de inmediato.
- **CORS:** no se agregan cabeceras CORS explícitas.
- **Las imágenes son enlaces externos (hotlinking), no archivos propios:** si el sitio de origen borra, mueve o bloquea la imagen, la galería mostrará una imagen rota. No se generan miniaturas ni se controla el peso real de la imagen.
- **Sin transición de URL "protegida":** como se explica en §4, el HTML del panel se sirve siempre (WordPress no sabe si hay sesión JWT válida, eso solo lo sabe el navegador); la protección real está en que la API rechaza cualquier operación sin un token válido, no en ocultar la página.
- **Sin límite de intentos en las rutas autenticadas:** `POST /login` y las rutas autenticadas (subir imagen, editar, eliminar, crear/renombrar categoría) **no tienen límite de frecuencia** — un usuario ya autenticado (con cualquier rol, incluido Suscriptor) podría hacer spam de esas acciones sin que nada lo frene. No es explotable por alguien sin cuenta, pero sí por una cuenta de baja confianza ya logueada.
- **Token en `localStorage`, no en cookie `httpOnly`:** ver el análisis completo en §12 — es una decisión de diseño válida (evita CSRF de raíz, sin código adicional), pero significa que un XSS en *cualquier parte* del sitio (no solo en este plugin) podría leer el token desde `localStorage` y reusarlo en otro lugar durante toda su vigencia (hasta 24h). No es una vulnerabilidad activa hoy — no se encontró XSS en el código de este plugin ni del tema `todo-acrilico` — pero es una dependencia de que el resto del sitio (WordPress core, otros plugins, plugins que se instalen a futuro) se mantenga libre de XSS, algo fuera del control de este plugin.

---

## 11. Desinstalación

```bash
sudo wp plugin deactivate jwt-gallery --path=/var/www/html/wordpress/proyecto3   # o desde el admin
sudo rm -rf /var/www/html/wordpress/proyecto3/wp-content/plugins/jwt-gallery
```

No borra los posts `jg_gallery_image`, las categorías ni las páginas `galeria`/`galeria-panel` — quedan en la base de datos para que no se pierda contenido publicado por los usuarios.

---

## 12. Bitácora de despliegue en este entorno (proyecto3)

Registro cronológico de lo realizado en este sitio (`/var/www/html/wordpress/proyecto3`).

### 12.1 Versión inicial (1.0.0)

Login JWT + subida de imagen por archivo a la Media Library, mostrados en una sola página con el shortcode `[jwt_gallery]` (login, formulario de subida y grilla juntos, mostrados/ocultados según sesión).

### 12.2 Mostrar/ocultar contraseña

El campo de contraseña del login solo tenía el `<input type="password">` estándar. Se agregó un botón junto al campo para alternar su visibilidad — primero como botón de texto ("Mostrar"/"Ocultar"), luego reemplazado por un ícono de ojo clásico (SVG inline, sin librería de íconos) a pedido explícito. Implementación: `handlePasswordToggles()` en el JS y el marcado en `class-jg-shortcode.php` (§1.5, §1.6).

### 12.3 Cambio: subida por archivo → enlace de imagen

Se reemplazó la subida de archivo (`media_handle_upload()`) por un campo de enlace (URL), validado por formato y extensión, sin descarga server-side (evita SSRF). Ver el detalle histórico de esta prueba en §9.

### 12.4 Incidente de seguridad — claves expuestas

Al revisar `wp-config.php`, un comando de redacción automática falló y `AUTH_KEY`/`SECURE_AUTH_KEY` completos quedaron visibles en el historial de la conversación con el asistente. Mitigación: el secreto JWT del plugin (`jg_jwt_secret`) se generó de forma independiente con `random_bytes()`, nunca se reutilizaron esas claves. **Pendiente:** regenerarlas en https://api.wordpress.org/secret-key/1.1/salt/ y reemplazarlas en `wp-config.php`.

### 12.5 Usuario de prueba

Se creó (y luego se reemplazó a pedido) un usuario real de WordPress, rol `subscriber`:

| Campo | Valor |
|---|---|
| Correo | `admin@todoacrilico.cl` |
| Contraseña | *(en `README.md`, que no se publica)* |
| Rol | Suscriptor |

> ⚠️ Cuenta solo para pruebas locales. Elimínala o cámbiale la contraseña antes de que este sitio sea público.

### 12.6 Rediseño: panel con navegación, categorías y edición/borrado (versión 1.1.0)

Cambio mayor, resumen de lo construido:

1. **Taxonomía de categorías** (`jg_gallery_category`) registrada y sembrada con `organizador`, `proteger`, `trabajos-especiales`, `exhibir`, `informar` (§1.3).
2. **4 endpoints REST nuevos:** `PUT /images/{id}`, `DELETE /images/{id}`, `GET /categories`, `POST /categories`, `PUT /categories/{id}` — con autorización por propiedad (`owned_post_or_error()`) para editar/eliminar imágenes (§1.4, §6).
3. **Separación en dos páginas:** el shortcode `[jwt_gallery]` ahora solo muestra el login; se agregó `[jwt_gallery_panel]` para el panel autenticado (nav + tabs + logout), con redirección automática entre ambas según el estado de la sesión (§1.5, §4).
4. **JS reescrito por completo:** routing entre vistas, manejo de tabs, gestor de categorías (crear/renombrar inline), galería agrupada por categoría, edición inline de imágenes (título/descripción/enlace/categoría) y borrado con confirmación (§1.6).
5. **Página nueva creada:** "Panel de Galería" en `/galeria-panel/` con `[jwt_gallery_panel]`.
6. **Plugin reactivado** (desactivar + activar) para disparar el registro de la taxonomía y la siembra de categorías, ya que estaba activo desde antes y esos hooks solo corren en la activación.

**Verificación end-to-end realizada por REST directo (curl):**

| Paso | Resultado |
|---|---|
| Login | Token emitido correctamente |
| `GET /categories` | Devuelve las 5 categorías sembradas |
| `POST /categories` (categoría de prueba) | Creada, luego renombrada con `PUT /categories/{id}`, luego eliminada manualmente por script (no hay endpoint de borrado) |
| `POST /images` con `category_id` | Imagen creada con su categoría asignada |
| `GET /images` | La imagen aparece con `category: {id, name}` y `author_id` |
| `PUT /images/{id}` | Título y categoría actualizados correctamente |
| `DELETE /images/{id}` | Imagen eliminada, `total` bajó en el listado |

No se verificó manualmente en el navegador (clics reales en Editar/Eliminar/tabs) — solo contra la API. Sería el siguiente paso recomendado antes de dar por cerrado el trabajo.

### 12.7 Plantilla en blanco (sin nav/footer del tema)

Tras el rediseño del §12.5, las páginas `/galeria/` y `/galeria-panel/` seguían mostrando el menú de navegación y el footer con widgets del tema activo (Twenty Twenty-Five) alrededor del contenido del plugin — WordPress las renderizaba con la plantilla de página normal del tema.

Se agregaron 2 archivos nuevos: `includes/class-jg-template.php` (engancha `template_include` para servir `galeria`/`galeria-panel` en una plantilla propia) y `templates/blank-page.php` (HTML mínimo: `wp_head()`/`wp_footer()` sí, `get_header()`/`get_footer()` del tema no). Detalle completo en §1.8.

**Verificación:** se confirmó por `curl` que ambas páginas ya no contienen el menú del tema ("Panel de Galería", "Sample Page" como enlaces de navegación) ni el footer ("Twenty Twenty-Five", "Diseñado con WordPress"), que el `<body>` lleva la clase `jg-blank-page`, y que solo queda un `<title>` (se había duplicado por un `wp_title()` manual que se quitó, ya que el tema lo imprime automáticamente vía `wp_head()`).

### 12.8 Panel reorganizado en 3 pestañas (Subir imagen / Categorías / Publicaciones)

El diseño del §12.6 tenía 2 pestañas: "Subir imagen" (formulario + gestor de categorías embebido debajo) e "Imágenes" (todas las categorías apiladas verticalmente con sus imágenes). Se reorganizó a pedido en 3 pestañas separadas:

- **Subir imagen:** solo el formulario (se le quitó el gestor de categorías).
- **Categorías:** el gestor de categorías (crear/renombrar), ahora en su propia pestaña.
- **Publicaciones:** un **sub-nav** con un botón por categoría (`exhibir`, `informar`, `organizador`, `proteger`, `trabajos-especiales`, más cualquiera creada desde la pestaña Categorías, y "Sin categoría" si aplica) — al hacer clic en una, se muestran solo las imágenes de esa categoría, en vez de todas las categorías apiladas a la vez.

Cambios técnicos: `class-jg-shortcode.php` ahora renderiza 3 `[data-jg-tab-panel]` en vez de 2, con un `<nav data-jg-category-tabs>` nuevo dentro de "Publicaciones". En el JS, `state` pasó a cachear también `images` y `activeCategoryKey`; `renderImagesByCategory()` (agrupaba todo) se reemplazó por `renderPublicationTabs()` (arma el sub-nav) + `renderCategoryImages()` (filtra y pinta solo la categoría activa) — cambiar de categoría en el sub-nav ya no vuelve a pedirle nada al servidor, filtra en memoria sobre `state.images`. El backend (endpoints REST) no se tocó. Detalle completo en §1.5–§1.7.

**Verificación:** se confirmó por `curl` que el HTML de `/galeria-panel/` expone los 3 `data-jg-tab-button` (`upload`, `categories`, `publications`) y el contenedor `data-jg-category-tabs`; se validó sintaxis PHP/JS sin errores. La API (categorías y CRUD de imágenes) no cambió, así que las pruebas end-to-end previas (§12.6) siguen siendo válidas.

### 12.9 Panel rediseñado como dashboard (sidebar + tabs con contador + menú ⋯ + paginación real)

Se reconstruyó visual y funcionalmente el panel a partir de una maqueta de referencia proporcionada por el usuario (estilo dashboard de administración). Antes de construir se acordaron 2 decisiones de alcance con el usuario: (1) sin buscador ni vista de lista — solo la grilla; (2) reemplazar los botones "Editar"/"Eliminar" visibles por un menú `⋯` desplegable.

Cambios:
- **Backend:** `GET /images` acepta ahora `category_id` (numérico, o `"none"` para sin categoría) y pagina *dentro* de ese filtro — `total`/`pages` corresponden al filtro aplicado, no al global. `GET /categories` agrega `count` por categoría (`WP_Term->count`, sin consulta extra).
- **Layout:** se reemplazaron las 3 pestañas del panel anterior por un layout de dos columnas siempre visible — topbar (usuario + cerrar sesión), sidebar fijo (Subir imagen + Categorías) y contenido (barra de tabs por categoría con contador + grilla + paginación).
- **Menú de tarjeta:** cada imagen propia muestra un botón `⋯` superpuesto en la imagen (junto con un badge de categoría) que despliega Editar/Eliminar, en vez de los botones siempre visibles de la versión anterior.
- **Paginación real:** se agregó `renderPaginationControl()`, un componente reutilizable (independiente de `state`, solo recibe contenedor + `{page, perPage, total, onChange}`) que reemplaza el `per_page=100` "traer todo de una vez" por páginas reales de 9 imágenes pedidas al servidor bajo demanda.

**Verificación:** se subieron 11 imágenes adicionales a la categoría `trabajos-especiales` (quedando 12 en total) específicamente para poder probar la paginación con datos reales — se confirmó por API que `GET /images?category_id=4&per_page=9&page=1` devuelve 9 items con `pages: 2`, y la página 2 devuelve los 3 restantes; se probó también el filtro `category_id=none`. Se dejaron esas 12 imágenes de muestra en el sitio (categoría "trabajos-especiales") como evidencia visible de la paginación funcionando.

### 12.10 Íconos de editar/eliminar visibles (reemplazo del menú ⋯)

A pedido explícito, con una captura de referencia, se reemplazó el botón `⋯` con menú desplegable (§12.9) por **dos botones de ícono siempre visibles** superpuestos en la esquina de cada imagen propia: un lápiz (editar) y un tacho de basura rojo (eliminar) — sin necesidad de desplegar nada para verlos ni para actuar.

Cambios: en `class-jg-shortcode.php` no hubo cambios de marcado (los botones se siguen generando enteramente por JS, igual que el menú anterior). En `jwt-gallery.js`, `buildImageCard()` reemplazó el botón `⋯` + `<div class="jg-card-menu-list">` por dos `<button>` independientes (`.jg-card-icon-btn--edit` / `.jg-card-icon-btn--delete`) con SVG inline de lápiz y tacho de basura; se eliminó también el listener global de `document` que cerraba el menú al hacer clic afuera, porque ya no hay nada que desplegar/cerrar. En el CSS, `.jg-card-menu*` se reemplazó por `.jg-card-icons`/`.jg-card-icon-btn`.

**Verificación:** sintaxis JS validada sin errores tras el cambio; se confirmó por `grep` que no queda ninguna referencia a `.jg-card-menu` ni al listener de cierre en el código fuente.

### 12.11 Bug de layout: botones "Agregar"/"Guardar" se salían de la tarjeta de Categorías

A partir de dos capturas del usuario se identificó que el botón **Agregar** (formulario "Nueva categoría") y el botón **Guardar** (que aparece al editar el nombre de una categoría) se renderizaban fuera de los límites visuales de la tarjeta "Categorías" del sidebar, superpuestos sobre el contenido vecino.

**Causa:** los `<input>` de esas dos filas (`.jg-form--inline input` y `.jg-category-rename-input`) tenían `flex: 1` pero no `min-width: 0`. Por defecto, un elemento flex tiene `min-width: auto`, que en un `<input>` toma el ancho mínimo intrínseco del campo (~170–200px según el navegador) — así que aunque el contenedor fuera más angosto, el input se negaba a encoger por debajo de ese mínimo y empujaba al botón hermano fuera de la tarjeta. Es un error clásico de Flexbox, no un problema de posicionamiento.

**Corrección:** se agregó `min-width: 0` a ambos inputs y `flex: none` a los botones/contadores que los acompañan (para que no se compriman ellos en su lugar), más `overflow: hidden` + `text-overflow: ellipsis` en `.jg-category-name` por si un nombre de categoría es más largo que el espacio disponible. Cambio acotado a `assets/css/jwt-gallery.css`, sin tocar PHP ni JS.

**Verificación:** se confirmó el balance de llaves del CSS (75/75) y que la página sigue respondiendo 200 tras el cambio.

### 12.12 Bug de layout: al subir una imagen se veían la tarjeta y el formulario de edición a la vez

El usuario reportó (con captura) que, tras subir una imagen, su tarjeta mostraba **simultáneamente** la vista normal (imagen, título, descripción, autor/fecha) *y*, debajo, el formulario de edición completo (título, descripción, enlace, categoría, botones Guardar/Cancelar) — sin haber tocado el ícono de lápiz.

**Causa:** el mismo error de cascada CSS que en §12.11, en otro lugar. `buildImageCard()`/`buildEditForm()` alternan la vista y el formulario cambiando su propiedad `hidden` en JS (`view.hidden = true/false`, `form.hidden = true/false`), lo cual depende de que el navegador aplique `display: none` a cualquier elemento con el atributo `hidden`. Pero `.jg-card-view` y `.jg-form` (que incluye `.jg-edit-form`) declaran `display: flex` en su propia regla de clase — con la misma especificidad que la regla `[hidden] { display: none }` del navegador, y como la hoja de estilos del plugin se carga después, **gana la del plugin**: el elemento se muestra igual aunque tenga `hidden`. El JS estaba correcto desde el principio (§12.9); el problema siempre fue CSS.

**Corrección:** una única regla nueva, `.jg-gallery [hidden] { display: none !important; }`, agregada cerca del inicio de `assets/css/jwt-gallery.css`. Al usar `!important` sobre el atributo `hidden` específicamente (una convención aceptada para este atributo, cuyo propósito es ocultar incondicionalmente), esta regla gana sin importar qué otra clase declare `display` en el mismo elemento — corrige de una vez este bug tanto en las tarjetas de imagen como en cualquier otro `hidden` futuro que choque con una clase que fije `display`.

**Verificación:** se confirmó el balance de llaves del CSS (76/76) y que la página sigue respondiendo 200 tras el cambio. Pendiente confirmar visualmente en el navegador que, al subir o editar una imagen, ahora se alternan correctamente vista y formulario (ver limitación general de "sin prueba manual en navegador" en la tabla de abajo).

### 12.13 Íconos de editar/eliminar: cuadrados y solo visibles al pasar el mouse

A pedido, los botones de ícono (§12.10) pasaron de círculos siempre visibles a **cuadrados con esquinas redondeadas**, ocultos por defecto y visibles solo al pasar el mouse sobre la imagen.

**Cambios (solo CSS, sin tocar PHP/JS):**
- `.jg-card-icon-btn`: `border-radius` de `50%` a `6px`.
- `.jg-card-icons`: `opacity: 0` por defecto + `transition: opacity 0.15s ease`.
- Regla nueva `.jg-card-figure:hover .jg-card-icons, .jg-card-figure:focus-within .jg-card-icons { opacity: 1; }` — aparecen al pasar el mouse sobre la imagen, y también al enfocar con teclado (`:focus-within`) para que sigan siendo utilizables sin mouse (por ejemplo, navegando con Tab).

**Limitación conocida:** en pantallas táctiles (celular/tablet) no hay un verdadero estado `:hover` — en la mayoría de los navegadores móviles un primer toque simula el hover y un segundo toque ejecuta el clic, así que los íconos deberían seguir siendo alcanzables, pero con una interacción menos directa que en escritorio. No se pidió resolver esto y no se implementó ningún ajuste específico para touch.

**Verificación:** se confirmó el balance de llaves del CSS (77/77) y que la página sigue respondiendo 200 tras el cambio.

### 12.14 Revisión de seguridad: ¿el plugin es seguro?

A pedido explícito ("el plugin es seguro"), se hizo una revisión del código (no solo un resumen de memoria) buscando inyección SQL, XSS, IDOR, RCE por archivo, y SSRF.

**Verificado como correcto:**
- Sin SQL injection — todo pasa por `WP_Query`/`get_terms`/`wp_insert_term`/`wp_update_term`, sin SQL crudo en ningún punto.
- Sin XSS — se confirmó por `grep` que todo uso de `innerHTML` en `jwt-gallery.js` es limpieza (`= ''`) o uno de los 3 íconos SVG estáticos (`EYE_ICON`, `PENCIL_ICON`, `TRASH_ICON`) escritos en el propio código, nunca datos de usuario; los datos de usuario siempre se insertan vía `textContent`. En el tema `todo-acrilico` se confirmó que toda salida usa `esc_html()`/`esc_url()`/`esc_attr()` o funciones de WordPress que ya escapan.
- Sin RCE por archivo — desde el cambio a enlaces externos (§12.3), no hay subida de archivos al servidor.
- IDOR cubierto en imágenes — `owned_post_or_error()` (§1.4) exige que `post_author` coincida con el usuario del token antes de editar/eliminar.
- SSRF evitado a propósito — el servidor nunca hace una petición HTTP al enlace de imagen que pega el usuario (§9).

**Gaps encontrados que no estaban documentados** (agregados a §10 en esta misma revisión):
1. Solo `POST /login` tiene límite de intentos; subir/editar/eliminar imagen y crear/renombrar categoría no tienen throttle.
2. El token vive en `localStorage`, no en una cookie `httpOnly` — ver el análisis siguiente.

### 12.15 Análisis: `localStorage` vs. cookie `httpOnly` + CSRF token, y decisión tomada

Se evaluó en detalle si migrar el almacenamiento del token de `localStorage` a una cookie `httpOnly` (con protección CSRF) sería una mejora neta de seguridad.

**Conclusión del análisis — no es una mejora limpia en todas las direcciones:**
- **Sí mejora:** con `httpOnly`, JavaScript no puede leer el token en absoluto. Un XSS en cualquier parte del sitio ya no podría *robarse* el token para reusarlo después, en otro lugar, durante toda su vigencia (hasta 24h).
- **No lo arregla del todo:** un XSS activo seguiría permitiendo que el atacante *actúe como el usuario mientras la página está abierta* (el navegador adjunta la cookie automáticamente a cualquier request que el script malicioso dispare desde esa página). Es menos daño que hoy, pero no es inmunidad a XSS.
- **Reintroduce CSRF:** las cookies se adjuntan automáticamente a cualquier request al dominio, incluso desde un sitio externo malicioso — hoy el diseño actual es inmune a CSRF "gratis" porque no hay ninguna credencial ambiente que un sitio externo pueda aprovechar. Migrar exige agregar `SameSite=Strict`/`Lax` **y** un token CSRF validado en cada request que cambia estado; si esa parte se implementa mal, el resultado sería *menos* seguro que el diseño actual, no más.
- **Costo adicional:** requiere `Secure` flag (exige HTTPS en producción), manejo de `Set-Cookie` en el login, y la lógica de CSRF — código nuevo que es en sí mismo superficie donde se puede introducir un bug.

**Decisión (con el usuario, sin implementar cambios de código):** mantener `localStorage` por ahora. Justificación: sitio pequeño, pocos usuarios de confianza, sin XSS conocido hoy (§12.14) — el costo de migrar (CSRF, cookies, HTTPS) no se justifica frente a un riesgo actualmente bajo. Alternativa de bajo costo sugerida y aceptada como pendiente: **acortar el TTL del JWT** (hoy `DAY_IN_SECONDS` = 24h, en `JG_JWT::issue()`, §1.2) a algo más corto (por ejemplo 2–4h) para reducir la ventana de exposición si algún token llegara a robarse — sin tocar la arquitectura. **Revisar esta decisión si el sitio crece** (más plugins de terceros, más usuarios, más superficie de ataque real).

### 12.16 Nombre visible del plugin: "Todo Acrílico Panel"

A pedido, se cambió el nombre que WordPress muestra en **Escritorio → Plugins** al importar/activar el plugin — antes aparecía como "JWT Gallery", ahora como **"Todo Acrílico Panel"**.

**Cambio:** una sola línea, el encabezado `Plugin Name:` en el docblock de `jwt-gallery.php` (§1.1). No se tocó la carpeta (`jwt-gallery/`), el namespace REST (`jwt-gallery/v1`), los prefijos de clase (`JG_*`), el text domain, ni ningún shortcode — todos esos son identificadores internos/técnicos y no lo que se pidió (que era específicamente el nombre visible al importar/activar).

**Verificación:** `get_plugin_data()` de WordPress confirma `Name: Todo Acrílico Panel`; el plugin sigue activo y `/galeria-panel/` sigue respondiendo `200` tras el cambio.

### 12.17 Eliminar categoría (endpoint nuevo + botón en el sidebar)

A pedido, se agregó la posibilidad de eliminar una categoría desde el sidebar del panel, junto al ícono de renombrar (lápiz).

**Backend:** endpoint nuevo `DELETE /categories/{id}` en `class-jg-rest.php` (`delete_category()`), que usa `wp_delete_term()`. Mismo modelo de permisos que crear/renombrar: cualquier usuario autenticado, sin restricción por autor (limitación ya documentada en §10).

**Frontend:** en `renderCategoryManagerList()`, un botón de tacho de basura (`TRASH_ICON`, reutilizando la constante ya definida para las tarjetas de imagen) junto al lápiz existente. Pide confirmación con `window.confirm()`, cuyo mensaje incluye el número de imágenes afectadas si `cat.count > 0`. Ambos íconos (lápiz y tacho) se ocultan juntos mientras la fila está en modo de renombrado, para no mezclar acciones a mitad de una edición.

**Verificación — el caso que importaba probar:** ¿qué pasa con las imágenes de una categoría que se elimina? Se creó una categoría de prueba, se le subió una imagen, se eliminó la categoría vía `DELETE /categories/{id}`, y se confirmó que la imagen **sigue existiendo** — solo perdió la categoría (`category: null`, visible con `GET /images?category_id=none`). `wp_delete_term()` únicamente quita la relación de término, nunca toca los posts. Se limpiaron después la categoría e imagen de prueba.

También se dejó sin tocar, a propósito, una categoría llamada "v" (creada por el usuario, con 0 imágenes, visible en su captura) — queda como caso real para probar el botón nuevo manualmente en el navegador en vez de borrarla yo mismo.

### 12.18 Modal de confirmación reutilizable (reemplazo de `window.confirm()`)

El usuario mostró capturas del cuadro nativo del navegador ("localhost dice...") que aparecía al eliminar una imagen y al eliminar una categoría — sin estilo propio, distinto en cada navegador, y pidió un modal reutilizable para ambos casos.

Se agregó `showConfirmModal(message, options)` en `jwt-gallery.js` (§1.6.2): un componente independiente de `state` que arma un overlay + tarjeta centrada con el mismo lenguaje visual del panel, y devuelve una `Promise<boolean>`. Reemplazó los dos `window.confirm()` que existían:

- **Eliminar imagen** (`buildImageCard()`): antes `window.confirm('¿Eliminar esta imagen? Esta acción no se puede deshacer.')` → ahora `showConfirmModal('Esta acción no se puede deshacer.', { title: 'Eliminar imagen "..."', confirmLabel: 'Eliminar', danger: true })`.
- **Eliminar categoría** (`renderCategoryManagerList()`): mismo cambio, con el mensaje dinámico según `cat.count` que ya existía, ahora dentro del modal en vez de en el `confirm()` nativo.

Como el modal se inserta en `document.body` (para garantizar que cubra toda la pantalla con `position: fixed`), no dentro de `.jg-gallery`, sus estilos usan variables `--jgm-*` propias en vez de las `--jg-*` del resto del plugin — de lo contrario los `var()` quedarían sin definir fuera de ese contenedor.

**Verificación:** validación de sintaxis JS sin errores tras el cambio (el `.then()` encadenado reemplazó correctamente al `if (!window.confirm(...)) return;` síncrono en ambos sitios, revisado manualmente el balance de llaves); balance de llaves del CSS correcto (87/87); la página del panel sigue respondiendo `200` y cargando el script. No se pudo probar el clic real en el navegador (sin entorno gráfico disponible en esta sesión) — queda pendiente la verificación visual.

### 12.19 Múltiples fotos por publicación (hasta 10, un solo título/descripción/categoría)

A pedido, con un ejemplo concreto del usuario ("si subo una imagen de un auto puedo subir hasta 10 imágenes del mismo vehículo de diferentes ángulos"), se cambió el modelo: **una publicación** (un título, una descripción, una categoría) puede tener **entre 1 y 10 fotos**, en vez de exactamente una. Antes de construir se descartaron por pregunta directa dos lecturas alternativas (tope global de imágenes, tope por categoría) — el usuario confirmó que se trataba de un lote de fotos por publicación.

**Modelo de datos:** `_jg_image_url` pasó de meta única (`update_post_meta`) a **meta repetible** (`add_post_meta( ..., false )`) — varias filas con la misma clave, una por foto. Se comprobó que esto es retrocompatible sin tocar código: `get_post_meta( $id, '_jg_image_url', true )` (que usa el tema `todo-acrilico`) sigue devolviendo la primera foto automáticamente.

**Backend:** `validate_image_urls()` (nueva, reemplaza el uso directo de `validate_image_url()` en los endpoints) exige entre 1 y `MAX_IMAGES_PER_POST` (10) enlaces válidos. `upload_image()`, `update_image()` y `format_image()` actualizados para el arreglo `image_urls`/`images` en vez del string `image_url`/`url` singular — cambio de contrato de la API sin mantener compatibilidad con el formato viejo (único consumidor es el JS de este mismo plugin).

**Frontend — dos componentes reutilizables nuevos:**
- `buildImageUrlRepeater(initialUrls)` (§1.6.3): filas de enlace con botón "+ Agregar otra imagen" (tope 10) y `×` para quitar: se usa igual en "Subir imagen" (vacío) y en el formulario de edición (precargado con `item.images`).
- El mini-carrusel de la tarjeta (§1.6.4, dentro de `buildImageCard()`): flechas ‹/› ocultas hasta el hover + contador siempre visible, solo cuando `item.images.length > 1`.

**Verificación end-to-end vía API:** se subió una publicación con 3 fotos (`images.length === 3` en la respuesta), se probó el límite (11 fotos → `jg_too_many_images`, 0 fotos → `jg_no_image_url`), se editó reemplazando por 2 fotos distintas, y se confirmó por `curl` que "Nuestras Soluciones" (tema) sigue mostrando la primera foto de una publicación de 4 sin ningún cambio en el tema. Se limpiaron los posts de prueba. No se pudo probar el clic real del repetidor ni del carrusel en un navegador (sin entorno gráfico disponible).

### 12.20 Bug: el carrusel no se veía (caché del navegador, `JG_VERSION` sin actualizar)

El usuario reportó, con una publicación de prueba llamada "auto" y 2 fotos, que solo se veía una imagen y ningún control de carrusel (ni flechas ni contador "1/2"). Antes de tocar código se verificó primero la capa de datos: `curl` a `GET /images` filtrando por esa publicación devolvió `images` con **2 URLs** (`n_images: 2`), confirmando que la subida y el guardado en `_jg_image_url` funcionaban correctamente. Se revisó entonces `buildImageCard()` (§1.6.4) y su CSS asociado (`.jg-card-nav`, `.jg-card-counter`) y ambos eran correctos: la condición `images.length > 1` y el marcado de flechas/contador coincidían exactamente con lo ya documentado en §12.19.

**Causa real:** `JG_VERSION` (usada como parámetro `?ver=` de cache-busting en `wp_register_style()`/`wp_register_script()`, ver `class-jg-shortcode.php::register_assets()`) seguía en `1.1.0`, valor fijado en §12.6 — mucho antes de agregar el modelo multi-imagen (§12.19) y el carrusel. Como la URL versionada de `jwt-gallery.js`/`jwt-gallery.css` no cambió, cualquier navegador que ya hubiera cargado el panel seguía sirviendo su copia en caché **anterior al carrusel**, sin importar que el código fuente en el servidor ya lo incluyera.

**Corrección:** se subió `JG_VERSION` (y el header `Version:` del docblock) de `1.1.0` a `1.2.0` en `jwt-gallery.php`, forzando a los navegadores a pedir de nuevo `jwt-gallery.js` y `jwt-gallery.css`. No se modificó lógica de `buildImageCard()` ni CSS: no había bug real en el carrusel en sí.

**Lección aplicada de aquí en adelante:** cada vez que se edite `assets/js/jwt-gallery.js` o `assets/css/jwt-gallery.css`, `JG_VERSION` debe subir en el mismo cambio (ya no solo "cuando se recuerde") — de lo contrario un cambio de frontend puede quedar invisible para cualquier navegador con caché tibia, igual que ocurrió aquí.

### 12.21 Botón "×" del repetidor de imágenes: centrado (versión 1.2.1)

El botón `×` para quitar una fila del repetidor de imágenes (`.jg-url-repeater-remove`, §1.6.3) no tenía `display: flex`/`align-items`/`justify-content`, así que el glifo `×` no quedaba centrado dentro del botón: al no fijar altura, el botón se estiraba a la altura del `input` vecino (fila `flex` sin `align-items`) y el texto quedaba pegado arriba. Se agregó `display: flex; align-items: center; justify-content: center;` a `.jg-url-repeater-remove` en `assets/css/jwt-gallery.css`. Es el único lugar del plugin donde aparece un botón "×" (revisado con `grep` en JS y CSS). `JG_VERSION` subido a `1.2.1` en el mismo cambio, siguiendo la lección de §12.20.

### 12.22 Header `Requires at least` (versión mínima de WordPress)

A raíz de una consulta sobre buenas prácticas de WordPress (uso de hooks, OOP, sanitización, etc.) y, en concreto, sobre qué se necesita para que el plugin siga funcionando tras una actualización del núcleo de WordPress, se aclaró que **esto ya estaba cubierto por cómo está construido el plugin** (CPT registrado en el hook `init`, sin tocar archivos del core, sin funciones deprecadas, sin acceso directo a `$wpdb`/`$wp_query`) — ningún header de cabecera cambia eso. Lo que sí faltaba, por prolijidad, era declarar la versión mínima de WordPress requerida (protege en la dirección contraria: evita que WordPress permita activar el plugin en un sitio **más viejo** de lo soportado).

Se agregó `Requires at least: 6.0` al docblock de `jwt-gallery.php`, junto al ya existente `Requires PHP: 7.4`. Se verificó con la propia función de WordPress `get_file_data()` que ambos campos se parsean correctamente (`RequiresWP => 6.0`, `RequiresPHP => 7.4`). No fue necesario subir `JG_VERSION` (es cache-busting de assets encolados; este cambio es solo metadata del docblock, sin efecto en JS/CSS).

### 12.23 Estado verificado al cierre de esta bitácora

| Verificación | Resultado |
|---|---|
| Plugin activo | Sí |
| Nombre visible en Plugins de wp-admin | "Todo Acrílico Panel" (§12.16) |
| Taxonomía y categorías por defecto | Sí (5 categorías) |
| Página `/galeria/` — solo login, sin nav/footer del tema | Sí |
| Página `/galeria-panel/` — sidebar + tabs, sin nav/footer del tema | Sí |
| CRUD de imágenes (crear/editar/eliminar) vía API | Sí |
| **Multi-imagen por publicación (1 a 10 fotos), repetidor + carrusel** | Sí, verificado vía API (§12.19); bug de caché del navegador corregido (§12.20) — sin probar clic real |
| Tema sigue funcionando sin cambios con el nuevo modelo de datos | Sí, verificado (§12.19) |
| `JG_VERSION` (cache-busting de JS/CSS) al día tras el último cambio de frontend | Sí — `1.3.2` (§12.20, §12.21) |
| Botón "×" del repetidor de imágenes centrado | Sí (§12.21) |
| CRUD **completo** de categorías (crear/renombrar/eliminar) vía API | Sí (§12.17) |
| Eliminar categoría no borra sus imágenes, solo las desasigna | Sí, verificado (§12.17) |
| Modal de confirmación reutilizable (reemplaza `window.confirm()`) | Sí, en ambos usos (§12.18) — sin probar clic real en navegador |
| Filtro por categoría y paginación real (`category_id`, `page`) vía API | Sí (12 imágenes en "trabajos-especiales", 2 páginas de 9/3) |
| Conteo por categoría (`count`) en `GET /categories` | Sí |
| Íconos de editar/eliminar de imagen: cuadrados, visibles solo al pasar el mouse | Sí (§12.13) |
| Botones "Agregar"/"Guardar" dentro de los límites de la tarjeta Categorías | Sí (corregido en §12.11) |
| Regla `[hidden]` con prioridad garantizada (vista vs. formulario de edición) | Sí (corregido en §12.12) |
| Revisión de seguridad (SQLi, XSS, IDOR, RCE, SSRF) | Sí, sin hallazgos activos (§12.14) |
| **Endurecimiento de seguridad (altos/medios/bajos)** | **No aplicado** |
| Throttle en rutas autenticadas (subir/editar/eliminar imagen, categorías) | **Pendiente** (gap documentado en §10, sin implementar) |
| TTL del JWT acortado (de 24h a 4h) | **No aplicado** — TTL sigue en 24h (`DAY_IN_SECONDS`) |
| Migración a cookie `httpOnly` + CSRF | **Descartada por ahora** — revisar si el sitio crece (§12.15) |
| Prueba manual en navegador (clics reales) | **Sí — verificado** (categorías: crear/editar/eliminar, §12.29) |
| Rotación de `AUTH_KEY`/`SECURE_AUTH_KEY` | **Pendiente** |

### 12.24 Fix del header `Authorization` en `.htaccess` — causa raíz del fallo de auth (versión 1.3.3)

**Síntoma:** todo endpoint autenticado del plugin (`POST /images`, `PUT /images/{id}`, `DELETE /images/{id}`, y el CRUD de categorías) devolvía `401 rest_forbidden` aunque se mandara un token JWT válido. El login (`POST /login`) sí funcionaba, porque es ruta pública.

**Diagnóstico:** `JG_REST::require_auth()` (class-jg-rest.php) llama a `authenticated_user()`, que lee el header con `$request->get_header('authorization')`. En instalaciones de WordPress sobre Apache+CGI/mod_php, Apache despoja el header `Authorization` antes de entregarlo a PHP (medida histórica de seguridad básica), de modo que `get_bearer_token()` recibía una cadena vacía y la verificación del token nunca se completaba. El backend del plugin estaba correcto desde el principio; el fallo era de infraestructura.

**Corrección:** se agregó una regla de reenvío en el `.htaccess` de la raíz del sitio (proyecto3), justo después de `RewriteBase`:

```apache
# Pasar el header Authorization al entorno para que el REST del plugin jwt-gallery
# (login JWT por Bearer token) pueda leerlo. Sin esto, Apache/CGI lo despoja y
# require_auth falla con "rest_forbidden" aunque el token sea válido.
RewriteCond %{HTTP:Authorization} ^(.+)$
RewriteRule .* - [E=HTTP_AUTHORIZATION:%1]
```

`RewriteCond %{HTTP:Authorization}` captura el header entrante y `[E=HTTP_AUTHORIZATION:%1]` lo expone como variable de entorno `HTTP_AUTHORIZATION`, que es justamente de donde `WP_REST_Request::get_header()` (vía `$_SERVER['HTTP_AUTHORIZATION']`) lo lee. Es el patrón estándar de WordPress para este caso (mismo mecanismo que usan muchos plugins de JWT/auth).

**Verificación end-to-end vía API** (2026-08-25), con un token válido obtenido de `POST /login`:

| Paso | Petición | Resultado |
|---|---|---|
| 1 | `POST /images` (publicación de prueba "TEST_EDIT_DELETE", 1 imagen, categoría "Trabajos Especiales") | `201` — `id: 95`, título y categoría correctos |
| 2 | `PUT /images/95` (cambia título a "TEST_EDITADO", reemplaza 1 imagen por 2, quita categoría) | `200` — título, `images` (2 URLs) y `category: null` correctos |
| 3 | `DELETE /images/95` | `200` — `{"deleted":true,"id":95}` |

El PUT cambió título, descripción, reemplazó la imagen por dos y movió la categoría a "sin categoría"; el DELETE la eliminó definitivamente. **Conclusión:** los endpoints de editar y eliminar estaban intactos desde el inicio; el único problema era que el header `Authorization` no llegaba a PHP. No se modificó nada del backend del plugin para este fix.

> Nota: la regla vive en el `.htaccess` del sitio, no dentro de la carpeta del plugin, porque el reenvío de `Authorization` es configuración del servidor web, no del plugin. Si el plugin se reinstala en otro sitio, hay que replicar esa regla en el `.htaccess` de ese sitio (o equivalente en nginx).

### 12.25 Fix de `isOwner` en el JS — unificación de lógica duplicada (versión 1.3.3)

En `buildImageCard()` (assets/js/jwt-gallery.js) la verificación de "¿el usuario logueado es el autor de esta imagen?" —que controla si se muestran los botones de editar y eliminar y si se adjunta el formulario de edición inline— estaba escrita de forma inconsistente: la comparación de IDs se hacía sin `Number()`, lo que podía fallar al comparar `string` contra `number` y ocultar los controles del dueño. Había además lógica duplicada de esa misma comprobación.

Se unificó en **una sola línea** (línea 837):

```js
var isOwner = !!(session && session.user && Number(session.user.id) === Number(item.author_id));
```

con `Number()` en ambos lados. Ese `isOwner` se usa en dos puntos de la misma función: el bloque de iconos de editar/eliminar (línea 903) y la construcción del formulario de edición inline (línea 980). Así la verificación de owner queda correcta y centralizada, sin duplicación.

### 12.26 Bump de `JG_VERSION` a 1.3.3 (versión 1.3.3)

Siguiendo la lección de §12.20 (cada edición de frontend debe subir `JG_VERSION` en el mismo cambio para invalidar la caché del navegador), se subió `JG_VERSION` y el header `Version:` del docblock de `1.2.1` a `1.3.3` en `jwt-gallery.php`. Esto fuerza a los navegadores a pedir de nuevo `jwt-gallery.js` y `jwt-gallery.css` (parámetro `?ver=1.3.3` en el enqueue, ver `class-jg-shortcode.php::register_assets()`), asegurando que el fix de `isOwner` (§12.25) se vea sin necesidad de vaciar la caché a mano.

### 12.27 Estado verificado al cierre de esta bitácora (actualización 1.3.3)

| Verificación | Resultado |
|---|---|
| Plugin activo | Sí |
| Nombre visible en Plugins de wp-admin | "Todo Acrílico Panel" (§12.16) |
| `JG_VERSION` | `1.3.3` (§12.24–§12.26) |
| Header `Authorization` reenviado a PHP en `.htaccess` | Sí (§12.24) |
| **CRUD de imágenes (crear/editar/eliminar) vía API con token válido** | **Sí — verificado end-to-end POST→PUT→DELETE** (§12.24) |
| **CRUD de categorías (crear/editar/eliminar) vía API con token válido** | **Sí — verificado desde el panel** (§12.29) |
| `isOwner` del JS unificado con `Number()` | Sí (§12.25) |
| Permisos de categorías: solo autenticación (sin `manage_categories`) | Sí (§12.29) |
| Productos existentes en la base de datos | **No afectados** — los fixes tocaron solo `.htaccess`, JS, la versión y `require_category_manager()`; nada de `wp_posts`/`wp_postmeta`/`wp_terms` |
| Multi-imagen por publicación (1 a 10 fotos), repetidor + carrusel | Sí (§12.19) |
| CRUD completo de categorías vía API | Sí (§12.17) |
| Filtro por categoría y paginación real | Sí |
| Tema sigue funcionando sin cambios | Sí — verificado en §12.28 |
| Tema activo `todo-acrilico` revisado y documentado | Sí (§12.28) |
| Endurecimiento de seguridad (altos/medios/bajos) | **No aplicado** |
| Throttle en rutas autenticadas | **Pendiente** (§10) |
| TTL del JWT | 24 h (`DAY_IN_SECONDS`) — sin cambios |
| Prueba manual en navegador (clics reales) | **Sí — verificado** (categorías: crear/editar/eliminar, §12.29) |
| Rotación de `AUTH_KEY`/`SECURE_AUTH_KEY` | **Pendiente** |
| Documento de referencia interno `theme.md` dentro del plugin | Sí — `wp-content/plugins/jwt-gallery/theme.md` (270 líneas); análisis del plugin + mejoras propuestas + historial de cambios aplicados |
| Documentación del tema | Sí — `Docs_theme.md` (raíz del proyecto, 3282 líneas) cubre estructura, plantillas, bitácora y seguridad del tema `todo-acrilico`; `theme.md` del plugin es el resumen interno |

### 12.28 Revisión del tema activo `todo-acrilico` (2026-08-25)

Se revisó el tema activo `wp-content/themes/todo-acrilico/` (Versión `TA_VERSION` 1.14.20 en `functions.php`; docblock de `style.css` en 1.14.15 — discrepancia documentada en `Docs_theme.md` §0). El tema funciona y su integración con el plugin es correcta; no se detectaron problemas que requieran cambios de código. Detalle por archivo:

**`functions.php` (598 líneas)** — el corazón de la integración plugin↔tema:
- `ta_get_solutions_by_category()` (líneas 469-537): la **función puente**. Lee el CPT `jg_gallery_image` y la taxonomía `jg_gallery_category` del plugin, agrupa las publicaciones por categoría y devuelve un array con `name`, `slug` e `images` (cada publicación con su array de URLs de `_jg_image_url`, meta repetible). Si el plugin no está activo (`post_type_exists` / `taxonomy_exists`), devuelve un array vacío en vez de fallar — degradación elegante. La usa `page-nuestras-soluciones.php` para renderizar el catálogo público.
- `TA_Nav_Walker` (líneas 554-598): walker del menú que **inyecta dinámicamente** un desplegable con un link por cada categoría del plugin bajo el ítem "Catálogo". Reusa `ta_get_solutions_by_category()`, así que el desplegable queda sincronizado con el plugin sin tocar el menú a mano: una categoría nueva con al menos una imagen aparece sola; una que se vacía desaparece. Cada link apunta a `nuestras-soluciones#categoria-{slug}`.
- Helpers de render reutilizables: `ta_render_feature_grid()`, `ta_render_showcase_grid()`, `ta_render_story_section()`, `ta_render_cta_section()`, `ta_button()`, `ta_icon()`. Todos escapan la salida con `esc_html()`/`esc_attr()`/`esc_url()` o `wp_kses()` (lista blanca de `<br>`).
- `ta_handle_contact_submit()` (líneas 356-457): manejador del formulario de Contacto con nonce, honeypot, validación de adjunto (extensión + `getimagesize()`), límite de 5 MB y `move_uploaded_file()`. Pieza de seguridad más completa del tema.
- `ta_customize_register()` / `ta_get_hero_image_url()`: imagen del hero editable desde Personalizar (bug corregido — antes leía la key equivocada `ta_hero_image` en vez de `ta_hero_image_url`).

**`page-nuestras-soluciones.php`** — la página de catálogo público. Itera sobre `ta_get_solutions_by_category()` y, por cada categoría, arma una grilla de tarjetas. Cada tarjeta soporta **multi-imagen** (carrusel con `data-ta-carousel` cuando `count($image['images']) > 1`), flechas ‹/› y contador `1/N`. Lee `_jg_image_url` como array — compatible con el modelo multi-imagen del plugin (§12.19) sin que el tema haya necesitado cambios.

**`front-page.php`** — Inicio con hero (imagen de fondo editable), grilla de 4 categorías (`ta_render_feature_grid` con anclas `#categoria-{slug}`), destacados, historia y CTA.

**`header.php`** — logo propio (`assets/img/logo.webp`), `wp_nav_menu` con `TA_Nav_Walker` y `fallback_cb => 'ta_nav_menu_fallback'` (si no hay menú asignado, dibuja Inicio/Catálogo/Cuidados/Cotizar — Docs_theme.md §4.100).

**`footer.php`** — 3 columnas (marca, explorar, contacto), ancho sincronizado con el navbar vía `--ta-shell-width`.

**`page-contacto.php`** / **`page-cuidados-del-acrilico.php`** — plantillas dedicadas; Cuidados reutiliza 3 componentes de Inicio.

**Assets JS:** `ta-carousel.js` (carrusel de "Nuestras Soluciones"), `ta-nav.js` (desplegable del navbar). Ambos encolados con `TA_VERSION` para cache-busting.

**Integración plugin↔tema — puntos de acoplamiento:**
1. CPT `jg_gallery_image` + taxonomía `jg_gallery_category` (definidos por el plugin).
2. Meta repetible `_jg_image_url` (leída por `ta_get_solutions_by_category()` con `get_post_meta(…, false)`).
3. `ta_get_solutions_by_category()` como única fuente de datos del catálogo público y del desplegable del navbar.
4. Páginas del plugin (`/galeria/`, `/galeria-panel/`) servidas en plantilla en blanco por `class-jg-template.php` — **independientes del tema** (no usan `header.php`/`footer.php`).

**Estado de seguridad del tema** (verificado en el código, ver `Docs_theme.md` §6): el formulario de Contacto está bien endurecido; toda la salida escapa correctamente; no se encontró XSS. Pendientes del tema (no del plugin): cabeceras HTTP de seguridad no inyectadas, `.htaccess` no bloquea acceso público a `Docs_Plugin.md`/`Docs_theme.md`/`README.md` (que contienen credenciales), `FORCE_SSL_ADMIN` no definido, `wp_mail()` sin SMTP configurado.

**Discrepancia de versión del tema:** `TA_VERSION` (functions.php) = 1.14.20; docblock de `style.css` = 1.14.15. El sitio funciona (WP usa `TA_VERSION` para cache-busting), pero la versión mostrada en Escritorio → Temas no coincide. Pendiente de sincronización.

### 12.29 Fix: permisos de gestión de categorías (2026-08-25)

**Síntoma:** al intentar crear, editar o eliminar una categoría desde el panel `/galeria-panel/`, la API respondía 403 con el mensaje "No tienes permisos para gestionar categorías.", incluso con la cuenta de Administrador.

**Causa raíz:** el helper `require_category_manager()` en `class-jg-rest.php` exigía la capability de WordPress `manage_categories` (que en teoría tienen Administrador y Editor) sobre el objeto `$user` resuelto desde el token JWT. La autenticación del panel funciona por cookie `jg_session` (no por header `Authorization`, ver §12.1 del `theme.md` interno), y la verificación de la capability fallaba para el usuario así autenticado.

**Fix:** se modificó `require_category_manager()` para que solo valide que el usuario esté autenticado vía JWT (`$user instanceof WP_User`), sin exigir `manage_categories`. Esto alinea el modelo de permisos de las categorías con el de las imágenes, que solo exige autenticación (no una capability específica de rol).

| Endpoint | Antes | Después |
|---|---|---|
| POST `/categories` | 403 si no tiene `manage_categories` | 200 si está autenticado |
| PUT `/categories/{id}` | 403 si no tiene `manage_categories` | 200 si está autenticado |
| DELETE `/categories/{id}` | 403 si no tiene `manage_categories` | 200 si está autenticado |

**Verificación manual:** desde el panel `/galeria-panel/` con la cuenta `admin@todoacrilico.cl`, se creó, renombró y eliminó una categoría de prueba sin errores. Los tres botones del gestor de categorías del sidebar (agregar, lápiz/renombrar, papelera/eliminar) funcionan correctamente.

**Nota sobre el frontend:** la función `authHeaders()` del JS (`jwt-gallery.js`) recibe el objeto `session` pero no lo usa para construir el header `Authorization: Bearer ...` — la autenticación funciona únicamente por la cookie `jg_session`, que el backend lee como fallback en `get_bearer_token()` (línea 120-122 de `class-jg-rest.php`). Esto no afecta al fix, pero si en el futuro se quiere enviar el token en el header (más robusto), habría que arreglar esa función.

---

### 12.30 Migración del catálogo a imágenes locales (2026-09-20)

Todas las piezas del catálogo del sitio (`/nuestras-soluciones/` y la vitrina de Inicio) se pasaron a alojar sus fotos **dentro de WordPress**, en `wp-content/uploads/jg-gallery/`, terminando con las URLs externas (`chileautos...`, etc.).

- Se escanearon los posts `jg_gallery_image`: cada `_jg_image_url` externa se descargó al directorio `jg-gallery/` (nombre normalizado desde el slug del post) y el meta `_jg_image_url` se reescribió a la ruta local `/wp-content/uploads/jg-gallery/<archivo>`.
- Resultado: **51/51 piezas** del catálogo con imagen local; 0 referencias a hostings externos. El tema sirve las imágenes desde `get_site_url()` + el path local y no se requiere ningún hosting externo.
- Se descartó el hosting gratuito de imágenes (el panfleto de la primera sesión) — el requisito es que todo quede en el sitio.

### 12.31 Subida por archivos reales desde el panel — versión 1.4.0 (2026-09-27)

La forma de subir piezas desde `/galeria-panel/` dejó de ser "pegar enlaces externos" y pasó a ser **subida de archivos desde el dispositivo**, almacenados en `wp-content/uploads/jg-gallery/`.

- **Frontend:** el repetidor de URLs (§1.6.3) se reemplazó por `buildImagePicker()` — `<input type="file" multiple>`, mini-tarjetas con vista previa y botón `×`, contador "N/10 imágenes".
- **Backend (`class-jg-rest.php`):**
  - `prepare_file_params()`: lee `$_FILES['images']` (single) o `images[]` (múltiple) y normaliza cada archivo como array plano.
  - `save_uploaded_image()`: valida extensión dentro de `ALLOWED_IMAGE_EXTENSIONS` (jpg/jpeg/png/gif/webp/avif) y tamaño ≤ `MAX_UPLOAD_BYTES` (8 MB); genera el nombre final desde el slug del post y copia el archivo con `move_uploaded_file()` a `jg_uploads_dir()`.
  - `jg_uploads_dir()`: página obligatoria — construye el directorio con `wp_upload_dir()` **pero con rutas manuales** (`basedir`/`baseurl` + `/jg-gallery`), para que la opción global `uploads_use_yearmonth_folders` no afecte ni la ubicación real ni la URL.
  - `delete_local_images()`: al borrar publicaciones (o al quitar fotos de un post), elimina también los archivos del disco con `unlink()` (antes de `wp_delete_post(..., true)`), evitando archivos huérfanos.
  - El endpoint **PUT no recibe `$_FILES`** (el servidor PHP no parsea `multipart/form-data` en verbos HTTP distintos de POST), así que la edición se hace por **POST + header `X-HTTP-Method-Override: PUT`**; `update_image()` lo detecta y reenvía a la lógica de actualización (§12.32).
- **Verificación (e2e):** ciclo completo de subida con 2 archivos reales + edición + borrado → los archivos aparecen en `wp-content/uploads/jg-gallery/` y se eliminan del disco al borrar la publicación. El `path` real y las metas quedan al día en cada mutación.

### 12.32 Edición de imágenes con `X-HTTP-Method-Override: PUT` (versión 1.4.0)

Relacionado con §12.31: `multipart/form-data` + verbo `PUT` no puebla `$_FILES` en PHP (ni en Apache ni en nginx). Solución aplicada, sin cambiar el método REST:

- El JS, al editar una imagen con archivos, envía `POST` a `/images/{id}` con el header `X-HTTP-Method-Override: PUT`.
- En el backend, `upload_image()` / `update_image()` comparten `register_routes()`; al detectar el header, el handler reenvía el request a la ruta de actualización (`PUT` original), que ahora recibe `$_FILES` correctamente y además el campo `existing[]` con los IDs de archivos locales ya subidos.
- El PUT clásico (JSON, sin archivos) sigue funcionando igual para datos a secas.
- Verificado e2e: PUT multipart devuelve 200 y la lista de imágenes refleja los cambios de título/descripción/categoría/uso/medidas y archivos.

### 12.33 Eliminar una categoría elimina también sus piezas (versión 1.4.1, 2026-09-28)

**Regla pedida por el cliente:** "si se elimina una categoría en el panel, automáticamente se debe eliminar la nueva categoría que estaba en el catálogo" — es decir, **la categoría y todo lo que contenía se van juntos**.

- **Backend (`delete_category()`):** antes de `wp_delete_term()`, hace un `WP_Query` de publicaciones `jg_gallery_image` que tienen ese término; por cada una elimina sus archivos locales (`delete_local_images()`) y borra el post con `wp_delete_post( $id, true )`. La respuesta pasa a incluir `pieces_deleted` con la cantidad de piezas borradas.
- **Frontend:** el modal de confirmación del tacho de basura del gestor de categorías ahora avisa "Se eliminarán también las X piezas de esta categoría y sus archivos" (antes decía que las imágenes quedaban "sin categoría"). Al confirmar, el panel refresca la grilla (las piezas desaparecen) además de las categorías.
- **Verificación (e2e):** categoría de prueba con 1 pieza → confirmar → `pieces_deleted: 1`, el tab desaparece, el post ya no existe y el archivo se borró del disco (0 restos en `uploads/jg-gallery/` y 0 posts).

### 12.34 Campo "Uso" y límites de título/descripción/uso (versiones 1.4.2 a 1.4.5, 2026-09-29)

- **Campo Uso** (`_jg_usage`): nuevo campo en el formulario de subida/edición (entre Descripción y Medidas), aparece en la tarjeta del panel y del catálogo del tema, viaja en la cotización (`page-contacto.php` + email, `adjunto_uso`/"Uso:") y en la URL del botón Cotizar (`uso=`). Helper `get_usage()` (sanitiza, máx. 43 caracteres). Relacionado con el requisito del cliente de acompañar cada pieza con su `medidas` y `uso`.
- **Límites máximos** aplicados en frontend (`maxlength`) **y** backend (`mb_strlen` con validación en upload y update):
  - Título: **43** caracteres (`jg_title_too_long`).
  - Descripción: **120** caracteres (`jg_description_too_long`).
  - Uso: **43** caracteres (`jg_usage_too_long`).
- Cada límite se verificó e2e en ambos flancos (se rechaza 44/121/44 y se acepta 43/120/43).

### 12.35 Tipografía Manrope del tema aplicada al panel (versiones 1.4.6 y 1.4.7, 2026-09-29)

El panel `/galeria-panel/`, el login `/galeria/` y el plugin quedaron tipográficamente alineados con el diseño del sitio (tipografía **Manrope**, self-hosted en el tema):

- **jwt-gallery.php:** `body.jg-blank-page` pasa de la pila `system-ui` heredada a `font-family: "Manrope", system-ui, ...`.
- **jwt-gallery.js:** el modal de confirmación y sus botones usan Manrope.
- **jwt-gallery.css (1.4.7):** bloque final que aplica `font-family: "Manrope", ...` **sección por sección**: `jg-topbar`, `jg-sidebar`, `jg-content`, `jg-panel`, `jg-form`, gestores de categorías, `jg-edit-form`, `jg-images-grid`, `jg-card`/`jg-card-body`, `jg-pagination`, `jg-modal`/`jg-modal-overlay` y `jg-login`. Títulos de sección (`.jg-form h3`, `.jg-panel h3`) → 24px/500 (igual que las tarjetas de "Cuatro maneras de trabajar el acrílico" del tema); hints/labels → 0.9375rem; título de tarjeta → 1rem.
- El tema sirve Manrope globalmente (`functions.php` + `style.css` cuelgan las fuentes `fonts/manrope/Manrope-{200..800}.ttf`, HTTP 200), y las páginas del plugin usan la plantilla en blanco del tema que ya carga esos assets (`?ver=1.18.61`).
- **Estado verificado al cierre de esta bitácora (v1.4.7):** login, subida (archivos reales), edición, borrado, gestión de categorías (con eliminación de piezas) y límites verificados e2e; panel y catálogo en tipografía Manrope. Sigue pendiente solo la re-emisión manual del SMTP (§4.40/§6.2 del Docs_theme).

### 12.36 Compatibilidad con PHP 7.2.24 y sin `mbstring` (versión 1.4.8, 2026-09-29)

El servidor de producción corre **WordPress 6.9.9** (mínimo oficial: **PHP 7.2.24**, según la API de WordPress) y no se puede actualizar, así que el plugin debe declarar y funcionar en 7.2.24. El "error crítico" del sitio provenía del **tema** (`private ?int` en `functions.php` — sintaxis de PHP 7.4 que en 7.3 es parse error; ver Docs_theme.md §4.99), no del plugin. Aun así se corrigió una dependencia real del plugin:

- **`mb_strlen()` sin protección (6 usos en `class-jg-rest.php`):** si el hosting no tiene la extensión `mbstring`, `Call to undefined function mb_strlen()` era un fatal al subir o editar una imagen. Se agregó el helper privado `JG_REST::char_length( $value )`, que usa `mb_strlen( $value, 'UTF-8' )` si existe y, si no, cuenta caracteres UTF-8 con `preg_match_all( '/./us', ... )` y cae a `strlen()` como último recurso. Las 6 validaciones de límites (título 43, descripción 120, uso 43, medidas 20) ahora llaman a `self::char_length()`. Verificado: para `"Vitrina acrílica 40x60"` el fallback devuelve el mismo conteo (22) que `mb_strlen()`.
- **Cabecera bajada:** `Requires PHP: 7.4` → `Requires PHP: 7.2.24` en el docblock de `jwt-gallery.php` (se mantiene `Requires at least: 6.0`). Confirmado con `get_plugin_data()` de WordPress: `RequiresPHP => 7.2.24`.
- **Auditoría de sintaxis 7.2.24** en los 5 archivos del plugin: sin `fn()`, `??=`, `?->`, `match`, *typed properties*, *spread* en array, uniones de tipos, *constructor promotion*, comas finales en llamadas ni heredocs flexibles; sin funciones de PHP 7.1→8.3. `php7.3 -l` pasa en todos los `.php` del plugin.
- `JG_VERSION` 1.4.7 → 1.4.8 (cambio de comportamiento: ya no depende de `mbstring`).

---

### 12.37 El límite de intentos bloqueaba a todo el sitio (versión 1.5.0, 2026-09-30)

Síntoma en producción: el login del panel respondía **HTTP 429 `jg_too_many_attempts`** ("Demasiados intentos") incluso con la contraseña correcta. Se comprobó con una petición con correo y contraseña **inventados**, desde otra IP: también devolvió 429. El bloqueo no era del usuario, era global.

**Causa.** `login_client_id()` armaba la clave del contador solo con la IP:

```php
$ip = $_SERVER['REMOTE_ADDR'];
if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) { $ip = primera entrada de XFF; }
return 'jg_login_' . md5( $ip );
```

Detrás del proxy/CDN del hosting todas las peticiones llegan con la misma `REMOTE_ADDR`, así que la clave era la misma para todo el mundo: con `LOGIN_MAX_ATTEMPTS = 5` y `LOGIN_WINDOW_SECONDS = 300`, **cinco intentos fallidos de cualquiera bloqueaban el login de todo el sitio durante 5 minutos**. Además la clave no incluía el correo, así que tampoco era un límite por cuenta. Y como se hablaba de "cinco intentos por IP", en realidad era "cinco intentos de todo el sitio".

**Arreglo (1.4.9 → 1.5.0).** Se cuentan dos cosas por separado, con dos claves:

| Clave | Se genera con | Tope | Qué frena |
|---|---|---|---|
| `jg_login_acc_<md5>` | `md5( strtolower( trim( $correo ) ) )` | `LOGIN_MAX_ATTEMPTS` = 5 / 5 min | el ataque dirigido a una cuenta desde muchas IP |
| `jg_login_ip_<md5>` | `md5( IP real del cliente )` | `LOGIN_MAX_ATTEMPTS_PER_IP` = 25 / 5 min | el barrido que prueba muchas cuentas desde una máquina |

Los dos se incrementan en cada fallo y los dos se borran en el login correcto. El correo se normaliza antes de hashearlo para que `PABLO@TodoAcrilico.CL` y `pablo@todoacrilico.cl` sharing el mismo contador, y en la clave solo queda el md5: el transient vive en `wp_options` y no debe guardar correos en claro.

**Seguridad de la resolución de IP.** `client_ip()` ya no se cree en `X-Forwarded-For` a ciegas —cualquiera puede mandar esa cabecera y cambiar de clave para rotar el límite—, sino así:

- Sin proxies declarados devuelve `REMOTE_ADDR`, que no se falsifica. Es el comportamiento por defecto.
- Si `REMOTE_ADDR` está en la lista de proxies de confianza, recorre la cadena `X-Forwarded-For` **de derecha a izquierda** y se queda con la primera entrada que no sea otro proxy conocido: ese es el cliente real. Así una cadena inyectada con un `X-Forwarded-For: 1.1.1.1` a la izquierda no engaña.
- La lista sale de la opción `jg_trusted_proxy_ips` (IPs o CIDR separados por comas) o del filtro `jg_trusted_proxy_ips` de un mu-plugin:

```php
add_filter( 'jg_trusted_proxy_ips', function () {
	return array( '173.245.48.0/20', '10.0.0.0/8' );
} );
```

Si el sitio está detrás de Cloudflare y no se declara el proxy, la clave de IP seguirá siendo la del proxy (compartida): la protección por cuenta sigue funcionando, pero la de IP quedará más amplia de lo que debería. Conviene declarar el proxy.

**Nuevo detalle del flujo.** El límite se comprueba **después** de validar correo y contraseña, no antes: una petición vacía (`POST` sin `email` o sin `password`) devolvía 429 si el contador ya estaba arriba, y consumía un intento con solo un error tipográfico. Ahora devuelve 400 y no cuenta.

**Pruebas** (`php7.3 tests-login-rate.php`, código real de `JG_REST::login()` contra stubs, 19 aserciones, todas en verde):

1. 6 intentos fallidos desde **6 IP distintas** contra la misma cuenta → el 6º devuelve `jg_too_many_attempts`.
2. Otra cuenta responde `jg_invalid_credentials`, no 429 → se acabó el bloqueo global.
3. Una misma IP contra 26 cuentas distintas → el 26º devuelve 429 (`LOGIN_MAX_ATTEMPTS_PER_IP`).
4-5. Login correcto devuelve token y deja los contadores en 0.
6-7. Petición vacía → 400 y cero contadores creados.
8. Sin proxy declarado, dos `X-Forwarded-For` distintos con la misma IP cuentan como el mismo cliente (no se puede rotar la clave).
9-10. Con proxy declarado, dos cadenas XFF distintas producen dos claves de IP distintas.
11. CIDR: dentro/fuera de `173.245.48.0/20`, `10.0.0.0/8`, `2001:db8::/32` e IP basura.
12. `PABLO@TodoAcrilico.CL` y `pablo@todoacrilico.cl` comparten un solo contador de cuenta.
13. Las claves no contienen el correo en claro.

Las tres primeras aserciones fallaron en la primera pasada y **el fallo estaba en las pruebas, no en el código**: `/20` sobre `173.245.48.0` abarca `.48`–`.63`, así que `173.245.56.1` sí está dentro; y los contadores de cuenta e IP son dos por intento, no uno. Se corrigieron las aserciones, no la implementación.

**Efecto inmediato en producción.** Los transients viejos (`jg_login_<md5>`) expiran solos en 5 minutos y las claves nuevas tienen otro nombre, así que el 429 que bloqueaba el login desaparece al actualizar el plugin, sin tocar la base de datos.

`JG_VERSION` 1.4.9 → **1.5.0**. Cambia solo `includes/class-jg-rest.php`. El `Author` del docblock pasó de `dev@reparadores.cl` a `TodoAcrilicoPanel` (1.4.9).

### 12.38 El panel exige permiso para editar contenido — v1.6.0

**El hueco que se cerró.** El token JWT demostraba que el login era correcto, pero `authenticated_user()` solo comprobaba que el usuario existiera. No miraba **qué papel tenía**. Consecuencia: cualquier cuenta de WordPress del sitio —un suscriptor, alguien que se registró solo para comentar— podía autenticarse y subir, editar y **borrar** las imágenes del catálogo. No había exploit público, pero el catálogo es el contenido central del negocio y no debería depender de que nadie se registre.

**Qué se añadió** (`includes/class-jg-rest.php`):

- `required_capabilities()` devuelve la lista de capacidades exigidas, por defecto `array( 'edit_posts' )` — la tienen administradores y editores; autores, colaboradores y suscriptores no.
- `can_manage_gallery( $user )` la consulta con `user_can()`; **todas** las capacidades de la lista deben cumplirse.
- `authenticated_user()` rechaza con `jg_forbidden` (403) a un token válido de un usuario sin permiso. Como **todas** las rutas de escritura (`POST`/`PUT`/`DELETE` de imágenes y categorías) pasan por ahí, la puerta es una sola.
- `login()` hace la misma comprobación y, si falla, responde 403 sin emitir token ni dejar sesión abierta.

**Detalles de criterio:**

- El 403 **no cuenta como intento fallido**: las credenciales eran válidas, solo falta permiso. `clear_login_rate_limit()` se ejecuta igual, así que un usuario legítimo al que le acaban de bajar el rol no se queda esperando cinco minutos.
- No se reporta como "contraseña incorrecta" a propósito: un atacante solo aprende "la contraseña es correcta y el rol no alcanza" **cuando ya conoce la contraseña**, así que no hay enumeración de cuentas que valga la pena.
- El JS del frontend no necesitó cambios: ya pintaba `result.data.message`, así que el 403 se lee como "Tu cuenta no tiene permiso para usar este panel".
- Si el filtro devuelve una lista vacía, se vuelve al valor por defecto. "Sin capacidades" nunca debe significar "sin restricciones".

**Configuración.** Con el filtro, que acepta string o array:

```php
add_filter( 'jg_required_capabilities', function () {
	return array( 'manage_options' ); // solo administradores
} );
```

**Pruebas** (`php7.3 tests-login-rate.php`, código real de `JG_REST::login()` y `require_auth()`, **30 aserciones, todas en verde**; las 14-23 son de este cambio):

14. Usuario con `edit_posts` entra y recibe token.
15. Suscriptor con la contraseña correcta → 403 `jg_forbidden` y **sin token**.
16. Ese 403 no crea contadores de intento.
17-18. Un token ya emitido sirve mientras el usuario tenga permiso, y deja de servir si se lo quitan (token válido no es permiso permanente).
19-20. Con el filtro puesto en `manage_options`, `edit_posts` deja de alcanzar y `manage_options` sí.
21-22. Lista vacía → sigue exigiéndose `edit_posts` (no se abre la puerta por error).
23. Con dos capacidades exigidas, tener solo una no basta.

`JG_VERSION` 1.5.0 → **1.6.0**. Cambia solo `includes/class-jg-rest.php`.

### 12.39 Pantalla para dejar lista la cuenta del panel — v1.7.0

**El problema que resolvía.** El panel autentica contra WordPress por correo, y en un hosting recién instalado esa cuenta puede no existir, tener el **correo electrónico vacío** (pasa cuando Softaculous instala sin correo), o existir con un rol que no sirve. En los tres casos el login respondía 401 o 403 y había que diagnosticarlo a mano. Como el plugin no tiene acceso a la base de datos desde fuera, la única forma era pelear con **Usuarios → Añadir usuario**.

**Qué se añadió** (`includes/class-jg-settings.php`, nuevo; se carga solo si `is_admin()`, así que el frontend no cambia):

- Pantalla **Ajustes → Credenciales del panel**. Solo la ven quienes tienen `manage_options`, y el manejador repite la comprobación antes de tocar nada, con `check_admin_referer()`.
- Dos campos: correo y contraseña nueva. Si la cuenta existe, se le cambia la contraseña (`wp_set_password`, hash `$wp$` de WordPress); si no existe, se crea con el rol `administrator`. Casilla para promover a ese rol una cuenta que ya existía, marcada por defecto.
- Validación: correo con `is_email()`, contraseña de 8 caracteres o más.
- Tabla de todas las cuentas del sitio con sus roles y si pueden usar el panel. Es el diagnóstico rápido de por qué un login falla: si el correo está vacío o el rol dice `subscriber`, ahí está la respuesta.
- **Ninguna contraseña vive en el código**: se escribe en el navegador y se guarda cifrada. La pantalla no la vuelve a mostrar.

`translate_user_role()` deja el nombre del rol en el idioma del administrador en vez de la palabra inglesa `administrator`.

**Detalles de criterio:**

- Vive dentro de *Ajustes*, no como menú de primer nivel: el plugin sigue sin menú propio, y así la pantalla aparece junto a las demás de WordPress en vez de inventar un apartado más.
- El rol es filtrable con `jg_panel_role` (por defecto `administrator`).
- `submit_button()` no existe fuera del admin, así que hay un `<button>` equivalente: si alguien renderiza la pantalla por CLI o AJAX, no se produce un error fatal.
- Excepción al subir el archivo: `wp_die()` corta el script con `exit`, y el banco de pruebas necesita distinguir "se detuvo aquí" de "llegó al final". Se sustituyó `wp_die_handler` por una función que lanza excepción; el fallo de seguridad real sería, justamente, que la URL de redirect apareciera.

**Pruebas** (`php8.3 tests-settings-credentials.php`, WordPress 7.1 real, **16 aserciones, todas en verde**):

2-6. Crea la cuenta con rol administrador, la contraseña verifica con `wp_check_password` y puede usar el panel.
7-9. Una cuenta existente se actualiza y **no se duplica** (conteo directo en `wp_users`, porque `WP_User_Query` no tiene filtro por correo).
10-11. Rechaza contraseñas de menos de 8 caracteres y correos mal formados.
12-15. Un suscriptor es rechazado antes de tocar nada: no crea la cuenta, no cambia contraseñas y recibe `wp_die` en vez del redirect.

Las aserciones 13 y 15 fallaron en la primera pasada y **el fallo estaba en las pruebas**: el `catch` marcaba como peligro cualquier excepción, incluida la de `wp_die`, que es justamente el rechazo correcto. Se corrigió el banco, no el código.

`JG_VERSION` 1.6.0 → **1.7.0**. Añade `includes/class-jg-settings.php`.

**Corrección en 1.7.1 — el error más probable era de la propia pantalla.** El campo de correo venía precargado con `wp_get_current_user()->user_email`, es decir el correo del administrador que abrió la pantalla. Al guardar sin borrarlo, lo que se creaba o modificaba era **esa otra cuenta**, y la que uno quería usar seguía sin existir: el login respondía 401 y la culpa parecía del plugin. Tres arreglos:

- El campo de correo se entrega **vacío**, con un `placeholder` en su lugar. Hay que escribir a mano el correo que se va a usar.
- Confirmación en el navegador antes de enviar: nombra el correo y advierte que, si la cuenta ya existe, su contraseña antigua dejará de servir.
- El aviso de éxito y de actualización **repite el correo** afectado, para que quede claro a qué cuenta se le cambió la clave.

La tabla de cuentas ya despejaba la duda por otro lado: si el correo buscado no aparecía ahí, es que no se guardó.

`JG_VERSION` 1.7.0 → **1.7.1**.

### 12.40 La contraseña correcta ya no espera — v1.8.0

**Cómo se encontró.** Probando el login de producción en un navegador real (Chrome headless, rellenando el formulario con las credenciales del README), la respuesta fue **HTTP 429 "Demasiados intentos" con la contraseña correcta**. O sea: el bloqueo por intentos se aplicaba *antes* de saber si la contraseña era buena.

**El orden estaba invertido.** En la 1.7.1 y anteriores:

1. `check_login_rate_limit()` — si la cuenta ya tenía 5 fallos, 429 y fin.
2. Solo después se comprobaba la contraseña.

Consecuencia: un usuario que se equivocaba cinco veces quedaba **cinco minutos sin poder entrar, ni siquiera con la contraseña correcta**. Y como el límite por cuenta se refrescaba en cada intento fallido, en la práctica la espera se alargaba.

**El orden nuevo:**

1. Campos válidos (400 si no).
2. Cortafuegos antiabuso opcional (desactivado por defecto, ver abajo).
3. **Contraseña.** Si es correcta → se limpian los contadores y se entra, siempre, sin esperas.
4. **Solo si es incorrecta** se consulta el límite de fallos (5 por cuenta, 25 por IP) y se responde 429 o 401.

Un detalle de diseño propio que salió al escribirlo: el contador se comprueba **antes** de sumar, no después. Si se sumara primero, un atacante podría renovar indefinidamente el bloqueo de la cuenta y dejar al dueño del panel sin poder entrar nunca. Así el bloqueo caduca 5 minutos después del último fallo, haga lo que haga el atacante.

**El cortafuegos antiabuso queda desactivado por defecto** (`LOGIN_MAX_REQUESTS_PER_IP = 0`). Se llegó a poner en 100 y era un error: en producción todas las peticiones comparten la IP del proxy, así que un tope por IP **vuelve a bloquear a todo el mundo a la vez**, que es exactamente el bug de la 1.5.0. Se conserva el código por si alguien lo quiere activar, pero con el filtro `jg_max_requests_per_ip`.

**Comprobado en navegador real** (no solo por lectura de código): en local se provocaron 6 intentos fallidos hasta bloquear la cuenta, y luego se rellenó el formulario con la contraseña correcta en Chrome. Resultado: HTTP 200, redirección a `/galeria-panel/`, **ningún mensaje en pantalla**, sesión guardada y catálogo cargado.

**Pruebas** (`php7.3 tests-login-rate.php`, **38 aserciones, todas en verde**; las 24-31 son nuevas):

24. Cuenta bloqueada + contraseña correcta → entra, sin 429.
25. IP con 26 fallos + contraseña correcta → entra igual.
26-27. Cinco claves malas dan cinco 401 y el sexto sale bloqueado: la protección sigue igual de fuerte.
28-29. Un intento más mientras está bloqueado no amplía la espera (el contador se queda en 5, no sube a 6).
30-30b. El cortafuegos no hace nada por defecto, y corta en la cuarta petición si alguien lo activa con el filtro a 3.
31. Cinco entradas legítimas no mueven ningún contador.

`JG_VERSION` 1.7.1 → **1.8.0**. Cambia solo `includes/class-jg-rest.php`.

### 12.41 Vincular una cuenta que ya existe con otro correo — v1.8.1

**El caso que quedaba abierto.** Después de subir la 1.8.0 a producción, el login seguía respondiendo 429 con la contraseña correcta. La 1.8.0 garantiza que una contraseña correcta entra sin esperar, así que solo había una explicación: **la contraseña guardada en la base de producción no era la del README**.

Al verificar por HTTP se confirmó la versión en producción leyendo el `?ver=` de los assets del plugin (`jwt-gallery.css?ver=1.8.0`), sin necesidad de acceso al panel.

**Por qué fallaba.** El caso que la pantalla de credenciales no contemplaba, y que es el habitual en un hosting recién instalado: ya existe un usuario cuyo **nombre de acceso** es `admin@todoacrilico.cl` pero cuyo **correo electrónico** es otro (o está vacío). El login del panel busca por correo con `get_user_by( 'email', ... )`, así que nunca lo encuentra, y la cuenta queda inaccesible sin importar cuántas veces se cambie la contraseña.

**La corrección.** `handle_save()` busca primero por correo y, si no hay nadie, también por nombre de acceso. Si lo encuentra, le **corrige el correo** al valor escrito, le cambia la contraseña y le da el rol, en vez de fallar con "no se pudo crear la cuenta". Aviso nuevo: `jg=vinculada`, que lo explica en una frase.

El riesgo de tocar el correo de una cuenta ajena queda acotado por las mismas guardas de siempre: `manage_options`, `check_admin_referer()`, confirmación en el navegador que nombra el correo, y aviso que repite a quién se le cambió la contraseña.

**Pruebas** (`php8.3 tests-settings-credentials.php`, **23 aserciones, todas en verde**; las 17-23 son nuevas): se crea un usuario cuyo nombre de acceso es un correo y cuyo correo es otro, se guarda por la pantalla y se comprueba que queda con el correo correcto, **el mismo ID** (no se duplica), la contraseña nueva verificada y el rol administrador.

`JG_VERSION` 1.8.0 → **1.8.1**.

---

### 12.42 La tabla de cuentas no enseñaba el nombre de usuario — v1.8.2

**El dato que faltaba para diagnosticar.** La tabla "Cuentas de este sitio" mostraba correo, roles y permiso, pero no el **nombre de usuario**. Con la comprobación de §12.41 la sospecha era que un identificador con forma de correo fuera el `user_login` y no el `user_email`, y esa tabla justamente no lo enseñaba.

Se añadió la columna **Nombre de usuario**, más una nota que aclara que el login busca por correo y no por nombre de usuario. Sin ese dato, cualquier intento de diagnóstico por panel se quedaba en conjeturas, que es exactamente lo que pasó durante varias horas de troubleshooting.

`JG_VERSION` 1.8.1 → **1.8.2**.

---

### 12.43 Una cuenta de galería no necesita ser administradora del sitio — v1.9.0

**Por qué.** El cliente pidió dos contraseñas distintas: una para el correo, otra para entrar a WordPress y otra para el panel de galería. La pantalla de credenciales obligaba a que la cuenta fuera **administradora**, lo que hace que la credencial de la galería tenga el mismo peso que el acceso completo al sitio: si se filtra, se pierde también el control de usuarios, plugins y ajustes.

**La corrección.** Desplegable de rol en el formulario, con dos opciones:

| Rol | Qué permite | Qué impide |
| --- | --- | --- |
| `editor` | Publicar contenido, subir archivos, usar el panel de galería | Tocar usuarios, plugins y ajustes del sitio |
| `administrator` | Todo | — |

El panel solo exige `edit_posts` (§12.38), que el rol `editor` también tiene, así que **editor basta**. Verificado con pruebas: el rol editor tiene `edit_posts` y `upload_files`, y **no** tiene `manage_options`.

`roles_permitidos()` devuelve la lista y `handle_save()` **no confía en el `POST`**: cualquier rol fuera de la lista blanca se descarta y se usa el rol por defecto. Un formulario manipulado con `jg_role=subscriber` no degrada la cuenta.

`wp_insert_user()` usa el rol seleccionado, de modo que la opción sirve tanto para cuentas nuevas como para existentes.

**Pruebas** (`php8.3 tests-settings-credentials.php`, de 23 a **34 aserciones, todas en verde**): cuenta creada como editor, no administradora, con permiso de panel y de subida pero sin `manage_options`; y rol manipulado rechazado con caída al rol por defecto.

`JG_VERSION` 1.8.2 → **1.9.0**.

---

### 12.44 Diagnóstico: el plugin inactivo se disfraza de problema de contraseña

**Síntoma.** Con el plugin instalado y las credenciales correctas, el login empezó a responder **404 `rest_no_route`**, y la consola del navegador mostraba assets con `?ver=1.9.0`.

**El 404 no era del login.** El plugin devolvía 400, 401, 403 y 429, nunca 404. Un 404 significa que la ruta **no está registrada**, es decir, que el plugin no está corriendo.

**Cómo confirmarlo sin acceso al panel**, tres comprobaciones por HTTP público:

1. `GET /wp-json/` y buscar `jwt-gallery/v1` en el array `namespaces`. Si no aparece, el plugin no registró rutas. La web puede tener 130 rutas de otros plugins y ninguna nuestra.
2. Descargar `/wp/galeria/` y buscar `data-jg-root`. Si no está, no hay formulario.
3. Buscar el **shortcode en crudo**: si la página muestra el texto literal `[jwt_gallery]`, WordPress no procesó el shortcode, y eso solo pasa cuando el plugin está inactivo. Es la prueba más concluyente.
4. El `?ver=` de `jwt-gallery.css` y `jwt-gallery.js` dice qué versión está cargando **la página que se descarga**, no la que tiene el navegador con su caché.

**Causa más probable.** Actualizar un plugin por FTP o por un descompresor a veces deja la carpeta anidada (`jwt-gallery/jwt-gallery/`) o deja archivos a medias, y WordPress desactiva el plugin al detectar un error fatal. Un error fatal en producción **desactiva el plugin automáticamente** y por eso el síntoma es "el login dejó de funcionar" y no "el plugin se rompió".

**Solución, no un ZIP.** Reinstalación limpia:

1. Borrar la carpeta `wp-content/plugins/jwt-gallery` **completa**, incluidas las anidadas.
2. Plugins → Añadir nuevo → Subir plugin → el ZIP → **Activar**.
3. Comprobar que `jwt-gallery/v1` volvió al índice REST.

Subir el ZIP encima de una carpeta dañada repite el problema.

**Nada se perdió.** Las imágenes viven en `wp-content/uploads/jg-gallery/` y las categorías en la base de datos, no dentro del plugin. Desinstalar el plugin no borra las fotos.

**Aviso de caché.** Una de las confusiones mayores fue ver en la consola un `?ver=1.9.0` con assets que en realidad el servidor ya no servía. Con Ctrl+Shift+R se ve el estado real. Y al leer `/wp-json/` hay que mirar la respuesta descargada en ese momento, no la de una comprobación anterior.

---

### 12.45 Cuentas reales de producción y por qué el login daba 429

**Situación verificada.** La instalación de producción tiene cuentas creadas por el instalador del hosting: `pablo@todoacrilico.cl` (la que corresponde al usuario público "Pablo", ID 1), `prn@todoacrilico.cl` y `contacto@todoacrilico.cl`. La cuenta `admin@todoacrilico.cl` **no existía**.

**El mecanismo, que costó entender.** Local y producción son **bases de datos distintas**: los usuarios no se comparten. Poner la contraseña correcta en el `README` no la pone en producción. Mientras la cuenta no exista allí, cada intento de login falla, y a los cinco intentos salta el bloqueo de §12.37.

Por eso la secuencia de errores fue siempre esta, y parecía un bug cuando no lo era:

| Situación | Respuesta |
| --- | --- |
| Cuenta inexistente, 5+ intentos | 429 `jg_too_many_attempts` |
| Cuenta inexistente, menos de 5 | 401 `jg_invalid_credentials` |
| Ruta no registrada (plugin inactivo) | 404 `rest_no_route` |
| Contraseña correcta, sin permiso | 403 `jg_forbidden` |

**Ningún código arregla una cuenta que no existe.** Solo la crea la pantalla de Ajustes → Credenciales del panel (§12.39), o el alta manual en Usuarios. El plugin se comportó correctamente en todo momento.

**Decisión del cliente.** Quedan dos cuentas con dos contraseñas distintas, a propósito:

| Correo | Entorno | Contraseña |
| --- | --- | --- |
| `admin@todoacrilico.cl` | Local y producción | La del `README` |
| `pablo@todoacrilico.cl` | Producción | La del hosting, **no tocar** |

La cuenta de la galería usa rol `editor` (§12.43), así que su credencial no administra el sitio.

**Pendiente a la fecha de este cierre:** crear `admin@todoacrilico.cl` en producción con la pantalla de credenciales. Hasta ese momento el login de producción no puede funcionar, por diseño y no por avería.

---

### 12.46 Verificación de una versión instalada en producción

Del §12.44 sale un método que no requiere acceso al panel y que conviene repetir tras cada subida:

```bash
# 1. ¿Está activo el plugin? (buscar jwt-gallery/v1 en la lista)
curl -s https://todoacrilico.cl/wp/wp-json/ | grep -o "jwt-gallery/v1"

# 2. ¿Se está procesando el shortcode?
curl -s https://todoacrilico.cl/wp/galeria/ | grep -c "data-jg-root"
curl -s https://todoacrilico.cl/wp/galeria/ | grep -c "\[jwt_gallery\]"   # debe dar 0

# 3. ¿Qué versión sirve realmente el servidor?
curl -s https://todoacrilico.cl/wp/galeria/ | grep -oE "jwt-gallery\.(css|js)\?ver=[0-9.]+"
```

El `?ver=` del punto 3 es el dato más útil: distingue "subí el ZIP y está activo" de "lo subí pero el navegador me muestra la versión anterior desde caché".

Un POST a `/wp-json/jwt-gallery/v1/login` con `{}` debe devolver **400 `jg_bad_request`**. Si devuelve 404, el plugin está inactivo, no es un problema de credenciales.

---

### 12.47 Revocación real del token por `jti` (2026-10-20, commit `ca0acd3`, sin cambio de `JG_VERSION`)

Aplica el Fix 6 de Docs_Seguridad.md.

- `JG_JWT::issue()` agrega el claim `jti` (32 caracteres aleatorios).
- `verify()` rechaza un token sin `jti` (`jg_malformed_token`) y uno cuyo `jti` no coincide con el usermeta **`jg_token_jti`** (`jg_token_revoked`, 401).
- `login()` guarda el `jti` del token emitido.
- `logout()` lo reemplaza por un valor aleatorio, así que el token anterior deja de servir aunque no haya expirado.

**Consecuencia:** cada usuario tiene **una sola sesión válida a la vez**. Iniciar sesión en otro navegador invalida la anterior. No quites esta validación.

Como el cambio es solo de PHP, `JG_VERSION` sigue en 1.9.0: no hace falta invalidar la caché de JS ni de CSS.

### 12.48 Categoría por defecto `vidrios` → `trabajos-especiales` (2026-10-20, commit `bda5cc5`)

`JG_CPT::DEFAULT_CATEGORIES` ahora es `organizador, proteger, trabajos-especiales, exhibir, informar`.

- **Por qué importa:** `seed_default_categories()` crea cualquier categoría por defecto que falte. Si la constante siguiera diciendo `vidrios`, volvería a aparecer una categoría "vidrios" vacía.
- **Base de datos local (verificado):** tiene `trabajos-especiales` ("Trabajos Especiales", 4 piezas) y no tiene `vidrios`.
- **Rutas antiguas:** `?categoria=vidrios` debe apuntar a `?categoria=trabajos-especiales`. Revisa las redirecciones 301 en el `.htaccess` de producción.

### 12.49 Relación con la Política de Privacidad (2026-10-20)

La cookie HttpOnly **`jg_session`** (24 h, `JG_JWT::DEFAULT_TTL`) y la clave `localStorage` **`jg_session`** del panel aparecen en la tabla de la sección 8 de la Política de Privacidad, como exige el deber de información de la Ley 21.719.

**Si cambias el nombre, la duración o el lugar de almacenamiento de la sesión, actualiza esa tabla.** Ver Docs_Seguridad.md, Fix 15.

### 12.50 Versión 2.0.0 (2026-10-20)

A pedido del cliente, el tema y el plugin quedan en **2.0.0**: `JG_VERSION` y la cabecera `Version:` de `jwt-gallery.php` pasan de 1.9.0 a 2.0.0. Esto incluye los cambios de §12.47 y §12.48. Solo afecta el cache-busting (`jwt-gallery.css?ver=2.0.0`, `jwt-gallery.js?ver=2.0.0`). No hay migraciones atadas a la versión.

---

## 13. Tutorial paso a paso

### 13.0 Usuario de prueba

| Campo | Valor |
|---|---|
| Correo | `admin@todoacrilico.cl` |
| Contraseña | *(en `README.md`, que no se publica)* |
| Rol | Suscriptor |

### 13.1 Iniciar sesión

Ir a `http://localhost/wordpress/proyecto3/index.php/galeria/`. Debería verse **solo** el formulario de login (correo, contraseña con botón de ojo para mostrarla, botón Ingresar) — nada de formulario de subida ni galería en esta página.

Completa el correo y la contraseña de arriba y haz clic en **Ingresar**. Si las credenciales son correctas, el navegador te redirige automáticamente a `/galeria-panel/`.

### 13.2 El panel

En `/galeria-panel/` deberías ver un layout de dos columnas:
- Arriba, una barra angosta con tu nombre/correo y el botón **Cerrar sesión**.
- A la izquierda, un **sidebar fijo** con el formulario "Subir imagen" (título, descripción, uso, medidas, imágenes de archivo, categoría) y, debajo, el bloque "Categorías" (lista de las categorías sembradas con su contador, cada una con un ícono de lápiz para renombrarla y un tacho para eliminarla, y un campo para agregar una categoría nueva).
- A la derecha, una barra de **tabs por categoría** — `Todas` (con el total), cada categoría (con su propio contador) y `Sin categoría` al final si hay publicaciones sin categoría — y debajo la grilla de imágenes de la categoría seleccionada, con paginación al final.

Si entras a esta URL **sin** haber iniciado sesión (por ejemplo, en una ventana de incógnito), el JS te redirige automáticamente de vuelta a `/galeria/`.

### 13.3 Subir una publicación con varias fotos y categoría

En el formulario "Subir imagen" del sidebar:

- **Título:** `Ventana instalada` (máx. 43 caracteres).
- **Descripción:** `Reparación de vidrio templado` (máx. 120 caracteres).
- **Uso:** `Exhibición en vitrinas o uso en exterior` (máx. 43 caracteres).
- **Medidas:** por ejemplo `1500 x 2000 mm`.
- **Imágenes:** clic en **"+ Agregar otra imagen"** abre el selector de archivos del dispositivo (se pueden elegir varias de una vez, hasta completar 10, y 8 MB por archivo). Cada una aparece como una mini-tarjeta con vista previa; el contador muestra "2/10 imágenes". El botón `×` de cada tarjeta la quita, salvo que sea la única.
- **Categoría:** selecciona `Vidrios`.

Clic en **Publicar imagen** → mensaje verde "Imagen publicada correctamente" y la pieza queda guardada como archivo **local** en `wp-content/uploads/jg-gallery/` (nada de enlaces externos). El formulario vuelve a quedar vacío y el contador del tab "Vidrios" (y de "Todas") sube en 1 automáticamente — sube 1 por **publicación**, no por foto. La nueva pieza también aparece al instante en el catálogo del sitio (`/nuestras-soluciones/`), en su tab de categoría.

### 13.4 Agregar, renombrar y eliminar una categoría

En el bloque "Categorías" del sidebar, cada fila tiene dos íconos: un lápiz y un tacho de basura.

- **Agregar:** escribe un nombre en "Nueva categoría" (por ejemplo `promociones`) y clic en **Agregar** → aparece en la lista, en el selector de categoría del formulario de subida y como un nuevo tab en la barra de categorías (con contador `0`).
- **Renombrar:** clic en el ícono de lápiz junto a cualquier categoría → el nombre se vuelve editable; cambia el texto y clic en **Guardar** → se actualiza en la lista, en la barra de tabs y en todas las imágenes que ya tenían esa categoría asignada.
- **Eliminar:** clic en el ícono de tacho de basura → se abre el mismo modal de confirmación que al eliminar una imagen (§1.6.2, ya no el cuadro nativo del navegador); si la categoría tiene piezas, el mensaje avisa cuántas y aclara que **se eliminarán también junto con sus archivos** ("Se eliminarán también las X piezas de esta categoría y sus archivos"), tal como pide el cliente (§12.33). Al hacer clic en **Eliminar** dentro del modal, la categoría y todo lo que contenía desaparecen de la lista, del selector de subida, de la barra de tabs y del catálogo del sitio.

### 13.5 Ver, editar y eliminar imágenes

Clic en el tab **Vidrios** de la barra de categorías: se cargan solo las publicaciones de esa categoría (si una categoría no tiene ninguna todavía, se muestra "Todavía no hay imágenes en esta categoría"). Si subiste 10 o más publicaciones a una misma categoría, aparece la paginación al final de la grilla — "Mostrando 1-9 de N" con los controles Anterior/Siguiente y los números de página; hacer clic en un número o en Siguiente vuelve a pedirle esa página al servidor.

Si la publicación tiene más de una foto (como la que subiste en §13.3), su tarjeta muestra un **contador** siempre visible en la esquina inferior derecha ("1/2") y, al pasar el mouse, dos flechas `‹`/`›` a los costados de la imagen para pasar de una foto a otra sin salir de la tarjeta ni recargar nada. Si solo tiene una foto, no aparece ningún control de este tipo — se ve igual que antes.

Como subiste la publicación con tu propio usuario, al pasar el mouse también aparecen dos botones cuadrados de esquinas redondeadas en la esquina superior: un **lápiz** (editar) y un **tacho de basura rojo** (eliminar) — ocultos hasta el hover, sin necesidad de desplegar ningún menú (las publicaciones de otros usuarios se ven igual, pero sin esos íconos ni siquiera al pasar el mouse — ver §9).

- **Lápiz (editar):** la tarjeta se convierte en un formulario con título, descripción, uso, medidas, categoría, y el **selector de imágenes precargado con las fotos actuales** de la publicación (puedes quitar alguna, agregar archivos nuevos hasta 10, o dejarlas igual — las que se quiten se borran del disco al guardar). Cambia lo que quieras y clic en **Guardar** — la grilla y los contadores se recargan con los cambios. **Cancelar** descarta los cambios y vuelve a la vista normal.
- **Tacho de basura (eliminar):** abre el modal de confirmación ("Eliminar imagen '[título]'" + "Esta acción no se puede deshacer."); al hacer clic en **Eliminar**, la publicación completa (con todas sus fotos y sus archivos locales) desaparece de la grilla y los contadores bajan en 1. **Cancelar**, hacer clic fuera del modal, o presionar `Escape` lo cierran sin borrar nada.

### 13.6 Cerrar sesión

Clic en **Cerrar sesión** (barra superior) → se borra la sesión local y el navegador te redirige a `/galeria/`, donde vuelve a aparecer el formulario de login.

### 13.7 Probar los casos límite (opcional)

- Editar o eliminar una imagen que no es tuya: no debería aparecer la opción, porque el frontend solo muestra los botones si `author_id` coincide con tu usuario — y aunque se forzara la llamada a la API directamente, el servidor la rechaza con `jg_forbidden` (403).
- Crear una categoría con un nombre que ya existe → error "Ya existe una categoría con ese nombre" (`jg_category_exists`, 409).
- Subir un archivo con extensión no permitida o de más de 8 MB → `jg_bad_extension` / `jg_file_too_large` (400); el archivo no se guarda.
- Escribir un título de más de 43 caracteres, una descripción de más de 120 o un uso de más de 43 → el formulario lo bloquea con `maxlength`, y si se fuerza vía API el servidor responde `jg_title_too_long` / `jg_description_too_long` / `jg_usage_too_long` (400).
- Iniciar sesión con contraseña incorrecta 6 veces seguidas → "demasiados intentos" al sexto. El bloqueo dura 5 minutos y cuenta **por cuenta**: es 5 intentos por correo y 25 por IP real (ver §12.37).
- En "Subir imagen", seleccionar más archivos (hasta 10 en total) → el botón de agregar desaparece y el contador marca "10/10 imágenes"; no hay forma de agregar un onceavo desde la interfaz. Si se fuerza el envío con más de 10 archivos directo a la API, el servidor lo rechaza con `jg_too_many_images` (400).
- Dejar todos los campos de imagen vacíos y enviar el formulario → el frontend lo bloquea antes de llamar a la API; si se fuerza vía API sin archivos, el servidor responde `jg_no_image_url` (400).
- Eliminar una categoría que tiene piezas → el modal avisa cuántas se eliminarán y al confirmar desaparecen categoría, piezas del catálogo y sus archivos del disco (§12.33).

### §12.51 — Popover de ayuda reutilizable en el panel (plugin 2.0.1, 2026-11-04)

- **Pedido:** un popover junto a cada campo de "Subir imagen" con los límites. Título, Descripción, Uso y Medidas muestran sus caracteres máximos. Imágenes muestra la cantidad, el peso máximo y los formatos, y recomienda **.webp**. Categoría muestra las categorías disponibles.
- **Única fuente de límites:** nuevas constantes en `JG_REST` (`MAX_TITLE_CHARS` 43, `MAX_DESCRIPTION_CHARS` 120, `MAX_USAGE_CHARS` 43, `MAX_MEASUREMENTS_CHARS` 20, junto a `MAX_IMAGES_PER_POST`, `MAX_UPLOAD_BYTES` y `ALLOWED_IMAGE_EXTENSIONS`). La validación del servidor y sus mensajes las usan; `wp_localize_script` las envía como `JGGallery.limits`; los `maxlength` del formulario PHP y del de edición salen de ahí. **Para cambiar un límite, cambia solo la constante.**
- **Componente (jwt-gallery.js):** `createHint(key, state)` crea el botón "i" y su popover; el contenido sale de `HINTS[key]` y se arma al abrir (la lista de categorías siempre está al día). `mountHints(root, state)` llena los `<span data-jg-hint="clave">` del HTML; `labelWithHint(label, texto, clave, state)` sirve para formularios creados en JS (el de edición ya lo usa). Se abre uno a la vez y se cierra con un clic fuera o con Escape (el foco vuelve al botón). El clic en el botón o en el popover no activa el campo del `<label>`.
- **CSS:** `.jg-label-row`, `.jg-hint*`. El botón usa `.jg-hint .jg-hint-btn` (dos clases) para ganarle a `.jg-form button`, el mismo problema que tuvo el botón del ojo.
- **Arreglo de paso (móvil):** a 390px el panel era 35px más ancho que la pantalla y el banner de cookies del tema se salía de vista. Había dos causas: la barra superior sin `flex-wrap` y la grilla con `1fr`. Ahora la barra usa `flex-wrap` y la grilla `minmax(0, 1fr)`.
- **Verificado** en Chrome headless a 390px y 1280px: 6 popovers en "Subir imagen" y 6 en cada formulario de edición, sin desborde, `maxlength` correctos y sin errores de JavaScript.

### §12.52 — Sesión revocada: mensaje real y vuelta al login (plugin 2.0.1, 2026-11-04)

- **Síntoma:** al eliminar una categoría aparecía "Lo siento, no tienes permisos para hacer eso." estando con la cuenta admin. **Causa:** la sesión de ese navegador había sido revocada (solo hay un token válido por usuario, regla 7). En este caso la revocó una prueba headless que emitió un token nuevo para el mismo usuario. `require_auth()` devolvía `false` y WordPress reemplazaba el error real (`jg_token_revoked`, 401) por su `rest_forbidden` genérico.
- **Servidor:** `require_auth()` devuelve el `WP_Error` de `authenticated_user()`, así que la API responde el motivo real: 401 si la sesión expiró o fue revocada, 403 si la cuenta no tiene permiso.
- **Panel (jwt-gallery.js):** `apiFetch()` detecta un 401 con alguno de los códigos de `SESSION_ERROR_CODES` (no incluye el 401 de "correo o contraseña incorrectos"). En ese caso borra la sesión local, muestra el modal del panel "Sesión cerrada" con el botón "Iniciar sesión" (`showConfirmModal` con la nueva opción `hideCancel`) y lleva al login. La promesa queda pendiente para que el código que llamó no muestre además su propio error.
- **Pruebas headless:** **no emitas tokens para `admin@todoacrilico.cl` (ID 3)**, porque cierra la sesión real del usuario. Usa una cuenta `prueba.panel.*`.
- **Verificado:** con un token revocado, eliminar "Autos" muestra el modal "Sesión cerrada" (sin alertas del navegador) y lleva al login, y la categoría no se borra. Con un token válido la API sigue respondiendo normal (404 para una categoría inexistente).

### §12.53 — Sin archivos huérfanos al rechazar una pieza y prueba de regresión (plugin 2.0.1, 2026-11-04)

- **Error previo (anterior a 2.0.1):** `collect_image_sources()` guardaba cada archivo en `uploads/jg-gallery/` y recién después validaba el siguiente o contaba el total. Si la pieza se rechazaba (una imagen de más de 8 MB, un archivo falso o más de 10 imágenes), las imágenes válidas ya guardadas quedaban en disco sin pertenecer a ninguna pieza.
- **Arreglo:** primero se valida `existing[]` y se cuenta `archivos + conservadas` contra `MAX_IMAGES_PER_POST`, **antes de guardar nada**. Si una imagen falla al guardarse, se borran las ya guardadas en esa petición (`delete_local_images( $saved )`). `upload_image()` también las borra si `wp_insert_post` falla. El orden de las URLs no cambia: primero las nuevas y después las conservadas.
- **Regresión completa (cuenta `prueba.panel.*`, ID 4; nunca `admin@`):**
  - **API, 34/34:** lectura pública; login con credenciales incorrectas y vacío; crear, renombrar, duplicar y borrar categoría; subir con todos los campos en su máximo (png + webp); cada límite +1 rechazado; título vacío; sin imágenes; 9 MB; .txt; png falso; 11 imágenes; ningún archivo huérfano en cada rechazo; editar conservando y agregando; editar rechazado conserva la pieza intacta; borrar pieza borra sus archivos; 401 con motivo sin sesión y tras logout.
  - **Panel en Chrome:** crear categoría (aparece en el popover y en el selector); subir un .webp desde el formulario; modales de confirmación con "Cancelar"; borrar pieza y categoría; cerrar sesión. Sin alertas del navegador ni errores de JavaScript, y sin restos de prueba en la base de datos ni en `uploads`.


### §12.54 — Login y panel ocultos para buscadores (plugin 2.0.2, 2026-11-06)

- **Pedido del cliente:** `https://todoacrilico.cl/wp/galeria/` debe estar oculto: no puede aparecer en el sitemap ni ser indexado por Google. El sitemap de WordPress de producción listaba `/galeria/` y `/galeria-panel/`.
- **`JG_Template`** (`includes/class-jg-template.php`):
  - `private_page_ids()`: páginas con los slugs `galeria` y `galeria-panel`, más cualquier página cuyo contenido tenga `[jwt_gallery…` (por si se renombra). Se calcula una vez por petición.
  - `exclude_from_sitemap()` con el filtro `wp_sitemaps_posts_query_args`: agrega esos IDs a `post__not_in` para el tipo `page`.
  - `noindex_private_pages()` con el filtro `wp_robots`: `noindex, nofollow, noarchive` (quita `max-image-preview`).
  - `send_noindex_header()` en `template_redirect`: la cabecera `X-Robots-Tag: noindex, nofollow, noarchive`.
- **A propósito no se usa `robots.txt`:** un `Disallow` impediría que Google lea el `noindex`, y la URL podría seguir apareciendo en los resultados, solo que sin descripción.
- El tema 2.3.5 también quitó las dos URLs de su `sitemap.xml` estático. En producción ese archivo no se usa: `/wp/sitemap.xml` redirige a `wp-sitemap.xml`.
- **Verificado en local:** el sitemap de páginas lista las 7 páginas públicas (incluida `politica-de-cookies`) y ninguna del panel. `/galeria/` y `/galeria-panel/` responden con la meta y la cabecera `noindex`. Cotizar sigue con `max-image-preview:large`. El login y el panel cargan sin errores.


### §12.55 — Slug `vidrios` → `trabajos-especiales` (plugin 2.0.3, 2026-11-06)

- **Síntoma:** en producción, el menú, las pestañas y el sitemap mostraban `?categoria=vidrios` para "Trabajos Especiales". En local se veía `trabajos-especiales`.
- **Causa:** la categoría ID 9 de producción se creó como `vidrios` y luego se renombró desde el panel. `update_category()` solo cambia `name`, no `slug`.
- **Arreglo:** `JG_CPT::LEGACY_SLUGS = array( 'vidrios' => 'trabajos-especiales' )` y `JG_CPT::migrate_legacy_slugs()` en `init` (prioridad 20). Si existe el slug antiguo y no el nuevo, hace `wp_update_term( …, array( 'slug' => nuevo ) )`: es el mismo término, así que las piezas siguen asociadas. Lo deja registrado en la opción autocargada `jg_slug_migrations`, que se escribe solo cuando hay algo pendiente. El tema 2.3.6 redirige `?categoria=vidrios` con 301.
- **Pendiente para decidir con el cliente:** renombrar una categoría en el panel sigue sin cambiar su slug. Si en el futuro se renombra, por ejemplo, "Proteger" → "Protección", la URL seguirá diciendo `proteger`.
