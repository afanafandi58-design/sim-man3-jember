<?php
/**
 * MAN 3 Hijau Dingin — theme setup.
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MAN3_HIJAU_VERSION', '1.5.39' );

$man3_org_inc = get_template_directory() . '/inc/struktur-organisasi.php';
if ( file_exists( $man3_org_inc ) ) {
	require_once $man3_org_inc;
}

$man3_video_inc = get_template_directory() . '/inc/galeri-video.php';
if ( file_exists( $man3_video_inc ) ) {
	require_once $man3_video_inc;
}

$man3_foto_inc = get_template_directory() . '/inc/galeri-foto.php';
if ( file_exists( $man3_foto_inc ) ) {
	require_once $man3_foto_inc;
}

$man3_profil_inc = get_template_directory() . '/inc/profil-madrasah.php';
if ( file_exists( $man3_profil_inc ) ) {
	require_once $man3_profil_inc;
}

$man3_pegawai_inc = get_template_directory() . '/inc/data-pegawai.php';
if ( file_exists( $man3_pegawai_inc ) ) {
	require_once $man3_pegawai_inc;
}

$man3_ekskul_inc = get_template_directory() . '/inc/ekskul.php';
if ( file_exists( $man3_ekskul_inc ) ) {
	require_once $man3_ekskul_inc;
}

$man3_hero_inc = get_template_directory() . '/inc/hero-slider.php';
if ( file_exists( $man3_hero_inc ) ) {
	require_once $man3_hero_inc;
}

$man3_news_mantiger_inc = get_template_directory() . '/inc/news-mantiger.php';
if ( file_exists( $man3_news_mantiger_inc ) ) {
	require_once $man3_news_mantiger_inc;
}

$man3_ppid_inc = get_template_directory() . '/inc/ppid.php';
if ( file_exists( $man3_ppid_inc ) ) {
	require_once $man3_ppid_inc;
}

$man3_survey_inc = get_template_directory() . '/inc/survey-kepuasan.php';
if ( file_exists( $man3_survey_inc ) ) {
	require_once $man3_survey_inc;
}

$man3_popup_inc = get_template_directory() . '/inc/info-popup.php';
if ( file_exists( $man3_popup_inc ) ) {
	require_once $man3_popup_inc;
}

$man3_sambutan_inc = get_template_directory() . '/inc/sambutan.php';
if ( file_exists( $man3_sambutan_inc ) ) {
	require_once $man3_sambutan_inc;
}

$man3_kunjungan_inc = get_template_directory() . '/inc/kunjungan.php';
if ( file_exists( $man3_kunjungan_inc ) ) {
	require_once $man3_kunjungan_inc;
}

$man3_refleksi_inc = get_template_directory() . '/inc/refleksi-warga.php';
if ( file_exists( $man3_refleksi_inc ) ) {
	require_once $man3_refleksi_inc;
}

/**
 * Theme supports & menus.
 */
