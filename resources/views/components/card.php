<?php
/**
 * Composant : Carte de contenu generique
 *
 * Composant reutilisable pour afficher une carte (article, evenement, membre, etc.)
 * sous forme de carte visuelle avec image, titre, extrait et lien.
 *
 * Variables attendues :
 * @var string      $title      Titre de la carte (echappe par le template appelant).
 * @var string      $excerpt    Extrait de texte (echappe par le template appelant).
 * @var string      $link       URL de destination (sanitisee).
 * @var string      $image_url  URL de l'image (optionnel).
 * @var string      $image_alt  Texte alternatif de l'image.
 * @var string      $badge      Texte du badge/categorie (optionnel).
 * @var string      $date       Date lisible (optionnel).
 * @var string      $meta       Metadonnees additionnelles HTML (optionnel).
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

use ThemeAssociatif\Support\HeroIcon;

$title     = isset($title) ? esc_html($title) : '';
$excerpt   = isset($excerpt) ? wp_kses_post($excerpt) : '';
$link      = isset($link) ? esc_url($link) : '#';
$imageUrl  = isset($image_url) ? esc_url($image_url) : '';
$imageAlt  = isset($image_alt) ? esc_attr($image_alt) : $title;
$badge     = isset($badge) ? esc_html($badge) : '';
$date      = isset($date) ? esc_html($date) : '';
$meta      = isset($meta) ? wp_kses_post($meta) : '';
?>

<article class="card">

    <?php if ($imageUrl) : ?>
        <!-- Image de la carte -->
        <a href="<?php echo $link; ?>" class="card__image-link" tabindex="-1" aria-hidden="true">
            <div class="card__image-wrapper">
                <img
                    src="<?php echo $imageUrl; ?>"
                    alt="<?php echo $imageAlt; ?>"
                    class="card__image"
                    loading="lazy"
                    decoding="async"
                >
            </div><!-- .card__image-wrapper -->
        </a>
    <?php endif; ?>

    <!-- Contenu textuel de la carte -->
    <div class="card__body">

        <?php if ($badge) : ?>
            <!-- Badge de categorie -->
            <span class="card__badge"><?php echo $badge; ?></span>
        <?php endif; ?>

        <?php if ($date) : ?>
            <!-- Date de publication ou d'evenement -->
            <time class="card__date">
                <?php HeroIcon::display('calendar', HeroIcon::STYLE_OUTLINE, ['class' => 'icon icon--xs']); ?>
                <?php echo $date; ?>
            </time>
        <?php endif; ?>

        <!-- Titre avec lien vers le contenu complet -->
        <h3 class="card__title">
            <a href="<?php echo $link; ?>" class="card__title-link">
                <?php echo $title; ?>
            </a>
        </h3>

        <?php if ($excerpt) : ?>
            <!-- Extrait de contenu -->
            <div class="card__excerpt">
                <?php echo $excerpt; ?>
            </div><!-- .card__excerpt -->
        <?php endif; ?>

        <?php if ($meta) : ?>
            <!-- Metadonnees additionnelles (lieu, participants, etc.) -->
            <div class="card__meta">
                <?php echo $meta; ?>
            </div><!-- .card__meta -->
        <?php endif; ?>

        <!-- Lien "Lire la suite" -->
        <a href="<?php echo $link; ?>" class="card__cta">
            <?php esc_html_e('Lire la suite', 'theme-associatif'); ?>
            <?php HeroIcon::display('arrow-right', HeroIcon::STYLE_OUTLINE, ['class' => 'icon icon--sm']); ?>
        </a>

    </div><!-- .card__body -->

</article><!-- .card -->
