<?php
/**
 * CPT + seed + halaman Struktur Organisasi.
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register CPT anggota struktur (hierarki via post_parent).
 */
function man3_org_register_cpt() {
	register_post_type(
		'man3_org',
		array(
			'labels'              => array(
				'name'          => __( 'Struktur Organisasi', 'man3-hijau-dingin' ),
				'singular_name' => __( 'Anggota', 'man3-hijau-dingin' ),
				'add_new_item'  => __( 'Tambah Anggota', 'man3-hijau-dingin' ),
				'edit_item'     => __( 'Edit Anggota', 'man3-hijau-dingin' ),
				'menu_name'     => __( 'Struktur Org.', 'man3-hijau-dingin' ),
			),
			'public'              => true,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-networking',
			'menu_position'       => 22,
			'hierarchical'        => true,
			'supports'            => array( 'title', 'thumbnail', 'page-attributes', 'excerpt' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'show_in_rest'        => true,
		)
	);
}
add_action( 'init', 'man3_org_register_cpt' );

/**
 * Meta jabatan.
 */
function man3_org_meta_box() {
	add_meta_box(
		'man3_org_jabatan',
		__( 'Jabatan', 'man3-hijau-dingin' ),
		'man3_org_meta_box_render',
		'man3_org',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_org_meta_box' );

function man3_org_meta_box_render( $post ) {
	wp_nonce_field( 'man3_org_save', 'man3_org_nonce' );
	$jabatan = get_post_meta( $post->ID, '_man3_org_jabatan', true );
	echo '<p><label for="man3_org_jabatan_field"><strong>' . esc_html__( 'Jabatan / peran', 'man3-hijau-dingin' ) . '</strong></label></p>';
	echo '<input type="text" class="widefat" id="man3_org_jabatan_field" name="man3_org_jabatan" value="' . esc_attr( $jabatan ) . '" placeholder="Contoh: Waka Kurikulum" />';
	echo '<p class="description">' . esc_html__( 'Foto: set Featured Image (Gambar Unggulan). Kosong = placeholder di situs.', 'man3-hijau-dingin' ) . '</p>';
}

function man3_org_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_org_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_org_nonce'] ) ), 'man3_org_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['man3_org_jabatan'] ) ) {
		update_post_meta( $post_id, '_man3_org_jabatan', sanitize_text_field( wp_unslash( $_POST['man3_org_jabatan'] ) ) );
	}
}
add_action( 'save_post_man3_org', 'man3_org_meta_save' );

/**
 * Kolom admin: jabatan + foto.
 */
function man3_org_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['man3_jabatan'] = __( 'Jabatan', 'man3-hijau-dingin' );
			$new['man3_foto']    = __( 'Foto', 'man3-hijau-dingin' );
		}
	}
	return $new;
}
add_filter( 'manage_man3_org_posts_columns', 'man3_org_columns' );

function man3_org_column_content( $col, $post_id ) {
	if ( 'man3_jabatan' === $col ) {
		echo esc_html( get_post_meta( $post_id, '_man3_org_jabatan', true ) );
	}
	if ( 'man3_foto' === $col ) {
		if ( has_post_thumbnail( $post_id ) ) {
			echo get_the_post_thumbnail( $post_id, array( 48, 48 ) );
		} else {
			echo '<span style="color:#888">' . esc_html__( 'Placeholder', 'man3-hijau-dingin' ) . '</span>';
		}
	}
}
add_action( 'manage_man3_org_posts_custom_column', 'man3_org_column_content', 10, 2 );

/**
 * Data seed TP 2026–2027 (tanpa foto).
 * parent = slug key induk; order = menu_order.
 */
