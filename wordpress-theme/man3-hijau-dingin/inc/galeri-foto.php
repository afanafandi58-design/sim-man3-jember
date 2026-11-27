<?php
/**
 * CPT Galeri Foto + seed 6 bulan + halaman /galeri/.
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register CPT + taxonomy jenis kegiatan.
 */
function man3_foto_register() {
	register_post_type(
		'man3_foto',
		array(
			'labels'             => array(
				'name'               => __( 'Galeri Foto', 'man3-hijau-dingin' ),
				'singular_name'      => __( 'Foto', 'man3-hijau-dingin' ),
				'add_new_item'       => __( 'Tambah Foto', 'man3-hijau-dingin' ),
				'edit_item'          => __( 'Edit Foto', 'man3-hijau-dingin' ),
				'menu_name'          => __( 'Galeri Foto', 'man3-hijau-dingin' ),
				'featured_image'     => __( 'Foto kegiatan', 'man3-hijau-dingin' ),
				'set_featured_image' => __( 'Pilih / unggah foto', 'man3-hijau-dingin' ),
			),
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-format-gallery',
			'menu_position'      => 22,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
		)
	);

	register_taxonomy(
		'man3_jenis_kegiatan',
		'man3_foto',
		array(
			'labels'            => array(
				'name'          => __( 'Jenis Kegiatan', 'man3-hijau-dingin' ),
				'singular_name' => __( 'Jenis Kegiatan', 'man3-hijau-dingin' ),
				'search_items'  => __( 'Cari jenis', 'man3-hijau-dingin' ),
				'all_items'     => __( 'Semua jenis', 'man3-hijau-dingin' ),
				'edit_item'     => __( 'Edit jenis', 'man3-hijau-dingin' ),
				'update_item'   => __( 'Perbarui jenis', 'man3-hijau-dingin' ),
				'add_new_item'  => __( 'Tambah jenis kegiatan', 'man3-hijau-dingin' ),
				'menu_name'     => __( 'Jenis Kegiatan', 'man3-hijau-dingin' ),
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => false,
		)
	);
}
add_action( 'init', 'man3_foto_register' );

/**
 * Meta: tanggal kegiatan.
 */
function man3_foto_meta_box() {
	add_meta_box(
		'man3_foto_meta',
		__( 'Detail Foto', 'man3-hijau-dingin' ),
		'man3_foto_meta_box_render',
		'man3_foto',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_foto_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function man3_foto_meta_box_render( $post ) {
	wp_nonce_field( 'man3_foto_save', 'man3_foto_nonce' );
	$date = get_post_meta( $post->ID, '_man3_foto_date', true );
	?>
	<p>
		<label for="man3_foto_date"><strong><?php esc_html_e( 'Tanggal kegiatan (YYYY-MM-DD)', 'man3-hijau-dingin' ); ?></strong></label>
		<input type="text" class="widefat" id="man3_foto_date" name="man3_foto_date" value="<?php echo esc_attr( $date ); ?>" placeholder="2026-10-20" />
	</p>
	<p class="description">
		<?php esc_html_e( 'Jenis kegiatan: panel kanan “Jenis Kegiatan”. Wajib: judul + Gambar Unggulan. Deskripsi opsional di editor.', 'man3-hijau-dingin' ); ?>
	</p>
	<?php
}

/**
 * @param int $post_id ID.
 */
function man3_foto_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_foto_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_foto_nonce'] ) ), 'man3_foto_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['man3_foto_date'] ) ) {
		update_post_meta( $post_id, '_man3_foto_date', sanitize_text_field( wp_unslash( $_POST['man3_foto_date'] ) ) );
	}
}
add_action( 'save_post_man3_foto', 'man3_foto_meta_save' );

/**
 * @param array<string,string> $cols Columns.
 * @return array<string,string>
 */
function man3_foto_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['man3_foto_thumb'] = __( 'Foto', 'man3-hijau-dingin' );
			$new['man3_foto_date']  = __( 'Tanggal', 'man3-hijau-dingin' );
		}
	}
	return $new;
}
add_filter( 'manage_man3_foto_posts_columns', 'man3_foto_columns' );

/**
 * @param string $col Column.
 * @param int    $post_id ID.
 */
