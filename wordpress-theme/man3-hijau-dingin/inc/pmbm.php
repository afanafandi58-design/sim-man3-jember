<?php
/**
 * PMBM — Penerimaan Murid Baru Madrasah (halaman /pmbm/ + menu atas).
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parent admin menu.
 */
function man3_pmbm_admin_menu() {
	add_menu_page(
		__( 'PMBM', 'man3-hijau-dingin' ),
		__( 'PMBM', 'man3-hijau-dingin' ),
		'edit_posts',
		'man3-pmbm',
		'man3_pmbm_admin_landing',
		'dashicons-welcome-learn-more',
		25
	);
}
add_action( 'admin_menu', 'man3_pmbm_admin_menu' );

/**
 * Simpan Informasi dari landing.
 */
function man3_pmbm_save_informasi() {
	if ( ! isset( $_POST['man3_pmbm_info_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_pmbm_info_nonce'] ) ), 'man3_pmbm_info_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$title = isset( $_POST['man3_pmbm_info_title'] ) ? sanitize_text_field( wp_unslash( $_POST['man3_pmbm_info_title'] ) ) : '';
	$body  = isset( $_POST['man3_pmbm_info_body'] ) ? wp_kses_post( wp_unslash( $_POST['man3_pmbm_info_body'] ) ) : '';
	update_option( 'man3_pmbm_info_title', $title, false );
	update_option( 'man3_pmbm_info_body', $body, false );
	add_settings_error( 'man3_pmbm_info', 'man3_pmbm_info_saved', __( 'Informasi PMBM disimpan.', 'man3-hijau-dingin' ), 'updated' );
}
add_action( 'admin_init', 'man3_pmbm_save_informasi' );

/**
 * Landing admin PMBM + form Informasi.
 */
function man3_pmbm_admin_landing() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$title = (string) get_option( 'man3_pmbm_info_title', '' );
	$body  = (string) get_option( 'man3_pmbm_info_body', '' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'PMBM', 'man3-hijau-dingin' ); ?></h1>
		<p><?php esc_html_e( 'Penerimaan Murid Baru Madrasah — kelola poster, tautan, dan informasi di halaman /pmbm/.', 'man3-hijau-dingin' ); ?></p>
		<ul>
			<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=man3_pmbm_poster' ) ); ?>"><?php esc_html_e( 'Poster', 'man3-hijau-dingin' ); ?></a> — <?php esc_html_e( 'gambar, urutan, aktif', 'man3-hijau-dingin' ); ?></li>
			<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=man3_pmbm_tautan' ) ); ?>"><?php esc_html_e( 'Tautan', 'man3-hijau-dingin' ); ?></a> — <?php esc_html_e( 'nama, URL, urutan, aktif', 'man3-hijau-dingin' ); ?></li>
			<li><a href="<?php echo esc_url( home_url( '/pmbm/' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Lihat halaman publik', 'man3-hijau-dingin' ); ?></a></li>
		</ul>

		<hr />
		<h2><?php esc_html_e( 'Informasi', 'man3-hijau-dingin' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Judul dan isi tambahan di bawah poster/tautan. Kosongkan keduanya agar blok Informasi tidak tampil.', 'man3-hijau-dingin' ); ?></p>
		<?php settings_errors( 'man3_pmbm_info' ); ?>
		<form method="post" action="">
			<?php wp_nonce_field( 'man3_pmbm_info_save', 'man3_pmbm_info_nonce' ); ?>
			<p>
				<label for="man3_pmbm_info_title"><strong><?php esc_html_e( 'Judul', 'man3-hijau-dingin' ); ?></strong></label><br />
				<input type="text" class="regular-text" id="man3_pmbm_info_title" name="man3_pmbm_info_title" value="<?php echo esc_attr( $title ); ?>" style="max-width:32rem;width:100%;" />
			</p>
			<p>
				<label for="man3_pmbm_info_body"><strong><?php esc_html_e( 'Isi', 'man3-hijau-dingin' ); ?></strong></label>
			</p>
			<?php
			wp_editor(
				$body,
				'man3_pmbm_info_body',
				array(
					'textarea_name' => 'man3_pmbm_info_body',
					'textarea_rows' => 10,
					'media_buttons' => true,
					'teeny'         => false,
				)
			);
			?>
			<p>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Simpan Informasi', 'man3-hijau-dingin' ); ?></button>
			</p>
		</form>
	</div>
	<?php
}

/**
 * Register Poster + Tautan CPTs.
 */
