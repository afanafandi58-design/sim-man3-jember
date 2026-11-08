<?php
/**
 * Survey Kepuasan beranda — pertanyaan dikelola admin + rekap agregat.
 *
 * Keamanan: nonce, honeypot, throttle IP-hash, sanitasi skala 1–5,
 * tanpa data sensitif; hasil admin hanya untuk manage_options.
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Skala jawaban.
 *
 * @return array<int,string>
 */
function man3_survey_scale_labels() {
	return array(
		1 => __( 'Sangat tidak puas', 'man3-hijau-dingin' ),
		2 => __( 'Tidak puas', 'man3-hijau-dingin' ),
		3 => __( 'Cukup', 'man3-hijau-dingin' ),
		4 => __( 'Puas', 'man3-hijau-dingin' ),
		5 => __( 'Sangat puas', 'man3-hijau-dingin' ),
	);
}

/**
 * Default 20 pertanyaan (termasuk zona integritas).
 *
 * @return array<int,array<string,mixed>>
 */
function man3_survey_default_questions() {
	$texts = array(
		'Seberapa puas Anda terhadap kualitas layanan pendidikan di MAN 3 Jember secara keseluruhan?',
		'Bagaimana penilaian Anda terhadap kualitas pembelajaran dan kompetensi guru?',
		'Seberapa puas Anda terhadap sikap dan pelayanan tenaga kependidikan (TU/administrasi)?',
		'Bagaimana penilaian Anda terhadap sarana dan prasarana pembelajaran?',
		'Seberapa puas Anda terhadap kebersihan dan kenyamanan lingkungan madrasah?',
		'Bagaimana penilaian Anda terhadap keamanan dan ketertiban di lingkungan madrasah?',
		'Seberapa puas Anda terhadap komunikasi madrasah dengan orang tua/wali dan masyarakat?',
		'Bagaimana penilaian Anda terhadap ketersediaan informasi publik di website/media resmi?',
		'Seberapa puas Anda terhadap program ekstrakurikuler dan pengembangan bakat siswa?',
		'Bagaimana penilaian Anda terhadap pembinaan karakter dan kegiatan keagamaan?',
		'Seberapa puas Anda terhadap kecepatan dan ketepatan layanan administrasi (surat, absensi, dll.)?',
		'Bagaimana penilaian Anda terhadap penanganan pengaduan/saran dari warga madrasah?',
		'Seberapa puas Anda terhadap transparansi informasi program dan kegiatan madrasah?',
		'Bagaimana penilaian Anda terhadap pemahaman dan penerapan Zona Integritas (WBK/WBBM) di MAN 3 Jember?',
		'Seberapa puas Anda bahwa layanan diberikan tanpa pungutan liar atau gratifikasi?',
		'Bagaimana penilaian Anda terhadap keadilan layanan tanpa diskriminasi?',
		'Seberapa puas Anda terhadap budaya antikorupsi dan integritas pegawai di madrasah?',
		'Bagaimana penilaian Anda terhadap kepatuhan petugas terhadap SOP/prosedur layanan?',
		'Seberapa puas Anda terhadap komitmen pimpinan dalam menjaga integritas layanan publik?',
		'Seberapa besar kemungkinan Anda merekomendasikan MAN 3 Jember kepada orang lain?',
	);

	$out = array();
	foreach ( $texts as $i => $text ) {
		$out[] = array(
			'id'     => 'q' . ( $i + 1 ),
			'text'   => $text,
			'order'  => ( $i + 1 ) * 10,
			'active' => 1,
		);
	}
	return $out;
}

/**
 * @return array<int,array<string,mixed>>
 */
