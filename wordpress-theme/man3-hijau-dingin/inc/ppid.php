<?php
/**
 * CPT PPID + halaman daftar /ppid/ + menu top-level.
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register CPT.
 */
function man3_ppid_register_cpt() {
	register_post_type(
		'man3_ppid',
		array(
			'labels'             => array(
				'name'               => __( 'PPID', 'man3-hijau-dingin' ),
				'singular_name'      => __( 'Informasi PPID', 'man3-hijau-dingin' ),
				'add_new'            => __( 'Tambah Baru', 'man3-hijau-dingin' ),
				'add_new_item'       => __( 'Tambah Informasi PPID', 'man3-hijau-dingin' ),
				'edit_item'          => __( 'Edit Informasi PPID', 'man3-hijau-dingin' ),
				'new_item'           => __( 'Informasi PPID Baru', 'man3-hijau-dingin' ),
				'view_item'          => __( 'Lihat Informasi', 'man3-hijau-dingin' ),
				'search_items'       => __( 'Cari PPID', 'man3-hijau-dingin' ),
				'not_found'          => __( 'Belum ada informasi PPID.', 'man3-hijau-dingin' ),
				'not_found_in_trash' => __( 'Tidak ada di tempat sampah.', 'man3-hijau-dingin' ),
				'menu_name'          => __( 'PPID', 'man3-hijau-dingin' ),
				'all_items'          => __( 'Semua Informasi', 'man3-hijau-dingin' ),
				'featured_image'     => __( 'Gambar', 'man3-hijau-dingin' ),
				'set_featured_image' => __( 'Set gambar', 'man3-hijau-dingin' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-media-document',
			'menu_position'      => 24,
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		)
	);
}
add_action( 'init', 'man3_ppid_register_cpt' );

/**
 * Meta: lampiran/dokumen opsional.
 */
function man3_ppid_meta_box() {
	add_meta_box(
		'man3_ppid_meta',
		__( 'Lampiran / Dokumen (opsional)', 'man3-hijau-dingin' ),
		'man3_ppid_meta_box_render',
		'man3_ppid',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'man3_ppid_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function man3_ppid_meta_box_render( $post ) {
	wp_nonce_field( 'man3_ppid_save', 'man3_ppid_nonce' );
	$file_id  = (int) get_post_meta( $post->ID, '_man3_ppid_file_id', true );
	$file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';
	?>
	<p>
		<label for="man3_ppid_file_id"><strong><?php esc_html_e( 'ID lampiran (Media Library)', 'man3-hijau-dingin' ); ?></strong></label>
		<input type="number" min="0" class="widefat" id="man3_ppid_file_id" name="man3_ppid_file_id" value="<?php echo esc_attr( (string) $file_id ); ?>" />
	</p>
	<p>
		<button type="button" class="button" id="man3_ppid_pick_file"><?php esc_html_e( 'Pilih / unggah dokumen', 'man3-hijau-dingin' ); ?></button>
		<button type="button" class="button" id="man3_ppid_clear_file"><?php esc_html_e( 'Hapus lampiran', 'man3-hijau-dingin' ); ?></button>
	</p>
	<p class="description" id="man3_ppid_file_preview">
		<?php if ( $file_url ) : ?>
			<a href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( basename( $file_url ) ); ?></a>
		<?php else : ?>
			<?php esc_html_e( 'Belum ada lampiran. PDF/DOC/gambar dari Media Library.', 'man3-hijau-dingin' ); ?>
		<?php endif; ?>
	</p>
	<?php
}

/**
 * Admin script for media picker on PPID edit screen.
 *
 * @param string $hook Hook.
 */
function man3_ppid_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'man3_ppid' !== $screen->post_type ) {
		return;
	}
	wp_enqueue_media();
	$js = <<<'JS'
(function ($) {
  function bind() {
    var $id = $('#man3_ppid_file_id');
    var $prev = $('#man3_ppid_file_preview');
    if (!$id.length) return;
    $('#man3_ppid_pick_file').on('click', function (e) {
      e.preventDefault();
      var frame = wp.media({ title: 'Pilih dokumen PPID', button: { text: 'Pakai lampiran' }, multiple: false });
      frame.on('select', function () {
        var att = frame.state().get('selection').first().toJSON();
        $id.val(att.id);
        $prev.html('<a href="' + att.url + '" target="_blank" rel="noopener noreferrer">' + (att.filename || att.url) + '</a>');
      });
      frame.open();
    });
    $('#man3_ppid_clear_file').on('click', function (e) {
      e.preventDefault();
      $id.val('0');
      $prev.text('Belum ada lampiran.');
    });
  }
  $(bind);
})(jQuery);
JS;
	wp_add_inline_script( 'jquery', $js );
}
add_action( 'admin_enqueue_scripts', 'man3_ppid_admin_assets' );

/**
 * @param int $post_id ID.
 */
function man3_ppid_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_ppid_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_ppid_nonce'] ) ), 'man3_ppid_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['man3_ppid_file_id'] ) ) {
		update_post_meta( $post_id, '_man3_ppid_file_id', max( 0, (int) $_POST['man3_ppid_file_id'] ) );
	}
}
add_action( 'save_post_man3_ppid', 'man3_ppid_meta_save' );

