<?php
/**
 * Template Name: Zona Integritas (ZI)
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$flyers = function_exists( 'man3_zi_get_flyers' ) ? man3_zi_get_flyers() : array();
$berita = function_exists( 'man3_zi_get_berita' ) ? man3_zi_get_berita() : array();
?>
<main id="main" class="site-main page-zona-integritas">
	<section class="section page-hero-org zi-hero">
		<div class="container">
			<p class="eyebrow"><?php esc_html_e( 'Zona Integritas', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title"><?php esc_html_e( 'ZI', 'man3-hijau-dingin' ); ?></h1>
			<p class="zi-lead"><?php esc_html_e( 'Informasi dan berita khusus Zona Integritas MAN 3 Jember.', 'man3-hijau-dingin' ); ?></p>
		</div>
	</section>

	<section class="section section-zi-flyer" id="zi-flyer" aria-labelledby="zi-flyer-heading">
		<div class="container">
			<h2 id="zi-flyer-heading" class="zi-section-title"><?php esc_html_e( 'Flyer', 'man3-hijau-dingin' ); ?></h2>
			<?php if ( ! $flyers ) : ?>
				<div class="zi-empty">
					<p><?php esc_html_e( 'Belum ada flyer Zona Integritas.', 'man3-hijau-dingin' ); ?></p>
				</div>
			<?php else : ?>
				<div class="zi-flyer-grid">
					<?php foreach ( $flyers as $flyer ) : ?>
						<figure class="zi-flyer-item">
							<a href="<?php echo esc_url( $flyer['full'] ); ?>" target="_blank" rel="noopener noreferrer" class="zi-flyer-link">
								<img
									src="<?php echo esc_url( $flyer['url'] ); ?>"
									alt="<?php echo esc_attr( $flyer['title'] ? $flyer['title'] : __( 'Flyer Zona Integritas', 'man3-hijau-dingin' ) ); ?>"
									loading="lazy"
								/>
							</a>
						</figure>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<section class="section section-zi-berita" id="zi-berita" aria-labelledby="zi-berita-heading">
		<div class="container">
			<h2 id="zi-berita-heading" class="zi-section-title"><?php esc_html_e( 'Berita', 'man3-hijau-dingin' ); ?></h2>
			<?php if ( ! $berita ) : ?>
				<div class="zi-empty">
					<p><?php esc_html_e( 'Belum ada berita Zona Integritas.', 'man3-hijau-dingin' ); ?></p>
				</div>
			<?php else : ?>
				<div class="zi-berita-list news-mantiger-list">
					<?php foreach ( $berita as $item ) : ?>
						<article class="zi-berita-card news-mantiger-card" id="zi-b-<?php echo esc_attr( (string) $item['id'] ); ?>">
							<a class="zi-berita-media news-mantiger-media" href="<?php echo esc_url( $item['url'] ); ?>" aria-hidden="<?php echo $item['thumb'] ? 'false' : 'true'; ?>">
								<?php if ( $item['thumb'] ) : ?>
									<img
										src="<?php echo esc_url( $item['thumb'] ); ?>"
										alt="<?php echo esc_attr( $item['title'] ); ?>"
										width="480"
										height="640"
										loading="lazy"
									/>
								<?php else : ?>
									<span class="news-mantiger-placeholder" aria-hidden="true">ZI</span>
								<?php endif; ?>
							</a>
							<div class="news-mantiger-card-body">
								<time class="news-mantiger-date" datetime="<?php echo esc_attr( $item['date_iso'] ); ?>">
									<?php echo esc_html( $item['date'] ); ?>
								</time>
								<h3 class="news-mantiger-title">
									<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
								</h3>
								<?php if ( $item['excerpt'] ) : ?>
									<p class="news-mantiger-excerpt"><?php echo esc_html( $item['excerpt'] ); ?></p>
								<?php endif; ?>
								<p class="zi-berita-more">
									<a class="btn btn-ghost" href="<?php echo esc_url( $item['url'] ); ?>">
										<?php esc_html_e( 'Baca selengkapnya', 'man3-hijau-dingin' ); ?>
									</a>
								</p>
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
