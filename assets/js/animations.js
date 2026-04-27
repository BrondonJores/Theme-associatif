/**
 * Scroll Animations Module
 *
 * Uses IntersectionObserver to trigger CSS animation classes on elements
 * as they enter the viewport. Respects prefers-reduced-motion.
 *
 * Usage: Add data-animate="fade-up" (or other variant) to any element.
 * Supported variants: fade-up, fade-down, fade-left, fade-right, fade-in,
 *                     scale-in, slide-up, zoom-in.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

'use strict';

/**
 * Whether the user prefers reduced motion.
 * @type {boolean}
 */
const prefersReducedMotion = window.matchMedia(
    '(prefers-reduced-motion: reduce)'
).matches;

/**
 * Default IntersectionObserver options.
 * @type {IntersectionObserverInit}
 */
const DEFAULT_OPTIONS = {
    root:       null,
    rootMargin: '0px 0px -60px 0px',
    threshold:  0.1,
};

/**
 * CSS class added when an element is visible.
 * @type {string}
 */
const IS_VISIBLE_CLASS = 'is-visible';

/**
 * CSS class applied to all animated elements before they enter view.
 * @type {string}
 */
const ANIMATE_CLASS = 'will-animate';

/**
 * Creates and returns an IntersectionObserver that adds the is-visible class.
 *
 * @param {IntersectionObserverInit} [options]
 * @returns {IntersectionObserver}
 */
function createScrollObserver(options = DEFAULT_OPTIONS) {
    return new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            const el    = entry.target;
            const delay = el.dataset.animateDelay;

            if (delay) {
                el.style.transitionDelay = `${delay}ms`;
            }

            el.classList.add(IS_VISIBLE_CLASS);

            // Stop observing once the animation has fired.
            if (!el.hasAttribute('data-animate-repeat')) {
                observer.unobserve(el);
            }
        });
    }, options);
}

/**
 * Initialises scroll-triggered animations.
 *
 * If the user prefers reduced motion, all elements are made immediately
 * visible without any animation class.
 */
function initAnimations() {
    const elements = document.querySelectorAll('[data-animate]');

    if (elements.length === 0) {
        return;
    }

    // Respect user motion preferences: show immediately, skip animations.
    if (prefersReducedMotion) {
        elements.forEach((el) => {
            el.classList.add(IS_VISIBLE_CLASS);
            el.removeAttribute('data-animate');
        });

        return;
    }

    // Guard: if IntersectionObserver is unavailable, show all elements.
    if (!('IntersectionObserver' in window)) {
        elements.forEach((el) => el.classList.add(IS_VISIBLE_CLASS));

        return;
    }

    const observer = createScrollObserver();

    elements.forEach((el) => {
        // Mark as pending so CSS can set the initial hidden state.
        el.classList.add(ANIMATE_CLASS);
        el.setAttribute('data-animate-variant', el.dataset.animate);
        observer.observe(el);
    });
}

/**
 * Animates a staggered group of child elements.
 *
 * @param {string} containerSelector - CSS selector for the parent container.
 * @param {string} childSelector     - CSS selector for children to animate.
 * @param {number} [baseDelay=0]     - Base delay in ms before stagger starts.
 * @param {number} [staggerStep=80]  - Delay in ms between each child.
 */
function initStaggerAnimations(
    containerSelector,
    childSelector,
    baseDelay  = 0,
    staggerStep = 80
) {
    const containers = document.querySelectorAll(containerSelector);

    containers.forEach((container) => {
        const children = container.querySelectorAll(childSelector);

        children.forEach((child, index) => {
            if (!child.hasAttribute('data-animate')) {
                child.setAttribute('data-animate', 'fade-up');
            }

            if (!child.hasAttribute('data-animate-delay')) {
                child.setAttribute('data-animate-delay', String(baseDelay + index * staggerStep));
            }
        });
    });

    // Re-run the main init so newly decorated elements get observed.
    initAnimations();
}

/**
 * Counts up a numeric element when it enters the viewport.
 *
 * @param {HTMLElement} el      - Element containing the number.
 * @param {number}      target  - Target number to count to.
 * @param {number}      [duration=1200] - Animation duration in ms.
 */
function animateCounter(el, target, duration = 1200) {
    if (prefersReducedMotion) {
        el.textContent = target.toLocaleString();

        return;
    }

    const start     = 0;
    const startTime = performance.now();

    const step = (currentTime) => {
        const elapsed  = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const eased    = 1 - Math.pow(1 - progress, 3); // ease-out cubic
        const current  = Math.floor(eased * (target - start) + start);

        el.textContent = current.toLocaleString();

        if (progress < 1) {
            requestAnimationFrame(step);
        }
    };

    requestAnimationFrame(step);
}

/**
 * Initialises counter animations for elements with data-counter attribute.
 */
function initCounters() {
    const counterElements = document.querySelectorAll('[data-counter]');

    if (counterElements.length === 0) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        counterElements.forEach((el) => {
            animateCounter(el, parseInt(el.dataset.counter, 10));
        });

        return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            const el     = entry.target;
            const target = parseInt(el.dataset.counter, 10);

            if (!Number.isNaN(target)) {
                animateCounter(el, target);
            }

            obs.unobserve(el);
        });
    }, { threshold: 0.5 });

    counterElements.forEach((el) => observer.observe(el));
}

export { initAnimations, initStaggerAnimations, initCounters };
