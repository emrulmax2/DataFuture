(function () {
    "use strict";

    $(".top-bar, .top-bar-boxed")
        .find(".search")
        .find("input")
        .each(function () {
            $(this).on("focus", function () {
                $(".top-bar, .top-bar-boxed")
                    .find(".search-result")
                    .addClass("show");
            });

            $(this).on("focusout", function () {
                $(".top-bar, .top-bar-boxed")
                    .find(".search-result")
                    .removeClass("show");
            });
        });
})();

(function () {
    "use strict";

    const headers = document.querySelectorAll("[data-global-header]");

    if (!headers.length) {
        return;
    }

    const closeMenus = (exceptMenu = null) => {
        document.querySelectorAll("[data-global-header] [data-header-menu]").forEach((menu) => {
            if (menu !== exceptMenu) {
                menu.dataset.menuOpen = "false";
            }
        });
    };

    const closeSearches = (exceptSearch = null) => {
        document.querySelectorAll("[data-global-search]").forEach((search) => {
            if (search !== exceptSearch) {
                search.dataset.searchOpen = "false";
            }
        });
    };

    headers.forEach((header) => {
        header.querySelectorAll("[data-header-menu-toggle]").forEach((toggle) => {
            toggle.addEventListener("click", (event) => {
                const menu = toggle.closest("[data-header-menu]");

                if (!menu) {
                    return;
                }

                event.stopPropagation();
                const willOpen = menu.dataset.menuOpen !== "true";
                closeMenus(menu);
                closeSearches();
                menu.dataset.menuOpen = willOpen ? "true" : "false";
            });
        });
    });

    document.addEventListener("click", (event) => {
        if (!event.target.closest("[data-header-menu]")) {
            closeMenus();
        }

        if (!event.target.closest("[data-global-search]")) {
            closeSearches();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeMenus();
            closeSearches();
        }

        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
            const input = document.querySelector("[data-global-search-input]");

            if (input) {
                event.preventDefault();
                input.focus();
                input.select();
            }
        }
    });

    const escapeHtml = (value) => {
        const wrapper = document.createElement("div");
        wrapper.textContent = value === null || value === undefined ? "" : String(value);

        return wrapper.innerHTML;
    };

    const escapeAttribute = (value) => escapeHtml(value).replace(/"/g, "&quot;").replace(/'/g, "&#039;");

    const buildGroup = (items, label, modifierClass) => {
        if (!items.length) {
            return "";
        }

        const rows = items
            .map((item) => {
                const name = escapeHtml(item.name || "Untitled");
                const meta = escapeHtml(item.meta || "");
                const status = escapeHtml(item.status || "");
                const initials = escapeHtml(item.initials || "LC");
                const url = escapeAttribute(item.url || "javascript:;");

                return `
                    <a class="lcc-global-search__item" href="${url}">
                        <span class="lcc-global-search__initials">${initials}</span>
                        <span class="lcc-global-search__copy">
                            <strong>${name}</strong>
                            ${meta ? `<small>${meta}</small>` : ""}
                        </span>
                        ${status ? `<span class="lcc-global-search__status">${status}</span>` : ""}
                    </a>
                `;
            })
            .join("");

        return `
            <div class="lcc-global-search__group ${modifierClass}">
                <div class="lcc-global-search__group-title">${label} &middot; ${items.length}</div>
                ${rows}
            </div>
        `;
    };

    document.querySelectorAll("[data-global-search]").forEach((search) => {
        const input = search.querySelector("[data-global-search-input]");
        const results = search.querySelector("[data-global-search-results]");
        const searchUrl = search.dataset.searchUrl;
        const canSearchStudents = search.dataset.searchStudents === "1";
        const canSearchEmployees = search.dataset.searchEmployees === "1";
        const emptyLabel = canSearchStudents && canSearchEmployees
            ? "students or employees"
            : (canSearchStudents ? "students" : "employees");

        if (!input || !results || !searchUrl) {
            return;
        }

        let timeoutId = null;
        let activeController = null;
        let lastQuery = "";

        const clearResults = () => {
            results.innerHTML = "";
            search.dataset.searchOpen = "false";
        };

        const renderEmpty = (query) => {
            results.innerHTML = `<div class="lcc-global-search__empty">No ${emptyLabel} match "${escapeHtml(query)}".</div>`;
            search.dataset.searchOpen = "true";
        };

        const renderResults = (payload, query) => {
            const students = canSearchStudents && Array.isArray(payload.students) ? payload.students : [];
            const employees = canSearchEmployees && Array.isArray(payload.employees) ? payload.employees : [];

            if (!students.length && !employees.length) {
                renderEmpty(query);
                return;
            }

            results.innerHTML = [
                buildGroup(students, "Students", "lcc-global-search__group--students"),
                buildGroup(employees, "Employees", "lcc-global-search__group--employees"),
            ].join("");
            search.dataset.searchOpen = "true";
        };

        const runSearch = () => {
            const query = input.value.trim();

            if (query.length < 2) {
                lastQuery = "";
                if (activeController) {
                    activeController.abort();
                    activeController = null;
                }
                clearResults();
                return;
            }

            if (query === lastQuery) {
                search.dataset.searchOpen = results.innerHTML ? "true" : "false";
                return;
            }

            lastQuery = query;

            if (activeController) {
                activeController.abort();
            }

            activeController = new AbortController();
            results.innerHTML = '<div class="lcc-global-search__loading">Searching...</div>';
            search.dataset.searchOpen = "true";

            fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                signal: activeController.signal,
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error("Search request failed");
                    }

                    return response.json();
                })
                .then((payload) => {
                    if (input.value.trim() !== query) {
                        return;
                    }

                    renderResults(payload, query);
                })
                .catch((error) => {
                    if (error.name === "AbortError") {
                        return;
                    }

                    results.innerHTML = '<div class="lcc-global-search__empty">Search is unavailable right now.</div>';
                    search.dataset.searchOpen = "true";
                });
        };

        const queueSearch = () => {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(runSearch, 220);
        };

        input.addEventListener("keyup", (event) => {
            if (event.key === "Escape") {
                clearResults();
                return;
            }

            queueSearch();
        });

        input.addEventListener("input", queueSearch);
        input.addEventListener("search", queueSearch);
        input.addEventListener("focus", () => {
            if (results.innerHTML && input.value.trim().length >= 2) {
                search.dataset.searchOpen = "true";
            }
        });
    });
})();
