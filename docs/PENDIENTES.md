# Pendientes (a completar por Esmalto)

## Datos de empresa (placeholders del diseño)
Se editan en el pie (patrón sincronizado «Pie Esmalto»), en Contacto y en las páginas legales.
- CIF `00000000` · teléfono `600 600 600` · email `hola@esmalto.com` (el diseño también usa `info@esmalto.com`: unificar)
- Dirección del almacén de recogida («Castellón (España)»)
- Enlaces de Instagram y LinkedIn (`#`)
- IBAN para transferencias: *WooCommerce → Ajustes → Pagos → Transferencia bancaria*
- Textos legales definitivos: Aviso legal, Privacidad, Condiciones generales y Cookies
- Vídeo de YouTube para «Cómo calcular gastos de envío»

## Pagos
- Conectar Stripe con tu cuenta (*WooCommerce → Ajustes → Pagos → Stripe*). Las claves las introduces tú.

## Catálogo
- **Precios**: ningún producto tiene precio (ver `CATALOGO.md → Precio`).
- **Lucca**: sin fotos ni ficha → importado como borrador.
- Sin foto de pieza: Southwell Vein (12 variaciones) y Borneo Deck (6). Usan la foto del producto.
- Sin datos de palé: 31 variaciones de Besana, Helsinki, Lucca y Norway. En ellas no aparece la opción de palé.
- Sin m²/caja: `ESM-CORALINA-BLANCO-30X60` y `ESM-CORALINA-BLANCO-60X60`. En ellas la calculadora no funciona.
- Revisar las tablas de estilo y tono (`catalogo/scripts/build_catalog.py`).

## Envíos
- Ajustar los tramos de peso y sus precios (*WooCommerce → Ajustes → Envío → España peninsular*).

## Técnico
- Tipografías servidas desde Google Fonts. Para cumplir el RGPD conviene alojarlas en el servidor: cambia `esmalto_fonts_url` o añade los `woff2` en `theme.json`.
- Si se añade analítica o publicidad, hará falta un banner de consentimiento de cookies.
- WholesaleX: **no** configures descuentos en «Wholesale Pricing», porque los aplica Esmalto Core (ver `B2B.md`).
