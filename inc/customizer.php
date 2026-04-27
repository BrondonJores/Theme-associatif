<?php
/**
 * WordPress Customizer Settings
 *
 * Registers all theme Customizer panels, sections, settings and controls
 * for the Theme Associatif WordPress theme.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers Customizer panels, sections, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize The Customizer manager instance.
 */
function theme_associatif_customizer(WP_Customize_Manager $wp_customize): void {

    /* ----------------------------------------------------------
       Panel: Theme Associatif
       ---------------------------------------------------------- */
    $wp_customize->add_panel('theme_associatif', array(
        'title'    => esc_html__('Theme Associatif', 'theme-associatif'),
        'priority' => 130,
    ));

    /* ----------------------------------------------------------
       Section: Association Identity
       ---------------------------------------------------------- */
    $wp_customize->add_section('theme_associatif_identity', array(
        'title'    => esc_html__('Association Identity', 'theme-associatif'),
        'panel'    => 'theme_associatif',
        'priority' => 10,
    ));

    // Association Name (mirrors Site Title, kept for display overrides).
    $wp_customize->add_setting('association_name', array(
        'default'           => get_bloginfo('name'),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('association_name', array(
        'label'   => esc_html__('Association Name', 'theme-associatif'),
        'section' => 'theme_associatif_identity',
        'type'    => 'text',
    ));

    // Association Tagline.
    $wp_customize->add_setting('association_tagline', array(
        'default'           => get_bloginfo('description'),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('association_tagline', array(
        'label'   => esc_html__('Tagline', 'theme-associatif'),
        'section' => 'theme_associatif_identity',
        'type'    => 'text',
    ));

    /* ----------------------------------------------------------
       Section: Hero Section
       ---------------------------------------------------------- */
    $wp_customize->add_section('theme_associatif_hero', array(
        'title'    => esc_html__('Hero Section', 'theme-associatif'),
        'panel'    => 'theme_associatif',
        'priority' => 20,
    ));

    $hero_settings = array(
        'hero_title'               => array('label' => esc_html__('Hero Title', 'theme-associatif'),           'type' => 'text',     'default' => get_bloginfo('name')),
        'hero_subtitle'            => array('label' => esc_html__('Hero Subtitle (eyebrow)', 'theme-associatif'), 'type' => 'text',   'default' => ''),
        'hero_description'         => array('label' => esc_html__('Hero Description', 'theme-associatif'),    'type' => 'textarea', 'default' => get_bloginfo('description')),
        'hero_badge_text'          => array('label' => esc_html__('Badge Text', 'theme-associatif'),          'type' => 'text',     'default' => ''),
        'hero_cta_primary_text'    => array('label' => esc_html__('Primary CTA Text', 'theme-associatif'),    'type' => 'text',     'default' => __('Discover our events', 'theme-associatif')),
        'hero_cta_primary_url'     => array('label' => esc_html__('Primary CTA URL', 'theme-associatif'),     'type' => 'url',      'default' => ''),
        'hero_cta_secondary_text'  => array('label' => esc_html__('Secondary CTA Text', 'theme-associatif'),  'type' => 'text',     'default' => __('Join us', 'theme-associatif')),
        'hero_cta_secondary_url'   => array('label' => esc_html__('Secondary CTA URL', 'theme-associatif'),   'type' => 'url',      'default' => ''),
    );

    foreach ($hero_settings as $setting_id => $config) {
        $sanitize_callback = match ($config['type']) {
            'url'      => 'esc_url_raw',
            'textarea' => 'sanitize_textarea_field',
            default    => 'sanitize_text_field',
        };

        $wp_customize->add_setting($setting_id, array(
            'default'           => $config['default'],
            'sanitize_callback' => $sanitize_callback,
            'transport'         => 'postMessage',
        ));
        $wp_customize->add_control($setting_id, array(
            'label'   => $config['label'],
            'section' => 'theme_associatif_hero',
            'type'    => $config['type'],
        ));
    }

    // Hero Image.
    $wp_customize->add_setting('hero_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'hero_image', array(
        'label'   => esc_html__('Hero Image', 'theme-associatif'),
        'section' => 'theme_associatif_hero',
    )));

    /* ----------------------------------------------------------
       Section: Statistics (front page)
       ---------------------------------------------------------- */
    $wp_customize->add_section('theme_associatif_stats', array(
        'title'    => esc_html__('Association Statistics', 'theme-associatif'),
        'panel'    => 'theme_associatif',
        'priority' => 30,
    ));

    $stats = array(
        'stat_members'  => array('label' => esc_html__('Active Members Count', 'theme-associatif'),     'default' => '500'),
        'stat_events'   => array('label' => esc_html__('Events per Year Count', 'theme-associatif'),   'default' => '50'),
        'stat_years'    => array('label' => esc_html__('Years of Activity Count', 'theme-associatif'), 'default' => '10'),
        'stat_projects' => array('label' => esc_html__('Projects Completed Count', 'theme-associatif'),'default' => '30'),
    );

    foreach ($stats as $stat_id => $stat_config) {
        $wp_customize->add_setting($stat_id, array(
            'default'           => $stat_config['default'],
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage',
        ));
        $wp_customize->add_control($stat_id, array(
            'label'   => $stat_config['label'],
            'section' => 'theme_associatif_stats',
            'type'    => 'text',
        ));
    }

    /* ----------------------------------------------------------
       Section: Social Links
       ---------------------------------------------------------- */
    $wp_customize->add_section('theme_associatif_social', array(
        'title'    => esc_html__('Social Links', 'theme-associatif'),
        'panel'    => 'theme_associatif',
        'priority' => 40,
    ));

    $social_links = array(
        'social_github'    => esc_html__('GitHub URL', 'theme-associatif'),
        'social_twitter'   => esc_html__('Twitter / X URL', 'theme-associatif'),
        'social_instagram' => esc_html__('Instagram URL', 'theme-associatif'),
        'social_linkedin'  => esc_html__('LinkedIn URL', 'theme-associatif'),
        'social_facebook'  => esc_html__('Facebook URL', 'theme-associatif'),
    );

    foreach ($social_links as $setting_id => $label) {
        $wp_customize->add_setting($setting_id, array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control($setting_id, array(
            'label'   => $label,
            'section' => 'theme_associatif_social',
            'type'    => 'url',
        ));
    }

    /* ----------------------------------------------------------
       Section: Colors
       ---------------------------------------------------------- */
    $wp_customize->add_section('theme_associatif_colors', array(
        'title'    => esc_html__('Theme Colors', 'theme-associatif'),
        'panel'    => 'theme_associatif',
        'priority' => 50,
    ));

    $wp_customize->add_setting('color_primary', array(
        'default'           => '#6366F1',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'color_primary', array(
        'label'   => esc_html__('Primary Color', 'theme-associatif'),
        'section' => 'theme_associatif_colors',
    )));

    $wp_customize->add_setting('color_secondary', array(
        'default'           => '#EC4899',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'color_secondary', array(
        'label'   => esc_html__('Secondary Color', 'theme-associatif'),
        'section' => 'theme_associatif_colors',
    )));

    $wp_customize->add_setting('color_accent', array(
        'default'           => '#14B8A6',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'color_accent', array(
        'label'   => esc_html__('Accent Color', 'theme-associatif'),
        'section' => 'theme_associatif_colors',
    )));

    /* ----------------------------------------------------------
       Section: Footer
       ---------------------------------------------------------- */
    $wp_customize->add_section('theme_associatif_footer', array(
        'title'    => esc_html__('Footer', 'theme-associatif'),
        'panel'    => 'theme_associatif',
        'priority' => 60,
    ));

    $wp_customize->add_setting('footer_description', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('footer_description', array(
        'label'   => esc_html__('Footer Description', 'theme-associatif'),
        'section' => 'theme_associatif_footer',
        'type'    => 'textarea',
    ));

    $wp_customize->add_setting('footer_copyright_text', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('footer_copyright_text', array(
        'label'       => esc_html__('Copyright Text', 'theme-associatif'),
        'description' => esc_html__('Text displayed after the year and site name in the copyright bar.', 'theme-associatif'),
        'section'     => 'theme_associatif_footer',
        'type'        => 'text',
    ));

    /* ----------------------------------------------------------
       Section: Contact
       ---------------------------------------------------------- */
    $wp_customize->add_section('theme_associatif_contact', array(
        'title'    => esc_html__('Contact Information', 'theme-associatif'),
        'panel'    => 'theme_associatif',
        'priority' => 70,
    ));

    $contact_settings = array(
        'contact_email'         => array('label' => esc_html__('Email Address', 'theme-associatif'),       'type' => 'email',    'sanitize' => 'sanitize_email'),
        'contact_phone'         => array('label' => esc_html__('Phone Number', 'theme-associatif'),        'type' => 'text',     'sanitize' => 'sanitize_text_field'),
        'contact_address'       => array('label' => esc_html__('Physical Address', 'theme-associatif'),    'type' => 'textarea', 'sanitize' => 'sanitize_textarea_field'),
        'contact_map_embed_url' => array('label' => esc_html__('Map Embed URL (Google Maps)', 'theme-associatif'), 'type' => 'url', 'sanitize' => 'esc_url_raw'),
        'contact_cf7_id'        => array('label' => esc_html__('Contact Form 7 Form ID', 'theme-associatif'), 'type' => 'number', 'sanitize' => 'absint'),
    );

    foreach ($contact_settings as $setting_id => $config) {
        $wp_customize->add_setting($setting_id, array(
            'default'           => '',
            'sanitize_callback' => $config['sanitize'],
        ));
        $wp_customize->add_control($setting_id, array(
            'label'   => $config['label'],
            'section' => 'theme_associatif_contact',
            'type'    => $config['type'],
        ));
    }
}
add_action('customize_register', 'theme_associatif_customizer');

/**
 * Outputs inline CSS to apply the Customizer-selected primary color
 * as a CSS custom property override on :root.
 */
function theme_associatif_customizer_css(): void {
    $primary   = get_theme_mod('color_primary', '#6366F1');
    $secondary = get_theme_mod('color_secondary', '#EC4899');
    $accent    = get_theme_mod('color_accent', '#14B8A6');

    $has_custom_colors = (
        '#6366F1' !== $primary ||
        '#EC4899' !== $secondary ||
        '#14B8A6' !== $accent
    );

    if (!$has_custom_colors) {
        return;
    }

    printf(
        '<style id="theme-associatif-custom-colors">:root{--color-primary:%s;--color-secondary:%s;--color-accent:%s;}</style>' . "\n",
        esc_attr($primary),
        esc_attr($secondary),
        esc_attr($accent)
    );
}
add_action('wp_head', 'theme_associatif_customizer_css', 99);
