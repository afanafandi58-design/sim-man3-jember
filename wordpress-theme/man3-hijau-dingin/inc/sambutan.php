<?php
/**
 * Sambutan Kepala Madrasah — halaman editorial (admin-editable).
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default full narrative (HTML paragraphs).
 *
 * @return string
 */
function man3_sambutan_default_content() {
	return '<p>Assalamu\'alaikum warahmatullahi wabarakatuh.</p>
<p>Alhamdulillahirabbil \'alamin, puji syukur kita panjatkan ke hadirat Allah SWT atas limpahan rahmat dan karunia-Nya. Saya, Imam Syahroni, Kepala Madrasah MAN 3 Jember, mengucapkan selamat datang kepada seluruh warga madrasah, orang tua/wali, alumni, dan mitra yang bersama-sama menumbuhkan generasi berilmu, berakhlak, dan berdaya saing. Kepercayaan masyarakat kepada MAN 3 Jember adalah amanah yang kami jaga dengan penuh tanggung jawab.</p>
<p>Sejalan dengan semangat <em>Maju Bermutu Mendunia</em> dan visi madrasah mewujudkan lulusan yang berakhlak mulia, cerdas, terampil, dan kreatif berbasis kearifan lokal, kami menempatkan penghayatan ajaran Islam dan budaya bangsa sebagai fondasi karakter, sambil mengembangkan potensi akademik maupun non-akademik peserta didik secara optimal. Pembelajaran bermutu, bimbingan keterampilan, serta kepedulian terhadap lingkungan kami hadirkan agar setiap siswa siap bersaing secara sehat, memiliki bekal kerja, dan tetap berpijak pada nilai keislaman.</p>
<p>Dengan dukungan civitas akademika yang kompak dan saling percaya, kami berkomitmen terus berinovasi dalam layanan pendidikan—melalui program unggulan, pengembangan profesional pendidik, dan penguatan sarana pembelajaran. Mari bersama merawat amanah ini demi kemajuan putra-putri bangsa. Wassalamu\'alaikum warahmatullahi wabarakatuh.</p>';
}

/**
 * Excerpt for beranda (plain text, truncated).
 *
 * @param int $max_chars Max characters.
 * @return string
 */
function man3_sambutan_get_excerpt( $max_chars = 220 ) {
	$page = man3_sambutan_get_page();
	$text = '';
	if ( $page instanceof WP_Post ) {
		if ( has_excerpt( $page ) ) {
			$text = wp_strip_all_tags( $page->post_excerpt );
		} else {
			$text = wp_strip_all_tags( $page->post_content );
		}
	}
	if ( '' === trim( $text ) ) {
		$text = wp_strip_all_tags( man3_sambutan_default_content() );
	}
	$text = preg_replace( '/\s+/u', ' ', $text );
	$text = trim( (string) $text );
	if ( function_exists( 'mb_strlen' ) && mb_strlen( $text ) > $max_chars ) {
		$cut = mb_substr( $text, 0, $max_chars );
		$sp  = mb_strrpos( $cut, ' ' );
		if ( false !== $sp && $sp > (int) ( $max_chars * 0.6 ) ) {
			$cut = mb_substr( $cut, 0, $sp );
		}
		return rtrim( $cut, " \t.,;:" ) . '…';
	}
	if ( strlen( $text ) > $max_chars ) {
		$cut = substr( $text, 0, $max_chars );
		$sp  = strrpos( $cut, ' ' );
		if ( false !== $sp ) {
			$cut = substr( $cut, 0, $sp );
		}
		return rtrim( $cut, " \t.,;:" ) . '…';
	}
	return $text;
}

/**
 * @return string
 */
function man3_sambutan_get_url() {
	$page = man3_sambutan_get_page();
	if ( $page instanceof WP_Post ) {
		$url = get_permalink( $page );
		if ( $url ) {
			return $url;
		}
	}
	return home_url( '/editorial/sambutan-kepala-madrasah/' );
}

/**
 * @return string
 */
function man3_sambutan_get_title() {
	$page = man3_sambutan_get_page();
	if ( $page instanceof WP_Post && $page->post_title ) {
		return $page->post_title;
	}
	return 'IMAM SYAHRONI';
}

