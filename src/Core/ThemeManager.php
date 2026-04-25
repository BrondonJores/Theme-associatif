<?php
/**
 * Gestionnaire principal du theme - Orchestrateur central
 *
 * Le ThemeManager est la classe centrale qui orchestre l'initialisation
 * complete du theme WordPress. Il suit le pattern Singleton pour garantir
 * qu'une seule instance gere l'ensemble du cycle de vie du theme.
 *
 * Responsabilites :
 * - Instancier le conteneur de services
 * - Charger la configuration
 * - Enregistrer et demarrer tous les service providers
 * - Configurer les fonctionnalites WordPress du theme
 *
 * Principe SOLID applique :
 * - Single Responsibility : Le ThemeManager orchestre uniquement l'initialisation.
 * - Open/Closed           : Nouveaux providers ajoutables sans modifier cette classe.
 * - Dependency Inversion  : Depend des interfaces, pas des implementations concretes.
 *
 * @package ThemeAssociatif\Core
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Core;

use ThemeAssociatif\Contracts\ContainerInterface;
use ThemeAssociatif\Contracts\ServiceProviderInterface;
use ThemeAssociatif\Providers\AssetServiceProvider;
use ThemeAssociatif\Providers\MenuServiceProvider;
use ThemeAssociatif\Providers\SecurityServiceProvider;
use ThemeAssociatif\Providers\SupportServiceProvider;

/**
 * ThemeManager
 *
 * Orchestrateur central du theme. Point d'entree unique pour l'initialisation
 * de tous les services et la configuration WordPress du theme.
 */
final class ThemeManager
{
    /**
     * Instance unique du ThemeManager (pattern Singleton).
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Conteneur d'injection de dependances.
     *
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * Gestionnaire de configuration.
     *
     * @var Configuration
     */
    private Configuration $config;

    /**
     * Liste des service providers a enregistrer.
     *
     * Principe Open/Closed : Ajouter de nouveaux providers ici sans
     * modifier la logique d'initialisation.
     *
     * @var array<class-string<ServiceProviderInterface>>
     */
    private array $providers = [
        SecurityServiceProvider::class,
        AssetServiceProvider::class,
        MenuServiceProvider::class,
        SupportServiceProvider::class,
    ];

    /**
     * Constructeur prive - Empeche l'instanciation directe (Singleton).
     */
    private function __construct()
    {
        $this->container = new ServiceContainer();
        $this->config    = new Configuration(get_template_directory() . '/config');

        // Enregistrer la configuration et le conteneur eux-memes pour acces global.
        $this->container->instance(Configuration::class, $this->config);
        $this->container->instance(ContainerInterface::class, $this->container);
    }

    /**
     * Obtenir l'instance unique du ThemeManager.
     *
     * @return self  L'instance unique.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Demarrer le theme : enregistrer les providers et configurer WordPress.
     *
     * Cette methode est appelee une seule fois depuis functions.php.
     * Elle suit deux phases :
     * 1. Enregistrement (register) : tous les providers s'enregistrent.
     * 2. Demarrage (boot)          : tous les providers s'initialisent.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerProviders();
        $this->bootProviders();
        $this->registerWordPressSupport();
        $this->registerTextDomain();
    }

    /**
     * Recuperer le conteneur de services.
     *
     * @return ContainerInterface  Le conteneur d'injection de dependances.
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Recuperer le gestionnaire de configuration.
     *
     * @return Configuration  Le gestionnaire de configuration.
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * Enregistrer tous les service providers dans le conteneur.
     *
     * La phase d'enregistrement precede la phase de demarrage pour
     * garantir que toutes les dependances sont disponibles.
     *
     * @return void
     */
    private function registerProviders(): void
    {
        foreach ($this->providers as $providerClass) {
            /** @var ServiceProviderInterface $provider */
            $provider = new $providerClass();
            $provider->register($this->container);

            // Conserver la reference du provider pour la phase boot.
            $this->container->instance($providerClass, $provider);
        }
    }

    /**
     * Demarrer tous les service providers (phase boot).
     *
     * Appele apres que tous les providers ont ete enregistres.
     *
     * @return void
     */
    private function bootProviders(): void
    {
        foreach ($this->providers as $providerClass) {
            /** @var ServiceProviderInterface $provider */
            $provider = $this->container->get($providerClass);
            $provider->boot($this->container);
        }
    }

    /**
     * Declarer les fonctionnalites WordPress supportees par le theme.
     *
     * Enregistre les supports via add_theme_support() lors du hook 'after_setup_theme'.
     *
     * @return void
     */
    private function registerWordPressSupport(): void
    {
        add_action('after_setup_theme', function (): void {
            // Activer le support du titre automatique (balise <title>).
            add_theme_support('title-tag');

            // Activer le support des images a la une.
            add_theme_support('post-thumbnails');

            // Activer le support HTML5 pour les formulaires, galeries, etc.
            add_theme_support('html5', [
                'search-form',
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
                'style',
                'script',
            ]);

            // Activer le support du logo personnalise.
            add_theme_support('custom-logo', [
                'height'      => 100,
                'width'       => 400,
                'flex-height' => true,
                'flex-width'  => true,
            ]);

            // Activer le support des blocs Gutenberg avec styles globaux.
            add_theme_support('wp-block-styles');

            // Activer les largeurs larges et completes des blocs.
            add_theme_support('align-wide');

            // Activer la couleur du fond personnalisable.
            add_theme_support('custom-background');

            // Definir les tailles d'images supplementaires du theme.
            add_image_size('theme-thumbnail', 400, 300, true);
            add_image_size('theme-featured', 1200, 600, true);
            add_image_size('theme-card', 800, 500, true);
        });
    }

    /**
     * Charger le domaine de traduction du theme.
     *
     * Charge les fichiers .mo depuis le dossier languages/ lors du hook 'after_setup_theme'.
     *
     * @return void
     */
    private function registerTextDomain(): void
    {
        add_action('after_setup_theme', static function (): void {
            load_theme_textdomain(
                'theme-associatif',
                get_template_directory() . '/languages'
            );
        });
    }

    /**
     * Empecher le clonage du Singleton.
     */
    private function __clone() {}

    /**
     * Empecher la deserialisation du Singleton.
     *
     * @throws \RuntimeException Toujours.
     */
    public function __wakeup(): void
    {
        throw new \RuntimeException('Le ThemeManager ne peut pas etre deserialise.');
    }
}