function man3_hijau_setup() {
	load_theme_textdomain( 'man3-hijau-dingin', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support( 'custom-logo', array(
		'height'      => 120,
		'width'       => 120,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	register_nav_menus(
		array(
			'primary' => __( 'Menu Utama', 'man3-hijau-dingin' ),
			'footer'  => __( 'Menu Footer', 'man3-hijau-dingin' ),
		)
	);
}
add_action( 'after_setup_theme', 'man3_hijau_setup' );

/**
 * Sembunyikan item top-level "VISI MISI…" dari menu primary.
 * Tetap tampil jika jadi submenu (mis. di bawah Beranda).
 *
 * @param array    $items Menu items.
 * @param stdClass $menu  Menu object.
 * @param array    $args  wp_get_nav_menu_items args.
 * @return array
 */
function man3_hijau_hide_toplevel_visi_misi( $items, $menu, $args ) {
	if ( empty( $items ) || ! is_array( $items ) ) {
		return $items;
	}

	$locations = get_nav_menu_locations();
	$primary   = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;
	if ( $primary && isset( $menu->term_id ) && (int) $menu->term_id !== $primary ) {
		return $items;
	}

	$remove_ids = array();
	foreach ( $items as $item ) {
		$parent = isset( $item->menu_item_parent ) ? (int) $item->menu_item_parent : 0;
		if ( 0 !== $parent ) {
			continue;
		}
		$title = isset( $item->title ) ? wp_strip_all_tags( $item->title ) : '';
		$url   = isset( $item->url ) ? (string) $item->url : '';
		$is_visi = (bool) preg_match( '/visi\s*misi/i', $title )
			|| false !== strpos( $url, 'visi-misi-man-3-jember' );
		if ( $is_visi ) {
			$remove_ids[ (int) $item->ID ] = true;
		}
	}

	if ( empty( $remove_ids ) ) {
		return $items;
	}

	$filtered = array();
	foreach ( $items as $item ) {
		$id     = (int) $item->ID;
		$parent = isset( $item->menu_item_parent ) ? (int) $item->menu_item_parent : 0;
		if ( isset( $remove_ids[ $id ] ) || isset( $remove_ids[ $parent ] ) ) {
			continue;
		}
		$filtered[] = $item;
	}
	return $filtered;
}
add_filter( 'wp_get_nav_menu_items', 'man3_hijau_hide_toplevel_visi_misi', 10, 3 );

/**
 * Sekali jalan: hapus entri top-level VISI MISI dari menu primary di DB
 * (submenu di bawah Beranda tidak disentuh).
 */
function man3_hijau_cleanup_toplevel_visi_misi_menu() {
	if ( get_option( 'man3_hijau_nav_drop_toplevel_visi' ) === '1.5.29' ) {
		return;
	}
	$locations = get_nav_menu_locations();
	$menu_id   = ! empty( $locations['primary'] ) ? (int) $locations['primary'] : 0;
	if ( ! $menu_id ) {
		return;
	}
	// Filter tampilan aktif — ambil mentah lewat get_posts.
	$raw = get_posts(
		array(
			'post_type'      => 'nav_menu_item',
			'posts_per_page' => 100,
			'post_status'    => 'any',
			'nopaging'       => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'nav_menu',
					'field'    => 'term_id',
					'terms'    => $menu_id,
				),
			),
		)
	);
	foreach ( $raw as $post ) {
		$parent = (int) get_post_meta( $post->ID, '_menu_item_menu_item_parent', true );
		if ( 0 !== $parent ) {
			continue;
		}
		$title = $post->post_title;
		if ( '' === $title ) {
			$object_id = (int) get_post_meta( $post->ID, '_menu_item_object_id', true );
			if ( $object_id ) {
				$title = get_the_title( $object_id );
			}
		}
		$url = (string) get_post_meta( $post->ID, '_menu_item_url', true );
		if ( ! $url ) {
			$object_id = (int) get_post_meta( $post->ID, '_menu_item_object_id', true );
			$type      = (string) get_post_meta( $post->ID, '_menu_item_type', true );
			if ( 'post_type' === $type && $object_id ) {
				$url = (string) get_permalink( $object_id );
			}
		}
		$is_visi = (bool) preg_match( '/visi\s*misi/i', (string) $title )
			|| false !== strpos( $url, 'visi-misi-man-3-jember' );
		if ( $is_visi ) {
			wp_delete_post( $post->ID, true );
		}
	}
	update_option( 'man3_hijau_nav_drop_toplevel_visi', '1.5.29', false );
}
add_action( 'init', 'man3_hijau_cleanup_toplevel_visi_misi_menu', 20 );

/**
 * Meta judul/URL mentah untuk item menu (tanpa filter tampilan).
 *
 * @param WP_Post $post Nav menu item post.
 * @return array{title:string,url:string}
 */
function man3_hijau_nav_item_meta( $post ) {
	$title = $post->post_title;
	if ( '' === $title ) {
		$object_id = (int) get_post_meta( $post->ID, '_menu_item_object_id', true );
		if ( $object_id ) {
			$title = get_the_title( $object_id );
		}
	}
	$url = (string) get_post_meta( $post->ID, '_menu_item_url', true );
	if ( ! $url ) {
		$object_id = (int) get_post_meta( $post->ID, '_menu_item_object_id', true );
		$type      = (string) get_post_meta( $post->ID, '_menu_item_type', true );
		if ( 'post_type' === $type && $object_id ) {
			$url = (string) get_permalink( $object_id );
		}
	}
	return array(
		'title' => (string) $title,
		'url'   => (string) $url,
	);
}

/**
 * Sekali jalan: pindahkan Download & Ekstra Kurikuler ke submenu Info Madrasah.
 */
function man3_hijau_move_download_ekskul_under_info() {
	if ( get_option( 'man3_hijau_nav_info_download_ekskul' ) === '1.5.30' ) {
		return;
	}
	$locations = get_nav_menu_locations();
	$menu_id   = ! empty( $locations['primary'] ) ? (int) $locations['primary'] : 0;
	if ( ! $menu_id ) {
		return;
	}
	$raw = get_posts(
		array(
			'post_type'      => 'nav_menu_item',
			'posts_per_page' => 100,
			'post_status'    => 'any',
			'nopaging'       => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'nav_menu',
					'field'    => 'term_id',
					'terms'    => $menu_id,
				),
			),
		)
	);

	$info_id       = 0;
	$move_ids      = array();
	$already_child = array(
		'download' => false,
		'ekskul'   => false,
	);

	foreach ( $raw as $post ) {
		$parent = (int) get_post_meta( $post->ID, '_menu_item_menu_item_parent', true );
		$meta   = man3_hijau_nav_item_meta( $post );
		$title  = $meta['title'];
		$url    = $meta['url'];

		if ( 0 === $parent && preg_match( '/^info\s*madrasah$/i', trim( $title ) ) ) {
			$info_id = (int) $post->ID;
		}

		$is_download = (bool) preg_match( '/^download$/i', trim( $title ) )
			|| false !== strpos( $url, '/download' );
		$is_ekskul   = (bool) preg_match( '/ekstra\s*kurikuler|ekstrakurikuler/i', $title )
			|| false !== strpos( $url, '/ekskul' );

		if ( $parent > 0 ) {
			if ( $is_download ) {
				$already_child['download'] = true;
			}
			if ( $is_ekskul ) {
				$already_child['ekskul'] = true;
			}
			continue;
		}
		if ( $is_download ) {
			$move_ids[ (int) $post->ID ] = 'download';
		} elseif ( $is_ekskul ) {
			$move_ids[ (int) $post->ID ] = 'ekskul';
		}
	}

	if ( ! $info_id || empty( $move_ids ) ) {
		update_option( 'man3_hijau_nav_info_download_ekskul', '1.5.30', false );
		return;
	}

	$order = 40;
	foreach ( $move_ids as $item_id => $kind ) {
		if ( ! empty( $already_child[ $kind ] ) ) {
			// Sudah ada di submenu — hapus duplikat top-level.
			wp_delete_post( $item_id, true );
			continue;
		}
		update_post_meta( $item_id, '_menu_item_menu_item_parent', (string) $info_id );
		wp_update_post(
			array(
				'ID'         => $item_id,
				'menu_order' => $order,
			)
		);
		$order += 10;
		$already_child[ $kind ] = true;
	}

	update_option( 'man3_hijau_nav_info_download_ekskul', '1.5.30', false );
}
add_action( 'init', 'man3_hijau_move_download_ekskul_under_info', 21 );

