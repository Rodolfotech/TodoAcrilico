<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TA_VERSION', '1.14.20' );

function ta_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );

	register_nav_menus(
		array(
			'primary' => 'Menú principal',
		)
	);
}
add_action( 'after_setup_theme', 'ta_setup' );

function ta_assets() {
	wp_enqueue_style( 'ta-style', get_stylesheet_uri(), array(), TA_VERSION );
	wp_enqueue_script( 'ta-carousel', get_template_directory_uri() . '/assets/js/ta-carousel.js', array(), TA_VERSION, true );
	wp_enqueue_script( 'ta-nav', get_template_directory_uri() . '/assets/js/ta-nav.js', array(), TA_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'ta_assets' );

/**
 * Botón reutilizable (CTA). Variantes: 'accent' (teal, la de siempre), 'dark' (relleno oscuro)
 * y 'outline' (contorno). $args['icon'] agrega una flecha "→" al final.
 */
function ta_button( $label, $url, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'variant' => 'accent',
			'icon'    => false,
			'class'   => '',
		)
	);

	$classes = array( 'ta-button', 'ta-button--' . $args['variant'] );
	if ( $args['class'] ) {
		$classes[] = $args['class'];
	}

	printf(
		'<a class="%1$s" href="%2$s">%3$s%4$s</a>',
		esc_attr( implode( ' ', $classes ) ),
		esc_url( $url ),
		esc_html( $label ),
		$args['icon'] ? '<span class="ta-button-icon" aria-hidden="true">→</span>' : ''
	);
}

/**
 * Imagen del hero de Inicio, editable desde Personalizar → Identidad del sitio
 * (sin tocar código). Devuelve '' si todavía no se ha cargado ninguna —
 * front-page.php muestra un estado vacío en ese caso, en vez de un <img> roto.
 */
function ta_customize_register( $wp_customize ) {
	$wp_customize->add_setting(
		'ta_hero_image_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'ta_hero_image_url',
			array(
				'label'    => 'Imagen del hero (Inicio)',
				'section'  => 'title_tagline',
				'settings' => 'ta_hero_image_url',
			)
		)
	);
}
add_action( 'customize_register', 'ta_customize_register' );

function ta_get_hero_image_url() {
    // Debe leer 'ta_hero_image_url' — es la key que registra ta_customize_register()
    // y bajo la que WP realmente guarda el valor (ver theme_mods_todo-acrilico en
    // wp_options). Antes leía 'ta_hero_image' (sin "_url"), una key que nunca se
    // escribe, así que cambiar la imagen desde Personalizar nunca tenía efecto.
    return get_theme_mod( 'ta_hero_image_url', 'https://i.postimg.cc/TYX3CTrK/home.webp' );
}
/**
 * Ícono SVG en línea (trazo, sin relleno) para las tarjetas de ta_render_feature_grid(),
 * el footer y la página de Contacto/Cuidados. Nombres disponibles: 'grid', 'eye', 'shield',
 * 'info', 'pane', 'pin', 'mail', 'clock', 'paperclip', 'thermometer', 'prohibited', 'spray',
 * 'circular'. Cualquier otro nombre devuelve un ícono genérico (círculo) para que nunca se
 * quede algo sin ícono. `$size` (default 28) controla el `width`/`height` del SVG devuelto.
 */
function ta_icon( $name, $size = 28 ) {
	$icons = array(
		'grid'   => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
		'eye'    => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>',
		'shield' => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v6c0 5-3.5 8.5-7 9-3.5-.5-7-4-7-9V6l7-3Z"/></svg>',
		'info'   => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="10" x2="12" y2="16"/><circle cx="12" cy="7" r="1" fill="currentColor" stroke="none"/></svg>',
		'pane'   => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M4 15l5-5 4 4 3-3 4 4"/></svg>',
		'pin'    => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-6.5-7-11a7 7 0 0 1 14 0c0 4.5-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>',
		'mail'   => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>',
		'clock'  => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg>',
		'paperclip'  => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a5 5 0 0 1-7.07-7.07l9.19-9.19a3.5 3.5 0 0 1 4.95 4.95l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>',
		'thermometer' => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4a2 2 0 0 0-4 0v10.5a4 4 0 1 0 4 0V4Z"/><line x1="12" y1="8" x2="12" y2="14"/></svg>',
		'prohibited' => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><line x1="5.5" y1="5.5" x2="18.5" y2="18.5"/></svg>',
		'spray'      => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="9" width="7" height="12" rx="1"/><path d="M10 9V6a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v3"/><path d="M15 5h3"/><path d="M16 8h3"/><path d="M17 3h2"/></svg>',
		'circular'   => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 3.5 7.1"/><path d="M3 17v-5h5"/></svg>',
	);

	$svg = isset( $icons[ $name ] ) ? $icons[ $name ] : '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg>';

	if ( 28 !== (int) $size ) {
		$svg = str_replace( 'width="28" height="28"', 'width="' . (int) $size . '" height="' . (int) $size . '"', $svg );
	}

	return $svg;
}

