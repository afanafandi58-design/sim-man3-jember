<?php
/**
 * Footer tema.
 *
 * @package Man3_Hijau_Dingin
 */
?>
<footer class="site-footer">
	<div class="container footer-grid">
		<div class="footer-brand">
			<img src="<?php echo esc_url( man3_hijau_asset( 'images/logo.png' ) ); ?>" alt="" width="52" height="52" />
			<div>
				<strong><?php bloginfo( 'name' ); ?></strong>
				<p>CERDAS KREATIF · CIVITAS MAN 3 JEMBER KOMPAK SALING PERCAYA</p>
			</div>
		</div>
		<div class="footer-col">
			<h3><?php esc_html_e( 'Kontak', 'man3-hijau-dingin' ); ?></h3>
			<a href="mailto:humasmantiger@gmail.com">humasmantiger@gmail.com</a>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Situs resmi', 'man3-hijau-dingin' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/news-mantiger/' ) ); ?>"><?php esc_html_e( 'News Mantiger', 'man3-hijau-dingin' ); ?></a>
		</div>
		<div class="footer-col">
			<h3><?php esc_html_e( 'Media sosial', 'man3-hijau-dingin' ); ?></h3>
			<a href="https://www.facebook.com/share/9T59pSFrFvKQNNjS/?mibextid=qi2Omg" target="_blank" rel="noreferrer">Facebook</a>
			<a href="https://www.instagram.com/mantiger.official?igsh=MW56eGYxMW12ZGZhbQ==" target="_blank" rel="noreferrer">Instagram @mantiger.official</a>
			<a href="https://www.youtube.com/@man3jember510" target="_blank" rel="noreferrer">YouTube MAN 3 Jember</a>
		</div>
	</div>
	<div class="container footer-bottom">
		<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
		<span><?php esc_html_e( 'Tema Hijau Dingin', 'man3-hijau-dingin' ); ?></span>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
