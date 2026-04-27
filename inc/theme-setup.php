<?php
/**
 * Theme Setup
 *
 * Registers all WordPress theme supports, navigation menus,
 * and widget sidebars.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function theme_associatif_setup(): void {
    // Localisation.
    load_theme_textdomain('theme-associatif', THEME_ASSOCIATIF_DIR . '/languages');

    // Content width.
    if (!isset($GLOBALS['content_width'])) {
        $GLOBALS['content_width'] = 1280;
    }

    // Title tag managed by WordPress.
    add_theme_support('title-tag');

    // Automatic feed links.
    add_theme_support('automatic-feed-links');

    // Post thumbnails.
    add_theme_support('post-thumbnails');

    // Register additional image sizes.
    add_image_size('theme-associatif-card',    600, 400, true);
    add_image_size('theme-associatif-hero',   1440, 600, true);
    add_image_size('theme-associatif-thumb',   400, 300, true);

    // HTML5 markup support.
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    // Custom logo.
    add_theme_support('custom-logo', array(
        'height'               => 80,
        'width'                => 200,
        'flex-width'           => true,
        'flex-height'          => true,
        'unlink-homepage-logo' => false,
    ));

    // Custom header.
    add_theme_support('custom-header', array(
        'default-image'      => '',
        'random-default'     => false,
        'width'              => 1440,
        'height'             => 600,
        'flex-height'        => true,
        'flex-width'         => true,
        'default-text-color' => '0f172a',
        'header-text'        => true,
        'uploads'            => true,
    ));

    // Custom background.
    add_theme_support('custom-background', array(
        'default-color' => 'ffffff',
        'default-image' => '',
    ));

    // Block editor styles (for Gutenberg).
    add_theme_support('editor-styles');
    add_editor_style('assets/css/main.css');

    // Wide / full-width block alignment support.
    add_theme_support('align-wide');

    // Responsive embed support.
    add_theme_support('responsive-embeds');

    // Core block patterns.
    add_theme_support('core-block-patterns');

    // Post formats.
    add_theme_support('post-formats', array(
        'aside',
        'gallery',
        'link',
        'image',
        'quote',
        'status',
        'video',
        'audio',
        'chat',
    ));

    // Selective refresh for widgets in Customizer.
    add_theme_support('customize-selective-refresh-widgets');

    // Register navigation menus.
    register_nav_menus(array(
        'primary'    => esc_html__('Primary Navigation', 'theme-associatif'),
        'footer'     => esc_html__('Footer Navigation', 'theme-associatif'),
        'footer-col-1' => esc_html__('Footer Column 1', 'theme-associatif'),
        'footer-col-2' => esc_html__('Footer Column 2', 'theme-associatif'),
        'social'     => esc_html__('Social Links Menu', 'theme-associatif'),
    ));
}
add_action('after_setup_theme', 'theme_associatif_setup');

/**
 * Registers widget areas (sidebars).
 */
function theme_associatif_register_sidebars(): void {
    $defaults = array(
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title widget__title">',
        'after_title'   => '</h3>',
    );

    register_sidebar(array_merge($defaults, array(
        'name'        => esc_html__('Blog Sidebar', 'theme-associatif'),
        'id'          => 'sidebar-blog',
        'description' => esc_html__('Add widgets here to appear in the blog sidebar.', 'theme-associatif'),
    )));

    register_sidebar(array_merge($defaults, array(
        'name'        => esc_html__('Footer Widget Area', 'theme-associatif'),
        'id'          => 'sidebar-footer',
        'description' => esc_html__('Add widgets here to appear in the footer.', 'theme-associatif'),
    )));

    register_sidebar(array_merge($defaults, array(
        'name'        => esc_html__('Events Sidebar', 'theme-associatif'),
        'id'          => 'sidebar-events',
        'description' => esc_html__('Add widgets here to appear in the events sidebar.', 'theme-associatif'),
    )));
}
add_action('widgets_init', 'theme_associatif_register_sidebars');
