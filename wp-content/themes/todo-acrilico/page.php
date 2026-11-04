<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="ta-page-content<?php echo ta_is_legal_page() ? ' ta-page-content--wide' : ''; ?>">
		<h1><?php the_title(); ?></h1>
		<div class="entry-content">
			<?php
			if ( ta_is_legal_page() ) {
				echo ta_render_legal_accordion( apply_filters( 'the_content', get_the_content() ) ); // phpcs:ignore WordPress.Security.EscapeOutput
				// Política de cookies: aquí el visitante acepta, rechaza o retira su
				// consentimiento (reemplaza al antiguo botón del footer). No quitar:
				// es el medio de revocación que exige la Ley 21.719 (CLAUDE.md, regla 12).
				if ( ta_is_cookies_page() && ta_analytics_enabled() ) {
					?>
					<div class="ta-cookie-prefs">
						<p>Puede aceptar, rechazar o retirar en cualquier momento su consentimiento para las cookies de analítica.</p>
						<button type="button" class="ta-button ta-button--dark" data-ta-consent-open>Cambiar preferencias de cookies</button>
					</div>
					<?php
				}
			} else {
				the_content();
			}
			?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
