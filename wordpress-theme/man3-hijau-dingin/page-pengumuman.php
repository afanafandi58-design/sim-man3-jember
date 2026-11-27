<?php
/**
 * Template Name: Pengumuman
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$items = function_exists( 'man3_pengumuman_get_items' ) ? man3_pengumuman_get_items() : array();
?>
<main id="main" class="site-main page-pengumuman">
	<section class="section page-hero-org pengumuman-hero">
		<div class="container">
			<p class="eyebrow"><?php esc_html_e( 'Info Madrasah', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title"><?php esc_html_e( 'Pengumuman', 'man3-hijau-dingin' ); ?></h1>
			<p class="pengumuman-lead">
				<?php esc_html_e( 'Informasi dan pengumuman resmi MAN 3 Jember.', 'man3-hijau-dingin' ); ?>
			</p>
			<?php if ( $items ) : ?>
				<p class="pengumuman-count">
					<?php
					printf(
						/* translators: %d: count */
						esc_html( _n( '%d pengumuman', '%d pengumuman', count( $items ), 'man3-hijau-dingin' ) ),
						count( $items )
					);
					?>
				</p>
			<?php endif; ?>
		</div>
	</section>

	<section class="section section-pengumuman">
		<div class="container">
			<?php if ( ! $items ) : ?>
				<div class="pengumuman-empty">
					<p><?php esc_html_e( 'Belum ada pengumuman aktif. Arsip akan tampil di sini setelah diunggah dari admin.', 'man3-hijau-dingin' ); ?></p>
				</div>
			<?php else : ?>
				<ul class="pengumuman-list">
					<?php foreach ( $items as $item ) : ?>
						<li class="pengumuman-item" id="pengumuman-<?php echo esc_attr( $item['slug'] ); ?>">
							<article class="pengumuman-card<?php echo empty( $item['thumb'] ) ? ' pengumuman-card--no-media' : ''; ?>">
								<?php if ( ! empty( $item['thumb'] ) ) : ?>
									<div class="pengumuman-media">
										<img
											src="<?php echo esc_url( $item['thumb'] ); ?>"
											alt=""
											width="320"
											height="200"
											loading="lazy"
										/>
									</div>
								<?php endif; ?>
								<div class="pengumuman-body">
									<time class="pengumuman-date" datetime="<?php echo esc_attr( $item['date_iso'] ); ?>">
										<?php echo esc_html( $item['date'] ); ?>
									</time>
									<h2 class="pengumuman-title"><?php echo esc_html( $item['title'] ); ?></h2>
									<?php if ( ! empty( $item['excerpt'] ) ) : ?>
										<p class="pengumuman-excerpt"><?php echo esc_html( $item['excerpt'] ); ?></p>
									<?php endif; ?>
									<?php if ( ! empty( $item['content'] ) && $item['content'] !== $item['excerpt'] ) : ?>
										<details class="pengumuman-more">
											<summary><?php esc_html_e( 'Baca selengkapnya', 'man3-hijau-dingin' ); ?></summary>
											<div class="pengumuman-full"><?php echo wp_kses_post( wpautop( get_post_field( 'post_content', $item['id'] ) ) ); ?></div>
										</details>
									<?php endif; ?>
									<?php if ( ! empty( $item['file'] ) ) : ?>
										<p class="pengumuman-file">
											<a class="pengumuman-file-link" href="<?php echo esc_url( $item['file'] ); ?>" target="_blank" rel="noopener noreferrer">
												<?php esc_html_e( 'Unduh berkas', 'man3-hijau-dingin' ); ?>
											</a>
										</p>
									<?php endif; ?>
								</div>
							</article>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
