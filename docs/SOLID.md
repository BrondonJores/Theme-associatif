# Architecture SOLID — Theme Associatif

Ce document explique comment les cinq principes SOLID sont appliques dans l'architecture du theme WordPress pour associations etudiantes.

---

## Table des matieres

1. [S — Single Responsibility Principle](#s--single-responsibility-principle)
2. [O — Open/Closed Principle](#o--openclosed-principle)
3. [L — Liskov Substitution Principle](#l--liskov-substitution-principle)
4. [I — Interface Segregation Principle](#i--interface-segregation-principle)
5. [D — Dependency Inversion Principle](#d--dependency-inversion-principle)
6. [Patterns complementaires](#patterns-complementaires)
7. [Structure des repertoires](#structure-des-repertoires)

---

## S — Single Responsibility Principle

> Une classe ne doit avoir qu'une seule raison de changer.

### Application dans le theme

Chaque classe du theme a une responsabilite unique et bien definie :

| Classe | Responsabilite unique |
|---|---|
| `ThemeManager` | Orchestrer l'initialisation du theme (uniquement) |
| `ServiceContainer` | Resoudre les dependances entre services |
| `Configuration` | Charger et exposer la configuration depuis les fichiers PHP |
| `AssetManager` | Enregistrer et enqueue les CSS/JS |
| `MenuManager` | Enregistrer les emplacements de menus et rendre leur HTML |
| `SecurityService` | Sanitisation, echappement et validation des donnees |
| `TemplateEngine` | Charger et rendre les fichiers templates PHP |
| `HeroIcon` | Generer le SVG des icones Heroicons |

### Exemple concret

`functions.php` ne fait qu'une chose : verifier les prerequis et demarrer le `ThemeManager`. Il ne contient aucune logique metier.

```php
// functions.php — responsabilite unique : initialisation
ThemeAssociatif\Core\ThemeManager::getInstance()->boot();
```

Si la logique d'enqueue des assets change, seul `AssetManager` est modifie. Si la logique de securite evolue, seul `SecurityService` change. Chaque classe evolue independamment.

---

## O — Open/Closed Principle

> Une entite logicielle doit etre ouverte a l'extension mais fermee a la modification.

### Application dans le theme

#### Ajout de nouveaux Service Providers

Pour ajouter une fonctionnalite au theme (ex : un gestionnaire d'evenements), il suffit de creer un nouveau provider et de l'ajouter a la liste dans `ThemeManager::$providers`. La logique d'initialisation n'est pas modifiee.

```php
// ThemeManager.php — ajout d'un nouveau provider SANS modifier la logique
private array $providers = [
    SecurityServiceProvider::class,
    AssetServiceProvider::class,
    MenuServiceProvider::class,
    SupportServiceProvider::class,
    EventServiceProvider::class, // Nouveau provider : aucune modification requise ailleurs
];
```

#### Ajout de nouveaux assets

La configuration des assets dans `config/assets.php` permet d'ajouter des styles ou scripts sans toucher a `AssetManager` :

```php
// config/assets.php
'styles' => [
    'theme-main'   => [...],
    'theme-events' => [...], // Nouveau style : AssetManager le charge automatiquement
],
```

#### Configuration extensible

La classe `Configuration` charge tous les fichiers PHP du dossier `config/`. Pour ajouter une section de configuration, il suffit de creer un nouveau fichier (ex : `config/events.php`).

---

## L — Liskov Substitution Principle

> Les objets d'une classe derivee doivent pouvoir remplacer les objets de la classe parente sans alterer la correction du programme.

### Application dans le theme

Toutes les implementations concretes respectent pleinement le contrat de leur interface. Un `TemplateEngine` alternatif (ex : moteur Twig) peut remplacer l'implementation PHP actuelle sans que les templates ni le reste du theme ne le sachent.

```php
// L'interface definit le contrat complet
interface TemplateEngineInterface {
    public function render(string $template, array $data = []): string;
    public function display(string $template, array $data = []): void;
    public function exists(string $template): bool;
}

// TwigEngine respecte exactement le meme contrat
class TwigEngine implements TemplateEngineInterface {
    // Toutes les methodes implementees avec la meme semantique
}
```

#### Verification systematique

Chaque implementation concrete :
- Respecte tous les types de parametres declares dans l'interface
- Respecte tous les types de retour declares dans l'interface
- Ne leve pas d'exceptions non documentees dans l'interface
- Preserves la semantique definie par le contrat

---

## I — Interface Segregation Principle

> Les clients ne doivent pas dependre d'interfaces qu'ils n'utilisent pas.

### Application dans le theme

Les interfaces sont granulaires et specifiques a leur domaine. Aucune interface "fourre-tout" n'existe.

#### Interfaces disponibles

| Interface | Methodes | Consommateurs |
|---|---|---|
| `ContainerInterface` | `bind`, `singleton`, `instance`, `get`, `has` | ThemeManager, providers |
| `ServiceProviderInterface` | `register`, `boot` | ThemeManager |
| `HookableInterface` | `registerHooks` | Providers avec hooks WordPress |
| `AssetManagerInterface` | `enqueueStyles`, `enqueueScripts`, `enqueueEditorAssets` | AssetServiceProvider |
| `MenuManagerInterface` | `registerLocations`, `renderMenu`, `hasMenu` | Templates, MenuServiceProvider |
| `SecurityServiceInterface` | `sanitizeText`, `escHtml`, `escAttr`, `escUrl`, `validateNonce`, `createNonce` | Templates, formulaires |
| `TemplateEngineInterface` | `render`, `display`, `exists` | Templates, helpers |

Chaque template ne depend que de l'interface dont il a besoin :

```php
// header.php — depend uniquement de MenuManagerInterface
$menuManager = $container->get(MenuManagerInterface::class);

// entry.php — n'a pas besoin du MenuManager, ne l'importe pas
```

---

## D — Dependency Inversion Principle

> Les modules de haut niveau ne doivent pas dependre des modules de bas niveau.
> Les deux doivent dependre d'abstractions.

### Application dans le theme

#### Le conteneur de dependances

Le `ServiceContainer` est le coeur du DIP dans ce theme. Il lie les interfaces a leurs implementations concretes :

```php
// SupportServiceProvider::register()
$container->singleton(
    TemplateEngineInterface::class, // Abstraction
    static fn($c) => new TemplateEngine(...) // Implementation concrete
);
```

Les templates et services dependent uniquement des interfaces :

```php
// Un template demande l'interface, jamais la classe concrete
$engine = $container->get(TemplateEngineInterface::class);

// Changer l'implementation ne necessite qu'une modification dans le provider
```

#### Injection de dependances

Les services recoivent leurs dependances via le constructeur, jamais via des singletons globaux ou des `new` directs (sauf dans les factories des providers) :

```php
// AssetManager recoit Configuration par injection
final class AssetManager implements AssetManagerInterface {
    public function __construct(
        private readonly Configuration $config // Injecte par le conteneur
    ) {}
}
```

#### Schema d'initialisation

```
functions.php
    └── ThemeManager::boot()
            ├── ServiceContainer (gestion des dependances)
            ├── Configuration (chargement config/*.php)
            └── ServiceProviders (register puis boot)
                    ├── SecurityServiceProvider
                    │       └── bind SecurityServiceInterface → SecurityService
                    ├── AssetServiceProvider
                    │       └── bind AssetManagerInterface → AssetManager
                    ├── MenuServiceProvider
                    │       └── bind MenuManagerInterface → MenuManager
                    └── SupportServiceProvider
                            └── bind TemplateEngineInterface → TemplateEngine
```

---

## Patterns complementaires

### Singleton (ThemeManager)

`ThemeManager` utilise le pattern Singleton pour garantir qu'une seule instance orchestre le theme. Le clonage et la deserialisation sont interdits.

### Service Locator (helpers)

Les fonctions dans `src/Support/helpers.php` servent de facade legere vers le conteneur. Elles sont uniquement utilisees dans les templates PHP pour des raisons de lisibilite.

```php
// Dans un template : interface claire et concise
theme_display('partials/entry-summary');
```

### Template Method (AbstractServiceProvider)

`AbstractServiceProvider` definit le squelette des providers. Les sous-classes implementent `register()` et `boot()` selon leurs besoins.

---

## Structure des repertoires

```
Theme-associatif/
├── src/
│   ├── Contracts/          Interfaces (abstractions)
│   │   ├── AssetManagerInterface.php
│   │   ├── ContainerInterface.php
│   │   ├── HookableInterface.php
│   │   ├── MenuManagerInterface.php
│   │   ├── SecurityServiceInterface.php
│   │   ├── ServiceProviderInterface.php
│   │   └── TemplateEngineInterface.php
│   │
│   ├── Core/               Infrastructure centrale
│   │   ├── Configuration.php
│   │   ├── ServiceContainer.php
│   │   └── ThemeManager.php
│   │
│   ├── Providers/          Enregistrement des services
│   │   ├── AbstractServiceProvider.php
│   │   ├── AssetServiceProvider.php
│   │   ├── MenuServiceProvider.php
│   │   ├── SecurityServiceProvider.php
│   │   └── SupportServiceProvider.php
│   │
│   ├── Services/           Implementations concretes
│   │   ├── AssetManager.php
│   │   ├── MenuManager.php
│   │   ├── SecurityService.php
│   │   └── TemplateEngine.php
│   │
│   └── Support/            Utilitaires et helpers
│       ├── HeroIcon.php
│       └── helpers.php
│
├── resources/
│   └── views/
│       ├── layouts/        Structures de mise en page
│       │   ├── base.php    Document HTML complet
│       │   ├── header.php  En-tete du site
│       │   └── footer.php  Pied de page
│       │
│       ├── components/     Composants reutilisables
│       │   ├── alert.php
│       │   ├── card.php
│       │   └── hero.php
│       │
│       └── partials/       Blocs de contenu
│           ├── entry.php          Article complet
│           ├── entry-summary.php  Resume d'article
│           └── no-results.php     Aucun resultat
│
├── config/                 Configuration du theme
│   ├── app.php
│   ├── assets.php
│   └── menus.php
│
├── functions.php           Point d'entree du theme
├── index.php               Template de repli
├── single.php              Article individuel
├── page.php                Page statique
├── archive.php             Archives
├── search.php              Resultats de recherche
├── 404.php                 Page introuvable
├── header.php              Compatibilite get_header()
├── footer.php              Compatibilite get_footer()
├── sidebar.php             Barre laterale
├── style.css               En-tete du theme (requis par WP)
└── composer.json           Dependances et autoloader PSR-4
```
