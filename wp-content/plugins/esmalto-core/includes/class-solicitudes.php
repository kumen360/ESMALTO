<?php
/**
 * Esmalto → Solicitudes: cada envío de los formularios de WPForms (contacto, muestras, presupuesto…)
 * se guarda en la base de datos, para no perder ninguna consulta si falla el correo.
 * WPForms Lite no guarda las entradas; aquí se guardan como entradas privadas de solo lectura.
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Solicitudes {

	const TIPO = 'esmalto_solicitud';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'registrar_tipo' ) );
		add_action( 'wpforms_process_complete', array( __CLASS__, 'guardar' ), 10, 4 );
		add_filter( 'manage_' . self::TIPO . '_posts_columns', array( __CLASS__, 'columnas' ) );
		add_action( 'manage_' . self::TIPO . '_posts_custom_column', array( __CLASS__, 'columna' ), 10, 2 );
		add_action( 'add_meta_boxes_' . self::TIPO, array( __CLASS__, 'caja' ) );
		add_filter( 'post_row_actions', array( __CLASS__, 'acciones_fila' ), 10, 2 );
	}

	public static function registrar_tipo() {
		register_post_type(
			self::TIPO,
			array(
				'labels'              => array(
					'name'          => __( 'Solicitudes', 'esmalto-core' ),
					'singular_name' => __( 'Solicitud', 'esmalto-core' ),
					'menu_name'     => __( 'Solicitudes', 'esmalto-core' ),
					'all_items'     => __( 'Solicitudes', 'esmalto-core' ),
					'edit_item'     => __( 'Solicitud', 'esmalto-core' ),
					'search_items'  => __( 'Buscar solicitudes', 'esmalto-core' ),
					'not_found'     => __( 'Todavía no hay solicitudes.', 'esmalto-core' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'esmalto-ajustes',
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'supports'            => array( 'title' ),
				'map_meta_cap'        => true,
				'capability_type'     => 'post',
				'capabilities'        => array(
					'create_posts'           => 'do_not_allow',
					'edit_posts'             => 'manage_woocommerce',
					'edit_others_posts'      => 'manage_woocommerce',
					'edit_private_posts'     => 'manage_woocommerce',
					'edit_published_posts'   => 'manage_woocommerce',
					'read_private_posts'     => 'manage_woocommerce',
					'delete_posts'           => 'manage_woocommerce',
					'delete_others_posts'    => 'manage_woocommerce',
					'delete_private_posts'   => 'manage_woocommerce',
					'delete_published_posts' => 'manage_woocommerce',
					'publish_posts'          => 'manage_woocommerce',
				),
			)
		);
	}

	/**
	 * Guarda el envío: título «Formulario · Nombre · email» y una tabla con todos los campos.
	 */
	public static function guardar( $campos, $entrada, $form_data, $entry_id ) {
		$filas  = array();
		$nombre = '';
		$email  = '';
		foreach ( (array) $campos as $campo ) {
			$valor = trim( (string) ( $campo['value'] ?? '' ) );
			if ( '' === $valor ) {
				continue;
			}
			$tipo = $campo['type'] ?? '';
			if ( 'name' === $tipo && '' === $nombre ) {
				$nombre = $valor;
			}
			if ( 'email' === $tipo && '' === $email ) {
				$email = $valor;
			}
			$filas[] = '<tr><th style="text-align:left;width:220px">' . esc_html( $campo['name'] ?? '' ) . '</th><td>' . nl2br( esc_html( $valor ) ) . '</td></tr>';
		}
		if ( ! $filas ) {
			return;
		}
		$formulario = $form_data['settings']['form_title'] ?? __( 'Formulario', 'esmalto-core' );
		wp_insert_post(
			array(
				'post_type'    => self::TIPO,
				'post_status'  => 'private',
				'post_title'   => wp_strip_all_tags( implode( ' · ', array_filter( array( $formulario, $nombre, $email ) ) ) ),
				'post_content' => '<table class="widefat striped">' . implode( '', $filas ) . '</table>',
				'meta_input'   => array(
					'_esm_formulario' => sanitize_text_field( $formulario ),
					'_esm_email'      => sanitize_email( $email ),
					'_esm_pagina'     => esc_url_raw( (string) wp_get_referer() ),
				),
			)
		);
	}

	public static function columnas( $columnas ) {
		return array(
			'cb'         => $columnas['cb'] ?? '',
			'title'      => __( 'Solicitud', 'esmalto-core' ),
			'formulario' => __( 'Formulario', 'esmalto-core' ),
			'email'      => __( 'Email', 'esmalto-core' ),
			'date'       => __( 'Fecha', 'esmalto-core' ),
		);
	}

	public static function columna( $columna, $post_id ) {
		if ( 'formulario' === $columna ) {
			echo esc_html( (string) get_post_meta( $post_id, '_esm_formulario', true ) );
		} elseif ( 'email' === $columna ) {
			$email = (string) get_post_meta( $post_id, '_esm_email', true );
			if ( $email ) {
				printf( '<a href="%s">%s</a>', esc_url( 'mailto:' . $email ), esc_html( $email ) );
			}
		}
	}

	/**
	 * Al abrir una solicitud se ven sus datos (solo lectura).
	 */
	public static function caja( $post ) {
		add_meta_box(
			'esm-solicitud-datos',
			__( 'Datos enviados', 'esmalto-core' ),
			static function ( $post ) {
				echo wp_kses_post( $post->post_content );
				$pagina = (string) get_post_meta( $post->ID, '_esm_pagina', true );
				if ( $pagina ) {
					printf( '<p>%s <a href="%s" target="_blank" rel="noopener">%s</a></p>', esc_html__( 'Enviada desde:', 'esmalto-core' ), esc_url( $pagina ), esc_html( $pagina ) );
				}
			},
			self::TIPO,
			'normal',
			'high'
		);
	}

	public static function acciones_fila( $acciones, $post ) {
		if ( self::TIPO === $post->post_type ) {
			unset( $acciones['inline hide-if-no-js'] );
		}
		return $acciones;
	}
}
