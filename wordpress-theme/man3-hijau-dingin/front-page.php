<?php
/**
 * Template beranda — desain hijau dingin + konten MAN 3 Jember.
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$logo      = man3_hijau_asset( 'images/logo.png' );
$hero_def  = man3_hijau_asset( 'images/hero-campus.jpg' );
$slides    = function_exists( 'man3_hero_get_slides' ) ? man3_hero_get_slides() : array();
if ( ! $slides && function_exists( 'man3_hero_fallback_slides' ) ) {
	$slides = man3_hero_fallback_slides();
}
if ( ! $slides ) {
	$slides = array(
		array(
			'id'    => 0,
			'title' => '',
			'url'   => $hero_def,
			'alt'   => 'MAN 3 Jember',
		),
	);
}
$slide_count = count( $slides );
$kamad       = man3_hijau_asset( 'images/kamad.png' );
$quotes    = man3_hijau_fallback_quotes();
$quick     = array(
	array( 'label' => 'Pengumuman', 'href' => home_url( '/pengumuman/' ) ),
	array( 'label' => 'Agenda', 'href' => home_url( '/agenda/' ) ),
	array( 'label' => 'Fasilitas', 'href' => home_url( '/fasilitas/' ) ),
	array( 'label' => 'Daftar Guru', 'href' => home_url( '/gtk/' ) ),
	array( 'label' => 'Staff', 'href' => home_url( '/Staff/' ) ),
	array( 'label' => 'Ekstra Kurikuler', 'href' => home_url( '/ekskul/' ) ),
	array( 'label' => 'Galeri Foto', 'href' => home_url( '/galeri/' ) ),
	array( 'label' => 'Video', 'href' => home_url( '/video/' ) ),
	array( 'label' => 'Download', 'href' => home_url( '/download/' ) ),
	array( 'label' => 'News Mantiger', 'href' => home_url( '/news-mantiger/' ) ),
);
$news_digital = array(
	array( 'label' => 'News Digital at Juli', 'published' => 'July 23, 2024', 'href' => home_url( '/pengumuman/hightlight-mei/' ) ),
	array( 'label' => 'News Digital at Juni', 'published' => 'July 23, 2024', 'href' => home_url( '/pengumuman/hightlight-mei/' ) ),
	array( 'label' => 'Highlight Mei', 'published' => 'May 6, 2026', 'href' => home_url( '/pengumuman/hightlight-mei/' ) ),
);

$wp_posts = new WP_Query(
	array(
		'posts_per_page' => 5,
		'post_status'    => 'publish',
	)
);
?>

<main>
	<section
		class="hero hero--slider"
		id="beranda"
		aria-label="<?php esc_attr_e( 'Beranda MAN 3 Jember', 'man3-hijau-dingin' ); ?>"
		data-hero-slider
		data-hero-interval="5500"
	>
		<div class="hero-track" aria-hidden="true">
			<?php foreach ( $slides as $i => $slide ) : ?>
				<figure class="hero-slide<?php echo 0 === $i ? ' is-active' : ''; ?>" data-hero-slide="<?php echo esc_attr( (string) $i ); ?>">
					<img
						src="<?php echo esc_url( $slide['url'] ); ?>"
						alt=""
						<?php echo 0 === $i ? '' : 'loading="lazy"'; ?>
						decoding="async"
					/>
				</figure>
			<?php endforeach; ?>
		</div>
		<div class="hero-content">
			<div class="hero-brand">
				<img src="<?php echo esc_url( $logo ); ?>" alt="" width="72" height="72" />
				<h1 class="hero-brand-name"><?php bloginfo( 'name' ); ?></h1>
			</div>
			<p class="hero-headline">CERDAS KREATIF</p>
			<p class="hero-support">
				CIVITAS MAN 3 JEMBER KOMPAK SALING PERCAYA. Membiasakan civitas akademika berperilaku sesuai ajaran islam dan norma yang berlaku di masyarakat
			</p>
			<div class="hero-cta">
				<a class="btn btn-primary" href="#sambutan"><?php esc_html_e( 'Sambutan Kepala Madrasah', 'man3-hijau-dingin' ); ?></a>
				<a class="btn btn-ghost" href="#berita"><?php esc_html_e( 'Berita Terbaru', 'man3-hijau-dingin' ); ?></a>
			</div>
		</div>
		<?php if ( $slide_count > 1 ) : ?>
			<div class="hero-nav" role="group" aria-label="<?php esc_attr_e( 'Navigasi slider hero', 'man3-hijau-dingin' ); ?>">
				<button type="button" class="hero-arrow hero-arrow--prev" data-hero-prev aria-label="<?php esc_attr_e( 'Slide sebelumnya', 'man3-hijau-dingin' ); ?>">
					<span aria-hidden="true">‹</span>
				</button>
				<button type="button" class="hero-arrow hero-arrow--next" data-hero-next aria-label="<?php esc_attr_e( 'Slide berikutnya', 'man3-hijau-dingin' ); ?>">
					<span aria-hidden="true">›</span>
				</button>
			</div>
			<div class="hero-dots" role="tablist" aria-label="<?php esc_attr_e( 'Pilih slide', 'man3-hijau-dingin' ); ?>">
				<?php for ( $i = 0; $i < $slide_count; $i++ ) : ?>
					<button
						type="button"
						class="hero-dot<?php echo 0 === $i ? ' is-active' : ''; ?>"
						data-hero-dot="<?php echo esc_attr( (string) $i ); ?>"
						role="tab"
						aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
						aria-label="<?php echo esc_attr( sprintf( /* translators: %d slide number */ __( 'Slide %d', 'man3-hijau-dingin' ), $i + 1 ) ); ?>"
					></button>
				<?php endfor; ?>
			</div>
		<?php endif; ?>
	</section>

	<?php
	$sambutan_name = function_exists( 'man3_sambutan_get_display_name' ) ? man3_sambutan_get_display_name() : 'IMAM SYAHRONI';
	$sambutan_role = function_exists( 'man3_sambutan_get_role' ) ? man3_sambutan_get_role() : __( 'Kepala Madrasah', 'man3-hijau-dingin' );
	$sambutan_date = function_exists( 'man3_sambutan_get_date_label' ) ? man3_sambutan_get_date_label() : '';
	$sambutan_ex   = function_exists( 'man3_sambutan_get_excerpt' ) ? man3_sambutan_get_excerpt( 220 ) : '';
	$sambutan_url  = function_exists( 'man3_sambutan_get_url' ) ? man3_sambutan_get_url() : home_url( '/editorial/sambutan-kepala-madrasah/' );
	$sambutan_page = function_exists( 'man3_sambutan_get_page' ) ? man3_sambutan_get_page() : null;
	if ( $sambutan_page instanceof WP_Post ) {
		$thumb = get_the_post_thumbnail_url( $sambutan_page, 'large' );
		if ( $thumb ) {
			$kamad = $thumb;
		}
	}
	?>
	<section class="section" id="sambutan">
		<div class="container sambutan">
			<div class="sambutan-photo">
				<img src="<?php echo esc_url( $kamad ); ?>" alt="<?php echo esc_attr( sprintf( /* translators: %s name */ __( 'Foto %s', 'man3-hijau-dingin' ), $sambutan_name ) ); ?>" />
			</div>
			<div class="sambutan-body">
				<p class="section-kicker"><?php esc_html_e( 'Editorial', 'man3-hijau-dingin' ); ?></p>
				<h2><?php echo esc_html( $sambutan_name ); ?></h2>
				<p class="sambutan-role"><?php echo esc_html( $sambutan_role ); ?></p>
				<?php if ( $sambutan_date ) : ?>
					<p class="sambutan-date"><?php echo esc_html( $sambutan_date ); ?></p>
				<?php endif; ?>
				<p class="sambutan-excerpt"><?php echo esc_html( $sambutan_ex ); ?></p>
				<a class="btn btn-solid" href="<?php echo esc_url( $sambutan_url ); ?>">
					<?php esc_html_e( 'Selengkapnya', 'man3-hijau-dingin' ); ?>
				</a>
			</div>
		</div>
	</section>

	<section class="section quotes" aria-labelledby="quotes-title">
		<div class="container">
			<div class="section-head">
				<p class="section-kicker"><?php esc_html_e( 'Suara Civitas', 'man3-hijau-dingin' ); ?></p>
				<h2 class="section-title" id="quotes-title"><?php esc_html_e( 'Refleksi warga madrasah', 'man3-hijau-dingin' ); ?></h2>
				<p class="section-lead"><?php esc_html_e( 'Cuplikan semangat dan nilai yang hidup di MAN 3 Jember.', 'man3-hijau-dingin' ); ?></p>
			</div>
			<div class="quote-track">
				<?php foreach ( $quotes as $q ) : ?>
					<blockquote class="quote-item">
						<p>&ldquo;<?php echo esc_html( $q['text'] ); ?>&rdquo;</p>
						<cite><?php echo esc_html( $q['author'] ); ?></cite>
					</blockquote>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="section" id="berita">
		<div class="container">
			<div class="section-head">
				<p class="section-kicker"><?php esc_html_e( 'Berita Terbaru', 'man3-hijau-dingin' ); ?></p>
				<h2 class="section-title"><?php esc_html_e( 'Kabar dari MAN 3 Jember', 'man3-hijau-dingin' ); ?></h2>
				<p class="section-lead"><?php esc_html_e( 'Berita dari WordPress (atau fallback konten publik yang sama).', 'man3-hijau-dingin' ); ?></p>
			</div>
			<div class="berita-list">
				<?php if ( $wp_posts->have_posts() ) : ?>
					<?php
					while ( $wp_posts->have_posts() ) :
						$wp_posts->the_post();
						$thumb = get_the_post_thumbnail_url( get_the_ID(), 'medium_large' );
						if ( ! $thumb ) {
							$thumb = man3_hijau_asset( 'images/berita-istisqa.jpg' );
						}
						?>
						<a class="berita-item" href="<?php the_permalink(); ?>">
							<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy" />
							<div class="berita-body">
								<p class="berita-date"><?php echo esc_html( get_the_date() ); ?></p>
								<h3><?php the_title(); ?></h3>
								<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
								<span class="more"><?php esc_html_e( 'Baca selengkapnya →', 'man3-hijau-dingin' ); ?></span>
							</div>
						</a>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<?php foreach ( man3_hijau_fallback_berita() as $item ) : ?>
						<a class="berita-item" href="<?php echo esc_url( $item['link'] ); ?>">
							<img src="<?php echo esc_url( $item['image'] ); ?>" alt="" loading="lazy" />
							<div class="berita-body">
								<p class="berita-date"><?php echo esc_html( $item['date'] ); ?></p>
								<h3><?php echo esc_html( $item['title'] ); ?></h3>
								<p><?php echo esc_html( $item['excerpt'] ); ?></p>
								<span class="more"><?php esc_html_e( 'Baca selengkapnya →', 'man3-hijau-dingin' ); ?></span>
							</div>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="section" id="visi-misi">
		<div class="container visi-block">
			<div>
				<div class="section-head">
					<p class="section-kicker"><?php esc_html_e( 'Visi Misi', 'man3-hijau-dingin' ); ?></p>
					<h2 class="section-title">VISI MISI MAN 3 JEMBER</h2>
				</div>
				<div class="visi-panel">
					<p>
						Membiasakan civitas akademika berperilaku sesuai ajaran islam dan norma yang berlaku di masyarakat — mewujudkan generasi cerdas, kreatif, beriman, dan saling percaya.
					</p>
					<a class="btn btn-outline" href="<?php echo esc_url( home_url( '/visi-misi-man-3-jember/' ) ); ?>">
						<?php esc_html_e( 'Baca Visi Misi lengkap', 'man3-hijau-dingin' ); ?>
					</a>
				</div>
			</div>
			<div>
				<div class="section-head">
					<p class="section-kicker"><?php esc_html_e( 'Info Madrasah', 'man3-hijau-dingin' ); ?></p>
					<h2 class="section-title"><?php esc_html_e( 'Akses cepat', 'man3-hijau-dingin' ); ?></h2>
					<p class="section-lead"><?php esc_html_e( 'Tautan menu yang sama dengan situs live.', 'man3-hijau-dingin' ); ?></p>
				</div>
				<div class="links-grid">
					<?php foreach ( $quick as $link ) : ?>
						<a href="<?php echo esc_url( $link['href'] ); ?>"<?php echo ( 0 === strpos( $link['href'], 'http' ) && false === strpos( $link['href'], home_url() ) ) ? ' target="_blank" rel="noreferrer"' : ''; ?>>
							<?php echo esc_html( $link['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>

	<section class="section" id="news-digital">
		<div class="container">
			<div class="section-head">
				<p class="section-kicker">News Digital</p>
				<h2 class="section-title"><?php esc_html_e( 'Highlight terbitan', 'man3-hijau-dingin' ); ?></h2>
			</div>
			<div class="news-digital">
				<?php foreach ( $news_digital as $n ) : ?>
					<a href="<?php echo esc_url( $n['href'] ); ?>">
						<span><?php echo esc_html( $n['label'] ); ?></span>
						<span><?php echo esc_html( sprintf( /* translators: %s date */ __( 'Diterbitkan : %s', 'man3-hijau-dingin' ), $n['published'] ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
