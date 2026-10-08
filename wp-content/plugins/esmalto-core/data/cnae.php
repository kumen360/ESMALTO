<?php
/**
 * Lista inicial de CNAE admitidos para el alta profesional.
 * Opción elegida: construcción + proyectos + comercio.
 * Se edita en Esmalto → Ajustes → B2B (un código por línea: «4333 | Descripción»).
 *
 * Incluye CNAE-2009 y las equivalencias CNAE-2025 confirmadas (46.83, 74.13).
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

return array(
	// Construcción de edificios.
	'4110' => 'Promoción inmobiliaria',
	'4121' => 'Construcción de edificios residenciales',
	'4122' => 'Construcción de edificios no residenciales',
	// Instalaciones.
	'4321' => 'Instalaciones eléctricas',
	'4322' => 'Fontanería, instalaciones de sistemas de calefacción y aire acondicionado',
	'4329' => 'Otras instalaciones en obras de construcción',
	// Acabados.
	'4331' => 'Revocamiento',
	'4332' => 'Instalación de carpintería',
	'4333' => 'Revestimiento de suelos y paredes',
	'4334' => 'Pintura y acristalamiento',
	'4339' => 'Otro acabado de edificios',
	// Otras actividades de construcción especializada.
	'4391' => 'Construcción de cubiertas',
	'4399' => 'Otras actividades de construcción especializada n.c.o.p.',
	// Arquitectura, ingeniería y diseño.
	'7111' => 'Servicios técnicos de arquitectura',
	'7112' => 'Servicios técnicos de ingeniería y asesoramiento técnico',
	'7410' => 'Actividades de diseño especializado (CNAE-2009)',
	'7413' => 'Actividades de diseño de interiores (CNAE-2025)',
	// Comercio.
	'4673' => 'Comercio al por mayor de madera, materiales de construcción y aparatos sanitarios (CNAE-2009)',
	'4683' => 'Comercio al por mayor de madera, materiales de construcción y aparatos sanitarios (CNAE-2025)',
	'4674' => 'Comercio al por mayor de ferretería, fontanería y calefacción',
	'4752' => 'Comercio al por menor de ferretería, pintura y vidrio en establecimientos especializados',
	'4753' => 'Comercio al por menor de alfombras, moquetas y revestimientos de paredes y suelos',
	'4759' => 'Comercio al por menor de muebles, iluminación y otros artículos de uso doméstico',
	// Inmobiliarias.
	'6810' => 'Compraventa de bienes inmobiliarios por cuenta propia',
	'6820' => 'Alquiler de bienes inmobiliarios por cuenta propia',
	'6831' => 'Agentes de la propiedad inmobiliaria',
	'6832' => 'Gestión y administración de la propiedad inmobiliaria',
);
