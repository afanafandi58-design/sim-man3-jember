<?php
/**
 * Template Name: Galeri Foto
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$items   = function_exists( 'man3_foto_get_items' ) ? man3_foto_get_items() : array();
$filters = function_exists( 'man3_foto_jenis_filters' ) ? man3_foto_jenis_filters( $items ) : array();
?>
<main id="main" class="site-main page-galeri-foto">
	<section class="section section-org page-hero-org">
		<div class="container">
			<p class="eyebrow"><?php esc_html_e( 'Gallery', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title"><?php esc_html_e( 'Foto', 'man3-hijau-dingin' ); ?></h1>
			<p class="section-lead">
				<?php esc_html_e( 'Dokumentasi kegiatan MAN 3 Jember — filter menurut jenis kegiatan, klik foto untuk memperbesar.', 'man3-hijau-dingin' ); ?>
			</p>
		</div>
	</section>

	<section class="section section-fotos">
		<div class="container">
			<?php if ( ! $items ) : ?>
				<p class="foto-empty">
					<?php esc_html_e( 'Belum ada foto. Admin: buka Galeri Foto di dashboard, unggah gambar unggulan, atau kunjungi ?man3_foto_reseed=1 saat login.', 'man3-hijau-dingin' ); ?>
				</p>
			<?php else : ?>
				<p class="foto-count" data-foto-count>
					<?php
					printf(
						/* translators: %d number of photos */
						esc_html( _n( '%d foto kegiatan', '%d foto kegiatan', count( $items ), 'man3-hijau-dingin' ) ),
						count( $items )
					);
					?>
				</p>

				<?php if ( $filters ) : ?>
					<div class="foto-filters" role="toolbar" aria-label="<?php esc_attr_e( 'Filter jenis kegiatan', 'man3-hijau-dingin' ); ?>">
						<button type="button" class="foto-filter is-active" data-foto-filter="all" aria-pressed="true">
							<?php esc_html_e( 'Semua', 'man3-hijau-dingin' ); ?>
						</button>
						<?php foreach ( $filters as $f ) : ?>
							<button type="button" class="foto-filter" data-foto-filter="<?php echo esc_attr( $f['slug'] ); ?>" aria-pressed="false">
								<?php echo esc_html( $f['name'] ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="foto-grid" data-foto-grid>
					<?php foreach ( $items as $i => $item ) : ?>
						<figure
							class="foto-card"
							data-foto-card
							data-jenis="<?php echo esc_attr( $item['slug'] ); ?>"
						>
							<button
								type="button"
								class="foto-card__media"
								data-foto-open
								data-full="<?php echo esc_url( $item['full'] ); ?>"
								data-title="<?php echo esc_attr( $item['title'] ); ?>"
								data-jenis-label="<?php echo esc_attr( $item['jenis'] ); ?>"
								data-date="<?php echo esc_attr( $item['date'] ? man3_foto_format_date_label( $item['date'] ) : '' ); ?>"
								data-desc="<?php echo esc_attr( $item['desc'] ); ?>"
								aria-label="<?php echo esc_attr( sprintf( /* translators: %s title */ __( 'Perbesar: %s', 'man3-hijau-dingin' ), $item['title'] ) ); ?>"
							>
								<img
									src="<?php echo esc_url( $item['thumb'] ); ?>"
									alt="<?php echo esc_attr( $item['title'] ); ?>"
									loading="<?php echo 0 === $i ? 'eager' : 'lazy'; ?>"
									width="800"
									height="600"
								/>
								<span class="foto-card__zoom" aria-hidden="true"></span>
							</button>
							<figcaption class="foto-card__body">
								<?php if ( $item['jenis'] ) : ?>
									<span class="foto-jenis"><?php echo esc_html( $item['jenis'] ); ?></span>
								<?php endif; ?>
								<?php if ( $item['date'] ) : ?>
									<span class="foto-date"><?php echo esc_html( man3_foto_format_date_label( $item['date'] ) ); ?></span>
								<?php endif; ?>
								<strong class="foto-title"><?php echo esc_html( $item['title'] ); ?></strong>
								<?php if ( $item['desc'] ) : ?>
									<span class="foto-desc"><?php echo esc_html( wp_html_excerpt( $item['desc'], 110, '…' ) ); ?></span>
								<?php endif; ?>
							</figcaption>
						</figure>
					<?php endforeach; ?>
				</div>
				<p class="foto-empty foto-empty--filter" data-foto-empty hidden>
					<?php esc_html_e( 'Tidak ada foto untuk jenis kegiatan ini.', 'man3-hijau-dingin' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</section>
</main>

<dialog class="foto-lightbox" data-foto-lightbox aria-label="<?php esc_attr_e( 'Pratinjau foto', 'man3-hijau-dingin' ); ?>">
	<div class="foto-lightbox__inner">
		<button type="button" class="foto-lightbox__close" data-foto-close aria-label="<?php esc_attr_e( 'Tutup', 'man3-hijau-dingin' ); ?>">×</button>
		<img src="" alt="" data-foto-lightbox-img width="1200" height="800" />
		<div class="foto-lightbox__meta">
			<span class="foto-jenis" data-foto-lightbox-jenis></span>
			<span class="foto-date" data-foto-lightbox-date></span>
			<strong class="foto-title" data-foto-lightbox-title></strong>
			<p class="foto-desc" data-foto-lightbox-desc></p>
		</div>
	</div>
</dialog>

<?php
get_footer();
