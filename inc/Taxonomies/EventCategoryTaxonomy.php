<?php
/**
 * Classe EventCategoryTaxonomy
 *
 * Enregistre la taxonomie 'event_category' pour la catégorisation
 * des événements associatifs.
 *
 * Exemples de catégories : soirée, formation, sortie, AG, sportif, culturel…
 *
 * @package ThemeAssociatif\Taxonomies
 * @since   1.0.0
 */

namespace ThemeAssociatif\Taxonomies;

/**
 * Classe EventCategoryTaxonomy
 */
class EventCategoryTaxonomy {

    /**
     * Identifiant de la taxonomie.
     *
     * @var string
     */
    const TAXONOMY = 'event_category';

    /**
     * Attache le hook d'initialisation.
     *
     * @return void
     */
    public function register(): void {
        add_action( 'init', [ $this, 'registerTaxonomy' ] );
    }

    /**
     * Enregistre la taxonomie hiérarchique 'event_category'.
     *
     * @return void
     */
    public function registerTaxonomy(): void {
        $labels = [
            'name'              => __( 'Catégories d\'événements', 'theme-associatif' ),
            'singular_name'     => __( 'Catégorie d\'événement', 'theme-associatif' ),
            'search_items'      => __( 'Rechercher une catégorie', 'theme-associatif' ),
            'all_items'         => __( 'Toutes les catégories', 'theme-associatif' ),
            'parent_item'       => __( 'Catégorie parente', 'theme-associatif' ),
            'parent_item_colon' => __( 'Catégorie parente :', 'theme-associatif' ),
            'edit_item'         => __( 'Modifier la catégorie', 'theme-associatif' ),
            'update_item'       => __( 'Mettre à jour', 'theme-associatif' ),
            'add_new_item'      => __( 'Ajouter une catégorie', 'theme-associatif' ),
            'new_item_name'     => __( 'Nouvelle catégorie', 'theme-associatif' ),
            'menu_name'         => __( 'Catégories', 'theme-associatif' ),
            'not_found'         => __( 'Aucune catégorie trouvée', 'theme-associatif' ),
        ];

        $args = [
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => [ 'slug' => 'categorie-evenement' ],
            'show_in_rest'      => true,
            'rest_base'         => 'event-categories',
        ];

        register_taxonomy( self::TAXONOMY, [ 'event' ], $args );
    }
}
