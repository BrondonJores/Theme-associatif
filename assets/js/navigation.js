/**
 * Navigation Module
 *
 * Handles:
 *   - Mobile hamburger menu toggle
 *   - Dropdown keyboard navigation (arrow keys, Enter, Escape, Tab)
 *   - Skip link focus management
 *   - Sticky header scroll effects
 *
 * Vanilla ES6+, no dependencies.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

'use strict';

/** @type {HTMLElement|null} */
const header     = document.getElementById('site-header');
/** @type {HTMLElement|null} */
const navToggle  = document.getElementById('nav-toggle');
/** @type {HTMLElement|null} */
const mobileMenu = document.getElementById('mobile-menu');
/** @type {HTMLElement|null} */
const backdrop   = document.getElementById('mobile-menu-backdrop');

/* ----------------------------------------------------------
   Hamburger / Mobile Menu
   ---------------------------------------------------------- */

/**
 * Opens the mobile navigation drawer.
 */
function openMobileMenu() {
    if (!navToggle || !mobileMenu) {
        return;
    }

    navToggle.setAttribute('aria-expanded', 'true');
    mobileMenu.classList.add('is-open');
    mobileMenu.removeAttribute('hidden');
    document.body.style.overflow = 'hidden';

    if (backdrop) {
        backdrop.classList.add('is-open');
    }

    // Move focus to first link in the menu.
    const firstLink = mobileMenu.querySelector('a, button');

    if (firstLink) {
        firstLink.focus();
    }
}

/**
 * Closes the mobile navigation drawer.
 */
function closeMobileMenu() {
    if (!navToggle || !mobileMenu) {
        return;
    }

    navToggle.setAttribute('aria-expanded', 'false');
    mobileMenu.classList.remove('is-open');
    document.body.style.overflow = '';

    if (backdrop) {
        backdrop.classList.remove('is-open');
    }

    navToggle.focus();
}

/**
 * Initialises the hamburger toggle.
 */
function initHamburger() {
    if (!navToggle || !mobileMenu) {
        return;
    }

    navToggle.addEventListener('click', () => {
        const isExpanded = navToggle.getAttribute('aria-expanded') === 'true';

        if (isExpanded) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    });

    // Close on backdrop click.
    if (backdrop) {
        backdrop.addEventListener('click', closeMobileMenu);
    }

    // Close on Escape key.
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && navToggle.getAttribute('aria-expanded') === 'true') {
            closeMobileMenu();
        }
    });

    // Close when viewport expands past mobile breakpoint.
    const breakpoint = window.matchMedia('(min-width: 1024px)');

    const handleBreakpoint = (mq) => {
        if (mq.matches) {
            closeMobileMenu();
        }
    };

    breakpoint.addEventListener('change', handleBreakpoint);
}

/* ----------------------------------------------------------
   Dropdown Keyboard Navigation
   ---------------------------------------------------------- */

/**
 * Retrieves sibling menu items for a given item.
 *
 * @param {HTMLElement} item - The current .menu-item element.
 * @returns {{ items: HTMLElement[], index: number }}
 */
function getSiblingMenuItems(item) {
    const parent = item.parentElement;
    const items  = Array.from(parent.querySelectorAll(':scope > .menu-item'));
    const index  = items.indexOf(item);

    return { items, index };
}

/**
 * Opens a dropdown for the given parent menu item.
 *
 * @param {HTMLElement} menuItem
 */
function openDropdown(menuItem) {
    menuItem.classList.add('is-open');
    const trigger = menuItem.querySelector(':scope > a');

    if (trigger) {
        trigger.setAttribute('aria-expanded', 'true');
    }
}

/**
 * Closes a dropdown for the given parent menu item.
 *
 * @param {HTMLElement} menuItem
 */
function closeDropdown(menuItem) {
    menuItem.classList.remove('is-open');
    const trigger = menuItem.querySelector(':scope > a');

    if (trigger) {
        trigger.setAttribute('aria-expanded', 'false');
    }
}

/**
 * Closes all open dropdowns.
 */
