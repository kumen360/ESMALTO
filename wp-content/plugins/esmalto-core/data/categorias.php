<?php
/**
 * Categorías del catálogo con página propia (repaso del 10/10, Anexo II).
 * slug (= URL en la raíz: /slug/) => nombre, grupo, título SEO, texto (va encima de los productos)
 * y la propuesta de colecciones. La asignación definitiva se edita y aplica en Esmalto → Herramientas.
 *
 * Reglas: en «Exterior» solo colecciones cuya ficha indica resbaladicidad de clase 3. El mínimo para
 * publicar una categoría es 1 colección y «Piscina» está confirmada (decisión del 17/10/2026).
 * Una definición con 'oculta' => true no se publica hasta marcar la casilla de confirmación.
 *
 * @package Esmalto_Core
 */

defined( 'ABSPATH' ) || exit;

return array(
	// Aspecto.
	'porcelanico-imitacion-madera' => array(
		'nombre'    => 'Madera',
		'grupo'     => 'Aspecto',
		'titulo'    => 'Porcelánico imitación madera | Esmalto',
		'texto'     => 'La calidez de la madera sin su mantenimiento. Lamas porcelánicas con veta y relieve, que no se hinchan con el agua ni piden barniz. Sirven igual para un salón que para un baño.',
		'propuesta' => array( 'Borneo', 'Borneo Deck', 'Clayton', 'Fika', 'Harper', 'Helsinki', 'Ikon', 'Kootenai', 'Loire', 'Niara', 'Nivala', 'Norway', 'Walkyria' ),
	),
	'porcelanico-imitacion-marmol' => array(
		'nombre'    => 'Mármol',
		'grupo'     => 'Aspecto',
		'titulo'    => 'Porcelánico imitación mármol | Esmalto',
		'texto'     => 'El dibujo del mármol en una pieza que no se mancha ni hay que pulir. Calacatta, crema marfil y otras vetas, en brillo o en mate y en formatos grandes para que la junta casi no se vea.',
		'propuesta' => array( 'Colosso', 'Crema Marfil', 'Imperial Calacatta', 'Pinoso', 'Siena', 'Valentino' ),
	),
	'porcelanico-efecto-piedra'    => array(
		'nombre'    => 'Piedra',
		'grupo'     => 'Aspecto',
		'titulo'    => 'Porcelánico efecto piedra | Esmalto',
		'texto'     => 'Superficies con el tacto y los matices de la piedra natural, con variación de tono entre piezas para que el suelo no parezca repetido. Para interior y, según la colección, también para exterior.',
		'propuesta' => array( 'Ambrossia', 'Arenisca', 'Coralina', 'Southwell Cross', 'Southwell Vein', 'Tivoli Cross', 'Tivoli Vein', 'Vermont' ),
	),
	'porcelanico-efecto-cemento'   => array(
		'nombre'    => 'Cemento',
		'grupo'     => 'Aspecto',
		'titulo'    => 'Porcelánico efecto cemento | Esmalto',
		'texto'     => 'El aspecto continuo del cemento pulido, sin fisuras ni tratamientos. Tonos neutros que encajan en viviendas, locales y oficinas, con varios formatos por colección para combinar suelo y pared.',
		'propuesta' => array( 'Besana', 'Capitol', 'San Francisco' ),
	),
	'porcelanico-liso'             => array(
		'nombre'    => 'Liso',
		'grupo'     => 'Aspecto',
		'titulo'    => 'Porcelánico liso y monocolor | Esmalto',
		'texto'     => 'Piezas de color uniforme para quien quiere que el protagonismo lo tengan el espacio y el mobiliario. Fáciles de combinar y de reponer.',
		'propuesta' => array( 'Alaska', 'Manley' ),
	),
	// Espacio.
	'azulejos-bano'                => array(
		'nombre'    => 'Baño',
		'grupo'     => 'Espacio',
		'titulo'    => 'Azulejos y porcelánico para baño | Esmalto',
		'texto'     => 'Suelos y revestimientos para baño que aguantan agua y vapor a diario. Puedes llevar la misma colección de suelo a pared o combinar un liso con un relieve en la zona de ducha.',
		'propuesta' => array( 'Alaska', 'Ambrossia', 'Arenisca', 'Besana', 'Borneo', 'Borneo Deck', 'Capitol', 'Clayton', 'Colosso', 'Coralina', 'Crema Marfil', 'Fika', 'Harper', 'Helsinki', 'Ikon', 'Imperial Calacatta', 'Kootenai', 'Loire', 'Manley', 'Niara', 'Nivala', 'Norway', 'Pinoso', 'San Francisco', 'Siena', 'Southwell Cross', 'Southwell Vein', 'Tivoli Cross', 'Tivoli Vein', 'Valentino', 'Vermont', 'Walkyria' ),
	),
	'azulejos-cocina'              => array(
		'nombre'    => 'Cocina',
		'grupo'     => 'Espacio',
		'titulo'    => 'Azulejos y porcelánico para cocina | Esmalto',
		'texto'     => 'Superficies que se limpian con un paño y no absorben grasa ni manchas. Formatos grandes para el suelo y para un frente de cocina con pocas juntas.',
		'propuesta' => array( 'Alaska', 'Ambrossia', 'Arenisca', 'Besana', 'Borneo', 'Borneo Deck', 'Capitol', 'Clayton', 'Coralina', 'Crema Marfil', 'Fika', 'Harper', 'Helsinki', 'Ikon', 'Imperial Calacatta', 'Kootenai', 'Loire', 'Manley', 'Niara', 'Nivala', 'Norway', 'Pinoso', 'San Francisco', 'Siena', 'Southwell Cross', 'Southwell Vein', 'Tivoli Cross', 'Tivoli Vein', 'Valentino', 'Vermont', 'Walkyria' ),
	),
	'suelos-salon'                 => array(
		'nombre'    => 'Salón',
		'grupo'     => 'Espacio',
		'titulo'    => 'Suelos porcelánicos para salón | Esmalto',
		'texto'     => 'El suelo es lo que más se ve de un salón. Maderas, piedras y cementos en formatos amplios, que dan continuidad a la vivienda y funcionan con suelo radiante.',
		'propuesta' => array( 'Alaska', 'Ambrossia', 'Arenisca', 'Besana', 'Borneo', 'Borneo Deck', 'Capitol', 'Clayton', 'Colosso', 'Coralina', 'Crema Marfil', 'Fika', 'Harper', 'Helsinki', 'Ikon', 'Imperial Calacatta', 'Kootenai', 'Loire', 'Manley', 'Niara', 'Nivala', 'Norway', 'Pinoso', 'San Francisco', 'Siena', 'Southwell Cross', 'Southwell Vein', 'Tivoli Cross', 'Tivoli Vein', 'Valentino', 'Vermont', 'Walkyria' ),
	),
	'suelos-dormitorio'            => array(
		'nombre'    => 'Dormitorio',
		'grupo'     => 'Espacio',
		'titulo'    => 'Suelos porcelánicos para dormitorio | Esmalto',
		'texto'     => 'Tonos cálidos y acabados mates para una estancia tranquila. Las lamas imitación madera son una opción habitual.',
		'propuesta' => array( 'Ambrossia', 'Besana', 'Borneo', 'Borneo Deck', 'Capitol', 'Clayton', 'Fika', 'Harper', 'Helsinki', 'Ikon', 'Imperial Calacatta', 'Kootenai', 'Loire', 'Niara', 'Nivala', 'Norway', 'Siena', 'Southwell Cross', 'Southwell Vein', 'Vermont', 'Walkyria' ),
	),
	'porcelanico-exterior'         => array(
		'nombre'    => 'Exterior',
		'grupo'     => 'Espacio',
		'titulo'    => 'Porcelánico para exterior y terraza | Esmalto',
		'texto'     => 'Colecciones aptas para terraza, porche y zonas de paso al aire libre, que resisten sol, lluvia y heladas. Consulta en cada ficha la versión antideslizante.',
		// Clase 3 en la ficha técnica (en varias, solo la versión antideslizante de algunos formatos).
		'propuesta' => array( 'Ambrossia', 'Arenisca', 'Borneo Deck', 'Capitol', 'Fika', 'Helsinki', 'Kootenai', 'Southwell Cross', 'Walkyria' ),
	),
	'porcelanico-piscina'          => array(
		'nombre'    => 'Piscina',
		'grupo'     => 'Espacio',
		'titulo'    => 'Porcelánico para piscinas | Esmalto',
		'texto'     => 'Pavimento antideslizante de clase 3 para el entorno de la piscina: playa, solárium y ducha exterior. Resiste el agua, el cloro y el sol sin perder color.',
		'propuesta' => array( 'Southwell Cross' ),
	),
	// Formato.
	'porcelanico-gran-formato'     => array(
		'nombre'    => 'Gran formato',
		'grupo'     => 'Formato',
		'titulo'    => 'Porcelánico de gran formato | Esmalto',
		'texto'     => 'Piezas de 100x100 y 120x120 cm para superficies con muy pocas juntas. Agrandan visualmente el espacio y son habituales en proyectos de arquitectura e interiorismo.',
		'propuesta' => array( 'Ambrossia', 'Besana', 'Capitol', 'Imperial Calacatta', 'Pinoso', 'Siena', 'Southwell Cross', 'Southwell Vein', 'Tivoli Cross', 'Tivoli Vein', 'Vermont' ),
	),
	'lamas-porcelanicas'           => array(
		'nombre'    => 'Lamas',
		'grupo'     => 'Formato',
		'titulo'    => 'Lamas porcelánicas imitación madera | Esmalto',
		'texto'     => 'Lamas largas, de 120 a 180 cm, que se colocan como una tarima. Disponibles en 20x120, 23x120, 25x150 y 30x180.',
		'propuesta' => array( 'Borneo', 'Borneo Deck', 'Clayton', 'Fika', 'Harper', 'Helsinki', 'Ikon', 'Kootenai', 'Niara', 'Nivala', 'Norway', 'Walkyria' ),
	),
	'porcelanico-60x120'           => array(
		'nombre'    => '60x120',
		'grupo'     => 'Formato',
		'titulo'    => 'Porcelánico 60x120 | Esmalto',
		// «Es también el que más colecciones reúne…»: lo confirma el catálogo (22 de 32 colecciones).
		'texto'     => 'El formato más versátil: sirve para suelo y para pared, en vivienda y en local. Es también el que más colecciones reúne en nuestro catálogo.',
		'propuesta' => array( 'Alaska', 'Ambrossia', 'Arenisca', 'Besana', 'Capitol', 'Colosso', 'Coralina', 'Crema Marfil', 'Helsinki', 'Imperial Calacatta', 'Loire', 'Manley', 'Norway', 'Pinoso', 'San Francisco', 'Siena', 'Southwell Cross', 'Southwell Vein', 'Tivoli Cross', 'Tivoli Vein', 'Valentino', 'Vermont' ),
	),
	'porcelanico-60x60'            => array(
		'nombre'    => '60x60',
		'grupo'     => 'Formato',
		'titulo'    => 'Porcelánico 60x60 | Esmalto',
		'texto'     => 'Un clásico que funciona en cualquier estancia y se coloca con poca merma. Buena elección para reformas y para superficies medianas.',
		'propuesta' => array( 'Alaska', 'Arenisca', 'Besana', 'Capitol', 'Coralina', 'Manley', 'San Francisco', 'Siena', 'Southwell Cross', 'Southwell Vein', 'Vermont' ),
	),
);