function man3_foto_column_content( $col, $post_id ) {
	if ( 'man3_foto_thumb' === $col ) {
		$thumb = get_the_post_thumbnail( $post_id, array( 72, 72 ) );
		echo $thumb ? $thumb : '&mdash;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( 'man3_foto_date' === $col ) {
		echo esc_html( (string) get_post_meta( $post_id, '_man3_foto_date', true ) );
	}
}
add_action( 'manage_man3_foto_posts_custom_column', 'man3_foto_column_content', 10, 2 );

/**
 * Cari attachment by filename fragment.
 *
 * @param string   $needle Filename fragment.
 * @param int|null $fallback_id Optional known ID.
 * @return int
 */
function man3_foto_find_attachment( $needle, $fallback_id = null ) {
	global $wpdb;
	$like = '%' . $wpdb->esc_like( $needle ) . '%';
	$id   = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%%' AND (guid LIKE %s OR post_title LIKE %s OR post_name LIKE %s) ORDER BY ID DESC LIMIT 1",
			$like,
			$like,
			$like
		)
	);
	if ( $id ) {
		return $id;
	}
	return $fallback_id ? (int) $fallback_id : 0;
}

/**
 * Seed rows — media library / berita 6 bulan terakhir (~Apr–Oct 2026).
 *
 * @return array<int,array<string,mixed>>
 */
