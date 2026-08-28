<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$contacto_page = get_page_by_path( 'contacto' );
$contacto_url  = $contacto_page ? get_permalink( $contacto_page ) : home_url( '/contacto/' );

$ta_contact_status = isset( $_GET['ta_contact'] ) ? sanitize_key( $_GET['ta_contact'] ) : '';
$ta_contact_reason = isset( $_GET['reason'] ) ? sanitize_key( $_GET['reason'] ) : '';

$ta_quote_image   = isset( $_GET['imagen'] ) ? esc_url_raw( wp_unslash( $_GET['imagen'] ) ) : '';
$ta_quote_piece   = isset( $_GET['pieza'] ) ? sanitize_text_field( wp_unslash( $_GET['pieza'] ) ) : '';
$ta_quote_piece   = wp_strip_all_tags( $ta_quote_piece );
$ta_quote_image   = ( $ta_quote_image && wp_http_validate_url( $ta_quote_image ) ) ? $ta_quote_image : '';
$ta_quote_desc    = isset( $_GET['descripcion'] ) ? sanitize_textarea_field( wp_unslash( $_GET['descripcion'] ) ) : '';
$ta_quote_desc    = $ta_quote_desc ? wp_strip_all_tags( trim( $ta_quote_desc ) ) : '';
$ta_quote_meas    = isset( $_GET['medidas'] ) ? sanitize_text_field( wp_unslash( $_GET['medidas'] ) ) : '';
$ta_quote_meas    = $ta_quote_meas ? trim( $ta_quote_meas ) : '';
$ta_has_quote     = (bool) $ta_quote_image;

