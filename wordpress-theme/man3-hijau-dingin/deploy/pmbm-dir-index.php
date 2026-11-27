<?php
/**
 * Deploy copy → public_html/pmbm/index.php
 *
 * Physical /pmbm/ still holds the legacy formulir (formulir.php, submit.php, assets/).
 * This DirectoryIndex bridge makes https://man3jember.sch.id/pmbm/ render the WP
 * page-pmbm.php template without deleting the formulir app.
 *
 * @package Man3_Hijau_Dingin
 */

define( 'WP_USE_THEMES', true );
require dirname( __DIR__ ) . '/wp-blog-header.php';
