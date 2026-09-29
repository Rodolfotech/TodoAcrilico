<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$contacto_page = get_page_by_path( 'contacto' );
$contacto_url  = $contacto_page ? get_permalink( $contacto_page ) : home_url( '/' );

$tabs = array(
	array(
		'label' => 'Todos',
		'value' => '',
	),
);

if ( taxonomy_exists( 'jg_gallery_category' ) ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'jg_gallery_category',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$tabs[] = array(
				'label' => $term->name,
				'value' => $term->slug,
			);
		}
	}
}

$requested_tab = isset( $_GET['categoria'] ) ? sanitize_key( wp_unslash( $_GET['categoria'] ) ) : '';
$allowed_tabs  = wp_list_pluck( $tabs, 'value' );
$active_tab    = in_array( $requested_tab, $allowed_tabs, true ) ? $requested_tab : '';
$page           = isset( $_GET['pagina'] ) ? max( 1, absint( $_GET['pagina'] ) ) : 1;
$query_args     = array(
	'post_type'      => 'jg_gallery_image',
	'post_status'    => 'publish',
	'posts_per_page' => 9,
	'paged'          => $page,
	'orderby'        => 'title',
	'order'          => 'ASC',
);

if ( $active_tab && taxonomy_exists( 'jg_gallery_category' ) ) {
	$query_args['tax_query'] = array(
		array(
			'taxonomy' => 'jg_gallery_category',
			'field'    => 'slug',
			'terms'    => $active_tab,
		),
	);
}

$products = post_type_exists( 'jg_gallery_image' ) ? new WP_Query( $query_args ) : null;
?>

