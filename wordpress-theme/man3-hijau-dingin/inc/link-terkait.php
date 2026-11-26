<?php
/**
 * Link Terkait — CPT + kolom kiri beranda (berdampingan Akses cepat).
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seed dari widget/sidebar lama (Wayback Apr 2025) — bukan karangan.
 *
 * @return array<int,array<string,string|int>>
 */
function man3_link_terkait_seed_rows() {
	return array(
		array(
			'key'   => 'kemenag-ri',
			'nama'  => 'KEMENAG RI',
			'url'   => 'https://kemenag.go.id/',
			'order' => 10,
		),
		array(
			'key'   => 'kemenag-jatim',
			'nama'  => 'KEMENAG JAWA TIMUR',
			'url'   => 'https://jatim.kemenag.go.id/',
			'order' => 20,
		),
		array(
			'key'   => 'kemenag-jember',
			'nama'  => 'KEMENAG KAB JEMBER',
			'url'   => 'https://jember.kemenag.go.id/',
			'order' => 30,
		),
		array(
			'key'   => 'simpatika',
			'nama'  => 'SIMPATIKA',
			'url'   => 'https://simpatika.kemenag.go.id/madrasah',
			'order' => 40,
		),
		array(
			'key'   => 'rdm-man3',
			'nama'  => 'RDM MAN 3 JEMBER',
			'url'   => 'https://rdm.man3jember.sch.id/',
			'order' => 50,
		),
		array(
			'key'   => 'e-kinerja',
			'nama'  => 'E_KINERJA',
			'url'   => 'https://kinerja.bkn.go.id/login',
			'order' => 60,
		),
		array(
			'key'   => 'sinadita',
			'nama'  => 'Si-NaDita',
			'url'   => 'https://sinadita.bansmprovjatim.com/login',
			'order' => 70,
		),
		array(
			'key'   => 'emis',
			'nama'  => 'EMIS',
			'url'   => 'https://emis.kemenag.go.id/',
			'order' => 80,
		),
	);
}

/**
 * Register CPT.
 */
