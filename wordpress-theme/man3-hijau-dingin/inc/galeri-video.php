<?php
/**
 * CPT + seed + halaman Galeri Video (YouTube & Instagram resmi).
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Akun resmi (footer situs + verifikasi handle).
 */
function man3_video_official_accounts() {
	return array(
		'youtube'   => array(
			'label'  => 'YouTube MAN 3 Jember',
			'handle' => '@man3jember510',
			'url'    => 'https://www.youtube.com/@man3jember510',
			'id'     => 'UC5o9hQ5lg56Mm-Yi4G9Kt6Q',
		),
		'instagram' => array(
			'label'  => 'Instagram @mantiger.official',
			'handle' => '@mantiger.official',
			'url'    => 'https://www.instagram.com/mantiger.official/',
		),
	);
}

/**
 * Register CPT video galeri.
 */
function man3_video_register_cpt() {
	register_post_type(
		'man3_video',
		array(
			'labels'             => array(
				'name'          => __( 'Galeri Video', 'man3-hijau-dingin' ),
				'singular_name' => __( 'Video', 'man3-hijau-dingin' ),
				'add_new_item'  => __( 'Tambah Video', 'man3-hijau-dingin' ),
				'edit_item'     => __( 'Edit Video', 'man3-hijau-dingin' ),
				'menu_name'     => __( 'Galeri Video', 'man3-hijau-dingin' ),
			),
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-video-alt3',
			'menu_position'      => 23,
			'supports'           => array( 'title', 'thumbnail', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
		)
	);
}
add_action( 'init', 'man3_video_register_cpt' );

/**
 * Meta box sumber + URL + ringkasan + tanggal.
 */
function man3_video_meta_box() {
	add_meta_box(
		'man3_video_meta',
		__( 'Detail Video', 'man3-hijau-dingin' ),
		'man3_video_meta_box_render',
		'man3_video',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_video_meta_box' );

function man3_video_meta_box_render( $post ) {
	wp_nonce_field( 'man3_video_save', 'man3_video_nonce' );
	$source  = get_post_meta( $post->ID, '_man3_video_source', true );
	$url     = get_post_meta( $post->ID, '_man3_video_url', true );
	$summary = get_post_meta( $post->ID, '_man3_video_summary', true );
	$date    = get_post_meta( $post->ID, '_man3_video_date', true );
	if ( ! $source ) {
		$source = 'youtube';
	}
	?>
	<p>
		<label for="man3_video_source"><strong><?php esc_html_e( 'Sumber', 'man3-hijau-dingin' ); ?></strong></label><br />
		<select name="man3_video_source" id="man3_video_source">
			<option value="youtube" <?php selected( $source, 'youtube' ); ?>>YouTube</option>
			<option value="instagram" <?php selected( $source, 'instagram' ); ?>>Instagram</option>
		</select>
	</p>
	<p>
		<label for="man3_video_url"><strong><?php esc_html_e( 'URL video (YouTube / Reel IG)', 'man3-hijau-dingin' ); ?></strong></label>
		<input type="url" class="widefat" id="man3_video_url" name="man3_video_url" value="<?php echo esc_attr( $url ); ?>" placeholder="https://www.youtube.com/watch?v=… atau https://www.instagram.com/reel/…" />
	</p>
	<p>
		<label for="man3_video_date"><strong><?php esc_html_e( 'Tanggal tayang (YYYY-MM-DD)', 'man3-hijau-dingin' ); ?></strong></label>
		<input type="text" class="widefat" id="man3_video_date" name="man3_video_date" value="<?php echo esc_attr( $date ); ?>" placeholder="2026-07-16" />
	</p>
	<p>
		<label for="man3_video_summary"><strong><?php esc_html_e( 'Ringkasan (bahasa Indonesia)', 'man3-hijau-dingin' ); ?></strong></label>
		<textarea class="widefat" rows="4" id="man3_video_summary" name="man3_video_summary"><?php echo esc_textarea( $summary ); ?></textarea>
	</p>
	<p class="description"><?php esc_html_e( 'Opsional: set Gambar Unggulan sebagai thumbnail. Jika kosong, thumbnail YouTube otomatis dipakai.', 'man3-hijau-dingin' ); ?></p>
	<?php
}

function man3_video_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_video_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_video_nonce'] ) ), 'man3_video_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['man3_video_source'] ) ) {
		$src = sanitize_key( wp_unslash( $_POST['man3_video_source'] ) );
		if ( ! in_array( $src, array( 'youtube', 'instagram' ), true ) ) {
			$src = 'youtube';
		}
		update_post_meta( $post_id, '_man3_video_source', $src );
	}
	if ( isset( $_POST['man3_video_url'] ) ) {
		update_post_meta( $post_id, '_man3_video_url', esc_url_raw( wp_unslash( $_POST['man3_video_url'] ) ) );
	}
	if ( isset( $_POST['man3_video_summary'] ) ) {
		update_post_meta( $post_id, '_man3_video_summary', sanitize_textarea_field( wp_unslash( $_POST['man3_video_summary'] ) ) );
	}
	if ( isset( $_POST['man3_video_date'] ) ) {
		update_post_meta( $post_id, '_man3_video_date', sanitize_text_field( wp_unslash( $_POST['man3_video_date'] ) ) );
	}
}
add_action( 'save_post_man3_video', 'man3_video_meta_save' );

