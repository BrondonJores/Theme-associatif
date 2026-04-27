<?php
/**
 * Template Part: Event Card Component
 *
 * Renders a card specifically designed to display event information.
 *
 * Accepted $args keys:
 *   string $title            Event title. Required.
 *   string $date             Formatted display date string.
 *   string $date_iso         ISO 8601 date for <time datetime=""> attribute.
 *   string $time             Event time string (e.g. "14:00 - 17:00").
 *   string $location         Venue or location name.
 *   string $description      Short event description text.
 *   string $image            URL for the event thumbnail image.
 *   string $image_alt        Alt text for the thumbnail. Default ''.
 *   string $url              Link to the full event page.
 *   string $category         Event category label.
 *   string $category_variant Badge color variant for the category. Default 'primary'.
 *   int    $seats_remaining  Number of seats left. 0 means no seats display.
 *   bool   $is_featured      Mark card as featured. Default false.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

$args = $args ?? array();

$title            = isset($args['title'])            ? $args['title']            : '';
$date             = isset($args['date'])             ? $args['date']             : '';
$date_iso         = isset($args['date_iso'])         ? $args['date_iso']         : $date;
$time             = isset($args['time'])             ? $args['time']             : '';
$location         = isset($args['location'])         ? $args['location']         : '';
$description      = isset($args['description'])      ? $args['description']      : '';
$image            = isset($args['image'])            ? $args['image']            : '';
$image_alt        = isset($args['image_alt'])        ? $args['image_alt']        : '';
$url              = isset($args['url'])              ? $args['url']              : '';
$category         = isset($args['category'])         ? $args['category']         : '';
$category_variant = isset($args['category_variant']) ? $args['category_variant'] : 'primary';
$seats_remaining  = isset($args['seats_remaining'])  ? (int) $args['seats_remaining'] : 0;
$is_featured      = isset($args['is_featured'])      ? (bool) $args['is_featured'] : false;

$card_classes = array('event-card', 'card');
if ($is_featured) {
    $card_classes[] = 'event-card--featured';
}

$seats_low = $seats_remaining > 0 && $seats_remaining <= 5;
?>
<article class="<?php echo esc_attr(implode(' ', $card_classes)); ?>">

    <?php if (!empty($image)) : ?>
        <div class="event-card__media card__media">
            <?php if (!empty($url)) : ?>
                <a href="<?php echo esc_url($url); ?>" class="card__media-link" tabindex="-1" aria-hidden="true">
            <?php endif; ?>

            <img
                src="<?php echo esc_url($image); ?>"
                alt="<?php echo esc_attr($image_alt); ?>"
                class="event-card__image card__image"
                loading="lazy"
                decoding="async"
            />

            <?php if (!empty($category)) : ?>
                <span class="event-card__category card__badge badge badge--<?php echo esc_attr($category_variant); ?>">
                    <?php echo esc_html($category); ?>
                </span>
            <?php endif; ?>

            <?php if ($seats_low) : ?>
                <span class="event-card__seats-badge badge badge--warning">
                    <?php
                    echo esc_html(
                        sprintf(
                            /* translators: %d: number of seats remaining. */
                            _n('%d seat left', '%d seats left', $seats_remaining, 'theme-associatif'),
                            $seats_remaining
                        )
                    );
                    ?>
                </span>
            <?php endif; ?>

            <?php if (!empty($url)) : ?>
                </a>
            <?php endif; ?>
        </div>
    <?php elseif (!empty($category)) : ?>
        <div class="event-card__category-wrap">
            <span class="event-card__category badge badge--<?php echo esc_attr($category_variant); ?>">
                <?php echo esc_html($category); ?>
            </span>
        </div>
    <?php endif; ?>

    <div class="event-card__body card__body">

        <?php if (!empty($date) || !empty($time)) : ?>
            <div class="event-card__datetime">
                <?php if (!empty($date)) : ?>
                    <time class="event-card__date" datetime="<?php echo esc_attr($date_iso); ?>">
                        <svg class="event-card__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <?php echo esc_html($date); ?>
                    </time>
                <?php endif; ?>

                <?php if (!empty($time)) : ?>
                    <span class="event-card__time">
                        <svg class="event-card__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <?php echo esc_html($time); ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($title)) : ?>
            <h3 class="event-card__title card__title">
                <?php if (!empty($url)) : ?>
                    <a href="<?php echo esc_url($url); ?>" class="card__title-link">
                        <?php echo esc_html($title); ?>
                    </a>
                <?php else : ?>
                    <?php echo esc_html($title); ?>
                <?php endif; ?>
            </h3>
        <?php endif; ?>

        <?php if (!empty($location)) : ?>
            <p class="event-card__location">
                <svg class="event-card__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <?php echo esc_html($location); ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($description)) : ?>
            <p class="event-card__description card__content">
                <?php echo esc_html($description); ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($url)) : ?>
            <div class="event-card__footer card__footer">
                <a
                    href="<?php echo esc_url($url); ?>"
                    class="event-card__cta card__cta"
                    aria-label="<?php echo esc_attr(sprintf(__('View details for %s', 'theme-associatif'), $title)); ?>"
                >
                    <?php esc_html_e('View details', 'theme-associatif'); ?>
                    <span aria-hidden="true">&rarr;</span>
                </a>

                <?php if ($seats_remaining > 0 && !$seats_low) : ?>
                    <span class="event-card__seats badge badge--success badge--sm">
                        <?php esc_html_e('Seats available', 'theme-associatif'); ?>
                    </span>
                <?php elseif ($seats_remaining === 0 && isset($args['seats_remaining'])) : ?>
                    <span class="event-card__seats badge badge--error badge--sm">
                        <?php esc_html_e('Fully booked', 'theme-associatif'); ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

</article>
