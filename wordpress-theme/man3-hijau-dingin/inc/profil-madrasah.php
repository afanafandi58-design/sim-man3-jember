<?php
/**
 * Halaman Profil Madrasah — agregat + grafik (privasi), admin, menu.
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data_file = __DIR__ . '/profil-data.php';
if ( file_exists( $data_file ) ) {
	require_once $data_file;
}

/**
 * Ambil data profil (option v2 atau default 2026).
 *
 * @return array<string,mixed>
 */
function man3_profil_get_data() {
	$stored = get_option( 'man3_profil_data_v2', null );
	if ( is_array( $stored ) && ! empty( $stored['version'] ) ) {
		$defaults = function_exists( 'man3_profil_default_data' ) ? man3_profil_default_data() : array();
		return array_replace_recursive( $defaults, $stored );
	}
	return function_exists( 'man3_profil_default_data' ) ? man3_profil_default_data() : array();
}

/**
 * Admin menu.
 */
function man3_profil_admin_menu() {
	add_menu_page(
		__( 'Profil Madrasah', 'man3-hijau-dingin' ),
		__( 'Profil Madrasah', 'man3-hijau-dingin' ),
		'manage_options',
		'man3-profil',
		'man3_profil_admin_page',
		'dashicons-id-alt',
		24
	);
}
add_action( 'admin_menu', 'man3_profil_admin_menu' );

/**
 * Admin page — identitas, narasi, angka agregat (bukan nama/NIP).
 */
