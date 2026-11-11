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

// Pisahkan anak langsung Kepala: Komite (sejajar) vs KTU (komando) vs Waka.
$komite = null;
$ktu    = null;
$wakas  = array();
if ( $head && ! empty( $head['children'] ) ) {
	foreach ( $head['children'] as $child ) {
		$j = strtolower( $child['jabatan'] );
		if ( false !== strpos( $j, 'waka' ) ) {
			$wakas[] = $child;
		} elseif ( false !== strpos( $j, 'komite' ) ) {
			$komite = $child;
		} elseif ( false !== strpos( $j, 'tata usaha' ) || false !== strpos( $j, 'ktu' ) ) {
			$ktu = $child;
		} else {
			// Fallback: non-waka lain di jalur komando di bawah kepala (setelah KTU bila ada).
			if ( ! $ktu ) {
				$ktu = $child;
			} else {
				$wakas[] = $child;
			}
		}
	}
}
?>

<main class="section org-page">
	<div class="container">
		<div class="section-head org-page-head">
			<p class="section-kicker"><?php esc_html_e( 'Pendidik dan Kependidikan', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title"><?php esc_html_e( 'Struktur Organisasi', 'man3-hijau-dingin' ); ?></h1>
			<p class="section-lead"><?php esc_html_e( 'MAN 3 Jember — Tahun Pelajaran 2026–2027.', 'man3-hijau-dingin' ); ?></p>
		</div>

		<?php if ( ! $head ) : ?>
			<p><?php esc_html_e( 'Data struktur belum tersedia.', 'man3-hijau-dingin' ); ?></p>
		<?php else : ?>

			<div class="org-chart" role="tree" aria-label="<?php esc_attr_e( 'Bagan struktur organisasi', 'man3-hijau-dingin' ); ?>">
				<div class="org-head-grid">
					<?php if ( $komite ) : ?>
						<div class="org-cell org-cell--komite">
							<?php man3_org_render_card( $komite, 'komite' ); ?>
						</div>
						<div class="org-cell org-cell--dash" aria-hidden="true">
							<span class="org-dash-link"></span>
						</div>
					<?php else : ?>
						<div class="org-cell org-cell--spacer" aria-hidden="true"></div>
						<div class="org-cell org-cell--spacer" aria-hidden="true"></div>
					<?php endif; ?>

					<div class="org-cell org-cell--kepala">
						<?php man3_org_render_card( $head, 'peak' ); ?>
					</div>

					<?php if ( $ktu ) : ?>
						<div class="org-cell org-cell--spacer" aria-hidden="true"></div>
						<div class="org-cell org-cell--spacer" aria-hidden="true"></div>
						<div class="org-cell org-cell--spine">
							<span class="org-connector org-connector--command" aria-hidden="true"></span>
						</div>

						<div class="org-cell org-cell--spacer" aria-hidden="true"></div>
						<div class="org-cell org-cell--spacer" aria-hidden="true"></div>
						<div class="org-cell org-cell--ktu">
							<?php man3_org_render_card( $ktu, 'ktu' ); ?>
						</div>
					<?php endif; ?>

					<?php if ( $wakas ) : ?>
						<div class="org-cell org-cell--spacer" aria-hidden="true"></div>
						<div class="org-cell org-cell--spacer" aria-hidden="true"></div>
						<div class="org-cell org-cell--spine">
							<span class="org-connector org-connector--command" aria-hidden="true"></span>
						</div>
					<?php endif; ?>
				</div>

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
