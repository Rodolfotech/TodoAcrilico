<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JG_CPT {

	const TAXONOMY = 'jg_gallery_category';

	const DEFAULT_CATEGORIES = array( 'organizador', 'proteger', 'vidrios', 'exhibir', 'informar' );

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
