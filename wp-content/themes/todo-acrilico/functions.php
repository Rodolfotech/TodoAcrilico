<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TA_VERSION', '1.19.3' );

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

	// El catálogo reutiliza literalmente los estilos del paginador del panel.
	$catalog_page = ta_find_page( array( 'catalogo', 'catálogo', 'nuestras-soluciones', 'soluciones' ) );

	if ( $catalog_page && is_page( $catalog_page->ID ) && wp_style_is( 'jg-gallery', 'registered' ) ) {
		wp_enqueue_style( 'jg-gallery' );
	}

	wp_enqueue_script( 'ta-carousel', get_template_directory_uri() . '/assets/js/ta-carousel.js', array(), TA_VERSION, true );
	wp_enqueue_script( 'ta-nav', get_template_directory_uri() . '/assets/js/ta-nav.js', array(), TA_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'ta_assets' );

/**
 * Título del documento para las páginas de Catálogo y Cotizar.
 *
 * El tema usa add_theme_support( 'title-tag' ), así que WordPress imprime el
 * <title> desde wp_head() (§header.php). Para cambiarlo no se edita el header
 * sino este filtro: en las páginas con slug "catalogo" y "cotizar" se muestra
 * exactamente "TODO acrílico"; en cualquier otra página se deja el título normal.
 */
function ta_document_title( $title ) {
	$catalogo_page = ta_find_page( array( 'catalogo', 'catálogo', 'nuestras-soluciones', 'soluciones', 'solucione', 'pieza' ) );
	$cotizar_page  = ta_find_page( array( 'cotizar', 'contacto', 'contact' ) );

	$custom_pages = array_filter( array( $catalogo_page, $cotizar_page ) );

	foreach ( $custom_pages as $page ) {
		if ( is_page( $page->ID ) ) {
			return 'TODO acrílico';
		}
	}

	return $title;
}
add_filter( 'pre_get_document_title', 'ta_document_title' );

/**
 * Metadescripción para SEO via el filtro correcto de WordPress.
 */
function ta_meta_description() {
	if ( is_admin() ) {
		return;
	}

	$description = 'TODO Acrílico - Soluciones en acrílico de alta calidad. Diseño, fabricación e instalación.';

	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post && ! empty( $post->post_excerpt ) ) {
			$description = substr( wp_strip_all_tags( $post->post_excerpt ), 0, 160 );
		} elseif ( $post && ! empty( $post->post_content ) ) {
			$description = substr( wp_strip_all_tags( $post->post_content ), 0, 160 );
		}
	} elseif ( is_home() || is_front_page() ) {
		$blogdesc = get_bloginfo( 'description' );
		if ( ! empty( $blogdesc ) ) {
			$description = $blogdesc;
		}
	}

	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
}
add_action( 'wp_head', 'ta_meta_description', 1 );

/**
 * Lazy loading nativo para imágenes (mejora rendimiento).
 * Agrega loading="lazy" a todas las imágenes del contenido.
 */
function ta_lazy_load_images( $html, $id, $attachment_id, $size, $icon ) {
	if ( strpos( $html, 'loading=' ) === false ) {
		$html = str_replace( '<img ', '<img loading="lazy" ', $html );
	}
	return $html;
}
add_filter( 'wp_get_attachment_image', 'ta_lazy_load_images', 10, 5 );

/**
 * Cache headers para mejor rendimiento.
 * Configura tiempos de expiración para assets estáticos.
 */
