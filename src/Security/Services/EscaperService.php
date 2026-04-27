<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Services;

use ThemeAssociatif\Security\Contracts\EscaperInterface;

/**
 * Class EscaperService
 *
 * Implementation du service d'echappement des donnees en sortie.
 * Chaque methode correspond a un contexte de sortie specifique et utilise
 * la fonction WordPress appropriee.
 *
 * Regle d'or : echapper le plus tard possible, juste avant l'affichage,
 * dans le bon contexte. Ne jamais faire confiance a une valeur non echappee
 * meme si elle a ete sanitisee au stockage.
 *
 * @package ThemeAssociatif\Security\Services
 * @since   1.0.0
 */
class EscaperService implements EscaperInterface
{
    /**
     * Liste des balises HTML autorisees par defaut pour kses.
     * Reproduit les balises les plus courantes autorisees pour les articles.
     *
     * @var array<string, array<string, bool>>
     */
    private array $defaultAllowedTags = [
        'a'          => ['href' => true, 'title' => true, 'target' => true, 'rel' => true],
        'abbr'       => ['title' => true],
        'blockquote' => ['cite' => true],
        'br'         => [],
        'cite'       => [],
        'code'       => [],
        'del'        => ['datetime' => true],
        'em'         => [],
        'i'          => [],
        'li'         => [],
        'ol'         => [],
        'p'          => ['class' => true],
        'pre'        => [],
        'q'          => ['cite' => true],
        'span'       => ['class' => true, 'style' => true],
        'strike'     => [],
        'strong'     => [],
        'ul'         => [],
    ];

    /**
     * {@inheritdoc}
     *
     * Utilise esc_html() de WordPress qui encode :
     * & en &amp; | < en &lt; | > en &gt; | " en &quot; | ' en &#039;
     */
    public function escHtml(string $value): string
    {
        return esc_html($value);
    }

    /**
     * {@inheritdoc}
     *
     * Echappe la valeur avec esc_html() et l'affiche directement.
     */
    public function escHtmlE(string $value): void
    {
        echo esc_html($value);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise esc_attr() de WordPress. Plus restrictif que esc_html(),
     * encode davantage de caracteres pour etre sur dans les attributs HTML.
     */
    public function escAttr(string $value): string
    {
        return esc_attr($value);
    }

    /**
     * {@inheritdoc}
     *
     * Echappe la valeur avec esc_attr() et l'affiche directement.
     */
    public function escAttrE(string $value): void
    {
        echo esc_attr($value);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise esc_url() de WordPress qui :
     * - Valide et filtre le protocole (supprime javascript:, data:, etc.)
     * - Encode les caracteres speciaux
     * - Supprime les espaces et caracteres de controle
     */
    public function escUrl(string $value): string
    {
        return esc_url($value);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise esc_url() avec echo pour afficher directement.
     */
    public function escUrlE(string $value): void
    {
        echo esc_url($value);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise esc_url_raw() de WordPress.
     * A utiliser uniquement pour les URLs internes ou de confiance
     * car ne valide pas aussi strictement que esc_url().
     */
    public function escUrlRaw(string $value): string
    {
        return esc_url_raw($value);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise esc_js() de WordPress qui echappe la chaine pour
     * une utilisation dans un element <script> ou un gestionnaire d'evenement HTML.
     * Encode : ', ", \, et les sauts de ligne.
     */
    public function escJs(string $value): string
    {
        return esc_js($value);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise esc_attr() comme base et ajoute l'encodage specifique au CSS.
     * Les caracteres dangereux pour CSS (;, {, }, (, ), etc.) sont supprimes
     * ou encodes pour eviter l'injection de regles CSS malveillantes.
     */
    public function escCss(string $value): string
    {
        return esc_attr(
            preg_replace('/[^a-zA-Z0-9\s\-_#.,!%]/', '', $value) ?? $value
        );
    }

    /**
     * {@inheritdoc}
     *
     * Utilise wp_kses() de WordPress avec la liste de balises fournie
     * ou la liste par defaut si aucune n'est specifiee.
     */
    public function escHtmlKses(string $value, array $allowedTags = []): string
    {
        $tags = empty($allowedTags) ? $this->defaultAllowedTags : $allowedTags;

        return wp_kses($value, $tags);
    }

    /**
     * {@inheritdoc}
     *
     * Combine esc_html() et __() pour echapper et traduire simultanement.
     * A utiliser pour les chaines de l'interface utilisateur.
     */
    public function escHtmlTrans(string $value, string $domain = 'theme-associatif'): string
    {
        return esc_html__($value, $domain);
    }
}
