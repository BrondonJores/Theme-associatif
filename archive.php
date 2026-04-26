<?php
/**
 * Template WordPress : page d'archive
 *
 * Affiche les pages d'archive generees par WordPress :
 * archives de categories, etiquettes, auteurs, dates.
 *
 * Position dans la hierarchie des templates WordPress :
 * category.php > tag.php > taxonomy.php > author.php > date.php > archive.php > index.php
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
    'title'   => get_the_archive_title(),
    'content' => function (): void {
        ?>
        <div class="archive">

            <?php if (get_the_archive_description()) : ?>
                <div class="archive__description">
                    <?php the_archive_description('<p>', '</p>'); ?>
                </div><!-- .archive__description -->
            <?php endif; ?>

            <div class="archive__posts posts-grid">
                <?php
                if (have_posts()) {
                    while (have_posts()) {
                        the_post();
                        get_template_part('resources/views/partials/entry-summary');
                    }

                    the_posts_pagination([
                        'mid_size'  => 2,
                        'prev_text' => heroicon_render('arrow-left', 'outline', ['class' => 'icon'])
                            . '<span>' . esc_html__('Precedent', 'theme-associatif') . '</span>',
                        'next_text' => '<span>' . esc_html__('Suivant', 'theme-associatif') . '</span>'
                            . heroicon_render('arrow-right', 'outline', ['class' => 'icon']),
                    ]);
                } else {
                    get_template_part('resources/views/partials/no-results');
                }
                ?>
            </div><!-- .archive__posts -->

        </div><!-- .archive -->
        <?php
    },
]);
