/*
 * Course Creations list for the redesigned Course Management module.
 *
 * A port of the legacy `course-creation.js` — same endpoints and payloads —
 * rebuilt against the new markup. The busiest screen in the module: nine
 * columns, two extra dropdown filters, a conditional field, two switch cards
 * and a venue repeater whose rows post five parallel arrays.
 *
 * Venue rows are cloned from a single <template> rendered by
 * `partials/venue-row.blade.php`, so the starting row, appended rows and rows
 * restored on edit are guaranteed to be identical. The legacy code kept three
 * separate copies of that markup and they had drifted: the appended copy named
 * its checkbox `evening_and_weekend[]` with no hidden field behind it, and an
 * unchecked checkbox posts nothing — so the array came back short and every
 * later row's flag landed on the wrong venue.
 */

import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import {
    PAGINATION_LANGS,
    PAGINATION_SIZES,
    clearErrors,
    createRangePainter,
    csrfHeaders,
    hideConfirm,
    openConfirm,
    paintCount,
    paintErrors,
    readFilters,
    refreshIcons,
    rowName,
    setBusy,
    showSuccess,
    wireConfirmReset,
    wireExports,
    wireFilters,
    wireResize,
} from "./course-table-kit";

const TABLE_ID = "#courseCreationTableId";

const FIELDS = [
    "course_id",
    "semester_id",
    "course_creation_qualification_id",
    "duration",
    "unit_length",
    "fees",
    "reg_fees",
    "university_commission",
    "required_hours",
];

/*
 * Every select inside the two modals becomes a searchable TomSelect — the
 * course list alone runs to dozens of long names.
 *
 * The library hides the original <select> and renders its own control, so the
 * native element keeps its `name` and value and the posted payload is
 * unchanged. What it does *not* do is notice `select.value = …` or
 * `form.reset()`, so every read/write below goes through the instance.
 * Dropdowns render to <body> because `.cm-modal` clips its overflow.
 */
const TOM_OPTIONS = {
    dropdownParent: "body",
    dropdownClass: "ts-dropdown cm-tom-dropdown",
    allowEmptyOption: true,
    plugins: { dropdown_input: {} },
};

function enhanceSelect(select) {
    // `select.tomselect` is set by the constructor, so this stays idempotent.
    if (!select || select.tomselect) return;

    // TomSelect only falls back to the first option's text for its placeholder
    // when `allowEmptyOption` is off (see its `getSettings`). We need that
    // option to stay selectable, so the placeholder is passed explicitly —
    // without it the control renders as an empty box.
    const blank = select.querySelector('option[value=""]');

    new TomSelect(select, {
        ...TOM_OPTIONS,
        placeholder: blank && blank.textContent.trim() ? blank.textContent.trim() : "Please Select",
    });
}

function enhanceSelects(scope) {
    scope.querySelectorAll("select.cm-select").forEach(enhanceSelect);
}

function setFieldValue(field, value) {
    if (!field) return;
    const next = value === null || value === undefined ? "" : value;

    if (field.tomselect) field.tomselect.setValue(String(next), true);
    else field.value = next;
}

function clearSelects(scope) {
    scope.querySelectorAll("select.cm-select").forEach((select) => {
        if (select.tomselect) select.tomselect.clear(true);
        else select.value = "";
    });
}

/** Frees the control before its row leaves the DOM. */
function destroySelects(scope) {
    scope.querySelectorAll("select.cm-select").forEach((select) => {
        if (select.tomselect) select.tomselect.destroy();
    });
}

