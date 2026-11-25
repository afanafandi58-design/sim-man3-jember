<?php
/**
 * Pengumuman — CPT + halaman /pengumuman/ (Info Madrasah).
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register CPT `pengumuman` (slug sama agar data lama di DB ikut terbaca).
 */
function man3_pengumuman_register_cpt() {
	register_post_type(
		'pengumuman',
		array(
			'labels'             => array(
				'name'               => __( 'Pengumuman', 'man3-hijau-dingin' ),
				'singular_name'      => __( 'Pengumuman', 'man3-hijau-dingin' ),
				'add_new'            => __( 'Tambah Pengumuman', 'man3-hijau-dingin' ),
				'add_new_item'       => __( 'Tambah Pengumuman', 'man3-hijau-dingin' ),
				'edit_item'          => __( 'Edit Pengumuman', 'man3-hijau-dingin' ),
				'menu_name'          => __( 'Pengumuman', 'man3-hijau-dingin' ),
				'all_items'          => __( 'Semua Pengumuman', 'man3-hijau-dingin' ),
				'featured_image'     => __( 'Gambar Pengumuman', 'man3-hijau-dingin' ),
				'set_featured_image' => __( 'Set gambar pengumuman', 'man3-hijau-dingin' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-megaphone',
			'menu_position'      => 22,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		)
	);
}
add_action( 'init', 'man3_pengumuman_register_cpt' );

/**
 * Meta: aktif + berkas PDF.
 */
function man3_pengumuman_meta_box() {
	add_meta_box(
		'man3_pengumuman_meta',
		__( 'Pengaturan Pengumuman', 'man3-hijau-dingin' ),
		'man3_pengumuman_meta_box_render',
		'pengumuman',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_pengumuman_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function man3_pengumuman_meta_box_render( $post ) {
	wp_nonce_field( 'man3_pengumuman_save', 'man3_pengumuman_nonce' );
	$active  = get_post_meta( $post->ID, '_man3_pengumuman_active', true );
	$file_id = (int) get_post_meta( $post->ID, '_man3_pengumuman_file_id', true );
	if ( '' === $active ) {
		$active = '1';
	}
	$file_url  = $file_id ? wp_get_attachment_url( $file_id ) : '';
	$file_name = $file_id ? basename( (string) get_attached_file( $file_id ) ) : '';
	?>
	<p>
		<label>
			<input type="checkbox" name="man3_pengumuman_active" value="1" <?php checked( $active, '1' ); ?> />
			<?php esc_html_e( 'Aktif di halaman Pengumuman', 'man3-hijau-dingin' ); ?>
		</label>
	</p>
	<p class="description">
		<?php esc_html_e( 'Judul = judul. Isi = editor. Tanggal = tanggal terbit. Gambar = Gambar Unggulan. Urutan = Order (angka kecil lebih dulu; daftar depan diurutkan tanggal terbaru).', 'man3-hijau-dingin' ); ?>
	</p>
	<hr />
	<p><strong><?php esc_html_e( 'Berkas unduhan (PDF, opsional)', 'man3-hijau-dingin' ); ?></strong></p>
	<input type="hidden" id="man3_pengumuman_file_id" name="man3_pengumuman_file_id" value="<?php echo esc_attr( (string) $file_id ); ?>" />
	<p id="man3_pengumuman_file_label" style="margin:0.35rem 0;">
		<?php if ( $file_url ) : ?>
			<a href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $file_name ? $file_name : __( 'Berkas terpilih', 'man3-hijau-dingin' ) ); ?></a>
		<?php else : ?>
			<em><?php esc_html_e( 'Belum ada berkas.', 'man3-hijau-dingin' ); ?></em>
		<?php endif; ?>
	</p>
	<p>
		<button type="button" class="button" id="man3_pengumuman_pick_file"><?php esc_html_e( 'Pilih / unggah PDF', 'man3-hijau-dingin' ); ?></button>
		<button type="button" class="button" id="man3_pengumuman_clear_file" <?php echo $file_id ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Hapus berkas', 'man3-hijau-dingin' ); ?></button>
	</p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function man3_pengumuman_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_pengumuman_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_pengumuman_nonce'] ) ), 'man3_pengumuman_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_man3_pengumuman_active', isset( $_POST['man3_pengumuman_active'] ) ? '1' : '0' );
	$file_id = isset( $_POST['man3_pengumuman_file_id'] ) ? absint( $_POST['man3_pengumuman_file_id'] ) : 0;
	if ( $file_id > 0 ) {
		update_post_meta( $post_id, '_man3_pengumuman_file_id', $file_id );
	} else {
		delete_post_meta( $post_id, '_man3_pengumuman_file_id' );
	}
}
add_action( 'save_post_pengumuman', 'man3_pengumuman_meta_save' );

/**
 * Media picker di layar edit pengumuman.
 *
 * @param string $hook Hook.
 */
function man3_pengumuman_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'pengumuman' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	wp_add_inline_script(
		'jquery',
		"(function($){
			$(function(){
				var frame;
				$('#man3_pengumuman_pick_file').on('click', function(e){
					e.preventDefault();
					if (frame) { frame.open(); return; }
					frame = wp.media({
						title: 'Pilih berkas PDF',
						button: { text: 'Pakai berkas ini' },
						multiple: false,
						library: { type: ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'] }
					});
					frame.on('select', function(){
						var att = frame.state().get('selection').first().toJSON();
						$('#man3_pengumuman_file_id').val(att.id);
						var name = att.filename || att.title || ('#'+att.id);
						$('#man3_pengumuman_file_label').html('<a href=\"'+att.url+'\" target=\"_blank\" rel=\"noopener noreferrer\">'+name+'</a>');
						$('#man3_pengumuman_clear_file').show();
					});
					frame.open();
				});
				$('#man3_pengumuman_clear_file').on('click', function(e){
					e.preventDefault();
					$('#man3_pengumuman_file_id').val('');
					$('#man3_pengumuman_file_label').html('<em>Belum ada berkas.</em>');
					$(this).hide();
				});
			});
		})(jQuery);"
	);
}
add_action( 'admin_enqueue_scripts', 'man3_pengumuman_admin_assets' );

/**
 * @param array $cols Columns.
 * @return array
 */
function man3_pengumuman_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['man3_pengumuman_date']   = __( 'Tanggal', 'man3-hijau-dingin' );
			$new['man3_pengumuman_active'] = __( 'Aktif', 'man3-hijau-dingin' );
			$new['man3_pengumuman_file']   = __( 'Berkas', 'man3-hijau-dingin' );
			$new['man3_pengumuman_order']  = __( 'Urutan', 'man3-hijau-dingin' );
		}
	}
	return $new;
}
add_filter( 'manage_pengumuman_posts_columns', 'man3_pengumuman_columns' );

