<?php
/**
 * Menú «Esmalto» y página de ajustes.
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

class Esmalto_Ajustes {

	const OPCION = 'esmalto_ajustes';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 9 );
		add_action( 'admin_init', array( __CLASS__, 'registrar' ) );
	}

	public static function menu() {
		add_menu_page(
			__( 'Esmalto', 'esmalto-core' ),
			__( 'Esmalto', 'esmalto-core' ),
			'manage_woocommerce',
			'esmalto-ajustes',
			array( __CLASS__, 'pagina' ),
			'dashicons-grid-view',
			56
		);
		add_submenu_page( 'esmalto-ajustes', __( 'Ajustes', 'esmalto-core' ), __( 'Ajustes', 'esmalto-core' ), 'manage_woocommerce', 'esmalto-ajustes', array( __CLASS__, 'pagina' ) );
	}

	public static function registrar() {
		add_filter(
			'option_page_capability_esmalto_ajustes_grupo',
			static function () {
				return 'manage_woocommerce';
			}
		);
		register_setting(
			'esmalto_ajustes_grupo',
			self::OPCION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanear' ),
				'default'           => esmalto_ajustes_defecto(),
			)
		);
	}

	public static function sanear( $entrada ) {
		$def    = esmalto_ajustes_defecto();
		$salida = array();
		$entrada = is_array( $entrada ) ? $entrada : array();
		foreach ( array( 'cat_a_pct', 'cat_b_pct', 'cat_c_pct', 'palet_pct' ) as $clave ) {
			$valor            = isset( $entrada[ $clave ] ) ? esmalto_num( $entrada[ $clave ] ) : $def[ $clave ];
			$salida[ $clave ] = max( 0, min( 90, round( $valor, 2 ) ) );
		}
		foreach ( array( 'cat_a_rol', 'cat_b_rol', 'cat_c_rol', 'rol_alta', 'comercial_nombre', 'comercial_tel', 'comercial_whatsapp' ) as $clave ) {
			$salida[ $clave ] = isset( $entrada[ $clave ] ) ? sanitize_text_field( wp_unslash( $entrada[ $clave ] ) ) : $def[ $clave ];
		}
		foreach ( array( 'comercial_email', 'email_avisos' ) as $clave ) {
			$salida[ $clave ] = isset( $entrada[ $clave ] ) ? sanitize_email( wp_unslash( $entrada[ $clave ] ) ) : $def[ $clave ];
		}
		$salida['b2b_sin_iva']    = empty( $entrada['b2b_sin_iva'] ) ? 0 : 1;
		$salida['aviso_producto'] = isset( $entrada['aviso_producto'] ) ? sanitize_textarea_field( wp_unslash( $entrada['aviso_producto'] ) ) : $def['aviso_producto'];
		$salida['catalogo_url']   = isset( $entrada['catalogo_url'] ) ? esc_url_raw( wp_unslash( $entrada['catalogo_url'] ) ) : $def['catalogo_url'];

		// CNAE: se guarda normalizado; si coincide con la lista inicial se deja vacío (= usar la inicial).
		$cnae = isset( $entrada['cnae'] ) ? sanitize_textarea_field( wp_unslash( $entrada['cnae'] ) ) : '';
		if ( '' !== trim( $cnae ) ) {
			$temporal = array();
			foreach ( preg_split( '/\r\n|\r|\n/', $cnae ) as $linea ) {
				$partes = array_map( 'trim', explode( '|', $linea, 2 ) );
				$codigo = Esmalto_CNAE::normalizar( $partes[0] ?? '' );
				if ( '' !== $codigo ) {
					$temporal[ $codigo ] = $partes[1] ?? '';
				}
			}
			$cnae = Esmalto_CNAE::como_texto( $temporal ) === Esmalto_CNAE::como_texto( Esmalto_CNAE::lista_inicial() ) ? '' : Esmalto_CNAE::como_texto( $temporal );
		}
		$salida['cnae'] = $cnae;
		return $salida;
	}

	public static function pagina() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$a     = esmalto_ajustes();
		$roles = esmalto_roles_wholesalex();
		$n     = self::OPCION;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Esmalto · Ajustes', 'esmalto-core' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'esmalto_ajustes_grupo' ); ?>

				<h2><?php esc_html_e( 'Categorías profesionales (B2B)', 'esmalto-core' ); ?></h2>
				<p><?php esc_html_e( 'Cada categoría corresponde a un rol de WholesaleX. Al aprobar a un profesional en WholesaleX → Usuarios, asígnale el rol de su categoría. El descuento se aplica sobre el precio de tarifa (PVP).', 'esmalto-core' ); ?></p>
				<table class="form-table" role="presentation">
					<?php foreach ( array( 'a' => 'A', 'b' => 'B', 'c' => 'C' ) as $k => $letra ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( sprintf( __( 'Categoría %s', 'esmalto-core' ), $letra ) ); ?></th>
							<td>
								<label><?php esc_html_e( 'Descuento', 'esmalto-core' ); ?>
									<input type="number" step="0.5" min="0" max="90" class="small-text" name="<?php echo esc_attr( $n . '[cat_' . $k . '_pct]' ); ?>" value="<?php echo esc_attr( $a[ 'cat_' . $k . '_pct' ] ); ?>"> %
								</label>
								&nbsp;&nbsp;
								<label><?php esc_html_e( 'Rol WholesaleX', 'esmalto-core' ); ?>
									<?php self::campo_rol( $n . '[cat_' . $k . '_rol]', $a[ 'cat_' . $k . '_rol' ], $roles ); ?>
								</label>
							</td>
						</tr>
					<?php endforeach; ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Rol al solicitar el alta', 'esmalto-core' ); ?></th>
						<td>
							<?php self::campo_rol( $n . '[rol_alta]', $a['rol_alta'], $roles ); ?>
							<p class="description"><?php esc_html_e( 'Rol propuesto a WholesaleX para las solicitudes nuevas (quedan pendientes hasta que las apruebes). Si se deja vacío se usa el de la categoría A.', 'esmalto-core' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Descuento por palé completo', 'esmalto-core' ); ?></th>
						<td>
							<input type="number" step="0.5" min="0" max="90" class="small-text" name="<?php echo esc_attr( $n . '[palet_pct]' ); ?>" value="<?php echo esc_attr( $a['palet_pct'] ); ?>"> %
							<p class="description"><?php esc_html_e( 'Descuento adicional para profesionales sobre las cajas que completan palés (se muestra como línea de descuento en la cesta).', 'esmalto-core' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Precios sin IVA para profesionales', 'esmalto-core' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( $n . '[b2b_sin_iva]' ); ?>" value="1" <?php checked( $a['b2b_sin_iva'], 1 ); ?>> <?php esc_html_e( 'Mostrar a los profesionales aprobados los precios sin IVA en tienda y cesta.', 'esmalto-core' ); ?></label></td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Contacto comercial', 'esmalto-core' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					self::fila_texto( __( 'Nombre', 'esmalto-core' ), $n . '[comercial_nombre]', $a['comercial_nombre'] );
					self::fila_texto( __( 'Email', 'esmalto-core' ), $n . '[comercial_email]', $a['comercial_email'], 'email' );
					self::fila_texto( __( 'Teléfono', 'esmalto-core' ), $n . '[comercial_tel]', $a['comercial_tel'] );
					self::fila_texto( __( 'WhatsApp (con prefijo, solo números)', 'esmalto-core' ), $n . '[comercial_whatsapp]', $a['comercial_whatsapp'] );
					self::fila_texto( __( 'Email para avisos de altas', 'esmalto-core' ), $n . '[email_avisos]', $a['email_avisos'], 'email', __( 'Vacío = email del administrador.', 'esmalto-core' ) );
					?>
				</table>

				<h2><?php esc_html_e( 'CNAE admitidos en el alta profesional', 'esmalto-core' ); ?></h2>
				<p><?php esc_html_e( 'Un código por línea: «4333 | Revestimiento de suelos y paredes». Se aceptan con o sin punto (43.33). Vacía el cuadro y guarda para volver a la lista inicial.', 'esmalto-core' ); ?></p>
				<textarea name="<?php echo esc_attr( $n . '[cnae]' ); ?>" rows="16" class="large-text code"><?php echo esc_textarea( Esmalto_CNAE::como_texto() ); ?></textarea>

				<h2><?php esc_html_e( 'Ficha de producto', 'esmalto-core' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Aviso bajo la calculadora', 'esmalto-core' ); ?></th>
						<td><textarea name="<?php echo esc_attr( $n . '[aviso_producto]' ); ?>" rows="3" class="large-text"><?php echo esc_textarea( $a['aviso_producto'] ); ?></textarea></td>
					</tr>
					<?php self::fila_texto( __( 'URL base del catálogo (fichas)', 'esmalto-core' ), $n . '[catalogo_url]', $a['catalogo_url'], 'url', __( 'Carpeta pública desde la que se importan las fichas técnicas PDF.', 'esmalto-core' ) ); ?>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	private static function campo_rol( $nombre, $valor, $roles ) {
		if ( empty( $roles ) ) {
			printf( '<input type="text" class="regular-text" name="%s" value="%s" placeholder="%s">', esc_attr( $nombre ), esc_attr( $valor ), esc_attr__( 'ID del rol en WholesaleX', 'esmalto-core' ) );
			return;
		}
		echo '<select name="' . esc_attr( $nombre ) . '"><option value="">' . esc_html__( '— Sin asignar —', 'esmalto-core' ) . '</option>';
		foreach ( $roles as $id => $titulo ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $id ), selected( $valor, $id, false ), esc_html( $titulo ) );
		}
		echo '</select>';
	}

	private static function fila_texto( $etiqueta, $nombre, $valor, $tipo = 'text', $ayuda = '' ) {
		?>
		<tr>
			<th scope="row"><?php echo esc_html( $etiqueta ); ?></th>
			<td>
				<input type="<?php echo esc_attr( $tipo ); ?>" class="regular-text" name="<?php echo esc_attr( $nombre ); ?>" value="<?php echo esc_attr( $valor ); ?>">
				<?php if ( $ayuda ) : ?>
					<p class="description"><?php echo esc_html( $ayuda ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<?php
	}
}
