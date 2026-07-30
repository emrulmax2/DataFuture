/*
 * Chrome for the redesigned admission module: header menus, the global
 * search box and the collapsible filter panel.
 *
 * Deliberately standalone — it never touches the Tabulator tables, the
 * edit forms or any of the modals, so the existing admission*.js bundles
 * keep owning all the data behaviour.
 */

import { createIcons, icons } from "lucide";

const CLOSE_ON_OUTSIDE = [];

/** Register a node/reset pair so one document click can close everything. */
function registerDismissable(root, close) {
    CLOSE_ON_OUTSIDE.push({ root, close });
}

document.addEventListener("click", (event) => {
    CLOSE_ON_OUTSIDE.forEach(({ root, close }) => {
        if (root.isConnected && !root.contains(event.target)) close();
    });
});

document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape") return;
    CLOSE_ON_OUTSIDE.forEach(({ close }) => close());
});

/* ------------------------------------------------------------------ *
 * Header — "Students" nav dropdown + profile dropdown
 * ------------------------------------------------------------------ */

function initHeaderMenus() {
    document.querySelectorAll("[data-adm-nav-group]").forEach((group) => {
        const trigger = group.querySelector("[data-adm-nav-toggle]");
        if (!trigger) return;

        const close = () => group.classList.remove("is-open");

        trigger.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            const wasOpen = group.classList.contains("is-open");
            CLOSE_ON_OUTSIDE.forEach(({ close: c }) => c());
            group.classList.toggle("is-open", !wasOpen);
        });

        registerDismissable(group, close);
    });

    const profile = document.querySelector("[data-adm-profile]");
    if (profile) {
        const trigger = profile.querySelector("[data-adm-profile-toggle]");
        const close = () => profile.classList.remove("is-open");

        if (trigger) {
            trigger.addEventListener("click", (event) => {
                event.preventDefault();
                event.stopPropagation();
                const wasOpen = profile.classList.contains("is-open");
                CLOSE_ON_OUTSIDE.forEach(({ close: c }) => c());
                profile.classList.toggle("is-open", !wasOpen);
            });
        }

        registerDismissable(profile, close);
    }
}

/* ------------------------------------------------------------------ *
 * Header — global search
 * ------------------------------------------------------------------ */

function initHeaderSearch() {
    const box = document.querySelector("[data-adm-search]");
    const toggle = document.querySelector("[data-adm-search-toggle]");
    if (!box || !toggle) return;

    const input = box.querySelector("[data-adm-search-input]");
    const closeBtn = box.querySelector("[data-adm-search-close]");
    const results = box.querySelector("[data-adm-search-results]");

    const clearResults = () => {
        box.dataset.searchOpen = "false";
        if (results) {
            results.classList.remove("is-open");
            results.innerHTML = "";
        }
    };

    const open = () => {
        box.classList.add("is-open");
        toggle.style.display = "none";
        if (input) input.focus();
    };

    const close = () => {
        box.classList.remove("is-open");
        toggle.style.display = "";
        clearResults();
    };

    toggle.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();
        open();
    });

    if (closeBtn) {
        closeBtn.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            close();
        });
    }

    registerDismissable(box, clearResults);
}

/* ------------------------------------------------------------------ *
 * Collapsible filter panels
 * ------------------------------------------------------------------ */

function initFilterToggles() {
    document.querySelectorAll("[data-adm-filter-toggle]").forEach((button) => {
        const panel = document.querySelector(button.getAttribute("data-adm-filter-toggle"));
        if (!panel) return;

        const label = button.querySelector("[data-adm-filter-label]");
        const storageKey = button.getAttribute("data-adm-filter-key");

        const paint = (open) => {
            panel.hidden = !open;
            if (label) label.textContent = open ? "Hide Search Filters" : "Show Search Filters";
            button.setAttribute("aria-expanded", open ? "true" : "false");
        };

        // Remember the last state so a page reload after "Go" does not
        // collapse the filters the user just spent time filling in.
        let open = true;
        if (storageKey) {
            const stored = window.localStorage.getItem(storageKey);
            if (stored !== null) open = stored === "1";
        }
        paint(open);

        button.addEventListener("click", (event) => {
            event.preventDefault();
            open = panel.hidden;
            paint(open);
            if (storageKey) window.localStorage.setItem(storageKey, open ? "1" : "0");
        });
    });
}

/* ------------------------------------------------------------------ *
 * Boot
 * ------------------------------------------------------------------ */

function boot() {
    if (!document.body.classList.contains("adm-body")) return;
    initHeaderMenus();
    initHeaderSearch();
    initFilterToggles();
    createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
} else {
    boot();
}
