# Área profesional (B2B)

## Flujo de alta
1. El profesional rellena **Profesionales → Alta profesional**. Los campos son nombre, empresa, CIF/NIF, **CNAE**, tipo, teléfono, email y contraseña.
2. El CNAE se valida en el servidor contra la lista admitida (Esmalto → Ajustes) y admite `4333` o `43.33`. El formulario muestra la descripción en vivo.
3. La cuenta se crea en WooCommerce con estado **pendiente** de WholesaleX (`__wholesalex_status = pending`). No puede iniciar sesión hasta que se apruebe.
4. Llegan dos emails: un aviso al equipo, con los datos y un enlace para aprobar, y un acuse al solicitante.
5. **Aprobar**: en *WholesaleX → Customers*, cambia el estado a *Active* y asigna el rol **Profesional A, B o C**.

## Categorías y descuentos
| Categoría | Rol WholesaleX | Descuento inicial |
|---|---|---|
| A | Profesional A | 20 % |
| B | Profesional B | 30 % |
| C | Profesional C | 40 % |

- Los porcentajes y la correspondencia con los roles se cambian en **Esmalto → Ajustes**.
- El descuento se aplica sobre la tarifa (PVP) en toda la tienda, la cesta y la calculadora. Los profesionales ven los precios **sin IVA**.
- **No configures descuentos en «Wholesale Pricing» de WholesaleX**: se sumarían a los de Esmalto.

## Palés
- En la ficha, la calculadora ofrece a los profesionales «Pedir por palés completos»: redondea a palés enteros con las cajas por palé de cada referencia.
- En la cesta, las cajas que completan palés reciben un **descuento adicional** (5 % por defecto, ajustable). Aparece como línea «Descuento palé completo».
- Las referencias sin datos de palé (ver `PENDIENTES.md`) no muestran la opción.

## Mi cuenta (profesional)
- *Pedidos*: historial de WooCommerce.
- *Escritorio* y *Contacto comercial*: panel con la categoría y los botones de email, teléfono y WhatsApp del comercial (Esmalto → Ajustes → Contacto comercial), más un formulario de contacto.

## Lista de CNAE admitidos (opción elegida: construcción + proyectos + comercio)
41.10 · 41.21 · 41.22 · 43.21 · 43.22 · 43.29 · 43.31 · 43.32 · 43.33 · 43.34 · 43.39 · 43.91 · 43.99 · 71.11 · 71.12 · 74.10 · 74.13 (CNAE-2025) · 46.73 · 46.83 (CNAE-2025) · 46.74 · 47.52 · 47.53 · 47.59 · 68.10 · 68.20 · 68.31 · 68.32

**Otras opciones** (se cambian en Ajustes, un código por línea):
- *Solo oficios de obra*: 41.21, 41.22, 43.31–43.39, 43.99.
- *Amplia*: toda la sección F (41–43), 71, 74.1, 46.73/46.74, 47.52, 68.
- Desde 2025 rige la **CNAE-2025**. Si un profesional da un código nuevo que no está en la lista, añádelo con su descripción.
