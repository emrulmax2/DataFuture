/*
 * HR Portal › Employment Reports (user's employment-reports design, 26 Nov 2026): report cards grouped by
 * category, filtered in the browser by the search box (name, description and search words) and the chips.
 */
(function () {
    "use strict";

    var root = document.querySelector("[data-erp]");
    if (!root) return;

    var data = JSON.parse(root.querySelector("[data-erp-data]").textContent || "{}");
    var R = data.reports || [];
    var CATS = Object.keys(data.categories || {}).map(function (k) { return [k, data.categories[k]]; });
    var S = { q: "", cat: "" };

    var P = 'fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"';
    var IC = {
        cake: '<path d="M5 20h14v-7H5zM3 20h18M8 13v-3M12 13v-3M16 13v-3M8 7v.5M12 6v.5M16 7v.5"/>',
        people: '<circle cx="9" cy="8" r="3"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0M16 5.5a3 3 0 0 1 0 5.5M17.5 14a5 5 0 0 1 3 5"/>',
        card: '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M6 16a3 3 0 0 1 6 0M15 10h3M15 14h3"/>',
        clock: '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
        file: '<path d="M7 3h7l4 4v14H7zM14 3v4h4M10 12h5M10 16h5"/>',
        flag: '<path d="M6 21V4M6 5h11l-2 3.5 2 3.5H6"/>',
        phone: '<path d="M5 4h4l1.5 4.5-2 1.5a11 11 0 0 0 5.5 5.5l1.5-2L20 15v4a1 1 0 0 1-1 1A15 15 0 0 1 4 5a1 1 0 0 1 1-1z"/>',
        shield: '<path d="M12 3l7 3v6c0 4-3 7-7 9-4-2-7-5-7-9V6z"/><path d="M9 12l2 2 4-4"/>',
        sheet: '<rect x="4" y="4" width="16" height="16" rx="2"/><path d="M4 9.5h16M4 14.5h16M9.5 4v16"/>',
        hours: '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16M12 13v3l2 1"/>',
        sun: '<circle cx="12" cy="12" r="4"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M18.4 5.6L17 7M7 17l-1.4 1.4"/>',
        plus: '<rect x="4" y="4" width="16" height="16" rx="3"/><path d="M12 8v8M8 12h8"/>'
    };
    var ARROW = '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h8M7.5 3.5L11 7l-3.5 3.5"/></svg>';

    var qInput = root.querySelector("[data-erp-q]");
    var catsBox = root.querySelector("[data-erp-cats]");
    var app = root.querySelector("[data-erp-app]");

    function esc(s) { return String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;"); }
    function hl(s, q) {
        s = String(s || "");
        if (!q) return esc(s);
        var i = s.toLowerCase().indexOf(q);
        return i < 0 ? esc(s) : esc(s.slice(0, i)) + "<mark>" + esc(s.slice(i, i + q.length)) + "</mark>" + esc(s.slice(i + q.length));
    }

    function card(r, q) {
        var inner = '<div class="erp-card__top"><span class="erp-ic"><svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" ' + P + ">" + (IC[r.ic] || IC.file) + "</svg></span>" +
            "<div><h3>" + hl(r.name, q) + '</h3><span class="erp-no">Report ' + esc(r.no) + "</span></div></div>" +
            "<p>" + hl(r.desc, q) + "</p>" +
            '<div class="erp-meta">' + (r.fmt || []).map(function (f) { return '<span class="erp-fmt">' + esc(f) + "</span>"; }).join("") +
            (r.sens ? '<span class="erp-sens">Sensitive</span>' : "") + "</div>" +
            '<div class="erp-foot"><span>' + (r.last ? "Last run " + esc(r.last) : "Not run yet") + "</span>" +
            (r.url ? '<span class="erp-open">Open' + ARROW + "</span>" : "<span>Not available</span>") + "</div>";
        return r.url ? '<a class="erp-card" href="' + esc(r.url) + '">' + inner + "</a>" : '<div class="erp-card is-off">' + inner + "</div>";
    }

    function render() {
        var q = S.q.trim().toLowerCase();
        var hit = R.filter(function (r) { return !q || (r.name + " " + r.desc + " " + r.words).toLowerCase().indexOf(q) >= 0; });
        var list = hit.filter(function (r) { return !S.cat || r.cat === S.cat; });
        var used = CATS.filter(function (c) { return R.some(function (r) { return r.cat === c[0]; }); });

        catsBox.innerHTML = '<button type="button" class="erp-cat" data-cat="" aria-pressed="' + !S.cat + '">All<b>' + hit.length + "</b></button>" +
            used.map(function (c) {
                var n = hit.filter(function (r) { return r.cat === c[0]; }).length;
                return '<button type="button" class="erp-cat" data-cat="' + esc(c[0]) + '" aria-pressed="' + (S.cat === c[0]) + '">' + esc(c[1]) + "<b>" + n + "</b></button>";
            }).join("");

        if (!list.length) {
            app.innerHTML = '<div class="erp-empty">' + (S.q.trim()
                ? 'No report matches "' + esc(S.q.trim()) + '"' + (S.cat ? " in this category" : "") + ". Try a different word, such as holiday or phone."
                : "There are no reports in this category.") + "</div>";
            return;
        }
        app.innerHTML = used.filter(function (c) { return list.some(function (r) { return r.cat === c[0]; }); }).map(function (c) {
            return '<section class="erp-group"><h2>' + esc(c[1]) + '</h2><div class="erp-grid">' +
                list.filter(function (r) { return r.cat === c[0]; }).map(function (r) { return card(r, q); }).join("") + "</div></section>";
        }).join("");
    }

    catsBox.addEventListener("click", function (e) {
        var b = e.target.closest("[data-cat]");
        if (!b) return;
        S.cat = b.getAttribute("data-cat");
        render();
        var f = catsBox.querySelector('[data-cat="' + S.cat + '"]');
        if (f) f.focus();
    });
    qInput.addEventListener("input", function () { S.q = qInput.value; render(); });
    qInput.addEventListener("keydown", function (e) {
        // Enter opens the only match.
        if (e.key !== "Enter") return;
        var links = app.querySelectorAll("a.erp-card");
        if (links.length === 1) { e.preventDefault(); links[0].click(); }
    });

    render();
})();
