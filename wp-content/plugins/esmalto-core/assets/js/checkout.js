/**
 * Esmalto · finalizar compra (bloque de pago de WooCommerce)
 * - Nota bajo el campo NIF: «Lo necesitamos para emitir tu factura».
 * - Casilla de recargo de equivalencia: avisa al servidor para recalcular el total al momento.
 */
( function () {
	'use strict';

	var cfg = window.esmaltoCheckout || {};
	var data = window.wp && window.wp.data;

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

	/* ---------------- Recargo de equivalencia ---------------- */
	if ( ! data || ! data.subscribe ) {
		return;
	}
	var ultimo = null;
	data.subscribe( function () {
		var tienda = data.select( 'wc/store/checkout' );
		var actualizar = window.wc && window.wc.blocksCheckout && window.wc.blocksCheckout.extensionCartUpdate;
		if ( ! tienda || ! tienda.getAdditionalFields || ! actualizar ) {
			return;
		}
		var valor = !! ( tienda.getAdditionalFields() || {} )[ cfg.campoRecargo ];
		if ( valor === ultimo ) {
			return;
		}
		var primeraVez = null === ultimo;
		ultimo = valor;
		// Al cargar solo hace falta avisar si el servidor tenía otro estado guardado.
		if ( primeraVez && valor === !! cfg.activo ) {
			return;
		}
		actualizar( { namespace: 'esmalto-recargo', data: { activo: valor } } );
	} );
}() );
