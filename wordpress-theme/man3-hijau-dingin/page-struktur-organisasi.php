<?php
/**
 * Template Name: Struktur Organisasi
 * Template halaman hierarki struktur organisasi.
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$tree = man3_org_get_tree();
$head = ! empty( $tree ) ? $tree[0] : null;

// Pisahkan anak langsung Kepala: komite/TU vs Waka
$support = array();
$wakas   = array();
if ( $head && ! empty( $head['children'] ) ) {
	foreach ( $head['children'] as $child ) {
		$j = strtolower( $child['jabatan'] );
		if ( false !== strpos( $j, 'waka' ) ) {
			$wakas[] = $child;
		} else {
			$support[] = $child;
		}
	}
}
?>

<main class="section org-page">
	<div class="container">
		<div class="section-head org-page-head">
			<p class="section-kicker"><?php esc_html_e( 'Pendidik dan Kependidikan', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title"><?php esc_html_e( 'Struktur Organisasi', 'man3-hijau-dingin' ); ?></h1>
			<p class="section-lead"><?php esc_html_e( 'MAN 3 Jember — Tahun Pelajaran 2026–2027. Foto dapat dilengkapi lewat admin (Gambar Unggulan).', 'man3-hijau-dingin' ); ?></p>
		</div>

		<?php if ( ! $head ) : ?>
			<p><?php esc_html_e( 'Data struktur belum tersedia. Admin: buka dashboard lalu muat ulang, atau kunjungi ?man3_org_reseed=1 saat login.', 'man3-hijau-dingin' ); ?></p>
		<?php else : ?>

			<div class="org-chart" role="tree" aria-label="<?php esc_attr_e( 'Bagan struktur organisasi', 'man3-hijau-dingin' ); ?>">
				<div class="org-level org-level--peak">
					<?php man3_org_render_card( $head, 'peak' ); ?>
				</div>

				<?php if ( $support ) : ?>
					<div class="org-connector" aria-hidden="true"></div>
					<div class="org-level org-level--support">
						<?php foreach ( $support as $node ) : ?>
							<?php man3_org_render_card( $node, 'support' ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $wakas ) : ?>
					<div class="org-connector org-connector--wide" aria-hidden="true"></div>
					<div class="org-level org-level--waka">
						<?php foreach ( $wakas as $waka ) : ?>
							<div class="org-branch">
								<?php man3_org_render_card( $waka, 'waka' ); ?>
								<?php if ( ! empty( $waka['children'] ) ) : ?>
									<div class="org-branch-line" aria-hidden="true"></div>
									<ul class="org-branch-list">
										<?php foreach ( $waka['children'] as $child ) : ?>
											<li>
												<?php man3_org_render_card( $child, 'staff' ); ?>
												<?php if ( ! empty( $child['children'] ) ) : ?>
													<ul class="org-branch-list org-branch-list--nested">
														<?php foreach ( $child['children'] as $grand ) : ?>
															<li><?php man3_org_render_card( $grand, 'nested' ); ?></li>
														<?php endforeach; ?>
													</ul>
												<?php endif; ?>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
