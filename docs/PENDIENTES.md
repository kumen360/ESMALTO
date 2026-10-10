# Pendientes (a completar por Esmalto)

## Datos de empresa (placeholders del diseño)
Se editan en el pie (*Apariencia → Editor → Patrones → Partes de plantilla → Pie*), en Contacto y en las páginas legales.
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
- **Lucca**: sin fotos ni ficha → importado como **privado** (no se ve en la tienda).
- Sin foto de pieza: Southwell Vein (12 variaciones) y Borneo Deck (6). Usan la foto del producto.
- Sin datos de palé: 31 variaciones de Besana, Helsinki, Lucca y Norway. En ellas no aparece la opción de palé.
- Sin m²/caja: `ESM-CORALINA-BLANCO-30X60` y `ESM-CORALINA-BLANCO-60X60`. En ellas la calculadora no funciona.
- Revisar las tablas de estilo y tono (`catalogo/scripts/build_catalog.py`).

## Repositorio privado
- Las fotos y fichas ya están en la biblioteca de medios. Las herramientas que descargan del repositorio (*Precargar fotos del catálogo*, *Importar fichas técnicas*) y la importación del CSV solo funcionan si el repositorio es público durante la operación.
- Para actualizar el tema o el plugin, sube los ZIP de `dist/` desde *Apariencia → Temas* y *Plugins → Añadir → Subir*.
- En `dist/` están el tema 1.1.2 y el plugin 1.2.1, con la lista CNAE-2025 como lista inicial y la respuesta nueva de «¿Qué CNAE se admiten?». En esmalto.com ya están aplicadas (Ajustes y página Profesionales), así que subirlos no es urgente.

## Diferencias deliberadas con el diseño
- Se mantiene «Cesta (0)» también en móvil (el diseño la oculta) para poder llegar a la cesta.
- El alta profesional pide además el CNAE (requisito del brief). La nota «Acceso gestionado con WholesaleX…» no se ha incluido.
- Las tarjetas de la tienda son por colección (con «N formatos»), no una por formato como en el prototipo.
- La ficha añade la pestaña «Descripción y embalaje» con todos los datos técnicos.

## Envíos
- Ajustar los tramos de peso y sus precios (*WooCommerce → Ajustes → Envío → España peninsular*).

## Técnico
- Tipografías servidas desde Google Fonts. Para cumplir el RGPD conviene alojarlas en el servidor: cambia `esmalto_fonts_url` o añade los `woff2` en `theme.json`.
- Si se añade analítica o publicidad, hará falta un banner de consentimiento de cookies.
- WholesaleX: **no** configures descuentos en «Wholesale Pricing», porque los aplica Esmalto Core (ver `B2B.md`).
