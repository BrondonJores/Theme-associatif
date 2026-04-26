<?php
/**
 * Template WordPress : page statique
 *
 * Affiche une page WordPress (post_type = 'page') dans son integralite.
 * Position dans la hierarchie des templates WordPress :
 * {slug}.php > page-{id}.php > page.php > singular.php > index.php
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$template_engine = ThemeAssociatif\Core\ThemeManager::getInstance()
    ->getContainer()
    ->get(\ThemeAssociatif\Contracts\TemplateEngineInterface::class);

$template_engine->display('layouts/base', [
    'title'   => single_post_title('', false),
    'content' => function (): void {
        if (have_posts()) {
            the_post();
            get_template_part('resources/views/partials/entry');
        }
    },
]);
