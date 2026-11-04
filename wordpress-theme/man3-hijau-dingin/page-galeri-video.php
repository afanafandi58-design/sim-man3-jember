<?php
/**
 * Template Name: Galeri Video
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$accounts = function_exists( 'man3_video_official_accounts' ) ? man3_video_official_accounts() : array();
$items    = function_exists( 'man3_video_get_items' ) ? man3_video_get_items() : array();
$yt_count = 0;
$ig_count = 0;
foreach ( $items as $it ) {
	if ( 'instagram' === $it['source'] ) {
		++$ig_count;
	} else {
		++$yt_count;
	}
}
?>
<main id="main" class="site-main page-galeri-video">
	<section class="section section-org page-hero-org">
		<div class="container">
			<p class="eyebrow"><?php esc_html_e( 'Galeri', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title"><?php esc_html_e( 'Video', 'man3-hijau-dingin' ); ?></h1>
			<p class="section-lead">
				<?php esc_html_e( 'Kumpulan video resmi MAN 3 Jember dari YouTube dan Instagram (enam bulan terakhir).', 'man3-hijau-dingin' ); ?>
			</p>
			<?php if ( $accounts ) : ?>
				<ul class="video-accounts">
					<?php if ( ! empty( $accounts['youtube'] ) ) : ?>
						<li>
							<a href="<?php echo esc_url( $accounts['youtube']['url'] ); ?>" target="_blank" rel="noreferrer">
								<?php echo esc_html( $accounts['youtube']['label'] . ' (' . $accounts['youtube']['handle'] . ')' ); ?>
							</a>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $accounts['instagram'] ) ) : ?>
						<li>
							<a href="<?php echo esc_url( $accounts['instagram']['url'] ); ?>" target="_blank" rel="noreferrer">
								<?php echo esc_html( $accounts['instagram']['label'] ); ?>
							</a>
						</li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
		</div>
	</section>

	<section class="section section-videos">
		<div class="container">
			<?php if ( ! $items ) : ?>
				<p class="video-empty">
					<?php esc_html_e( 'Belum ada video. Admin: buka Galeri Video di dashboard, atau kunjungi ?man3_video_reseed=1 saat login.', 'man3-hijau-dingin' ); ?>
				</p>
			<?php else : ?>
				<p class="video-count">
					<?php
					printf(
						/* translators: 1: youtube count 2: instagram count */
						esc_html__( '%1$d video YouTube · %2$d Instagram', 'man3-hijau-dingin' ),
						(int) $yt_count,
						(int) $ig_count
					);
					?>
				</p>
				<div class="video-grid">
					<?php foreach ( $items as $item ) : ?>
						<article class="video-card" data-source="<?php echo esc_attr( $item['source'] ); ?>">
							<div class="video-media">
								<?php if ( 'youtube' === $item['source'] && $item['yt_id'] ) : ?>
									<iframe
										src="<?php echo esc_url( 'https://www.youtube.com/embed/' . $item['yt_id'] ); ?>"
										title="<?php echo esc_attr( $item['title'] ); ?>"
										loading="lazy"
										allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
										allowfullscreen
										referrerpolicy="strict-origin-when-cross-origin"
									></iframe>
								<?php elseif ( 'instagram' === $item['source'] && $item['url'] ) : ?>
									<a class="video-thumb-link" href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noreferrer">
										<?php if ( $item['thumb'] ) : ?>
											<img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy" width="640" height="360" />
										<?php else : ?>
											<span class="video-thumb-fallback" aria-hidden="true">IG</span>
										<?php endif; ?>
										<span class="video-play-badge"><?php esc_html_e( 'Tonton di Instagram', 'man3-hijau-dingin' ); ?></span>
									</a>
								<?php elseif ( $item['thumb'] ) : ?>
									<a class="video-thumb-link" href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noreferrer">
										<img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy" width="640" height="360" />
									</a>
								<?php endif; ?>
							</div>
							<div class="video-body">
								<span class="video-badge video-badge--<?php echo esc_attr( $item['source'] ); ?>">
									<?php echo esc_html( 'instagram' === $item['source'] ? 'Instagram' : 'YouTube' ); ?>
								</span>
								<?php if ( ! empty( $item['date'] ) ) : ?>
									<time class="video-date" datetime="<?php echo esc_attr( $item['date'] ); ?>">
										<?php echo esc_html( $item['date'] ); ?>
									</time>
								<?php endif; ?>
								<h2 class="video-title">
									<a href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noreferrer">
										<?php echo esc_html( $item['title'] ); ?>
									</a>
								</h2>
								<?php if ( ! empty( $item['summary'] ) ) : ?>
									<p class="video-summary"><?php echo esc_html( $item['summary'] ); ?></p>
								<?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>

				<?php if ( ! empty( $accounts['instagram'] ) && 0 === $ig_count ) : ?>
					<aside class="video-ig-cta">
						<h2><?php esc_html_e( 'Reels Instagram resmi', 'man3-hijau-dingin' ); ?></h2>
						<p>
							<?php
							esc_html_e(
								'Feed Reels Instagram tidak tersedia lewat API publik tanpa token. Ikuti akun resmi, atau admin dapat menambah tautan Reel di menu Galeri Video.',
								'man3-hijau-dingin'
							);
							?>
						</p>
						<a class="btn btn-primary" href="<?php echo esc_url( $accounts['instagram']['url'] ); ?>" target="_blank" rel="noreferrer">
							<?php echo esc_html( $accounts['instagram']['handle'] ); ?>
						</a>
					</aside>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
