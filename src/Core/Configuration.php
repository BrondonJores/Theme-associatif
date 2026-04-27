<?php
/**
 * Gestionnaire de configuration centralise et securise
 *
 * Charge, valide et expose la configuration du theme a partir des fichiers
 * du dossier config/. Implemente un acces immutable par dot notation
 * pour une utilisation fluide et securisee.
 *
 * Principe SOLID applique :
 * - Single Responsibility : Ce gestionnaire s'occupe uniquement de la configuration.
 * - Open/Closed           : Nouveaux fichiers de config peuvent etre ajoutes sans modifier la classe.
 *
 * @package ThemeAssociatif\Core
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Core;

use RuntimeException;

/**
 * Configuration
 *
 * Gestionnaire de configuration centralise.
 * Charge les fichiers PHP du dossier config/ et permet un acces
 * par dot notation (ex: 'app.version', 'assets.styles').
 */
final class Configuration
{
    /**
     * Tableau de toutes les valeurs de configuration chargees.
     *
     * @var array<string, mixed>
     */
    private array $config = [];

    /**
     * Chemin absolu vers le dossier de configuration.
     *
     * @var string
     */
    private string $configPath;

    /**
     * Constructeur - Charger tous les fichiers de configuration.
     *
     * @param string $configPath  Chemin absolu vers le dossier config/.
     *
     * @throws RuntimeException Si le dossier de configuration est introuvable.
     */
    public function __construct(string $configPath)
    {
        if (! is_dir($configPath)) {
            throw new RuntimeException(
                sprintf('Le dossier de configuration est introuvable : %s', $configPath)
            );
        }

        $this->configPath = rtrim($configPath, '/\\');
        $this->loadAll();
    }

    /**
     * Recuperer une valeur de configuration par dot notation.
     *
     * Exemples :
     * - get('app.version')  => '1.0.0'
     * - get('menus')        => ['primary' => [...], ...]
     * - get('assets.styles.main.src')
     *
     * @param string $key      Cle en dot notation.
     * @param mixed  $default  Valeur par defaut si la cle est absente.
     *
     * @return mixed  La valeur de configuration ou la valeur par defaut.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $keys  = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Verifier si une cle de configuration existe.
     *
     * @param string $key  Cle en dot notation.
     *
     * @return bool  True si la cle existe, false sinon.
     */
    public function has(string $key): bool
    {
        return $this->get($key, '__MISSING__') !== '__MISSING__';
    }

    /**
     * Recuperer l'integralite de la configuration chargee.
     *
     * @return array<string, mixed>  Tableau complet de la configuration.
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Charger tous les fichiers PHP du dossier config/.
     *
     * Chaque fichier PHP doit retourner un tableau. Le nom du fichier
     * devient la cle racine de configuration.
     * Exemple : config/app.php => $config['app']
     *
     * @return void
     */
    private function loadAll(): void
    {
        $files = glob($this->configPath . '/*.php');

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            $key   = basename($file, '.php');
            $value = require $file;

            if (! is_array($value)) {
                throw new RuntimeException(
                    sprintf(
                        'Le fichier de configuration "%s" doit retourner un tableau.',
                        $file
                    )
                );
            }

            $this->config[$key] = $value;
        }
    }
}
