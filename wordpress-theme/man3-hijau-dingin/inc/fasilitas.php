<?php
/**
 * Fasilitas madrasah — CPT + halaman /fasilitas/ (data lama arsip WP).
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data seed dari cadangan DB / CPT fasilitas lama (bukan karangan).
 *
 * @return array<int,array<string,mixed>>
 */
function man3_fasilitas_seed_rows() {
	return array(
		array(
			'key'         => 'laboratorium-komputer',
			'nama'        => 'Laboratorium Komputer',
			'deskripsi'   => 'Fasilitas laboratorium komputer di MAN 3 Jember ini sangat penting dalam mendukung proses pendidikan dan pembelajaran yang berfokus pada pengembangan keterampilan di bidang TIK, serta mempersiapkan siswa untuk menghadapi tuntutan teknologi dalam kehidupan sehari-hari dan dunia kerja.',
			'order'       => 10,
			'image_file'  => 'WhatsApp-Image-2024-05-08-at-07.53.32-1.jpeg',
			'legacy_id'   => 6903,
		),
		array(
			'key'         => 'musholla-serba-guna',
			'nama'        => 'Musholla Serba Guna',
			'deskripsi'   => 'Fasilitas Musholla Serba Guna di MAN 3 Jember merupakan sebuah ruangan atau bangunan yang memiliki fungsi ganda, yaitu sebagai tempat beribadah juga sebagai ruang multifungsi untuk kegiatan non-ibadah lainnya. Musholla serba guna ini dirancang untuk memenuhi kebutuhan spiritual siswa, guru, dan karyawan MAN 3 Jember, sambil tetap mendukung berbagai kegiatan sosial dan kegiatan sekolah lainnya.',
			'order'       => 20,
			'image_file'  => 'WhatsApp-Image-2024-06-25-at-11.29.37.jpeg',
			'legacy_id'   => 6906,
		),
	);
}

/**
 * Register CPT — slug `fasilitas` agar post lama (jika masih di DB) ikut terbaca.
 */
function man3_fasilitas_register_cpt() {
	register_post_type(
		'fasilitas',
		array(
			'labels'             => array(
				'name'               => __( 'Fasilitas', 'man3-hijau-dingin' ),
				'singular_name'      => __( 'Fasilitas', 'man3-hijau-dingin' ),
				'add_new'            => __( 'Tambah Fasilitas', 'man3-hijau-dingin' ),
				'add_new_item'       => __( 'Tambah Fasilitas', 'man3-hijau-dingin' ),
				'edit_item'          => __( 'Edit Fasilitas', 'man3-hijau-dingin' ),
				'menu_name'          => __( 'Fasilitas', 'man3-hijau-dingin' ),
				'all_items'          => __( 'Semua Fasilitas', 'man3-hijau-dingin' ),
				'featured_image'     => __( 'Gambar Fasilitas', 'man3-hijau-dingin' ),
				'set_featured_image' => __( 'Set gambar fasilitas', 'man3-hijau-dingin' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-building',
			'menu_position'      => 23,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		)
	);
}
add_action( 'init', 'man3_fasilitas_register_cpt' );

/**
 * Meta aktif.
 */
function man3_fasilitas_meta_box() {
	add_meta_box(
		'man3_fasilitas_meta',
		__( 'Pengaturan Fasilitas', 'man3-hijau-dingin' ),
		'man3_fasilitas_meta_box_render',
		'fasilitas',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_fasilitas_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function man3_fasilitas_meta_box_render( $post ) {
	wp_nonce_field( 'man3_fasilitas_save', 'man3_fasilitas_nonce' );
	$active = get_post_meta( $post->ID, '_man3_fasilitas_active', true );
	if ( '' === $active ) {
		$active = '1';
	}
	?>
	<p>
		<label>
			<input type="checkbox" name="man3_fasilitas_active" value="1" <?php checked( $active, '1' ); ?> />
			<?php esc_html_e( 'Aktif di halaman Fasilitas', 'man3-hijau-dingin' ); ?>
		</label>
	</p>
	<p class="description">
		<?php esc_html_e( 'Nama = judul. Keterangan = editor. Gambar = Gambar Unggulan. Urutan = Atribut Halaman → Order (angka kecil lebih dulu).', 'man3-hijau-dingin' ); ?>
	</p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function man3_fasilitas_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_fasilitas_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_fasilitas_nonce'] ) ), 'man3_fasilitas_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_man3_fasilitas_active', isset( $_POST['man3_fasilitas_active'] ) ? '1' : '0' );
}
add_action( 'save_post_fasilitas', 'man3_fasilitas_meta_save' );

/**
 * Kolom admin.
 *
 * @param array $cols Columns.
 * @return array
 */
function man3_fasilitas_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['man3_fasilitas_thumb']  = __( 'Gambar', 'man3-hijau-dingin' );
			$new['man3_fasilitas_active'] = __( 'Aktif', 'man3-hijau-dingin' );
			$new['man3_fasilitas_order']  = __( 'Urutan', 'man3-hijau-dingin' );
		}
	}
	return $new;
}
add_filter( 'manage_fasilitas_posts_columns', 'man3_fasilitas_columns' );

/**
 * @param string $col Column.
 * @param int    $post_id Post ID.
 */
function man3_fasilitas_column_content( $col, $post_id ) {
	if ( 'man3_fasilitas_thumb' === $col ) {
		$thumb = get_the_post_thumbnail( $post_id, array( 72, 48 ) );
		echo $thumb ? $thumb : '&mdash;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( 'man3_fasilitas_active' === $col ) {
		$active = get_post_meta( $post_id, '_man3_fasilitas_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		echo '1' === $active ? esc_html__( 'Ya', 'man3-hijau-dingin' ) : esc_html__( 'Tidak', 'man3-hijau-dingin' );
	}
	if ( 'man3_fasilitas_order' === $col ) {
		echo esc_html( (string) get_post_field( 'menu_order', $post_id ) );
	}
}
add_action( 'manage_fasilitas_posts_custom_column', 'man3_fasilitas_column_content', 10, 2 );

/**
 * Cari attachment by filename basename.
 *
 * @param string $filename File name.
 * @return int
 */
function man3_fasilitas_find_attachment( $filename ) {
	if ( ! $filename ) {
		return 0;
	}
	global $wpdb;
	$like = '%' . $wpdb->esc_like( $filename );
	$id   = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1",
			$like
		)
	);
	return $id > 0 ? $id : 0;
}

