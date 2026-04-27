# Theme Associatif

A modern, accessible WordPress theme designed for student associations. Built with a custom CSS design system, vanilla JavaScript modules, full WCAG AA compliance, mobile-first responsive design, and dark mode support.

---

## Features

- **Design System** — CSS custom properties for colors, typography, spacing, shadows, z-index, and animations.
- **Dark Mode** — Automatic via `prefers-color-scheme` and manual toggle persisted in `localStorage`.
- **Accessible** — WCAG 2.1 AA compliant: focus management, ARIA roles, skip links, keyboard navigation.
- **Mobile-first** — Responsive grid and layout built with CSS custom properties and min-width breakpoints.
- **Component Library** — Reusable PHP template parts: Button, Card, Modal, Hero, Event Card.
- **WordPress Customizer** — Association identity, hero section, social links, color overrides, footer text.
- **Navigation** — Sticky header, accessible hamburger menu, keyboard-navigable dropdowns.
- **Events** — Filterable events listing page (by category and period).
- **Members** — Member directory with searchable profile card grid.
- **Contact** — Contact page with native form or Contact Form 7 integration, map embed, social links.
- **Gutenberg Ready** — `editor-styles`, wide/full alignment support, responsive embeds.
- **No Dependencies** — Pure vanilla ES6+ JavaScript, no jQuery.

---

## Requirements

- **WordPress:** 6.0 or higher
- **PHP:** 8.0 or higher
- **Browser support:** All modern browsers (Chrome, Firefox, Safari, Edge)

---

## Installation

### Manual Installation

1. Download or clone this repository:
   ```bash
   git clone https://github.com/BrondonJores/Theme-associatif.git
   ```
2. Copy the theme folder to your WordPress themes directory:
   ```
   wp-content/themes/Theme-associatif/
   ```
3. Log in to your WordPress admin panel.
4. Go to **Appearance > Themes** and activate **Theme Associatif**.

### Via WP-CLI

```bash
cd wp-content/themes
git clone https://github.com/BrondonJores/Theme-associatif.git Theme-associatif
wp theme activate Theme-associatif
```

---

## Development Setup

No build tools are required. The theme uses native CSS `@import` and ES6 modules served directly to modern browsers.

### Running a Local WordPress Environment

```bash
# Using Local by Flywheel, DDEV, or wp-env
npm install -g @wordpress/env   # optional

# Or use any local PHP server pointing to WordPress
```

### File Watching (optional)

Since there is no build step, you can use any live-reload tool. Example with browser-sync:

```bash
npm install -g browser-sync
browser-sync start --proxy "localhost" --files "**/*.css,**/*.js,**/*.php"
```

---

## File Structure

```
Theme-associatif/
├── style.css                          Theme metadata + CSS reset
├── functions.php                      Theme bootstrap (loads inc/)
├── index.php                          Fallback template
├── header.php                         Site header with nav and dark mode toggle
├── footer.php                         Site footer with columns and copyright
├── page.php                           Standard page template
├── single.php                         Single post template
├── archive.php                        Archive/category/tag template
├── 404.php                            Error page template
├── search.php                         Search results template
├── front-page.php                     Home page with hero, events, stats, news
├── page-events.php                    Template: Events (filterable listing)
├── page-members.php                   Template: Members (profile grid)
├── page-contact.php                   Template: Contact (form + map)
│
├── assets/
│   ├── css/
│   │   ├── main.css                   Aggregator @import file
│   │   ├── design-system/
│   │   │   ├── _variables.css         CSS custom properties
│   │   │   ├── _typography.css        Heading/body styles
│   │   │   ├── _spacing.css           Utility spacing classes
│   │   │   ├── _grid.css              Responsive grid utilities
│   │   │   └── _animations.css        Keyframes + scroll-reveal classes
│   │   ├── components/
│   │   │   ├── _buttons.css           Button variants
│   │   │   ├── _cards.css             Card components
│   │   │   ├── _forms.css             Form elements
│   │   │   ├── _modals.css            Modal dialog
│   │   │   ├── _navigation.css        Header nav, hamburger, dropdowns
│   │   │   └── _badges.css            Badge/chip components
│   │   └── layout/
│   │       ├── _header.css            Site header layout
│   │       ├── _footer.css            Site footer layout
│   │       └── _sidebar.css           Sidebar + widget areas
│   └── js/
│       ├── main.js                    Entry point: init all modules + theme toggle
│       ├── navigation.js              Hamburger, dropdowns, sticky header
│       ├── modal.js                   Modal open/close/focus trap
│       └── animations.js             IntersectionObserver scroll animations
│
├── template-parts/
│   └── components/
│       ├── button.php                 Reusable button component
│       ├── card.php                   Reusable card component
│       ├── modal.php                  Reusable modal component
│       ├── hero.php                   Hero section component
│       └── event-card.php            Event-specific card component
│
├── inc/
│   ├── theme-setup.php               Theme supports, menus, sidebars
│   ├── enqueue-assets.php            Fonts, CSS and JS enqueuing
│   ├── template-tags.php             Helper template functions
│   └── customizer.php               WordPress Customizer panels/settings
│
└── docs/
    └── design-system.md             Design system documentation
```

---

## Customizer Options

Navigate to **Appearance > Customize > Theme Associatif** to find:

| Section                | Settings                                              |
|------------------------|-------------------------------------------------------|
| Association Identity   | Name, tagline                                         |
| Hero Section           | Title, subtitle, description, badge, CTAs, image      |
| Association Statistics | Member count, event count, years, projects            |
| Social Links           | GitHub, Twitter/X, Instagram, LinkedIn, Facebook      |
| Theme Colors           | Primary, secondary, accent color overrides            |
| Footer                 | Description, copyright text                           |
| Contact Information    | Email, phone, address, map URL, CF7 form ID           |

---

## Navigation Menus

Register menus in **Appearance > Menus**:

| Location      | Description                           |
|---------------|---------------------------------------|
| Primary       | Main header navigation                |
| Footer        | Footer contact/legal column           |
| Footer Col 1  | First footer link column              |
| Footer Col 2  | Second footer link column             |
| Social Links  | Social media links menu               |

---

## Widget Areas

| ID               | Description                          |
|------------------|--------------------------------------|
| `sidebar-blog`   | Blog/single post sidebar             |
| `sidebar-events` | Events page sidebar                  |
| `sidebar-footer` | Footer widget area                   |

---

## Page Templates

Assign these templates in the **Page Attributes** meta box:

| Template | Slug              | Description               |
|----------|-------------------|---------------------------|
| Events   | `page-events.php` | Filterable events listing |
| Members  | `page-members.php`| Member profile grid       |
| Contact  | `page-contact.php`| Contact form + map        |

---

## License

GNU General Public License v2 or later — see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).

---

## Contributing

1. Fork the repository.
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Commit your changes following WordPress coding standards.
4. Open a pull request.
