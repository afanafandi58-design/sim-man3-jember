<?php
/**
 * CPT Ekstra Kurikuler + seed + halaman /ekskul/ + menu.
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data_file = __DIR__ . '/ekskul-data.php';
if ( file_exists( $data_file ) ) {
	require_once $data_file;
}

/**
 * Register CPT.
 */
function man3_ekskul_register_cpt() {
	register_post_type(
		'man3_ekskul',
		array(
			'labels'             => array(
				'name'          => __( 'Ekstra Kurikuler', 'man3-hijau-dingin' ),
				'singular_name' => __( 'Ekskul', 'man3-hijau-dingin' ),
				'add_new_item'  => __( 'Tambah Ekskul', 'man3-hijau-dingin' ),
				'edit_item'     => __( 'Edit Ekskul', 'man3-hijau-dingin' ),
				'menu_name'     => __( 'Ekstra Kurikuler', 'man3-hijau-dingin' ),
			),
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-universal-access-alt',
			'menu_position'      => 24,
			'supports'           => array( 'title', 'thumbnail', 'editor', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
		)
	);
}
add_action( 'init', 'man3_ekskul_register_cpt' );

/**
 * Meta box.
 */
function man3_ekskul_meta_box() {
	add_meta_box(
		'man3_ekskul_meta',
		__( 'Detail Ekskul', 'man3-hijau-dingin' ),
		'man3_ekskul_meta_box_render',
		'man3_ekskul',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_ekskul_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function man3_ekskul_meta_box_render( $post ) {
	wp_nonce_field( 'man3_ekskul_save', 'man3_ekskul_nonce' );
	$kategori = get_post_meta( $post->ID, '_man3_ekskul_kategori', true );
	$pembina  = get_post_meta( $post->ID, '_man3_ekskul_pembina', true );
	$unit     = get_post_meta( $post->ID, '_man3_ekskul_unit', true );
	$labels   = function_exists( 'man3_ekskul_kategori_labels' ) ? man3_ekskul_kategori_labels() : array();
	if ( ! $kategori ) {
		$kategori = 'karakter';
	}
	?>
	<p>
		<label for="man3_ekskul_kategori"><strong><?php esc_html_e( 'Kategori', 'man3-hijau-dingin' ); ?></strong></label><br />
		<select name="man3_ekskul_kategori" id="man3_ekskul_kategori">
			<?php foreach ( $labels as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $kategori, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="man3_ekskul_pembina"><strong><?php esc_html_e( 'Pembina / pelatih', 'man3-hijau-dingin' ); ?></strong></label>
		<input type="text" class="widefat" id="man3_ekskul_pembina" name="man3_ekskul_pembina" value="<?php echo esc_attr( $pembina ); ?>" placeholder="Nama pembina (opsional)" />
	</p>
	<p>
		<label for="man3_ekskul_unit"><strong><?php esc_html_e( 'Nama unit / gugus (opsional)', 'man3-hijau-dingin' ); ?></strong></label>
		<input type="text" class="widefat" id="man3_ekskul_unit" name="man3_ekskul_unit" value="<?php echo esc_attr( $unit ); ?>" placeholder="mis. PMR Wira Kusuma" />
	</p>
	<p class="description">
		<?php esc_html_e( 'Deskripsi: isi di editor utama. Foto: set Gambar Unggulan. Kosong = placeholder inisial.', 'man3-hijau-dingin' ); ?>
	</p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function man3_ekskul_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_ekskul_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_ekskul_nonce'] ) ), 'man3_ekskul_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$labels = function_exists( 'man3_ekskul_kategori_labels' ) ? man3_ekskul_kategori_labels() : array();
	if ( isset( $_POST['man3_ekskul_kategori'] ) ) {
		$kat = sanitize_key( wp_unslash( $_POST['man3_ekskul_kategori'] ) );
		if ( ! isset( $labels[ $kat ] ) ) {
			$kat = 'karakter';
		}
		update_post_meta( $post_id, '_man3_ekskul_kategori', $kat );
	}
	if ( isset( $_POST['man3_ekskul_pembina'] ) ) {
		update_post_meta( $post_id, '_man3_ekskul_pembina', sanitize_text_field( wp_unslash( $_POST['man3_ekskul_pembina'] ) ) );
	}
	if ( isset( $_POST['man3_ekskul_unit'] ) ) {
		update_post_meta( $post_id, '_man3_ekskul_unit', sanitize_text_field( wp_unslash( $_POST['man3_ekskul_unit'] ) ) );
	}
}
add_action( 'save_post_man3_ekskul', 'man3_ekskul_meta_save' );

/**
 * Cari attachment by filename basename.
 *
 * @param string $filename File name.
 * @return int Attachment ID or 0.
 */
function man3_ekskul_find_attachment( $filename ) {
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
 * Seed once / force.
 */
function man3_ekskul_run_seed() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$force = isset( $_GET['man3_ekskul_reseed'] ) && '1' === $_GET['man3_ekskul_reseed'];
	if ( ! $force && get_option( 'man3_ekskul_seeded_v1' ) ) {
		return;
	}
	man3_ekskul_do_seed();
	update_option( 'man3_ekskul_seeded_v1', 1 );
}
add_action( 'admin_init', 'man3_ekskul_run_seed' );

/**
 * Front reseed trigger.
 */
function man3_ekskul_front_seed_trigger() {
	if ( ! isset( $_GET['man3_ekskul_reseed'] ) || '1' !== $_GET['man3_ekskul_reseed'] ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	man3_ekskul_do_seed();
	update_option( 'man3_ekskul_seeded_v1', 1 );
	wp_safe_redirect( home_url( '/ekskul/' ) );
	exit;
}
add_action( 'template_redirect', 'man3_ekskul_front_seed_trigger' );

/**
 * Seed CPT + page + menu.
 */
function man3_ekskul_do_seed() {
	if ( ! function_exists( 'man3_ekskul_seed_rows' ) ) {
		return;
	}
	foreach ( man3_ekskul_seed_rows() as $row ) {
		$existing = get_posts(
			array(
				'post_type'      => 'man3_ekskul',
				'name'           => $row['key'],
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);
		$postarr = array(
			'post_title'   => $row['nama'],
			'post_name'    => $row['key'],
			'post_status'  => 'publish',
			'post_type'    => 'man3_ekskul',
			'post_content' => $row['deskripsi'],
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
		update_post_meta( $pid, '_man3_ekskul_kategori', $row['kategori'] );
		update_post_meta( $pid, '_man3_ekskul_pembina', $row['pembina'] );
		update_post_meta( $pid, '_man3_ekskul_unit', $row['unit'] );

		if ( empty( get_post_thumbnail_id( $pid ) ) && ! empty( $row['image_file'] ) ) {
			$att = man3_ekskul_find_attachment( $row['image_file'] );
			if ( $att ) {
				set_post_thumbnail( $pid, $att );
			}
		}
	}

	$page = get_page_by_path( 'ekskul' );
	if ( ! $page ) {
		$pid = wp_insert_post(
			array(
				'post_title'   => 'Ekstra Kurikuler',
				'post_name'    => 'ekskul',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- Ekstra Kurikuler MAN 3 Jember -->',
			)
		);
		if ( $pid && ! is_wp_error( $pid ) ) {
			update_post_meta( $pid, '_wp_page_template', 'page-ekskul.php' );
		}
	} else {
		wp_update_post(
			array(
				'ID'          => $page->ID,
				'post_title'  => 'Ekstra Kurikuler',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $page->ID, '_wp_page_template', 'page-ekskul.php' );
	}

	man3_ekskul_ensure_menu();
}

/**
 * Pastikan item menu Ekstra Kurikuler mengarah ke halaman /ekskul/.
 */
function man3_ekskul_ensure_menu() {
	$page = get_page_by_path( 'ekskul' );
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

	$items = wp_get_nav_menu_items( $menu_id );
	$found = null;
	if ( $items ) {
		foreach ( $items as $item ) {
			$title = trim( wp_strip_all_tags( $item->title ) );
			$url   = isset( $item->url ) ? (string) $item->url : '';
			if (
				0 === strcasecmp( $title, 'Ekstra Kurikuler' )
				|| 0 === strcasecmp( $title, 'Ekstrakurikuler' )
				|| 0 === strcasecmp( $title, 'Ekskul' )
				|| (int) $item->object_id === $page_id
				|| false !== stripos( $url, '/ekskul' )
			) {
				$found = $item;
				break;
			}
		}
	}

	$args = array(
		'menu-item-title'     => 'Ekstra Kurikuler',
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $page_id,
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
		'menu-item-parent-id' => 0,
	);
	if ( ! $found ) {
		wp_update_nav_menu_item( $menu_id, 0, $args );
	} else {
		$args['menu-item-parent-id'] = (int) $found->menu_item_parent;
		wp_update_nav_menu_item( $menu_id, (int) $found->ID, $args );
	}
}

/**
 * Items for front.
 *
 * @param string $kategori all|olahraga|karakter|seni.
 * @return array<int,array<string,mixed>>
 */
function man3_ekskul_get_items( $kategori = 'all' ) {
	$posts = get_posts(
		array(
			'post_type'      => 'man3_ekskul',
			'posts_per_page' => 100,
			'post_status'    => 'publish',
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);
	$labels = function_exists( 'man3_ekskul_kategori_labels' ) ? man3_ekskul_kategori_labels() : array();
	$out    = array();
	foreach ( $posts as $p ) {
		$kat = get_post_meta( $p->ID, '_man3_ekskul_kategori', true ) ?: 'karakter';
		if ( 'all' !== $kategori && $kat !== $kategori ) {
			continue;
		}
		$name  = get_the_title( $p );
		$parts = preg_split( '/\s+/', trim( $name ) );
		$ini   = '';
		foreach ( array_slice( $parts, 0, 2 ) as $w ) {
			if ( $w ) {
				$ini .= strtoupper( substr( $w, 0, 1 ) );
			}
		}
		if ( strlen( $ini ) < 1 ) {
			$ini = 'E';
		}
		$content = trim( wp_strip_all_tags( $p->post_content ) );
		$out[]   = array(
			'id'         => $p->ID,
			'nama'       => $name,
			'slug'       => $p->post_name,
			'kategori'   => $kat,
			'kategori_label' => isset( $labels[ $kat ] ) ? $labels[ $kat ] : $kat,
			'pembina'    => get_post_meta( $p->ID, '_man3_ekskul_pembina', true ),
			'unit'       => get_post_meta( $p->ID, '_man3_ekskul_unit', true ),
			'deskripsi'  => $content,
			'excerpt'    => wp_trim_words( $content, 28, '…' ),
			'thumb'      => get_the_post_thumbnail_url( $p, 'large' ),
			'inisial'    => mb_substr( $ini, 0, 2 ),
		);
	}
	return $out;
}
