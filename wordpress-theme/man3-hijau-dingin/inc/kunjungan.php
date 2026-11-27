<?php
/**
 * Daftar kunjungan situs di footer.
 *
 * Sumber utama: Post Views Counter (wp_post_views.visits) jika tersedia.
 * Cadangan: penghitung tema harian berbasis cookie (tanpa menyimpan IP mentah).
 * Tidak mengarang angka — hanya menampilkan yang tersimpan + lanjutan hitungan.
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Zona waktu situs.
 *
 * @return DateTimeZone
 */
function man3_kunjungan_timezone() {
	if ( function_exists( 'wp_timezone' ) ) {
		return wp_timezone();
	}
	return new DateTimeZone( 'Asia/Jakarta' );
}

/**
 * Tanggal Ymd di zona situs.
 *
 * @param int $offset_days Offset dari hari ini.
 * @return string
 */
function man3_kunjungan_ymd( $offset_days = 0 ) {
	$dt = new DateTimeImmutable( 'now', man3_kunjungan_timezone() );
	if ( (int) $offset_days !== 0 ) {
		$dt = $dt->modify( ( (int) $offset_days ) . ' days' );
	}
	return $dt->format( 'Ymd' );
}

/**
 * Label tampilan singkat (hari ini / kemarin / d M).
 *
 * @param string $ymd Period YYYYMMDD.
 * @return string
 */
function man3_kunjungan_label_day( $ymd ) {
	$today = man3_kunjungan_ymd( 0 );
	$yesterday = man3_kunjungan_ymd( -1 );
	if ( $ymd === $today ) {
		return __( 'Hari ini', 'man3-hijau-dingin' );
	}
	if ( $ymd === $yesterday ) {
		return __( 'Kemarin', 'man3-hijau-dingin' );
	}
	$dt = DateTimeImmutable::createFromFormat( '!Ymd', $ymd, man3_kunjungan_timezone() );
	if ( ! $dt ) {
		return $ymd;
	}
	if ( function_exists( 'wp_date' ) ) {
		return wp_date( 'j M', $dt->getTimestamp(), man3_kunjungan_timezone() );
	}
	return $dt->format( 'j M' );
}

/**
 * Apakah tabel Post Views Counter siap dipakai untuk visits.
 *
 * @return bool
 */
function man3_kunjungan_pvc_ready() {
	static $ready = null;
	if ( null !== $ready ) {
		return $ready;
	}
	global $wpdb;
	$table = $wpdb->prefix . 'post_views';
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	if ( $exists !== $table ) {
		$ready = false;
		return $ready;
	}
	$col = $wpdb->get_results( "SHOW COLUMNS FROM `{$table}` LIKE 'visits'" );
	$ready = ! empty( $col );
	return $ready;
}

/**
 * Baca agregat visits dari PVC (langsung dari DB; aman jika API total menolak).
 *
 * @param int $days Jumlah hari terakhir (termasuk hari ini).
 * @return array{total:int,today:int,days:array<string,int>,source:string}|null
 */
