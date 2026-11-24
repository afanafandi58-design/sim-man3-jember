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
			$is_visi = (bool) is_page( 'visi-misi-man-3-jember' );
			?>
			<article <?php post_class( $is_visi ? 'page-visi-misi' : '' ); ?>>
				<?php if ( ! $is_visi ) : ?>
					<p class="section-kicker"><?php esc_html_e( 'Halaman', 'man3-hijau-dingin' ); ?></p>
				<?php else : ?>
					<p class="section-kicker"><?php esc_html_e( 'Profil', 'man3-hijau-dingin' ); ?></p>
				<?php endif; ?>
				<h1 class="section-title"><?php the_title(); ?></h1>
				<div class="entry-content<?php echo $is_visi ? ' entry-content--visi' : ''; ?>">
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
