<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Contracts;

/**
 * Interface ValidatorInterface
 *
 * Contrat pour le service de validation des donnees.
 * La validation verifie que les donnees respectent les regles metier
 * sans modifier la valeur (contrairement a la sanitization).
 *
 * @package ThemeAssociatif\Security\Contracts
 * @since   1.0.0
 */
interface ValidatorInterface
{
    /**
     * Valide un ensemble de donnees selon un tableau de regles.
     *
     * Le format des regles est : ['champ' => ['regle1', 'regle2:parametre', ...]]
     *
     * @param  array<string, mixed>         $data  Les donnees a valider.
     * @param  array<string, array<string>> $rules Les regles de validation.
     * @return bool True si toutes les regles sont respectees, false sinon.
     */
    public function validate(array $data, array $rules): bool;

    /**
     * Retourne les erreurs de validation apres un appel a validate().
     *
     * @return array<string, array<string>> Les erreurs indexees par nom de champ.
     */
    public function getErrors(): array;

    /**
     * Retourne la premiere erreur d'un champ specifique.
     *
     * @param  string $field Le nom du champ.
     * @return string|null   Le message d'erreur ou null si aucune erreur.
     */
    public function getFirstError(string $field): ?string;

    /**
     * Verifie si la validation a echoue.
     *
     * @return bool True si des erreurs existent.
     */
    public function fails(): bool;

    /**
     * Remet a zero les erreurs de validation.
     *
     * @return void
     */
    public function reset(): void;

    /**
     * Ajoute une regle de validation personnalisee.
     *
     * @param  string   $name     Le nom de la regle.
     * @param  callable $callback La fonction de validation (reçoit la valeur, retourne bool).
     * @param  string   $message  Le message d'erreur si la regle echoue.
     * @return void
     */
    public function addCustomRule(string $name, callable $callback, string $message): void;
}
