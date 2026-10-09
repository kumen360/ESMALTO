<?php
/**
 * Plugin Name:       Esmalto Core
 * Plugin URI:        https://www.esmalto.com
 * Description:       Lógica de tienda de Esmalto: calculadora de m² (cajas y palés), B2B (alta con CNAE, categorías A/B/C, descuento por palé, contacto comercial), envío por peso, fichas técnicas y herramientas de puesta en marcha.
 * Version:           1.2.0
 * Requires at least: 6.6
 * Requires PHP:      8.0
 * Requires Plugins:  woocommerce
 * Author:            Kumen360
 * Text Domain:       esmalto-core
 * License:           GPL-2.0-or-later
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'ESMALTO_CORE_VERSION', '1.2.0' );
define( 'ESMALTO_CORE_FILE', __FILE__ );
define( 'ESMALTO_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'ESMALTO_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once ESMALTO_CORE_DIR . 'includes/functions.php';
require_once ESMALTO_CORE_DIR . 'includes/class-cnae.php';
require_once ESMALTO_CORE_DIR . 'includes/class-ajustes.php';
require_once ESMALTO_CORE_DIR . 'includes/class-b2b.php';
require_once ESMALTO_CORE_DIR . 'includes/class-registro-pro.php';
require_once ESMALTO_CORE_DIR . 'includes/class-registro-cliente.php';
require_once ESMALTO_CORE_DIR . 'includes/class-producto.php';
require_once ESMALTO_CORE_DIR . 'includes/class-calculadora.php';
require_once ESMALTO_CORE_DIR . 'includes/class-envio.php';
require_once ESMALTO_CORE_DIR . 'includes/class-formularios.php';
require_once ESMALTO_CORE_DIR . 'includes/class-paginas.php';
require_once ESMALTO_CORE_DIR . 'includes/class-herramientas.php';

add_action( 'plugins_loaded', 'esmalto_core_init', 20 );
function esmalto_core_init() {
	Esmalto_Ajustes::init();
	Esmalto_Herramientas::init();
	Esmalto_Registro_Pro::init();
	Esmalto_Formularios::init();
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'esmalto_core_aviso_woocommerce' );
		return;
	}
	Esmalto_B2B::init();
	Esmalto_Registro_Cliente::init();
	Esmalto_Producto::init();
	Esmalto_Calculadora::init();
	Esmalto_Envio::init();
}

function esmalto_core_aviso_woocommerce() {
	echo '<div class="notice notice-warning"><p>' . esc_html__( 'Esmalto Core necesita WooCommerce activo.', 'esmalto-core' ) . '</p></div>';
}

register_activation_hook( __FILE__, 'esmalto_core_activar' );
function esmalto_core_activar() {
	Esmalto_B2B::registrar_endpoint();
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
