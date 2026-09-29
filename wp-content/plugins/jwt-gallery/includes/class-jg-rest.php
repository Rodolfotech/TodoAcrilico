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

	const LOGIN_WINDOW_SECONDS = 300;

	const COOKIE_NAME = 'jg_session';

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

		return $user;
	}

	public static function require_auth( WP_REST_Request $request ) {
		return ! is_wp_error( self::authenticated_user( $request ) );
	}

	public static function login( WP_REST_Request $request ) {
		$email    = sanitize_email( (string) $request->get_param( 'email' ) );
		$password = (string) $request->get_param( 'password' );

		$limit = self::check_login_rate_limit();
		if ( is_wp_error( $limit ) ) {
			return $limit;
		}

		if ( ! $email || ! is_email( $email ) || '' === $password ) {
			return new WP_Error( 'jg_bad_request', 'Correo y contraseña son obligatorios.', array( 'status' => 400 ) );
		}

		$user = get_user_by( 'email', $email );

		if ( ! $user || ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
			self::register_login_attempt();
			return new WP_Error( 'jg_invalid_credentials', 'Correo o contraseña incorrectos.', array( 'status' => 401 ) );
		}

		self::clear_login_rate_limit();

		$ttl   = JG_JWT::DEFAULT_TTL;
		$token = JG_JWT::issue(
			array(
				'sub'   => $user->ID,
				'email' => $user->user_email,
			),
			$ttl
		);

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

	private static function login_client_id() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';

		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$parts = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$first = trim( $parts[0] );
			if ( filter_var( $first, FILTER_VALIDATE_IP ) ) {
				$ip = $first;
			}
		}

		return 'jg_login_' . md5( $ip );
	}

	private static function check_login_rate_limit() {
		$transient = self::login_client_id();
		$attempts  = (int) get_transient( $transient );

		if ( $attempts >= self::LOGIN_MAX_ATTEMPTS ) {
			return new WP_Error(
				'jg_too_many_attempts',
				'Demasiados intentos. Inténtalo de nuevo en unos minutos.',
				array( 'status' => 429 )
			);
		}

		return null;
	}

	private static function register_login_attempt() {
		$transient = self::login_client_id();
		$attempts  = (int) get_transient( $transient );

		set_transient( $transient, $attempts + 1, self::LOGIN_WINDOW_SECONDS );
	}

	private static function clear_login_rate_limit() {
		delete_transient( self::login_client_id() );
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

	private static function get_measurements( WP_REST_Request $request ) {
		$measurements = sanitize_text_field( (string) $request->get_param( 'measurements' ) );
		if ( mb_strlen( $measurements ) > 20 ) {
			return new WP_Error( 'jg_measurements_too_long', 'Las medidas pueden tener hasta 20 caracteres.', array( 'status' => 400 ) );
		}

		return $measurements;
	}

	private static function get_usage( WP_REST_Request $request ) {
		$usage = sanitize_text_field( (string) $request->get_param( 'usage' ) );
		if ( mb_strlen( $usage ) > 43 ) {
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
		if ( mb_strlen( $title ) > 43 ) {
			return new WP_Error( 'jg_title_too_long', 'El título puede tener hasta 43 caracteres.', array( 'status' => 400 ) );
		}
		if ( mb_strlen( $description ) > 120 ) {
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

		if ( mb_strlen( $title ) > 43 ) {
			return new WP_Error( 'jg_title_too_long', 'El título puede tener hasta 43 caracteres.', array( 'status' => 400 ) );
		}
		if ( mb_strlen( $description ) > 120 ) {
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
