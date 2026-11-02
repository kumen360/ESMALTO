<?php
/**
 * Pago (bloque «Finalizar compra»): NIF o DNI obligatorio para todos (lo necesitamos para la factura).
 * Se valida la letra o el dígito de control y se guarda en el pedido (también como _billing_nif) y en
 * el cliente.
 *
 * Campos adicionales del pago de WooCommerce (woocommerce_register_additional_checkout_field).
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Checkout {

	const CAMPO_NIF = 'esmalto/nif';

	public static function init() {
		add_action( 'woocommerce_init', array( __CLASS__, 'registrar_campos' ) );
		add_filter( 'woocommerce_get_default_value_for_' . self::CAMPO_NIF, array( __CLASS__, 'nif_por_defecto' ), 10, 3 );
		add_action( 'woocommerce_set_additional_field_value', array( __CLASS__, 'copiar_campo' ), 10, 4 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'recursos' ) );
	}

	public static function registrar_campos() {
		if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
			return;
		}
		woocommerce_register_additional_checkout_field(
			array(
				'id'                => self::CAMPO_NIF,
				'label'             => __( 'NIF o DNI', 'esmalto-core' ),
				'location'          => 'contact',
				'type'              => 'text',
				'required'          => true,
				'attributes'        => array(
					'autocomplete'     => 'off',
					'maxLength'        => 14,
					'aria-describedby' => 'esm-nif-ayuda',
				),
				'sanitize_callback' => 'esmalto_nif_normalizar',
				'validate_callback' => array( __CLASS__, 'validar_nif' ),
			)
		);
	}

	public static function validar_nif( $valor ) {
		if ( ! esmalto_nif_valido( $valor ) ) {
			return new WP_Error( 'esmalto_nif', __( 'Revisa el NIF o DNI: la letra o el número de control no coinciden.', 'esmalto-core' ) );
		}
	}

	/**
	 * Clientes con cuenta: el NIF que dieron al darse de alta.
	 */
	public static function nif_por_defecto( $valor, $grupo, $objeto ) {
		if ( '' === (string) $valor && $objeto instanceof WC_Customer && $objeto->get_id() ) {
			return (string) get_user_meta( $objeto->get_id(), 'esmalto_cif', true );
		}
		return $valor;
	}

	/**
	 * Copia el NIF a las claves que leen la ficha del cliente, Holded y los listados.
	 */
	public static function copiar_campo( $clave, $valor, $grupo, $objeto ) {
		if ( self::CAMPO_NIF !== $clave ) {
			return;
		}
		if ( $objeto instanceof WC_Order ) {
			$objeto->update_meta_data( '_billing_nif', $valor );
		} elseif ( $objeto instanceof WC_Customer && $objeto->get_id() && '' !== (string) $valor ) {
			update_user_meta( $objeto->get_id(), 'esmalto_cif', $valor );
		}
	}

	public static function recursos() {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}
		wp_enqueue_script( 'esmalto-checkout', ESMALTO_CORE_URL . 'assets/js/checkout.js', array(), ESMALTO_CORE_VERSION, true );
		wp_localize_script(
			'esmalto-checkout',
			'esmaltoCheckout',
			array(
				'campoNif' => self::CAMPO_NIF,
				'ayudaNif' => __( 'Lo necesitamos para emitir tu factura.', 'esmalto-core' ),
			)
		);
	}
}