function ta_cache_headers() {
	if ( ! is_admin() ) {
		if ( preg_match( '/\.(js|css|png|jpg|jpeg|gif|webp|svg|woff|woff2|ttf|eot)(\?.*)?$/i', $_SERVER['REQUEST_URI'] ) ) {
			header( 'Cache-Control: public, max-age=31536000' );
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 31536000 ) . ' GMT' );
		} elseif ( ! is_user_logged_in() ) {
			header( 'Cache-Control: public, max-age=3600' );
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 3600 ) . ' GMT' );
		}
	}
}
add_action( 'send_headers', 'ta_cache_headers' );

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
    $stored = get_theme_mod( 'ta_hero_image_url', '' );

    // Imagen de portada local (pedido del usuario: nada de enlaces externos).
    // El valor por defecto antiguo apuntaba a un hosting externo (i.postimg.cc);
    // si está guardado ese mismo, se ignora y se usa la local.
    $local = get_template_directory_uri() . '/assets/img/home/home.webp';

    if ( '' === $stored || 'https://i.postimg.cc/TYX3CTrK/home.webp' === $stored ) {
        return $local;
    }

    return $stored;
}
/**
 * Ícono SVG en línea (trazo, sin relleno) para las tarjetas de ta_render_feature_grid(),
 * el footer y la página de Contacto/Cuidados. Nombres disponibles: 'grid', 'eye', 'shield',
 * 'info', 'pane', 'pin', 'mail', 'clock', 'paperclip', 'thermometer', 'prohibited', 'spray',
 * 'circular', 'instagram', 'linkedin'. Cualquier otro nombre devuelve un ícono genérico
 * (círculo) para que nunca se quede algo sin ícono. `$size` (default 28) controla el
 * `width`/`height` del SVG devuelto.
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
		'instagram'  => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37Z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
		'linkedin'   => '<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6Z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>',
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
 * $class (opcional) agrega una clase al <section> (p. ej. 'ta-features--white').
 */
function ta_render_feature_grid( $eyebrow, $title, $items, $description = '', $class = '' ) {
    $section_class = 'ta-features' . ( $class ? ' ' . esc_attr( $class ) : '' );
    ?>
    <section class="<?php echo $section_class; ?>">
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
	// return apply_filters( 'ta_contact_email', 'contacto@todoacrilico.cl' );
	return apply_filters( 'ta_contact_email', 'rodolfo.parada.gonzalez@gmail.com' );
}


