<?php
/**
 * Composant : Section hero (banniere principale)
 *
 * Composant reutilisable pour afficher une section hero (banniere d'introduction)
 * en haut des pages principales. Supporte un titre, une description et un CTA.
 *
 * Variables attendues :
 * @var string $hero_title        Titre principal du hero.
 * @var string $hero_subtitle     Sous-titre ou description courte (optionnel).
 * @var string $hero_cta_label    Libelle du bouton d'action principale (optionnel).
 * @var string $hero_cta_url      URL du bouton d'action principale (optionnel).
 * @var string $hero_image_url    URL de l'image d'arriere-plan (optionnel).
 * @var string $hero_image_alt    Texte alternatif de l'image.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

use ThemeAssociatif\Support\HeroIcon;

$heroTitle    = isset($hero_title) ? esc_html($hero_title) : esc_html(get_bloginfo('name'));
$heroSubtitle = isset($hero_subtitle) ? wp_kses_post($hero_subtitle) : esc_html(get_bloginfo('description'));
$heroCtaLabel = isset($hero_cta_label) ? esc_html($hero_cta_label) : '';
$heroCtaUrl   = isset($hero_cta_url) ? esc_url($hero_cta_url) : '';
$heroImageUrl = isset($hero_image_url) ? esc_url($hero_image_url) : '';
$heroImageAlt = isset($hero_image_alt) ? esc_attr($hero_image_alt) : '';

// Construire les styles inline si une image d'arriere-plan est definie.
$heroStyle = '';
if ($heroImageUrl) {
    $heroStyle = ' style="background-image: url(' . $heroImageUrl . ');"';
}
?>

<section
    class="hero<?php echo $heroImageUrl ? ' hero--with-image' : ''; ?>"
    aria-label="<?php esc_attr_e('Section principale', 'theme-associatif'); ?>"
    <?php echo $heroStyle; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
>
    <div class="hero__overlay" aria-hidden="true"></div>

    <div class="hero__content container">

        <h1 class="hero__title"><?php echo $heroTitle; ?></h1>

        <?php if ($heroSubtitle) : ?>
            <div class="hero__subtitle">
                <?php echo $heroSubtitle; ?>
            </div>
        <?php endif; ?>

        <?php if ($heroCtaLabel && $heroCtaUrl) : ?>
            <div class="hero__actions">
                <a href="<?php echo $heroCtaUrl; ?>" class="btn btn--primary hero__cta">
                    <?php echo $heroCtaLabel; ?>
                    <?php HeroIcon::display('arrow-right', HeroIcon::STYLE_OUTLINE, ['class' => 'icon icon--sm']); ?>
                </a>
            </div><!-- .hero__actions -->
        <?php endif; ?>

    </div><!-- .hero__content -->

</section><!-- .hero -->
