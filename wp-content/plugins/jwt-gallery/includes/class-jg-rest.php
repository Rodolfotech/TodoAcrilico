<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JG_REST {

	const NS = 'jwt-gallery/v1';

	const ALLOWED_IMAGE_EXTENSIONS = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif' );

	const MAX_IMAGES_PER_POST = 10;

	const MAX_UPLOAD_BYTES = 8388608;

	const LOGIN_MAX_ATTEMPTS = 5;

	const LOGIN_MAX_ATTEMPTS_PER_IP = 25;

	const LOGIN_WINDOW_SECONDS = 300;

	/**
	 * Peticiones al login admitidas por IP en la ventana. 0 = desactivado.
	 *
	 * Es un cortafuegos opcional contra el abuso que consiste en pedir verificaciones
	 * de contraseña sin parar (bcrypt es lento a propósito, así que eso consume CPU
	 * del servidor). Va antes de comprobar la contraseña, y por eso **no se activa
	 * por defecto**: en producción el sitio está detrás del proxy del hosting y todas
	 * las peticiones comparten la misma IP, de modo que un tope por IP puede acabar
	 * bloqueando a todo el mundo a la vez, que es justo el problema que se corrigió
	 * en la 1.5.0.
	 *
	 * Quien quiera ese límite puede activarlo con el filtro `jg_max_requests_per_ip`.
	 */
	const LOGIN_MAX_REQUESTS_PER_IP = 0;

	const COOKIE_NAME = 'jg_session';

	const OPTION_TRUSTED_PROXY_IPS = 'jg_trusted_proxy_ips';

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
			'/logout',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'logout' ),
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
		if ( $auth && stripos( $auth, 'Bearer ' ) === 0 ) {
			return trim( substr( $auth, 7 ) );
		}

		if ( ! empty( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			return sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) );
		}

		return '';
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

		if ( ! self::can_manage_gallery( $user ) ) {
			return new WP_Error(
				'jg_forbidden',
				'Tu cuenta no tiene permiso para usar este panel.',
				array( 'status' => 403 )
			);
		}

		return $user;
	}

	/**
	 * Capacidades que hay que tener para usar el panel.
	 *
	 * El token JWT demuestra que el login fue correcto, pero no dice nada de
	 * qué papel tiene el usuario: sin esta comprobación, cualquier cuenta de
	 * WordPress —un suscriptor, alguien que se registrou para comentar— podría
	 * subir, editar y borrar las imágenes del catálogo. Por defecto se exige
	 * `edit_posts`, que tienen administradores y editores, y no los demás.
	 *
	 * Se pueden cambiar con el filtro `jg_required_capabilities` (string o
	 * array). Todas las capacidades de la lista deben cumplirse:
	 *
	 *     add_filter( 'jg_required_capabilities', function () {
	 *         return array( 'manage_options' ); // solo administradores
	 *     } );
	 *
	 * @return array
	 */
	private static function required_capabilities() {
		$caps = apply_filters( 'jg_required_capabilities', array( 'edit_posts' ) );

		if ( is_string( $caps ) ) {
			$caps = preg_split( '/[\s,;]+/', $caps );
		}

		$clean = array();
		if ( is_array( $caps ) ) {
			foreach ( $caps as $cap ) {
				$cap = trim( (string) $cap );
				if ( '' !== $cap ) {
					$clean[] = $cap;
				}
			}
		}

		// Si alguien filtra la lista y la deja vacía, se vuelve al valor por
		// defecto: "sin capacidades" nunca debe significar "sin restricciones".
		return $clean ? $clean : array( 'edit_posts' );
	}

	/**
	 * ¿Este usuario puede administrar la galería?
	 *
	 * @param WP_User|false $user Usuario a comprobar.
	 * @return bool
	 */
	private static function can_manage_gallery( $user ) {
		if ( ! $user || ! isset( $user->ID ) ) {
			return false;
		}

		foreach ( self::required_capabilities() as $cap ) {
			if ( ! user_can( $user, $cap ) ) {
				return false;
			}
		}

		return true;
	}

	public static function require_auth( WP_REST_Request $request ) {
		return ! is_wp_error( self::authenticated_user( $request ) );
	}

	public static function login( WP_REST_Request $request ) {
		$email    = sanitize_email( (string) $request->get_param( 'email' ) );
		$password = (string) $request->get_param( 'password' );

		// Primero se validan los campos: una petición vacía no debe contar como
		// intento ni consumir una de las cinco oportunidades de una cuenta real.
		if ( ! $email || ! is_email( $email ) || '' === $password ) {
			return new WP_Error( 'jg_bad_request', 'Correo y contraseña son obligatorios.', array( 'status' => 400 ) );
		}

		// Cortafuegos antiabuso opcional, si alguien lo activó por filtro. Con
		// contraseña correcta nunca debe activar: es un tope de peticiones, no de
		// credenciales (ver LOGIN_MAX_REQUESTS_PER_IP).
		$abuso = self::check_login_abuse();
		if ( is_wp_error( $abuso ) ) {
			return $abuso;
		}

		// Las credenciales se verifican ANTES del bloqueo por intentos.
		//
		// Antes el orden era el inverso, y eso significaba que un usuario con la
		// contraseña correcta podía quedarse esperando cinco minutos si antes se
		// había equivocado cinco veces: el bloqueo se aplicaba sin saber si la
		// contraseña era buena. Ahora una contraseña correcta entra siempre de
		// inmediato; lo que se frena son los intentos fallidos, que es lo que
		// de verdad pretende un límite de intentos.
		$user = get_user_by( 'email', $email );

		if ( ! $user || ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
			// Se comprueba el límite antes de sumar, para que un bloqueo ya
			// iniciado no siga renovándose: si no, un atacante podría dejar a la
			// cuenta bloqueada indefinidamente y el dueño del panel sin poder
			// entrar nunca.
			$limit = self::check_login_rate_limit( $email );
			if ( is_wp_error( $limit ) ) {
				return $limit;
			}

			self::register_login_attempt( $email );

			return new WP_Error( 'jg_invalid_credentials', 'Correo o contraseña incorrectos.', array( 'status' => 401 ) );
		}

		self::clear_login_rate_limit( $email );

		// La contraseña es correcta pero la cuenta no sirve para el panel. Se
		// responde 403 sin emitir token ni dejar una sesión abierta. No cuenta
		// como intento fallido: las credenciales eran válidas.
		if ( ! self::can_manage_gallery( $user ) ) {
			return new WP_Error(
				'jg_forbidden',
				'Tu cuenta no tiene permiso para usar este panel.',
				array( 'status' => 403 )
			);
		}

		$ttl   = JG_JWT::DEFAULT_TTL;
		$token = JG_JWT::issue(
			array(
				'sub'   => $user->ID,
				'email' => $user->user_email,
			),
			$ttl
		);

		// Guardar el jti en usermeta para validación en logout/revocación.
		// Decodificar el payload del token para extraer el jti.
		$token_parts = explode( '.', $token );
		if ( count( $token_parts ) === 3 ) {
			$payload      = json_decode( base64_decode( str_pad( strtr( $token_parts[1], '-_', '+/' ), strlen( $token_parts[1] ) % 4, '=', STR_PAD_RIGHT ) ), true );
			$jti          = isset( $payload['jti'] ) ? $payload['jti'] : '';
			if ( $jti ) {
				update_user_meta( $user->ID, 'jg_token_jti', $jti );
			}
		}

		self::set_session_cookie( $token, $ttl );

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

	public static function logout( WP_REST_Request $request ) {
		// Intentar autenticar al usuario para obtener su ID y rotar el jti.
		$claims = JG_JWT::verify( self::get_bearer_token( $request ) );
		if ( ! is_wp_error( $claims ) && ! empty( $claims['sub'] ) ) {
			// Rotar el jti para invalidar realmente el token en el servidor.
			update_user_meta( (int) $claims['sub'], 'jg_token_jti', wp_generate_password( 32, false ) );
		}

		self::clear_session_cookie();
		return rest_ensure_response( array( 'logged_out' => true ) );
	}

	private static function set_session_cookie( $token, $ttl ) {
		if ( headers_sent() ) {
			return;
		}

		setcookie(
			self::COOKIE_NAME,
			$token,
			array(
				'expires'  => time() + (int) $ttl,
				'path'     => '/',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	private static function clear_session_cookie() {
		if ( headers_sent() ) {
			return;
		}

		setcookie(
			self::COOKIE_NAME,
			'',
			array(
				'expires'  => time() - 3600,
				'path'     => '/',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	/**
	 * Direcciones de proxy en las que sí se cree en las cabeceras de reenvío.
	 *
	 * Vacío por defecto: si no se declara ninguna, `X-Forwarded-For` se ignora
	 * porque cualquier visitante puede mandarla y así cambiar de clave para
	 * saltarse el límite. Configúrala con la opción `jg_trusted_proxy_ips`
	 * (IPs o CIDR separados por comas) o con el filtro `jg_trusted_proxy_ips`
	 * en un mu-plugin:
	 *
	 *     add_filter( 'jg_trusted_proxy_ips', function () {
	 *         return array( '173.245.48.0/20', '10.0.0.0/8' );
	 *     } );
	 *
	 * @return array
	 */
	private static function trusted_proxy_ips() {
		$raw = get_option( self::OPTION_TRUSTED_PROXY_IPS, '' );

		if ( is_string( $raw ) ) {
			$raw = preg_split( '/[\s,;]+/', $raw );
		}

		$ips = array();
		if ( is_array( $raw ) ) {
			foreach ( $raw as $candidate ) {
				$candidate = trim( (string) $candidate );
				if ( '' !== $candidate ) {
					$ips[] = $candidate;
				}
			}
		}

		$ips = apply_filters( 'jg_trusted_proxy_ips', $ips );

		return is_array( $ips ) ? array_values( $ips ) : array();
	}

	/**
	 * Comprueba si una IP coincide con una entrada de la lista (exacta o CIDR).
	 *
	 * @param string $ip       IP a comprobar.
	 * @param array  $candidatos IPs o CIDR de confianza.
	 * @return bool
	 */
	private static function ip_in_list( $ip, $candidatos ) {
		foreach ( $candidatos as $candidate ) {
			if ( $ip === $candidate ) {
				return true;
			}

			if ( false !== strpos( $candidate, '/' ) && self::ip_in_cidr( $ip, $candidate ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * ¿La IP cae dentro del prefijo CIDR indicado?
	 *
	 * @param string $ip    IP a comprobar.
	 * @param string $cidr  Prefijo, por ejemplo 173.245.48.0/20.
	 * @return bool
	 */
	private static function ip_in_cidr( $ip, $cidr ) {
		$parts = explode( '/', $cidr, 2 );
		$subnet = $parts[0];
		$bits   = isset( $parts[1] ) ? trim( $parts[1] ) : '';

		$ip_bin    = @inet_pton( $ip );
		$subnet_bin = @inet_pton( $subnet );

		if ( false === $ip_bin || false === $subnet_bin || strlen( $ip_bin ) !== strlen( $subnet_bin ) ) {
			return false;
		}

		if ( '' === $bits ) {
			return 0 === strcmp( $ip_bin, $subnet_bin );
		}

		$bits = (int) $bits;
		$max  = strlen( $ip_bin ) * 8;

		if ( $bits < 0 || $bits > $max ) {
			return false;
		}

		$bytes = intdiv( $bits, 8 );
		$rest  = $bits % 8;

		if ( $bytes > 0 && 0 !== strncmp( $ip_bin, $subnet_bin, $bytes ) ) {
			return false;
		}

		if ( 0 === $rest ) {
			return true;
		}

		$mask = chr( 0xFF << ( 8 - $rest ) & 0xFF );

		return ( $ip_bin[ $bytes ] & $mask ) === ( $subnet_bin[ $bytes ] & $mask );
	}

	/**
	 * IP real del cliente.
	 *
	 * Sin proxies declarados devuelve `REMOTE_ADDR`, que no se puede falsificar.
	 * Si `REMOTE_ADDR` es un proxy de confianza, recorre la cadena
	 * `X-Forwarded-For` de derecha a izquierda y se queda con la primera
	 * entrada que no sea otro proxy conocido: ese es el cliente real.
	 *
	 * @return string
	 */
	private static function client_ip() {
		$remote = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$remote = filter_var( $remote, FILTER_VALIDATE_IP ) ? $remote : '0.0.0.0';

		$trusted = self::trusted_proxy_ips();

		if ( ! $trusted || ! self::ip_in_list( $remote, $trusted ) ) {
			return $remote;
		}

		$headers = array( 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CF_CONNECTING_IP' );

		foreach ( $headers as $header ) {
			if ( empty( $_SERVER[ $header ] ) ) {
				continue;
			}

			$raw   = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
			$chain = array_reverse( array_filter( array_map( 'trim', explode( ',', $raw ) ), 'strlen' ) );

			foreach ( $chain as $candidate ) {
				if ( ! filter_var( $candidate, FILTER_VALIDATE_IP ) ) {
					continue;
				}

				if ( self::ip_in_list( $candidate, $trusted ) ) {
					continue;
				}

				return $candidate;
			}
		}

		return $remote;
	}

	/**
	 * Clave del contador de intentos de una cuenta.
	 *
	 * Se guarda el md5 del correo normalizado, nunca el correo en claro: el
	 * nombre del transient vive en la tabla de options.
	 *
	 * @param string $email Correo con el que se intenta entrar.
	 * @return string
	 */
	private static function account_key( $email ) {
		return 'jg_login_acc_' . md5( strtolower( trim( $email ) ) );
	}

	/**
	 * Clave del contador de intentos de un cliente.
	 *
	 * @return string
	 */
	private static function ip_key() {
		return 'jg_login_ip_' . md5( self::client_ip() );
	}

	/**
	 * ¿Alguno de los dos contadores (cuenta o IP) alcanzó su tope?
	 *
	 * Se cuentan los dos por separado a propósito: la cuenta frena el ataque
	 * dirigido a un usuario desde muchas IP distintas, y la IP frena el
	 * barrido que prueba muchas cuentas distintas desde una sola máquina. Sin
	 * el límite por cuenta, un atacante con botnet pasa los 25 por IP; sin el
	 * límite por IP, uno solo prueba todas las cuentas.
	 *
	 * @param string $email Correo con el que se intenta entrar.
	 * @return WP_Error|null
	 */
	private static function check_login_rate_limit( $email ) {
		$limits = array(
			self::account_key( $email ) => self::LOGIN_MAX_ATTEMPTS,
			self::ip_key()             => self::LOGIN_MAX_ATTEMPTS_PER_IP,
		);

		foreach ( $limits as $key => $max ) {
			if ( (int) get_transient( $key ) >= $max ) {
				return new WP_Error(
					'jg_too_many_attempts',
					'Demasiados intentos. Inténtalo de nuevo en unos minutos.',
					array( 'status' => 429 )
				);
			}
		}

		return null;
	}

	private static function register_login_attempt( $email ) {
		foreach ( array( self::account_key( $email ), self::ip_key() ) as $key ) {
			set_transient( $key, (int) get_transient( $key ) + 1, self::LOGIN_WINDOW_SECONDS );
		}
	}

	/**
	 * Clave del contador de peticiones (no de fallos) de esta IP.
	 *
	 * @return string
	 */
	private static function request_key() {
		return 'jg_login_req_' . md5( self::client_ip() );
	}

	/**
	 * Tope de peticiones por IP, 0 si está desactivado.
	 *
	 * @return int
	 */
	private static function max_requests_per_ip() {
		/**
		 * Filtra el tope de peticiones al login por IP en la ventana de cinco
		 * minutos. 0 lo desactiva.
		 *
		 * @param int $max Número máximo de peticiones. Por defecto 0.
		 */
		$max = (int) apply_filters( 'jg_max_requests_per_ip', self::LOGIN_MAX_REQUESTS_PER_IP );

		return $max > 0 ? $max : 0;
	}

	/**
	 * Cortafuegos antiabuso por IP, si está activado.
	 *
	 * No tiene nada que ver con las credenciales: solo evita que se pida al
	 * servidor una cantidad indefinida de verificaciones de contraseña en cinco
	 * minutos. Desactivado por defecto (ver LOGIN_MAX_REQUESTS_PER_IP).
	 *
	 * @return WP_Error|null
	 */
	private static function check_login_abuse() {
		$max = self::max_requests_per_ip();
		if ( 0 === $max ) {
			return null;
		}

		$key = self::request_key();
		$veces = (int) get_transient( $key );
		set_transient( $key, $veces + 1, self::LOGIN_WINDOW_SECONDS );

		if ( $veces + 1 > $max ) {
			return new WP_Error(
				'jg_too_many_requests',
				'Demasiadas peticiones seguidas. Espera unos minutos.',
				array( 'status' => 429 )
			);
		}

		return null;
	}

	private static function clear_login_rate_limit( $email ) {
		delete_transient( self::account_key( $email ) );
		delete_transient( self::ip_key() );
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

	/**
	 * Normaliza la estructura de $_FILES del campo name="files[]" a una lista
	 * de archivos individuales.
	 *
	 * @param WP_REST_Request $request
	 * @return array<int, array{name: string, type: string, tmp_name: string, error: int, size: int}>
	 */
	private static function prepare_file_params( WP_REST_Request $request ) {
		$files = $request->get_file_params();

		if ( empty( $files ) || empty( $files['files'] ) || empty( $files['files']['name'] ) || ! is_array( $files['files']['name'] ) ) {
			return array();
		}

		$normalized = array();
		$count      = count( $files['files']['name'] );

		for ( $i = 0; $i < $count; $i++ ) {
			$normalized[] = array(
				'name'     => isset( $files['files']['name'][ $i ] ) ? (string) $files['files']['name'][ $i ] : '',
				'type'     => isset( $files['files']['type'][ $i ] ) ? (string) $files['files']['type'][ $i ] : '',
				'tmp_name' => isset( $files['files']['tmp_name'][ $i ] ) ? (string) $files['files']['tmp_name'][ $i ] : '',
				'error'    => isset( $files['files']['error'][ $i ] ) ? (int) $files['files']['error'][ $i ] : UPLOAD_ERR_NO_FILE,
				'size'     => isset( $files['files']['size'][ $i ] ) ? (int) $files['files']['size'][ $i ] : 0,
			);
		}

		return $normalized;
	}

	/**
	 * Valida una imagen subida por formulario y la guarda dentro de WordPress
	 * (wp-content/uploads/jg-gallery/). Devuelve la URL pública local.
	 *
	 * @param array{name: string, type: string, tmp_name: string, error: int, size: int} $file
	 * @return string|WP_Error URL local o error.
	 */
	private static function save_uploaded_image( array $file ) {
		if ( UPLOAD_ERR_OK !== (int) $file['error'] ) {
			return new WP_Error( 'jg_upload_error', 'No se pudo recibir la imagen.', array( 'status' => 400 ) );
		}

		if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new WP_Error( 'jg_upload_error', 'El archivo de imagen no es válido.', array( 'status' => 400 ) );
		}

		if ( (int) $file['size'] > self::MAX_UPLOAD_BYTES ) {
			return new WP_Error( 'jg_file_too_large', 'Cada imagen debe pesar menos de 8 MB.', array( 'status' => 400 ) );
		}

		$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, self::ALLOWED_IMAGE_EXTENSIONS, true ) ) {
			return new WP_Error( 'jg_bad_extension', 'Solo se permiten imágenes jpg, jpeg, png, gif, webp o avif.', array( 'status' => 400 ) );
		}

		$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
		if ( empty( $check['ext'] ) || ! in_array( strtolower( $check['ext'] ), self::ALLOWED_IMAGE_EXTENSIONS, true ) ) {
			return new WP_Error( 'jg_invalid_image_file', 'El archivo no es una imagen válida.', array( 'status' => 400 ) );
		}

		$gen  = self::jg_uploads_dir();
		$name = wp_unique_filename( $gen['dir'], sanitize_file_name( $file['name'] ) );

		if ( ! move_uploaded_file( $file['tmp_name'], trailingslashit( $gen['dir'] ) . $name ) ) {
			return new WP_Error( 'jg_save_failed', 'No se pudo guardar la imagen en el servidor.', array( 'status' => 500 ) );
		}

		return trailingslashit( $gen['url'] ) . $name;
	}

	private static function jg_uploads_dir() {
		$uploads = wp_upload_dir();
		$dir     = trailingslashit( $uploads['basedir'] ) . 'jg-gallery';

		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		if ( ! is_writable( $dir ) ) {
			@chmod( $dir, 0777 );
		}

		return array(
			'dir' => $dir,
			'url' => trailingslashit( $uploads['baseurl'] ) . 'jg-gallery',
		);
	}

	/**
	 * Recolecta las fuentes de imagen de la publicación: subidas nuevas de
	 * archivo (multipart) + URLs conservadas de edición previa.
	 *
	 * @param WP_REST_Request $request
	 * @return array|WP_Error Lista de URLs locales/externas o error.
	 */
	private static function collect_image_sources( WP_REST_Request $request ) {
		$urls = array();

		foreach ( self::prepare_file_params( $request ) as $file ) {
			if ( UPLOAD_ERR_NO_FILE === (int) $file['error'] ) {
				continue;
			}
			$stored = self::save_uploaded_image( $file );
			if ( is_wp_error( $stored ) ) {
				return $stored;
			}
			$urls[] = $stored;
		}

		$existing = $request->get_param( 'existing' );
		if ( null === $existing ) {
			$existing = $request->get_param( 'image_urls' );
		}

		if ( ! empty( $existing ) ) {
			$validated = self::validate_image_urls( $existing );
			if ( is_wp_error( $validated ) ) {
				return $validated;
			}
			$urls = array_merge( $urls, $validated );
		}

		if ( count( $urls ) > self::MAX_IMAGES_PER_POST ) {
			return new WP_Error(
				'jg_too_many_images',
				'Puedes subir un máximo de ' . self::MAX_IMAGES_PER_POST . ' imágenes por publicación.',
				array( 'status' => 400 )
			);
		}

		return $urls;
	}

	/**
	 * Convierte una URL local de la galería en su ruta en disco.
	 *
	 * @param string $url
	 * @return string Ruta local, o '' si no pertenece a jg-gallery.
	 */
	private static function local_image_path( $url ) {
		$uploads = wp_upload_dir();
		$base    = trailingslashit( $uploads['baseurl'] ) . 'jg-gallery/';

		if ( strpos( $url, $base ) !== 0 ) {
			return '';
		}

		return trailingslashit( $uploads['basedir'] ) . 'jg-gallery/' . substr( $url, strlen( $base ) );
	}

	/**
	 * Elimina del disco los archivos locales que ya no se referencian.
	 *
	 * @param array $urls URLs locales a descartar.
	 */
	private static function delete_local_images( $urls ) {
		foreach ( (array) $urls as $url ) {
			$path = self::local_image_path( $url );
			if ( $path && is_file( $path ) ) {
				unlink( $path );
			}
		}
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

	/**
	 * Longitud en caracteres UTF-8 sin depender de la extensión mbstring, que
	 * falta en algunos hostings mínimos y provocaba un fatal
	 * ("Call to undefined function mb_strlen()") al subir o editar imágenes.
	 */
	private static function char_length( $value ) {
		$value = (string) $value;

		if ( function_exists( 'mb_strlen' ) ) {
			return mb_strlen( $value, 'UTF-8' );
		}

		$count = preg_match_all( '/./us', $value );

		return false === $count ? strlen( $value ) : $count;
	}

	private static function get_measurements( WP_REST_Request $request ) {
		$measurements = sanitize_text_field( (string) $request->get_param( 'measurements' ) );
		if ( self::char_length( $measurements ) > 20 ) {
			return new WP_Error( 'jg_measurements_too_long', 'Las medidas pueden tener hasta 20 caracteres.', array( 'status' => 400 ) );
		}

		return $measurements;
	}

	private static function get_usage( WP_REST_Request $request ) {
		$usage = sanitize_text_field( (string) $request->get_param( 'usage' ) );
		if ( self::char_length( $usage ) > 43 ) {
			return new WP_Error( 'jg_usage_too_long', 'El uso puede tener hasta 43 caracteres.', array( 'status' => 400 ) );
		}

		return $usage;
	}

	public static function upload_image( WP_REST_Request $request ) {
		$user = self::authenticated_user( $request );
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$title        = sanitize_text_field( (string) $request->get_param( 'title' ) );
		$description  = sanitize_textarea_field( (string) $request->get_param( 'description' ) );
		$usage        = self::get_usage( $request );
		if ( is_wp_error( $usage ) ) {
			return $usage;
		}
		$measurements = self::get_measurements( $request );
		if ( is_wp_error( $measurements ) ) {
			return $measurements;
		}
		$category_id = (int) $request->get_param( 'category_id' );
		

		if ( '' === $title ) {
			return new WP_Error( 'jg_no_title', 'El título es obligatorio.', array( 'status' => 400 ) );
		}
		if ( self::char_length( $title ) > 43 ) {
			return new WP_Error( 'jg_title_too_long', 'El título puede tener hasta 43 caracteres.', array( 'status' => 400 ) );
		}
		if ( self::char_length( $description ) > 120 ) {
			return new WP_Error( 'jg_description_too_long', 'La descripción puede tener hasta 120 caracteres.', array( 'status' => 400 ) );
		}

		$urls = self::collect_image_sources( $request );
		if ( is_wp_error( $urls ) ) {
			return $urls;
		}
		if ( empty( $urls ) ) {
			return new WP_Error( 'jg_no_image_url', 'Debes subir al menos una imagen.', array( 'status' => 400 ) );
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
		if ( '' !== $usage ) {
			update_post_meta( $post_id, '_jg_usage', $usage );
		}
		if ( '' !== $measurements ) {
			update_post_meta( $post_id, '_jg_measurements', $measurements );
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

		$title        = sanitize_text_field( (string) $request->get_param( 'title' ) );
		$description  = sanitize_textarea_field( (string) $request->get_param( 'description' ) );
		$usage        = self::get_usage( $request );
		$measurements = self::get_measurements( $request );
		$category_id  = (int) $request->get_param( 'category_id' );

		if ( is_wp_error( $usage ) ) {
			return $usage;
		}
		if ( is_wp_error( $measurements ) ) {
			return $measurements;
		}
		

		if ( '' === $title ) {
			return new WP_Error( 'jg_no_title', 'El título es obligatorio.', array( 'status' => 400 ) );
		}

		if ( self::char_length( $title ) > 43 ) {
			return new WP_Error( 'jg_title_too_long', 'El título puede tener hasta 43 caracteres.', array( 'status' => 400 ) );
		}
		if ( self::char_length( $description ) > 120 ) {
			return new WP_Error( 'jg_description_too_long', 'La descripción puede tener hasta 120 caracteres.', array( 'status' => 400 ) );
		}

		$old_urls = get_post_meta( $post->ID, '_jg_image_url', false );
		$old_urls = is_array( $old_urls ) ? $old_urls : array();

		$urls = self::collect_image_sources( $request );
		if ( is_wp_error( $urls ) ) {
			return $urls;
		}
		if ( empty( $urls ) ) {
			return new WP_Error( 'jg_no_image_url', 'Debes mantener o subir al menos una imagen.', array( 'status' => 400 ) );
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
		if ( '' === $usage ) {
			delete_post_meta( $post->ID, '_jg_usage' );
		} else {
			update_post_meta( $post->ID, '_jg_usage', $usage );
		}
		if ( '' === $measurements ) {
			delete_post_meta( $post->ID, '_jg_measurements' );
		} else {
			update_post_meta( $post->ID, '_jg_measurements', $measurements );
		}
		self::assign_category( $post->ID, $category_id );

		self::delete_local_images( array_diff( array_unique( $old_urls ), $urls ) );

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

		$old_urls = get_post_meta( $post->ID, '_jg_image_url', false );
		$old_urls = is_array( $old_urls ) ? $old_urls : array();

		wp_delete_post( $post->ID, true );
		self::delete_local_images( $old_urls );

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

		$items = array();
		foreach ( $query->posts as $post ) {
			$items[] = self::format_image( $post );
		}

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

		$author = get_the_author_meta( 'display_name', $post->post_author );

		$response = array(
			'id'          => $post->ID,
			'title'        => get_the_title( $post ),
			'description'  => $post->post_content,
			'usage'        => get_post_meta( $post->ID, '_jg_usage', true ),
			'measurements' => get_post_meta( $post->ID, '_jg_measurements', true ),
			'images'       => $urls,
			'date'        => get_the_date( 'c', $post ),
			'author'      => $author,
			'author_id'   => (int) $post->post_author,
			'category'    => $category,
		);

		return $response;
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

	private static function require_category_manager( $user ) {
		// El usuario ya fue autenticado vía JWT por authenticated_user() antes de
		// llegar aquí. Las categorías son compartidas por todo el panel (igual que
		// en el admin nativo de WordPress), por lo que cualquier usuario con sesión
		// iniciada puede crearlas, editarlas y eliminarlas. Esto se alinea con el
		// modelo de permisos de las imágenes del plugin, que solo exige
		// autenticación, no la capability manage_categories (que solo tienen
		// Administrador y Editor).
		if ( ! $user || ! ( $user instanceof WP_User ) ) {
			return new WP_Error( 'jg_forbidden', 'No tienes permisos para gestionar categorías.', array( 'status' => 403 ) );
		}
		return null;
	}

	public static function create_category( WP_REST_Request $request ) {
		$user = self::authenticated_user( $request );
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$cap = self::require_category_manager( $user );
		if ( is_wp_error( $cap ) ) {
			return $cap;
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

		$cap = self::require_category_manager( $user );
		if ( is_wp_error( $cap ) ) {
			return $cap;
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

		$cap = self::require_category_manager( $user );
		if ( is_wp_error( $cap ) ) {
			return $cap;
		}

		$term_id = (int) $request->get_param( 'id' );
		$term    = get_term( $term_id, JG_CPT::TAXONOMY );

		if ( ! $term || is_wp_error( $term ) ) {
			return new WP_Error( 'jg_category_not_found', 'La categoría no existe.', array( 'status' => 404 ) );
		}

		// Eliminar también las piezas publicadas de la categoría para que la
		// categoría desaparezca por completo del catálogo (pestaña y piezas),
		// incluidos sus archivos locales en wp-content/uploads/jg-gallery/.
		$query = new WP_Query(
			array(
				'post_type'      => 'jg_gallery_image',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'tax_query'      => array(
					array(
						'taxonomy' => JG_CPT::TAXONOMY,
						'field'    => 'term_id',
						'terms'    => $term_id,
					),
				),
			)
		);

		foreach ( $query->posts as $post_id ) {
			$urls = get_post_meta( $post_id, '_jg_image_url', false );
			if ( is_array( $urls ) ) {
				self::delete_local_images( $urls );
			}
			wp_delete_post( $post_id, true );
		}

		$result = wp_delete_term( $term_id, JG_CPT::TAXONOMY );

		if ( is_wp_error( $result ) || false === $result ) {
			return new WP_Error( 'jg_category_failed', 'No se pudo eliminar la categoría.', array( 'status' => 500 ) );
		}

		return rest_ensure_response( array( 'deleted' => true, 'id' => $term_id, 'pieces_deleted' => count( $query->posts ) ) );
	}
}
