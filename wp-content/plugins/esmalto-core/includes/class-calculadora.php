<?php
/**
 * Calculadora de m² en la ficha de producto.
 *
 *   cajas = ⌈ m² / m² por caja ⌉        coste = cajas × precio de la caja
 *   Profesionales: precio de su categoría y opción «palés completos»
 *   (cajas = palés × cajas por palé, con el % adicional de Esmalto → Ajustes).
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Calculadora {

	public static function init() {
		add_filter( 'woocommerce_available_variation', array( __CLASS__, 'datos_variacion' ), 10, 3 );
		add_action( 'woocommerce_after_add_to_cart_form', array( __CLASS__, 'html' ), 10 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'recursos' ), 20 );
	}

	/**
	 * Datos de embalaje y precios de cada variación para el JS de la ficha.
	 */
	public static function datos_variacion( $datos, $product, $variation ) {
		$e        = esmalto_embalaje( $variation->get_id() );
		$conPrecio = '' !== $variation->get_price() && (float) $variation->get_price() > 0;
		$datos['esm'] = array(
			'm2_caja'         => $e['m2_caja'],
			'cajas_palet'     => $e['cajas_palet'],
			'm2_palet'        => $e['m2_palet'],
			'kg_caja'         => $e['kg_caja'],
			'piezas_caja'     => $e['piezas_caja'],
			'precio_caja'     => $conPrecio ? (float) wc_get_price_to_display( $variation ) : 0,
			'precio_caja_sin' => $conPrecio ? (float) wc_get_price_excluding_tax( $variation ) : 0,
			'precio_caja_con' => $conPrecio ? (float) wc_get_price_including_tax( $variation ) : 0,
			'formato'         => (string) get_post_meta( $variation->get_id(), '_formato', true ),
			'espesor'         => (string) get_post_meta( $variation->get_id(), '_espesor', true ),
			'sku'             => $variation->get_sku(),
			'nombre'          => trim( $product->get_name() . ' · ' . wc_get_formatted_variation( $variation, true, false, false ), ' ·' ),
		);
		return $datos;
	}

	public static function html() {
		global $product;
		if ( ! $product instanceof WC_Product || ! $product->is_type( 'variable' ) ) {
			return;
		}
		$b2b = Esmalto_B2B::es_profesional();
		$pct = (float) esmalto_ajuste( 'palet_pct' );
		?>
		<div class="esm-calc" data-esm-calc hidden>
			<div class="esm-calc__titulo"><?php esc_html_e( 'Calcula tu proyecto', 'esmalto-core' ); ?></div>
			<div class="esm-calc__fila">
				<input type="number" min="0" step="0.01" inputmode="decimal" value="10" data-esm-m2 aria-label="<?php esc_attr_e( 'Metros cuadrados necesarios', 'esmalto-core' ); ?>">
				<span class="esm-calc__unidad"><?php esc_html_e( 'm² necesarios', 'esmalto-core' ); ?></span>
				<div class="esm-calc__total">
					<small><?php esc_html_e( 'Estimado', 'esmalto-core' ); ?></small>
					<strong data-esm-total>—</strong>
				</div>
			</div>
			<label class="esm-calc__palet">
				<input type="checkbox" data-esm-merma>
				<?php esc_html_e( 'Añadir un 10 % de merma por cortes', 'esmalto-core' ); ?>
			</label>
			<?php if ( $b2b ) : ?>
				<label class="esm-calc__palet" data-esm-palet-opcion hidden>
					<input type="checkbox" data-esm-palet>
					<?php
					echo esc_html(
						$pct > 0
							? sprintf( __( 'Pedir por palés completos (−%s %% adicional)', 'esmalto-core' ), wc_format_localized_decimal( $pct ) )
							: __( 'Pedir por palés completos', 'esmalto-core' )
					);
					?>
				</label>
			<?php endif; ?>
			<div class="esm-calc__detalle">
				<div class="esm-calc__dato"><span><?php esc_html_e( 'Cajas', 'esmalto-core' ); ?></span><b data-esm-cajas>—</b></div>
				<div class="esm-calc__dato"><span><?php esc_html_e( 'm² reales', 'esmalto-core' ); ?></span><b data-esm-m2reales>—</b></div>
				<div class="esm-calc__dato"><span><?php esc_html_e( 'Precio / caja', 'esmalto-core' ); ?></span><b data-esm-precio-caja>—</b></div>
				<div class="esm-calc__dato" data-esm-dato-palets hidden><span><?php esc_html_e( 'Palés', 'esmalto-core' ); ?></span><b data-esm-palets>—</b></div>
			</div>
			<p class="esm-calc__nota" data-esm-nota></p>
			<button type="button" class="button esm-calc__aplicar" data-esm-aplicar><?php esc_html_e( 'Usar estas cajas', 'esmalto-core' ); ?></button>
		</div>
		<?php
	}

	public static function recursos() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}
		wp_enqueue_script( 'esmalto-producto', ESMALTO_CORE_URL . 'assets/js/producto.js', array( 'jquery' ), ESMALTO_CORE_VERSION, true );
		$categoria = Esmalto_B2B::categoria();
		wp_localize_script(
			'esmalto-producto',
			'esmaltoProducto',
			array(
				'moneda'    => array(
					'simbolo'    => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ),
					'decimales'  => wc_get_price_decimals(),
					'sepDecimal' => wc_get_price_decimal_separator(),
					'sepMiles'   => wc_get_price_thousand_separator(),
					'formato'    => html_entity_decode( get_woocommerce_price_format(), ENT_QUOTES, 'UTF-8' ),
				),
				'b2b'       => '' !== $categoria,
				'categoria' => $categoria,
				'paletPct'  => (float) esmalto_ajuste( 'palet_pct' ),
				'ivaIncl'   => 'incl' === get_option( 'woocommerce_tax_display_shop' ),
				'textos'    => array(
					'consultar'     => __( 'Precio a consultar', 'esmalto-core' ),
					'notaConsultar' => __( 'Pide presupuesto con estas cantidades: te respondemos en 24 h.', 'esmalto-core' ),
					'notaIvaIncl'   => __( 'IVA incluido · portes no incluidos', 'esmalto-core' ),
					'notaIvaExcl'   => __( 'Precios sin IVA · portes no incluidos', 'esmalto-core' ),
					'm2'            => __( '/m²', 'esmalto-core' ),
					'sinIva'        => __( '/ m² (sin IVA)', 'esmalto-core' ),
					'conIva'        => __( '/ m² (IVA incluido)', 'esmalto-core' ),
					'caja'          => __( 'Caja de %1$s m² · %2$s piezas · %3$s kg', 'esmalto-core' ),
					'palet'         => __( '%1$s cajas por palé (%2$s m²)', 'esmalto-core' ),
					'descPalet'     => __( 'Incluye %1$s de descuento por %2$s palé(s) completo(s).', 'esmalto-core' ),
					'sinCalculo'    => __( 'Esta referencia no tiene m² por caja: consulta con el comercial.', 'esmalto-core' ),
					'merma'         => __( 'Incluye un 10 % de merma.', 'esmalto-core' ),
					'usar'          => __( 'Usar %s cajas', 'esmalto-core' ),
					'embalaje'      => __( '%1$s piezas/caja · %2$s m²/caja · %3$s kg/caja', 'esmalto-core' ),
					'embalajePalet' => __( ' · %s cajas/palé', 'esmalto-core' ),
				),
			)
		);
	}
}
