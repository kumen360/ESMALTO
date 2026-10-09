<?php
/**
 * Presentación de WooCommerce en el tema (la lógica de negocio está en esmalto-core).
 *
 * @package Esmalto
 */

defined( 'ABSPATH' ) || exit;

/**
 * La página «Tienda» se edita con bloques (patrón «Introducción de la tienda»): ruta, título,
 * texto y barra «Uso / Ordenar». Se muestra a todo el ancho, encima de los filtros y del listado.
 */
add_action( 'init', 'esmalto_woo_hooks' );
function esmalto_woo_hooks() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
	// El alta de clientes ya pide aceptar la política de privacidad con su casilla.
	remove_action( 'woocommerce_register_form', 'wc_registration_privacy_policy_text', 20 );
	add_action( 'astra_content_before', 'esmalto_shop_intro' );
	add_action( 'woocommerce_before_shop_loop', 'esmalto_barra_en_intro', 1 );
	add_action( 'woocommerce_before_main_content', 'esmalto_sin_ruta_en_tienda', 1 );
	// Ruta de la ficha a todo el ancho, encima de la galería.
	add_action( 'woocommerce_before_single_product', 'woocommerce_breadcrumb', 5 );
	add_shortcode( 'esmalto_ordenar', 'esmalto_ordenar' );
}

function esmalto_es_tienda() {
	return function_exists( 'is_shop' ) && is_shop() && ! is_search();
}

function esmalto_shop_intro() {
	if ( ! esmalto_es_tienda() ) {
		return;
	}
	$page = get_post( wc_get_page_id( 'shop' ) );
	if ( $page && '' !== trim( $page->post_content ) ) {
		echo '<div class="esmalto-intro-tienda"><div class="esmalto-intro-tienda__in">' . do_shortcode( do_blocks( $page->post_content ) ) . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- contenido de bloques del editor.
	}
}

/**
 * La tienda ya muestra su ruta en la introducción.
 */
function esmalto_sin_ruta_en_tienda() {
	if ( esmalto_es_tienda() ) {
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	}
}

/**
 * En la tienda, «Ordenar» va en la barra de la introducción ([esmalto_ordenar]) y no hay recuento.
 */
function esmalto_barra_en_intro() {
	if ( esmalto_es_tienda() ) {
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	}
}

function esmalto_ordenar() {
	if ( ! function_exists( 'woocommerce_catalog_ordering' ) || ! ( is_shop() || is_product_taxonomy() ) ) {
		return '';
	}
	if ( ! isset( $GLOBALS['woocommerce_loop'] ) ) {
		wc_setup_loop();
	}
	ob_start();
	woocommerce_catalog_ordering();
	$select = ob_get_clean();
	return $select ? '<div class="esm-ordenar"><span class="esm-ordenar__etiqueta">' . esc_html__( 'Ordenar', 'esmalto' ) . '</span>' . $select . '</div>' : '';
}

add_filter( 'woocommerce_show_page_title', 'esmalto_shop_title' );
function esmalto_shop_title( $show ) {
	return esmalto_es_tienda() ? false : $show;
}

add_filter( 'woocommerce_breadcrumb_defaults', 'esmalto_breadcrumb_defaults' );
function esmalto_breadcrumb_defaults( $defaults ) {
	$defaults['delimiter'] = ' / ';
	$defaults['home']      = __( 'Inicio', 'esmalto' );
	return $defaults;
}

/**
 * Ruta corta como en el diseño: Inicio / Catálogo / colección.
 */
add_filter( 'woocommerce_get_breadcrumb', 'esmalto_migas' );
function esmalto_migas( $crumbs ) {
	if ( count( $crumbs ) > 1 && ( is_product() || is_product_taxonomy() ) ) {
		return array(
			$crumbs[0],
			array( __( 'Catálogo', 'esmalto' ), wc_get_page_permalink( 'shop' ) ),
			end( $crumbs ),
		);
	}
	return $crumbs;
}

/**
 * Marca como activos el enlace del menú y el chip de «Uso» que corresponden a la URL actual.
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

		var actual = new URLSearchParams( window.location.search ).get( 'filter_espacio' ) || '';
		document.querySelectorAll( '.esm-chips-espacio .wp-block-button__link' ).forEach( function ( a ) {
			var valor;
			try { valor = new URL( a.getAttribute( 'href' ) || '', window.location.origin ).searchParams.get( 'filter_espacio' ) || ''; } catch ( e ) { return; }
			if ( valor === actual && ( valor || document.body.classList.contains( 'woocommerce-shop' ) ) ) {
				a.parentNode.classList.add( 'is-activo' );
				a.setAttribute( 'aria-current', 'page' );
			}
		} );
	} )();
	</script>
	<?php
}
