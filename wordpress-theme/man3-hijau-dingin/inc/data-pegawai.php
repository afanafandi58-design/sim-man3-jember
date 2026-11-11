<?php
/**
 * CPT Data Pegawai (Guru & Staff) + seed + menu.
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data_file = __DIR__ . '/pegawai-data.php';
if ( file_exists( $data_file ) ) {
	require_once $data_file;
}

/**
 * Register CPT.
 */
function man3_pegawai_register_cpt() {
	register_post_type(
		'man3_pegawai',
		array(
			'labels'             => array(
				'name'          => __( 'Data Pegawai', 'man3-hijau-dingin' ),
				'singular_name' => __( 'Pegawai', 'man3-hijau-dingin' ),
				'add_new_item'  => __( 'Tambah Pegawai', 'man3-hijau-dingin' ),
				'edit_item'     => __( 'Edit Pegawai', 'man3-hijau-dingin' ),
				'menu_name'     => __( 'Data Pegawai', 'man3-hijau-dingin' ),
			),
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-groups',
			'menu_position'      => 25,
			'supports'           => array( 'title', 'thumbnail', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
		)
	);
}
add_action( 'init', 'man3_pegawai_register_cpt' );

function man3_pegawai_meta_box() {
	add_meta_box(
		'man3_pegawai_meta',
		__( 'Detail Pegawai', 'man3-hijau-dingin' ),
		'man3_pegawai_meta_box_render',
		'man3_pegawai',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_pegawai_meta_box' );

function man3_pegawai_meta_box_render( $post ) {
	wp_nonce_field( 'man3_pegawai_save', 'man3_pegawai_nonce' );
	$jabatan   = get_post_meta( $post->ID, '_man3_pegawai_jabatan', true );
	$jab_short = get_post_meta( $post->ID, '_man3_pegawai_jabatan_singkat', true );
	$pendidikan = get_post_meta( $post->ID, '_man3_pegawai_pendidikan', true );
	$jenis     = get_post_meta( $post->ID, '_man3_pegawai_jenis', true );
	if ( ! $jenis ) {
		$jenis = 'pendidik';
	}
	?>
	<p>
		<label for="man3_pegawai_jenis"><strong><?php esc_html_e( 'Jenis', 'man3-hijau-dingin' ); ?></strong></label><br />
		<select name="man3_pegawai_jenis" id="man3_pegawai_jenis">
			<option value="pendidik" <?php selected( $jenis, 'pendidik' ); ?>><?php esc_html_e( 'Pendidik', 'man3-hijau-dingin' ); ?></option>
			<option value="tendik" <?php selected( $jenis, 'tendik' ); ?>><?php esc_html_e( 'Tenaga Kependidikan', 'man3-hijau-dingin' ); ?></option>
		</select>
	</p>
	<p>
		<label for="man3_pegawai_jabatan_singkat"><strong><?php esc_html_e( 'Jabatan (tampil di kartu)', 'man3-hijau-dingin' ); ?></strong></label>
		<input type="text" class="widefat" id="man3_pegawai_jabatan_singkat" name="man3_pegawai_jabatan_singkat" value="<?php echo esc_attr( $jab_short ); ?>" />
	</p>
	<p>
		<label for="man3_pegawai_jabatan"><strong><?php esc_html_e( 'Jabatan lengkap', 'man3-hijau-dingin' ); ?></strong></label>
		<textarea class="widefat" rows="2" id="man3_pegawai_jabatan" name="man3_pegawai_jabatan"><?php echo esc_textarea( $jabatan ); ?></textarea>
	</p>
	<p>
		<label for="man3_pegawai_pendidikan"><strong><?php esc_html_e( 'Pendidikan terakhir', 'man3-hijau-dingin' ); ?></strong></label>
		<input type="text" class="widefat" id="man3_pegawai_pendidikan" name="man3_pegawai_pendidikan" value="<?php echo esc_attr( $pendidikan ); ?>" />
	</p>
	<p class="description"><?php esc_html_e( 'Foto: set Gambar Unggulan / Featured Image. Kosong = placeholder inisial.', 'man3-hijau-dingin' ); ?></p>
	<?php
}

function man3_pegawai_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_pegawai_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_pegawai_nonce'] ) ), 'man3_pegawai_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$jenis = isset( $_POST['man3_pegawai_jenis'] ) ? sanitize_key( wp_unslash( $_POST['man3_pegawai_jenis'] ) ) : 'pendidik';
	if ( ! in_array( $jenis, array( 'pendidik', 'tendik' ), true ) ) {
		$jenis = 'pendidik';
	}
	update_post_meta( $post_id, '_man3_pegawai_jenis', $jenis );
	if ( isset( $_POST['man3_pegawai_jabatan'] ) ) {
		update_post_meta( $post_id, '_man3_pegawai_jabatan', sanitize_textarea_field( wp_unslash( $_POST['man3_pegawai_jabatan'] ) ) );
	}
	if ( isset( $_POST['man3_pegawai_jabatan_singkat'] ) ) {
		update_post_meta( $post_id, '_man3_pegawai_jabatan_singkat', sanitize_text_field( wp_unslash( $_POST['man3_pegawai_jabatan_singkat'] ) ) );
	}
	if ( isset( $_POST['man3_pegawai_pendidikan'] ) ) {
		update_post_meta( $post_id, '_man3_pegawai_pendidikan', sanitize_text_field( wp_unslash( $_POST['man3_pegawai_pendidikan'] ) ) );
	}
}
add_action( 'save_post_man3_pegawai', 'man3_pegawai_meta_save' );

function man3_pegawai_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['man3_jenis']      = __( 'Jenis', 'man3-hijau-dingin' );
			$new['man3_jabatan']    = __( 'Jabatan', 'man3-hijau-dingin' );
			$new['man3_pendidikan'] = __( 'Pendidikan', 'man3-hijau-dingin' );
			$new['man3_foto']       = __( 'Foto', 'man3-hijau-dingin' );
		}
	}
	return $new;
}
add_filter( 'manage_man3_pegawai_posts_columns', 'man3_pegawai_columns' );

