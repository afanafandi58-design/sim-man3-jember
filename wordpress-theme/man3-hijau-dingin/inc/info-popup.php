<?php
/**
 * Popup informasi beranda — poster gambar (mode utama), dikelola admin.
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
		'enabled'      => 0,
		'title'        => '',
		'content'      => '',
		'image_id'     => 0,
		'cta_label'    => '',
		'cta_url'      => '',
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
	// Mode utama: butuh gambar poster.
	if ( empty( $s['image_id'] ) ) {
		return false;
	}
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
		$data    = array(
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
		foreach ( array( 'start_date', 'end_date' ) as $dk ) {
			if ( $data[ $dk ] && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $data[ $dk ] ) ) {
				$data[ $dk ] = '';
			}
		}
		if ( $data['enabled'] && empty( $data['image_id'] ) ) {
			$data['enabled'] = 0;
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'Popup membutuhkan gambar poster. Unggah/pilih gambar dulu, lalu aktifkan kembali.', 'man3-hijau-dingin' ) . '</p></div>';
		}
		update_option( 'man3_info_popup', $data, false );
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Pengaturan popup disimpan.', 'man3-hijau-dingin' ) . '</p></div>';
	}

	$s          = man3_popup_get_settings();
	$img        = ! empty( $s['image_id'] ) ? wp_get_attachment_image_url( (int) $s['image_id'], 'medium_large' ) : '';
	$active_now = man3_popup_is_active( $s );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Popup Informasi', 'man3-hijau-dingin' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Mode utama: poster gambar besar di tengah beranda (seperti situs MAN 1). Ganti isi dengan mengunggah gambar dari Media. Teks/tombol opsional. Tutup: × / backdrop / Escape; opsi “jangan tampilkan lagi hari ini”.', 'man3-hijau-dingin' ); ?>
			<?php if ( $active_now ) : ?>
				<strong style="color:#0f5132;"> <?php esc_html_e( 'Status sekarang: AKTIF.', 'man3-hijau-dingin' ); ?></strong>
			<?php else : ?>
				<strong> <?php esc_html_e( 'Status sekarang: tidak tampil (nonaktif, belum ada gambar, atau di luar jadwal).', 'man3-hijau-dingin' ); ?></strong>
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
							<?php esc_html_e( 'Tampilkan di beranda (wajib ada gambar)', 'man3-hijau-dingin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Gambar poster', 'man3-hijau-dingin' ); ?> <span style="color:#b32d2e;">*</span></th>
					<td>
						<input type="hidden" name="image_id" id="man3_popup_image_id" value="<?php echo esc_attr( (string) (int) $s['image_id'] ); ?>" />
						<div id="man3_popup_image_preview" style="margin-bottom:10px;">
							<?php if ( $img ) : ?>
								<img src="<?php echo esc_url( $img ); ?>" alt="" style="max-width:min(520px,100%);height:auto;border-radius:10px;border:1px solid #c3c4c7;box-shadow:0 4px 16px rgba(0,0,0,.08);" />
							<?php else : ?>
								<p class="description" style="margin:0;"><?php esc_html_e( 'Belum ada gambar. Klik “Pilih / unggah gambar”.', 'man3-hijau-dingin' ); ?></p>
							<?php endif; ?>
						</div>
						<button type="button" class="button button-primary" id="man3_popup_image_pick"><?php esc_html_e( 'Pilih / unggah gambar', 'man3-hijau-dingin' ); ?></button>
						<button type="button" class="button" id="man3_popup_image_clear"><?php esc_html_e( 'Hapus gambar', 'man3-hijau-dingin' ); ?></button>
						<p class="description"><?php esc_html_e( 'Disarankan poster landscape (mis. 1200×675 atau lebih). Seluruh pesan utama ada di gambar.', 'man3-hijau-dingin' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="man3_popup_title"><?php esc_html_e( 'Judul teks (opsional)', 'man3-hijau-dingin' ); ?></label></th>
					<td>
						<input type="text" class="large-text" id="man3_popup_title" name="title" value="<?php echo esc_attr( $s['title'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Ditampilkan di bawah gambar jika diisi. Untuk aksesibilitas, alt gambar memakai judul ini.', 'man3-hijau-dingin' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="man3_popup_content"><?php esc_html_e( 'Teks tambahan (opsional)', 'man3-hijau-dingin' ); ?></label></th>
					<td>
						<?php
						wp_editor(
							$s['content'],
							'man3_popup_content',
							array(
								'textarea_name' => 'content',
								'textarea_rows' => 5,
								'media_buttons' => false,
								'teeny'         => true,
							)
						);
						?>
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
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="man3_popup_cookie"><?php esc_html_e( 'Sembunyikan setelah “jangan tampilkan lagi” (jam)', 'man3-hijau-dingin' ); ?></label></th>
					<td>
						<input type="number" min="1" max="720" id="man3_popup_cookie" name="cookie_hours" value="<?php echo esc_attr( (string) (int) $s['cookie_hours'] ); ?>" />
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Simpan popup', 'man3-hijau-dingin' ), 'primary', 'man3_popup_save' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Markup di footer (hanya front-end beranda).
 */
