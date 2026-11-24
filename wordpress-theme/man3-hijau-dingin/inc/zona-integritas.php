<?php
/**
 * Zona Integritas (ZI) — flyer + berita khusus, menu atas ZI.
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parent admin menu.
 */
function man3_zi_admin_menu() {
	add_menu_page(
		__( 'ZI — Zona Integritas', 'man3-hijau-dingin' ),
		__( 'ZI', 'man3-hijau-dingin' ),
		'edit_posts',
		'man3-zi',
		'man3_zi_admin_landing',
		'dashicons-shield-alt',
		24
	);
}
add_action( 'admin_menu', 'man3_zi_admin_menu' );

/**
 * Teks default SETIA — lima paragraf (pisah baris kosong).
 *
 * @return string
 */
function man3_zi_setia_default_text() {
	return "Sinergis berarti kepala madrasah, guru, tenaga kependidikan, siswa, orang tua, komite, dan mitra Zona Integritas MAN 3 Jember bekerja dalam satu arah. Setiap pihak tahu perannya dan saling menguatkan agar program ZI berjalan bersama, bukan sendiri-sendiri.\n\nEfektif berarti setiap layanan dan kegiatan madrasah tepat sasaran, tertib, dan bisa dipertanggungjawabkan. Waktu, tenaga, dan anggaran dipakai untuk hasil yang nyata bagi siswa dan warga madrasah.\n\nToleran berarti warga madrasah saling menghargai perbedaan pendapat, latar belakang, dan cara beribadah yang tetap dalam koridor madrasah. Suasana belajar dijaga aman, santun, dan terbuka bagi seluruh stakeholder.\n\nInovatif berarti guru, tenaga kependidikan, dan siswa berani memperbaiki cara kerja dan cara belajar. Pembaruan itu memudahkan layanan, memperkuat integritas, dan membuat program ZI lebih mudah diikuti.\n\nAmanah berarti jabatan, anggaran, dan kepercayaan masyarakat dijaga dengan jujur. Kepala madrasah, guru, tenaga kependidikan, siswa, orang tua, dan komite menolak gratifikasi serta penyalahgunaan wewenang, sehingga MAN 3 Jember tetap bersih, melayani, dan bebas dari korupsi.";
}

/**
 * Ambil teks SETIA dari opsi; kosong = tidak tampil.
 *
 * @return string
 */
function man3_zi_get_setia_text() {
	$raw = get_option( 'man3_zi_setia_text', null );
	if ( null === $raw ) {
		return man3_zi_setia_default_text();
	}
	return trim( (string) $raw );
}

/**
 * Render HTML SETIA: tiap blok (pisah baris kosong) jadi <p>; kata kunci dibold.
 *
 * @param string $text Teks mentah dari admin.
 */
function man3_zi_render_setia( $text ) {
	$text = trim( (string) $text );
	if ( '' === $text ) {
		return;
	}
	$parts = preg_split( '/\R\s*\R/u', $text );
	if ( ! is_array( $parts ) ) {
		$parts = array( $text );
	}
	$keys = array( 'Sinergis', 'Efektif', 'Toleran', 'Inovatif', 'Amanah' );
	echo '<div class="zi-setia">';
	foreach ( $parts as $part ) {
		$part = trim( (string) $part );
		if ( '' === $part ) {
			continue;
		}
		$bolded = false;
		foreach ( $keys as $key ) {
			if ( 0 === stripos( $part, $key ) ) {
				$rest = substr( $part, strlen( $key ) );
				echo '<p><strong>' . esc_html( $key ) . '</strong>' . esc_html( $rest ) . '</p>';
				$bolded = true;
				break;
			}
		}
		if ( ! $bolded ) {
			echo '<p>' . esc_html( $part ) . '</p>';
		}
	}
	echo '</div>';
}

/**
 * Sekali: ganti teks SETIA lama (satu paragraf) ke lima paragraf baru.
 */
function man3_zi_migrate_setia_v2() {
	if ( get_option( 'man3_zi_setia_text_v2' ) ) {
		return;
	}
	$cur = get_option( 'man3_zi_setia_text', null );
	$old_start = 'SETIA adalah semangat Zona Integritas';
	if ( null === $cur || 0 === strpos( (string) $cur, $old_start ) ) {
		update_option( 'man3_zi_setia_text', man3_zi_setia_default_text(), false );
	}
	update_option( 'man3_zi_setia_text_v2', 1, false );
}
add_action( 'init', 'man3_zi_migrate_setia_v2', 25 );

