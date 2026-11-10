<?php
/**
 * Slide informasi beranda — strip foto di bawah menu (bukan modal pemblokir).
 * Dikelola lewat sidebar “Popup Informasi” (multi-foto, urutan, aktif).
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
		'enabled'    => 0,
		'title'      => '',
		'interval'   => 5000,
		'start_date' => '',
		'end_date'   => '',
		'slides'     => array(),
		// Legacy (migrasi sekali):
		'image_id'   => 0,
		'content'    => '',
		'cta_label'  => '',
		'cta_url'    => '',
		'cookie_hours' => 24,
	);
}

/**
 * Normalisasi satu slide.
 *
 * @param mixed $row Raw.
 * @return array<string,mixed>|null
 */
function man3_popup_normalize_slide( $row ) {
	if ( ! is_array( $row ) ) {
		return null;
	}
	$image_id = absint( $row['image_id'] ?? 0 );
	if ( $image_id <= 0 ) {
		return null;
	}
	return array(
		'image_id' => $image_id,
		'title'    => sanitize_text_field( (string) ( $row['title'] ?? '' ) ),
		'link'     => esc_url_raw( (string) ( $row['link'] ?? '' ) ),
		'active'   => empty( $row['active'] ) ? 0 : 1,
	);
}

/**
 * Migrasi image_id tunggal → slides[].
 *
 * @param array<string,mixed> $stored Stored.
 * @return array<string,mixed>
 */
function man3_popup_migrate_slides( $stored ) {
	if ( ! empty( $stored['slides'] ) && is_array( $stored['slides'] ) ) {
		$clean = array();
		foreach ( $stored['slides'] as $row ) {
			$n = man3_popup_normalize_slide( $row );
			if ( $n ) {
				$clean[] = $n;
			}
		}
		$stored['slides'] = $clean;
		return $stored;
	}

	$legacy_id = absint( $stored['image_id'] ?? 0 );
	if ( $legacy_id > 0 ) {
		$stored['slides'] = array(
			array(
				'image_id' => $legacy_id,
				'title'    => sanitize_text_field( (string) ( $stored['title'] ?? '' ) ),
				'link'     => esc_url_raw( (string) ( $stored['cta_url'] ?? '' ) ),
				'active'   => 1,
			),
		);
	} else {
		$stored['slides'] = array();
	}
	return $stored;
}

/**
 * @return array<string,mixed>
 */
function man3_popup_get_settings() {
	$stored = get_option( 'man3_info_popup', array() );
	if ( ! is_array( $stored ) ) {
		$stored = array();
	}
	$stored = array_merge( man3_popup_defaults(), $stored );
	$stored = man3_popup_migrate_slides( $stored );
	$stored['interval'] = max( 3000, min( 15000, absint( $stored['interval'] ) ) );
	return $stored;
}

/**
 * Slide aktif untuk tampilan publik.
 *
 * @param array<string,mixed> $s Settings.
 * @return array<int,array<string,mixed>>
 */
function man3_popup_get_active_slides( $s = null ) {
	if ( null === $s ) {
		$s = man3_popup_get_settings();
	}
	$out = array();
	foreach ( (array) $s['slides'] as $row ) {
		if ( empty( $row['active'] ) || empty( $row['image_id'] ) ) {
			continue;
		}
		$url = wp_get_attachment_image_url( (int) $row['image_id'], 'large' );
		if ( ! $url ) {
			$url = wp_get_attachment_image_url( (int) $row['image_id'], 'full' );
		}
		if ( ! $url ) {
			continue;
		}
		$alt = $row['title'] ? $row['title'] : wp_get_attachment_caption( (int) $row['image_id'] );
		if ( ! $alt ) {
			$alt = __( 'Informasi MAN 3 Jember', 'man3-hijau-dingin' );
		}
		$out[] = array(
			'image_id' => (int) $row['image_id'],
			'title'    => (string) $row['title'],
			'link'     => (string) $row['link'],
			'url'      => $url,
			'alt'      => $alt,
		);
	}
	return $out;
}

