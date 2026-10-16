<?php
/**
 * Title: Acceso y alta profesional
 * Slug: esmalto/profesionales-acceso
 * Categories: esmalto
 * Keywords: login, registro, alta, profesionales, b2b, cnae
 * Viewport Width: 1400
 * Description: Dos columnas: inicio de sesión y formulario de alta profesional (empresa, CNAE validado, teléfono). El encabezado del alta va en contenedores flex (Pila).
 */
?>
<!-- wp:group {"align":"full","className":"esm-seccion","style":{"spacing":{"padding":{"top":"clamp(48px, 7vw, 80px)","right":"clamp(16px, 4vw, 40px)","left":"clamp(16px, 4vw, 40px)","bottom":"0"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull esm-seccion" style="padding-top:clamp(48px, 7vw, 80px);padding-right:clamp(16px, 4vw, 40px);padding-bottom:0;padding-left:clamp(16px, 4vw, 40px)"><!-- wp:columns {"className":"esm-acceso","style":{"spacing":{"blockGap":{"left":"0"}},"border":{"color":"rgba(255,255,255,0.1)","width":"1px"}},"backgroundColor":"superficie"} -->
<div class="wp-block-columns esm-acceso has-border-color has-superficie-background-color has-background" style="border-color:rgba(255,255,255,0.1);border-width:1px"><!-- wp:column {"style":{"spacing":{"padding":{"top":"clamp(28px, 4vw, 44px)","right":"clamp(28px, 4vw, 44px)","bottom":"clamp(28px, 4vw, 44px)","left":"clamp(28px, 4vw, 44px)"}}}} -->
<div class="wp-block-column" style="padding-top:clamp(28px, 4vw, 44px);padding-right:clamp(28px, 4vw, 44px);padding-bottom:clamp(28px, 4vw, 44px);padding-left:clamp(28px, 4vw, 44px)"><!-- wp:paragraph {"className":"is-style-antetitulo"} -->
<p class="is-style-antetitulo">Acceso profesional</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"12px"}},"typography":{"fontSize":"clamp(22px, 2.6vw, 30px)"}}} -->
<h2 class="wp-block-heading" style="margin-top:12px;font-size:clamp(22px, 2.6vw, 30px)">Ya tengo cuenta</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"texto-apagado","fontSize":"sm"} -->
<p class="has-texto-apagado-color has-text-color has-sm-font-size">Accede con tus credenciales para ver la tarifa mayorista y las condiciones de tu cuenta.</p>
<!-- /wp:paragraph -->

<!-- wp:loginout {"displayLoginAsForm":true,"redirectToCurrent":false,"className":"esm-login"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"style":{"spacing":{"padding":{"top":"clamp(28px, 4vw, 44px)","right":"clamp(28px, 4vw, 44px)","bottom":"clamp(28px, 4vw, 44px)","left":"clamp(28px, 4vw, 44px)"}}}} -->
<div class="wp-block-column" style="padding-top:clamp(28px, 4vw, 44px);padding-right:clamp(28px, 4vw, 44px);padding-bottom:clamp(28px, 4vw, 44px);padding-left:clamp(28px, 4vw, 44px)"><!-- wp:group {"className":"esm-alta-encabezado","style":{"spacing":{"blockGap":"22px"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
<div class="wp-block-group esm-alta-encabezado"><!-- wp:group {"style":{"spacing":{"blockGap":"12px"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"className":"is-style-antetitulo"} -->
<p class="is-style-antetitulo">Alta profesional</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"typography":{"lineHeight":"1.25","fontSize":"clamp(22px, 2.6vw, 30px)"}}} -->
<h2 class="wp-block-heading" style="font-size:clamp(22px, 2.6vw, 30px);line-height:1.25">Solicita tu cuenta profesional</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"textColor":"texto-apagado","fontSize":"sm"} -->
<p class="has-texto-apagado-color has-text-color has-sm-font-size">Cuéntanos quién eres y a qué te dedicas. Comprobamos tu actividad y te damos acceso a la tarifa en 24–48 h.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:shortcode -->
[esmalto_registro_profesional]
<!-- /wp:shortcode --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