/**
 * Kolom admin.
 */
function man3_video_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['man3_source']  = __( 'Sumber', 'man3-hijau-dingin' );
			$new['man3_vdate']   = __( 'Tanggal', 'man3-hijau-dingin' );
			$new['man3_vurl']    = __( 'URL', 'man3-hijau-dingin' );
		}
	}
	return $new;
}
add_filter( 'manage_man3_video_posts_columns', 'man3_video_columns' );

function man3_video_column_content( $col, $post_id ) {
	if ( 'man3_source' === $col ) {
		echo esc_html( strtoupper( (string) get_post_meta( $post_id, '_man3_video_source', true ) ) );
	}
	if ( 'man3_vdate' === $col ) {
		echo esc_html( (string) get_post_meta( $post_id, '_man3_video_date', true ) );
	}
	if ( 'man3_vurl' === $col ) {
		$url = get_post_meta( $post_id, '_man3_video_url', true );
		if ( $url ) {
			echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noreferrer">' . esc_html__( 'Buka', 'man3-hijau-dingin' ) . '</a>';
		}
	}
}
add_action( 'manage_man3_video_posts_custom_column', 'man3_video_column_content', 10, 2 );

/**
 * Parse YouTube ID dari URL.
 *
 * @param string $url URL.
 * @return string
 */
function man3_video_youtube_id( $url ) {
	$url = trim( (string) $url );
	if ( preg_match( '/(?:youtu\.be\/|v=|embed\/|shorts\/)([\w-]{11})/', $url, $m ) ) {
		return $m[1];
	}
	if ( preg_match( '/^[\w-]{11}$/', $url ) ) {
		return $url;
	}
	return '';
}

/**
 * Parse Instagram shortcode dari URL reel/p/tv.
 *
 * @param string $url URL.
 * @return string
 */
function man3_video_instagram_code( $url ) {
	if ( preg_match( '#instagram\.com/(?:reel|p|tv)/([\w-]+)#i', (string) $url, $m ) ) {
		return $m[1];
	}
	return '';
}

/**
 * Seed data: YouTube RSS resmi 6 bulan terakhir (cutoff ~2026-04-22).
 * Instagram: feed publik tidak tersedia tanpa token API — admin dapat menambah Reel via CPT.
 *
 * @return array<int,array<string,string>>
 */
