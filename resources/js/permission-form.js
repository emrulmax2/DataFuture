import Litepicker from "litepicker";

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
