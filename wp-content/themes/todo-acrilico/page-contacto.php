<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$contacto_page = ta_find_page( array( 'cotizar', 'contacto', 'contact' ) );
$contacto_url  = $contacto_page ? get_permalink( $contacto_page ) : home_url( '/cotizar/' );

$ta_privacy_page = ta_find_page( array( 'politica-de-privacidad', 'privacidad' ) );
$ta_terms_page   = ta_find_page( array( 'terminos-y-condiciones', 'termino' ) );
$ta_privacy_url  = $ta_privacy_page ? get_permalink( $ta_privacy_page ) : home_url( '/' );
$ta_terms_url    = $ta_terms_page ? get_permalink( $ta_terms_page ) : home_url( '/' );

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
$ta_quote_uso     = isset( $_GET['uso'] ) ? sanitize_text_field( wp_unslash( $_GET['uso'] ) ) : '';
$ta_quote_uso     = $ta_quote_uso ? trim( $ta_quote_uso ) : '';
$ta_has_quote     = (bool) $ta_quote_image;

$ta_contact_error_messages = array(
	'campos'          => 'Completa tu nombre (mín. 3 caracteres) y descripción del proyecto (mín. 20 caracteres).',
	'nombre_corto'    => 'El nombre debe tener al menos 3 caracteres.',
	'nombre_largo'    => 'El nombre no puede exceder 100 caracteres.',
	'nombre_numerico' => 'Ingrese su nombre y apellidos (sin números).',
	'desc_corta'      => 'La descripción del proyecto debe tener al menos 20 caracteres.',
	'email_vacio'     => 'Por favor ingresa tu correo electrónico.',
	'email_invalido'  => 'El correo electrónico no tiene un formato válido.',
	'email_caracteres'=> 'El correo no puede comenzar con caracteres especiales (ej: no puede ser ".user@..."). Intenta nuevamente.',
	'email_dominio'   => 'Usa un correo permitido: gmail.com, outlook.com, hotmail.com, live.cl, yahoo.com, icloud.com o dominio .cl',
	'email_patron'    => 'El correo tiene un formato inválido (no puede tener "@." o comenzar con punto).',
	'celular'         => 'Ingresa un celular chileno válido (8 dígitos, formato: 7456 4321 o similar).',
	'archivo'         => 'No se pudo procesar el archivo adjunto. Intenta de nuevo.',
	'archivo_grande'  => 'El archivo adjunto no puede superar los 5 MB.',
	'archivo_tipo'    => 'El archivo adjunto debe ser una imagen (JPG, PNG, GIF o WEBP) o un PDF.',
	'archivos_muchos' => 'Puedes adjuntar hasta 5 archivos.',
	'terminos'        => 'Debes aceptar la Política de Privacidad y los Términos y condiciones para continuar.',
	'nonce'           => 'Tu sesión expiró, vuelve a intentarlo.',
	'rate_limit'      => 'Espera un momento antes de enviar otro mensaje.',
	'envio'           => 'No se pudo enviar el mensaje. Intenta de nuevo más tarde.',
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

		<!-- Modal de Rate Limiting -->
		<div id="ta-contact-rate-limit-modal" class="ta-modal ta-modal--hidden">
			<div class="ta-modal-overlay"></div>
			<div class="ta-modal-content">
				<button type="button" class="ta-modal-close ta-modal-close--right" id="ta-rate-limit-close" aria-label="Cerrar modal">&times;</button>
				<div class="ta-modal-icon">⏱️</div>
				<h3 class="ta-modal-title">Espera un momento</h3>
				<p class="ta-modal-message">Antes de enviar otro mensaje</p>
				<div class="ta-modal-countdown">
					<span id="ta-countdown-value" class="ta-countdown-number">60</span>
					<span class="ta-countdown-label">segundos</span>
				</div>
				<p class="ta-modal-note">Intenta de nuevo cuando el contador llegue a 0</p>
			</div>
		</div>

		<!-- Modal de Éxito -->
		<div id="ta-contact-success-modal" class="ta-modal ta-modal--hidden">
			<div class="ta-modal-overlay"></div>
			<div class="ta-modal-content ta-modal-content--success">
				<button type="button" class="ta-modal-close ta-modal-close--right" id="ta-success-close" aria-label="Cerrar modal">&times;</button>
				<div class="ta-modal-icon ta-modal-icon--success">✅</div>
				<h3 class="ta-modal-title">¡Mensaje enviado!</h3>
				<p class="ta-modal-message">Nos pondremos en contacto en un plazo de 24 a 48 horas hábiles.</p>
				<button type="button" class="ta-button ta-button--dark ta-modal-button" id="ta-success-close-btn">Cerrar</button>
			</div>
		</div>

		<div class="ta-contact-form-card">
			<?php if ( 'error' === $ta_contact_status ) : ?>
				<p class="ta-contact-alert ta-contact-alert--error"><?php echo esc_html( $ta_contact_error_messages[ $ta_contact_reason ] ?? 'Ocurrió un error. Intenta de nuevo.' ); ?></p>
			<?php else : ?>
				<p class="ta-contact-alert ta-contact-alert--error" style="display: none;"></p>
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
						<?php if ( $ta_quote_uso ) : ?>
							<span class="ta-contact-quote-uso"><strong>Uso:</strong> <?php echo esc_html( $ta_quote_uso ); ?></span>
						<?php endif; ?>
						<?php if ( $ta_quote_meas ) : ?>
							<span class="ta-contact-quote-medi"><strong>Medidas:</strong> <?php echo esc_html( $ta_quote_meas ); ?></span>
						<?php endif; ?>
						<span class="ta-contact-quote-note">Esta información se adjunta automáticamente junto a tu mensaje.</span>
					</div>
				</div>
			<?php endif; ?>

			<form class="ta-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-ajax-url="<?php echo esc_url( home_url( '/wp-admin/admin-post.php' ) ); ?>" enctype="multipart/form-data">
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
					<?php if ( $ta_quote_uso ) : ?>
						<input type="hidden" name="adjunto_uso" value="<?php echo esc_attr( $ta_quote_uso ); ?>">
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
						<input type="text" name="nombre" placeholder="Ej. Juan Pérez" minlength="3" maxlength="100" required>
						<span class="ta-contact-field-error" data-field="nombre"></span>
					</label>
					<label>
						Teléfono
						<div class="ta-contact-tel-wrap">
							<div class="ta-contact-tel-prefix">
								<span class="ta-contact-flag" aria-hidden="true">🇨🇱</span>
								<span>+56 9</span>
							</div>
							<input type="tel" name="celular" placeholder="8765 4321" inputmode="tel" pattern="[0-9\s]{8,12}" maxlength="12" required>
						</div>
						<span class="ta-contact-field-error" data-field="celular"></span>
					</label>
				</div>
				<label>
					Correo Electrónico
					<input type="email" name="email" placeholder="juan@ejemplo.cl" required>
					<span class="ta-contact-field-error" data-field="email"></span>
				</label>
				<label>
					Descripción del proyecto
					<textarea name="descripcion" rows="5" minlength="20" maxlength="2000" placeholder="Cuéntanos detalles de medidas, materiales y plazos…" required></textarea>
					<div class="ta-contact-textarea-footer">
						<span class="ta-contact-field-error" data-field="descripcion"></span>
						<span class="ta-contact-char-limit">Límite 2000 caracteres</span>
					</div>
				</label>
				<div class="ta-contact-files">
					<input type="file" name="adjunto[]" id="ta-adjunto-input" accept="image/*,.pdf,application/pdf" multiple>
					<label for="ta-adjunto-input" class="ta-contact-file-upload">
						<?php echo ta_icon( 'paperclip', 16 ); ?>Adjuntar imagen, PDF del boceto o producto (opcional)
					</label>
					<div id="ta-adjunto-list"></div>
				</div>
				<p class="ta-contact-files-note">Nota: Por favor no adjunte documentos con datos personales sensibles o cédula de identidad.</p>
				<label class="ta-contact-optin ta-contact-accept">
					<input type="checkbox" name="acepto_terminos" value="1" required>
					<span>He leído y acepto <a href="<?php echo esc_url( $ta_privacy_url ); ?>" target="_blank" rel="noopener noreferrer">Política de Privacidad</a> y los <a href="<?php echo esc_url( $ta_terms_url ); ?>" target="_blank" rel="noopener noreferrer">Términos y Condiciones</a>.</span>
				</label>
				<label class="ta-contact-optin">
					<input type="checkbox" name="acepta_ofertas" value="1">
					<span>(Opcional) Acepto recibir ofertas y comunicaciones comerciales.</span>
				</label>
				<div class="ta-contact-alert ta-contact-alert--info" role="note">
					<span class="ta-alert-icon" aria-hidden="true"><?php echo ta_icon( 'shield', 18 ); ?></span>
					<p class="ta-alert-text">Sus datos serán tratados conforme a la ley 21.719 para gestionar su cotización. Consulte nuestra Política de Privacidad o ejerza sus derechos en contacto@todoacrilico.cl</p>
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
	var MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB
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

	function showFileError(message) {
		var existingError = list.querySelector('.ta-contact-file-error');
		if (existingError) {
			existingError.remove();
		}
		var errorDiv = document.createElement('div');
		errorDiv.className = 'ta-contact-file-error';
		errorDiv.textContent = '⚠ ' + message;
		list.insertBefore(errorDiv, list.firstChild);
	}

	function clearFileError() {
		var errorDiv = list.querySelector('.ta-contact-file-error');
		if (errorDiv) {
			errorDiv.remove();
		}
	}

	function render() {
		list.innerHTML = '';
		var full = dt.files.length >= MAX;
		Array.prototype.forEach.call(dt.files, function (file, index) {
			var row = document.createElement('div');
			row.className = 'ta-contact-file-status';

			var name = document.createElement('span');
			var sizeWarning = '';
			if (file.size > MAX_FILE_SIZE) {
				sizeWarning = ' (⚠ ' + formatSize(file.size) + ' - muy grande)';
				row.style.opacity = '0.6';
			}
			name.textContent = file.name + (formatSize(file.size) ? ' (' + formatSize(file.size) + ')' : '') + sizeWarning;

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
				clearFileError();
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
		clearFileError();
		var accepted = [];
		var errorMessages = [];

		for (var i = 0; i < input.files.length; i++) {
			var file = input.files[i];

			// Validar tamaño
			if (file.size > MAX_FILE_SIZE) {
				errorMessages.push('El archivo "' + file.name + '" debe pesar menos de 5 MB. Pesa ' + formatSize(file.size) + '.');
				continue;
			}

			// Validar extensión
			var ext = file.name.split('.').pop().toLowerCase();
			var validExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
			if (validExts.indexOf(ext) === -1) {
				errorMessages.push('El archivo "' + file.name + '" tiene formato no permitido. Usa: JPG, PNG, GIF, WEBP o PDF.');
				continue;
			}

			// Validar límite de archivos
			if (dt.files.length + accepted.length >= MAX) {
				errorMessages.push('Puedes adjuntar máximo 5 archivos.');
				continue;
			}

			accepted.push(file);
		}

		// Mostrar todos los errores si existen
		if (errorMessages.length > 0) {
			showFileError(errorMessages.join(' '));
		}

		// Solo agregar archivos válidos
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
	// Filtro para campo nombre: solo permite letras, espacios y acentos + convertir a mayúsculas
	var nombre = document.querySelector('input[name="nombre"]');
	if (nombre) {
		nombre.addEventListener('input', function () {
			// Elimina números automáticamente
			var originalValue = nombre.value;
			var filteredValue = originalValue.replace(/\d/g, '');
			// Convertir a mayúsculas
			var upperValue = filteredValue.toUpperCase();
			nombre.value = upperValue;
		});
	}

	// Convertir email a minúsculas
	var email = document.querySelector('input[name="email"]');
	if (email) {
		email.addEventListener('input', function () {
			email.value = email.value.toLowerCase();
		});
	}

	// Formateo para teléfono
	var tel = document.querySelector('.ta-contact-tel-wrap input[type="tel"][name="celular"]');
	if (tel) {
		tel.addEventListener('input', function () {
			var digits = tel.value.replace(/\D/g, '').slice(0, 8);
			tel.value = digits.length > 4 ? digits.slice(0, 4) + ' ' + digits.slice(4) : digits;
		});
	}
})();

(function () {
	var form = document.querySelector('.ta-contact-form');
	if (!form) return;

	var fieldErrorMessages = {
		nombre: {
			valueMissing: 'Ingrese su nombre y apellidos',
			tooShort: 'Mínimo 3 caracteres',
			tooLong: 'Máximo 100 caracteres',
			custom: 'Ingrese su nombre y apellidos (sin números)'
		},
		email: {
			valueMissing: 'El correo es requerido',
			typeMismatch: 'Formato de correo inválido',
			custom: 'No puede comenzar con caracteres especiales'
		},
		celular: {
			valueMissing: 'El teléfono es requerido',
			patternMismatch: 'Ingresa 8 dígitos válidos (ej: 7456 4321)',
			custom: 'Formato de teléfono inválido'
		},
		descripcion: {
			valueMissing: 'La descripción es requerida',
			tooShort: 'Mínimo 20 caracteres',
			tooLong: 'Máximo 2000 caracteres',
			custom: 'Descripción inválida'
		}
	};

	function getFieldErrorMessage(field) {
		var messages = fieldErrorMessages[field.name] || {};
		var validity = field.validity;

		if (validity.valueMissing) return messages.valueMissing || 'Campo requerido';
		if (validity.typeMismatch) return messages.typeMismatch || 'Formato inválido';
		if (validity.tooShort) return messages.tooShort || 'Demasiado corto';
		if (validity.tooLong) return messages.tooLong || 'Demasiado largo';
		if (validity.patternMismatch) return messages.patternMismatch || 'Formato inválido';
		if (validity.customError) return messages.custom || 'Valor inválido';

		return '';
	}

	function showFieldError(field, message) {
		field.style.borderColor = '#dc2626';
		var errorSpan = form.querySelector('[data-field="' + field.name + '"]');
		if (errorSpan) {
			errorSpan.textContent = message;
			errorSpan.style.display = 'block';
		}
	}

	function clearFieldError(field) {
		field.style.borderColor = '#16a34a';
		var errorSpan = form.querySelector('[data-field="' + field.name + '"]');
		if (errorSpan) {
			errorSpan.textContent = '';
			errorSpan.style.display = 'none';
		}
	}

	function validateField(field) {
		if (field.value.length === 0) {
			field.style.borderColor = '';
			var errorSpan = form.querySelector('[data-field="' + field.name + '"]');
			if (errorSpan) {
				errorSpan.style.display = 'none';
			}
			return true;
		}

		// Validación personalizada: nombre NO puede contener números
		if (field.name === 'nombre') {
			if (/\d/.test(field.value)) {
				field.setCustomValidity('El nombre no puede contener números');
				var message = 'Ingrese su nombre y apellidos (sin números)';
				showFieldError(field, message);
				return false;
			} else {
				field.setCustomValidity('');
			}
		}

		// Validación personalizada: email (solo si tiene contenido)
		if (field.name === 'email' && field.value.length > 0) {
			var isValid = true;
			var errorMsg = 'Ingrese un correo electrónico válido';

			if (!/^[a-z0-9]/i.test(field.value)) {
				isValid = false;
			} else if (field.value.indexOf('@.') !== -1 || /^\./.test(field.value)) {
				isValid = false;
			} else if (!/(@gmail\.com|@outlook\.com|@hotmail\.com|@live\.cl|@yahoo\.com|@icloud\.com|\.cl)$/i.test(field.value)) {
				// Solo permite: los 6 dominios específicos O cualquier dominio .cl
				isValid = false;
			}

			if (!isValid) {
				field.setCustomValidity('Email inválido');
				showFieldError(field, errorMsg);
				return false;
			} else {
				field.setCustomValidity('');
			}
		}

		if (!field.checkValidity()) {
			var message = getFieldErrorMessage(field);
			showFieldError(field, message);
			return false;
		} else {
			clearFieldError(field);
			return true;
		}
	}

	var fields = form.querySelectorAll('input[minlength], input[maxlength], textarea[minlength], input[type="email"], input[type="tel"], input[type="text"]');
	Array.prototype.forEach.call(fields, function (field) {
		if (field.name === 'ta_contact_website') return;

		field.addEventListener('blur', function () {
			validateField(field);
		});

		field.addEventListener('input', function () {
			if (field.value.length > 2 || field.value.length === 0) {
				validateField(field);
			}
		});
	});

	var submitBtn = form.querySelector('button[type="submit"]');
	if (submitBtn) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var hasErrors = false;
			var firstInvalidField = null;

			var fieldsToValidate = form.querySelectorAll('input[required], textarea[required], input[type="email"]');
			Array.prototype.forEach.call(fieldsToValidate, function (field) {
				if (field.name === 'ta_contact_website') return;
				if (!validateField(field)) {
					hasErrors = true;
					if (!firstInvalidField) {
						firstInvalidField = field;
					}
				}
			});

			var termsCheckbox = form.querySelector('input[name="acepto_terminos"]');
			if (termsCheckbox && !termsCheckbox.checked) {
				hasErrors = true;
				termsCheckbox.style.borderColor = '#dc2626';
			}

			if (hasErrors) {
				if (firstInvalidField) {
					firstInvalidField.focus();
					firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
				}
				return false;
			}

			// AJAX submit — mantener datos del formulario si hay error
			submitBtn.disabled = true;
			submitBtn.style.opacity = '0.6';

			var formData = new FormData(form);
			var ajaxUrl = form.getAttribute('data-ajax-url') || form.action;

			// Si es URL relativa, convertir a absoluta
			if (ajaxUrl.charAt(0) === '/') {
				ajaxUrl = window.location.origin + ajaxUrl;
			}

			fetch(ajaxUrl, {
				method: 'POST',
				body: formData,
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
			.then(function(response) {
				if (!response.ok) {
					throw new Error('HTTP ' + response.status);
				}
				return response.json();
			})
			.then(function(data) {
				submitBtn.disabled = false;
				submitBtn.style.opacity = '1';

				if (data.success) {
					// Éxito: limpiar formulario y mostrar modal
					form.reset();
					var successModal = document.getElementById('ta-contact-success-modal');
					if (successModal) {
						successModal.classList.remove('ta-modal--hidden');
					}
					// Después de 3 segundos, redirigir
					setTimeout(function() {
						window.location.href = data.redirect_url || window.location.href;
					}, 3000);
				} else {
					// Error: mostrar mensaje pero MANTENER datos
					var alertDiv = form.parentElement.querySelector('.ta-contact-alert--error');
					if (alertDiv) {
						alertDiv.textContent = data.message || 'Ocurrió un error. Intenta de nuevo.';
						alertDiv.style.display = 'block';
						alertDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
					}
					// Para rate_limit, mostrar modal en lugar de alerta
					if (data.reason === 'rate_limit') {
						// Trigger countdown modal manualmente
						var modal = document.getElementById('ta-contact-rate-limit-modal');
						if (modal) {
							modal.classList.remove('ta-modal--hidden');
							submitBtn.disabled = true;
							submitBtn.style.opacity = '0.6';
							localStorage.setItem('ta_rate_limit_timestamp', Date.now().toString());
							// Reiniciar countdown
							var countdownValue = document.getElementById('ta-countdown-value');
							var secondsLeft = 60;
							countdownValue.textContent = secondsLeft;
							var countdownInterval = setInterval(function () {
								secondsLeft--;
								countdownValue.textContent = secondsLeft;
								if (secondsLeft <= 0) {
									clearInterval(countdownInterval);
									modal.classList.add('ta-modal--hidden');
									submitBtn.disabled = false;
									submitBtn.style.opacity = '1';
									localStorage.removeItem('ta_rate_limit_timestamp');
								}
							}, 1000);
						}
					}
				}
			})
			.catch(function(err) {
				console.error('Error en AJAX:', err);
				submitBtn.disabled = false;
				submitBtn.style.opacity = '1';
				alert('Error de conexión. Intenta de nuevo. Detalles: ' + err.message);
			});
		});
	}
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

// Cerrar modales al hacer clic en X, botón Cerrar, o en el overlay
(function () {
	var modals = document.querySelectorAll('.ta-modal');
	Array.prototype.forEach.call(modals, function (modal) {
		var closeBtn = modal.querySelector('.ta-modal-close');
		var closeModalBtn = modal.querySelector('.ta-modal-button');
		var overlay = modal.querySelector('.ta-modal-overlay');

		if (closeBtn) {
			closeBtn.addEventListener('click', function () {
				modal.classList.add('ta-modal--hidden');
			});
		}

		if (closeModalBtn) {
			closeModalBtn.addEventListener('click', function () {
				modal.classList.add('ta-modal--hidden');
			});
		}

		if (overlay) {
			overlay.addEventListener('click', function () {
				modal.classList.add('ta-modal--hidden');
			});
		}
	});
})();

// Modal de Rate Limiting con Countdown
(function () {
	var modal = document.getElementById('ta-contact-rate-limit-modal');
	var countdownValue = document.getElementById('ta-countdown-value');
	var form = document.querySelector('.ta-contact-form');
	var submitBtn = form.querySelector('button[type="submit"]');
	if (!modal || !form) return;

	var RATE_LIMIT_KEY = 'ta_rate_limit_timestamp';
	var RATE_LIMIT_DURATION = 60000; // 60 segundos en ms

	// Verificar si hay mensaje de rate_limit en la URL o si hay timestamp guardado
	var urlParams = new URLSearchParams(window.location.search);
	var reason = urlParams.get('reason');
	var savedTimestamp = localStorage.getItem(RATE_LIMIT_KEY);

	// Si viene con parámetro rate_limit Y no hay timestamp guardado, guardar el timestamp
	// (evita sobrescribir si el usuario actualiza la página)
	if (reason === 'rate_limit' && !savedTimestamp) {
		localStorage.setItem(RATE_LIMIT_KEY, Date.now().toString());
		savedTimestamp = localStorage.getItem(RATE_LIMIT_KEY);
	}

	// Si hay timestamp guardado, mostrar modal y countdown
	if (savedTimestamp) {
		var timestamp = parseInt(savedTimestamp, 10);
		var now = Date.now();
		var elapsed = now - timestamp;
		var secondsLeft = Math.ceil((RATE_LIMIT_DURATION - elapsed) / 1000);

		// Si aún hay tiempo, mostrar modal
		if (secondsLeft > 0) {
			// Mostrar modal
			modal.classList.remove('ta-modal--hidden');
			submitBtn.disabled = true;

			countdownValue.textContent = secondsLeft;

			var countdownInterval = setInterval(function () {
				secondsLeft--;
				countdownValue.textContent = secondsLeft;

				if (secondsLeft <= 0) {
					clearInterval(countdownInterval);
					// Cerrar modal y habilitar botón
					modal.classList.add('ta-modal--hidden');
					submitBtn.disabled = false;
					// Cambiar estilo del botón para indicar que está activo
					submitBtn.style.opacity = '1';
					submitBtn.style.cursor = 'pointer';
					// Limpiar timestamp
					localStorage.removeItem(RATE_LIMIT_KEY);

					// Remover parámetro 'reason=rate_limit' de la URL
					// para que no reaparezca el contador al actualizar
					var newUrl = window.location.pathname + window.location.search.replace(/[?&]reason=rate_limit/g, '').replace(/^&/, '?');
					if (newUrl.endsWith('?')) {
						newUrl = newUrl.slice(0, -1);
					}
					window.history.replaceState({}, document.title, newUrl);
				}
			}, 1000);
		} else {
			// Si ya pasaron los 60 segundos, limpiar y no mostrar modal
			localStorage.removeItem(RATE_LIMIT_KEY);
		}
	}
})();
</script>

<?php
get_footer();