/**
 * Enqueue fonts, CSS, JS.
 */
function man3_hijau_assets() {
	wp_enqueue_style(
		'man3-hijau-fonts',
		'https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700;9..144,800&family=Outfit:wght@300;400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'man3-hijau-theme',
		get_template_directory_uri() . '/assets/css/theme.css',
		array( 'man3-hijau-fonts' ),
		MAN3_HIJAU_VERSION
	);

	wp_enqueue_style(
		'man3-hijau-style',
		get_stylesheet_uri(),
		array( 'man3-hijau-theme' ),
		MAN3_HIJAU_VERSION
	);

	wp_enqueue_script(
		'man3-hijau-theme',
		get_template_directory_uri() . '/assets/js/theme.js',
		array(),
		MAN3_HIJAU_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'man3_hijau_assets' );

/**
 * URI aset tema.
 *
 * @param string $path Relatif ke folder assets/.
 */
function man3_hijau_asset( $path ) {
	return get_template_directory_uri() . '/assets/' . ltrim( $path, '/' );
}

/**
 * Walker sederhana untuk dropdown native <details>/<summary> di header.
 * Fallback menu hardcoded ada di header.php jika belum ada menu assigned.
 */
class Man3_Hijau_Nav_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<div class="nav-dropdown">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</div>';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$has_children = in_array( 'menu-item-has-children', $classes, true );
		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$url   = ! empty( $item->url ) ? $item->url : '#';

		if ( 0 === (int) $depth && $has_children ) {
			$output .= '<details class="nav-item has-children">';
			$output .= '<summary aria-haspopup="true">';
			$output .= '<span class="nav-label">' . esc_html( $title ) . '</span>';
			$output .= '<span class="nav-caret" aria-hidden="true"></span>';
			$output .= '</summary>';
			return;
		}

		$class = ( 0 === (int) $depth ) ? 'nav-link' : 'nav-sublink';
		$output .= '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a>';
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		if ( 0 === (int) $depth && in_array( 'menu-item-has-children', $classes, true ) ) {
			$output .= '</details>';
		}
	}
}

