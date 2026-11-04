<?php
/**
 * Template Name: PPID
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$items = function_exists( 'man3_ppid_get_items' ) ? man3_ppid_get_items() : array();
?>
<main id="main" class="site-main page-ppid">
	<section class="section page-hero-org ppid-hero">
		<div class="container">
			<p class="eyebrow"><?php esc_html_e( 'Informasi publik', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title"><?php esc_html_e( 'PPID', 'man3-hijau-dingin' ); ?></h1>
			<p class="section-lead">
				<?php esc_html_e( 'Pejabat Pengelola Informasi dan Dokumentasi MAN 3 Jember. Konten dikelola dari dashboard — menu PPID.', 'man3-hijau-dingin' ); ?>
			</p>
			<?php if ( $items ) : ?>
				<p class="ppid-count">
					<?php
					printf(
						/* translators: %d: count */
						esc_html( _n( '%d informasi', '%d informasi', count( $items ), 'man3-hijau-dingin' ) ),
						count( $items )
					);
					?>
				</p>
			<?php endif; ?>
		</div>
	</section>

	<section class="section section-ppid">
		<div class="container">
			<?php if ( ! $items ) : ?>
				<div class="ppid-empty">
					<p><?php esc_html_e( 'Belum ada informasi PPID yang dipublikasikan.', 'man3-hijau-dingin' ); ?></p>
					<p class="ppid-empty-hint">
						<?php esc_html_e( 'Admin: buka menu PPID di dashboard, lalu klik Tambah Baru. Isi judul, konten, gambar (opsional), dan lampiran dokumen (opsional), kemudian Publish.', 'man3-hijau-dingin' ); ?>
					</p>
				</div>
			<?php else : ?>
				<div class="ppid-list">
					<?php foreach ( $items as $item ) : ?>
						<article class="ppid-card" id="ppid-<?php echo esc_attr( (string) $item['id'] ); ?>">
							<?php if ( $item['thumb'] ) : ?>
								<div class="ppid-media">
									<img
										src="<?php echo esc_url( $item['thumb'] ); ?>"
										alt="<?php echo esc_attr( $item['title'] ); ?>"
										width="720"
										height="450"
										loading="lazy"
									/>
								</div>
							<?php endif; ?>
							<div class="ppid-card-body">
								<time class="ppid-date" datetime="<?php echo esc_attr( $item['date_iso'] ); ?>">
									<?php echo esc_html( $item['date'] ); ?>
								</time>
								<h2 class="ppid-title"><?php echo esc_html( $item['title'] ); ?></h2>
								<?php if ( $item['excerpt'] ) : ?>
									<p class="ppid-excerpt"><?php echo esc_html( $item['excerpt'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $item['content'] ) && trim( wp_strip_all_tags( $item['content'] ) ) !== trim( $item['excerpt'] ) ) : ?>
									<details class="ppid-more">
										<summary><?php esc_html_e( 'Baca selengkapnya', 'man3-hijau-dingin' ); ?></summary>
										<div class="ppid-full entry-content">
											<?php echo wp_kses_post( $item['content'] ); ?>
										</div>
									</details>
								<?php endif; ?>
								<?php if ( ! empty( $item['file_url'] ) ) : ?>
									<p class="ppid-file">
										<a class="btn btn-outline" href="<?php echo esc_url( $item['file_url'] ); ?>" target="_blank" rel="noopener noreferrer">
											<?php
											printf(
												/* translators: %s: filename */
												esc_html__( 'Unduh: %s', 'man3-hijau-dingin' ),
												esc_html( $item['file_name'] ? $item['file_name'] : __( 'dokumen', 'man3-hijau-dingin' ) )
											);
											?>
										</a>
									</p>
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
