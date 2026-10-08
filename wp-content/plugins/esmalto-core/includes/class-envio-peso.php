<?php
/**
 * Método de envío por peso.
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Envio_Peso extends WC_Shipping_Method {

	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'esmalto_peso';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Envío por peso (Esmalto)', 'esmalto-core' );
		$this->method_description = __( 'Coste según el peso total del pedido (el peso de cada variación es el de una caja). Tramos editables y coste por palé para pedidos grandes.', 'esmalto-core' );
		$this->supports           = array( 'shipping-zones', 'instance-settings', 'instance-settings-modal' );
		$this->init();
	}

	public function init() {
		$this->instance_form_fields = array(
			'title'        => array(
				'title'   => __( 'Nombre', 'esmalto-core' ),
				'type'    => 'text',
				'default' => __( 'Entrega a domicilio', 'esmalto-core' ),
			),
			'tramos'       => array(
				'title'       => __( 'Tramos de peso', 'esmalto-core' ),
				'type'        => 'textarea',
				'description' => __( 'Una línea por tramo: «kg máximos = coste en €» (sin IVA). Ej.: 30 = 12', 'esmalto-core' ),
				'default'     => "30 = 12\n100 = 25\n300 = 45\n600 = 75\n1000 = 110",
				'css'         => 'min-height:120px;font-family:monospace',
			),
			'kg_palet'     => array(
				'title'       => __( 'Kg por palé', 'esmalto-core' ),
				'type'        => 'number',
				'default'     => '1000',
				'description' => __( 'Por encima del último tramo se cobra por palés de este peso.', 'esmalto-core' ),
			),
			'coste_palet'  => array(
				'title'   => __( 'Coste por palé (€)', 'esmalto-core' ),
				'type'    => 'price',
				'default' => '120',
			),
			'gratis_desde' => array(
				'title'       => __( 'Envío gratis desde (€)', 'esmalto-core' ),
				'type'        => 'price',
				'default'     => '',
				'description' => __( 'Importe del pedido a partir del cual el envío es gratis. Vacío = nunca.', 'esmalto-core' ),
			),
			'tax_status'   => array(
				'title'   => __( 'Impuestos', 'esmalto-core' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => 'taxable',
				'options' => array(
					'taxable' => __( 'Sujeto a impuestos', 'esmalto-core' ),
					'none'    => __( 'Ninguno', 'esmalto-core' ),
				),
			),
		);
		$this->title      = $this->get_option( 'title' );
		$this->tax_status = $this->get_option( 'tax_status' );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public function calculate_shipping( $package = array() ) {
		$kg = 0.0;
		foreach ( $package['contents'] as $item ) {
			$producto = $item['data'];
			if ( $producto && $producto->needs_shipping() ) {
				$kg += (float) $producto->get_weight() * (int) $item['quantity'];
			}
		}
		$gratis = esmalto_num( $this->get_option( 'gratis_desde' ) );
		$total  = isset( $package['contents_cost'] ) ? (float) $package['contents_cost'] : 0;
		$coste  = ( $gratis > 0 && $total >= $gratis )
			? 0
			: Esmalto_Envio::coste( $kg, Esmalto_Envio::leer_tramos( $this->get_option( 'tramos' ) ), esmalto_num( $this->get_option( 'kg_palet' ) ), esmalto_num( $this->get_option( 'coste_palet' ) ) );
		if ( null === $coste ) {
			return;
		}
		$this->add_rate(
			array(
				'id'        => $this->get_rate_id(),
				'label'     => $this->title,
				'cost'      => $coste,
				'package'   => $package,
				'meta_data' => array( __( 'Peso', 'esmalto-core' ) => wc_format_localized_decimal( round( $kg, 1 ) ) . ' kg' ),
			)
		);
	}
}
