<?php
/**
 * Métodos de envío de Esmalto (se añaden en WooCommerce → Ajustes → Envío → zona):
 * - «Porte por m² y palé»: el que se usa desde el 25/10/2026 (2,50 €/m² + 9 €/palé, revisable).
 * - «Envío por peso»: tramos de kg y coste por palé (se mantiene por compatibilidad).
 * También: datos de recogida en almacén y plazo de entrega en correos y confirmación.
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
		// Plazo de entrega (Esmalto → Ajustes) en los pedidos con entrega.
		add_action( 'woocommerce_email_order_details', array( __CLASS__, 'plazo_email' ), 26, 3 );
		add_action( 'woocommerce_thankyou', array( __CLASS__, 'plazo_gracias' ), 6 );
	}

	private static function plazo_pedido( $order ) {
		if ( ! $order instanceof WC_Order || ! $order->get_shipping_methods() || self::es_recogida( $order ) ) {
			return '';
		}
		return trim( (string) esmalto_ajuste( 'plazo_entrega' ) );
	}

	public static function plazo_email( $order, $para_admin, $texto_plano ) {
		$texto = $para_admin ? '' : self::plazo_pedido( $order );
		if ( '' === $texto ) {
			return;
		}
		if ( $texto_plano ) {
			echo "\n" . esc_html__( 'Plazo de entrega', 'esmalto-core' ) . "\n" . esc_html( $texto ) . "\n";
			return;
		}
		echo '<h2>' . esc_html__( 'Plazo de entrega', 'esmalto-core' ) . '</h2><p>' . esc_html( $texto ) . '</p>';
	}

	public static function plazo_gracias( $order_id ) {
		$texto = self::plazo_pedido( wc_get_order( $order_id ) );
		if ( '' !== $texto ) {
			echo '<section class="esm-plazo-pedido"><h2>' . esc_html__( 'Plazo de entrega', 'esmalto-core' ) . '</h2><p>' . esc_html( $texto ) . '</p></section>';
		}
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
		require_once ESMALTO_CORE_DIR . 'includes/class-envio-m2.php';
	}

	public static function registrar( $metodos ) {
		$metodos['esmalto_m2']   = 'Esmalto_Envio_M2';
		$metodos['esmalto_peso'] = 'Esmalto_Envio_Peso';
		return $metodos;
	}

	/**
	 * m², peso y palés de un paquete. Cada referencia ocupa cajas / cajas por palé de un palé;
	 * sin datos de palé se estima por peso (1.000 kg por palé). Con algo que enviar, mínimo 1 palé.
	 */
	public static function medir( $contenido ) {
		$m2        = 0.0;
		$kg        = 0.0;
		$ocupacion = 0.0;
		$hay       = false;
		foreach ( $contenido as $item ) {
			$producto = $item['data'] ?? null;
			if ( ! $producto instanceof WC_Product || ! $producto->needs_shipping() ) {
				continue;
			}
			$hay    = true;
			$cajas  = (int) $item['quantity'];
			$e      = esmalto_embalaje( $producto->get_id() );
			$peso   = (float) $producto->get_weight() * $cajas;
			$m2    += $e['m2_caja'] * $cajas;
			$kg    += $peso;
			$ocupacion += $e['cajas_palet'] > 0 ? $cajas / $e['cajas_palet'] : $peso / 1000;
		}
		return array(
			'm2'     => $m2,
			'kg'     => $kg,
			'palets' => $hay ? max( 1, (int) ceil( $ocupacion - 1e-9 ) ) : 0,
		);
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