(function () {
    if (!$(TABLE_ID).length) return;

    let lastTotal = null;
    let tableContent = null;

    const getTable = () => tableContent;
    const paintRange = createRangePainter(TABLE_ID, getTable, () => lastTotal);

    /* ------------------------------------------------------------------ *
     * Table
     * ------------------------------------------------------------------ */

    function buildTable() {
        tableContent = new Tabulator(TABLE_ID, {
            ajaxURL: route("course.creation.list"),
            ajaxParams: readFilters(),
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: PAGINATION_SIZES,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records",
            langs: PAGINATION_LANGS,
            ajaxResponse(url, params, response) {
                lastTotal = typeof response.total === "number" ? response.total : null;
                paintCount(lastTotal);

                return response;
            },
            columns: [
                { title: "#ID", field: "id", width: 64 },
                {
                    title: "Course",
                    field: "course",
                    headerHozAlign: "left",
                    widthGrow: 4,
                    minWidth: 170,
                    cssClass: "cm-cell--primary",
                },
                { title: "Qualification", field: "qualification", headerHozAlign: "left", widthGrow: 3, minWidth: 130, cssClass: "cm-cell--clamp" },
                { title: "Semester", field: "semester", headerHozAlign: "left", widthGrow: 2, minWidth: 100 },
                { title: "Duration", field: "duration", headerHozAlign: "left", width: 76 },
                { title: "Unit Length", field: "unit_length", headerHozAlign: "left", width: 92 },
                {
                    title: "Venue (s)",
                    field: "venues",
                    headerSort: false,
                    headerHozAlign: "left",
                    widthGrow: 3,
                    minWidth: 150,
                    download: false,
                    formatter(cell) {
                        // Venue names and SLC codes are user-entered, so they are
                        // escaped before going anywhere near innerHTML.
                        const venues = cell.getData().venues || [];
                        let html = "";

                        venues.forEach((v) => {
                            if (v.pivot && v.pivot.deleted_at != null) return;
                            html +=
                                '<span class="cm-stack__name">' +
                                escapeHtml(v.name) +
                                "</span>" +
                                '<span class="cm-stack__sub">' +
                                escapeHtml(v.pivot ? v.pivot.slc_code : "") +
                                "</span>";
                        });

                        return html;
                    },
                },
                { title: "Fees", field: "fees", headerHozAlign: "left", width: 92 },
                { title: "Reg. Fees", field: "reg_fees", headerHozAlign: "left", width: 92 },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 116,
                    download: false,
                    formatter(cell) {
                        const data = cell.getData();
                        let btns = '<span class="cm-rowactions">';

                        if (data.deleted_at == null) {
                            btns +=
                                '<a href="' +
                                route("course.creation.show", data.id) +
                                '" title="View course creation" class="cm-rowbtn cm-rowbtn--view"><i data-lucide="eye"></i></a>';
                            btns +=
                                '<button type="button" data-id="' +
                                data.id +
                                '" data-tw-toggle="modal" data-tw-target="#editCourseCreationModal" title="Edit course creation" class="edit_btn cm-rowbtn cm-rowbtn--edit"><i data-lucide="pencil"></i></button>';
                            btns +=
                                '<button type="button" data-id="' +
                                data.id +
                                '" title="Delete course creation" class="delete_btn cm-rowbtn cm-rowbtn--delete"><i data-lucide="trash-2"></i></button>';
                        } else {
                            btns +=
                                '<button type="button" data-id="' +
                                data.id +
                                '" title="Restore course creation" class="restore_btn cm-rowbtn cm-rowbtn--restore"><i data-lucide="rotate-ccw"></i></button>';
                        }

                        return btns + "</span>";
                    },
                },
            ],
            renderComplete() {
                refreshIcons();
                paintRange();

                const cols = this.getColumns();
                if (cols.length > 0) {
                    const last = cols[cols.length - 1];
                    last.setWidth(last.getWidth() - 1);
                }
            },
        });

        wireExports(getTable, "course-creations", "Course Creations");
    }

    function escapeHtml(value) {
        const wrapper = document.createElement("div");
        wrapper.textContent = value === null || value === undefined ? "" : String(value);

        return wrapper.innerHTML;
    }

    buildTable();
    wireResize(getTable);
    wireFilters(buildTable);
    wireConfirmReset();

    // Only the modals — the toolbar's course/semester filters stay native.
    document.querySelectorAll("#addCourseCreationForm, #editCourseCreationForm").forEach(enhanceSelects);

    /* ------------------------------------------------------------------ *
     * Conditional fields
     * ------------------------------------------------------------------ */

    // `data-cm-toggles="x"` reveals every `[data-cm-showif="x"]` in the same
    // form, so Required Hours only appears with a work placement.
    function paintConditionals(form) {
        form.querySelectorAll("[data-cm-toggles]").forEach((toggle) => {
            const key = toggle.getAttribute("data-cm-toggles");
            form.querySelectorAll(`[data-cm-showif="${key}"]`).forEach((target) => {
                target.hidden = !toggle.checked;
            });
        });
    }

    document.querySelectorAll("#addCourseCreationForm, #editCourseCreationForm").forEach((form) => {
        form.addEventListener("change", (event) => {
            if (event.target.matches("[data-cm-toggles]")) paintConditionals(form);
        });
        paintConditionals(form);
    });

    /* ------------------------------------------------------------------ *
     * Venue repeater
     * ------------------------------------------------------------------ */

    /** Weekends is only meaningful — and only saved — when eve/weekend is on. */
    function paintVenueRow(row) {
        const on = row.querySelector(".eveningAndWeekend").checked;
        const hidden = row.querySelector(".evening_and_weekend");
        const weekends = row.querySelector(".weekends");

        hidden.value = on ? 1 : 0;
        if (on) {
            weekends.removeAttribute("readonly");
        } else {
            weekends.setAttribute("readonly", "readonly");
            weekends.value = "";
        }
    }

    function venueRows(scope) {
        return scope.querySelectorAll(".cm-venue-row");
    }

    function addVenueRow(repeater) {
        const template = repeater.querySelector("[data-cm-venue-template]");
        const row = template.content.firstElementChild.cloneNode(true);
        repeater.querySelector("[data-cm-venue-rows]").appendChild(row);
        // The clone comes from inert <template> content, so it is a plain
        // <select> until it is enhanced here.
        enhanceSelects(row);

        return row;
    }

    document.querySelectorAll("[data-cm-venues]").forEach((repeater) => {
        repeater.querySelector("[data-cm-venue-add]").addEventListener("click", (event) => {
            event.preventDefault();
            addVenueRow(repeater);
        });

        repeater.addEventListener("change", (event) => {
            if (event.target.matches(".eveningAndWeekend")) {
                paintVenueRow(event.target.closest(".cm-venue-row"));
            }
        });

        repeater.addEventListener("click", (event) => {
            const button = event.target.closest("[data-cm-venue-remove]");
            if (!button) return;
            event.preventDefault();

            const row = button.closest(".cm-venue-row");
            const savedId = button.getAttribute("data-id");

            // A row that exists in the database needs confirming and a request;
            // one the user just added is only ever in the DOM.
            if (savedId) {
                pendingVenueRow = row;
                openConfirm({
                    id: savedId,
                    action: "VENUE",
                    title: "Remove this venue?",
                    message: "The venue will be detached from this course creation.",
                    confirmLabel: "Remove",
                    tone: "danger",
                });
                return;
            }

            // Never leave the repeater with nothing to fill in.
            if (venueRows(repeater).length > 1) {
                destroySelects(row);
                row.remove();
            } else {
                resetVenueRow(row);
            }
        });
    });

    function resetVenueRow(row) {
        setFieldValue(row.querySelector(".venue_id"), "");
        row.querySelector(".slc_code").value = "";
        row.querySelector(".weekdays").value = "";
        row.querySelector(".eveningAndWeekend").checked = false;
        paintVenueRow(row);
    }

    /** Rebuilds a form's venue rows from an edit response. */
    function fillVenues(repeater, venues) {
        const rowsHost = repeater.querySelector("[data-cm-venue-rows]");
        venueRows(repeater).forEach((row) => {
            destroySelects(row);
            row.remove();
        });

        const live = (venues || []).filter((v) => !v.pivot || v.pivot.deleted_at == null);

        if (!live.length) {
            addVenueRow(repeater);
            return;
        }

        live.forEach((venue) => {
            const row = addVenueRow(repeater);
            const pivot = venue.pivot || {};

            setFieldValue(row.querySelector(".venue_id"), venue.id);
            row.querySelector(".slc_code").value = pivot.slc_code ?? "";
            row.querySelector(".weekdays").value = pivot.weekdays > 0 ? pivot.weekdays : "";
            row.querySelector(".eveningAndWeekend").checked = pivot.evening_and_weekend == 1;
            paintVenueRow(row);
            if (pivot.evening_and_weekend == 1) {
                row.querySelector(".weekends").value = pivot.weekends > 0 ? pivot.weekends : "";
            }

            // Marks the row as persisted, so removing it asks first and calls
            // the detach endpoint rather than just dropping it from the DOM.
            if (pivot.id) {
                row.querySelector("[data-cm-venue-remove]").setAttribute("data-id", pivot.id);
            }
        });

        rowsHost.dispatchEvent(new Event("cm:venues-filled"));
    }

    // The row awaiting the confirm dialog, so it can be dropped once the
    // server has detached it.
    let pendingVenueRow = null;

    /* ------------------------------------------------------------------ *
     * Create / update — unchanged endpoints and payloads
     * ------------------------------------------------------------------ */

    // Native listener, not jQuery: `.on("shown.tw.modal")` would be read as the
    // event "shown" in the "tw.modal" namespace and never fire, because the
    // theme dispatches a CustomEvent whose *type* contains the dots.
    document.querySelector("#addCourseCreationModal").addEventListener("shown.tw.modal", function () {
        const form = document.getElementById("addCourseCreationForm");
        const repeater = form.querySelector("[data-cm-venues]");

        clearErrors("#addCourseCreationForm");
        form.reset();
        // `reset()` restores the native <select> but leaves TomSelect showing
        // the previous label, so the controls are cleared explicitly.
        clearSelects(form);

        // `reset()` restores each control's markup default but leaves any rows
        // the user appended last time, so the repeater is rebuilt explicitly.
        venueRows(repeater).forEach((row, index) => {
            if (index === 0) {
                resetVenueRow(row);
            } else {
                destroySelects(row);
                row.remove();
            }
        });
        if (!venueRows(repeater).length) addVenueRow(repeater);
        paintConditionals(form);
    });

    $("#addCourseCreationForm").on("submit", function (e) {
        e.preventDefault();
        const modal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addCourseCreationModal"));

        clearErrors("#addCourseCreationForm");
        setBusy("#saveCourseCreation", true);

        axios({
            method: "post",
            url: route("course.creation.store"),
            data: new FormData(document.getElementById("addCourseCreationForm")),
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#saveCourseCreation", false);

                if (response.status == 200) {
                    modal.hide();
                    showSuccess("Course creation added", "The course creation has been created successfully.");
                }
                buildTable();
            })
            .catch((error) => {
                setBusy("#saveCourseCreation", false);
                if (error.response && error.response.status == 422) {
                    paintErrors("#addCourseCreationForm", error.response.data.errors);
                }
            });
    });

    $(TABLE_ID).on("click", ".edit_btn", function () {
        const editId = $(this).attr("data-id");
        const form = document.getElementById("editCourseCreationForm");
        clearErrors("#editCourseCreationForm");

        axios({ method: "get", url: route("course.creation.edit", editId), headers: csrfHeaders() })
            .then((response) => {
                if (response.status != 200) return;

                const row = response.data;
                FIELDS.forEach((field) => {
                    setFieldValue(form.querySelector(`[name="${field}"]`), row[field]);
                });

                $("#editCourseCreationForm .has_evening_and_weekend").prop("checked", row.has_evening_and_weekend == 1);
                $("#editCourseCreationForm .is_workplacement").prop("checked", row.is_workplacement == 1);
                if (!row.required_hours || row.required_hours <= 0) {
                    setFieldValue(form.querySelector('[name="required_hours"]'), "");
                }

                fillVenues(form.querySelector("[data-cm-venues]"), row.venues);
                paintConditionals(form);
                $('#editCourseCreationForm input[name="id"]').val(editId);
            })
            .catch((error) => {
                console.log(error);
            });
    });

    $("#editCourseCreationForm").on("submit", function (e) {
        e.preventDefault();
        const modal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editCourseCreationModal"));

        clearErrors("#editCourseCreationForm");
        setBusy("#updateCourseCreation", true);

        axios({
            method: "post",
            // This endpoint takes no URL parameter — the record id travels in
            // the form's hidden `id` field.
            url: route("course.creation.update"),
            data: new FormData(document.getElementById("editCourseCreationForm")),
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#updateCourseCreation", false);

                if (response.status == 200) {
                    modal.hide();
                    showSuccess("Course creation updated", "The changes have been saved successfully.");
                }
                buildTable();
            })
            .catch((error) => {
                setBusy("#updateCourseCreation", false);

                if (error.response) {
                    if (error.response.status == 422) {
                        paintErrors("#editCourseCreationForm", error.response.data.errors);
                    } else if (error.response.status == 304) {
                        modal.hide();
                        showSuccess("No change", "Nothing was modified on this record.");
                    }
                }
            });
    });

    /* ------------------------------------------------------------------ *
     * Confirm (delete / restore / detach venue)
     * ------------------------------------------------------------------ */

    $(TABLE_ID).on("click", ".delete_btn", function () {
        openConfirm({
            id: $(this).attr("data-id"),
            action: "DELETE",
            title: "Delete this course creation?",
            message: `“${rowName(this, "course")}” will be moved to the archive and hidden from this list.`,
            confirmLabel: "Delete",
            tone: "danger",
        });
    });

    $(TABLE_ID).on("click", ".restore_btn", function () {
        openConfirm({
            id: $(this).attr("data-id"),
            action: "RESTORE",
            title: "Restore this course creation?",
            message: `“${rowName(this, "course")}” will be returned to the active list.`,
            confirmLabel: "Restore",
            tone: "safe",
        });
    });

    $("#confirmModal .agreeWith").on("click", function () {
        const $agree = $(this);
        const recordID = $agree.attr("data-id");
        const action = $agree.attr("data-action");

        const requests = {
            DELETE: () => ({ method: "delete", url: route("course.creation.destory", recordID) }),
            RESTORE: () => ({ method: "post", url: route("course.creation.restore", recordID) }),
            VENUE: () => ({ method: "delete", url: route("course.creation.venue.destroy", recordID) }),
        };

        const build = requests[action];
        if (!build) return;

        const request = build();
        $("#confirmModal button").attr("disabled", "disabled");

        axios({ method: request.method, url: request.url, headers: csrfHeaders() })
            .then((response) => {
                $("#confirmModal button").removeAttr("disabled");
                if (response.status != 200) return;

                hideConfirm();

                if (action === "VENUE") {
                    // Dropped only after the server confirms the detach, so a
                    // failed request leaves the row visible.
                    if (pendingVenueRow) {
                        const repeater = pendingVenueRow.closest("[data-cm-venues]");
                        pendingVenueRow.remove();
                        if (repeater && !venueRows(repeater).length) addVenueRow(repeater);
                        pendingVenueRow = null;
                    }
                    showSuccess("Venue removed", "The venue has been detached from this course creation.");
                } else {
                    showSuccess(
                        action === "DELETE" ? "Course creation deleted" : "Course creation restored",
                        action === "DELETE"
                            ? "The record has been moved to the archive."
                            : "The record has been returned to the active list.",
                    );
                }

                buildTable();
            })
            .catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
    });
})();
