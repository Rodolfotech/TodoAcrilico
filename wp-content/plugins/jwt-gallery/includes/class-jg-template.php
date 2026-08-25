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
