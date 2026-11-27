<?php
/**
 * Template Name: PMBM
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$posters = function_exists( 'man3_pmbm_get_posters' ) ? man3_pmbm_get_posters() : array();
$tautan  = function_exists( 'man3_pmbm_get_tautan' ) ? man3_pmbm_get_tautan() : array();
$info    = function_exists( 'man3_pmbm_get_informasi' ) ? man3_pmbm_get_informasi() : null;
?>
<main id="main" class="site-main page-pmbm">
	<section class="section page-hero-org pmbm-hero">
		<div class="container">
			<p class="eyebrow"><?php esc_html_e( 'MAN 3 Jember', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title"><?php esc_html_e( 'PMBM', 'man3-hijau-dingin' ); ?></h1>
			<p class="pmbm-lead"><?php esc_html_e( 'Penerimaan Murid Baru Madrasah', 'man3-hijau-dingin' ); ?></p>
		</div>
	</section>

	<?php if ( $posters ) : ?>
		<section class="section section-pmbm-poster" aria-label="<?php esc_attr_e( 'Poster PMBM', 'man3-hijau-dingin' ); ?>">
			<div class="container">
				<div class="pmbm-poster-stack">
					<?php foreach ( $posters as $poster ) : ?>
						<figure class="pmbm-poster">
							<img
								src="<?php echo esc_url( $poster['url'] ); ?>"
								alt="<?php echo esc_attr( $poster['title'] ? $poster['title'] : __( 'Poster PMBM', 'man3-hijau-dingin' ) ); ?>"
								loading="eager"
								decoding="async"
							/>
						</figure>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $tautan ) : ?>
		<section class="section section-pmbm-tautan" aria-labelledby="pmbm-tautan-heading">
			<div class="container">
				<h2 id="pmbm-tautan-heading" class="pmbm-section-title"><?php esc_html_e( 'Tautan', 'man3-hijau-dingin' ); ?></h2>
				<ul class="pmbm-tautan-list">
					<?php foreach ( $tautan as $link ) : ?>
						<li>
							<a class="pmbm-tautan-link" href="<?php echo esc_url( $link['href'] ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( $link['label'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $info ) : ?>
		<section class="section section-pmbm-info" aria-labelledby="pmbm-info-heading">
			<div class="container pmbm-info">
				<?php if ( $info['title'] ) : ?>
					<h2 id="pmbm-info-heading" class="pmbm-section-title"><?php echo esc_html( $info['title'] ); ?></h2>
				<?php else : ?>
					<h2 id="pmbm-info-heading" class="screen-reader-text"><?php esc_html_e( 'Informasi', 'man3-hijau-dingin' ); ?></h2>
				<?php endif; ?>
				<?php if ( trim( wp_strip_all_tags( $info['body'] ) ) ) : ?>
					<div class="pmbm-info-body">
						<?php echo wp_kses_post( $info['body'] ); ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