function man3_pmbm_register_cpts() {
	register_post_type(
		'man3_pmbm_poster',
		array(
			'labels'             => array(
				'name'               => __( 'Poster PMBM', 'man3-hijau-dingin' ),
				'singular_name'      => __( 'Poster PMBM', 'man3-hijau-dingin' ),
				'add_new'            => __( 'Tambah Poster', 'man3-hijau-dingin' ),
				'add_new_item'       => __( 'Tambah Poster PMBM', 'man3-hijau-dingin' ),
				'edit_item'          => __( 'Edit Poster PMBM', 'man3-hijau-dingin' ),
				'menu_name'          => __( 'Poster', 'man3-hijau-dingin' ),
				'all_items'          => __( 'Poster', 'man3-hijau-dingin' ),
				'featured_image'     => __( 'Gambar Poster', 'man3-hijau-dingin' ),
				'set_featured_image' => __( 'Set gambar poster', 'man3-hijau-dingin' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'man3-pmbm',
			'supports'           => array( 'title', 'thumbnail', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		)
	);

	register_post_type(
		'man3_pmbm_tautan',
		array(
			'labels'             => array(
				'name'          => __( 'Tautan PMBM', 'man3-hijau-dingin' ),
				'singular_name' => __( 'Tautan PMBM', 'man3-hijau-dingin' ),
				'add_new'       => __( 'Tambah Tautan', 'man3-hijau-dingin' ),
				'add_new_item'  => __( 'Tambah Tautan PMBM', 'man3-hijau-dingin' ),
				'edit_item'     => __( 'Edit Tautan PMBM', 'man3-hijau-dingin' ),
				'menu_name'     => __( 'Tautan', 'man3-hijau-dingin' ),
				'all_items'     => __( 'Tautan', 'man3-hijau-dingin' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'man3-pmbm',
			'supports'           => array( 'title', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		)
	);
}
add_action( 'init', 'man3_pmbm_register_cpts' );

/**
 * Meta aktif poster.
 */
function man3_pmbm_poster_meta_box() {
	add_meta_box(
		'man3_pmbm_poster_meta',
		__( 'Pengaturan Poster', 'man3-hijau-dingin' ),
		'man3_pmbm_poster_meta_render',
		'man3_pmbm_poster',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_pmbm_poster_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function man3_pmbm_poster_meta_render( $post ) {
	wp_nonce_field( 'man3_pmbm_poster_save', 'man3_pmbm_poster_nonce' );
	$active = get_post_meta( $post->ID, '_man3_pmbm_active', true );
	if ( '' === $active ) {
		$active = '1';
	}
	?>
	<p>
		<label>
			<input type="checkbox" name="man3_pmbm_active" value="1" <?php checked( $active, '1' ); ?> />
			<?php esc_html_e( 'Aktif di halaman PMBM', 'man3-hijau-dingin' ); ?>
		</label>
	</p>
	<p class="description"><?php esc_html_e( 'Gambar = Gambar Unggulan. Urutan = Order (angka kecil lebih dulu).', 'man3-hijau-dingin' ); ?></p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function man3_pmbm_poster_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_pmbm_poster_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_pmbm_poster_nonce'] ) ), 'man3_pmbm_poster_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_man3_pmbm_active', isset( $_POST['man3_pmbm_active'] ) ? '1' : '0' );
}
add_action( 'save_post_man3_pmbm_poster', 'man3_pmbm_poster_meta_save' );

/**
 * Meta URL + aktif tautan.
 */
function man3_pmbm_tautan_meta_box() {
	add_meta_box(
		'man3_pmbm_tautan_meta',
		__( 'Pengaturan Tautan', 'man3-hijau-dingin' ),
		'man3_pmbm_tautan_meta_render',
		'man3_pmbm_tautan',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_pmbm_tautan_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function man3_pmbm_tautan_meta_render( $post ) {
	wp_nonce_field( 'man3_pmbm_tautan_save', 'man3_pmbm_tautan_nonce' );
	$url    = get_post_meta( $post->ID, '_man3_pmbm_url', true );
	$active = get_post_meta( $post->ID, '_man3_pmbm_active', true );
	if ( '' === $active ) {
		$active = '1';
	}
	?>
	<p>
		<label for="man3_pmbm_url"><strong><?php esc_html_e( 'URL', 'man3-hijau-dingin' ); ?></strong></label>
		<input type="url" class="widefat" id="man3_pmbm_url" name="man3_pmbm_url" value="<?php echo esc_attr( (string) $url ); ?>" placeholder="https://" />
	</p>
	<p>
		<label>
			<input type="checkbox" name="man3_pmbm_active" value="1" <?php checked( $active, '1' ); ?> />
			<?php esc_html_e( 'Aktif di halaman PMBM', 'man3-hijau-dingin' ); ?>
		</label>
	</p>
	<p class="description"><?php esc_html_e( 'Nama = judul. Urutan = Order.', 'man3-hijau-dingin' ); ?></p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function man3_pmbm_tautan_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_pmbm_tautan_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_pmbm_tautan_nonce'] ) ), 'man3_pmbm_tautan_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$url = isset( $_POST['man3_pmbm_url'] ) ? esc_url_raw( wp_unslash( $_POST['man3_pmbm_url'] ) ) : '';
	update_post_meta( $post_id, '_man3_pmbm_url', $url );
	update_post_meta( $post_id, '_man3_pmbm_active', isset( $_POST['man3_pmbm_active'] ) ? '1' : '0' );
}
add_action( 'save_post_man3_pmbm_tautan', 'man3_pmbm_tautan_meta_save' );

/**
 * @param array $cols Columns.
 * @return array
 */
function man3_pmbm_poster_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['man3_pmbm_thumb']  = __( 'Gambar', 'man3-hijau-dingin' );
			$new['man3_pmbm_active'] = __( 'Aktif', 'man3-hijau-dingin' );
			$new['man3_pmbm_order']  = __( 'Urutan', 'man3-hijau-dingin' );
		}
	}
	return $new;
}
add_filter( 'manage_man3_pmbm_poster_posts_columns', 'man3_pmbm_poster_columns' );

/**
 * @param string $col Column.
 * @param int    $post_id Post ID.
 */
function man3_pmbm_poster_column_content( $col, $post_id ) {
	if ( 'man3_pmbm_thumb' === $col ) {
		$thumb = get_the_post_thumbnail( $post_id, array( 72, 48 ) );
		echo $thumb ? $thumb : '&mdash;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( 'man3_pmbm_active' === $col ) {
		$active = get_post_meta( $post_id, '_man3_pmbm_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		echo '1' === $active ? esc_html__( 'Ya', 'man3-hijau-dingin' ) : esc_html__( 'Tidak', 'man3-hijau-dingin' );
	}
	if ( 'man3_pmbm_order' === $col ) {
		echo esc_html( (string) get_post_field( 'menu_order', $post_id ) );
	}
}
add_action( 'manage_man3_pmbm_poster_posts_custom_column', 'man3_pmbm_poster_column_content', 10, 2 );

/**
 * @param array $cols Columns.
 * @return array
 */
function man3_pmbm_tautan_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['man3_pmbm_url']    = __( 'URL', 'man3-hijau-dingin' );
			$new['man3_pmbm_active'] = __( 'Aktif', 'man3-hijau-dingin' );
			$new['man3_pmbm_order']  = __( 'Urutan', 'man3-hijau-dingin' );
		}
	}
	return $new;
}
add_filter( 'manage_man3_pmbm_tautan_posts_columns', 'man3_pmbm_tautan_columns' );

/**
 * @param string $col Column.
 * @param int    $post_id Post ID.
 */
function man3_pmbm_tautan_column_content( $col, $post_id ) {
	if ( 'man3_pmbm_url' === $col ) {
		$url = get_post_meta( $post_id, '_man3_pmbm_url', true );
		echo $url ? '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $url ) . '</a>' : '&mdash;';
	}
	if ( 'man3_pmbm_active' === $col ) {
		$active = get_post_meta( $post_id, '_man3_pmbm_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		echo '1' === $active ? esc_html__( 'Ya', 'man3-hijau-dingin' ) : esc_html__( 'Tidak', 'man3-hijau-dingin' );
	}
	if ( 'man3_pmbm_order' === $col ) {
		echo esc_html( (string) get_post_field( 'menu_order', $post_id ) );
	}
}
add_action( 'manage_man3_pmbm_tautan_posts_custom_column', 'man3_pmbm_tautan_column_content', 10, 2 );

/**
 * Pastikan halaman /pmbm/.
 *
 * @return int
 */
function man3_pmbm_ensure_page() {
	$page = get_page_by_path( 'pmbm' );
	if ( $page instanceof WP_Post ) {
		update_post_meta( $page->ID, '_wp_page_template', 'page-pmbm.php' );
		wp_update_post(
			array(
				'ID'          => $page->ID,
				'post_status' => 'publish',
				'post_title'  => 'PMBM',
			)
		);
		return (int) $page->ID;
	}

	$id = wp_insert_post(
		array(
			'post_title'   => 'PMBM',
			'post_name'    => 'pmbm',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		),
		true
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	update_post_meta( (int) $id, '_wp_page_template', 'page-pmbm.php' );
	return (int) $id;
}

/**
 * Sisipkan menu atas PMBM di akhir (setelah PPID).
 */
function man3_pmbm_ensure_menu() {
	$page_id = man3_pmbm_ensure_page();
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

	$pmbm_item = null;
	$max_order = 0;
	foreach ( $items as $item ) {
		if ( 0 !== (int) $item->menu_item_parent ) {
			continue;
		}
		$title = trim( wp_strip_all_tags( $item->title ) );
		if (
			0 === strcasecmp( $title, 'PMBM' )
			|| ( (int) $item->object_id === $page_id && 'page' === $item->object )
			|| false !== stripos( (string) $item->url, '/pmbm' )
		) {
			$pmbm_item = $item;
		}
		$max_order = max( $max_order, (int) $item->menu_order );
	}

	$args = array(
		'menu-item-title'     => 'PMBM',
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $page_id,
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
		'menu-item-parent-id' => 0,
		'menu-item-position'  => $max_order + 10,
	);

	if ( $pmbm_item ) {
		$pmbm_id = wp_update_nav_menu_item( $menu_id, (int) $pmbm_item->ID, $args );
	} else {
		$pmbm_id = wp_update_nav_menu_item( $menu_id, 0, $args );
	}
	if ( is_wp_error( $pmbm_id ) || ! $pmbm_id ) {
		return;
	}

	/* Pastikan PMBM di urutan terakhir top-level. */
	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		return;
	}
	$tops = array();
	$pmbm = null;
	foreach ( $items as $item ) {
		if ( 0 !== (int) $item->menu_item_parent ) {
			continue;
		}
		$title = trim( wp_strip_all_tags( $item->title ) );
		if ( 0 === strcasecmp( $title, 'PMBM' ) || (int) $item->ID === (int) $pmbm_id ) {
			$pmbm = $item;
			continue;
		}
		$tops[] = $item;
	}
	if ( ! $pmbm ) {
		return;
	}
	$tops[] = $pmbm;
	$pos    = 1;
	foreach ( $tops as $item ) {
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
 * Bootstrap admin.
 */
function man3_pmbm_admin_bootstrap() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	man3_pmbm_ensure_page();
	man3_pmbm_ensure_menu();
}
add_action( 'admin_init', 'man3_pmbm_admin_bootstrap' );

/**
 * Bootstrap depan (sekali).
 */
function man3_pmbm_front_bootstrap() {
	if ( get_option( 'man3_pmbm_page_ok_v1' ) ) {
		return;
	}
	man3_pmbm_ensure_page();
	man3_pmbm_ensure_menu();
	update_option( 'man3_pmbm_page_ok_v1', 1, false );
}
add_action( 'init', 'man3_pmbm_front_bootstrap', 40 );

/**
 * Poster aktif (urut Order).
 *
 * @return array<int,array<string,mixed>>
 */
function man3_pmbm_get_posters() {
	$posts = get_posts(
		array(
			'post_type'      => 'man3_pmbm_poster',
			'posts_per_page' => 40,
			'post_status'    => 'publish',
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
		)
	);
	$out = array();
	foreach ( $posts as $p ) {
		$active = get_post_meta( $p->ID, '_man3_pmbm_active', true );
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
			'id'    => (int) $p->ID,
			'title' => get_the_title( $p ),
			'url'   => $url,
		);
	}
	return $out;
}

/**
 * Tautan aktif.
 *
 * @return array<int,array<string,string>>
 */
function man3_pmbm_get_tautan() {
	$posts = get_posts(
		array(
			'post_type'      => 'man3_pmbm_tautan',
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
		$active = get_post_meta( $p->ID, '_man3_pmbm_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		if ( '1' !== $active ) {
			continue;
		}
		$url = trim( (string) get_post_meta( $p->ID, '_man3_pmbm_url', true ) );
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

/**
 * Informasi (judul + isi). Kosong = tidak tampil.
 *
 * @return array{title:string,body:string}|null
 */
function man3_pmbm_get_informasi() {
	$title = trim( (string) get_option( 'man3_pmbm_info_title', '' ) );
	$body  = trim( (string) get_option( 'man3_pmbm_info_body', '' ) );
	$body_plain = trim( wp_strip_all_tags( $body ) );
	if ( '' === $title && '' === $body_plain ) {
		return null;
	}
	return array(
		'title' => $title,
		'body'  => $body,
	);
}
