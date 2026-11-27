<?php
/**
 * Agenda — CPT + flyer + halaman /agenda/ (Info Madrasah).
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seed resmi Desember 2026 (bukan karangan).
 *
 * @return array<int,array<string,string|int>>
 */
function man3_agenda_seed_rows() {
	return array(
		array(
			'key'       => 'asas-gasal-2026',
			'tanggal'   => '23 Nov – 5 Des 2026',
			'judul'     => 'ASAS Gasal',
			'keterangan'=> 'Asesmen Sumatif Akhir Semester Gasal Tahun Pelajaran 2026/2027.',
			'order'     => 10,
		),
		array(
			'key'       => 'pengolahan-nilai-rapor-2026',
			'tanggal'   => '6 – 17 Des 2026',
			'judul'     => 'Pengolahan Nilai & Persiapan Rapor',
			'keterangan'=> 'Rekapitulasi dan finalisasi nilai di RDM serta penyelesaian administrasi pembelajaran.',
			'order'     => 20,
		),
		array(
			'key'       => 'pembagian-rapor-2026',
			'tanggal'   => '18 atau 19 Des 2026',
			'judul'     => 'Pembagian Rapor',
			'keterangan'=> 'Penyerahan Laporan Hasil Belajar (Rapor) Semester Gasal kepada orang tua/wali murid.',
			'order'     => 30,
		),
		array(
			'key'       => 'libur-semester-gasal-2026',
			'tanggal'   => '21 Des 2026 – 2 Jan 2027',
			'judul'     => 'Libur Semester Gasal',
			'keterangan'=> 'Libur akhir semester gasal Tahun Pelajaran 2026/2027.',
			'order'     => 40,
		),
		array(
			'key'       => 'masuk-kembali-2027',
			'tanggal'   => '4 Jan 2027',
			'judul'     => 'Masuk Kembali',
			'keterangan'=> 'Awal pembelajaran Semester Genap Tahun Pelajaran 2026/2027.',
			'order'     => 50,
		),
	);
}

/**
 * Register CPT agenda.
 */
