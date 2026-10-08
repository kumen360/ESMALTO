# ESMALTO — Web e-commerce B2C + B2B

Tienda de azulejos y cerámica en WordPress + WooCommerce para **esmalto.com**.

| Carpeta | Contenido |
|---|---|
| `docs/` | Brief, catálogo, B2B, despliegue y pendientes |
| `design/` | Diseño original de Claude Design (`ESMALTO.dc.html`) y sus recursos |
| `catalogo/` | Fuente, generador, CSV de importación, fotos (`img/`) y fichas PDF |
| `wp-content/themes/esmalto/` | Tema hijo de Astra: `theme.json`, patrones de bloques y estilos |
| `wp-content/plugins/esmalto-core/` | Calculadora m², B2B (CNAE, categorías, palés), envío por peso y herramientas |
| `dist/` | ZIP instalables del tema y del plugin |

Empieza por [`docs/BRIEF.md`](docs/BRIEF.md). Pendientes de la empresa: [`docs/PENDIENTES.md`](docs/PENDIENTES.md).

## Fases
1. Repositorio, documentación y catálogo normalizado ✅
2. Tema hijo, `theme.json`, patrones y plugin `esmalto-core`
3. Instalación en esmalto.com: plugins, páginas, importación del catálogo, B2B, pagos y envíos
4. Revisión final y documentación
