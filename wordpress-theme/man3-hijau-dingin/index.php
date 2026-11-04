<?php
/**
 * Fallback index (daftar tulisan).
 *
 * @package Man3_Hijau_Dingin
 */

get_header();
?>

<main class="section">
	<div class="container">
		<div class="section-head">
			<p class="section-kicker"><?php esc_html_e( 'Arsip', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title">
				<?php
				if ( is_home() && ! is_front_page() ) {
					esc_html_e( 'Berita', 'man3-hijau-dingin' );
				} elseif ( is_search() ) {
					printf( /* translators: %s search query */ esc_html__( 'Hasil pencarian: %s', 'man3-hijau-dingin' ), esc_html( get_search_query() ) );
				} elseif ( is_archive() ) {
					the_archive_title();
				} else {
					bloginfo( 'name' );
				}
				?>
			</h1>
		</div>

		<div class="berita-list">
			<?php if ( have_posts() ) : ?>
				<?php
				while ( have_posts() ) :
					the_post();
					$thumb = get_the_post_thumbnail_url( get_the_ID(), 'medium_large' );
					if ( ! $thumb ) {
						$thumb = man3_hijau_asset( 'images/berita-istisqa.jpg' );
					}
					?>
					<a class="berita-item" href="<?php the_permalink(); ?>">
						<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy" />
						<div class="berita-body">
							<p class="berita-date"><?php echo esc_html( get_the_date() ); ?></p>
							<h3><?php the_title(); ?></h3>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
							<span class="more"><?php esc_html_e( 'Baca selengkapnya →', 'man3-hijau-dingin' ); ?></span>
						</div>
					</a>
				<?php endwhile; ?>
			<?php else : ?>
				<p><?php esc_html_e( 'Belum ada konten.', 'man3-hijau-dingin' ); ?></p>
			<?php endif; ?>
		</div>

		<div style="margin-top:2rem">
			<?php the_posts_pagination(); ?>
		</div>
	</div>
</main>

<?php
get_footer();
