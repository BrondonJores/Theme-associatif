<?php
/**
 * Template Tags
 *
 * Helper functions used in WordPress theme templates to output
 * post metadata, thumbnails, excerpts and navigation.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Outputs the posted-on date for a post.
 *
 * @param int|null $post_id Optional post ID. Defaults to the current post.
 */
function theme_associatif_posted_on(?int $post_id = null): void {
    $time_string = '<time class="entry-meta__date" datetime="%1$s">%2$s</time>';

    $time_string = sprintf(
        $time_string,
        esc_attr(get_the_date(DATE_W3C, $post_id)),
        esc_html(get_the_date('', $post_id))
    );

    printf(
        '<span class="entry-meta__posted-on">%1$s %2$s</span>',
        '<svg class="entry-meta__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>',
        $time_string
    );
}

/**
 * Outputs the posted-by author for a post.
 *
 * @param int|null $post_id Optional post ID. Defaults to the current post.
 */
function theme_associatif_posted_by(?int $post_id = null): void {
    $author_id = get_post_field('post_author', $post_id ?? get_the_ID());

    printf(
        '<span class="entry-meta__author">%1$s <a class="entry-meta__author-link" href="%2$s" rel="author">%3$s</a></span>',
        '<svg class="entry-meta__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
        esc_url(get_author_posts_url($author_id)),
        esc_html(get_the_author_meta('display_name', $author_id))
    );
}

/**
 * Outputs the post thumbnail wrapped in a linked container.
 *
 * @param string   $size    WordPress image size. Default 'post-thumbnail'.
 * @param int|null $post_id Optional post ID.
 */
function theme_associatif_thumbnail(string $size = 'post-thumbnail', ?int $post_id = null): void {
    $target_id = $post_id ?? get_the_ID();

    if (!has_post_thumbnail($target_id)) {
        return;
    }

    ?>
    <div class="entry-thumbnail">
        <a href="<?php echo esc_url(get_the_permalink($target_id)); ?>" tabindex="-1" aria-hidden="true">
            <?php echo get_the_post_thumbnail($target_id, $size, array('class' => 'entry-thumbnail__img')); ?>
        </a>
    </div>
    <?php
}

/**
 * Returns the post excerpt, trimmed to a specified word count.
 *
 * Does not output anything; use echo to display.
 *
 * @param int      $word_count Maximum number of words. Default 30.
 * @param int|null $post_id    Optional post ID.
 * @return string The trimmed excerpt.
 */
function theme_associatif_excerpt(int $word_count = 30, ?int $post_id = null): string {
    $target_id = $post_id ?? get_the_ID();
    $post      = get_post($target_id);

    if (!$post) {
        return '';
    }

    if (!empty($post->post_excerpt)) {
        return wp_trim_words($post->post_excerpt, $word_count, '&hellip;');
    }

    return wp_trim_words(
        wp_strip_all_tags(apply_filters('the_content', $post->post_content)),
        $word_count,
        '&hellip;'
    );
}

/**
 * Outputs post navigation (previous / next) links.
 *
 * @param array $args Optional arguments passed to get_the_post_navigation().
 */
function theme_associatif_the_post_navigation(array $args = array()): void {
    $defaults = array(
        'prev_text'          => '<span class="nav-subtitle">' . esc_html__('Previous', 'theme-associatif') . '</span><span class="nav-title">%title</span>',
        'next_text'          => '<span class="nav-subtitle">' . esc_html__('Next', 'theme-associatif') . '</span><span class="nav-title">%title</span>',
        'in_same_term'       => false,
        'excluded_terms'     => '',
        'taxonomy'           => 'category',
        'screen_reader_text' => esc_html__('Post navigation', 'theme-associatif'),
        'aria_label'         => esc_html__('Post navigation', 'theme-associatif'),
        'class'              => 'post-navigation',
    );

    the_post_navigation(wp_parse_args($args, $defaults));
}

/**
 * Returns a formatted human-readable time difference (e.g. "3 days ago").
 *
 * @param int|null $post_id Optional post ID.
 * @return string Human-readable date difference string.
 */
function theme_associatif_human_time_diff(?int $post_id = null): string {
    $target_id  = $post_id ?? get_the_ID();
    $post_time  = get_post_time('U', true, $target_id);
    $current    = time();
    $difference = $current - $post_time;

    // Show exact date if older than 30 days.
    if ($difference > (30 * DAY_IN_SECONDS)) {
        return get_the_date('', $target_id);
    }

    return sprintf(
        /* translators: %s: human-readable time difference. */
        esc_html__('%s ago', 'theme-associatif'),
        human_time_diff($post_time, $current)
    );
}

/**
 * Outputs the breadcrumb trail for a post or page.
 *
 * Uses Yoast SEO breadcrumbs if available, otherwise renders a simple trail.
 */
function theme_associatif_breadcrumbs(): void {
    if (function_exists('yoast_breadcrumb')) {
        yoast_breadcrumb('<nav class="breadcrumbs" aria-label="' . esc_attr__('Breadcrumb', 'theme-associatif') . '">', '</nav>');
        return;
    }

    $items = array();
    $items[] = sprintf(
        '<li class="breadcrumbs__item"><a href="%s">%s</a></li>',
        esc_url(home_url('/')),
        esc_html__('Home', 'theme-associatif')
    );

    if (is_singular()) {
        $categories = get_the_category();

        if (!empty($categories)) {
            $items[] = sprintf(
                '<li class="breadcrumbs__item"><a href="%s">%s</a></li>',
                esc_url(get_category_link($categories[0]->term_id)),
                esc_html($categories[0]->name)
            );
        }

        $items[] = sprintf(
            '<li class="breadcrumbs__item breadcrumbs__item--current" aria-current="page">%s</li>',
            esc_html(get_the_title())
        );
    } elseif (is_archive()) {
        $items[] = sprintf(
            '<li class="breadcrumbs__item breadcrumbs__item--current" aria-current="page">%s</li>',
            esc_html(get_the_archive_title())
        );
    } elseif (is_search()) {
        $items[] = sprintf(
            '<li class="breadcrumbs__item breadcrumbs__item--current" aria-current="page">%s</li>',
            esc_html(sprintf(__('Search: %s', 'theme-associatif'), get_search_query()))
        );
    }

    if (count($items) > 1) {
        printf(
            '<nav class="breadcrumbs" aria-label="%s"><ol class="breadcrumbs__list" role="list">%s</ol></nav>',
            esc_attr__('Breadcrumb', 'theme-associatif'),
            implode('', $items)
        );
    }
}
