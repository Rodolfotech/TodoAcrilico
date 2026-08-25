<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$contacto_page = get_page_by_path( 'contacto' );
$contacto_url  = $contacto_page ? get_permalink( $contacto_page ) : home_url( '/contacto/' );

$ta_contact_status = isset( $_GET['ta_contact'] ) ? sanitize_key( $_GET['ta_contact'] ) : '';
$ta_contact_reason = isset( $_GET['reason'] ) ? sanitize_key( $_GET['reason'] ) : '';

$ta_contact_error_messages = array(
	'campos'         => 'Completa tu nombre y la descripción del proyecto.',
	'email'          => 'Ingresa un correo electrónico válido.',
	'archivo'        => 'No se pudo procesar el archivo adjunto. Intenta de nuevo.',
	'archivo_grande' => 'El archivo adjunto no puede superar los 5 MB.',
	'archivo_tipo'   => 'El archivo adjunto debe ser una imagen (JPG, PNG, GIF o WEBP).',
	'nonce'          => 'Tu sesión expiró, vuelve a intentarlo.',
	'envio'          => 'No se pudo enviar el mensaje. Intenta de nuevo más tarde.',
);
?>

<section class="ta-contact">
	<div class="ta-container ta-contact-inner">

		<div class="ta-contact-info">
			<p class="ta-contact-eyebrow">Contacto</p>
			<h1 class="ta-contact-title">Cuéntanos tu proyecto</h1>
			<span class="ta-contact-divider" aria-hidden="true"></span>
			<p class="ta-contact-description">Escríbenos con la mayor cantidad de detalles: medidas, uso y contexto. Si tienes bocetos o imágenes, adjúntalas para cotizar más rápido.</p>

			<ul class="ta-contact-list">
				<li>
					<span class="ta-contact-icon" aria-hidden="true"><?php echo ta_icon( 'mail', 20 ); ?></span>
					<div>
						<p class="ta-contact-label">Email Corporativo</p>
						<p class="ta-contact-value"><?php echo esc_html( ta_get_contact_email() ); ?></p>
					</div>
				</li>
				<li>
					<span class="ta-contact-icon" aria-hidden="true"><?php echo ta_icon( 'pin', 20 ); ?></span>
					<div>
						<p class="ta-contact-label">Ubicación</p>
						<p class="ta-contact-value">Santiago, Chile</p>
						<p class="ta-contact-value-sub">Av. Las Condes 1234, Oficina 502</p>
					</div>
				</li>
				<li>
					<span class="ta-contact-icon" aria-hidden="true"><?php echo ta_icon( 'clock', 20 ); ?></span>
					<div>
						<p class="ta-contact-label">Horario de Atención</p>
						<p class="ta-contact-value">Lunes a Viernes</p>
						<p class="ta-contact-value-sub">09:00 — 18:30 hrs</p>
					</div>
				</li>
			</ul>
		</div>

		<div class="ta-contact-form-card">
			<?php if ( 'success' === $ta_contact_status ) : ?>
				<p class="ta-contact-alert ta-contact-alert--success">¡Gracias! Recibimos tu mensaje y te responderemos entre 24 y 48 horas hábiles.</p>
			<?php elseif ( 'error' === $ta_contact_status ) : ?>
				<p class="ta-contact-alert ta-contact-alert--error"><?php echo esc_html( $ta_contact_error_messages[ $ta_contact_reason ] ?? 'Ocurrió un error. Intenta de nuevo.' ); ?></p>
			<?php endif; ?>

			<form class="ta-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
				<input type="hidden" name="action" value="ta_contact_submit">
				<input type="hidden" name="redirect_to" value="<?php echo esc_url( $contacto_url ); ?>">
				<?php wp_nonce_field( 'ta_contact_submit', 'ta_contact_nonce' ); ?>
				<p class="ta-contact-honeypot" aria-hidden="true">
					<label>
						Sitio web (dejar vacío)
						<input type="text" name="ta_contact_website" tabindex="-1" autocomplete="off">
					</label>
				</p>
				<div class="ta-contact-form-row">
					<label>
						Nombre Completo
						<input type="text" name="nombre" placeholder="Ej. Juan Pérez" required>
					</label>
					<label>
						Correo Electrónico
						<input type="email" name="email" placeholder="juan@ejemplo.cl" required>
					</label>
				</div>
				<label>
					Descripción del proyecto
					<textarea name="descripcion" rows="5" placeholder="Cuéntanos detalles de medidas, materiales y plazos…" required></textarea>
				</label>
				<label class="ta-contact-file">
					<input type="file" name="adjunto" accept="image/*">
					<span><?php echo ta_icon( 'paperclip', 16 ); ?>Adjuntar boceto o imagen (opcional)</span>
				</label>
				<button type="submit" class="ta-button ta-button--dark ta-contact-submit">
					Enviar mensaje
					<span class="ta-button-icon" aria-hidden="true">→</span>
				</button>
				<p class="ta-contact-form-note">Respondemos entre 24 y 48 horas hábiles.</p>
			</form>
		</div>

	</div>
</section>

<?php
get_footer();
