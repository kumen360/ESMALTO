<?php
/**
 * Esmalto → Herramientas: puesta en marcha en un clic, estado del sitio, fichas técnicas,
 * textos ALT de imágenes y carga de tarifa por SKU.
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Herramientas {

	const PAGINA = 'esmalto-herramientas';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_action( 'admin_post_esmalto_configurar', array( __CLASS__, 'accion_configurar' ) );
		add_action( 'admin_post_esmalto_tarifa', array( __CLASS__, 'accion_tarifa' ) );
		add_action( 'admin_post_esmalto_plantilla', array( __CLASS__, 'accion_plantilla' ) );
		add_action( 'wp_ajax_esmalto_lote', array( __CLASS__, 'ajax_lote' ) );

		// Importación de productos: lotes pequeños, más tiempo y menos tamaños de imagen.
		add_filter( 'woocommerce_product_import_batch_size', array( __CLASS__, 'lote_importacion' ) );
		add_action( 'wp_ajax_woocommerce_do_ajax_product_import', array( __CLASS__, 'preparar_importacion' ), 1 );
	}

	public static function lote_importacion() {
		return 1;
	}

	public static function preparar_importacion() {
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		}
		add_filter( 'intermediate_image_sizes_advanced', array( __CLASS__, 'tamanos_imagen' ) );
	}

	/**
	 * Durante la importación solo se generan los tamaños que usan la tienda y el tema.
	 */
	public static function tamanos_imagen( $tamanos ) {
		$utiles = array( 'thumbnail', 'medium', 'large', 'woocommerce_thumbnail', 'woocommerce_single', 'woocommerce_gallery_thumbnail' );
		return array_intersect_key( $tamanos, array_flip( $utiles ) );
	}

	public static function menu() {
		add_submenu_page( 'esmalto-ajustes', __( 'Herramientas', 'esmalto-core' ), __( 'Herramientas', 'esmalto-core' ), 'manage_woocommerce', self::PAGINA, array( __CLASS__, 'pagina' ) );
	}

	/* ------------------------------------------------------------------ Estado */

	private static function estado() {
		global $wpdb;
		$tema       = wp_get_theme();
		$productos  = function_exists( 'wc_get_products' ) ? count( wc_get_products( array( 'limit' => -1, 'return' => 'ids', 'status' => array( 'publish', 'draft' ) ) ) ) : 0;
		$con_precio = 0;
		$sin_ficha  = 0;
		if ( $productos ) {
			$con_precio = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.post_type = 'product_variation' AND pm.meta_key = '_regular_price' AND pm.meta_value <> ''" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$sin_ficha  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} a WHERE a.meta_key = '_ficha_tecnica' AND a.meta_value <> '' AND NOT EXISTS (SELECT 1 FROM {$wpdb->postmeta} b WHERE b.post_id = a.post_id AND b.meta_key = '_ficha_tecnica_id' AND b.meta_value <> '')" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}
		$fotos = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_wc_attachment_source'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$roles = esmalto_ajustes();
		return array(
			array( 'WooCommerce activo', class_exists( 'WooCommerce' ) ),
			array( 'Tema «Esmalto» (hijo de Astra) activo', 'esmalto' === $tema->get_stylesheet() ),
			array( 'WholesaleX activo', esmalto_wholesalex_activo() ),
			array( 'WPForms activo', post_type_exists( 'wpforms' ) ),
			array( 'Stripe instalado', class_exists( 'WC_Stripe' ) || defined( 'WC_STRIPE_VERSION' ) ),
			array( 'Formularios creados', Esmalto_Formularios::id( 'contacto' ) && Esmalto_Formularios::id( 'muestras' ) && Esmalto_Formularios::id( 'presupuesto' ) ),
			array( 'Páginas creadas', (bool) get_option( 'esmalto_paginas' ) ),
			array( sprintf( 'Fotos del catálogo en la biblioteca (%d)', $fotos ), $fotos > 0 ),
			array( sprintf( 'Productos importados (%d)', $productos ), $productos > 0 ),
			array( sprintf( 'Variaciones con precio (%d)', $con_precio ), $con_precio > 0 ),
			array( sprintf( 'Fichas técnicas pendientes de importar (%d)', $sin_ficha ), $productos > 0 && 0 === $sin_ficha ),
			array( 'Categorías A/B/C asignadas a roles de WholesaleX', $roles['cat_a_rol'] && $roles['cat_b_rol'] && $roles['cat_c_rol'] ),
			array( 'Modo «Próximamente» desactivado (web pública)', 'yes' !== get_option( 'woocommerce_coming_soon' ) ),
		);
	}

	/* ------------------------------------------------------------------ Página */

	public static function pagina() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$log = get_transient( 'esmalto_log_' . get_current_user_id() );
		delete_transient( 'esmalto_log_' . get_current_user_id() );
		?>
		<div class="wrap esmalto-herramientas">
			<h1><?php esc_html_e( 'Esmalto · Herramientas', 'esmalto-core' ); ?></h1>

			<?php if ( $log ) : ?>
				<div class="notice notice-success"><ul style="list-style:disc;padding-left:20px">
					<?php foreach ( (array) $log as $linea ) : ?>
						<li><?php echo esc_html( $linea ); ?></li>
					<?php endforeach; ?>
				</ul></div>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Estado', 'esmalto-core' ); ?></h2>
			<table class="widefat striped" style="max-width:760px">
				<tbody>
					<?php foreach ( self::estado() as $fila ) : ?>
						<tr><td><?php echo esc_html( $fila[0] ); ?></td><td style="width:60px"><?php echo $fila[1] ? '✅' : '⬜'; ?></td></tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( '1. Puesta en marcha', 'esmalto-core' ); ?></h2>
			<p><?php esc_html_e( 'Configura WooCommerce (España, euros, IVA 21 %, envíos por peso y recogida, transferencia), enlaces permanentes, logo e icono, filtros de la tienda, formularios y páginas. Se puede repetir: no sobrescribe el contenido de las páginas ya creadas salvo que marques la casilla.', 'esmalto-core' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="esmalto_configurar">
				<?php wp_nonce_field( 'esmalto_configurar' ); ?>
				<label><input type="checkbox" name="forzar" value="1"> <?php esc_html_e( 'Sobrescribir páginas, formularios y filtros de la tienda con la versión original de Esmalto', 'esmalto-core' ); ?></label><br>
				<label><input type="checkbox" name="proximamente" value="1" checked> <?php esc_html_e( 'Activar el modo «Próximamente» de WooCommerce (solo los administradores ven la web)', 'esmalto-core' ); ?></label>
				<?php submit_button( __( 'Configurar sitio', 'esmalto-core' ), 'primary', 'submit', true ); ?>
			</form>

			<h2><?php esc_html_e( '2. Catálogo', 'esmalto-core' ); ?></h2>
			<p><?php esc_html_e( 'Antes de importar el CSV en Productos → Importar, precarga sus fotos: se descargan en lotes pequeños, con su texto ALT, y la importación las reutiliza en lugar de descargarlas otra vez.', 'esmalto-core' ); ?></p>
			<p><button type="button" class="button button-primary" data-esmalto-lote="fotos"><?php esc_html_e( 'Precargar fotos del catálogo', 'esmalto-core' ); ?></button></p>
			<p><?php esc_html_e( 'Después de importar los productos, importa las fichas técnicas (PDF) desde el repositorio del catálogo. El botón de textos ALT vuelve a aplicarlos a todas las fotos importadas.', 'esmalto-core' ); ?></p>
			<p>
				<button type="button" class="button button-primary" data-esmalto-lote="fichas"><?php esc_html_e( 'Importar fichas técnicas', 'esmalto-core' ); ?></button>
				<button type="button" class="button" data-esmalto-lote="alt"><?php esc_html_e( 'Aplicar textos ALT a las imágenes', 'esmalto-core' ); ?></button>
			</p>
			<pre id="esmalto-lote-log" style="max-width:760px;max-height:260px;overflow:auto;background:#fff;border:1px solid #ccd0d4;padding:10px;display:none"></pre>

			<h2><?php esc_html_e( '3. Tarifa de precios', 'esmalto-core' ); ?></h2>
			<p><?php esc_html_e( 'Pega una línea por referencia: «SKU;precio». El precio puede ser por m² (se multiplica por los m² de cada caja) o por caja. Para particulares el precio guardado es sin IVA salvo que marques que incluye IVA.', 'esmalto-core' ); ?></p>
			<p><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=esmalto_plantilla' ), 'esmalto_plantilla' ) ); ?>"><?php esc_html_e( 'Descargar plantilla (CSV con todos los SKU)', 'esmalto-core' ); ?></a></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="esmalto_tarifa">
				<?php wp_nonce_field( 'esmalto_tarifa' ); ?>
				<textarea name="tarifa" rows="10" class="large-text code" placeholder="ESM-AMBROSSIA-GREIGE-100X100;24,35"></textarea>
				<p>
					<label><input type="radio" name="unidad" value="m2" checked> <?php esc_html_e( 'Precio por m²', 'esmalto-core' ); ?></label>&nbsp;&nbsp;
					<label><input type="radio" name="unidad" value="caja"> <?php esc_html_e( 'Precio por caja', 'esmalto-core' ); ?></label>&nbsp;&nbsp;
					<label><input type="checkbox" name="con_iva" value="1"> <?php esc_html_e( 'Los precios incluyen IVA (21 %)', 'esmalto-core' ); ?></label>
				</p>
				<?php submit_button( __( 'Cargar tarifa', 'esmalto-core' ), 'secondary' ); ?>
			</form>
		</div>
		<script>
		( function () {
			var log = document.getElementById( 'esmalto-lote-log' );
			function escribir( t ) { log.style.display = 'block'; log.textContent += t + '\n'; log.scrollTop = log.scrollHeight; }
			function lote( tarea, desde, boton, intento ) {
				var datos = new FormData();
				intento = intento || 0;
				datos.append( 'action', 'esmalto_lote' );
				datos.append( 'tarea', tarea );
				datos.append( 'desde', desde );
				datos.append( '_wpnonce', <?php echo wp_json_encode( wp_create_nonce( 'esmalto_lote' ) ); ?> );
				fetch( ajaxurl, { method: 'POST', body: datos, credentials: 'same-origin' } )
					.then( function ( r ) { if ( ! r.ok ) { throw new Error( 'HTTP ' + r.status ); } return r.json(); } )
					.then( function ( r ) {
						if ( ! r.success ) { escribir( '✖ ' + ( r.data || 'Error' ) ); boton.disabled = false; return; }
						( r.data.log || [] ).forEach( escribir );
						if ( r.data.siguiente !== null ) { lote( tarea, r.data.siguiente, boton ); } else { escribir( '✔ Terminado.' ); boton.disabled = false; }
					} )
					.catch( function ( e ) {
						// Un corte del servidor no detiene el proceso: se repite el mismo lote (lo ya hecho se salta).
						if ( intento < 3 ) {
							escribir( '… reintentando (' + e.message + ')' );
							setTimeout( function () { lote( tarea, desde, boton, intento + 1 ); }, 8000 );
							return;
						}
						escribir( '✖ ' + e ); boton.disabled = false;
					} );
			}
			document.querySelectorAll( '[data-esmalto-lote]' ).forEach( function ( b ) {
				b.addEventListener( 'click', function () { b.disabled = true; escribir( '— ' + b.textContent ); lote( b.getAttribute( 'data-esmalto-lote' ), 0, b ); } );
			} );
		}() );
		</script>
		<?php
	}

	private static function volver( $log ) {
		set_transient( 'esmalto_log_' . get_current_user_id(), $log, 300 );
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGINA ) );
		exit;
	}

	/* ------------------------------------------------------------------ Configurar */

	public static function accion_configurar() {
		if ( ! current_user_can( 'manage_woocommerce' ) || ! check_admin_referer( 'esmalto_configurar' ) ) {
			wp_die( esc_html__( 'Sin permisos.', 'esmalto-core' ) );
		}
		@set_time_limit( 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		$forzar = ! empty( $_POST['forzar'] );
		$log    = array();

		$log = array_merge( $log, self::configurar_wordpress() );
		if ( class_exists( 'WooCommerce' ) ) {
			$log = array_merge( $log, self::configurar_woocommerce( ! empty( $_POST['proximamente'] ) ) );
			$log = array_merge( $log, self::configurar_envios() );
			$log = array_merge( $log, self::configurar_filtros( $forzar ) );
		}
		$formularios = Esmalto_Formularios::crear( $forzar );
		$log[]       = is_wp_error( $formularios ) ? '⚠ Formularios: ' . $formularios->get_error_message() : 'Formularios de WPForms listos (contacto, muestras y presupuesto).';
		$log         = array_merge( $log, Esmalto_Paginas::crear( $forzar ) );

		Esmalto_B2B::registrar_endpoint();
		flush_rewrite_rules();
		$log[] = 'Enlaces permanentes regenerados.';
		self::volver( $log );
	}

	private static function configurar_wordpress() {
		$log = array();
		update_option( 'blogname', 'Esmalto' );
		update_option( 'blogdescription', 'Cerámica para profesionales' );
		update_option( 'permalink_structure', '/%postname%/' );
		update_option( 'default_comment_status', 'closed' );
		update_option( 'default_ping_status', 'closed' );
		update_option( 'date_format', 'd/m/Y' );
		update_option( 'time_format', 'H:i' );
		update_option( 'start_of_week', 1 );
		$log[] = 'WordPress: nombre, descripción, enlaces /%postname%/, comentarios cerrados y formato de fecha.';

		// Contenido de ejemplo a la papelera (recuperable).
		foreach ( array( '¡Hola, mundo!', 'Hello world!' ) as $titulo ) {
			$posts = get_posts( array( 'post_type' => 'post', 'title' => $titulo, 'post_status' => 'publish', 'numberposts' => 1 ) );
			if ( $posts ) {
				wp_trash_post( $posts[0]->ID );
				$log[] = 'Entrada de ejemplo enviada a la papelera.';
			}
		}
		foreach ( array( 'sample-page', 'pagina-ejemplo' ) as $slug ) {
			$pagina = get_page_by_path( $slug );
			if ( $pagina ) {
				wp_trash_post( $pagina->ID );
				$log[] = 'Página de ejemplo enviada a la papelera.';
			}
		}

		// Logo e icono del sitio desde el tema.
		if ( 'esmalto' === get_stylesheet() ) {
			if ( ! get_theme_mod( 'custom_logo' ) ) {
				$logo = self::adjuntar_archivo_tema( 'assets/img/esmalto-logo.png', 'Esmalto · Cerámica para profesionales' );
				if ( $logo ) {
					set_theme_mod( 'custom_logo', $logo );
					$log[] = 'Logo asignado.';
				}
			}
			if ( ! get_option( 'site_icon' ) ) {
				$icono = self::adjuntar_archivo_tema( 'assets/img/esmalto-isotipo.png', 'Esmalto' );
				if ( $icono ) {
					update_option( 'site_icon', $icono );
					$log[] = 'Icono del sitio asignado.';
				}
			}
		}
		return $log;
	}

	private static function adjuntar_archivo_tema( $ruta, $alt ) {
		$archivo = get_stylesheet_directory() . '/' . $ruta;
		if ( ! file_exists( $archivo ) ) {
			return 0;
		}
		$subida = wp_upload_bits( basename( $archivo ), null, file_get_contents( $archivo ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( ! empty( $subida['error'] ) ) {
			return 0;
		}
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$tipo = wp_check_filetype( $subida['file'] );
		$id   = wp_insert_attachment(
			array(
				'post_mime_type' => $tipo['type'],
				'post_title'     => $alt,
				'post_status'    => 'inherit',
			),
			$subida['file']
		);
		if ( is_wp_error( $id ) ) {
			return 0;
		}
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $subida['file'] ) );
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
		return $id;
	}

	private static function configurar_woocommerce( $proximamente ) {
		$opciones = array(
			'woocommerce_default_country'                      => 'ES:CS',
			'woocommerce_store_city'                           => 'Castellón de la Plana',
			'woocommerce_store_postcode'                       => '12001',
			'woocommerce_allowed_countries'                    => 'specific',
			'woocommerce_specific_allowed_countries'           => array( 'ES' ),
			'woocommerce_ship_to_countries'                    => '',
			'woocommerce_currency'                             => 'EUR',
			'woocommerce_currency_pos'                         => 'right_space',
			'woocommerce_price_thousand_sep'                   => '.',
			'woocommerce_price_decimal_sep'                    => ',',
			'woocommerce_price_num_decimals'                   => 2,
			'woocommerce_weight_unit'                          => 'kg',
			'woocommerce_dimension_unit'                       => 'cm',
			'woocommerce_calc_taxes'                           => 'yes',
			'woocommerce_prices_include_tax'                   => 'no',
			'woocommerce_tax_based_on'                         => 'shipping',
			'woocommerce_default_customer_address'             => 'base',
			'woocommerce_tax_display_shop'                     => 'incl',
			'woocommerce_tax_display_cart'                     => 'incl',
			'woocommerce_tax_total_display'                    => 'single',
			'woocommerce_manage_stock'                         => 'no',
			'woocommerce_enable_reviews'                       => 'no',
			'woocommerce_enable_guest_checkout'                => 'yes',
			'woocommerce_enable_checkout_login_reminder'       => 'yes',
			'woocommerce_enable_signup_and_login_from_checkout' => 'yes',
			'woocommerce_enable_myaccount_registration'        => 'yes',
			'woocommerce_registration_generate_username'       => 'yes',
			'woocommerce_registration_generate_password'       => 'no',
			'woocommerce_thumbnail_cropping'                   => 'custom',
			'woocommerce_thumbnail_cropping_custom_width'      => '4',
			'woocommerce_thumbnail_cropping_custom_height'     => '5',
			'woocommerce_thumbnail_image_width'                => 600,
			'woocommerce_single_image_width'                   => 1000,
			'woocommerce_email_from_name'                      => 'Esmalto',
			'woocommerce_permalinks'                           => array(
				'product_base'           => 'producto',
				'category_base'          => 'categoria-producto',
				'tag_base'               => 'etiqueta-producto',
				'attribute_base'         => '',
				'use_verbose_page_rules' => false,
			),
		);
		foreach ( $opciones as $clave => $valor ) {
			update_option( $clave, $valor );
		}
		$log = array( 'WooCommerce: España, euros (1.234,56 €), kg, IVA, sin reseñas, registro en Mi cuenta y miniaturas 4:5.' );

		// IVA general 21 %.
		if ( class_exists( 'WC_Tax' ) ) {
			global $wpdb;
			$existe = $wpdb->get_var( $wpdb->prepare( "SELECT tax_rate_id FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_country = %s AND tax_rate_class = '' LIMIT 1", 'ES' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			if ( ! $existe ) {
				WC_Tax::_insert_tax_rate(
					array(
						'tax_rate_country'  => 'ES',
						'tax_rate_state'    => '',
						'tax_rate'          => '21.0000',
						'tax_rate_name'     => 'IVA',
						'tax_rate_priority' => 1,
						'tax_rate_compound' => 0,
						'tax_rate_shipping' => 1,
						'tax_rate_order'    => 0,
						'tax_rate_class'    => '',
					)
				);
				$log[] = 'Tipo de IVA 21 % creado.';
			}
		}

		// Transferencia bancaria (el IBAN lo completa Esmalto).
		$bacs = get_option( 'woocommerce_bacs_settings', array() );
		$bacs = array_merge(
			is_array( $bacs ) ? $bacs : array(),
			array(
				'enabled'      => 'yes',
				'title'        => 'Transferencia bancaria',
				'description'  => 'Realiza el pago por transferencia en los 3 días siguientes al pedido. Usa el número de pedido como concepto. Prepararemos tu pedido cuando recibamos el pago.',
				'instructions' => 'Envía el justificante a hola@esmalto.com. Si no recibimos el pago en 3 días, el pedido se cancelará.',
			)
		);
		update_option( 'woocommerce_bacs_settings', $bacs );
		$log[] = 'Transferencia bancaria activada (falta el IBAN en WooCommerce → Ajustes → Pagos).';

		update_option( 'woocommerce_coming_soon', $proximamente ? 'yes' : 'no' );
		update_option( 'woocommerce_store_pages_only', 'no' );
		$log[] = $proximamente ? 'Modo «Próximamente» activado.' : 'Modo «Próximamente» desactivado.';
		return $log;
	}

	private static function configurar_envios() {
		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			return array();
		}
		$log    = array();
		$zonas  = array(
			'Baleares, Canarias, Ceuta y Melilla' => array(
				'orden'      => 0,
				'ubicacion'  => array( 'ES:PM', 'ES:GC', 'ES:TF', 'ES:CE', 'ES:ML' ),
				'tipo'       => 'state',
				'metodos'    => array( 'local_pickup' ),
			),
			'España peninsular'                   => array(
				'orden'      => 1,
				'ubicacion'  => array( 'ES' ),
				'tipo'       => 'country',
				'metodos'    => array( 'esmalto_peso', 'local_pickup' ),
			),
		);
		$existentes = array();
		foreach ( WC_Shipping_Zones::get_zones() as $z ) {
			$existentes[ $z['zone_name'] ] = $z['id'];
		}
		foreach ( $zonas as $nombre => $def ) {
			if ( isset( $existentes[ $nombre ] ) ) {
				$log[] = sprintf( 'Zona de envío «%s» ya existía (sin cambios).', $nombre );
				continue;
			}
			$zona = new WC_Shipping_Zone();
			$zona->set_zone_name( $nombre );
			$zona->set_zone_order( $def['orden'] );
			foreach ( $def['ubicacion'] as $codigo ) {
				$zona->add_location( $codigo, $def['tipo'] );
			}
			$zona->save();
			foreach ( $def['metodos'] as $metodo ) {
				$instancia = $zona->add_shipping_method( $metodo );
				if ( 'local_pickup' === $metodo && $instancia ) {
					update_option(
						'woocommerce_local_pickup_' . $instancia . '_settings',
						array(
							'title'      => 'Recogida en almacén (gratis)',
							'tax_status' => 'none',
							'cost'       => '',
						)
					);
				}
			}
			$log[] = sprintf( 'Zona de envío «%s» creada.', $nombre );
		}
		return $log;
	}

	/**
	 * Filtros de la barra lateral en el orden del diseño (familia, formato, acabado, color) y después los demás.
	 * El espacio (baño, cocina…) se elige con los chips «Uso» de la cabecera de la tienda.
	 */
	private static function configurar_filtros( $forzar = false ) {
		$lista   = array( 'display_type' => 'list', 'query_type' => 'or' );
		$widgets = array(
			array( 'woocommerce_layered_nav_filters', array( 'title' => 'Filtros activos' ) ),
			array( 'woocommerce_layered_nav', array( 'title' => 'Familia técnica', 'attribute' => 'familia' ) + $lista ),
			array( 'woocommerce_layered_nav', array( 'title' => 'Formato', 'attribute' => 'formato' ) + $lista ),
			array( 'woocommerce_layered_nav', array( 'title' => 'Acabado', 'attribute' => 'acabado' ) + $lista ),
			array( 'woocommerce_layered_nav', array( 'title' => 'Color', 'attribute' => 'tono' ) + $lista ),
			array( 'woocommerce_layered_nav', array( 'title' => 'Estilo', 'attribute' => 'estilo' ) + $lista ),
			array( 'woocommerce_layered_nav', array( 'title' => 'Interior / exterior', 'attribute' => 'ubicacion' ) + $lista ),
			array( 'woocommerce_layered_nav', array( 'title' => 'Uso', 'attribute' => 'uso' ) + $lista ),
			array( 'woocommerce_layered_nav', array( 'title' => 'Antideslizante', 'attribute' => 'antideslizante' ) + $lista ),
			array( 'woocommerce_price_filter', array( 'title' => 'Precio' ) ),
		);
		$sidebars = get_option( 'sidebars_widgets', array() );
		if ( ! empty( $sidebars['astra-woo-shop-sidebar'] ) && ! $forzar ) {
			return array( 'Barra de filtros de la tienda ya configurada (sin cambios).' );
		}
		$ids = array();
		foreach ( $widgets as $widget ) {
			list( $base, $ajustes ) = $widget;
			$instancias = get_option( 'widget_' . $base, array() );
			$instancias = is_array( $instancias ) ? $instancias : array();
			$numeros    = array_filter( array_keys( $instancias ), 'is_int' );
			$n          = $numeros ? max( $numeros ) + 1 : 2;
			$instancias[ $n ]            = $ajustes;
			$instancias['_multiwidget']  = 1;
			update_option( 'widget_' . $base, $instancias );
			$ids[] = $base . '-' . $n;
		}
		$sidebars['astra-woo-shop-sidebar'] = $ids;
		update_option( 'sidebars_widgets', $sidebars );
		return array( 'Filtros de la tienda añadidos a la barra lateral (Apariencia → Widgets).' );
	}

	/* ------------------------------------------------------------------ Lotes AJAX */

	public static function ajax_lote() {
		if ( ! current_user_can( 'manage_woocommerce' ) || ! check_ajax_referer( 'esmalto_lote', '_wpnonce', false ) ) {
			wp_send_json_error( 'Sin permisos' );
		}
		@set_time_limit( 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		$tarea = isset( $_POST['tarea'] ) ? sanitize_key( $_POST['tarea'] ) : '';
		$desde = isset( $_POST['desde'] ) ? absint( $_POST['desde'] ) : 0;
		if ( 'fotos' === $tarea ) {
			wp_send_json_success( self::lote_fotos( $desde ) );
		}
		if ( 'fichas' === $tarea ) {
			wp_send_json_success( self::lote_fichas() );
		}
		if ( 'alt' === $tarea ) {
			wp_send_json_success( self::lote_alt( $desde ) );
		}
		wp_send_json_error( 'Tarea desconocida' );
	}

	private static function lote_fichas() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$pendientes = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => 3,
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					'relation' => 'AND',
					array(
						'key'     => '_ficha_tecnica',
						'value'   => '',
						'compare' => '!=',
					),
					array(
						'key'     => '_ficha_tecnica_id',
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);
		$log  = array();
		$base = trailingslashit( esmalto_ajuste( 'catalogo_url' ) );
		foreach ( $pendientes as $producto_id ) {
			$ruta = ltrim( (string) get_post_meta( $producto_id, '_ficha_tecnica', true ), '/' );
			$url  = preg_match( '#^https?://#', $ruta ) ? $ruta : $base . $ruta;
			$tmp  = download_url( $url, 120 );
			if ( is_wp_error( $tmp ) ) {
				update_post_meta( $producto_id, '_ficha_tecnica_id', 0 );
				$log[] = '✖ ' . get_the_title( $producto_id ) . ': ' . $tmp->get_error_message();
				continue;
			}
			$id = media_handle_sideload(
				array(
					'name'     => basename( wp_parse_url( $url, PHP_URL_PATH ) ),
					'tmp_name' => $tmp,
				),
				$producto_id,
				sprintf( 'Ficha técnica %s', get_the_title( $producto_id ) )
			);
			if ( is_wp_error( $id ) ) {
				wp_delete_file( $tmp );
				update_post_meta( $producto_id, '_ficha_tecnica_id', 0 );
				$log[] = '✖ ' . get_the_title( $producto_id ) . ': ' . $id->get_error_message();
				continue;
			}
			update_post_meta( $producto_id, '_ficha_tecnica_id', $id );
			$log[] = '✔ ' . get_the_title( $producto_id );
		}
		return array(
			'log'       => $log,
			'siguiente' => count( $pendientes ) ? 0 : null,
		);
	}

	/**
	 * Precarga las fotos del CSV del catálogo en lotes cortos, con su texto ALT.
	 * WooCommerce reutiliza el adjunto cuyo «_wc_attachment_source» coincide con la URL,
	 * así la importación no descarga decenas de fotos en una sola petición.
	 */
	private static function lote_fotos( $desde ) {
		$urls = self::urls_catalogo( 0 === $desde );
		if ( is_wp_error( $urls ) ) {
			return array(
				'log'       => array( '✖ ' . $urls->get_error_message() ),
				'siguiente' => null,
			);
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		add_filter( 'intermediate_image_sizes_advanced', array( __CLASS__, 'tamanos_imagen' ) );

		$mapa   = self::mapa_alt();
		$total  = count( $urls );
		$inicio = microtime( true );
		$pos    = $desde;
		$nuevas = 0;
		$log    = array();
		// Hasta 6 fotos o unos 15 s por petición, lejos del límite de 30 s del servidor.
		while ( $pos < $total && $pos - $desde < 6 && microtime( true ) - $inicio < 15 ) {
			$url = $urls[ $pos ];
			++$pos;
			if ( self::adjunto_de( $url ) ) {
				continue;
			}
			$subida = wc_rest_upload_image_from_url( $url );
			if ( is_wp_error( $subida ) ) {
				$log[] = '✖ ' . basename( $url ) . ': ' . $subida->get_error_message();
				continue;
			}
			$id = wc_rest_set_uploaded_image_as_attachment( $subida );
			if ( ! $id || is_wp_error( $id ) ) {
				$log[] = '✖ ' . basename( $url ) . ': no se pudo guardar.';
				continue;
			}
			update_post_meta( $id, '_wc_attachment_source', $url );
			self::aplicar_alt( $id, $url, $mapa );
			++$nuevas;
		}
		$log[] = sprintf( 'Fotos %d–%d de %d: %d descargadas.', $desde + 1, $pos, $total, $nuevas );
		return array(
			'log'       => $log,
			'siguiente' => $pos < $total ? $pos : null,
		);
	}

	/**
	 * URLs únicas de la columna «Images» del CSV del catálogo, tal como las lee el importador.
	 */
	private static function urls_catalogo( $refrescar ) {
		$urls = get_transient( 'esmalto_fotos_catalogo' );
		if ( ! $refrescar && is_array( $urls ) ) {
			return $urls;
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$tmp = download_url( trailingslashit( esmalto_ajuste( 'catalogo_url' ) ) . 'salida/esmalto-productos.csv', 60 );
		if ( is_wp_error( $tmp ) ) {
			return $tmp;
		}
		$f    = fopen( $tmp, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$cab  = $f ? fgetcsv( $f, 0, ',', '"', '' ) : false;
		$col  = $cab ? array_search( 'Images', array_map( fn( $c ) => preg_replace( '/^\xEF\xBB\xBF/', '', trim( (string) $c ) ), $cab ), true ) : false;
		$urls = array();
		while ( false !== $col && ( $fila = fgetcsv( $f, 0, ',', '"', '' ) ) !== false ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition
			foreach ( explode( ',', (string) ( $fila[ $col ] ?? '' ) ) as $url ) {
				$url = esc_url_raw( trim( $url ) );
				if ( $url ) {
					$urls[ $url ] = true;
				}
			}
		}
		if ( $f ) {
			fclose( $f ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
		wp_delete_file( $tmp );
		if ( false === $col ) {
			return new WP_Error( 'esmalto_csv', 'El CSV del catálogo no tiene la columna «Images».' );
		}
		$urls = array_keys( $urls );
		set_transient( 'esmalto_fotos_catalogo', $urls, DAY_IN_SECONDS );
		return $urls;
	}

	private static function adjunto_de( $url ) {
		$ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'key'   => '_wc_attachment_source',
						'value' => $url,
					),
				),
			)
		);
		return $ids ? (int) $ids[0] : 0;
	}

	private static function mapa_alt() {
		$archivo = ESMALTO_CORE_DIR . 'data/imagenes-alt.json';
		return file_exists( $archivo ) ? (array) json_decode( (string) file_get_contents( $archivo ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}

	private static function aplicar_alt( $id, $fuente, $mapa ) {
		$nombre = basename( (string) wp_parse_url( $fuente, PHP_URL_PATH ) );
		if ( empty( $mapa[ $nombre ] ) ) {
			return false;
		}
		$d = $mapa[ $nombre ];
		update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( $d['alt'] ) );
		wp_update_post(
			array(
				'ID'           => $id,
				'post_title'   => sanitize_text_field( $d['title'] ),
				'post_excerpt' => sanitize_text_field( $d['caption'] ),
				'post_content' => sanitize_textarea_field( $d['description'] ),
			)
		);
		return true;
	}

	private static function lote_alt( $desde ) {
		$mapa    = self::mapa_alt();
		$por_pag = 80;
		$ids     = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => $por_pag,
				'offset'         => $desde,
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'meta_key'       => '_wc_attachment_source', // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		$hechas = 0;
		foreach ( $ids as $id ) {
			if ( self::aplicar_alt( $id, (string) get_post_meta( $id, '_wc_attachment_source', true ), $mapa ) ) {
				++$hechas;
			}
		}
		return array(
			'log'       => array( sprintf( 'Imágenes %d–%d: %d con ALT aplicado.', $desde + 1, $desde + count( $ids ), $hechas ) ),
			'siguiente' => count( $ids ) === $por_pag ? $desde + $por_pag : null,
		);
	}

	/* ------------------------------------------------------------------ Tarifa */

	public static function accion_tarifa() {
		if ( ! current_user_can( 'manage_woocommerce' ) || ! check_admin_referer( 'esmalto_tarifa' ) ) {
			wp_die( esc_html__( 'Sin permisos.', 'esmalto-core' ) );
		}
		$texto   = isset( $_POST['tarifa'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tarifa'] ) ) : '';
		$por_m2  = ! isset( $_POST['unidad'] ) || 'caja' !== $_POST['unidad'];
		$con_iva = ! empty( $_POST['con_iva'] );
		$ok      = 0;
		$errores = array();
		$padres  = array();
		foreach ( preg_split( '/\r\n|\r|\n/', $texto ) as $n => $linea ) {
			$linea = trim( $linea );
			if ( '' === $linea || 0 === stripos( $linea, 'sku' ) ) {
				continue;
			}
			$partes = preg_split( '/\s*[;\t]\s*/', $linea );
			if ( count( $partes ) < 2 ) {
				$errores[] = sprintf( 'Línea %d sin «;»: %s', $n + 1, $linea );
				continue;
			}
			$sku    = trim( $partes[0] );
			$precio = esmalto_num( end( $partes ) );
			$id     = wc_get_product_id_by_sku( $sku );
			$var    = $id ? wc_get_product( $id ) : null;
			if ( ! $var || $precio <= 0 ) {
				$errores[] = sprintf( '%s: %s', $sku, $var ? 'precio no válido' : 'SKU no encontrado' );
				continue;
			}
			if ( $por_m2 ) {
				$m2 = esmalto_num( get_post_meta( $var->get_id(), '_m2_por_caja', true ) );
				if ( $m2 <= 0 ) {
					$errores[] = sprintf( '%s: sin m² por caja (usa precio por caja)', $sku );
					continue;
				}
				$precio *= $m2;
			}
			if ( $con_iva ) {
				$precio /= 1.21;
			}
			$var->set_regular_price( wc_format_decimal( $precio, 4 ) );
			$var->save();
			if ( $var->get_parent_id() ) {
				$padres[ $var->get_parent_id() ] = true;
			}
			++$ok;
		}
		foreach ( array_keys( $padres ) as $padre ) {
			WC_Product_Variable::sync( $padre );
			wc_delete_product_transients( $padre );
		}
		$log = array( sprintf( 'Tarifa cargada: %d precios actualizados en %d productos.', $ok, count( $padres ) ) );
		if ( $errores ) {
			$log[] = sprintf( '%d líneas con incidencias:', count( $errores ) );
			$log   = array_merge( $log, array_slice( $errores, 0, 50 ) );
		}
		self::volver( $log );
	}

	public static function accion_plantilla() {
		if ( ! current_user_can( 'manage_woocommerce' ) || ! check_admin_referer( 'esmalto_plantilla' ) ) {
			wp_die( esc_html__( 'Sin permisos.', 'esmalto-core' ) );
		}
		$variaciones = wc_get_products(
			array(
				'type'    => 'variation',
				'limit'   => -1,
				'orderby' => 'parent',
				'order'   => 'ASC',
				'status'  => array( 'publish', 'private' ),
			)
		);
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=esmalto-tarifa.csv' );
		$salida = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		fwrite( $salida, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		fputcsv( $salida, array( 'SKU', 'Producto', 'm2 por caja', 'Precio actual por caja (sin IVA)', 'Precio nuevo' ), ';', '"', '' );
		foreach ( $variaciones as $v ) {
			fputcsv(
				$salida,
				array(
					$v->get_sku(),
					trim( wp_strip_all_tags( $v->get_name() ) . ' · ' . wc_get_formatted_variation( $v, true, false, false ), ' ·' ),
					str_replace( '.', ',', (string) get_post_meta( $v->get_id(), '_m2_por_caja', true ) ),
					str_replace( '.', ',', (string) $v->get_regular_price( 'edit' ) ),
					'',
				),
				';',
				'"',
				''
			);
		}
		fclose( $salida ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
}