/**
 * Simpan teks SETIA dari form admin landing ZI.
 */
function man3_zi_save_setia_text() {
	if ( ! isset( $_POST['man3_zi_setia_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_zi_setia_nonce'] ) ), 'man3_zi_setia_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$text = isset( $_POST['man3_zi_setia_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['man3_zi_setia_text'] ) ) : '';
	update_option( 'man3_zi_setia_text', $text, false );
	add_settings_error( 'man3_zi_setia', 'man3_zi_setia_saved', __( 'Penjelasan SETIA disimpan.', 'man3-hijau-dingin' ), 'updated' );
}
add_action( 'admin_init', 'man3_zi_save_setia_text' );

/**
 * Landing singkat di sidebar ZI (+ kotak teks SETIA).
 */
function man3_zi_admin_landing() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$page = home_url( '/zi/' );
	$setia = man3_zi_get_setia_text();
	if ( null === get_option( 'man3_zi_setia_text', null ) ) {
		$setia = man3_zi_setia_default_text();
	} else {
		$setia = (string) get_option( 'man3_zi_setia_text', '' );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'ZI — Zona Integritas', 'man3-hijau-dingin' ); ?></h1>
		<p><?php esc_html_e( 'Kelola flyer dan berita khusus Zona Integritas. Tampil di halaman publik /zi/ dan menu atas ZI.', 'man3-hijau-dingin' ); ?></p>
		<ul>
			<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=man3_zi_flyer' ) ); ?>"><?php esc_html_e( 'Flyer ZI', 'man3-hijau-dingin' ); ?></a> — <?php esc_html_e( 'gambar, urutan, aktif', 'man3-hijau-dingin' ); ?></li>
			<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=man3_zi_berita' ) ); ?>"><?php esc_html_e( 'Berita ZI', 'man3-hijau-dingin' ); ?></a> — <?php esc_html_e( 'judul, isi, gambar, tanggal', 'man3-hijau-dingin' ); ?></li>
			<li><a href="<?php echo esc_url( $page ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Lihat halaman publik', 'man3-hijau-dingin' ); ?></a></li>
		</ul>

		<hr />
		<h2><?php esc_html_e( 'Penjelasan slogan SETIA', 'man3-hijau-dingin' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Lima paragraf (Sinergis, Efektif, Toleran, Inovatif, Amanah) di bawah judul halaman ZI, sebelum flyer. Pisahkan tiap paragraf dengan baris kosong. Kosongkan agar tidak tampil di situs.', 'man3-hijau-dingin' ); ?></p>
		<?php settings_errors( 'man3_zi_setia' ); ?>
		<form method="post" action="">
			<?php wp_nonce_field( 'man3_zi_setia_save', 'man3_zi_setia_nonce' ); ?>
			<textarea
				name="man3_zi_setia_text"
				id="man3_zi_setia_text"
				rows="14"
				class="large-text"
				style="max-width: 52rem;"
			><?php echo esc_textarea( $setia ); ?></textarea>
			<p>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Simpan SETIA', 'man3-hijau-dingin' ); ?></button>
			</p>
		</form>
	</div>
	<?php
}

/**
 * Register CPTs.
 */
