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
		'comercial_email'    => 'hola@esmalto.com',
		'comercial_tel'      => '600 600 600',
		'comercial_whatsapp' => '',
		'email_avisos'       => '',
		'cnae'               => '',
		'aviso_producto'     => 'Puede haber errores en los detalles de los productos. Para cualquier duda, contacta con el departamento comercial.',
		'catalogo_url'       => 'https://raw.githubusercontent.com/kumen360/ESMALTO/main/catalogo/',
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
