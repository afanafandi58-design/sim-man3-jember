<?php
/**
 * Single Berita ZI.
 *
 * @package Man3_Hijau_Dingin
 */

get_header();
?>
<main id="main" class="site-main page-zona-integritas single-zi-berita">
	<section class="section">
		<div class="container zi-single">
			<p class="zi-back">
				<a href="<?php echo esc_url( home_url( '/zi/' ) ); ?>">← <?php esc_html_e( 'Kembali ke ZI', 'man3-hijau-dingin' ); ?></a>
			</p>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'zi-single-article' ); ?>>
					<p class="eyebrow"><?php esc_html_e( 'Berita ZI', 'man3-hijau-dingin' ); ?></p>
					<h1 class="section-title"><?php the_title(); ?></h1>
					<p class="zi-single-date">
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					</p>
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="zi-single-media">
							<?php the_post_thumbnail( 'large' ); ?>
						</figure>
					<?php endif; ?>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				</article>
				<?php
			endwhile;
			?>
		</div>
	</section>
</main>
<?php
get_footer();
