# Brief — Web e-commerce ESMALTO (B2C + B2B)

Tienda online de azulejos y cerámica para particulares (B2C) y profesionales (B2B) en **https://www.esmalto.com**.

## Stack
WordPress 7.1 · Astra (gratis) + tema hijo `esmalto` · Gutenberg (bloques nativos, sin page builders ni HTML a medida) · WooCommerce · WholesaleX (free) · WPForms Lite · WooCommerce Stripe Gateway · plugin propio `esmalto-core` (lógica que los bloques no pueden hacer).

## Requisitos
| Área | Requisito |
|---|---|
| Diseño | Fiel a `design/ESMALTO.dc.html` (Claude Design). Colores, tipografías y espaciados en `theme.json`. Secciones repetibles como patrones de bloques. Todo editable desde el editor. |
| Catálogo | 33 colecciones / 335 variaciones con todas las fotos (ambiente + pieza) y datos técnicos. Filtros: estilo, color (tono), precio, interior/exterior, espacio, formato, uso, antideslizante. |
| Calculadora m² | `cajas = ⌈m² / m² por caja⌉`; muestra cajas, m² reales y coste total. B2B: precio de su categoría y opción de palés. |
| B2B | Alta con empresa, email, CNAE y teléfono. CNAE validado contra lista. Cuenta pendiente hasta aprobación manual. Categorías A/B/C = 20/30/40 % (ajustables). Historial de pedidos y botón de contacto comercial. |
| Pagos | Stripe (tarjeta) y transferencia bancaria. |
| Entrega | A domicilio (por peso, tramos editables; palé para pedidos grandes) o recogida en almacén (gratis). Baleares/Canarias/Ceuta/Melilla: a consultar. |
| Entrega de trabajo | Push a GitHub al final de cada fase. Documentación breve en `docs/`. |

## Decisiones cerradas (08/10/2026)
1. Se construye **directamente en producción** (esmalto.com), con «Próximamente» de WooCommerce activo hasta el lanzamiento.
2. El catálogo **no trae precios** → se importa sin precio y con botón «Solicitar presupuesto». La tarifa se carga después por SKU (herramienta en *Esmalto → Herramientas*).
3. Solo las **33 colecciones del CSV** (AGRA, ALBERI y BLANCO del diseño eran de muestra).
4. Solo se muestra la marca **Esmalto** (el fabricante queda como dato interno `_fabricante`).
5. Las imágenes se suben **desde este repositorio** (URLs raw de GitHub) → el repo debe ser público durante la importación.
6. CNAE válidos: **construcción + proyectos + comercio** (lista editable en *Esmalto → B2B*).
7. Envío **por peso** con tramos editables; palé completo para pedidos grandes; recogida en almacén gratis.
8. Hero de portada **estático** (bloque Portada).
9. Datos de empresa y textos legales: **placeholders del diseño** (ver `PENDIENTES.md`).
10. Palés B2B: **redondeo a palés completos + % de descuento extra** ajustable.
