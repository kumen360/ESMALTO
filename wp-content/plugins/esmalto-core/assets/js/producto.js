/**
 * Esmalto · ficha de producto
 * - Selector de variaciones como chips (el <select> sigue existiendo para WooCommerce).
 * - Precio por m² de la variación elegida (sin/con IVA) y modo «Precio a consultar».
 * - Calculadora: cajas = ⌈m² / m² por caja⌉, coste total, palés para profesionales.
 */
( function ( $ ) {
	'use strict';

	var cfg = window.esmaltoProducto || {};
	var txt = cfg.textos || {};
	var mon = cfg.moneda || { simbolo: '€', decimales: 2, sepDecimal: ',', sepMiles: '.', formato: '%2$s %1$s' };

	function numero( n, dec ) {
		dec = typeof dec === 'number' ? dec : 2;
		var partes = ( Math.round( n * Math.pow( 10, dec ) ) / Math.pow( 10, dec ) ).toFixed( dec ).split( '.' );
		partes[ 0 ] = partes[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, mon.sepMiles );
		return partes.length > 1 ? partes[ 0 ] + mon.sepDecimal + partes[ 1 ] : partes[ 0 ];
	}

	function numeroCorto( n ) {
		return numero( n, Math.abs( n - Math.round( n ) ) < 0.005 ? 0 : 2 );
	}

	function dinero( n ) {
		return mon.formato.replace( '%1$s', mon.simbolo ).replace( '%2$s', numero( n, mon.decimales ) );
	}

	function sprintf( plantilla ) {
		var args = Array.prototype.slice.call( arguments, 1 );
		return plantilla.replace( /%(\d)\$s/g, function ( m, i ) {
			return args[ i - 1 ];
		} ).replace( /%s/, args[ 0 ] );
	}

	function leerNumero( valor ) {
		var n = parseFloat( String( valor || '' ).replace( ',', '.' ) );
		return isNaN( n ) ? 0 : n;
	}

	$( function () {
		var $form = $( 'form.variations_form' );
		if ( ! $form.length ) {
			return;
		}
		var $precio = $( '[data-esm-precio]' );
		var $principal = $precio.find( '[data-esm-principal]' );
		var $nota = $precio.find( '[data-esm-nota]' );
		var $lineas = $precio.find( '[data-esm-lineas]' );
		var original = { principal: $principal.html(), nota: $nota.text() };
		var $calc = $( '[data-esm-calc]' );
		var $presupuesto = $( '[data-esm-presupuesto]' );
		var $muestra = $( '[data-esm-muestra]' );
		var hrefPresupuesto = $presupuesto.attr( 'href' ) || '';
		var hrefMuestra = $muestra.attr( 'href' ) || '';
		var actual = null;

		/* ---------------- Chips ---------------- */
		function pintarChips() {
			$form.find( 'table.variations select' ).each( function () {
				var $sel = $( this );
				var $chips = $sel.siblings( '.esm-chips' );
				if ( ! $chips.length ) {
					$chips = $( '<div class="esm-chips" role="group"></div>' ).attr( 'aria-label', $sel.closest( 'tr' ).find( 'label' ).text() );
					$sel.addClass( 'esm-oculto' ).after( $chips );
				}
				$chips.empty();
				$sel.find( 'option' ).each( function () {
					if ( ! this.value ) {
						return;
					}
					var activo = $sel.val() === this.value;
					var $chip = $( '<button type="button" class="esm-chip"></button>' )
						.text( $( this ).text() )
						.attr( 'data-value', this.value )
						.attr( 'aria-pressed', activo ? 'true' : 'false' )
						.toggleClass( 'is-activo', activo );
					if ( this.disabled || $( this ).hasClass( 'disabled' ) ) {
						$chip.addClass( 'is-agotado' ).attr( 'title', 'No disponible con la selección actual' );
					}
					$chips.append( $chip );
				} );
			} );
		}

		$form.on( 'click', '.esm-chip', function () {
			var $sel = $( this ).closest( 'td' ).find( 'select' );
			var valor = String( $( this ).attr( 'data-value' ) );
			if ( $( this ).hasClass( 'is-agotado' ) ) {
				// Permite cambiar de combinación: limpia el resto de atributos.
				$form.find( 'table.variations select' ).not( $sel ).val( '' );
			}
			$sel.val( $sel.val() === valor ? '' : valor ).trigger( 'change' );
		} );
		$form.on( 'woocommerce_update_variation_values', pintarChips );
		$form.on( 'change', 'select', function () {
			window.setTimeout( pintarChips, 0 );
		} );
		pintarChips();

		/* ---------------- Precio ---------------- */
		function pintarPrecio( v ) {
			var e = v && v.esm ? v.esm : null;
			if ( ! e ) {
				return;
			}
			var html = [];
			if ( e.precio_caja > 0 && e.m2_caja > 0 ) {
				$principal.html( dinero( e.precio_caja / e.m2_caja ) + txt.m2 );
				$nota.text( cfg.ivaIncl ? txt.notaIvaIncl : txt.notaIvaExcl );
				html.push( '<div class="sin-iva">' + dinero( e.precio_caja_sin / e.m2_caja ) + ' <small>' + txt.sinIva + '</small></div>' );
				html.push( '<div class="con-iva">' + dinero( e.precio_caja_con / e.m2_caja ) + ' <small>' + txt.conIva + '</small></div>' );
			} else {
				$principal.text( txt.consultar );
				$nota.text( txt.notaConsultar );
			}
			if ( e.m2_caja > 0 ) {
				html.push( '<div><small>' + sprintf( txt.caja, numeroCorto( e.m2_caja ), e.piezas_caja || '—', numeroCorto( e.kg_caja ) ) + '</small></div>' );
			}
			if ( e.cajas_palet > 0 ) {
				html.push( '<div><small>' + sprintf( txt.palet, e.cajas_palet, numeroCorto( e.m2_palet || e.cajas_palet * e.m2_caja ) ) + '</small></div>' );
			}
			$lineas.html( html.join( '' ) );

			// Especificaciones de la variación elegida.
			if ( e.formato ) {
				$( '[data-esm-spec="formatos"]' ).text( e.formato );
			}
			if ( e.espesor ) {
				$( '[data-esm-spec="espesor"]' ).text( e.espesor );
			}
			if ( e.m2_caja > 0 ) {
				var emb = sprintf( txt.embalaje, e.piezas_caja || '—', numeroCorto( e.m2_caja ), numeroCorto( e.kg_caja ) ) + ( e.cajas_palet > 0 ? sprintf( txt.embalajePalet, e.cajas_palet ) : '' );
				$( '[data-esm-spec="embalaje"]' ).text( emb ).closest( 'li' ).prop( 'hidden', false );
			}
		}

		function restaurarPrecio() {
			$principal.html( original.principal );
			$nota.text( original.nota );
			$lineas.empty();
			$( '[data-esm-fila="embalaje"]' ).prop( 'hidden', true );
		}

		/* ---------------- Calculadora ---------------- */
		function calcular() {
			var e = actual && actual.esm ? actual.esm : null;
			if ( ! e ) {
				$calc.prop( 'hidden', true );
				return null;
			}
			$calc.prop( 'hidden', false );
			var $nota = $calc.find( '[data-esm-nota]' );
			var $aplicar = $calc.find( '[data-esm-aplicar]' );
			var $opcionPalet = $calc.find( '[data-esm-palet-opcion]' );
			$opcionPalet.prop( 'hidden', ! ( cfg.b2b && e.cajas_palet > 0 ) );

			if ( ! ( e.m2_caja > 0 ) ) {
				$calc.find( '[data-esm-cajas],[data-esm-m2reales],[data-esm-total],[data-esm-precio-caja]' ).text( '—' );
				$nota.text( txt.sinCalculo );
				$aplicar.prop( 'hidden', true );
				return null;
			}

			var m2 = leerNumero( $calc.find( '[data-esm-m2]' ).val() );
			var merma = $calc.find( '[data-esm-merma]' ).is( ':checked' );
			if ( merma ) {
				m2 = m2 * 1.1;
			}
			var cajas = m2 > 0 ? Math.ceil( m2 / e.m2_caja - 1e-9 ) : 0;
			var porPalets = cfg.b2b && e.cajas_palet > 0 && $calc.find( '[data-esm-palet]' ).is( ':checked' );
			var palets = 0;
			if ( porPalets && cajas > 0 ) {
				palets = Math.ceil( cajas / e.cajas_palet );
				cajas = palets * e.cajas_palet;
			} else if ( e.cajas_palet > 0 ) {
				palets = Math.floor( cajas / e.cajas_palet );
			}
			var m2Reales = cajas * e.m2_caja;
			var notas = [];
			if ( merma ) {
				notas.push( txt.merma );
			}

			$calc.find( '[data-esm-cajas]' ).text( cajas );
			$calc.find( '[data-esm-m2reales]' ).text( numero( m2Reales, 2 ) + ' m²' );
			$calc.find( '[data-esm-dato-palets]' ).prop( 'hidden', ! ( e.cajas_palet > 0 ) );
			$calc.find( '[data-esm-palets]' ).text( e.cajas_palet > 0 ? numero( cajas / e.cajas_palet, 2 ) : '—' );

			if ( e.precio_caja > 0 ) {
				var total = cajas * e.precio_caja;
				if ( cfg.b2b && cfg.paletPct > 0 && palets > 0 ) {
					var descuento = palets * e.cajas_palet * e.precio_caja * cfg.paletPct / 100;
					total -= descuento;
					notas.push( sprintf( txt.descPalet, dinero( descuento ), palets ) );
				}
				$calc.find( '[data-esm-total]' ).text( cajas ? dinero( total ) : '—' );
				$calc.find( '[data-esm-precio-caja]' ).text( dinero( e.precio_caja ) );
				$aplicar.prop( 'hidden', ! ( cajas > 0 && actual.is_purchasable ) ).text( sprintf( txt.usar, cajas ) );
			} else {
				$calc.find( '[data-esm-total]' ).text( txt.consultar );
				$calc.find( '[data-esm-precio-caja]' ).text( '—' );
				$aplicar.prop( 'hidden', true );
				notas.push( txt.notaConsultar );
			}
			$nota.text( notas.join( ' ' ) );
			return { m2: m2Reales, cajas: cajas };
		}

		function actualizarEnlaces() {
			var r = calcular();
			var producto = actual && actual.esm ? actual.esm.nombre + ' (' + actual.esm.sku + ')' : null;
			if ( hrefPresupuesto ) {
				var url = new URL( hrefPresupuesto, window.location.href );
				if ( producto ) {
					url.searchParams.set( 'producto', producto );
				}
				if ( r && r.cajas ) {
					url.searchParams.set( 'm2', numero( r.m2, 2 ) );
					url.searchParams.set( 'cajas', r.cajas );
				}
				$presupuesto.attr( 'href', url.toString() );
			}
			if ( hrefMuestra && producto ) {
				var urlM = new URL( hrefMuestra, window.location.href );
				urlM.searchParams.set( 'producto', producto );
				$muestra.attr( 'href', urlM.toString() );
			}
		}

		$calc.on( 'input change', 'input', actualizarEnlaces );
		$calc.on( 'click', '[data-esm-aplicar]', function () {
			var r = calcular();
			if ( r && r.cajas ) {
				$form.find( 'input.qty' ).val( r.cajas ).trigger( 'change' );
				$form.find( '.single_add_to_cart_button' ).trigger( 'focus' );
			}
		} );

		$form.on( 'found_variation', function ( evento, variacion ) {
			actual = variacion;
			pintarPrecio( variacion );
			var comprable = !! ( variacion.is_purchasable && variacion.is_in_stock && variacion.esm && variacion.esm.precio_caja > 0 );
			$form.find( '.single_add_to_cart_button, .quantity' ).toggle( comprable );
			$presupuesto.toggleClass( 'esm-boton-presupuesto--principal', ! comprable );
			actualizarEnlaces();
		} );

		$form.on( 'reset_data hide_variation', function () {
			actual = null;
			restaurarPrecio();
			$calc.prop( 'hidden', true );
			$presupuesto.attr( 'href', hrefPresupuesto ).removeClass( 'esm-boton-presupuesto--principal' );
			$muestra.attr( 'href', hrefMuestra );
		} );
	} );
}( jQuery ) );