function man3_kunjungan_from_pvc( $days = 7 ) {
	if ( ! man3_kunjungan_pvc_ready() ) {
		return null;
	}

	global $wpdb;
	$table   = $wpdb->prefix . 'post_views';
	$content = '';
	$has_content = $wpdb->get_results( "SHOW COLUMNS FROM `{$table}` LIKE 'content'" );
	if ( ! empty( $has_content ) ) {
		$content = ' AND `content` = 0';
	}

	// Total lifetime: baris period=total, fallback SUM harian jika total kosong.
	$total = (int) $wpdb->get_var(
		"SELECT COALESCE(SUM(`visits`),0) FROM `{$table}` WHERE `type` = 0 AND `period` = 'total'{$content}"
	);

	$days = max( 1, min( 31, (int) $days ) );
	$from = man3_kunjungan_ymd( 1 - $days );
	$to   = man3_kunjungan_ymd( 0 );

	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT `period`, SUM(`visits`) AS v
			FROM `{$table}`
			WHERE `type` = 0 AND `period` BETWEEN %s AND %s{$content}
			GROUP BY `period`",
			$from,
			$to
		),
		ARRAY_A
	);

	$by_day = array();
	for ( $i = $days - 1; $i >= 0; $i-- ) {
		$key           = man3_kunjungan_ymd( -$i );
		$by_day[ $key ] = 0;
	}
	$sum_range = 0;
	if ( is_array( $rows ) ) {
		foreach ( $rows as $row ) {
			$p = isset( $row['period'] ) ? (string) $row['period'] : '';
			$v = isset( $row['v'] ) ? (int) $row['v'] : 0;
			if ( isset( $by_day[ $p ] ) ) {
				$by_day[ $p ] = $v;
				$sum_range   += $v;
			}
		}
	}

	if ( $total <= 0 && $sum_range > 0 ) {
		// Belum ada baris total — pakai jumlah harian tersimpan (bukan angka baru).
		$all_daily = (int) $wpdb->get_var(
			"SELECT COALESCE(SUM(`visits`),0) FROM `{$table}`
			WHERE `type` = 0 AND `period` REGEXP '^[0-9]{8}$'{$content}"
		);
		$total = $all_daily > 0 ? $all_daily : $sum_range;
	}

	$today = isset( $by_day[ $to ] ) ? (int) $by_day[ $to ] : 0;

	return array(
		'total'  => max( 0, $total ),
		'today'  => max( 0, $today ),
		'days'   => $by_day,
		'source' => 'post-views-counter',
	);
}

/**
 * Opsi cadangan tema.
 *
 * @return array{days:array<string,int>,total:int,seeded_from:string}
 */
function man3_kunjungan_theme_store() {
	$store = get_option( 'man3_kunjungan_stats', null );
	if ( ! is_array( $store ) ) {
		$store = array(
			'days'        => array(),
			'total'       => 0,
			'seeded_from' => '',
		);
	}
	if ( ! isset( $store['days'] ) || ! is_array( $store['days'] ) ) {
		$store['days'] = array();
	}
	$store['total']       = isset( $store['total'] ) ? max( 0, (int) $store['total'] ) : 0;
	$store['seeded_from'] = isset( $store['seeded_from'] ) ? (string) $store['seeded_from'] : '';
	return $store;
}

/**
 * Seed cadangan dari agregat PVC views (tayangan) hanya sekali, jika visits kosong.
 * Angka diambil dari DB — tidak dikarang.
 *
 * @return void
 */
function man3_kunjungan_maybe_seed() {
	if ( get_option( 'man3_kunjungan_seeded', '' ) ) {
		return;
	}

	$store = man3_kunjungan_theme_store();
	$seed  = 0;
	$from  = '';

	$pvc = man3_kunjungan_from_pvc( 7 );
	if ( is_array( $pvc ) && $pvc['total'] > 0 ) {
		$seed = (int) $pvc['total'];
		$from = 'pvc-visits';
	} elseif ( man3_kunjungan_pvc_ready() ) {
		global $wpdb;
		$table = $wpdb->prefix . 'post_views';
		// Fallback historis: total tayangan konten (bukan kunjungan situs palsu).
		$views = (int) $wpdb->get_var(
			"SELECT COALESCE(SUM(`count`),0) FROM `{$table}` WHERE `type` = 0 AND `period` = 'total'"
		);
		if ( $views > 0 ) {
			// Simpan sebagai baseline tampilan terpisah; total kunjungan tetap dari hitungan lanjut.
			update_option( 'man3_kunjungan_views_archive', $views, false );
			$from = 'pvc-views-archive-noted';
		}
	}

	if ( $seed > 0 ) {
		$store['total']       = $seed;
		$store['seeded_from'] = $from;
	} elseif ( $from ) {
		$store['seeded_from'] = $from;
	}

	update_option( 'man3_kunjungan_stats', $store, false );
	update_option( 'man3_kunjungan_seeded', gmdate( 'c' ), false );
}

/**
 * Hash pengunjung harian (tanpa IP mentah tersimpan lama).
 *
 * @return string
 */