/**
 * Pastikan halaman /fasilitas/.
 *
 * @return int
 */
function man3_fasilitas_ensure_page() {
	$page = get_page_by_path( 'fasilitas' );
	if ( $page instanceof WP_Post ) {
		update_post_meta( $page->ID, '_wp_page_template', 'page-fasilitas.php' );
		if ( 'publish' !== $page->post_status ) {
			wp_update_post(
				array(
					'ID'          => $page->ID,
					'post_status' => 'publish',
					'post_title'  => 'Fasilitas',
				)
			);
		} else {
			wp_update_post(
				array(
					'ID'         => $page->ID,
					'post_title' => 'Fasilitas',
				)
			);
		}
		return (int) $page->ID;
	}

	$id = wp_insert_post(
		array(
			'post_title'   => 'Fasilitas',
			'post_name'    => 'fasilitas',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		),
		true
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	update_post_meta( (int) $id, '_wp_page_template', 'page-fasilitas.php' );
	return (int) $id;
}

/**
 * Pastikan menu Info Madrasah → Fasilitas mengarah ke halaman (jangan pindah posisi).
 */
function man3_fasilitas_ensure_menu() {
	$page_id = man3_fasilitas_ensure_page();
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

	$items   = wp_get_nav_menu_items( $menu_id );
	$matches = array();
	if ( is_array( $items ) ) {
		foreach ( $items as $item ) {
			$title = trim( wp_strip_all_tags( $item->title ) );
			$url   = isset( $item->url ) ? (string) $item->url : '';
			if (
				0 === strcasecmp( $title, 'Fasilitas' )
				|| (int) $item->object_id === $page_id
				|| false !== stripos( $url, '/fasilitas' )
			) {
				$matches[] = $item;
			}
		}
	}

	$keep = null;
	foreach ( $matches as $item ) {
		if ( 'page' === $item->object || (int) $item->object_id === $page_id ) {
			$keep = $item;
			break;
		}
	}
	if ( ! $keep && $matches ) {
		$keep = $matches[0];
	}

	$parent_id = $keep ? (int) $keep->menu_item_parent : 0;
	if ( ! $parent_id && is_array( $items ) ) {
		/* Cari parent Info Madrasah agar tetap submenu. */
		foreach ( $items as $item ) {
			$title = trim( wp_strip_all_tags( $item->title ) );
			if ( 0 === strcasecmp( $title, 'Info Madrasah' ) && 0 === (int) $item->menu_item_parent ) {
				$parent_id = (int) $item->ID;
				break;
			}
		}
	}

	foreach ( $matches as $item ) {
		if ( $keep && (int) $item->ID === (int) $keep->ID ) {
			continue;
		}
		wp_delete_post( (int) $item->ID, true );
	}

	$args = array(
		'menu-item-title'     => 'Fasilitas',
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $page_id,
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
		'menu-item-parent-id' => $parent_id,
	);

	if ( $keep ) {
		wp_update_nav_menu_item( $menu_id, (int) $keep->ID, $args );
	} else {
		wp_update_nav_menu_item( $menu_id, 0, $args );
	}
}

/**
 * Seed / migrate data lama.
 */
function man3_fasilitas_do_seed() {
	foreach ( man3_fasilitas_seed_rows() as $row ) {
		$existing = null;

		/* 1) Post lama by legacy ID. */
		if ( ! empty( $row['legacy_id'] ) ) {
			$legacy = get_post( (int) $row['legacy_id'] );
			if ( $legacy && 'fasilitas' === $legacy->post_type ) {
				$existing = $legacy;
			}
		}

		/* 2) By slug. */
		if ( ! $existing ) {
			$found = get_posts(
				array(
					'post_type'      => 'fasilitas',
					'name'           => $row['key'],
					'posts_per_page' => 1,
					'post_status'    => 'any',
				)
			);
			if ( $found ) {
				$existing = $found[0];
			}
		}

		$postarr = array(
			'post_title'   => $row['nama'],
			'post_name'    => $row['key'],
			'post_status'  => 'publish',
			'post_type'    => 'fasilitas',
			'post_content' => $row['deskripsi'],
			'menu_order'   => (int) $row['order'],
		);

		if ( $existing ) {
			$postarr['ID'] = (int) $existing->ID;
			/* Pertahankan konten lama jika sudah ada teks. */
			if ( trim( (string) $existing->post_content ) ) {
				$postarr['post_content'] = $existing->post_content;
			}
			if ( trim( (string) $existing->post_title ) ) {
				$postarr['post_title'] = $existing->post_title;
			}
			$pid = wp_update_post( $postarr, true );
		} else {
			$pid = wp_insert_post( $postarr, true );
		}

		if ( ! $pid || is_wp_error( $pid ) ) {
			continue;
		}

		$active = get_post_meta( (int) $pid, '_man3_fasilitas_active', true );
		if ( '' === $active ) {
			update_post_meta( (int) $pid, '_man3_fasilitas_active', '1' );
		}

		if ( empty( get_post_thumbnail_id( (int) $pid ) ) && ! empty( $row['image_file'] ) ) {
			$att = man3_fasilitas_find_attachment( $row['image_file'] );
			if ( $att ) {
				set_post_thumbnail( (int) $pid, $att );
			}
		}
	}

	man3_fasilitas_ensure_page();
	man3_fasilitas_ensure_menu();
}

/**
 * Admin bootstrap.
 */
function man3_fasilitas_admin_bootstrap() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	man3_fasilitas_ensure_page();
	man3_fasilitas_ensure_menu();
	if ( ! get_option( 'man3_fasilitas_seeded_v1' ) ) {
		man3_fasilitas_do_seed();
		update_option( 'man3_fasilitas_seeded_v1', 1, false );
	}
}
add_action( 'admin_init', 'man3_fasilitas_admin_bootstrap' );