$ta_contact_error_messages = array(
	'campos'         => 'Completa tu nombre y la descripción del proyecto.',
	'email'          => 'Ingresa un correo electrónico válido.',
	'celular'        => 'Ingresa un celular chileno válido (9 dígitos que empiecen en 9).',
	'archivo'        => 'No se pudo procesar el archivo adjunto. Intenta de nuevo.',
	'archivo_grande' => 'El archivo adjunto no puede superar los 5 MB.',
	'archivo_tipo'   => 'El archivo adjunto debe ser una imagen (JPG, PNG, GIF o WEBP) o un PDF.',
	'archivos_muchos'=> 'Puedes adjuntar hasta 5 archivos.',
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

			<?php if ( $ta_has_quote ) : ?>
				<div class="ta-contact-quote" id="ta-contact-quote">
					<button type="button" class="ta-contact-quote-remove" id="ta-contact-quote-remove" aria-label="Quitar pieza a cotizar del mensaje">×</button>
					<?php if ( $ta_quote_image ) : ?>
						<div class="ta-contact-quote-thumb">
							<img src="<?php echo esc_url( $ta_quote_image ); ?>" alt="<?php echo esc_attr( $ta_quote_piece ? $ta_quote_piece : 'Imagen de la pieza a cotizar' ); ?>" loading="lazy">
						</div>
					<?php endif; ?>
					<div class="ta-contact-quote-text">
						<?php if ( $ta_quote_piece ) : ?>
							<strong><?php echo esc_html( $ta_quote_piece ); ?></strong>
						<?php endif; ?>
						<?php if ( $ta_quote_desc ) : ?>
							<span class="ta-contact-quote-desc"><?php echo esc_html( $ta_quote_desc ); ?></span>
						<?php endif; ?>
						<?php if ( $ta_quote_meas ) : ?>
							<span class="ta-contact-quote-medi"><strong>Medidas:</strong> <?php echo esc_html( $ta_quote_meas ); ?></span>
						<?php endif; ?>
						<span class="ta-contact-quote-note">Esta información se adjunta automáticamente junto a tu mensaje.</span>
					</div>
				</div>
			<?php endif; ?>

			<form class="ta-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
				<input type="hidden" name="action" value="ta_contact_submit">
				<input type="hidden" name="redirect_to" value="<?php echo esc_url( $contacto_url ); ?>">
				<?php wp_nonce_field( 'ta_contact_submit', 'ta_contact_nonce' ); ?>
				<?php if ( $ta_has_quote ) : ?>
					<input type="hidden" name="adjunto_referencia" value="<?php echo esc_url( $ta_quote_image ); ?>">
					<?php if ( $ta_quote_piece ) : ?>
						<input type="hidden" name="adjunto_titulo" value="<?php echo esc_attr( $ta_quote_piece ); ?>">
					<?php endif; ?>
					<?php if ( $ta_quote_desc ) : ?>
						<input type="hidden" name="adjunto_descripcion" value="<?php echo esc_attr( $ta_quote_desc ); ?>">
					<?php endif; ?>
					<?php if ( $ta_quote_meas ) : ?>
						<input type="hidden" name="adjunto_medidas" value="<?php echo esc_attr( $ta_quote_meas ); ?>">
					<?php endif; ?>
				<?php endif; ?>
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
						Teléfono
						<div class="ta-contact-tel-wrap">
							<div class="ta-contact-tel-prefix">
								<span class="ta-contact-flag" aria-hidden="true">🇨🇱</span>
								<span>+56 9</span>
							</div>
							<input type="tel" name="celular" placeholder="8765 4321" inputmode="tel" required>
						</div>
					</label>
				</div>
				<label>
					Correo Electrónico
					<input type="email" name="email" placeholder="juan@ejemplo.cl" required>
				</label>
				<label>
					Descripción del proyecto
					<textarea name="descripcion" rows="5" maxlength="2000" placeholder="Cuéntanos detalles de medidas, materiales y plazos…" required></textarea>
					<span class="ta-contact-char-limit">Límite 2000 caracteres</span>
				</label>
				<div class="ta-contact-files">
					<input type="file" name="adjunto[]" id="ta-adjunto-input" accept="image/*,.pdf,application/pdf" multiple>
					<label for="ta-adjunto-input" class="ta-contact-file-upload">
						<?php echo ta_icon( 'paperclip', 16 ); ?>Adjuntar imagen, PDF del boceto o producto (opcional)
					</label>
					<div id="ta-adjunto-list"></div>
				</div>
				<button type="submit" class="ta-button ta-button--dark ta-contact-submit">
					Enviar mensaje
					<span class="ta-button-icon" aria-hidden="true">→</span>
				</button>
				<p class="ta-contact-form-note">Respondemos entre 24 y 48 horas hábiles.</p>
			</form>
		</div>

	</div>
</section>

<script>
(function () {
	var MAX = 5;
	var input = document.getElementById('ta-adjunto-input');
	var list = document.getElementById('ta-adjunto-list');
	var upload = document.querySelector('.ta-contact-file-upload');
	if (!input || !list || !upload) return;

	var dt = new DataTransfer();

	function formatSize(bytes) {
		if (!bytes) return '';
		if (bytes >= 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
		return Math.ceil(bytes / 1024) + ' KB';
	}

	function syncFiles() {
		input.files = dt.files;
	}

	function render() {
		list.innerHTML = '';
		var full = dt.files.length >= MAX;
		Array.prototype.forEach.call(dt.files, function (file, index) {
			var row = document.createElement('div');
			row.className = 'ta-contact-file-status';

			var name = document.createElement('span');
			name.textContent = file.name + (formatSize(file.size) ? ' (' + formatSize(file.size) + ')' : '');

			var actions = document.createElement('div');
			actions.className = 'ta-contact-file-actions';

			var add = document.createElement('button');
			add.type = 'button';
			add.className = 'ta-contact-file-add';
			add.textContent = 'Agregar';
			if (full) {
				add.disabled = true;
			}
			add.addEventListener('click', function () {
				input.click();
			});

			var remove = document.createElement('button');
			remove.type = 'button';
			remove.className = 'ta-contact-file-remove';
			remove.textContent = 'Eliminar';
			remove.setAttribute('aria-label', 'Eliminar ' + file.name);
			remove.addEventListener('click', function () {
				dt.items.remove(index);
				syncFiles();
				render();
			});

			actions.appendChild(add);
			actions.appendChild(remove);
			row.appendChild(name);
			row.appendChild(actions);
			list.appendChild(row);
		});

		upload.style.display = full ? 'none' : '';
	}

	input.addEventListener('change', function () {
		var accepted = [];
		for (var i = 0; i < input.files.length && dt.files.length + accepted.length < MAX; i++) {
			accepted.push(input.files[i]);
		}
		for (var j = 0; j < accepted.length; j++) {
			dt.items.add(accepted[j]);
		}
		input.value = '';
		syncFiles();
		render();
	});

	render();
})();

(function () {
	var tel = document.querySelector('.ta-contact-tel-wrap input[type="tel"][name="celular"]');
	if (!tel) return;

	tel.addEventListener('input', function () {
		var digits = tel.value.replace(/\D/g, '').slice(0, 8);
		tel.value = digits.length > 4 ? digits.slice(0, 4) + ' ' + digits.slice(4) : digits;
	});
})();

(function () {
	var quote = document.getElementById('ta-contact-quote');
	var remove = document.getElementById('ta-contact-quote-remove');
	if (!quote || !remove) return;

	remove.addEventListener('click', function () {
		var hiddens = document.querySelectorAll('input[name^="adjunto_"]');
		Array.prototype.forEach.call(hiddens, function (h) { h.remove(); });
		quote.remove();
	});
})();
</script>

<?php
get_footer();