/**
 * @param string $col Column.
 * @param int    $post_id Post ID.
 */
function man3_pengumuman_column_content( $col, $post_id ) {
	if ( 'man3_pengumuman_date' === $col ) {
		echo esc_html( get_the_date( '', $post_id ) );
	}
	if ( 'man3_pengumuman_active' === $col ) {
		$active = get_post_meta( $post_id, '_man3_pengumuman_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		echo '1' === $active ? esc_html__( 'Ya', 'man3-hijau-dingin' ) : esc_html__( 'Tidak', 'man3-hijau-dingin' );
	}
	if ( 'man3_pengumuman_file' === $col ) {
		$file_id = (int) get_post_meta( $post_id, '_man3_pengumuman_file_id', true );
		if ( $file_id ) {
			$url = wp_get_attachment_url( $file_id );
			echo $url ? '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">PDF</a>' : '&mdash;';
		} else {
			echo '&mdash;';
		}
	}
	if ( 'man3_pengumuman_order' === $col ) {
		echo esc_html( (string) get_post_field( 'menu_order', $post_id ) );
	}
}
add_action( 'manage_pengumuman_posts_custom_column', 'man3_pengumuman_column_content', 10, 2 );

/**
 * Pastikan halaman /pengumuman/.
 *
 * @return int
 */
function man3_pengumuman_ensure_page() {
	$page = get_page_by_path( 'pengumuman' );
	if ( $page instanceof WP_Post ) {
		update_post_meta( $page->ID, '_wp_page_template', 'page-pengumuman.php' );
		wp_update_post(
			array(
				'ID'          => $page->ID,
				'post_status' => 'publish',
				'post_title'  => 'Pengumuman',
			)
		);
		return (int) $page->ID;
	}

	$id = wp_insert_post(
		array(
			'post_title'   => 'Pengumuman',
			'post_name'    => 'pengumuman',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		),
		true
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	update_post_meta( (int) $id, '_wp_page_template', 'page-pengumuman.php' );
	return (int) $id;
}

/**
 * Pastikan menu Info Madrasah → Pengumuman mengarah ke halaman (posisi tidak dipindah).
 */
function man3_pengumuman_ensure_menu() {
	$page_id = man3_pengumuman_ensure_page();
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
				0 === strcasecmp( $title, 'Pengumuman' )
				|| (int) $item->object_id === $page_id
				|| false !== stripos( $url, '/pengumuman' )
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
		'menu-item-title'     => 'Pengumuman',
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
 * Aktifkan meta untuk pengumuman lama yang belum punya flag.
 */
function man3_pengumuman_migrate_active_flags() {
	if ( get_option( 'man3_pengumuman_active_flag_v1' ) ) {
		return;
	}
	$posts = get_posts(
		array(
			'post_type'      => 'pengumuman',
			'posts_per_page' => 200,
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'fields'         => 'ids',
		)
	);
	foreach ( $posts as $pid ) {
		$cur = get_post_meta( (int) $pid, '_man3_pengumuman_active', true );
		if ( '' === $cur ) {
			update_post_meta( (int) $pid, '_man3_pengumuman_active', '1' );
		}
	}
	update_option( 'man3_pengumuman_active_flag_v1', 1, false );
}
add_action( 'init', 'man3_pengumuman_migrate_active_flags', 35 );

/**
 * Bootstrap halaman + menu.
 */
function man3_pengumuman_admin_bootstrap() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	man3_pengumuman_ensure_page();
	man3_pengumuman_ensure_menu();
}
add_action( 'admin_init', 'man3_pengumuman_admin_bootstrap' );

/**
 * Trigger depan (sekali) untuk pastikan halaman ada tanpa login admin.
 */
function man3_pengumuman_front_bootstrap() {
	if ( get_option( 'man3_pengumuman_page_ok_v1' ) ) {
		return;
	}
	man3_pengumuman_ensure_page();
	man3_pengumuman_ensure_menu();
	update_option( 'man3_pengumuman_page_ok_v1', 1, false );
}
add_action( 'init', 'man3_pengumuman_front_bootstrap', 40 );

/**
 * Daftar pengumuman aktif untuk halaman depan (terbaru di atas).
 *
 * @return array<int,array<string,mixed>>
 */
function man3_pengumuman_get_items() {
	$posts = get_posts(
		array(
			'post_type'      => 'pengumuman',
			'posts_per_page' => 80,
			'post_status'    => 'publish',
			'orderby'        => array(
				'date'       => 'DESC',
				'menu_order' => 'ASC',
			),
		)
	);
	$out = array();
	foreach ( $posts as $p ) {
		$active = get_post_meta( $p->ID, '_man3_pengumuman_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		if ( '1' !== $active ) {
			continue;
		}
		$raw     = wp_strip_all_tags( $p->post_content );
		$excerpt = wp_trim_words( $raw, 36, '…' );
		$file_id = (int) get_post_meta( $p->ID, '_man3_pengumuman_file_id', true );
		$file    = '';
		if ( $file_id ) {
			$url = wp_get_attachment_url( $file_id );
			if ( $url ) {
				$file = $url;
			}
		}
		$thumb = get_the_post_thumbnail_url( $p, 'medium_large' );
		if ( ! $thumb ) {
			$thumb = get_the_post_thumbnail_url( $p, 'large' );
		}
		$out[] = array(
			'id'       => (int) $p->ID,
			'title'    => get_the_title( $p ),
			'slug'     => $p->post_name,
			'date'     => get_the_date( '', $p ),
			'date_iso' => get_the_date( 'c', $p ),
			'excerpt'  => $excerpt,
			'content'  => $raw,
			'thumb'    => $thumb ? $thumb : '',
			'file'     => $file,
		);
	}
	return $out;
}