<section class="ta-features ta-features--catalog">
	<div class="ta-container">
		<p class="ta-features-eyebrow">Nuestro Catálogo</p>
		<h2 class="ta-features-title">Encuentra la Pieza que Necesitas</h2>
		<p class="ta-features-description">Explora nuestra colección de piezas y productos fabricados en acrílico.<br>Diseñados para ofrecer durabilidad, precisión y un acabado de alta calidad para<br>cualquier proyecto.</p>

		<?php ta_render_filter_tabs( $tabs, $active_tab, 'categoria' ); ?>

		<?php if ( ! $products || ! $products->have_posts() ) : ?>
			<div class="ta-empty-state">
				<p>Todavía no hay productos publicados en esta categoría.</p>
			</div>
		<?php else : ?>
			<div class="ta-solutions-grid">
				<?php foreach ( $products->posts as $product ) : ?>
					<?php
					$urls         = get_post_meta( $product->ID, '_jg_image_url', false );
					$urls         = is_array( $urls ) ? array_values( array_filter( $urls ) ) : array();
					$has_carousel = count( $urls ) > 1;

					$badge = '';
					if ( taxonomy_exists( 'jg_gallery_category' ) ) {
						$terms = get_the_terms( $product->ID, 'jg_gallery_category' );
						if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
							$badge = $terms[0]->name;
						}
					}

					$usage        = get_post_meta( $product->ID, '_jg_usage', true );
					$measurements = get_post_meta( $product->ID, '_jg_measurements', true );

					$quote_url = add_query_arg(
						array_filter(
							array(
								'imagen'      => ! empty( $urls ) ? $urls[0] : null,
								'pieza'       => get_the_title( $product ) ?: null,
								'descripcion' => $product->post_content ? trim( $product->post_content ) : null,
								'uso'         => $usage ? trim( $usage ) : null,
								'medidas'     => $measurements ? trim( $measurements ) : null,
							)
						),
						$contacto_url
					);
					?>
					<?php if ( ! empty( $urls ) ) : ?>
						<article class="ta-solution-card">
							<div class="ta-solution-figure" <?php echo $has_carousel ? 'data-ta-carousel' : ''; ?>>
								<?php if ( $badge ) : ?>
									<span class="ta-solution-badge"><?php echo esc_html( $badge ); ?></span>
								<?php endif; ?>
								<?php foreach ( $urls as $index => $url ) : ?>
									<img
										src="<?php echo esc_url( $url ); ?>"
										alt="<?php echo esc_attr( get_the_title( $product ) ); ?>"
										loading="lazy"
										class="ta-carousel-slide"
										<?php echo 0 === $index ? '' : 'hidden'; ?>
									>
								<?php endforeach; ?>
								<?php if ( $has_carousel ) : ?>
									<button type="button" class="ta-carousel-nav ta-carousel-nav--prev" aria-label="Imagen anterior">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
									</button>
									<button type="button" class="ta-carousel-nav ta-carousel-nav--next" aria-label="Imagen siguiente">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
									</button>
									<span class="ta-carousel-counter">1/<?php echo count( $urls ); ?></span>
								<?php endif; ?>
							</div>
							<div class="ta-solution-body">
								<h3><?php echo esc_html( get_the_title( $product ) ); ?></h3>
								<?php if ( $product->post_content ) : ?>
									<p><?php echo esc_html( $product->post_content ); ?></p>
									<?php endif; ?>
									<?php $usage = get_post_meta( $product->ID, '_jg_usage', true ); ?>
									<?php if ( $usage ) : ?>
										<p class="ta-solution-usage"><strong>Uso:</strong> <?php echo esc_html( $usage ); ?></p>
									<?php endif; ?>
									<?php $measurements = get_post_meta( $product->ID, '_jg_measurements', true ); ?>
									<?php if ( $measurements ) : ?>
										<p class="ta-solution-measurements"><strong>Medidas:</strong> <?php echo esc_html( $measurements ); ?></p>
									<?php endif; ?>
									<a class="ta-button ta-button--dark ta-solution-quote" href="<?php echo esc_url( $quote_url ); ?>">
										<span class="ta-solution-quote-icon" aria-hidden="true">$</span>
										Cotizar esta pieza
									</a>
							</div>
						</article>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>

			<?php if ( $products->max_num_pages > 1 ) : ?>
				<?php
				$catalog_url = static function ( $page_number ) use ( $active_tab ) {
					$args = array( 'pagina' => $page_number );
					if ( $active_tab ) {
						$args['categoria'] = $active_tab;
					}
					return add_query_arg( $args, get_permalink() );
				};
				?>
				<div class="ta-catalog-pagination">
					<nav class="jg-pagination" aria-label="Paginación del catálogo">
						<p class="jg-pagination-info">Mostrando <?php echo esc_html( ( ( $page - 1 ) * 9 ) + 1 ); ?>-<?php echo esc_html( min( $page * 9, $products->found_posts ) ); ?> de <?php echo esc_html( $products->found_posts ); ?></p>
						<div class="jg-pagination-nav">
							<?php if ( $page > 1 ) : ?>
								<button type="button" class="jg-pagination-btn" onclick="window.location.href='<?php echo esc_js( $catalog_url( $page - 1 ) ); ?>'">‹ Anterior</button>
							<?php else : ?>
								<button type="button" class="jg-pagination-btn" disabled>‹ Anterior</button>
							<?php endif; ?>

							<?php foreach ( ta_get_pagination_pages( $page, $products->max_num_pages ) as $page_number ) : ?>
								<?php if ( '…' === $page_number ) : ?>
									<span class="jg-pagination-ellipsis">…</span>
								<?php else : ?>
									<button type="button" class="jg-pagination-page<?php echo $page === $page_number ? ' is-active' : ''; ?>"<?php echo $page === $page_number ? ' aria-current="page"' : ''; ?> onclick="window.location.href='<?php echo esc_js( $catalog_url( $page_number ) ); ?>'"><?php echo esc_html( $page_number ); ?></button>
								<?php endif; ?>
							<?php endforeach; ?>

							<?php if ( $page < $products->max_num_pages ) : ?>
								<button type="button" class="jg-pagination-btn" onclick="window.location.href='<?php echo esc_js( $catalog_url( $page + 1 ) ); ?>'">Siguiente ›</button>
							<?php else : ?>
								<button type="button" class="jg-pagination-btn" disabled>Siguiente ›</button>
							<?php endif; ?>
						</div>
					</nav>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</section>

<?php
$contacto_page = get_page_by_path( 'contacto' );
$contacto_url  = $contacto_page ? get_permalink( $contacto_page ) : home_url( '/' );

ta_render_cta_section(
	'¿Tienes una idea en mente?',
	'Estamos listos para materializar tu proyecto en acrílico. Desde piezas unitarias hasta grandes producciones.',
	'Hablemos de tu proyecto',
	$contacto_url
);

wp_reset_postdata();
get_footer();
