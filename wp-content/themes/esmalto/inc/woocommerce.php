<?php
/**
 * Presentación de WooCommerce en el tema (la lógica de negocio está en esmalto-core).
 *
 * @package Esmalto
 */

defined( 'ABSPATH' ) || exit;

/**
 * La página «Tienda» se edita con bloques (patrón «Introducción de la tienda»): ruta, título,
 * texto y barra «Uso / Formato / Ordenar». Se muestra a todo el ancho, encima de los filtros y del
 * listado. Las categorías con página propia usan la misma barra con su título y su texto.
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

/**
 * Categoría del catálogo con página propia (las crea Esmalto Core y llevan «esmalto_grupo»).
 * Las demás categorías de producto se muestran como siempre.
 */
function esmalto_categoria_con_pagina() {
	if ( ! function_exists( 'is_product_category' ) || ! is_product_category() ) {
		return null;
	}
	$term = get_queried_object();
	return ( $term instanceof WP_Term && '' !== (string) get_term_meta( $term->term_id, 'esmalto_grupo', true ) ) ? $term : null;
}

/**
 * Tienda o categoría con página propia: llevan la introducción a todo el ancho.
 */
function esmalto_con_intro() {
	return esmalto_es_tienda() || null !== esmalto_categoria_con_pagina();
}

function esmalto_shop_intro() {
	$page = function_exists( 'wc_get_page_id' ) ? get_post( wc_get_page_id( 'shop' ) ) : null;
	if ( esmalto_es_tienda() ) {
		if ( $page && '' !== trim( $page->post_content ) ) {
			echo '<div class="esmalto-intro-tienda"><div class="esmalto-intro-tienda__in">' . do_shortcode( do_blocks( $page->post_content ) ) . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- contenido de bloques del editor.
		}
		return;
	}
	$term = esmalto_categoria_con_pagina();
	if ( ! $term ) {
		return;
	}
	// Ruta, título y texto de la categoría; debajo, la misma barra «Uso / Formato / Ordenar» de la página Tienda.
	$titulo = (string) get_term_meta( $term->term_id, 'esmalto_h1', true );
	$html   = '<div class="wp-block-group esm-intro-tienda">';
	$html  .= '<p class="is-style-miga"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Inicio', 'esmalto' ) . '</a> / <a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'Catálogo', 'esmalto' ) . '</a> / ' . esc_html( $term->name ) . '</p>';
	$html  .= '<h1 class="wp-block-heading" style="margin-top:12px;margin-bottom:0">' . esc_html( '' !== $titulo ? $titulo : $term->name ) . '</h1>';
	if ( '' !== trim( $term->description ) ) {
		$html .= '<p class="esm-intro-categoria has-texto-apagado-color has-text-color" style="margin-top:12px;margin-bottom:0">' . esc_html( $term->description ) . '</p>';
	}
	$html .= esmalto_barra_de_la_tienda( $page ) . '</div>';
	echo '<div class="esmalto-intro-tienda"><div class="esmalto-intro-tienda__in">' . $html . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado arriba; la barra son bloques del editor.
}

/**
 * Bloque «esm-barra-tienda» de la página Tienda (se edita allí y se reutiliza en las categorías).
 */
function esmalto_barra_de_la_tienda( $page ) {
	if ( ! $page ) {
		return '';
	}
	$buscar = static function ( $bloques ) use ( &$buscar ) {
		foreach ( $bloques as $bloque ) {
			if ( false !== strpos( (string) ( $bloque['attrs']['className'] ?? '' ), 'esm-barra-tienda' ) ) {
				return $bloque;
			}
			$dentro = $buscar( $bloque['innerBlocks'] ?? array() );
			if ( $dentro ) {
				return $dentro;
			}
		}
		return null;
	};
	$barra = $buscar( parse_blocks( $page->post_content ) );
	return $barra ? do_shortcode( render_block( $barra ) ) : '';
}

/**
 * La tienda y las categorías con página propia ya muestran su ruta, título y texto en la introducción.
 */
function esmalto_sin_ruta_en_tienda() {
	if ( esmalto_con_intro() ) {
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
	}
}

/**
 * «Ordenar» va en la barra de la introducción ([esmalto_ordenar]) y no hay recuento.
 */
function esmalto_barra_en_intro() {
	if ( esmalto_con_intro() ) {
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
	return esmalto_con_intro() ? false : $show;
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
 * Marca como activos el enlace del menú y los chips de «Uso» y «Formato» que corresponden a la URL
 * actual, y hace que «Filtros» (móvil) abra y cierre la barra de filtros.
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

		// Chip activo: misma ruta y mismos filtros (filter_*). «Todos» (sin filtros) si su fila no filtra.
		var params = new URLSearchParams( window.location.search );
		var conjunto = function ( v ) { return ( v || '' ).split( ',' ).filter( Boolean ).sort().join( ',' ); };
		document.querySelectorAll( '.esm-chips-espacio, .esm-chips-formato' ).forEach( function ( grupo ) {
			var propios = grupo.classList.contains( 'esm-chips-formato' ) ? [ 'filter_formato' ] : [ 'filter_espacio', 'filter_ubicacion' ];
			grupo.querySelectorAll( '.wp-block-button__link' ).forEach( function ( a ) {
				var u;
				try { u = new URL( a.getAttribute( 'href' ) || '', window.location.origin ); } catch ( e ) { return; }
				if ( u.pathname.replace( /\/+$/, '/' ) !== path ) { return; }
				var claves = [];
				u.searchParams.forEach( function ( v, k ) { if ( 0 === k.indexOf( 'filter_' ) ) { claves.push( k ); } } );
				var activo = claves.length
					? claves.every( function ( k ) { return conjunto( u.searchParams.get( k ) ) === conjunto( params.get( k ) ); } )
					: ! propios.some( function ( k ) { return params.has( k ); } );
				if ( activo ) {
					a.parentNode.classList.add( 'is-activo' );
					a.setAttribute( 'aria-current', 'page' );
				}
			} );
		} );

		// «Filtros» (móvil): abre y cierra la barra de filtros, que se coloca encima del listado.
		var lateral = document.getElementById( 'secondary' );
		var activos = document.querySelectorAll( '.widget_layered_nav_filters li.chosen' ).length;
		document.querySelectorAll( '.esm-boton-filtros .wp-block-button__link' ).forEach( function ( a ) {
			if ( activos ) { a.textContent = a.textContent.replace( /\s*\(\d+\)$/, '' ) + ' (' + activos + ')'; }
			if ( ! lateral ) { return; }
			a.setAttribute( 'aria-controls', 'secondary' );
			a.setAttribute( 'aria-expanded', 'false' );
			a.addEventListener( 'click', function ( e ) {
				if ( ! window.matchMedia( '(max-width: 921px)' ).matches ) { return; }
				e.preventDefault();
				var abierto = document.body.classList.toggle( 'esm-filtros-abiertos' );
				a.setAttribute( 'aria-expanded', abierto ? 'true' : 'false' );
				if ( abierto ) { window.scrollTo( { top: lateral.getBoundingClientRect().top + window.scrollY - 96, behavior: 'smooth' } ); }
			} );
		} );
	} )();
	</script>
	<?php
}
