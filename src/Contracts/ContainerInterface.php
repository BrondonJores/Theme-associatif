<?php
/**
 * Interface du conteneur d'injection de dependances
 *
 * Definit le contrat que tout conteneur de services doit respecter dans ce theme.
 * Inspire de la PSR-11 (Container Interface) pour la compatibilite et l'interoperabilite.
 *
 * Principe SOLID applique :
 * - Interface Segregation    : Interface fine, focalisee sur la gestion des bindings.
 * - Dependency Inversion     : Le code depend de cette abstraction, pas de l'implementation.
 * - Open/Closed              : Extensible sans modification de cette interface.
 *
 * @package ThemeAssociatif\Contracts
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Contracts;

/**
 * ContainerInterface
 *
 * Contrat pour le conteneur d'injection de dependances.
 * Tout service enregistre dans le conteneur doit pouvoir etre recupere
 * via son identifiant (nom de classe ou alias).
 */
interface ContainerInterface
{
    /**
     * Enregistrer un binding dans le conteneur.
     *
     * @param string   $abstract  Identifiant ou nom de l'interface/classe abstraite.
     * @param callable $factory   Fonction de creation retournant l'instance concrete.
     *
     * @return void
     */
    public function bind(string $abstract, callable $factory): void;

    /**
     * Enregistrer un singleton dans le conteneur.
     *
     * Un singleton n'est instancie qu'une seule fois ; les appels subsequents
     * retournent la meme instance.
     *
     * @param string   $abstract  Identifiant ou nom de l'interface/classe abstraite.
     * @param callable $factory   Fonction de creation retournant l'instance concrete.
     *
     * @return void
     */
    public function singleton(string $abstract, callable $factory): void;

    /**
     * Recuperer une instance depuis le conteneur.
     *
     * @param string $abstract  Identifiant ou nom de la classe/interface a resoudre.
     *
     * @return mixed  L'instance resolue.
     *
     * @throws \RuntimeException Si le binding est introuvable ou si la resolution echoue.
     */
    public function get(string $abstract): mixed;

    /**
     * Verifier si un binding est enregistre dans le conteneur.
     *
     * @param string $abstract  Identifiant a verifier.
     *
     * @return bool  True si le binding existe, false sinon.
     */
    public function has(string $abstract): bool;
}
