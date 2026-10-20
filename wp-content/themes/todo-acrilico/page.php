<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="ta-page-content<?php echo ta_is_legal_page() ? ' ta-page-content--wide' : ''; ?>">
		<h1><?php the_title(); ?></h1>
		<div class="entry-content">
			<?php
			if ( ta_is_legal_page() ) {
				echo ta_render_legal_accordion( apply_filters( 'the_content', get_the_content() ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				the_content();
			}
			?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
