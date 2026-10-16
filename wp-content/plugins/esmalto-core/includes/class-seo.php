<?php
/**
 * SEO con Yoast SEO: variables para la plantilla de las fichas, títulos y descripciones de las
 * páginas principales (repaso del 10/10, Anexo III) y mapa del sitio sin páginas de servicio.
 *
 * Plantilla de producto (Yoast → Ajustes → Tipos de contenido → Productos):
 *   %%title%%: porcelánico efecto %%esm_aspecto%% %%esm_acabado%% %%sep%% %%sitename%%
 *   Serie %%title%% en porcelánico %%esm_acabado%%, efecto %%esm_aspecto%%. %%esm_colores%% y %%esm_formatos%%. Consulta la ficha técnica y pide tu muestra.
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_SEO {

	const PLANTILLA_TITULO = '%%title%%: porcelánico efecto %%esm_aspecto%% %%esm_acabado%% %%sep%% %%sitename%%';
	const PLANTILLA_DESC   = 'Serie %%title%% en porcelánico %%esm_acabado%%, efecto %%esm_aspecto%%. %%esm_colores%% y %%esm_formatos%%. Consulta la ficha técnica y pide tu muestra.';

	public static function init() {
		add_action( 'wpseo_register_extra_replacements', array( __CLASS__, 'variables' ) );
		add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', array( __CLASS__, 'fuera_del_mapa' ) );
	}

	/**
	 * Títulos y descripciones de las páginas principales (slug => [título, descripción]).
	 */
	public static function paginas() {
		return array(
			'inicio'          => array( 'Esmalto | Porcelánico y cerámica para profesionales', 'Porcelánico de gran formato y cerámica para obra, reforma e interiorismo. Tarifa profesional, muestras y entrega en obra en toda la península.' ),
			'tienda'          => array( 'Catálogo de porcelánico y azulejos | Esmalto', 'Todas las colecciones de Esmalto: madera, mármol, piedra y cemento, de 30x60 a 120x120. Filtra por uso, familia y formato y pide tu muestra.' ),
			'profesionales'   => array( 'Tarifa profesional de cerámica | Esmalto', 'Cuenta profesional para reformistas, estudios y constructoras: precios netos, una persona de contacto y entrega en obra. Alta en 24–48 h.' ),
			'muestras'        => array( 'Muestras de porcelánico y azulejos | Esmalto', 'Pide una muestra antes de decidir: la pieza no se cobra, solo el transporte. Elige color y formato en la ficha del producto.' ),
			'gastos-de-envio' => array( 'Envíos y entrega en obra | Esmalto', 'Cómo calculamos el transporte: por peso en pedidos pequeños y por palé en los grandes. Entrega a domicilio o en obra y recogida en almacén.' ),
			'contacto'        => array( 'Contacto | Esmalto', 'Escríbenos o llámanos para presupuestos, información de producto, muestras o pedidos en curso.' ),
		);
	}

	/* ------------------------------------------------------------------ Variables */

	public static function variables() {
		wpseo_register_var_replacement( '%%esm_aspecto%%', array( __CLASS__, 'var_aspecto' ), 'advanced', __( 'Aspecto de la colección en minúsculas (madera, mármol, piedra, cemento…).', 'esmalto-core' ) );
		wpseo_register_var_replacement( '%%esm_acabado%%', array( __CLASS__, 'var_acabado' ), 'advanced', __( 'Acabado de la colección en minúsculas (mate, brillo…).', 'esmalto-core' ) );
		wpseo_register_var_replacement( '%%esm_colores%%', array( __CLASS__, 'var_colores' ), 'advanced', __( 'Número de colores: «4 colores», «1 color».', 'esmalto-core' ) );
		wpseo_register_var_replacement( '%%esm_formatos%%', array( __CLASS__, 'var_formatos' ), 'advanced', __( 'Número de formatos: «3 formatos», «1 formato».', 'esmalto-core' ) );
	}

	private static function producto( $args ) {
		$id = is_object( $args ) && isset( $args->ID ) ? (int) $args->ID : ( is_array( $args ) && isset( $args['ID'] ) ? (int) $args['ID'] : get_the_ID() );
		return ( $id && 'product' === get_post_type( $id ) ) ? wc_get_product( $id ) : null;
	}

	private static function terminos( $product, $taxonomia ) {
		$nombres = wc_get_product_terms( $product->get_id(), $taxonomia, array( 'fields' => 'names' ) );
		return is_wp_error( $nombres ) ? array() : $nombres;
	}

	/**
	 * «mate», «mate y brillo», «madera, piedra y cemento».
	 */
	private static function enumerar( $nombres ) {
		$nombres = array_map( 'mb_strtolower', array_values( array_filter( $nombres ) ) );
		$ultimo  = array_pop( $nombres );
		return $nombres ? implode( ', ', $nombres ) . ' y ' . $ultimo : (string) $ultimo;
	}

	public static function var_aspecto( $var, $args ) {
		$p = self::producto( $args );
		return $p ? self::enumerar( self::terminos( $p, 'pa_estilo' ) ) : '';
	}

	public static function var_acabado( $var, $args ) {
		$p = self::producto( $args );
		return $p ? self::enumerar( self::terminos( $p, 'pa_acabado' ) ) : '';
	}

	public static function var_colores( $var, $args ) {
		$p = self::producto( $args );
		$n = $p ? count( self::terminos( $p, 'pa_color' ) ) : 0;
		return $n ? sprintf( _n( '%d color', '%d colores', $n, 'esmalto-core' ), $n ) : '';
	}

	public static function var_formatos( $var, $args ) {
		$p = self::producto( $args );
		$n = $p ? count( self::terminos( $p, 'pa_formato' ) ) : 0;
		return $n ? sprintf( _n( '%d formato', '%d formatos', $n, 'esmalto-core' ), $n ) : '';
	}

	/* ------------------------------------------------------------------ Mapa del sitio */

	/**
	 * Cesta, pago, «Mi cuenta» (WooCommerce ya las marca noindex) y el alta de WholesaleX, que no se usa.
	 */
	public static function fuera_del_mapa( $ids ) {
		$ids = is_array( $ids ) ? $ids : array();
		if ( function_exists( 'wc_get_page_id' ) ) {
			foreach ( array( 'cart', 'checkout', 'myaccount' ) as $pagina ) {
				$ids[] = wc_get_page_id( $pagina );
			}
		}
		$wholesalex = get_page_by_path( 'wholesale-register' );
		if ( $wholesalex ) {
			$ids[] = $wholesalex->ID;
		}
		return array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );
	}

	/* ------------------------------------------------------------------ Aplicar */

	/**
	 * Esmalto → Herramientas → «Aplicar SEO»: separador «|», plantilla de las fichas, títulos y
	 * descripciones de las páginas principales y fuera los títulos fijos que traía la importación.
	 *
	 * @return array|WP_Error registro.
	 */
	public static function aplicar() {
		if ( ! defined( 'WPSEO_VERSION' ) ) {
			return new WP_Error( 'esmalto_seo', __( 'Yoast SEO no está activo.', 'esmalto-core' ) );
		}
		$log    = array();
		$titles = get_option( 'wpseo_titles', array() );
		$titles = is_array( $titles ) ? $titles : array();

		$titles['separator']        = 'sc-pipe';
		$titles['title-product']    = self::PLANTILLA_TITULO;
		$titles['metadesc-product'] = self::PLANTILLA_DESC;
		update_option( 'wpseo_titles', $titles );
		$log[] = 'Separador «|» y plantilla de título y descripción de las fichas de producto.';

		foreach ( self::paginas() as $slug => $datos ) {
			$pagina = get_page_by_path( $slug );
			if ( ! $pagina ) {
				$log[] = sprintf( 'Página «%s» no encontrada.', $slug );
				continue;
			}
			update_post_meta( $pagina->ID, '_yoast_wpseo_title', $datos[0] );
			update_post_meta( $pagina->ID, '_yoast_wpseo_metadesc', $datos[1] );
			$log[] = sprintf( '%s: título y descripción.', get_the_title( $pagina ) );
		}

		// Los títulos y descripciones fijos de la importación («X | Porcelánico Y | Esmalto») taparían la plantilla.
		$quitados = 0;
		foreach ( wc_get_products( array( 'limit' => -1, 'status' => array( 'publish', 'private', 'draft' ), 'return' => 'ids', 'type' => array( 'variable', 'simple' ) ) ) as $id ) {
			$titulo = (string) get_post_meta( $id, '_yoast_wpseo_title', true );
			if ( '' !== $titulo && preg_match( '/^.+ \| Porcel.+ \| Esmalto$/u', $titulo ) ) {
				delete_post_meta( $id, '_yoast_wpseo_title' );
				delete_post_meta( $id, '_yoast_wpseo_metadesc' );
				++$quitados;
			}
		}
		$log[] = sprintf( 'Títulos fijos de la importación quitados en %d productos (usan la plantilla).', $quitados );

		if ( class_exists( 'WPSEO_Sitemaps_Cache' ) ) {
			WPSEO_Sitemaps_Cache::clear();
		}
		return $log;
	}
}
