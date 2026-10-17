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
2. Tema hijo, `theme.json`, patrones y plugin `esmalto-core` ✅ (probados en WordPress Playground: importación, B2B, palés, envíos, alta con CNAE)
3. Instalación en esmalto.com ✅ — tema y plugin, páginas, 33 colecciones (673 fotos con ALT y 32 fichas PDF), roles B2B A/B/C, envíos y transferencia. Revisión página a página contra el diseño (escritorio y móvil) y bloques validados en el editor.
4. Web publicada ✅ (9-10-2026, «Próximamente» desactivado). Lo que falta está en [`docs/PENDIENTES.md`](docs/PENDIENTES.md): Stripe, IBAN, textos legales y tarifa.
5. Repaso del 10/10 (errores, textos, tienda lista para vender, SEO con Yoast y categorías con página propia): [`docs/REPASO-2026-10-10.md`](docs/REPASO-2026-10-10.md) ✅ — tema 1.2.0 y plugin 1.3.0 en esmalto.com desde el 17/10. Lo que falta está en su apartado «No posible todavía».
