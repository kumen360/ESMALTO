<?php
/**
 * Title: Acceso y alta profesional
 * Slug: esmalto/profesionales-acceso
 * Categories: esmalto
 * Keywords: login, registro, alta, profesionales, b2b, cnae
 * Viewport Width: 1400
 * Description: Dos columnas: inicio de sesión y formulario de alta profesional (empresa, CNAE validado, teléfono).
 */
?>
<!-- wp:group {"align":"full","className":"esm-seccion","style":{"spacing":{"padding":{"top":"clamp(48px, 7vw, 80px)","right":"clamp(16px, 4vw, 40px)","left":"clamp(16px, 4vw, 40px)","bottom":"0"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull esm-seccion" style="padding-top:clamp(48px, 7vw, 80px);padding-right:clamp(16px, 4vw, 40px);padding-bottom:0;padding-left:clamp(16px, 4vw, 40px)"><!-- wp:columns {"className":"esm-acceso","style":{"spacing":{"blockGap":{"left":"0"}},"border":{"color":"rgba(255,255,255,0.1)","width":"1px"}},"backgroundColor":"superficie"} -->
<div class="wp-block-columns esm-acceso has-border-color has-superficie-background-color has-background" style="border-color:rgba(255,255,255,0.1);border-width:1px"><!-- wp:column {"width":"42%","style":{"spacing":{"padding":{"top":"clamp(28px, 4vw, 44px)","right":"clamp(28px, 4vw, 44px)","bottom":"clamp(28px, 4vw, 44px)","left":"clamp(28px, 4vw, 44px)"}}}} -->
<div class="wp-block-column" style="padding-top:clamp(28px, 4vw, 44px);padding-right:clamp(28px, 4vw, 44px);padding-bottom:clamp(28px, 4vw, 44px);padding-left:clamp(28px, 4vw, 44px);flex-basis:42%"><!-- wp:paragraph {"className":"is-style-antetitulo"} -->
<p class="is-style-antetitulo">Acceso profesional</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"12px"}}},"fontSize":"xl"} -->
<h2 class="wp-block-heading has-xl-font-size" style="margin-top:12px">Ya tengo cuenta</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"texto-apagado","fontSize":"sm"} -->
<p class="has-texto-apagado-color has-text-color has-sm-font-size">Accede con tus credenciales para ver la tarifa mayorista y las condiciones de tu cuenta.</p>
<!-- /wp:paragraph -->

<!-- wp:loginout {"displayLoginAsForm":true,"redirectToCurrent":false,"className":"esm-login"} /-->

<!-- wp:paragraph {"fontSize":"xs"} -->
<p class="has-xs-font-size"><a href="/mi-cuenta/lost-password/">¿Olvidaste tu contraseña?</a> · <a href="/mi-cuenta/">Ir a mi cuenta</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"58%","style":{"spacing":{"padding":{"top":"clamp(28px, 4vw, 44px)","right":"clamp(28px, 4vw, 44px)","bottom":"clamp(28px, 4vw, 44px)","left":"clamp(28px, 4vw, 44px)"}},"border":{"left":{"color":"rgba(255,255,255,0.1)","width":"1px"}}}} -->
<div class="wp-block-column" style="border-left-color:rgba(255,255,255,0.1);border-left-width:1px;padding-top:clamp(28px, 4vw, 44px);padding-right:clamp(28px, 4vw, 44px);padding-bottom:clamp(28px, 4vw, 44px);padding-left:clamp(28px, 4vw, 44px);flex-basis:58%"><!-- wp:paragraph {"className":"is-style-antetitulo"} -->
<p class="is-style-antetitulo">Alta profesional</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"style":{"spacing":{"margin":{"top":"12px"}},"typography":{"lineHeight":"1.25"}},"fontSize":"xl"} -->
<h2 class="wp-block-heading has-xl-font-size" style="margin-top:12px;line-height:1.25">Rellena el formulario y activaremos tu cuenta profesional</h2>
<!-- /wp:heading -->

<!-- wp:shortcode -->
[esmalto_registro_profesional]
<!-- /wp:shortcode --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