function man3_popup_render() {
	if ( is_admin() || wp_doing_ajax() || is_feed() ) {
		return;
	}
	if ( ! is_front_page() && ! is_home() ) {
		return;
	}

	$s = man3_popup_get_settings();
	if ( ! man3_popup_is_active( $s ) ) {
		return;
	}

	$img_id  = (int) $s['image_id'];
	$img_url = wp_get_attachment_image_url( $img_id, 'full' );
	if ( ! $img_url ) {
		$img_url = wp_get_attachment_image_url( $img_id, 'large' );
	}
	if ( ! $img_url ) {
		return;
	}

	$hours   = max( 1, (int) $s['cookie_hours'] );
	$title   = $s['title'] ? $s['title'] : __( 'Informasi MAN 3 Jember', 'man3-hijau-dingin' );
	$has_text = ( '' !== trim( (string) $s['title'] ) ) || ( '' !== trim( wp_strip_all_tags( (string) $s['content'] ) ) );
	$has_cta  = ! empty( $s['cta_label'] ) && ! empty( $s['cta_url'] );
	$show_bar = $has_text || $has_cta;
	?>
	<div
		class="man3-popup man3-popup--poster"
		id="man3-info-popup"
		hidden
		data-man3-popup
		data-cookie-hours="<?php echo esc_attr( (string) $hours ); ?>"
		data-cookie-key="man3_popup_hide"
		role="dialog"
		aria-modal="true"
		aria-label="<?php echo esc_attr( $title ); ?>"
	>
		<div class="man3-popup__backdrop" data-man3-popup-close tabindex="-1"></div>
		<div class="man3-popup__panel">
			<figure class="man3-popup__poster">
				<img
					src="<?php echo esc_url( $img_url ); ?>"
					alt="<?php echo esc_attr( $title ); ?>"
					width="1200"
					height="675"
					loading="eager"
					decoding="async"
				/>
			</figure>
			<?php if ( $show_bar ) : ?>
				<div class="man3-popup__body">
					<?php if ( ! empty( $s['title'] ) ) : ?>
						<h2 class="man3-popup__title" id="man3-popup-title"><?php echo esc_html( $s['title'] ); ?></h2>
					<?php endif; ?>
					<?php if ( '' !== trim( wp_strip_all_tags( (string) $s['content'] ) ) ) : ?>
						<div class="man3-popup__content entry-content">
							<?php echo wp_kses_post( $s['content'] ); ?>
						</div>
					<?php endif; ?>
					<?php if ( $has_cta ) : ?>
						<p class="man3-popup__cta">
							<a class="btn btn-primary" href="<?php echo esc_url( $s['cta_url'] ); ?>"><?php echo esc_html( $s['cta_label'] ); ?></a>
						</p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<button type="button" class="man3-popup__x" data-man3-popup-close aria-label="<?php esc_attr_e( 'Tutup', 'man3-hijau-dingin' ); ?>">×</button>
			<div class="man3-popup__actions man3-popup__actions--poster">
				<label class="man3-popup__dismiss-today">
					<input type="checkbox" data-man3-popup-snooze />
					<?php esc_html_e( 'Jangan tampilkan lagi hari ini', 'man3-hijau-dingin' ); ?>
				</label>
			</div>
		</div>
	</div>
	<?php
}
// Priority 5: sebelum wp_print_footer_scripts (~20) agar markup ada saat script di-cache lama; JS juga menunggu DOMContentLoaded.
add_action( 'wp_footer', 'man3_popup_render', 5 );
