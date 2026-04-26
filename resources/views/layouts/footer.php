<?php
/**
 * Template partiel : pied de page du site
 *
 * Affiche le pied de page avec :
 * - Informations de l'association
 * - Navigation de pied de page
 * - Liens vers les reseaux sociaux
 * - Mentions legales et copyright
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

use ThemeAssociatif\Core\ThemeManager;
use ThemeAssociatif\Contracts\MenuManagerInterface;
use ThemeAssociatif\Support\HeroIcon;

$container   = ThemeManager::getInstance()->getContainer();
$menuManager = $container->get(MenuManagerInterface::class);
$currentYear = (int) gmdate('Y');
$siteName    = esc_html(get_bloginfo('name'));
$siteUrl     = esc_url(home_url('/'));
?>

<footer class="site-footer" role="contentinfo">
    <div class="site-footer__inner container">

        <!-- Colonne identite de l'association -->
        <div class="site-footer__identity">
            <a href="<?php echo $siteUrl; ?>" class="site-footer__logo-link" rel="home">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <span class="site-footer__site-name"><?php echo $siteName; ?></span>
                <?php endif; ?>
            </a>

            <p class="site-footer__description">
                <?php echo esc_html(get_bloginfo('description')); ?>
            </p>

            <!-- Adresse email de contact -->
            <?php $adminEmail = antispambot(get_option('admin_email', '')); ?>
            <?php if ($adminEmail) : ?>
                <a
                    href="mailto:<?php echo esc_attr($adminEmail); ?>"
                    class="site-footer__contact-link"
                >
                    <?php HeroIcon::display('envelope', HeroIcon::STYLE_OUTLINE, ['class' => 'icon icon--sm']); ?>
                    <span><?php echo esc_html($adminEmail); ?></span>
                </a>
            <?php endif; ?>
        </div><!-- .site-footer__identity -->

        <!-- Navigation de pied de page -->
        <?php if ($menuManager->hasMenu('footer')) : ?>
            <nav
                class="site-footer__nav"
                aria-label="<?php esc_attr_e('Navigation pied de page', 'theme-associatif'); ?>"
            >
                <?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $menuManager->renderMenu('footer', [
                    'container'  => false,
                    'menu_class' => 'site-footer__nav-list',
                ]);
                ?>
            </nav><!-- .site-footer__nav -->
        <?php endif; ?>

    </div><!-- .site-footer__inner -->

    <!-- Barre de bas de page : copyright et mentions legales -->
    <div class="site-footer__bottom">
        <div class="container">
            <p class="site-footer__copyright">
                <?php
                printf(
                    /* translators: 1: Annee, 2: Nom du site */
                    esc_html__('&copy; %1$s %2$s. Tous droits reserves.', 'theme-associatif'),
                    esc_html((string) $currentYear),
                    esc_html($siteName)
                );
                ?>
            </p>

            <p class="site-footer__credits">
                <?php
                printf(
                    /* translators: 1: Lien vers WordPress.org */
                    esc_html__('Propulse par %1$s', 'theme-associatif'),
                    '<a href="https://wordpress.org" target="_blank" rel="noopener noreferrer">WordPress</a>'
                );
                ?>
            </p>
        </div>
    </div><!-- .site-footer__bottom -->

</footer><!-- .site-footer -->
