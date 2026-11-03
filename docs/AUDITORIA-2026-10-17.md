# Auditoría de esmalto.com · 17/10/2026

Revisión completa de la web publicada, ordenada por prioridad. No he cambiado nada en la web: todo lo que sigue son propuestas.

**Qué he revisado**
- 15 páginas tipo como visitante sin sesión, en escritorio (1440 px) y en móvil (390 px): portada, tienda, categoría, ficha, Profesionales, Muestras, Contacto, Envíos, FAQ, Presupuesto, cesta, Mi cuenta, un texto legal, la búsqueda y el error 404.
- Las 73 direcciones del mapa del sitio y los 1.493 enlaces internos que contienen.
- Velocidad con Lighthouse (móvil, la misma prueba que usa PageSpeed Insights) y accesibilidad con axe (WCAG 2.2 AA).
- Servidor: cabeceras, caché, `robots.txt`, rutas sensibles y DNS del correo.
- Escritorio de WordPress, solo lectura: Salud del sitio, plugins, temas, usuarios y, en WooCommerce, pagos, envíos, impuestos y correos.

**Lo que no cuento como error.** La web está oculta a los buscadores porque así lo has decidido hasta que esté terminada: está marcada la casilla «Pedir a los motores de búsqueda que no indexen este sitio» y hay un `robots.txt` restrictivo. Ese paso va en «Día del lanzamiento».

Ojo: eso solo la saca de Google. Cualquiera que tenga el enlace puede verla. Si la quieres cerrada del todo, activa *WooCommerce → Ajustes → Visibilidad del sitio → Próximamente*. Tú y quien tenga sesión seguiréis viéndola.

## Resumen: lo primero

| # | Qué | Por qué |
|---|---|---|
| 1 | Datos reales de la empresa | Hoy salen «CIF 00000000» y «600 600 600»; la ley exige los datos reales |
| 2 | Textos legales completos | Las 4 páginas legales dicen «[Texto pendiente de redacción legal definitiva]» |
| 3 | Cookies sin permiso | 7 cookies de seguimiento y las tipografías de Google se cargan sin consentimiento |
| 4 | Página de alta de WholesaleX | Cualquiera puede pedir el alta eligiendo «Profesional A, B o C», sin CNAE |
| 5 | Correos que pueden no llegar | Se envían como @gmail.com desde CDmon; WPForms no guarda las solicitudes |
| 6 | Cobro | Stripe sin conectar y sin IBAN, pero la web promete tarjeta |
| 7 | Catálogo y precios | Falta la tarifa y hay datos incompletos |
| 8 | Información de compra y facturas | Falta el plazo de entrega y no se puede dar el NIF al pagar |
| 9 | Caché de 2 días en el navegador | Cambios que tardan en verse y profesionales sin su tarifa |
| 10 | Ficha de producto | 11,5 MB y 37,5 s hasta ver la imagen en móvil |

## Prioridad 1 · Antes de abrir la web

### 1. Datos reales de la empresa
- **Hoy:**
  - Salen «CIF 00000000», «600 600 600» y «Castellón (España)» en el pie, en Contacto y en los textos legales.
  - La dirección de la tienda en WooCommerce no tiene calle (solo «Castellón de la Plana 12001») y es la que sale en el pie de los correos.
  - «Cómo llegar» busca «Castellón de la Plana» en Google Maps.
  - Instagram y LinkedIn apuntan a «#».
- **Hacer:** poner razón social, NIF, domicilio, inscripción en el Registro Mercantil, teléfono y un único email. En el diseño conviven hola@ e info@.
- **Quién:** Esmalto da los datos y yo los pongo. Esfuerzo bajo.

### 2. Textos legales completos
- **Hoy:**
  - Aviso legal, Privacidad, Cookies y Condiciones generales muestran «[Texto pendiente de redacción legal definitiva]».
  - En Condiciones, «Devoluciones» dice «Pendiente de redacción legal definitiva».
  - La página «Reembolso y devoluciones» de WooCommerce está en borrador.
- **Hacer:**
  - **Aviso legal:** los datos que pide la LSSI (art. 10).
  - **Privacidad completa (RGPD art. 13):**
    - finalidades, base legal y plazos de conservación;
    - destinatarios: hosting CDmon, Stripe y, si se instala analítica, Google;
    - derechos y reclamación ante la AEPD.
  - **Condiciones de contratación:**
    - desistimiento de 14 días con su formulario;
    - devoluciones: cajas completas, sin abrir y del mismo lote;
    - garantía legal de 3 años;
    - plazos de entrega, reclamaciones y ley aplicable.
  - **Debajo de cada formulario** (contacto, muestras, presupuesto, alta profesional y registro): la información básica de privacidad.
  - **Textos de privacidad de WooCommerce:** que digan para qué se usan los datos. Hoy pone «mejorar tu experiencia en esta web y otros propósitos».
