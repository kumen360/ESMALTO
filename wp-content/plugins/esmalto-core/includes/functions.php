<?php
/**
 * Utilidades comunes y ajustes.
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Valores por defecto de Esmalto → Ajustes.
 */
function esmalto_ajustes_defecto() {
	return array(
		'precios_visibles'   => 0,
		'cat_a_pct'          => 20,
		'cat_b_pct'          => 30,
		'cat_c_pct'          => 40,
		'cat_a_rol'          => '',
		'cat_b_rol'          => '',
		'cat_c_rol'          => '',
		'rol_alta'           => '',
		'palet_pct'          => 5,
		'b2b_sin_iva'        => 1,
		'comercial_nombre'   => 'Departamento comercial',
		'comercial_email'    => 'clientes.b2b@esmalto.com',
		'comercial_tel'      => '+34 614 05 10 42',
		'comercial_whatsapp' => '',
		'email_avisos'       => 'clientes.b2b@esmalto.com',
		'email_formularios'  => 'info@esmalto.com',
		'cnae'               => '',
		'aviso_producto'     => 'Puede haber errores en los detalles de los productos. Para cualquier duda, contacta con el departamento comercial.',
		'catalogo_url'       => 'https://raw.githubusercontent.com/kumen360/ESMALTO/main/catalogo/',
		'recogida_direccion' => '',
		'recogida_horario'   => '',
		'plazo_entrega'      => 'Península: entrega en 3 a 5 días laborables desde la confirmación del pedido. Baleares, Canarias, Ceuta y Melilla: bajo presupuesto.',
	);
}

function esmalto_ajustes() {
	$guardados = get_option( 'esmalto_ajustes', array() );
	return wp_parse_args( is_array( $guardados ) ? $guardados : array(), esmalto_ajustes_defecto() );
}

function esmalto_ajuste( $clave ) {
	$ajustes = esmalto_ajustes();
	return $ajustes[ $clave ] ?? null;
}

/**
 * ¿Se muestran los precios y se puede comprar online? Se activa en Esmalto → Ajustes cuando la tarifa
 * esté cargada; mientras tanto la tienda funciona en modo «Solicitar presupuesto» aunque haya precios.
 */
function esmalto_precios_visibles() {
	return (bool) apply_filters( 'esmalto_precios_visibles', (bool) esmalto_ajuste( 'precios_visibles' ) );
}

/**
 * Número desde texto con coma o punto decimal.
 */
function esmalto_num( $valor ) {
	if ( is_numeric( $valor ) ) {
		return (float) $valor;
	}
	$valor = str_replace( array( ' ', "\xc2\xa0" ), '', (string) $valor );
	if ( false !== strpos( $valor, ',' ) && false !== strpos( $valor, '.' ) ) {
		$valor = str_replace( '.', '', $valor );
	}
	return (float) str_replace( ',', '.', $valor );
}

/**
 * URL de una página por su slug (con alternativa si aún no existe).
 */
function esmalto_url_pagina( $slug ) {
	$pagina = get_page_by_path( $slug );
	return $pagina ? get_permalink( $pagina ) : home_url( '/' . trim( $slug, '/' ) . '/' );
}

function esmalto_email_avisos() {
	$email = esmalto_ajuste( 'email_avisos' );
	return is_email( $email ) ? $email : get_option( 'admin_email' );
}

/**
 * Correo de contacto: recibe los formularios (contacto, muestras y presupuesto) y firma los correos
 * de WordPress.
 */
function esmalto_email_formularios() {
	$email = esmalto_ajuste( 'email_formularios' );
	return is_email( $email ) ? $email : get_option( 'admin_email' );
}

/**
 * El remitente por defecto de WordPress (wordpress@dominio) no existe: se usa el correo de contacto.
 */
add_filter(
	'wp_mail_from',
	static function ( $email ) {
		return 0 === strpos( (string) $email, 'wordpress@' ) ? esmalto_email_formularios() : $email;
	}
);
add_filter(
	'wp_mail_from_name',
	static function ( $nombre ) {
		return 'WordPress' === $nombre ? get_bloginfo( 'name' ) : $nombre;
	}
);

/**
 * NIF, DNI o NIE sin espacios, puntos ni guiones, en mayúsculas y sin el prefijo «ES».
 */
