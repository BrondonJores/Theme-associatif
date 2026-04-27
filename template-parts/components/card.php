<?php
/**
 * Template Part: Card Component
 *
 * Renders a reusable content card.
 *
 * Accepted $args keys:
 *   string       $title         Card heading text. Required.
 *   string       $content       Card body text or HTML excerpt.
 *   string       $image         Image URL for the card thumbnail.
 *   string       $image_alt     Alt text for the thumbnail image. Default ''.
 *   string       $url           URL the card links to.
 *   array        $meta          Associative array with optional 'date' and 'author' keys.
 *   string       $variant       'default' | 'featured' | 'horizontal'. Default 'default'.
 *   array        $badge         Associative array with 'text' and optional 'variant' keys.
 *   string       $extra_classes Additional CSS classes for the card wrapper.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

$args = $args ?? array();

$title         = isset($args['title'])         ? $args['title']         : '';
$content       = isset($args['content'])       ? $args['content']       : '';
$image         = isset($args['image'])         ? $args['image']         : '';
$image_alt     = isset($args['image_alt'])     ? $args['image_alt']     : '';
$url           = isset($args['url'])           ? $args['url']           : '';
$meta          = isset($args['meta'])          ? (array) $args['meta']  : array();
$variant       = isset($args['variant'])       ? $args['variant']       : 'default';
$badge         = isset($args['badge'])         ? (array) $args['badge'] : array();
$extra_classes = isset($args['extra_classes']) ? $args['extra_classes'] : '';

$allowed_variants = array('default', 'featured', 'horizontal', 'compact');
if (!in_array($variant, $allowed_variants, true)) {
    $variant = 'default';
}

$classes = array('card');
$classes[] = 'card--' . $variant;

if (!empty($extra_classes)) {
    $classes[] = $extra_classes;
}

$class_string = implode(' ', array_filter($classes));

$badge_text    = isset($badge['text'])    ? $badge['text']    : '';
$badge_variant = isset($badge['variant']) ? $badge['variant'] : 'primary';

$date   = isset($meta['date'])   ? $meta['date']   : '';
$author = isset($meta['author']) ? $meta['author'] : '';
?>
<article class="<?php echo esc_attr($class_string); ?>">

    <?php if (!empty($image)) : ?>
        <div class="card__media">
            <?php if (!empty($url)) : ?>
                <a href="<?php echo esc_url($url); ?>" class="card__media-link" tabindex="-1" aria-hidden="true">
            <?php endif; ?>

            <img
                src="<?php echo esc_url($image); ?>"
                alt="<?php echo esc_attr($image_alt); ?>"
                class="card__image"
                loading="lazy"
                decoding="async"
            />

            <?php if (!empty($badge_text)) : ?>
                <span class="card__badge badge badge--<?php echo esc_attr($badge_variant); ?>">
                    <?php echo esc_html($badge_text); ?>
                </span>
            <?php endif; ?>

            <?php if (!empty($url)) : ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="card__body">

        <?php if (!empty($badge_text) && empty($image)) : ?>
            <span class="card__badge badge badge--<?php echo esc_attr($badge_variant); ?>">
                <?php echo esc_html($badge_text); ?>
            </span>
        <?php endif; ?>

        <?php if (!empty($meta) && (!empty($date) || !empty($author))) : ?>
            <div class="card__meta">
                <?php if (!empty($date)) : ?>
                    <time class="card__date" datetime="<?php echo esc_attr($date); ?>">
                        <?php echo esc_html($date); ?>
                    </time>
                <?php endif; ?>

                <?php if (!empty($date) && !empty($author)) : ?>
                    <span class="card__meta-separator" aria-hidden="true">&middot;</span>
                <?php endif; ?>

                <?php if (!empty($author)) : ?>
                    <span class="card__author"><?php echo esc_html($author); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($title)) : ?>
            <h3 class="card__title">
                <?php if (!empty($url)) : ?>
                    <a href="<?php echo esc_url($url); ?>" class="card__title-link">
                        <?php echo esc_html($title); ?>
                    </a>
                <?php else : ?>
                    <?php echo esc_html($title); ?>
                <?php endif; ?>
            </h3>
        <?php endif; ?>

        <?php if (!empty($content)) : ?>
            <div class="card__content">
                <?php echo wp_kses_post($content); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($url)) : ?>
            <div class="card__footer">
                <a href="<?php echo esc_url($url); ?>" class="card__cta" aria-label="<?php echo esc_attr(sprintf(__('Read more about %s', 'theme-associatif'), $title)); ?>">
                    <?php esc_html_e('Read more', 'theme-associatif'); ?>
                    <span class="card__cta-arrow" aria-hidden="true">&rarr;</span>
                </a>
            </div>
        <?php endif; ?>

    </div>

</article>
