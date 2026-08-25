<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="ta-page-content">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
				<div class="entry-content"><?php the_excerpt(); ?></div>
			</article>
			<?php
		endwhile;
		?>
	<?php else : ?>
		<p>No hay contenido para mostrar.</p>
	<?php endif; ?>
</div>

<?php
get_footer();
