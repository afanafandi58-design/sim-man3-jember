<?php
/**
 * Template Name: Agenda
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$items     = function_exists( 'man3_agenda_get_items' ) ? man3_agenda_get_items() : array();
$flyer_url = function_exists( 'man3_agenda_get_flyer_url' ) ? man3_agenda_get_flyer_url() : '';
?>
<main id="main" class="site-main page-agenda">
	<section class="section page-hero-org agenda-hero">
		<div class="container">
			<p class="eyebrow"><?php esc_html_e( 'Info Madrasah', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title"><?php esc_html_e( 'Agenda', 'man3-hijau-dingin' ); ?></h1>
			<p class="agenda-lead">
				<?php esc_html_e( 'Jadwal penting kegiatan madrasah.', 'man3-hijau-dingin' ); ?>
			</p>
		</div>
	</section>

	<?php if ( $flyer_url ) : ?>
		<section class="section section-agenda-flyer" aria-label="<?php esc_attr_e( 'Flyer agenda', 'man3-hijau-dingin' ); ?>">
			<div class="container">
				<figure class="agenda-flyer">
					<img
						src="<?php echo esc_url( $flyer_url ); ?>"
						alt="<?php esc_attr_e( 'Agenda Penting Bulan Desember 2026', 'man3-hijau-dingin' ); ?>"
						width="1217"
						height="1292"
						loading="eager"
						decoding="async"
					/>
				</figure>
			</div>
		</section>
	<?php endif; ?>

	<section class="section section-agenda-list">
		<div class="container">
			<h2 class="agenda-list-heading"><?php esc_html_e( 'Daftar agenda', 'man3-hijau-dingin' ); ?></h2>
			<?php if ( ! $items ) : ?>
				<div class="agenda-empty">
					<p><?php esc_html_e( 'Belum ada agenda aktif. Jadwal akan tampil di sini setelah diunggah dari admin.', 'man3-hijau-dingin' ); ?></p>
				</div>
			<?php else : ?>
				<ol class="agenda-list">
					<?php foreach ( $items as $i => $item ) : ?>
						<li class="agenda-item" id="agenda-<?php echo esc_attr( $item['slug'] ); ?>">
							<article class="agenda-card">
								<span class="agenda-num" aria-hidden="true"><?php echo esc_html( (string) ( $i + 1 ) ); ?></span>
								<div class="agenda-body">
									<?php if ( ! empty( $item['tanggal'] ) ) : ?>
										<p class="agenda-tanggal"><?php echo esc_html( $item['tanggal'] ); ?></p>
									<?php endif; ?>
									<h3 class="agenda-title"><?php echo esc_html( $item['title'] ); ?></h3>
									<?php if ( ! empty( $item['keterangan'] ) ) : ?>
										<p class="agenda-desc"><?php echo esc_html( $item['keterangan'] ); ?></p>
									<?php endif; ?>
								</div>
							</article>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
