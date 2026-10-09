<?php
/**
 * Title: Introducción de la tienda
 * Slug: esmalto/intro-tienda
 * Categories: esmalto, woo-commerce
 * Keywords: tienda, catálogo, filtros, chips, ordenar
 * Viewport Width: 1400
 * Description: Ruta, título, texto y barra «Uso / Ordenar» que se muestran a todo el ancho encima del listado de productos.
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
<div class="wp-block-group esm-barra-tienda" style="border-top-color:rgba(255,255,255,0.08);border-top-width:1px;border-bottom-color:rgba(255,255,255,0.08);border-bottom-width:1px;margin-top:clamp(24px, 3vw, 32px);padding-top:16px;padding-bottom:16px"><!-- wp:group {"style":{"spacing":{"blockGap":"8px"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"className":"is-style-etiqueta"} -->
<p class="is-style-etiqueta">Uso</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"esm-chips-espacio","style":{"spacing":{"blockGap":"8px"}}} -->
<div class="wp-block-buttons esm-chips-espacio"><!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/tienda/">Todos</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/tienda/?filter_espacio=bano">Baño</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/tienda/?filter_espacio=cocina">Cocina</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/tienda/?filter_espacio=salon">Salón</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/tienda/?filter_espacio=dormitorio">Dormitorio</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:shortcode -->
[esmalto_ordenar]
<!-- /wp:shortcode --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
