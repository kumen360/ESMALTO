<?php
/**
 * Páginas del sitio construidas con bloques nativos y los patrones del tema «esmalto».
 * Se crean desde Esmalto → Herramientas. Después todo se edita en el editor de bloques.
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Paginas {

	const MARGEN = 'clamp(16px, 4vw, 40px)';

	/* ------------------------------------------------------------------ Bloques */

	public static function patron( $slug ) {
		$registro = WP_Block_Patterns_Registry::get_instance();
		$patron   = $registro->get_registered( $slug );
		return $patron ? trim( $patron['content'] ) : '';
	}

	public static function p( $html, $clase = '', $color = '' ) {
		$atts    = array();
		$classes = array();
		if ( $clase ) {
			$atts['className'] = $clase;
			$classes[]         = $clase;
		}
		if ( $color ) {
			$atts['textColor'] = $color;
			$classes[]         = 'has-' . $color . '-color';
			$classes[]         = 'has-text-color';
		}
		$json = $atts ? ' ' . wp_json_encode( $atts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : '';
		$cls  = $classes ? ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' : '';
		return "<!-- wp:paragraph{$json} -->\n<p{$cls}>{$html}</p>\n<!-- /wp:paragraph -->";
	}

	public static function h( $texto, $nivel = 2, $tamano = '' ) {
		$atts = array();
		if ( 2 !== $nivel ) {
			$atts['level'] = $nivel;
		}
		$clase = 'wp-block-heading';
		if ( $tamano ) {
			$atts['fontSize'] = $tamano;
			$clase           .= ' has-' . $tamano . '-font-size';
		}
		$json = $atts ? ' ' . wp_json_encode( $atts ) : '';
		return "<!-- wp:heading{$json} -->\n<h{$nivel} class=\"{$clase}\">{$texto}</h{$nivel}>\n<!-- /wp:heading -->";
	}

	public static function lista( $items, $clase = '' ) {
		$json  = $clase ? ' ' . wp_json_encode( array( 'className' => $clase ) ) : '';
		$cls   = 'wp-block-list' . ( $clase ? ' ' . $clase : '' );
		$inner = array();
		foreach ( $items as $item ) {
			$inner[] = "<!-- wp:list-item -->\n<li>{$item}</li>\n<!-- /wp:list-item -->";
		}
		return "<!-- wp:list{$json} -->\n<ul class=\"{$cls}\">" . implode( "\n\n", $inner ) . "</ul>\n<!-- /wp:list -->";
	}

	public static function shortcode( $codigo ) {
		return "<!-- wp:shortcode -->\n{$codigo}\n<!-- /wp:shortcode -->";
	}

	public static function detalle( $pregunta, $respuesta ) {
		return "<!-- wp:details -->\n<details class=\"wp-block-details\"><summary>{$pregunta}</summary>" . self::p( $respuesta ) . "</details>\n<!-- /wp:details -->";
	}

	public static function separador() {
		return "<!-- wp:separator {\"style\":{\"spacing\":{\"margin\":{\"top\":\"44px\",\"bottom\":\"24px\"}}},\"className\":\"is-style-wide\"} -->\n<hr class=\"wp-block-separator has-alpha-channel-opacity is-style-wide\" style=\"margin-top:44px;margin-bottom:24px\"/>\n<!-- /wp:separator -->";
	}

	/**
	 * Sección a ancho completo con contenido centrado.
	 */
	public static function seccion( $bloques, $ancho = '1280px', $arriba = 'clamp(32px, 5vw, 48px)', $abajo = 'clamp(60px, 9vw, 110px)', $clase = '' ) {
		$clases = trim( 'esm-seccion ' . $clase );
		$m      = self::MARGEN;
		$atts   = array(
			'align'     => 'full',
			'className' => $clases,
			'style'     => array(
				'spacing' => array(
					'padding' => array(
						'top'    => $arriba,
						'right'  => $m,
						'bottom' => $abajo,
						'left'   => $m,
					),
				),
			),
			'layout'    => array(
				'type'        => 'constrained',
				'contentSize' => $ancho,
			),
		);
		return '<!-- wp:group ' . wp_json_encode( $atts, JSON_UNESCAPED_SLASHES ) . " -->\n" .
			'<div class="wp-block-group alignfull ' . esc_attr( $clases ) . '" style="padding-top:' . $arriba . ';padding-right:' . $m . ';padding-bottom:' . $abajo . ';padding-left:' . $m . '">' .
			implode( "\n\n", array_filter( (array) $bloques ) ) .
			"</div>\n<!-- /wp:group -->";
	}

	/**
	 * Caja con fondo de superficie y borde (formularios).
	 */
	public static function caja( $bloques ) {
		$atts = array(
			'style'           => array(
				'spacing' => array(
					'padding' => array(
						'top'    => 'clamp(24px, 4vw, 40px)',
						'right'  => 'clamp(24px, 4vw, 40px)',
						'bottom' => 'clamp(24px, 4vw, 40px)',
						'left'   => 'clamp(24px, 4vw, 40px)',
					),
				),
				'border'  => array(
					'color' => 'rgba(255,255,255,0.1)',
					'width' => '1px',
				),
			),
			'backgroundColor' => 'superficie',
			'layout'          => array( 'type' => 'default' ),
		);
		$p    = 'clamp(24px, 4vw, 40px)';
		return '<!-- wp:group ' . wp_json_encode( $atts, JSON_UNESCAPED_SLASHES ) . " -->\n" .
			'<div class="wp-block-group has-border-color has-superficie-background-color has-background" style="border-color:rgba(255,255,255,0.1);border-width:1px;padding-top:' . $p . ';padding-right:' . $p . ';padding-bottom:' . $p . ';padding-left:' . $p . '">' .
			implode( "\n\n", (array) $bloques ) .
			"</div>\n<!-- /wp:group -->";
	}

	public static function cabecera( $miga, $titulo, $intro = '' ) {
		$m       = self::MARGEN;
		$bloques = array(
			self::p( '<a href="/">Inicio</a> / ' . esc_html( $miga ), 'is-style-miga' ),
			"<!-- wp:heading {\"level\":1} -->\n<h1 class=\"wp-block-heading\">" . esc_html( $titulo ) . "</h1>\n<!-- /wp:heading -->",
		);
		if ( $intro ) {
			$bloques[] = self::p( $intro, 'esm-intro', 'texto-apagado' );
		}
		return '<!-- wp:group {"align":"full","className":"esm-seccion","style":{"spacing":{"padding":{"top":"clamp(32px, 5vw, 56px)","right":"' . $m . '","bottom":"0","left":"' . $m . '"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->' . "\n" .
			'<div class="wp-block-group alignfull esm-seccion" style="padding-top:clamp(32px, 5vw, 56px);padding-right:' . $m . ';padding-bottom:0;padding-left:' . $m . '">' .
			'<!-- wp:group {"className":"esm-cabecera-pagina","style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"default"}} -->' . "\n" .
			'<div class="wp-block-group esm-cabecera-pagina">' .
			implode( "\n\n", $bloques ) .
			"</div>\n<!-- /wp:group -->" .
			"</div>\n<!-- /wp:group -->";
	}

	/**
	 * Contenedor flex vertical («Pila») con el hueco indicado entre bloques.
	 */
	public static function pila( $bloques, $hueco = '22px', $clase = '' ) {
		$atts = array(
			'style'  => array( 'spacing' => array( 'blockGap' => $hueco ) ),
			'layout' => array(
				'type'           => 'flex',
				'orientation'    => 'vertical',
				'justifyContent' => 'stretch',
			),
		);
		if ( $clase ) {
			$atts = array( 'className' => $clase ) + $atts;
		}
		$cls = 'wp-block-group' . ( $clase ? ' ' . $clase : '' );
		return '<!-- wp:group ' . wp_json_encode( $atts, JSON_UNESCAPED_SLASHES ) . " -->\n" .
			'<div class="' . esc_attr( $cls ) . '">' . implode( "\n\n", array_filter( (array) $bloques ) ) . "</div>\n<!-- /wp:group -->";
	}

	/**
	 * Título de apartado sin margen superior (el hueco lo pone el contenedor flex).
	 */
	public static function h_apartado( $texto ) {
		return "<!-- wp:heading {\"style\":{\"spacing\":{\"margin\":{\"top\":\"0\",\"bottom\":\"0\"}}},\"fontSize\":\"lg\"} -->\n<h2 class=\"wp-block-heading has-lg-font-size\" style=\"margin-top:0;margin-bottom:0\">{$texto}</h2>\n<!-- /wp:heading -->";
	}

	/**
	 * Lista numerada.
	 */
	public static function lista_numerada( $items ) {
		$inner = array();
		foreach ( $items as $item ) {
			$inner[] = "<!-- wp:list-item -->\n<li>{$item}</li>\n<!-- /wp:list-item -->";
		}
		return "<!-- wp:list {\"ordered\":true} -->\n<ol class=\"wp-block-list\">" . implode( "\n\n", $inner ) . "</ol>\n<!-- /wp:list -->";
	}

	/**
	 * Pregunta con su respuesta visible (como en el diseño).
	 */
	public static function pregunta( $pregunta, $respuesta ) {
		return "<!-- wp:group {\"className\":\"esm-faq__item\",\"layout\":{\"type\":\"default\"}} -->\n<div class=\"wp-block-group esm-faq__item\">" .
			self::p( $pregunta, 'esm-faq__pregunta' ) . "\n\n" . self::p( $respuesta, 'esm-faq__respuesta' ) .
			"</div>\n<!-- /wp:group -->";
	}

	/**
	 * Página de texto (legal / información) con apartados [titulo, texto|lista].
	 */
	public static function documento( $miga, $titulo, $intro, $apartados, $extra = array() ) {
		$bloques = array(
			self::p( '<a href="/">Inicio</a> / ' . esc_html( $miga ), 'is-style-miga' ),
			"<!-- wp:heading {\"level\":1} -->\n<h1 class=\"wp-block-heading\">" . esc_html( $titulo ) . "</h1>\n<!-- /wp:heading -->",
			self::p( $intro, 'esm-documento__intro', 'texto-suave' ),
		);
		foreach ( $apartados as $apartado ) {
			$bloques[] = self::h( $apartado[0], 2, 'lg' );
			foreach ( array_slice( $apartado, 1 ) as $contenido ) {
				if ( is_array( $contenido ) ) {
					$bloques[] = self::lista( $contenido );
				} else {
					// Un bloque ya montado («<!-- wp:…») se añade tal cual; el texto, como párrafo.
					$bloques[] = 0 === strpos( $contenido, '<!-- wp:' ) ? $contenido : self::p( $contenido, '', 'texto-apagado' );
				}
			}
		}
		foreach ( $extra as $bloque ) {
			$bloques[] = $bloque;
		}
		$bloques[] = self::separador();
		$bloques[] = self::p( 'Esmalto Cerámica · Kumen Tres Sesenta, S.L. · NIF B88655014 · Sanlúcar de Barrameda (Cádiz) · info@esmalto.com · +34 614 05 10 42', 'esm-documento__pie', 'texto-mudo' );
		return self::seccion( $bloques, '820px', 'clamp(32px, 5vw, 56px)', 'clamp(60px, 9vw, 110px)', 'esm-documento' );
	}

	/* ------------------------------------------------------------------ Páginas */

	public static function definiciones() {
		return array(
			'inicio'                 => array( 'titulo' => 'Inicio', 'contenido' => array( __CLASS__, 'inicio' ) ),
			'tienda'                 => array( 'titulo' => 'Tienda', 'woo' => 'shop', 'contenido' => array( __CLASS__, 'tienda' ) ),
			'profesionales'          => array( 'titulo' => 'Profesionales', 'contenido' => array( __CLASS__, 'profesionales' ) ),
			'contacto'               => array( 'titulo' => 'Contacto', 'contenido' => array( __CLASS__, 'contacto' ) ),
			'presupuesto'            => array( 'titulo' => 'Solicitar presupuesto', 'contenido' => array( __CLASS__, 'presupuesto' ) ),
			'preguntas-frecuentes'   => array( 'titulo' => 'Preguntas frecuentes', 'contenido' => array( __CLASS__, 'faq' ) ),
			'muestras'               => array( 'titulo' => 'Muestras', 'contenido' => array( __CLASS__, 'muestras' ) ),
			'formas-de-pago'         => array( 'titulo' => 'Formas de pago', 'contenido' => array( __CLASS__, 'pagos' ) ),
			'gastos-de-envio'        => array( 'titulo' => 'Cómo calcular gastos de envío', 'contenido' => array( __CLASS__, 'envios' ) ),
			'condiciones-generales'  => array( 'titulo' => 'Condiciones generales', 'contenido' => array( __CLASS__, 'condiciones' ) ),
			'aviso-legal'            => array( 'titulo' => 'Aviso legal', 'contenido' => array( __CLASS__, 'aviso_legal' ) ),
			'politica-de-privacidad' => array( 'titulo' => 'Política de privacidad', 'privacidad' => true, 'contenido' => array( __CLASS__, 'privacidad' ) ),
			'politica-de-cookies'    => array( 'titulo' => 'Política de cookies', 'contenido' => array( __CLASS__, 'cookies' ) ),
			'mi-cuenta'              => array( 'titulo' => 'Mi cuenta', 'woo' => 'myaccount', 'contenido' => array( __CLASS__, 'mi_cuenta' ) ),
			'cesta'                  => array( 'titulo' => 'Cesta', 'woo' => 'cart', 'contenido' => null ),
			'finalizar-compra'       => array( 'titulo' => 'Finalizar compra', 'woo' => 'checkout', 'contenido' => null ),
		);
	}

	public static function inicio() {
		return implode(
			"\n\n",
			array(
				self::patron( 'esmalto/portada' ),
				self::patron( 'esmalto/compra-por-espacio' ),
				self::patron( 'esmalto/compra-por-formato' ),
				self::patron( 'esmalto/colecciones-destacadas' ),
				self::patron( 'esmalto/banner-profesional' ),
			)
		);
	}

	public static function tienda() {
		return self::patron( 'esmalto/intro-tienda' );
	}

	public static function profesionales() {
		return implode(
			"\n\n",
			array(
				self::patron( 'esmalto/profesionales-portada' ),
				self::patron( 'esmalto/profesionales-acceso' ),
				self::patron( 'esmalto/ventajas' ),
				self::patron( 'esmalto/proceso-activacion' ),
				self::patron( 'esmalto/preguntas-frecuentes' ),
			)
		);
	}

	public static function contacto() {
		return self::cabecera( 'Contacto', 'Contacto', 'Escríbenos y te responderá el departamento correspondiente. Para pedidos en curso, indica tu número de pedido.' ) . "\n\n" . self::patron( 'esmalto/contacto' );
	}

	public static function presupuesto() {
		return self::cabecera( 'Presupuesto', 'Solicitar presupuesto', 'Indícanos el producto, los metros y dónde lo necesitas. Te respondemos en 24 h laborables. Si eres profesional, inicia sesión para ver tu tarifa.' ) . "\n\n" .
			self::seccion( array( self::caja( array( self::shortcode( '[esmalto_formulario tipo="presupuesto"]' ) ) ) ), '900px' );
	}

	public static function faq() {
		$preguntas = array(
			array( '¿Cómo accedo a los precios profesionales?', 'Rellena el <a href="/profesionales/">formulario de alta profesional</a> con tu CNAE. Verificamos tu perfil y activamos tu cuenta con tu categoría de precios en 24–48 h.' ),
			array( '¿Cómo calculo cuántas cajas necesito?', 'En cada producto tienes una calculadora: escribe los m² y te indica las cajas (siempre completas), los m² reales y el coste total. Te recomendamos añadir un 10 % de merma por cortes.' ),
			array( '¿Por qué se venden por cajas?', 'La cerámica se sirve en cajas completas para garantizar el mismo lote y tono en toda la superficie. Pide todo el material de una vez.' ),
			array( '¿Enviáis a obra en toda la península?', 'Sí. Entregamos a domicilio o a pie de obra en 3 a 5 días laborables desde la confirmación del pedido. El porte se calcula por m² y por palé: consulta <a href="/gastos-de-envio/">cómo se calculan los gastos de envío</a>.' ),
			array( '¿Puedo recoger el pedido en vuestro almacén?', 'Sí, elige «Recogida en almacén» al finalizar la compra. Es gratis y te avisamos cuando esté preparado.' ),
			array( '¿Puedo pedir muestras antes de comprar?', 'Sí. Las muestras son gratuitas y solo se pagan los portes. Consulta la página de <a href="/muestras/">Muestras</a>.' ),
			array( '¿Qué formas de pago aceptáis?', 'Tarjeta de crédito o débito (pago seguro con Stripe) y transferencia bancaria.' ),
			array( '¿Hacéis envíos a Baleares, Canarias, Ceuta o Melilla?', 'Sí, bajo presupuesto. <a href="/contacto/">Escríbenos</a> con el pedido y el código postal.' ),
		);
		$items = array();
		foreach ( $preguntas as $q ) {
			$items[] = self::pregunta( $q[0], $q[1] );
		}
		$bloques = array(
			self::p( '<a href="/">Inicio</a> / FAQ', 'is-style-miga' ),
			"<!-- wp:heading {\"level\":1} -->\n<h1 class=\"wp-block-heading\">Preguntas frecuentes</h1>\n<!-- /wp:heading -->",
			self::p( '¿Tienes otra duda? Escríbenos a <a href="mailto:info@esmalto.com">info@esmalto.com</a> o llama al <a href="tel:+34614051042">+34 614 05 10 42</a> (de lunes a viernes, de 9:00 a 14:00 y de 16:00 a 19:00).', 'esm-documento__intro', 'texto-suave' ),
			"<!-- wp:group {\"className\":\"esm-faq esm-faq--pagina\",\"layout\":{\"type\":\"default\"}} -->\n<div class=\"wp-block-group esm-faq esm-faq--pagina\">" . implode( "\n\n", $items ) . "</div>\n<!-- /wp:group -->",
		);
		return self::seccion( $bloques, '820px', 'clamp(32px, 5vw, 56px)', 'clamp(60px, 9vw, 110px)', 'esm-documento' );
	}

	/**
	 * Muestras (textos del repaso del 10/10). Todo el contenido va en contenedores flex («Pila»);
	 * al final se mantiene el formulario, que es adonde lleva «Solicitar una muestra» en cada ficha.
	 */
	public static function muestras() {
		$cabecera  = self::pila(
			array(
				self::p( '<a href="/">Inicio</a> / Muestras', 'is-style-miga' ),
				"<!-- wp:heading {\"level\":1} -->\n<h1 class=\"wp-block-heading\">Muestras</h1>\n<!-- /wp:heading -->",
				self::p( 'Una foto no enseña cómo cambia un porcelánico con la luz de tu obra ni cómo es al tacto. Pide la pieza antes de decidir: la muestra no se cobra, solo pagas el transporte.', 'esm-documento__intro', 'texto-suave' ),
			)
		);
		$como      = self::pila(
			array(
				self::h_apartado( 'Cómo pedirlas' ),
				self::lista_numerada(
					array(
						'En la ficha del producto, elige color y formato y pulsa «Solicitar una muestra».',
						'Añade todas las que necesites; van en la misma solicitud.',
						'Te confirmamos el coste del transporte y, cuando lo aceptes, salen hacia tu dirección.',
					)
				),
			),
			'18px'
		);
		$saber     = self::pila(
			array(
				self::h_apartado( 'Lo que conviene saber' ),
				self::lista(
					array(
						'<strong>Tamaño.</strong> Enviamos una pieza o un corte de hasta 25 × 25 cm; los formatos pequeños van enteros. Si no hay corte del formato elegido, te mandamos otro de la misma serie y color.',
						'<strong>Formato.</strong> Pídela en el formato que vayas a colocar: una misma serie puede variar ligeramente de tono de un formato a otro.',
						'<strong>Para qué sirve.</strong> Para comprobar color, acabado y tacto. Un golpe en el transporte que no impida esa comprobación no da derecho a devolver los portes.',
						'<strong>Cantidad.</strong> Una muestra por referencia y cliente.',
						'<strong>Envío.</strong> Unas muestras salen de nuestro almacén y otras directamente de fábrica, así que pueden llegar en paquetes distintos.',
						'<strong>Fuera de la península.</strong> El transporte lleva suplemento.',
					)
				),
			),
			'18px'
		);
		$solicitud = self::pila(
			array(
				self::h_apartado( 'Solicitud de muestras' ),
				self::caja( array( self::shortcode( '[esmalto_formulario tipo="muestras"]' ) ) ),
			),
			'18px'
		);
		// Pie con filete superior (un separador dentro de un contenedor flex se queda sin ancho).
		$pie       = '<!-- wp:group {"style":{"border":{"top":{"color":"rgba(255,255,255,0.12)","width":"1px"}},"spacing":{"padding":{"top":"24px"}}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->' . "\n" .
			'<div class="wp-block-group" style="border-top-color:rgba(255,255,255,0.12);border-top-width:1px;padding-top:24px">' .
			self::p( 'Esmalto Cerámica · Kumen Tres Sesenta, S.L. · NIF B88655014 · Sanlúcar de Barrameda (Cádiz) · info@esmalto.com · +34 614 05 10 42', 'esm-documento__pie', 'texto-mudo' ) .
			"</div>\n<!-- /wp:group -->";
		return self::seccion(
			array( self::pila( array( $cabecera, $como, $saber, $solicitud, $pie ), '48px', 'esm-muestras' ) ),
			'820px',
			'clamp(32px, 5vw, 56px)',
			'clamp(60px, 9vw, 110px)',
			'esm-documento'
		);
	}

	public static function pagos() {
		return self::documento(
			'Formas de pago',
			'Formas de pago',
			'En Esmalto puedes pagar tus pedidos de forma segura con estos métodos:',
			array(
				array( 'Tarjeta de crédito o débito', 'Pago seguro con Stripe. Aceptamos Visa, Mastercard, American Express y, según tu dispositivo, Apple Pay y Google Pay. Los datos de la tarjeta los gestiona Stripe: Esmalto no los almacena.' ),
				array( 'Transferencia bancaria', 'Tras realizar el pedido te mostramos los datos bancarios. Mantenemos el pedido reservado 3 días: si en ese plazo no recibimos el pago ni el justificante en info@esmalto.com, el pedido se cancela. Los pedidos por transferencia se preparan cuando se confirma el cobro.' ),
				array( 'Profesionales', 'Las cuentas profesionales pueden acordar condiciones de pago con su comercial.' ),
			)
		);
	}

	public static function envios() {
		return self::documento(
			'Gastos de envío',
			'Cómo calcular gastos de envío',
			'El porte depende de los m² del pedido y de los palés que ocupa. Lo verás calculado en la cesta antes de pagar.',
			array(
				array( 'Entrega a domicilio u obra (península)', array( 'Porte: 2,50 € por m² (+ IVA).', 'Palés: 9 € por palé, aparte del porte (+ IVA).', 'Entrega a pie de calle; para descarga en obra consúltanos.' ) ),
				array( 'Plazo de entrega', 'Península: entrega en 3 a 5 días laborables desde la confirmación del pedido. Baleares, Canarias, Ceuta y Melilla: bajo presupuesto.' ),
				array( 'Recogida en almacén', 'Gratis. Elige «Recogida en almacén» al finalizar la compra y te avisaremos cuando el pedido esté listo.', self::pila( array( self::shortcode( '[esmalto_recogida]' ) ), '12px', 'esm-recogida-datos' ) ),
				array( 'Baleares, Canarias, Ceuta y Melilla', 'Envíos bajo presupuesto: escríbenos con el pedido y el código postal.' ),
			)
		);
	}

	public static function condiciones() {
		return self::documento(
			'Condiciones generales',
			'Condiciones generales',
			'Condiciones generales de contratación de Esmalto Cerámica, marca comercial de Kumen Tres Sesenta, S.L. (NIF B88655014), con domicilio en C/ Bolsa, 44, 11540 Sanlúcar de Barrameda (Cádiz). [Texto pendiente de redacción legal definitiva].',
			array(
				array( 'Objeto', 'Estas condiciones regulan la compra de productos a través de la tienda online de Esmalto.' ),
				array( 'Pedidos y precios', 'Los productos se venden por cajas completas. Los precios para particulares incluyen IVA y no incluyen portes. Los clientes profesionales con cuenta activada ven su tarifa neta sin IVA.' ),
				array( 'Entrega', 'Entrega a domicilio u obra según la tarifa de envío vigente, o recogida gratuita en almacén.' ),
				array( 'Devoluciones', 'Pendiente de redacción legal definitiva.' ),
			)
		);
	}

	public static function aviso_legal() {
		return self::documento(
			'Aviso legal',
			'Aviso legal',
			'Titular del sitio web: Kumen Tres Sesenta, S.L. · NIF B88655014 · C/ Bolsa, 44, 11540 Sanlúcar de Barrameda (Cádiz) · Registro Mercantil de Cádiz, hoja CA-69375. Email: info@esmalto.com. Teléfono: +34 614 05 10 42. Esmalto Cerámica es una marca comercial de Kumen Tres Sesenta, S.L. [Texto pendiente de redacción legal definitiva].',
			array(
				array( 'Condiciones de uso', 'El acceso y uso de este sitio web atribuye la condición de usuario y supone la aceptación de este aviso legal.' ),
				array( 'Propiedad intelectual', 'Los contenidos, imágenes de producto y marcas son propiedad de Esmalto o de sus respectivos titulares.' ),
			)
		);
	}

	public static function privacidad() {
		return self::documento(
			'Política de privacidad',
			'Política de privacidad',
			'Responsable del tratamiento: Kumen Tres Sesenta, S.L. (Esmalto Cerámica) · NIF B88655014 · C/ Bolsa, 44, 11540 Sanlúcar de Barrameda (Cádiz). Contacto: info@esmalto.com. [Texto pendiente de redacción legal definitiva].',
			array(
				array( 'Datos que tratamos', 'Los que nos facilitas en los formularios de contacto, muestras, presupuesto, alta de cliente o profesional y en tus pedidos, para gestionar tu solicitud, tu cuenta y tus compras. En los pedidos te pedimos el NIF o DNI para emitir la factura. En el alta profesional tratamos también los datos de tu empresa y su CNAE para verificar tu acceso a la tarifa profesional.' ),
				array( 'Pagos', 'Los pagos con tarjeta los procesa Stripe; Esmalto no almacena los datos de tu tarjeta.' ),
				array( 'Derechos', 'Puedes ejercer tus derechos de acceso, rectificación, supresión, oposición, limitación y portabilidad escribiendo a info@esmalto.com.' ),
			)
		);
	}

	public static function cookies() {
		return self::documento(
			'Política de cookies',
			'Política de cookies',
			'Esta web usa únicamente cookies técnicas necesarias para su funcionamiento (sesión, cesta, inicio de sesión y pago seguro). [Revisar si se añaden herramientas de analítica o publicidad: en ese caso hará falta un banner de consentimiento].',
			array(
				array( 'Cookies técnicas', array( 'WordPress: inicio de sesión y preferencias.', 'WooCommerce: cesta y sesión de compra.', 'Stripe: prevención del fraude en el pago con tarjeta.' ) ),
				array( 'Cómo desactivarlas', 'Puedes bloquear o borrar las cookies desde la configuración de tu navegador; algunas funciones de la tienda podrían dejar de funcionar.' ),
			)
		);
	}

	public static function mi_cuenta() {
		return self::cabecera( 'Mi cuenta', 'Mi cuenta', 'Consulta tus pedidos, direcciones y datos. ¿Eres profesional del sector? <a href="/profesionales/">Solicita tu acceso profesional</a>.' ) . "\n\n" .
			self::seccion( array( self::shortcode( '[woocommerce_my_account]' ) ) );
	}

	/**
	 * Crea o actualiza las páginas. Sin $forzar no se sobrescribe el contenido de páginas ya creadas por Esmalto.
	 *
	 * @return array registro de acciones.
	 */
	public static function crear( $forzar = false ) {
		$log = array();
		$ids = get_option( 'esmalto_paginas', array() );
		foreach ( self::definiciones() as $slug => $def ) {
			$existente = null;
			if ( ! empty( $def['woo'] ) && function_exists( 'wc_get_page_id' ) ) {
				$woo_id = wc_get_page_id( $def['woo'] );
				if ( $woo_id > 0 && get_post( $woo_id ) ) {
					$existente = get_post( $woo_id );
				}
			}
			if ( ! empty( $def['privacidad'] ) ) {
				$priv_id = (int) get_option( 'wp_page_for_privacy_policy' );
				if ( $priv_id && get_post( $priv_id ) && 'trash' !== get_post_status( $priv_id ) ) {
					$existente = get_post( $priv_id );
				}
			}
			if ( ! $existente ) {
				$existente = get_page_by_path( $slug );
			}
			$contenido = $def['contenido'] ? call_user_func( $def['contenido'] ) : null;

			if ( $existente ) {
				$datos = array(
					'ID'          => $existente->ID,
					'post_title'  => $def['titulo'],
					'post_name'   => $slug,
					'post_status' => 'publish',
				);
				$nuestra = (bool) get_post_meta( $existente->ID, '_esmalto_pagina', true );
				if ( null !== $contenido && ( $forzar || ! $nuestra ) ) {
					$datos['post_content'] = $contenido;
				}
				wp_update_post( wp_slash( $datos ) );
				$id    = $existente->ID;
				$log[] = sprintf( 'Página «%s» actualizada%s.', $def['titulo'], isset( $datos['post_content'] ) ? '' : ' (contenido conservado)' );
			} else {
				$id = wp_insert_post(
					wp_slash(
						array(
							'post_type'    => 'page',
							'post_status'  => 'publish',
							'post_title'   => $def['titulo'],
							'post_name'    => $slug,
							'post_content' => (string) $contenido,
						)
					)
				);
				$log[] = sprintf( 'Página «%s» creada.', $def['titulo'] );
			}
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_esmalto_pagina', $slug );
				$ids[ $slug ] = $id;
			}
		}
		update_option( 'esmalto_paginas', $ids );

		// Portada, páginas de WooCommerce y privacidad.
		if ( ! empty( $ids['inicio'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $ids['inicio'] );
		}
		$woo = array(
			'woocommerce_shop_page_id'      => 'tienda',
			'woocommerce_myaccount_page_id' => 'mi-cuenta',
			'woocommerce_cart_page_id'      => 'cesta',
			'woocommerce_checkout_page_id'  => 'finalizar-compra',
			'woocommerce_terms_page_id'     => 'condiciones-generales',
		);
		foreach ( $woo as $opcion => $slug ) {
			if ( ! empty( $ids[ $slug ] ) ) {
				update_option( $opcion, $ids[ $slug ] );
			}
		}
		if ( ! empty( $ids['politica-de-privacidad'] ) ) {
			update_option( 'wp_page_for_privacy_policy', $ids['politica-de-privacidad'] );
		}
		$log[] = 'Portada, tienda, cesta, pago, mi cuenta, condiciones y privacidad asignadas.';
		return $log;
	}
}
