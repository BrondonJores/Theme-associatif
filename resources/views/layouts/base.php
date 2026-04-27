<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
/**
 * Template de mise en page principal (base layout)
 *
 * Tous les templates du theme incluent ce fichier comme base.
 * Il fournit la structure HTML complete avec header, contenu et footer.
 *
 * Variables disponibles :
 * @var string   $content  Contenu HTML principal de la page.
 * @var string   $title    Titre de la page (optionnel).
 * @var \ThemeAssociatif\Core\Configuration $config  Configuration du theme.
 */
?>

<div class="site" id="page">

    <?php
    /**
     * En-tete du site avec navigation principale.
     * Utilise le partial header.php pour la separation des responsabilites.
     */
    get_template_part('resources/views/layouts/header');
    ?>

    <main class="site__main" id="main" role="main">
        <?php
        // Afficher le contenu passe en parametre depuis le template appelant.
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $content ?? '';
        ?>
    </main>

    <?php
    /**
     * Pied de page du site avec navigation secondaire et informations legales.
     */
    get_template_part('resources/views/layouts/footer');
    ?>

</div><!-- .site -->

<?php wp_footer(); ?>
</body>
</html>