function man3_foto_seed_items() {
	return array(
		array(
			'key'      => 'foto-hari-santri-2026',
			'title'    => 'Selamat Hari Santri 2026',
			'jenis'    => 'Peringatan',
			'date'     => '2026-10-20',
			'file'     => 'hari-santri',
			'fallback' => 7412,
			'desc'     => 'Peringatan Hari Santri 2026 di MAN 3 Jember dengan semangat santri menguatkan negeri.',
			'order'    => 10,
		),
		array(
			'key'      => 'foto-istiqomah-okt-2026',
			'title'    => 'Ikhtiar spiritual warga madrasah',
			'jenis'    => 'Keagamaan',
			'date'     => '2026-10-20',
			'file'     => 'istiqa',
			'fallback' => 7408,
			'desc'     => 'Kegiatan keagamaan memperkuat keimanan dan kebersamaan civitas MAN 3 Jember.',
			'order'    => 20,
		),
		array(
			'key'      => 'foto-edukasi-ispa-2026',
			'title'    => 'Edukasi cegah ISPA bersama Puskesmas',
			'jenis'    => 'Kesehatan',
			'date'     => '2026-10-12',
			'file'     => 'ispa',
			'fallback' => 7404,
			'desc'     => 'Edukasi kesehatan untuk warga madrasah agar lebih waspada terhadap ISPA.',
			'order'    => 30,
		),
		array(
			'key'      => 'foto-debat-kandidat-2026',
			'title'    => 'Debat kandidat organisasi siswa',
			'jenis'    => 'Organisasi Siswa',
			'date'     => '2026-10-12',
			'file'     => 'debat-kandidat',
			'fallback' => 7399,
			'desc'     => 'Adu gagasan pasangan calon dalam pemilihan organisasi siswa MAN 3 Jember.',
			'order'    => 40,
		),
		array(
			'key'      => 'foto-pertemuan-wali-expo-2026',
			'title'    => 'Pertemuan wali murid kelas XII',
			'jenis'    => 'Pertemuan Wali',
			'date'     => '2026-08-27',
			'file'     => 'expo-1',
			'fallback' => 7395,
			'desc'     => 'Silaturahmi dan sosialisasi bersama orang tua/wali murid kelas XII.',
			'order'    => 50,
		),
		array(
			'key'      => 'foto-paskibraka-2026',
			'title'    => 'Siswi MAN 3 terpilih Paskibraka',
			'jenis'    => 'Prestasi',
			'date'     => '2026-08-16',
			'file'     => 'paskibraka.jpeg',
			'fallback' => 7387,
			'desc'     => 'Kebanggaan madrasah atas terpilihnya siswi sebagai anggota Paskibraka.',
			'order'    => 60,
		),
		array(
			'key'      => 'foto-juara-cyber-clash-2026',
			'title'    => 'Juara Jember Cyber Clash 2026',
			'jenis'    => 'Prestasi',
			'date'     => '2026-07-17',
			'file'     => 'foto-man-3-jember-juara',
			'fallback' => 7383,
			'desc'     => 'Tim MAN 3 Jember meraih prestasi pada Jember Cyber Clash 2026.',
			'order'    => 70,
		),
		array(
			'key'      => 'foto-matamuda-cover-2026',
			'title'    => 'MATAMUDA 2026/2027 resmi dimulai',
			'jenis'    => 'MATAMUDA',
			'date'     => '2026-07-13',
			'file'     => 'cover-matamuda',
			'fallback' => 7380,
			'desc'     => 'Pembukaan masa taaruf siswa madrasah untuk tahun pelajaran baru.',
			'order'    => 80,
		),
		array(
			'key'      => 'foto-workshop-guru-2026',
			'title'    => 'Workshop penguatan kompetensi guru',
			'jenis'    => 'Pengembangan Guru',
			'date'     => '2026-07-12',
			'file'     => 'foto7',
			'fallback' => 7377,
			'desc'     => 'Penguatan kapasitas pendidik melalui workshop dan pendampingan.',
			'order'    => 90,
		),
		array(
			'key'      => 'foto-sambut-tapel-2026',
			'title'    => 'Sambut tahun pelajaran 2026/2027',
			'jenis'    => 'MATAMUDA',
			'date'     => '2026-07-06',
			'file'     => 'matamuda.png',
			'fallback' => 7372,
			'desc'     => 'Persiapan dan sambutan tahun pelajaran baru di lingkungan MAN 3 Jember.',
			'order'    => 100,
		),
		array(
			'key'      => 'foto-cyber-clash-tim-2026',
			'title'    => 'Tim Trio Hangker Ngangong juara 1',
			'jenis'    => 'Prestasi',
			'date'     => '2026-07-01',
			'file'     => 'cyber-clash-1',
			'fallback' => 7368,
			'desc'     => 'Prestasi tim dalam kompetisi digital tingkat kabupaten.',
			'order'    => 110,
		),
		array(
			'key'      => 'foto-gema-mafesta-2026',
			'title'    => 'GEMA MAFESTA milad ke-49',
			'jenis'    => 'Peringatan',
			'date'     => '2026-06-29',
			'file'     => 'gema-mafesta',
			'fallback' => 7365,
			'desc'     => 'Perayaan milad ke-49 MAN 3 Jember penuh semangat dan kebersamaan.',
			'order'    => 120,
		),
		array(
			'key'      => 'foto-zona-integritas-2026',
			'title'    => 'Penguatan Zona Integritas',
			'jenis'    => 'Zona Integritas',
			'date'     => '2026-06-10',
			'file'     => 'ZI.jpeg',
			'fallback' => 7343,
			'desc'     => 'Komitmen tata kelola bersih melalui penguatan Zona Integritas.',
			'order'    => 130,
		),
		array(
			'key'      => 'foto-narasi-kuliner-2026',
			'title'    => 'Kegiatan narasi kuliner',
			'jenis'    => 'Kegiatan Siswa',
			'date'     => '2026-05-06',
			'file'     => 'narasi-kuliner',
			'fallback' => 7264,
			'desc'     => 'Aktivitas kreatif siswa mengangkat kuliner lokal dan keterampilan.',
			'order'    => 140,
		),
		array(
			'key'      => 'foto-kokurikuler-2026',
			'title'    => 'Kegiatan kokurikuler',
			'jenis'    => 'Kegiatan Siswa',
			'date'     => '2026-04-30',
			'file'     => 'kokurikuler',
			'fallback' => 7235,
			'desc'     => 'Penguatan pembelajaran kokurikuler di luar kelas.',
			'order'    => 150,
		),
		array(
			'key'      => 'foto-apel-pagi-perdana-2026',
			'title'    => 'Apel pagi perdana',
			'jenis'    => 'Upacara',
			'date'     => '2026-04-30',
			'file'     => 'apel-pagi-perdana',
			'fallback' => 7232,
			'desc'     => 'Apel pagi membangun disiplin dan kebersamaan warga madrasah.',
			'order'    => 160,
		),
		array(
			'key'      => 'foto-sertijab-kamad-2026',
			'title'    => 'Sertijab Kepala Madrasah',
			'jenis'    => 'Kelembagaan',
			'date'     => '2026-04-28',
			'file'     => 'sertijab-imam-syahroni',
			'fallback' => 7229,
			'desc'     => 'Serah terima jabatan Kepala Madrasah Imam Syahroni.',
			'order'    => 170,
		),
	);
}