function man3_org_seed_tree() {
	return array(
		array(
			'key'     => 'imam-syahroni',
			'name'    => 'IMAM SYAHRONI',
			'jabatan' => 'Kepala Madrasah',
			'parent'  => '',
			'order'   => 1,
		),
		array(
			'key'     => 'kh-abdullah-ubaid',
			'name'    => 'KH. ABDULLAH UBAID',
			'jabatan' => 'Komite Madrasah',
			'parent'  => 'imam-syahroni',
			'order'   => 10,
		),
		array(
			'key'     => 'rudi-hartono',
			'name'    => 'RUDI HARTONO',
			'jabatan' => 'Kepala Tata Usaha',
			'parent'  => 'imam-syahroni',
			'order'   => 11,
		),
		array(
			'key'     => 'supriyadi',
			'name'    => 'SUPRIYADI',
			'jabatan' => 'Waka Sarpras',
			'parent'  => 'imam-syahroni',
			'order'   => 20,
		),
		array(
			'key'     => 'bibit-wahyuningsih',
			'name'    => 'BIBIT WAHYUNINGSIH',
			'jabatan' => 'Waka Kesiswaan',
			'parent'  => 'imam-syahroni',
			'order'   => 21,
		),
		array(
			'key'     => 'usman-affan-irdoi',
			'name'    => 'USMAN AFFAN IRDOI',
			'jabatan' => 'Waka Kurikulum',
			'parent'  => 'imam-syahroni',
			'order'   => 22,
		),
		array(
			'key'     => 'zidni-ilma',
			'name'    => 'ZIDNI ILMA',
			'jabatan' => 'Waka Humas',
			'parent'  => 'imam-syahroni',
			'order'   => 23,
		),
		// Sarpras
		array( 'key' => 'muh-muhrizin', 'name' => 'MUH. MUHRIZIN', 'jabatan' => 'Koordinator BK', 'parent' => 'supriyadi', 'order' => 30 ),
		array( 'key' => 'slamet-sahrudin', 'name' => 'SLAMET SAHRUDIN', 'jabatan' => 'Kepala Perpustakaan', 'parent' => 'supriyadi', 'order' => 31 ),
		array( 'key' => 'shodiq-lab-biologi', 'name' => 'M. SHODIQ AR.', 'jabatan' => 'Kepala Lab. Biologi', 'parent' => 'supriyadi', 'order' => 32 ),
		array( 'key' => 'kasworo', 'name' => 'KASWORO', 'jabatan' => 'Kepala Lab. Kimia', 'parent' => 'supriyadi', 'order' => 33 ),
		array( 'key' => 'hariyanto', 'name' => 'HARIYANTO', 'jabatan' => 'Kepala Lab. Fisika', 'parent' => 'supriyadi', 'order' => 34 ),
		// Kesiswaan
		array( 'key' => 'fathkur-rozi', 'name' => 'M. FATHKUR ROZI', 'jabatan' => 'Koord. Ekstrakurikuler', 'parent' => 'bibit-wahyuningsih', 'order' => 40 ),
		array( 'key' => 'ririn-indrayati', 'name' => 'RIRIN INDRAYATI', 'jabatan' => 'Pembina OSIS-MPK', 'parent' => 'bibit-wahyuningsih', 'order' => 41 ),
		array( 'key' => 'moh-muhtadi', 'name' => 'MOH. MUHTADI', 'jabatan' => 'Koord. Tata Tertib', 'parent' => 'bibit-wahyuningsih', 'order' => 42 ),
		array( 'key' => 'eli-rachmawati', 'name' => 'ELI RACHMAWATI', 'jabatan' => 'Koord. UKS', 'parent' => 'bibit-wahyuningsih', 'order' => 43 ),
		array( 'key' => 'budi-santosa', 'name' => 'BUDI SANTOSA', 'jabatan' => 'Ketua TPM', 'parent' => 'bibit-wahyuningsih', 'order' => 44 ),
		// Kurikulum
		array( 'key' => 'heni-hidayati', 'name' => 'HENI HIDAYATI', 'jabatan' => 'Ketua Tim Bina Prestasi', 'parent' => 'usman-affan-irdoi', 'order' => 50 ),
		array( 'key' => 'aliman', 'name' => 'ALIMAN', 'jabatan' => 'Koord. Tahfidz', 'parent' => 'usman-affan-irdoi', 'order' => 51 ),
		array( 'key' => 'siska-anggun', 'name' => 'SISKA ANGGUN PUJI L.', 'jabatan' => 'Ketua Keterampilan', 'parent' => 'usman-affan-irdoi', 'order' => 52 ),
		array( 'key' => 'murad-naser', 'name' => 'MURAD NASER', 'jabatan' => 'Koord. Prodistik & L. Kom', 'parent' => 'siska-anggun', 'order' => 53 ),
		array( 'key' => 'naimatul-m', 'name' => 'NAIMATUL M.', 'jabatan' => 'Koord. Opt. Komputer', 'parent' => 'siska-anggun', 'order' => 54 ),
		array( 'key' => 'sri-suharwati', 'name' => 'SRI SUHARWATI', 'jabatan' => 'Koord. Tata Boga', 'parent' => 'siska-anggun', 'order' => 55 ),
		array( 'key' => 'nadiya-elmida', 'name' => 'NADIYA ELMIDA', 'jabatan' => 'Koord. Tata Busana', 'parent' => 'siska-anggun', 'order' => 56 ),
		array( 'key' => 'siti-nurhasanah', 'name' => 'SITI NURHASANAH', 'jabatan' => 'Koord. Tata Rias', 'parent' => 'siska-anggun', 'order' => 57 ),
		// Humas
		array( 'key' => 'makrus-nur-a', 'name' => 'MAKRUS NUR A.', 'jabatan' => 'Koord. Tim Medsos', 'parent' => 'zidni-ilma', 'order' => 60 ),
		array( 'key' => 'ahmad-afandi', 'name' => 'AHMAD AFANDI', 'jabatan' => 'Pengelola Web & IT', 'parent' => 'zidni-ilma', 'order' => 61 ),
		array( 'key' => 'shodiq-pertanian', 'name' => 'M. SHODIQ AR.', 'jabatan' => 'Pertanian Milenial', 'parent' => 'zidni-ilma', 'order' => 62 ),
		array( 'key' => 'ari-ramdan', 'name' => 'ARI RAMDAN S.', 'jabatan' => 'Podcast & Livestreaming', 'parent' => 'zidni-ilma', 'order' => 63 ),
	);
}

