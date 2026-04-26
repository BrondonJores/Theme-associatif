<?php
/**
 * Conteneur d'injection de dependances
 *
 * Implementation legere du patron Service Container / IoC Container.
 * Gere l'enregistrement et la resolution des dependances du theme
 * sans necessiter de librairie externe, en gardant le theme leger.
 *
 * Principe SOLID applique :
 * - Single Responsibility : Ce conteneur gere uniquement les bindings et la resolution.
 * - Open/Closed           : Nouveaux bindings peuvent etre ajoutes sans modifier la classe.
 * - Liskov Substitution   : Respecte pleinement le ContainerInterface.
 * - Dependency Inversion  : Le theme depend de ContainerInterface, pas de cette classe.
 *
 * @package ThemeAssociatif\Core
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Core;

use ThemeAssociatif\Contracts\ContainerInterface;
use RuntimeException;

/**
 * ServiceContainer
 *
 * Conteneur IoC (Inversion of Control) pour la gestion des dependances du theme.
 * Supporte les bindings simples et les singletons.
 */
final class ServiceContainer implements ContainerInterface
{
    /**
     * Tableau des factories enregistrees pour chaque binding.
     *
     * @var array<string, callable>
     */
    private array $bindings = [];

    /**
     * Tableau des factories de singletons enregistrees.
     *
     * @var array<string, callable>
     */
    private array $singletons = [];

    /**
     * Tableau des instances de singletons deja resolues (cache).
     *
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * Enregistrer un binding dans le conteneur.
     *
     * Chaque appel a get() creera une nouvelle instance via la factory.
     *
     * @param string   $abstract  Identifiant du binding (interface ou classe).
     * @param callable $factory   Fonction de creation de l'instance.
     *
     * @return void
     */
    public function bind(string $abstract, callable $factory): void
    {
        $this->bindings[$abstract] = $factory;
    }

    /**
     * Enregistrer un singleton dans le conteneur.
     *
     * La factory est appelee une seule fois ; l'instance est mise en cache
     * pour les appels subsequents.
     *
     * @param string   $abstract  Identifiant du singleton (interface ou classe).
     * @param callable $factory   Fonction de creation de l'instance unique.
     *
     * @return void
     */
    public function singleton(string $abstract, callable $factory): void
    {
        $this->singletons[$abstract] = $factory;
    }

    /**
     * Recuperer une instance depuis le conteneur.
     *
     * Ordre de resolution :
     * 1. Instance singleton deja resolue (cache)
     * 2. Singleton enregistre (creation et mise en cache)
     * 3. Binding simple (nouvelle instance a chaque appel)
     *
     * @param string $abstract  Identifiant a resoudre.
     *
     * @return mixed  L'instance resolue.
     *
     * @throws RuntimeException Si le binding est introuvable.
     */
    public function get(string $abstract): mixed
    {
        // Retourner l'instance singleton deja resolue si disponible.
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Resoudre et mettre en cache un singleton.
        if (isset($this->singletons[$abstract])) {
            $this->instances[$abstract] = ($this->singletons[$abstract])($this);
            return $this->instances[$abstract];
        }

        // Resoudre un binding simple (nouvelle instance a chaque appel).
        if (isset($this->bindings[$abstract])) {
            return ($this->bindings[$abstract])($this);
        }

        throw new RuntimeException(
            sprintf(
                'Aucun binding trouve pour "%s". Verifiez que le service provider correspondant est enregistre.',
                $abstract
            )
        );
    }

    /**
     * Verifier si un binding est enregistre dans le conteneur.
     *
     * @param string $abstract  Identifiant a verifier.
     *
     * @return bool  True si un binding (simple ou singleton) existe.
     */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract])
            || isset($this->singletons[$abstract])
            || isset($this->instances[$abstract]);
    }

    /**
     * Enregistrer une instance pre-construite comme singleton.
     *
     * Permet d'enregistrer une instance existante sans factory.
     *
     * @param string $abstract  Identifiant du binding.
     * @param mixed  $instance  L'instance pre-construite.
     *
     * @return void
     */
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }
}
