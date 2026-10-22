<?php
/**
 * Halaman Profil Madrasah — data option + admin + menu.
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
 * Ambil data profil (option atau default).
 *
 * @return array<string,mixed>
 */
function man3_profil_get_data() {
	$stored = get_option( 'man3_profil_data_v1', null );
	if ( is_array( $stored ) && ! empty( $stored ) ) {
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
		$data['visi']                    = sanitize_textarea_field( wp_unslash( $_POST['visi'] ?? '' ) );
		$misi_raw                        = sanitize_textarea_field( wp_unslash( $_POST['misi'] ?? '' ) );
		$data['misi']                    = array_values(
			array_filter(
				array_map( 'trim', preg_split( '/\r\n|\r|\n/', $misi_raw ) )
			)
		);
		update_option( 'man3_profil_data_v1', $data, false );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Profil disimpan. Tabel siswa/jurusan/pendidik/prasarana diubah lewat reseeding atau edit opsi lanjutan (JSON) jika diperlukan.', 'man3-hijau-dingin' ) . '</p></div>';
	}
	if ( isset( $_POST['man3_profil_reseed'] ) && check_admin_referer( 'man3_profil_save_action', 'man3_profil_nonce' ) ) {
		if ( function_exists( 'man3_profil_default_data' ) ) {
			update_option( 'man3_profil_data_v1', man3_profil_default_data(), false );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Data profil direset ke seed default (identitas MAN 3).', 'man3-hijau-dingin' ) . '</p></div>';
		}
	}
	$d  = man3_profil_get_data();
	$id = $d['identitas'] ?? array();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Profil Madrasah', 'man3-hijau-dingin' ); ?></h1>
		<p><a href="<?php echo esc_url( home_url( '/profil/' ) ); ?>" target="_blank"><?php esc_html_e( 'Lihat halaman publik', 'man3-hijau-dingin' ); ?></a></p>
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
			<h2><?php esc_html_e( 'Visi & Misi', 'man3-hijau-dingin' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th><label for="visi"><?php esc_html_e( 'Visi', 'man3-hijau-dingin' ); ?></label></th>
					<td><textarea class="large-text" rows="3" id="visi" name="visi"><?php echo esc_textarea( $d['visi'] ?? '' ); ?></textarea></td>
				</tr>
				<tr>
					<th><label for="misi"><?php esc_html_e( 'Misi (satu baris per poin)', 'man3-hijau-dingin' ); ?></label></th>
					<td><textarea class="large-text" rows="6" id="misi" name="misi"><?php echo esc_textarea( implode( "\n", $d['misi'] ?? array() ) ); ?></textarea></td>
				</tr>
			</table>
			<p>
				<button type="submit" class="button button-primary" name="man3_profil_save" value="1"><?php esc_html_e( 'Simpan identitas & visi-misi', 'man3-hijau-dingin' ); ?></button>
				<button type="submit" class="button" name="man3_profil_reseed" value="1" onclick="return confirm('Reset seluruh data profil ke seed default?');"><?php esc_html_e( 'Reset ke seed default', 'man3-hijau-dingin' ); ?></button>
			</p>
		</form>
		<hr />
		<p class="description">
			<?php
			printf(
				/* translators: counts */
				esc_html__( 'Seed memuat %1$d pendidik/tendik, %2$d prasarana, data siswa/jurusan/rombel dari dokumen profil. Edit massal: gunakan Reset seed setelah memperbarui file tema, atau hubungi pengelola teknis.', 'man3-hijau-dingin' ),
				isset( $d['pendidik'] ) ? count( $d['pendidik'] ) : 0,
				isset( $d['prasarana'] ) ? count( $d['prasarana'] ) : 0
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Seed halaman + option + menu.
 */
function man3_profil_run_seed() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$force = isset( $_GET['man3_profil_reseed'] ) && '1' === $_GET['man3_profil_reseed'];
	if ( ! $force && get_option( 'man3_profil_seeded_v1' ) ) {
		return;
	}
	man3_profil_do_seed();
	update_option( 'man3_profil_seeded_v1', 1 );
}
add_action( 'admin_init', 'man3_profil_run_seed' );

function man3_profil_front_seed_trigger() {
	if ( ! isset( $_GET['man3_profil_reseed'] ) || '1' !== $_GET['man3_profil_reseed'] ) {
		return;
	}
	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	man3_profil_do_seed();
	update_option( 'man3_profil_seeded_v1', 1 );
	wp_safe_redirect( home_url( '/profil/' ) );
	exit;
}
add_action( 'template_redirect', 'man3_profil_front_seed_trigger' );

function man3_profil_do_seed() {
	if ( function_exists( 'man3_profil_default_data' ) ) {
		update_option( 'man3_profil_data_v1', man3_profil_default_data(), false );
	}
	$page = get_page_by_path( 'profil' );
	if ( ! $page ) {
		$pid = wp_insert_post(
			array(
				'post_title'   => 'Profil',
				'post_name'    => 'profil',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '<!-- Profil MAN 3 Jember -->',
			)
		);
		if ( $pid && ! is_wp_error( $pid ) ) {
			update_post_meta( $pid, '_wp_page_template', 'page-profil.php' );
		}
	} else {
		update_post_meta( $page->ID, '_wp_page_template', 'page-profil.php' );
	}
	man3_profil_ensure_menu();
}

/**
 * Menu: Beranda → Profil (sertakan Visi Misi jika sudah ada).
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
		$menus = wp_get_nav_menus();
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
			if ( 0 === strcasecmp( $title, 'Profil' ) || (int) $item->object_id === $page_id ) {
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
		// Pastikan Beranda punya children (ubah jadi custom parent jika perlu).
		if ( 'custom' !== $parent->type && empty( $parent->url ) ) {
			wp_update_nav_menu_item(
				$menu_id,
				$parent_id,
				array(
					'menu-item-title'  => 'Beranda',
					'menu-item-url'    => home_url( '/' ),
					'menu-item-status' => 'publish',
					'menu-item-type'   => 'custom',
				)
			);
		}
	}

	if ( ! $child ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => 'Profil',
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
				'menu-item-title'     => 'Profil',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent_id,
			)
		);
	}
}
