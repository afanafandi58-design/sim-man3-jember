<?php
/**
 * Template Name: News Mantiger
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$items = function_exists( 'man3_news_mantiger_get_items' ) ? man3_news_mantiger_get_items() : array();
$ig    = 'https://www.instagram.com/mantiger.official/';
?>
<main id="main" class="site-main page-news-mantiger">
	<section class="section page-hero-org news-mantiger-hero">
		<div class="container">
			<p class="eyebrow"><?php esc_html_e( 'Kanal berita siswa', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title"><?php esc_html_e( 'News Mantiger', 'man3-hijau-dingin' ); ?></h1>
			<p class="section-lead">
				<?php esc_html_e( 'Liputan dan kabar warga MAN 3 Jember (Mantiger). Dikelola dari dashboard — Tambah Baru di menu News Mantiger.', 'man3-hijau-dingin' ); ?>
			</p>
			<?php if ( $items ) : ?>
				<p class="news-mantiger-count">
					<?php
					printf(
						/* translators: %d: count */
						esc_html( _n( '%d berita', '%d berita', count( $items ), 'man3-hijau-dingin' ) ),
						count( $items )
					);
					?>
				</p>
			<?php endif; ?>
		</div>
	</section>

	<section class="section section-news-mantiger">
		<div class="container">
			<?php if ( ! $items ) : ?>
				<div class="news-mantiger-empty">
					<p><?php esc_html_e( 'Belum ada berita News Mantiger.', 'man3-hijau-dingin' ); ?></p>
					<p class="news-mantiger-empty-hint">
						<?php esc_html_e( 'Admin: buka menu News Mantiger di dashboard, lalu klik Tambah Baru. Isi judul, konten, tanggal, dan Gambar Unggulan, kemudian Publish.', 'man3-hijau-dingin' ); ?>
					</p>
					<p class="news-mantiger-actions">
						<a class="btn btn-ghost" href="<?php echo esc_url( $ig ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Instagram @mantiger.official', 'man3-hijau-dingin' ); ?>
						</a>
					</p>
				</div>
			<?php else : ?>
				<div class="news-mantiger-list">
					<?php foreach ( $items as $item ) : ?>
						<article class="news-mantiger-card" id="nm-<?php echo esc_attr( (string) $item['id'] ); ?>">
							<div class="news-mantiger-media" aria-hidden="<?php echo $item['thumb'] ? 'false' : 'true'; ?>">
								<?php if ( $item['thumb'] ) : ?>
									<img
										src="<?php echo esc_url( $item['thumb'] ); ?>"
										alt="<?php echo esc_attr( $item['title'] ); ?>"
										width="720"
										height="450"
										loading="lazy"
									/>
								<?php else : ?>
									<span class="news-mantiger-placeholder" aria-hidden="true">NM</span>
								<?php endif; ?>
							</div>
							<div class="news-mantiger-card-body">
								<time class="news-mantiger-date" datetime="<?php echo esc_attr( $item['date_iso'] ); ?>">
									<?php echo esc_html( $item['date'] ); ?>
								</time>
								<h2 class="news-mantiger-title"><?php echo esc_html( $item['title'] ); ?></h2>
								<?php if ( $item['excerpt'] ) : ?>
									<p class="news-mantiger-excerpt"><?php echo esc_html( $item['excerpt'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $item['content'] ) && trim( wp_strip_all_tags( $item['content'] ) ) !== trim( $item['excerpt'] ) ) : ?>
									<details class="news-mantiger-more">
										<summary><?php esc_html_e( 'Baca selengkapnya', 'man3-hijau-dingin' ); ?></summary>
										<div class="news-mantiger-full entry-content">
											<?php echo wp_kses_post( $item['content'] ); ?>
										</div>
									</details>
								<?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
