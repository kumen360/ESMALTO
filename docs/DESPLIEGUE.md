# Despliegue y mantenimiento

## Instalación (orden)
1. **Plugins de WordPress.org**: WooCommerce, WholesaleX, WPForms Lite y WooCommerce Stripe Gateway. **Tema**: Astra.
2. **Tema hijo y plugin propio**: subir `dist/esmalto.zip` (Apariencia → Temas → Añadir → Subir) y `dist/esmalto-core.zip` (Plugins → Añadir → Subir). Activar el tema **Esmalto** y el plugin **Esmalto Core**.
3. **Esmalto → Herramientas → Configurar sitio**: WooCommerce, IVA, envíos, filtros, formularios y páginas. Deja activado «Próximamente». Con «Sobrescribir…» se rehacen las páginas, los formularios (mismos ID) y los filtros de la tienda con la versión original; los envíos y el IVA nunca se duplican.
4. **Precargar las fotos**: *Esmalto → Herramientas → Precargar fotos del catálogo*. Descarga las 673 fotos en lotes de 6 (unos 25 min), con su texto ALT, y reintenta solo si el servidor corta. Así la importación no tiene que descargar decenas de fotos en una sola petición.
5. **Importar el catálogo**: *Productos → Importar* con `catalogo/salida/esmalto-productos.csv`.
   - **No** marques «Actualizar productos existentes» en la primera importación.
   - El mapeo de columnas es automático (las columnas `Meta:` se importan como metadatos).
   - Las fotos y fichas se descargan desde GitHub: el repositorio debe ser **público** mientras se precargan e importan. Después puede volver a ser privado.
   - El plugin importa de 1 en 1 fila para no superar el límite de 30 s del servidor.
6. **Esmalto → Herramientas**: *Importar fichas técnicas*. (*Aplicar textos ALT* solo hace falta si se suben fotos por otra vía.)
7. **WholesaleX**: los roles Profesional A/B/C ya están creados. En **Esmalto → Ajustes** asígnalos a las categorías A/B/C.
8. **Pagos**: conectar Stripe y añadir el IBAN de la transferencia (WooCommerce → Ajustes → Pagos).
9. Revisar `PENDIENTES.md` y desactivar «Próximamente» (WooCommerce → Ajustes → Visibilidad del sitio).

## Actualizar el tema o el plugin
```
python -I tools/build_dist.py
```
Sube el ZIP nuevo: WordPress pregunta si quieres reemplazar la versión instalada.

## Regenerar el catálogo
```
python -I catalogo/scripts/build_catalog.py --root .
```
Para actualizar productos ya importados, vuelve a importar el CSV marcando «Actualizar productos existentes» (se cruzan por SKU).

## Editar contenidos
- **Páginas**: editor de bloques. Las secciones están en el insertador → *Patrones* → «Esmalto · secciones».
- **Cabecera y pie**: *Apariencia → Editor → Patrones → Partes de plantilla* (Cabecera, Pie).
- **Filtros de la tienda**: *Apariencia → Widgets → WooCommerce Sidebar*.
- **Colores y tipografías**: `wp-content/themes/esmalto/theme.json`.
- **Formularios**: WPForms → Todos los formularios (Contacto, Solicitud de muestras, Solicitud de presupuesto).
