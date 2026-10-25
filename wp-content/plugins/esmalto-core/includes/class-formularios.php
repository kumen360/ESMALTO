<?php
/**
 * Formularios de WPForms Lite (contacto, muestras, presupuesto).
 *
 * [esmalto_formulario tipo="contacto|muestras|presupuesto"] inserta el formulario creado por
 * Esmalto → Herramientas, sin depender del ID. Los campos «Producto», «m²» y «Cajas» se
 * rellenan solos con los parámetros de la URL (enlaces desde la ficha de producto).
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Formularios {

	const OPCION = 'esmalto_formularios';

	public static function init() {
		add_shortcode( 'esmalto_formulario', array( __CLASS__, 'shortcode' ) );
		add_action( 'wpforms_frontend_output_before', array( __CLASS__, 'ocultar_campo_trampa' ) );
	}

	/**
	 * WPForms inserta un campo trampa antispam (etiqueta hecha con palabras sueltas de las demás) y
	 * lo oculta con un script en línea. Hasta que el script se ejecuta, o si no llega a ejecutarse,
	 * se veía su etiqueta («colores colores Productos,»). Se imprime el mismo CSS desde el servidor;
	 * el número de campo se calcula igual que WPForms (primer hueco en los ID o el siguiente al mayor).
	 */
	public static function ocultar_campo_trampa( $form_data ) {
		if ( empty( $form_data['settings']['antispam_v3'] ) || empty( $form_data['fields'] ) || ! is_array( $form_data['fields'] ) ) {
			return;
		}
		$ids    = array_map( 'intval', array_keys( $form_data['fields'] ) );
		$mayor  = max( $ids );
		$trampa = $mayor + 1;
		for ( $i = 1; $i <= $mayor; $i++ ) {
			if ( ! in_array( $i, $ids, true ) ) {
				$trampa = $i;
				break;
			}
		}
		$selector = sprintf( '#wpforms-%d-field_%d-container', (int) $form_data['id'], $trampa );
		printf(
			'<style>%1$s{position:absolute!important;overflow:hidden!important;display:inline!important;height:1px!important;width:1px!important;z-index:-1000!important;padding:0!important}%1$s input{visibility:hidden}</style>',
			$selector // phpcs:ignore WordPress.Security.EscapeOutput -- solo números.
		);
	}

	public static function id( $tipo ) {
		$ids = get_option( self::OPCION, array() );
		$id  = isset( $ids[ $tipo ] ) ? (int) $ids[ $tipo ] : 0;
		return ( $id && 'wpforms' === get_post_type( $id ) && 'publish' === get_post_status( $id ) ) ? $id : 0;
	}

	public static function shortcode( $atts ) {
		$atts = shortcode_atts( array( 'tipo' => 'contacto' ), $atts, 'esmalto_formulario' );
		$tipo = sanitize_key( $atts['tipo'] );
		$id   = self::id( $tipo );
		$ancla = 'muestras' === $tipo ? 'solicitud' : 'formulario';
		if ( $id && shortcode_exists( 'wpforms' ) ) {
			return '<div id="' . esc_attr( $ancla ) . '" class="esm-formulario esm-formulario--' . esc_attr( $tipo ) . '">' . do_shortcode( '[wpforms id="' . $id . '" title="false" description="false"]' ) . '</div>' .
				( 'muestras' === $tipo ? self::script_muestras( $id ) : '' );
		}
		if ( current_user_can( 'manage_options' ) ) {
			return '<p class="esm-placeholder">' . esc_html__( 'Formulario pendiente: créalo en Esmalto → Herramientas → «Crear formularios» (requiere WPForms Lite activo).', 'esmalto-core' ) . '</p>';
		}
		$email = esmalto_ajuste( 'comercial_email' );
		return '<p>' . esc_html__( 'Escríbenos a', 'esmalto-core' ) . ' <a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a></p>';
	}

	/**
	 * «Añade todas las que necesites; van en la misma solicitud»: cada «Solicitar una muestra» de una
	 * ficha se suma a la lista guardada en el navegador en lugar de sustituirla, y el campo «Productos,
	 * colores y formatos» (campo 4) la muestra entera. La lista se vacía al enviar la solicitud.
	 * Sin líneas en blanco dentro del script (wpautop).
	 */
	private static function script_muestras( $form_id ) {
		$campo = '#wpforms-' . (int) $form_id . '-field_4';
		return '<script>
( function () {
	var campo = document.querySelector( ' . wp_json_encode( $campo ) . ' );
	if ( ! campo ) { return; }
	var clave = "esmalto_muestras", lista = [];
	try { lista = JSON.parse( window.localStorage.getItem( clave ) || "[]" ) || []; } catch ( e ) { lista = []; }
	var nuevo = new URLSearchParams( window.location.search ).get( "producto" );
	if ( nuevo && lista.indexOf( nuevo ) === -1 ) { lista.push( nuevo ); }
	function guardar() { try { window.localStorage.setItem( clave, JSON.stringify( lista ) ); } catch ( e ) {} }
	guardar();
	if ( lista.length ) { campo.value = lista.join( "\n" ); }
	campo.addEventListener( "input", function () {
		lista = campo.value.split( "\n" ).map( function ( l ) { return l.trim(); } ).filter( Boolean );
		guardar();
	} );
	if ( window.jQuery && campo.form ) {
		window.jQuery( campo.form ).on( "wpformsAjaxSubmitSuccess", function () { lista = []; guardar(); } );
	}
} )();
</script>';
	}

	/* ------------------------------------------------------------------ Creación */

	/**
	 * Campo con la etiqueta oculta y el texto como marcador, como en el diseño
	 * (WPForms mantiene la etiqueta para lectores de pantalla).
	 */
	private static function campo( $id, $tipo, $etiqueta, $extra = array() ) {
		return array_merge(
			array(
				'id'          => (string) $id,
				'type'        => $tipo,
				'label'       => $etiqueta,
				'label_hide'  => '1',
				'description' => '',
				'size'        => 'large',
				'placeholder' => 'select' === $tipo ? '' : $etiqueta,
				// El campo «Nombre» de formato simple usa su propio marcador.
				'simple_placeholder' => 'name' === $tipo ? $etiqueta : '',
				'css'         => '',
			),
			$extra
		);
	}

	private static function mitad( $primera = false ) {
		return array( 'css' => $primera ? 'wpforms-one-half wpforms-first' : 'wpforms-one-half' );
	}

	private static function privacidad( $id ) {
		$url = esmalto_url_pagina( 'politica-de-privacidad' );
		return self::campo(
			$id,
			'checkbox',
			__( 'Privacidad', 'esmalto-core' ),
			array(
				'choices'    => array(
					1 => array(
						'label' => sprintf( __( 'He leído y acepto la <a href="%s" target="_blank">política de privacidad</a>.', 'esmalto-core' ), esc_url( $url ) ),
						'value' => '',
						'image' => '',
					),
				),
				'required'   => '1',
				'label_hide' => '1',
			)
		);
	}

	private static function opciones( $lista ) {
		$choices = array();
		$i       = 1;
		foreach ( $lista as $etiqueta ) {
			$choices[ $i++ ] = array(
				'label' => $etiqueta,
				'value' => '',
				'image' => '',
			);
		}
		return $choices;
	}

	public static function definiciones() {
		$prefijo = array( 'default_value' => '' );
		return array(
			'contacto'    => array(
				'titulo'  => __( 'Contacto', 'esmalto-core' ),
				'enviar'  => __( 'Enviar mensaje', 'esmalto-core' ),
				'asunto'  => __( 'Nuevo mensaje de contacto ({field_id="3"})', 'esmalto-core' ),
				'gracias' => __( 'Gracias por escribirnos. Te responderemos lo antes posible.', 'esmalto-core' ),
				'campos'  => array(
					self::campo( 0, 'name', __( 'Nombre y apellidos', 'esmalto-core' ), array( 'format' => 'simple', 'required' => '1' ) ),
					self::campo( 1, 'email', __( 'Email', 'esmalto-core' ), array( 'required' => '1' ) + self::mitad( true ) ),
					self::campo( 2, 'text', __( 'Teléfono', 'esmalto-core' ), $prefijo + self::mitad() ),
					self::campo(
						3,
						'select',
						__( 'Motivo', 'esmalto-core' ),
						array(
							'choices'  => self::opciones( array( 'Comercial', 'Presupuesto', 'Información de producto', 'Muestras', 'Postventa', 'Otros' ) ),
							'required' => '1',
						)
					),
					self::campo( 4, 'textarea', __( 'Mensaje', 'esmalto-core' ), array( 'required' => '1', 'size' => 'medium' ) + $prefijo ),
					self::privacidad( 5 ),
				),
			),
			'muestras'    => array(
				'titulo'  => __( 'Solicitud de muestras', 'esmalto-core' ),
				'enviar'  => __( 'Solicitar muestras', 'esmalto-core' ),
				'asunto'  => __( 'Solicitud de muestras: {field_id="4"}', 'esmalto-core' ),
				'gracias' => __( 'Hemos recibido tu solicitud. Te enviaremos el importe de los portes para confirmar el envío de las muestras.', 'esmalto-core' ),
				'campos'  => array(
					self::campo( 0, 'name', __( 'Nombre y apellidos', 'esmalto-core' ), array( 'format' => 'simple', 'required' => '1' ) ),
					self::campo( 1, 'email', __( 'Email', 'esmalto-core' ), array( 'required' => '1' ) + self::mitad( true ) ),
					self::campo( 2, 'text', __( 'Teléfono', 'esmalto-core' ), array( 'required' => '1' ) + $prefijo + self::mitad() ),
					self::campo( 3, 'textarea', __( 'Dirección de envío (calle, CP, población, provincia)', 'esmalto-core' ), array( 'required' => '1', 'size' => 'small' ) + $prefijo ),
					self::campo( 4, 'textarea', __( 'Productos, colores y formatos', 'esmalto-core' ), array( 'required' => '1', 'size' => 'small', 'default_value' => '{query_var key="producto"}' ) ),
					self::privacidad( 5 ),
				),
			),
			'presupuesto' => array(
				'titulo'  => __( 'Solicitud de presupuesto', 'esmalto-core' ),
				'enviar'  => __( 'Pedir presupuesto', 'esmalto-core' ),
				'asunto'  => __( 'Presupuesto: {field_id="4"}', 'esmalto-core' ),
				'gracias' => __( 'Gracias. Te enviaremos el presupuesto en 24 h laborables.', 'esmalto-core' ),
				'campos'  => array(
					self::campo( 0, 'name', __( 'Nombre y apellidos', 'esmalto-core' ), array( 'format' => 'simple', 'required' => '1' ) ),
					self::campo( 1, 'email', __( 'Email', 'esmalto-core' ), array( 'required' => '1' ) + self::mitad( true ) ),
					self::campo( 2, 'text', __( 'Teléfono', 'esmalto-core' ), array( 'required' => '1' ) + $prefijo + self::mitad() ),
					self::campo( 3, 'text', __( 'Empresa (si eres profesional)', 'esmalto-core' ), $prefijo ),
					self::campo( 4, 'text', __( 'Producto', 'esmalto-core' ), array( 'required' => '1', 'default_value' => '{query_var key="producto"}' ) ),
					self::campo( 5, 'text', __( 'Metros cuadrados', 'esmalto-core' ), array( 'default_value' => '{query_var key="m2"}' ) + self::mitad( true ) ),
					self::campo( 6, 'text', __( 'Cajas', 'esmalto-core' ), array( 'default_value' => '{query_var key="cajas"}' ) + self::mitad() ),
					self::campo(
						7,
						'select',
						__( 'Entrega', 'esmalto-core' ),
						array( 'choices' => self::opciones( array( 'Entrega a domicilio / obra', 'Recogida en almacén' ) ) ) + self::mitad( true )
					),
					self::campo( 8, 'text', __( 'Código postal de entrega', 'esmalto-core' ), $prefijo + self::mitad() ),
					self::campo( 9, 'textarea', __( 'Comentarios', 'esmalto-core' ), array( 'size' => 'small' ) + $prefijo ),
					self::privacidad( 10 ),
				),
			),
		);
	}

	/**
	 * Crea los formularios en WPForms; con $forzar actualiza los existentes sin cambiar su ID. Devuelve [tipo => id].
	 */
	public static function crear( $forzar = false ) {
		if ( ! post_type_exists( 'wpforms' ) ) {
			return new WP_Error( 'esmalto_wpforms', __( 'WPForms Lite no está activo.', 'esmalto-core' ) );
		}
		$ids = get_option( self::OPCION, array() );
		foreach ( self::definiciones() as $tipo => $def ) {
			$id = self::id( $tipo );
			if ( $id && ! $forzar ) {
				continue;
			}
			if ( ! $id ) {
				$id = wp_insert_post(
					array(
						'post_title'   => $def['titulo'],
						'post_status'  => 'publish',
						'post_type'    => 'wpforms',
						'post_content' => '{}',
					),
					true
				);
			}
			if ( is_wp_error( $id ) ) {
				return $id;
			}
			$campos = array();
			foreach ( $def['campos'] as $campo ) {
				$campos[ $campo['id'] ] = $campo;
			}
			$datos = array(
				'id'       => (string) $id,
				'field_id' => max( array_map( 'intval', array_keys( $campos ) ) ) + 1,
				'fields'   => $campos,
				'settings' => array(
					'form_title'             => $def['titulo'],
					'form_desc'              => '',
					'submit_text'            => $def['enviar'],
					'submit_text_processing' => __( 'Enviando…', 'esmalto-core' ),
					'form_class'             => 'esm-wpforms',
					'submit_class'           => '',
					'ajax_submit'            => '1',
					'antispam'               => '1',
					'antispam_v3'            => '1',
					'notification_enable'    => '1',
					'notifications'          => array(
						1 => array(
							'notification_name' => __( 'Aviso al equipo', 'esmalto-core' ),
							'email'             => esmalto_email_formularios(),
							'subject'           => $def['asunto'],
							'sender_name'       => get_bloginfo( 'name' ),
							'sender_address'    => esmalto_email_formularios(),
							'replyto'           => '{field_id="1"}',
							'message'           => '{all_fields}',
						),
					),
					'confirmations'          => array(
						1 => array(
							'name'           => __( 'Mensaje', 'esmalto-core' ),
							'type'           => 'message',
							'message'        => '<p>' . esc_html( $def['gracias'] ) . '</p>',
							'message_scroll' => '1',
							'page'           => '',
							'redirect'       => '',
						),
					),
				),
				'meta'     => array( 'template' => 'blank' ),
			);
			wp_update_post(
				array(
					'ID'           => $id,
					'post_content' => wp_slash( wp_json_encode( $datos ) ),
				)
			);
			$ids[ $tipo ] = $id;
		}
		update_option( self::OPCION, $ids );
		return $ids;
	}
}
