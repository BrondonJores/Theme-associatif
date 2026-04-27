<?php
/**
 * Template Name: Events
 *
 * Events listing page with filtering by category and date.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

get_header();

// Retrieve filter parameters from the request.
$selected_category = isset($_GET['event_category']) ? sanitize_text_field(wp_unslash($_GET['event_category'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$selected_period   = isset($_GET['event_period'])   ? sanitize_text_field(wp_unslash($_GET['event_period']))   : 'upcoming'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// Build the query based on selected period.
$meta_query = array();
$today      = gmdate('Y-m-d');

if ('past' === $selected_period) {
    $meta_query[] = array(
        'key'     => '_event_date',
        'value'   => $today,
        'compare' => '<',
        'type'    => 'DATE',
    );
} else {
    $meta_query[] = array(
        'relation' => 'OR',
        array(
            'key'     => '_event_date',
            'value'   => $today,
            'compare' => '>=',
            'type'    => 'DATE',
        ),
        array(
            'key'     => '_event_date',
            'compare' => 'NOT EXISTS',
        ),
    );
}

$query_args = array(
    'post_type'      => 'post',
    'posts_per_page' => 12,
    'paged'          => max(1, get_query_var('paged')),
    'orderby'        => 'date',
    'order'          => 'ASC',
    'meta_query'     => $meta_query,
);

if (!empty($selected_category)) {
    $query_args['category_name'] = $selected_category;
}

$events_query = new WP_Query($query_args);

// Retrieve all categories for the filter form.
$categories = get_categories(array('hide_empty' => true));
?>

<main id="main-content" class="site-main">
    <div class="container">

        <header class="page-header" aria-label="<?php esc_attr_e('Events page header', 'theme-associatif'); ?>">
            <?php the_title('<h1 class="page-title">', '</h1>'); ?>
            <?php the_content(); ?>
        </header>

        <!-- Filter Bar -->
        <div class="events-filter" role="search" aria-label="<?php esc_attr_e('Filter events', 'theme-associatif'); ?>">
            <form class="events-filter__form" method="get" action="<?php echo esc_url(get_permalink()); ?>">

                <div class="events-filter__group">
                    <label for="event_category" class="events-filter__label">
                        <?php esc_html_e('Category', 'theme-associatif'); ?>
                    </label>
                    <select id="event_category" name="event_category" class="form-control events-filter__select">
                        <option value=""><?php esc_html_e('All categories', 'theme-associatif'); ?></option>
                        <?php foreach ($categories as $cat) : ?>
                            <option
                                value="<?php echo esc_attr($cat->slug); ?>"
                                <?php selected($selected_category, $cat->slug); ?>
                            >
                                <?php echo esc_html($cat->name); ?>
                                (<?php echo esc_html($cat->count); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="events-filter__group">
                    <label for="event_period" class="events-filter__label">
                        <?php esc_html_e('Period', 'theme-associatif'); ?>
                    </label>
                    <select id="event_period" name="event_period" class="form-control events-filter__select">
                        <option value="upcoming" <?php selected($selected_period, 'upcoming'); ?>>
                            <?php esc_html_e('Upcoming', 'theme-associatif'); ?>
                        </option>
                        <option value="past" <?php selected($selected_period, 'past'); ?>>
                            <?php esc_html_e('Past events', 'theme-associatif'); ?>
                        </option>
                        <option value="all" <?php selected($selected_period, 'all'); ?>>
                            <?php esc_html_e('All events', 'theme-associatif'); ?>
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn--primary events-filter__submit">
                    <?php esc_html_e('Filter', 'theme-associatif'); ?>
                </button>

                <?php if (!empty($selected_category) || 'upcoming' !== $selected_period) : ?>
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn--ghost events-filter__reset">
                        <?php esc_html_e('Reset', 'theme-associatif'); ?>
                    </a>
                <?php endif; ?>

            </form>
        </div>

        <?php if ($events_query->have_posts()) : ?>

            <div class="grid grid-cols-1 grid-cols-2@sm grid-cols-3@lg gap-8">
                <?php
                while ($events_query->have_posts()) :
                    $events_query->the_post();
                    $cat  = get_the_category();

                    get_template_part('template-parts/components/event-card', null, array(
                        'title'       => get_the_title(),
                        'date'        => get_the_date(),
                        'date_iso'    => get_the_date('Y-m-d'),
                        'time'        => get_post_meta(get_the_ID(), '_event_time', true),
                        'location'    => get_post_meta(get_the_ID(), '_event_location', true),
                        'description' => theme_associatif_excerpt(20),
                        'image'       => get_the_post_thumbnail_url(get_the_ID(), 'medium_large'),
                        'image_alt'   => get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true),
                        'url'         => get_the_permalink(),
                        'category'    => !empty($cat) ? $cat[0]->name : '',
                    ));
                endwhile;

                wp_reset_postdata();
                ?>
            </div>

            <?php
            echo paginate_links(array(
                'total'   => $events_query->max_num_pages,
                'current' => max(1, get_query_var('paged')),
                'format'  => '?paged=%#%',
            ));
            ?>

        <?php else : ?>

            <div class="events-no-results">
                <p><?php esc_html_e('No events found for the selected filters.', 'theme-associatif'); ?></p>
                <a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn--outline">
                    <?php esc_html_e('View all events', 'theme-associatif'); ?>
                </a>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
