<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$contacto_page = get_page_by_path( 'contacto' );
$contacto_url  = $contacto_page ? get_permalink( $contacto_page ) : home_url( '/' );
?>

<?php
ta_render_feature_grid(
    'Guía de mantenimiento',
    'Cómo cuidar tus piezas',
    array(
        array(
            'icon'        => ta_icon( 'thermometer' ),
            'title'       => 'Evitar el calor',
            'description' => 'No expongas el acrílico a <br>fuentes de calor: se deforma <br>sobre 90°C.',
        ),
        array(
            'icon'        => ta_icon( 'prohibited' ),
            'title'       => 'No uses estos productos',
            'description' => 'Limpiadores para vidrio, <br>alcohol, acetona, bencina, <br>solventes ni productos <br>abrasivos.',
        ),
        array(
            'icon'        => ta_icon( 'spray' ),
            'title'       => 'Limpieza suave',
            'description' => 'Agua tibia con detergente <br>suave (por ejemplo, shampoo <br>de bebé).',
        ),
        array(
            'icon'        => ta_icon( 'circular' ),
            'title'       => '¿Rayones?',
            'description' => 'Aplica cera de pulir y frota <br>suavemente en movimientos <br>circulares.',
        ),
    ),
    'El acrílico es un material noble y duradero que requiere de atenciones específicas para <br> 
	 conservar su transparencia y brillo original por décadas. Sigue estas recomendaciones <br>
	 arquitectónicas para su correcta preservación.'
);

ta_render_story_section(
    array(
        'eyebrow'         => 'Consejo final del experto',
        'title'           => 'El secreto reside en la limpieza.',
        'paragraphs'      => array(
            'Aunque el acrílico sea transparente, no es lo mismo <br>
			 que el vidrio: usa siempre productos diseñados para <br>
			 acrílico.',
            'Evita el uso de cepillos, esponjas abrasivas o <br>
			 estropajos, ya que pueden rayar y dañar <br>
			 permanentemente la superficie.',
        ),
        'stats'           => array(),
        'image_url'       => 'https://i.postimg.cc/Kj93gzYt/cuidados.webp',
        'show_logo_badge' => false,
        'media_position'  => 'right',
        'panel'           => true,
    )
);

ta_render_cta_section(
    '¿Tienes una idea en mente?',
    'Estamos listos para materializar tu proyecto en acrílico. Desde piezas unitarias hasta grandes producciones.',
    'Hablemos de tu proyecto',
    $contacto_url
);
?>

<?php
get_footer();