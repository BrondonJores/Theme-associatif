<?php
/**
 * Template Part: Hero Section
 *
 * Full-width hero banner with title, subtitle, description, dual CTAs,
 * an optional background image, and an optional badge.
 *
 * Accepted $args keys:
 *   string $title           Main hero heading. Required.
 *   string $subtitle        Short eyebrow text displayed above the title.
 *   string $description     Paragraph text displayed below the title.
 *   array  $cta_primary     Associative array: 'text' and 'url'.
 *   array  $cta_secondary   Associative array: 'text' and 'url'.
 *   string $image           URL of the hero background/side image.
 *   string $image_alt       Alt text for the hero image. Default ''.
 *   string $badge_text      Short text displayed in a badge above the title.
 *   string $badge_variant   Badge color variant. Default 'primary'.
 *   string $variant         'centered' | 'split'. Default 'centered'.
 *   string $extra_classes   Additional CSS classes.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

$args = $args ?? array();

$title         = isset($args['title'])         ? $args['title']         : '';
$subtitle      = isset($args['subtitle'])      ? $args['subtitle']      : '';
$description   = isset($args['description'])   ? $args['description']   : '';
$cta_primary   = isset($args['cta_primary'])   ? (array) $args['cta_primary']   : array();
$cta_secondary = isset($args['cta_secondary']) ? (array) $args['cta_secondary'] : array();
$image         = isset($args['image'])         ? $args['image']         : '';
$image_alt     = isset($args['image_alt'])     ? $args['image_alt']     : '';
$badge_text    = isset($args['badge_text'])    ? $args['badge_text']    : '';
$badge_variant = isset($args['badge_variant']) ? $args['badge_variant'] : 'primary';
$variant       = isset($args['variant'])       ? $args['variant']       : 'centered';
$extra_classes = isset($args['extra_classes']) ? $args['extra_classes'] : '';

$allowed_variants = array('centered', 'split');
if (!in_array($variant, $allowed_variants, true)) {
    $variant = 'centered';
}

$has_image = !empty($image);
$is_split  = 'split' === $variant;

$classes = array('hero');
$classes[] = 'hero--' . $variant;

if ($has_image) {
    $classes[] = 'hero--has-image';
}

if (!empty($extra_classes)) {
    $classes[] = $extra_classes;
}

$class_string = implode(' ', array_filter($classes));

$cta_primary_text   = isset($cta_primary['text'])   ? $cta_primary['text']   : '';
$cta_primary_url    = isset($cta_primary['url'])     ? $cta_primary['url']    : '';
$cta_secondary_text = isset($cta_secondary['text'])  ? $cta_secondary['text'] : '';
$cta_secondary_url  = isset($cta_secondary['url'])   ? $cta_secondary['url']  : '';
?>
<section class="<?php echo esc_attr($class_string); ?>" aria-label="<?php esc_attr_e('Hero section', 'theme-associatif'); ?>">

    <?php if (!$is_split && $has_image) : ?>
        <div class="hero__bg-image" aria-hidden="true">
            <img
                src="<?php echo esc_url($image); ?>"
                alt=""
                class="hero__bg-img"
                loading="eager"
                decoding="async"
            />
            <div class="hero__overlay"></div>
        </div>
    <?php endif; ?>

    <div class="hero__inner container">

        <div class="hero__content" data-animate="fade-up">

            <?php if (!empty($badge_text)) : ?>
                <span class="badge badge--<?php echo esc_attr($badge_variant); ?> hero__badge" data-animate="fade-down" data-animate-delay="100">
                    <?php echo esc_html($badge_text); ?>
                </span>
            <?php endif; ?>

            <?php if (!empty($subtitle)) : ?>
                <p class="hero__subtitle"><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>

            <?php if (!empty($title)) : ?>
                <h1 class="hero__title"><?php echo esc_html($title); ?></h1>
            <?php endif; ?>

            <?php if (!empty($description)) : ?>
                <p class="hero__description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>

            <?php if (!empty($cta_primary_text) || !empty($cta_secondary_text)) : ?>
                <div class="hero__actions">
                    <?php if (!empty($cta_primary_text) && !empty($cta_primary_url)) : ?>
                        <?php
                        get_template_part('template-parts/components/button', null, array(
                            'text'    => $cta_primary_text,
                            'url'     => $cta_primary_url,
                            'variant' => 'primary',
                            'size'    => 'lg',
                        ));
                        ?>
                    <?php endif; ?>

                    <?php if (!empty($cta_secondary_text) && !empty($cta_secondary_url)) : ?>
                        <?php
                        get_template_part('template-parts/components/button', null, array(
                            'text'    => $cta_secondary_text,
                            'url'     => $cta_secondary_url,
                            'variant' => 'outline',
                            'size'    => 'lg',
                        ));
                        ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>

        <?php if ($is_split && $has_image) : ?>
            <div class="hero__media" data-animate="fade-left" data-animate-delay="200">
                <img
                    src="<?php echo esc_url($image); ?>"
                    alt="<?php echo esc_attr($image_alt); ?>"
                    class="hero__image"
                    loading="eager"
                    decoding="async"
                />
            </div>
        <?php endif; ?>

    </div>
</section>
