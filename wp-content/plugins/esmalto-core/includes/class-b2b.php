<?php
/**
 * B2B: categorías A/B/C (roles de WholesaleX), descuentos, IVA, palés y área de cliente profesional.
 *
 * Estado y rol los gestiona WholesaleX:
 *   __wholesalex_status = pending | active | reject | inactive
 *   __wholesalex_role   = ID del rol asignado al aprobar
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_B2B {

	const ENDPOINT = 'contacto-comercial';

	private static $cache = array();

	public static function init() {
		if ( ! is_admin() || wp_doing_ajax() ) {
			add_filter( 'woocommerce_product_get_price', array( __CLASS__, 'precio' ), 20, 2 );
			add_filter( 'woocommerce_product_variation_get_price', array( __CLASS__, 'precio' ), 20, 2 );
			add_filter( 'woocommerce_variation_prices_price', array( __CLASS__, 'precio' ), 20, 2 );
		}
		add_filter( 'woocommerce_get_variation_prices_hash', array( __CLASS__, 'hash_precios' ), 20, 3 );

		add_filter( 'pre_option_woocommerce_tax_display_shop', array( __CLASS__, 'iva_profesional' ) );
		add_filter( 'pre_option_woocommerce_tax_display_cart', array( __CLASS__, 'iva_profesional' ) );

		add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'descuento_palets' ) );

		add_action( 'init', array( __CLASS__, 'registrar_endpoint' ) );
		add_filter( 'woocommerce_get_query_vars', array( __CLASS__, 'query_vars' ) );
		add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'menu_cuenta' ) );
		add_filter( 'woocommerce_endpoint_' . self::ENDPOINT . '_title', array( __CLASS__, 'titulo_endpoint' ) );
		add_action( 'woocommerce_account_' . self::ENDPOINT . '_endpoint', array( __CLASS__, 'pagina_contacto' ) );
		add_action( 'woocommerce_account_dashboard', array( __CLASS__, 'panel_escritorio' ), 5 );

		add_filter( 'wp_authenticate_user', array( __CLASS__, 'bloquear_pendientes' ), 99 );

		add_action( 'show_user_profile', array( __CLASS__, 'perfil' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'perfil' ) );
		add_action( 'personal_options_update', array( __CLASS__, 'guardar_perfil' ) );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'guardar_perfil' ) );
		add_filter( 'manage_users_columns', array( __CLASS__, 'columnas' ) );
		add_filter( 'manage_users_custom_column', array( __CLASS__, 'columna' ), 10, 3 );
	}

	/* ------------------------------------------------------------------ Categoría */

	/**
	 * Categoría del usuario (A, B, C) o '' si no es profesional aprobado.
	 */
	public static function categoria( $user_id = 0 ) {
		$user_id = $user_id ? (int) $user_id : get_current_user_id();
		if ( ! $user_id ) {
			return '';
		}
		if ( isset( self::$cache[ $user_id ] ) ) {
			return self::$cache[ $user_id ];
		}
		$categoria = '';
		$estado    = get_user_meta( $user_id, '__wholesalex_status', true );
		$rol       = get_user_meta( $user_id, '__wholesalex_role', true );
		if ( $rol && ( 'active' === $estado || ( '' === $estado && ! esmalto_wholesalex_activo() ) ) ) {
			$ajustes = esmalto_ajustes();
			foreach ( array( 'a', 'b', 'c' ) as $k ) {
				if ( '' !== $ajustes[ 'cat_' . $k . '_rol' ] && $rol === $ajustes[ 'cat_' . $k . '_rol' ] ) {
					$categoria = strtoupper( $k );
					break;
				}
			}
		}
		self::$cache[ $user_id ] = $categoria;
		return $categoria;
	}

	public static function es_profesional( $user_id = 0 ) {
		return '' !== self::categoria( $user_id );
	}

	/**
	 * Descuento (%) de la categoría del usuario.
	 */
	public static function descuento( $user_id = 0 ) {
		$categoria = self::categoria( $user_id );
		return $categoria ? (float) esmalto_ajuste( 'cat_' . strtolower( $categoria ) . '_pct' ) : 0.0;
	}

	public static function estado_solicitud( $user_id = 0 ) {
		$user_id = $user_id ? (int) $user_id : get_current_user_id();
		return $user_id ? (string) get_user_meta( $user_id, '__wholesalex_status', true ) : '';
	}

	/* ------------------------------------------------------------------ Precios */

	public static function precio( $precio, $producto = null ) {
		if ( '' === $precio || null === $precio ) {
			return $precio;
		}
		$descuento = self::descuento();
		if ( $descuento <= 0 ) {
			return $precio;
		}
		return (float) $precio * ( 1 - $descuento / 100 );
	}

	public static function hash_precios( $hash, $producto, $para_mostrar ) {
		$hash[] = 'esmalto-' . self::categoria() . '-' . self::descuento() . '-' . ( self::es_profesional() && esmalto_ajuste( 'b2b_sin_iva' ) ? 'excl' : 'std' );
		return $hash;
	}

	public static function iva_profesional( $valor ) {
		if ( ! did_action( 'init' ) || ! esmalto_ajuste( 'b2b_sin_iva' ) ) {
			return $valor;
		}
		return self::es_profesional() ? 'excl' : $valor;
	}

	/**
	 * Descuento adicional sobre las cajas que completan palés (solo profesionales).
	 */
	public static function descuento_palets( $cart ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}
		$pct = (float) esmalto_ajuste( 'palet_pct' );
		if ( $pct <= 0 || ! self::es_profesional() ) {
			return;
		}
		$base = 0.0;
		foreach ( $cart->get_cart() as $item ) {
			$id          = ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];
			$cajas_palet = (int) get_post_meta( $id, '_cajas_por_pallet', true );
			$cantidad    = (int) $item['quantity'];
			if ( $cajas_palet < 1 || $cantidad < $cajas_palet ) {
				continue;
			}
			$palets = intdiv( $cantidad, $cajas_palet );
			$base  += ( (float) $item['line_subtotal'] / $cantidad ) * $palets * $cajas_palet;
		}
		if ( $base > 0 ) {
			$cart->add_fee(
				sprintf( __( 'Descuento palé completo (%s %%)', 'esmalto-core' ), wc_format_localized_decimal( $pct ) ),
				- round( $base * $pct / 100, wc_get_price_decimals() ),
				true
			);
		}
	}

	/* ------------------------------------------------------------------ Mi cuenta */

	public static function registrar_endpoint() {
		add_rewrite_endpoint( self::ENDPOINT, EP_ROOT | EP_PAGES );
	}

	public static function query_vars( $vars ) {
		$vars[ self::ENDPOINT ] = self::ENDPOINT;
		return $vars;
	}

	public static function titulo_endpoint() {
		return __( 'Contacto comercial', 'esmalto-core' );
	}

	public static function menu_cuenta( $items ) {
		unset( $items['downloads'] );
		$nuevo = array();
		foreach ( $items as $clave => $etiqueta ) {
			if ( 'customer-logout' === $clave ) {
				$nuevo[ self::ENDPOINT ] = __( 'Contacto comercial', 'esmalto-core' );
			}
			$nuevo[ $clave ] = $etiqueta;
		}
		if ( ! isset( $nuevo[ self::ENDPOINT ] ) ) {
			$nuevo[ self::ENDPOINT ] = __( 'Contacto comercial', 'esmalto-core' );
		}
		return $nuevo;
	}

	public static function panel_comercial() {
		$a         = esmalto_ajustes();
		$categoria = self::categoria();
		ob_start();
		?>
		<div class="esm-panel-comercial">
			<div>
				<p class="is-style-antetitulo">
					<?php
					echo esc_html(
						$categoria
							? sprintf( __( 'Cuenta profesional · Categoría %1$s (−%2$s %%)', 'esmalto-core' ), $categoria, wc_format_localized_decimal( self::descuento() ) )
							: __( 'Atención al cliente', 'esmalto-core' )
					);
					?>
				</p>
				<h3><?php echo esc_html( $a['comercial_nombre'] ); ?></h3>
				<p>
					<?php
					if ( $categoria && (float) $a['palet_pct'] > 0 ) {
						echo esc_html( sprintf( __( 'Por palé completo, %s %% de descuento adicional.', 'esmalto-core' ), wc_format_localized_decimal( $a['palet_pct'] ) ) ) . ' ';
					}
					echo esc_html( trim( $a['comercial_email'] . ' · ' . $a['comercial_tel'], ' ·' ) );
					?>
				</p>
			</div>
			<div class="acciones">
				<?php if ( is_email( $a['comercial_email'] ) ) : ?>
					<a class="button" href="<?php echo esc_url( 'mailto:' . $a['comercial_email'] ); ?>"><?php esc_html_e( 'Escribir al comercial', 'esmalto-core' ); ?></a>
				<?php endif; ?>
				<?php if ( $a['comercial_tel'] ) : ?>
					<a class="button esm-boton-secundario" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $a['comercial_tel'] ) ); ?>"><?php esc_html_e( 'Llamar', 'esmalto-core' ); ?></a>
				<?php endif; ?>
				<?php if ( $a['comercial_whatsapp'] ) : ?>
					<a class="button esm-boton-secundario" target="_blank" rel="noopener" href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/\D/', '', $a['comercial_whatsapp'] ) ); ?>">WhatsApp</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function panel_escritorio() {
		if ( self::es_profesional() ) {
			echo self::panel_comercial(); // phpcs:ignore WordPress.Security.EscapeOutput -- HTML escapado en panel_comercial().
			return;
		}
		printf(
			'<p class="esm-cta-pro">%s <a href="%s">%s</a></p>',
			esc_html__( '¿Eres profesional del sector?', 'esmalto-core' ),
			esc_url( esmalto_url_pagina( 'profesionales' ) ),
			esc_html__( 'Solicita tu acceso profesional', 'esmalto-core' )
		);
	}

	public static function pagina_contacto() {
		echo self::panel_comercial(); // phpcs:ignore WordPress.Security.EscapeOutput
		echo do_shortcode( '[esmalto_formulario tipo="contacto"]' ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Si WholesaleX no está activo, impide el acceso a solicitudes pendientes o rechazadas.
	 */
	public static function bloquear_pendientes( $user ) {
		if ( is_wp_error( $user ) ) {
			$mensaje = wp_strip_all_tags( $user->get_error_message() );
			if ( preg_match( '/pending/i', $mensaje ) ) {
				return new WP_Error( 'esmalto_pendiente', __( 'Tu cuenta profesional está pendiente de aprobación. Te avisaremos por email cuando esté activa.', 'esmalto-core' ) );
			}
			if ( preg_match( '/reject/i', $mensaje ) ) {
				return new WP_Error( 'esmalto_rechazada', __( 'Tu solicitud profesional no ha sido aprobada. Escríbenos si necesitas más información.', 'esmalto-core' ) );
			}
			return $user;
		}
		if ( esmalto_wholesalex_activo() ) {
			return $user;
		}
		$estado = get_user_meta( $user->ID, '__wholesalex_status', true );
		if ( in_array( $estado, array( 'pending', 'reject' ), true ) ) {
			return new WP_Error( 'esmalto_pendiente', __( 'Tu cuenta profesional está pendiente de aprobación. Te avisaremos por email.', 'esmalto-core' ) );
		}
		return $user;
	}

	/* ------------------------------------------------------------------ Admin de usuarios */

	public static function campos_perfil() {
		return array(
			'esmalto_empresa'  => __( 'Empresa / estudio', 'esmalto-core' ),
			'esmalto_cif'      => __( 'CIF / NIF', 'esmalto-core' ),
			'esmalto_cnae'     => __( 'CNAE', 'esmalto-core' ),
			'esmalto_tipo'     => __( 'Tipo de profesional', 'esmalto-core' ),
			'billing_phone'    => __( 'Teléfono', 'esmalto-core' ),
			'esmalto_proyecto' => __( 'Proyecto / comentarios', 'esmalto-core' ),
		);
	}

	public static function perfil( $user ) {
		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			return;
		}
		$cnae      = get_user_meta( $user->ID, 'esmalto_cnae', true );
		$estado    = get_user_meta( $user->ID, '__wholesalex_status', true );
		$categoria = self::categoria( $user->ID );
		?>
		<h2><?php esc_html_e( 'Esmalto · Datos profesionales', 'esmalto-core' ); ?></h2>
		<table class="form-table" role="presentation">
			<?php foreach ( self::campos_perfil() as $clave => $etiqueta ) : ?>
				<tr>
					<th><label for="<?php echo esc_attr( $clave ); ?>"><?php echo esc_html( $etiqueta ); ?></label></th>
					<td>
						<?php if ( 'esmalto_proyecto' === $clave ) : ?>
							<textarea id="<?php echo esc_attr( $clave ); ?>" name="<?php echo esc_attr( $clave ); ?>" rows="3" class="regular-text"><?php echo esc_textarea( get_user_meta( $user->ID, $clave, true ) ); ?></textarea>
						<?php else : ?>
							<input type="text" id="<?php echo esc_attr( $clave ); ?>" name="<?php echo esc_attr( $clave ); ?>" value="<?php echo esc_attr( get_user_meta( $user->ID, $clave, true ) ); ?>" class="regular-text">
						<?php endif; ?>
						<?php if ( 'esmalto_cnae' === $clave && $cnae ) : ?>
							<p class="description">
								<?php
								echo Esmalto_CNAE::es_valido( $cnae )
									? '✔ ' . esc_html( Esmalto_CNAE::descripcion( $cnae ) )
									: '✖ ' . esc_html__( 'No está en la lista de CNAE admitidos.', 'esmalto-core' );
								?>
							</p>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<tr>
				<th><?php esc_html_e( 'Estado B2B', 'esmalto-core' ); ?></th>
				<td>
					<?php
					echo esc_html( self::texto_estado( $estado ) );
					if ( $categoria ) {
						echo ' · ' . esc_html( sprintf( __( 'Categoría %1$s (−%2$s %%)', 'esmalto-core' ), $categoria, wc_format_localized_decimal( self::descuento( $user->ID ) ) ) );
					}
					?>
					<p class="description"><?php esc_html_e( 'La aprobación y el rol (categoría) se gestionan en WholesaleX → Usuarios.', 'esmalto-core' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	public static function guardar_perfil( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'update-user_' . $user_id ) ) {
			return;
		}
		foreach ( array_keys( self::campos_perfil() ) as $clave ) {
			if ( ! isset( $_POST[ $clave ] ) ) {
				continue;
			}
			$valor = 'esmalto_proyecto' === $clave
				? sanitize_textarea_field( wp_unslash( $_POST[ $clave ] ) )
				: sanitize_text_field( wp_unslash( $_POST[ $clave ] ) );
			if ( 'esmalto_cnae' === $clave && '' !== Esmalto_CNAE::normalizar( $valor ) ) {
				$valor = Esmalto_CNAE::normalizar( $valor );
			}
			update_user_meta( $user_id, $clave, $valor );
		}
	}

	public static function texto_estado( $estado ) {
		$estados = array(
			'pending'  => __( 'Pendiente de aprobación', 'esmalto-core' ),
			'active'   => __( 'Activo', 'esmalto-core' ),
			'reject'   => __( 'Rechazado', 'esmalto-core' ),
			'inactive' => __( 'Inactivo', 'esmalto-core' ),
		);
		return $estados[ $estado ] ?? __( 'Cliente particular', 'esmalto-core' );
	}

	public static function columnas( $columnas ) {
		$columnas['esmalto_empresa'] = __( 'Empresa', 'esmalto-core' );
		$columnas['esmalto_cnae']    = __( 'CNAE', 'esmalto-core' );
		$columnas['esmalto_b2b']     = __( 'B2B', 'esmalto-core' );
		return $columnas;
	}

	public static function columna( $salida, $columna, $user_id ) {
		switch ( $columna ) {
			case 'esmalto_empresa':
				return esc_html( get_user_meta( $user_id, 'esmalto_empresa', true ) );
			case 'esmalto_cnae':
				$cnae = get_user_meta( $user_id, 'esmalto_cnae', true );
				if ( ! $cnae ) {
					return '—';
				}
				return esc_html( Esmalto_CNAE::formatear( $cnae ) ) . ( Esmalto_CNAE::es_valido( $cnae ) ? ' ✔' : ' ✖' );
			case 'esmalto_b2b':
				$texto     = self::texto_estado( get_user_meta( $user_id, '__wholesalex_status', true ) );
				$categoria = self::categoria( $user_id );
				return esc_html( $texto . ( $categoria ? ' · ' . $categoria : '' ) );
		}
		return $salida;
	}
}
