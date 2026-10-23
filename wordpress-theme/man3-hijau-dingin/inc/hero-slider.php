<?php
/**
 * CPT Hero Slide / Banner Kegiatan — slider beranda.
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register CPT.
 */
function man3_hero_register_cpt() {
	register_post_type(
		'man3_hero_slide',
		array(
			'labels'             => array(
				'name'          => __( 'Hero Slide', 'man3-hijau-dingin' ),
				'singular_name' => __( 'Hero Slide', 'man3-hijau-dingin' ),
				'add_new_item'  => __( 'Tambah Slide', 'man3-hijau-dingin' ),
				'edit_item'     => __( 'Edit Slide', 'man3-hijau-dingin' ),
				'menu_name'     => __( 'Hero Slide', 'man3-hijau-dingin' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-images-alt2',
			'menu_position'      => 22,
			'supports'           => array( 'title', 'thumbnail', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
		)
	);
}
add_action( 'init', 'man3_hero_register_cpt' );

/**
 * Meta: aktif/nonaktif.
 */
function man3_hero_meta_box() {
	add_meta_box(
		'man3_hero_meta',
		__( 'Pengaturan Slide', 'man3-hijau-dingin' ),
		'man3_hero_meta_box_render',
		'man3_hero_slide',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_hero_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function man3_hero_meta_box_render( $post ) {
	wp_nonce_field( 'man3_hero_save', 'man3_hero_nonce' );
	$active = get_post_meta( $post->ID, '_man3_hero_active', true );
	if ( '' === $active ) {
		$active = '1';
	}
	?>
	<p>
		<label>
			<input type="checkbox" name="man3_hero_active" value="1" <?php checked( $active, '1' ); ?> />
			<?php esc_html_e( 'Aktif di beranda', 'man3-hijau-dingin' ); ?>
		</label>
	</p>
	<p class="description">
		<?php esc_html_e( 'Judul opsional (hanya untuk admin). Urutan: Attribut Halaman → Order. Wajib: Gambar Unggulan (foto landscape disarankan).', 'man3-hijau-dingin' ); ?>
	</p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function man3_hero_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_hero_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_hero_nonce'] ) ), 'man3_hero_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$active = isset( $_POST['man3_hero_active'] ) ? '1' : '0';
	update_post_meta( $post_id, '_man3_hero_active', $active );
}
add_action( 'save_post_man3_hero_slide', 'man3_hero_meta_save' );

/**
 * Kolom admin: aktif + thumbnail.
 *
 * @param array $cols Columns.
 * @return array
 */
function man3_hero_admin_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['man3_hero_thumb']  = __( 'Foto', 'man3-hijau-dingin' );
			$new['man3_hero_active'] = __( 'Aktif', 'man3-hijau-dingin' );
			$new['man3_hero_order']  = __( 'Urutan', 'man3-hijau-dingin' );
		}
	}
	return $new;
}
add_filter( 'manage_man3_hero_slide_posts_columns', 'man3_hero_admin_columns' );

/**
 * @param string $col Column key.
 * @param int    $post_id Post ID.
 */
function man3_hero_admin_column_content( $col, $post_id ) {
	if ( 'man3_hero_thumb' === $col ) {
		$thumb = get_the_post_thumbnail( $post_id, array( 72, 48 ) );
		echo $thumb ? $thumb : '&mdash;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( 'man3_hero_active' === $col ) {
		$active = get_post_meta( $post_id, '_man3_hero_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		echo '1' === $active ? esc_html__( 'Ya', 'man3-hijau-dingin' ) : esc_html__( 'Tidak', 'man3-hijau-dingin' );
	}
	if ( 'man3_hero_order' === $col ) {
		echo esc_html( (string) get_post_field( 'menu_order', $post_id ) );
	}
}
add_action( 'manage_man3_hero_slide_posts_custom_column', 'man3_hero_admin_column_content', 10, 2 );

/**
 * Seed rows: attachment ID + label.
 *
 * @return array<int,array{key:string,title:string,attachment_id:int,order:int}>
 */
function man3_hero_seed_rows() {
	return array(
		array(
			'key'           => 'hero-hari-santri',
			'title'         => 'Hari Santri',
			'attachment_id' => 7412,
			'order'         => 10,
		),
		array(
			'key'           => 'hero-matamuda',
			'title'         => 'MATAMUDA',
			'attachment_id' => 7380,
			'order'         => 20,
		),
		array(
			'key'           => 'hero-matsama',
			'title'         => 'MATSAMA',
			'attachment_id' => 6934,
			'order'         => 30,
		),
		array(
			'key'           => 'hero-expo',
			'title'         => 'Campus Expo',
			'attachment_id' => 7395,
			'order'         => 40,
		),
		array(
			'key'           => 'hero-pancasila',
			'title'         => 'Peringatan Pancasila',
			'attachment_id' => 6846,
			'order'         => 50,
		),
	);
}

/**
 * Seed once / force.
 */
function man3_hero_run_seed() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$force = isset( $_GET['man3_hero_reseed'] ) && '1' === $_GET['man3_hero_reseed'];
	if ( ! $force && get_option( 'man3_hero_seeded_v1' ) ) {
		return;
	}
	man3_hero_do_seed();
	update_option( 'man3_hero_seeded_v1', 1 );
}
add_action( 'admin_init', 'man3_hero_run_seed' );

/**
 * Front reseed.
 */
function man3_hero_front_seed_trigger() {
	if ( ! isset( $_GET['man3_hero_reseed'] ) || '1' !== $_GET['man3_hero_reseed'] ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	man3_hero_do_seed();
	update_option( 'man3_hero_seeded_v1', 1 );
	wp_safe_redirect( home_url( '/' ) );
	exit;
}
add_action( 'template_redirect', 'man3_hero_front_seed_trigger' );

/**
 * Seed CPT slides from existing media.
 */
function man3_hero_do_seed() {
	foreach ( man3_hero_seed_rows() as $row ) {
		$att_id = (int) $row['attachment_id'];
		if ( $att_id < 1 || 'attachment' !== get_post_type( $att_id ) ) {
			continue;
		}
		$existing = get_posts(
			array(
				'post_type'      => 'man3_hero_slide',
				'name'           => $row['key'],
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);
		$postarr = array(
			'post_title'  => $row['title'],
			'post_name'   => $row['key'],
			'post_status' => 'publish',
			'post_type'   => 'man3_hero_slide',
			'menu_order'  => (int) $row['order'],
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
		update_post_meta( $pid, '_man3_hero_active', '1' );
		set_post_thumbnail( $pid, $att_id );
	}
}

/**
 * Active slides for front (with image).
 *
 * @return array<int,array{id:int,title:string,url:string,alt:string}>
 */
function man3_hero_get_slides() {
	$posts = get_posts(
		array(
			'post_type'      => 'man3_hero_slide',
			'posts_per_page' => 20,
			'post_status'    => 'publish',
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);
	$out = array();
	foreach ( $posts as $p ) {
		$active = get_post_meta( $p->ID, '_man3_hero_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		if ( '1' !== $active ) {
			continue;
		}
		$url = get_the_post_thumbnail_url( $p, 'full' );
		if ( ! $url ) {
			continue;
		}
		$out[] = array(
			'id'    => $p->ID,
			'title' => get_the_title( $p ),
			'url'   => $url,
			'alt'   => get_the_title( $p ),
		);
	}
	return $out;
}

/**
 * Fallback single slide (theme default hero).
 *
 * @return array<int,array{id:int,title:string,url:string,alt:string}>
 */
function man3_hero_fallback_slides() {
	$url = trailingslashit( get_template_directory_uri() ) . 'assets/images/hero-campus.jpg';
	return array(
		array(
			'id'    => 0,
			'title' => '',
			'url'   => $url,
			'alt'   => 'MAN 3 Jember',
		),
	);
}
