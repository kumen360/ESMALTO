<?php
/**
 * Muestras de pago: «Solicitar una muestra» en la ficha añade a la cesta una muestra del color y formato
 * elegidos, que se paga como un pedido normal: 9,00 € + IVA, con la pieza y el envío incluidos
 * (condiciones generales, apartado 12). Una muestra por referencia.
 *
 * Un solo producto oculto («Muestra», ESM-MUESTRA) sirve para todas: cada línea de la cesta guarda de qué
 * variación es y toma su nombre y su foto; el pedido guarda su referencia. Se crea con Herramientas → 6.
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Muestras {

	const SKU    = 'ESM-MUESTRA';
	const PRECIO = '9'; // € sin IVA.
	const CLAVE  = 'esmalto_muestra'; // Parámetro del enlace y dato de la línea: ID de la variación.

	public static function init() {
		// Después de WholesaleX: si recibe un «no» añade su propio aviso de cantidad mínima.
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'validar' ), 20, 2 );
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'dato_cesta' ), 10, 2 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'desde_sesion' ) );
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'preparar_cesta' ), 5 );
		add_filter( 'woocommerce_add_to_cart_item_name_in_quotes', array( __CLASS__, 'nombre_aviso' ), 10, 2 );
		add_filter( 'woocommerce_add_to_cart_redirect', array( __CLASS__, 'volver' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'linea_pedido' ), 10, 3 );
		add_filter( 'woocommerce_package_rates', array( __CLASS__, 'sin_recogida' ), 10, 2 );
		add_action( 'template_redirect', array( __CLASS__, 'ficha_propia' ) );
	}

	public static function id() {
		return (int) wc_get_product_id_by_sku( self::SKU );
	}

	public static function es_muestra( $producto ) {
		return $producto instanceof WC_Product && self::SKU === $producto->get_sku( 'edit' );
	}

	/**
	 * ¿El paquete solo lleva muestras?
	 */
	public static function solo_muestras( $contenido ) {
		$hay = false;
		foreach ( (array) $contenido as $item ) {
			if ( ! self::es_muestra( $item['data'] ?? null ) ) {
				return false;
			}
			$hay = true;
		}
		return $hay;
	}

	/**
	 * «Muestra de Ambrossia · Greige, 100x100, Baldosa».
	 */
	public static function nombre( $variacion ) {
		$padre   = $variacion->get_parent_id() ? wc_get_product( $variacion->get_parent_id() ) : $variacion;
		$detalle = $variacion->is_type( 'variation' ) ? wc_get_formatted_variation( $variacion, true, false, false ) : '';
		/* translators: %s: nombre de la colección */
		return sprintf( __( 'Muestra de %s', 'esmalto-core' ), $padre ? $padre->get_name() : '' ) . ( $detalle ? ' · ' . $detalle : '' );
	}

	/**
	 * Variación (o producto simple) publicada de la que se pide la muestra, o null.
	 */
	private static function referencia( $id ) {
		$producto = $id ? wc_get_product( $id ) : null;
		if ( ! $producto || ! $producto->is_type( array( 'variation', 'simple' ) ) || self::es_muestra( $producto ) ) {
			return null;
		}
		$publicado = $producto->get_parent_id() ? get_post_status( $producto->get_parent_id() ) : $producto->get_status();
		return 'publish' === $publicado ? $producto : null;
	}

	private static function pedida() {
		return absint( wp_unslash( $_REQUEST[ self::CLAVE ] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification -- enlace de «añadir a la cesta», como los de WooCommerce.
	}

	public static function validar( $valido, $product_id ) {
		if ( ! $valido || (int) $product_id !== self::id() ) {
			return $valido;
		}
		$referencia = self::referencia( self::pedida() );
		if ( ! $referencia ) {
			wc_add_notice( __( 'Elige color y formato en la ficha del producto para pedir su muestra.', 'esmalto-core' ), 'error' );
			return false;
		}
		foreach ( WC()->cart ? WC()->cart->get_cart() : array() as $item ) {
			if ( (int) ( $item[ self::CLAVE ] ?? 0 ) === $referencia->get_id() ) {
				/* translators: %s: nombre de la muestra */
				wc_add_notice( sprintf( __( 'La %s ya está en tu cesta: se envía una por referencia.', 'esmalto-core' ), lcfirst( self::nombre( $referencia ) ) ), 'notice' );
				return false;
			}
		}
		return true;
	}

	public static function dato_cesta( $datos, $product_id ) {
		if ( (int) $product_id === self::id() && self::pedida() ) {
			$datos[ self::CLAVE ] = self::pedida();
		}
		return $datos;
	}

	/**
	 * La línea de la cesta muestra el nombre y la foto de la referencia.
	 */
	private static function vestir( $producto, $id ) {
		$referencia = self::referencia( $id );
		if ( ! $producto instanceof WC_Product || ! $referencia ) {
			return;
		}
		$producto->set_name( self::nombre( $referencia ) );
		$imagen = $referencia->get_image_id() ? $referencia->get_image_id() : get_post_thumbnail_id( $referencia->get_parent_id() );
		if ( $imagen ) {
			$producto->set_image_id( $imagen );
		}
	}

	public static function desde_sesion( $item ) {
		if ( ! empty( $item[ self::CLAVE ] ) ) {
			self::vestir( $item['data'] ?? null, (int) $item[ self::CLAVE ] );
		}
		return $item;
	}

	public static function preparar_cesta( $cesta ) {
		foreach ( $cesta->get_cart() as $item ) {
			if ( ! empty( $item[ self::CLAVE ] ) ) {
				self::vestir( $item['data'] ?? null, (int) $item[ self::CLAVE ] );
			}
		}
	}

	public static function nombre_aviso( $nombre, $product_id ) {
		$referencia = (int) $product_id === self::id() ? self::referencia( self::pedida() ) : null;
		return $referencia ? '&ldquo;' . esc_html( self::nombre( $referencia ) ) . '&rdquo;' : $nombre;
	}

	/**
	 * Tras añadir la muestra se vuelve a la ficha sin los parámetros (al recargar no se intenta otra vez).
	 */
	public static function volver( $url, $producto = null ) {
		if ( ! self::es_muestra( $producto ) || empty( $_SERVER['REQUEST_URI'] ) ) {
			return $url;
		}
		return remove_query_arg( array( 'add-to-cart', self::CLAVE ), home_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- remove_query_arg y wp_safe_redirect.
	}

	public static function linea_pedido( $linea, $clave, $valores ) {
		$referencia = empty( $valores[ self::CLAVE ] ) ? null : self::referencia( (int) $valores[ self::CLAVE ] );
		if ( ! $referencia ) {
			return;
		}
		$linea->set_name( self::nombre( $referencia ) );
		$linea->add_meta_data( __( 'Referencia', 'esmalto-core' ), $referencia->get_sku(), true );
		$linea->add_meta_data( '_esmalto_muestra', $referencia->get_id(), true );
	}

	/**
	 * Un pedido solo de muestras no se recoge en almacén: el envío va incluido.
	 */
	public static function sin_recogida( $tarifas, $paquete ) {
		if ( ! self::solo_muestras( $paquete['contents'] ?? array() ) ) {
			return $tarifas;
		}
		foreach ( $tarifas as $id => $tarifa ) {
			if ( in_array( $tarifa->get_method_id(), array( 'local_pickup', 'pickup_location' ), true ) ) {
				unset( $tarifas[ $id ] );
			}
		}
		return $tarifas;
	}

	/**
	 * La ficha del producto «Muestra» no se enseña: lleva a la página de Muestras.
	 */
	public static function ficha_propia() {
		if ( is_singular( 'product' ) && (int) get_queried_object_id() === self::id() ) {
			wp_safe_redirect( esmalto_url_pagina( 'muestras' ) );
			exit;
		}
	}

	/**
	 * Crea o actualiza el producto oculto «Muestra» (Herramientas → 6).
	 */
	public static function crear() {
		$id       = self::id();
		$producto = $id ? wc_get_product( $id ) : new WC_Product_Simple();
		if ( ! $id ) {
			$producto->set_sku( self::SKU );
		}
		$producto->set_name( __( 'Muestra', 'esmalto-core' ) );
		$producto->set_regular_price( self::PRECIO );
		$producto->set_status( 'publish' );
		$producto->set_catalog_visibility( 'hidden' );
		$producto->set_sold_individually( true );
		$producto->set_tax_status( 'taxable' );
		$producto->set_reviews_allowed( false );
		$producto->set_short_description( __( 'Muestra de una referencia: 9,00 € + IVA, con la pieza y el envío incluidos.', 'esmalto-core' ) );
		$id = $producto->save();
		update_post_meta( $id, '_yoast_wpseo_meta-robots-noindex', '1' );
		return sprintf( 'Muestras: producto oculto «Muestra» (%s) a 9,00 € + IVA; se añade desde «Solicitar una muestra» en cada ficha.', self::SKU );
	}
}