define( 'TA_CONTACT_MAX_FILE_SIZE', 5 * 1024 * 1024 ); // 5 MB por adjunto.
define( 'TA_CONTACT_MAX_FILES', 5 ); // Hasta 5 archivos adjuntos.
define( 'TA_CONTACT_NAME_MIN', 3 ); // Mínimo 3 caracteres en nombre.
define( 'TA_CONTACT_NAME_MAX', 100 ); // Máximo 100 caracteres en nombre.
define( 'TA_CONTACT_DESC_MIN', 20 ); // Mínimo 20 caracteres en descripción.
define( 'TA_CONTACT_RATE_LIMIT', 60 ); // Rate limit: 60 segundos entre envíos.

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
	$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : home_url( '/cotizar/' );

	$adjunto_paths = array();
	$attachments    = array();

	$fail = function ( $reason ) use ( $redirect_to, &$adjunto_paths ) {
		foreach ( $adjunto_paths as $adjunto_path ) {
			if ( $adjunto_path && file_exists( $adjunto_path ) ) {
				wp_delete_file( $adjunto_path );
			}
		}
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

	// Aceptación obligatoria de la Política de Privacidad y los Términos y
	// condiciones: sin el checkbox marcado no se envía la cotización.
	if ( empty( $_POST['acepto_terminos'] ) ) {
		$fail( 'terminos' );
	}

	$nombre      = isset( $_POST['nombre'] ) ? sanitize_text_field( wp_unslash( $_POST['nombre'] ) ) : '';
	$email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$celular     = isset( $_POST['celular'] ) ? sanitize_text_field( wp_unslash( $_POST['celular'] ) ) : '';
	$descripcion = isset( $_POST['descripcion'] ) ? sanitize_textarea_field( wp_unslash( $_POST['descripcion'] ) ) : '';
	$acepta_ofertas = empty( $_POST['acepta_ofertas'] ) ? '0' : '1';

	// Rate limiting: máximo 1 envío por IP cada TA_CONTACT_RATE_LIMIT segundos.
	$client_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( $client_ip ) {
		$rate_limit_key = 'ta_contact_rate_' . md5( $client_ip );
		$last_submit     = get_transient( $rate_limit_key );
		if ( $last_submit ) {
			$fail( 'rate_limit' );
		}
		set_transient( $rate_limit_key, time(), TA_CONTACT_RATE_LIMIT );
	}
	$adjunto_ref = isset( $_POST['adjunto_referencia'] ) ? esc_url_raw( wp_unslash( $_POST['adjunto_referencia'] ) ) : '';
	$adjunto_ref = ( $adjunto_ref && wp_http_validate_url( $adjunto_ref ) ) ? $adjunto_ref : '';
	$adjunto_tit  = isset( $_POST['adjunto_titulo'] ) ? sanitize_text_field( wp_unslash( $_POST['adjunto_titulo'] ) ) : '';
	$adjunto_tit  = $adjunto_tit ? wp_strip_all_tags( trim( $adjunto_tit ) ) : '';
	$adjunto_desc = isset( $_POST['adjunto_descripcion'] ) ? sanitize_textarea_field( wp_unslash( $_POST['adjunto_descripcion'] ) ) : '';
	$adjunto_desc = $adjunto_desc ? wp_strip_all_tags( trim( $adjunto_desc ) ) : '';
	$adjunto_med  = isset( $_POST['adjunto_medidas'] ) ? sanitize_text_field( wp_unslash( $_POST['adjunto_medidas'] ) ) : '';
	$adjunto_med  = $adjunto_med ? trim( $adjunto_med ) : '';
	$adjunto_uso  = isset( $_POST['adjunto_uso'] ) ? sanitize_text_field( wp_unslash( $_POST['adjunto_uso'] ) ) : '';
	$adjunto_uso  = $adjunto_uso ? trim( $adjunto_uso ) : '';

	if ( '' === $nombre || '' === $descripcion ) {
		$fail( 'campos' );
	}

	$nombre_length = strlen( $nombre );
	if ( $nombre_length < TA_CONTACT_NAME_MIN ) {
		$fail( 'nombre_corto' );
	}
	if ( $nombre_length > TA_CONTACT_NAME_MAX ) {
		$fail( 'nombre_largo' );
	}

	// Anti-spam: rechazar nombres que contienen números.
	if ( preg_match( '/\d/', $nombre ) ) {
		$fail( 'nombre_numerico' );
	}

	$desc_length = strlen( $descripcion );
	if ( $desc_length < TA_CONTACT_DESC_MIN ) {
		$fail( 'desc_corta' );
	}

	if ( '' === $email ) {
		$fail( 'email_vacio' );
	}

	if ( ! is_email( $email ) ) {
		$fail( 'email_invalido' );
	}

	// Validación mejorada de email: rechazar si empieza con caracteres especiales.
	if ( preg_match( '/^[^a-z0-9]/i', $email ) ) {
		$fail( 'email_caracteres' );
	}

	// Validación: email no puede tener patrón inválido como ".@" o ".fadf@".
	if ( preg_match( '/^\./i', $email ) || strpos( $email, '@.' ) !== false ) {
		$fail( 'email_patron' );
	}

	// El dominio del correo debe estar dentro de los permitidos.
	// Solo: dominios específicos (gmail, outlook, hotmail, etc.) + dominios .cl
	$email_domain = strtolower( substr( strrchr( $email, '@' ), 1 ) );
	$allowed_domains = array( 'gmail.com', 'outlook.com', 'hotmail.com', 'live.cl', 'yahoo.com', 'icloud.com' );
	$has_allowed_domain = in_array( $email_domain, $allowed_domains, true ) || preg_match( '/\.cl$/', $email_domain );
	if ( ! $has_allowed_domain ) {
		$fail( 'email_dominio' );
	}

	// Celular chileno obligatorio: ocho dígitos móviles tras el prefijo +56 9.
	// El campo del formulario entrega 8 dígitos (sin el 9, que va en el prefijo);
	// por compatibilidad se tolera también el formato con 9 inicial.
	$celular_digits = preg_replace( '/[^\d]/', '', $celular );
	$celular_digits = preg_replace( '/^56/', '', $celular_digits );
	if ( preg_match( '/^9\d{8}$/', $celular_digits ) ) {
		$celular_digits = substr( $celular_digits, 1 );
	}
	if ( ! preg_match( '/^[2-9]\d{7}$/', $celular_digits ) ) {
		$fail( 'celular' );
	}
	$celular = '+56 9 ' . $celular_digits;

	// Adjuntos opcionales (varias imágenes o PDFs). Con el input `multiple`, PHP
	// recibe $_FILES['adjunto'] como arrays. Se normaliza a una lista de archivos
	// y se valida cada uno (extensión + contenido real: getimagesize para
	// imágenes, MIME %PDF para los PDF).
	$submit_files = array();
	if ( isset( $_FILES['adjunto'] ) && is_array( $_FILES['adjunto']['name'] ) ) {
		for ( $i = 0, $n = count( $_FILES['adjunto']['name'] ); $i < $n; $i++ ) {
			if ( UPLOAD_ERR_NO_FILE === $_FILES['adjunto']['error'][ $i ] ) {
				continue;
			}
			$submit_files[] = array(
				'name'  => $_FILES['adjunto']['name'][ $i ],
				'tmp'   => $_FILES['adjunto']['tmp_name'][ $i ],
				'error' => $_FILES['adjunto']['error'][ $i ],
				'size'  => $_FILES['adjunto']['size'][ $i ],
			);
		}
	}

	if ( count( $submit_files ) > TA_CONTACT_MAX_FILES ) {
		$fail( 'archivos_muchos' );
	}

	$allowed_ext = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf' );

	foreach ( $submit_files as $f ) {
		if ( UPLOAD_ERR_OK !== $f['error'] ) {
			$fail( 'archivo' );
		}

		if ( $f['size'] > TA_CONTACT_MAX_FILE_SIZE ) {
			$fail( 'archivo_grande' );
		}

		$ext = strtolower( pathinfo( sanitize_file_name( $f['name'] ), PATHINFO_EXTENSION ) );

		if ( ! in_array( $ext, $allowed_ext, true ) ) {
			$fail( 'archivo_tipo' );
		}

		// Contenido real: una imagen con getimagesize; un PDF por su cabecera %PDF.
		if ( 'pdf' === $ext ) {
			$mime = function_exists( 'mime_content_type' ) ? mime_content_type( $f['tmp'] ) : '';
			if ( stripos( (string) $mime, 'application/pdf' ) === false ) {
				$fail( 'archivo_tipo' );
			}
		} elseif ( ! @getimagesize( $f['tmp'] ) ) {
			$fail( 'archivo_tipo' );
		}

		$upload_dir = wp_upload_dir();
		$target_dir = trailingslashit( $upload_dir['basedir'] ) . 'ta-contacto-tmp/';
		if ( ! file_exists( $target_dir ) ) {
			wp_mkdir_p( $target_dir );
		}

		$adjunto_path = $target_dir . wp_unique_filename( $target_dir, sanitize_file_name( $f['name'] ) );

		// move_uploaded_file() verifica por su cuenta que el archivo venga de una
		// subida HTTP real — no acepta rutas arbitrarias aunque alguien falsifique $_FILES.
		if ( ! move_uploaded_file( $f['tmp'], $adjunto_path ) ) {
			$fail( 'archivo' );
		}

		$attachments[]  = $adjunto_path;
		$adjunto_paths[] = $adjunto_path;
	}

	$subject = sprintf( 'Nuevo mensaje de contacto — %s', $nombre );

	// Cuerpo HTML con el estilo de email definido (tablas de 600px, tonos
	// neutros). Todos los valores dinámicos van escapados (esc_html / esc_url /
	// esc_attr); la imagen de la pieza a cotizar se incrusta por su URL pública
	// ya validada con wp_http_validate_url() — más seguro y compatible con
	// webmail que un CID, y sin datos personales incrustados.
	$html_body = '<!DOCTYPE html>'
		. '<html lang="es"><head><meta charset="UTF-8">'
		. '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
		. '<title>Nuevo mensaje de contacto</title></head>'
		. '<body style="margin: 0; padding: 20px; background-color: #f4f6f8; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; color: #333333;">'
		// Contenedor principal.
		. '<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">'
		// Encabezado / título.
		. '<tr><td style="padding: 24px 24px 16px 24px;">'
		. '<h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #0f172a;">Nuevo mensaje de contacto — <span style="font-weight: 400;">' . esc_html( $nombre ) . '</span></h2>'
		. '</td></tr>'
		// DATOS DEL CLIENTE.
		. '<tr><td style="padding: 0 24px 20px 24px;">'
		. '<p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 0.5px; text-transform: uppercase;">DATOS DEL CLIENTE</p>'
		. '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; border-top: 1px solid #e2e8f0;">'
		. '<tr style="border-bottom: 1px solid #f1f5f9;"><td width="28%" style="padding: 10px 0; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase;">NOMBRE</td>'
		. '<td style="padding: 10px 0; font-size: 14px; color: #1e293b;">' . esc_html( $nombre ) . '</td></tr>'
		. '<tr style="border-bottom: 1px solid #f1f5f9;"><td width="28%" style="padding: 10px 0; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase;">CELULAR</td>'
		. '<td style="padding: 10px 0; font-size: 14px; color: #1e293b;">' . esc_html( $celular ) . '</td></tr>'
		. '<tr style="border-bottom: 1px solid #f1f5f9;"><td width="28%" style="padding: 10px 0; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase;">CORREO</td>'
		. '<td style="padding: 10px 0; font-size: 14px; color: #1e293b;">' . esc_html( $email ) . '</td></tr>'
		. '</table>'
		// Consentimiento: la línea siempre se muestra, con check (aceptó) o cruz
		// (no aceptó). Verde si aceptó, gris neutro si no.
		. '<p style="margin: 8px 0 0 0; font-size: 12px; color: ' . ( '1' === $acepta_ofertas ? '#15903e' : '#64748b' ) . ';">'
		. ( '1' === $acepta_ofertas ? '✓ Aceptó recibir ofertas y comunicaciones comerciales.' : '✗ No aceptó recibir ofertas y comunicaciones comerciales.' )
		. '</p>'
		. '</td></tr>'
		// DESCRIPCIÓN DEL PROYECTO.
		. '<tr><td style="padding: 0 24px 24px 24px;">'
		. '<p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 0.5px; text-transform: uppercase;">DESCRIPCIÓN DEL PROYECTO</p>'
		. '<div style="font-size: 14px; line-height: 1.5; color: #334155;">' . nl2br( esc_html( $descripcion ) ) . '</div>'
		. '</td></tr>';

	// Información de la pieza a cotizar, pasada automáticamente al hacer clic en
	// "Cotizar esta pieza" en el catálogo (page-nuestras-soluciones.php).
	if ( $adjunto_ref || $adjunto_tit || $adjunto_desc || $adjunto_med ) {
		$html_body .= '<tr><td style="padding: 0 24px 24px 24px;">'
			. '<p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 0.5px; text-transform: uppercase;">PIEZA A COTIZAR</p>';
		if ( $adjunto_tit ) {
			$html_body .= '<p style="margin: 0 0 4px; font-size: 14px; color: #334155;"><strong style="color:#0f172a;">Título:</strong> ' . esc_html( $adjunto_tit ) . '</p>';
		}
		if ( $adjunto_uso ) {
			$html_body .= '<p style="margin: 0 0 4px; font-size: 14px; color: #334155;"><strong style="color:#0f172a;">Uso:</strong> ' . esc_html( $adjunto_uso ) . '</p>';
		}
		if ( $adjunto_med ) {
			$html_body .= '<p style="margin: 0 0 4px; font-size: 14px; color: #334155;"><strong style="color:#0f172a;">Medidas:</strong> ' . esc_html( $adjunto_med ) . '</p>';
		}
		if ( $adjunto_desc ) {
			$html_body .= '<p style="margin: 0 0 4px; font-size: 14px; color: #334155;"><strong style="color:#0f172a;">Descripción:</strong> ' . nl2br( esc_html( $adjunto_desc ) ) . '</p>';
		}
		if ( $adjunto_ref ) {
			$html_body .= '<div style="margin-top: 8px; border-radius: 6px; overflow: hidden; border: 1px solid #e2e8f0; background-color: #000000; text-align: center;">'
				. '<img src="' . esc_url( $adjunto_ref ) . '" alt="' . esc_attr( $adjunto_tit ? $adjunto_tit : 'Imagen de la pieza a cotizar' ) . '" style="display: block; width: 100%; max-width: 100%; height: auto;">'
				. '<div style="padding: 8px 12px; background-color: #ffffff; text-align: left; font-size: 12px; color: #475569; border-top: 1px solid #e2e8f0;">' . esc_html( $adjunto_tit ? $adjunto_tit : 'Imagen de la pieza a cotizar' ) . '</div>'
				. '</div>'
				. '<p style="margin: 8px 0 0; font-size: 12px; color: #64748b;">Ver imagen: <a href="' . esc_url( $adjunto_ref ) . '" style="color: #15903e;">' . esc_html( $adjunto_ref ) . '</a></p>';
		}
		$html_body .= '</td></tr>';
	}

	// Archivos adjuntos del formulario: resumen estilizado (los archivos reales
	// viajan adjuntos al correo con wp_mail(); no se incrustan en línea).
	if ( $adjunto_paths ) {
		$html_body .= '<tr><td style="padding: 16px 24px; border-top: 1px solid #e2e8f0; background-color: #fafbfc;">'
			. '<p style="margin: 0; font-size: 12px; color: #64748b;">📎 <strong>' . count( $adjunto_paths ) . ( count( $adjunto_paths ) === 1 ? ' archivo adjunto' : ' archivos adjuntos' ) . '</strong> &nbsp;·&nbsp; Analizados automáticamente</p>'
			. '<p style="margin: 6px 0 0; font-size: 12px; color: #475569;">' . esc_html( implode( ' · ', array_map( 'basename', $adjunto_paths ) ) ) . '</p>'
			. '</td></tr>';
	}

	// Cierre del contenedor.
	$html_body .= '</table></body></html>';

	// Sin "From" propio a propósito: dejarlo así es lo que hace que un plugin SMTP
	// (WP Mail SMTP, etc.) controle el remitente sin que este código le compita.
	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'Reply-To: ' . $nombre . ' <' . $email . '>',
	);

	$sent = wp_mail( ta_get_contact_email(), $subject, $html_body, $headers, $attachments );

	foreach ( $adjunto_paths as $adjunto_path ) {
		if ( $adjunto_path && file_exists( $adjunto_path ) ) {
			wp_delete_file( $adjunto_path );
		}
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
 * Pestañas reutilizables para filtrar contenido por URL. Cada pestaña debe incluir
 * `label` y `value`; `value` se envía en el parámetro indicado y reinicia la página.
 *
 * @param array<int, array{label: string, value: string}> $tabs
 * @param string                                        $active_value
 * @param string                                        $query_arg
 */
function ta_render_filter_tabs( $tabs, $active_value, $query_arg = 'categoria' ) {
	if ( empty( $tabs ) ) {
		return;
	}

	?>
	<nav class="ta-filter-tabs" aria-label="Filtrar catálogo por categoría">
		<?php foreach ( $tabs as $tab ) : ?>
			<?php
			$url = add_query_arg(
				array_filter(
					array(
						$query_arg => $tab['value'] ?: null,
						'pagina'   => null,
					)
				),
				get_permalink()
			);
			$is_active = $active_value === $tab['value'];
			?>
			<a class="ta-filter-tabs__tab<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $tab['label'] ); ?></a>
		<?php endforeach; ?>
	</nav>
	<?php
}

/**
 * Renderiza el mismo patrón de páginas que el panel JWT Gallery: todas hasta
 * siete; después primera, página anterior/siguiente, última y elipsis.
 *
 * @return array<int, int|string>
 */
function ta_get_pagination_pages( $current, $total ) {
	$current = max( 1, (int) $current );
	$total   = max( 1, (int) $total );

	if ( $total <= 7 ) {
		return range( 1, $total );
	}

	$pages = array( 1 );
	$start = max( 2, $current - 1 );
	$end   = min( $total - 1, $current + 1 );

	if ( $start > 2 ) {
		$pages[] = '…';
	}
	for ( $number = $start; $number <= $end; $number++ ) {
		$pages[] = $number;
	}
	if ( $end < $total - 1 ) {
		$pages[] = '…';
	}
	$pages[] = $total;

	return $pages;
}

/**
 * Normaliza un texto para compararlo sin depender de mayúsculas ni
 * acentos: "Cuidados del Acrílico" == "cuidados del acrilico".
 *
 * @param string $text
 * @return string
 */
function ta_normalize_text( $text ) {
	return strtolower(
		strtr(
			(string) $text,
			array(
				'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
				'ñ' => 'n', 'ü' => 'u',
				'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
				'Ñ' => 'n', 'Ü' => 'u',
			)
		)
	);
}

/**
 * Resuelve una página del sitio sin depender de que conserve el slug "de
 * apariencia". Tras una migración (p. ej. Softaculous) las páginas suelen
 * conservar su título pero cambiar de slug ("Nuestras soluciones" → services),
 * lo que dejaba al tema sin enlaces, sin desplegable ni sin plantillas.
 * Estrategia:
 *
 *  1. Coincidencia exacta por slug canónico (`get_page_by_path`).
 *  2. Entre las páginas publicadas, la primera cuyo título o slug contenga
 *     (sin mayúsculas ni acentos) alguna de las palabras clave dadas.
 *
 * Primero se prueban todos los slugs canónicos y después, por cada palabra
 * clave, las páginas ordenadas por título. Devuelve `null` si no coincide nada.
 *
 * @param string|string[] $needles Slug(s) canónico(s) y/o palabra(s) clave de título.
 * @return WP_Post|null
 */
function ta_find_page( $needles ) {
	$needles = (array) $needles;

	foreach ( $needles as $slug ) {
		$page = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $page && 'page' === $page->post_type ) {
			return $page;
		}
	}

	$haystacks = array();
	foreach ( get_pages( array( 'post_status' => 'publish', 'sort_column' => 'post_title' ) ) as $page ) {
		$haystacks[] = (object) array(
			'page' => $page,
			'key'  => ta_normalize_text( $page->post_title . ' ' . $page->post_name ),
		);
	}

	foreach ( $needles as $needle ) {
		$needle = ta_normalize_text( $needle );
		if ( '' === $needle ) {
			continue;
		}
		foreach ( $haystacks as $haystack ) {
			if ( false !== strpos( $haystack->key, $needle ) ) {
				return $haystack->page;
			}
		}
	}

	return null;
}

