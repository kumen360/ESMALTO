/**
 * Esmalto · alta profesional: ayuda en vivo del CNAE (la validación definitiva es en el servidor).
 */
( function () {
	'use strict';
	var cfg = window.esmaltoCnae || { lista: {} };
	var campo = document.getElementById( 'esm_cnae' );
	var ayuda = document.getElementById( 'esm_cnae_ayuda' );
	if ( ! campo || ! ayuda ) {
		return;
	}
	function comprobar() {
		var digitos = campo.value.replace( /\D/g, '' );
		campo.removeAttribute( 'aria-invalid' );
		ayuda.className = 'esm-form__ayuda';
		if ( ! digitos ) {
			ayuda.textContent = '';
			return;
		}
		if ( digitos.length !== 4 ) {
			ayuda.textContent = cfg.formato;
			return;
		}
		if ( Object.prototype.hasOwnProperty.call( cfg.lista, digitos ) ) {
			ayuda.textContent = '✔ ' + digitos.slice( 0, 2 ) + '.' + digitos.slice( 2 ) + ' · ' + cfg.lista[ digitos ];
			ayuda.className = 'esm-form__ayuda es-valido';
		} else {
			ayuda.textContent = cfg.noValido;
			ayuda.className = 'esm-form__ayuda es-error';
			campo.setAttribute( 'aria-invalid', 'true' );
		}
	}
	campo.addEventListener( 'input', comprobar );
	campo.addEventListener( 'change', comprobar );
	comprobar();
}() );