function esmalto_nif_normalizar( $valor ) {
	$valor = strtoupper( preg_replace( '/[^0-9A-Za-z]/', '', (string) $valor ) );
	return ( 0 === strpos( $valor, 'ES' ) && strlen( $valor ) > 9 ) ? substr( $valor, 2 ) : $valor;
}

/**
 * ¿Es un DNI, NIE o NIF de empresa (CIF) válido? Comprueba la letra o el dígito de control.
 */
function esmalto_nif_valido( $valor ) {
	$v      = esmalto_nif_normalizar( $valor );
	$letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
	if ( preg_match( '/^(\d{8})([A-Z])$/', $v, $m ) ) { // DNI.
		return $letras[ (int) $m[1] % 23 ] === $m[2];
	}
	if ( preg_match( '/^([XYZ])(\d{7})([A-Z])$/', $v, $m ) ) { // NIE.
		return $letras[ (int) ( strpos( 'XYZ', $m[1] ) . $m[2] ) % 23 ] === $m[3];
	}
	if ( preg_match( '/^[KLM](\d{7})([A-Z])$/', $v, $m ) ) { // NIF de personas sin DNI.
		return $letras[ (int) $m[1] % 23 ] === $m[2];
	}
	if ( preg_match( '/^([ABCDEFGHJNPQRSUVW])(\d{7})([0-9A-J])$/', $v, $m ) ) { // NIF de entidades (CIF).
		$suma = 0;
		for ( $i = 0; $i < 7; $i++ ) {
			$d = (int) $m[2][ $i ];
			if ( 0 === $i % 2 ) {
				$d = 2 * $d;
				$d = intdiv( $d, 10 ) + $d % 10;
			}
			$suma += $d;
		}
		$control = ( 10 - $suma % 10 ) % 10;
		$letra   = 'JABCDEFGHI'[ $control ];
		if ( false !== strpos( 'NPQRSW', $m[1] ) ) {
			return $m[3] === $letra;
		}
		if ( false !== strpos( 'ABEH', $m[1] ) ) {
			return $m[3] === (string) $control;
		}
		return $m[3] === (string) $control || $m[3] === $letra;
	}
	return false;
}

/**
 * WholesaleX: roles definidos (id => título).
 */
function esmalto_roles_wholesalex() {
	$roles = get_option( '_wholesalex_roles', array() );
	$salida = array();
	if ( is_array( $roles ) ) {
		foreach ( $roles as $rol ) {
			if ( ! empty( $rol['id'] ) && ! in_array( $rol['id'], array( 'wholesalex_guest', 'wholesalex_b2c_users' ), true ) ) {
				$salida[ $rol['id'] ] = $rol['_role_title'] ?? $rol['id'];
			}
		}
	}
	return $salida;
}

function esmalto_wholesalex_activo() {
	return function_exists( 'wholesalex' ) || defined( 'WHOLESALEX_VER' );
}

/**
 * Datos de embalaje de una variación.
 */
function esmalto_embalaje( $variacion_id ) {
	return array(
		'm2_caja'       => esmalto_num( get_post_meta( $variacion_id, '_m2_por_caja', true ) ),
		'kg_caja'       => esmalto_num( get_post_meta( $variacion_id, '_kg_por_caja', true ) ),
		'piezas_caja'   => (int) get_post_meta( $variacion_id, '_piezas_por_caja', true ),
		'cajas_palet'   => (int) get_post_meta( $variacion_id, '_cajas_por_pallet', true ),
		'm2_palet'      => esmalto_num( get_post_meta( $variacion_id, '_m2_por_pallet', true ) ),
		'kg_palet'      => esmalto_num( get_post_meta( $variacion_id, '_kg_por_pallet', true ) ),
	);
}

/**
 * Plantilla de email con el estilo de WooCommerce (o texto plano si no está activo).
 */
function esmalto_enviar_email( $para, $asunto, $titulo, $html ) {
	if ( function_exists( 'WC' ) && WC()->mailer() ) {
		$mailer = WC()->mailer();
		return $mailer->send( $para, $asunto, $mailer->wrap_message( $titulo, $html ), array( 'Content-Type: text/html; charset=UTF-8' ) );
	}
	return wp_mail( $para, $asunto, $html, array( 'Content-Type: text/html; charset=UTF-8' ) );
}
