<?php
/**
 * Interface du gestionnaire d'assets (scripts et styles)
 *
 * Definit le contrat pour l'enregistrement et l'enqueue des ressources
 * statiques (CSS, JavaScript) dans WordPress selon les bonnes pratiques.
 *
 * Principe SOLID applique :
 * - Single Responsibility : Gestion exclusive des assets du theme.
 * - Interface Segregation : Interface focalisee sur les operations d'assets uniquement.
 * - Open/Closed           : Nouveaux types d'assets peuvent etre ajoutes via extension.
 *
 * @package ThemeAssociatif\Contracts
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Contracts;

/**
 * AssetManagerInterface
 *
 * Contrat pour le gestionnaire de ressources statiques.
 * Gere les scripts et styles du theme avec versionnage et dependances.
 */
interface AssetManagerInterface
{
    /**
     * Enregistrer et enqueue les styles CSS du theme.
     *
     * Utilise wp_enqueue_style() avec versionnage automatique
     * base sur le hash du fichier pour eviter les problemes de cache.
     *
     * @return void
     */
    public function enqueueStyles(): void;

    /**
     * Enregistrer et enqueue les scripts JavaScript du theme.
     *
     * Utilise wp_enqueue_script() avec attributs de securite modernes
     * (defer, module) et localisation des donnees via wp_localize_script().
     *
     * @return void
     */
    public function enqueueScripts(): void;

    /**
     * Enregistrer un style CSS personnalise.
     *
     * @param string $handle      Identifiant unique du style.
     * @param string $src         URL ou chemin relatif du fichier CSS.
     * @param array  $deps        Tableau des dependances (handles).
     * @param bool   $media       Media query (ex: 'all', 'print', 'screen').
     *
     * @return void
     */
    public function registerStyle(string $handle, string $src, array $deps = [], string $media = 'all'): void;

    /**
     * Enregistrer un script JavaScript personnalise.
     *
     * @param string $handle      Identifiant unique du script.
     * @param string $src         URL ou chemin relatif du fichier JS.
     * @param array  $deps        Tableau des dependances (handles).
     * @param bool   $inFooter    True pour charger en bas de page, false pour le head.
     *
     * @return void
     */
    public function registerScript(string $handle, string $src, array $deps = [], bool $inFooter = true): void;
}
