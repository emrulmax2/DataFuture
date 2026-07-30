/*
 * Shared plumbing for the Tabulator list screens in the redesigned Course
 * Management module.
 *
 * Everything here is layout/behaviour that is identical on every list —
 * footer wording, the page-range readout, the record count, the filter
 * toolbar, export/print, modal busy state and the confirm dialog. The
 * per-screen files own their columns, their form fields and their endpoints.
 */

import xlsx from "xlsx";
import { createIcons, icons } from "lucide";

/**
 * Relabels Tabulator's footer to the design's wording. The first/last buttons
 * are hidden in CSS, leaving prev / page / next.
 */
export const PAGINATION_LANGS = {
    default: {
        pagination: {
            page_size: "Rows",
            first: "«",
            first_title: "First page",
            last: "»",
            last_title: "Last page",
            prev: "‹",
            prev_title: "Previous page",
            next: "›",
            next_title: "Next page",
        },
    },
};

export const PAGINATION_SIZES = [10, 25, 50];

export function refreshIcons() {
    createIcons({ icons, "stroke-width": 1.9, nameAttr: "data-lucide" });
}

/*
 * Every lookup below is scoped. Single-table screens pass nothing and search
 * the whole document; the course detail screen has three independent tables on
 * one page and passes each card element, so their toolbars cannot collide.
 */
function root(scope) {
    if (!scope) return document;

    return typeof scope === "string" ? document.querySelector(scope) || document : scope;
}

/**
 * Current filter values for one toolbar: the search box, the status chips, and
 * any extra dropdowns, which contribute a parameter named by their
 * `data-cm-filter` value (Course Creations filters by course and semester).
 */
export function readFilters(scope) {
    const el = root(scope);
    const query = el.querySelector("[data-cm-query]");
    const status = el.querySelector("[data-cm-status]");

    const params = {
        querystr: query && query.value !== "" ? query.value : "",
        status: status && status.value !== "" ? status.value : "",
    };

    el.querySelectorAll("[data-cm-filter]").forEach((extra) => {
        params[extra.getAttribute("data-cm-filter")] = extra.value;
    });

    return params;
}

/**
 * Header record count, e.g. "134 records". Blank when the total is unknown.
 * The noun comes from `data-cm-count` when the screen lists one specific thing
 * ("6 modules"); it is given in the singular and pluralised with an "s".
 */
export function paintCount(total, scope) {
    const label = root(scope).querySelector("[data-cm-count]");
    if (!label) return;

    const noun = label.getAttribute("data-cm-count") || "record";
    label.textContent = total === null ? "" : `${total} ${total === 1 ? noun : noun + "s"}`;
}

/**
 * "1–10 of 134" wedged into Tabulator's paginator, which has no counter in v4.
 * Returns a painter bound to one table host.
 */
export function createRangePainter(tableSelector, getTable, getTotal) {
    return function paintRange() {
        const paginator = document.querySelector(`${tableSelector} .tabulator-paginator`);
        const table = getTable();
        if (!paginator || !table) return;

        let range = paginator.querySelector(".cm-page-range");
        if (!range) {
            range = document.createElement("span");
            range.className = "cm-page-range";
            const sizeSelect = paginator.querySelector(".tabulator-page-size");
            if (sizeSelect && sizeSelect.nextSibling) {
                paginator.insertBefore(range, sizeSelect.nextSibling);
            } else {
                paginator.appendChild(range);
            }
        }

        const shown = table.getData().length;
        if (!shown) {
            range.textContent = "";
            return;
        }

        const total = getTotal();
        const page = table.getPage() || 1;
        const size = parseInt(table.getPageSize(), 10) || shown;
        const start = (page - 1) * size + 1;
        const end = start + shown - 1;

        range.textContent = total === null ? `${start}–${end}` : `${start}–${end} of ${total}`;
    };
}

/**
 * Search box, status chips and Reset. `rebuild` re-creates the table with the
 * current filter values.
 */
export function wireFilters(rebuild, options = {}) {
    const { scope, defaultStatus = "1" } = options;
    const $el = $(root(scope));

    // Debounced so typing filters as you go, matching the design's single
    // search field (there is no "Go" button on this layout).
    let searchTimer = null;
    $el.find("[data-cm-query]").on("input search", function () {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(rebuild, 300);
    });

    $el.find("[data-cm-filterform]").on("submit", function (event) {
        event.preventDefault();
        rebuild();
    });

    $el.find("[data-cm-chip]").on("click", function () {
        const $chip = $(this);
        if ($chip.hasClass("is-active")) return;

        $el.find("[data-cm-chip]").removeClass("is-active").attr("aria-pressed", "false");
        $chip.addClass("is-active").attr("aria-pressed", "true");
        $el.find("[data-cm-status]").val($chip.attr("data-cm-chip"));
        rebuild();
    });

    $el.find("[data-cm-filter]").on("change", rebuild);

    $el.find("[data-cm-reset]").on("click", function () {
        $el.find("[data-cm-query]").val("");
        $el.find("[data-cm-status]").val(defaultStatus);
        $el.find("[data-cm-filter]").val("");
        $el.find("[data-cm-chip]").removeClass("is-active").attr("aria-pressed", "false");
        $el.find(`[data-cm-chip="${defaultStatus}"]`).addClass("is-active").attr("aria-pressed", "true");
        rebuild();
    });
}

