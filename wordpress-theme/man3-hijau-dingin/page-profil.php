<?php
/**
 * Template Name: Profil Madrasah
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$data = function_exists( 'man3_profil_get_data' ) ? man3_profil_get_data() : array();
$id   = $data['identitas'] ?? array();
$misi = $data['misi'] ?? array();
$siswa = $data['siswa'] ?? array();
$jurusan = $data['jurusan'] ?? array();
$rombel = $data['rombel'] ?? array();
$pendidik = $data['pendidik'] ?? array();
$prasarana = $data['prasarana'] ?? array();

$guru_count = 0;
$staf_count = 0;
$sert_count = 0;
foreach ( $pendidik as $p ) {
	if ( isset( $p['jabatan'] ) && 0 === strcasecmp( $p['jabatan'], 'Guru' ) ) {
		++$guru_count;
	} else {
		++$staf_count;
	}
	if ( isset( $p['sertifikasi'] ) && 'Ya' === $p['sertifikasi'] ) {
		++$sert_count;
	}
}
$total_siswa = ! empty( $siswa[0]['total'] ) ? (int) $siswa[0]['total'] : 0;
?>
<main id="main" class="site-main page-profil">
	<section class="section page-hero-org profil-hero">
		<div class="container">
			<p class="eyebrow"><?php esc_html_e( 'Beranda', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title"><?php esc_html_e( 'Profil Madrasah', 'man3-hijau-dingin' ); ?></h1>
			<p class="section-lead">
				<?php echo esc_html( $id['nama'] ?? 'MAN 3 Jember' ); ?>
				<?php if ( ! empty( $id['akreditasi'] ) ) : ?>
					— <?php printf( esc_html__( 'Akreditasi %s', 'man3-hijau-dingin' ), esc_html( $id['akreditasi'] ) ); ?>
				<?php endif; ?>
			</p>
			<nav class="profil-jump" aria-label="<?php esc_attr_e( 'Navigasi bagian profil', 'man3-hijau-dingin' ); ?>">
				<a href="#identitas"><?php esc_html_e( 'Identitas', 'man3-hijau-dingin' ); ?></a>
				<a href="#visi-misi"><?php esc_html_e( 'Visi & Misi', 'man3-hijau-dingin' ); ?></a>
				<a href="#siswa"><?php esc_html_e( 'Siswa', 'man3-hijau-dingin' ); ?></a>
				<a href="#jurusan"><?php esc_html_e( 'Jurusan', 'man3-hijau-dingin' ); ?></a>
				<a href="#rombel"><?php esc_html_e( 'Rombel', 'man3-hijau-dingin' ); ?></a>
				<a href="#pendidik"><?php esc_html_e( 'Pendidik', 'man3-hijau-dingin' ); ?></a>
				<a href="#prasarana"><?php esc_html_e( 'Prasarana', 'man3-hijau-dingin' ); ?></a>
			</nav>
		</div>
	</section>

	<section id="identitas" class="section profil-section">
		<div class="container">
			<h2 class="profil-h2"><?php esc_html_e( 'Identitas Madrasah', 'man3-hijau-dingin' ); ?></h2>
			<div class="profil-identity-grid">
				<?php
				$identity_rows = array(
					array( 'label' => 'Nama', 'value' => $id['nama'] ?? '' ),
					array( 'label' => 'NPSN', 'value' => $id['npsn'] ?? '' ),
					array( 'label' => 'NSM', 'value' => $id['nsm'] ?? '' ),
					array( 'label' => 'Akreditasi', 'value' => $id['akreditasi'] ?? '' ),
					array( 'label' => 'Alamat', 'value' => $id['alamat'] ?? '' ),
					array( 'label' => 'Telepon', 'value' => $id['telepon'] ?? '' ),
					array( 'label' => 'Email', 'value' => $id['email'] ?? '' ),
					array( 'label' => 'Website', 'value' => $id['website'] ?? '' ),
				);
				foreach ( $identity_rows as $row ) :
					if ( '' === trim( (string) $row['value'] ) ) {
						continue;
					}
					?>
					<div class="profil-id-item">
						<span class="profil-id-label"><?php echo esc_html( $row['label'] ); ?></span>
						<?php if ( 'Email' === $row['label'] ) : ?>
							<a class="profil-id-value" href="<?php echo esc_url( 'mailto:' . $row['value'] ); ?>"><?php echo esc_html( $row['value'] ); ?></a>
						<?php elseif ( 'Website' === $row['label'] ) : ?>
							<a class="profil-id-value" href="<?php echo esc_url( $row['value'] ); ?>" target="_blank" rel="noreferrer"><?php echo esc_html( $row['value'] ); ?></a>
						<?php else : ?>
							<span class="profil-id-value"><?php echo esc_html( $row['value'] ); ?></span>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section id="visi-misi" class="section profil-section profil-section--tint">
		<div class="container profil-vm">
			<div class="profil-visi">
				<h2 class="profil-h2"><?php esc_html_e( 'Visi', 'man3-hijau-dingin' ); ?></h2>
				<p class="profil-visi-text"><?php echo esc_html( $data['visi'] ?? '' ); ?></p>
			</div>
			<div class="profil-misi">
				<h2 class="profil-h2"><?php esc_html_e( 'Misi', 'man3-hijau-dingin' ); ?></h2>
				<ol class="profil-misi-list">
					<?php foreach ( $misi as $item ) : ?>
						<li><?php echo esc_html( $item ); ?></li>
					<?php endforeach; ?>
				</ol>
			</div>
		</div>
	</section>

	<section id="siswa" class="section profil-section">
		<div class="container">
			<h2 class="profil-h2"><?php esc_html_e( 'Jumlah Siswa', 'man3-hijau-dingin' ); ?></h2>
			<?php if ( $total_siswa ) : ?>
				<p class="profil-stat-highlight">
					<span class="profil-stat-num"><?php echo esc_html( number_format_i18n( $total_siswa ) ); ?></span>
					<span class="profil-stat-label"><?php esc_html_e( 'peserta didik (data tahun terisi)', 'man3-hijau-dingin' ); ?></span>
				</p>
			<?php endif; ?>
			<div class="profil-table-wrap">
				<table class="profil-table">
					<thead>
						<tr>
							<th rowspan="2"><?php esc_html_e( 'Tahun Pelajaran', 'man3-hijau-dingin' ); ?></th>
							<th colspan="2"><?php esc_html_e( 'Kelas 10', 'man3-hijau-dingin' ); ?></th>
							<th colspan="2"><?php esc_html_e( 'Kelas 11', 'man3-hijau-dingin' ); ?></th>
							<th colspan="2"><?php esc_html_e( 'Kelas 12', 'man3-hijau-dingin' ); ?></th>
							<th rowspan="2"><?php esc_html_e( 'Jumlah', 'man3-hijau-dingin' ); ?></th>
						</tr>
						<tr>
							<th>L</th><th>P</th><th>L</th><th>P</th><th>L</th><th>P</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $siswa as $row ) : ?>
							<tr>
								<td><?php echo esc_html( $row['tahun'] ?? '' ); ?></td>
								<td><?php echo esc_html( $row['x_l'] ?? '' ); ?></td>
								<td><?php echo esc_html( $row['x_p'] ?? '' ); ?></td>
								<td><?php echo esc_html( $row['xi_l'] ?? '' ); ?></td>
								<td><?php echo esc_html( $row['xi_p'] ?? '' ); ?></td>
								<td><?php echo esc_html( $row['xii_l'] ?? '' ); ?></td>
								<td><?php echo esc_html( $row['xii_p'] ?? '' ); ?></td>
								<td><strong><?php echo esc_html( $row['total'] ?? '' ); ?></strong></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</section>

	<section id="jurusan" class="section profil-section profil-section--tint">
		<div class="container">
			<h2 class="profil-h2"><?php esc_html_e( 'Data Jurusan', 'man3-hijau-dingin' ); ?></h2>
			<div class="profil-cards">
				<?php foreach ( $jurusan as $j ) : ?>
					<article class="profil-mini-card">
						<h3><?php echo esc_html( $j['nama'] ?? '' ); ?></h3>
						<p><strong><?php echo esc_html( $j['rombel'] ?? '' ); ?></strong> <?php esc_html_e( 'rombel', 'man3-hijau-dingin' ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section id="rombel" class="section profil-section">
		<div class="container">
			<h2 class="profil-h2"><?php esc_html_e( 'Data Rombel', 'man3-hijau-dingin' ); ?></h2>
			<div class="profil-cards profil-cards--3">
				<?php foreach ( $rombel as $r ) : ?>
					<article class="profil-mini-card">
						<h3><?php printf( esc_html__( 'Kelas %s', 'man3-hijau-dingin' ), esc_html( $r['kelas'] ?? '' ) ); ?></h3>
						<p><strong><?php echo esc_html( $r['jumlah'] ?? '' ); ?></strong> <?php esc_html_e( 'rombel', 'man3-hijau-dingin' ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
			<?php if ( ! empty( $data['rombel_total'] ) ) : ?>
				<p class="profil-total-note"><?php printf( esc_html__( 'Total rombel: %s', 'man3-hijau-dingin' ), esc_html( $data['rombel_total'] ) ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<section id="pendidik" class="section profil-section profil-section--tint">
		<div class="container">
			<h2 class="profil-h2"><?php esc_html_e( 'Pendidik dan Tenaga Kependidikan', 'man3-hijau-dingin' ); ?></h2>
			<div class="profil-cards profil-cards--3">
				<article class="profil-mini-card">
					<h3><?php esc_html_e( 'Guru', 'man3-hijau-dingin' ); ?></h3>
					<p><strong><?php echo esc_html( (string) $guru_count ); ?></strong></p>
				</article>
				<article class="profil-mini-card">
					<h3><?php esc_html_e( 'Staf / Tendik', 'man3-hijau-dingin' ); ?></h3>
					<p><strong><?php echo esc_html( (string) $staf_count ); ?></strong></p>
				</article>
				<article class="profil-mini-card">
					<h3><?php esc_html_e( 'Tersertifikasi', 'man3-hijau-dingin' ); ?></h3>
					<p><strong><?php echo esc_html( (string) $sert_count ); ?></strong></p>
				</article>
			</div>
			<div class="profil-table-wrap profil-table-wrap--scroll">
				<table class="profil-table profil-table--compact">
					<thead>
						<tr>
							<th>#</th>
							<th><?php esc_html_e( 'Nama', 'man3-hijau-dingin' ); ?></th>
							<th><?php esc_html_e( 'Jabatan', 'man3-hijau-dingin' ); ?></th>
							<th><?php esc_html_e( 'Mapel', 'man3-hijau-dingin' ); ?></th>
							<th><?php esc_html_e( 'Pendidikan', 'man3-hijau-dingin' ); ?></th>
							<th><?php esc_html_e( 'Sertifikasi', 'man3-hijau-dingin' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $pendidik as $i => $p ) : ?>
							<tr>
								<td><?php echo esc_html( (string) ( $i + 1 ) ); ?></td>
								<td><?php echo esc_html( $p['nama'] ?? '' ); ?></td>
								<td><?php echo esc_html( $p['jabatan'] ?? '' ); ?></td>
								<td><?php echo esc_html( $p['mapel'] ?? '—' ); ?></td>
								<td><?php echo esc_html( trim( ( $p['pendidikan'] ?? '' ) . ' ' . ( $p['jurusan'] ?? '' ) ) ); ?></td>
								<td><?php echo esc_html( $p['sertifikasi'] ?? '' ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</section>

	<section id="prasarana" class="section profil-section">
		<div class="container">
			<h2 class="profil-h2"><?php esc_html_e( 'Data Prasarana', 'man3-hijau-dingin' ); ?></h2>
			<div class="profil-table-wrap profil-table-wrap--scroll">
				<table class="profil-table profil-table--compact">
					<thead>
						<tr>
							<th>#</th>
							<th><?php esc_html_e( 'Nama', 'man3-hijau-dingin' ); ?></th>
							<th><?php esc_html_e( 'Jumlah', 'man3-hijau-dingin' ); ?></th>
							<th><?php esc_html_e( 'Ukuran (m)', 'man3-hijau-dingin' ); ?></th>
							<th><?php esc_html_e( 'Kondisi', 'man3-hijau-dingin' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $prasarana as $i => $pr ) : ?>
							<tr>
								<td><?php echo esc_html( (string) ( $i + 1 ) ); ?></td>
								<td><?php echo esc_html( $pr['nama'] ?? '' ); ?></td>
								<td><?php echo esc_html( $pr['jumlah'] ?? '' ); ?></td>
								<td><?php echo esc_html( trim( ( $pr['panjang'] ?? '' ) . ' × ' . ( $pr['lebar'] ?? '' ), ' ×' ) ); ?></td>
								<td><?php echo esc_html( $pr['kondisi'] ?? '' ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