function man3_video_seed_items() {
	return array(
		array(
			'key'     => 'yt-h5-HpWACW2A',
			'source'  => 'youtube',
			'title'   => 'Pelatihan Inovasi Pembelajaran Berbasis AI',
			'url'     => 'https://www.youtube.com/watch?v=h5-HpWACW2A',
			'date'    => '2026-07-16',
			'summary' => 'Dokumentasi pelatihan inovasi pembelajaran berbasis AI untuk pendidik MAN 3 Jember.',
			'order'   => 10,
		),
		array(
			'key'     => 'yt-UyRgTw1ozhc',
			'source'  => 'youtube',
			'title'   => 'MATAMUDA MAN 3 Jember, Day 1',
			'url'     => 'https://www.youtube.com/watch?v=UyRgTw1ozhc',
			'date'    => '2026-07-13',
			'summary' => 'Hari pertama sekolah menjadi awal langkah penuh semangat di MAN 3 Jember, dengan sambutan hangat bagi peserta didik baru.',
			'order'   => 20,
		),
		array(
			'key'     => 'yt-nFyBwSZr3-w',
			'source'  => 'youtube',
			'title'   => 'GEMA FESTA HUT MAN 3 Jember',
			'url'     => 'https://www.youtube.com/watch?v=nFyBwSZr3-w',
			'date'    => '2026-06-17',
			'summary' => 'Semangat, tawa, dan kebersamaan berpadu dalam perayaan GEMA FESTA memperingati HUT MAN 3 Jember.',
			'order'   => 30,
		),
		array(
			'key'     => 'yt-p958ETcFgxk',
			'source'  => 'youtube',
			'title'   => 'Pembinaan Zona Integritas',
			'url'     => 'https://www.youtube.com/watch?v=p958ETcFgxk',
			'date'    => '2026-06-10',
			'summary' => 'Pendampingan Zona Integritas bersama Tim Kemenag Kabupaten Jember untuk penguatan tata kelola madrasah.',
			'order'   => 40,
		),
		array(
			'key'     => 'yt-uBH8P4n1Dyw',
			'source'  => 'youtube',
			'title'   => 'Peringatan Hari Lahir Pancasila 2026',
			'url'     => 'https://www.youtube.com/watch?v=uBH8P4n1Dyw',
			'date'    => '2026-06-01',
			'summary' => 'Peringatan Hari Lahir Pancasila 2026 di MAN 3 Jember menanamkan nilai persatuan, keadilan, dan gotong royong.',
			'order'   => 50,
		),
		array(
			'key'     => 'yt-O1rwezClxBM',
			'source'  => 'youtube',
			'title'   => 'Mempererat silaturahmi, membangun harmoni',
			'url'     => 'https://www.youtube.com/watch?v=O1rwezClxBM',
			'date'    => '2026-05-29',
			'summary' => 'Kegiatan mempererat silaturahmi dan membangun harmoni di lingkungan MAN 3 Jember.',
			'order'   => 60,
		),
		array(
			'key'     => 'yt-aKwMqSiOvIA',
			'source'  => 'youtube',
			'title'   => 'Kamu yang Terbaik untuk Almamater MAN 3 Jember',
			'url'     => 'https://www.youtube.com/watch?v=aKwMqSiOvIA',
			'date'    => '2026-05-13',
			'summary' => 'Refleksi perjalanan siswa menuju akhir masa belajar: rasa syukur, kebanggaan, dan komitmen untuk almamater.',
			'order'   => 70,
		),
		array(
			'key'     => 'yt-LtAxkfq939M',
			'source'  => 'youtube',
			'title'   => 'Program 3S (Senyum, Salam, Sapa)',
			'url'     => 'https://www.youtube.com/watch?v=LtAxkfq939M',
			'date'    => '2026-05-11',
			'summary' => 'Program 3S terus berlanjut sebagai bagian dari penguatan budaya santun dan ramah di MAN 3 Jember.',
			'order'   => 80,
		),
		array(
			'key'     => 'yt-tDWQLkItS2M',
			'source'  => 'youtube',
			'title'   => 'Peluncuran Program 3S oleh Kepala Madrasah',
			'url'     => 'https://www.youtube.com/watch?v=tDWQLkItS2M',
			'date'    => '2026-05-06',
			'summary' => 'Kepala Madrasah Imam Syahroni, S.Pd., M.Si. meluncurkan program 3S sebagai ikhtiar membangun budaya positif warga madrasah.',
			'order'   => 90,
		),
	);
}

/**
 * Seed CPT + halaman + menu.
 */
function man3_video_run_seed() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$force = isset( $_GET['man3_video_reseed'] ) && '1' === $_GET['man3_video_reseed'];
	if ( ! $force && get_option( 'man3_video_seeded_v1' ) ) {
		return;
	}
	man3_video_do_seed();
	update_option( 'man3_video_seeded_v1', 1 );
}
add_action( 'admin_init', 'man3_video_run_seed' );