/**
 * Asigna la plantilla dedicada de cada página (Catálogo, Cuidados, Contacto)
 * aunque su slug no sea el "de apariencia". WordPress elige plantilla con la
 * jerarquía `page-{slug}.php` antes que `page.php`, así que tras una migración
 * con slugs distintos ("Nuestras soluciones" → services, "Contacto" → contact)
 * esas páginas caían en la plantilla genérica y se veían como una entrada de
 * blog vacía en vez del diseño real (pestañas del catálogo, formulario, íconos).
 *
 * Este filtro solo interviene cuando WordPress ya resolvió `page.php` — si el
 * administrador asignó una plantilla a mano, esa gana. La página se identifica
 * con `ta_find_page()` (misma resolución del nav, §4.101), no por su slug.
 *
 * Excepción (versión 1.18.65): también reemplaza las plantillas en blanco de
 * Elementor ("Elementor Canvas" / "Elementor Header Footer"). Estas no son un
 * diseño elegido por el usuario para el sitio, sino el valor por defecto que
 * deja el constructor cuando edita una página, y bloqueaban la plantilla del
 * tema en /cuidados/. Se puede desactivar con el filtro
 * `ta_override_elementor_templates` (devolver `false`) si alguna vez se quiere
 * respetar la de Elementor.
 */