function man3_survey_get_questions( $active_only = false ) {
	$stored = get_option( 'man3_survey_questions', null );
	if ( ! is_array( $stored ) || ! $stored ) {
		$stored = man3_survey_default_questions();
		update_option( 'man3_survey_questions', $stored, false );
	}
	$items = array();
	foreach ( $stored as $q ) {
		if ( ! is_array( $q ) || empty( $q['id'] ) || empty( $q['text'] ) ) {
			continue;
		}
		$active = ! empty( $q['active'] ) ? 1 : 0;
		if ( $active_only && ! $active ) {
			continue;
		}
		$items[] = array(
			'id'     => sanitize_key( $q['id'] ),
			'text'   => sanitize_text_field( $q['text'] ),
			'order'  => isset( $q['order'] ) ? (int) $q['order'] : 0,
			'active' => $active,
		);
	}
	usort(
		$items,
		static function ( $a, $b ) {
			if ( $a['order'] === $b['order'] ) {
				return strcmp( $a['id'], $b['id'] );
			}
			return $a['order'] <=> $b['order'];
		}
	);
	return $items;
}

/**
 * Create responses table.
 */
function man3_survey_install_table() {
	global $wpdb;
	$table   = $wpdb->prefix . 'man3_survey_responses';
	$charset = $wpdb->get_charset_collate();
	$sql     = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		created_at datetime NOT NULL,
		ip_hash char(64) NOT NULL DEFAULT '',
		answers longtext NOT NULL,
		PRIMARY KEY  (id),
		KEY created_at (created_at),
		KEY ip_hash (ip_hash)
	) {$charset};";
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
	update_option( 'man3_survey_db_version', '1', false );
}
add_action(
	'after_switch_theme',
	static function () {
		man3_survey_install_table();
	}
);

/**
 * Ensure table exists (admin / first submit).
 */
function man3_survey_maybe_install() {
	if ( get_option( 'man3_survey_db_version' ) ) {
		return;
	}
	man3_survey_install_table();
}

/**
 * Client IP hash (one-way) for throttle — bukan PII mentah.
 *
 * @return string
 */
function man3_survey_client_ip_hash() {
	$ip = '';
	if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}
	$salt = wp_salt( 'nonce' );
	return hash_hmac( 'sha256', $ip, $salt );
}

/**
 * Rate limit: max 1 submit / 30 menit per IP-hash.
 *
 * @param string $ip_hash Hash.
 * @return bool True if blocked.
 */
function man3_survey_is_throttled( $ip_hash ) {
	$key = 'man3_survey_rl_' . substr( $ip_hash, 0, 32 );
	return (bool) get_transient( $key );
}

/**
 * @param string $ip_hash Hash.
 */
function man3_survey_mark_throttled( $ip_hash ) {
	$key = 'man3_survey_rl_' . substr( $ip_hash, 0, 32 );
	set_transient( $key, 1, 30 * MINUTE_IN_SECONDS );
}

/**
 * Handle public form POST.
 */
