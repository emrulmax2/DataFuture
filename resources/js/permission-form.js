import Litepicker from "litepicker";
import { createIcons, icons } from "lucide";

/**
 * Shared behaviour for the two permission forms that render the same checkbox
 * tree: the department templates (/site-settings/permissions) and the employee
 * privilege page, whose markup is injected over AJAX. Handlers are delegated so
 * they survive that injection; initPermissionPickers() must be called again on
 * the new markup because Litepicker binds to the element directly.
 */

export function initPermissionPickers(root = document) {
    root.querySelectorAll(".rangepicker:not([data-picker-ready])").forEach((element) => {
        element.setAttribute("data-picker-ready", "true");

        new Litepicker({
            element,
            autoApply: true,
            singleMode: false,
            numberOfColumns: 2,
            numberOfMonths: 2,
            showWeekNumbers: false,
            format: "DD-MM-YYYY",
            dropdowns: { minYear: 1900, maxYear: 2050, months: true, years: true },
        });
    });
}

export function bindPermissionToggles() {
    // The date range only applies while temporary access is granted, so clear it
    // on the way out rather than leaving a stale range to be saved.
    $(document).on("change", ".remoteTempToggle", function () {
        const $wrap = $(this).closest(".grid").find(".remoteDateRangeWrap");

        if ($(this).prop("checked")) {
            $wrap.removeClass("hidden");
        } else {
            $wrap.addClass("hidden").find(".rangepicker").val("");
        }
    });

    $(document).on("change", ".accountsPrivilegeToggle", function () {
        const $wrap = $(this).closest(".grid").find(".accountsUserTypeWrap");

        if ($(this).prop("checked")) {
            $wrap.removeClass("hidden");
        } else {
            $wrap.addClass("hidden").find("select").val("");
        }
    });

    // A child permission is meaningless without its parent, so unchecking the
    // parent disables and clears everything nested under it.
    $(document).on("change", ".parentPermissionItem", function () {
        const $children = $(this).closest("div").siblings(".childrenPermissionWrap").find("input[type=checkbox]");

        if ($(this).prop("checked")) {
            $children.prop("disabled", false);
        } else {
            $children.prop("disabled", true).prop("checked", false);
        }
    });
}

/* -------------------------------------------------------------------------- */
/* Bulk "Check all / Uncheck all"                                             */
/*                                                                            */
/* The permission tree is deep (a dozen groups per department, each with many */
/* checkboxes), so setting a department up one box at a time is the source of */
/* the "too confusing" complaint. This injects a compact toolbar at the top   */
/* of every department panel and every permission group with Check all /      */
/* Uncheck all buttons plus a live "n of m selected" count, so the scope of   */
/* each control is unambiguous. Controls are built here rather than in Blade  */
/* to avoid repeating the same markup across the group sections by hand.      */
/* -------------------------------------------------------------------------- */

const bulkBars = [];
let bulkListenersBound = false;

function bulkCheckboxes(scope) {
    return Array.from(scope.querySelectorAll('input[type="checkbox"]'));
}

function refreshBulkBar(entry) {
    const boxes = bulkCheckboxes(entry.scope);
    const total = boxes.length;
    const checked = boxes.filter((b) => b.checked).length;

    entry.countEl.textContent = `${checked} of ${total} selected`;

    let state = "none";
    if (total > 0 && checked === total) state = "all";
    else if (checked > 0) state = "some";
    entry.countEl.setAttribute("data-state", state);

    // Disable the action that would be a no-op so the current state is obvious.
    // Department rows show the count only, so they carry no buttons to update.
    if (entry.checkBtn) entry.checkBtn.disabled = total === 0 || checked === total;
    if (entry.clearBtn) entry.clearBtn.disabled = checked === 0;
}

function refreshAllBulkBars() {
    bulkBars.forEach(refreshBulkBar);
}

function applyBulk(scope, check) {
    bulkCheckboxes(scope).forEach((cb) => {
        cb.checked = check;
        // Checking a box re-enables it; the parent/child sync below re-disables
        // any child whose parent ends up unchecked.
        if (check) cb.disabled = false;
    });

    // Re-run the dependent-field logic (parent/child gating, the temporary
    // remote-access date range, the accounts user-type select) by firing the
    // same change event a manual click would.
    scope
        .querySelectorAll(".parentPermissionItem, .remoteTempToggle, .accountsPrivilegeToggle")
        .forEach((el) => el.dispatchEvent(new Event("change", { bubbles: true })));

    refreshAllBulkBars();
}

