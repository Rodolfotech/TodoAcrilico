<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ta_footer_solutions = get_page_by_path( 'nuestras-soluciones' );
$ta_footer_cuidados  = get_page_by_path( 'cuidados-del-acrilico' );
$ta_footer_contacto  = get_page_by_path( 'contacto' );
$ta_footer_terminos  = get_page_by_path( 'terminos-y-condiciones' );
$ta_footer_privacidad = get_page_by_path( 'politica-de-privacidad' );
?>
</main>

<footer class="ta-footer">
	<div class="ta-footer-top">
		<div class="ta-footer-col ta-footer-brand">
			<img
				src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/logo.webp' ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				class="ta-footer-logo"
				width="90"
				height="89"
			>
			<p>Más de 5 años diseñando y fabricando soluciones en acrílico a medida en Santiago, Chile.</p>
		</div>

		<div class="ta-footer-col">
			<h3 class="ta-footer-heading">Explorar</h3>
			<ul class="ta-footer-links">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a></li>
				<li><a href="<?php echo esc_url( $ta_footer_solutions ? get_permalink( $ta_footer_solutions ) : home_url( '/' ) ); ?>">Catálogo</a></li>
				<li><a href="<?php echo esc_url( $ta_footer_cuidados ? get_permalink( $ta_footer_cuidados ) : home_url( '/' ) ); ?>">Cuidados</a></li>
				<li><a href="<?php echo esc_url( $ta_footer_contacto ? get_permalink( $ta_footer_contacto ) : home_url( '/' ) ); ?>">Contacto</a></li>
			</ul>
		</div>

		<div class="ta-footer-col">
			<h3 class="ta-footer-heading">Contacto</h3>
			<ul class="ta-footer-contact-list">
				<li>
					<span class="ta-footer-icon" aria-hidden="true"><?php echo ta_icon( 'pin', 18 ); ?></span>
					Santiago, Chile
				</li>
				<li>
					<span class="ta-footer-icon" aria-hidden="true"><?php echo ta_icon( 'mail', 18 ); ?></span>
					<a href="mailto:contacto@todoacrilico.cl">contacto@todoacrilico.cl</a>
				</li>
				<li>
					<span class="ta-footer-icon" aria-hidden="true"><?php echo ta_icon( 'clock', 18 ); ?></span>
					Lun a Vie · 9:00 — 18:00
				</li>
			</ul>
		</div>

		<div class="ta-footer-col">
			<h3 class="ta-footer-heading">Síguenos</h3>
			<ul class="ta-footer-social">
				<li>
					<a href="https://www.instagram.com/todoacrilico_cl/" target="_blank" rel="noopener noreferrer" aria-label="Instagram de Todo Acrílico">
						<span class="ta-footer-icon" aria-hidden="true"><?php echo ta_icon( 'instagram', 20 ); ?></span>
					</a>
				</li>
				<li>
					<a href="https://www.linkedin.com/company/todo-acrílico/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn de Todo Acrílico">
						<span class="ta-footer-icon" aria-hidden="true"><?php echo ta_icon( 'linkedin', 20 ); ?></span>
					</a>
				</li>
			</ul>
		</div>
	</div>

	<div class="ta-footer-bottom">
		<p class="ta-footer-copy">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> TODO Acrílico SpA</p>
		<div class="ta-footer-legal">
			<a href="<?php echo esc_url( $ta_footer_privacidad ? get_permalink( $ta_footer_privacidad ) : home_url( '/' ) ); ?>">Política de privacidad</a>
			<a href="<?php echo esc_url( $ta_footer_terminos ? get_permalink( $ta_footer_terminos ) : home_url( '/' ) ); ?>">Términos y condiciones</a>
			<span>Diseñado con foco en usabilidad</span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
