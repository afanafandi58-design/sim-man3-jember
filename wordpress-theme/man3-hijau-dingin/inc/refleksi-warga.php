<?php
/**
 * CPT Refleksi Warga — kutipan beranda “Refleksi warga madrasah”.
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register CPT.
 */
function man3_refleksi_register_cpt() {
	register_post_type(
		'man3_refleksi',
		array(
			'labels'             => array(
				'name'               => __( 'Refleksi Warga', 'man3-hijau-dingin' ),
				'singular_name'      => __( 'Kutipan', 'man3-hijau-dingin' ),
				'add_new'            => __( 'Tambah Kutipan', 'man3-hijau-dingin' ),
				'add_new_item'       => __( 'Tambah Kutipan', 'man3-hijau-dingin' ),
				'edit_item'          => __( 'Edit Kutipan', 'man3-hijau-dingin' ),
				'new_item'           => __( 'Kutipan Baru', 'man3-hijau-dingin' ),
				'view_item'          => __( 'Lihat Kutipan', 'man3-hijau-dingin' ),
				'search_items'       => __( 'Cari Kutipan', 'man3-hijau-dingin' ),
				'not_found'          => __( 'Belum ada kutipan.', 'man3-hijau-dingin' ),
				'not_found_in_trash' => __( 'Tidak ada di tempat sampah.', 'man3-hijau-dingin' ),
				'menu_name'          => __( 'Refleksi Warga', 'man3-hijau-dingin' ),
				'all_items'          => __( 'Semua Kutipan', 'man3-hijau-dingin' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-format-quote',
			'menu_position'      => 24,
			'supports'           => array( 'title', 'editor', 'page-attributes', 'revisions' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		)
	);
}
add_action( 'init', 'man3_refleksi_register_cpt' );

/**
 * Meta box: aktif/nonaktif.
 */
function man3_refleksi_meta_box() {
	add_meta_box(
		'man3_refleksi_meta',
		__( 'Pengaturan Kutipan', 'man3-hijau-dingin' ),
		'man3_refleksi_meta_box_render',
		'man3_refleksi',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_refleksi_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function man3_refleksi_meta_box_render( $post ) {
	wp_nonce_field( 'man3_refleksi_save', 'man3_refleksi_nonce' );
	$active = get_post_meta( $post->ID, '_man3_refleksi_active', true );
	if ( '' === $active ) {
		$active = '1';
	}
	?>
	<p>
		<label>
			<input type="checkbox" name="man3_refleksi_active" value="1" <?php checked( $active, '1' ); ?> />
			<?php esc_html_e( 'Aktif di beranda', 'man3-hijau-dingin' ); ?>
		</label>
	</p>
	<p class="description">
		<?php esc_html_e( 'Judul = nama penutur. Isi editor = teks kutipan. Urutan: Atribut Halaman → Order (angka kecil tampil lebih dulu).', 'man3-hijau-dingin' ); ?>
	</p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function man3_refleksi_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_refleksi_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_refleksi_nonce'] ) ), 'man3_refleksi_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$active = isset( $_POST['man3_refleksi_active'] ) ? '1' : '0';
	update_post_meta( $post_id, '_man3_refleksi_active', $active );
}
add_action( 'save_post_man3_refleksi', 'man3_refleksi_meta_save' );

/**
 * Admin help.
 */
function man3_refleksi_admin_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'man3_refleksi' !== $screen->post_type ) {
		return;
	}
	if ( 'edit' === $screen->base || 'post' === $screen->base ) {
		echo '<div class="notice notice-info"><p>';
		esc_html_e( 'Refleksi Warga tampil di beranda pada bagian “Refleksi warga madrasah”. Judul = nama, konten = kutipan. Centang “Aktif di beranda” dan atur Order untuk urutan.', 'man3-hijau-dingin' );
		echo '</p></div>';
	}
}
add_action( 'admin_notices', 'man3_refleksi_admin_notice' );

/**
 * @param array $cols Columns.
 * @return array
 */
function man3_refleksi_admin_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['man3_refleksi_quote']  = __( 'Kutipan', 'man3-hijau-dingin' );
			$new['man3_refleksi_active'] = __( 'Aktif', 'man3-hijau-dingin' );
			$new['man3_refleksi_order']  = __( 'Urutan', 'man3-hijau-dingin' );
		}
	}
	return $new;
}
add_filter( 'manage_man3_refleksi_posts_columns', 'man3_refleksi_admin_columns' );

/**
 * @param string $col Column key.
 * @param int    $post_id Post ID.
 */
