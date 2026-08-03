/*
 * Course module detail screen for the redesigned Course Management module.
 *
 * Replaces the legacy `course-module-assesment.js` + `module-datafuture.js`.
 * Same endpoints and payloads; rebuilt against the new markup and merged so
 * both tabs share a single confirm and success dialog instead of carrying
 * their own (`confirmModalCMA` / `confirmModalMDF`).
 */

import Tabulator from "tabulator-tables";
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
} from "./course-table-kit";

(function () {
    const assessHost = document.querySelector("#moduleAssesmentDataTable");
    if (!assessHost) return;

    const moduleId = assessHost.getAttribute("data-moduleid");

    function createTab({ key, tableId, listRoute, columns, fileBase, sheetName }) {
        const selector = `#${tableId}`;
        const panel = document.querySelector(`[data-cm-tabpanel="${key}"]`);
        if (!panel) return null;

        let lastTotal = null;
        let table = null;

        const getTable = () => table;
        const paintRange = createRangePainter(selector, getTable, () => lastTotal);

        function build() {
            table = new Tabulator(selector, {
                ajaxURL: route(listRoute),
                // Both list endpoints read the module as `module`.
                ajaxParams: Object.assign({ module: moduleId }, readFilters(panel)),
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
                    paintCount(lastTotal, panel);

                    return response;
                },
                columns,
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

            wireExports(getTable, fileBase, sheetName, panel);
        }

        wireFilters(() => build(), { scope: panel });

        return {
            key,
            panel,
            build,
            rebuild: () => build(),
            // Tabulator sizes columns from the container, which is 0px wide
            // while the panel is hidden — so a tab is built on first reveal and
            // redrawn on every later visit.
            reveal() {
                if (!table) build();
                else table.redraw(true);
            },
            isBuilt: () => table !== null,
        };
    }

    const actionButtons = (data, { noun, editTarget }) => {
        let btns = '<span class="cm-rowactions">';

        if (data.deleted_at == null) {
            btns +=
                '<button type="button" data-id="' +
                data.id +
                '" data-tw-toggle="modal" data-tw-target="' +
                editTarget +
                '" title="Edit ' +
                noun +
                '" class="edit_btn cm-rowbtn cm-rowbtn--edit"><i data-lucide="pencil"></i></button>';
            btns +=
                '<button type="button" data-id="' +
                data.id +
                '" title="Delete ' +
                noun +
                '" class="delete_btn cm-rowbtn cm-rowbtn--delete"><i data-lucide="trash-2"></i></button>';
        } else {
            btns +=
                '<button type="button" data-id="' +
                data.id +
                '" title="Restore ' +
                noun +
                '" class="restore_btn cm-rowbtn cm-rowbtn--restore"><i data-lucide="rotate-ccw"></i></button>';
        }

        return btns + "</span>";
    };

    const assessmentsTab = createTab({
        key: "assessments",
        tableId: "moduleAssesmentDataTable",
        listRoute: "course.module.assesment.list",
        fileBase: "module-assesments",
        sheetName: "Assesments",
        columns: [
            { title: "#ID", field: "id", width: 84 },
            {
                title: "Name",
                field: "name",
                headerHozAlign: "left",
                widthGrow: 2,
                minWidth: 180,
                cssClass: "cm-cell--primary",
            },
            { title: "Code", field: "code", headerHozAlign: "left", widthGrow: 1, minWidth: 110 },
            {
                title: "View In Plans",
                field: "view_in_plan",
                headerSort: false,
                hozAlign: "center",
                headerHozAlign: "center",
                width: 130,
                download: false,
                formatter(cell) {
                    // Commits immediately on click — matching the legacy
                    // behaviour, which had no confirm step for this toggle. The
                    // sibling values ride along because the update endpoint
                    // rewrites the whole row.
                    const d = cell.getData();
                    const on = d.view_in_plan == 1;

                    return (
                        '<button type="button" class="view-plans cm-switch ' +
                        (on ? "cm-switch--on" : "cm-switch--off") +
                        '" title="' +
                        (on ? "Hide from plans" : "Show in plans") +
                        '" data-id="' +
                        d.id +
                        '" data-course_module_id="' +
                        d.course_module_id +
                        '" data-assessment_type_id="' +
                        d.assessment_type_id +
                        '" data-is_result_segment="' +
                        d.is_result_segment +
                        '"><span class="cm-switch__knob"><i data-lucide="' +
                        (on ? "check" : "x") +
                        '"></i></span></button>'
                    );
                },
            },
            {
                title: "Result Segment",
                field: "is_result_segment",
                headerSort: false,
                hozAlign: "center",
                headerHozAlign: "center",
                width: 140,
                formatter(cell) {
                    return cell.getData().is_result_segment == 1 ? "Yes" : "No";
                },
            },
            {
                title: "Actions",
                field: "id",
                headerSort: false,
                hozAlign: "right",
                headerHozAlign: "right",
                width: 120,
                download: false,
                formatter(cell) {
                    return actionButtons(cell.getData(), {
                        noun: "assesment",
                        editTarget: "#moduleAssesmentEditModal",
                    });
                },
            },
        ],
    });

    const datafutureTab = createTab({
        key: "datafuture",
        tableId: "moduleDatafutureDataTable",
        listRoute: "module.datafuture.list",
        fileBase: "module-datafuture",
        sheetName: "Datafuture Fields",
        columns: [
            { title: "#ID", field: "id", width: 74 },
            {
                title: "Category",
                field: "category",
                headerHozAlign: "left",
                widthGrow: 1,
                minWidth: 130,
                cssClass: "cm-cell--primary",
            },
            { title: "Field Name", field: "datafuture_field_id", headerHozAlign: "left", widthGrow: 1, minWidth: 150 },
            { title: "Field Type", field: "field_type", headerHozAlign: "left", width: 116 },
            { title: "Field Value", field: "field_value", headerHozAlign: "left", widthGrow: 1, minWidth: 120 },
            { title: "Description", field: "field_desc", headerHozAlign: "left", widthGrow: 1, minWidth: 140, cssClass: "cm-cell--clamp" },
            {
                title: "Actions",
                field: "id",
                headerSort: false,
                hozAlign: "right",
                headerHozAlign: "right",
                width: 120,
                download: false,
                formatter(cell) {
                    return actionButtons(cell.getData(), {
                        noun: "field",
                        editTarget: "#moduleDataFutureEditModal",
                    });
                },
            },
        ],
    });

    const tabs = [assessmentsTab, datafutureTab].filter(Boolean);

    /* ------------------------------------------------------------------ *
     * Tabs
     * ------------------------------------------------------------------ */

    document.querySelectorAll("[data-cm-tab]").forEach((button) => {
        button.addEventListener("click", () => {
            const key = button.getAttribute("data-cm-tab");

            document.querySelectorAll("[data-cm-tab]").forEach((other) => {
                const on = other === button;
                other.classList.toggle("is-active", on);
                other.setAttribute("aria-selected", on ? "true" : "false");
            });

            document.querySelectorAll("[data-cm-tabpanel]").forEach((panel) => {
                panel.hidden = panel.getAttribute("data-cm-tabpanel") !== key;
            });

            const tab = tabs.find((t) => t.key === key);
            if (tab) tab.reveal();
        });
    });

    if (assessmentsTab) assessmentsTab.build();

    window.addEventListener("resize", () => {
        tabs.forEach((tab) => {
            if (tab.isBuilt() && !tab.panel.hidden) tab.reveal();
        });
    });

    wireConfirmReset();

    /* ------------------------------------------------------------------ *
     * Checklist select-all / clear-all
     * ------------------------------------------------------------------ */

    document.querySelectorAll("[data-cm-checklist]").forEach((list) => {
        const toggle = list.querySelector("[data-cm-checklist-toggle]");
        const boxes = () => Array.from(list.querySelectorAll(".cm-check__input"));

        const paintToggle = () => {
            if (!toggle) return;
            const all = boxes().every((b) => b.checked);
            toggle.textContent = all ? "Clear all" : "Select all";
        };

        if (toggle) {
            toggle.addEventListener("click", () => {
                const all = boxes().every((b) => b.checked);
                boxes().forEach((b) => {
                    b.checked = !all;
                });
                paintToggle();
            });
        }

        list.addEventListener("change", paintToggle);
        paintToggle();
    });

    /* ------------------------------------------------------------------ *
     * Forms
     * ------------------------------------------------------------------ */

    function wireForm({ modalId, formId, buttonId, postRoute, mode, noun, onDone, resetExtra }) {
        const modalEl = document.querySelector(`#${modalId}`);
        if (!modalEl) return;

        const isAdd = mode === "add";

        // Native listener, not jQuery: `.on("shown.tw.modal")` would be read as
        // the event "shown" in the "tw.modal" namespace and never fire, because
        // the theme dispatches a CustomEvent whose *type* contains the dots.
        if (isAdd) {
            modalEl.addEventListener("shown.tw.modal", function () {
                clearErrors(`#${formId}`);
                const form = document.getElementById(formId);
                const keep = form.querySelector('[name="course_module_id"]').value;
                form.reset();
                // `reset()` restores the markup defaults, which is right for
                // every field except the hidden module id.
                form.querySelector('[name="course_module_id"]').value = keep;
                if (resetExtra) resetExtra(form);
            });
        }

        $(`#${formId}`).on("submit", function (e) {
            e.preventDefault();
            const modal = tailwind.Modal.getOrCreateInstance(modalEl);

            clearErrors(`#${formId}`);
            setBusy(`#${buttonId}`, true);

            axios({
                method: "post",
                // These endpoints take no URL parameter — the record id travels
                // in the form's hidden `id` field.
                url: route(postRoute),
                data: new FormData(document.getElementById(formId)),
                headers: csrfHeaders(),
            })
                .then((response) => {
                    setBusy(`#${buttonId}`, false);

                    if (response.status == 200) {
                        modal.hide();
                        showSuccess(
                            isAdd ? `${noun} added` : `${noun} updated`,
                            isAdd
                                ? `The ${noun.toLowerCase()} has been created successfully.`
                                : "The changes have been saved successfully.",
                        );
                    }
                    onDone();
                })
                .catch((error) => {
                    setBusy(`#${buttonId}`, false);

                    if (error.response) {
                        if (error.response.status == 422) {
                            paintErrors(`#${formId}`, error.response.data.errors);
                        } else if (error.response.status == 304) {
                            modal.hide();
                            showSuccess("No change", "Nothing was modified on this record.");
                        }
                    }
                });
        });
    }

    function wireEditLoader({ tableId, editRoute, formId, fill }) {
        $(`#${tableId}`).on("click", ".edit_btn", function () {
            const editId = $(this).attr("data-id");
            clearErrors(`#${formId}`);

            axios({ method: "get", url: route(editRoute, editId), headers: csrfHeaders() })
                .then((response) => {
                    if (response.status != 200) return;
                    fill(response.data);
                    $(`#${formId} input[name="id"]`).val(editId);
                })
                .catch((error) => {
                    console.log(error);
                });
        });
    }

    /* ----------------------------- Assesments ------------------------- */

    if (assessmentsTab) {
        // Every grade is ticked on a fresh form, matching the legacy markup.
        const checkAllGrades = (form) => {
            form.querySelectorAll('input[name="grade[]"]').forEach((b) => {
                b.checked = true;
            });
            const toggle = form.querySelector("[data-cm-checklist-toggle]");
            if (toggle) toggle.textContent = "Clear all";
        };

        wireForm({
            modalId: "moduleAssesmentAddModal",
            formId: "moduleAssesmentAddForm",
            buttonId: "saveModuleAssesment",
            postRoute: "course.module.assesment.store",
            mode: "add",
            noun: "Assesment",
            onDone: () => assessmentsTab.rebuild(),
            resetExtra: checkAllGrades,
        });

        wireForm({
            modalId: "moduleAssesmentEditModal",
            formId: "moduleAssesmentEditForm",
            buttonId: "updateModuleAssesment",
            postRoute: "course.module.assesment.update",
            mode: "edit",
            noun: "Assesment",
            onDone: () => assessmentsTab.rebuild(),
        });

        wireEditLoader({
            tableId: "moduleAssesmentDataTable",
            editRoute: "course.module.assesment.edit",
            formId: "moduleAssesmentEditForm",
            fill(row) {
                const f = "#moduleAssesmentEditForm";
                $(`${f} select[name="assessment_type_id"]`).val(row.assessment_type_id ?? "");
                $(`${f} input[name="view_in_plan"]`).val(row.view_in_plan ?? 0);

                // Matched on grade id. The legacy code compared *positions* in
                // the two lists, so it only ticked the right boxes when the
                // saved grades happened to line up with the rendered order.
                const selected = Array.isArray(row.grades) ? row.grades.map((g) => String(g.id)) : [];
                $(`${f} input[name="grade[]"]`).each(function () {
                    this.checked = selected.includes(String(this.value));
                });

                const toggle = document.querySelector(`${f} [data-cm-checklist-toggle]`);
                if (toggle) {
                    const boxes = document.querySelectorAll(`${f} input[name="grade[]"]`);
                    const all = boxes.length > 0 && Array.from(boxes).every((b) => b.checked);
                    toggle.textContent = all ? "Clear all" : "Select all";
                }
            },
        });

        // Confirmed like every other status switch in the module. The legacy
        // code committed straight from the click; nothing here writes to the
        // database until the dialog is accepted.
        $("#moduleAssesmentDataTable").on("click", ".view-plans", function () {
            const turningOn = $(this).hasClass("cm-switch--off");

            openConfirm({
                id: $(this).attr("data-id"),
                // The third segment is the value to write, since the endpoint
                // takes an explicit flag rather than toggling server-side.
                action: turningOn ? "AS:VIEWPLAN:1" : "AS:VIEWPLAN:0",
                title: turningOn ? "Show this assesment in plans?" : "Hide this assesment from plans?",
                message: turningOn
                    ? `“${rowName(this)}” will appear on the class plan screens.`
                    : `“${rowName(this)}” will stop appearing on the class plan screens.`,
                confirmLabel: turningOn ? "Show in plans" : "Hide from plans",
                tone: "safe",
            });
        });
    }

    /* ----------------------------- Datafuture ------------------------- */

    if (datafutureTab) {
        wireForm({
            modalId: "moduleDataFutureAddModal",
            formId: "moduleDataFutureAddForm",
            buttonId: "saveModuleDF",
            postRoute: "module.datafuture.store",
            mode: "add",
            noun: "Field",
            onDone: () => datafutureTab.rebuild(),
        });

        wireForm({
            modalId: "moduleDataFutureEditModal",
            formId: "moduleDataFutureEditForm",
            buttonId: "updateModuleDF",
            postRoute: "module.datafuture.update",
            mode: "edit",
            noun: "Field",
            onDone: () => datafutureTab.rebuild(),
        });

        wireEditLoader({
            tableId: "moduleDatafutureDataTable",
            editRoute: "module.datafuture.edit",
            formId: "moduleDataFutureEditForm",
            fill(row) {
                const f = "#moduleDataFutureEditForm";
                $(`${f} select[name="datafuture_field_id"]`).val(row.datafuture_field_id ?? "");
                $(`${f} input[name="field_value"]`).val(row.field_value ?? "");
            },
        });
    }

    /* ------------------------------------------------------------------ *
     * Confirm (delete / restore) — one dialog, both tables
     * ------------------------------------------------------------------ */

    const CONFIRM = {
        AS: {
            tab: () => assessmentsTab,
            noun: "Assesment",
            requests: {
                DELETE: (id) => ({ method: "delete", url: route("course.module.assesment.destory", id) }),
                RESTORE: (id) => ({ method: "post", url: route("course.module.assesment.restore", id) }),
                // The update endpoint rewrites the whole row, so the siblings
                // have to ride along or they would be blanked. They are read
                // back off the row's own button rather than stashed, so a
                // cancelled dialog can never leave a stale payload behind.
                VIEWPLAN: (id, value) => {
                    const $btn = $(`#moduleAssesmentDataTable .view-plans[data-id="${id}"]`);

                    return {
                        method: "post",
                        url: route("course.module.assesment.update"),
                        data: {
                            id: id,
                            view_in_plan: value,
                            course_module_id: $btn.data("course_module_id"),
                            assessment_type_id: $btn.data("assessment_type_id"),
                            is_result_segment: $btn.data("is_result_segment"),
                        },
                    };
                },
            },
        },
        MDF: {
            tab: () => datafutureTab,
            noun: "Field",
            requests: {
                DELETE: (id) => ({ method: "delete", url: route("module.datafuture.destory", id) }),
                RESTORE: (id) => ({ method: "post", url: route("module.datafuture.restore", id) }),
            },
        },
    };

    function wireRowActions(tableId, group, nounLower) {
        $(`#${tableId}`).on("click", ".delete_btn", function () {
            openConfirm({
                id: $(this).attr("data-id"),
                action: `${group}:DELETE`,
                title: `Delete this ${nounLower}?`,
                message: `“${rowName(this)}” will be moved to the archive and hidden from this list.`,
                confirmLabel: "Delete",
                tone: "danger",
            });
        });

        $(`#${tableId}`).on("click", ".restore_btn", function () {
            openConfirm({
                id: $(this).attr("data-id"),
                action: `${group}:RESTORE`,
                title: `Restore this ${nounLower}?`,
                message: `“${rowName(this)}” will be returned to the active list.`,
                confirmLabel: "Restore",
                tone: "safe",
            });
        });
    }

    wireRowActions("moduleAssesmentDataTable", "AS", "assesment");
    wireRowActions("moduleDatafutureDataTable", "MDF", "field");

    $("#confirmModal .agreeWith").on("click", function () {
        const $agree = $(this);
        const recordID = $agree.attr("data-id");
        const [group, verb, value] = ($agree.attr("data-action") || "").split(":");

        const config = CONFIRM[group];
        if (!config || !config.requests[verb]) return;

        const request = config.requests[verb](recordID, value);

        $("#confirmModal button").attr("disabled", "disabled");

        axios({
            method: request.method,
            url: request.url,
            data: request.data,
            headers: csrfHeaders(),
        })
            .then((response) => {
                $("#confirmModal button").removeAttr("disabled");

                if (response.status == 200) {
                    hideConfirm();

                    if (verb === "DELETE") {
                        showSuccess(`${config.noun} deleted`, "The record has been moved to the archive.");
                    } else if (verb === "RESTORE") {
                        showSuccess(`${config.noun} restored`, "The record has been returned to the active list.");
                    } else {
                        showSuccess(
                            "Plan visibility updated",
                            value === "1"
                                ? "The assesment will now appear on the class plan screens."
                                : "The assesment will no longer appear on the class plan screens.",
                        );
                    }
                }

                const tab = config.tab();
                if (tab) tab.rebuild();
            })
            .catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
    });
})();
