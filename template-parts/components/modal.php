<?php
/**
 * Template Part: Modal Component
 *
 * Renders an accessible modal dialog. JavaScript in assets/js/modal.js
 * handles open/close, focus trapping, and keyboard interactions.
 *
 * Accepted $args keys:
 *   string $id      Unique HTML id for the modal. Required.
 *   string $title   Modal heading text. Required.
 *   string $content Modal body HTML content.
 *   string $footer  Optional footer HTML content (action buttons, etc.).
 *   string $size    'sm' | 'md' | 'lg' | 'xl'. Default 'md'.
 *
 * Usage example:
 *   get_template_part('template-parts/components/modal', null, array(
 *       'id'      => 'confirm-delete',
 *       'title'   => __('Confirm deletion', 'theme-associatif'),
 *       'content' => '<p>' . __('Are you sure?', 'theme-associatif') . '</p>',
 *       'footer'  => '<button data-modal-close class="btn btn--outline">Cancel</button>',
 *       'size'    => 'sm',
 *   ));
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

$args = $args ?? array();

$modal_id = isset($args['id'])      ? $args['id']      : '';
$title    = isset($args['title'])   ? $args['title']   : '';
$content  = isset($args['content']) ? $args['content'] : '';
$footer   = isset($args['footer'])  ? $args['footer']  : '';
$size     = isset($args['size'])    ? $args['size']    : 'md';

if (empty($modal_id)) {
    return;
}

$allowed_sizes = array('sm', 'md', 'lg', 'xl');
if (!in_array($size, $allowed_sizes, true)) {
    $size = 'md';
}

$dialog_id    = esc_attr($modal_id);
$title_id     = $dialog_id . '-title';
$described_id = !empty($content) ? $dialog_id . '-desc' : '';
?>
<div
    id="<?php echo $dialog_id; ?>"
    class="modal modal--<?php echo esc_attr($size); ?>"
    role="dialog"
    aria-modal="true"
    aria-labelledby="<?php echo esc_attr($title_id); ?>"
    <?php if (!empty($described_id)) : ?>
        aria-describedby="<?php echo esc_attr($described_id); ?>"
    <?php endif; ?>
    hidden
>
    <div class="modal__backdrop" aria-hidden="true"></div>

    <div class="modal__container">

        <div class="modal__header">
            <h2 id="<?php echo esc_attr($title_id); ?>" class="modal__title">
                <?php echo esc_html($title); ?>
            </h2>

            <button
                type="button"
                class="modal__close"
                data-modal-close
                aria-label="<?php esc_attr_e('Close dialog', 'theme-associatif'); ?>"
            >
                <svg
                    class="modal__close-icon"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    width="20"
                    height="20"
                    aria-hidden="true"
                    focusable="false"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <?php if (!empty($content)) : ?>
            <div
                id="<?php echo esc_attr($described_id); ?>"
                class="modal__body"
            >
                <?php echo wp_kses_post($content); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($footer)) : ?>
            <div class="modal__footer">
                <?php echo wp_kses_post($footer); ?>
            </div>
        <?php endif; ?>

    </div>
</div>
