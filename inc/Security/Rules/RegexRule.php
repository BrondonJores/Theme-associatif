<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Rules;

/**
 * Class RegexRule
 *
 * Regle de validation : le champ doit correspondre a une expression reguliere.
 * A utiliser avec prudence : toujours valider le pattern regex lui-meme
 * pour eviter les attaques ReDoS (Regular Expression Denial of Service).
 *
 * @package ThemeAssociatif\Security\Rules
 * @since   1.0.0
 */
class RegexRule extends AbstractValidationRule
{
    /**
     * Message d'erreur par defaut pour cette regle.
     *
     * @var string
     */
    protected string $message = 'Le format du champ :field est invalide.';

    /**
     * L'expression reguliere a appliquer (avec delimiteurs PCRE).
     *
     * @var string
     */
    private string $pattern;

    /**
     * Constructeur avec pattern regex requis.
     *
     * @param string      $pattern Le pattern PCRE complet (ex: '/^[a-z]+$/i').
     * @param string|null $message Message personnalise.
     */
    public function __construct(string $pattern, ?string $message = null)
    {
        $this->pattern = $pattern;
        parent::__construct($message);
    }

    /**
     * {@inheritdoc}
     *
     * @param  mixed  $value La valeur a valider.
     * @param  string $field Le nom du champ.
     * @return bool   True si la valeur correspond au pattern.
     */
    public function passes(mixed $value, string $field): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        $result = @preg_match($this->pattern, $value);

        if ($result === false) {
            return false;
        }

        return $result === 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'regex';
    }
}