/**
 * Grilla de tarjetas reutilizable (ícono + título + bajada + link opcional "Ver piezas →").
 * $items: array de array{icon: string (SVG en línea), title: string, description: string, url?: string}.
 * `url` es opcional — si una tarjeta no lo trae, no se imprime el botón (tarjetas puramente
 * informativas, ej. la guía de cuidados). $description (opcional) agrega un párrafo entre
 * el título de la sección y la grilla. Usado en front-page.php para las 4 categorías
 * destacadas y en page-cuidados-del-acrilico.php para la guía de mantenimiento.
 */
function ta_render_feature_grid( $eyebrow, $title, $items, $description = '' ) {
    ?>
    <section class="ta-features">
        <div class="ta-container">
            <p class="ta-features-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
            <h2 class="ta-features-title"><?php echo esc_html( $title ); ?></h2>
            <?php if ( $description ) : ?>
                <!-- CAMBIO: Habilitado wp_kses para permitir <br> en la descripción general de la grilla -->
                <p class="ta-features-description"><?php echo wp_kses( $description, array( 'br' => array() ) ); ?></p>
            <?php endif; ?>
            <div class="ta-features-grid">
                <?php foreach ( $items as $item ) : ?>
                    <article class="ta-feature-card">
                        <span class="ta-feature-icon" aria-hidden="true"><?php echo $item['icon']; ?></span>
                        <h3><?php echo esc_html( $item['title'] ); ?></h3>
                        
                        <!-- CAMBIO: Habilitado wp_kses para permitir <br> en la descripción de cada tarjeta -->
                        <p><?php echo wp_kses( $item['description'], array( 'br' => array() ) ); ?></p>
                        
                        <?php if ( ! empty( $item['url'] ) ) : ?>
                            <?php
                            ta_button(
                                'Ver piezas',
                                $item['url'],
                                array(
                                    'variant' => 'text',
                                    'icon'    => true,
                                )
                            );
                            ?>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Grilla de "destacados" reutilizable: encabezado (eyebrow + título + link opcional
 * "Ver todo el catálogo" alineado a la derecha) y tarjetas grandes de foto + título +
 * bajada, sin ícono ni borde — pensada para fotos de producto en vez de íconos.
 *
 * $items: array de array{title: string, description: string, image_url: string}.
 * $cta (opcional): array{label: string, url: string} para el link del encabezado.
 *
 * Si `image_url` viene vacío, se muestra un placeholder rayado en vez de un <img> roto —
 * así se puede armar la sección antes de tener las fotos reales y completarlas después
 * editando solo el array de `$items`, sin tocar el markup.
 */
function ta_render_showcase_grid( $eyebrow, $title, $items, $cta = null ) {
    ?>
    <section class="ta-showcase">
        <div class="ta-container">
            <div class="ta-showcase-header">
                <div>
                    <p class="ta-showcase-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
                    <h2 class="ta-showcase-title"><?php echo esc_html( $title ); ?></h2>
                </div>
                <?php if ( $cta ) : ?>
                    <?php
                    ta_button(
                        $cta['label'],
                        $cta['url'],
                        array(
                            'variant' => 'text',
                            'class'   => 'ta-showcase-cta',
                        )
                    );
                    ?>
                <?php endif; ?>
            </div>
            <div class="ta-showcase-grid">
                <?php foreach ( $items as $item ) : ?>
                    <article class="ta-showcase-card">
                        <div class="ta-showcase-figure">
                            <?php if ( ! empty( $item['image_url'] ) ) : ?>
                                <img src="<?php echo esc_url( $item['image_url'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy">
                            <?php else : ?>
                                <div class="ta-showcase-figure-empty" aria-hidden="true"></div>
                            <?php endif; ?>
                        </div>
                        <h3><?php echo esc_html( $item['title'] ); ?></h3>
                        <p><?php echo esc_html( $item['description'] ); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}
/**
 * Sección "nuestra historia" reutilizable: foto grande (con una insignia opcional del
 * logo superpuesta en la esquina) y, al otro lado, eyebrow + título + párrafos + una
 * fila opcional de estadísticas (ej. "5k+ Proyectos realizados").
 *
 * $args:
 * - eyebrow (string), title (string), paragraphs (array de string)
 * - stats (array de array{value: string, label: string}) — si viene vacío, no se
 *   imprime la fila de estadísticas (la rayita divisoria sí se imprime siempre,
 *   después de los párrafos).
 * - image_url (string) — si viene vacío, se muestra un placeholder rayado en vez de
 *   un <img> roto, para poder armar la sección antes de tener la foto real.
 * - show_logo_badge (bool, default true) — usa el logo del tema (assets/img/logo.webp,
 *   header.php §1.3), no una URL nueva.
 * - media_position ('left'|'right', default 'left') — de qué lado va la foto.
 * - panel (bool, default false) — envuelve el contenido en una tarjeta con fondo e
 *   inset, para secciones tipo "consejo destacado" en vez del layout a página completa.
 */

/**
 * Busca esta función en tu functions.php o inc/template-tags.php
 */
function ta_render_story_section( $args ) {
    $args = wp_parse_args(
        $args,
        array(
            'eyebrow'         => '',
            'title'           => '',
            'paragraphs'      => array(),
            'stats'           => array(),
            'image_url'       => '',
            'show_logo_badge' => true,
            'media_position'  => 'left',
            'panel'           => false,
        )
    );

    $section_classes = array( 'ta-story' );
    if ( $args['panel'] ) {
        $section_classes[] = 'ta-story--panel';
    }

    $inner_classes = array( 'ta-container', 'ta-story-inner' );
    if ( 'right' === $args['media_position'] ) {
        $inner_classes[] = 'ta-story-inner--media-right';
    }
    ?>
    <section class="<?php echo esc_attr( implode( ' ', $section_classes ) ); ?>">
        <div class="<?php echo esc_attr( implode( ' ', $inner_classes ) ); ?>">
            <div class="ta-story-media">
                <?php if ( $args['image_url'] ) : ?>
                    <img src="<?php echo esc_url( $args['image_url'] ); ?>" alt="<?php echo esc_attr( $args['title'] ); ?>" loading="lazy">
                <?php else : ?>
                    <div class="ta-story-media-empty" aria-hidden="true"></div>
                <?php endif; ?>
                <?php if ( $args['show_logo_badge'] ) : ?>
                    <div class="ta-story-badge">
                        <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo.webp' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="ta-story-content">
                <p class="ta-story-eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
                <h2 class="ta-story-title"><?php echo esc_html( $args['title'] ); ?></h2>
                
                <!-- ======================================================= -->
                <!-- ¡AQUÍ VA EL CÓDIGO! Reemplaza el foreach anterior por este: -->
                <?php foreach ( $args['paragraphs'] as $paragraph ) : ?>
                    <p><?php echo wp_kses( $paragraph, array( 'br' => array() ) ); ?></p>
                <?php endforeach; ?>
                <!-- ======================================================= -->
                
                <hr class="ta-story-divider">
                <?php if ( $args['stats'] ) : ?>
                    <div class="ta-story-stats">
                        <?php foreach ( $args['stats'] as $stat ) : ?>
                            <div class="ta-story-stat">
                                <span class="ta-story-stat-value"><?php echo esc_html( $stat['value'] ); ?></span>
                                <span class="ta-story-stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Sección de cierre reutilizable (llamado a la acción): título + bajada opcional +
 * un botón, todo centrado. Pensada para invitar a contactar/cotizar al final de
 * una página, pero sin nada específico de "contacto" — el título, la bajada, el
 * texto del botón y el link son parámetros.
 */
function ta_render_cta_section( $title, $description, $button_label, $button_url ) {
	?>
	<section class="ta-cta">
		<div class="ta-container ta-cta-inner">
			<h2 class="ta-cta-title"><?php echo esc_html( $title ); ?></h2>
			<?php if ( $description ) : ?>
				<p class="ta-cta-description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
			<?php ta_button( $button_label, $button_url, array( 'variant' => 'dark' ) ); ?>
		</div>
	</section>
	<?php
}

/**
 * Correo donde llegan los mensajes del formulario de Contacto (page-contacto.php,
 * §1.10). Filtrable con `ta_contact_email` para no tener que tocar código si cambia.
 */
function ta_get_contact_email() {
	return apply_filters( 'ta_contact_email', 'contacto@todoacrilico.cl' );
}

define( 'TA_CONTACT_MAX_FILE_SIZE', 5 * 1024 * 1024 ); // 5 MB.

/**
 * Manejador del formulario de Contacto. Registrado tanto para visitantes sin sesión
 * (`_nopriv_`) como con sesión — es un formulario público, tiene que aceptar los dos.
 * Valida/sanitiza cada campo, revisa nonce + honeypot, maneja el adjunto con cuidado
 * (extensión + contenido real de imagen + tamaño máximo) y redirige de vuelta a la
 * página de Contacto con un parámetro en la URL (éxito/error) en vez de recargar en
 * blanco — así funciona sin JavaScript y sin reenvíos accidentales al refrescar.
 */
add_action( 'admin_post_nopriv_ta_contact_submit', 'ta_handle_contact_submit' );
add_action( 'admin_post_ta_contact_submit', 'ta_handle_contact_submit' );

function ta_handle_contact_submit() {
	$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : home_url( '/contacto/' );

	$fail = function ( $reason ) use ( $redirect_to ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'ta_contact' => 'error',
					'reason'     => $reason,
				),
				$redirect_to
			)
		);
		exit;
	};

	if ( ! isset( $_POST['ta_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ta_contact_nonce'] ) ), 'ta_contact_submit' ) ) {
		$fail( 'nonce' );
	}

	// Honeypot: un campo oculto que solo un bot completaría. Si viene lleno, se
	// descarta en silencio simulando éxito — no le da pistas a quien lo esté probando.
	if ( ! empty( $_POST['ta_contact_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'ta_contact', 'success', $redirect_to ) );
		exit;
	}

	$nombre      = isset( $_POST['nombre'] ) ? sanitize_text_field( wp_unslash( $_POST['nombre'] ) ) : '';
	$email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$descripcion = isset( $_POST['descripcion'] ) ? sanitize_textarea_field( wp_unslash( $_POST['descripcion'] ) ) : '';

	if ( '' === $nombre || '' === $descripcion ) {
		$fail( 'campos' );
	}

	if ( '' === $email || ! is_email( $email ) ) {
		$fail( 'email' );
	}

	$attachments  = array();
	$adjunto_path = '';

	if ( ! empty( $_FILES['adjunto'] ) && UPLOAD_ERR_NO_FILE !== $_FILES['adjunto']['error'] ) {
		if ( UPLOAD_ERR_OK !== $_FILES['adjunto']['error'] ) {
			$fail( 'archivo' );
		}

		if ( $_FILES['adjunto']['size'] > TA_CONTACT_MAX_FILE_SIZE ) {
			$fail( 'archivo_grande' );
		}

		$allowed_ext = array( 'jpg', 'jpeg', 'png', 'gif', 'webp' );
		$ext         = strtolower( pathinfo( $_FILES['adjunto']['name'], PATHINFO_EXTENSION ) );

		// Extensión Y contenido real (getimagesize falla si no es una imagen de verdad,
		// aunque alguien le haya puesto extensión .jpg a otra cosa).
		if ( ! in_array( $ext, $allowed_ext, true ) || ! @getimagesize( $_FILES['adjunto']['tmp_name'] ) ) {
			$fail( 'archivo_tipo' );
		}

		$upload_dir = wp_upload_dir();
		$target_dir = trailingslashit( $upload_dir['basedir'] ) . 'ta-contacto-tmp/';
		if ( ! file_exists( $target_dir ) ) {
			wp_mkdir_p( $target_dir );
		}

		$adjunto_path = $target_dir . wp_unique_filename( $target_dir, sanitize_file_name( $_FILES['adjunto']['name'] ) );

		// move_uploaded_file() verifica por su cuenta que el archivo venga de una subida
		// HTTP real — no acepta rutas arbitrarias aunque alguien falsifique $_FILES.
		if ( ! move_uploaded_file( $_FILES['adjunto']['tmp_name'], $adjunto_path ) ) {
			$fail( 'archivo' );
		}

		$attachments[] = $adjunto_path;
	}

	$subject = sprintf( 'Nuevo mensaje de contacto — %s', $nombre );
	$body    = "Nombre: {$nombre}\n" .
		"Correo: {$email}\n\n" .
		"Descripción del proyecto:\n{$descripcion}\n";

	// Sin "From" propio a propósito: dejarlo así es lo que hace que un plugin SMTP
	// (WP Mail SMTP, etc.) controle el remitente sin que este código le compita.
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $nombre . ' <' . $email . '>',
	);

	$sent = wp_mail( ta_get_contact_email(), $subject, $body, $headers, $attachments );

	if ( $adjunto_path && file_exists( $adjunto_path ) ) {
		wp_delete_file( $adjunto_path );
	}

	if ( ! $sent ) {
		$fail( 'envio' );
	}

	wp_safe_redirect( add_query_arg( 'ta_contact', 'success', $redirect_to ) );
	exit;
}