function man3_refleksi_admin_column_content( $col, $post_id ) {
	if ( 'man3_refleksi_quote' === $col ) {
		$text = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
		echo esc_html( wp_html_excerpt( $text, 72, '…' ) );
	}
	if ( 'man3_refleksi_active' === $col ) {
		$active = get_post_meta( $post_id, '_man3_refleksi_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		echo '1' === $active ? esc_html__( 'Ya', 'man3-hijau-dingin' ) : esc_html__( 'Tidak', 'man3-hijau-dingin' );
	}
	if ( 'man3_refleksi_order' === $col ) {
		echo esc_html( (string) get_post_field( 'menu_order', $post_id ) );
	}
}
add_action( 'manage_man3_refleksi_posts_custom_column', 'man3_refleksi_admin_column_content', 10, 2 );

/**
 * Default sort by menu_order.
 *
 * @param WP_Query $query Query.
 */
function man3_refleksi_admin_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-man3_refleksi' !== $screen->id ) {
		return;
	}
	if ( ! isset( $_GET['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$query->set( 'orderby', 'menu_order title' );
		$query->set( 'order', 'ASC' );
	}
}
add_action( 'pre_get_posts', 'man3_refleksi_admin_order' );

/**
 * Seed data (kutipan yang sudah tampil di beranda).
 *
 * @return array<int,array{key:string,author:string,text:string,order:int}>
 */
function man3_refleksi_seed_rows() {
	return array(
		array(
			'key'    => 'abah',
			'author' => 'Abah',
			'text'   => 'Madrasah bukan hanya tempat mencari ilmu, tetapi tempat membentuk hati, adab, dan masa depan...',
			'order'  => 10,
		),
		array(
			'key'    => 'sm',
			'author' => 'SM',
			'text'   => 'Prinsip ekonomi Islam yang adil dan transparan sejalan dengan nilai-nilai kejujuran dan integritas yang kita pelajari di madrasah.',
			'order'  => 20,
		),
		array(
			'key'    => 'biets-hayyuni',
			'author' => "Biet's Hayyuni",
			'text'   => 'Tersiarlah cahaya ilmu dari siswa-siswi madrasah, Berakhlak, santun, dan beradab, tatkala mereka berjalan. Mereka adalah bunga-bunga yang harum di taman ilmu, Menghiasi dunia dengan kebaikan.....',
			'order'  => 30,
		),
		array(
			'key'    => 'wilda-naela',
			'author' => 'Wilda Naela',
			'text'   => 'Hidup bersosial yang baik mengajarkan kita untuk menghargai perbedaan dan saling mendukung dalam kebaikan....',
			'order'  => 40,
		),
		array(
			'key'    => 'usman-affan-ridoi',
			'author' => 'Usman Affan Ridoi',
			'text'   => 'Kedisiplinan adalah kunci kesuksesan; sikap yang baik adalah jalan menuju kebahagiaan abadi....',
			'order'  => 50,
		),
	);
}

/**
 * Insert / update seed posts.
 */
function man3_refleksi_do_seed() {
	foreach ( man3_refleksi_seed_rows() as $row ) {
		$existing = get_posts(
			array(
				'post_type'      => 'man3_refleksi',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_key'       => '_man3_refleksi_seed_key',
				'meta_value'     => $row['key'],
				'fields'         => 'ids',
			)
		);
		$postarr = array(
			'post_type'    => 'man3_refleksi',
			'post_status'  => 'publish',
			'post_title'   => $row['author'],
			'post_content' => $row['text'],
			'menu_order'   => (int) $row['order'],
		);
		if ( $existing ) {
			$postarr['ID'] = (int) $existing[0];
			$pid           = wp_update_post( $postarr, true );
		} else {
			$pid = wp_insert_post( $postarr, true );
		}
		if ( ! $pid || is_wp_error( $pid ) ) {
			continue;
		}
		update_post_meta( (int) $pid, '_man3_refleksi_seed_key', $row['key'] );
		update_post_meta( (int) $pid, '_man3_refleksi_active', '1' );
	}
}

/**
 * Seed once on admin, or force via ?man3_refleksi_reseed=1.
 */
function man3_refleksi_run_seed() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$force = isset( $_GET['man3_refleksi_reseed'] ) && '1' === $_GET['man3_refleksi_reseed']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $force && get_option( 'man3_refleksi_seeded_v1' ) ) {
		return;
	}
	man3_refleksi_do_seed();
	update_option( 'man3_refleksi_seeded_v1', 1, false );
}
add_action( 'admin_init', 'man3_refleksi_run_seed' );

/**
 * Front reseed trigger (admin login).
 */
function man3_refleksi_front_seed_trigger() {
	if ( ! isset( $_GET['man3_refleksi_reseed'] ) || '1' !== $_GET['man3_refleksi_reseed'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	man3_refleksi_do_seed();
	update_option( 'man3_refleksi_seeded_v1', 1, false );
	wp_safe_redirect( home_url( '/#quotes-title' ) );
	exit;
}
add_action( 'template_redirect', 'man3_refleksi_front_seed_trigger' );

/**
 * Kutipan aktif untuk beranda.
 *
 * @return array<int,array{text:string,author:string}>
 */
function man3_refleksi_get_items() {
	$posts = get_posts(
		array(
			'post_type'      => 'man3_refleksi',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);
	$out = array();
	foreach ( $posts as $p ) {
		$active = get_post_meta( $p->ID, '_man3_refleksi_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		if ( '1' !== $active ) {
			continue;
		}
		$text = trim( wp_strip_all_tags( $p->post_content ) );
		if ( '' === $text ) {
			continue;
		}
		$out[] = array(
			'text'   => $text,
			'author' => $p->post_title,
		);
	}
	return $out;
}
