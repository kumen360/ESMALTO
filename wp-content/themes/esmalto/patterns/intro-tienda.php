<?php
/**
 * Title: Introducción de la tienda
 * Slug: esmalto/intro-tienda
 * Categories: esmalto, woo-commerce
 * Keywords: tienda, catálogo, filtros, chips, ordenar
 * Viewport Width: 1400
 * Description: Ruta, título, texto y barra con las filas «Uso» y «Formato», «Filtros» (solo móvil) y «Ordenar», a todo el ancho encima del listado.
 */
?>
<!-- wp:group {"className":"esm-intro-tienda","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
<div class="wp-block-group esm-intro-tienda"><!-- wp:paragraph {"className":"is-style-miga"} -->
<p class="is-style-miga"><a href="/">Inicio</a> / Catálogo</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"style":{"spacing":{"margin":{"top":"12px","bottom":"0"}}}} -->
<h1 class="wp-block-heading" style="margin-top:12px;margin-bottom:0">Catálogo de colecciones</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"texto-apagado","style":{"spacing":{"margin":{"top":"12px","bottom":"0"}}}} -->
<p class="has-texto-apagado-color has-text-color" style="margin-top:12px;margin-bottom:0">Gres porcelánico técnico y cerámica de gran formato.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"esm-barra-tienda","style":{"spacing":{"margin":{"top":"clamp(24px, 3vw, 32px)"},"padding":{"top":"16px","bottom":"16px"},"blockGap":"16px"},"border":{"top":{"color":"rgba(255,255,255,0.08)","width":"1px"},"bottom":{"color":"rgba(255,255,255,0.08)","width":"1px"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group esm-barra-tienda" style="border-top-color:rgba(255,255,255,0.08);border-top-width:1px;border-bottom-color:rgba(255,255,255,0.08);border-bottom-width:1px;margin-top:clamp(24px, 3vw, 32px);padding-top:16px;padding-bottom:16px"><!-- wp:group {"className":"esm-barra-tienda__filas","style":{"spacing":{"blockGap":"10px"}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group esm-barra-tienda__filas"><!-- wp:group {"style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"className":"is-style-etiqueta"} -->
<p class="is-style-etiqueta">Uso</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"esm-chips-espacio","style":{"spacing":{"blockGap":"8px"}}} -->
<div class="wp-block-buttons esm-chips-espacio"><!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/tienda/">Todos</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/azulejos-bano/">Baño</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/azulejos-cocina/">Cocina</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/suelos-salon/">Salón</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/suelos-dormitorio/">Dormitorio</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/porcelanico-exterior/">Exterior</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/porcelanico-piscina/">Piscina</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"className":"is-style-etiqueta"} -->
<p class="is-style-etiqueta">Formato</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"esm-chips-formato","style":{"spacing":{"blockGap":"8px"}}} -->
<div class="wp-block-buttons esm-chips-formato"><!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/porcelanico-gran-formato/">Gran formato</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/lamas-porcelanicas/">Lamas</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/porcelanico-60x120/">60x120</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/porcelanico-60x60/">60x60</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group"><!-- wp:buttons {"className":"esm-boton-filtros"} -->
<div class="wp-block-buttons esm-boton-filtros"><!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="#secondary">Filtros</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:shortcode -->
[esmalto_ordenar]
<!-- /wp:shortcode --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
