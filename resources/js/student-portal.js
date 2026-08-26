/*
 |--------------------------------------------------------------------------
 | Student portal shell
 |--------------------------------------------------------------------------
 |
 | Behaviour that belongs to the portal *chrome* rather than to any single
 | screen: the off-canvas sidebar, the small pill dropdowns in page headers,
 | and the news carousel in the right rail.
 |
 | Deliberately dependency-free (no jQuery, no tw-starter) so it works on
 | every portal page whether or not a screen-specific bundle is loaded. The
 | shared tw-starter modals used by the contact-update forms keep their own
 | `data-tw-toggle` wiring.
 */

(function () {
    "use strict";

    /* ── Off-canvas sidebar ─────────────────────────────────────────────── */

    const sidebar = document.getElementById("spfSidebar");
    const toggle = document.getElementById("spfSidebarToggle");
    const backdrop = document.getElementById("spfBackdrop");

    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove("is-open");
        if (backdrop) backdrop.classList.remove("is-open");
        if (toggle) toggle.setAttribute("aria-expanded", "false");
    }

    if (toggle && sidebar) {
        toggle.addEventListener("click", function () {
            const open = sidebar.classList.toggle("is-open");
            if (backdrop) backdrop.classList.toggle("is-open", open);
            toggle.setAttribute("aria-expanded", open ? "true" : "false");
        });
    }

    if (backdrop) {
        backdrop.addEventListener("click", closeSidebar);
    }

    /* ── Pill dropdowns ─────────────────────────────────────────────────── */

    function closeAllDropdowns(except) {
        document.querySelectorAll(".spf-dd__menu.is-open").forEach(function (menu) {
            if (menu !== except) menu.classList.remove("is-open");
        });
    }

    document.addEventListener("click", function (event) {
        const trigger = event.target.closest("[data-spf-dd]");

        if (trigger) {
            event.preventDefault();
            const menu = document.getElementById(trigger.getAttribute("data-spf-dd"));
            if (!menu) return;
            const willOpen = !menu.classList.contains("is-open");
            closeAllDropdowns(menu);
            menu.classList.toggle("is-open", willOpen);
            return;
        }

        // A click inside an open menu should not close it — except on an item,
        // which navigates or opens a modal anyway.
        const insideMenu = event.target.closest(".spf-dd__menu");
        if (insideMenu && !event.target.closest(".spf-dd__item")) return;

        closeAllDropdowns(null);
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeAllDropdowns(null);
            closeSidebar();
        }
    });

    /* ── Tab strips (module details) ──────────────────────── */

    document.querySelectorAll("[data-spf-tabs]").forEach(function (strip) {
        const buttons = Array.prototype.slice.call(
            strip.querySelectorAll("[data-spf-tab]")
        );

        buttons.forEach(function (button) {
            button.addEventListener("click", function () {
                const target = button.getAttribute("data-spf-tab");

                buttons.forEach(function (other) {
                    const on = other === button;
                    other.classList.toggle("is-active", on);
                    const panel = document.getElementById(
                        other.getAttribute("data-spf-tab")
                    );
                    if (panel) panel.classList.toggle("hidden", !on);
                });

                // Tabulator measures zero width while hidden, so nudge it once
                // the panel is actually on screen.
                window.dispatchEvent(new Event("resize"));
                void target;
            });
        });
    });

    /* ── Attendance excuse selection count ─────────────────── */

    const excuseCount = document.getElementById("excuse-count");

    if (excuseCount) {
        const boxes = document.querySelectorAll(
            "[data-excuse-list] input[type=checkbox]"
        );

        const renderCount = function () {
            let n = 0;
            boxes.forEach(function (box) {
                if (box.checked) n++;
            });
            excuseCount.textContent =
                n === 0
                    ? "No sessions selected yet"
                    : n + (n === 1 ? " session selected" : " sessions selected");
        };

        boxes.forEach(function (box) {
            box.addEventListener("change", renderCount);
        });
    }

    /* ── Order list filters ────────────────────────────────── */

    const orderTable = document.querySelector("[data-order-table]");

    if (orderTable) {
        const rows = Array.prototype.slice.call(
            orderTable.querySelectorAll("[data-order-row]")
        );
        const search = document.getElementById("orderSearch");
        const statusLabel = document.getElementById("orderStatusLabel");
        const count = orderTable.querySelector("[data-order-count]");
        const noun = orderTable.querySelector("[data-order-noun]");
        let status = "";

        const applyFilters = function () {
            const term = search ? search.value.trim().toLowerCase() : "";
            let shown = 0;

            rows.forEach(function (row) {
                const matchesTerm =
                    term === "" ||
                    (row.getAttribute("data-invoice") || "").indexOf(term) !== -1;
                const matchesStatus =
                    status === "" || row.getAttribute("data-status") === status;
                const visible = matchesTerm && matchesStatus;

                row.style.display = visible ? "" : "none";
                if (visible) shown++;
            });

            if (count) count.textContent = shown;
            if (noun) noun.textContent = shown === 1 ? "order" : "orders";
        };

        if (search) search.addEventListener("input", applyFilters);

        orderTable.ownerDocument
            .querySelectorAll("[data-order-status]")
            .forEach(function (option) {
                option.addEventListener("click", function () {
                    status = option.getAttribute("data-order-status");
                    if (statusLabel) statusLabel.textContent = option.textContent.trim();
                    applyFilters();
                });
            });
    }

    /* ── Basket hover card ─────────────────────────────────── */

    const cartWrap = document.querySelector(".spf-cartwrap");

    if (cartWrap) {
        // Brushing past the icon should not flash the card open, and moving
        // the pointer towards it should not snap it shut.
        const OPEN_DELAY = 260;
        const CLOSE_DELAY = 420;
        let openTimer = null;
        let closeTimer = null;

        const clearTimers = function () {
            clearTimeout(openTimer);
            clearTimeout(closeTimer);
        };

        const open = function () {
            clearTimers();
            cartWrap.classList.add("is-open");
        };

        const close = function () {
            clearTimers();
            cartWrap.classList.remove("is-open");
        };

        // mouseenter/mouseleave treat the wrapper and its descendants as one
        // region, so moving between the icon and the card does not close it.
        cartWrap.addEventListener("mouseenter", function () {
            clearTimers();
            openTimer = setTimeout(open, OPEN_DELAY);
        });

        cartWrap.addEventListener("mouseleave", function () {
            clearTimers();
            closeTimer = setTimeout(close, CLOSE_DELAY);
        });

        // Keyboard users get it without waiting.
        cartWrap.addEventListener("focusin", open);
        cartWrap.addEventListener("focusout", close);
    }

    /* ── News carousel ──────────────────────────────────────────────────── */

    const news = document.getElementById("spfNews");

    if (news) {
        const slides = Array.prototype.slice.call(
            news.querySelectorAll("[data-news-slide]")
        );
        const dots = Array.prototype.slice.call(
            news.querySelectorAll("[data-news-dot]")
        );
        let index = 0;

        // A clamped body can only be measured once it is on screen, so this
        // runs for whichever slide has just become visible.
        function syncClamp(slide) {
            const body = slide.querySelector("[data-news-body]");
            const more = slide.querySelector("[data-news-more]");
            if (!body || !more) return;

            body.classList.remove("is-expanded");
            more.textContent = "Read more →";

            const overflows = body.scrollHeight > body.clientHeight + 1;
            body.classList.toggle("is-clamped", overflows);
            more.hidden = !overflows;
        }

        function render() {
            slides.forEach(function (slide, i) {
                slide.style.display = i === index ? "" : "none";
            });
            dots.forEach(function (dot, i) {
                dot.classList.toggle("is-active", i === index);
            });
            if (slides[index]) syncClamp(slides[index]);
        }

        news.addEventListener("click", function (event) {
            const more = event.target.closest("[data-news-more]");
            if (!more) return;

            const body = more.closest(".spf-news__item").querySelector("[data-news-body]");
            const expanded = body.classList.toggle("is-expanded");
            more.textContent = expanded ? "Show less ↑" : "Read more →";
        });

        news.querySelectorAll("[data-news-nav]").forEach(function (button) {
            button.addEventListener("click", function () {
                const step = parseInt(button.getAttribute("data-news-nav"), 10);
                index = (index + step + slides.length) % slides.length;
                render();
            });
        });

        dots.forEach(function (dot, i) {
            dot.addEventListener("click", function () {
                index = i;
                render();
            });
        });

        if (slides.length) render();
    }
})();
