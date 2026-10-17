<?php
/**
 * Categorías del catálogo con página propia (data/categorias.php): URL en la raíz (/porcelanico-exterior/),
 * categorías ocultas (sin el mínimo de colecciones o pendientes de confirmar) y herramienta para crearlas
 * y asignar colecciones. Las demás categorías de producto no cambian.
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Categorias {

	const OPCION_ASIGNACION = 'esmalto_categorias_asignacion';
	const META_OCULTA       = 'esmalto_oculta';
	const META_GRUPO        = 'esmalto_grupo';
	const META_TITULO       = 'esmalto_h1';
	const MINIMO            = 1;

	public static function init() {
		add_action( 'init', array( __CLASS__, 'reglas' ), 20 );
		add_action( 'init', array( __CLASS__, 'regenerar_reglas' ), 99 );
		add_filter( 'term_link', array( __CLASS__, 'enlace' ), 10, 3 );
		add_action( 'template_redirect', array( __CLASS__, 'redirigir_u_ocultar' ), 5 );
		add_filter( 'wpseo_exclude_from_sitemap_by_term_ids', array( __CLASS__, 'ocultas_fuera_del_mapa' ) );
		add_action( 'product_cat_edit_form_fields', array( __CLASS__, 'campo_oculta' ), 20 );
		add_action( 'edited_product_cat', array( __CLASS__, 'guardar_oculta' ) );
		add_filter( 'woocommerce_get_related_product_cat_terms', array( __CLASS__, 'relacionados' ) );
	}

	/**
	 * «También te puede interesar» sigue saliendo por familia: estas categorías (Baño o Salón reúnen
	 * todo el catálogo) no cuentan para los productos relacionados.
	 */
	public static function relacionados( $ids ) {
		$nuestras = array();
		foreach ( array_keys( self::definiciones() ) as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			if ( $term ) {
				$nuestras[] = (int) $term->term_id;
			}
		}
		return array_values( array_diff( array_map( 'intval', (array) $ids ), $nuestras ) );
	}

	public static function definiciones() {
		static $defs = null;
		if ( null === $defs ) {
			$defs = include ESMALTO_CORE_DIR . 'data/categorias.php';
		}
		return $defs;
	}

	public static function es_nuestra( $term ) {
		return $term instanceof WP_Term && 'product_cat' === $term->taxonomy && isset( self::definiciones()[ $term->slug ] );
	}

	public static function oculta( $term ) {
		return $term instanceof WP_Term && '1' === (string) get_term_meta( $term->term_id, self::META_OCULTA, true );
	}

	/* ------------------------------------------------------------------ URL en la raíz */

	public static function reglas() {
		foreach ( array_keys( self::definiciones() ) as $slug ) {
			$s = preg_quote( $slug, '#' );
			add_rewrite_rule( '^' . $s . '/?$', 'index.php?product_cat=' . $slug, 'top' );
			add_rewrite_rule( '^' . $s . '/page/([0-9]{1,})/?$', 'index.php?product_cat=' . $slug . '&paged=$matches[1]', 'top' );
		}
	}

	/**
	 * Al cambiar de versión del plugin (o tras crear categorías) se regeneran los enlaces permanentes.
	 */
	public static function regenerar_reglas() {
		if ( get_option( 'esmalto_reglas_version' ) !== ESMALTO_CORE_VERSION ) {
			flush_rewrite_rules( false );
			update_option( 'esmalto_reglas_version', ESMALTO_CORE_VERSION );
		}
	}

	public static function enlace( $url, $term, $taxonomia ) {
		if ( 'product_cat' === $taxonomia && $term instanceof WP_Term && isset( self::definiciones()[ $term->slug ] ) ) {
			return home_url( user_trailingslashit( $term->slug ) );
		}
		return $url;
	}

	/**
	 * La URL antigua (/categoria-producto/slug/) lleva a la nueva; una categoría oculta da 404
	 * a los visitantes (los administradores la ven para revisarla).
	 */
	public static function redirigir_u_ocultar() {
		if ( ! function_exists( 'is_product_category' ) || ! is_product_category() ) {
			return;
		}
		$term = get_queried_object();
		if ( ! self::es_nuestra( $term ) ) {
			return;
		}
		if ( self::oculta( $term ) && ! current_user_can( 'manage_woocommerce' ) ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
			return;
		}
		global $wp;
		$ruta = trim( (string) $wp->request, '/' );
		if ( $ruta !== $term->slug && 0 !== strpos( $ruta, $term->slug . '/page/' ) ) {
			$destino = get_term_link( $term );
			$pagina  = (int) get_query_var( 'paged' );
			if ( $pagina > 1 ) {
				$destino = trailingslashit( $destino ) . user_trailingslashit( 'page/' . $pagina );
			}
			if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
				$destino .= '?' . sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) );
			}
			wp_safe_redirect( $destino, 301 );
			exit;
		}
	}

	public static function ocultas_fuera_del_mapa( $ids ) {
		$ids = is_array( $ids ) ? $ids : array();
		foreach ( array_keys( self::definiciones() ) as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			if ( $term && self::oculta( $term ) ) {
				$ids[] = (int) $term->term_id;
			}
		}
		return $ids;
	}

	/* ------------------------------------------------------------------ Campo «No publicar» */

	public static function campo_oculta( $term ) {
		if ( ! self::es_nuestra( $term ) ) {
			return;
		}
		wp_nonce_field( 'esmalto_oculta', '_esmalto_oculta_nonce' );
		?>
		<tr class="form-field">
			<th scope="row"><?php esc_html_e( 'No publicar', 'esmalto-core' ); ?></th>
			<td>
				<label><input type="checkbox" name="esmalto_oculta" value="1" <?php checked( self::oculta( $term ) ); ?>> <?php esc_html_e( 'Ocultar esta categoría a los visitantes (página 404, fuera del mapa del sitio y de los menús).', 'esmalto-core' ); ?></label>
				<p class="description"><?php esc_html_e( 'Esmalto → Herramientas la oculta sola si no tiene colecciones o está pendiente de confirmar.', 'esmalto-core' ); ?></p>
			</td>
		</tr>
		<?php
	}

	public static function guardar_oculta( $term_id ) {
		if ( ! isset( $_POST['_esmalto_oculta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_esmalto_oculta_nonce'] ), 'esmalto_oculta' ) || ! current_user_can( 'manage_product_terms' ) ) {
			return;
		}
		update_term_meta( $term_id, self::META_OCULTA, empty( $_POST['esmalto_oculta'] ) ? '0' : '1' );
	}

	/* ------------------------------------------------------------------ Herramienta */

	/**
	 * Asignación en texto: «slug: Colección, Colección…» por línea (por defecto, la propuesta).
	 */
	public static function asignacion_texto() {
		$guardada = (string) get_option( self::OPCION_ASIGNACION, '' );
		if ( '' !== trim( $guardada ) ) {
			return $guardada;
		}
		$lineas = array();
		foreach ( self::definiciones() as $slug => $def ) {
			$lineas[] = $slug . ': ' . implode( ', ', $def['propuesta'] );
		}
		return implode( "\n", $lineas );
	}

	private static function leer_asignacion( $texto ) {
		$asignacion = array();
		foreach ( preg_split( '/\r\n|\r|\n/', (string) $texto ) as $linea ) {
			$partes = array_map( 'trim', explode( ':', $linea, 2 ) );
			if ( 2 === count( $partes ) && isset( self::definiciones()[ $partes[0] ] ) ) {
				$asignacion[ $partes[0] ] = array_values( array_filter( array_map( 'trim', explode( ',', $partes[1] ) ) ) );
			}
		}
		return $asignacion;
	}

	/**
	 * Primera frase del texto (descripción para buscadores).
	 */
	public static function primera_frase( $texto ) {
		return preg_match( '/^.+?[.!?](?=\s|$)/u', trim( $texto ), $m ) ? $m[0] : trim( $texto );
	}

	/**
	 * Crea o actualiza las categorías (nombre, URL, texto, título y descripción de Yoast) y asigna las
	 * colecciones. Se ocultan las que no llegan al mínimo de colecciones y las pendientes de confirmar.
	 *
	 * @param string $texto     Asignación «slug: Colección, …».
	 * @param bool   $confirmar Publicar también las categorías «pendientes de confirmar».
	 * @return array registro.
	 */
	public static function aplicar( $texto, $confirmar = false ) {
		update_option( self::OPCION_ASIGNACION, $texto, false );
		$asignacion = self::leer_asignacion( $texto );
		$productos  = array();
		foreach ( get_posts( array( 'post_type' => 'product', 'post_status' => array( 'publish', 'private' ), 'numberposts' => -1 ) ) as $p ) {
			$productos[ mb_strtolower( $p->post_title ) ] = $p;
		}
		$log = array();
		foreach ( self::definiciones() as $slug => $def ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			if ( ! $term ) {
				$nuevo = wp_insert_term( $def['nombre'], 'product_cat', array( 'slug' => $slug, 'description' => $def['texto'] ) );
				if ( is_wp_error( $nuevo ) ) {
					$log[] = sprintf( '⚠ %s: %s', $def['nombre'], $nuevo->get_error_message() );
					continue;
				}
				$term = get_term( $nuevo['term_id'], 'product_cat' );
			}
			if ( class_exists( 'WPSEO_Taxonomy_Meta' ) ) {
				WPSEO_Taxonomy_Meta::set_values(
					$term->term_id,
					'product_cat',
					array(
						'wpseo_title' => $def['titulo'],
						'wpseo_desc'  => self::primera_frase( $def['texto'] ),
					)
				);
			}
			// Tras guardar Yoast, para que su índice recoja título y descripción.
			wp_update_term( $term->term_id, 'product_cat', array( 'name' => $def['nombre'], 'slug' => $slug, 'description' => $def['texto'] ) );
			update_term_meta( $term->term_id, self::META_GRUPO, $def['grupo'] );
			update_term_meta( $term->term_id, self::META_TITULO, preg_replace( '/\s*\|\s*Esmalto$/u', '', $def['titulo'] ) );

			// Colecciones: se añaden las de la lista y se quitan las que ya no están.
			$quiero = array();
			$faltan = array();
			foreach ( $asignacion[ $slug ] ?? array() as $nombre ) {
				$p = $productos[ mb_strtolower( $nombre ) ] ?? null;
				if ( $p ) {
					$quiero[] = $p->ID;
				} else {
					$faltan[] = $nombre;
				}
			}
			$actuales = get_objects_in_term( $term->term_id, 'product_cat' );
			foreach ( array_diff( array_map( 'intval', (array) $actuales ), $quiero ) as $id ) {
				wp_remove_object_terms( $id, $term->term_id, 'product_cat' );
			}
			foreach ( $quiero as $id ) {
				wp_set_object_terms( $id, $term->term_id, 'product_cat', true );
			}
			$publicadas = count(
				array_filter(
					$quiero,
					static function ( $id ) {
						return 'publish' === get_post_status( $id );
					}
				)
			);
			$pendiente = ! empty( $def['oculta'] ) && ! $confirmar;
			$ocultar   = $pendiente || $publicadas < self::MINIMO;
			update_term_meta( $term->term_id, self::META_OCULTA, $ocultar ? '1' : '0' );
			$log[] = sprintf(
				'%s (/%s/): %d colecciones%s%s',
				$def['nombre'],
				$slug,
				$publicadas,
				$ocultar ? ( $pendiente ? ' · oculta (pendiente de confirmar)' : sprintf( ' · oculta (menos de %d)', self::MINIMO ) ) : ' · publicada',
				$faltan ? ' · no encontradas: ' . implode( ', ', $faltan ) : ''
			);
		}
		delete_option( 'esmalto_reglas_version' );
		if ( class_exists( 'WPSEO_Sitemaps_Cache' ) ) {
			WPSEO_Sitemaps_Cache::clear();
		}
		return $log;
	}
}
