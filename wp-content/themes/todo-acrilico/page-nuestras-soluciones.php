<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$groups = ta_get_solutions_by_category();

if ( have_posts() ) {
	the_post();
}

$page_content = get_the_content();
?>

<div class="ta-container">
	<section class="ta-section">
		<h2><?php the_title(); ?></h2>
		<?php if ( $page_content ) : ?>
			<div class="ta-section-intro entry-content"><?php the_content(); ?></div>
		<?php else : ?>
			<p class="ta-section-intro">Estas son nuestras soluciones en acrílico, organizadas por categoría.</p>
		<?php endif; ?>

		<?php if ( empty( $groups ) ) : ?>
			<div class="ta-empty-state">
				<p>Todavía no hay imágenes publicadas en la galería.</p>
			</div>
		<?php else : ?>
			<?php foreach ( $groups as $group ) : ?>
				<div class="ta-solutions-group" id="categoria-<?php echo esc_attr( $group['slug'] ); ?>">
					<h2><?php echo esc_html( $group['name'] ); ?></h2>
					<div class="ta-solutions-grid">
						<?php foreach ( $group['images'] as $image ) : ?>
							<?php $has_carousel = count( $image['images'] ) > 1; ?>
							<article class="ta-solution-card">
								<div class="ta-solution-figure" <?php echo $has_carousel ? 'data-ta-carousel' : ''; ?>>
									<?php foreach ( $image['images'] as $index => $url ) : ?>
										<img
											src="<?php echo esc_url( $url ); ?>"
											alt="<?php echo esc_attr( $image['title'] ); ?>"
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
										<span class="ta-carousel-counter">1/<?php echo count( $image['images'] ); ?></span>
									<?php endif; ?>
								</div>
								<div class="ta-solution-body">
									<h3><?php echo esc_html( $image['title'] ); ?></h3>
									<?php if ( $image['description'] ) : ?>
										<p><?php echo esc_html( $image['description'] ); ?></p>
									<?php endif; ?>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</section>
</div>

<?php
get_footer();
