<?php
/**
 * Template Name: Data Pegawai (Guru & Staff)
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$filter = isset( $_GET['jenis'] ) ? sanitize_key( wp_unslash( $_GET['jenis'] ) ) : 'all';
if ( ! in_array( $filter, array( 'all', 'pendidik', 'tendik' ), true ) ) {
	$filter = 'all';
}
$items = function_exists( 'man3_pegawai_get_items' ) ? man3_pegawai_get_items( $filter ) : array();
$all   = function_exists( 'man3_pegawai_get_items' ) ? man3_pegawai_get_items( 'all' ) : array();
$n_p   = 0;
$n_t   = 0;
foreach ( $all as $it ) {
	if ( 'tendik' === $it['jenis'] ) {
		++$n_t;
	} else {
		++$n_p;
	}
}
$base = get_permalink();
?>
<main id="main" class="site-main page-data-pegawai">
	<section class="section page-hero-org">
		<div class="container">
			<p class="eyebrow"><?php esc_html_e( 'Pendidik dan Kependidikan', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title"><?php esc_html_e( 'Guru & Staff', 'man3-hijau-dingin' ); ?></h1>
			<p class="section-lead">
				<?php esc_html_e( 'Data pegawai MAN 3 Jember — nama, jabatan, dan pendidikan terakhir. Foto dapat dilengkapi lewat admin.', 'man3-hijau-dingin' ); ?>
			</p>
		</div>
	</section>

	<section class="section section-pegawai">
		<div class="container">
			<div class="pegawai-toolbar" role="tablist" aria-label="<?php esc_attr_e( 'Filter jenis pegawai', 'man3-hijau-dingin' ); ?>">
				<a class="pegawai-filter<?php echo 'all' === $filter ? ' is-active' : ''; ?>" href="<?php echo esc_url( $base ); ?>"><?php esc_html_e( 'Semua', 'man3-hijau-dingin' ); ?> <span><?php echo esc_html( (string) ( $n_p + $n_t ) ); ?></span></a>
				<a class="pegawai-filter<?php echo 'pendidik' === $filter ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'jenis', 'pendidik', $base ) ); ?>"><?php esc_html_e( 'Pendidik', 'man3-hijau-dingin' ); ?> <span><?php echo esc_html( (string) $n_p ); ?></span></a>
				<a class="pegawai-filter<?php echo 'tendik' === $filter ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'jenis', 'tendik', $base ) ); ?>"><?php esc_html_e( 'Tenaga Kependidikan', 'man3-hijau-dingin' ); ?> <span><?php echo esc_html( (string) $n_t ); ?></span></a>
			</div>

			<?php if ( ! $items ) : ?>
				<p class="pegawai-empty"><?php esc_html_e( 'Belum ada data. Admin: buka Data Pegawai atau ?man3_pegawai_reseed=1 saat login.', 'man3-hijau-dingin' ); ?></p>
			<?php else : ?>
				<div class="pegawai-grid">
					<?php foreach ( $items as $item ) : ?>
						<article class="pegawai-card" data-jenis="<?php echo esc_attr( $item['jenis'] ); ?>">
							<div class="pegawai-photo" aria-hidden="true">
								<?php if ( ! empty( $item['thumb'] ) ) : ?>
									<img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="" width="96" height="96" loading="lazy" />
								<?php else : ?>
									<span class="pegawai-initials"><?php echo esc_html( $item['inisial'] ); ?></span>
								<?php endif; ?>
							</div>
							<div class="pegawai-body">
								<span class="pegawai-badge pegawai-badge--<?php echo esc_attr( $item['jenis'] ); ?>">
									<?php echo esc_html( 'tendik' === $item['jenis'] ? 'Tendik' : 'Pendidik' ); ?>
								</span>
								<h2 class="pegawai-name"><?php echo esc_html( $item['nama'] ); ?></h2>
								<p class="pegawai-role"><?php echo esc_html( $item['jabatan'] ? $item['jabatan'] : '—' ); ?></p>
								<p class="pegawai-edu"><?php echo esc_html( $item['pendidikan'] ? $item['pendidikan'] : '—' ); ?></p>
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
