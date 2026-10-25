<?php
/**
 * Pago (bloque «Finalizar compra»):
 * - NIF o DNI obligatorio para todos (lo necesitamos para la factura): se valida la letra o el dígito
 *   de control y se guarda en el pedido (también como _billing_nif) y en el cliente.
 * - Casilla de recargo de equivalencia para comercios minoristas en ese régimen: al marcarla se suma
 *   el 5,2 % sobre la base imponible (productos, porte y demás líneas) y el pedido queda marcado.
 *
 * Campos adicionales del pago de WooCommerce (woocommerce_register_additional_checkout_field).
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Checkout {

	const CAMPO_NIF     = 'esmalto/nif';
	const CAMPO_RECARGO = 'esmalto/recargo';
	const SESION        = 'esmalto_recargo';
	const RECARGO_PCT   = 5.2;

	public static function init() {
		add_action( 'woocommerce_init', array( __CLASS__, 'registrar_campos' ) );
		add_filter( 'woocommerce_get_default_value_for_' . self::CAMPO_NIF, array( __CLASS__, 'nif_por_defecto' ), 10, 3 );
		add_action( 'woocommerce_set_additional_field_value', array( __CLASS__, 'copiar_campo' ), 10, 4 );
		// Esmalto Core arranca en plugins_loaded (20), cuando WooCommerce Blocks ya está cargado.
		add_action( 'init', array( __CLASS__, 'registrar_actualizacion' ) );
		add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'recargo' ), 99 );
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( __CLASS__, 'comprobar_recargo' ), 10, 2 );
		add_action( 'woocommerce_store_api_checkout_order_processed', array( __CLASS__, 'limpiar_recargo' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'recursos' ) );
	}

	public static function etiqueta_recargo() {
		/* translators: %s: porcentaje del recargo */
		return sprintf( __( 'Recargo de equivalencia (%s %%)', 'esmalto-core' ), wc_format_localized_decimal( self::RECARGO_PCT ) );
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
		woocommerce_register_additional_checkout_field(
			array(
				'id'       => self::CAMPO_RECARGO,
				'label'    => __( 'Soy comercio minorista en recargo de equivalencia (se suma un 5,2 % al pedido)', 'esmalto-core' ),
				'location' => 'order',
				'type'     => 'checkbox',
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
	 * Copia el NIF y la marca de recargo a las claves que leen la ficha del cliente, Holded y los listados.
	 */
	public static function copiar_campo( $clave, $valor, $grupo, $objeto ) {
		if ( self::CAMPO_NIF === $clave ) {
			if ( $objeto instanceof WC_Order ) {
				$objeto->update_meta_data( '_billing_nif', $valor );
			} elseif ( $objeto instanceof WC_Customer && $objeto->get_id() && '' !== (string) $valor ) {
				update_user_meta( $objeto->get_id(), 'esmalto_cif', $valor );
			}
		} elseif ( self::CAMPO_RECARGO === $clave && $objeto instanceof WC_Order ) {
			$objeto->update_meta_data( '_esmalto_recargo', $valor ? 'yes' : 'no' );
		}
	}

	/* ------------------------------------------------------------------ Recargo */

	/**
	 * La casilla avisa al servidor (Store API) para recalcular el total en el momento.
	 */
	public static function registrar_actualizacion() {
		if ( ! function_exists( 'woocommerce_store_api_register_update_callback' ) ) {
			return;
		}
		woocommerce_store_api_register_update_callback(
			array(
				'namespace' => 'esmalto-recargo',
				'callback'  => static function ( $datos ) {
					if ( WC()->session ) {
						WC()->session->set( self::SESION, ! empty( $datos['activo'] ) );
					}
				},
			)
		);
	}

	private static function activo() {
		return WC()->session && WC()->session->get( self::SESION );
	}

	public static function recargo( $cart ) {
		if ( ! self::activo() || ! $cart instanceof WC_Cart ) {
			return;
		}
		$base = (float) $cart->get_cart_contents_total() + (float) $cart->get_shipping_total();
		foreach ( $cart->get_fees() as $fee ) {
			$base += (float) $fee->amount;
		}
		if ( $base <= 0 ) {
			return;
		}
		$cart->add_fee( self::etiqueta_recargo(), round( $base * self::RECARGO_PCT / 100, 2 ), false );
	}

	/**
	 * Al pagar, la casilla y el total tienen que coincidir (por si el navegador no llegó a avisar).
	 */
	public static function comprobar_recargo( $order, $request ) {
		$campos  = (array) ( $request['additional_fields'] ?? array() );
		$marcado = ! empty( $campos[ self::CAMPO_RECARGO ] );
		$aplicado = false;
		foreach ( $order->get_fees() as $fee ) {
			if ( self::etiqueta_recargo() === $fee->get_name() ) {
				$aplicado = true;
			}
		}
		if ( $marcado === $aplicado ) {
			return;
		}
		if ( WC()->session ) {
			WC()->session->set( self::SESION, $marcado );
		}
		if ( class_exists( '\Automattic\WooCommerce\StoreApi\Exceptions\RouteException' ) ) {
			throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
				'esmalto_recargo',
				esc_html__( 'Hemos actualizado el total con el recargo de equivalencia. Revísalo y vuelve a pulsar «Realizar el pedido».', 'esmalto-core' ),
				409
			);
		}
	}

	public static function limpiar_recargo() {
		if ( WC()->session ) {
			WC()->session->set( self::SESION, false );
		}
	}

	public static function recursos() {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}
		wp_enqueue_script( 'esmalto-checkout', ESMALTO_CORE_URL . 'assets/js/checkout.js', array( 'wp-data' ), ESMALTO_CORE_VERSION, true );
		wp_localize_script(
			'esmalto-checkout',
			'esmaltoCheckout',
			array(
				'campoNif'     => self::CAMPO_NIF,
				'campoRecargo' => self::CAMPO_RECARGO,
				'activo'       => (bool) self::activo(),
				'ayudaNif'     => __( 'Lo necesitamos para emitir tu factura.', 'esmalto-core' ),
			)
		);
	}
}
