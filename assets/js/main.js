/**
 * Main JavaScript Entry Point
 *
 * Imports and initialises all feature modules:
 *   - Navigation (hamburger, dropdowns, sticky header)
 *   - Modals (open/close/focus trap)
 *   - Animations (scroll-reveal, counters)
 *   - Theme toggle (light / dark mode)
 *
 * Vanilla ES6+ modules, no dependencies.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

'use strict';

import { initNavigation }                       from './navigation.js';
import { initModals }                           from './modal.js';
import { initAnimations, initCounters }         from './animations.js';

/* ----------------------------------------------------------
   Theme Toggle (Light / Dark Mode)
   ---------------------------------------------------------- */

/**
 * Storage key for the persisted theme preference.
 * @type {string}
 */
const THEME_STORAGE_KEY = 'theme-associatif-theme';

/**
 * Returns the user's current theme preference.
 * Priority: localStorage > OS setting > 'light'.
 *
 * @returns {'light'|'dark'}
 */
function getInitialTheme() {
    const stored = localStorage.getItem(THEME_STORAGE_KEY);

    if (stored === 'dark' || stored === 'light') {
        return stored;
    }

    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        return 'dark';
    }

    return 'light';
}

/**
 * Applies the given theme to the document root element.
 *
 * @param {'light'|'dark'} theme
 */
function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
}

/**
 * Updates the accessible label of each theme toggle button.
 *
 * @param {'light'|'dark'} theme
 */
function updateToggleLabel(theme) {
    document.querySelectorAll('.theme-toggle').forEach((btn) => {
        const label = theme === 'dark'
            ? btn.dataset.labelLight || 'Switch to light mode'
            : btn.dataset.labelDark  || 'Switch to dark mode';

        btn.setAttribute('aria-label', label);
    });
}

/**
 * Persists the theme choice and applies it.
 *
 * @param {'light'|'dark'} theme
 */
function setTheme(theme) {
    applyTheme(theme);
    updateToggleLabel(theme);

    try {
        localStorage.setItem(THEME_STORAGE_KEY, theme);
    } catch (err) {
        // Storage may be unavailable in private browsing.
    }
}

/**
 * Initialises the theme toggle buttons.
 */
function initThemeToggle() {
    const currentTheme = getInitialTheme();

    applyTheme(currentTheme);
    updateToggleLabel(currentTheme);

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('.theme-toggle');

        if (!toggle) {
            return;
        }

        const isDark   = document.documentElement.getAttribute('data-theme') === 'dark';
        const newTheme = isDark ? 'light' : 'dark';

        setTheme(newTheme);
    });

    // Sync across tabs.
    window.addEventListener('storage', (event) => {
        if (event.key === THEME_STORAGE_KEY && (event.newValue === 'dark' || event.newValue === 'light')) {
            applyTheme(event.newValue);
            updateToggleLabel(event.newValue);
        }
    });

    // Follow OS setting changes when no stored preference exists.
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (mq) => {
        if (!localStorage.getItem(THEME_STORAGE_KEY)) {
            const theme = mq.matches ? 'dark' : 'light';

            applyTheme(theme);
            updateToggleLabel(theme);
        }
    });
}

/* ----------------------------------------------------------
   DOM Ready Initialisation
   ---------------------------------------------------------- */

/**
 * Bootstraps all modules once the DOM is fully parsed.
 */
function bootstrap() {
    // Remove no-js class, added as a progressive enhancement baseline.
    document.documentElement.classList.remove('no-js');
    document.documentElement.classList.add('js');

    initThemeToggle();
    initNavigation();
    initModals();
    initAnimations();
    initCounters();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrap);
} else {
    bootstrap();
}
