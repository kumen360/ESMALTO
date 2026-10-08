<?php
/**
 * Title: Introducción de la tienda
 * Slug: esmalto/intro-tienda
 * Categories: esmalto, woo-commerce
 * Keywords: tienda, catálogo, filtros, chips
 * Viewport Width: 1400
 * Description: Ruta, título y chips de espacio que se muestran encima del listado de productos.
 */
?>
<!-- wp:group {"className":"esm-intro-tienda","style":{"spacing":{"blockGap":"12px","margin":{"bottom":"18px"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group esm-intro-tienda" style="margin-bottom:18px"><!-- wp:paragraph {"className":"is-style-miga"} -->
<p class="is-style-miga"><a href="/">Inicio</a> / Catálogo</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"style":{"spacing":{"margin":{"top":"12px","bottom":"0"}}}} -->
<h1 class="wp-block-heading" style="margin-top:12px;margin-bottom:0">Catálogo de colecciones</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"texto-apagado"} -->
<p class="has-texto-apagado-color has-text-color">Gres porcelánico técnico y cerámica de gran formato. Filtra por espacio, estilo, tono, formato o uso interior y exterior.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"esm-chips-espacio","style":{"spacing":{"blockGap":"8px","margin":{"top":"18px"}}}} -->
<div class="wp-block-buttons esm-chips-espacio" style="margin-top:18px"><!-- wp:button {"className":"is-style-chip"} -->
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
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-chip"} -->
<div class="wp-block-button is-style-chip"><a class="wp-block-button__link wp-element-button" href="/tienda/?filter_ubicacion=exterior">Exterior</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
