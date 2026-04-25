<?php
/**
 * Composant HeroIcon - Rendu d'icones SVG Heroicons
 *
 * Composant reutilisable pour afficher les icones de la bibliotheque Heroicons
 * (https://heroicons.com) directement en SVG inline.
 *
 * Utilise le style "outline" par defaut (contours fins, 24x24)
 * avec support du style "solid" (remplis, 24x24) et "mini" (remplis, 20x20).
 *
 * Principe SOLID applique :
 * - Single Responsibility : Ce composant gere uniquement le rendu des icones.
 * - Open/Closed           : Nouveau style d'icones peut etre ajoute sans modifier la classe.
 *
 * @package ThemeAssociatif\Support
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Support;

/**
 * HeroIcon
 *
 * Composant pour afficher des icones SVG Heroicons en inline.
 * Les SVG inline permettent de les styler via CSS et d'ameliorer
 * les performances en evitant des requetes HTTP supplementaires.
 */
final class HeroIcon
{
    /**
     * Style d'icone : contours fins (24x24).
     */
    public const STYLE_OUTLINE = 'outline';

    /**
     * Style d'icone : solide rempli (24x24).
     */
    public const STYLE_SOLID = 'solid';

    /**
     * Style d'icone : mini rempli (20x20).
     */
    public const STYLE_MINI = 'mini';

    /**
     * Bibliotheque d'icones SVG disponibles.
     * Seules les icones les plus utilisees dans le contexte associatif sont incluses.
     * Pour une bibliotheque complete, integrer via npm heroicons.
     *
     * Chaque entree contient les variantes outline/solid avec les attributs 'd' du path SVG.
     *
     * @var array<string, array<string, string>>
     */
    private static array $icons = [
        // Navigation et interface.
        'home' => [
            'outline' => '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />',
            'solid'   => '<path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z" /><path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z" />',
        ],
        'user' => [
            'outline' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />',
            'solid'   => '<path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />',
        ],
        'users' => [
            'outline' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />',
            'solid'   => '<path d="M16.5 7.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM3.375 19.5a8.25 8.25 0 0 1 16.498 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /><path d="M18.75 13.875a4.125 4.125 0 0 1 4.125 4.125c0 .906-.291 1.745-.786 2.428a9.04 9.04 0 0 0-3.453-2.437l-.007-.002A6.77 6.77 0 0 0 18.75 13.875Zm-13.5 0c.497 0 .978.053 1.44.152l-.007.003a9.04 9.04 0 0 0-3.453 2.437A4.108 4.108 0 0 1 2.25 18c0-2.278 1.847-4.125 4.125-4.125Z" />',
        ],
        'calendar' => [
            'outline' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />',
            'solid'   => '<path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule="evenodd" />',
        ],
        'bell' => [
            'outline' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />',
            'solid'   => '<path fill-rule="evenodd" d="M5.25 9a6.75 6.75 0 0 1 13.5 0v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 0 1-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 1 1-7.48 0 24.585 24.585 0 0 1-4.831-1.244.75.75 0 0 1-.298-1.205A8.217 8.217 0 0 0 5.25 9.75V9Z" clip-rule="evenodd" />',
        ],
        'envelope' => [
            'outline' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />',
            'solid'   => '<path d="M1.5 8.67v8.58a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V8.67l-8.928 5.493a3 3 0 0 1-3.144 0L1.5 8.67Z" /><path d="M22.5 6.908V6.75a3 3 0 0 0-3-3h-15a3 3 0 0 0-3 3v.158l9.714 5.978a1.5 1.5 0 0 0 1.572 0L22.5 6.908Z" />',
        ],
        'map-pin' => [
            'outline' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />',
            'solid'   => '<path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-2.003 3.5-4.697 3.5-8.327a8.25 8.25 0 0 0-16.5 0c0 3.63 1.556 6.324 3.5 8.327a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />',
        ],
        'magnifying-glass' => [
            'outline' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />',
            'solid'   => '<path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5ZM2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5Z" clip-rule="evenodd" />',
        ],
        'bars-3' => [
            'outline' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />',
            'solid'   => '<path fill-rule="evenodd" d="M3 6.75A.75.75 0 0 1 3.75 6h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 6.75ZM3 12a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 12Zm0 5.25a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />',
        ],
        'x-mark' => [
            'outline' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />',
            'solid'   => '<path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 0 1 1.06 0L12 10.94l5.47-5.47a.75.75 0 1 1 1.06 1.06L13.06 12l5.47 5.47a.75.75 0 1 1-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 0 1-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />',
        ],
        'chevron-right' => [
            'outline' => '<path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />',
            'solid'   => '<path fill-rule="evenodd" d="M16.28 11.47a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 0 1-1.06-1.06L14.69 12 7.72 5.03a.75.75 0 0 1 1.06-1.06l7.5 7.5Z" clip-rule="evenodd" />',
        ],
        'arrow-right' => [
            'outline' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />',
            'solid'   => '<path fill-rule="evenodd" d="M12.97 3.97a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 1 1-1.06-1.06l6.22-6.22H3a.75.75 0 0 1 0-1.5h16.19l-6.22-6.22a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />',
        ],
        'check' => [
            'outline' => '<path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />',
            'solid'   => '<path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />',
        ],
        'information-circle' => [
            'outline' => '<path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />',
            'solid'   => '<path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 0 1 .67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 1 1-.671-1.34l.041-.022ZM12 9a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />',
        ],
        'exclamation-triangle' => [
            'outline' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />',
            'solid'   => '<path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />',
        ],
    ];

