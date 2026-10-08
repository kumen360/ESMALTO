<?php
/**
 * Método de envío «Envío por peso (Esmalto)»: tramos de kg editables y coste por palé
 * para pedidos que superan el último tramo. Se añade en WooCommerce → Ajustes → Envío → zona.
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Envio {

	public static function init() {
		add_action( 'woocommerce_shipping_init', array( __CLASS__, 'cargar' ) );
		add_filter( 'woocommerce_shipping_methods', array( __CLASS__, 'registrar' ) );
	}

	public static function cargar() {
		require_once ESMALTO_CORE_DIR . 'includes/class-envio-peso.php';
	}

	public static function registrar( $metodos ) {
		$metodos['esmalto_peso'] = 'Esmalto_Envio_Peso';
		return $metodos;
	}

	/**
	 * «30 = 12» por línea → [ [30, 12], ... ] ordenado por kg.
	 */
	public static function leer_tramos( $texto ) {
		$tramos = array();
		foreach ( preg_split( '/\r\n|\r|\n/', (string) $texto ) as $linea ) {
			if ( preg_match( '/^\s*([\d.,]+)\s*(?:kg)?\s*[=:;|]\s*([\d.,]+)\s*(?:€)?\s*$/iu', $linea, $m ) ) {
				$tramos[] = array( esmalto_num( $m[1] ), esmalto_num( $m[2] ) );
			}
		}
		usort(
			$tramos,
			static function ( $a, $b ) {
				return $a[0] <=> $b[0];
			}
		);
		return $tramos;
	}

	/**
	 * Coste para un peso dado. null si no hay tramos.
	 */
	public static function coste( $kg, $tramos, $kg_palet, $coste_palet ) {
		if ( empty( $tramos ) ) {
			return null;
		}
		foreach ( $tramos as $tramo ) {
			if ( $kg <= $tramo[0] ) {
				return (float) $tramo[1];
			}
		}
		if ( $kg_palet > 0 && $coste_palet > 0 ) {
			return ceil( $kg / $kg_palet ) * $coste_palet;
		}
		$ultimo = end( $tramos );
		return (float) $ultimo[1];
	}
}
