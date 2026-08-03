/*
 * Course detail screen for the redesigned Course Management module.
 *
 * Replaces the legacy `course-module.js` + `course-datafuture.js` +
 * `course-monitor.js` trio. Same endpoints, same payloads, same add / edit /
 * delete / restore / active-toggle flows — rebuilt against the new markup and
 * merged into one file, because all three tabs now share a single confirm and
 * success dialog instead of carrying their own (`confirmModalMD/DF/MN`).
 *
 * Each tab's toolbar is scoped to its own card, so the three search boxes and
 * chip groups cannot collide.
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
    const modulesHost = document.querySelector("#courseModuleTableId");
    if (!modulesHost) return;

    const courseId = modulesHost.getAttribute("data-courseid");

    /* ------------------------------------------------------------------ *
     * One tab = one table + one toolbar
     * ------------------------------------------------------------------ */

    /**
     * Builds a tab's Tabulator against its own card, so `readFilters`,
     * `paintCount` and `wireExports` only ever see that tab's controls.
     */
    function createTab({ key, tableId, listRoute, columns, fileBase, sheetName, defaultStatus }) {
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
                // The three list endpoints all read the course as `course`.
                ajaxParams: Object.assign({ course: courseId }, readFilters(panel)),
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

        wireFilters(() => build(), { scope: panel, defaultStatus: defaultStatus || "1" });

        return {
            key,
            panel,
            build,
            rebuild: () => build(),
            // Tabulator sizes columns from the container, which is 0px wide
            // while the panel is hidden — so a tab is built the first time it
            // is shown and redrawn on every later visit.
            reveal() {
                if (!table) build();
                else table.redraw(true);
            },
            isBuilt: () => table !== null,
        };
    }

    const actionButtons = (data, { viewUrl, noun, editTarget }) => {
        let btns = '<span class="cm-rowactions">';

        if (data.deleted_at == null) {
            if (viewUrl) {
                btns +=
                    '<a href="' +
                    viewUrl +
                    '" title="View ' +
                    noun +
                    '" class="cm-rowbtn cm-rowbtn--view"><i data-lucide="eye"></i></a>';
            }
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

    const modulesTab = createTab({
        key: "modules",
        tableId: "courseModuleTableId",
        listRoute: "course.module.list",
        fileBase: "course-modules",
        sheetName: "Course Modules",
        columns: [
            { title: "#ID", field: "id", width: 74 },
            {
                title: "Module",
                field: "name",
                headerHozAlign: "left",
                widthGrow: 3,
                minWidth: 200,
                cssClass: "cm-cell--primary",
            },
            { title: "Level", field: "level", headerHozAlign: "left", widthGrow: 1, minWidth: 100 },
            { title: "Code", field: "code", headerHozAlign: "left", widthGrow: 1, minWidth: 110 },
            { title: "Credit", field: "credit_value", headerHozAlign: "left", width: 84 },
            { title: "Unit", field: "unit_value", headerHozAlign: "left", width: 74 },
            { title: "Status", field: "status", headerHozAlign: "left", width: 108 },
            { title: "Class Type", field: "class_type", headerHozAlign: "left", width: 116 },
            {
                title: "Active",
                field: "active",
                headerSort: false,
                hozAlign: "center",
                headerHozAlign: "center",
                width: 88,
                download: false,
                formatter(cell) {
                    // A button, not a checkbox: the change is only committed
                    // after the confirm dialog, so it must not flip on click.
                    const data = cell.getData();
                    const on = data.active == 1;

                    return (
                        '<button type="button" data-id="' +
                        data.id +
                        '" title="' +
                        (on ? "Set inactive" : "Set active") +
                        '" class="active_updater cm-switch ' +
                        (on ? "cm-switch--on" : "cm-switch--off") +
                        '"><span class="cm-switch__knob"><i data-lucide="' +
                        (on ? "check" : "x") +
                        '"></i></span></button>'
                    );
                },
            },
            {
                title: "Actions",
                field: "id",
                headerSort: false,
                hozAlign: "right",
                headerHozAlign: "right",
                width: 140,
                download: false,
                formatter(cell) {
                    const data = cell.getData();

                    return actionButtons(data, {
                        viewUrl: route("course.module.show", data.id),
                        noun: "module",
                        editTarget: "#courseModuleEditModal",
                    });
                },
            },
        ],
    });

    const datafutureTab = createTab({
        key: "datafuture",
        tableId: "courseDataFutureTableId",
        listRoute: "course.datafuture.list",
        fileBase: "course-datafuture",
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
                        editTarget: "#courseDataFutureEditModal",
                    });
                },
            },
        ],
    });

    const monitorsTab = createTab({
        key: "monitors",
        tableId: "courseMonitorTableId",
        listRoute: "course.monitor.list",
        fileBase: "monitory-accounts",
        sheetName: "Monitory Accounts",
        columns: [
            { title: "#ID", field: "id", width: 84 },
            {
                title: "Name",
                field: "name",
                headerHozAlign: "left",
                widthGrow: 1,
                minWidth: 160,
                cssClass: "cm-cell--primary",
            },
            { title: "Email", field: "email", headerHozAlign: "left", widthGrow: 2, minWidth: 200 },
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
                        noun: "account",
                        editTarget: "#courseMonitorEditModal",
                    });
                },
            },
        ],
    });

    const tabs = [modulesTab, datafutureTab, monitorsTab].filter(Boolean);
    const tabByKey = (key) => tabs.find((t) => t.key === key);

    /* ------------------------------------------------------------------ *
     * Tab switching
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

            const tab = tabByKey(key);
            if (tab) tab.reveal();
        });
    });

    // The first tab is visible on load, so it can be built straight away.
    if (modulesTab) modulesTab.build();

    window.addEventListener("resize", () => {
        tabs.forEach((tab) => {
            if (tab.isBuilt() && !tab.panel.hidden) tab.reveal();
        });
    });

    wireConfirmReset();

    /* ------------------------------------------------------------------ *
     * Forms
     * ------------------------------------------------------------------ */

    /**
     * Wires one add/edit pair. `fill` maps an edit response onto the form; the
     * add form is simply reset when its modal opens.
     */
    function wireForm({ modalId, formId, buttonId, storeRoute, mode, noun, onDone, fill }) {
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
                const courseField = form.querySelector('[name="course_id"]').value;
                form.reset();
                // `reset()` restores the markup defaults, which is right for
                // every field except the hidden course id.
                form.querySelector('[name="course_id"]').value = courseField;
            });
        }

        $(`#${formId}`).on("submit", function (e) {
            e.preventDefault();
            const modal = tailwind.Modal.getOrCreateInstance(modalEl);
            const form = document.getElementById(formId);

            clearErrors(`#${formId}`);
            setBusy(`#${buttonId}`, true);

            axios({
                method: "post",
                // The update endpoints take no URL parameter — the record id
                // travels in the form's hidden `id` field.
                url: route(storeRoute),
                data: new FormData(form),
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

        if (!isAdd && fill) {
            $(`#${modalId}`).data("cm-fill", fill);
        }
    }

    /** Loads a record into an edit modal. */
    function wireEditLoader({ tableId, editRoute, formId, fill }) {
        $(`#${tableId}`).on("click", ".edit_btn", function () {
            const editId = $(this).attr("data-id");
            clearErrors(`#${formId}`);

            axios({ method: "get", url: route(editRoute, editId), headers: csrfHeaders() })
                .then((response) => {
                    if (response.status != 200) return;
                    fill(response.data, editId);
                    $(`#${formId} input[name="id"]`).val(editId);
                })
                .catch((error) => {
                    console.log(error);
                });
        });
    }

    /* -------------------------------- Modules ------------------------- */

    if (modulesTab) {
        wireForm({
            modalId: "courseModuleAddModal",
            formId: "courseModuleAddForm",
            buttonId: "saveModule",
            storeRoute: "course.module.store",
            mode: "add",
            noun: "Module",
            onDone: () => modulesTab.rebuild(),
        });

        wireForm({
            modalId: "courseModuleEditModal",
            formId: "courseModuleEditForm",
            buttonId: "updateModule",
            storeRoute: "course.module.update",
            mode: "edit",
            noun: "Module",
            onDone: () => modulesTab.rebuild(),
        });

        wireEditLoader({
            tableId: "courseModuleTableId",
            editRoute: "course.module.edit",
            formId: "courseModuleEditForm",
            fill(row) {
                const f = "#courseModuleEditForm";
                $(`${f} input[name="name"]`).val(row.name ?? "");
                $(`${f} select[name="module_level_id"]`).val(row.module_level_id ?? "");
                $(`${f} input[name="code"]`).val(row.code ?? "");
                $(`${f} input[name="credit_value"]`).val(row.credit_value ?? "");
                $(`${f} input[name="unit_value"]`).val(row.unit_value ?? "");
                $(`${f} select[name="status"]`).val(row.status ?? "");
                $(`${f} select[name="class_type"]`).val(row.class_type ?? "");
                $(`${f} input[name="active"]`).prop("checked", row.active == 1);
            },
        });
    }

    /* ------------------------------ Datafuture ------------------------ */

    if (datafutureTab) {
        wireForm({
            modalId: "courseDataFutureAddModal",
            formId: "courseDataFutureAddForm",
            buttonId: "saveBaseDF",
            storeRoute: "course.datafuture.store",
            mode: "add",
            noun: "Field",
            onDone: () => datafutureTab.rebuild(),
        });

        wireForm({
            modalId: "courseDataFutureEditModal",
            formId: "courseDataFutureEditForm",
            buttonId: "updateBaseDF",
            storeRoute: "course.datafuture.update",
            mode: "edit",
            noun: "Field",
            onDone: () => datafutureTab.rebuild(),
        });

        wireEditLoader({
            tableId: "courseDataFutureTableId",
            editRoute: "course.datafuture.edit",
            formId: "courseDataFutureEditForm",
            fill(row) {
                const f = "#courseDataFutureEditForm";
                $(`${f} select[name="datafuture_field_id"]`).val(row.datafuture_field_id ?? "");
                $(`${f} input[name="field_value"]`).val(row.field_value ?? "");
            },
        });
    }

    /* ------------------------------- Monitors ------------------------- */

    if (monitorsTab) {
        wireForm({
            modalId: "courseMonitorAddModal",
            formId: "courseMonitorAddForm",
            buttonId: "saveBaseMN",
            storeRoute: "course.monitor.store",
            mode: "add",
            noun: "Account",
            onDone: () => monitorsTab.rebuild(),
        });

        wireForm({
            modalId: "courseMonitorEditModal",
            formId: "courseMonitorEditForm",
            buttonId: "updateBaseMN",
            storeRoute: "course.monitor.update",
            mode: "edit",
            noun: "Account",
            onDone: () => monitorsTab.rebuild(),
        });

        wireEditLoader({
            tableId: "courseMonitorTableId",
            editRoute: "course.monitor.edit",
            formId: "courseMonitorEditForm",
            fill(row) {
                const f = "#courseMonitorEditForm";
                $(`${f} input[name="name"]`).val(row.name ?? "");
                $(`${f} input[name="email"]`).val(row.email ?? "");
            },
        });
    }

    /* ------------------------------------------------------------------ *
     * Confirm (delete / restore / active toggle) — one dialog, three tables
     * ------------------------------------------------------------------ */

    // `action` carries the table it belongs to, so a single confirm modal can
    // serve all three tabs where the legacy code needed one modal each.
    const CONFIRM = {
        MODULE: {
            tab: () => modulesTab,
            noun: "Module",
            requests: {
                DELETE: (id) => ({ method: "delete", url: route("course.module.destory", id) }),
                RESTORE: (id) => ({ method: "post", url: route("course.module.restore", id) }),
                // This endpoint takes no URL parameter and reads {id, status}
                // from the body — unlike delete/restore either side of it.
                STATUS: (id, value) => ({
                    method: "post",
                    url: route("course.module.status.update"),
                    data: { id: id, status: value },
                }),
            },
        },
        DF: {
            tab: () => datafutureTab,
            noun: "Field",
            requests: {
                DELETE: (id) => ({ method: "delete", url: route("course.datafuture.destory", id) }),
                RESTORE: (id) => ({ method: "post", url: route("course.datafuture.restore", id) }),
            },
        },
        MN: {
            tab: () => monitorsTab,
            noun: "Account",
            requests: {
                DELETE: (id) => ({ method: "delete", url: route("course.monitor.destory", id) }),
                RESTORE: (id) => ({ method: "post", url: route("course.monitor.restore", id) }),
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

    wireRowActions("courseModuleTableId", "MODULE", "module");
    wireRowActions("courseDataFutureTableId", "DF", "field");
    wireRowActions("courseMonitorTableId", "MN", "account");

    $("#courseModuleTableId").on("click", ".active_updater", function () {
        const turningOff = $(this).hasClass("cm-switch--on");

        openConfirm({
            id: $(this).attr("data-id"),
            // The third segment is the value to write, since the endpoint takes
            // an explicit status rather than toggling server-side.
            action: turningOff ? "MODULE:STATUS:0" : "MODULE:STATUS:1",
            title: turningOff ? "Set this module inactive?" : "Set this module active?",
            message: turningOff
                ? `“${rowName(this)}” will stop appearing in term module creation.`
                : `“${rowName(this)}” will be available in term module creation again.`,
            confirmLabel: turningOff ? "Set inactive" : "Set active",
            tone: "safe",
        });
    });

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
                        showSuccess("Status updated", `The ${config.noun.toLowerCase()} status has been changed.`);
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
