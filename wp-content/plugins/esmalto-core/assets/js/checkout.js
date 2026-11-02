/**
 * Esmalto · finalizar compra (bloque de pago de WooCommerce)
 * - Nota bajo el campo NIF: «Lo necesitamos para emitir tu factura».
 */
( function () {
	'use strict';

	var cfg = window.esmaltoCheckout || {};

	/* ---------------- Nota del NIF ---------------- */
	function ponerAyuda() {
		if ( document.getElementById( 'esm-nif-ayuda' ) ) {
			return;
		}
		var id = String( cfg.campoNif || '' ).replace( '/', '-' );
		var campo = document.querySelector( 'input[id$="' + id + '"], input[id$="' + cfg.campoNif + '"]' );
		if ( ! campo ) {
			return;
		}
		var contenedor = campo.closest( '.wc-block-components-text-input' ) || campo.parentNode;
		var ayuda = document.createElement( 'p' );
		ayuda.id = 'esm-nif-ayuda';
		ayuda.className = 'esm-ayuda-campo';
		ayuda.textContent = cfg.ayudaNif || '';
		contenedor.insertAdjacentElement( 'afterend', ayuda );
		campo.setAttribute( 'aria-describedby', 'esm-nif-ayuda' );
	}
	new MutationObserver( ponerAyuda ).observe( document.documentElement, { childList: true, subtree: true } );
	ponerAyuda();
}() );
