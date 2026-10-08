# Catálogo

## Flujo
```
catalogo/fuente/Esmalto_woocommerce_import.csv ─┐
catalogo/fuente/Esmalto_woocommerce_import.xlsx ├─ catalogo/scripts/build_catalog.py ─► catalogo/salida/esmalto-productos.csv
SALIDA_WEBP/ (fotos y fichas originales)        ┘                                     ├► catalogo/img/…            (fotos renombradas)
                                                                                      ├► catalogo/fichas/*.pdf      (fichas técnicas)
                                                                                      ├► catalogo/salida/imagenes-alt.json
                                                                                      └► catalogo/salida/informe.md (incidencias)
```
Regenerar: `python -I catalogo/scripts/build_catalog.py --root .`

## Qué corrige el generador respecto al CSV original
- **Separador de valores**: el original usaba ` | `; el importador de WooCommerce espera comas. Sin este cambio cada color/formato se habría creado como un único valor.
- **Atributo «Modelo»** eliminado de las variaciones: WooCommerce lo habría convertido en un desplegable de una sola opción. El modelo queda en `_modelo`.
- **Marca**: se elimina «Ecoceramic» de textos, etiquetas, nombres de archivo y ALT (solo queda en el meta interno `_fabricante`).
- **Imágenes**: renombradas a `esmalto-*.webp`, en minúsculas y sin espacios, y deduplicadas por contenido (709 referencias → 673 archivos).
- **Normalización**: formatos `60x120`, espesores `9 mm`/`20 mm`, decimales con punto (`51.84`), plurales («4 colores»).
- **Categorías**: `Pavimentos y revestimientos > {Familia}` (sin una categoría por colección). Etiquetas eliminadas (eran ruido).
- **Descargas**: las columnas *Download* se eliminan porque el importador las rechaza en productos variables. Las fichas pasan a PDF y las importa `esmalto-core`.

## Atributos y filtros
| Atributo | Tipo | Uso |
|---|---|---|
| Color · Formato · Tipo de pieza · Espesor · Acabado | global, **variación** | selector en ficha + filtro |
| Estilo | global | filtro (Madera, Mármol, Piedra, Cemento, Liso) |
| Tono | global, oculto en ficha | filtro de color unificado (Blanco, Beige, Gris, Negro, Marrón, Verde) |
| Espacio | global | filtro / «Compra por espacio» (Baño, Cocina, Salón, Dormitorio, Terraza, Fachada, Piscina) |
| Ubicación | global | filtro Interior / Exterior |
| Uso | global | Pavimento / Revestimiento |
| Antideslizante · Familia · Material | global | filtro / especificaciones |
| Rectificado · Destonificación · Versión antideslizante | local | especificaciones |

El estilo y el tono se asignan con tablas en `build_catalog.py` (`ESTILO`, `TONO`): edítalas y regenera.

## Datos de embalaje (meta de cada variación)
`_m2_por_caja`, `_kg_por_caja`, `_piezas_por_caja`, `_cajas_por_pallet`, `_m2_por_pallet`, `_kg_por_pallet`. Los usa la calculadora. El peso de la variación es el peso de una caja.

## Precio
- La unidad de venta es la **caja**: el *precio normal* de WooCommerce es €/caja y la web muestra también €/m².
- El origen no trae precios. Para cargar una tarifa pega `SKU;€/m²` en *Esmalto → Herramientas → Cargar tarifa*: calcula el €/caja con el m²/caja de cada variación.

## Destacados y borradores
- Destacados en portada: Ambrossia, Imperial Calacatta, Colosso y Harper (`DESTACADOS`).
- Lucca se importa como **borrador**: no tiene fotos ni ficha.

Incidencias completas: [`catalogo/salida/informe.md`](../catalogo/salida/informe.md).