/**
 * Categorías de la galería (plugin JWT Gallery) con sus imágenes publicadas,
 * para usarlas en page-nuestras-soluciones.php. Devuelve un array vacío si el
 * plugin no está activo, en vez de fallar.
 *
 * Cada publicación puede tener varias fotos (`_jg_image_url` es meta repetible,
 * ver Docs_Plugin.md §12.19); por eso `images` es un array de URLs, no una sola.
 *
 * @return array<int, array{name: string, slug: string, images: array<int, array{title: string, description: string, images: array<int, string>}>}>
 */
function ta_get_solutions_by_category() {
	if ( ! post_type_exists( 'jg_gallery_image' ) || ! taxonomy_exists( 'jg_gallery_category' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'jg_gallery_category',
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	$groups = array();

	foreach ( $terms as $term ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'jg_gallery_image',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'tax_query'      => array(
					array(
						'taxonomy' => 'jg_gallery_category',
						'field'    => 'term_id',
						'terms'    => $term->term_id,
					),
				),
			)
		);

		if ( ! $query->have_posts() ) {
			continue;
		}

		$images = array();
		foreach ( $query->posts as $post ) {
			$urls = get_post_meta( $post->ID, '_jg_image_url', false );
			$urls = is_array( $urls ) ? array_values( array_filter( $urls ) ) : array();
			if ( empty( $urls ) ) {
				continue;
			}
			$images[] = array(
				'title'       => get_the_title( $post ),
				'description' => $post->post_content,
				'images'      => $urls,
			);
		}

		if ( empty( $images ) ) {
			continue;
		}

		$groups[] = array(
			'name'   => $term->name,
			'slug'   => $term->slug,
			'images' => $images,
		);
	}

	return $groups;
}

