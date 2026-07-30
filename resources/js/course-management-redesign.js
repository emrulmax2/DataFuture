/*
 * Chrome for the redesigned Course Management module: header menus, the
 * global search box and the sidebar accordion.
 *
 * Deliberately standalone — it never touches the Tabulator tables, the edit
 * forms or any of the modals, so the existing course-management bundles
 * (courses.js, semester.js, plan.js, …) keep owning all the data behaviour.
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
    document.querySelectorAll("[data-cm-nav-group]").forEach((group) => {
        const trigger = group.querySelector("[data-cm-nav-toggle]");
        if (!trigger) return;

        const close = () => {
            group.classList.remove("is-open");
            trigger.setAttribute("aria-expanded", "false");
        };

        trigger.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            const wasOpen = group.classList.contains("is-open");
            CLOSE_ON_OUTSIDE.forEach(({ close: c }) => c());
            group.classList.toggle("is-open", !wasOpen);
            trigger.setAttribute("aria-expanded", wasOpen ? "false" : "true");
        });

        registerDismissable(group, close);
    });

    const profile = document.querySelector("[data-cm-profile]");
    if (profile) {
        const trigger = profile.querySelector("[data-cm-profile-toggle]");
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
    const box = document.querySelector("[data-cm-search]");
    const toggle = document.querySelector("[data-cm-search-toggle]");
    if (!box) return;

    const input = box.querySelector("[data-cm-search-input]");
    const closeBtn = box.querySelector("[data-cm-search-close]");
    const results = box.querySelector("[data-cm-search-results]");

    const clearResults = () => {
        box.dataset.searchOpen = "false";
        if (results) {
            results.classList.remove("is-open");
            results.innerHTML = "";
        }
    };

    const open = () => {
        box.classList.add("is-open");
        if (toggle) toggle.style.display = "none";
        if (input) input.focus();
    };

    const close = () => {
        box.classList.remove("is-open");
        if (toggle) toggle.style.display = "";
        clearResults();
    };

    if (toggle) {
        toggle.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            open();
        });
    }

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
 * Sidebar accordion
 * ------------------------------------------------------------------ */

function initSideNav() {
    document.querySelectorAll("[data-cm-sidenav-group]").forEach((group) => {
        const trigger = group.querySelector("[data-cm-sidenav-toggle]");
        if (!trigger) return;

        const storageKey = group.getAttribute("data-cm-sidenav-key");
        // A group holding the current page always opens, whatever was stored —
        // collapsing the section you are standing in reads as a broken menu.
        const holdsActive = group.hasAttribute("data-cm-sidenav-active");

        // Open is the default (and what the markup ships with, so there is no
        // expand-flash on load); only an explicit stored "0" collapses.
        let open = true;
        if (!holdsActive && storageKey) {
            open = window.localStorage.getItem(storageKey) !== "0";
        }

        const paint = (next) => {
            group.classList.toggle("is-open", next);
            trigger.setAttribute("aria-expanded", next ? "true" : "false");
        };

        paint(open);

        trigger.addEventListener("click", (event) => {
            event.preventDefault();
            open = !group.classList.contains("is-open");
            paint(open);
            if (storageKey) window.localStorage.setItem(storageKey, open ? "1" : "0");
        });
    });
}

/* ------------------------------------------------------------------ *
 * Boot
 * ------------------------------------------------------------------ */

function boot() {
    if (!document.body.classList.contains("cm-body")) return;
    initHeaderMenus();
    initHeaderSearch();
    initSideNav();
    createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
} else {
    boot();
}
