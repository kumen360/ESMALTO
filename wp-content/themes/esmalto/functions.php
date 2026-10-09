<?php
/**
 * Esmalto — tema hijo de Astra.
 *
 * - Diseño (colores, tipografías, espaciados): theme.json
 * - Secciones reutilizables: patterns/ (categoría «Esmalto» en el insertador)
 * - Cabecera y pie: parts/header.html y parts/footer.html (Apariencia → Partes de plantilla)
 * - La lógica de tienda (calculadora, B2B, envíos) vive en el plugin esmalto-core.
 *
 * @package Esmalto
 */

defined( 'ABSPATH' ) || exit;

define( 'ESMALTO_THEME_VERSION', '1.1.1' );

require_once __DIR__ . '/inc/setup.php';
require_once __DIR__ . '/inc/astra.php';
require_once __DIR__ . '/inc/woocommerce.php';
require_once __DIR__ . '/inc/formularios.php';