function ta_page_template_by_role( $template ) {
	if ( ! is_page() || is_front_page() ) {
		return $template;
	}

	$is_generic   = 'page.php' === basename( $template );
	$is_elementor = false !== strpos( $template, '/plugins/elementor/' );

	// Cualquier otra plantilla (las del propio tema, las blank-page.php del
	// plugin de galería, una elegida a mano) se respeta.
	if ( ! $is_generic && ! ( $is_elementor && apply_filters( 'ta_override_elementor_templates', true ) ) ) {
		return $template;
	}

	$current = get_queried_object_id();

	$map = array(
		array( array( 'catalogo', 'catálogo', 'nuestras-soluciones', 'soluciones', 'solucione', 'pieza' ), 'page-nuestras-soluciones.php' ),
		array( array( 'cuidados', 'cuidado', 'cuidados-del-acrilico' ), 'page-cuidados-del-acrilico.php' ),
		array( array( 'cotizar', 'contacto', 'contact' ), 'page-contacto.php' ),
	);

	foreach ( $map as $entry ) {
		$page = ta_find_page( $entry[0] );

		if ( $page && (int) $page->ID === (int) $current ) {
			return get_template_directory() . '/' . $entry[1];
		}
	}

	return $template;
}
add_filter( 'template_include', 'ta_page_template_by_role', 20 );