/** Export/print buttons. Re-bound on each rebuild, hence the `.off()`. */
export function wireExports(getTable, fileBase, sheetName, scope) {
    const $el = $(root(scope));

    $el.find('[data-cm-export="csv"]').off("click").on("click", function () {
        getTable().download("csv", `${fileBase}.csv`);
    });

    $el.find('[data-cm-export="xlsx"]').off("click").on("click", function () {
        window.XLSX = xlsx;
        getTable().download("xlsx", `${fileBase}.xlsx`, { sheetName });
    });

    $el.find("[data-cm-print]").off("click").on("click", function () {
        getTable().print();
    });
}

/** Redraw on resize. Registered once per page, never inside the table builder. */
export function wireResize(getTable) {
    window.addEventListener("resize", () => {
        const table = getTable();
        if (!table) return;
        table.redraw();
        refreshIcons();
    });
}

/**
 * Swaps a submit button's glyph for its spinner while a request is in flight,
 * so the two never show side by side.
 */
export function setBusy(selector, busy) {
    const btn = document.querySelector(selector);
    if (!btn) return;

    const spinner = btn.querySelector(".cm-spinner");
    const icon = btn.querySelector("[data-cm-btn-icon]");

    if (busy) btn.setAttribute("disabled", "disabled");
    else btn.removeAttribute("disabled");

    if (spinner) spinner.style.display = busy ? "inline-block" : "none";
    if (icon) icon.style.display = busy ? "none" : "";
}

export function clearErrors(formSelector) {
    $(`${formSelector} .cm-input, ${formSelector} .cm-select`).removeClass("border-danger");
    $(`${formSelector} .acc__input-error`).html("");
}

/** Paints a 422 validation response onto the matching fields. */
export function paintErrors(formSelector, errors) {
    for (const [key, val] of Object.entries(errors)) {
        $(`${formSelector} .${key}`).addClass("border-danger");
        $(`${formSelector} .error-${key}`).html(val);
    }
}

/**
 * The shared result dialog. `tone` picks the glyph and tint: "ok" for a
 * completed action, "warn" to report a failure — a green tick on an error
 * reads as success.
 */
export function showSuccess(title, message, tone = "ok") {
    const modal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const isOk = tone !== "warn";

    $("#successModal .cm-success__icon").toggleClass("cm-success__icon--warn", !isOk);
    $('#successModal [data-cm-notice-icon="ok"]').toggle(isOk);
    $('#successModal [data-cm-notice-icon="warn"]').toggle(!isOk);

    modal.show();
    $("#successModal .successModalTitle").html(title);
    $("#successModal .successModalDesc").html(message);
}

/**
 * Configures and opens the confirm dialog.
 *
 * `tone` picks the icon/button treatment: "danger" for destructive actions,
 * "safe" for restore / status changes. Both glyph variants are pre-rendered in
 * the blade and toggled here — swapping a `data-lucide` attribute would only
 * work once, since createIcons replaces the <i> with an <svg>.
 */
export function openConfirm({ id, action, title, message, confirmLabel, tone = "danger" }) {
    const modal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const isDanger = tone === "danger";

    $("#confirmModal .confModTitle").html(title);
    $("#confirmModal .confModDesc").html(message);

    $("#confirmModal .cm-confirm__icon").toggleClass("cm-confirm__icon--restore", !isDanger);
    $('#confirmModal [data-cm-confirm-icon="DELETE"]').toggle(isDanger);
    $('#confirmModal [data-cm-confirm-icon="RESTORE"]').toggle(!isDanger);
    $('#confirmModal [data-cm-confirm-glyph="DELETE"]').toggle(isDanger);
    $('#confirmModal [data-cm-confirm-glyph="RESTORE"]').toggle(!isDanger);

    $("#confirmModal .agreeWith")
        .attr("data-id", id)
        .attr("data-action", action)
        .toggleClass("cm-btn--danger", isDanger)
        .toggleClass("cm-btn--save", !isDanger);
    $("#confirmModal [data-cm-confirm-label]").text(confirmLabel);

    modal.show();
}

export function hideConfirm() {
    tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal")).hide();
}

/** Resets the pending action when the dialog closes. */
export function wireConfirmReset() {
    const el = document.querySelector("#confirmModal");
    if (!el) return;

    // Native listener, not jQuery: `.on("hidden.tw.modal")` would be read as the
    // event "hidden" in the "tw.modal" namespace and never fire, because the
    // theme dispatches a CustomEvent whose *type* contains the dots.
    el.addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0").attr("data-action", "none");
    });
}

/** The row's name cell, so a confirm prompt can quote the record. */
export function rowName(button, field = "name") {
    const cell = $(button)
        .closest(".tabulator-row")
        .find(`.tabulator-cell[tabulator-field="${field}"]`)
        .first();

    return (cell.text() || "This record").trim();
}

export function csrfHeaders() {
    return { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") };
}