/**
 * Ensure jenis term exists.
 *
 * @param string $name Term name.
 * @return int Term taxonomy ID.
 */
function man3_foto_ensure_jenis( $name ) {
	$name = trim( (string) $name );
	if ( '' === $name ) {
		return 0;
	}
	$term = term_exists( $name, 'man3_jenis_kegiatan' );
	if ( ! $term ) {
		$term = wp_insert_term( $name, 'man3_jenis_kegiatan' );
	}
	if ( is_wp_error( $term ) ) {
		return 0;
	}
	return (int) ( is_array( $term ) ? $term['term_taxonomy_id'] : 0 );
}

/**
 * Run seed of CPT items + page + menu.
 */
function man3_foto_do_seed() {
	foreach ( man3_foto_seed_items() as $row ) {
		$att_id = man3_foto_find_attachment( $row['file'], isset( $row['fallback'] ) ? (int) $row['fallback'] : null );
		if ( ! $att_id ) {
			continue;
		}

		$existing = get_posts(
			array(
				'post_type'      => 'man3_foto',
				'name'           => $row['key'],
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);
		$postarr = array(
			'post_title'   => $row['title'],
			'post_name'    => $row['key'],
			'post_status'  => 'publish',
			'post_type'    => 'man3_foto',
			'post_content' => $row['desc'],
			'menu_order'   => (int) $row['order'],
		);
		if ( $existing ) {
			$postarr['ID'] = (int) $existing[0]->ID;
			$pid           = wp_update_post( $postarr, true );
		} else {
			$pid = wp_insert_post( $postarr, true );
		}
		if ( ! $pid || is_wp_error( $pid ) ) {
			continue;
		}
		update_post_meta( (int) $pid, '_man3_foto_date', $row['date'] );
		set_post_thumbnail( (int) $pid, $att_id );
		$term = term_exists( $row['jenis'], 'man3_jenis_kegiatan' );
		if ( ! $term ) {
			$term = wp_insert_term( $row['jenis'], 'man3_jenis_kegiatan' );
		}
		if ( ! is_wp_error( $term ) ) {
			$term_id = (int) ( is_array( $term ) ? $term['term_id'] : $term );
			wp_set_object_terms( (int) $pid, array( $term_id ), 'man3_jenis_kegiatan', false );
		}
	}

	man3_foto_ensure_page();
	man3_foto_ensure_menu();
}

/**
 * @return int Page ID.
 */
function man3_foto_ensure_page() {
	$page = get_page_by_path( 'galeri' );
	if ( ! $page ) {
		$pid = wp_insert_post(
			array(
				'post_title'   => 'Galeri Foto',
				'post_name'    => 'galeri',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- Galeri Foto MAN 3 Jember -->',
			),
			true
		);
		if ( is_wp_error( $pid ) || ! $pid ) {
			return 0;
		}
		update_post_meta( (int) $pid, '_wp_page_template', 'page-galeri-foto.php' );
		return (int) $pid;
	}
	update_post_meta( $page->ID, '_wp_page_template', 'page-galeri-foto.php' );
	if ( 'publish' !== $page->post_status ) {
		wp_update_post(
			array(
				'ID'          => $page->ID,
				'post_status' => 'publish',
			)
		);
	}
	return (int) $page->ID;
}

/**
 * Pastikan menu Gallery → Foto mengarah ke halaman /galeri/.
 */
function man3_foto_ensure_menu() {
	$page_id = man3_foto_ensure_page();
	if ( ! $page_id ) {
		return;
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$menu_id   = ! empty( $locations['primary'] ) ? (int) $locations['primary'] : 0;
	if ( ! $menu_id ) {
		$menus = wp_get_nav_menus();
		if ( $menus ) {
			$menu_id = (int) $menus[0]->term_id;
		}
	}
	if ( ! $menu_id ) {
		return;
	}

	$items  = wp_get_nav_menu_items( $menu_id );
	$parent = null;
	$child  = null;
	if ( $items ) {
		foreach ( $items as $item ) {
			$title = trim( wp_strip_all_tags( $item->title ) );
			if ( 0 === strcasecmp( $title, 'Gallery' ) || 0 === strcasecmp( $title, 'Galeri' ) ) {
				$parent = $item;
			}
			if ( 0 === strcasecmp( $title, 'Foto' ) || ( (int) $item->object_id === $page_id && 'page' === $item->object ) ) {
				$child = $item;
			}
		}
	}

	if ( ! $parent ) {
		$parent_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => 'Gallery',
				'menu-item-url'    => home_url( '/galeri/' ),
				'menu-item-status' => 'publish',
				'menu-item-type'   => 'custom',
			)
		);
	} else {
		$parent_id = (int) $parent->ID;
	}

	if ( ! $child ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => 'Foto',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent_id,
			)
		);
	} else {
		wp_update_nav_menu_item(
			$menu_id,
			(int) $child->ID,
			array(
				'menu-item-title'     => 'Foto',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent_id,
			)
		);
	}
}