function man3_profil_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_POST['man3_profil_save'] ) && check_admin_referer( 'man3_profil_save_action', 'man3_profil_nonce' ) ) {
		$data = man3_profil_get_data();
		$data['identitas']['nama']       = sanitize_text_field( wp_unslash( $_POST['nama'] ?? '' ) );
		$data['identitas']['nsm']        = sanitize_text_field( wp_unslash( $_POST['nsm'] ?? '' ) );
		$data['identitas']['npsn']       = sanitize_text_field( wp_unslash( $_POST['npsn'] ?? '' ) );
		$data['identitas']['akreditasi'] = sanitize_text_field( wp_unslash( $_POST['akreditasi'] ?? '' ) );
		$data['identitas']['alamat']     = sanitize_text_field( wp_unslash( $_POST['alamat'] ?? '' ) );
		$data['identitas']['telepon']    = sanitize_text_field( wp_unslash( $_POST['telepon'] ?? '' ) );
		$data['identitas']['email']      = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$data['identitas']['website']    = esc_url_raw( wp_unslash( $_POST['website'] ?? '' ) );
		$data['sejarah']                 = sanitize_textarea_field( wp_unslash( $_POST['sejarah'] ?? '' ) );
		$data['visi']                    = sanitize_textarea_field( wp_unslash( $_POST['visi'] ?? '' ) );
		$misi_raw                        = sanitize_textarea_field( wp_unslash( $_POST['misi'] ?? '' ) );
		$data['misi']                    = array_values(
			array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $misi_raw ) ) )
		);
		$data['siswa_chart']['total']         = absint( $_POST['siswa_total'] ?? 0 );
		$data['siswa_chart']['rombel_total']  = absint( $_POST['rombel_total'] ?? 0 );
		$data['pegawai_chart']['pendidik']    = absint( $_POST['pegawai_pendidik'] ?? 0 );
		$data['pegawai_chart']['tendik']      = absint( $_POST['pegawai_tendik'] ?? 0 );
		$data['pegawai_chart']['total']       = (int) $data['pegawai_chart']['pendidik'] + (int) $data['pegawai_chart']['tendik'];
		$data['pegawai_chart']['jenis']       = array(
			array( 'label' => 'Pendidik (Guru)', 'value' => (int) $data['pegawai_chart']['pendidik'] ),
			array( 'label' => 'Tenaga Kependidikan', 'value' => (int) $data['pegawai_chart']['tendik'] ),
		);
		$pns     = absint( $_POST['status_pns'] ?? 0 );
		$non_asn = absint( $_POST['status_non_asn'] ?? 0 );
		$data['pegawai_chart']['status'] = array(
			array( 'label' => 'PNS', 'value' => $pns ),
			array( 'label' => 'Non-ASN (PPPK/GTT/PTT)', 'value' => $non_asn ),
		);
		update_option( 'man3_profil_data_v2', $data, false );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Profil disimpan (agregat publik, tanpa nama/NIP).', 'man3-hijau-dingin' ) . '</p></div>';
	}
	if ( isset( $_POST['man3_profil_reseed'] ) && check_admin_referer( 'man3_profil_save_action', 'man3_profil_nonce' ) ) {
		man3_profil_do_seed();
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Data profil direset ke seed Profil 2026 (agregat).', 'man3-hijau-dingin' ) . '</p></div>';
	}
	$d       = man3_profil_get_data();
	$id      = $d['identitas'] ?? array();
	$siswa   = $d['siswa_chart'] ?? array();
	$pegawai = $d['pegawai_chart'] ?? array();
	$status  = $pegawai['status'] ?? array();
	$pns_v   = isset( $status[0]['value'] ) ? (int) $status[0]['value'] : 0;
	$non_v   = isset( $status[1]['value'] ) ? (int) $status[1]['value'] : 0;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Profil Madrasah', 'man3-hijau-dingin' ); ?></h1>
		<p><a href="<?php echo esc_url( home_url( '/profil/' ) ); ?>" target="_blank" rel="noreferrer"><?php esc_html_e( 'Lihat halaman publik', 'man3-hijau-dingin' ); ?></a></p>
		<p class="description"><?php esc_html_e( 'Halaman publik hanya menampilkan narasi umum dan grafik agregat. Jangan memasukkan nama, NIP, atau data personal pegawai di sini.', 'man3-hijau-dingin' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'man3_profil_save_action', 'man3_profil_nonce' ); ?>
			<h2><?php esc_html_e( 'Identitas', 'man3-hijau-dingin' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php
				$fields = array(
					'nama'       => 'Nama Madrasah',
					'nsm'        => 'NSM',
					'npsn'       => 'NPSN',
					'akreditasi' => 'Akreditasi',
					'alamat'     => 'Alamat',
					'telepon'    => 'Telepon',
					'email'      => 'Email',
					'website'    => 'Website',
				);
				foreach ( $fields as $key => $label ) :
					?>
					<tr>
						<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td><input class="regular-text" type="text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $id[ $key ] ?? '' ); ?>" /></td>
					</tr>
				<?php endforeach; ?>
			</table>
			<h2><?php esc_html_e( 'Narasi', 'man3-hijau-dingin' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th><label for="sejarah"><?php esc_html_e( 'Sejarah singkat', 'man3-hijau-dingin' ); ?></label></th>
					<td><textarea class="large-text" rows="5" id="sejarah" name="sejarah"><?php echo esc_textarea( $d['sejarah'] ?? '' ); ?></textarea></td>
				</tr>
				<tr>
					<th><label for="visi"><?php esc_html_e( 'Visi', 'man3-hijau-dingin' ); ?></label></th>
					<td><textarea class="large-text" rows="3" id="visi" name="visi"><?php echo esc_textarea( $d['visi'] ?? '' ); ?></textarea></td>
				</tr>
				<tr>
					<th><label for="misi"><?php esc_html_e( 'Misi (satu baris per poin)', 'man3-hijau-dingin' ); ?></label></th>
					<td><textarea class="large-text" rows="6" id="misi" name="misi"><?php echo esc_textarea( implode( "\n", $d['misi'] ?? array() ) ); ?></textarea></td>
				</tr>
			</table>
			<h2><?php esc_html_e( 'Angka agregat (grafik publik)', 'man3-hijau-dingin' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th><label for="siswa_total"><?php esc_html_e( 'Jumlah siswa', 'man3-hijau-dingin' ); ?></label></th>
					<td><input type="number" min="0" id="siswa_total" name="siswa_total" value="<?php echo esc_attr( (string) ( $siswa['total'] ?? 0 ) ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="rombel_total"><?php esc_html_e( 'Jumlah rombel', 'man3-hijau-dingin' ); ?></label></th>
					<td><input type="number" min="0" id="rombel_total" name="rombel_total" value="<?php echo esc_attr( (string) ( $siswa['rombel_total'] ?? 0 ) ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="pegawai_pendidik"><?php esc_html_e( 'Jumlah pendidik', 'man3-hijau-dingin' ); ?></label></th>
					<td><input type="number" min="0" id="pegawai_pendidik" name="pegawai_pendidik" value="<?php echo esc_attr( (string) ( $pegawai['pendidik'] ?? 0 ) ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="pegawai_tendik"><?php esc_html_e( 'Jumlah tendik', 'man3-hijau-dingin' ); ?></label></th>
					<td><input type="number" min="0" id="pegawai_tendik" name="pegawai_tendik" value="<?php echo esc_attr( (string) ( $pegawai['tendik'] ?? 0 ) ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="status_pns"><?php esc_html_e( 'Total PNS', 'man3-hijau-dingin' ); ?></label></th>
					<td><input type="number" min="0" id="status_pns" name="status_pns" value="<?php echo esc_attr( (string) $pns_v ); ?>" /></td>
				</tr>
				<tr>
					<th><label for="status_non_asn"><?php esc_html_e( 'Total Non-ASN', 'man3-hijau-dingin' ); ?></label></th>
					<td><input type="number" min="0" id="status_non_asn" name="status_non_asn" value="<?php echo esc_attr( (string) $non_v ); ?>" /></td>
				</tr>
			</table>
			<p>
				<button type="submit" class="button button-primary" name="man3_profil_save" value="1"><?php esc_html_e( 'Simpan', 'man3-hijau-dingin' ); ?></button>
				<button type="submit" class="button" name="man3_profil_reseed" value="1" onclick="return confirm('Reset ke seed Profil 2026 (agregat)?');"><?php esc_html_e( 'Reset ke seed 2026', 'man3-hijau-dingin' ); ?></button>
			</p>
		</form>
	</div>
	<?php
}

/**
 * Seed once / force (?man3_profil_reseed=1).
 */
function man3_profil_run_seed() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$force = isset( $_GET['man3_profil_reseed'] ) && '1' === $_GET['man3_profil_reseed'];
	if ( ! $force && get_option( 'man3_profil_seeded_v2' ) ) {
		return;
	}
	man3_profil_do_seed();
	update_option( 'man3_profil_seeded_v2', 1 );
}
add_action( 'admin_init', 'man3_profil_run_seed' );

/**
 * Front reseed trigger.
 */
function man3_profil_front_seed_trigger() {
	if ( ! isset( $_GET['man3_profil_reseed'] ) || '1' !== $_GET['man3_profil_reseed'] ) {
		return;
	}
	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	man3_profil_do_seed();
	update_option( 'man3_profil_seeded_v2', 1 );
	wp_safe_redirect( home_url( '/profil/' ) );
	exit;
}
add_action( 'template_redirect', 'man3_profil_front_seed_trigger' );

/**
 * Seed option + publish page + menu.
 */
function man3_profil_do_seed() {
	if ( function_exists( 'man3_profil_default_data' ) ) {
		update_option( 'man3_profil_data_v2', man3_profil_default_data(), false );
		delete_option( 'man3_profil_data_v1' );
	}
	$page = get_page_by_path( 'profil' );
	if ( ! $page ) {
		$pid = wp_insert_post(
			array(
				'post_title'   => 'Profil Madrasah',
				'post_name'    => 'profil',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- Profil MAN 3 Jember 2026 -->',
			)
		);
		if ( $pid && ! is_wp_error( $pid ) ) {
			update_post_meta( $pid, '_wp_page_template', 'page-profil.php' );
		}
	} else {
		wp_update_post(
			array(
				'ID'          => $page->ID,
				'post_title'  => 'Profil Madrasah',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $page->ID, '_wp_page_template', 'page-profil.php' );
	}
	man3_profil_ensure_menu();
}

/**
 * Menu: Beranda → Profil Madrasah.
 */
function man3_profil_ensure_menu() {
	$page = get_page_by_path( 'profil' );
	if ( ! $page ) {
		return;
	}
	$page_id   = (int) $page->ID;
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$menu_id   = ! empty( $locations['primary'] ) ? (int) $locations['primary'] : 0;
	if ( ! $menu_id ) {
		$menus   = wp_get_nav_menus();
		$menu_id = $menus ? (int) $menus[0]->term_id : wp_create_nav_menu( 'Menu Utama MAN 3' );
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
			if ( 0 === strcasecmp( $title, 'Beranda' ) && (int) $item->menu_item_parent === 0 ) {
				$parent = $item;
			}
			if (
				0 === strcasecmp( $title, 'Profil' )
				|| 0 === strcasecmp( $title, 'Profil Madrasah' )
				|| (int) $item->object_id === $page_id
			) {
				$child = $item;
			}
		}
	}

	if ( ! $parent ) {
		$parent_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'  => 'Beranda',
				'menu-item-url'    => home_url( '/' ),
				'menu-item-status' => 'publish',
				'menu-item-type'   => 'custom',
			)
		);
	} else {
		$parent_id = (int) $parent->ID;
	}

	$args = array(
		'menu-item-title'     => 'Profil Madrasah',
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $page_id,
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
		'menu-item-parent-id' => $parent_id,
	);
	if ( ! $child ) {
		wp_update_nav_menu_item( $menu_id, 0, $args );
	} else {
		wp_update_nav_menu_item( $menu_id, (int) $child->ID, $args );
	}
}

/**
 * Chart.js + init hanya di halaman profil.
 */
function man3_profil_enqueue_charts() {
	if ( ! is_page_template( 'page-profil.php' ) && ! is_page( 'profil' ) ) {
		return;
	}
	wp_enqueue_script(
		'chartjs',
		'https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js',
		array(),
		'4.4.6',
		true
	);
	wp_enqueue_script(
		'man3-profil-charts',
		get_template_directory_uri() . '/assets/js/profil-charts.js',
		array( 'chartjs' ),
		MAN3_HIJAU_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'man3_profil_enqueue_charts' );