function closeAllDropdowns() {
    document.querySelectorAll('.menu-item-has-children.is-open').forEach(closeDropdown);
}

/**
 * Adds aria-expanded to all dropdown triggers and wires keyboard events.
 */
function initDropdowns() {
    const dropdownItems = document.querySelectorAll(
        '.primary-menu .menu-item-has-children'
    );

    dropdownItems.forEach((item) => {
        const trigger  = item.querySelector(':scope > a');
        const subMenu  = item.querySelector(':scope > .sub-menu');

        if (!trigger || !subMenu) {
            return;
        }

        // Add ARIA attributes.
        trigger.setAttribute('aria-haspopup', 'true');
        trigger.setAttribute('aria-expanded', 'false');
        subMenu.setAttribute('role', 'menu');
        subMenu.querySelectorAll('a').forEach((link) => link.setAttribute('role', 'menuitem'));

        // Toggle on click (mouse users).
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const isOpen = item.classList.contains('is-open');

            closeAllDropdowns();

            if (!isOpen) {
                openDropdown(item);
            }
        });

        // Keyboard: Enter/Space opens dropdown.
        trigger.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                const isOpen = item.classList.contains('is-open');

                closeAllDropdowns();

                if (!isOpen) {
                    openDropdown(item);
                    const firstSubLink = subMenu.querySelector('a');

                    if (firstSubLink) {
                        firstSubLink.focus();
                    }
                }
            }

            // Arrow Down opens dropdown.
            if (event.key === 'ArrowDown') {
                event.preventDefault();

                if (!item.classList.contains('is-open')) {
                    openDropdown(item);
                }

                const firstSubLink = subMenu.querySelector('a');

                if (firstSubLink) {
                    firstSubLink.focus();
                }
            }
        });

        // Arrow navigation inside sub-menu.
        subMenu.addEventListener('keydown', (event) => {
            const links      = Array.from(subMenu.querySelectorAll('a'));
            const activeLink = document.activeElement;
            const idx        = links.indexOf(activeLink);

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                const next = links[idx + 1] || links[0];

                next.focus();
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                const prev = links[idx - 1] || links[links.length - 1];

                prev.focus();
            }

            if (event.key === 'Escape') {
                closeDropdown(item);
                trigger.focus();
            }
        });
    });

    // Close dropdowns when clicking outside.
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.menu-item-has-children')) {
            closeAllDropdowns();
        }
    });

    // Close dropdowns on focusout (Tab away).
    document.addEventListener('focusin', (event) => {
        if (!event.target.closest('.primary-menu')) {
            closeAllDropdowns();
        }
    });
}

/* ----------------------------------------------------------
   Sticky Header Scroll Effect
   ---------------------------------------------------------- */

/**
 * Adds/removes the .is-scrolled class on the site header.
 */
function initStickyHeader() {
    if (!header) {
        return;
    }

    const SCROLL_THRESHOLD = 10;

    const onScroll = () => {
        if (window.scrollY > SCROLL_THRESHOLD) {
            header.classList.add('is-scrolled');
        } else {
            header.classList.remove('is-scrolled');
        }
    };

    window.addEventListener('scroll', onScroll, { passive: true });

    // Set initial state.
    onScroll();
}

/* ----------------------------------------------------------
   Mobile Menu Dropdowns (within .mobile-menu__list)
   ---------------------------------------------------------- */

/**
 * Wires up sub-menu toggles inside the mobile navigation drawer.
 */
function initMobileDropdowns() {
    if (!mobileMenu) {
        return;
    }

    mobileMenu.querySelectorAll('.menu-item-has-children > a').forEach((trigger) => {
        const item = trigger.parentElement;

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            item.classList.toggle('is-open');
        });
    });
}

/* ----------------------------------------------------------
   Public init
   ---------------------------------------------------------- */

/**
 * Initialises all navigation features.
 */
function initNavigation() {
    initHamburger();
    initDropdowns();
    initStickyHeader();
    initMobileDropdowns();
}

export { initNavigation };