/**
 * Walker del menú principal: le agrega al ítem "Catálogo" (el que apunta a
 * "Nuestras Soluciones") un desplegable con un link por cada categoría de
 * `ta_get_solutions_by_category()` — las mismas categorías con imágenes
 * publicadas que arma las secciones de esa página (con id="categoria-{slug}",
 * page-nuestras-soluciones.php §1.8). Al reusar esa función, el desplegable
 * queda siempre sincronizado con el plugin sin tocar el menú a mano: una
 * categoría nueva con al menos una imagen publicada aparece sola, y una que
 * se vacía o se borra desaparece sola, en la siguiente carga de página.
 */
/**
 * Walker del menú principal: le agrega al ítem "Catálogo" (el que apunta a
 * "Nuestras Soluciones") un desplegable con un link por cada categoría de
 * `ta_get_solutions_by_category()`.
 */
class TA_Nav_Walker extends Walker_Nav_Menu {
    private ?int $solutions_page_id = null;

    private function get_solutions_page_id() {
        if ( null === $this->solutions_page_id ) {
            $page                    = get_page_by_path( 'nuestras-soluciones' );
            $this->solutions_page_id = $page ? $page->ID : 0;
        }
        return $this->solutions_page_id;
    }

    // 1. Agregamos la clase 'ta-nav-has-caret' al <li> de Catálogo si tiene categorías
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        if ( 0 === $depth && 'page' === $item->object && (int) $item->object_id === $this->get_solutions_page_id() ) {
            $groups = ta_get_solutions_by_category();
            if ( ! empty( $groups ) ) {
                $item->classes[] = 'ta-nav-has-caret';
            }
        }
        parent::start_el( $output, $item, $depth, $args, $id );
    }

    // 2. Inyectamos el botón flecha y el desplegable <ul class="ta-nav-dropdown">
    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        if ( 0 === $depth && 'page' === $item->object && (int) $item->object_id === $this->get_solutions_page_id() ) {
            $groups = ta_get_solutions_by_category();

            if ( ! empty( $groups ) ) {
                $output .= '<button type="button" class="ta-nav-caret-toggle" aria-expanded="false" aria-label="Mostrar categorías de catálogo"></button>';
                $output .= '<ul class="ta-nav-dropdown">';
                foreach ( $groups as $group ) {
                    $output .= sprintf(
                        '<li><a href="%1$s#categoria-%2$s">%3$s</a></li>',
                        esc_url( get_permalink( $this->get_solutions_page_id() ) ),
                        esc_attr( $group['slug'] ),
                        esc_html( $group['name'] )
                    );
                }
                $output .= '</ul>';
            }
        }

        parent::end_el( $output, $item, $depth, $args );
    }
}
