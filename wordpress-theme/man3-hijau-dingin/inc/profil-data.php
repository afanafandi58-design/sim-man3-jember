<?php
/**
 * Default data Profil Madrasah 2026 — agregat publik (tanpa nama/NIP individu).
 * Sumber: Profil-MAN-3-2026.docx
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data default profil (aman untuk publik).
 *
 * @return array<string,mixed>
 */
function man3_profil_default_data() {
	return array(
		'version'   => '2026',
		'identitas' => array(
			'nama'       => 'Madrasah Aliyah Negeri 3 Jember',
			'nsm'        => '131135090003',
			'npsn'       => '20580293',
			'akreditasi' => 'A (Unggul)',
			'alamat'     => 'Jl. Ahmad Yani No. 76, Desa Jombang, Kec. Jombang, Kab. Jember, Jawa Timur 68168',
			'telepon'    => '(0336) 322267',
			'email'      => 'humasmantiger@gmail.com',
			'website'    => 'https://man3jember.sch.id/',
			'status'     => 'Negeri',
			'tahun'      => '1995',
		),
		'sejarah'   => 'Madrasah Aliyah Negeri 3 Jember lahir dari perjalanan panjang pendidikan di Desa Jombang. Bermula dari MA Al Ittihadiyah (1977), kemudian menjadi MAN Filial MAN 1 Jember (1982–1995), dan sejak SK Menteri Agama Nomor 515 A Tahun 1995 berstatus negeri penuh sebagai MAN 3 Jember. Program unggulan meliputi bina prestasi, keterampilan, dan tahfidzul Qur’an.',
		'timeline'  => array(
			array( 'tahun' => '1977–1982', 'nama' => 'MA Ittihadiyah', 'pengelola' => 'Yayasan' ),
			array( 'tahun' => '1982–1995', 'nama' => 'MAN Filial Jember', 'pengelola' => 'Yayasan & Negara' ),
			array( 'tahun' => '1995–sekarang', 'nama' => 'MAN 3 Jember', 'pengelola' => 'Negara' ),
		),
		'visi'      => 'Terwujudnya lulusan yang berkarakter akhlak mulia, cerdas, terampil, dan kreatif berbasis kearifan lokal untuk membangun peradaban yang berdaya saing.',
		'misi'      => array(
			'Mewujudkan karakter beriman dan bertakwa: yakin dan tawakkal, jujur dan amanah, sabar dan syukur, khusyuk beribadah, serta peduli alam dan sesama.',
			'Mewujudkan perilaku disiplin, tanggung jawab, empati, toleransi, dan kepedulian sosial.',
			'Meningkatkan literasi dan numerasi siswa; mendorong penelitian, proyek, dan lomba akademik.',
			'Meningkatkan karya inovatif siswa serta pemanfaatan teknologi digital secara positif.',
			'Mewujudkan keterampilan praktis (kerajinan, seni, pertanian, kewirausahaan) sesuai potensi daerah.',
			'Menjalin kemitraan dengan masyarakat/komunitas lokal untuk mendukung program keterampilan.',
		),
		'program'   => array(
			array(
				'nama' => 'Bina Prestasi',
				'desk' => 'Kelas dengan penguatan materi UTBK pada Bahasa Indonesia, Bahasa Inggris, dan Matematika.',
			),
			array(
				'nama' => 'Keterampilan',
				'desk' => 'Tata busana, multimedia, operator komputer, Prodistik, tata boga, dan tata rias (moving class).',
			),
			array(
				'nama' => 'Tahfidzul Qur’an',
				'desk' => 'Program pembinaan hafalan Al-Qur’an sebagai ciri khas pengembangan karakter religius.',
			),
		),
		/* Agregat siswa TP 2026/2027 — tanpa daftar per individu. */
		'siswa_chart' => array(
			'total'  => 728,
			'label'  => 'Peserta didik (TP 2026/2027, rekap rombel)',
			'gender' => array(
				array( 'label' => 'Laki-laki', 'value' => 213 ),
				array( 'label' => 'Perempuan', 'value' => 515 ),
			),
			'tingkat' => array(
				array( 'label' => 'Kelas 10', 'value' => 232 ),
				array( 'label' => 'Kelas 11', 'value' => 244 ),
				array( 'label' => 'Kelas 12', 'value' => 252 ),
			),
			'peminatan' => array(
				array( 'label' => 'Sains', 'value' => 242, 'rombel' => 10 ),
				array( 'label' => 'Soshum', 'value' => 210, 'rombel' => 8 ),
				array( 'label' => 'Keagamaan', 'value' => 44, 'rombel' => 2 ),
				array( 'label' => 'Kelas 10 (belum peminatan)', 'value' => 232, 'rombel' => 8 ),
			),
			'rombel_total' => 28,
		),
		/*
		 * Agregat pegawai — HANYA status/kategori.
		 * Sumber ringkasan dokumen: 55 pendidik + 14 tendik; PNS vs Non-ASN.
		 */
		'pegawai_chart' => array(
			'total'     => 69,
			'pendidik'  => 55,
			'tendik'    => 14,
			'jenis'     => array(
				array( 'label' => 'Pendidik (Guru)', 'value' => 55 ),
				array( 'label' => 'Tenaga Kependidikan', 'value' => 14 ),
			),
			'status'    => array(
				array( 'label' => 'PNS', 'value' => 41 ),
				array( 'label' => 'Non-ASN (PPPK/GTT/PTT)', 'value' => 28 ),
			),
			'status_detail' => array(
				array( 'label' => 'PNS — Pendidik', 'value' => 37 ),
				array( 'label' => 'PNS — Tendik', 'value' => 4 ),
				array( 'label' => 'Non-ASN — Pendidik', 'value' => 18 ),
				array( 'label' => 'Non-ASN — Tendik', 'value' => 10 ),
			),
			'catatan'   => 'Ditampilkan hanya ringkasan agregat status kepegawaian. Nama, NIP, dan data personal tidak dipublikasikan.',
		),
		'prasarana_chart' => array(
			'stats' => array(
				array( 'label' => 'Luas lahan', 'value' => '10.890 m²' ),
				array( 'label' => 'Luas bangunan', 'value' => '2.843 m²' ),
				array( 'label' => 'Rombel', 'value' => '28' ),
				array( 'label' => 'Ruang kelas', 'value' => '31' ),
			),
			'bars'  => array(
				array( 'label' => 'Ruang kelas', 'value' => 31 ),
				array( 'label' => 'Rombongan belajar', 'value' => 28 ),
				array( 'label' => 'Lantai bangunan', 'value' => 2 ),
			),
			'catatan' => 'Ringkasan sarana utama dari profil 2026 (bukan inventaris detail per ruang).',
		),
	);
}