function man3_kunjungan_visitor_token() {
	$ip = '';
	if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = (string) wp_unslash( $_SERVER['REMOTE_ADDR'] );
	}
	$ua = ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? (string) wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '';
	$day = man3_kunjungan_ymd( 0 );
	$salt = wp_salt( 'nonce' );
	return hash_hmac( 'sha256', $day . '|' . $ip . '|' . substr( $ua, 0, 80 ), $salt );
}

/**
 * Catat kunjungan unik harian (cadangan tema).
 *
 * @return void
 */
function man3_kunjungan_track() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	if ( is_feed() || is_robots() || is_trackback() ) {
		return;
	}
	// Jangan dobel jika PVC sudah menghitung visits situs.
	if ( man3_kunjungan_pvc_ready() && function_exists( 'Post_Views_Counter' ) ) {
		return;
	}
	if ( ! empty( $_COOKIE['man3_kv'] ) && (string) wp_unslash( $_COOKIE['man3_kv'] ) === man3_kunjungan_ymd( 0 ) ) {
		return;
	}

	man3_kunjungan_maybe_seed();

	$token   = man3_kunjungan_visitor_token();
	$tkey    = 'man3_kv_' . substr( $token, 0, 32 );
	if ( get_transient( $tkey ) ) {
		return;
	}

	$today = man3_kunjungan_ymd( 0 );
	$store = man3_kunjungan_theme_store();
	if ( ! isset( $store['days'][ $today ] ) ) {
		$store['days'][ $today ] = 0;
	}
	$store['days'][ $today ]++;
	$store['total']++;

	// Simpan max 45 hari.
	ksort( $store['days'] );
	if ( count( $store['days'] ) > 45 ) {
		$store['days'] = array_slice( $store['days'], -45, null, true );
	}

	update_option( 'man3_kunjungan_stats', $store, false );
	set_transient( $tkey, 1, DAY_IN_SECONDS );

	if ( ! headers_sent() ) {
		$expire = time() + DAY_IN_SECONDS;
		setcookie( 'man3_kv', $today, array(
			'expires'  => $expire,
			'path'     => COOKIEPATH ? COOKIEPATH : '/',
			'domain'   => COOKIE_DOMAIN,
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		) );
	}
}
add_action( 'template_redirect', 'man3_kunjungan_track', 20 );

/**
 * Statistik siap tampil.
 *
 * @param int $days Jumlah hari.
 * @return array{total:int,today:int,yesterday:int,days:array<string,int>,source:string,views_archive:int}
 */
function man3_kunjungan_get_stats( $days = 7 ) {
	$days = max( 1, min( 31, (int) $days ) );
	man3_kunjungan_maybe_seed();
	$pvc  = man3_kunjungan_from_pvc( $days );

	// PVC siap → tampilkan angka PVC apa adanya (termasuk 0), lanjut dihitung plugin.
	if ( is_array( $pvc ) ) {
		$yesterday_key = man3_kunjungan_ymd( -1 );
		return array(
			'total'         => (int) $pvc['total'],
			'today'         => (int) $pvc['today'],
			'yesterday'     => isset( $pvc['days'][ $yesterday_key ] ) ? (int) $pvc['days'][ $yesterday_key ] : 0,
			'days'          => $pvc['days'],
			'source'        => $pvc['source'],
			'views_archive' => (int) get_option( 'man3_kunjungan_views_archive', 0 ),
		);
	}

	$store = man3_kunjungan_theme_store();
	$by_day = array();
	for ( $i = $days - 1; $i >= 0; $i-- ) {
		$key            = man3_kunjungan_ymd( -$i );
		$by_day[ $key ] = isset( $store['days'][ $key ] ) ? (int) $store['days'][ $key ] : 0;
	}
	$today_key     = man3_kunjungan_ymd( 0 );
	$yesterday_key = man3_kunjungan_ymd( -1 );

	return array(
		'total'         => (int) $store['total'],
		'today'         => isset( $by_day[ $today_key ] ) ? (int) $by_day[ $today_key ] : 0,
		'yesterday'     => isset( $by_day[ $yesterday_key ] ) ? (int) $by_day[ $yesterday_key ] : 0,
		'days'          => $by_day,
		'source'        => $store['seeded_from'] ? 'theme+' . $store['seeded_from'] : 'theme',
		'views_archive' => (int) get_option( 'man3_kunjungan_views_archive', 0 ),
	);
}

