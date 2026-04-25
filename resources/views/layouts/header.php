<?php
/**
 * Template partiel : en-tete du site
 *
 * Affiche l'en-tete du site avec :
 * - Logo ou nom du site
 * - Navigation principale
 * - Bouton de recherche et menu mobile
 *
 * Ce template utilise les services MenuManager et HeroIcon
 * pour rester decouple de la logique metier.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

use ThemeAssociatif\Core\ThemeManager;
use ThemeAssociatif\Contracts\MenuManagerInterface;
use ThemeAssociatif\Support\HeroIcon;

$container   = ThemeManager::getInstance()->getContainer();
$menuManager = $container->get(MenuManagerInterface::class);
$siteUrl     = esc_url(home_url('/'));
$siteName    = esc_html(get_bloginfo('name'));
$siteDesc    = esc_html(get_bloginfo('description'));
?>

<header class="site-header" role="banner">
    <div class="site-header__inner container">

        <!-- Identite du site : logo ou nom de l'association -->
        <div class="site-header__identity">
            <a href="<?php echo $siteUrl; ?>" class="site-header__logo-link" rel="home">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <span class="site-header__site-name"><?php echo $siteName; ?></span>
                    <?php if ($siteDesc) : ?>
                        <span class="site-header__site-desc"><?php echo $siteDesc; ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </a>
        </div><!-- .site-header__identity -->

        <!-- Navigation principale (desktop) -->
        <nav class="site-header__nav" aria-label="<?php esc_attr_e('Navigation principale', 'theme-associatif'); ?>">
            <?php
            // Afficher le menu primary si un menu est assigne a cet emplacement.
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $menuManager->renderMenu('primary', [
                'container'       => false,
                'menu_class'      => 'site-header__nav-list',
                'items_wrap'      => '<ul class="%2$s" role="list">%3$s</ul>',
            ]);
            ?>
        </nav><!-- .site-header__nav -->

        <!-- Actions de l'en-tete : recherche, menu mobile -->
        <div class="site-header__actions">

            <!-- Bouton de recherche -->
            <button
                type="button"
                class="site-header__search-btn"
                aria-label="<?php esc_attr_e('Ouvrir la recherche', 'theme-associatif'); ?>"
                aria-expanded="false"
                aria-controls="search-overlay"
            >
                <?php HeroIcon::display('magnifying-glass', HeroIcon::STYLE_OUTLINE, ['class' => 'icon icon--sm']); ?>
            </button>

            <!-- Bouton hamburger (mobile uniquement) -->
            <button
                type="button"
                class="site-header__menu-btn"
                aria-label="<?php esc_attr_e('Ouvrir le menu', 'theme-associatif'); ?>"
                aria-expanded="false"
                aria-controls="mobile-menu"
            >
                <?php HeroIcon::display('bars-3', HeroIcon::STYLE_OUTLINE, ['class' => 'icon']); ?>
                <span class="sr-only"><?php esc_html_e('Menu', 'theme-associatif'); ?></span>
            </button>

        </div><!-- .site-header__actions -->

    </div><!-- .site-header__inner -->

    <!-- Navigation mobile -->
    <div
        class="mobile-menu"
        id="mobile-menu"
        role="dialog"
        aria-modal="true"
        aria-label="<?php esc_attr_e('Menu de navigation', 'theme-associatif'); ?>"
        hidden
    >
        <div class="mobile-menu__header">
            <span class="mobile-menu__title"><?php esc_html_e('Menu', 'theme-associatif'); ?></span>
            <button
                type="button"
                class="mobile-menu__close"
                aria-label="<?php esc_attr_e('Fermer le menu', 'theme-associatif'); ?>"
            >
                <?php HeroIcon::display('x-mark', HeroIcon::STYLE_OUTLINE, ['class' => 'icon']); ?>
            </button>
        </div><!-- .mobile-menu__header -->

        <?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $menuManager->renderMenu('mobile', [
            'container'  => false,
            'menu_class' => 'mobile-menu__list',
        ]);
        ?>
    </div><!-- .mobile-menu -->

</header><!-- .site-header -->
