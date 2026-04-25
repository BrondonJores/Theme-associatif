<?php
/**
 * Composant : Message d'alerte / notification
 *
 * Composant reutilisable pour afficher des messages d'alerte contextualises
 * (succes, erreur, avertissement, information) dans les templates.
 *
 * Variables attendues :
 * @var string $alert_type     Type d'alerte : 'success', 'error', 'warning', 'info'.
 * @var string $alert_message  Message de l'alerte (HTML autorise avec wp_kses_post).
 * @var string $alert_title    Titre de l'alerte (optionnel).
 * @var bool   $alert_dismiss  True si l'alerte peut etre dismissible (optionnel).
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

use ThemeAssociatif\Support\HeroIcon;

$alertType    = isset($alert_type) && in_array($alert_type, ['success', 'error', 'warning', 'info'], true)
    ? $alert_type
    : 'info';
$alertMessage = isset($alert_message) ? wp_kses_post($alert_message) : '';
$alertTitle   = isset($alert_title) ? esc_html($alert_title) : '';
$alertDismiss = isset($alert_dismiss) && $alert_dismiss;

// Choisir l'icone appropriee selon le type d'alerte.
$iconName = match ($alertType) {
    'success' => 'check',
    'error'   => 'exclamation-triangle',
    'warning' => 'exclamation-triangle',
    default   => 'information-circle',
};

// Role ARIA selon le type d'alerte.
$ariaRole = in_array($alertType, ['error', 'warning'], true) ? 'alert' : 'status';
?>

<div
    class="alert alert--<?php echo esc_attr($alertType); ?><?php echo $alertDismiss ? ' alert--dismissible' : ''; ?>"
    role="<?php echo esc_attr($ariaRole); ?>"
    aria-live="polite"
>

    <!-- Icone de l'alerte -->
    <div class="alert__icon" aria-hidden="true">
        <?php HeroIcon::display($iconName, HeroIcon::STYLE_OUTLINE, ['class' => 'icon']); ?>
    </div><!-- .alert__icon -->

    <!-- Contenu de l'alerte -->
    <div class="alert__content">

        <?php if ($alertTitle) : ?>
            <p class="alert__title"><strong><?php echo $alertTitle; ?></strong></p>
        <?php endif; ?>

        <?php if ($alertMessage) : ?>
            <div class="alert__message">
                <?php echo $alertMessage; ?>
            </div>
        <?php endif; ?>

    </div><!-- .alert__content -->

    <?php if ($alertDismiss) : ?>
        <!-- Bouton de fermeture de l'alerte -->
        <button
            type="button"
            class="alert__dismiss"
            aria-label="<?php esc_attr_e('Fermer cette notification', 'theme-associatif'); ?>"
        >
            <?php HeroIcon::display('x-mark', HeroIcon::STYLE_OUTLINE, ['class' => 'icon icon--sm']); ?>
        </button>
    <?php endif; ?>

</div><!-- .alert -->