function man3_pegawai_column_content( $col, $post_id ) {
	if ( 'man3_jenis' === $col ) {
		$j = get_post_meta( $post_id, '_man3_pegawai_jenis', true );
		echo esc_html( 'tendik' === $j ? 'Tendik' : 'Pendidik' );
	}
	if ( 'man3_jabatan' === $col ) {
		$s = get_post_meta( $post_id, '_man3_pegawai_jabatan_singkat', true );
		echo esc_html( $s ? $s : get_post_meta( $post_id, '_man3_pegawai_jabatan', true ) );
	}
	if ( 'man3_pendidikan' === $col ) {
		echo esc_html( get_post_meta( $post_id, '_man3_pegawai_pendidikan', true ) );
	}
	if ( 'man3_foto' === $col ) {
		if ( has_post_thumbnail( $post_id ) ) {
			echo get_the_post_thumbnail( $post_id, array( 40, 40 ) );
		} else {
			echo '<span style="color:#888">—</span>';
		}
	}
}
add_action( 'manage_man3_pegawai_posts_custom_column', 'man3_pegawai_column_content', 10, 2 );

/**
 * Seed.
 */
function man3_pegawai_run_seed() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$force = isset( $_GET['man3_pegawai_reseed'] ) && '1' === $_GET['man3_pegawai_reseed'];
	if ( ! $force && get_option( 'man3_pegawai_seeded_v1' ) ) {
		return;
	}
	man3_pegawai_do_seed();
	update_option( 'man3_pegawai_seeded_v1', 1 );
}
add_action( 'admin_init', 'man3_pegawai_run_seed' );

function man3_pegawai_front_seed_trigger() {
	if ( ! isset( $_GET['man3_pegawai_reseed'] ) || '1' !== $_GET['man3_pegawai_reseed'] ) {
		return;
	}
	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	man3_pegawai_do_seed();
	update_option( 'man3_pegawai_seeded_v1', 1 );
	wp_safe_redirect( home_url( '/data-pegawai/' ) );
	exit;
}
add_action( 'template_redirect', 'man3_pegawai_front_seed_trigger' );

