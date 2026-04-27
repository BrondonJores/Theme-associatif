<?php
/**
 * Theme Associatif - Main theme bootstrap file.
 *
 * Loads all required includes, registers hooks, and initialises the theme.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'THEME_ASSOCIATIF_VERSION', '1.0.0' );
define( 'THEME_ASSOCIATIF_DIR', get_template_directory() );
define( 'THEME_ASSOCIATIF_URI', get_template_directory_uri() );
define( 'THEME_ASSOCIATIF_READING_WPM', 200 );

/**
 * Load required includes in dependency order.
 */
require_once THEME_ASSOCIATIF_DIR . '/inc/theme-setup.php';
require_once THEME_ASSOCIATIF_DIR . '/inc/enqueue-assets.php';
require_once THEME_ASSOCIATIF_DIR . '/inc/template-tags.php';
require_once THEME_ASSOCIATIF_DIR . '/inc/customizer.php';