- **Quién:** asesor legal (o un generador profesional); los maqueto yo. Esfuerzo medio.

### 3. Cookies que hoy se instalan sin permiso
- **Hoy:**
  - En la primera visita se guardan 7 cookies `sbjs_*` de la «Atribución de pedidos» de WooCommerce, que registra de dónde viene cada visitante.
  - Las tipografías se cargan desde los servidores de Google, que reciben la IP del visitante.
  - La política de cookies dice que solo hay cookies técnicas.
- **Hacer:**
  - Desactivar la atribución de pedidos (*WooCommerce → Ajustes → Avanzado → Funciones*), o activarla solo con consentimiento cuando esté Complianz.
  - Servir las tipografías desde esmalto.com.
  - Actualizar la política de cookies.
- **Quién:** yo. Esfuerzo bajo.

### 4. Página pública de alta de WholesaleX
- **Hoy:** `/wholesale-register/` está publicada. No sale en el mapa del sitio, pero se puede abrir. Es un formulario en inglés que deja a cualquiera elegir «Profesional A, B o C» sin pedir el CNAE. Cuando haya precios, si WholesaleX aprueba las altas automáticamente, sería una puerta directa a la tarifa profesional.
- **Hacer:**
  - Despublicarla.
  - Comprobar en WholesaleX que toda alta queda pendiente de aprobación.
  - Revisar las 2 cuentas profesionales que existen y borrarlas si son de prueba.
- **Quién:** yo, con tu visto bueno. Esfuerzo bajo.

### 5. Correos que pueden no llegar
- **Hoy:**
  - WooCommerce firma los correos con una dirección @gmail.com, pero salen del servidor de CDmon. Como no pasa SPF ni DKIM de gmail.com, lo más probable es que Gmail y Outlook lo traten como suplantación y lo manden a spam o lo rechacen.
  - WPForms Lite no guarda las solicitudes. Si el correo de una muestra, un presupuesto o un alta no llega, esa solicitud se pierde.
- **Hacer:**
  - Crear un buzón @esmalto.com (por ejemplo, pedidos@) y enviar por el SMTP autenticado de CDmon con WP Mail SMTP.
  - Confirmar que CDmon firma con DKIM. El SPF y el DMARC del dominio ya están.
  - Guardar un registro de los correos enviados, o usar formularios que guarden las entradas.
  - Enviar un correo de prueba de cada tipo.
- **Quién:** tú creas el buzón en CDmon y el resto lo hago yo. Esfuerzo bajo.

### 6. Cobro
- **Hoy:**
  - Solo está activa la transferencia y falta el IBAN.
  - «Formas de pago» y las FAQ prometen tarjeta, Apple Pay y Google Pay, pero Stripe no está conectado.
  - El plugin de Stripe tiene una actualización pendiente.
- **Hacer:** conectar Stripe con tus claves, probarlo en modo de pruebas y poner el IBAN. Hasta entonces, ajustar esos textos.
- **Quién:** tú pones las claves y el IBAN; yo pruebo y reviso. Esfuerzo bajo.

### 7. Catálogo y precios
- La tarifa por referencia y un pedido de prueba real (bloque D1 del repaso).
- Coralina blanco 30x60 y 60x60 no tienen m²/caja, así que su calculadora no funciona.
- 31 variaciones no tienen datos de palé (Besana, Helsinki, Lucca y Norway).
- Fotos que faltan: Lucca no tiene ninguna (por eso está privada); Southwell Vein y Borneo Deck no tienen foto de pieza.
- **Quién:** Esmalto pasa los datos y yo los cargo.

### 8. Información obligatoria en la compra
- **Plazo de entrega:** que se vea en la ficha, al pagar y en el correo de confirmación. Es lo primero que pregunta quien compra cerámica.
- **NIF y razón social:** opcionales al pagar. Hoy solo se piden en el registro, así que un pedido sin cuenta no puede llevar NIF para la factura.
- **Correo de confirmación:** que incluya las condiciones y el derecho de desistimiento.
- **Facturas:** decidir cómo se emiten. Si las genera la web, tiene que ser con un sistema adaptado a Verifactu, obligatorio desde el 1/1/2027 para sociedades y desde el 1/7/2027 para autónomos.
- **Quién:** tú decides y yo lo configuro. Esfuerzo medio.

