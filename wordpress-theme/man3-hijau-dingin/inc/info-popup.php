<?php
/**
 * Popup informasi beranda — dikelola admin, tanpa plugin pihak ketiga.
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default settings.
 *
 * @return array<string,mixed>
 */
function man3_popup_defaults() {
	return array(
		'enabled'      => 1,
		'title'        => __( 'Selamat datang di MAN 3 Jember', 'man3-hijau-dingin' ),
		'content'      => '<p>' . esc_html__( 'MAJU BERMUTU MENDUNIA — pantau pengumuman dan berita terbaru madrasah di situs resmi ini.', 'man3-hijau-dingin' ) . '</p>',
		'image_id'     => 0,
		'cta_label'    => __( 'Lihat pengumuman', 'man3-hijau-dingin' ),
		'cta_url'      => home_url( '/pengumuman/' ),
		'start_date'   => '',
		'end_date'     => '',
		'cookie_hours' => 24,
	);
}

/**
 * @return array<string,mixed>
 */
function man3_popup_get_settings() {
	$stored = get_option( 'man3_info_popup', array() );
	if ( ! is_array( $stored ) ) {
		$stored = array();
	}
	return array_merge( man3_popup_defaults(), $stored );
}

/**
 * Whether popup should render for this request.
 *
 * @param array<string,mixed> $s Settings.
 */
function man3_popup_is_active( $s ) {
	if ( empty( $s['enabled'] ) ) {
		return false;
	}
	// Admin bar / customize preview: still show so editors can check.
	$today = gmdate( 'Y-m-d' );
	$start = ! empty( $s['start_date'] ) ? (string) $s['start_date'] : '';
	$end   = ! empty( $s['end_date'] ) ? (string) $s['end_date'] : '';
	if ( $start && $today < $start ) {
		return false;
	}
	if ( $end && $today > $end ) {
		return false;
	}
	return true;
}

/**
 * Admin menu.
 */
function man3_popup_admin_menu() {
	add_menu_page(
		__( 'Popup Informasi', 'man3-hijau-dingin' ),
		__( 'Popup Informasi', 'man3-hijau-dingin' ),
		'manage_options',
		'man3-info-popup',
		'man3_popup_admin_page',
		'dashicons-megaphone',
		26
	);
}
add_action( 'admin_menu', 'man3_popup_admin_menu' );

/**
 * Media uploader on settings page.
 *
 * @param string $hook Hook.
 */
