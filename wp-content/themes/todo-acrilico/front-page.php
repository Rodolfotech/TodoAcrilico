<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$solutions_page  = get_page_by_path( 'nuestras-soluciones' );
$solutions_url   = $solutions_page ? get_permalink( $solutions_page ) : home_url( '/' );
$contacto_page   = get_page_by_path( 'contacto' );
$contacto_url    = $contacto_page ? get_permalink( $contacto_page ) : home_url( '/' );
$hero_image_url  = ta_get_hero_image_url();
?>

<section class="ta-hero<?php echo $hero_image_url ? ' ta-hero--has-image' : ''; ?>" <?php if ( $hero_image_url ) : ?>style="background-image: linear-gradient(90deg, rgba(221,225,224,0.97) 0%, rgba(221,225,224,0.9) 35%, rgba(221,225,224,0.5) 100%), url('<?php echo esc_url( $hero_image_url ); ?>');"<?php endif; ?>>
	<div class="ta-hero-inner">
		<p class="ta-hero-eyebrow">Taller de acrílico · Chile · Desde 2019</p>
		<h1>
			Soluciones en acrílico,
			<span class="ta-hero-title-accent">pensadas para durar.</span>
		</h1>
		<p>Diseñamos y fabricamos piezas para organizar, exhibir,proteger e
		   informar. Cada solución se adapta a tu espacio y a tu marca con la precisión
		   técnica que nos caracteriza.
		</p>
		<div class="ta-hero-actions">
			<?php
			ta_button(
				'Ver catálogo',
				$solutions_url,
				array(
					'variant' => 'dark',
					'icon'    => true,
				)
			);
			ta_button(
				'Cotizar un proyecto',
				$contacto_url,
				array( 'variant' => 'outline' )
			);
			?>
		</div>
		<?php if ( ! $hero_image_url ) : ?>
			<p class="ta-hero-image-hint">Agrega la imagen de fondo desde <strong>Personalizar → Identidad del sitio → Imagen del hero (Inicio)</strong>.</p>
		<?php endif; ?>
	</div>
</section>

<?php
ta_render_feature_grid(
	'Categorías',
	'Cuatro maneras de trabajar el acrílico.',
	array(
		array(
			'icon'        => ta_icon( 'grid' ),
			'title'       => 'Organizar',
			'description' => 'Orden claro, a la vista. <br>
			                  Piezas que ayudan a  <br>
							  ordenar espacios de <br>
							  trabajo, tiendas y <br>
							  eventos.', 
			'url'         => $solutions_url . '#categoria-organizador',
		),
		array(
			'icon'        => ta_icon( 'eye' ),
			'title'       => 'Exhibir',
			'description' => 'Presenta lo que importa. <br>
			                  Soportes y vitrinas que  <br>
							  ponen tus productos u  <br>
							  objetos en primer plano.',
			'url'         => $solutions_url . '#categoria-exhibir',
		),
		array(
			'icon'        => ta_icon( 'shield' ),
			'title'       => 'Proteger',
			'description' => 'Cuida sin ocultar. <br>
			                  Cubiertas y protecciones <br>
							  transparentes para <br>
							  maquetas, obras y equipos.',
			'url'         => $solutions_url . '#categoria-proteger',
		),
		array(
			'icon'        => ta_icon( 'info' ),
			'title'       => 'Informar',
			'description' => 'Comunica con claridad. <br>
			                  Señalética, tótems y <br>
							  soportes con códigos QR <br>
							  para guiar a las personas.',
			'url'         => $solutions_url . '#categoria-informar',
		),
	)
);

ta_render_showcase_grid(
	'Destacados',
	'Piezas más solicitadas.',
	array(
		array(
			'title'       => 'Buzón',
			'description' => 'Buzón transparente para sugerencias, votaciones o sorteos.',
			'image_url'   => 'https://i.postimg.cc/fbkX7YdT/buzon-acrilico-transparente.webp',
		),
		array(
			'title'       => 'Organizador de escritorio',
			'description' => 'Compartimentos abiertos para papelería y accesorios.',
			'image_url'   => 'https://i.postimg.cc/qqcCBwf4/organizador-de-escritorio.webp',
		),
		array(
			'title'       => 'Bandeja modular',
			'description' => 'Bandejas apilables para separar productos o insumos.',
			'image_url'   => 'https://i.postimg.cc/jjGWzmtK/bandeja-modular.webp',
		),
	),
	array(
		'label' => 'Ver todo el catálogo',
		'url'   => $solutions_url,
	)
);

ta_render_story_section(
	array(
		'eyebrow'    => 'Nuestra historia',
		'title'      => 'Más de 20 años trabajando el acrílico a medida.',
		'paragraphs' => array(
			'Nacimos como un taller pequeño con una idea simple: el acrílico bien trabajado ordena, muestra y protege sin estorbar. Hoy seguimos fabricando cada pieza a mano en Santiago, combinando tecnología de corte láser con el acabado manual que solo los años de experiencia permiten.',
			'Trabajamos con marcas, arquitectos, museos y personas que buscan una solución concreta. Si no está en el catálogo, lo diseñamos para ti desde cero, asegurando calidad en todo lo que realizamos.',
		),
		'stats'      => array(
			array(
				'value' => '5k+',
				'label' => 'Proyectos realizados',
			),
			array(
				'value' => '20+',
				'label' => 'Años de experiencia',
			),
		),
		'image_url'  => 'https://i.postimg.cc/t4c7jw1W/equipo-todo-acrilico.webp',
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
