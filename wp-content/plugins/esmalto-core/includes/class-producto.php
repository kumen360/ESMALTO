<?php
/**
 * Presentación de producto: precio por m², modo «Solicitar presupuesto», especificaciones,
 * botones de presupuesto/muestra/ficha, listados y textos.
 *
 * La unidad de venta es la CAJA: el precio de WooCommerce es €/caja y aquí se muestra también €/m².
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Producto {

	public static function init() {
		add_filter( 'woocommerce_variation_is_visible', array( __CLASS__, 'variacion_visible' ), 10, 2 );
		add_filter( 'woocommerce_ajax_variation_threshold', array( __CLASS__, 'umbral_variaciones' ) );
		add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'precio_html' ), 20, 2 );

		add_filter( 'woocommerce_catalog_orderby', array( __CLASS__, 'ordenacion' ) );
		add_filter( 'woocommerce_default_catalog_orderby_options', array( __CLASS__, 'ordenacion' ) );

		if ( defined( 'ASTRA_THEME_VERSION' ) ) {
			add_action( 'astra_woo_shop_title_after', array( __CLASS__, 'meta_listado' ) );
		} else {
			add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'meta_listado' ), 5 );
		}
		add_action( 'woocommerce_before_shop_loop_item_title', array( __CLASS__, 'insignia' ), 9 );
		add_filter( 'woocommerce_sale_flash', array( __CLASS__, 'insignia_oferta' ) );
		add_filter( 'woocommerce_product_add_to_cart_text', array( __CLASS__, 'texto_boton_listado' ), 10, 2 );

		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'antetitulo' ), 4 );
		add_action( 'woocommerce_before_add_to_cart_form', array( __CLASS__, 'bloque_precio' ), 5 );
		add_action( 'woocommerce_before_add_to_cart_form', array( __CLASS__, 'especificaciones' ), 10 );
		add_action( 'woocommerce_after_add_to_cart_button', array( __CLASS__, 'boton_presupuesto' ) );
		add_action( 'woocommerce_after_add_to_cart_form', array( __CLASS__, 'acciones' ), 5 );
		add_action( 'woocommerce_after_add_to_cart_form', array( __CLASS__, 'aviso' ), 30 );
		add_filter( 'woocommerce_product_single_add_to_cart_text', array( __CLASS__, 'texto_anadir' ) );
		add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'pestanas' ), 98 );
		add_filter( 'woocommerce_product_related_products_heading', array( __CLASS__, 'titulo_relacionados' ) );
		add_filter( 'woocommerce_output_related_products_args', array( __CLASS__, 'args_relacionados' ) );

		add_filter( 'gettext_woocommerce', array( __CLASS__, 'textos' ), 10, 3 );
	}

	/* ------------------------------------------------------------------ Precio */

	/**
	 * Las variaciones sin precio se muestran igualmente (para elegir y pedir presupuesto).
	 */
	public static function variacion_visible( $visible, $variation_id ) {
		return 'publish' === get_post_status( $variation_id ) ? true : $visible;
	}

	public static function umbral_variaciones() {
		return 150;
	}

	/**
	 * Precio mínimo por m² (precio de visualización de cada caja / m² por caja).
	 */
	public static function precio_m2_minimo( $product ) {
		if ( ! $product instanceof WC_Product ) {
			return null;
		}
		if ( $product->is_type( 'variable' ) ) {
			$precios = $product->get_variation_prices( true );
			$minimo  = null;
			foreach ( $precios['price'] as $variation_id => $precio ) {
				$m2 = esmalto_num( get_post_meta( $variation_id, '_m2_por_caja', true ) );
				if ( $m2 <= 0 || '' === $precio || (float) $precio <= 0 ) {
					continue;
				}
				$ppm = (float) $precio / $m2;
				if ( null === $minimo || $ppm < $minimo ) {
					$minimo = $ppm;
				}
			}
			return $minimo;
		}
		$m2     = esmalto_num( get_post_meta( $product->get_id(), '_m2_por_caja', true ) );
		$precio = '' === $product->get_price() ? 0 : wc_get_price_to_display( $product );
		return ( $m2 > 0 && $precio > 0 ) ? $precio / $m2 : null;
	}

	public static function precio_html( $html, $product ) {
		if ( ( is_admin() && ! wp_doing_ajax() ) || ! $product instanceof WC_Product || $product->is_type( 'variation' ) ) {
			return $html;
		}
		$minimo = self::precio_m2_minimo( $product );
		if ( null === $minimo ) {
			return '' === trim( wp_strip_all_tags( (string) $html ) )
				? '<span class="esm-precio-consultar">' . esc_html__( 'Precio a consultar', 'esmalto-core' ) . '</span>'
				: $html;
		}
		$texto = sprintf(
			/* translators: %s: precio por m² */
			__( 'Desde %s', 'esmalto-core' ),
			wc_price( $minimo ) . '/m²'
		);
		if ( Esmalto_B2B::es_profesional() ) {
			$texto .= ' <small class="esm-precio-iva">' . esc_html__( 'sin IVA', 'esmalto-core' ) . '</small>';
		}
		return $texto;
	}

	/* ------------------------------------------------------------------ Listados */

	public static function ordenacion( $opciones ) {
		return array(
			'menu_order' => __( 'Relevancia', 'esmalto-core' ),
			'popularity' => __( 'Más vendidos', 'esmalto-core' ),
			'date'       => __( 'Novedades', 'esmalto-core' ),
			'price'      => __( 'Precio más bajo', 'esmalto-core' ),
			'price-desc' => __( 'Precio más alto', 'esmalto-core' ),
		);
	}

	public static function meta_listado() {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$familia  = $product->get_attribute( 'pa_familia' );
		$estilo   = $product->get_attribute( 'pa_estilo' );
		$formatos = wc_get_product_terms( $product->get_id(), 'pa_formato', array( 'fields' => 'names' ) );
		$formatos = is_wp_error( $formatos ) ? array() : $formatos;
		$derecha  = 1 === count( $formatos ) ? $formatos[0] : sprintf( _n( '%d formato', '%d formatos', count( $formatos ), 'esmalto-core' ), count( $formatos ) );
		printf(
			'<div class="esm-loop-meta"><span>%s</span><span class="esm-loop-meta__formatos">%s</span></div>',
			esc_html( implode( ' · ', array_filter( array( $familia, $estilo ) ) ) ),
			$formatos ? esc_html( $derecha ) : ''
		);
	}

	public static function insignia() {
		global $product;
		if ( $product instanceof WC_Product && $product->is_featured() && ! $product->is_on_sale() ) {
			echo '<span class="esm-insignia esm-insignia--novedad">' . esc_html__( 'Novedad', 'esmalto-core' ) . '</span>';
		}
	}

	public static function insignia_oferta() {
		return '<span class="onsale esm-insignia">' . esc_html__( 'Oferta', 'esmalto-core' ) . '</span>';
	}

	public static function texto_boton_listado( $texto, $product ) {
		return ( $product instanceof WC_Product && $product->is_type( 'variable' ) ) ? __( 'Ver colección', 'esmalto-core' ) : $texto;
	}

	/* ------------------------------------------------------------------ Ficha */

	public static function estilo_texto( $estilo ) {
		$mapa = array(
			'Madera'  => __( 'Efecto madera', 'esmalto-core' ),
			'Mármol'  => __( 'Efecto mármol', 'esmalto-core' ),
			'Piedra'  => __( 'Efecto piedra', 'esmalto-core' ),
			'Cemento' => __( 'Efecto cemento', 'esmalto-core' ),
			'Liso'    => __( 'Color liso', 'esmalto-core' ),
		);
		return $mapa[ $estilo ] ?? $estilo;
	}

	public static function antetitulo() {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$familia = $product->get_attribute( 'pa_familia' );
		$estilo  = $product->get_attribute( 'pa_estilo' );
		$partes  = array_filter( array( $familia, $estilo ? self::estilo_texto( $estilo ) : '' ) );
		if ( $partes ) {
			echo '<p class="esm-producto-familia">' . esc_html( implode( ' · ', $partes ) ) . '</p>';
		}
	}

	public static function bloque_precio() {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$b2b       = Esmalto_B2B::es_profesional();
		$minimo    = self::precio_m2_minimo( $product );
		$categoria = Esmalto_B2B::categoria();
		if ( null === $minimo ) {
			$principal = __( 'Precio a consultar', 'esmalto-core' );
			$nota      = __( 'Pide presupuesto sin compromiso: te respondemos en 24 h.', 'esmalto-core' );
		} else {
			$principal = sprintf( __( 'Desde %s', 'esmalto-core' ), wc_price( $minimo ) . '/m²' );
			$nota      = $b2b ? __( 'Precios sin IVA · portes no incluidos', 'esmalto-core' ) : __( 'IVA incluido · portes no incluidos', 'esmalto-core' );
		}
		?>
		<div class="esm-precio" data-esm-precio>
			<div>
				<div class="esm-precio__principal" data-esm-principal><?php echo wp_kses_post( $principal ); ?></div>
				<div class="esm-precio__nota" data-esm-nota><?php echo esc_html( $nota ); ?></div>
				<?php if ( $categoria ) : ?>
					<span class="esm-precio__categoria"><?php echo esc_html( sprintf( __( 'Tarifa profesional · Categoría %1$s (−%2$s %%)', 'esmalto-core' ), $categoria, wc_format_localized_decimal( Esmalto_B2B::descuento() ) ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="esm-precio__lineas" data-esm-lineas></div>
		</div>
		<?php
	}

	private static function antideslizante( $product ) {
		$ad      = $product->get_attribute( 'pa_antideslizante' );
		$version = $product->get_attribute( 'version-antideslizante' );
		if ( 'Sí' === $ad ) {
			return __( 'Sí', 'esmalto-core' );
		}
		if ( 'Sí' === $version ) {
			return __( 'Versión antideslizante disponible', 'esmalto-core' );
		}
		return $ad;
	}

	public static function especificaciones() {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$lista = static function ( $valor ) {
			return str_replace( ', ', ' / ', (string) $valor );
		};
		$filas = array(
			'material'        => array( __( 'Material', 'esmalto-core' ), $lista( $product->get_attribute( 'pa_material' ) ) ),
			'formatos'        => array( __( 'Formatos (cm)', 'esmalto-core' ), $lista( $product->get_attribute( 'pa_formato' ) ) ),
			'acabado'         => array( __( 'Acabado', 'esmalto-core' ), $product->get_attribute( 'pa_acabado' ) ),
			'colores'         => array( __( 'Colores', 'esmalto-core' ), $lista( $product->get_attribute( 'pa_color' ) ) ),
			'uso'             => array( __( 'Uso', 'esmalto-core' ), implode( ' · ', array_filter( array( $lista( $product->get_attribute( 'pa_uso' ) ), $lista( $product->get_attribute( 'pa_ubicacion' ) ) ) ) ) ),
			'espacios'        => array( __( 'Espacios', 'esmalto-core' ), $product->get_attribute( 'pa_espacio' ) ),
			'rectificado'     => array( __( 'Rectificado', 'esmalto-core' ), $product->get_attribute( 'rectificado' ) ),
			'espesor'         => array( __( 'Espesor', 'esmalto-core' ), $lista( $product->get_attribute( 'pa_espesor' ) ) ),
			'destonificacion' => array( __( 'Destonificación', 'esmalto-core' ), $product->get_attribute( 'destonificacion' ) ),
			'antideslizante'  => array( __( 'Antideslizante', 'esmalto-core' ), self::antideslizante( $product ) ),
			'embalaje'        => array( __( 'Embalaje', 'esmalto-core' ), '' ),
		);
		echo '<div class="esm-specs"><div class="esm-specs__titulo">' . esc_html__( 'Especificaciones técnicas:', 'esmalto-core' ) . '</div><ul>';
		foreach ( $filas as $clave => $fila ) {
			$oculto = '' === trim( (string) $fila[1] ) ? ' hidden' : '';
			printf(
				'<li data-esm-fila="%1$s"%2$s><strong>%3$s:</strong> <span data-esm-spec="%1$s">%4$s</span></li>',
				esc_attr( $clave ),
				$oculto, // phpcs:ignore WordPress.Security.EscapeOutput
				esc_html( $fila[0] ),
				esc_html( $fila[1] )
			);
		}
		echo '</ul></div>';
	}

	private static function nombre_para_formulario( $product ) {
		return $product->get_name() . ' (' . $product->get_sku() . ')';
	}

	public static function boton_presupuesto() {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$url = add_query_arg( 'producto', rawurlencode( self::nombre_para_formulario( $product ) ), esmalto_url_pagina( 'presupuesto' ) );
		printf(
			'<a class="button esm-boton-secundario esm-boton-presupuesto" data-esm-presupuesto href="%s">%s</a>',
			esc_url( $url . '#formulario' ),
			esc_html__( 'Solicitar presupuesto', 'esmalto-core' )
		);
	}

	public static function acciones() {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$muestra = add_query_arg( 'producto', rawurlencode( self::nombre_para_formulario( $product ) ), esmalto_url_pagina( 'muestras' ) );
		$ficha   = (int) get_post_meta( $product->get_id(), '_ficha_tecnica_id', true );
		$url_pdf = $ficha ? wp_get_attachment_url( $ficha ) : '';
		echo '<div class="esm-acciones">';
		printf( '<a class="button esm-boton-muestra" data-esm-muestra href="%s">%s</a>', esc_url( $muestra . '#solicitud' ), esc_html__( 'Solicitar una muestra', 'esmalto-core' ) );
		if ( $url_pdf ) {
			printf( '<a class="button esm-boton-ficha" href="%s" target="_blank" rel="noopener">%s</a>', esc_url( $url_pdf ), esc_html__( '↓ Descargar ficha técnica (PDF)', 'esmalto-core' ) );
		}
		echo '</div>';
	}

	public static function aviso() {
		$texto = esmalto_ajuste( 'aviso_producto' );
		if ( $texto ) {
			echo '<p class="esm-aviso-producto">' . esc_html( $texto ) . '</p>';
		}
	}

	public static function texto_anadir() {
		return __( 'Añadir a la cesta', 'esmalto-core' );
	}

	public static function pestanas( $tabs ) {
		unset( $tabs['additional_information'], $tabs['reviews'] );
		if ( isset( $tabs['description'] ) ) {
			$tabs['description']['title'] = __( 'Descripción y embalaje', 'esmalto-core' );
		}
		return $tabs;
	}

	public static function titulo_relacionados() {
		return __( 'También te puede interesar', 'esmalto-core' );
	}

	public static function args_relacionados( $args ) {
		$args['posts_per_page'] = 3;
		$args['columns']        = 3;
		return $args;
	}

	/**
	 * «Carrito» → «Cesta» en los textos de WooCommerce (como en el diseño).
	 */
	public static function textos( $traduccion, $texto, $dominio ) {
		static $mapa = array(
			'Añadir al carrito'      => 'Añadir a la cesta',
			'Ver carrito'            => 'Ver cesta',
			'Carrito'                => 'Cesta',
			'Actualizar carrito'     => 'Actualizar cesta',
			'Tu carrito está vacío.' => 'Tu cesta está vacía.',
			'Totales del carrito'    => 'Totales de la cesta',
			'Volver a la tienda'     => 'Volver a la tienda',
		);
		return $mapa[ $traduccion ] ?? $traduccion;
	}
}