function man3_survey_handle_submit() {
	if ( empty( $_POST['man3_survey_submit'] ) ) {
		return;
	}
	if ( ! is_front_page() && ! is_home() ) {
		return;
	}

	$redirect = home_url( '/#survey-kepuasan' );

	if ( ! isset( $_POST['man3_survey_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_survey_nonce'] ) ), 'man3_survey_submit' ) ) {
		wp_safe_redirect( add_query_arg( 'survey', 'invalid', $redirect ) );
		exit;
	}

	// Honeypot: harus kosong.
	if ( ! empty( $_POST['man3_survey_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'survey', 'ok', $redirect ) );
		exit;
	}

	$ip_hash = man3_survey_client_ip_hash();
	if ( man3_survey_is_throttled( $ip_hash ) ) {
		wp_safe_redirect( add_query_arg( 'survey', 'limit', $redirect ) );
		exit;
	}

	$questions = man3_survey_get_questions( true );
	if ( ! $questions ) {
		wp_safe_redirect( add_query_arg( 'survey', 'empty', $redirect ) );
		exit;
	}

	$raw = isset( $_POST['man3_survey_q'] ) && is_array( $_POST['man3_survey_q'] ) ? wp_unslash( $_POST['man3_survey_q'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$answers = array();
	foreach ( $questions as $q ) {
		$id = $q['id'];
		if ( ! isset( $raw[ $id ] ) ) {
			wp_safe_redirect( add_query_arg( 'survey', 'incomplete', $redirect ) );
			exit;
		}
		$val = (int) $raw[ $id ];
		if ( $val < 1 || $val > 5 ) {
			wp_safe_redirect( add_query_arg( 'survey', 'invalid', $redirect ) );
			exit;
		}
		$answers[ $id ] = $val;
	}

	man3_survey_maybe_install();
	global $wpdb;
	$table = $wpdb->prefix . 'man3_survey_responses';
	$ok    = $wpdb->insert(
		$table,
		array(
			'created_at' => current_time( 'mysql' ),
			'ip_hash'    => $ip_hash,
			'answers'    => wp_json_encode( $answers ),
		),
		array( '%s', '%s', '%s' )
	);

	if ( ! $ok ) {
		wp_safe_redirect( add_query_arg( 'survey', 'error', $redirect ) );
		exit;
	}

	man3_survey_mark_throttled( $ip_hash );
	wp_safe_redirect( add_query_arg( 'survey', 'ok', $redirect ) );
	exit;
}
add_action( 'template_redirect', 'man3_survey_handle_submit', 20 );

/**
 * Flash message from query arg.
 *
 * @return array{type:string,text:string}|null
 */
function man3_survey_flash_message() {
	if ( empty( $_GET['survey'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return null;
	}
	$code = sanitize_key( wp_unslash( $_GET['survey'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$map  = array(
		'ok'         => array( 'type' => 'success', 'text' => __( 'Terima kasih. Tanggapan Anda telah tercatat.', 'man3-hijau-dingin' ) ),
		'limit'      => array( 'type' => 'warn', 'text' => __( 'Anda baru saja mengirim survei. Silakan coba lagi nanti.', 'man3-hijau-dingin' ) ),
		'incomplete' => array( 'type' => 'warn', 'text' => __( 'Mohon jawab semua pertanyaan sebelum mengirim.', 'man3-hijau-dingin' ) ),
		'invalid'    => array( 'type' => 'warn', 'text' => __( 'Kiriman tidak valid. Muat ulang halaman dan coba lagi.', 'man3-hijau-dingin' ) ),
		'error'      => array( 'type' => 'warn', 'text' => __( 'Terjadi gangguan saat menyimpan. Coba lagi sebentar.', 'man3-hijau-dingin' ) ),
		'empty'      => array( 'type' => 'warn', 'text' => __( 'Survei sedang tidak aktif.', 'man3-hijau-dingin' ) ),
	);
	return isset( $map[ $code ] ) ? $map[ $code ] : null;
}

/**
 * Render section beranda.
 */
function man3_survey_render_section() {
	$questions = man3_survey_get_questions( true );
	$scale     = man3_survey_scale_labels();
	$flash     = man3_survey_flash_message();
	?>
	<section class="section section-survey" id="survey-kepuasan" aria-labelledby="survey-title">
		<div class="container">
			<div class="section-head">
				<p class="section-kicker"><?php esc_html_e( 'Partisipasi publik', 'man3-hijau-dingin' ); ?></p>
				<h2 class="section-title" id="survey-title"><?php esc_html_e( 'Survey Kepuasan', 'man3-hijau-dingin' ); ?></h2>
				<p class="section-lead">
					<?php esc_html_e( 'Bantu evaluasi layanan MAN 3 Jember, termasuk penerapan zona integritas. Skala 1 (Sangat tidak puas) hingga 5 (Sangat puas). Anonim.', 'man3-hijau-dingin' ); ?>
				</p>
			</div>

			<?php if ( $flash ) : ?>
				<p class="survey-flash survey-flash--<?php echo esc_attr( $flash['type'] ); ?>" role="status">
					<?php echo esc_html( $flash['text'] ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! $questions ) : ?>
				<p class="survey-empty"><?php esc_html_e( 'Survei belum tersedia. Silakan kembali lagi nanti.', 'man3-hijau-dingin' ); ?></p>
			<?php elseif ( $flash && 'success' === $flash['type'] ) : ?>
				<div class="survey-thanks">
					<p><?php esc_html_e( 'Partisipasi Anda membantu peningkatan mutu dan integritas layanan madrasah.', 'man3-hijau-dingin' ); ?></p>
				</div>
			<?php else : ?>
				<form class="survey-form" method="post" action="<?php echo esc_url( home_url( '/' ) ); ?>#survey-kepuasan" novalidate>
					<?php wp_nonce_field( 'man3_survey_submit', 'man3_survey_nonce' ); ?>
					<input type="hidden" name="man3_survey_submit" value="1" />
					<p class="survey-hp" aria-hidden="true">
						<label for="man3_survey_website"><?php esc_html_e( 'Jangan diisi', 'man3-hijau-dingin' ); ?></label>
						<input type="text" name="man3_survey_website" id="man3_survey_website" value="" tabindex="-1" autocomplete="off" />
					</p>

					<ol class="survey-list">
						<?php foreach ( $questions as $i => $q ) : ?>
							<li class="survey-item">
								<p class="survey-q">
									<span class="survey-q-num"><?php echo esc_html( (string) ( $i + 1 ) ); ?>.</span>
									<?php echo esc_html( $q['text'] ); ?>
								</p>
								<div class="survey-scale" role="radiogroup" aria-label="<?php echo esc_attr( $q['text'] ); ?>">
									<?php foreach ( $scale as $val => $label ) : ?>
										<label class="survey-opt">
											<input
												type="radio"
												name="man3_survey_q[<?php echo esc_attr( $q['id'] ); ?>]"
												value="<?php echo esc_attr( (string) $val ); ?>"
												required
											/>
											<span class="survey-opt-val"><?php echo esc_html( (string) $val ); ?></span>
											<span class="survey-opt-label"><?php echo esc_html( $label ); ?></span>
										</label>
									<?php endforeach; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ol>

					<p class="survey-note">
						<?php esc_html_e( 'Tidak menyimpan nama, email, atau data identitas. Maksimal satu kiriman per perangkat dalam 30 menit.', 'man3-hijau-dingin' ); ?>
					</p>
					<button type="submit" class="btn btn-solid survey-submit">
						<?php esc_html_e( 'Kirim tanggapan', 'man3-hijau-dingin' ); ?>
					</button>
				</form>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Admin menu.
 */
function man3_survey_admin_menu() {
	add_menu_page(
		__( 'Survey Kepuasan', 'man3-hijau-dingin' ),
		__( 'Survey Kepuasan', 'man3-hijau-dingin' ),
		'manage_options',
		'man3-survey',
		'man3_survey_admin_screen',
		'dashicons-chart-bar',
		27
	);
}
add_action( 'admin_menu', 'man3_survey_admin_menu' );

/**
 * Save questions from admin (capability-checked).
 */
function man3_survey_admin_save() {
	if ( ! isset( $_POST['man3_survey_admin_save'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Akses ditolak.', 'man3-hijau-dingin' ) );
	}
	check_admin_referer( 'man3_survey_admin' );

	$raw = isset( $_POST['questions'] ) && is_array( $_POST['questions'] ) ? wp_unslash( $_POST['questions'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$out = array();
	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$text = isset( $row['text'] ) ? sanitize_text_field( $row['text'] ) : '';
		if ( '' === $text ) {
			continue;
		}
		$id = isset( $row['id'] ) ? sanitize_key( $row['id'] ) : '';
		if ( '' === $id ) {
			$id = 'q' . wp_generate_password( 8, false, false );
		}
		$out[] = array(
			'id'     => $id,
			'text'   => $text,
			'order'  => isset( $row['order'] ) ? (int) $row['order'] : 0,
			'active' => ! empty( $row['active'] ) ? 1 : 0,
		);
	}

	if ( ! empty( $_POST['man3_survey_new_text'] ) ) {
		$new_text = sanitize_text_field( wp_unslash( $_POST['man3_survey_new_text'] ) );
		if ( '' !== $new_text ) {
			$out[] = array(
				'id'     => 'q' . wp_generate_password( 8, false, false ),
				'text'   => $new_text,
				'order'  => isset( $_POST['man3_survey_new_order'] ) ? (int) $_POST['man3_survey_new_order'] : ( count( $out ) + 1 ) * 10,
				'active' => 1,
			);
		}
	}

	if ( ! empty( $_POST['man3_survey_reset_defaults'] ) ) {
		$out = man3_survey_default_questions();
	}

	update_option( 'man3_survey_questions', $out, false );
	man3_survey_maybe_install();

	wp_safe_redirect( admin_url( 'admin.php?page=man3-survey&updated=1' ) );
	exit;
}
add_action( 'admin_init', 'man3_survey_admin_save' );

/**
 * Aggregate stats (admin only caller).
 *
 * @return array{total:int,by_question:array<string,array<string,mixed>>}
 */
function man3_survey_get_aggregates() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return array(
			'total'       => 0,
			'by_question' => array(),
		);
	}
	man3_survey_maybe_install();
	global $wpdb;
	$table = $wpdb->prefix . 'man3_survey_responses';
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results( "SELECT answers FROM {$table} ORDER BY id DESC LIMIT 5000", ARRAY_A );
	$questions = man3_survey_get_questions( false );
	$stats     = array();
	foreach ( $questions as $q ) {
		$stats[ $q['id'] ] = array(
			'text'  => $q['text'],
			'count' => 0,
			'sum'   => 0,
			'avg'   => 0,
			'hist'  => array( 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0 ),
		);
	}
	$total = 0;
	if ( $rows ) {
		foreach ( $rows as $row ) {
			$ans = json_decode( (string) $row['answers'], true );
			if ( ! is_array( $ans ) ) {
				continue;
			}
			++$total;
			foreach ( $ans as $qid => $val ) {
				$qid = sanitize_key( $qid );
				$val = (int) $val;
				if ( ! isset( $stats[ $qid ] ) || $val < 1 || $val > 5 ) {
					continue;
				}
				$stats[ $qid ]['count']++;
				$stats[ $qid ]['sum'] += $val;
				$stats[ $qid ]['hist'][ $val ]++;
			}
		}
	}
	foreach ( $stats as $qid => $s ) {
		$stats[ $qid ]['avg'] = $s['count'] ? round( $s['sum'] / $s['count'], 2 ) : 0;
	}
	return array(
		'total'       => $total,
		'by_question' => $stats,
	);
}

/**
 * Admin UI.
 */
function man3_survey_admin_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Akses ditolak.', 'man3-hijau-dingin' ) );
	}
	man3_survey_maybe_install();
	$questions = man3_survey_get_questions( false );
	$agg       = man3_survey_get_aggregates();
	$scale     = man3_survey_scale_labels();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Survey Kepuasan', 'man3-hijau-dingin' ); ?></h1>
		<p>
			<?php esc_html_e( 'Form tampil di beranda (#survey-kepuasan). Kelola pertanyaan di bawah; rekap hanya agregat (tanpa identitas responden).', 'man3-hijau-dingin' ); ?>
			<a href="<?php echo esc_url( home_url( '/#survey-kepuasan' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Lihat di beranda', 'man3-hijau-dingin' ); ?></a>
		</p>
		<?php if ( ! empty( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Pengaturan disimpan.', 'man3-hijau-dingin' ); ?></p></div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Rekap jawaban', 'man3-hijau-dingin' ); ?></h2>
		<p><?php printf( /* translators: %d total */ esc_html__( 'Total tanggapan tersimpan: %d', 'man3-hijau-dingin' ), (int) $agg['total'] ); ?></p>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Pertanyaan', 'man3-hijau-dingin' ); ?></th>
					<th><?php esc_html_e( 'N', 'man3-hijau-dingin' ); ?></th>
					<th><?php esc_html_e( 'Rata-rata', 'man3-hijau-dingin' ); ?></th>
					<th><?php esc_html_e( 'Distribusi 1–5', 'man3-hijau-dingin' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $agg['by_question'] as $s ) : ?>
					<tr>
						<td><?php echo esc_html( $s['text'] ); ?></td>
						<td><?php echo esc_html( (string) $s['count'] ); ?></td>
						<td><?php echo esc_html( (string) $s['avg'] ); ?></td>
						<td>
							<?php
							$parts = array();
							foreach ( $s['hist'] as $k => $n ) {
								$parts[] = $k . ':' . $n;
							}
							echo esc_html( implode( ' · ', $parts ) );
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p class="description">
			<?php
			printf(
				/* translators: scale legend */
				esc_html__( 'Skala: %s', 'man3-hijau-dingin' ),
				esc_html( implode( ', ', array_map( static function ( $k, $v ) { return $k . '=' . $v; }, array_keys( $scale ), array_values( $scale ) ) ) )
			);
			?>
		</p>

		<hr />
		<h2><?php esc_html_e( 'Kelola pertanyaan', 'man3-hijau-dingin' ); ?></h2>
		<form method="post" action="">
			<?php wp_nonce_field( 'man3_survey_admin' ); ?>
			<input type="hidden" name="man3_survey_admin_save" value="1" />
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Urutan', 'man3-hijau-dingin' ); ?></th>
						<th><?php esc_html_e( 'Aktif', 'man3-hijau-dingin' ); ?></th>
						<th><?php esc_html_e( 'Pertanyaan', 'man3-hijau-dingin' ); ?></th>
						<th><?php esc_html_e( 'ID', 'man3-hijau-dingin' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $questions as $i => $q ) : ?>
						<tr>
							<td style="width:5rem">
								<input type="number" name="questions[<?php echo esc_attr( (string) $i ); ?>][order]" value="<?php echo esc_attr( (string) $q['order'] ); ?>" />
								<input type="hidden" name="questions[<?php echo esc_attr( (string) $i ); ?>][id]" value="<?php echo esc_attr( $q['id'] ); ?>" />
							</td>
							<td style="width:4rem">
								<input type="checkbox" name="questions[<?php echo esc_attr( (string) $i ); ?>][active]" value="1" <?php checked( $q['active'], 1 ); ?> />
							</td>
							<td>
								<textarea class="large-text" rows="2" name="questions[<?php echo esc_attr( (string) $i ); ?>][text]"><?php echo esc_textarea( $q['text'] ); ?></textarea>
							</td>
							<td><code><?php echo esc_html( $q['id'] ); ?></code></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p>
				<label for="man3_survey_new_text"><strong><?php esc_html_e( 'Tambah pertanyaan baru', 'man3-hijau-dingin' ); ?></strong></label><br />
				<textarea class="large-text" rows="2" id="man3_survey_new_text" name="man3_survey_new_text" placeholder="<?php esc_attr_e( 'Teks pertanyaan…', 'man3-hijau-dingin' ); ?>"></textarea>
				<input type="number" name="man3_survey_new_order" value="<?php echo esc_attr( (string) ( ( count( $questions ) + 1 ) * 10 ) ); ?>" />
			</p>
			<p>
				<label>
					<input type="checkbox" name="man3_survey_reset_defaults" value="1" />
					<?php esc_html_e( 'Reset ke 20 pertanyaan default (mengganti daftar di atas)', 'man3-hijau-dingin' ); ?>
				</label>
			</p>
			<?php submit_button( __( 'Simpan pertanyaan', 'man3-hijau-dingin' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Seed defaults once on admin.
 */
function man3_survey_admin_bootstrap() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( null === get_option( 'man3_survey_questions', null ) ) {
		update_option( 'man3_survey_questions', man3_survey_default_questions(), false );
	}
	man3_survey_maybe_install();
}
add_action( 'admin_init', 'man3_survey_admin_bootstrap' );
