<?php
/**
 * Prestasi Siswa — CPT prestasi (data lama) + pita slide beranda.
 *
 * @package Man3_Hijau_Dingin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seed/mapping dari CPT prestasi arsip DB (bukan karangan nama).
 *
 * @return array<int,array<string,mixed>>
 */
function man3_prestasi_seed_rows() {
	return array(
		array(
			'legacy_id'  => 7199,
			'key'        => 'm-fardan-ardiansyah-dan-risty-nuraily-mendapatkan-juara-2-dalam-event-lomba-video-matematika-yang-diselenggarakan-oleh-itb-bandung',
			'nama'       => 'M Fardan Ardiansyah dan Risty Nuraily',
			'juara'      => 'Juara 2 Lomba Video Matematika — ITB Bandung',
			'keterangan' => 'M Fardan Ardiansyah dan Risty Nuraily mendapatkan juara 2 dalam event lomba Video Matematika yang diselenggarakan oleh ITB Bandung',
			'order'      => 10,
			'image_file' => 'prestasi4.jpeg',
		),
		array(
			'legacy_id'  => 7198,
			'key'        => 'tim-al-banjari-man-3-jember-mendapatkan-juara-1-dalam-festival-al-banjari-tingkat-pelajar-se-lumajang-jember-dalam-rangka-milad-ponpes-al-maktub-ke-70',
			'nama'       => 'Tim Al Banjari MAN 3 Jember',
			'juara'      => 'Juara 1 Festival Al Banjari tingkat pelajar se Lumajang–Jember (Milad Ponpes Al Maktub ke-70)',
			'keterangan' => '',
			'order'      => 20,
			'image_file' => 'prestasi3.jpeg',
		),
		array(
			'legacy_id'  => 7246,
			'key'        => 'pramuka-man-3-jember-pangkalan-terajin-laga-aksi-iii',
			'nama'       => 'Pramuka MAN 3 Jember',
			'juara'      => 'Pangkalan Terajin LAGA AKSI III Se Jawa di MAN 2 Kota Malang',
			'keterangan' => 'Selamat dan Sukses kepada Pramuka MAN 3 Jember sebagai Pangkalan Terajin dalam kegiatan LAGA AKSI III Se Jawa di MAN 2 Kota Malang.',
			'order'      => 30,
			'image_file' => 'lomba-pramuka-1.jpeg',
		),
		array(
			'legacy_id'  => 7193,
			'key'        => 'penghargaan-kolaborasi-cyber-security-bmkg',
			'nama'       => 'MAN 3 Jember',
			'juara'      => 'Penghargaan kolaborasi Cyber Security dari BMKG',
			'keterangan' => 'Penghargaan kolaborasi dalam bidang Cyber Security dari Badan Meteorologi Klimatologi dan Geofisika (BMKG).',
			'order'      => 40,
			'image_file' => 'prestasi1.jpeg',
		),
	);
}

/**
 * Register CPT — slug `prestasi` agar post lama ikut terbaca.
 */
