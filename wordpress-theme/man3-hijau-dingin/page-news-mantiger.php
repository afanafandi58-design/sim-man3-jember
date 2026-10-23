<?php
/**
 * Template Name: News Mantiger
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$berita_url = home_url( '/category/berita/' );
$ig_url     = 'https://www.instagram.com/mantiger.official/';
?>
<main id="main" class="site-main page-news-mantiger">
	<section class="section page-hero-org news-mantiger-hero">
		<div class="container">
			<p class="eyebrow"><?php esc_html_e( 'Kanal berita siswa', 'man3-hijau-dingin' ); ?></p>
			<h1 class="section-title"><?php esc_html_e( 'News Mantiger', 'man3-hijau-dingin' ); ?></h1>
			<p class="section-lead">
				<?php esc_html_e( 'Ruang berita dan liputan warga MAN 3 Jember (Mantiger). Konten lengkap akan terus ditambahkan — sementara Anda dapat mengikuti berita madrasah dan kanal Instagram resmi.', 'man3-hijau-dingin' ); ?>
			</p>
		</div>
	</section>

	<section class="section section-news-mantiger">
		<div class="container news-mantiger-body">
			<?php
			while ( have_posts() ) :
				the_post();
				$content = trim( (string) get_the_content() );
				if ( $content ) {
					echo '<div class="entry-content news-mantiger-content">';
					the_content();
					echo '</div>';
				}
			endwhile;
			?>

			<div class="news-mantiger-actions">
				<a class="btn btn-primary" href="<?php echo esc_url( $berita_url ); ?>">
					<?php esc_html_e( 'Lihat berita madrasah', 'man3-hijau-dingin' ); ?>
				</a>
				<a class="btn btn-ghost" href="<?php echo esc_url( $ig_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Instagram @mantiger.official', 'man3-hijau-dingin' ); ?>
				</a>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
