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
		// Dirección y horario de recogida (Esmalto → Ajustes).
		add_shortcode( 'esmalto_recogida', array( __CLASS__, 'shortcode_recogida' ) );
		add_action( 'woocommerce_email_order_details', array( __CLASS__, 'recogida_email' ), 25, 3 );
		add_action( 'woocommerce_thankyou', array( __CLASS__, 'recogida_gracias' ), 5 );
	}

	/**
	 * Dirección y horario del almacén. Vacíos: nada (o un aviso solo para administradores).
	 */
	public static function recogida_html( $aviso = true ) {
		$direccion = trim( (string) esmalto_ajuste( 'recogida_direccion' ) );
		$horario   = trim( (string) esmalto_ajuste( 'recogida_horario' ) );
		if ( '' === $direccion && '' === $horario ) {
			return ( $aviso && current_user_can( 'manage_woocommerce' ) )
				? '<p class="esm-placeholder">' . esc_html__( 'Dirección y horario de recogida pendientes: añádelos en Esmalto → Ajustes → Recogida en almacén. Este aviso solo lo ven los administradores.', 'esmalto-core' ) . '</p>'
				: '';
		}
		$html = '<div class="esm-recogida">';
		if ( '' !== $direccion ) {
			$html .= '<p><strong>' . esc_html__( 'Dirección:', 'esmalto-core' ) . '</strong> ' . nl2br( esc_html( $direccion ) ) . '</p>';
		}
		if ( '' !== $horario ) {
			$html .= '<p><strong>' . esc_html__( 'Horario:', 'esmalto-core' ) . '</strong> ' . nl2br( esc_html( $horario ) ) . '</p>';
		}
		return $html . '</div>';
	}

	public static function shortcode_recogida() {
		return self::recogida_html();
	}

	private static function es_recogida( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return false;
		}
		foreach ( $order->get_shipping_methods() as $metodo ) {
			if ( in_array( $metodo->get_method_id(), array( 'local_pickup', 'pickup_location' ), true ) ) {
				return true;
			}
		}
		return false;
	}

	public static function recogida_email( $order, $para_admin, $texto_plano ) {
		if ( $para_admin || ! self::es_recogida( $order ) ) {
			return;
		}
		$html = self::recogida_html( false );
		if ( '' === $html ) {
			return;
		}
		if ( $texto_plano ) {
			echo "\n" . esc_html__( 'Recogida en almacén', 'esmalto-core' ) . "\n" . wp_strip_all_tags( str_replace( array( '<br />', '</p>' ), "\n", $html ) ) . "\n";
			return;
		}
		echo '<h2>' . esc_html__( 'Recogida en almacén', 'esmalto-core' ) . '</h2>' . $html; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado en recogida_html().
	}

	public static function recogida_gracias( $order_id ) {
		$order = wc_get_order( $order_id );
		$html  = self::es_recogida( $order ) ? self::recogida_html( false ) : '';
		if ( '' !== $html ) {
			echo '<section class="esm-recogida-pedido"><h2>' . esc_html__( 'Recogida en almacén', 'esmalto-core' ) . '</h2>' . $html . '</section>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado en recogida_html().
		}
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
