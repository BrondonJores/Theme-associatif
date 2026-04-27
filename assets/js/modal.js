/**
 * Modal Component
 *
 * Handles open/close, focus trapping and keyboard (Escape) support.
 * Fully accessible: manages aria-modal, aria-hidden, and focus lifecycle.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

'use strict';

/** @type {HTMLElement|null} Element that triggered the currently open modal. */
let lastFocusedElement = null;

/**
 * Returns all focusable elements within a container.
 *
 * @param {HTMLElement} container
 * @returns {HTMLElement[]}
 */
function getFocusableElements(container) {
    const selectors = [
        'a[href]',
        'button:not([disabled])',
        'input:not([disabled])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        '[tabindex]:not([tabindex="-1"])',
        'details > summary',
    ].join(', ');

    return Array.from(container.querySelectorAll(selectors)).filter(
        (el) => !el.closest('[hidden]') && !el.closest('[aria-hidden="true"]')
    );
}

/**
 * Traps keyboard focus within a modal dialog.
 *
 * @param {KeyboardEvent} event
 * @param {HTMLElement}   modal
 */
function trapFocus(event, modal) {
    if (event.key !== 'Tab') {
        return;
    }

    const focusable = getFocusableElements(modal);

    if (focusable.length === 0) {
        event.preventDefault();
        return;
    }

    const first = focusable[0];
    const last  = focusable[focusable.length - 1];

    if (event.shiftKey) {
        if (document.activeElement === first) {
            event.preventDefault();
            last.focus();
        }
    } else {
        if (document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }
}

/**
 * Opens a modal by its ID.
 *
 * @param {string} modalId
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);

    if (!modal) {
        return;
    }

    lastFocusedElement = document.activeElement;

    modal.removeAttribute('hidden');
    modal.setAttribute('aria-hidden', 'false');
    modal.setAttribute('aria-modal', 'true');
    document.body.setAttribute('data-modal-open', 'true');
    document.body.style.overflow = 'hidden';

    // Move focus to the first focusable element or the modal itself.
    const focusable = getFocusableElements(modal);
    const target    = focusable[0] || modal;

    // Defer focus to allow CSS transitions to begin.
    requestAnimationFrame(() => {
        target.focus({ preventScroll: true });
    });

    modal.addEventListener('keydown', handleModalKeydown);

    // Dispatch a custom event for external hooks.
    modal.dispatchEvent(new CustomEvent('modal:open', { bubbles: true }));
}

/**
 * Closes a modal by its ID or element reference.
 *
 * @param {string|HTMLElement} modalOrId
 */
function closeModal(modalOrId) {
    const modal = typeof modalOrId === 'string'
        ? document.getElementById(modalOrId)
        : modalOrId;

    if (!modal) {
        return;
    }

    modal.setAttribute('hidden', '');
    modal.setAttribute('aria-hidden', 'true');
    modal.removeAttribute('aria-modal');
    document.body.removeAttribute('data-modal-open');
    document.body.style.overflow = '';

    modal.removeEventListener('keydown', handleModalKeydown);

    // Restore focus to the element that triggered the modal.
    if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
        lastFocusedElement.focus({ preventScroll: true });
        lastFocusedElement = null;
    }

    modal.dispatchEvent(new CustomEvent('modal:close', { bubbles: true }));
}

/**
 * Keydown handler attached to the active modal.
 *
 * @param {KeyboardEvent} event
 */
function handleModalKeydown(event) {
    const modal = event.currentTarget;

    if (event.key === 'Escape') {
        closeModal(modal);
        return;
    }

    trapFocus(event, modal);
}

/**
 * Handles clicks on backdrop overlays to close the modal.
 *
 * @param {MouseEvent} event
 */
function handleBackdropClick(event) {
    const modal = event.currentTarget;

    if (event.target === modal || event.target.classList.contains('modal__backdrop')) {
        closeModal(modal);
    }
}

/**
 * Attaches all modal event listeners in the document.
 */
function initModals() {
    // Open triggers: [data-modal-open="modal-id"]
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-modal-open]');

        if (trigger) {
            event.preventDefault();
            openModal(trigger.dataset.modalOpen);
        }
    });

    // Close triggers: [data-modal-close] or .modal__close inside a modal
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-modal-close], .modal__close');

        if (trigger) {
            event.preventDefault();
            const modal = trigger.closest('[role="dialog"]');

            if (modal) {
                closeModal(modal);
            }
        }
    });

    // Backdrop click on each modal
    document.querySelectorAll('[role="dialog"]').forEach((modal) => {
        modal.addEventListener('click', handleBackdropClick);
    });
}

export { initModals, openModal, closeModal };