/**
 * Menú de reserva del header: si en Apariencia → Menús no hay ningún menú
 * asignado a la ubicación "Menú principal" (lo típico después de una
 * migración, porque `nav_menu_locations` no siempre se copia), `wp_nav_menu()`
 * no dibujaría absolutamente nada. Con este fallback el nav muestra SIEMPRE
 * los cuatro destinos del sitio — Inicio, Catálogo, Cuidados y Cotizar — con
 * las mismas clases que usa el menú real, incluido el desplegable de
 * categorías en "Catálogo" (misma lógica que `TA_Nav_Walker`). Las páginas se
 * resuelven con `ta_find_page()`: da igual cuál sea su slug, basta con que el
 * título de la página siga mencionando soluciones / cuidados / contacto. Y si
 * ni siquiera así se encuentra, el ítem se muestra igual apuntando a la
 * portada — un menú con solo Inicio no debería ocurrir.
 *
 * @param array $args Argumentos que entrega `wp_nav_menu()`.
 */
function ta_nav_menu_fallback( $args ) {
	$menu_class = isset( $args['menu_class'] ) ? $args['menu_class'] : 'ta-nav-list';

	$catalog_page = ta_find_page( array( 'catalogo', 'catálogo', 'nuestras-soluciones', 'soluciones', 'solucione', 'pieza' ) );
	$care_page    = ta_find_page( array( 'cuidados', 'cuidado', 'cuidados-del-acrilico' ) );
	$contact_page = ta_find_page( array( 'cotizar', 'contacto', 'contact' ) );

	// Los cuatro destinos se muestran SIEMPRE. Si la página no se encuentra
	// (título y slug muy distintos tras una migración), el ítem apunta a la
	// portada en vez de desaparecer.
	$items = array(
		array(
			'label'   => esc_html__( 'Inicio', 'todo-acrilico' ),
			'url'     => home_url( '/' ),
			'current' => is_front_page(),
		),
		array(
			'label'   => esc_html__( 'Catálogo', 'todo-acrilico' ),
			'url'     => $catalog_page ? get_permalink( $catalog_page ) : home_url( '/' ),
			'current' => $catalog_page ? is_page( $catalog_page->ID ) : false,
			'caret'   => true,
		),
		array(
			'label'   => esc_html__( 'Cuidados', 'todo-acrilico' ),
			'url'     => $care_page ? get_permalink( $care_page ) : home_url( '/' ),
			'current' => $care_page ? is_page( $care_page->ID ) : false,
		),
		array(
			'label'   => esc_html__( 'Cotizar', 'todo-acrilico' ),
			'url'     => $contact_page ? get_permalink( $contact_page ) : home_url( '/' ),
			'current' => $contact_page ? is_page( $contact_page->ID ) : false,
			'cta'     => true,
		),
	);

	$html = '<ul class="' . esc_attr( $menu_class ) . '">';

	foreach ( $items as $item ) {
		$classes = array( 'menu-item', 'menu-item-type-post_type', 'menu-item-object-page' );

		if ( ! empty( $item['current'] ) ) {
			$classes[] = 'current-menu-item';
			$classes[] = 'current_page_item';
		}

		$groups = array();

		if ( ! empty( $item['caret'] ) ) {
			$groups = ta_get_solutions_by_category();

			if ( ! empty( $groups ) ) {
				$classes[] = 'ta-nav-has-caret';
			}
		}

		if ( ! empty( $item['cta'] ) ) {
			$classes[] = 'ta-nav-cta';
		}

		$html .= '<li class="' . esc_attr( implode( ' ', $classes ) ) . '">';
		$html .= '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';

		if ( ! empty( $groups ) ) {
			$html .= '<button type="button" class="ta-nav-caret-toggle" aria-expanded="false" aria-label="' . esc_attr__( 'Mostrar categorías de catálogo', 'todo-acrilico' ) . '"></button>';
			$html .= '<ul class="ta-nav-dropdown">';
			$html .= '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html__( 'Todas las piezas', 'todo-acrilico' ) . '</a></li>';

			foreach ( $groups as $group ) {
				$html .= '<li><a href="' . esc_url( add_query_arg( 'categoria', $group['slug'], $item['url'] ) ) . '">' . esc_html( $group['name'] ) . '</a></li>';
			}

			$html .= '</ul>';
		}

		$html .= '</li>';
	}

	$html .= '</ul>';

	echo $html;
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
    private $solutions_page_id = null;

    private function get_solutions_page_id() {
        if ( null === $this->solutions_page_id ) {
            $page                    = ta_find_page( array( 'catalogo', 'catálogo', 'nuestras-soluciones', 'soluciones', 'solucione', 'pieza' ) );
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
                $catalog_url = get_permalink( $this->get_solutions_page_id() );

                $output .= '<button type="button" class="ta-nav-caret-toggle" aria-expanded="false" aria-label="Mostrar categorías de catálogo"></button>';
                $output .= '<ul class="ta-nav-dropdown">';
                $output .= sprintf(
                    '<li><a href="%1$s">%2$s</a></li>',
                    esc_url( $catalog_url ),
                    esc_html__( 'Todas las piezas', 'todo-acrilico' )
                );
                foreach ( $groups as $group ) {
                    $output .= sprintf(
                        '<li><a href="%1$s">%2$s</a></li>',
                        esc_url( add_query_arg( 'categoria', $group['slug'], $catalog_url ) ),
                        esc_html( $group['name'] )
                    );
                }
                $output .= '</ul>';
            }
        }

        parent::end_el( $output, $item, $depth, $args );
    }
}
