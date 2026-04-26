# Theme Associatif

Theme WordPress moderne et securise pour les associations etudiantes.
Architecture strictement SOLID avec injection de dependances, interfaces bien definies et separation complete des responsabilites.

---

## Fonctionnalites

- Architecture SOLID (Single Responsibility, Open/Closed, Liskov, Interface Segregation, Dependency Inversion)
- Conteneur d'injection de dependances (Service Container)
- Autoloader PSR-4 via Composer
- Systeme de templates PHP avec protection contre les traversees de repertoire
- Gestion centralisee des assets (CSS/JS) avec versionnage par hash
- Gestion des menus avec emplacements multiples
- Service de securite (sanitisation, echappement, nonces, validation)
- Icones via Heroicons (SVG inline)
- Support PHP 8.1+, WordPress 6.0+
- Compatible Gutenberg (wp-block-styles, align-wide)
- Entierement traduit (domaine `theme-associatif`)

---

## Prerequis

| Outil | Version minimale |
|---|---|
| PHP | 8.1 |
| WordPress | 6.0 |
| Composer | 2.x |

---

## Installation

### 1. Cloner le theme

```bash
cd /chemin/vers/wordpress/wp-content/themes/
git clone https://github.com/BrondonJores/Theme-associatif theme-associatif
```

### 2. Installer les dependances Composer

```bash
cd theme-associatif
composer install --optimize-autoloader
```

### 3. Activer le theme

Depuis l'administration WordPress : **Apparence > Themes > Theme Associatif > Activer**.

---

## Structure du projet

```
theme-associatif/
├── src/
│   ├── Contracts/      Interfaces (abstractions)
│   ├── Core/           Infrastructure centrale (ThemeManager, ServiceContainer, Configuration)
│   ├── Providers/      Service Providers (enregistrement des services)
│   ├── Services/       Implementations concretes (AssetManager, MenuManager, etc.)
│   └── Support/        Utilitaires (HeroIcon, helpers)
├── resources/views/    Templates PHP
│   ├── layouts/        Structures de page (base, header, footer)
│   ├── components/     Composants reutilisables (card, alert, hero)
│   └── partials/       Blocs de contenu (entry, entry-summary, no-results)
├── config/             Fichiers de configuration (app, assets, menus)
├── docs/               Documentation technique
├── functions.php       Point d'entree du theme
├── index.php           Template de repli WordPress
├── single.php          Template article individuel
├── page.php            Template page statique
├── archive.php         Template archives
├── search.php          Template resultats de recherche
├── 404.php             Template page introuvable
├── style.css           En-tete du theme (requis par WordPress)
└── composer.json       Dependances et autoloader PSR-4
```

---

## Configuration

Les fichiers de configuration se trouvent dans `config/` :

| Fichier | Description |
|---|---|
| `config/app.php` | Parametres generaux, fonctionnalites, infos de l'association |
| `config/assets.php` | Styles CSS et scripts JS a charger |
| `config/menus.php` | Emplacements de menus de navigation |

Acces depuis le code :

```php
// Via le helper global
$version = theme_config('app.version');
$perPage = theme_config('app.per_page', 10);

// Via le service
$config = ThemeManager::getInstance()->getConfig();
$debug  = $config->get('app.debug', false);
```

---

## Utilisation des templates

### Afficher un template

```php
// Affichage direct (echo)
theme_display('components/card', [
    'title'   => 'Mon evenement',
    'excerpt' => 'Description de l\'evenement.',
    'link'    => get_permalink(),
]);

// Capturer le HTML sans l'afficher
$html = theme_view('components/alert', ['message' => 'Inscription confirmee.']);
```

### Afficher une icone Heroicon

```php
// Affichage direct
heroicon('calendar', 'outline', ['class' => 'icon icon--sm']);

// Capturer le SVG
$svg = heroicon_render('user', 'solid', ['class' => 'icon']);
```

---

## Etendre le theme

### Ajouter un nouveau service

1. Creer l'interface dans `src/Contracts/` :

```php
interface EventManagerInterface {
    public function getUpcoming(int $limit): array;
}
```

2. Creer l'implementation dans `src/Services/` :

```php
final class EventManager implements EventManagerInterface {
    public function getUpcoming(int $limit): array { ... }
}
```

3. Creer le provider dans `src/Providers/` :

```php
final class EventServiceProvider extends AbstractServiceProvider {
    public function register(ContainerInterface $container): void {
        $container->singleton(EventManagerInterface::class, fn($c) => new EventManager($c->get(Configuration::class)));
    }
}
```

4. Enregistrer le provider dans `ThemeManager::$providers`.

---

## Qualite du code

```bash
# Tests unitaires
composer test

# Analyse statique PHPStan (niveau 8)
composer phpstan

# Verification des standards WordPress Coding Standards
composer phpcs

# Correction automatique des standards
composer phpcbf
```

---

## Architecture SOLID

Pour une documentation complete des principes SOLID appliques dans ce theme, consultez [docs/SOLID.md](docs/SOLID.md).

---

## Licence

GPL-2.0-or-later — voir [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html).