/**
 * Data fallback konten (dari situs publik) — dipakai jika WP belum punya post.
 */
function man3_hijau_fallback_quotes() {
	if ( function_exists( 'man3_refleksi_get_items' ) ) {
		$items = man3_refleksi_get_items();
		if ( $items ) {
			return $items;
		}
	}
	return array(
		array(
			'text'   => 'Madrasah bukan hanya tempat mencari ilmu, tetapi tempat membentuk hati, adab, dan masa depan...',
			'author' => 'Abah',
		),
		array(
			'text'   => 'Prinsip ekonomi Islam yang adil dan transparan sejalan dengan nilai-nilai kejujuran dan integritas yang kita pelajari di madrasah.',
			'author' => 'SM',
		),
		array(
			'text'   => 'Tersiarlah cahaya ilmu dari siswa-siswi madrasah, Berakhlak, santun, dan beradab, tatkala mereka berjalan. Mereka adalah bunga-bunga yang harum di taman ilmu, Menghiasi dunia dengan kebaikan.....',
			'author' => "Biet's Hayyuni",
		),
		array(
			'text'   => 'Hidup bersosial yang baik mengajarkan kita untuk menghargai perbedaan dan saling mendukung dalam kebaikan....',
			'author' => 'Wilda Naela',
		),
		array(
			'text'   => 'Kedisiplinan adalah kunci kesuksesan; sikap yang baik adalah jalan menuju kebahagiaan abadi....',
			'author' => 'Usman Affan Ridoi',
		),
	);
}

/**
 * Menu fallback (struktur sama dengan situs live).
 *
 * @param bool $mobile Untuk markup mobile.
 */
function man3_hijau_fallback_nav( $mobile = false ) {
	$items = array(
		array(
			'label'    => 'Beranda',
			'children' => array(
				array( 'label' => 'Profil Madrasah', 'href' => home_url( '/profil/' ) ),
				array( 'label' => 'Visi Misi', 'href' => home_url( '/visi-misi-man-3-jember/' ) ),
			),
		),
		array(
			'label'    => 'Info Madrasah',
			'children' => array(
				array( 'label' => 'Pengumuman', 'href' => home_url( '/pengumuman/' ) ),
				array( 'label' => 'Agenda', 'href' => home_url( '/agenda/' ) ),
				array( 'label' => 'Fasilitas', 'href' => home_url( '/fasilitas/' ) ),
				array( 'label' => 'Download', 'href' => home_url( '/download/' ) ),
				array( 'label' => 'Ekstra Kurikuler', 'href' => home_url( '/ekskul/' ) ),
			),
		),
		array(
			'label'    => 'Pendidik dan Kependidikan',
			'children' => array(
				array( 'label' => 'Guru & Staff', 'href' => home_url( '/data-pegawai/' ) ),
				array( 'label' => 'Struktur Organisasi', 'href' => home_url( '/struktur-organisasi/' ) ),
			),
		),
		array(
			'label'    => 'Gallery',
			'children' => array(
				array( 'label' => 'Foto', 'href' => home_url( '/galeri/' ) ),
				array( 'label' => 'Video', 'href' => home_url( '/video/' ) ),
			),
		),
		array( 'label' => 'Berita', 'href' => home_url( '/#berita' ) ),
		array( 'label' => 'Pengumuman', 'href' => home_url( '/pengumuman/' ) ),
		array( 'label' => 'News Mantiger', 'href' => home_url( '/news-mantiger/' ) ),
		array( 'label' => 'PPID', 'href' => home_url( '/ppid/' ) ),
	);

	foreach ( $items as $item ) {
		if ( ! empty( $item['children'] ) ) {
			echo '<details class="nav-item has-children">';
			echo '<summary aria-haspopup="true"><span class="nav-label">' . esc_html( $item['label'] ) . '</span><span class="nav-caret" aria-hidden="true"></span></summary>';
			echo $mobile ? '<div class="nav-dropdown">' : '<div class="nav-dropdown">';
			foreach ( $item['children'] as $child ) {
				echo '<a class="nav-sublink" href="' . esc_url( $child['href'] ) . '">' . esc_html( $child['label'] ) . '</a>';
			}
			echo '</div></details>';
		} else {
			echo '<a class="nav-link" href="' . esc_url( $item['href'] ) . '">' . esc_html( $item['label'] ) . '</a>';
		}
	}
}

