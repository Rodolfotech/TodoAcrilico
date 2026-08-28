<?php
/**
 * Plugin Name:       TodoAcrílico-Panel
 * Description:       Login por JWT (correo + contraseña) y galería de imágenes por categorías, con edición y borrado. Shortcode [jwt_gallery] para el login y [jwt_gallery_panel] para el panel autenticado.
 * Version:           1.3.4
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            dev@reparadores.cl
 * License:           GPL-2.0-or-later
 * Text Domain:       jwt-gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JG_VERSION', '1.3.4' );
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

/**
 * Seguridad: deshabilitar XML-RPC.
 *
 * xmlrpc.php permite amplificación de brute-force vía system.multicall
 * (cientos de intentos de contraseña en una sola petición). El plugin no
 * usa la app móvil ni pingbacks, así que se deshabilita por completo.
 *
 * - El filtro devuelve false → WP responde 403 a toda petición a xmlrpc.php.
 * - pingback.ping se deshabilita al desactivar XML-RPC por completo.
 * - El REST API (wp-json) y los endpoints del plugin no se ven afectados.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );

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