/**
 * Seed CPT + halaman + menu (sekali / paksa via ?man3_org_reseed=1 + capability).
 */
function man3_org_maybe_seed() {
	if ( ! is_admin() && ! ( isset( $_GET['man3_org_seed'] ) && current_user_can( 'manage_options' ) ) ) {
		// Juga jalankan saat admin_init
		return;
	}
}
add_action( 'admin_init', 'man3_org_run_seed' );
add_action( 'init', 'man3_org_ensure_page_rewrite', 20 );

function man3_org_ensure_page_rewrite() {
	// no-op placeholder; seed di admin_init
}

function man3_org_run_seed() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$force = isset( $_GET['man3_org_reseed'] ) && '1' === $_GET['man3_org_reseed'];
	if ( ! $force && get_option( 'man3_org_seeded_v1' ) ) {
		man3_org_ensure_menu();
		return;
	}
	man3_org_do_seed();
	update_option( 'man3_org_seeded_v1', 1 );
}

/**
 * Jalankan seed dari front jika admin + query (setelah deploy).
 */
function man3_org_front_seed_trigger() {
	if ( ! isset( $_GET['man3_org_reseed'] ) || '1' !== $_GET['man3_org_reseed'] ) {
		return;
	}
	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	man3_org_do_seed();
	update_option( 'man3_org_seeded_v1', 1 );
	wp_safe_redirect( home_url( '/struktur-organisasi/' ) );
	exit;
}
add_action( 'template_redirect', 'man3_org_front_seed_trigger' );