function man3_link_terkait_register_cpt() {
	register_post_type(
		'man3_link_terkait',
		array(
			'labels'             => array(
				'name'          => __( 'Link Terkait', 'man3-hijau-dingin' ),
				'singular_name' => __( 'Link Terkait', 'man3-hijau-dingin' ),
				'add_new'       => __( 'Tambah Link', 'man3-hijau-dingin' ),
				'add_new_item'  => __( 'Tambah Link Terkait', 'man3-hijau-dingin' ),
				'edit_item'     => __( 'Edit Link Terkait', 'man3-hijau-dingin' ),
				'menu_name'     => __( 'Link Terkait', 'man3-hijau-dingin' ),
				'all_items'     => __( 'Semua Link', 'man3-hijau-dingin' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-admin-links',
			'menu_position'      => 24,
			'supports'           => array( 'title', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		)
	);
}
add_action( 'init', 'man3_link_terkait_register_cpt' );

/**
 * Meta URL + aktif.
 */
function man3_link_terkait_meta_box() {
	add_meta_box(
		'man3_link_terkait_meta',
		__( 'Pengaturan Link', 'man3-hijau-dingin' ),
		'man3_link_terkait_meta_box_render',
		'man3_link_terkait',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_link_terkait_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function man3_link_terkait_meta_box_render( $post ) {
	wp_nonce_field( 'man3_link_terkait_save', 'man3_link_terkait_nonce' );
	$url    = get_post_meta( $post->ID, '_man3_link_url', true );
	$active = get_post_meta( $post->ID, '_man3_link_active', true );
	if ( '' === $active ) {
		$active = '1';
	}
	?>
	<p>
		<label for="man3_link_url"><strong><?php esc_html_e( 'URL', 'man3-hijau-dingin' ); ?></strong></label>
		<input type="url" class="widefat" id="man3_link_url" name="man3_link_url" value="<?php echo esc_attr( (string) $url ); ?>" placeholder="https://" />
	</p>
	<p>
		<label>
			<input type="checkbox" name="man3_link_active" value="1" <?php checked( $active, '1' ); ?> />
			<?php esc_html_e( 'Aktif di beranda (kolom Link Terkait)', 'man3-hijau-dingin' ); ?>
		</label>
	</p>
	<p class="description">
		<?php esc_html_e( 'Nama = judul. Urutan = Order (angka kecil lebih dulu).', 'man3-hijau-dingin' ); ?>
	</p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function man3_link_terkait_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_link_terkait_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_link_terkait_nonce'] ) ), 'man3_link_terkait_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$url = isset( $_POST['man3_link_url'] ) ? esc_url_raw( wp_unslash( $_POST['man3_link_url'] ) ) : '';
	update_post_meta( $post_id, '_man3_link_url', $url );
	update_post_meta( $post_id, '_man3_link_active', isset( $_POST['man3_link_active'] ) ? '1' : '0' );
}
add_action( 'save_post_man3_link_terkait', 'man3_link_terkait_meta_save' );

/**
 * @param array $cols Columns.
 * @return array
 */
function man3_link_terkait_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['man3_link_url']    = __( 'URL', 'man3-hijau-dingin' );
			$new['man3_link_active'] = __( 'Aktif', 'man3-hijau-dingin' );
			$new['man3_link_order']  = __( 'Urutan', 'man3-hijau-dingin' );
		}
	}
	return $new;
}
add_filter( 'manage_man3_link_terkait_posts_columns', 'man3_link_terkait_columns' );

/**
 * @param string $col Column.
 * @param int    $post_id Post ID.
 */
function man3_link_terkait_column_content( $col, $post_id ) {
	if ( 'man3_link_url' === $col ) {
		$url = get_post_meta( $post_id, '_man3_link_url', true );
		if ( $url ) {
			echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $url ) . '</a>';
		} else {
			echo '&mdash;';
		}
	}
	if ( 'man3_link_active' === $col ) {
		$active = get_post_meta( $post_id, '_man3_link_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		echo '1' === $active ? esc_html__( 'Ya', 'man3-hijau-dingin' ) : esc_html__( 'Tidak', 'man3-hijau-dingin' );
	}
	if ( 'man3_link_order' === $col ) {
		echo esc_html( (string) get_post_field( 'menu_order', $post_id ) );
	}
}
add_action( 'manage_man3_link_terkait_posts_custom_column', 'man3_link_terkait_column_content', 10, 2 );

/**
 * Seed dari arsip sidebar lama.
 */
function man3_link_terkait_run_seed() {
	if ( get_option( 'man3_link_terkait_seeded_v1' ) ) {
		return;
	}
	foreach ( man3_link_terkait_seed_rows() as $row ) {
		$existing = get_posts(
			array(
				'post_type'      => 'man3_link_terkait',
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'meta_key'       => '_man3_link_seed_key',
				'meta_value'     => $row['key'],
				'fields'         => 'ids',
			)
		);
		if ( $existing ) {
			continue;
		}
		$pid = wp_insert_post(
			array(
				'post_type'   => 'man3_link_terkait',
				'post_status' => 'publish',
				'post_title'  => $row['nama'],
				'menu_order'  => (int) $row['order'],
			),
			true
		);
		if ( is_wp_error( $pid ) || ! $pid ) {
			continue;
		}
		update_post_meta( (int) $pid, '_man3_link_url', $row['url'] );
		update_post_meta( (int) $pid, '_man3_link_active', '1' );
		update_post_meta( (int) $pid, '_man3_link_seed_key', $row['key'] );
	}
	update_option( 'man3_link_terkait_seeded_v1', 1, false );
}

/**
 * Bootstrap admin + reseed opsional.
 */
function man3_link_terkait_admin_bootstrap() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	man3_link_terkait_run_seed();
}
add_action( 'admin_init', 'man3_link_terkait_admin_bootstrap' );

/**
 * Front seed once / force reseed.
 */
function man3_link_terkait_front_bootstrap() {
	$force = isset( $_GET['man3_link_terkait_reseed'] ) && '1' === $_GET['man3_link_terkait_reseed']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $force && current_user_can( 'manage_options' ) ) {
		delete_option( 'man3_link_terkait_seeded_v1' );
		$old = get_posts(
			array(
				'post_type'      => 'man3_link_terkait',
				'posts_per_page' => 100,
				'post_status'    => 'any',
				'meta_key'       => '_man3_link_seed_key',
				'fields'         => 'ids',
			)
		);
		foreach ( $old as $oid ) {
			wp_delete_post( (int) $oid, true );
		}
		man3_link_terkait_run_seed();
		return;
	}
	if ( get_option( 'man3_link_terkait_page_ok_v1' ) ) {
		return;
	}
	man3_link_terkait_run_seed();
	update_option( 'man3_link_terkait_page_ok_v1', 1, false );
}
add_action( 'init', 'man3_link_terkait_front_bootstrap', 40 );

/**
 * Daftar link aktif untuk beranda.
 *
 * @return array<int,array<string,string>>
 */
function man3_link_terkait_get_items() {
	$posts = get_posts(
		array(
			'post_type'      => 'man3_link_terkait',
			'posts_per_page' => 40,
			'post_status'    => 'publish',
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
		)
	);
	$out = array();
	foreach ( $posts as $p ) {
		$active = get_post_meta( $p->ID, '_man3_link_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		if ( '1' !== $active ) {
			continue;
		}
		$url = trim( (string) get_post_meta( $p->ID, '_man3_link_url', true ) );
		if ( ! $url ) {
			continue;
		}
		$out[] = array(
			'label' => get_the_title( $p ),
			'href'  => $url,
		);
	}
	return $out;
}