/**
 * Whether strip should render.
 *
 * @param array<string,mixed> $s Settings.
 */
function man3_popup_is_active( $s ) {
	if ( empty( $s['enabled'] ) ) {
		return false;
	}
	if ( ! man3_popup_get_active_slides( $s ) ) {
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
 * Persist migrasi image_id → slides sekali (tanpa mengubah angka/foto).
 */
function man3_popup_maybe_persist_migration() {
	$raw = get_option( 'man3_info_popup', null );
	if ( ! is_array( $raw ) ) {
		return;
	}
	if ( ! empty( $raw['slides'] ) && is_array( $raw['slides'] ) ) {
		return;
	}
	if ( empty( $raw['image_id'] ) ) {
		return;
	}
	$migrated = man3_popup_migrate_slides( array_merge( man3_popup_defaults(), $raw ) );
	update_option( 'man3_info_popup', $migrated, false );
}
add_action( 'init', 'man3_popup_maybe_persist_migration', 20 );

/**
 * Admin menu (nama lama dipertahankan agar admin tidak bingung).
 */
function man3_popup_admin_menu() {
	add_menu_page(
		__( 'Popup Informasi', 'man3-hijau-dingin' ),
		__( 'Popup Informasi', 'man3-hijau-dingin' ),
		'manage_options',
		'man3-info-popup',
		'man3_popup_admin_page',
		'dashicons-images-alt',
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
	wp_add_inline_style(
		'wp-admin',
		'.man3-slide-rows{display:flex;flex-direction:column;gap:12px;max-width:720px}'
		. '.man3-slide-row{display:grid;grid-template-columns:96px 1fr auto;gap:12px;align-items:start;padding:12px;border:1px solid #c3c4c7;border-radius:8px;background:#fff}'
		. '.man3-slide-row__thumb img{width:96px;height:64px;object-fit:cover;border-radius:6px;background:#f0f0f1;display:block}'
		. '.man3-slide-row__thumb .placeholder{width:96px;height:64px;border-radius:6px;background:#f0f0f1;display:flex;align-items:center;justify-content:center;color:#787c82;font-size:11px}'
		. '.man3-slide-row__fields{display:flex;flex-direction:column;gap:6px}'
		. '.man3-slide-row__fields input[type=text],.man3-slide-row__fields input[type=url]{width:100%}'
		. '.man3-slide-row__actions{display:flex;flex-direction:column;gap:6px}'
	);
}
add_action( 'admin_enqueue_scripts', 'man3_popup_admin_assets' );

/**
 * Parse slides dari POST.
 *
 * @return array<int,array<string,mixed>>
 */
function man3_popup_parse_posted_slides() {
	$raw = isset( $_POST['slides'] ) && is_array( $_POST['slides'] ) ? wp_unslash( $_POST['slides'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$out = array();
	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$n = man3_popup_normalize_slide(
			array(
				'image_id' => $row['image_id'] ?? 0,
				'title'    => $row['title'] ?? '',
				'link'     => $row['link'] ?? '',
				'active'   => ! empty( $row['active'] ) ? 1 : 0,
			)
		);
		if ( $n ) {
			$out[] = $n;
		}
	}
	return $out;
}

/**
 * Admin UI.
 */
function man3_popup_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['man3_popup_save'] ) && check_admin_referer( 'man3_popup_save_action', 'man3_popup_nonce' ) ) {
		$slides = man3_popup_parse_posted_slides();
		$data   = array(
			'enabled'      => ! empty( $_POST['enabled'] ) ? 1 : 0,
			'title'        => sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) ),
			'interval'     => max( 3000, min( 15000, absint( $_POST['interval'] ?? 5000 ) ) ),
			'start_date'   => sanitize_text_field( wp_unslash( $_POST['start_date'] ?? '' ) ),
			'end_date'     => sanitize_text_field( wp_unslash( $_POST['end_date'] ?? '' ) ),
			'slides'       => $slides,
			'image_id'     => ! empty( $slides[0]['image_id'] ) ? (int) $slides[0]['image_id'] : 0,
			'content'      => '',
			'cta_label'    => '',
			'cta_url'      => '',
			'cookie_hours' => 24,
		);
		foreach ( array( 'start_date', 'end_date' ) as $dk ) {
			if ( $data[ $dk ] && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $data[ $dk ] ) ) {
				$data[ $dk ] = '';
			}
		}
		$has_active = false;
		foreach ( $slides as $sl ) {
			if ( ! empty( $sl['active'] ) ) {
				$has_active = true;
				break;
			}
		}
		if ( $data['enabled'] && ! $has_active ) {
			$data['enabled'] = 0;
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'Slide membutuhkan minimal satu foto aktif. Unggah/pilih foto dulu, lalu aktifkan kembali.', 'man3-hijau-dingin' ) . '</p></div>';
		}
		update_option( 'man3_info_popup', $data, false );
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Pengaturan slide informasi disimpan.', 'man3-hijau-dingin' ) . '</p></div>';
	}

	$s          = man3_popup_get_settings();
	$slides     = ! empty( $s['slides'] ) ? $s['slides'] : array();
	$active_now = man3_popup_is_active( $s );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Popup Informasi → Slide atas', 'man3-hijau-dingin' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Foto informasi tampil sebagai slider otomatis di bawah menu beranda (bukan popup yang memblokir halaman). Hero kegiatan tetap terpisah. Urutkan baris dari atas ke bawah = urutan slide.', 'man3-hijau-dingin' ); ?>
			<?php if ( $active_now ) : ?>
				<strong style="color:#0f5132;"> <?php esc_html_e( 'Status sekarang: AKTIF di beranda.', 'man3-hijau-dingin' ); ?></strong>
			<?php else : ?>
				<strong> <?php esc_html_e( 'Status sekarang: tidak tampil (nonaktif, belum ada foto aktif, atau di luar jadwal).', 'man3-hijau-dingin' ); ?></strong>
			<?php endif; ?>
		</p>
		<form method="post" action="" id="man3-popup-admin-form">
			<?php wp_nonce_field( 'man3_popup_save_action', 'man3_popup_nonce' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Aktifkan slide', 'man3-hijau-dingin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="enabled" value="1" <?php checked( ! empty( $s['enabled'] ) ); ?> />
							<?php esc_html_e( 'Tampilkan strip foto di bawah menu beranda', 'man3-hijau-dingin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="man3_popup_title"><?php esc_html_e( 'Judul area (opsional)', 'man3-hijau-dingin' ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="man3_popup_title" name="title" value="<?php echo esc_attr( $s['title'] ); ?>" placeholder="<?php esc_attr_e( 'Informasi', 'man3-hijau-dingin' ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="man3_popup_interval"><?php esc_html_e( 'Interval autoplay (ms)', 'man3-hijau-dingin' ); ?></label></th>
					<td>
						<input type="number" min="3000" max="15000" step="500" id="man3_popup_interval" name="interval" value="<?php echo esc_attr( (string) (int) $s['interval'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Disarankan 4500–6000. Pause otomatis saat hover / fokus / tab tersembunyi.', 'man3-hijau-dingin' ); ?></p>
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
					<th scope="row"><?php esc_html_e( 'Foto slide', 'man3-hijau-dingin' ); ?> <span style="color:#b32d2e;">*</span></th>
					<td>
						<div id="man3_slide_rows" class="man3-slide-rows" data-next-index="<?php echo esc_attr( (string) count( $slides ) ); ?>">
							<?php
							if ( ! $slides ) {
								$slides = array(
									array(
										'image_id' => 0,
										'title'    => '',
										'link'     => '',
										'active'   => 1,
									),
								);
							}
							foreach ( $slides as $i => $slide ) :
								$thumb = ! empty( $slide['image_id'] ) ? wp_get_attachment_image_url( (int) $slide['image_id'], 'medium' ) : '';
								?>
								<div class="man3-slide-row" data-slide-row>
									<div class="man3-slide-row__thumb" data-thumb>
										<?php if ( $thumb ) : ?>
											<img src="<?php echo esc_url( $thumb ); ?>" alt="" />
										<?php else : ?>
											<span class="placeholder"><?php esc_html_e( 'Kosong', 'man3-hijau-dingin' ); ?></span>
										<?php endif; ?>
									</div>
									<div class="man3-slide-row__fields">
										<input type="hidden" name="slides[<?php echo esc_attr( (string) $i ); ?>][image_id]" value="<?php echo esc_attr( (string) (int) $slide['image_id'] ); ?>" data-image-id />
										<label>
											<input type="checkbox" name="slides[<?php echo esc_attr( (string) $i ); ?>][active]" value="1" <?php checked( ! empty( $slide['active'] ) ); ?> />
											<?php esc_html_e( 'Aktif', 'man3-hijau-dingin' ); ?>
										</label>
										<input type="text" name="slides[<?php echo esc_attr( (string) $i ); ?>][title]" value="<?php echo esc_attr( $slide['title'] ); ?>" placeholder="<?php esc_attr_e( 'Judul / alt (opsional)', 'man3-hijau-dingin' ); ?>" />
										<input type="url" name="slides[<?php echo esc_attr( (string) $i ); ?>][link]" value="<?php echo esc_attr( $slide['link'] ); ?>" placeholder="https:// (tautan opsional)" />
									</div>
									<div class="man3-slide-row__actions">
										<button type="button" class="button button-primary" data-pick-image><?php esc_html_e( 'Pilih foto', 'man3-hijau-dingin' ); ?></button>
										<button type="button" class="button" data-move-up><?php esc_html_e( 'Naik', 'man3-hijau-dingin' ); ?></button>
										<button type="button" class="button" data-move-down><?php esc_html_e( 'Turun', 'man3-hijau-dingin' ); ?></button>
										<button type="button" class="button link-delete" data-remove-row><?php esc_html_e( 'Hapus', 'man3-hijau-dingin' ); ?></button>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
						<p style="margin-top:12px;">
							<button type="button" class="button" id="man3_slide_add"><?php esc_html_e( '+ Tambah foto', 'man3-hijau-dingin' ); ?></button>
						</p>
						<p class="description"><?php esc_html_e( 'Disarankan foto landscape (mis. 1200×500). Hero kegiatan dikelola terpisah di menu Hero Slide.', 'man3-hijau-dingin' ); ?></p>
						<template id="man3_slide_row_tpl">
							<div class="man3-slide-row" data-slide-row>
								<div class="man3-slide-row__thumb" data-thumb>
									<span class="placeholder"><?php esc_html_e( 'Kosong', 'man3-hijau-dingin' ); ?></span>
								</div>
								<div class="man3-slide-row__fields">
									<input type="hidden" name="slides[__i__][image_id]" value="0" data-image-id />
									<label>
										<input type="checkbox" name="slides[__i__][active]" value="1" checked />
										<?php esc_html_e( 'Aktif', 'man3-hijau-dingin' ); ?>
									</label>
									<input type="text" name="slides[__i__][title]" value="" placeholder="<?php esc_attr_e( 'Judul / alt (opsional)', 'man3-hijau-dingin' ); ?>" />
									<input type="url" name="slides[__i__][link]" value="" placeholder="https:// (tautan opsional)" />
								</div>
								<div class="man3-slide-row__actions">
									<button type="button" class="button button-primary" data-pick-image><?php esc_html_e( 'Pilih foto', 'man3-hijau-dingin' ); ?></button>
									<button type="button" class="button" data-move-up><?php esc_html_e( 'Naik', 'man3-hijau-dingin' ); ?></button>
									<button type="button" class="button" data-move-down><?php esc_html_e( 'Turun', 'man3-hijau-dingin' ); ?></button>
									<button type="button" class="button link-delete" data-remove-row><?php esc_html_e( 'Hapus', 'man3-hijau-dingin' ); ?></button>
								</div>
							</div>
						</template>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Simpan slide informasi', 'man3-hijau-dingin' ), 'primary', 'man3_popup_save' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Markup strip di alur halaman (bawah menu, atas hero).
 */
function man3_popup_render_strip() {
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

	$slides = man3_popup_get_active_slides( $s );
	if ( ! $slides ) {
		return;
	}

	$count    = count( $slides );
	$interval = (int) $s['interval'];
	$heading  = $s['title'] ? $s['title'] : __( 'Informasi', 'man3-hijau-dingin' );
	?>
	<section
		class="man3-info-strip"
		id="info-slide"
		aria-roledescription="carousel"
		aria-label="<?php echo esc_attr( $heading ); ?>"
		data-info-slider
		data-info-interval="<?php echo esc_attr( (string) $interval ); ?>"
	>
		<div class="container man3-info-strip__shell">
			<div class="man3-info-strip__head">
				<span class="man3-info-strip__eyebrow"><?php echo esc_html( $heading ); ?></span>
				<?php if ( $count > 1 ) : ?>
					<span class="man3-info-strip__count" data-info-status aria-live="polite">1 / <?php echo esc_html( (string) $count ); ?></span>
				<?php endif; ?>
			</div>
			<div class="man3-info-strip__viewport">
				<div class="man3-info-strip__track">
					<?php foreach ( $slides as $i => $slide ) : ?>
						<figure
							class="man3-info-strip__slide<?php echo 0 === $i ? ' is-active' : ''; ?>"
							data-info-slide="<?php echo esc_attr( (string) $i ); ?>"
							<?php echo 0 === $i ? '' : ' hidden'; ?>
						>
							<?php if ( ! empty( $slide['link'] ) ) : ?>
								<a class="man3-info-strip__link" href="<?php echo esc_url( $slide['link'] ); ?>">
									<img
										src="<?php echo esc_url( $slide['url'] ); ?>"
										alt="<?php echo esc_attr( $slide['alt'] ); ?>"
										width="1200"
										height="480"
										<?php echo 0 === $i ? 'loading="eager"' : 'loading="lazy"'; ?>
										decoding="async"
									/>
								</a>
							<?php else : ?>
								<img
									src="<?php echo esc_url( $slide['url'] ); ?>"
									alt="<?php echo esc_attr( $slide['alt'] ); ?>"
									width="1200"
									height="480"
									<?php echo 0 === $i ? 'loading="eager"' : 'loading="lazy"'; ?>
									decoding="async"
								/>
							<?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>
				<?php if ( $count > 1 ) : ?>
					<div class="man3-info-strip__nav" role="group" aria-label="<?php esc_attr_e( 'Navigasi slide informasi', 'man3-hijau-dingin' ); ?>">
						<button type="button" class="man3-info-strip__arrow man3-info-strip__arrow--prev" data-info-prev aria-label="<?php esc_attr_e( 'Slide sebelumnya', 'man3-hijau-dingin' ); ?>">
							<span aria-hidden="true">‹</span>
						</button>
						<button type="button" class="man3-info-strip__arrow man3-info-strip__arrow--next" data-info-next aria-label="<?php esc_attr_e( 'Slide berikutnya', 'man3-hijau-dingin' ); ?>">
							<span aria-hidden="true">›</span>
						</button>
					</div>
					<div class="man3-info-strip__dots" role="tablist" aria-label="<?php esc_attr_e( 'Pilih foto informasi', 'man3-hijau-dingin' ); ?>">
						<?php for ( $i = 0; $i < $count; $i++ ) : ?>
							<button
								type="button"
								class="man3-info-strip__dot<?php echo 0 === $i ? ' is-active' : ''; ?>"
								data-info-dot="<?php echo esc_attr( (string) $i ); ?>"
								role="tab"
								aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
								aria-label="<?php echo esc_attr( sprintf( /* translators: %d slide number */ __( 'Foto %d', 'man3-hijau-dingin' ), $i + 1 ) ); ?>"
							></button>
						<?php endfor; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Legacy modal dihapus — tidak render di footer.
 */
function man3_popup_render() {
	// No-op: diganti strip di alur halaman.
}
