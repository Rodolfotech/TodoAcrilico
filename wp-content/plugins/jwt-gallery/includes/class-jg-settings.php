<?php
/**
 * Pantalla de ajustes: dejar lista una cuenta para el panel de galería.
 *
 * Existe por una razón práctica: el panel autentica contra WordPress con el
 * correo del usuario, y en un hosting recién instalado esa cuenta puede no
 * existir, tener el campo de correo vacío o no ser administrador. Antes había
 * que acertar eso a mano en Usuarios; aquí se resuelve en un paso y sin
 * escribir contraseñas en el código.
 *
 * Solo la ven los que pueden administrar el sitio (`manage_options`).
 *
 * @package jwt-gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class JG_Settings {

	/**
	 * Nonce del formulario.
	 */
	const NONCE = 'jg_credentials';

	/**
	 * Capacidad que se exige para ver y usar esta pantalla.
	 */
	const CAP = 'manage_options';

	/**
	 * Rol que recibe la cuenta del panel.
	 *
	 * @return string
	 */
	public static function panel_role() {
		/**
		 * Filtra el rol que se asigna a la cuenta del panel.
		 *
		 * @param string $rol Rol de WordPress.
		 */
		return (string) apply_filters( 'jg_panel_role', 'administrator' );
	}

	/**
	 * Registra la pantalla y el manejador del formulario.
	 */
	public static function register() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_post_jg_save_credentials', array( __CLASS__, 'handle_save' ) );
	}

	/**
	 * Añade la pantalla dentro de Ajustes, sin crear un menú de primer nivel.
	 */
	public static function add_menu() {
		add_options_page(
			'Credenciales del panel de galería',
			'Credenciales del panel',
			self::CAP,
			'jg-credentials',
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Roles que esta pantalla puede asignar. Se limita a los que sirven para usar el
	 * panel (tienen `edit_posts`) y en los que no se puede tocar el sitio entero.
	 */
	private static function roles_permitidos() {
		$roles = apply_filters( 'jg_panel_roles', array( 'editor', 'administrator' ) );

		return array_values( array_intersect( array_map( 'sanitize_key', (array) $roles ), array( 'editor', 'administrator' ) ) );
	}

	/**
	 * Crea o actualiza la cuenta del panel.
	 */
	public static function handle_save() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die(
				'No tienes permiso para administrar las credenciales del panel.',
				'Permiso denegado',
				array( 'response' => 403 )
			);
		}
		check_admin_referer( self::NONCE );

		$email   = isset( $_POST['jg_email'] ) ? sanitize_email( wp_unslash( $_POST['jg_email'] ) ) : '';
		$pass    = isset( $_POST['jg_password'] ) ? (string) wp_unslash( $_POST['jg_password'] ) : '';
		$promote = ! empty( $_POST['jg_promote'] );

		if ( ! is_email( $email ) ) {
			self::redirect( 'email-invalido' );
		}

		if ( strlen( $pass ) < 8 ) {
			self::redirect( 'corta' );
		}

		$rol_post = isset( $_POST['jg_role'] ) ? sanitize_key( wp_unslash( $_POST['jg_role'] ) ) : '';
		$role     = in_array( $rol_post, self::roles_permitidos(), true ) ? $rol_post : self::panel_role();

		$user = get_user_by( 'email', $email );

		/*
		 * Caso frecuente en un hosting recién instalado: ya existe un usuario con
		 * este nombre de acceso pero con OTRO correo. Buscar por correo —que es lo
		 * que hace el login del panel— no lo encuentra, así que la cuenta quedaría
		 * inaccesible para siempre. Se busca también por nombre de acceso y se le
		 * corrige el correo.
		 */
		$vinculada = false;
		if ( ! $user ) {
			$por_login = get_user_by( 'login', $email );
			if ( $por_login ) {
				$user      = $por_login;
				$vinculada = true;
			}
		}

		if ( $user ) {
			if ( $vinculada ) {
				$actualizado = wp_update_user(
					array(
						'ID'         => $user->ID,
						'user_email' => $email,
					)
				);

				if ( is_wp_error( $actualizado ) ) {
					self::redirect( 'error' );
				}
			}

			wp_set_password( $pass, $user->ID );

			if ( $promote ) {
				$target = new WP_User( $user->ID );
				if ( ! in_array( $role, $target->roles, true ) ) {
					$target->add_role( $role );
				}
			}

			self::redirect( $vinculada ? 'vinculada' : 'actualizada' );
		}

		$user_id = wp_insert_user(
			array(
				'user_login' => $email,
				'user_email' => $email,
				'user_pass'  => $pass,
				'role'       => $role,
			)
		);

		if ( is_wp_error( $user_id ) ) {
			self::redirect( 'error' );
		}

		self::redirect( 'creada' );
	}

	/**
	 * Vuelve a la pantalla con un código de resultado.
	 *
	 * @param string $code Código de resultado en la URL.
	 */
	private static function redirect( $code ) {
		$args = array( 'jg' => rawurlencode( $code ) );

		// Se devuelve el correo para que el aviso diga a qué cuenta se tocó.
		if ( isset( $_POST['jg_email'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- validado en handle_save().
			$args['jg_email'] = rawurlencode( sanitize_email( wp_unslash( $_POST['jg_email'] ) ) );
		}

		wp_safe_redirect(
			add_query_arg( $args, admin_url( 'options-general.php?page=jg-credentials' ) )
		);
		exit;
	}

	/**
	 * Pinta la pantalla.
	 */
	public static function render() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die(
				'No tienes permiso para ver esta pantalla.',
				'Permiso denegado',
				array( 'response' => 403 )
			);
		}

		$notices = array(
			'creada'         => array( 'success', 'Cuenta creada para %s. Ya puedes entrar al panel con ese correo y esa contraseña.' ),
			'actualizada'    => array( 'success', 'Contraseña actualizada en la cuenta %s. Ya puedes entrar al panel con ese correo y esa contraseña.' ),
			'vinculada'      => array( 'success', 'Ese nombre de acceso ya pertenecía a una cuenta con otro correo: se vinculó a %s y se actualizó su contraseña. Ya puedes entrar al panel.' ),
			'corta'          => array( 'error', 'La contraseña necesita al menos 8 caracteres.' ),
			'email-invalido' => array( 'error', 'El correo no tiene un formato válido.' ),
			'error'          => array( 'error', 'WordPress no pudo guardar la cuenta. Revisa que el correo no esté en uso por otro usuario en Usuarios.' ),
		);

		$panel_url = self::panel_url();
		$role      = self::panel_role();
		$rol_nombre = translate_user_role( $role ) ? translate_user_role( $role ) : $role;

		?>
		<div class="wrap">
			<h1>Credenciales del panel de galería</h1>

			<?php if ( isset( $_GET['jg'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo muestra un texto. ?>
				<?php
				$code = sanitize_key( wp_unslash( $_GET['jg'] ) );
				if ( isset( $notices[ $code ] ) ) {
					$texto = $notices[ $code ][1];
					if ( false !== strpos( $texto, '%s' ) ) {
						// Se repite el correo en el aviso para no adivinar a qué cuenta
						// se le cambió la contraseña.
						$texto = sprintf( $texto, '<strong>' . esc_html( isset( $_GET['jg_email'] ) ? sanitize_email( wp_unslash( $_GET['jg_email'] ) ) : '' ) . '</strong>' );
					}
					?>
					<div class="notice notice-<?php echo esc_attr( $notices[ $code ][0] ); ?> is-dismissible">
						<p><?php echo wp_kses( $texto, array( 'strong' => array() ) ); ?></p>
					</div>
					<?php
				}
				?>
			<?php } ?>

			<p>
				El panel de la galería entra con una cuenta de WordPress. Escribe el correo que
				quieres usar y una contraseña: si esa cuenta ya existe se le cambia la contraseña,
				y si no existe se crea con el rol <strong><?php echo esc_html( $rol_nombre ); ?></strong>.
			</p>
			<?php if ( $panel_url ) : ?>
				<p>
					Dirección del panel:
					<a href="<?php echo esc_url( $panel_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $panel_url ); ?></a>
				</p>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
				onsubmit="return jgConfirmarCredenciales();">
				<?php wp_nonce_field( self::NONCE ); ?>
				<input type="hidden" name="action" value="jg_save_credentials">

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="jg_email">Correo</label>
						</th>
						<td>
							<input type="email" id="jg_email" name="jg_email" class="regular-text" required
								autocomplete="off"
								value=""
								placeholder="nombre@dominio.cl">
							<p class="description">
								Se deja vacío a propósito: hay que escribir el correo que se va a usar
								para entrar al panel. Si el campo viene con otro correo y guardas, lo
								que cambias es la contraseña de <em>esa</em> cuenta.
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="jg_password">Contraseña</label>
						</th>
						<td>
							<input type="password" id="jg_password" name="jg_password" class="regular-text"
								required minlength="8" autocomplete="new-password">
							<p class="description">Mínimo 8 caracteres. Se guarda cifrada por WordPress; esta pantalla nunca la vuelve a mostrar.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="jg_role">Rol</label>
						</th>
						<td>
							<select name="jg_role" id="jg_role">
								<?php foreach ( self::roles_permitidos() as $opcion ) : ?>
									<option value="<?php echo esc_attr( $opcion ); ?>" <?php selected( $role, $opcion ); ?>>
										<?php echo esc_html( translate_user_role( $opcion ) ? translate_user_role( $opcion ) : $opcion ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description">
								El panel necesita una cuenta con permiso de publicar contenido
								(<code>edit_posts</code>), que tienen tanto el rol editor como el
								administrador. El rol <strong>editor</strong> es la opción más
								recomendada si la cuenta es solo para la galería: puede subir y
								organizar las fotos, pero no tocar usuarios, plugins ni los ajustes
								del sitio.
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Rol extra</th>
						<td>
							<label>
								<input type="checkbox" name="jg_promote" value="1" checked>
								Si la cuenta ya existe, darle también el rol <?php echo esc_html( $rol_nombre ); ?>
							</label>
							<p class="description">
								Se marca por defecto para no dejar la cuenta sin permiso. Desmárcalo si
								la cuenta ya tiene el rol elegido arriba y no quieres añadir otro.
							</p>
						</td>
					</tr>
				</table>

				<?php
				// submit_button() vive en wp-admin/includes/template.php; el fallback
				// evita un error fatal si esta pantalla se renderiza fuera del admin.
				if ( function_exists( 'submit_button' ) ) {
					submit_button( 'Guardar y dejar lista la cuenta' );
				} else {
					printf(
						'<p class="submit"><button type="submit" class="button button-primary">%s</button></p>',
						esc_html__( 'Guardar y dejar lista la cuenta', 'jwt-gallery' )
					);
				}
				?>
			</form>

			<h2>Cuentas de este sitio</h2>
			<p class="description">
				El login del panel busca la cuenta por <strong>correo electrónico</strong>, no por
				nombre de usuario. Si una fila tiene el nombre de usuario con forma de correo pero
				otro correo distinto, esa cuenta no se puede usar en el login hasta corregirlo.
			</p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>Correo</th>
						<th>Nombre de usuario</th>
						<th>Roles</th>
						<th>¿Puede usar el panel?</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$users = get_users(
						array(
							'number'  => 100,
							'orderby' => 'ID',
							'order'   => 'ASC',
						)
					);
					if ( ! $users ) {
						echo '<tr><td colspan="4">No hay usuarios.</td></tr>';
					}
					foreach ( $users as $u ) {
						$puede = user_can( $u, 'edit_posts' );
						?>
						<tr>
							<td><?php echo esc_html( $u->user_email ? $u->user_email : '(correo vacío)' ); ?></td>
							<td><?php echo esc_html( $u->user_login ); ?></td>
							<td><?php echo esc_html( $u->roles ? implode( ', ', $u->roles ) : '—' ); ?></td>
							<td>
								<?php if ( $puede ) : ?>
									<span class="dashicons dashicons-yes" style="color:#03854A;"></span> Sí
								<?php else : ?>
									<span class="dashicons dashicons-no-alt"></span> No
								<?php endif; ?>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
		<script>
		/* Evita el error más probable: guardar con un correo distinto del que se
		   quiere usar y, sin querer, cambiar la contraseña de otra cuenta. */
		function jgConfirmarCredenciales() {
			var correo = document.getElementById( 'jg_email' ).value.trim();
			if ( ! correo ) {
				return false;
			}
			return window.confirm(
				'Se va a crear o actualizar la contraseña de la cuenta:\n\n' + correo +
				'\n\nSi esa cuenta ya existe, su contraseña antigua dejará de servir. ¿Continuar?'
			);
		}
		</script>
		<?php
	}

	/**
	 * URL pública de la página del panel, si existe.
	 *
	 * @return string
	 */
	private static function panel_url() {
		if ( ! function_exists( 'get_posts' ) ) {
			return '';
		}

		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
				's'              => '[jwt_gallery_panel]',
			)
		);

		if ( empty( $pages ) ) {
			return '';
		}

		return (string) get_permalink( $pages[0] );
	}
}
