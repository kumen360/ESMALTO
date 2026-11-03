# Pendientes (a completar por Esmalto)

Lo pendiente del repaso del 10/10 está en `REPASO-2026-10-10.md` → «No posible todavía»; lo de los ajustes del 25/10, en `AJUSTES-2026-10-25.md` → «Lo que haces tú», «Pendiente de tu confirmación» y «No posible todavía»; lo de la revisión del 29/10, en `REVISION-2026-10-29.md`; lo de la del 03/11, en `REVISION-2026-11-03.md`.

## Datos de empresa
- Kumen Tres Sesenta, S.L. (Esmalto Cerámica), NIF B88655014, +34 614 05 10 42, hola@esmalto.com. Se aplican con *Esmalto → Herramientas → 6*; el pie y Contacto están en el tema.
- Desde el 03/11:
  - oficina técnica de ventas: Calle La Llosa, 3, planta 3, puerta 16, 12003 Castellón de la Plana, la dirección que sale en la web;
  - domicilio social: C/ Bolsa, 44, 11540 Sanlúcar de Barrameda (Cádiz), en el bloque del titular de los textos legales.
- Recogida en almacén: según las condiciones generales, el punto se comunica después de la compra. Si algún día se quiere publicar, se pone en *Esmalto → Ajustes → Recogida en almacén*.
- Enlaces de Instagram y LinkedIn: los iconos están ocultos (visibilidad de bloque en el pie) hasta tener las URL.
- IBAN para transferencias: *WooCommerce → Ajustes → Pagos → Transferencia bancaria*
- Textos legales: versión 1.2, publicada el 03/11. Lo que debe validar el asesor está en `REVISION-2026-11-03.md`. Si se activan Google Analytics o Stripe, añadir sus cookies a la tabla (`Esmalto_Paginas::cookies_instaladas()`).

## Pagos
- Conectar Stripe con tu cuenta (*WooCommerce → Ajustes → Pagos → Stripe*), a nombre de Kumen y con «ESMALTO» en el extracto. Las claves las introduces tú.
- Facturas con Holded (serie ESM-): conectar WooCommerce y comprobar que llega el NIF (`_billing_nif`).

## Catálogo
- **Precios**: tarifa del Excel de Esmalto cargada el 25/10 (333 de 335; faltan Arenisca 75x75 Marengo y San Francisco 60x120 Aquamarine). **Visibles desde el 26/10** (*Esmalto → Ajustes → Mostrar precios y vender online*); esa casilla también activa «Añadir a la cesta». Para pagar solo hay transferencia, y falta el IBAN. El Excel de trabajo es `Esmalto - Tarifa web.xlsx` (no se sube al repositorio).
- **Lucca**: sin fotos ni ficha → importado como **privado** (no se ve en la tienda).
- Sin foto de pieza: Southwell Vein (12 variaciones) y Borneo Deck (6). Usan la foto del producto.
- Datos de palé de Besana, Helsinki, Lucca y Norway y m²/caja de Coralina blanco 30x60 y 60x60: completados el 25/10 con los datos de Esmalto (`build_catalog.py`, `EMBALAJE_FORMATO` y `EMBALAJE_SKU`). Coralina blanco sale de la ficha de Ecoceramic: pendiente de que lo confirme Víctor.
- Revisar las tablas de estilo y tono (`catalogo/scripts/build_catalog.py`).

## Repositorio privado
- Privado desde el 03/11.
- Las fotos y fichas ya están en la biblioteca de medios. Las herramientas que descargan del repositorio solo funcionan si es público durante la operación:
  - *Precargar fotos del catálogo*;
  - *Importar fichas técnicas*;
  - la importación del CSV.
- Para actualizar el tema o el plugin, sube los ZIP de `dist/` desde *Apariencia → Temas* y *Plugins → Añadir → Subir*, o con `tools/build_subida.py` (ver `DESPLIEGUE.md`).
- Instalados en esmalto.com: tema 1.3.3 y Esmalto Core 1.6.1 (03/11).

## Diferencias deliberadas con el diseño
- Se mantiene «Cesta (0)» también en móvil (el diseño la oculta) para poder llegar a la cesta.
- El alta profesional pide además el CNAE (requisito del brief). La nota «Acceso gestionado con WholesaleX…» no se ha incluido.
- Las tarjetas de la tienda son por colección (con sus medidas), no una por formato como en el prototipo.
- La ficha añade la pestaña «Descripción y embalaje» con todos los datos técnicos.

## Envíos
- Porte por m² y palé: 2,50 €/m² + 9 €/palé (sin IVA), hasta revisarlo el 31 de marzo (*WooCommerce → Ajustes → Envío → España peninsular*).

## Técnico
- Tipografías alojadas en el tema desde el 25/10 (sin Google Fonts).
- Banner de cookies: Complianz instalado el 02/11. Analítica: GA4 con el modo de consentimiento v2, cuando exista la cuenta de Google de Esmalto (el ID se pone en Complianz).
- Correo: crear hola@, pedidos@ y clientes.b2b@ con DKIM y poner el SMTP en WordPress (WP Mail SMTP o FluentSMTP). Las solicitudes de los formularios se guardan en *Esmalto → Solicitudes*.
- Servidor de CDmon: el 25/10 estuvo más de una hora muy lento (30-75 s por página, los archivos estáticos 0,1 s) y la prueba de «loopback» de *Herramientas → Salud del sitio* fallaba por tiempo; luego volvió a 1,5-2 s. Si se repite, revisar el consumo de CPU y procesos en el panel de CDmon y consultarlo con su soporte.
- WholesaleX: **no** configures descuentos en «Wholesale Pricing», porque los aplica Esmalto Core (ver `B2B.md`).
