<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Services;

use ThemeAssociatif\Security\Contracts\ValidatorInterface;
use ThemeAssociatif\Security\Rules\AbstractValidationRule;
use ThemeAssociatif\Security\Rules\EmailRule;
use ThemeAssociatif\Security\Rules\IntegerRule;
use ThemeAssociatif\Security\Rules\MaxLengthRule;
use ThemeAssociatif\Security\Rules\MinLengthRule;
use ThemeAssociatif\Security\Rules\RegexRule;
use ThemeAssociatif\Security\Rules\RequiredRule;
use ThemeAssociatif\Security\Rules\UrlRule;

/**
 * Class ValidatorService
 *
 * Implementation du service de validation des donnees.
 * Supporte un systeme de regles extensible via des classes de regles
 * independantes (Open/Closed Principle) et des regles anonymes (closures).
 *
 * Syntaxe des regles :
 *   'required'          - champ obligatoire
 *   'email'             - adresse email valide
 *   'url'               - URL valide
 *   'integer'           - entier valide
 *   'min_length:8'      - longueur minimale
 *   'max_length:255'    - longueur maximale
 *   'regex:/^[a-z]+$/'  - correspond au pattern
 *
 * @package ThemeAssociatif\Security\Services
 * @since   1.0.0
 */
class ValidatorService implements ValidatorInterface
{
    /**
     * Les erreurs de validation apres le dernier appel a validate().
     *
     * @var array<string, array<string>>
     */
    private array $errors = [];

    /**
     * Les regles de validation enregistrees (nom => instance ou callable).
     *
     * @var array<string, AbstractValidationRule|callable>
     */
    private array $rules = [];

    /**
     * Les regles personnalisees enregistrees via addCustomRule().
     *
     * @var array<string, array{callback: callable, message: string}>
     */
    private array $customRules = [];

    /**
     * Constructeur : enregistre les regles natives du systeme.
     */
    public function __construct()
    {
        $this->registerBuiltinRules();
    }

    /**
     * {@inheritdoc}
     *
     * Parcourt chaque champ et chaque regle, instancie la regle correspondante
     * et accumule les erreurs sans interrompre la validation.
     */
    public function validate(array $data, array $rules): bool
    {
        $this->reset();

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $ruleDefinition) {
                $this->evaluateRule($field, $value, $ruleDefinition);
            }
        }

        return empty($this->errors);
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->errors = [];
    }

    /**
     * {@inheritdoc}
     *
     * La callback doit avoir la signature : function(mixed $value): bool
     * Elle doit retourner true si la valeur est valide.
     */
    public function addCustomRule(string $name, callable $callback, string $message): void
    {
        $this->customRules[$name] = [
            'callback' => $callback,
            'message'  => $message,
        ];
    }

    /**
     * Enregistre les regles de validation natives du theme.
     * Chaque regle est un objet implementant AbstractValidationRule.
     *
     * @return void
     */
    private function registerBuiltinRules(): void
    {
        $this->rules = [
            'required' => new RequiredRule(),
            'email'    => new EmailRule(),
            'url'      => new UrlRule(),
            'integer'  => new IntegerRule(),
        ];
    }

    /**
     * Evalue une regle specifique sur un champ et une valeur.
     * Ajoute l'erreur au tableau si la regle echoue.
     *
     * @param  string $field          Le nom du champ.
     * @param  mixed  $value          La valeur a valider.
     * @param  string $ruleDefinition La definition de la regle (ex: 'min_length:8').
     * @return void
     */
    private function evaluateRule(string $field, mixed $value, string $ruleDefinition): void
    {
        [$ruleName, $parameter] = $this->parseRuleDefinition($ruleDefinition);

        $rule = $this->resolveRule($ruleName, $parameter);

        if ($rule === null) {
            return;
        }

        if ($rule instanceof AbstractValidationRule) {
            if (!$rule->passes($value, $field)) {
                $this->addError($field, $rule->getMessage($field));
            }

            return;
        }

        if (is_array($rule) && isset($rule['callback'], $rule['message'])) {
            if (!($rule['callback'])($value)) {
                $this->addError($field, str_replace(':field', $field, $rule['message']));
            }
        }
    }

    /**
     * Analyse la definition d'une regle et separe le nom du parametre.
     * Ex: 'min_length:8' => ['min_length', '8']
     * Ex: 'required'     => ['required', null]
     *
     * @param  string $definition La definition brute de la regle.
     * @return array{0: string, 1: string|null} Le nom de la regle et son parametre.
     */
    private function parseRuleDefinition(string $definition): array
    {
        $parts = explode(':', $definition, 2);

        return [$parts[0], $parts[1] ?? null];
    }

    /**
     * Resout une regle par son nom et son parametre.
     * Cree une nouvelle instance si un parametre est fourni.
     *
     * @param  string      $ruleName  Le nom de la regle.
     * @param  string|null $parameter Le parametre de la regle.
     * @return AbstractValidationRule|array{callback: callable, message: string}|null
     */
    private function resolveRule(string $ruleName, ?string $parameter): AbstractValidationRule|array|null
    {
        if (isset($this->customRules[$ruleName])) {
            return $this->customRules[$ruleName];
        }

        if ($parameter !== null) {
            return match ($ruleName) {
                'min_length' => new MinLengthRule((int) $parameter),
                'max_length' => new MaxLengthRule((int) $parameter),
                'regex'      => new RegexRule($parameter),
                'integer'    => new IntegerRule(null, null),
                default      => $this->rules[$ruleName] ?? null,
            };
        }

        return $this->rules[$ruleName] ?? null;
    }

    /**
     * Ajoute une erreur pour un champ specifique.
     *
     * @param  string $field   Le nom du champ.
     * @param  string $message Le message d'erreur.
     * @return void
     */
    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }
}
