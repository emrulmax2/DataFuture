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

    /* ── Basket removal ──────────────────────────────────────────────────
       The hover card lists the first few items and each row can be removed
       without leaving the page. The endpoint returns the remaining cart, so
       the list is rebuilt from that — deleting just the clicked row would
       leave a hidden overflow item stranded behind a "+N more" line. */
    const cartPop = document.getElementById("spfCartPop");
    const cartItems = document.getElementById("spfCartItems");

    if (cartPop && cartItems) {
        const removeUrl = cartItems.getAttribute("data-spf-cart-remove-url") || "";
        const limit = parseInt(cartItems.getAttribute("data-spf-cart-limit"), 10) || 4;

        const money = function (n) {
            return "£" + n.toFixed(2);
        };

        const closeIcon = function () {
            const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            svg.setAttribute("viewBox", "0 0 24 24");
            svg.setAttribute("fill", "none");
            svg.setAttribute("stroke", "currentColor");
            svg.setAttribute("stroke-width", "2");
            svg.setAttribute("stroke-linecap", "round");
            svg.setAttribute("class", "w-3 h-3");
            const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
            path.setAttribute("d", "M18 6 6 18M6 6l12 12");
            svg.appendChild(path);
            return svg;
        };

        const buildRow = function (item) {
            const name = (item.letter_set && item.letter_set.letter_title) ||
                (item.letterSet && item.letterSet.letter_title) || "Item";

            const row = document.createElement("div");
            row.className = "spf-cart__item";
            row.setAttribute("data-spf-cart-item", item.id);

            const nameEl = document.createElement("span");
            nameEl.className = "spf-cart__item-name";
            nameEl.textContent = name;

            const qty = document.createElement("span");
            qty.className = "spf-cart__item-qty";
            qty.textContent = "×" + (item.quantity || 1);

            const price = document.createElement("span");
            price.className = "spf-cart__item-price";
            price.textContent = money(parseFloat(item.total_amount || 0));

            const button = document.createElement("button");
            button.type = "button";
            button.className = "spf-cart__remove";
            button.title = "Remove from basket";
            button.setAttribute("aria-label", "Remove " + name + " from basket");
            button.setAttribute("data-spf-cart-remove", removeUrl.replace("__ID__", item.id));
            button.appendChild(closeIcon());

            row.appendChild(nameEl);
            row.appendChild(qty);
            row.appendChild(price);
            row.appendChild(button);
            return row;
        };

        const showEmpty = function () {
            cartItems.remove();

            const total = document.getElementById("spfCartTotal");
            const more = document.getElementById("spfCartMore");
            if (total) total.remove();
            if (more) more.remove();

            const cta = cartPop.querySelector(".spf-cart__cta");
            if (cta) {
                const browse = cta.getAttribute("data-spf-empty-href");
                if (browse) cta.setAttribute("href", browse);
                cta.textContent = "Browse documents →";

                const empty = document.createElement("div");
                empty.className = "spf-cart__empty";
                empty.textContent = "Your basket is empty.";
                cartPop.insertBefore(empty, cta);
            }

            const wrap = cartPop.closest(".spf-cartwrap");
            const badge = wrap ? wrap.querySelector(".spf-cart__badge") : null;
            if (badge) badge.remove();

            const basket = wrap ? wrap.querySelector(".spf-cart") : null;
            const cta2 = cartPop.querySelector(".spf-cart__cta");
            if (basket && cta2) {
                // Nothing to check out, so the icon goes back to the shop.
                basket.setAttribute("href", cta2.getAttribute("href"));
                basket.setAttribute("aria-label", "Basket (empty)");
            }
        };

        const render = function (items) {
            if (!items.length) {
                showEmpty();
                return;
            }

            cartItems.textContent = "";
            items.slice(0, limit).forEach(function (item) {
                cartItems.appendChild(buildRow(item));
            });

            const badge = document.querySelector(".spf-cart__badge");
            if (badge) badge.textContent = items.length;

            const total = document.getElementById("spfCartTotal");
            if (total) {
                total.lastElementChild.textContent = money(
                    items.reduce(function (sum, item) {
                        return sum + parseFloat(item.total_amount || 0) + parseFloat(item.tax_amount || 0);
                    }, 0)
                );
            }

            const more = document.getElementById("spfCartMore");
            const hidden = items.length - Math.min(items.length, limit);
            if (more) {
                if (hidden > 0) more.textContent = "+ " + hidden + " more";
                else more.remove();
            }
        };

        cartPop.addEventListener("click", function (event) {
            const button = event.target.closest("[data-spf-cart-remove]");
            if (!button) return;

            event.preventDefault();

            const row = button.closest("[data-spf-cart-item]");
            if (!row || row.classList.contains("is-removing")) return;

            row.classList.add("is-removing");

            const token = document.querySelector('meta[name="csrf-token"]');

            fetch(button.getAttribute("data-spf-cart-remove"), {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": token ? token.getAttribute("content") : "",
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json"
                }
            })
                .then(function (response) {
                    if (!response.ok) throw new Error(response.status);
                    return response.json();
                })
                .then(function (data) {
                    render(Array.isArray(data.cart) ? data.cart : []);
                })
                .catch(function () {
                    row.classList.remove("is-removing");
                });
        });
    }
})();
