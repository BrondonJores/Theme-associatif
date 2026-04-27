# Design System — Theme Associatif

Complete reference for all design tokens, component APIs and usage patterns used in this WordPress theme.

---

## Table of Contents

1. [Design Tokens](#design-tokens)
   - [Colors](#colors)
   - [Typography](#typography)
   - [Spacing](#spacing)
   - [Shadows](#shadows)
   - [Border Radius](#border-radius)
   - [Z-Index Scale](#z-index-scale)
2. [Components](#components)
   - [Button](#button)
   - [Card](#card)
   - [Badge](#badge)
   - [Modal](#modal)
   - [Navigation](#navigation)
   - [Hero](#hero)
   - [Event Card](#event-card)
3. [Layout](#layout)
   - [Container](#container)
   - [Grid](#grid)
   - [Header](#header)
   - [Footer](#footer)
   - [Sidebar](#sidebar)
4. [Dark Mode](#dark-mode)
5. [Accessibility](#accessibility)

---

## Design Tokens

All tokens are defined as CSS custom properties in `assets/css/design-system/_variables.css`.

### Colors

#### Brand Palette

| Token                    | Value     | Usage                         |
|--------------------------|-----------|-------------------------------|
| `--color-primary`        | `#6366F1` | Indigo — CTAs, links, focus   |
| `--color-primary-dark`   | `#4F46E5` | Hover state for primary       |
| `--color-primary-light`  | `#818CF8` | Lighter tint (dark mode)      |
| `--color-secondary`      | `#EC4899` | Pink — accents, badges        |
| `--color-secondary-dark` | `#DB2777` | Hover state for secondary     |
| `--color-accent`         | `#14B8A6` | Teal — highlights, tags       |
| `--color-accent-dark`    | `#0D9488` | Hover state for accent        |

#### Semantic Colors

| Token                   | Value     | Usage                         |
|-------------------------|-----------|-------------------------------|
| `--color-success`       | `#22C55E` | Success states                |
| `--color-success-bg`    | `#F0FDF4` | Success background            |
| `--color-warning`       | `#F59E0B` | Warning states                |
| `--color-warning-bg`    | `#FFFBEB` | Warning background            |
| `--color-error`         | `#EF4444` | Error states                  |
| `--color-error-bg`      | `#FEF2F2` | Error background              |
| `--color-info`          | `#3B82F6` | Informational states          |

#### Surface Colors (theme-aware)

| Token                      | Light Value | Dark Value  |
|----------------------------|-------------|-------------|
| `--color-bg`               | `#FFFFFF`   | `#0F172A`   |
| `--color-bg-secondary`     | `#F8FAFC`   | `#1E293B`   |
| `--color-bg-tertiary`      | `#F1F5F9`   | `#334155`   |
| `--color-surface`          | `#FFFFFF`   | `#1E293B`   |
| `--color-border`           | `#E2E8F0`   | `#334155`   |
| `--color-text`             | `#0F172A`   | `#F1F5F9`   |
| `--color-text-secondary`   | `#475569`   | `#CBD5E1`   |

---

### Typography

Fonts are loaded via Google Fonts (enqueued in `inc/enqueue-assets.php`).

| Token             | Value                         |
|-------------------|-------------------------------|
| `--font-heading`  | `'Poppins', system-ui, sans-serif` |
| `--font-body`     | `'Inter', system-ui, sans-serif`   |
| `--font-mono`     | `'JetBrains Mono', monospace`      |

#### Type Scale (1.25 Major Third)

| Token          | Value      | Approx px |
|----------------|------------|-----------|
| `--text-xs`    | `0.64rem`  | 10.24px   |
| `--text-sm`    | `0.8rem`   | 12.8px    |
| `--text-base`  | `1rem`     | 16px      |
| `--text-lg`    | `1.25rem`  | 20px      |
| `--text-xl`    | `1.563rem` | 25px      |
| `--text-2xl`   | `1.953rem` | 31.25px   |
| `--text-3xl`   | `2.441rem` | 39px      |
| `--text-4xl`   | `3.052rem` | 48.8px    |
| `--text-5xl`   | `3.815rem` | 61px      |

#### Font Weights

| Token                      | Value |
|----------------------------|-------|
| `--font-weight-regular`    | `400` |
| `--font-weight-medium`     | `500` |
| `--font-weight-semibold`   | `600` |
| `--font-weight-bold`       | `700` |
| `--font-weight-extrabold`  | `800` |

---

### Spacing

Base unit is **4px** (`0.25rem`).

| Token        | Value     | px  |
|--------------|-----------|-----|
| `--space-1`  | `0.25rem` | 4   |
| `--space-2`  | `0.5rem`  | 8   |
| `--space-3`  | `0.75rem` | 12  |
| `--space-4`  | `1rem`    | 16  |
| `--space-5`  | `1.25rem` | 20  |
| `--space-6`  | `1.5rem`  | 24  |
| `--space-8`  | `2rem`    | 32  |
| `--space-10` | `2.5rem`  | 40  |
| `--space-12` | `3rem`    | 48  |
| `--space-16` | `4rem`    | 64  |
| `--space-20` | `5rem`    | 80  |
| `--space-24` | `6rem`    | 96  |

---

### Shadows

| Token          | Usage                        |
|----------------|------------------------------|
| `--shadow-sm`  | Subtle card lift             |
| `--shadow-md`  | Dropdowns, tooltips          |
| `--shadow-lg`  | Modals, overlays             |
| `--shadow-xl`  | Full-page drawers            |
| `--shadow-2xl` | High-emphasis surfaces       |
| `--shadow-inner` | Inset form inputs          |

---

### Border Radius

| Token           | Value      |
|-----------------|------------|
| `--radius-sm`   | `0.25rem`  |
| `--radius-md`   | `0.375rem` |
| `--radius-lg`   | `0.5rem`   |
| `--radius-xl`   | `0.75rem`  |
| `--radius-2xl`  | `1rem`     |
| `--radius-3xl`  | `1.5rem`   |
| `--radius-full` | `9999px`   |

---

### Z-Index Scale

| Token          | Value | Usage                      |
|----------------|-------|----------------------------|
| `--z-below`    | `-1`  | Backgrounds                |
| `--z-base`     | `0`   | Default stacking           |
| `--z-raised`   | `10`  | Raised cards               |
| `--z-dropdown` | `100` | Dropdown menus             |
| `--z-sticky`   | `200` | Sticky header              |
| `--z-overlay`  | `300` | Mobile drawer, backdrops   |
| `--z-modal`    | `400` | Modal dialogs              |
| `--z-popover`  | `500` | Popovers                   |
| `--z-toast`    | `600` | Toast notifications        |
| `--z-tooltip`  | `700` | Tooltips                   |

---

## Components

### Button

**File:** `template-parts/components/button.php`  
**CSS:** `assets/css/components/_buttons.css`

#### PHP Usage

```php
get_template_part('template-parts/components/button', null, array(
    'text'          => 'Join us',
    'url'           => '/membership',
    'variant'       => 'primary',
    'size'          => 'lg',
    'icon'          => '<svg .../>',
    'icon_position' => 'right',
));
```

#### Args

| Arg           | Type   | Default     | Description                                         |
|---------------|--------|-------------|-----------------------------------------------------|
| `text`        | string | `''`        | Button label text                                   |
| `url`         | string | `''`        | When set, renders an `<a>` instead of `<button>`    |
| `variant`     | string | `'primary'` | `primary`, `secondary`, `outline`, `ghost`, `danger`|
| `size`        | string | `'md'`      | `sm`, `md`, `lg`                                    |
| `icon`        | string | `''`        | SVG markup to include inside the button             |
| `icon_position` | string | `'left'`  | `left` or `right`                                   |
| `type`        | string | `'button'`  | HTML type attribute for `<button>` element          |
| `disabled`    | bool   | `false`     | Adds `disabled` and `aria-disabled="true"`          |
| `extra_classes` | string | `''`      | Additional CSS class names                          |
| `aria_label`  | string | `''`        | Overrides accessible label (icon-only buttons)      |

#### CSS Classes

```
.btn                          Base button
.btn--primary                 Indigo filled
.btn--secondary               Pink filled
.btn--outline                 Transparent with border
.btn--ghost                   Transparent hover only
.btn--danger                  Red/error variant
.btn--sm / .btn--lg           Size modifiers
.btn--icon-only               Square icon button
```

---

### Card

**File:** `template-parts/components/card.php`  
**CSS:** `assets/css/components/_cards.css`

#### PHP Usage

```php
get_template_part('template-parts/components/card', null, array(
    'title'   => 'Event title',
    'content' => 'Short description...',
    'image'   => get_the_post_thumbnail_url(get_the_ID(), 'medium_large'),
    'url'     => get_the_permalink(),
    'meta'    => array('date' => get_the_date(), 'author' => get_the_author()),
    'badge'   => array('text' => 'News', 'variant' => 'accent'),
    'variant' => 'default',
));
```

#### Args

| Arg           | Type   | Default     | Description                                  |
|---------------|--------|-------------|----------------------------------------------|
| `title`       | string | `''`        | Card heading                                 |
| `content`     | string | `''`        | Body text or HTML                            |
| `image`       | string | `''`        | Thumbnail URL                                |
| `image_alt`   | string | `''`        | Alt text for the image                       |
| `url`         | string | `''`        | Link destination                             |
| `meta`        | array  | `[]`        | `date` and `author` keys                     |
| `badge`       | array  | `[]`        | `text` and `variant` keys                    |
| `variant`     | string | `'default'` | `default`, `featured`, `horizontal`          |
| `extra_classes` | string | `''`      | Additional CSS class names                   |

---

### Badge

**CSS:** `assets/css/components/_badges.css`

#### HTML Usage

```html
<span class="badge badge--primary">New</span>
<span class="badge badge--success badge--lg">Confirmed</span>
<span class="badge badge--error badge--dot">Cancelled</span>
```

#### Variants

| Class               | Appearance                   |
|---------------------|------------------------------|
| `badge--default`    | Neutral gray                 |
| `badge--primary`    | Indigo tint                  |
| `badge--secondary`  | Pink tint                    |
| `badge--accent`     | Teal tint                    |
| `badge--success`    | Green tint                   |
| `badge--warning`    | Amber tint                   |
| `badge--error`      | Red tint                     |
| `badge--info`       | Blue tint                    |
| `badge--solid-*`    | Solid filled variants        |
| `badge--outline`    | Outline variant              |

#### Sizes

| Class       | Description   |
|-------------|---------------|
| `badge--sm` | Compact       |
| `badge--md` | Default       |
| `badge--lg` | Large         |

---

### Modal

**File:** `template-parts/components/modal.php`  
**CSS:** `assets/css/components/_modals.css`  
**JS:** `assets/js/modal.js`

#### PHP Usage

```php
get_template_part('template-parts/components/modal', null, array(
    'id'      => 'confirm-action',
    'title'   => 'Confirm action',
    'content' => '<p>Are you sure you want to proceed?</p>',
    'footer'  => '<button class="btn btn--primary" data-modal-close>Confirm</button>
                  <button class="btn btn--ghost" data-modal-close>Cancel</button>',
    'size'    => 'sm',
));
```

#### Open via HTML

```html
<button data-modal-open="confirm-action">Open modal</button>
```

#### Args

| Arg       | Type   | Default | Description                               |
|-----------|--------|---------|-------------------------------------------|
| `id`      | string | `''`    | Unique HTML id. Required.                 |
| `title`   | string | `''`    | Dialog heading                            |
| `content` | string | `''`    | Dialog body HTML                          |
| `footer`  | string | `''`    | Footer HTML (typically action buttons)    |
| `size`    | string | `'md'`  | `sm`, `md`, `lg`, `xl`                    |

---

### Navigation

**CSS:** `assets/css/components/_navigation.css`  
**JS:** `assets/js/navigation.js`

The navigation uses `data-theme` and system preferences for dark mode automatically. The hamburger is wired to `#nav-toggle` / `#mobile-menu`.

---

### Hero

**File:** `template-parts/components/hero.php`

#### PHP Usage

```php
get_template_part('template-parts/components/hero', null, array(
    'title'         => 'Welcome to our Association',
    'subtitle'      => 'Student life',
    'description'   => 'We connect students through events, workshops and projects.',
    'cta_primary'   => array('text' => 'Our events', 'url' => '/events'),
    'cta_secondary' => array('text' => 'Learn more', 'url' => '/about'),
    'image'         => get_theme_mod('hero_image'),
    'badge_text'    => 'New season',
    'variant'       => 'split',
));
```

---

### Event Card

**File:** `template-parts/components/event-card.php`

#### PHP Usage

```php
get_template_part('template-parts/components/event-card', null, array(
    'title'           => 'Annual Workshop',
    'date'            => 'March 15, 2025',
    'date_iso'        => '2025-03-15',
    'time'            => '14:00 - 17:00',
    'location'        => 'Room B202',
    'description'     => 'Join us for our annual hands-on workshop.',
    'image'           => get_the_post_thumbnail_url(),
    'url'             => get_the_permalink(),
    'category'        => 'Workshop',
    'seats_remaining' => 12,
));
```

---

## Layout

### Container

```html
<div class="container">...</div>
```

Max width: `1280px` (`--container-xl`). Responsive padding via `--container-padding`.

---

### Grid

```html
<div class="grid grid-cols-3 gap-8">
    <div>...</div>
    <div>...</div>
    <div>...</div>
</div>
```

Responsive suffixes: `@sm` (640px), `@md` (768px), `@lg` (1024px), `@xl` (1280px).

---

### Header

The site header is `position: sticky`. It gains the `.is-scrolled` class via JavaScript after scrolling past 10px, triggering a shadow and height reduction.

- Default height: `--header-height: 4rem`
- Scrolled height: `--header-height-scrolled: 3.5rem`

---

### Footer

Three-column layout on desktop, stacked on mobile. Column menus are registered as nav locations `footer-col-1`, `footer-col-2`, and `footer`.

---

### Sidebar

Use the `.has-sidebar` class on a parent to get a content + sidebar layout.

```html
<div class="has-sidebar">
    <main class="content-area">...</main>
    <aside class="widget-area widget-area--sticky">
        <?php dynamic_sidebar('sidebar-blog'); ?>
    </aside>
</div>
```

---

## Dark Mode

Dark mode is supported via:
1. `[data-theme="dark"]` attribute on `<html>` (set by JavaScript)
2. `prefers-color-scheme: dark` media query as fallback

The user's preference is persisted in `localStorage` under the key `theme-associatif-theme`.

An inline script in `wp_head` applies the stored theme before the first paint to prevent a flash of incorrect theme.

Toggle button: any element with the class `.theme-toggle` will toggle the theme when clicked.

---

## Accessibility

This theme targets **WCAG 2.1 AA** compliance:

- All interactive elements have visible `:focus-visible` outlines (3px `--color-primary` with 2px offset).
- Color contrast ratios meet 4.5:1 for normal text and 3:1 for large text.
- Skip-to-content link is included in `header.php`.
- All icon-only buttons include `aria-label`.
- SVG icons include `aria-hidden="true"` and `focusable="false"`.
- Modal dialogs use `role="dialog"`, `aria-modal`, `aria-labelledby`, and trap focus.
- Navigation menus include `role="navigation"` and descriptive `aria-label`.
- Images use meaningful `alt` text or `alt=""` with `aria-hidden` for decorative images.
- Animations respect `prefers-reduced-motion: reduce`.
