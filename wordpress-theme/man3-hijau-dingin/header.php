<?php
/**
 * Header tema.
 *
 * @package Man3_Hijau_Dingin
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
	<div class="container header-inner">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<?php
			/* Badge persegi (bukan logo lebar WordPress) agar sejajar tinggi dua baris nama. */
			$brand_logo = man3_hijau_asset( 'images/logo-man3-badge.png' );
			?>
			<img
				class="brand-logo"
				src="<?php echo esc_url( $brand_logo ); ?>"
				alt=""
				width="88"
				height="88"
				decoding="async"
			/>
			<span class="brand-text">
				<span class="brand-name"><?php bloginfo( 'name' ); ?></span>
				<span class="brand-sub"><?php echo esc_html( get_bloginfo( 'description' ) ? get_bloginfo( 'description' ) : 'Cerdas Kreatif' ); ?></span>
			</span>
		</a>

		<nav class="nav-desktop" aria-label="<?php esc_attr_e( 'Navigasi utama', 'man3-hijau-dingin' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'items_wrap'     => '%3$s',
						'fallback_cb'    => false,
						'walker'         => new Man3_Hijau_Nav_Walker(),
						'depth'          => 2,
					)
				);
			} else {
				man3_hijau_fallback_nav();
			}
			?>
		</nav>

		<button type="button" class="menu-toggle" aria-expanded="false" aria-controls="nav-mobile" data-menu-toggle>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'man3-hijau-dingin' ); ?></span>
			<span data-menu-icon>☰</span>
		</button>
	</div>

	<nav id="nav-mobile" class="container nav-mobile" aria-label="<?php esc_attr_e( 'Navigasi mobile', 'man3-hijau-dingin' ); ?>" data-nav-mobile>
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'fallback_cb'    => false,
					'walker'         => new Man3_Hijau_Nav_Walker(),
					'depth'          => 2,
				)
			);
		} else {
			man3_hijau_fallback_nav( true );
		}
		?>
	</nav>
</header>
