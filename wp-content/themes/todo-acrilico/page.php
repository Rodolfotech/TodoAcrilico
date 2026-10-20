<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="ta-page-content<?php echo is_page( 'politica-de-privacidad' ) ? ' ta-page-content--wide' : ''; ?>">
		<h1><?php the_title(); ?></h1>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
