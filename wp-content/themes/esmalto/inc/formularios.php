<?php
/**
 * Formularios de acceso y alta como en el diseño: textos dentro de los campos (las etiquetas
 * quedan para lectores de pantalla) y «¿Olvidaste tu contraseña?» junto a «Recordarme».
 *
 * @package Esmalto
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bloque «Iniciar/cerrar sesión» (acceso profesional).
 */
add_filter( 'login_form_defaults', 'esmalto_login_textos' );
function esmalto_login_textos( $args ) {
	$args['label_username'] = __( 'Email profesional', 'esmalto' );
	$args['label_remember'] = __( 'Recordarme', 'esmalto' );
	$args['label_log_in']   = __( 'Acceder', 'esmalto' );
	return $args;
}

add_filter( 'login_form_middle', 'esmalto_login_olvido' );
function esmalto_login_olvido( $html ) {
	$url = function_exists( 'wc_lostpassword_url' ) ? wc_lostpassword_url() : wp_lostpassword_url();
	return $html . '<p class="esm-login__olvido"><a href="' . esc_url( $url ) . '">' . esc_html__( '¿Olvidaste tu contraseña?', 'esmalto' ) . '</a></p>';
}

add_action( 'wp_footer', 'esmalto_marcadores_formularios', 98 );
function esmalto_marcadores_formularios() {
	?>
	<script>
	( function () {
		var formularios = '.esm-login form, form.woocommerce-form-login, form.woocommerce-form-register, form.woocommerce-ResetPassword';
		document.querySelectorAll( formularios ).forEach( function ( f ) {
			f.classList.add( 'esm-con-marcadores' );
			f.querySelectorAll( 'input[type="text"], input[type="email"], input[type="password"], input[type="tel"]' ).forEach( function ( campo ) {
				if ( campo.placeholder || ! campo.id ) { return; }
				var etiqueta = f.querySelector( 'label[for="' + campo.id + '"]' );
				if ( ! etiqueta ) { return; }
				var texto = etiqueta.cloneNode( true );
				texto.querySelectorAll( '.required, .screen-reader-text' ).forEach( function ( n ) { n.remove(); } );
				campo.placeholder = texto.textContent.replace( /\*/g, '' ).trim();
				etiqueta.classList.add( 'screen-reader-text' );
			} );
		} );
	} )();
	</script>
	<?php
}
