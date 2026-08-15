/**
 * Front-end behaviour.
 *
 * Two small features, both progressive enhancements — the page works with
 * JavaScript disabled, it just loses the mobile menu toggle and the
 * copy-to-clipboard button.
 *
 *   1. Header disclosures — the search panel and the mobile category menu.
 *   2. Share row — "copy link" and the native mobile share sheet.
 */

/* -------------------------------------------------------------------------
 * 1. Header disclosures
 * ---------------------------------------------------------------------- */

/**
 * Wire a button to the panel it controls.
 *
 * @param {string} toggleSelector
 * @param {string} panelSelector
 * @param {() => void} [onOpen] runs after the panel becomes visible
 * @returns {{open: () => void, close: () => void, isOpen: () => boolean} | null}
 */
function createDisclosure(toggleSelector, panelSelector, onOpen) {
    const toggle = document.querySelector(toggleSelector);
    const panel = document.querySelector(panelSelector);

    if (!toggle || !panel) {
        return null;
    }

    const isOpen = () => !panel.hidden;

    const close = () => {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
    };

    const open = () => {
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        onOpen?.();
    };

    toggle.addEventListener('click', () => (isOpen() ? close() : open()));

    return { open, close, isOpen };
}

function initHeader() {
    const search = createDisclosure('[data-search-toggle]', '[data-search-panel]', () => {
        document.querySelector('[data-search-input]')?.focus();
    });

    const nav = createDisclosure('[data-nav-toggle]', '[data-nav-panel]');

    // Only one panel at a time.
    document.querySelector('[data-search-toggle]')?.addEventListener('click', () => nav?.close());
    document.querySelector('[data-nav-toggle]')?.addEventListener('click', () => search?.close());

    // Escape closes whichever panel is open.
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            search?.close();
            nav?.close();
        }
    });
}

/* -------------------------------------------------------------------------
 * 2. Share row
 * ---------------------------------------------------------------------- */

/**
 * Copy text to the clipboard, falling back to a hidden textarea on browsers
 * (or insecure origins) where the async Clipboard API is unavailable.
 *
 * @param {string} value
 * @returns {Promise<boolean>} whether the copy succeeded
 */
async function copyToClipboard(value) {
    if (navigator.clipboard?.writeText) {
        try {
            await navigator.clipboard.writeText(value);
            return true;
        } catch {
            // Permission denied — fall through to the legacy path.
        }
    }

    const field = document.createElement('textarea');
    field.value = value;
    field.setAttribute('readonly', '');
    field.style.position = 'fixed';
    field.style.opacity = '0';
    document.body.appendChild(field);
    field.select();

    let copied = false;
    try {
        copied = document.execCommand('copy');
    } catch {
        copied = false;
    }

    field.remove();
    return copied;
}

function initShare() {
    document.querySelectorAll('[data-share]').forEach((row) => {
        const url = row.dataset.shareUrl;
        const title = row.dataset.shareTitle;
        const feedback = row.querySelector('[data-share-feedback]');

        const say = (message) => {
            if (!feedback) return;
            feedback.textContent = message;
            setTimeout(() => { feedback.textContent = ''; }, 2500);
        };

        row.querySelector('[data-share-copy]')?.addEventListener('click', async () => {
            say(await copyToClipboard(url) ? 'Link copied' : 'Press Ctrl+C to copy');
        });

        // The share sheet only exists on supporting browsers, mostly mobile.
        const nativeButton = row.querySelector('[data-share-native]');
        if (nativeButton && navigator.share) {
            nativeButton.hidden = false;
            nativeButton.addEventListener('click', async () => {
                try {
                    await navigator.share({ title, url });
                } catch {
                    // The reader dismissed the sheet; nothing to do.
                }
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initHeader();
    initShare();
});
