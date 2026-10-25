# Pendientes (a completar por Esmalto)

Lo pendiente del repaso del 10/10 está en `REPASO-2026-10-10.md` → «No posible todavía».

## Datos de empresa (placeholders del diseño)
Se editan en el pie (*Apariencia → Editor → Patrones → Partes de plantilla → Pie*), en Contacto y en las páginas legales.
- CIF `00000000` · teléfono `600 600 600` · email `hola@esmalto.com` (el diseño también usa `info@esmalto.com`: unificar)
- Dirección y horario del almacén de recogida: *Esmalto → Ajustes → Recogida en almacén* (salen en «Cómo calcular gastos de envío» y en los pedidos con recogida). En el pie sigue «Castellón (España)».
- Enlaces de Instagram y LinkedIn (`#`)
- IBAN para transferencias: *WooCommerce → Ajustes → Pagos → Transferencia bancaria*
- Textos legales definitivos: Aviso legal, Privacidad, Condiciones generales y Cookies

## Pagos
- Conectar Stripe con tu cuenta (*WooCommerce → Ajustes → Pagos → Stripe*). Las claves las introduces tú.

## Catálogo
- **Precios**: ningún producto tiene precio (ver `CATALOGO.md → Precio`). Cuando llegue la tarifa: *Herramientas → Tarifa de precios* y después *Ajustes → Mostrar precios y vender online*.
- **Lucca**: sin fotos ni ficha → importado como **privado** (no se ve en la tienda).
- Sin foto de pieza: Southwell Vein (12 variaciones) y Borneo Deck (6). Usan la foto del producto.
- Datos de palé de Besana, Helsinki, Lucca y Norway y m²/caja de Coralina blanco 30x60 y 60x60: completados el 25/10 con los datos de Esmalto (`build_catalog.py`, `EMBALAJE_FORMATO` y `EMBALAJE_SKU`). Coralina blanco sale de la ficha de Ecoceramic: pendiente de que lo confirme Víctor.
- Revisar las tablas de estilo y tono (`catalogo/scripts/build_catalog.py`).

## Repositorio privado
- Las fotos y fichas ya están en la biblioteca de medios. Las herramientas que descargan del repositorio (*Precargar fotos del catálogo*, *Importar fichas técnicas*) y la importación del CSV solo funcionan si el repositorio es público durante la operación.
- Para actualizar el tema o el plugin, sube los ZIP de `dist/` desde *Apariencia → Temas* y *Plugins → Añadir → Subir*.
- El tema 1.2.0 y el plugin 1.3.0 de `dist/` están instalados en esmalto.com (17/10). Para la próxima subida sirve el mismo método: subir los ZIP o hacer público el repositorio durante la operación.

## Diferencias deliberadas con el diseño
- Se mantiene «Cesta (0)» también en móvil (el diseño la oculta) para poder llegar a la cesta.
- El alta profesional pide además el CNAE (requisito del brief). La nota «Acceso gestionado con WholesaleX…» no se ha incluido.
- Las tarjetas de la tienda son por colección (con sus medidas), no una por formato como en el prototipo.
- La ficha añade la pestaña «Descripción y embalaje» con todos los datos técnicos.

## Envíos
- Ajustar los tramos de peso y sus precios (*WooCommerce → Ajustes → Envío → España peninsular*).

## Técnico
- Tipografías servidas desde Google Fonts. Para cumplir el RGPD conviene alojarlas en el servidor: cambia `esmalto_fonts_url` o añade los `woff2` en `theme.json`.
- Analítica: no hay ninguna instalada. Propuesta en `REPASO-2026-10-10.md` (D4); con analítica o publicidad hará falta un aviso de consentimiento de cookies.
- WholesaleX: **no** configures descuentos en «Wholesale Pricing», porque los aplica Esmalto Core (ver `B2B.md`).
