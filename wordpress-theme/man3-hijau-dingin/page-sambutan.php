<?php
/**
 * Template Name: Sambutan Kepala Madrasah
 * Halaman editorial sambutan Kamad.
 *
 * @package Man3_Hijau_Dingin
 */

get_header();

$kamad = man3_hijau_asset( 'images/kamad.png' );
$name  = function_exists( 'man3_sambutan_get_display_name' ) ? man3_sambutan_get_display_name() : 'IMAM SYAHRONI';
$role  = function_exists( 'man3_sambutan_get_role' ) ? man3_sambutan_get_role() : __( 'Kepala Madrasah', 'man3-hijau-dingin' );
?>

<main class="section sambutan-page">
	<div class="container">
		<?php
		while ( have_posts() ) :
			the_post();
			$thumb = get_the_post_thumbnail_url( get_the_ID(), 'large' );
			$photo = $thumb ? $thumb : $kamad;
			?>
			<article <?php post_class( 'sambutan sambutan--full' ); ?>>
				<div class="sambutan-photo">
					<img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
				</div>
				<div class="sambutan-body">
					<p class="section-kicker"><?php esc_html_e( 'Editorial', 'man3-hijau-dingin' ); ?></p>
					<h1 class="sambutan-page-title"><?php echo esc_html( $name ); ?></h1>
					<p class="sambutan-role"><?php echo esc_html( $role ); ?></p>
					<p class="sambutan-date"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></p>
					<div class="sambutan-content entry-content">
						<?php the_content(); ?>
					</div>
					<p class="sambutan-back">
						<a class="btn btn-outline" href="<?php echo esc_url( home_url( '/#sambutan' ) ); ?>">
							<?php esc_html_e( 'Kembali ke beranda', 'man3-hijau-dingin' ); ?>
						</a>
					</p>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</div>
</main>

<?php
get_footer();
