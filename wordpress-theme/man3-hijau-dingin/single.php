<?php
/**
 * Single post.
 *
 * @package Man3_Hijau_Dingin
 */

get_header();
?>

<main class="section">
	<div class="container">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<p class="section-kicker"><?php echo esc_html( get_the_date() ); ?></p>
				<h1 class="section-title"><?php the_title(); ?></h1>
				<?php if ( has_post_thumbnail() ) : ?>
					<div style="margin:1.5rem 0 2rem;border-radius:1.25rem;overflow:hidden">
						<?php the_post_thumbnail( 'large' ); ?>
					</div>
				<?php endif; ?>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</div>
</main>

<?php
get_footer();
