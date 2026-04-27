<?php
/**
 * Template standard WordPress : barre laterale
 *
 * Affiche la zone de widgets principale (sidebar-1).
 * Les zones de widgets sont enregistrees dans SupportServiceProvider.
 *
 * Ce template est charge par get_sidebar() depuis les templates de page.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}
?>

<aside class="sidebar" role="complementary" aria-label="<?php esc_attr_e('Contenu complementaire', 'theme-associatif'); ?>">
    <?php
    if (is_active_sidebar('sidebar-1')) {
        dynamic_sidebar('sidebar-1');
    }
    ?>
</aside><!-- .sidebar -->