### 9. Caché de 2 días en el navegador
- **Hoy:** el servidor indica a los navegadores que guarden cada página 2 días (`Cache-Control: max-age=172800`). Tiene dos efectos:
  - Quien ya ha visitado la web puede ver la versión antigua hasta 2 días después de un cambio, por ejemplo al activar los precios.
  - Un profesional que inicia sesión puede ver páginas guardadas antes, sin su tarifa.
- **Hacer:** que el navegador no guarde las páginas y que guarde un año los archivos estáticos (imágenes, CSS y JS).
- **Quién:** CDmon, o yo si hay acceso al `.htaccess` (gestor de archivos de CDmon o editor de archivos de Yoast). Esfuerzo bajo.

## Prioridad 2 · Velocidad, accesibilidad y seguridad

### 10. Ficha de producto muy pesada
- **Hoy, en móvil:**
  - La ficha pesa 11,5 MB, hace 131 peticiones y la imagen principal tarda 37,5 s en aparecer. Lighthouse le da 63/100 de rendimiento.
  - Para el zoom, carga a tamaño completo todas las fotos de la galería: 50 en Ambrossia, 36 en Vermont y 26 en Harper. Las más grandes pesan 500–750 KB.
  - Las miniaturas se ven borrosas: son de 100 px y se muestran más grandes.
- **Hacer:**
  - Quitar el zoom, o cargarlo solo al pasar el ratón.
  - Poner menos fotos en la galería; las de ambiente pueden ir en una sección aparte.
  - Dejar los originales en 1.600 px y 250 KB como máximo.
  - Dar prioridad a la foto de la variación inicial.
  - Usar miniaturas más grandes.
- **Quién:** yo. Esfuerzo medio.

### 11. Servidor lento
- **Hoy:** el servidor tarda 1,3–3 s en empezar a enviar cualquier página. Salud del sitio indica que OPcache de PHP está desactivado y que no hay caché de páginas ni de objetos.
- **Hacer:**
  - Activar OPcache en el panel de CDmon.
  - Instalar una caché de páginas que excluya la cesta, el pago, Mi cuenta y a los usuarios con sesión (por los precios profesionales).
  - Usar Redis si el plan de CDmon lo permite.
- **Quién:** tú o CDmon para OPcache y Redis; yo el plugin de caché. Esfuerzo bajo.

### 12. Imágenes y carga de la portada
- **Hoy, en móvil:** la portada saca 67/100 y su imagen principal tarda 8,2 s; la categoría Baño saca 76/100 y tarda 4,5 s.
- **Causas:**
  - La foto de portada pesa 311 KB y se sirve a 1.800 px también en móvil.
  - Las tarjetas de Espacio y Formato son de 1.000 px y se ven a 200–350 px.
  - En móvil, 14 imágenes de la portada no se cargan en diferido.
  - El CSS retrasa el primer pintado unos 1,6 s.
- **Hacer:** versiones de 600, 1.200 y 1.800 px (`srcset`), carga diferida de lo que queda fuera de pantalla y CSS crítico en línea.
- **Quién:** yo. Esfuerzo medio.

### 13. Accesibilidad (WCAG 2.2 AA)
Lighthouse da 92–96/100, pero axe encuentra cinco tipos de fallo grave:
- **Botones blancos sobre amarillo** (contraste 1,6:1) en «Nuevo en la tienda» de la cesta vacía: poner el texto oscuro.
- **Enlaces dentro de párrafos** que solo se distinguen por el color (FAQ y búsqueda): subrayarlos.
- **Textos pequeños con contraste 3,8:1** (crédito del pie y nota del formulario profesional): subirlos a 4,5:1.
- **Controles demasiado pequeños:** la casilla de privacidad del alta profesional (13 px) y el botón «ver contraseña» de Mi cuenta (14 px). El mínimo es 24 px.
- **Teclado:** la mini-cesta cerrada sigue siendo navegable con el teclado en todas las páginas, y las miniaturas de la galería no se pueden recorrer con él.

Si Esmalto tiene 10 o más empleados o factura más de 2 M€, la accesibilidad es obligatoria desde el 28/06/2025 (Ley 11/2023) y conviene publicar una declaración de accesibilidad.
- **Quién:** yo. Esfuerzo bajo-medio.

