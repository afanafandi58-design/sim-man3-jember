<?php
/**
 * Page template.
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
				<p class="section-kicker"><?php esc_html_e( 'Halaman', 'man3-hijau-dingin' ); ?></p>
				<h1 class="section-title"><?php the_title(); ?></h1>
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