function man3_prestasi_register_cpt() {
	register_post_type(
		'prestasi',
		array(
			'labels'             => array(
				'name'               => __( 'Prestasi Siswa', 'man3-hijau-dingin' ),
				'singular_name'      => __( 'Prestasi', 'man3-hijau-dingin' ),
				'add_new'            => __( 'Tambah Prestasi', 'man3-hijau-dingin' ),
				'add_new_item'       => __( 'Tambah Prestasi Siswa', 'man3-hijau-dingin' ),
				'edit_item'          => __( 'Edit Prestasi', 'man3-hijau-dingin' ),
				'menu_name'          => __( 'Prestasi Siswa', 'man3-hijau-dingin' ),
				'all_items'          => __( 'Semua Prestasi', 'man3-hijau-dingin' ),
				'featured_image'     => __( 'Foto (opsional)', 'man3-hijau-dingin' ),
				'set_featured_image' => __( 'Set foto', 'man3-hijau-dingin' ),
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-awards',
			'menu_position'      => 22,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => true,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		)
	);
}
add_action( 'init', 'man3_prestasi_register_cpt' );

/**
 * Meta boxes.
 */
function man3_prestasi_meta_box() {
	add_meta_box(
		'man3_prestasi_meta',
		__( 'Detail Prestasi', 'man3-hijau-dingin' ),
		'man3_prestasi_meta_box_render',
		'prestasi',
		'normal',
		'high'
	);
	add_meta_box(
		'man3_prestasi_active',
		__( 'Pengaturan', 'man3-hijau-dingin' ),
		'man3_prestasi_active_box_render',
		'prestasi',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'man3_prestasi_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function man3_prestasi_meta_box_render( $post ) {
	wp_nonce_field( 'man3_prestasi_save', 'man3_prestasi_nonce' );
	$juara = get_post_meta( $post->ID, '_man3_prestasi_juara', true );
	?>
	<p>
		<label for="man3_prestasi_juara"><strong><?php esc_html_e( 'Prestasi / juara', 'man3-hijau-dingin' ); ?></strong></label>
		<input type="text" class="widefat" id="man3_prestasi_juara" name="man3_prestasi_juara" value="<?php echo esc_attr( $juara ); ?>" placeholder="<?php esc_attr_e( 'mis. Juara 1 Lomba … tingkat …', 'man3-hijau-dingin' ); ?>" />
	</p>
	<p class="description">
		<?php esc_html_e( 'Judul = nama siswa / tim. Keterangan singkat opsional: isi di editor. Foto opsional: Gambar Unggulan. Urutan: Atribut → Order.', 'man3-hijau-dingin' ); ?>
	</p>
	<?php
}

/**
 * @param WP_Post $post Post.
 */
function man3_prestasi_active_box_render( $post ) {
	$active = get_post_meta( $post->ID, '_man3_prestasi_active', true );
	if ( '' === $active ) {
		$active = '1';
	}
	?>
	<p>
		<label>
			<input type="checkbox" name="man3_prestasi_active" value="1" <?php checked( $active, '1' ); ?> />
			<?php esc_html_e( 'Aktif di beranda', 'man3-hijau-dingin' ); ?>
		</label>
	</p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function man3_prestasi_meta_save( $post_id ) {
	if ( ! isset( $_POST['man3_prestasi_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['man3_prestasi_nonce'] ) ), 'man3_prestasi_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['man3_prestasi_juara'] ) ) {
		update_post_meta( $post_id, '_man3_prestasi_juara', sanitize_text_field( wp_unslash( $_POST['man3_prestasi_juara'] ) ) );
	}
	update_post_meta( $post_id, '_man3_prestasi_active', isset( $_POST['man3_prestasi_active'] ) ? '1' : '0' );
}
add_action( 'save_post_prestasi', 'man3_prestasi_meta_save' );

/**
 * Kolom admin.
 *
 * @param array $cols Columns.
 * @return array
 */
function man3_prestasi_columns( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		if ( 'title' === $k ) {
			$new[ $k ] = __( 'Nama', 'man3-hijau-dingin' );
			$new['man3_prestasi_juara']  = __( 'Prestasi', 'man3-hijau-dingin' );
			$new['man3_prestasi_thumb']  = __( 'Foto', 'man3-hijau-dingin' );
			$new['man3_prestasi_active'] = __( 'Aktif', 'man3-hijau-dingin' );
			$new['man3_prestasi_order']  = __( 'Urutan', 'man3-hijau-dingin' );
		} else {
			$new[ $k ] = $v;
		}
	}
	return $new;
}
add_filter( 'manage_prestasi_posts_columns', 'man3_prestasi_columns' );

/**
 * @param string $col Column.
 * @param int    $post_id Post ID.
 */
function man3_prestasi_column_content( $col, $post_id ) {
	if ( 'man3_prestasi_juara' === $col ) {
		$j = get_post_meta( $post_id, '_man3_prestasi_juara', true );
		echo $j ? esc_html( $j ) : '&mdash;';
	}
	if ( 'man3_prestasi_thumb' === $col ) {
		$thumb = get_the_post_thumbnail( $post_id, array( 48, 48 ) );
		echo $thumb ? $thumb : '&mdash;'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	if ( 'man3_prestasi_active' === $col ) {
		$active = get_post_meta( $post_id, '_man3_prestasi_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		echo '1' === $active ? esc_html__( 'Ya', 'man3-hijau-dingin' ) : esc_html__( 'Tidak', 'man3-hijau-dingin' );
	}
	if ( 'man3_prestasi_order' === $col ) {
		echo esc_html( (string) get_post_field( 'menu_order', $post_id ) );
	}
}
add_action( 'manage_prestasi_posts_custom_column', 'man3_prestasi_column_content', 10, 2 );

/**
 * @param string $filename File basename.
 * @return int
 */
function man3_prestasi_find_attachment( $filename ) {
	if ( ! $filename ) {
		return 0;
	}
	global $wpdb;
	$like = '%' . $wpdb->esc_like( $filename );
	$id   = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1",
			$like
		)
	);
	return $id > 0 ? $id : 0;
}

/**
 * Seed / migrate data lama.
 */
function man3_prestasi_do_seed() {
	foreach ( man3_prestasi_seed_rows() as $row ) {
		$existing = null;
		if ( ! empty( $row['legacy_id'] ) ) {
			$legacy = get_post( (int) $row['legacy_id'] );
			if ( $legacy && 'prestasi' === $legacy->post_type ) {
				$existing = $legacy;
			}
		}
		if ( ! $existing ) {
			$found = get_posts(
				array(
					'post_type'      => 'prestasi',
					'name'           => $row['key'],
					'posts_per_page' => 1,
					'post_status'    => 'any',
				)
			);
			if ( $found ) {
				$existing = $found[0];
			}
		}

		$postarr = array(
			'post_title'   => $row['nama'],
			'post_name'    => $row['key'],
			'post_status'  => 'publish',
			'post_type'    => 'prestasi',
			'post_content' => $row['keterangan'],
			'menu_order'   => (int) $row['order'],
		);

		if ( $existing ) {
			$postarr['ID'] = (int) $existing->ID;
			/* Judul lama sering berupa kalimat prestasi penuh — ganti ke nama terurai dari seed. */
			$pid = wp_update_post( $postarr, true );
		} else {
			$pid = wp_insert_post( $postarr, true );
		}
		if ( ! $pid || is_wp_error( $pid ) ) {
			continue;
		}

		update_post_meta( (int) $pid, '_man3_prestasi_juara', $row['juara'] );
		$active = get_post_meta( (int) $pid, '_man3_prestasi_active', true );
		if ( '' === $active ) {
			update_post_meta( (int) $pid, '_man3_prestasi_active', '1' );
		}

		if ( empty( get_post_thumbnail_id( (int) $pid ) ) && ! empty( $row['image_file'] ) ) {
			$att = man3_prestasi_find_attachment( $row['image_file'] );
			if ( $att ) {
				set_post_thumbnail( (int) $pid, $att );
			}
		}
	}
}

/**
 * Admin bootstrap.
 */
function man3_prestasi_admin_bootstrap() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! get_option( 'man3_prestasi_seeded_v1' ) ) {
		man3_prestasi_do_seed();
		update_option( 'man3_prestasi_seeded_v1', 1, false );
	}
}
add_action( 'admin_init', 'man3_prestasi_admin_bootstrap' );

/**
 * Front reseed.
 */
function man3_prestasi_front_bootstrap() {
	if ( ! isset( $_GET['man3_prestasi_reseed'] ) || '1' !== $_GET['man3_prestasi_reseed'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	delete_option( 'man3_prestasi_seeded_v1' );
	man3_prestasi_do_seed();
	update_option( 'man3_prestasi_seeded_v1', 1, false );
	wp_safe_redirect( home_url( '/#prestasi-siswa' ) );
	exit;
}
add_action( 'template_redirect', 'man3_prestasi_front_bootstrap' );

/**
 * Item aktif untuk beranda.
 *
 * @return array<int,array<string,mixed>>
 */
function man3_prestasi_get_items() {
	$posts = get_posts(
		array(
			'post_type'              => 'prestasi',
			'post_status'            => 'publish',
			'posts_per_page'         => 40,
			'orderby'                => 'menu_order title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		)
	);
	$out = array();
	foreach ( $posts as $p ) {
		$active = get_post_meta( $p->ID, '_man3_prestasi_active', true );
		if ( '' === $active ) {
			$active = '1';
		}
		if ( '1' !== $active ) {
			continue;
		}
		$nama  = get_the_title( $p );
		$juara = get_post_meta( $p->ID, '_man3_prestasi_juara', true );
		if ( ! $juara ) {
			/* Fallback post lama: judul = kalimat prestasi. */
			$juara = $nama;
			$nama  = __( 'Prestasi', 'man3-hijau-dingin' );
		}
		$raw   = $p->post_excerpt ? $p->post_excerpt : wp_strip_all_tags( $p->post_content );
		$thumb = get_the_post_thumbnail_url( $p, 'thumbnail' );
		$out[] = array(
			'id'      => (int) $p->ID,
			'nama'    => $nama,
			'juara'   => $juara,
			'ket'     => wp_trim_words( $raw, 22, '…' ),
			'thumb'   => $thumb ? $thumb : '',
		);
	}
	return $out;
}

/**
 * Markup pita prestasi beranda (di atas berita).
 */
function man3_prestasi_render_ticker() {
	$items = man3_prestasi_get_items();
	if ( ! $items ) {
		return;
	}
	$count = count( $items );
	?>
	<section
		class="section section-prestasi"
		id="prestasi-siswa"
		aria-labelledby="prestasi-title"
		data-prestasi-ticker
		data-prestasi-interval="5500"
	>
		<div class="container">
			<div class="prestasi-ribbon">
				<div class="prestasi-ribbon-head">
					<p class="section-kicker"><?php esc_html_e( 'Siswa berprestasi', 'man3-hijau-dingin' ); ?></p>
					<h2 class="prestasi-title" id="prestasi-title"><?php esc_html_e( 'Prestasi Siswa', 'man3-hijau-dingin' ); ?></h2>
				</div>
				<div class="prestasi-viewport" aria-live="polite">
					<div class="prestasi-track" data-prestasi-track>
						<?php foreach ( $items as $i => $item ) : ?>
							<article
								class="prestasi-card<?php echo 0 === $i ? ' is-active' : ''; ?>"
								data-prestasi-item="<?php echo esc_attr( (string) $i ); ?>"
								aria-hidden="<?php echo 0 === $i ? 'false' : 'true'; ?>"
							>
								<?php if ( $item['thumb'] ) : ?>
									<div class="prestasi-photo">
										<img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="" width="72" height="72" loading="lazy" />
									</div>
								<?php endif; ?>
								<div class="prestasi-body">
									<p class="prestasi-nama"><?php echo esc_html( $item['nama'] ); ?></p>
									<p class="prestasi-juara"><?php echo esc_html( $item['juara'] ); ?></p>
									<?php if ( $item['ket'] && $item['ket'] !== $item['juara'] ) : ?>
										<p class="prestasi-ket"><?php echo esc_html( $item['ket'] ); ?></p>
									<?php endif; ?>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
				<?php if ( $count > 1 ) : ?>
					<div class="prestasi-controls">
						<button type="button" class="prestasi-arrow prestasi-arrow--prev" data-prestasi-prev aria-label="<?php esc_attr_e( 'Prestasi sebelumnya', 'man3-hijau-dingin' ); ?>">
							<span aria-hidden="true">‹</span>
						</button>
						<div class="prestasi-dots" role="tablist" aria-label="<?php esc_attr_e( 'Navigasi prestasi', 'man3-hijau-dingin' ); ?>">
							<?php foreach ( $items as $i => $item ) : ?>
								<button
									type="button"
									class="prestasi-dot<?php echo 0 === $i ? ' is-active' : ''; ?>"
									data-prestasi-dot="<?php echo esc_attr( (string) $i ); ?>"
									role="tab"
									aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
									aria-label="<?php echo esc_attr( sprintf( /* translators: %d */ __( 'Prestasi %d', 'man3-hijau-dingin' ), $i + 1 ) ); ?>"
								></button>
							<?php endforeach; ?>
						</div>
						<button type="button" class="prestasi-arrow prestasi-arrow--next" data-prestasi-next aria-label="<?php esc_attr_e( 'Prestasi berikutnya', 'man3-hijau-dingin' ); ?>">
							<span aria-hidden="true">›</span>
						</button>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}
