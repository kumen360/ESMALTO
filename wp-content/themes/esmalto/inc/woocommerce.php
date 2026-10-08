<?php
/**
 * Presentación de WooCommerce en el tema (la lógica de negocio está en esmalto-core).
 *
 * @package Esmalto
 */

defined( 'ABSPATH' ) || exit;

/**
 * La página «Tienda» se edita con bloques (patrón «Introducción de la tienda»)
 * y se muestra encima del listado de productos.
 */
add_action( 'init', 'esmalto_woo_hooks' );
function esmalto_woo_hooks() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
	add_action( 'woocommerce_archive_description', 'esmalto_shop_intro', 10 );
}

function esmalto_shop_intro() {
	if ( ! is_shop() || is_search() || is_paged() ) {
		return;
	}
	$page = get_post( wc_get_page_id( 'shop' ) );
	if ( $page && '' !== trim( $page->post_content ) ) {
		echo '<div class="esmalto-intro-tienda">' . do_blocks( $page->post_content ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- contenido de bloques del editor.
	}
}

add_filter( 'woocommerce_show_page_title', 'esmalto_shop_title' );
function esmalto_shop_title( $show ) {
	return ( function_exists( 'is_shop' ) && is_shop() && ! is_search() ) ? false : $show;
}

add_filter( 'woocommerce_breadcrumb_defaults', 'esmalto_breadcrumb_defaults' );
function esmalto_breadcrumb_defaults( $defaults ) {
	$defaults['delimiter'] = ' / ';
	$defaults['home']      = __( 'Inicio', 'esmalto' );
	return $defaults;
}

/**
 * Marca como activo el enlace del menú de la cabecera que corresponde a la URL actual.
 */
add_action( 'wp_footer', 'esmalto_menu_activo', 99 );
function esmalto_menu_activo() {
	?>
	<script>
	( function () {
		var path = window.location.pathname.replace( /\/+$/, '/' );
		var best = null, bestLen = 0;
		document.querySelectorAll( '.esm-menu .wp-block-navigation-item a' ).forEach( function ( a ) {
			var href = a.getAttribute( 'href' ) || '';
			try { href = new URL( href, window.location.origin ).pathname; } catch ( e ) { return; }
			var match = href === '/' ? path === '/' : path.indexOf( href ) === 0;
			if ( match && href.length > bestLen ) { best = a; bestLen = href.length; }
		} );
		if ( ! best && ( document.body.classList.contains( 'woocommerce' ) || document.body.classList.contains( 'single-product' ) ) ) {
			best = document.querySelector( '.esm-menu a[href$="/tienda/"]' );
		}
		if ( best ) { best.parentNode.classList.add( 'is-activo' ); best.setAttribute( 'aria-current', 'page' ); }
	} )();
	</script>
	<?php
}