/**
 * Admin notice.
 */
function man3_ppid_admin_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'man3_ppid' !== $screen->post_type ) {
		return;
	}
	if ( 'edit' === $screen->base || 'post' === $screen->base ) {
		echo '<div class="notice notice-info"><p>';
		esc_html_e( 'Isi judul, konten, gambar (opsional), dan lampiran dokumen (opsional). Setelah Publish, muncul di halaman /ppid/.', 'man3-hijau-dingin' );
		echo ' <a href="' . esc_url( home_url( '/ppid/' ) ) . '" target="_blank" rel="noopener noreferrer">';
		esc_html_e( 'Lihat halaman publik', 'man3-hijau-dingin' );
		echo '</a></p></div>';
	}
}
add_action( 'admin_notices', 'man3_ppid_admin_notice' );

/**
 * Ensure listing page exists.
 *
 * @return int
 */
function man3_ppid_ensure_page() {
	$page = get_page_by_path( 'ppid' );
	if ( $page instanceof WP_Post ) {
		update_post_meta( $page->ID, '_wp_page_template', 'page-ppid.php' );
		if ( 'publish' !== $page->post_status ) {
			wp_update_post(
				array(
					'ID'          => $page->ID,
					'post_status' => 'publish',
				)
			);
		}
		update_option( 'man3_ppid_page_ok', 1, false );
		return (int) $page->ID;
	}

	if ( ! current_user_can( 'manage_options' ) && ! is_admin() ) {
		return 0;
	}

	$id = wp_insert_post(
		array(
			'post_title'   => 'PPID',
			'post_name'    => 'ppid',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '<!-- PPID MAN 3 Jember -->',
		),
		true
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	update_post_meta( (int) $id, '_wp_page_template', 'page-ppid.php' );
	update_option( 'man3_ppid_page_ok', 1, false );
	return (int) $id;
}

/**
 * Pastikan item menu top-level PPID.
 */
function man3_ppid_ensure_menu() {
	$page_id = man3_ppid_ensure_page();
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
	$found = null;
	if ( $items ) {
		foreach ( $items as $item ) {
			$title = trim( wp_strip_all_tags( $item->title ) );
			if ( 0 === strcasecmp( $title, 'PPID' ) || ( (int) $item->object_id === $page_id && 'page' === $item->object ) ) {
				$found = $item;
				break;
			}
		}
	}

	$args = array(
		'menu-item-title'     => 'PPID',
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $page_id,
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
		'menu-item-parent-id' => 0,
	);

	if ( $found ) {
		wp_update_nav_menu_item( $menu_id, (int) $found->ID, $args );
	} else {
		wp_update_nav_menu_item( $menu_id, 0, $args );
	}
}

/**
 * Seed satu item contoh (sekali) agar halaman tidak kosong saat pertama deploy.
 */
function man3_ppid_seed_sample() {
	if ( get_option( 'man3_ppid_seeded_v1' ) ) {
		return;
	}
	$existing = get_posts(
		array(
			'post_type'      => 'man3_ppid',
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);
	if ( $existing ) {
		update_option( 'man3_ppid_seeded_v1', 1, false );
		return;
	}

	$content = '<p>Pejabat Pengelola Informasi dan Dokumentasi (PPID) MAN 3 Jember menyediakan informasi publik sesuai ketentuan peraturan perundang-undangan.</p>'
		. '<p>Informasi berkala, serta merta, dan setiap saat akan dipublikasikan melalui halaman ini. Silakan hubungi humas madrasah apabila membutuhkan bantuan lebih lanjut.</p>';

	$id = wp_insert_post(
		array(
			'post_title'   => 'Tentang PPID MAN 3 Jember',
			'post_name'    => 'tentang-ppid-man-3-jember',
			'post_status'  => 'publish',
			'post_type'    => 'man3_ppid',
			'post_content' => $content,
			'post_excerpt' => 'Pengantar layanan informasi publik PPID MAN 3 Jember.',
		),
		true
	);
	if ( ! is_wp_error( $id ) && $id ) {
		update_option( 'man3_ppid_seeded_v1', 1, false );
	}
}

/**
 * Bootstrap page + menu + sample on admin.
 */
function man3_ppid_admin_bootstrap() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	man3_ppid_ensure_page();
	man3_ppid_ensure_menu();
	man3_ppid_seed_sample();
}
add_action( 'admin_init', 'man3_ppid_admin_bootstrap' );

/**
 * Front trigger: ensure + optional reseed sample.
 */
function man3_ppid_front_bootstrap() {
	if ( ! isset( $_GET['man3_ppid_reseed'] ) || '1' !== $_GET['man3_ppid_reseed'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	delete_option( 'man3_ppid_seeded_v1' );
	man3_ppid_ensure_page();
	man3_ppid_ensure_menu();
	man3_ppid_seed_sample();
	wp_safe_redirect( home_url( '/ppid/' ) );
	exit;
}
add_action( 'template_redirect', 'man3_ppid_front_bootstrap' );

/**
 * @return array<int, array<string, mixed>>
 */
function man3_ppid_get_items() {
	$q = new WP_Query(
		array(
			'post_type'              => 'man3_ppid',
			'post_status'            => 'publish',
			'posts_per_page'         => 50,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		)
	);

	$items = array();
	foreach ( $q->posts as $post ) {
		$thumb    = get_the_post_thumbnail_url( $post, 'large' );
		$file_id  = (int) get_post_meta( $post->ID, '_man3_ppid_file_id', true );
		$file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';
		$file_name = '';
		if ( $file_id ) {
			$file_name = basename( get_attached_file( $file_id ) );
			if ( ! $file_name ) {
				$file_name = basename( (string) $file_url );
			}
		}
		$raw = $post->post_excerpt ? $post->post_excerpt : wp_strip_all_tags( $post->post_content );
		$items[] = array(
			'id'        => (int) $post->ID,
			'title'     => get_the_title( $post ),
			'excerpt'   => wp_trim_words( $raw, 40, '…' ),
			'content'   => apply_filters( 'the_content', $post->post_content ),
			'date'      => get_the_date( '', $post ),
			'date_iso'  => get_the_date( DATE_W3C, $post ),
			'thumb'     => $thumb ? $thumb : '',
			'file_url'  => $file_url ? $file_url : '',
			'file_name' => $file_name,
		);
	}
	wp_reset_postdata();
	return $items;
}
