<?php
/**
 * Enqueue Assets
 *
 * Registers and enqueues Google Fonts, the main stylesheet,
 * and the main JavaScript module for the theme.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueues all front-end scripts and styles.
 */
function theme_associatif_enqueue_assets(): void {
    $version = defined('THEME_ASSOCIATIF_VERSION') ? THEME_ASSOCIATIF_VERSION : '1.0.0';
    $uri     = defined('THEME_ASSOCIATIF_URI')     ? THEME_ASSOCIATIF_URI     : get_template_directory_uri();

    // Google Fonts: Poppins (headings) and Inter (body).
    wp_enqueue_style(
        'theme-associatif-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap',
        array(),
        null // No version for external URL.
    );

    // Main theme stylesheet (imports design-system + components + layout).
    wp_enqueue_style(
        'theme-associatif-main',
        $uri . '/assets/css/main.css',
        array('theme-associatif-google-fonts'),
        $version
    );

    // WordPress core comment-reply script (only on singular pages with comments).
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }

    // Main JavaScript module.
    wp_enqueue_script(
        'theme-associatif-main',
        $uri . '/assets/js/main.js',
        array(),
        $version,
        array(
            'in_footer' => true,
            'strategy'  => 'defer',
        )
    );

    // Mark the main script as a module so it can use ES6 imports.
    add_filter('script_loader_tag', 'theme_associatif_add_module_type', 10, 3);
}
add_action('wp_enqueue_scripts', 'theme_associatif_enqueue_assets');

/**
 * Adds type="module" to the main theme script tag.
 *
 * @param string $tag    The complete HTML <script> tag.
 * @param string $handle The script's registered handle.
 * @param string $src    The script's source URL.
 * @return string Modified script tag.
 */
function theme_associatif_add_module_type(string $tag, string $handle, string $src): string {
    if ('theme-associatif-main' !== $handle) {
        return $tag;
    }

    return str_replace(' src=', ' type="module" src=', $tag);
}

/**
 * Enqueues block editor styles for the WordPress block editor (Gutenberg).
 */
function theme_associatif_enqueue_block_editor_assets(): void {
    $version = defined('THEME_ASSOCIATIF_VERSION') ? THEME_ASSOCIATIF_VERSION : '1.0.0';
    $uri     = defined('THEME_ASSOCIATIF_URI')     ? THEME_ASSOCIATIF_URI     : get_template_directory_uri();

    wp_enqueue_style(
        'theme-associatif-editor',
        $uri . '/assets/css/main.css',
        array(),
        $version
    );
}
add_action('enqueue_block_editor_assets', 'theme_associatif_enqueue_block_editor_assets');

/**
 * Outputs an inline script to apply the persisted theme before paint,
 * preventing a flash of incorrect theme on initial page load.
 */
function theme_associatif_inline_theme_script(): void {
    ?>
    <script>
        (function () {
            var stored = '';
            try { stored = localStorage.getItem('theme-associatif-theme') || ''; } catch (e) {}
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            var theme = stored === 'dark' || stored === 'light' ? stored : (prefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <?php
}
add_action('wp_head', 'theme_associatif_inline_theme_script', 1);
