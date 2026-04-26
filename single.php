<?php
/**
 * Template WordPress : article individuel (single post)
 *
 * Affiche un article unique dans son integralite.
 * Position dans la hierarchie des templates WordPress :
 * single-{post-type}-{slug}.php > single-{post-type}.php > single.php > singular.php > index.php
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