function buildBulkCount() {
    const countEl = document.createElement("span");
    countEl.className = "ss-perm-bulk__count";
    countEl.setAttribute("data-perm-count", "");
    countEl.textContent = "0 of 0 selected";
    return countEl;
}

function buildBulkActions() {
    const actionsEl = document.createElement("span");
    actionsEl.className = "ss-perm-bulk__actions";
    actionsEl.innerHTML = `
        <button type="button" class="ss-perm-bulk__btn ss-perm-bulk__btn--check" data-perm-bulk="check" title="Check all" aria-label="Check all">
            <i data-lucide="check-check"></i><span>Check all</span>
        </button>
        <button type="button" class="ss-perm-bulk__btn ss-perm-bulk__btn--clear" data-perm-bulk="uncheck" title="Uncheck all" aria-label="Uncheck all">
            <i data-lucide="eraser"></i><span>Uncheck all</span>
        </button>`;
    return actionsEl;
}

function registerBulkBar(header, scope, withActions) {
    if (!header || !scope || header.hasAttribute("data-perm-bulk-ready")) return false;
    header.setAttribute("data-perm-bulk-ready", "true");
    header.classList.add("ss-perm-bulk-host");

    // Count goes inside the title wrapper, right after the accordion name.
    const countEl = buildBulkCount();
    const titleWrap =
        header.querySelector("button.accordion-button .flex") || header.querySelector("button.accordion-button");
    titleWrap.appendChild(countEl);

    // Department (first-level) rows show the count only; the group rows also get
    // the Check all / Uncheck all buttons at the far right of the header (a
    // sibling of the toggle button, so clicking them never toggles the panel).
    let actionsEl = null;
    let checkBtn = null;
    let clearBtn = null;
    if (withActions) {
        actionsEl = buildBulkActions();
        header.appendChild(actionsEl);
        checkBtn = actionsEl.querySelector('[data-perm-bulk="check"]');
        clearBtn = actionsEl.querySelector('[data-perm-bulk="uncheck"]');
    }

    const entry = { actionsEl, scope, countEl, checkBtn, clearBtn };
    bulkBars.push(entry);
    refreshBulkBar(entry);
    return true;
}

export function initPermissionBulkToggles(root = document) {
    const form = root.querySelector("#permissionUpdateForm");
    if (!form) return;

    let injected = false;

    // Department panels first, then each permission group inside them. The
    // controls go in each header; the checkboxes they govern live in the body.
    form.querySelectorAll(".ss-perm-list > .accordion > .accordion-item").forEach((dept) => {
        const header = dept.querySelector(":scope > .accordion-header");
        const body = dept.querySelector(":scope > .accordion-collapse > .accordion-body");
        injected = registerBulkBar(header, body, false) || injected;
    });

    form.querySelectorAll(".accordion-item.bg-gray-100").forEach((group) => {
        const header = group.querySelector(":scope > .accordion-header");
        const body = group.querySelector(":scope > .accordion-collapse > .accordion-body");
        injected = registerBulkBar(header, body, true) || injected;
    });

    if (injected) {
        createIcons({ icons, "stroke-width": 1.7, nameAttr: "data-lucide" });
    }

    if (bulkListenersBound) return;
    bulkListenersBound = true;

    document.addEventListener("click", (e) => {
        const btn = e.target.closest("[data-perm-bulk]");
        if (!btn) return;
        const entry = bulkBars.find((b) => b.actionsEl === btn.closest(".ss-perm-bulk__actions"));
        if (!entry) return;
        e.preventDefault();
        applyBulk(entry.scope, btn.getAttribute("data-perm-bulk") === "check");
    });

    // Keep the counts live for manual clicks. A parent checkbox cascades to its
    // children on this same event, so recount on the next tick once that settles.
    document.addEventListener("change", (e) => {
        if (e.target.matches('input[type="checkbox"]')) {
            setTimeout(refreshAllBulkBars, 0);
        }
    });
}