/**
 * Admin auto-seed once.
 */
function man3_foto_run_seed() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$force = isset( $_GET['man3_foto_reseed'] ) && '1' === $_GET['man3_foto_reseed']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $force && get_option( 'man3_foto_seeded_v1' ) ) {
		man3_foto_ensure_page();
		return;
	}
	man3_foto_do_seed();
	update_option( 'man3_foto_seeded_v1', 1, false );
}
add_action( 'admin_init', 'man3_foto_run_seed' );

/**
 * Front reseed for logged-in admin.
 */
function man3_foto_front_seed_trigger() {
	if ( ! isset( $_GET['man3_foto_reseed'] ) || '1' !== $_GET['man3_foto_reseed'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	man3_foto_do_seed();
	update_option( 'man3_foto_seeded_v1', 1, false );
	wp_safe_redirect( home_url( '/galeri/' ) );
	exit;
}
add_action( 'template_redirect', 'man3_foto_front_seed_trigger' );

/**
 * @return array<int,array<string,mixed>>
 */
function man3_foto_get_items() {
	$q = new WP_Query(
		array(
			'post_type'      => 'man3_foto',
			'post_status'    => 'publish',
			'posts_per_page' => 60,
			'orderby'        => array(
				'meta_value' => 'DESC',
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'meta_key'       => '_man3_foto_date',
		)
	);
	$items = array();
	foreach ( $q->posts as $p ) {
		$terms = get_the_terms( $p->ID, 'man3_jenis_kegiatan' );
		$jenis = '';
		$slug  = '';
		if ( $terms && ! is_wp_error( $terms ) ) {
			$jenis = $terms[0]->name;
			$slug  = $terms[0]->slug;
		}
		$date = (string) get_post_meta( $p->ID, '_man3_foto_date', true );
		$thumb = get_the_post_thumbnail_url( $p->ID, 'large' );
		$full  = get_the_post_thumbnail_url( $p->ID, 'full' );
		if ( ! $thumb ) {
			continue;
		}
		$items[] = array(
			'id'      => (int) $p->ID,
			'title'   => get_the_title( $p ),
			'desc'    => wp_strip_all_tags( $p->post_content ),
			'date'    => $date,
			'jenis'   => $jenis,
			'slug'    => $slug,
			'thumb'   => $thumb,
			'full'    => $full ? $full : $thumb,
		);
	}
	return $items;
}

/**
 * Format tanggal kegiatan untuk tampilan.
 *
 * @param string $ymd Y-m-d.
 * @return string
 */
function man3_foto_format_date_label( $ymd ) {
	$ymd = trim( (string) $ymd );
	if ( '' === $ymd ) {
		return '';
	}
	$ts = strtotime( $ymd . ' 12:00:00' );
	if ( ! $ts ) {
		return $ymd;
	}
	if ( function_exists( 'wp_date' ) ) {
		return wp_date( 'j M Y', $ts );
	}
	return date_i18n( 'j M Y', $ts );
}

/**
 * Unique jenis list from items.
 *
 * @param array<int,array<string,mixed>> $items Items.
 * @return array<int,array{name:string,slug:string}>
 */
function man3_foto_jenis_filters( $items ) {
	$map = array();
	foreach ( $items as $it ) {
		if ( empty( $it['slug'] ) ) {
			continue;
		}
		$map[ $it['slug'] ] = $it['jenis'];
	}
	$out = array();
	foreach ( $map as $slug => $name ) {
		$out[] = array(
			'slug' => $slug,
			'name' => $name,
		);
	}
	usort(
		$out,
		static function ( $a, $b ) {
			return strcasecmp( $a['name'], $b['name'] );
		}
	);
	return $out;
}
