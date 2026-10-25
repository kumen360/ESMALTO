<?php
/**
 * Método de envío por m² y palé: porte por m² del pedido más un importe por cada palé.
 *
 *   coste = precio por m² × m² del pedido + precio por palé × palés
 *   palés = ⌈ Σ cajas / cajas por palé ⌉ (cada referencia ocupa su parte de palé; mínimo 1)
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Envio_M2 extends WC_Shipping_Method {

	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'esmalto_m2';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Porte por m² y palé (Esmalto)', 'esmalto-core' );
		$this->method_description = __( 'Porte por m² del pedido más un importe por palé. Los palés se calculan con las cajas por palé de cada referencia (una caja ocupa su parte de palé; mínimo 1).', 'esmalto-core' );
		$this->supports           = array( 'shipping-zones', 'instance-settings', 'instance-settings-modal' );
		$this->init();
	}

	public function init() {
		$this->instance_form_fields = array(
			'title'        => array(
				'title'   => __( 'Nombre', 'esmalto-core' ),
				'type'    => 'text',
				'default' => __( 'Entrega en península (3 a 5 días laborables)', 'esmalto-core' ),
			),
			'precio_m2'    => array(
				'title'       => __( 'Porte por m² (€, sin IVA)', 'esmalto-core' ),
				'type'        => 'price',
				'default'     => '2.50',
				'description' => __( 'Se multiplica por los m² del pedido (m² por caja × cajas).', 'esmalto-core' ),
			),
			'precio_palet' => array(
				'title'       => __( 'Precio por palé (€, sin IVA)', 'esmalto-core' ),
				'type'        => 'price',
				'default'     => '9',
				'description' => __( 'Aparte del porte, por cada palé que ocupe el pedido.', 'esmalto-core' ),
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
		$medida = Esmalto_Envio::medir( $package['contents'] ?? array() );
		if ( ! $medida['palets'] ) {
			return;
		}
		$gratis = esmalto_num( $this->get_option( 'gratis_desde' ) );
		$total  = isset( $package['contents_cost'] ) ? (float) $package['contents_cost'] : 0;
		$coste  = ( $gratis > 0 && $total >= $gratis )
			? 0
			: round( esmalto_num( $this->get_option( 'precio_m2' ) ) * $medida['m2'] + esmalto_num( $this->get_option( 'precio_palet' ) ) * $medida['palets'], 2 );
		$this->add_rate(
			array(
				'id'        => $this->get_rate_id(),
				'label'     => $this->title,
				'cost'      => $coste,
				'package'   => $package,
				'meta_data' => array(
					__( 'm²', 'esmalto-core' )   => wc_format_localized_decimal( round( $medida['m2'], 2 ) ),
					__( 'Palés', 'esmalto-core' ) => $medida['palets'],
				),
			)
		);
	}
}
