# Área profesional (B2B)

## Flujo de alta
1. El profesional rellena **Profesionales → Alta profesional**. Los campos son nombre, empresa, CIF/NIF, **CNAE**, tipo, teléfono y email (sin contraseña, como en el diseño).
2. El CNAE se valida en el servidor contra la lista admitida (Esmalto → Ajustes) y admite `4333` o `43.33`. El formulario muestra la descripción en vivo.
3. La cuenta se crea en WooCommerce con estado **pendiente** de WholesaleX (`__wholesalex_status = pending`). No puede iniciar sesión hasta que se apruebe.
4. Llegan dos emails: un aviso al equipo, con los datos y un enlace para aprobar, y un acuse al solicitante.
5. **Aprobar**: en *WholesaleX → Customers*, cambia el estado a *Active* y asigna el rol **Profesional A, B o C**.
6. Al pasar a *Active*, el profesional recibe un email (una sola vez) con el enlace para **crear su contraseña**. Después entra en *Mi cuenta* con su email.

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

## Lista de CNAE admitidos (definitiva, CNAE-2025)
46 actividades. Fuente: [`Esmalto_Lista_CNAE-2025.csv`](Esmalto_Lista_CNAE-2025.csv) (código, descripción, grupo, estado y CNAE-2009 al que sustituye). La lista activa se edita en **Esmalto → Ajustes**, un código por línea (`4333 | Descripción`; la columna «Línea para Ajustes» del CSV ya tiene ese formato).

| Grupo | CNAE-2025 |
|---|---|
| Fabricación afín | 23.70 Corte, tallado y acabado de la piedra |
| Construcción de edificios | 41.01 Construcción de edificios residenciales · 41.02 Construcción de edificios no residenciales |
| Ingeniería civil | 42.11 Construcción de carreteras y autopistas · 42.12 Construcción de vías férreas de superficie y subterráneas · 42.13 Construcción de puentes y túneles · 42.21 Construcción de redes para fluidos · 42.22 Construcción de redes eléctricas y de telecomunicaciones · 42.91 Obras hidráulicas · 42.99 Construcción de otros proyectos de ingeniería civil n.c.o.p. |
| Construcción especializada | 43.11 Demolición · 43.12 Preparación de terrenos · 43.13 Perforaciones y sondeos · 43.41 Construcción de cubiertas · 43.42 Otras actividades de construcción especializada en la construcción de edificios · 43.50 Actividades de construcción especializada en ingeniería civil · 43.60 Actividades de intermediación para servicios de construcción especializada · 43.91 Actividades de mampostería y albañilería · 43.99 Otras actividades de construcción especializada n.c.o.p. |
| Instalaciones | 43.21 Instalaciones eléctricas · 43.22 Fontanería, instalación de sistemas de calefacción y aire acondicionado · 43.23 Instalación de aislamientos · 43.24 Otras instalaciones en obras de construcción |
| Acabados | 43.31 Revocamiento · 43.32 Instalación de carpintería · 43.33 Revestimiento de suelos y paredes · 43.34 Pintura y acristalamiento · 43.35 Otros acabados de edificios |
| Comercio | 46.13 Intermediarios del comercio de madera y materiales de construcción · 46.15 Intermediarios del comercio de muebles, artículos para el hogar y ferretería · 46.47 Comercio al por mayor de muebles para hogar y oficinas, alfombras y aparatos de iluminación · 46.83 Comercio al por mayor de madera, materiales de construcción y aparatos sanitarios · 46.84 Comercio al por mayor de equipos y suministros de ferretería, fontanería y calefacción · 47.52 Comercio al por menor de ferretería, materiales de construcción, pinturas y vidrio · 47.53 Comercio al por menor de alfombras, moquetas y revestimientos de paredes y suelos · 47.55 Comercio al por menor de muebles, aparatos de iluminación, vajilla y otros artículos de uso doméstico |
| Inmobiliarias | 68.11 Compraventa de bienes inmobiliarios por cuenta propia · 68.12 Promoción inmobiliaria · 68.20 Alquiler de bienes inmobiliarios por cuenta propia · 68.31 Servicios de intermediación para actividades inmobiliarias · 68.32 Otras actividades inmobiliarias por cuenta de terceros |
| Arquitectura, ingeniería y diseño | 71.11 Servicios técnicos de arquitectura · 71.12 Servicios técnicos de ingeniería y otras actividades relacionadas con el asesoramiento técnico · 74.13 Actividades de diseño de interiores |
| Servicios a edificios | 81.10 Servicios integrales a edificios e instalaciones · 81.30 Actividades de jardinería |

| CNAE-2009 (ya no se admite) | Usar en su lugar |
|---|---|
| 41.10 | 68.12 |
| 41.21 | 41.01 |
| 41.22 | 41.02 |
| 43.29 | 43.23 o 43.24 |
| 43.39 | 43.35 |
| 46.73 | 46.83 |
| 46.74 | 46.84 |
| 47.59 | 47.55 |
| 68.10 | 68.11 |
| 74.10 | 74.13 |

- Solo se admiten códigos CNAE-2025. Si alguien escribe un código antiguo de la segunda tabla, el formulario lo rechaza: indícale el nuevo.
- El 43.91 sigue en la lista, pero en el CNAE-2025 es «Mampostería y albañilería»; las cubiertas pasan al 43.41.
