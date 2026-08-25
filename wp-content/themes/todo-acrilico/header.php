<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="ta-header">
	<div class="ta-header-inner">
		<a class="ta-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img
				src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo.webp' ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				class="ta-logo-img"
				width="90"
				height="89"
			>
		</a>
		<nav class="ta-nav" aria-label="Menú principal">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'ta-nav-list',
					'fallback_cb'    => false,
					'walker'         => new TA_Nav_Walker(),
				)
			);
			?>
		</nav>
	</div>
</header>

<main class="ta-main">
