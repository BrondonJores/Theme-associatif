<?php
/**
 * Template Part: Button Component
 *
 * Renders a reusable, accessible button or link element.
 *
 * Accepted $args keys:
 *   string       $text          Button label text. Required.
 *   string       $url           When set, renders an <a> instead of <button>.
 *   string       $variant       'primary' | 'secondary' | 'outline' | 'ghost'. Default 'primary'.
 *   string       $size          'sm' | 'md' | 'lg'. Default 'md'.
 *   string       $icon          SVG markup or icon class to prepend inside the button.
 *   string       $icon_position 'left' | 'right'. Default 'left'.
 *   string       $type          HTML button type attribute. Default 'button'.
 *   bool         $disabled      Renders the disabled state. Default false.
 *   string       $extra_classes Additional CSS classes.
 *   string       $aria_label    Overrides the accessible label when icon-only.
 *   array        $attributes    Additional HTML attributes as key => value pairs.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

$args = $args ?? array();

$text          = isset($args['text'])          ? $args['text']          : '';
$url           = isset($args['url'])           ? $args['url']           : '';
$variant       = isset($args['variant'])       ? $args['variant']       : 'primary';
$size          = isset($args['size'])          ? $args['size']          : 'md';
$icon          = isset($args['icon'])          ? $args['icon']          : '';
$icon_position = isset($args['icon_position']) ? $args['icon_position'] : 'left';
$type          = isset($args['type'])          ? $args['type']          : 'button';
$disabled      = isset($args['disabled'])      ? (bool) $args['disabled'] : false;
$extra_classes = isset($args['extra_classes']) ? $args['extra_classes'] : '';
$aria_label    = isset($args['aria_label'])    ? $args['aria_label']    : '';
$attributes    = isset($args['attributes'])    ? (array) $args['attributes'] : array();

// Validate variant against allowed values.
$allowed_variants = array('primary', 'secondary', 'outline', 'ghost', 'danger');
if (!in_array($variant, $allowed_variants, true)) {
    $variant = 'primary';
}

// Validate size against allowed values.
$allowed_sizes = array('sm', 'md', 'lg');
if (!in_array($size, $allowed_sizes, true)) {
    $size = 'md';
}

// Build the CSS class list.
$classes = array('btn');
$classes[] = 'btn--' . $variant;

if ('md' !== $size) {
    $classes[] = 'btn--' . $size;
}

if ($disabled) {
    $classes[] = 'btn--disabled';
}

if (!empty($icon) && empty($text)) {
    $classes[] = 'btn--icon-only';
}

if (!empty($extra_classes)) {
    $classes[] = $extra_classes;
}

$class_string = implode(' ', array_filter($classes));

// Build extra attributes string.
$extra_attrs = '';
foreach ($attributes as $attr_name => $attr_value) {
    $extra_attrs .= ' ' . esc_attr($attr_name) . '="' . esc_attr($attr_value) . '"';
}

// Determine whether this is a link or a button element.
$is_link = !empty($url);
?>
<?php if ($is_link) : ?>
    <a
        href="<?php echo esc_url($url); ?>"
        class="<?php echo esc_attr($class_string); ?>"
        <?php if ($disabled) : ?>aria-disabled="true" tabindex="-1"<?php endif; ?>
        <?php if (!empty($aria_label)) : ?>aria-label="<?php echo esc_attr($aria_label); ?>"<?php endif; ?>
        <?php echo $extra_attrs; // Attributes are pre-escaped above. ?>
    >
        <?php if (!empty($icon) && 'left' === $icon_position) : ?>
            <span class="btn__icon btn__icon--left" aria-hidden="true"><?php echo $icon; // Icon SVG markup. ?></span>
        <?php endif; ?>

        <?php if (!empty($text)) : ?>
            <span class="btn__text"><?php echo esc_html($text); ?></span>
        <?php endif; ?>

        <?php if (!empty($icon) && 'right' === $icon_position) : ?>
            <span class="btn__icon btn__icon--right" aria-hidden="true"><?php echo $icon; // Icon SVG markup. ?></span>
        <?php endif; ?>
    </a>
<?php else : ?>
    <button
        type="<?php echo esc_attr($type); ?>"
        class="<?php echo esc_attr($class_string); ?>"
        <?php if ($disabled) : ?>disabled aria-disabled="true"<?php endif; ?>
        <?php if (!empty($aria_label)) : ?>aria-label="<?php echo esc_attr($aria_label); ?>"<?php endif; ?>
        <?php echo $extra_attrs; // Attributes are pre-escaped above. ?>
    >
        <?php if (!empty($icon) && 'left' === $icon_position) : ?>
            <span class="btn__icon btn__icon--left" aria-hidden="true"><?php echo $icon; // Icon SVG markup. ?></span>
        <?php endif; ?>

        <?php if (!empty($text)) : ?>
            <span class="btn__text"><?php echo esc_html($text); ?></span>
        <?php endif; ?>

        <?php if (!empty($icon) && 'right' === $icon_position) : ?>
            <span class="btn__icon btn__icon--right" aria-hidden="true"><?php echo $icon; // Icon SVG markup. ?></span>
        <?php endif; ?>
    </button>
<?php endif; ?>