function man3_agenda_register_cpt() {
	register_post_type(
		'agenda',
		array(
			'labels'             => array(
				'name'               => __( 'Agenda', 'man3-hijau-dingin' ),
				'singular_name'      => __( 'Agenda', 'man3-hijau-dingin' ),
				'add_new'            => __( 'Tambah Agenda', 'man3-hijau-dingin' ),
				'add_new_item'       => __( 'Tambah Agenda', 'man3-hijau-dingin' ),
				'edit_item'          => __( 'Edit Agenda', 'man3-hijau-dingin' ),
				'menu_name'          => __( 'Agenda', 'man3-hijau-dingin' ),
				'all_items'          => __( 'Semua Agenda', 'man3-hijau-dingin' ),
				'featured_image'     => __( 'Gambar Agenda', 'man3-hijau-dingin' ),
				'set_featured_image' => __( 'Set gambar agenda', 'man3-hijau-dingin' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-calendar-alt',
			'menu_position'      => 21,
			'supports'           => array( 'title', 'editor', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		)
	);
}
add_action( 'init', 'man3_agenda_register_cpt' );

/**
 * Meta: tanggal/rentang + aktif.
 */
function man3_agenda_meta_box() {
	add_meta_box(
		'man3_agenda_meta',
		__( 'Pengaturan Agenda', 'man3-hijau-dingin' ),
		'man3_agenda_meta_box_render',
		'agenda',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_agenda_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function man3_agenda_meta_box_render( $post ) {
	wp_nonce_field( 'man3_agenda_save', 'man3_agenda_nonce' );
	$tanggal = get_post_meta( $post->ID, '_man3_agenda_tanggal', true );
	$active  = get_post_meta( $post->ID, '_man3_agenda_active', true );
	if ( '' === $active ) {
		$active = '1';
	}
	?>
	<p>
		<label for="man3_agenda_tanggal"><strong><?php esc_html_e( 'Tanggal / rentang', 'man3-hijau-dingin' ); ?></strong></label>
		<input type="text" class="widefat" id="man3_agenda_tanggal" name="man3_agenda_tanggal" value="<?php echo esc_attr( (string) $tanggal ); ?>" placeholder="contoh: 23 Nov – 5 Des 2026" />
	</p>
	<p>
		<label>
			<input type="checkbox" name="man3_agenda_active" value="1" <?php checked( $active, '1' ); ?> />
			<?php esc_html_e( 'Aktif di halaman Agenda', 'man3-hijau-dingin' ); ?>
		</label>
	</p>
	<p class="description">
		<?php esc_html_e( 'Judul = judul. Keterangan = editor. Urutan = Order (angka kecil lebih dulu). Flyer diganti di Agenda → Flyer.', 'man3-hijau-dingin' ); ?>
	</p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function man3_agenda_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_agenda_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_agenda_nonce'] ) ), 'man3_agenda_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$tanggal = isset( $_POST['man3_agenda_tanggal'] ) ? sanitize_text_field( wp_unslash( $_POST['man3_agenda_tanggal'] ) ) : '';
	update_post_meta( $post_id, '_man3_agenda_tanggal', $tanggal );
	update_post_meta( $post_id, '_man3_agenda_active', isset( $_POST['man3_agenda_active'] ) ? '1' : '0' );
}
add_action( 'save_post_agenda', 'man3_agenda_meta_save' );

/**
 * @param array $cols Columns.
 * @return array
 */
function man3_agenda_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['man3_agenda_tanggal'] = __( 'Tanggal', 'man3-hijau-dingin' );
			$new['man3_agenda_active']  = __( 'Aktif', 'man3-hijau-dingin' );
			$new['man3_agenda_order']   = __( 'Urutan', 'man3-hijau-dingin' );
		}
	}
	return $new;
}
add_filter( 'manage_agenda_posts_columns', 'man3_agenda_columns' );

/**
 * @param string $col Column.
 * @param int    $post_id Post ID.
 */
function man3_agenda_column_content( $col, $post_id ) {
	if ( 'man3_agenda_tanggal' === $col ) {
		$t = get_post_meta( $post_id, '_man3_agenda_tanggal', true );
		echo $t ? esc_html( (string) $t ) : '&mdash;';
	}
	if ( 'man3_agenda_active' === $col ) {
		$active = get_post_meta( $post_id, '_man3_agenda_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		echo '1' === $active ? esc_html__( 'Ya', 'man3-hijau-dingin' ) : esc_html__( 'Tidak', 'man3-hijau-dingin' );
	}
	if ( 'man3_agenda_order' === $col ) {
		echo esc_html( (string) get_post_field( 'menu_order', $post_id ) );
	}
}
add_action( 'manage_agenda_posts_custom_column', 'man3_agenda_column_content', 10, 2 );

/**
 * Submenu Flyer di bawah Agenda.
 */
function man3_agenda_flyer_menu() {
	add_submenu_page(
		'edit.php?post_type=agenda',
		__( 'Flyer Agenda', 'man3-hijau-dingin' ),
		__( 'Flyer', 'man3-hijau-dingin' ),
		'edit_posts',
		'man3-agenda-flyer',
		'man3_agenda_flyer_page_render'
	);
}
add_action( 'admin_menu', 'man3_agenda_flyer_menu' );

/**
 * Assets media picker halaman Flyer.
 *
 * @param string $hook Hook.
 */
function man3_agenda_flyer_admin_assets( $hook ) {
	if ( 'agenda_page_man3-agenda-flyer' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_add_inline_script(
		'jquery',
		"(function($){
			$(function(){
				var frame;
				$('#man3_agenda_flyer_pick').on('click', function(e){
					e.preventDefault();
					if (frame) { frame.open(); return; }
					frame = wp.media({
						title: 'Pilih flyer agenda',
						button: { text: 'Pakai gambar ini' },
						multiple: false,
						library: { type: 'image' }
					});
					frame.on('select', function(){
						var att = frame.state().get('selection').first().toJSON();
						$('#man3_agenda_flyer_id').val(att.id);
						var url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
						$('#man3_agenda_flyer_preview').html('<img src=\"'+url+'\" alt=\"\" style=\"max-width:280px;height:auto;border:1px solid #ccd0d4;border-radius:6px\" />');
						$('#man3_agenda_flyer_clear').show();
					});
					frame.open();
				});
				$('#man3_agenda_flyer_clear').on('click', function(e){
					e.preventDefault();
					$('#man3_agenda_flyer_id').val('0');
					$('#man3_agenda_flyer_preview').html('<em>Belum ada flyer.</em>');
					$(this).hide();
				});
			});
		})(jQuery);"
	);
}
add_action( 'admin_enqueue_scripts', 'man3_agenda_flyer_admin_assets' );

/**
 * Halaman admin Flyer.
 */
function man3_agenda_flyer_page_render() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	if ( isset( $_POST['man3_agenda_flyer_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_agenda_flyer_nonce'] ) ), 'man3_agenda_flyer_save' ) ) {
		$id = isset( $_POST['man3_agenda_flyer_id'] ) ? absint( $_POST['man3_agenda_flyer_id'] ) : 0;
		update_option( 'man3_agenda_flyer_id', $id, false );
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Flyer disimpan.', 'man3-hijau-dingin' ) . '</p></div>';
	}
	$flyer_id = (int) get_option( 'man3_agenda_flyer_id', 0 );
	$url      = $flyer_id ? wp_get_attachment_image_url( $flyer_id, 'medium' ) : '';
	if ( ! $url && $flyer_id ) {
		$url = wp_get_attachment_url( $flyer_id );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Flyer Agenda', 'man3-hijau-dingin' ); ?></h1>
		<p><?php esc_html_e( 'Gambar flyer tampil di atas daftar agenda di halaman /agenda/. Disarankan object-fit contain (utuh, tidak terpotong).', 'man3-hijau-dingin' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'man3_agenda_flyer_save', 'man3_agenda_flyer_nonce' ); ?>
			<input type="hidden" id="man3_agenda_flyer_id" name="man3_agenda_flyer_id" value="<?php echo esc_attr( (string) $flyer_id ); ?>" />
			<div id="man3_agenda_flyer_preview" style="margin:1rem 0;">
				<?php if ( $url ) : ?>
					<img src="<?php echo esc_url( $url ); ?>" alt="" style="max-width:280px;height:auto;border:1px solid #ccd0d4;border-radius:6px" />
				<?php else : ?>
					<em><?php esc_html_e( 'Belum ada flyer.', 'man3-hijau-dingin' ); ?></em>
				<?php endif; ?>
			</div>
			<p>
				<button type="button" class="button" id="man3_agenda_flyer_pick"><?php esc_html_e( 'Pilih / unggah flyer', 'man3-hijau-dingin' ); ?></button>
				<button type="button" class="button" id="man3_agenda_flyer_clear" <?php echo $flyer_id ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Hapus flyer', 'man3-hijau-dingin' ); ?></button>
			</p>
			<?php submit_button( __( 'Simpan flyer', 'man3-hijau-dingin' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Pastikan halaman /agenda/.
 *
 * @return int
 */
function man3_agenda_ensure_page() {
	$page = get_page_by_path( 'agenda' );
	if ( $page instanceof WP_Post ) {
		update_post_meta( $page->ID, '_wp_page_template', 'page-agenda.php' );
		wp_update_post(
			array(
				'ID'          => $page->ID,
				'post_status' => 'publish',
				'post_title'  => 'Agenda',
			)
		);
		return (int) $page->ID;
	}

	$id = wp_insert_post(
		array(
			'post_title'   => 'Agenda',
			'post_name'    => 'agenda',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		),
		true
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	update_post_meta( (int) $id, '_wp_page_template', 'page-agenda.php' );
	return (int) $id;
}

/**
 * Pastikan menu Info Madrasah → Agenda (posisi tidak dipindah).
 */
function man3_agenda_ensure_menu() {
	$page_id = man3_agenda_ensure_page();
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
				0 === strcasecmp( $title, 'Agenda' )
				|| (int) $item->object_id === $page_id
				|| false !== stripos( $url, '/agenda' )
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
		'menu-item-title'     => 'Agenda',
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
 * Upload flyer dari file tema ke Media Library (sekali).
 *
 * @return int Attachment ID.
 */
function man3_agenda_ensure_flyer_attachment() {
	$existing = (int) get_option( 'man3_agenda_flyer_id', 0 );
	if ( $existing && get_post( $existing ) ) {
		return $existing;
	}

	$filename = 'agenda-desember-2026.png';
	global $wpdb;
	$like = '%' . $wpdb->esc_like( $filename );
	$found = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1",
			$like
		)
	);
	if ( $found > 0 ) {
		update_option( 'man3_agenda_flyer_id', $found, false );
		return $found;
	}

	$src = get_template_directory() . '/assets/images/' . $filename;
	if ( ! file_exists( $src ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$bits = wp_upload_bits( $filename, null, file_get_contents( $src ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! empty( $bits['error'] ) || empty( $bits['file'] ) ) {
		return 0;
	}

	$filetype = wp_check_filetype( $filename, null );
	$att_id   = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'] ? $filetype['type'] : 'image/png',
			'post_title'     => 'Agenda Penting Bulan Desember 2026',
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$bits['file']
	);
	if ( is_wp_error( $att_id ) || ! $att_id ) {
		return 0;
	}
	$meta = wp_generate_attachment_metadata( (int) $att_id, $bits['file'] );
	if ( $meta ) {
		wp_update_attachment_metadata( (int) $att_id, $meta );
	}
	update_option( 'man3_agenda_flyer_id', (int) $att_id, false );
	return (int) $att_id;
}

/**
 * Seed lima agenda resmi.
 */
function man3_agenda_run_seed() {
	if ( get_option( 'man3_agenda_seeded_v1' ) ) {
		man3_agenda_ensure_flyer_attachment();
		return;
	}

	man3_agenda_ensure_flyer_attachment();

	/* Nonaktifkan agenda lama di luar daftar resmi (jangan tampil di /agenda/). */
	$legacy = get_posts(
		array(
			'post_type'      => 'agenda',
			'posts_per_page' => 100,
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'fields'         => 'ids',
		)
	);
	$seed_keys = array();
	foreach ( man3_agenda_seed_rows() as $row ) {
		$seed_keys[ $row['key'] ] = true;
	}
	foreach ( $legacy as $lid ) {
		$key = get_post_meta( (int) $lid, '_man3_agenda_seed_key', true );
		if ( $key && isset( $seed_keys[ $key ] ) ) {
			continue;
		}
		update_post_meta( (int) $lid, '_man3_agenda_active', '0' );
	}

	foreach ( man3_agenda_seed_rows() as $row ) {
		$existing = get_posts(
			array(
				'post_type'      => 'agenda',
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'meta_key'       => '_man3_agenda_seed_key',
				'meta_value'     => $row['key'],
				'fields'         => 'ids',
			)
		);
		if ( $existing ) {
			continue;
		}
		$pid = wp_insert_post(
			array(
				'post_type'    => 'agenda',
				'post_status'  => 'publish',
				'post_title'   => $row['judul'],
				'post_content' => $row['keterangan'],
				'menu_order'   => (int) $row['order'],
			),
			true
		);
		if ( is_wp_error( $pid ) || ! $pid ) {
			continue;
		}
		update_post_meta( (int) $pid, '_man3_agenda_tanggal', $row['tanggal'] );
		update_post_meta( (int) $pid, '_man3_agenda_active', '1' );
		update_post_meta( (int) $pid, '_man3_agenda_seed_key', $row['key'] );
	}

	update_option( 'man3_agenda_seeded_v1', 1, false );
}

/**
 * Bootstrap admin.
 */
function man3_agenda_admin_bootstrap() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	man3_agenda_ensure_page();
	man3_agenda_ensure_menu();
	man3_agenda_run_seed();
}
add_action( 'admin_init', 'man3_agenda_admin_bootstrap' );

/**
 * Bootstrap depan (sekali) + reseed opsional.
 */
function man3_agenda_front_bootstrap() {
	$force = isset( $_GET['man3_agenda_reseed'] ) && '1' === $_GET['man3_agenda_reseed']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $force && current_user_can( 'manage_options' ) ) {
		delete_option( 'man3_agenda_seeded_v1' );
		// Hapus item seed lama agar isi resmi diganti ulang.
		$old = get_posts(
			array(
				'post_type'      => 'agenda',
				'posts_per_page' => 50,
				'post_status'    => 'any',
				'meta_key'       => '_man3_agenda_seed_key',
				'fields'         => 'ids',
			)
		);
		foreach ( $old as $oid ) {
			wp_delete_post( (int) $oid, true );
		}
		man3_agenda_ensure_page();
		man3_agenda_ensure_menu();
		man3_agenda_run_seed();
		return;
	}

	if ( get_option( 'man3_agenda_page_ok_v1' ) ) {
		return;
	}
	man3_agenda_ensure_page();
	man3_agenda_ensure_menu();
	man3_agenda_run_seed();
	update_option( 'man3_agenda_page_ok_v1', 1, false );
}
add_action( 'init', 'man3_agenda_front_bootstrap', 40 );

/**
 * URL flyer aktif.
 *
 * @return string
 */
function man3_agenda_get_flyer_url() {
	$id = (int) get_option( 'man3_agenda_flyer_id', 0 );
	if ( $id ) {
		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( ! $url ) {
			$url = wp_get_attachment_url( $id );
		}
		if ( $url ) {
			return $url;
		}
	}
	$fallback = get_template_directory_uri() . '/assets/images/agenda-desember-2026.png';
	$path     = get_template_directory() . '/assets/images/agenda-desember-2026.png';
	return file_exists( $path ) ? $fallback : '';
}

/**
 * Daftar agenda aktif (urut Order).
 *
 * @return array<int,array<string,mixed>>
 */
function man3_agenda_get_items() {
	$posts = get_posts(
		array(
			'post_type'      => 'agenda',
			'posts_per_page' => 80,
			'post_status'    => 'publish',
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			),
		)
	);
	$out = array();
	foreach ( $posts as $p ) {
		$active = get_post_meta( $p->ID, '_man3_agenda_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		if ( '1' !== $active ) {
			continue;
		}
		$out[] = array(
			'id'         => (int) $p->ID,
			'title'      => get_the_title( $p ),
			'slug'       => $p->post_name,
			'tanggal'    => (string) get_post_meta( $p->ID, '_man3_agenda_tanggal', true ),
			'keterangan' => trim( wp_strip_all_tags( $p->post_content ) ),
			'order'      => (int) $p->menu_order,
		);
	}
	return $out;
}
