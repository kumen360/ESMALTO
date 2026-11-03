<?php
/**
 * Datos de la empresa (Esmalto Cerámica es una marca comercial de Kumen Tres Sesenta, S.L.) y la
 * herramienta que los aplica junto con los correos, los formularios y el envío por m² y palé
 * (Esmalto → Herramientas → 6). Cada paso se puede repetir sin efectos secundarios.
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Empresa {

	const RAZON_SOCIAL  = 'Kumen Tres Sesenta, S.L.';
	const MARCA         = 'Esmalto Cerámica';
	const NIF           = 'B88655014';
	const DIRECCION     = 'Calle La Llosa, 3';
	const DIRECCION_2   = 'Planta 3, puerta 16';
	const CODIGO_POSTAL = '12003';
	const POBLACION     = 'Castellón de la Plana';
	const PROVINCIA     = 'CS'; // Castellón.
	const REGISTRO      = 'Registro Mercantil de Cádiz, hoja CA-69375';
	const TELEFONO      = '+34 614 05 10 42';
	const HORARIO       = 'Lunes a viernes, de 9:00 a 14:00 y de 16:00 a 19:00';
	const EMAIL         = 'hola@esmalto.com';
	const EMAIL_PEDIDOS = 'pedidos@esmalto.com';
	const EMAIL_B2B     = 'clientes.b2b@esmalto.com';

	/**
	 * Colores de las plantillas de correo de WooCommerce (los del tema: fondo, superficie, texto y
	 * amarillo para los enlaces). Sobre blanco no se leía el logotipo amarillo.
	 */
	const COLORES_CORREO = array(
		'woocommerce_email_base_color'            => '#fec30c',
		'woocommerce_email_background_color'      => '#002100',
		'woocommerce_email_body_background_color' => '#00330f',
		'woocommerce_email_text_color'            => '#f1f1f1',
		'woocommerce_email_footer_text_color'     => '#c8d2c8',
	);

	/**
	 * «Calle La Llosa, 3, planta 3, puerta 16, 12003 Castellón de la Plana».
	 */
	public static function direccion() {
		return self::DIRECCION . ', ' . lcfirst( self::DIRECCION_2 ) . ', ' . self::CODIGO_POSTAL . ' ' . self::POBLACION;
	}

	public static function pie_correo() {
		return self::MARCA . ' es una marca comercial de ' . self::RAZON_SOCIAL . ' · NIF ' . self::NIF . '<br />' .
			self::direccion() . ' · ' . self::EMAIL . ' · ' . self::TELEFONO;
	}

	/**
	 * @return string[] registro.
	 */
	public static function aplicar() {
		$log = array();

		// 1. Esmalto → Ajustes.
		$ajustes                      = esmalto_ajustes();
		$ajustes['comercial_email']   = self::EMAIL_B2B;
		$ajustes['comercial_tel']     = self::TELEFONO;
		$ajustes['email_avisos']      = self::EMAIL_B2B;
		$ajustes['email_formularios'] = self::EMAIL;
		// Vacío o con el texto anterior a las condiciones generales («desde la confirmación del pedido»).
		if ( '' === trim( (string) ( $ajustes['plazo_entrega'] ?? '' ) ) || false !== strpos( $ajustes['plazo_entrega'], 'confirmación del pedido' ) ) {
			$ajustes['plazo_entrega'] = esmalto_ajustes_defecto()['plazo_entrega'];
		}
		update_option( Esmalto_Ajustes::OPCION, $ajustes );
		$log[] = sprintf( 'Esmalto → Ajustes: contacto comercial y avisos de altas a %1$s, formularios a %2$s, teléfono %3$s y plazo de entrega.', self::EMAIL_B2B, self::EMAIL, self::TELEFONO );

		if ( ! class_exists( 'WooCommerce' ) ) {
			$log[] = '⚠ WooCommerce no está activo: no se aplica nada más.';
			return $log;
		}

		// 2. Dirección de la tienda (sale en los correos y es la base del IVA).
		update_option( 'woocommerce_store_address', self::DIRECCION );
		update_option( 'woocommerce_store_address_2', self::DIRECCION_2 );
		update_option( 'woocommerce_store_city', self::POBLACION );
		update_option( 'woocommerce_store_postcode', self::CODIGO_POSTAL );
		update_option( 'woocommerce_default_country', 'ES:' . self::PROVINCIA );
		$log[] = sprintf( 'Dirección de la tienda: %s.', self::direccion() );

		// 3. Transferencia: reserva de 5 días hábiles (condiciones generales); el justificante va al correo de contacto.
		$bacs = get_option( 'woocommerce_bacs_settings', array() );
		$bacs = is_array( $bacs ) ? $bacs : array();
		$bacs['description']  = 'Tu pedido queda reservado durante 5 días hábiles; si el pago no se recibe en ese plazo, se anula sin coste para ti. Usa el número de pedido como concepto. Prepararemos tu pedido cuando recibamos el pago.';
		$bacs['instructions'] = sprintf( 'Tu pedido queda reservado durante 5 días hábiles; si el pago no se recibe en ese plazo, se anula sin coste para ti. Envía el justificante a %s.', self::EMAIL );
		update_option( 'woocommerce_bacs_settings', $bacs );
		$log[] = sprintf( 'Transferencia: pedido reservado 5 días hábiles y justificante a %s.', self::EMAIL );

		// 4. Correos de WooCommerce: remitente, respuestas, logotipo, colores y pie.
		update_option( 'woocommerce_email_from_name', 'Esmalto' );
		update_option( 'woocommerce_email_from_address', self::EMAIL_PEDIDOS );
		update_option( 'woocommerce_email_reply_to_enabled', 'yes' );
		update_option( 'woocommerce_email_reply_to_name', 'Esmalto' );
		update_option( 'woocommerce_email_reply_to_address', self::EMAIL );
		$logo = self::url_logo();
		if ( $logo ) {
			update_option( 'woocommerce_email_header_image', $logo );
		}
		foreach ( self::COLORES_CORREO as $opcion => $color ) {
			update_option( $opcion, $color );
		}
		update_option( 'woocommerce_email_footer_text', self::pie_correo() );
		$log[] = sprintf( 'Correos de WooCommerce: remitente «Esmalto» <%1$s>, respuestas a %2$s, logotipo, colores de la marca y pie con los datos de la empresa.', self::EMAIL_PEDIDOS, self::EMAIL );

		// 5. Formularios de WPForms: avisos al correo de contacto, firmados con él.
		$log = array_merge( $log, self::formularios() );

		// Pago: teléfono obligatorio, como el correo (lo piden también las condiciones generales).
		update_option( 'woocommerce_checkout_phone_field', 'required' );
		$log[] = 'Pago: teléfono obligatorio.';

		// 6. Sin cookies de «Atribución de pedidos» (sbjs_*).
		update_option( 'woocommerce_feature_order_attribution_enabled', 'no' );
		$log[] = 'Atribución de pedidos de WooCommerce desactivada (sin cookies sbjs_*).';

		// 7. Envío: porte por m² y palé en lugar del envío por peso.
		$log = array_merge( $log, self::envio() );

		// 8. Alta propia de WholesaleX: fuera (el alta profesional es la de Esmalto, con CNAE).
		$pagina = get_page_by_path( 'wholesale-register' );
		if ( $pagina && 'publish' === $pagina->post_status ) {
			wp_update_post( array( 'ID' => $pagina->ID, 'post_status' => 'draft' ) );
			$log[] = 'Página de alta de WholesaleX (/wholesale-register/) pasada a borrador.';
		} else {
			$log[] = 'Página de alta de WholesaleX: no está publicada.';
		}

		// 9. Bloques de cesta y pago: textos y botones.
		$log = array_merge( $log, self::cesta_y_pago() );

		// 10. Altas profesionales pendientes: sin el «rol solicitado» que bloquea la aprobación en WholesaleX.
		$log[] = self::altas_pendientes();
		return $log;
	}

	/**
	 * Las altas pendientes hechas antes de la 1.5.0 guardan __wholesalex_registration_role sin el contexto
	 * que WholesaleX exige para verificarlo («The requested registration role can no longer be verified…»).
	 */
	private static function altas_pendientes() {
		$arregladas = 0;
		foreach ( get_users( array( 'meta_key' => 'esmalto_alta_pro', 'fields' => 'ID' ) ) as $id ) { // phpcs:ignore WordPress.DB.SlowDBQuery
			if ( 'pending' === get_user_meta( $id, '__wholesalex_status', true )
				&& get_user_meta( $id, '__wholesalex_registration_role', true )
				&& ! get_user_meta( $id, '__wholesalex_registration_context', true )
				&& delete_user_meta( $id, '__wholesalex_registration_role' ) ) {
				++$arregladas;
			}
		}
		return sprintf( 'Altas profesionales pendientes: %d lista(s) para aprobar en WholesaleX.', $arregladas );
	}

	/**
	 * Textos y botones que guardan los bloques de cesta y pago en sus páginas de WooCommerce:
	 * «Seguir comprando» bajo «Finalizar compra», «Totales de la cesta», el botón de confirmar con el
	 * texto de las condiciones generales y «Volver a la cesta».
	 */
	private static function cesta_y_pago() {
		$log   = array();
		$cesta = get_post( wc_get_page_id( 'cart' ) );
		if ( $cesta && has_block( 'woocommerce/cart', $cesta ) ) {
			$contenido = self::atributos_bloque( $cesta->post_content, 'woocommerce/cart-order-summary-heading-block', array( 'content' => 'Totales de la cesta' ) );
			if ( false === strpos( $contenido, 'esm-seguir-comprando' ) ) {
				$fin       = '<!-- /wp:woocommerce/proceed-to-checkout-block -->';
				$boton     = '<!-- wp:buttons {"className":"esm-seguir-comprando"} -->' . "\n" .
					'<div class="wp-block-buttons esm-seguir-comprando"><!-- wp:button {"className":"is-style-outline"} -->' . "\n" .
					'<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/tienda/">Seguir comprando</a></div>' . "\n" .
					'<!-- /wp:button --></div>' . "\n" . '<!-- /wp:buttons -->';
				$contenido = str_replace( $fin, $fin . "\n\n" . $boton, $contenido );
			}
			wp_update_post( wp_slash( array( 'ID' => $cesta->ID, 'post_content' => $contenido ) ) );
			$log[] = 'Cesta: «Seguir comprando» bajo «Finalizar compra» y «Totales de la cesta».';
		} else {
			$log[] = '⚠ Cesta: la página no usa el bloque de WooCommerce (sin cambios).';
		}
		$pago = get_post( wc_get_page_id( 'checkout' ) );
		if ( $pago && has_block( 'woocommerce/checkout', $pago ) ) {
			$contenido = self::atributos_bloque(
				$pago->post_content,
				'woocommerce/checkout-actions-block',
				array(
					'placeOrderButtonLabel'   => 'Confirmar pedido con obligación de pago',
					'showReturnToCart'        => true,
					'returnToCartButtonLabel' => 'Volver a la cesta',
				)
			);
			wp_update_post( wp_slash( array( 'ID' => $pago->ID, 'post_content' => $contenido ) ) );
			$log[] = 'Pago: botón «Confirmar pedido con obligación de pago» y «Volver a la cesta».';
		} else {
			$log[] = '⚠ Pago: la página no usa el bloque de WooCommerce (sin cambios).';
		}
		return $log;
	}

	/**
	 * Añade o cambia atributos en el comentario de un bloque, sin tocar el resto de la página.
	 */
	private static function atributos_bloque( $contenido, $bloque, $atributos ) {
		return preg_replace_callback(
			'#<!-- wp:' . preg_quote( $bloque, '#' ) . '(\s+\{.*?\})?\s*(/?)-->#s',
			static function ( $m ) use ( $bloque, $atributos ) {
				$actuales = empty( $m[1] ) ? array() : (array) json_decode( trim( $m[1] ), true );
				$json     = wp_json_encode( array_merge( $actuales, $atributos ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
				return '<!-- wp:' . $bloque . ' ' . $json . ' ' . $m[2] . '-->';
			},
			$contenido
		);
	}

	private static function url_logo() {
		$ruta = get_theme_file_path( 'assets/img/esmalto-logo.png' );
		if ( file_exists( $ruta ) ) {
			return get_theme_file_uri( 'assets/img/esmalto-logo.png' );
		}
		$id = (int) get_theme_mod( 'custom_logo' );
		return $id ? (string) wp_get_attachment_image_url( $id, 'full' ) : '';
	}

	private static function formularios() {
		$ids = get_option( Esmalto_Formularios::OPCION, array() );
		$hechos = array();
		foreach ( (array) $ids as $tipo => $id ) {
			$post = get_post( (int) $id );
			if ( ! $post || 'wpforms' !== $post->post_type ) {
				continue;
			}
			$datos = json_decode( $post->post_content, true );
			if ( ! is_array( $datos ) || empty( $datos['settings']['notifications'] ) ) {
				continue;
			}
			foreach ( $datos['settings']['notifications'] as $n => $aviso ) {
				$datos['settings']['notifications'][ $n ]['email']          = self::EMAIL;
				$datos['settings']['notifications'][ $n ]['sender_address'] = self::EMAIL;
				$datos['settings']['notifications'][ $n ]['sender_name']    = 'Esmalto';
			}
			// Mensaje al enviar: el vigente (el de muestras ya no habla de portes).
			$definicion = Esmalto_Formularios::definiciones()[ $tipo ] ?? null;
			if ( $definicion && ! empty( $datos['settings']['confirmations'][1] ) ) {
				$datos['settings']['confirmations'][1]['message'] = '<p>' . esc_html( $definicion['gracias'] ) . '</p>';
			}
			// Email y teléfono obligatorios: si uno está mal escrito, aún se puede contactar por el otro.
			foreach ( (array) $datos['fields'] as $f => $campo ) {
				if ( 'email' === $campo['type'] || 'Teléfono' === $campo['label'] ) {
					$datos['fields'][ $f ]['required'] = '1';
				}
			}
			wp_update_post(
				array(
					'ID'           => $post->ID,
					'post_content' => wp_slash( wp_json_encode( $datos ) ),
				)
			);
			$hechos[] = $tipo;
		}
		return array(
			$hechos
				? sprintf( 'Formularios (%1$s): avisos a %2$s, enviados desde %2$s; email y teléfono obligatorios.', implode( ', ', $hechos ), self::EMAIL )
				: '⚠ Formularios: no se ha encontrado ninguno de Esmalto.',
		);
	}

	/**
	 * En cada zona con «Envío por peso» activo se añade «Porte por m² y palé» (2,50 €/m² + 9 €/palé,
	 * sin IVA) y se desactiva el de peso (queda guardado por si hay que volver).
	 */
	private static function envio() {
		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			return array();
		}
		$log = array();
		foreach ( WC_Shipping_Zones::get_zones() as $datos ) {
			$zona    = new WC_Shipping_Zone( $datos['id'] );
			$metodos = $zona->get_shipping_methods();
			$peso    = null;
			$m2      = null;
			foreach ( $metodos as $metodo ) {
				if ( 'esmalto_peso' === $metodo->id && 'yes' === $metodo->enabled ) {
					$peso = $metodo;
				}
				if ( 'esmalto_m2' === $metodo->id ) {
					$m2 = $metodo;
				}
			}
			if ( ! $peso ) {
				continue;
			}
			$ruta = sprintf( '/wc/v3/shipping/zones/%d/methods', $zona->get_id() );
			if ( ! $m2 ) {
				$peticion = new WP_REST_Request( 'POST', $ruta );
				$peticion->set_body_params(
					array(
						'method_id' => 'esmalto_m2',
						'order'     => (int) $peso->method_order,
						'enabled'   => true,
						'settings'  => array(
							'title'        => __( 'Entrega en península (3 a 5 días laborables)', 'esmalto-core' ),
							'precio_m2'    => '2.50',
							'precio_palet' => '9',
							'gratis_desde' => '',
							'tax_status'   => 'taxable',
						),
					)
				);
				$respuesta = rest_do_request( $peticion );
				if ( $respuesta->is_error() ) {
					$log[] = sprintf( '⚠ Zona «%s»: no se ha podido añadir el porte por m² (%s).', $zona->get_zone_name(), $respuesta->as_error()->get_error_message() );
					continue;
				}
			}
			$peticion = new WP_REST_Request( 'PUT', $ruta . '/' . (int) $peso->instance_id );
			$peticion->set_body_params( array( 'enabled' => false ) );
			rest_do_request( $peticion );

			// La entrega va la primera (es la opción marcada al pagar); el resto conserva su orden.
			$orden = 1;
			foreach ( ( new WC_Shipping_Zone( $zona->get_id() ) )->get_shipping_methods() as $metodo ) {
				$peticion = new WP_REST_Request( 'PUT', $ruta . '/' . (int) $metodo->instance_id );
				$peticion->set_body_params( array( 'order' => 'esmalto_m2' === $metodo->id ? 0 : $orden++ ) );
				rest_do_request( $peticion );
			}
			$log[] = sprintf( 'Zona «%s»: «Porte por m² y palé» (2,50 €/m² + 9 €/palé, sin IVA) activo y «Envío por peso» desactivado.', $zona->get_zone_name() );
		}
		return $log ? $log : array( 'Envío: no había ninguna zona con «Envío por peso» activo (sin cambios).' );
	}
}