function man3_zi_register_cpts() {
	register_post_type(
		'man3_zi_flyer',
		array(
			'labels'             => array(
				'name'          => __( 'Flyer ZI', 'man3-hijau-dingin' ),
				'singular_name' => __( 'Flyer ZI', 'man3-hijau-dingin' ),
				'add_new_item'  => __( 'Tambah Flyer ZI', 'man3-hijau-dingin' ),
				'edit_item'     => __( 'Edit Flyer ZI', 'man3-hijau-dingin' ),
				'menu_name'     => __( 'Flyer ZI', 'man3-hijau-dingin' ),
				'all_items'     => __( 'Flyer ZI', 'man3-hijau-dingin' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'man3-zi',
			'menu_icon'          => 'dashicons-format-image',
			'supports'           => array( 'title', 'thumbnail', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		)
	);

	register_post_type(
		'man3_zi_berita',
		array(
			'labels'             => array(
				'name'               => __( 'Berita ZI', 'man3-hijau-dingin' ),
				'singular_name'      => __( 'Berita ZI', 'man3-hijau-dingin' ),
				'add_new'            => __( 'Tambah Berita', 'man3-hijau-dingin' ),
				'add_new_item'       => __( 'Tambah Berita ZI', 'man3-hijau-dingin' ),
				'edit_item'          => __( 'Edit Berita ZI', 'man3-hijau-dingin' ),
				'menu_name'          => __( 'Berita ZI', 'man3-hijau-dingin' ),
				'all_items'          => __( 'Berita ZI', 'man3-hijau-dingin' ),
				'featured_image'     => __( 'Gambar Unggulan', 'man3-hijau-dingin' ),
				'set_featured_image' => __( 'Set gambar unggulan', 'man3-hijau-dingin' ),
			),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'man3-zi',
			'menu_icon'          => 'dashicons-media-document',
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
			'has_archive'        => false,
			'rewrite'            => array(
				'slug'       => 'zi-berita',
				'with_front' => false,
			),
			'show_in_rest'       => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		)
	);
}
add_action( 'init', 'man3_zi_register_cpts' );

/**
 * Meta aktif untuk flyer.
 */
function man3_zi_flyer_meta_box() {
	add_meta_box(
		'man3_zi_flyer_meta',
		__( 'Pengaturan Flyer', 'man3-hijau-dingin' ),
		'man3_zi_flyer_meta_box_render',
		'man3_zi_flyer',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_zi_flyer_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function man3_zi_flyer_meta_box_render( $post ) {
	wp_nonce_field( 'man3_zi_flyer_save', 'man3_zi_flyer_nonce' );
	$active = get_post_meta( $post->ID, '_man3_zi_active', true );
	if ( '' === $active ) {
		$active = '1';
	}
	?>
	<p>
		<label>
			<input type="checkbox" name="man3_zi_active" value="1" <?php checked( $active, '1' ); ?> />
			<?php esc_html_e( 'Aktif di halaman ZI', 'man3-hijau-dingin' ); ?>
		</label>
	</p>
	<p class="description">
		<?php esc_html_e( 'Wajib: Gambar Unggulan. Judul hanya untuk admin. Urutan: Atribut Halaman → Order (angka kecil lebih dulu).', 'man3-hijau-dingin' ); ?>
	</p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function man3_zi_flyer_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_zi_flyer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_zi_flyer_nonce'] ) ), 'man3_zi_flyer_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_man3_zi_active', isset( $_POST['man3_zi_active'] ) ? '1' : '0' );
}
add_action( 'save_post_man3_zi_flyer', 'man3_zi_flyer_meta_save' );

/**
 * Meta aktif untuk berita (opsional nonaktif tanpa unpublish).
 */
function man3_zi_berita_meta_box() {
	add_meta_box(
		'man3_zi_berita_meta',
		__( 'Pengaturan Berita ZI', 'man3-hijau-dingin' ),
		'man3_zi_berita_meta_box_render',
		'man3_zi_berita',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_zi_berita_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function man3_zi_berita_meta_box_render( $post ) {
	wp_nonce_field( 'man3_zi_berita_save', 'man3_zi_berita_nonce' );
	$active = get_post_meta( $post->ID, '_man3_zi_active', true );
	if ( '' === $active ) {
		$active = '1';
	}
	?>
	<p>
		<label>
			<input type="checkbox" name="man3_zi_active" value="1" <?php checked( $active, '1' ); ?> />
			<?php esc_html_e( 'Aktif di daftar ZI', 'man3-hijau-dingin' ); ?>
		</label>
	</p>
	<p class="description">
		<?php esc_html_e( 'Isi judul, konten, tanggal, dan Gambar Unggulan. Publish agar muncul di /zi/.', 'man3-hijau-dingin' ); ?>
	</p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function man3_zi_berita_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_zi_berita_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_zi_berita_nonce'] ) ), 'man3_zi_berita_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_man3_zi_active', isset( $_POST['man3_zi_active'] ) ? '1' : '0' );
}
add_action( 'save_post_man3_zi_berita', 'man3_zi_berita_meta_save' );

/**
 * Kolom admin flyer.
 *
 * @param array $cols Columns.
 * @return array
 */
function man3_zi_flyer_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['man3_zi_thumb']  = __( 'Gambar', 'man3-hijau-dingin' );
			$new['man3_zi_active'] = __( 'Aktif', 'man3-hijau-dingin' );
			$new['man3_zi_order']  = __( 'Urutan', 'man3-hijau-dingin' );
		}
	}
	return $new;
}
add_filter( 'manage_man3_zi_flyer_posts_columns', 'man3_zi_flyer_columns' );

/**
 * @param string $col Column.
 * @param int    $post_id Post ID.
 */
function man3_zi_flyer_column_content( $col, $post_id ) {
	if ( 'man3_zi_thumb' === $col ) {
		$thumb = get_the_post_thumbnail( $post_id, array( 72, 48 ) );
		echo $thumb ? $thumb : '&mdash;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( 'man3_zi_active' === $col ) {
		$active = get_post_meta( $post_id, '_man3_zi_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		echo '1' === $active ? esc_html__( 'Ya', 'man3-hijau-dingin' ) : esc_html__( 'Tidak', 'man3-hijau-dingin' );
	}
	if ( 'man3_zi_order' === $col ) {
		echo esc_html( (string) get_post_field( 'menu_order', $post_id ) );
	}
}
add_action( 'manage_man3_zi_flyer_posts_custom_column', 'man3_zi_flyer_column_content', 10, 2 );

/**
 * Kolom admin berita.
 *
 * @param array $cols Columns.
 * @return array
 */
function man3_zi_berita_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['man3_zi_thumb']  = __( 'Gambar', 'man3-hijau-dingin' );
			$new['man3_zi_active'] = __( 'Aktif', 'man3-hijau-dingin' );
		}
	}
	return $new;
}
add_filter( 'manage_man3_zi_berita_posts_columns', 'man3_zi_berita_columns' );

/**
 * @param string $col Column.
 * @param int    $post_id Post ID.
 */
function man3_zi_berita_column_content( $col, $post_id ) {
	if ( 'man3_zi_thumb' === $col ) {
		$thumb = get_the_post_thumbnail( $post_id, array( 48, 64 ) );
		echo $thumb ? $thumb : '&mdash;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( 'man3_zi_active' === $col ) {
		$active = get_post_meta( $post_id, '_man3_zi_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		echo '1' === $active ? esc_html__( 'Ya', 'man3-hijau-dingin' ) : esc_html__( 'Tidak', 'man3-hijau-dingin' );
	}
}
add_action( 'manage_man3_zi_berita_posts_custom_column', 'man3_zi_berita_column_content', 10, 2 );

/**
 * Pastikan halaman /zi/.
 *
 * @return int
 */
function man3_zi_ensure_page() {
	$page = get_page_by_path( 'zi' );
	if ( $page instanceof WP_Post ) {
		update_post_meta( $page->ID, '_wp_page_template', 'page-zona-integritas.php' );
		if ( 'publish' !== $page->post_status ) {
			wp_update_post(
				array(
					'ID'          => $page->ID,
					'post_status' => 'publish',
				)
			);
		}
		update_option( 'man3_zi_page_ok', 1, false );
		return (int) $page->ID;
	}

	$id = wp_insert_post(
		array(
			'post_title'   => 'ZI',
			'post_name'    => 'zi',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		),
		true
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	update_post_meta( (int) $id, '_wp_page_template', 'page-zona-integritas.php' );
	update_option( 'man3_zi_page_ok', 1, false );
	return (int) $id;
}

/**
 * Sisipkan menu atas ZI setelah Berita, sebelum News Mantiger.
 */
function man3_zi_ensure_menu() {
	$page_id = man3_zi_ensure_page();
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

	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		$items = array();
	}

	$zi_item     = null;
	$top_level   = array();
	foreach ( $items as $item ) {
		if ( 0 !== (int) $item->menu_item_parent ) {
			continue;
		}
		$title = trim( wp_strip_all_tags( $item->title ) );
		if ( 'ZI' === $title || ( (int) $item->object_id === $page_id && 'page' === $item->object ) ) {
			$zi_item = $item;
		}
		$top_level[] = $item;
	}

	$args = array(
		'menu-item-title'     => 'ZI',
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $page_id,
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
		'menu-item-parent-id' => 0,
	);

	if ( $zi_item ) {
		$zi_id = wp_update_nav_menu_item( $menu_id, (int) $zi_item->ID, $args );
	} else {
		$zi_id = wp_update_nav_menu_item( $menu_id, 0, $args );
	}
	if ( is_wp_error( $zi_id ) || ! $zi_id ) {
		return;
	}

	/* Susun ulang: ZI tepat setelah Berita, sebelum News Mantiger. */
	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		return;
	}
	$tops = array();
	foreach ( $items as $item ) {
		if ( 0 === (int) $item->menu_item_parent ) {
			$tops[] = $item;
		}
	}

	$ordered = array();
	$zi_obj  = null;
	$others  = array();
	foreach ( $tops as $item ) {
		$title = trim( wp_strip_all_tags( $item->title ) );
		if ( 'ZI' === $title || (int) $item->ID === (int) $zi_id ) {
			$zi_obj = $item;
			continue;
		}
		$others[] = $item;
	}
	if ( ! $zi_obj ) {
		return;
	}

	$inserted = false;
	foreach ( $others as $item ) {
		$title = trim( wp_strip_all_tags( $item->title ) );
		if ( ! $inserted && 0 === strcasecmp( $title, 'News Mantiger' ) ) {
			$ordered[] = $zi_obj;
			$inserted  = true;
		}
		$ordered[] = $item;
		if ( ! $inserted && 0 === strcasecmp( $title, 'Berita' ) ) {
			$ordered[] = $zi_obj;
			$inserted  = true;
		}
	}
	if ( ! $inserted ) {
		$ordered[] = $zi_obj;
	}

	$pos = 1;
	foreach ( $ordered as $item ) {
		wp_update_post(
			array(
				'ID'         => (int) $item->ID,
				'menu_order' => $pos,
			)
		);
		++$pos;
	}
}

/**
 * Cari attachment poster Zona Integritas dari media / popup.
 *
 * @return int
 */
function man3_zi_find_seed_attachment_id() {
	if ( function_exists( 'man3_popup_get_settings' ) ) {
		$s = man3_popup_get_settings();
		foreach ( (array) ( $s['slides'] ?? array() ) as $row ) {
			$id = absint( $row['image_id'] ?? 0 );
			if ( $id <= 0 ) {
				continue;
			}
			$file = get_attached_file( $id );
			$url  = wp_get_attachment_url( $id );
			$hay  = strtolower( (string) $file . ' ' . (string) $url . ' ' . get_the_title( $id ) );
			if ( false !== strpos( $hay, 'zona' ) || false !== strpos( $hay, 'integritas' ) ) {
				return $id;
			}
		}
		foreach ( (array) ( $s['slides'] ?? array() ) as $row ) {
			$id = absint( $row['image_id'] ?? 0 );
			if ( $id > 0 ) {
				return $id;
			}
		}
	}

	$q = new WP_Query(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 5,
			's'              => 'Zona Integritas',
			'fields'         => 'ids',
		)
	);
	if ( $q->posts ) {
		return (int) $q->posts[0];
	}
	return 0;
}

/**
 * Seed satu flyer contoh.
 */
function man3_zi_seed_flyer() {
	if ( get_option( 'man3_zi_flyer_seeded_v1' ) ) {
		return;
	}
	$existing = get_posts(
		array(
			'post_type'      => 'man3_zi_flyer',
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);
	if ( $existing ) {
		update_option( 'man3_zi_flyer_seeded_v1', 1, false );
		return;
	}

	$att = man3_zi_find_seed_attachment_id();
	$pid = wp_insert_post(
		array(
			'post_type'   => 'man3_zi_flyer',
			'post_status' => 'publish',
			'post_title'  => 'Flyer Zona Integritas',
			'menu_order'  => 10,
		),
		true
	);
	if ( ! $pid || is_wp_error( $pid ) ) {
		return;
	}
	update_post_meta( (int) $pid, '_man3_zi_active', '1' );
	update_post_meta( (int) $pid, '_man3_zi_seed_key', 'zona-integritas-default' );
	if ( $att ) {
		set_post_thumbnail( (int) $pid, $att );
	}
	update_option( 'man3_zi_flyer_seeded_v1', 1, false );
}

/**
 * Seed satu berita contoh agar daftar + single bisa dicek.
 */
function man3_zi_seed_berita() {
	if ( get_option( 'man3_zi_berita_seeded_v1' ) ) {
		return;
	}
	$existing = get_posts(
		array(
			'post_type'      => 'man3_zi_berita',
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);
	if ( $existing ) {
		update_option( 'man3_zi_berita_seeded_v1', 1, false );
		return;
	}

	$att = man3_zi_find_seed_attachment_id();
	$pid = wp_insert_post(
		array(
			'post_type'    => 'man3_zi_berita',
			'post_status'  => 'publish',
			'post_title'   => 'Komitmen Zona Integritas MAN 3 Jember',
			'post_content' => "<p>MAN 3 Jember berkomitmen membangun Zona Integritas menuju Wilayah Bebas dari Korupsi (WBK) dan Wilayah Birokrasi Bersih dan Melayani (WBBM).</p>\n<p>Informasi dan kegiatan terkait Zona Integritas akan diumumkan melalui halaman ZI ini. Admin dapat menambah, mengubah, atau menonaktifkan berita melalui menu <strong>ZI → Berita ZI</strong>.</p>",
			'post_excerpt' => 'Komitmen madrasah membangun Zona Integritas menuju WBK/WBBM.',
		),
		true
	);
	if ( ! $pid || is_wp_error( $pid ) ) {
		return;
	}
	update_post_meta( (int) $pid, '_man3_zi_active', '1' );
	update_post_meta( (int) $pid, '_man3_zi_seed_key', 'zona-integritas-berita-default' );
	if ( $att ) {
		set_post_thumbnail( (int) $pid, $att );
	}
	update_option( 'man3_zi_berita_seeded_v1', 1, false );
}

/**
 * Bootstrap admin: page, menu, seed, rewrite.
 */
function man3_zi_admin_bootstrap() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	man3_zi_ensure_page();
	man3_zi_ensure_menu();
	man3_zi_seed_flyer();
	man3_zi_seed_berita();
	if ( ! get_option( 'man3_zi_rewrite_flushed_v1' ) ) {
		flush_rewrite_rules( false );
		update_option( 'man3_zi_rewrite_flushed_v1', 1, false );
	}
}
add_action( 'admin_init', 'man3_zi_admin_bootstrap' );

/**
 * Front reseed / ensure.
 */
function man3_zi_front_bootstrap() {
	if ( ! isset( $_GET['man3_zi_reseed'] ) || '1' !== $_GET['man3_zi_reseed'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	delete_option( 'man3_zi_flyer_seeded_v1' );
	delete_option( 'man3_zi_berita_seeded_v1' );
	man3_zi_ensure_page();
	man3_zi_ensure_menu();
	man3_zi_seed_flyer();
	man3_zi_seed_berita();
	flush_rewrite_rules( false );
	wp_safe_redirect( home_url( '/zi/' ) );
	exit;
}
add_action( 'template_redirect', 'man3_zi_front_bootstrap' );

/**
 * Flyer aktif untuk halaman publik.
 *
 * @return array<int,array<string,mixed>>
 */
function man3_zi_get_flyers() {
	$posts = get_posts(
		array(
			'post_type'      => 'man3_zi_flyer',
			'post_status'    => 'publish',
			'posts_per_page' => 40,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);
	$out = array();
	foreach ( $posts as $p ) {
		$active = get_post_meta( $p->ID, '_man3_zi_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		if ( '1' !== $active ) {
			continue;
		}
		$url = get_the_post_thumbnail_url( $p, 'large' );
		if ( ! $url ) {
			$url = get_the_post_thumbnail_url( $p, 'full' );
		}
		if ( ! $url ) {
			continue;
		}
		$out[] = array(
			'id'    => (int) $p->ID,
			'title' => get_the_title( $p ),
			'url'   => $url,
			'full'  => get_the_post_thumbnail_url( $p, 'full' ) ? get_the_post_thumbnail_url( $p, 'full' ) : $url,
		);
	}
	return $out;
}

/**
 * Berita ZI aktif.
 *
 * @return array<int,array<string,mixed>>
 */
function man3_zi_get_berita() {
	$q = new WP_Query(
		array(
			'post_type'              => 'man3_zi_berita',
			'post_status'            => 'publish',
			'posts_per_page'         => 40,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		)
	);
	$items = array();
	foreach ( $q->posts as $post ) {
		$active = get_post_meta( $post->ID, '_man3_zi_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		if ( '1' !== $active ) {
			continue;
		}
		$thumb = get_the_post_thumbnail_url( $post, 'medium_large' );
		$raw   = $post->post_excerpt ? $post->post_excerpt : wp_strip_all_tags( $post->post_content );
		$items[] = array(
			'id'       => (int) $post->ID,
			'title'    => get_the_title( $post ),
			'excerpt'  => wp_trim_words( $raw, 36, '…' ),
			'date'     => get_the_date( '', $post ),
			'date_iso' => get_the_date( DATE_W3C, $post ),
			'thumb'    => $thumb ? $thumb : '',
			'url'      => get_permalink( $post ),
		);
	}
	wp_reset_postdata();
	return $items;
}