    /**
     * Generer le HTML SVG d'une icone Heroicon.
     *
     * @param string               $name        Nom de l'icone (ex: 'home', 'user', 'calendar').
     * @param string               $style       Style de l'icone : 'outline', 'solid', ou 'mini'.
     * @param array<string, mixed> $attributes  Attributs HTML supplementaires (class, id, etc.).
     *
     * @return string  Le SVG HTML de l'icone ou une chaine vide si inconnue.
     */
    public static function render(
        string $name,
        string $style = self::STYLE_OUTLINE,
        array $attributes = []
    ): string {
        if (! isset(self::$icons[$name][$style])) {
            // Fallback sur outline si le style n'existe pas.
            if (! isset(self::$icons[$name][self::STYLE_OUTLINE])) {
                return '';
            }
            $style = self::STYLE_OUTLINE;
        }

        $size         = $style === self::STYLE_MINI ? '20' : '24';
        $strokeAttrs  = $style === self::STYLE_OUTLINE
            ? 'fill="none" stroke="currentColor" stroke-width="1.5"'
            : 'fill="currentColor"';

        // Construire les attributs HTML additionnels.
        $htmlAttributes = self::buildAttributes($attributes);

        $defaultClass = 'icon icon--' . esc_attr($name);
        if (! isset($attributes['class'])) {
            $htmlAttributes = ' class="' . $defaultClass . '"' . $htmlAttributes;
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$s %1$s" %2$s%3$s aria-hidden="true">%4$s</svg>',
            esc_attr($size),
            $strokeAttrs,
            $htmlAttributes,
            self::$icons[$name][$style]
        );
    }

    /**
     * Afficher directement le SVG d'une icone (echo).
     *
     * @param string               $name        Nom de l'icone.
     * @param string               $style       Style de l'icone.
     * @param array<string, mixed> $attributes  Attributs HTML supplementaires.
     *
     * @return void
     */
    public static function display(
        string $name,
        string $style = self::STYLE_OUTLINE,
        array $attributes = []
    ): void {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo self::render($name, $style, $attributes);
    }

    /**
     * Construire une chaine d'attributs HTML a partir d'un tableau.
     *
     * @param array<string, mixed> $attributes  Tableau [attribut => valeur].
     *
     * @return string  Chaine d'attributs HTML.
     */
    private static function buildAttributes(array $attributes): string
    {
        $html = '';

        foreach ($attributes as $attr => $value) {
            // Les attributs booleen (ex: hidden) sans valeur.
            if ($value === true) {
                $html .= ' ' . esc_attr($attr);
                continue;
            }
            if ($value === false) {
                continue;
            }
            $html .= ' ' . esc_attr($attr) . '="' . esc_attr((string) $value) . '"';
        }

        return $html;
    }
}