### 14. Seguridad y mantenimiento
- **Acceso al escritorio:**
  - Verificación en dos pasos para los 2 administradores.
  - Límite de intentos en `wp-login.php`.
  - Desactivar XML-RPC, que hoy responde.
  - Ocultar la lista de usuarios de la API pública (`/wp-json/wp/v2/users` muestra los nombres de los administradores) y desactivar los archivos de autor en Yoast.
- **Cabeceras de seguridad que faltan:** HSTS, `X-Content-Type-Options`, `X-Frame-Options` y `Referrer-Policy`.
- **Plugins y temas:**
  - Actualizar Astra y WooCommerce Stripe Gateway.
  - Borrar los plugins inactivos (Akismet y Hello Dolly).
  - Borrar los temas Twenty Twenty-Two, Twenty Twenty-Three y Twenty Twenty-Four. Hay que mantener Astra, que es la base del tema Esmalto, y Twenty Twenty-Five.
- **Copias de seguridad:** All-in-One WP Migration es manual. Hacen falta copias automáticas fuera del servidor (al menos diarias de la base de datos) y una prueba de restauración.
- **Vigilancia:** un aviso si la web se cae, por ejemplo con UptimeRobot.
- **Quién:** yo, salvo la verificación en dos pasos, que activas tú con tu móvil. Esfuerzo bajo.

## Prioridad 3 · Experiencia de compra, contenido y marca

### 15. Ficha de producto
- **Orden en móvil:** color, formato y «Solicitar presupuesto» aparecen después de unos 1.400 px de especificaciones.
  - Subir los selectores y el botón.
  - Plegar las especificaciones.
  - Añadir una barra fija con el botón.
- **Datos repetidos y contradictorios:** las especificaciones salen arriba y otra vez en «Características técnicas». Hay incoherencias como «9 mm» y «9 MM», o «Antideslizante: No» junto a «Versión antideslizante: Sí», y «ADZ» no se explica. Mejor una sola tabla con la clase de resbaladicidad de cada formato, clave en Exterior y Piscina.
- **Aviso de la ficha:** «Puede haber errores en los detalles de los productos…» resta confianza. Mejor «¿Dudas técnicas? Te asesoramos», con el teléfono.

### 16. Nombres del fabricante en inglés
- **Hoy:**
  - «Porce Plus», «Porcelain» y «Eco Woods» salen en las tarjetas, en la ficha («Familia») y en el filtro «Familia técnica».
  - Siguen publicadas 4 categorías antiguas con esos nombres (`/categoria-producto/pavimentos-y-revestimientos/…`). Repiten productos y están en el mapa del sitio.
- **Hacer:**
  - Mostrar el aspecto (Piedra, Mármol, Madera…) en su lugar.
  - Quitar el filtro «Familia técnica».
  - Sacar esas categorías del mapa del sitio o redirigirlas.
  - Ordenar el filtro «Formato» por tamaño y escribir el decimal con coma («33,3x100»).

### 17. Cesta, 404 y contacto
- **Cesta vacía:** dice «¡Tu carrito está actualmente vacío!» con una carita triste grande, cuando la web usa «Cesta». Ajustar el texto y el diseño a la marca.
- **Errores de WooCommerce que no se ven:**
  - En la cesta, WooCommerce 11.2.1 pide una dirección con «undefined» y recibe un 404.
  - La plantilla de la mini-cesta tiene enlaces «state.cartItem.permalink», que los rastreadores ven como 67 páginas rotas.
  - Ensuciarán Search Console; revisarlos tras la próxima actualización de WooCommerce.
- **Página 404:** es la genérica de Astra. Añadir un buscador de productos, las categorías y el contacto.
- **Contacto:**
  - El recuadro del mapa está vacío: poner un mapa o una foto real con la dirección, el horario, un teléfono que se pueda pulsar y WhatsApp.
  - El formulario empieza con «Comercial» elegido; mejor «Elige un motivo».

### 18. Portada
- **Móvil:** las 6 tarjetas de Espacio y las 4 de Formato van a ancho completo, unos 3.400 px de desplazamiento. Mejor dos columnas o un carrusel horizontal.
- **Confianza:** faltan quiénes sois, el almacén o la exposición, proyectos realizados, opiniones de clientes, pago seguro y envío, y una llamada a pedir muestras.

### 19. Textos y correos
- **Términos:** unificar «pallets» y «palés», «carrito» y «cesta», y evitar «B2B» en Profesionales y en las FAQ.
- **Correos de WooCommerce:** usan el morado por defecto, no llevan logo y el pie está vacío. Ponerles el logo, los colores y los datos de Esmalto.
- **Novedades:** la casilla «Quiero recibir novedades» del registro necesita una herramienta de envío (MailPoet, Brevo…). Si no, mejor quitarla.