function man3_org_do_seed() {
	$map = array(); // key => post_id

	foreach ( man3_org_seed_tree() as $row ) {
		$existing = get_posts(
			array(
				'post_type'      => 'man3_org',
				'name'           => $row['key'],
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);
		$parent_id = 0;
		if ( ! empty( $row['parent'] ) && isset( $map[ $row['parent'] ] ) ) {
			$parent_id = (int) $map[ $row['parent'] ];
		} elseif ( ! empty( $row['parent'] ) ) {
			$p = get_posts(
				array(
					'post_type'      => 'man3_org',
					'name'           => $row['parent'],
					'posts_per_page' => 1,
					'post_status'    => 'any',
				)
			);
			if ( $p ) {
				$parent_id = (int) $p[0]->ID;
			}
		}

		$postarr = array(
			'post_title'  => $row['name'],
			'post_name'   => $row['key'],
			'post_type'   => 'man3_org',
			'post_status' => 'publish',
			'post_parent' => $parent_id,
			'menu_order'  => (int) $row['order'],
		);

		if ( $existing ) {
			$postarr['ID'] = $existing[0]->ID;
			$pid           = wp_update_post( $postarr, true );
		} else {
			$pid = wp_insert_post( $postarr, true );
		}
		if ( is_wp_error( $pid ) || ! $pid ) {
			continue;
		}
		update_post_meta( $pid, '_man3_org_jabatan', $row['jabatan'] );
		update_post_meta( $pid, '_man3_org_key', $row['key'] );
		// Pastikan tanpa featured image saat seed.
		delete_post_thumbnail( $pid );
		$map[ $row['key'] ] = $pid;
	}

	man3_org_ensure_page();
	man3_org_ensure_menu();
}

function man3_org_ensure_page() {
	$page = get_page_by_path( 'struktur-organisasi' );
	if ( ! $page ) {
		$pid = wp_insert_post(
			array(
				'post_title'   => 'Struktur Organisasi',
				'post_name'    => 'struktur-organisasi',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- Struktur Organisasi MAN 3 Jember TP 2026–2027 -->',
			)
		);
		if ( $pid && ! is_wp_error( $pid ) ) {
			update_post_meta( $pid, '_wp_page_template', 'page-struktur-organisasi.php' );
		}
	} else {
		update_post_meta( $page->ID, '_wp_page_template', 'page-struktur-organisasi.php' );
	}
}

/**
 * Pastikan menu: parent Pendidik dan Kependidikan → Struktur Organisasi.
 */
function man3_org_ensure_menu() {
	$page = get_page_by_path( 'struktur-organisasi' );
	if ( ! $page ) {
		return;
	}
	$page_id  = (int) $page->ID;
	$page_url = get_permalink( $page_id );

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$menu_id   = 0;
	if ( ! empty( $locations['primary'] ) ) {
		$menu_id = (int) $locations['primary'];
	}
	if ( ! $menu_id ) {
		$menus = wp_get_nav_menus();
		if ( $menus ) {
			$menu_id = (int) $menus[0]->term_id;
		} else {
			$menu_id = wp_create_nav_menu( 'Menu Utama MAN 3' );
		}
		if ( ! is_wp_error( $menu_id ) ) {
			$locations['primary'] = (int) $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}
	if ( ! $menu_id || is_wp_error( $menu_id ) ) {
		return;
	}

	$items  = wp_get_nav_menu_items( $menu_id );
	$parent = null;
	$child  = null;
	if ( $items ) {
		foreach ( $items as $item ) {
			$title = trim( wp_strip_all_tags( $item->title ) );
			if ( 0 === strcasecmp( $title, 'Pendidik dan Kependidikan' ) || 0 === strcasecmp( $title, 'Pendidik & Kependidikan' ) ) {
				$parent = $item;
			}
			if ( 0 === strcasecmp( $title, 'Struktur Organisasi' ) || (int) $item->object_id === $page_id ) {
				$child = $item;
			}
		}
	}

	if ( ! $parent ) {
		$parent_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => 'Pendidik dan Kependidikan',
				'menu-item-url'    => '#',
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
				'menu-item-title'     => 'Struktur Organisasi',
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
				'menu-item-title'     => 'Struktur Organisasi',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent_id,
				'menu-item-url'       => $page_url,
			)
		);
	}
}

/**
 * Ambil pohon anggota untuk tampilan.
 */
function man3_org_get_tree() {
	$posts = get_posts(
		array(
			'post_type'      => 'man3_org',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
		)
	);
	$by_id = array();
	foreach ( $posts as $p ) {
		$by_id[ $p->ID ] = array(
			'id'       => $p->ID,
			'name'     => $p->post_title,
			'jabatan'  => get_post_meta( $p->ID, '_man3_org_jabatan', true ),
			'parent'   => (int) $p->post_parent,
			'order'    => (int) $p->menu_order,
			'photo'    => get_the_post_thumbnail_url( $p->ID, 'thumbnail' ),
			'children' => array(),
		);
	}
	$roots = array();
	foreach ( $by_id as $id => &$node ) {
		if ( $node['parent'] && isset( $by_id[ $node['parent'] ] ) ) {
			$by_id[ $node['parent'] ]['children'][] = &$node;
		} else {
			$roots[] = &$node;
		}
	}
	unset( $node );
	return $roots;
}

/**
 * Render satu kartu anggota.
 */
function man3_org_render_card( $node, $variant = '' ) {
	$classes = 'org-card';
	if ( $variant ) {
		$classes .= ' org-card--' . sanitize_html_class( $variant );
	}
	$initials = '';
	$parts    = preg_split( '/\s+/', $node['name'] );
	foreach ( array_slice( $parts, 0, 2 ) as $part ) {
		$clean = preg_replace( '/[^A-Za-z]/', '', $part );
		if ( $clean ) {
			$initials .= strtoupper( $clean[0] );
		}
	}
	if ( ! $initials ) {
		$initials = '?';
	}
	?>
	<article class="<?php echo esc_attr( $classes ); ?>">
		<div class="org-avatar" aria-hidden="true">
			<?php if ( ! empty( $node['photo'] ) ) : ?>
				<img src="<?php echo esc_url( $node['photo'] ); ?>" alt="" width="72" height="72" loading="lazy" />
			<?php else : ?>
				<span class="org-avatar-placeholder"><?php echo esc_html( $initials ); ?></span>
			<?php endif; ?>
		</div>
		<div class="org-card-body">
			<h3 class="org-name"><?php echo esc_html( $node['name'] ); ?></h3>
			<p class="org-role"><?php echo esc_html( $node['jabatan'] ); ?></p>
		</div>
	</article>
	<?php
}
