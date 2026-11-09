/*
 * Admission portal shell: the small pieces the top bar needs on top of the
 * app-wide header script (js/search.js owns opening/closing the menus and
 * the global search itself through `data-header-menu` / `data-global-search`).
 *
 *  - keeps every toggle's aria-expanded in step with the menu state
 *  - focuses the search input when the search menu opens
 */
(function () {
    "use strict";

    if (!document.body.classList.contains("adp-body")) return;

    const toggles = Array.from(document.querySelectorAll("[data-global-header] [data-header-menu-toggle]"));

    const sync = () => {
        toggles.forEach((toggle) => {
            const menu = toggle.closest("[data-header-menu]");
            toggle.setAttribute("aria-expanded", menu && menu.dataset.menuOpen === "true" ? "true" : "false");
        });
    };

    toggles.forEach((toggle) => {
        // search.js stops propagation on the toggle click, so the state is
        // read here on the same element rather than from the document.
        toggle.addEventListener("click", () => {
            window.setTimeout(() => {
                sync();
                if (!toggle.hasAttribute("data-adp-search-toggle")) return;
                const menu = toggle.closest("[data-header-menu]");
                const input = menu && menu.querySelector("[data-global-search-input]");
                if (menu && menu.dataset.menuOpen === "true" && input) input.focus();
            }, 0);
        });
    });

    document.addEventListener("click", () => window.setTimeout(sync, 0));
    document.addEventListener("keydown", () => window.setTimeout(sync, 0));
})();
