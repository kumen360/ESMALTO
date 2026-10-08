<?php
/**
 * Integración con Astra (gratis):
 * - Sustituye la cabecera/pie de Astra por las partes de plantilla de bloques del tema.
 * - Páginas de contenido a ancho completo y sin título (las secciones son bloques).
 * - Barra lateral de filtros en la tienda, sin barra en fichas y páginas.
 *
 * @package Esmalto
 */

defined( 'ABSPATH' ) || exit;

add_action( 'template_redirect', 'esmalto_astra_header_footer', 999 );
function esmalto_astra_header_footer() {
	remove_all_actions( 'astra_header' );
	remove_all_actions( 'astra_footer' );
	add_action( 'astra_header', 'esmalto_render_header' );
	add_action( 'astra_footer', 'esmalto_render_footer' );
}

function esmalto_render_header() {
	echo '<div id="esmalto-cabecera" class="esmalto-cabecera">';
	block_template_part( 'header' );
	echo '</div>';
}

function esmalto_render_footer() {
	echo '<div class="esmalto-pie">';
	block_template_part( 'footer' );
	echo '</div>';
}

/**
 * Páginas construidas con bloques (todas salvo cesta y pago, que usan el título de Astra).
 */
function esmalto_is_block_page() {
	if ( ! is_page() ) {
		return false;
	}
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) {
		return false;
	}
	return true;
}

function esmalto_is_shop_archive() {
	return function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() );
}

add_filter( 'astra_get_content_layout', 'esmalto_content_layout', 99 );
function esmalto_content_layout( $layout ) {
	return esmalto_is_block_page() ? 'page-builder' : $layout;
}

add_filter( 'astra_page_layout', 'esmalto_sidebar_layout', 99 );
function esmalto_sidebar_layout( $layout ) {
	if ( esmalto_is_shop_archive() ) {
		return 'left-sidebar';
	}
	if ( is_page() || is_singular( 'product' ) ) {
		return 'no-sidebar';
	}
	return $layout;
}

add_filter( 'astra_the_title_enabled', 'esmalto_title_enabled', 99 );
function esmalto_title_enabled( $enabled ) {
	return esmalto_is_block_page() ? false : $enabled;
}

add_filter( 'body_class', 'esmalto_body_class' );
function esmalto_body_class( $classes ) {
	$classes[] = 'esmalto';
	if ( esmalto_is_block_page() ) {
		$classes[] = 'esmalto-bloques';
	}
	return $classes;
}

/**
 * Valores por defecto de Astra (solo se aplican si no se han cambiado en el Personalizador).
 */
add_filter( 'astra_theme_defaults', 'esmalto_astra_defaults' );
function esmalto_astra_defaults( $defaults ) {
	$defaults['site-content-width']   = 1280;
	$defaults['shop-grid']            = array(
		'desktop' => 3,
		'tablet'  => 2,
		'mobile'  => 1,
	);
	$defaults['shop-no-of-products']  = 12;
	$defaults['shop-product-structure'] = array( 'title', 'price' );
	$defaults['single-product-breadcrumb-disable'] = false;
	return $defaults;
}

/**
 * WooCommerce inserta automáticamente su bloque «Mi cuenta» en las cabeceras de bloques.
 * El diseño ya tiene el botón «Clientes», así que se retira.
 */
add_filter( 'hooked_block_types', 'esmalto_sin_bloques_insertados', 20, 4 );
function esmalto_sin_bloques_insertados( $hooked, $posicion, $ancla, $contexto ) {
	if ( $contexto instanceof WP_Block_Template && 'wp_template_part' === $contexto->type && in_array( $contexto->slug, array( 'header', 'footer' ), true ) ) {
		return array_values( array_diff( $hooked, array( 'woocommerce/customer-account', 'woocommerce/mini-cart' ) ) );
	}
	return $hooked;
}