/**
 * Fallback berita jika belum ada post WordPress.
 */
function man3_hijau_fallback_berita() {
	$img = man3_hijau_asset( 'images/' );
	return array(
		array(
			'date'    => '20 October 2026',
			'title'   => 'MAN 3 JEMBER MENGETUK PINTU LANGIT: IKHTIAR SPIRITUAL MEMOHON RAHMAT DAN TURUNNYA HUJAN',
			'excerpt' => 'Jember, Selasa, 20 Oktober 2026 — Dalam balutan keimanan dan penuh kerendahan hati, seluruh civitas akademika MAN 3 Jember melaksanakan salat Istisqa sebagai ikhtiar spiritual untuk memohon rahmat dan turunnya..',
			'image'   => $img . 'berita-istisqa.jpg',
			'link'    => home_url( '/man-3-jember-mengetuk-pintu-langit-ikhtiar-spiritual-memohon-rahmat-dan-turunnya-hujan/' ),
		),
		array(
			'date'    => '12 October 2026',
			'title'   => 'Cegah ISPA Sejak Dini, Puskesmas Jombang Edukasi Warga MAN 3 Jember tentang Pentingnya Hidup Sehat',
			'excerpt' => 'JEMBER – Puskesmas Jombang menggelar sosialisasi pencegahan Infeksi Saluran Pernapasan Akut (ISPA) di MAN 3 Jember, Senin (12/10/2026). Kegiatan ini menyasar siswa dan guru sebagai bagian dari upaya meningkatkan kesadaran..',
			'image'   => $img . 'berita-ispa.jpg',
			'link'    => home_url( '/cegah-ispa-sejak-dini-puskesmas-jombang-edukasi-warga-man-3-jember-tentang-pentingnya-hidup-sehat/' ),
		),
		array(
			'date'    => '12 October 2026',
			'title'   => 'Tiga Paslon Adu Gagasan, Warga MAN 3 Jember Tentukan Ketua dan Wakil Ketua OSIM 2026/2027',
			'excerpt' => 'JEMBER – MAN 3 Jember menggelar debat kandidat calon Ketua dan Wakil Ketua Organisasi Siswa Intra Madrasah (OSIM) periode 2026/2027, yang dilanjutkan dengan pemilihan oleh seluruh warga madrasah. Kegiatan ini..',
			'image'   => $img . 'berita-debat.jpg',
			'link'    => home_url( '/tiga-paslon-adu-gagasan-warga-man-3-jember-tentukan-ketua-dan-wakil-ketua-osim-2026-2027/' ),
		),
		array(
			'date'    => '27 August 2026',
			'title'   => 'MAN 3 Jember Gelar Pertemuan Wali Murid Kelas XII, Sosialisasikan Persiapan TKA 2026 dan Job Career Menuju Job Fair 2027',
			'excerpt' => 'MAN 3 Jember menggelar pertemuan wali murid kelas XII Tahun Pelajaran 2026/2027 yang bertempat di aula madrasah pada Kamis (27/8). Kegiatan ini bertujuan untuk memperkuat sinergi antara madrasah dan orang..',
			'image'   => $img . 'berita-expo.jpg',
			'link'    => home_url( '/man-3-jember-gelar-pertemuan-wali-murid-kelas-xii-sosialisasikan-persiapan-tka-2026-dan-job-career-menuju-job-fair-2027/' ),
		),
		array(
			'date'    => '2026',
			'title'   => 'Bangga! Siswi MAN 3 Jember Terpilih sebagai Paskibraka Kabupaten Jember 2026',
			'excerpt' => 'MAN 3 Jember — Menjelang peringatan Hari Ulang Tahun (HUT) ke-81 Kemerdekaan Republik Indonesia, kabar membanggakan kembali hadir dari MAN 3 Jember. Prestasi tersebut datang dari Amyra Nadia Safa, siswi..',
			'image'   => $img . 'berita-expo.jpg',
			'link'    => home_url( '/category/berita/' ),
		),
	);
}
