<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JG_CPT {

	const TAXONOMY = 'jg_gallery_category';

	const DEFAULT_CATEGORIES = array( 'organizador', 'proteger', 'trabajos-especiales', 'exhibir', 'informar' );

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'init', array( __CLASS__, 'migrate_legacy_slugs' ), 20 );
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

	/**
	 * Slugs antiguos que deben pasar a su slug actual. Producción tenía la
	 * categoría "Trabajos Especiales" con el slug `vidrios` (se renombró desde
	 * el panel, que solo cambia el nombre), así que sus URL decían
	 * ?categoria=vidrios. Se corrige UNA vez: mismo término (mismo ID), las
	 * piezas no cambian. El tema redirige con 301 la URL antigua.
	 */
	const LEGACY_SLUGS = array( 'vidrios' => 'trabajos-especiales' );

	public static function migrate_legacy_slugs() {
		$done    = (array) get_option( 'jg_slug_migrations', array() );
		$pending = array_diff_key( self::LEGACY_SLUGS, $done );
		if ( ! $pending ) {
			return; // Caso normal: una lectura de opción autocargada y nada más.
		}
		foreach ( $pending as $old => $new ) {
			$term = get_term_by( 'slug', $old, self::TAXONOMY );
			if ( $term && ! get_term_by( 'slug', $new, self::TAXONOMY ) ) {
				$result = wp_update_term( $term->term_id, self::TAXONOMY, array( 'slug' => $new ) );
				if ( is_wp_error( $result ) ) {
					continue; // Se reintenta en la próxima carga.
				}
			}
			$done[ $old ] = current_time( 'mysql' );
		}
		update_option( 'jg_slug_migrations', $done, true );
	}

	public static function seed_default_categories() {
		foreach ( self::DEFAULT_CATEGORIES as $name ) {
			if ( ! term_exists( $name, self::TAXONOMY ) ) {
				wp_insert_term( $name, self::TAXONOMY );
			}
		}
	}
}