/**
 * Display name for beranda heading (may differ from page title).
 *
 * @return string
 */
function man3_sambutan_get_display_name() {
	$page = man3_sambutan_get_page();
	if ( $page instanceof WP_Post ) {
		$custom = get_post_meta( $page->ID, 'man3_sambutan_display_name', true );
		if ( is_string( $custom ) && '' !== trim( $custom ) ) {
			return trim( $custom );
		}
	}
	return 'IMAM SYAHRONI';
}

/**
 * @return string
 */
function man3_sambutan_get_role() {
	$page = man3_sambutan_get_page();
	if ( $page instanceof WP_Post ) {
		$role = get_post_meta( $page->ID, 'man3_sambutan_role', true );
		if ( is_string( $role ) && '' !== trim( $role ) ) {
			return trim( $role );
		}
	}
	return __( 'Kepala Madrasah', 'man3-hijau-dingin' );
}

/**
 * @return string Formatted date.
 */
function man3_sambutan_get_date_label() {
	$page = man3_sambutan_get_page();
	if ( $page instanceof WP_Post ) {
		return get_the_date( 'F j, Y', $page );
	}
	return '';
}

/**
 * @return WP_Post|null
 */
function man3_sambutan_get_page() {
	$id = (int) get_option( 'man3_sambutan_page_id', 0 );
	if ( $id ) {
		$p = get_post( $id );
		if ( $p instanceof WP_Post && 'page' === $p->post_type && 'publish' === $p->post_status ) {
			return $p;
		}
	}
	$page = get_page_by_path( 'editorial/sambutan-kepala-madrasah' );
	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		update_option( 'man3_sambutan_page_id', (int) $page->ID, false );
		return $page;
	}
	$page = get_page_by_path( 'sambutan-kepala-madrasah' );
	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		update_option( 'man3_sambutan_page_id', (int) $page->ID, false );
		return $page;
	}
	return null;
}

/**
 * Ensure Editorial parent + Sambutan page exist; seed content once.
 *
 * @param bool $force_seed Overwrite empty content with default.
 * @return int Page ID or 0.
 */
