<?php
/**
 * Fonctions d'aide globales du theme
 *
 * Ce fichier fournit des fonctions globales de commodite pour les templates.
 * Ces helpers servent de raccourcis vers les services enregistres
 * dans le conteneur, rendant les templates plus lisibles.
 *
 * IMPORTANT : Ces fonctions sont des facades legeres.
 * Elles ne contiennent pas de logique metier : tout est delegue aux services.
 *
 * @package ThemeAssociatif\Support
 * @since   1.0.0
 */

declare(strict_types=1);

use ThemeAssociatif\Core\ThemeManager;
use ThemeAssociatif\Contracts\SecurityServiceInterface;
use ThemeAssociatif\Contracts\TemplateEngineInterface;
use ThemeAssociatif\Contracts\MenuManagerInterface;
use ThemeAssociatif\Support\HeroIcon;

if (! function_exists('theme_container')) {
    /**
     * Recuperer le conteneur de services du theme.
     *
     * @return \ThemeAssociatif\Contracts\ContainerInterface
     */
    function theme_container(): \ThemeAssociatif\Contracts\ContainerInterface
    {
        return ThemeManager::getInstance()->getContainer();
    }
}

if (! function_exists('theme_config')) {
    /**
     * Recuperer une valeur de configuration du theme.
     *
     * @param string $key      Cle en dot notation (ex: 'app.version').
     * @param mixed  $default  Valeur par defaut si la cle est absente.
     *
     * @return mixed  La valeur de configuration.
     */
    function theme_config(string $key, mixed $default = null): mixed
    {
        return ThemeManager::getInstance()->getConfig()->get($key, $default);
    }
}

if (! function_exists('theme_view')) {
    /**
     * Rendre un template et retourner le HTML.
     *
     * @param string               $template  Chemin du template (sans .php).
     * @param array<string, mixed> $data      Donnees a injecter.
     *
     * @return string  Le HTML rendu.
     */
    function theme_view(string $template, array $data = []): string
    {
        /** @var TemplateEngineInterface $engine */
        $engine = theme_container()->get(TemplateEngineInterface::class);
        return $engine->render($template, $data);
    }
}

if (! function_exists('theme_display')) {
    /**
     * Afficher un template directement (echo).
     *
     * @param string               $template  Chemin du template (sans .php).
     * @param array<string, mixed> $data      Donnees a injecter.
     *
     * @return void
     */
    function theme_display(string $template, array $data = []): void
    {
        /** @var TemplateEngineInterface $engine */
        $engine = theme_container()->get(TemplateEngineInterface::class);
        $engine->display($template, $data);
    }
}

if (! function_exists('theme_menu')) {
    /**
     * Rendre un menu de navigation HTML.
     *
     * @param string               $location  Slug de l'emplacement de menu.
     * @param array<string, mixed> $args      Arguments additionnels.
     *
     * @return string  Le HTML du menu.
     */
    function theme_menu(string $location, array $args = []): string
    {
        /** @var MenuManagerInterface $menuManager */
        $menuManager = theme_container()->get(MenuManagerInterface::class);
        return $menuManager->renderMenu($location, $args);
    }
}

if (! function_exists('esc_html_theme')) {
    /**
     * Echapper et afficher une chaine HTML via le service de securite.
     *
     * @param string $output  La valeur a echapper et afficher.
     *
     * @return void
     */
    function esc_html_theme(string $output): void
    {
        /** @var SecurityServiceInterface $security */
        $security = theme_container()->get(SecurityServiceInterface::class);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $security->escHtml($output);
    }
}

if (! function_exists('heroicon')) {
    /**
     * Afficher une icone Heroicon en SVG inline.
     *
     * @param string               $name        Nom de l'icone.
     * @param string               $style       Style ('outline', 'solid', 'mini').
     * @param array<string, mixed> $attributes  Attributs HTML supplementaires.
     *
     * @return void
     */
    function heroicon(string $name, string $style = HeroIcon::STYLE_OUTLINE, array $attributes = []): void
    {
        HeroIcon::display($name, $style, $attributes);
    }
}

if (! function_exists('heroicon_render')) {
    /**
     * Retourner une icone Heroicon en SVG inline (sans echo).
     *
     * @param string               $name        Nom de l'icone.
     * @param string               $style       Style ('outline', 'solid', 'mini').
     * @param array<string, mixed> $attributes  Attributs HTML supplementaires.
     *
     * @return string  Le SVG HTML de l'icone.
     */
    function heroicon_render(string $name, string $style = HeroIcon::STYLE_OUTLINE, array $attributes = []): string
    {
        return HeroIcon::render($name, $style, $attributes);
    }
}
