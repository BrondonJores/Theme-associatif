<?php
/**
 * Template standard WordPress : en-tete du site
 *
 * Ce fichier est charge par get_header() pour assurer la compatibilite
 * WordPress et le bon fonctionnement des extensions tierces.
 *
 * Dans ce theme, le rendu complet du document HTML est gere par le layout
 * de base (resources/views/layouts/base.php) via le TemplateEngine.
 * Ce fichier delegue l'affichage du composant d'en-tete au partial dedie.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

get_template_part('resources/views/layouts/header');
