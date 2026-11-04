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
				// Límites reales del servidor (JG_REST): los usan el maxlength de
				// los campos y los popovers de ayuda del panel.
				'limits'   => array(
					'title'        => JG_REST::MAX_TITLE_CHARS,
					'description'  => JG_REST::MAX_DESCRIPTION_CHARS,
					'usage'        => JG_REST::MAX_USAGE_CHARS,
					'measurements' => JG_REST::MAX_MEASUREMENTS_CHARS,
					'images'       => JG_REST::MAX_IMAGES_PER_POST,
					'imageMb'      => (int) ( JG_REST::MAX_UPLOAD_BYTES / 1048576 ),
					'formats'      => JG_REST::ALLOWED_IMAGE_EXTENSIONS,
				),
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
			<div class="jg-brand">
				<img class="jg-brand-logo" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo.webp' ); ?>" alt="TodoAcrilico">
				<span class="jg-brand-name">TodoAcrílico</span>
			</div>
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
				<div class="jg-brand">
					<img class="jg-brand-logo" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo.webp' ); ?>" alt="TodoAcrilico">
					<span class="jg-brand-name">TodoAcrílico</span>
				</div>
				<div class="jg-topbar-actions">
					<span class="jg-topbar-user" data-jg-user></span>
					<button type="button" class="jg-button-secondary" data-jg-logout>Cerrar sesión</button>
				</div>
			</header>

			<div class="jg-layout">

				<aside class="jg-sidebar">
					<div class="jg-panel jg-sidebar-block">
						<h3>Subir imagen</h3>
						<p class="jg-sidebar-hint">Sube imágenes desde tu dispositivo, se guardan dentro de WordPress</p>
						<form class="jg-form" data-jg-upload>
							<label>
								<span class="jg-label-row">Título <span data-jg-hint="title"></span></span>
								<input type="text" name="title" maxlength="<?php echo (int) JG_REST::MAX_TITLE_CHARS; ?>" placeholder="Ej. Vitrina modular 03" required>
							</label>
							<label>
								<span class="jg-label-row">Descripción <span data-jg-hint="description"></span></span>
								<textarea name="description" maxlength="<?php echo (int) JG_REST::MAX_DESCRIPTION_CHARS; ?>" rows="3" placeholder="Breve descripción de la pieza…"></textarea>
								</label>
								<label>
									<span class="jg-label-row">Uso <span data-jg-hint="usage"></span></span>
									<input type="text" name="usage" maxlength="<?php echo (int) JG_REST::MAX_USAGE_CHARS; ?>" placeholder="Ej. Exhibición en vitrinas">
								</label>
								<label>
									<span class="jg-label-row">Medidas <span data-jg-hint="measurements"></span></span>
									<input type="text" name="measurements" maxlength="<?php echo (int) JG_REST::MAX_MEASUREMENTS_CHARS; ?>" placeholder="Ej. 20 × 30 cm">
							</label>
							<label>
								<span class="jg-label-row">Imágenes (misma pieza en distintos ángulos) <span data-jg-hint="images"></span></span>
							</label>
							<div data-jg-upload-images-mount></div>
							<label>
								<span class="jg-label-row">Categoría <span data-jg-hint="category"></span></span>
								<select name="category_id" data-jg-category-select >
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
