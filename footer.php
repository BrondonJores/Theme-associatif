<?php
/**
 * Template standard WordPress : pied de page du site
 *
 * Ce fichier est charge par get_footer() pour assurer la compatibilite
 * WordPress et le bon fonctionnement des extensions tierces.
 *
 * Dans ce theme, le rendu complet du document HTML est gere par le layout
 * de base (resources/views/layouts/base.php) via le TemplateEngine.
 * Ce fichier delegue l'affichage du composant de pied de page au partial dedie.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

get_template_part('resources/views/layouts/footer');
