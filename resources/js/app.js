/**
 * Front-end behaviour.
 *
 * Three small features, all progressive enhancements — the page works with
 * JavaScript disabled, it just loses the menu toggle and the copy buttons.
 *
 *   1. Header disclosures — the search panel and the mobile category menu.
 *   2. Share row — "copy link" and the native mobile share sheet.
 *   3. Copy buttons — the feed address on /rss.
 *   4. Contact popup — the dialog that replaced the site's email addresses.
 *   5. Cookie consent — gates Google Analytics through Consent Mode.
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

/* -------------------------------------------------------------------------
 * 3. Copy-to-clipboard buttons
 * ---------------------------------------------------------------------- */

/**
 * Wires any [data-copy-button] to the input named by its data-copy-for,
 * used by the feed address on /rss.
 */
function initCopyButtons() {
    document.querySelectorAll('[data-copy-button]').forEach((button) => {
        const source = document.getElementById(button.dataset.copyFor);
        if (!source) return;

        const feedback = button.closest('div')?.parentElement?.querySelector('[data-copy-feedback]');
        const original = feedback?.textContent;

        button.addEventListener('click', async () => {
            const copied = await copyToClipboard(source.value);

            // Select the text either way, so a manual Ctrl+C still works.
            source.select();

            if (!feedback) return;
            feedback.textContent = copied ? 'Copied to your clipboard.' : 'Press Ctrl+C to copy.';
            setTimeout(() => { feedback.textContent = original; }, 2500);
        });
    });
}

/* -------------------------------------------------------------------------
 * 4. Contact popup
 * ---------------------------------------------------------------------- */

/**
 * The dialog that replaced every mailto: address on the site.
 *
 * Progressive enhancement throughout: the triggers are ordinary links to the
 * contact page, and the form inside posts normally. This only upgrades the
 * experience when <dialog> and fetch() are available.
 */
function initContactDialog() {
    const dialog = document.querySelector('[data-contact-dialog]');
    const triggers = document.querySelectorAll('[data-contact-open]');

    if (!dialog || !triggers.length || typeof dialog.showModal !== 'function') {
        // No dialog support: leave the links alone so they navigate to /contact.
        return;
    }

    const form = dialog.querySelector('[data-contact-form]');
    const formWrap = dialog.querySelector('[data-contact-form-wrap]');
    const success = dialog.querySelector('[data-contact-success]');
    const successText = dialog.querySelector('[data-contact-success-text]');
    const errorBox = dialog.querySelector('[data-contact-errors]');
    const topicField = dialog.querySelector('[data-contact-topic]');
    const blurb = dialog.querySelector('[data-contact-blurb]');
    const submit = dialog.querySelector('[data-contact-submit]');

    // Per-topic helper text, mirroring the labels rendered server-side.
    const blurbs = {};
    topicField?.querySelectorAll('option').forEach((option) => {
        blurbs[option.value] = option.dataset.blurb || '';
    });

    const showErrors = (messages) => {
        if (!errorBox) return;
        errorBox.innerHTML = '';
        const list = document.createElement('ul');
        list.className = 'space-y-1';
        messages.forEach((text) => {
            const item = document.createElement('li');
            item.textContent = text;
            list.appendChild(item);
        });
        errorBox.appendChild(list);
        errorBox.classList.remove('hidden');
    };

    const open = (topic) => {
        errorBox?.classList.add('hidden');
        formWrap?.classList.remove('hidden');
        success?.classList.add('hidden');

        if (topic && topicField) {
            topicField.value = topic;
            if (blurb && blurbs[topic]) blurb.textContent = blurbs[topic];
        }

        dialog.showModal();
        // Focus the first thing the reader actually has to fill in.
        dialog.querySelector('#contact-name')?.focus();
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            open(trigger.dataset.contactTopic);
        });
    });

    dialog.querySelectorAll('[data-contact-close]').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });

    // Clicking the backdrop closes it. The dialog element itself fills the
    // backdrop area, so compare against the actual content box.
    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) return;
        const box = dialog.getBoundingClientRect();
        const outside = event.clientX < box.left || event.clientX > box.right
            || event.clientY < box.top || event.clientY > box.bottom;
        if (outside) dialog.close();
    });

    topicField?.addEventListener('change', () => {
        if (blurb && blurbs[topicField.value]) blurb.textContent = blurbs[topicField.value];
    });

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorBox?.classList.add('hidden');

        if (submit) {
            submit.disabled = true;
            submit.textContent = 'Sending…';
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (response.ok) {
                const data = await response.json();
                if (successText && data.message) successText.textContent = data.message;
                formWrap?.classList.add('hidden');
                success?.classList.remove('hidden');
                form.reset();
                dialog.querySelector('[data-contact-close]')?.focus();
                return;
            }

            if (response.status === 422) {
                const data = await response.json();
                showErrors(Object.values(data.errors || {}).flat());
            } else if (response.status === 429) {
                showErrors(['Too many messages from this connection. Please try again in a minute.']);
            } else {
                showErrors(['Something went wrong sending that. Please try again.']);
            }
        } catch {
            // Offline, or the request was blocked — let them fall back.
            showErrors(['We could not reach the server. Please check your connection and try again.']);
        } finally {
            if (submit) {
                submit.disabled = false;
                submit.textContent = 'Send message';
            }
        }
    });

    // The no-JS path redirects back with a flash; reopen so the reader sees it.
    if (dialog.dataset.contactAutoOpen === '1') {
        dialog.showModal();
    }
}

/* -------------------------------------------------------------------------
 * 5. Cookie consent
 * ---------------------------------------------------------------------- */

const CONSENT_KEY = 'nyvora-consent';

/**
 * Read the stored choice, tolerating browsers where storage is unavailable.
 *
 * @returns {'granted'|'denied'|null}
 */
function storedConsent() {
    try {
        const value = localStorage.getItem(CONSENT_KEY);
        return value === 'granted' || value === 'denied' ? value : null;
    } catch {
        return null;
    }
}

/**
 * The banner that gates analytics and advertising cookies.
 *
 * The tag on the page starts with everything denied (see <x-analytics>), so
 * until this sends an "update" nothing is stored. The choice lives in
 * localStorage rather than a cookie, so declining does not itself set one.
 */
function initConsentBanner() {
    const banner = document.querySelector('[data-consent-banner]');
    if (!banner) return;

    // Already answered on a previous visit — the tag re-applied it inline.
    if (storedConsent() !== null) return;

    banner.hidden = false;

    const decide = (state) => {
        try {
            localStorage.setItem(CONSENT_KEY, state);
        } catch {
            // Storage blocked; the denied defaults simply stay in force.
        }

        if (typeof window.gtag === 'function') {
            window.gtag('consent', 'update', {
                ad_storage: state,
                ad_user_data: state,
                ad_personalization: state,
                analytics_storage: state,
            });
        }

        banner.hidden = true;
    };

    banner.querySelector('[data-consent-accept]')?.addEventListener('click', () => decide('granted'));
    banner.querySelector('[data-consent-reject]')?.addEventListener('click', () => decide('denied'));
}

document.addEventListener('DOMContentLoaded', () => {
    initHeader();
    initShare();
    initCopyButtons();
    initContactDialog();
    initConsentBanner();
});