## Prioridad 4 · Crecer después de abrir
- **Analítica:**
  - GA4 con Complianz, ya decidido; falta el ID G-….
  - Medir conversiones: muestras, presupuestos, altas y pedidos.
  - Modo de consentimiento v2.
- **Google y Bing:**
  - Search Console y Bing Webmaster Tools.
  - Perfil de Empresa en Google, si hay exposición o almacén abierto al público.
  - Merchant Center, para salir gratis en Google Shopping cuando haya precios.
- **Contenido para buscadores:**
  - Textos de categoría más largos (hoy tienen 1–3 frases).
  - Guías: exterior y antideslizante, gran formato, cómo calcular los m².
  - Datos de empresa local (LocalBusiness) con la dirección y el horario reales.
- **Opiniones:** en las fichas (hoy están desactivadas) o reseñas de Google.
- **Pagos:** Apple Pay y Google Pay con Stripe; Bizum con el TPV del banco si lo piden los particulares.
- **Extras:** tarifa en PDF para profesionales, visualizador de ambientes y versión en inglés.
- **Mantenimiento:** entorno de pruebas, actualizaciones semanales y revisión mensual de Salud del sitio.

## Día del lanzamiento
1. Desmarcar *Ajustes → Lectura → Pedir a los motores de búsqueda que no indexen este sitio*.
2. Sustituir el `robots.txt` del servidor. Es un archivo del hosting, no de WordPress.
   - **Ahora:** bloquea a todos los rastreadores salvo Google y Bing, les pide esperar 300 s entre página y página y no indica el mapa del sitio.
   - **El nuevo debe:**
     - permitir todo;
     - bloquear la cesta, el pago, Mi cuenta, `?add-to-cart` y los filtros (`?filter_…`, hoy más de 1.400 combinaciones);
     - indicar `Sitemap: https://www.esmalto.com/sitemap_index.xml`.
3. Comprobar que salen las etiquetas canónicas. Yoast no las pone mientras la web tiene `noindex`.
4. En Search Console y Bing: verificar la web, enviar el mapa del sitio y pedir la indexación de la portada y las categorías.
5. Si activaste «Próximamente», desactivarlo. Hacer un pedido real de principio a fin y revisar los correos.

## Lo que ya está bien
- HTTPS con una sola redirección a www, y HTTP/2.
- Las páginas no se mueven al cargar (CLS 0) y en móvil no hay scroll lateral. Lighthouse da 100/100 en buenas prácticas.
- Títulos y descripciones correctos en las páginas principales, las 15 categorías y las 32 fichas.
- Imágenes con texto alternativo, idioma «es» y enlace «Saltar al contenido».
- El error 404 devuelve código 404.
- El mapa del sitio no incluye la cesta, el pago ni Mi cuenta. No hay enlaces internos rotos que vea el usuario.
- SPF y DMARC del dominio configurados y comentarios cerrados.
- Formularios con casilla de privacidad y calculadora con merma del 10 %.

## Mediciones

| Página (móvil) | Rendimiento | Accesibilidad | Buenas prácticas | SEO* | Imagen principal (LCP) | Peso |
|---|---|---|---|---|---|---|
| Portada | 67 | 96 | 100 | 58 | 8,2 s | 1,5 MB |
| Categoría Baño | 76 | 96 | 100 | 58 | 4,5 s | 1,0 MB |
| Ficha Ambrossia | 63 | 92 | 100 | 58 | 37,5 s | 11,5 MB |

\* La nota de SEO baja por el `noindex` que mantienes a propósito.

Medido con Lighthouse 12.8 en móvil simulado (4G lenta) el 17/10/2026. El tiempo hasta el primer byte, medido desde aquí, fue de 1,3–3 s en las 15 páginas.

## Fuentes
- [WooCommerce: Order Attribution Tracking](https://woocommerce.com/document/order-attribution-tracking/)
- [Cuatrecasas: entrada en vigor de la Ley de Accesibilidad](https://www.cuatrecasas.com/es/global/propiedad-intelectual/art/claves-proxima-entrada-vigor-ley-accesibilidad-espana)
- [Legálitas: European Accessibility Act y comercio online](https://www.legalitas.com/actualidad/european-accessibility-act)
- [Infoautónomos: aplazamiento de Verifactu a 2027](https://www.infoautonomos.com/?p=102125)
