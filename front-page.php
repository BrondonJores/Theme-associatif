<?php
/**
 * WordPress Front Page Template
 *
 * Renders the site home page with hero section, featured events,
 * association stats, and latest news sections.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

get_header();

// Hero section data from Customizer settings.
$hero_title       = get_theme_mod('hero_title', get_bloginfo('name'));
$hero_subtitle    = get_theme_mod('hero_subtitle', '');
$hero_description = get_theme_mod('hero_description', get_bloginfo('description'));
$hero_cta_text    = get_theme_mod('hero_cta_primary_text', __('Discover our events', 'theme-associatif'));
$hero_cta_url     = get_theme_mod('hero_cta_primary_url', '');
$hero_cta2_text   = get_theme_mod('hero_cta_secondary_text', __('Join us', 'theme-associatif'));
$hero_cta2_url    = get_theme_mod('hero_cta_secondary_url', '');
$hero_image       = get_theme_mod('hero_image', '');
$hero_badge       = get_theme_mod('hero_badge_text', '');
?>

<main id="main-content" class="site-main">

    <!-- Hero Section -->
    <?php
    get_template_part('template-parts/components/hero', null, array(
        'title'         => $hero_title,
        'subtitle'      => $hero_subtitle,
        'description'   => $hero_description,
        'cta_primary'   => array(
            'text' => $hero_cta_text,
            'url'  => !empty($hero_cta_url) ? $hero_cta_url : get_permalink(get_option('page_for_posts')),
        ),
        'cta_secondary' => array(
            'text' => $hero_cta2_text,
            'url'  => !empty($hero_cta2_url) ? $hero_cta2_url : '',
        ),
        'image'         => $hero_image,
        'badge_text'    => $hero_badge,
        'variant'       => !empty($hero_image) ? 'split' : 'centered',
    ));
    ?>

    <!-- Featured Events Section -->
    <?php
    $events_args = array(
        'post_type'      => 'post',
        'posts_per_page' => 3,
        'meta_key'       => '_event_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array(
            array(
                'key'     => '_event_date',
                'value'   => gmdate('Y-m-d'),
                'compare' => '>=',
                'type'    => 'DATE',
            ),
        ),
    );

    $events_query = new WP_Query($events_args);

    // Fallback: show latest posts when no events with meta exist.
    if (!$events_query->have_posts()) {
        $events_query = new WP_Query(array(
            'posts_per_page' => 3,
            'category_name'  => 'events',
        ));
    }
    ?>

    <section class="section section--events" aria-labelledby="events-heading">
        <div class="container">
            <div class="section-header" data-animate="fade-up">
                <h2 id="events-heading" class="section-title">
                    <?php esc_html_e('Upcoming Events', 'theme-associatif'); ?>
                </h2>
                <p class="section-description">
                    <?php esc_html_e('Join us for our upcoming activities and workshops.', 'theme-associatif'); ?>
                </p>
            </div>

            <?php if ($events_query->have_posts()) : ?>
                <div class="grid grid-cols-1 grid-cols-2@sm grid-cols-3@lg gap-8">
                    <?php
                    $event_index = 0;
                    while ($events_query->have_posts()) :
                        $events_query->the_post();
                    ?>
                        <div data-animate="fade-up" data-animate-delay="<?php echo esc_attr($event_index * 100); ?>">
                            <?php
                            get_template_part('template-parts/components/event-card', null, array(
                                'title'       => get_the_title(),
                                'date'        => get_the_date(),
                                'description' => theme_associatif_excerpt(20),
                                'image'       => get_the_post_thumbnail_url(get_the_ID(), 'medium_large'),
                                'url'         => get_the_permalink(),
                                'category'    => get_the_category() ? get_the_category()[0]->name : '',
                            ));
                            ?>
                        </div>
                    <?php
                        $event_index++;
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>

                <div class="section-footer">
                    <?php
                    get_template_part('template-parts/components/button', null, array(
                        'text'    => __('View all events', 'theme-associatif'),
                        'url'     => get_permalink(get_page_by_path('events')),
                        'variant' => 'outline',
                    ));
                    ?>
                </div>
            <?php else : ?>
                <p class="section-empty"><?php esc_html_e('No upcoming events at this time.', 'theme-associatif'); ?></p>
            <?php endif; ?>

        </div>
    </section>

    <!-- Association Stats Section -->
    <section class="section section--stats section--bg" aria-labelledby="stats-heading">
        <div class="container">
            <h2 id="stats-heading" class="sr-only"><?php esc_html_e('Association statistics', 'theme-associatif'); ?></h2>

            <div class="stats-grid" role="list">
                <?php
                $stats = array(
                    array(
                        'value' => get_theme_mod('stat_members', '500'),
                        'label' => __('Active members', 'theme-associatif'),
                    ),
                    array(
                        'value' => get_theme_mod('stat_events', '50'),
                        'label' => __('Events per year', 'theme-associatif'),
                    ),
                    array(
                        'value' => get_theme_mod('stat_years', '10'),
                        'label' => __('Years of activity', 'theme-associatif'),
                    ),
                    array(
                        'value' => get_theme_mod('stat_projects', '30'),
                        'label' => __('Projects completed', 'theme-associatif'),
                    ),
                );

                foreach ($stats as $index => $stat) :
                    $numeric = (int) filter_var($stat['value'], FILTER_SANITIZE_NUMBER_INT);
                ?>
                    <div class="stat-item" role="listitem" data-animate="fade-up" data-animate-delay="<?php echo esc_attr($index * 100); ?>">
                        <span class="stat-item__value" data-counter="<?php echo esc_attr($numeric); ?>">
                            <?php echo esc_html($stat['value']); ?>
                        </span>
                        <span class="stat-item__label"><?php echo esc_html($stat['label']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Latest News Section -->
    <?php
    $news_query = new WP_Query(array(
        'posts_per_page' => 3,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
    ?>

    <section class="section section--news" aria-labelledby="news-heading">
        <div class="container">
            <div class="section-header" data-animate="fade-up">
                <h2 id="news-heading" class="section-title">
                    <?php esc_html_e('Latest News', 'theme-associatif'); ?>
                </h2>
                <p class="section-description">
                    <?php esc_html_e('Stay updated with the latest from our association.', 'theme-associatif'); ?>
                </p>
            </div>

            <?php if ($news_query->have_posts()) : ?>
                <div class="grid grid-cols-1 grid-cols-2@sm grid-cols-3@lg gap-8">
                    <?php
                    $news_index = 0;
                    while ($news_query->have_posts()) :
                        $news_query->the_post();
                    ?>
                        <div data-animate="fade-up" data-animate-delay="<?php echo esc_attr($news_index * 100); ?>">
                            <?php
                            get_template_part('template-parts/components/card', null, array(
                                'title'   => get_the_title(),
                                'content' => theme_associatif_excerpt(20),
                                'image'   => get_the_post_thumbnail_url(get_the_ID(), 'medium_large'),
                                'url'     => get_the_permalink(),
                                'meta'    => array(
                                    'date'   => get_the_date(),
                                    'author' => get_the_author(),
                                ),
                            ));
                            ?>
                        </div>
                    <?php
                        $news_index++;
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>

                <div class="section-footer">
                    <?php
                    get_template_part('template-parts/components/button', null, array(
                        'text'    => __('Read all articles', 'theme-associatif'),
                        'url'     => get_permalink(get_option('page_for_posts')),
                        'variant' => 'outline',
                    ));
                    ?>
                </div>
            <?php else : ?>
                <p class="section-empty"><?php esc_html_e('No articles published yet.', 'theme-associatif'); ?></p>
            <?php endif; ?>

        </div>
    </section>

    <!-- Front Page Custom Content (page editor content) -->
    <?php if (have_posts()) : the_post(); ?>
        <?php if (get_the_content()) : ?>
            <section class="section section--page-content">
                <div class="container">
                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>

</main>

<?php get_footer(); ?>
