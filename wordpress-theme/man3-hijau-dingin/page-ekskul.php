<?php
/**
 * Template Name: Ekstra Kurikuler
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$filter = isset( $_GET['kat'] ) ? sanitize_key( wp_unslash( $_GET['kat'] ) ) : 'all';
$labels = function_exists( 'man3_ekskul_kategori_labels' ) ? man3_ekskul_kategori_labels() : array();
if ( 'all' !== $filter && ! isset( $labels[ $filter ] ) ) {
	$filter = 'all';
}
$items = function_exists( 'man3_ekskul_get_items' ) ? man3_ekskul_get_items( $filter ) : array();
$all   = function_exists( 'man3_ekskul_get_items' ) ? man3_ekskul_get_items( 'all' ) : array();
$counts = array( 'all' => count( $all ) );
foreach ( array_keys( $labels ) as $k ) {
	$counts[ $k ] = 0;
}
foreach ( $all as $it ) {
	if ( isset( $counts[ $it['kategori'] ] ) ) {
		++$counts[ $it['kategori'] ];
	}
}
$base = get_permalink();
?>
<main id="main" class="site-main page-ekskul">
	<section class="section page-hero-org ekskul-hero">
		<div class="container">
			<p class="eyebrow"><?php esc_html_e( 'Pengembangan diri', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title"><?php esc_html_e( 'Ekstra Kurikuler', 'man3-hijau-dingin' ); ?></h1>
			<p class="section-lead">
				<?php esc_html_e( 'Wadah bakat, karakter, dan prestasi siswa MAN 3 Jember — dari arsip kegiatan resmi madrasah.', 'man3-hijau-dingin' ); ?>
			</p>
			<p class="ekskul-hero-count">
				<?php
				printf(
					/* translators: %d: count */
					esc_html( _n( '%d kegiatan', '%d kegiatan', (int) $counts['all'], 'man3-hijau-dingin' ) ),
					(int) $counts['all']
				);
				?>
			</p>
		</div>
	</section>

	<section class="section section-ekskul">
		<div class="container">
			<div class="ekskul-toolbar" role="tablist" aria-label="<?php esc_attr_e( 'Filter kategori ekskul', 'man3-hijau-dingin' ); ?>">
				<a class="ekskul-filter<?php echo 'all' === $filter ? ' is-active' : ''; ?>" href="<?php echo esc_url( $base ); ?>">
					<?php esc_html_e( 'Semua', 'man3-hijau-dingin' ); ?>
					<span><?php echo esc_html( (string) $counts['all'] ); ?></span>
				</a>
				<?php foreach ( $labels as $key => $label ) : ?>
					<a class="ekskul-filter<?php echo $filter === $key ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'kat', $key, $base ) ); ?>">
						<?php echo esc_html( $label ); ?>
						<span><?php echo esc_html( (string) ( $counts[ $key ] ?? 0 ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>

			<?php if ( ! $items ) : ?>
				<p class="ekskul-empty">
					<?php esc_html_e( 'Belum ada data. Admin: buka menu Ekstra Kurikuler, atau ?man3_ekskul_reseed=1 saat login.', 'man3-hijau-dingin' ); ?>
				</p>
			<?php else : ?>
				<div class="ekskul-grid">
					<?php foreach ( $items as $item ) : ?>
						<article class="ekskul-card" id="ekskul-<?php echo esc_attr( $item['slug'] ); ?>" data-kat="<?php echo esc_attr( $item['kategori'] ); ?>">
							<div class="ekskul-media" aria-hidden="<?php echo $item['thumb'] ? 'false' : 'true'; ?>">
								<?php if ( $item['thumb'] ) : ?>
									<img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="<?php echo esc_attr( $item['nama'] ); ?>" width="640" height="400" loading="lazy" />
								<?php else : ?>
									<span class="ekskul-placeholder"><?php echo esc_html( $item['inisial'] ); ?></span>
								<?php endif; ?>
								<span class="ekskul-badge"><?php echo esc_html( $item['kategori_label'] ); ?></span>
							</div>
							<div class="ekskul-body">
								<h2 class="ekskul-name"><?php echo esc_html( $item['nama'] ); ?></h2>
								<?php if ( ! empty( $item['unit'] ) ) : ?>
									<p class="ekskul-unit"><?php echo esc_html( $item['unit'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $item['pembina'] ) ) : ?>
									<p class="ekskul-pembina">
										<span><?php esc_html_e( 'Pembina', 'man3-hijau-dingin' ); ?></span>
										<?php echo esc_html( $item['pembina'] ); ?>
									</p>
								<?php endif; ?>
								<p class="ekskul-desc"><?php echo esc_html( $item['excerpt'] ); ?></p>
								<details class="ekskul-more">
									<summary><?php esc_html_e( 'Selengkapnya', 'man3-hijau-dingin' ); ?></summary>
									<p><?php echo esc_html( $item['deskripsi'] ); ?></p>
								</details>
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