function man3_video_front_seed_trigger() {
	if ( ! isset( $_GET['man3_video_reseed'] ) || '1' !== $_GET['man3_video_reseed'] ) {
		return;
	}
	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	man3_video_do_seed();
	update_option( 'man3_video_seeded_v1', 1 );
	wp_safe_redirect( home_url( '/video/' ) );
	exit;
}
add_action( 'template_redirect', 'man3_video_front_seed_trigger' );

function man3_video_do_seed() {
	foreach ( man3_video_seed_items() as $row ) {
		$existing = get_posts(
			array(
				'post_type'      => 'man3_video',
				'name'           => $row['key'],
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);
		$postarr = array(
			'post_title'  => $row['title'],
			'post_name'   => $row['key'],
			'post_status' => 'publish',
			'post_type'   => 'man3_video',
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
		update_post_meta( $pid, '_man3_video_source', $row['source'] );
		update_post_meta( $pid, '_man3_video_url', $row['url'] );
		update_post_meta( $pid, '_man3_video_summary', $row['summary'] );
		update_post_meta( $pid, '_man3_video_date', $row['date'] );
	}

	$page = get_page_by_path( 'video' );
	if ( ! $page ) {
		$pid = wp_insert_post(
			array(
				'post_title'   => 'Video',
				'post_name'    => 'video',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- Galeri Video resmi MAN 3 Jember -->',
			)
		);
		if ( $pid && ! is_wp_error( $pid ) ) {
			update_post_meta( $pid, '_wp_page_template', 'page-galeri-video.php' );
		}
	} else {
		update_post_meta( $page->ID, '_wp_page_template', 'page-galeri-video.php' );
	}

	man3_video_ensure_menu();
}

/**
 * Menu: Gallery/Galeri → Video (+ pastikan parent ada).
 */
function man3_video_ensure_menu() {
	$page = get_page_by_path( 'video' );
	if ( ! $page ) {
		return;
	}
	$page_id = (int) $page->ID;

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$menu_id   = ! empty( $locations['primary'] ) ? (int) $locations['primary'] : 0;
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
			if ( 0 === strcasecmp( $title, 'Gallery' ) || 0 === strcasecmp( $title, 'Galeri' ) ) {
				$parent = $item;
			}
			if ( 0 === strcasecmp( $title, 'Video' ) || (int) $item->object_id === $page_id ) {
				$child = $item;
			}
		}
	}

	if ( ! $parent ) {
		$parent_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => 'Gallery',
				'menu-item-url'    => home_url( '/galeri/' ),
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
				'menu-item-title'     => 'Video',
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
				'menu-item-title'     => 'Video',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent_id,
			)
		);
	}
}

/**
 * Ambil daftar video untuk tampilan depan.
 *
 * @return array<int,array<string,mixed>>
 */
function man3_video_get_items() {
	$posts = get_posts(
		array(
			'post_type'      => 'man3_video',
			'posts_per_page' => 50,
			'post_status'    => 'publish',
			'orderby'        => array(
				'meta_value' => 'DESC',
				'menu_order' => 'ASC',
			),
			'meta_key'       => '_man3_video_date',
		)
	);
	$out = array();
	foreach ( $posts as $p ) {
		$source  = get_post_meta( $p->ID, '_man3_video_source', true ) ?: 'youtube';
		$url     = get_post_meta( $p->ID, '_man3_video_url', true );
		$summary = get_post_meta( $p->ID, '_man3_video_summary', true );
		$date    = get_post_meta( $p->ID, '_man3_video_date', true );
		$yt      = man3_video_youtube_id( $url );
		$ig      = man3_video_instagram_code( $url );
		$thumb   = get_the_post_thumbnail_url( $p, 'large' );
		if ( ! $thumb && $yt ) {
			$thumb = 'https://i.ytimg.com/vi/' . $yt . '/hqdefault.jpg';
		}
		$out[] = array(
			'id'      => $p->ID,
			'title'   => get_the_title( $p ),
			'source'  => $source,
			'url'     => $url,
			'summary' => $summary,
			'date'    => $date,
			'yt_id'   => $yt,
			'ig_code' => $ig,
			'thumb'   => $thumb,
		);
	}
	return $out;
}