/**
 * Front reseed.
 */
function man3_fasilitas_front_bootstrap() {
	if ( ! isset( $_GET['man3_fasilitas_reseed'] ) || '1' !== $_GET['man3_fasilitas_reseed'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	delete_option( 'man3_fasilitas_seeded_v1' );
	man3_fasilitas_do_seed();
	update_option( 'man3_fasilitas_seeded_v1', 1, false );
	wp_safe_redirect( home_url( '/fasilitas/' ) );
	exit;
}
add_action( 'template_redirect', 'man3_fasilitas_front_bootstrap' );

/**
 * Item aktif untuk halaman publik.
 *
 * @return array<int,array<string,mixed>>
 */
function man3_fasilitas_get_items() {
	$posts = get_posts(
		array(
			'post_type'              => 'fasilitas',
			'post_status'            => 'publish',
			'posts_per_page'         => 60,
			'orderby'                => 'menu_order title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		)
	);
	$out = array();
	foreach ( $posts as $p ) {
		/* Lewati halaman arsip yang salah tipe (judul Fasilitas + slug fasilitas). */
		if ( 'fasilitas' === $p->post_name && 0 === strcasecmp( $p->post_title, 'Fasilitas' ) && ! trim( wp_strip_all_tags( $p->post_content ) ) ) {
			continue;
		}
		$active = get_post_meta( $p->ID, '_man3_fasilitas_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		if ( '1' !== $active ) {
			continue;
		}
		$thumb = get_the_post_thumbnail_url( $p, 'large' );
		if ( ! $thumb ) {
			$thumb = get_the_post_thumbnail_url( $p, 'medium_large' );
		}
		$raw = $p->post_excerpt ? $p->post_excerpt : wp_strip_all_tags( $p->post_content );
		$out[] = array(
			'id'       => (int) $p->ID,
			'nama'     => get_the_title( $p ),
			'slug'     => $p->post_name,
			'excerpt'  => wp_trim_words( $raw, 40, '…' ),
			'deskripsi'=> trim( wp_strip_all_tags( $p->post_content ) ),
			'thumb'    => $thumb ? $thumb : '',
			'order'    => (int) $p->menu_order,
		);
	}
	return $out;
}
