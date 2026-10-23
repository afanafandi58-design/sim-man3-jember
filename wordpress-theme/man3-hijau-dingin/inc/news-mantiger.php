<?php
/**
 * CPT News Mantiger + halaman daftar /news-mantiger/.
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register CPT.
 */
function man3_news_mantiger_register_cpt() {
	register_post_type(
		'man3_news_mantiger',
		array(
			'labels'             => array(
				'name'               => __( 'News Mantiger', 'man3-hijau-dingin' ),
				'singular_name'      => __( 'Berita Mantiger', 'man3-hijau-dingin' ),
				'add_new'            => __( 'Tambah Baru', 'man3-hijau-dingin' ),
				'add_new_item'       => __( 'Tambah Berita Mantiger', 'man3-hijau-dingin' ),
				'edit_item'          => __( 'Edit Berita Mantiger', 'man3-hijau-dingin' ),
				'new_item'           => __( 'Berita Mantiger Baru', 'man3-hijau-dingin' ),
				'view_item'          => __( 'Lihat Berita', 'man3-hijau-dingin' ),
				'search_items'       => __( 'Cari News Mantiger', 'man3-hijau-dingin' ),
				'not_found'          => __( 'Belum ada berita.', 'man3-hijau-dingin' ),
				'not_found_in_trash' => __( 'Tidak ada di tempat sampah.', 'man3-hijau-dingin' ),
				'menu_name'          => __( 'News Mantiger', 'man3-hijau-dingin' ),
				'all_items'          => __( 'Semua Berita', 'man3-hijau-dingin' ),
				'featured_image'     => __( 'Gambar Unggulan', 'man3-hijau-dingin' ),
				'set_featured_image' => __( 'Set gambar unggulan', 'man3-hijau-dingin' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-welcome-write-blog',
			'menu_position'      => 25,
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		)
	);
}
add_action( 'init', 'man3_news_mantiger_register_cpt' );

/**
 * Admin help text under title.
 */
function man3_news_mantiger_admin_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'man3_news_mantiger' !== $screen->post_type ) {
		return;
	}
	if ( 'edit' === $screen->base || 'post' === $screen->base ) {
		echo '<div class="notice notice-info"><p>';
		esc_html_e( 'Isi judul, konten (atau ringkasan di kutipan), tanggal publikasi, dan Gambar Unggulan. Setelah Publish, berita muncul di halaman /news-mantiger/.', 'man3-hijau-dingin' );
		echo ' <a href="' . esc_url( home_url( '/news-mantiger/' ) ) . '" target="_blank" rel="noopener noreferrer">';
		esc_html_e( 'Lihat halaman publik', 'man3-hijau-dingin' );
		echo '</a></p></div>';
	}
}
add_action( 'admin_notices', 'man3_news_mantiger_admin_notice' );

/**
 * Ensure listing page exists (once).
 */
function man3_news_mantiger_ensure_page() {
	if ( get_option( 'man3_news_mantiger_page_ok' ) ) {
		$existing = get_page_by_path( 'news-mantiger' );
		if ( $existing instanceof WP_Post && 'publish' === $existing->post_status ) {
			return (int) $existing->ID;
		}
	}
	if ( ! current_user_can( 'manage_options' ) && ! is_admin() ) {
		return 0;
	}

	$page = get_page_by_path( 'news-mantiger' );
	if ( $page instanceof WP_Post ) {
		update_post_meta( $page->ID, '_wp_page_template', 'page-news-mantiger.php' );
		if ( 'publish' !== $page->post_status ) {
			wp_update_post(
				array(
					'ID'          => $page->ID,
					'post_status' => 'publish',
				)
			);
		}
		update_option( 'man3_news_mantiger_page_ok', 1, false );
		return (int) $page->ID;
	}

	$id = wp_insert_post(
		array(
			'post_title'   => 'News Mantiger',
			'post_name'    => 'news-mantiger',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		),
		true
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	update_post_meta( (int) $id, '_wp_page_template', 'page-news-mantiger.php' );
	update_option( 'man3_news_mantiger_page_ok', 1, false );
	return (int) $id;
}
add_action(
	'admin_init',
	static function () {
		if ( current_user_can( 'manage_options' ) ) {
			man3_news_mantiger_ensure_page();
		}
	}
);

/**
 * @return array<int, array<string, mixed>>
 */
function man3_news_mantiger_get_items() {
	$q = new WP_Query(
		array(
			'post_type'              => 'man3_news_mantiger',
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
		$thumb = get_the_post_thumbnail_url( $post, 'large' );
		$raw   = $post->post_excerpt ? $post->post_excerpt : wp_strip_all_tags( $post->post_content );
		$items[] = array(
			'id'      => (int) $post->ID,
			'title'   => get_the_title( $post ),
			'excerpt' => wp_trim_words( $raw, 36, '…' ),
			'content' => apply_filters( 'the_content', $post->post_content ),
			'date'     => get_the_date( '', $post ),
			'date_iso' => get_the_date( DATE_W3C, $post ),
			'thumb'    => $thumb ? $thumb : '',
		);
	}
	wp_reset_postdata();
	return $items;
}
