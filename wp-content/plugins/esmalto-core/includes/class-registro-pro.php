<?php
/**
 * Alta profesional: [esmalto_registro_profesional]
 *
 * Crea un cliente de WooCommerce en estado «pendiente» de WholesaleX (no puede entrar
 * hasta que se apruebe en WholesaleX → Usuarios) y valida el CNAE contra la lista admitida.
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Registro_Pro {

	const ACCION = 'esmalto_registro_pro';

	private static $errores = array();
	private static $valores = array();

	public static function init() {
		add_shortcode( 'esmalto_registro_profesional', array( __CLASS__, 'shortcode' ) );
		add_action( 'template_redirect', array( __CLASS__, 'procesar' ) );
	}

	public static function tipos() {
		return array(
			__( 'Arquitecto / estudio', 'esmalto-core' ),
			__( 'Interiorista', 'esmalto-core' ),
			__( 'Constructor / reformista', 'esmalto-core' ),
			__( 'Instalador / alicatador', 'esmalto-core' ),
			__( 'Jefe de compras / obra', 'esmalto-core' ),
			__( 'Distribuidor / tienda', 'esmalto-core' ),
			__( 'Promotor / inmobiliaria', 'esmalto-core' ),
			__( 'Otros', 'esmalto-core' ),
		);
	}

	public static function procesar() {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['esmalto_accion'] ) || self::ACCION !== $_POST['esmalto_accion'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		if ( ! isset( $_POST['_esm_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_esm_nonce'] ) ), self::ACCION ) ) {
			self::$errores['general'] = __( 'La sesión ha caducado. Vuelve a enviar el formulario.', 'esmalto-core' );
			return;
		}
		// Campo trampa antispam.
		if ( ! empty( $_POST['esm_web'] ) ) {
			return;
		}

		$v = array(
			'nombre'    => sanitize_text_field( wp_unslash( $_POST['esm_nombre'] ?? '' ) ),
			'empresa'   => sanitize_text_field( wp_unslash( $_POST['esm_empresa'] ?? '' ) ),
			'cif'       => strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', sanitize_text_field( wp_unslash( $_POST['esm_cif'] ?? '' ) ) ) ),
			'tipo'      => sanitize_text_field( wp_unslash( $_POST['esm_tipo'] ?? '' ) ),
			'cnae'      => sanitize_text_field( wp_unslash( $_POST['esm_cnae'] ?? '' ) ),
			'telefono'  => sanitize_text_field( wp_unslash( $_POST['esm_telefono'] ?? '' ) ),
			'email'     => sanitize_email( wp_unslash( $_POST['esm_email'] ?? '' ) ),
			'proyecto'  => sanitize_textarea_field( wp_unslash( $_POST['esm_proyecto'] ?? '' ) ),
			'privacidad' => ! empty( $_POST['esm_privacidad'] ),
		);
		$password = (string) wp_unslash( $_POST['esm_password'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- la contraseña no se sanea.
		self::$valores = $v;

		$e = array();
		if ( '' === $v['nombre'] ) {
			$e['nombre'] = __( 'Indica tu nombre y apellidos.', 'esmalto-core' );
		}
		if ( '' === $v['empresa'] ) {
			$e['empresa'] = __( 'Indica el nombre de la empresa o estudio.', 'esmalto-core' );
		}
		if ( ! preg_match( '/^[A-Z0-9]{8,10}$/', $v['cif'] ) ) {
			$e['cif'] = __( 'Introduce un CIF o NIF válido.', 'esmalto-core' );
		}
		if ( '' === Esmalto_CNAE::normalizar( $v['cnae'] ) ) {
			$e['cnae'] = __( 'El CNAE debe tener 4 dígitos (p. ej. 4333 o 43.33).', 'esmalto-core' );
		} elseif ( ! Esmalto_CNAE::es_valido( $v['cnae'] ) ) {
			$e['cnae'] = __( 'Tu CNAE no está entre las actividades admitidas para la tarifa profesional. Escríbenos y lo revisamos.', 'esmalto-core' );
		}
		if ( strlen( preg_replace( '/\D/', '', $v['telefono'] ) ) < 9 ) {
			$e['telefono'] = __( 'Introduce un teléfono de contacto.', 'esmalto-core' );
		}
		if ( ! is_email( $v['email'] ) ) {
			$e['email'] = __( 'Introduce un email válido.', 'esmalto-core' );
		} elseif ( email_exists( $v['email'] ) ) {
			$e['email'] = sprintf(
				/* translators: %s: URL de mi cuenta */
				__( 'Ya existe una cuenta con este email. <a href="%s">Inicia sesión</a> y escríbenos para pasarla a profesional.', 'esmalto-core' ),
				esc_url( esmalto_url_pagina( 'mi-cuenta' ) )
			);
		}
		if ( strlen( $password ) < 8 ) {
			$e['password'] = __( 'La contraseña debe tener al menos 8 caracteres.', 'esmalto-core' );
		}
		if ( ! $v['privacidad'] ) {
			$e['privacidad'] = __( 'Debes aceptar la política de privacidad.', 'esmalto-core' );
		}
		if ( $e ) {
			self::$errores = $e;
			return;
		}

		$partes   = preg_split( '/\s+/', $v['nombre'], 2 );
		$nombre   = $partes[0];
		$apellido = $partes[1] ?? '';

		// Sin el email «cuenta creada» de WooCommerce: la cuenta aún no está activa.
		add_filter( 'woocommerce_email_enabled_customer_new_account', '__return_false', 99 );
		$usuario = function_exists( 'wc_create_new_customer_username' )
			? wc_create_new_customer_username( $v['email'], array( 'first_name' => $nombre, 'last_name' => $apellido ) )
			: sanitize_user( current( explode( '@', $v['email'] ) ), true );
		$user_id = function_exists( 'wc_create_new_customer' )
			? wc_create_new_customer( $v['email'], $usuario, $password, array( 'first_name' => $nombre, 'last_name' => $apellido ) )
			: wp_insert_user(
				array(
					'user_login' => $v['email'],
					'user_email' => $v['email'],
					'user_pass'  => $password,
					'first_name' => $nombre,
					'last_name'  => $apellido,
					'role'       => 'customer',
				)
			);
		remove_filter( 'woocommerce_email_enabled_customer_new_account', '__return_false', 99 );

		if ( is_wp_error( $user_id ) ) {
			self::$errores['general'] = $user_id->get_error_message();
			return;
		}

		$cnae = Esmalto_CNAE::normalizar( $v['cnae'] );
		$meta = array(
			'esmalto_empresa'     => $v['empresa'],
			'esmalto_cif'         => $v['cif'],
			'esmalto_cnae'        => $cnae,
			'esmalto_tipo'        => $v['tipo'],
			'esmalto_proyecto'    => $v['proyecto'],
			'esmalto_alta_pro'    => current_time( 'mysql' ),
			'billing_first_name'  => $nombre,
			'billing_last_name'   => $apellido,
			'billing_company'     => $v['empresa'],
			'billing_phone'       => $v['telefono'],
			'billing_email'       => $v['email'],
			'billing_country'     => 'ES',
			'__wholesalex_status' => 'pending',
		);
		$rol = esmalto_ajuste( 'rol_alta' ) ? esmalto_ajuste( 'rol_alta' ) : esmalto_ajuste( 'cat_a_rol' );
		if ( $rol ) {
			$meta['__wholesalex_registration_role'] = $rol;
		}
		foreach ( $meta as $clave => $valor ) {
			update_user_meta( $user_id, $clave, $valor );
		}
		do_action( 'esmalto_registro_profesional', $user_id, $v );

		self::avisar( $user_id, $v, $cnae );

		wp_safe_redirect( add_query_arg( 'alta', 'ok', wp_get_referer() ? wp_get_referer() : esmalto_url_pagina( 'profesionales' ) ) . '#alta-profesional' );
		exit;
	}

	private static function avisar( $user_id, $v, $cnae ) {
		$filas = array(
			__( 'Nombre', 'esmalto-core' )   => $v['nombre'],
			__( 'Empresa', 'esmalto-core' )  => $v['empresa'],
			__( 'CIF/NIF', 'esmalto-core' )  => $v['cif'],
			__( 'Tipo', 'esmalto-core' )     => $v['tipo'],
			__( 'CNAE', 'esmalto-core' )     => Esmalto_CNAE::formatear( $cnae ) . ' — ' . Esmalto_CNAE::descripcion( $cnae ),
			__( 'Teléfono', 'esmalto-core' ) => $v['telefono'],
			__( 'Email', 'esmalto-core' )    => $v['email'],
			__( 'Proyecto', 'esmalto-core' ) => $v['proyecto'],
		);
		$tabla = '<table cellspacing="0" cellpadding="6" border="1" style="border-collapse:collapse;width:100%">';
		foreach ( $filas as $k => $valor ) {
			$tabla .= '<tr><th style="text-align:left">' . esc_html( $k ) . '</th><td>' . nl2br( esc_html( $valor ) ) . '</td></tr>';
		}
		$tabla .= '</table>';

		$enlace = esmalto_wholesalex_activo() ? admin_url( 'admin.php?page=wholesalex-users' ) : admin_url( 'user-edit.php?user_id=' . $user_id );
		esmalto_enviar_email(
			esmalto_email_avisos(),
			sprintf( __( 'Nueva solicitud profesional: %s', 'esmalto-core' ), $v['empresa'] ),
			__( 'Nueva solicitud profesional', 'esmalto-core' ),
			'<p>' . esc_html__( 'Hay una nueva solicitud de acceso profesional pendiente de aprobación.', 'esmalto-core' ) . '</p>' . $tabla .
			'<p><a href="' . esc_url( $enlace ) . '">' . esc_html__( 'Revisar y aprobar (asigna la categoría A, B o C)', 'esmalto-core' ) . '</a></p>'
		);
		esmalto_enviar_email(
			$v['email'],
			__( 'Hemos recibido tu solicitud profesional', 'esmalto-core' ),
			__( 'Solicitud recibida', 'esmalto-core' ),
			'<p>' . esc_html( sprintf( __( 'Hola %s,', 'esmalto-core' ), $v['nombre'] ) ) . '</p><p>' .
			esc_html__( 'Gracias por solicitar tu acceso profesional en Esmalto. Revisaremos tus datos y CNAE y activaremos tu cuenta en 24–48 h. Te avisaremos por email; después podrás entrar con tu email y la contraseña que has elegido.', 'esmalto-core' ) . '</p>'
		);
	}

	private static function error( $campo ) {
		return isset( self::$errores[ $campo ] ) ? '<span class="esm-form__error" role="alert">' . wp_kses_post( self::$errores[ $campo ] ) . '</span>' : '';
	}

	private static function valor( $campo ) {
		return isset( self::$valores[ $campo ] ) && is_string( self::$valores[ $campo ] ) ? self::$valores[ $campo ] : '';
	}

	public static function shortcode() {
		if ( isset( $_GET['alta'] ) && 'ok' === $_GET['alta'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			return '<div id="alta-profesional" class="esm-form__ok"><p class="esm-form__ok-titulo">' . esc_html__( 'Solicitud recibida', 'esmalto-core' ) . '</p><p>' .
				esc_html__( 'Gracias. Revisaremos tus datos y te contactaremos en 24–48 h con tu acceso profesional.', 'esmalto-core' ) . '</p></div>';
		}
		if ( is_user_logged_in() && Esmalto_B2B::es_profesional() ) {
			return '<p class="esm-form__ok">' . esc_html__( 'Ya tienes una cuenta profesional activa.', 'esmalto-core' ) . ' <a href="' . esc_url( esmalto_url_pagina( 'mi-cuenta' ) ) . '">' . esc_html__( 'Ir a mi cuenta', 'esmalto-core' ) . '</a></p>';
		}

		wp_enqueue_script( 'esmalto-registro', ESMALTO_CORE_URL . 'assets/js/registro.js', array(), ESMALTO_CORE_VERSION, true );
		$lista = array();
		foreach ( Esmalto_CNAE::lista() as $codigo => $descripcion ) {
			$lista[ (string) $codigo ] = $descripcion;
		}
		wp_localize_script(
			'esmalto-registro',
			'esmaltoCnae',
			array(
				'lista'     => $lista,
				'noValido'  => __( 'Este CNAE no está entre las actividades admitidas.', 'esmalto-core' ),
				'formato'   => __( 'Escribe los 4 dígitos (p. ej. 4333).', 'esmalto-core' ),
			)
		);

		ob_start();
		?>
		<form id="alta-profesional" class="esm-form esm-form--pro" method="post" action="#alta-profesional" novalidate>
			<?php echo self::error( 'general' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="esm-form__rejilla">
				<p class="esm-form__campo esm-form__campo--ancho">
					<label for="esm_nombre"><?php esc_html_e( 'Nombre y apellidos', 'esmalto-core' ); ?> *</label>
					<input type="text" id="esm_nombre" name="esm_nombre" required autocomplete="name" value="<?php echo esc_attr( self::valor( 'nombre' ) ); ?>">
					<?php echo self::error( 'nombre' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</p>
				<p class="esm-form__campo">
					<label for="esm_empresa"><?php esc_html_e( 'Empresa / estudio', 'esmalto-core' ); ?> *</label>
					<input type="text" id="esm_empresa" name="esm_empresa" required autocomplete="organization" value="<?php echo esc_attr( self::valor( 'empresa' ) ); ?>">
					<?php echo self::error( 'empresa' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</p>
				<p class="esm-form__campo">
					<label for="esm_cif"><?php esc_html_e( 'CIF / NIF', 'esmalto-core' ); ?> *</label>
					<input type="text" id="esm_cif" name="esm_cif" required value="<?php echo esc_attr( self::valor( 'cif' ) ); ?>">
					<?php echo self::error( 'cif' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</p>
				<p class="esm-form__campo">
					<label for="esm_cnae"><?php esc_html_e( 'CNAE (actividad principal)', 'esmalto-core' ); ?> *</label>
					<input type="text" id="esm_cnae" name="esm_cnae" required inputmode="numeric" list="esm_cnae_lista" placeholder="4333" value="<?php echo esc_attr( self::valor( 'cnae' ) ); ?>" aria-describedby="esm_cnae_ayuda">
					<datalist id="esm_cnae_lista">
						<?php foreach ( $lista as $codigo => $descripcion ) : ?>
							<option value="<?php echo esc_attr( $codigo ); ?>"><?php echo esc_html( Esmalto_CNAE::formatear( $codigo ) . ' · ' . $descripcion ); ?></option>
						<?php endforeach; ?>
					</datalist>
					<span id="esm_cnae_ayuda" class="esm-form__ayuda"></span>
					<?php echo self::error( 'cnae' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</p>
				<p class="esm-form__campo">
					<label for="esm_tipo"><?php esc_html_e( 'Tipo de profesional', 'esmalto-core' ); ?></label>
					<select id="esm_tipo" name="esm_tipo">
						<?php foreach ( self::tipos() as $tipo ) : ?>
							<option <?php selected( self::valor( 'tipo' ), $tipo ); ?>><?php echo esc_html( $tipo ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p class="esm-form__campo">
					<label for="esm_telefono"><?php esc_html_e( 'Teléfono', 'esmalto-core' ); ?> *</label>
					<input type="tel" id="esm_telefono" name="esm_telefono" required autocomplete="tel" value="<?php echo esc_attr( self::valor( 'telefono' ) ); ?>">
					<?php echo self::error( 'telefono' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</p>
				<p class="esm-form__campo">
					<label for="esm_email"><?php esc_html_e( 'Email profesional', 'esmalto-core' ); ?> *</label>
					<input type="email" id="esm_email" name="esm_email" required autocomplete="email" value="<?php echo esc_attr( self::valor( 'email' ) ); ?>">
					<?php echo self::error( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</p>
				<p class="esm-form__campo">
					<label for="esm_password"><?php esc_html_e( 'Contraseña', 'esmalto-core' ); ?> *</label>
					<input type="password" id="esm_password" name="esm_password" required minlength="8" autocomplete="new-password">
					<?php echo self::error( 'password' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</p>
				<p class="esm-form__campo esm-form__campo--ancho">
					<label for="esm_proyecto"><?php esc_html_e( 'Cuéntanos tu proyecto (opcional)', 'esmalto-core' ); ?></label>
					<textarea id="esm_proyecto" name="esm_proyecto" rows="4"><?php echo esc_textarea( self::valor( 'proyecto' ) ); ?></textarea>
				</p>
				<p class="esm-form__trampa" aria-hidden="true">
					<label for="esm_web">Web</label>
					<input type="text" id="esm_web" name="esm_web" tabindex="-1" autocomplete="off">
				</p>
				<p class="esm-form__campo esm-form__campo--ancho esm-form__check">
					<label>
						<input type="checkbox" name="esm_privacidad" value="1" required <?php checked( ! empty( self::$valores['privacidad'] ) ); ?>>
						<?php
						printf(
							/* translators: %s: URL política de privacidad */
							wp_kses_post( __( 'He leído y acepto la <a href="%s" target="_blank">política de privacidad</a>.', 'esmalto-core' ) ),
							esc_url( esmalto_url_pagina( 'politica-de-privacidad' ) )
						);
						?>
					</label>
					<?php echo self::error( 'privacidad' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</p>
			</div>
			<input type="hidden" name="esmalto_accion" value="<?php echo esc_attr( self::ACCION ); ?>">
			<?php wp_nonce_field( self::ACCION, '_esm_nonce' ); ?>
			<button type="submit" class="button esm-form__enviar"><?php esc_html_e( 'Enviar solicitud', 'esmalto-core' ); ?></button>
			<p class="esm-form__nota"><?php esc_html_e( 'Tu cuenta quedará pendiente hasta que la revisemos. Te avisaremos por email.', 'esmalto-core' ); ?></p>
		</form>
		<?php
		return ob_get_clean();
	}
}
