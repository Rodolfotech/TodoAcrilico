<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JG_REST {

	const NS = 'jwt-gallery/v1';

	const ALLOWED_IMAGE_EXTENSIONS = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif' );

	const MAX_IMAGES_PER_POST = 10;

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

	public static function upload_image( WP_REST_Request $request ) {
		$user = self::authenticated_user( $request );
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		$title        = sanitize_text_field( (string) $request->get_param( 'title' ) );
		$description  = sanitize_textarea_field( (string) $request->get_param( 'description' ) );
		$measurements = self::get_measurements( $request );
		if ( is_wp_error( $measurements ) ) {
			return $measurements;
		}
		$raw_urls     = $request->get_param( 'image_urls' );
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
		$measurements = self::get_measurements( $request );
		$raw_urls     = $request->get_param( 'image_urls' );
		$category_id  = (int) $request->get_param( 'category_id' );

		if ( is_wp_error( $measurements ) ) {
			return $measurements;
		}

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
		if ( '' === $measurements ) {
			delete_post_meta( $post->ID, '_jg_measurements' );
		} else {
			update_post_meta( $post->ID, '_jg_measurements', $measurements );
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

		$result = wp_delete_term( $term_id, JG_CPT::TAXONOMY );

		if ( is_wp_error( $result ) || false === $result ) {
			return new WP_Error( 'jg_category_failed', 'No se pudo eliminar la categoría.', array( 'status' => 500 ) );
		}

		return rest_ensure_response( array( 'deleted' => true, 'id' => $term_id ) );
	}
}
