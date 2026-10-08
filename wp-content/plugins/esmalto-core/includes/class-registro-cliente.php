<?php
/**
 * Alta de clientes particulares (Mi cuenta → Crear una cuenta): campos del diseño.
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Registro_Cliente {

	public static function init() {
		add_action( 'woocommerce_register_form_start', array( __CLASS__, 'campos_inicio' ) );
		add_action( 'woocommerce_register_form', array( __CLASS__, 'campos_fin' ) );
		add_filter( 'woocommerce_registration_errors', array( __CLASS__, 'validar' ), 10, 3 );
		add_action( 'woocommerce_created_customer', array( __CLASS__, 'guardar' ) );
	}

	private static function post( $clave ) {
		return isset( $_POST[ $clave ] ) ? sanitize_text_field( wp_unslash( $_POST[ $clave ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification -- WooCommerce verifica el nonce del registro.
	}

	private static function campo( $id, $etiqueta, $tipo = 'text', $obligatorio = false, $clase = 'form-row-wide', $autocomplete = '' ) {
		printf(
			'<p class="woocommerce-form-row form-row %1$s"><label for="%2$s">%3$s%4$s</label><input type="%5$s" class="woocommerce-Input input-text" name="%2$s" id="%2$s" value="%6$s" %7$s %8$s></p>',
			esc_attr( $clase ),
			esc_attr( $id ),
			esc_html( $etiqueta ),
			$obligatorio ? '&nbsp;<span class="required">*</span>' : '',
			esc_attr( $tipo ),
			esc_attr( self::post( $id ) ),
			$obligatorio ? 'required' : '',
			$autocomplete ? 'autocomplete="' . esc_attr( $autocomplete ) . '"' : ''
		);
	}

	public static function campos_inicio() {
		echo '<input type="hidden" name="esm_cliente" value="1">';
		self::campo( 'esm_first_name', __( 'Nombre', 'esmalto-core' ), 'text', true, 'form-row-first', 'given-name' );
		self::campo( 'esm_last_name', __( 'Apellidos', 'esmalto-core' ), 'text', true, 'form-row-last', 'family-name' );
		echo '<div class="clear"></div>';
	}

	public static function campos_fin() {
		self::campo( 'esm_phone', __( 'Teléfono', 'esmalto-core' ), 'tel', false, 'form-row-first', 'tel' );
		self::campo( 'esm_nif', __( 'NIF (opcional)', 'esmalto-core' ), 'text', false, 'form-row-last' );
		echo '<div class="clear"></div><p class="esm-form__subtitulo">' . esc_html__( 'Dirección de envío (opcional)', 'esmalto-core' ) . '</p>';
		self::campo( 'esm_address_1', __( 'Dirección', 'esmalto-core' ), 'text', false, 'form-row-wide', 'address-line1' );
		self::campo( 'esm_postcode', __( 'Código postal', 'esmalto-core' ), 'text', false, 'form-row-first', 'postal-code' );
		self::campo( 'esm_city', __( 'Población', 'esmalto-core' ), 'text', false, 'form-row-last', 'address-level2' );
		self::campo( 'esm_state', __( 'Provincia', 'esmalto-core' ), 'text', false, 'form-row-wide', 'address-level1' );
		?>
		<div class="clear"></div>
		<p class="form-row form-row-wide esm-form__check">
			<label>
				<input type="checkbox" name="esm_terminos" value="1" required <?php checked( '1', self::post( 'esm_terminos' ) ); ?>>
				<?php
				printf(
					wp_kses_post( __( 'He leído y acepto las <a href="%1$s" target="_blank">condiciones generales</a> y la <a href="%2$s" target="_blank">política de privacidad</a>.', 'esmalto-core' ) ),
					esc_url( esmalto_url_pagina( 'condiciones-generales' ) ),
					esc_url( esmalto_url_pagina( 'politica-de-privacidad' ) )
				);
				?>
				&nbsp;<span class="required">*</span>
			</label>
		</p>
		<p class="form-row form-row-wide esm-form__check">
			<label>
				<input type="checkbox" name="esm_novedades" value="1" <?php checked( '1', self::post( 'esm_novedades' ) ); ?>>
				<?php esc_html_e( 'Quiero recibir novedades y promociones de Esmalto.', 'esmalto-core' ); ?>
			</label>
		</p>
		<?php
	}

	public static function validar( $errores, $usuario, $email ) {
		if ( '1' !== self::post( 'esm_cliente' ) ) {
			return $errores; // Registro desde el pago u otro formulario.
		}
		if ( '' === self::post( 'esm_first_name' ) ) {
			$errores->add( 'esm_first_name', __( 'Indica tu nombre.', 'esmalto-core' ) );
		}
		if ( '' === self::post( 'esm_last_name' ) ) {
			$errores->add( 'esm_last_name', __( 'Indica tus apellidos.', 'esmalto-core' ) );
		}
		if ( '1' !== self::post( 'esm_terminos' ) ) {
			$errores->add( 'esm_terminos', __( 'Debes aceptar las condiciones generales y la política de privacidad.', 'esmalto-core' ) );
		}
		return $errores;
	}

	public static function guardar( $user_id ) {
		if ( '1' !== self::post( 'esm_cliente' ) ) {
			return;
		}
		$nombre   = self::post( 'esm_first_name' );
		$apellido = self::post( 'esm_last_name' );
		wp_update_user(
			array(
				'ID'           => $user_id,
				'first_name'   => $nombre,
				'last_name'    => $apellido,
				'display_name' => trim( $nombre . ' ' . $apellido ),
			)
		);
		$mapa = array(
			'billing_first_name'  => $nombre,
			'billing_last_name'   => $apellido,
			'shipping_first_name' => $nombre,
			'shipping_last_name'  => $apellido,
			'billing_phone'       => self::post( 'esm_phone' ),
			'esmalto_cif'         => strtoupper( self::post( 'esm_nif' ) ),
			'billing_address_1'   => self::post( 'esm_address_1' ),
			'shipping_address_1'  => self::post( 'esm_address_1' ),
			'billing_postcode'    => self::post( 'esm_postcode' ),
			'shipping_postcode'   => self::post( 'esm_postcode' ),
			'billing_city'        => self::post( 'esm_city' ),
			'shipping_city'       => self::post( 'esm_city' ),
			'billing_state_text'  => self::post( 'esm_state' ),
			'billing_country'     => 'ES',
			'shipping_country'    => 'ES',
		);
		foreach ( $mapa as $clave => $valor ) {
			if ( '' !== $valor ) {
				update_user_meta( $user_id, $clave, $valor );
			}
		}
		update_user_meta( $user_id, 'esmalto_novedades', '1' === self::post( 'esm_novedades' ) ? current_time( 'mysql' ) : '' );
		update_user_meta( $user_id, 'esmalto_terminos', current_time( 'mysql' ) );
	}
}