function man3_pegawai_do_seed() {
	if ( ! function_exists( 'man3_pegawai_seed_rows' ) ) {
		return;
	}
	foreach ( man3_pegawai_seed_rows() as $row ) {
		$existing = get_posts(
			array(
				'post_type'      => 'man3_pegawai',
				'name'           => $row['key'],
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);
		$postarr = array(
			'post_title'  => $row['nama'],
			'post_name'   => $row['key'],
			'post_status' => 'publish',
			'post_type'   => 'man3_pegawai',
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
		update_post_meta( $pid, '_man3_pegawai_jabatan', $row['jabatan'] );
		update_post_meta( $pid, '_man3_pegawai_jabatan_singkat', $row['jabatan_singkat'] );
		update_post_meta( $pid, '_man3_pegawai_pendidikan', $row['pendidikan'] );
		update_post_meta( $pid, '_man3_pegawai_jenis', $row['jenis'] );
	}

	$page = get_page_by_path( 'data-pegawai' );
	if ( ! $page ) {
		$pid = wp_insert_post(
			array(
				'post_title'   => 'Guru & Staff',
				'post_name'    => 'data-pegawai',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- Data Pegawai MAN 3 Jember -->',
			)
		);
		if ( $pid && ! is_wp_error( $pid ) ) {
			update_post_meta( $pid, '_wp_page_template', 'page-data-pegawai.php' );
		}
	} else {
		wp_update_post(
			array(
				'ID'         => $page->ID,
				'post_title' => 'Guru & Staff',
				'post_status'=> 'publish',
			)
		);
		update_post_meta( $page->ID, '_wp_page_template', 'page-data-pegawai.php' );
	}

	man3_pegawai_ensure_menu();
}

/**
 * Menu: ganti Daftar Guru + Staff → satu item Guru & Staff.
 */
function man3_pegawai_ensure_menu() {
	$page = get_page_by_path( 'data-pegawai' );
	if ( ! $page ) {
		return;
	}
	$page_id   = (int) $page->ID;
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$menu_id   = ! empty( $locations['primary'] ) ? (int) $locations['primary'] : 0;
	if ( ! $menu_id ) {
		$menus   = wp_get_nav_menus();
		$menu_id = $menus ? (int) $menus[0]->term_id : wp_create_nav_menu( 'Menu Utama MAN 3' );
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
	$legacy = array();
	if ( $items ) {
		foreach ( $items as $item ) {
			$title = trim( wp_strip_all_tags( $item->title ) );
			if ( 0 === strcasecmp( $title, 'Pendidik dan Kependidikan' ) || 0 === strcasecmp( $title, 'Pendidik & Kependidikan' ) ) {
				$parent = $item;
			}
			if (
				0 === strcasecmp( $title, 'Guru & Staff' )
				|| 0 === strcasecmp( $title, 'Data Pegawai' )
				|| (int) $item->object_id === $page_id
			) {
				$child = $item;
			}
			// Legacy duplicates to remove.
			if (
				0 === strcasecmp( $title, 'Daftar Guru' )
				|| 0 === strcasecmp( $title, 'Staff' )
				|| 0 === strcasecmp( $title, 'Guru' )
				|| 0 === strcasecmp( $title, 'GTK' )
			) {
				$legacy[] = (int) $item->ID;
			}
		}
	}

	foreach ( $legacy as $lid ) {
		wp_delete_post( $lid, true );
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

	$args = array(
		'menu-item-title'     => 'Guru & Staff',
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $page_id,
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
		'menu-item-parent-id' => $parent_id,
	);
	if ( ! $child ) {
		wp_update_nav_menu_item( $menu_id, 0, $args );
	} else {
		wp_update_nav_menu_item( $menu_id, (int) $child->ID, $args );
	}
}

/**
 * Items for front.
 *
 * @param string $jenis all|pendidik|tendik.
 * @return array<int,array<string,mixed>>
 */
function man3_pegawai_get_items( $jenis = 'all' ) {
	$posts = get_posts(
		array(
			'post_type'      => 'man3_pegawai',
			'posts_per_page' => 200,
			'post_status'    => 'publish',
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);
	$out = array();
	foreach ( $posts as $p ) {
		$j = get_post_meta( $p->ID, '_man3_pegawai_jenis', true ) ?: 'pendidik';
		if ( 'all' !== $jenis && $j !== $jenis ) {
			continue;
		}
		$jab = get_post_meta( $p->ID, '_man3_pegawai_jabatan_singkat', true );
		if ( ! $jab ) {
			$jab = get_post_meta( $p->ID, '_man3_pegawai_jabatan', true );
		}
		$name  = get_the_title( $p );
		$parts = preg_split( '/\s+/', trim( $name ) );
		$ini   = '';
		foreach ( array_slice( $parts, 0, 2 ) as $w ) {
			$w = ltrim( $w, '.,' );
			if ( $w && ! in_array( strtolower( $w ), array( 'drs.', 'dra.', 'dr.', 'ir.', 'h.', 'hj.' ), true ) ) {
				$ini .= strtoupper( substr( $w, 0, 1 ) );
			}
		}
		if ( strlen( $ini ) < 1 ) {
			$ini = 'P';
		}
		$out[] = array(
			'id'         => $p->ID,
			'nama'       => $name,
			'jabatan'    => $jab,
			'pendidikan' => get_post_meta( $p->ID, '_man3_pegawai_pendidikan', true ),
			'jenis'      => $j,
			'thumb'      => get_the_post_thumbnail_url( $p, 'medium' ),
			'inisial'    => substr( $ini, 0, 2 ),
		);
	}
	return $out;
}