function man3_sambutan_ensure_page( $force_seed = false ) {
	$parent = get_page_by_path( 'editorial' );
	if ( ! ( $parent instanceof WP_Post ) ) {
		$parent_id = wp_insert_post(
			array(
				'post_title'   => 'Editorial',
				'post_name'    => 'editorial',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<p>' . esc_html__( 'Ruang editorial MAN 3 Jember.', 'man3-hijau-dingin' ) . '</p>',
			),
			true
		);
		if ( is_wp_error( $parent_id ) || ! $parent_id ) {
			return 0;
		}
		$parent = get_post( (int) $parent_id );
	} elseif ( 'publish' !== $parent->post_status ) {
		wp_update_post(
			array(
				'ID'          => $parent->ID,
				'post_status' => 'publish',
			)
		);
	}

	$page = get_page_by_path( 'editorial/sambutan-kepala-madrasah' );
	if ( ! ( $page instanceof WP_Post ) ) {
		$page = get_page_by_path( 'sambutan-kepala-madrasah' );
	}

	$content = man3_sambutan_default_content();

	if ( $page instanceof WP_Post ) {
		$update = array(
			'ID'          => $page->ID,
			'post_parent' => (int) $parent->ID,
			'post_name'   => 'sambutan-kepala-madrasah',
			'post_status' => 'publish',
		);
		if ( 'publish' !== $page->post_status ) {
			$update['post_status'] = 'publish';
		}
		$empty = '' === trim( wp_strip_all_tags( (string) $page->post_content ) );
		if ( $empty || $force_seed ) {
			$update['post_content'] = $content;
		}
		if ( '' === trim( (string) $page->post_title ) || 'sambutan-kepala-madrasah' === $page->post_name ) {
			if ( '' === trim( (string) $page->post_title ) ) {
				$update['post_title'] = 'Sambutan Kepala Madrasah';
			}
		}
		wp_update_post( $update );
		update_post_meta( $page->ID, '_wp_page_template', 'page-sambutan.php' );
		if ( ! get_post_meta( $page->ID, 'man3_sambutan_display_name', true ) ) {
			update_post_meta( $page->ID, 'man3_sambutan_display_name', 'IMAM SYAHRONI' );
		}
		if ( ! get_post_meta( $page->ID, 'man3_sambutan_role', true ) ) {
			update_post_meta( $page->ID, 'man3_sambutan_role', 'Kepala Madrasah' );
		}
		update_option( 'man3_sambutan_page_id', (int) $page->ID, false );
		update_option( 'man3_sambutan_page_ok', 1, false );
		return (int) $page->ID;
	}

	$id = wp_insert_post(
		array(
			'post_title'   => 'Sambutan Kepala Madrasah',
			'post_name'    => 'sambutan-kepala-madrasah',
			'post_parent'  => (int) $parent->ID,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => $content,
			'post_excerpt' => '',
		),
		true
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	update_post_meta( (int) $id, '_wp_page_template', 'page-sambutan.php' );
	update_post_meta( (int) $id, 'man3_sambutan_display_name', 'IMAM SYAHRONI' );
	update_post_meta( (int) $id, 'man3_sambutan_role', 'Kepala Madrasah' );
	update_option( 'man3_sambutan_page_id', (int) $id, false );
	update_option( 'man3_sambutan_page_ok', 1, false );
	return (int) $id;
}

add_action(
	'admin_init',
	static function () {
		if ( current_user_can( 'edit_pages' ) ) {
			man3_sambutan_ensure_page( false );
		}
	}
);

/**
 * Admin shortcut: edit the sambutan page.
 */
function man3_sambutan_admin_menu() {
	add_menu_page(
		__( 'Sambutan Kamad', 'man3-hijau-dingin' ),
		__( 'Sambutan Kamad', 'man3-hijau-dingin' ),
		'edit_pages',
		'man3-sambutan',
		'man3_sambutan_admin_screen',
		'dashicons-megaphone',
		26
	);
}
add_action( 'admin_menu', 'man3_sambutan_admin_menu' );

/**
 * Admin screen.
 */
function man3_sambutan_admin_screen() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$id = man3_sambutan_ensure_page( false );
	$page = $id ? get_post( $id ) : null;
	$url  = $page ? get_permalink( $page ) : man3_sambutan_get_url();
	$edit = $id ? get_edit_post_link( $id, 'raw' ) : '';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Sambutan Kepala Madrasah', 'man3-hijau-dingin' ); ?></h1>
		<p><?php esc_html_e( 'Konten editorial beranda (tombol Selengkapnya). Edit lewat editor halaman WordPress.', 'man3-hijau-dingin' ); ?></p>
		<?php if ( $edit ) : ?>
			<p>
				<a class="button button-primary button-hero" href="<?php echo esc_url( $edit ); ?>">
					<?php esc_html_e( 'Edit teks sambutan', 'man3-hijau-dingin' ); ?>
				</a>
			</p>
		<?php endif; ?>
		<p>
			<strong><?php esc_html_e( 'URL publik:', 'man3-hijau-dingin' ); ?></strong>
			<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $url ); ?></a>
		</p>
		<p class="description">
			<?php esc_html_e( 'Meta opsional di custom field: man3_sambutan_display_name (nama di beranda), man3_sambutan_role (jabatan). Cuplikan beranda diambil dari kutipan halaman atau awal isi.', 'man3-hijau-dingin' ); ?>
		</p>
		<?php if ( isset( $_GET['man3_sambutan_reseed'] ) && current_user_can( 'manage_options' ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<?php
			man3_sambutan_ensure_page( true );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Narasi default telah diisi ulang.', 'man3-hijau-dingin' ) . '</p></div>';
			?>
		<?php elseif ( current_user_can( 'manage_options' ) ) : ?>
			<p>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=man3-sambutan&man3_sambutan_reseed=1' ) ); ?>">
					<?php esc_html_e( 'Isi ulang narasi default', 'man3-hijau-dingin' ); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Public reseed for logged-in admin (parity with other modules).
 */
function man3_sambutan_maybe_reseed() {
	if ( empty( $_GET['man3_sambutan_reseed'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	man3_sambutan_ensure_page( true );
	wp_safe_redirect( man3_sambutan_get_url() );
	exit;
}
add_action( 'template_redirect', 'man3_sambutan_maybe_reseed', 5 );
