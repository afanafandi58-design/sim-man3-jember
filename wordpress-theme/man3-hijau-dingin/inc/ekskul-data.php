<?php
/**
 * Seed data Ekstra Kurikuler — dari arsip CPT /ekskul/ situs lama (Wayback + media live).
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Baris seed.
 *
 * @return array<int,array<string,mixed>>
 */
function man3_ekskul_seed_rows() {
	return array(
		array(
			'key'         => 'pramuka',
			'nama'        => 'Pramuka',
			'kategori'    => 'karakter',
			'pembina'     => '',
			'unit'        => '',
			'order'       => 10,
			'image_file'  => 'anjangsana_2.jpeg',
			'deskripsi'   => 'Ekstrakurikuler Pramuka di MAN 3 Jember adalah wadah yang mempersatukan para siswa dalam petualangan dan pembelajaran di alam terbuka. Dibimbing oleh para pembina yang berpengalaman, kegiatan Pramuka di MAN 3 Jember tidak hanya mengajarkan keterampilan bertahan hidup, namun juga nilai-nilai kepemimpinan, kerjasama tim, dan tanggung jawab sosial. Setiap pekannya, para anggota Pramuka berkumpul untuk mengikuti berbagai kegiatan, mulai dari perkemahan hingga latihan keterampilan kepramukaan.',
		),
		array(
			'key'         => 'paskibraka',
			'nama'        => 'Paskibraka',
			'kategori'    => 'karakter',
			'pembina'     => 'Fathin Turmudzi Alfin, S.Pd.',
			'unit'        => '',
			'order'       => 20,
			'image_file'  => 'foto-paskib_2.jpeg',
			'deskripsi'   => 'Ekstrakurikuler Paskibraka di MAN 3 Jember adalah kegiatan di luar kurikulum utama yang ditujukan untuk melatih dan mengembangkan keterampilan kepemimpinan, disiplin, kepatuhan, dan semangat nasionalisme siswa. Kegiatan ini berfokus pada persiapan siswa untuk menjadi anggota Paskibraka (Pasukan Pengibar Bendera Pusaka), yang bertugas dalam upacara kenegaraan seperti peringatan Hari Kemerdekaan dan acara protokoler lainnya.',
		),
		array(
			'key'         => 'pmr',
			'nama'        => 'PMR',
			'kategori'    => 'karakter',
			'pembina'     => '',
			'unit'        => 'PMR Wira Kusuma',
			'order'       => 30,
			'image_file'  => 'pmr.jpeg',
			'deskripsi'   => 'Kegiatan ekstrakurikuler Palang Merah Remaja (PMR) di MAN 3 Jember menjadi wadah pembinaan karakter siswa dalam bidang kepalangmerahan, kesehatan, dan kepedulian sosial. Melalui PMR, siswa dibekali pengetahuan dasar tentang pertolongan pertama, hidup bersih dan sehat, serta nilai-nilai kemanusiaan yang berlandaskan semangat tolong-menolong dan empati terhadap sesama. Pelaksanaan kegiatan PMR dilakukan secara rutin dan terprogram, meliputi latihan pertolongan pertama dan simulasi penanganan situasi darurat.',
		),
		array(
			'key'         => 'futsal',
			'nama'        => 'Futsal',
			'kategori'    => 'olahraga',
			'pembina'     => '',
			'unit'        => '',
			'order'       => 40,
			'image_file'  => 'futsal.jpg',
			'deskripsi'   => 'Ekstrakurikuler futsal di MAN 3 Jember adalah kegiatan tambahan di luar jam pelajaran yang berfokus pada olahraga futsal. Kegiatan ini dirancang untuk mengembangkan bakat dan minat siswa dalam bidang olahraga futsal, serta untuk meningkatkan keterampilan teknis, strategi permainan, dan kerja sama tim.',
		),
		array(
			'key'         => 'bulu-tangkis',
			'nama'        => 'Bulu Tangkis',
			'kategori'    => 'olahraga',
			'pembina'     => 'Moh. Muhtadi, S.Pd.',
			'unit'        => '',
			'order'       => 50,
			'image_file'  => 'latgab-bulutangkis_1-scaled.jpeg',
			'deskripsi'   => 'Ekstrakurikuler bulu tangkis di MAN 3 Jember merupakan salah satu wadah yang sangat diminati oleh para siswa yang gemar dalam olahraga ini. Dengan fasilitas yang memadai dan didukung oleh pelatih berpengalaman, klub bulu tangkis ini memberikan kesempatan bagi para anggotanya untuk mengembangkan keterampilan teknis dan strategis. Setiap minggunya, para anggota berkumpul untuk berlatih bersama.',
		),
		array(
			'key'         => 'paduan-suara',
			'nama'        => 'Paduan Suara',
			'kategori'    => 'seni',
			'pembina'     => '',
			'unit'        => '',
			'order'       => 60,
			'image_file'  => 'Screenshot-2024-05-20-111116.png',
			'deskripsi'   => 'Ekstrakurikuler paduan suara di MAN 3 Jember adalah tempat di mana bakat vokal siswa berkembang dan dihargai. Dibimbing oleh para guru musik yang berpengalaman, klub paduan suara ini menyediakan kesempatan bagi para anggotanya untuk mengeksplorasi dan mengembangkan suara mereka dalam berbagai genre musik. Setiap minggunya, para anggota berkumpul untuk berlatih menyanyikan berbagai lagu secara harmonis.',
		),
	);
}

/**
 * Label kategori filter.
 *
 * @return array<string,string>
 */
function man3_ekskul_kategori_labels() {
	return array(
		'olahraga' => __( 'Olahraga', 'man3-hijau-dingin' ),
		'karakter' => __( 'Karakter & Kepemimpinan', 'man3-hijau-dingin' ),
		'seni'     => __( 'Seni & Budaya', 'man3-hijau-dingin' ),
	);
}
