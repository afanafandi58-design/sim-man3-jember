<?php
/**
 * Template Name: Fasilitas
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$items = function_exists( 'man3_fasilitas_get_items' ) ? man3_fasilitas_get_items() : array();
?>
<main id="main" class="site-main page-fasilitas">
	<section class="section page-hero-org fasilitas-hero">
		<div class="container">
			<p class="eyebrow"><?php esc_html_e( 'Info Madrasah', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title"><?php esc_html_e( 'Fasilitas', 'man3-hijau-dingin' ); ?></h1>
			<p class="fasilitas-lead">
				<?php esc_html_e( 'Sarana dan prasarana MAN 3 Jember yang mendukung pembelajaran dan kegiatan madrasah.', 'man3-hijau-dingin' ); ?>
			</p>
			<?php if ( $items ) : ?>
				<p class="fasilitas-count">
					<?php
					printf(
						/* translators: %d: count */
						esc_html( _n( '%d fasilitas', '%d fasilitas', count( $items ), 'man3-hijau-dingin' ) ),
						count( $items )
					);
					?>
				</p>
			<?php endif; ?>
		</div>
	</section>

	<section class="section section-fasilitas">
		<div class="container">
			<?php if ( ! $items ) : ?>
				<div class="fasilitas-empty">
					<p><?php esc_html_e( 'Belum ada data fasilitas.', 'man3-hijau-dingin' ); ?></p>
				</div>
			<?php else : ?>
				<div class="fasilitas-grid">
					<?php foreach ( $items as $item ) : ?>
						<article class="fasilitas-card" id="fasilitas-<?php echo esc_attr( $item['slug'] ); ?>">
							<div class="fasilitas-media" aria-hidden="<?php echo $item['thumb'] ? 'false' : 'true'; ?>">
								<?php if ( $item['thumb'] ) : ?>
									<img
										src="<?php echo esc_url( $item['thumb'] ); ?>"
										alt="<?php echo esc_attr( $item['nama'] ); ?>"
										width="640"
										height="400"
										loading="lazy"
									/>
								<?php else : ?>
									<span class="fasilitas-placeholder" aria-hidden="true"><?php echo esc_html( mb_substr( $item['nama'], 0, 1 ) ); ?></span>
								<?php endif; ?>
							</div>
							<div class="fasilitas-body">
								<h2 class="fasilitas-name"><?php echo esc_html( $item['nama'] ); ?></h2>
								<?php if ( $item['excerpt'] ) : ?>
									<p class="fasilitas-desc"><?php echo esc_html( $item['excerpt'] ); ?></p>
								<?php endif; ?>
								<?php if ( $item['deskripsi'] && $item['deskripsi'] !== $item['excerpt'] ) : ?>
									<details class="fasilitas-more">
										<summary><?php esc_html_e( 'Selengkapnya', 'man3-hijau-dingin' ); ?></summary>
										<p><?php echo esc_html( $item['deskripsi'] ); ?></p>
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