/**
 * Endpoint admin-only untuk verifikasi sumber data (deploy/debug).
 *
 * @return void
 */
function man3_kunjungan_ajax_probe() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}
	check_ajax_referer( 'man3_kunjungan_probe', 'nonce' );
	wp_send_json_success(
		array(
			'pvc_ready' => man3_kunjungan_pvc_ready(),
			'stats'     => man3_kunjungan_get_stats( 7 ),
			'seeded_at' => get_option( 'man3_kunjungan_seeded', '' ),
		)
	);
}
add_action( 'wp_ajax_man3_kunjungan_probe', 'man3_kunjungan_ajax_probe' );

/**
 * Format angka lokal.
 *
 * @param int $n Angka.
 * @return string
 */
function man3_kunjungan_format( $n ) {
	$n = (int) $n;
	if ( function_exists( 'number_format_i18n' ) ) {
		return number_format_i18n( $n );
	}
	return number_format( $n, 0, ',', '.' );
}

/**
 * Markup footer kunjungan.
 *
 * @return void
 */
function man3_kunjungan_render_footer() {
	$stats = man3_kunjungan_get_stats( 7 );
	$max   = 1;
	foreach ( $stats['days'] as $v ) {
		$max = max( $max, (int) $v );
	}
	$week_sum = array_sum( $stats['days'] );
	?>
	<section class="man3-kunjungan" aria-label="<?php esc_attr_e( 'Statistik kunjungan situs', 'man3-hijau-dingin' ); ?>">
		<div class="container man3-kunjungan__inner">
			<div class="man3-kunjungan__head">
				<span class="man3-kunjungan__title"><?php esc_html_e( 'Kunjungan situs', 'man3-hijau-dingin' ); ?></span>
				<span class="man3-kunjungan__hint"><?php esc_html_e( 'Diperbarui harian · tanpa data pribadi', 'man3-hijau-dingin' ); ?></span>
			</div>
			<ul class="man3-kunjungan__metrics">
				<li>
					<span class="man3-kunjungan__label"><?php esc_html_e( 'Hari ini', 'man3-hijau-dingin' ); ?></span>
					<strong class="man3-kunjungan__value"><?php echo esc_html( man3_kunjungan_format( $stats['today'] ) ); ?></strong>
				</li>
				<li>
					<span class="man3-kunjungan__label"><?php esc_html_e( 'Kemarin', 'man3-hijau-dingin' ); ?></span>
					<strong class="man3-kunjungan__value"><?php echo esc_html( man3_kunjungan_format( $stats['yesterday'] ) ); ?></strong>
				</li>
				<li>
					<span class="man3-kunjungan__label"><?php esc_html_e( '7 hari', 'man3-hijau-dingin' ); ?></span>
					<strong class="man3-kunjungan__value"><?php echo esc_html( man3_kunjungan_format( $week_sum ) ); ?></strong>
				</li>
				<li>
					<span class="man3-kunjungan__label"><?php esc_html_e( 'Total', 'man3-hijau-dingin' ); ?></span>
					<strong class="man3-kunjungan__value"><?php echo esc_html( man3_kunjungan_format( $stats['total'] ) ); ?></strong>
				</li>
			</ul>
			<ol class="man3-kunjungan__bars" aria-hidden="true">
				<?php foreach ( $stats['days'] as $ymd => $count ) : ?>
					<?php
					$pct = (int) round( ( (int) $count / $max ) * 100 );
					if ( $pct > 0 && $pct < 8 ) {
						$pct = 8;
					}
					?>
					<li title="<?php echo esc_attr( man3_kunjungan_label_day( $ymd ) . ': ' . man3_kunjungan_format( $count ) ); ?>">
						<span style="--bar: <?php echo esc_attr( (string) $pct ); ?>%"></span>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>
	<?php
}
