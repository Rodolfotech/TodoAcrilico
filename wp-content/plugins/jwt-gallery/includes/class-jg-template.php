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

		// Login y panel son privados de la empresa: fuera del sitemap y con
		// noindex, para que Google no los indexe. No se bloquean en robots.txt
		// a propósito: si Google no puede entrar, tampoco ve el noindex.
		add_filter( 'wp_sitemaps_posts_query_args', array( __CLASS__, 'exclude_from_sitemap' ), 10, 2 );
		add_filter( 'wp_robots', array( __CLASS__, 'noindex_private_pages' ) );
		add_action( 'template_redirect', array( __CLASS__, 'send_noindex_header' ) );
	}

	/**
	 * IDs de las páginas privadas: las de los slugs del plugin y cualquier
	 * página que contenga los shortcodes [jwt_gallery] o [jwt_gallery_panel]
	 * (sirve aunque en producción se renombre la página).
	 *
	 * @return int[]
	 */
	public static function private_page_ids() {
		static $ids = null;
		if ( null !== $ids ) {
			return $ids;
		}
		global $wpdb;
		$ids = array();
		foreach ( self::SLUGS as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				$ids[] = (int) $page->ID;
			}
		}
		$found = $wpdb->get_col(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status <> 'inherit' AND post_content LIKE '%[jwt\\_gallery%'"
		);
		$ids = array_values( array_unique( array_merge( $ids, array_map( 'intval', (array) $found ) ) ) );
		return $ids;
	}

	private static function is_private_page() {
		$ids = self::private_page_ids();
		return $ids && is_page( $ids );
	}

	public static function exclude_from_sitemap( $args, $post_type ) {
		if ( 'page' !== $post_type ) {
			return $args;
		}
		$ids = self::private_page_ids();
		if ( $ids ) {
			$args['post__not_in'] = array_merge( isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(), $ids );
		}
		return $args;
	}

	public static function noindex_private_pages( $robots ) {
		if ( self::is_private_page() ) {
			unset( $robots['max-image-preview'] );
			$robots['noindex']   = true;
			$robots['nofollow']  = true;
			$robots['noarchive'] = true;
		}
		return $robots;
	}

	public static function send_noindex_header() {
		if ( self::is_private_page() && ! headers_sent() ) {
			header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
		}
	}

	public static function maybe_use_blank_template( $template ) {
		if ( is_page( self::SLUGS ) ) {
			return JG_PATH . 'templates/blank-page.php';
		}

		return $template;
	}
}
