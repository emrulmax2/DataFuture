/*
 * Header behavior for the redesigned Agent Management landing page.
 *
 * Search results are rendered by resources/js/search.js through the shared
 * data-global-search attributes. This file only owns the compact dark header
 * chrome for this module.
 */

import { createIcons, icons } from "lucide";

const dismissables = [];

function registerDismissable(root, close) {
    dismissables.push({ root, close });
}

function closeDismissables() {
    dismissables.forEach(({ root, close }) => {
        if (root.isConnected) {
            close();
        }
    });
}

function initSearchPill() {
    const search = document.querySelector("[data-agm-search]");
    if (!search) return;

    const toggle = search.querySelector("[data-agm-search-toggle]");
    const closeButton = search.querySelector("[data-agm-search-close]");
    const input = search.querySelector("[data-agm-search-input]");
    const results = search.querySelector("[data-global-search-results]");

    const open = () => {
        search.classList.add("is-open");
        window.requestAnimationFrame(() => input?.focus());
    };

    const close = () => {
        search.classList.remove("is-open");
        search.dataset.searchOpen = "false";
        if (input) {
            input.value = "";
        }
        if (results) {
            results.innerHTML = "";
        }
    };

    toggle?.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();
        open();
    });

    closeButton?.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();
        close();
    });

    input?.addEventListener("focus", open);

    registerDismissable(search, () => {
        search.dataset.searchOpen = "false";
        if (results) {
            results.innerHTML = "";
        }
    });

    document.addEventListener("keydown", (event) => {
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
            open();
        }
    });
}

document.addEventListener("click", (event) => {
    dismissables.forEach(({ root, close }) => {
        if (root.isConnected && !root.contains(event.target)) {
            close();
        }
    });
});

document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        closeDismissables();
    }
});

document.addEventListener("DOMContentLoaded", () => {
    initSearchPill();
    createIcons({
        icons,
        "stroke-width": 1.8,
        nameAttr: "data-lucide",
    });
});
