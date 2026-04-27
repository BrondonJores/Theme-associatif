<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Rules;

/**
 * Class UrlRule
 *
 * Regle de validation : le champ doit etre une URL valide.
 * Valide le format de l'URL et restreint les protocoles autorises
 * a http et https par defaut pour eviter les injections javascript: ou data:.
 *
 * @package ThemeAssociatif\Security\Rules
 * @since   1.0.0
 */
class UrlRule extends AbstractValidationRule
{
    /**
     * Message d'erreur par defaut pour cette regle.
     *
     * @var string
     */
    protected string $message = 'Le champ :field doit etre une URL valide (http ou https).';

    /**
     * Les protocoles autorises.
     *
     * @var array<string>
     */
    private array $allowedSchemes;

    /**
     * Constructeur avec protocols autorises optionnels.
     *
     * @param array<string> $allowedSchemes Les protocoles autorises (defaut: http, https).
     * @param string|null   $message        Message personnalise.
     */
    public function __construct(array $allowedSchemes = ['http', 'https'], ?string $message = null)
    {
        $this->allowedSchemes = $allowedSchemes;
        parent::__construct($message);
    }

    /**
     * {@inheritdoc}
     *
     * @param  mixed  $value La valeur a valider.
     * @param  string $field Le nom du champ.
     * @return bool   True si la valeur est une URL valide avec un protocole autorise.
     */
    public function passes(mixed $value, string $field): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);

        if ($scheme === false || $scheme === null) {
            return false;
        }

        return in_array(strtolower($scheme), $this->allowedSchemes, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'url';
    }
}