function man3_popup_admin_assets( $hook ) {
	if ( 'toplevel_page_man3-info-popup' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'man3-popup-admin',
		get_template_directory_uri() . '/assets/js/popup-admin.js',
		array( 'jquery' ),
		MAN3_HIJAU_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'man3_popup_admin_assets' );

/**
 * Admin UI.
 */
function man3_popup_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['man3_popup_save'] ) && check_admin_referer( 'man3_popup_save_action', 'man3_popup_nonce' ) ) {
		$prev    = man3_popup_get_settings();
		$content = wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) );
		if ( '' === trim( wp_strip_all_tags( $content ) ) ) {
			$content = $prev['content'];
		}
		$data = array(
			'enabled'      => ! empty( $_POST['enabled'] ) ? 1 : 0,
			'title'        => sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) ),
			'content'      => $content,
			'image_id'     => absint( $_POST['image_id'] ?? 0 ),
			'cta_label'    => sanitize_text_field( wp_unslash( $_POST['cta_label'] ?? '' ) ),
			'cta_url'      => esc_url_raw( wp_unslash( $_POST['cta_url'] ?? '' ) ),
			'start_date'   => sanitize_text_field( wp_unslash( $_POST['start_date'] ?? '' ) ),
			'end_date'     => sanitize_text_field( wp_unslash( $_POST['end_date'] ?? '' ) ),
			'cookie_hours' => max( 1, min( 720, absint( $_POST['cookie_hours'] ?? 24 ) ) ),
		);
		// Normalize dates YYYY-MM-DD.
		foreach ( array( 'start_date', 'end_date' ) as $dk ) {
			if ( $data[ $dk ] && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $data[ $dk ] ) ) {
				$data[ $dk ] = '';
			}
		}
		update_option( 'man3_info_popup', $data, false );
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Pengaturan popup disimpan.', 'man3-hijau-dingin' ) . '</p></div>';
	}

	$s     = man3_popup_get_settings();
	$img   = ! empty( $s['image_id'] ) ? wp_get_attachment_image_url( (int) $s['image_id'], 'medium' ) : '';
	$active_now = man3_popup_is_active( $s );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Popup Informasi', 'man3-hijau-dingin' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Popup muncul di beranda saat pengunjung masuk (bisa ditutup; opsi “jangan tampilkan lagi hari ini”). Tidak memakai plugin CM Pop-Up.', 'man3-hijau-dingin' ); ?>
			<?php if ( $active_now ) : ?>
				<strong style="color:#0f5132;"> <?php esc_html_e( 'Status sekarang: AKTIF.', 'man3-hijau-dingin' ); ?></strong>
			<?php else : ?>
				<strong> <?php esc_html_e( 'Status sekarang: tidak tampil (nonaktif atau di luar jadwal).', 'man3-hijau-dingin' ); ?></strong>
			<?php endif; ?>
		</p>
		<form method="post" action="">
			<?php wp_nonce_field( 'man3_popup_save_action', 'man3_popup_nonce' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Aktifkan popup', 'man3-hijau-dingin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="enabled" value="1" <?php checked( ! empty( $s['enabled'] ) ); ?> />
							<?php esc_html_e( 'Tampilkan di situs publik', 'man3-hijau-dingin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="man3_popup_title"><?php esc_html_e( 'Judul', 'man3-hijau-dingin' ); ?></label></th>
					<td><input type="text" class="large-text" id="man3_popup_title" name="title" value="<?php echo esc_attr( $s['title'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="man3_popup_content"><?php esc_html_e( 'Isi teks', 'man3-hijau-dingin' ); ?></label></th>
					<td>
						<?php
						wp_editor(
							$s['content'],
							'man3_popup_content',
							array(
								'textarea_name' => 'content',
								'textarea_rows' => 8,
								'media_buttons' => false,
								'teeny'         => true,
							)
						);
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Gambar (opsional)', 'man3-hijau-dingin' ); ?></th>
					<td>
						<input type="hidden" name="image_id" id="man3_popup_image_id" value="<?php echo esc_attr( (string) (int) $s['image_id'] ); ?>" />
						<div id="man3_popup_image_preview" style="margin-bottom:8px;">
							<?php if ( $img ) : ?>
								<img src="<?php echo esc_url( $img ); ?>" alt="" style="max-width:280px;height:auto;border-radius:8px;" />
							<?php endif; ?>
						</div>
						<button type="button" class="button" id="man3_popup_image_pick"><?php esc_html_e( 'Pilih gambar', 'man3-hijau-dingin' ); ?></button>
						<button type="button" class="button" id="man3_popup_image_clear"><?php esc_html_e( 'Hapus gambar', 'man3-hijau-dingin' ); ?></button>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="man3_popup_cta_label"><?php esc_html_e( 'Tombol tautan (opsional)', 'man3-hijau-dingin' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="man3_popup_cta_label" name="cta_label" value="<?php echo esc_attr( $s['cta_label'] ); ?>" placeholder="<?php esc_attr_e( 'Teks tombol', 'man3-hijau-dingin' ); ?>" />
						<input type="url" class="regular-text" id="man3_popup_cta_url" name="cta_url" value="<?php echo esc_attr( $s['cta_url'] ); ?>" placeholder="https://" style="margin-top:6px;display:block;" />
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Jadwal (opsional)', 'man3-hijau-dingin' ); ?></th>
					<td>
						<label><?php esc_html_e( 'Mulai', 'man3-hijau-dingin' ); ?>
							<input type="date" name="start_date" value="<?php echo esc_attr( $s['start_date'] ); ?>" />
						</label>
						&nbsp;
						<label><?php esc_html_e( 'Selesai', 'man3-hijau-dingin' ); ?>
							<input type="date" name="end_date" value="<?php echo esc_attr( $s['end_date'] ); ?>" />
						</label>
						<p class="description"><?php esc_html_e( 'Kosongkan untuk selalu aktif selama centang “Aktifkan” menyala.', 'man3-hijau-dingin' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="man3_popup_cookie"><?php esc_html_e( 'Sembunyikan setelah ditutup (jam)', 'man3-hijau-dingin' ); ?></label></th>
					<td>
						<input type="number" min="1" max="720" id="man3_popup_cookie" name="cookie_hours" value="<?php echo esc_attr( (string) (int) $s['cookie_hours'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Default 24 jam jika pengunjung memilih “Jangan tampilkan lagi hari ini”.', 'man3-hijau-dingin' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Simpan popup', 'man3-hijau-dingin' ), 'primary', 'man3_popup_save' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Markup di footer (hanya front-end).
 */
function man3_popup_render() {
	if ( is_admin() || wp_doing_ajax() || is_feed() ) {
		return;
	}
	// Focus on homepage entry like classic welcome popup; still allow other pages if desired — homepage only.
	if ( ! is_front_page() && ! is_home() ) {
		return;
	}

	$s = man3_popup_get_settings();
	if ( ! man3_popup_is_active( $s ) ) {
		return;
	}

	$img_url = ! empty( $s['image_id'] ) ? wp_get_attachment_image_url( (int) $s['image_id'], 'large' ) : '';
	$hours   = max( 1, (int) $s['cookie_hours'] );
	$title   = $s['title'] ? $s['title'] : __( 'Informasi', 'man3-hijau-dingin' );
	?>
	<div
		class="man3-popup"
		id="man3-info-popup"
		hidden
		data-man3-popup
		data-cookie-hours="<?php echo esc_attr( (string) $hours ); ?>"
		data-cookie-key="man3_popup_hide"
		role="dialog"
		aria-modal="true"
		aria-labelledby="man3-popup-title"
	>
		<div class="man3-popup__backdrop" data-man3-popup-close tabindex="-1"></div>
		<div class="man3-popup__panel">
			<button type="button" class="man3-popup__x" data-man3-popup-close aria-label="<?php esc_attr_e( 'Tutup', 'man3-hijau-dingin' ); ?>">×</button>
			<?php if ( $img_url ) : ?>
				<div class="man3-popup__media">
					<img src="<?php echo esc_url( $img_url ); ?>" alt="" width="720" height="400" loading="eager" />
				</div>
			<?php endif; ?>
			<div class="man3-popup__body">
				<h2 class="man3-popup__title" id="man3-popup-title"><?php echo esc_html( $title ); ?></h2>
				<div class="man3-popup__content entry-content">
					<?php echo wp_kses_post( $s['content'] ); ?>
				</div>
				<?php if ( ! empty( $s['cta_label'] ) && ! empty( $s['cta_url'] ) ) : ?>
					<p class="man3-popup__cta">
						<a class="btn btn-primary" href="<?php echo esc_url( $s['cta_url'] ); ?>"><?php echo esc_html( $s['cta_label'] ); ?></a>
					</p>
				<?php endif; ?>
				<div class="man3-popup__actions">
					<label class="man3-popup__dismiss-today">
						<input type="checkbox" data-man3-popup-snooze />
						<?php esc_html_e( 'Jangan tampilkan lagi hari ini', 'man3-hijau-dingin' ); ?>
					</label>
					<button type="button" class="btn btn-ghost man3-popup__close-btn" data-man3-popup-close>
						<?php esc_html_e( 'Tutup', 'man3-hijau-dingin' ); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'man3_popup_render', 30 );
