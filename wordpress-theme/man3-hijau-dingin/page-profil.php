<?php
/**
 * Template Name: Profil Madrasah
 * Profil publik: narasi umum + grafik agregat (tanpa nama/NIP).
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$data    = function_exists( 'man3_profil_get_data' ) ? man3_profil_get_data() : array();
$id      = $data['identitas'] ?? array();
$misi    = $data['misi'] ?? array();
$siswa   = $data['siswa_chart'] ?? array();
$pegawai = $data['pegawai_chart'] ?? array();
$prasarana = $data['prasarana_chart'] ?? array();
$program = $data['program'] ?? array();
$timeline = $data['timeline'] ?? array();

$chart_payload = array(
	'siswaGender'    => $siswa['gender'] ?? array(),
	'siswaTingkat'   => $siswa['tingkat'] ?? array(),
	'siswaPeminatan' => $siswa['peminatan'] ?? array(),
	'pegawaiJenis'   => $pegawai['jenis'] ?? array(),
	'pegawaiStatus'  => $pegawai['status'] ?? array(),
	'pegawaiDetail'  => $pegawai['status_detail'] ?? array(),
	'prasaranaBars'  => $prasarana['bars'] ?? array(),
);
?>
<main id="main" class="site-main page-profil" data-profil-charts="<?php echo esc_attr( wp_json_encode( $chart_payload ) ); ?>">
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
				<a href="#sejarah"><?php esc_html_e( 'Sejarah', 'man3-hijau-dingin' ); ?></a>
				<a href="#identitas"><?php esc_html_e( 'Identitas', 'man3-hijau-dingin' ); ?></a>
				<a href="#visi-misi"><?php esc_html_e( 'Visi & Misi', 'man3-hijau-dingin' ); ?></a>
				<a href="#program"><?php esc_html_e( 'Program', 'man3-hijau-dingin' ); ?></a>
				<a href="#siswa"><?php esc_html_e( 'Siswa', 'man3-hijau-dingin' ); ?></a>
				<a href="#pendidik"><?php esc_html_e( 'Kepegawaian', 'man3-hijau-dingin' ); ?></a>
				<a href="#prasarana"><?php esc_html_e( 'Prasarana', 'man3-hijau-dingin' ); ?></a>
			</nav>
		</div>
	</section>

	<section id="sejarah" class="section profil-section">
		<div class="container">
			<h2 class="profil-h2"><?php esc_html_e( 'Sejarah Singkat', 'man3-hijau-dingin' ); ?></h2>
			<p class="profil-prose"><?php echo esc_html( $data['sejarah'] ?? '' ); ?></p>
			<?php if ( $timeline ) : ?>
				<ol class="profil-timeline">
					<?php foreach ( $timeline as $row ) : ?>
						<li>
							<span class="profil-timeline-year"><?php echo esc_html( $row['tahun'] ?? '' ); ?></span>
							<span class="profil-timeline-name"><?php echo esc_html( $row['nama'] ?? '' ); ?></span>
							<span class="profil-timeline-meta"><?php echo esc_html( $row['pengelola'] ?? '' ); ?></span>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
		</div>
	</section>

	<section id="identitas" class="section profil-section profil-section--tint">
		<div class="container">
			<h2 class="profil-h2"><?php esc_html_e( 'Identitas Madrasah', 'man3-hijau-dingin' ); ?></h2>
			<div class="profil-identity-grid">
				<?php
				$identity_rows = array(
					array( 'label' => 'Nama', 'value' => $id['nama'] ?? '' ),
					array( 'label' => 'NPSN', 'value' => $id['npsn'] ?? '' ),
					array( 'label' => 'NSM', 'value' => $id['nsm'] ?? '' ),
					array( 'label' => 'Status', 'value' => $id['status'] ?? '' ),
					array( 'label' => 'Tahun negeri', 'value' => $id['tahun'] ?? '' ),
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

	<section id="visi-misi" class="section profil-section">
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

	<section id="program" class="section profil-section profil-section--tint">
		<div class="container">
			<h2 class="profil-h2"><?php esc_html_e( 'Program Unggulan', 'man3-hijau-dingin' ); ?></h2>
			<div class="profil-cards profil-cards--3">
				<?php foreach ( $program as $prog ) : ?>
					<article class="profil-mini-card">
						<h3><?php echo esc_html( $prog['nama'] ?? '' ); ?></h3>
						<p><?php echo esc_html( $prog['desk'] ?? '' ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section id="siswa" class="section profil-section">
		<div class="container">
			<h2 class="profil-h2"><?php esc_html_e( 'Peserta Didik', 'man3-hijau-dingin' ); ?></h2>
			<?php if ( ! empty( $siswa['total'] ) ) : ?>
				<p class="profil-stat-highlight">
					<span class="profil-stat-num"><?php echo esc_html( number_format_i18n( (int) $siswa['total'] ) ); ?></span>
					<span class="profil-stat-label"><?php echo esc_html( $siswa['label'] ?? __( 'peserta didik', 'man3-hijau-dingin' ) ); ?></span>
				</p>
				<?php if ( ! empty( $siswa['rombel_total'] ) ) : ?>
					<p class="profil-total-note">
						<?php printf( esc_html__( '%d rombongan belajar', 'man3-hijau-dingin' ), (int) $siswa['rombel_total'] ); ?>
					</p>
				<?php endif; ?>
			<?php endif; ?>
			<p class="profil-chart-note"><?php esc_html_e( 'Grafik ringkas komposisi siswa (agregat). Detail per individu tidak ditampilkan.', 'man3-hijau-dingin' ); ?></p>
			<div class="profil-chart-grid">
				<figure class="profil-chart-card">
					<h3><?php esc_html_e( 'Menurut jenis kelamin', 'man3-hijau-dingin' ); ?></h3>
					<canvas id="chart-siswa-gender" height="150" aria-label="<?php esc_attr_e( 'Grafik siswa menurut jenis kelamin', 'man3-hijau-dingin' ); ?>"></canvas>
				</figure>
				<figure class="profil-chart-card">
					<h3><?php esc_html_e( 'Menurut tingkat kelas', 'man3-hijau-dingin' ); ?></h3>
					<canvas id="chart-siswa-tingkat" height="150" aria-label="<?php esc_attr_e( 'Grafik siswa menurut tingkat', 'man3-hijau-dingin' ); ?>"></canvas>
				</figure>
				<figure class="profil-chart-card profil-chart-card--wide">
					<h3><?php esc_html_e( 'Menurut peminatan / kelompok', 'man3-hijau-dingin' ); ?></h3>
					<canvas id="chart-siswa-peminatan" height="165" aria-label="<?php esc_attr_e( 'Grafik siswa menurut peminatan', 'man3-hijau-dingin' ); ?>"></canvas>
				</figure>
			</div>
		</div>
	</section>

	<section id="pendidik" class="section profil-section profil-section--tint">
		<div class="container">
			<h2 class="profil-h2"><?php esc_html_e( 'Pendidik & Tenaga Kependidikan', 'man3-hijau-dingin' ); ?></h2>
			<p class="profil-prose">
				<?php
				printf(
					/* translators: 1: total 2: pendidik 3: tendik */
					esc_html__( 'Seluruhnya %1$d orang: %2$d tenaga pendidik dan %3$d tenaga kependidikan. Berikut ringkasan agregat status kepegawaian.', 'man3-hijau-dingin' ),
					(int) ( $pegawai['total'] ?? 0 ),
					(int) ( $pegawai['pendidik'] ?? 0 ),
					(int) ( $pegawai['tendik'] ?? 0 )
				);
				?>
			</p>
			<?php if ( ! empty( $pegawai['catatan'] ) ) : ?>
				<p class="profil-privacy-note"><?php echo esc_html( $pegawai['catatan'] ); ?></p>
			<?php endif; ?>
			<div class="profil-chart-grid">
				<figure class="profil-chart-card">
					<h3><?php esc_html_e( 'Komposisi pegawai', 'man3-hijau-dingin' ); ?></h3>
					<canvas id="chart-pegawai-jenis" height="150" aria-label="<?php esc_attr_e( 'Grafik komposisi pendidik dan tendik', 'man3-hijau-dingin' ); ?>"></canvas>
				</figure>
				<figure class="profil-chart-card">
					<h3><?php esc_html_e( 'Status kepegawaian', 'man3-hijau-dingin' ); ?></h3>
					<canvas id="chart-pegawai-status" height="150" aria-label="<?php esc_attr_e( 'Grafik status PNS dan Non-ASN', 'man3-hijau-dingin' ); ?>"></canvas>
				</figure>
				<figure class="profil-chart-card profil-chart-card--wide">
					<h3><?php esc_html_e( 'PNS & Non-ASN per jenis tenaga', 'man3-hijau-dingin' ); ?></h3>
					<canvas id="chart-pegawai-detail" height="165" aria-label="<?php esc_attr_e( 'Grafik detail status per jenis tenaga', 'man3-hijau-dingin' ); ?>"></canvas>
				</figure>
			</div>
		</div>
	</section>

	<section id="prasarana" class="section profil-section">
		<div class="container">
			<h2 class="profil-h2"><?php esc_html_e( 'Sarana & Prasarana', 'man3-hijau-dingin' ); ?></h2>
			<?php if ( ! empty( $prasarana['stats'] ) ) : ?>
				<div class="profil-cards">
					<?php foreach ( $prasarana['stats'] as $st ) : ?>
						<article class="profil-mini-card">
							<h3><?php echo esc_html( $st['label'] ?? '' ); ?></h3>
							<p><strong><?php echo esc_html( $st['value'] ?? '' ); ?></strong></p>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $prasarana['catatan'] ) ) : ?>
				<p class="profil-chart-note"><?php echo esc_html( $prasarana['catatan'] ); ?></p>
			<?php endif; ?>
			<figure class="profil-chart-card profil-chart-card--wide" style="margin-top:1.25rem">
				<h3><?php esc_html_e( 'Ringkasan kapasitas', 'man3-hijau-dingin' ); ?></h3>
				<canvas id="chart-prasarana" height="150" aria-label="<?php esc_attr_e( 'Grafik ringkasan prasarana', 'man3-hijau-dingin' ); ?>"></canvas>
			</figure>
		</div>
	</section>
</main>
<?php
get_footer();
