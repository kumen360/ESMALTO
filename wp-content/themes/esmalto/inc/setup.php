<?php
/**
 * Soportes del tema, recursos, estilos de bloque y categorías de patrones.
 *
 * @package Esmalto
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tipografías del diseño (Google Fonts). Cámbialas aquí si se alojan en local.
 */
function esmalto_fonts_url() {
	return apply_filters(
		'esmalto_fonts_url',
		'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap'
	);
}

add_action( 'after_setup_theme', 'esmalto_setup' );
function esmalto_setup() {
	add_theme_support( 'block-template-parts' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 150,
			'width'       => 640,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_editor_style( 'assets/css/editor.css' );
}

add_action( 'wp_enqueue_scripts', 'esmalto_enqueue_front', 20 );
function esmalto_enqueue_front() {
	wp_enqueue_style( 'esmalto-fuentes', esmalto_fonts_url(), array(), null );
	$parent = wp_style_is( 'astra-theme-css', 'registered' ) ? array( 'astra-theme-css' ) : array();
	wp_enqueue_style( 'esmalto', get_stylesheet_uri(), $parent, ESMALTO_THEME_VERSION );
	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style( 'esmalto-tienda', get_theme_file_uri( 'assets/css/tienda.css' ), array( 'esmalto' ), ESMALTO_THEME_VERSION );
	}
}

/**
 * Estilos de bloque: se cargan en la web y dentro del editor (iframe).
 */
add_action( 'enqueue_block_assets', 'esmalto_enqueue_block_assets' );
function esmalto_enqueue_block_assets() {
	wp_enqueue_style( 'esmalto-bloques', get_theme_file_uri( 'assets/css/bloques.css' ), array(), ESMALTO_THEME_VERSION );
	if ( is_admin() ) {
		wp_enqueue_style( 'esmalto-fuentes', esmalto_fonts_url(), array(), null );
	}
}

add_filter( 'wp_resource_hints', 'esmalto_resource_hints', 10, 2 );
function esmalto_resource_hints( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}

/**
 * Estilos de bloque propios (se eligen en el panel «Estilos» de cada bloque).
 */
add_action( 'init', 'esmalto_block_styles' );
function esmalto_block_styles() {
	$styles = array(
		'core/paragraph' => array(
			'antetitulo' => __( 'Antetítulo', 'esmalto' ),
			'miga'       => __( 'Ruta (miga de pan)', 'esmalto' ),
			'etiqueta'   => __( 'Etiqueta mono', 'esmalto' ),
		),
		'core/heading'   => array(
			'antetitulo' => __( 'Antetítulo', 'esmalto' ),
		),
		'core/button'    => array(
			'chip'   => __( 'Chip / filtro', 'esmalto' ),
			'enlace' => __( 'Enlace con flecha', 'esmalto' ),
		),
		'core/group'     => array(
			'tarjeta' => __( 'Tarjeta con borde', 'esmalto' ),
			'aviso'   => __( 'Aviso', 'esmalto' ),
			'filete'  => __( 'Filete amarillo', 'esmalto' ),
		),
		'core/cover'     => array(
			'tarjeta-enlace' => __( 'Tarjeta enlazada', 'esmalto' ),
		),
		'core/list'      => array(
			'guiones' => __( 'Guiones', 'esmalto' ),
			'enlaces' => __( 'Lista de enlaces', 'esmalto' ),
		),
		'core/image'     => array(
			'llenar' => __( 'Rellenar alto', 'esmalto' ),
		),
	);
	foreach ( $styles as $block => $variants ) {
		foreach ( $variants as $name => $label ) {
			register_block_style(
				$block,
				array(
					'name'  => $name,
					'label' => $label,
				)
			);
		}
	}
}

add_action( 'init', 'esmalto_pattern_categories', 9 );
function esmalto_pattern_categories() {
	register_block_pattern_category( 'esmalto', array( 'label' => __( 'Esmalto · secciones', 'esmalto' ) ) );
	register_block_pattern_category( 'esmalto-paginas', array( 'label' => __( 'Esmalto · páginas completas', 'esmalto' ) ) );
}

/**
 * URL de una imagen incluida en el tema (para los patrones).
 */
function esmalto_img( $file ) {
	return esc_url( get_theme_file_uri( 'assets/img/' . $file ) );
}
