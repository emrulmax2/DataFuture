/*
 * Course Creation detail screen for the redesigned Course Management module.
 *
 * Replaces `course-creation-availability.js` + `course-creation-instance.js` +
 * `course-creation-datafuture.js`. Same endpoints and payloads; rebuilt against
 * the new markup and merged so all three tabs share one confirm and success
 * dialog instead of carrying their own.
 *
 * Availabilty and Datafuture are ordinary Tabulator lists. Instance is not: an
 * instance expands to its own terms table, so it is hand-rendered here rather
 * than nesting a second Tabulator inside a rowFormatter the way the legacy code
 * did. Terms load on first expand.
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
} from "./course-table-kit";

const TOM_OPTIONS = {
    dropdownParent: "body",
    dropdownClass: "ts-dropdown cm-tom-dropdown",
    allowEmptyOption: true,
    plugins: { dropdown_input: {} },
};

function enhanceSelects(scope) {
    scope.querySelectorAll("select.cm-select").forEach((select) => {
        if (select.tomselect) return;
        // `allowEmptyOption` stops TomSelect deriving a placeholder from the
        // first option, so it is passed explicitly — otherwise the control
        // renders as an empty box.
        const blank = select.querySelector('option[value=""]');
        new TomSelect(select, {
            ...TOM_OPTIONS,
            placeholder: blank && blank.textContent.trim() ? blank.textContent.trim() : "Please Select",
        });
    });
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

function escapeHtml(value) {
    const wrapper = document.createElement("div");
    wrapper.textContent = value === null || value === undefined ? "" : String(value);

    return wrapper.innerHTML;
}

(function () {
    const availabilityHost = document.querySelector("#courseCreationAvailibilityTableId");
    if (!availabilityHost) return;

    const creationId = availabilityHost.getAttribute("data-coursecreationid");

    /* ------------------------------------------------------------------ *
     * Tabulator tabs
     * ------------------------------------------------------------------ */

    function createTab({ key, tableId, listRoute, paramName, columns, fileBase, sheetName }) {
        const selector = `#${tableId}`;
        const panel = document.querySelector(`[data-cm-tabpanel="${key}"]`);
        if (!panel) return null;

        let lastTotal = null;
        let table = null;

        const getTable = () => table;
        const paintRange = createRangePainter(selector, getTable, () => lastTotal);

        function build() {
            const params = Object.assign({}, readFilters(panel));
            params[paramName] = creationId;

            table = new Tabulator(selector, {
                ajaxURL: route(listRoute),
                ajaxParams: params,
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
                ajaxResponse(url, requestParams, response) {
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
            // while the panel is hidden.
            reveal() {
                if (!table) build();
                else table.redraw(true);
            },
            isBuilt: () => table !== null,
        };
    }

    const rowActions = (data, { editTarget, noun, allowDelete = true }) => {
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
            if (allowDelete) {
                btns +=
                    '<button type="button" data-id="' +
                    data.id +
                    '" title="Delete ' +
                    noun +
                    '" class="delete_btn cm-rowbtn cm-rowbtn--delete"><i data-lucide="trash-2"></i></button>';
            }
        } else if (allowDelete) {
            btns +=
                '<button type="button" data-id="' +
                data.id +
                '" title="Restore ' +
                noun +
                '" class="restore_btn cm-rowbtn cm-rowbtn--restore"><i data-lucide="rotate-ccw"></i></button>';
        }

        return btns + "</span>";
    };

    const availabilityTab = createTab({
        key: "availability",
        tableId: "courseCreationAvailibilityTableId",
        listRoute: "course.creation.availability.list",
        paramName: "coursecreationid",
        fileBase: "course-availabilty",
        sheetName: "Availabilty",
        columns: [
            { title: "#ID", field: "id", width: 74 },
            {
                title: "Admission Start",
                field: "admission_date",
                headerHozAlign: "left",
                widthGrow: 1,
                minWidth: 120,
                cssClass: "cm-cell--primary",
            },
            { title: "Admission End", field: "admission_end_date", headerHozAlign: "left", widthGrow: 1, minWidth: 120 },
            { title: "Course Start", field: "course_start_date", headerHozAlign: "left", widthGrow: 1, minWidth: 120 },
            { title: "Course End", field: "course_end_date", headerHozAlign: "left", widthGrow: 1, minWidth: 120 },
            { title: "Last Joinning", field: "last_joinning_date", headerHozAlign: "left", widthGrow: 1, minWidth: 120 },
            { title: "Type", field: "type", headerHozAlign: "left", width: 100 },
            {
                title: "Actions",
                field: "id",
                headerSort: false,
                hozAlign: "right",
                headerHozAlign: "right",
                width: 90,
                download: false,
                formatter(cell) {
                    // No destroy/restore endpoint exists for availabilty.
                    return rowActions(cell.getData(), {
                        editTarget: "#cretionAvailabilityEditModal",
                        noun: "availabilty",
                        allowDelete: false,
                    });
                },
            },
        ],
    });

    const datafutureTab = createTab({
        key: "datafuture",
        tableId: "courseCreationDataFutureTableId",
        listRoute: "course.creation.datafuture.list",
        // This endpoint reads `creationid`; availabilty next door reads
        // `coursecreationid`. They genuinely differ.
        paramName: "creationid",
        fileBase: "course-creation-datafuture",
        sheetName: "Datafuture Fields",
        columns: [
            { title: "#ID", field: "id", width: 74 },
            {
                title: "Field Name",
                field: "field_name",
                headerHozAlign: "left",
                widthGrow: 2,
                minWidth: 150,
                cssClass: "cm-cell--primary",
            },
            { title: "Field Type", field: "field_type", headerHozAlign: "left", width: 116 },
            { title: "Field Value", field: "field_value", headerHozAlign: "left", widthGrow: 2, minWidth: 130 },
            { title: "Description", field: "field_desc", headerHozAlign: "left", widthGrow: 2, minWidth: 140, cssClass: "cm-cell--clamp" },
            {
                title: "Actions",
                field: "id",
                headerSort: false,
                hozAlign: "right",
                headerHozAlign: "right",
                width: 120,
                download: false,
                formatter(cell) {
                    return rowActions(cell.getData(), {
                        editTarget: "#editCourseCreationDataFutureModal",
                        noun: "field",
                    });
                },
            },
        ],
    });

    /* ------------------------------------------------------------------ *
     * Instance accordion
     * ------------------------------------------------------------------ */

    const instanceHost = document.querySelector("#courseCreationInstList");
    const instancePanel = document.querySelector('[data-cm-tabpanel="instance"]');

    const instanceTab = {
        key: "instance",
        panel: instancePanel,
        built: false,
        build: loadInstances,
        rebuild: loadInstances,
        reveal() {
            if (!this.built) {
                this.built = true;
                loadInstances();
            }
        },
        isBuilt() {
            return this.built;
        },
    };

    function instanceRowHtml(row) {
        const money = (v) => (v === null || v === undefined || v === "" ? "—" : escapeHtml(v));

        return `
            <div class="cm-inst" data-cm-instance="${row.id}" data-has-terms="${row.has_terms > 0 ? 1 : 0}">
                <div class="cm-inst__row">
                    <button type="button" class="cm-inst__toggle" data-cm-instance-toggle title="Show terms">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                    </button>
                    <span class="cm-inst__id">${escapeHtml(row.id)}</span>
                    <span class="cm-inst__year">${escapeHtml(row.academic_year)}</span>
                    <span class="cm-inst__cell">${escapeHtml(row.start_date)}</span>
                    <span class="cm-inst__cell">${escapeHtml(row.end_date)}</span>
                    <span class="cm-inst__cell">${escapeHtml(row.total_teaching_week)}</span>
                    <span class="cm-inst__cell cm-inst__cell--money">${money(row.fees)}</span>
                    <span class="cm-inst__cell">${money(row.reg_fees)}</span>
                    <span class="cm-inst__actions">
                        <button type="button" data-id="${row.id}" class="cm-pillbtn" data-cm-term-add>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                            Add Term
                        </button>
                        <button type="button" data-id="${row.id}" data-tw-toggle="modal" data-tw-target="#editCourseCreationInstModal" title="Edit instance" class="inst_edit_btn cm-rowbtn cm-rowbtn--edit"><i data-lucide="pencil"></i></button>
                        <button type="button" data-id="${row.id}" title="Delete instance" class="inst_delete_btn cm-rowbtn cm-rowbtn--delete"><i data-lucide="trash-2"></i></button>
                    </span>
                </div>
                <div class="cm-inst__body" data-cm-instance-body></div>
            </div>`;
    }

    function termsHtml(rows) {
        if (!rows.length) {
            return `<div class="cm-inst__empty">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                No terms found under this instance.
            </div>`;
        }

        const head = ["ID", "Name", "Term", "Session", "Start", "End", "T. Weeks", "T. Start", "T. End", "R. Start", "R. End"];
        let html = '<div class="cm-terms"><div class="cm-terms__head">';
        head.forEach((h) => {
            html += `<span>${h}</span>`;
        });
        html += '<span style="text-align:right;">Actions</span></div>';

        rows.forEach((t) => {
            html += '<div class="cm-terms__row">';
            [
                t.id, t.name, t.term, t.session_term, t.start_date, t.end_date,
                t.total_teaching_weeks, t.teaching_start_date, t.teaching_end_date,
                t.revision_start_date, t.revision_end_date,
            ].forEach((cell) => {
                html += `<span>${escapeHtml(cell)}</span>`;
            });
            html += `<span class="cm-terms__actions">
                <button type="button" data-id="${t.id}" data-tw-toggle="modal" data-tw-target="#instancetermEditModal" title="Edit term" class="term_edit_btn cm-rowbtn cm-rowbtn--edit"><i data-lucide="pencil"></i></button>
                <button type="button" data-id="${t.id}" title="Delete term" class="term_delete_btn cm-rowbtn cm-rowbtn--delete"><i data-lucide="trash-2"></i></button>
            </span></div>`;
        });

        return html + "</div>";
    }

    function loadInstances() {
        if (!instanceHost) return;

        // Instances per course creation are few, so they are fetched in one go
        // rather than paginated — the design shows a plain expandable list.
        axios({
            method: "get",
            url: route("course.creation.instance.list"),
            params: { creationid: creationId, status: 1, size: 200, page: 1 },
            headers: csrfHeaders(),
        })
            .then((response) => {
                const rows = (response.data && response.data.data) || [];
                paintCount(rows.length, instancePanel);

                instanceHost.innerHTML = rows.length
                    ? rows.map(instanceRowHtml).join("")
                    : `<div style="padding:40px 26px;text-align:center;font-size:13.5px;font-weight:600;color:#a8a49a;">No instances yet</div>`;

                refreshIcons();
            })
            .catch((error) => {
                console.log(error);
            });
    }

    function loadTerms(instanceEl) {
        const id = instanceEl.getAttribute("data-cm-instance");
        const body = instanceEl.querySelector("[data-cm-instance-body]");

        axios({
            method: "get",
            url: route("instance.term.list"),
            params: { creationinstanceid: id, status: 1, size: 200, page: 1 },
            headers: csrfHeaders(),
        })
            .then((response) => {
                body.innerHTML = termsHtml((response.data && response.data.data) || []);
                body.setAttribute("data-loaded", "1");
                refreshIcons();
            })
            .catch((error) => {
                console.log(error);
            });
    }

    if (instanceHost) {
        instanceHost.addEventListener("click", (event) => {
            const toggle = event.target.closest("[data-cm-instance-toggle]");
            if (!toggle) return;

            const instanceEl = toggle.closest(".cm-inst");
            const open = instanceEl.classList.toggle("is-open");
            const body = instanceEl.querySelector("[data-cm-instance-body]");

            // Fetched on first expand only; later opens reuse what is there.
            if (open && body.getAttribute("data-loaded") !== "1") loadTerms(instanceEl);
        });
    }

    const tabs = [availabilityTab, instanceTab, datafutureTab].filter(Boolean);

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

    if (availabilityTab) availabilityTab.build();

    window.addEventListener("resize", () => {
        tabs.forEach((tab) => {
            if (tab.isBuilt() && !tab.panel.hidden && tab.key !== "instance") tab.reveal();
        });
    });

    wireConfirmReset();
    document
        .querySelectorAll(
            "#cretionAvailabilityAddForm, #cretionAvailabilityEditForm, #addCourseCreationInstForm, #editCourseCreationInstForm, #instancetermAddForm, #instancetermEditForm, #addCourseCreationDataFutureForm, #editCourseCreationDataFutureForm",
        )
        .forEach(enhanceSelects);

    /* ------------------------------------------------------------------ *
     * Forms
     * ------------------------------------------------------------------ */

    function wireForm({ modalId, formId, buttonId, postRoute, mode, noun, onDone, keepFields = [] }) {
        const modalEl = document.querySelector(`#${modalId}`);
        if (!modalEl) return;

        const isAdd = mode === "add";

        // Native listener, not jQuery: `.on("shown.tw.modal")` would be read as
        // the event "shown" in the "tw.modal" namespace and never fire, because
        // the theme dispatches a CustomEvent whose *type* contains the dots.
        if (isAdd) {
            modalEl.addEventListener("shown.tw.modal", function () {
                const form = document.getElementById(formId);
                clearErrors(`#${formId}`);

                // `reset()` restores markup defaults, which would wipe ids the
                // page set at runtime, so those are carried across.
                const kept = keepFields.map((name) => [name, form.querySelector(`[name="${name}"]`).value]);
                form.reset();
                clearSelects(form);
                kept.forEach(([name, value]) => {
                    form.querySelector(`[name="${name}"]`).value = value;
                });
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

    /** Copies an edit response onto a form, selects included. */
    function fillForm(formId, row, fields) {
        const form = document.getElementById(formId);
        fields.forEach((name) => setFieldValue(form.querySelector(`[name="${name}"]`), row[name]));
        setFieldValue(form.querySelector('input[name="id"]'), row.id);
    }

    function wireEditLoader({ rootSelector, buttonClass, editRoute, formId, fields }) {
        $(rootSelector).on("click", `.${buttonClass}`, function () {
            const editId = $(this).attr("data-id");
            clearErrors(`#${formId}`);

            axios({ method: "get", url: route(editRoute, editId), headers: csrfHeaders() })
                .then((response) => {
                    if (response.status != 200) return;
                    fillForm(formId, response.data, fields);
                })
                .catch((error) => {
                    console.log(error);
                });
        });
    }

    /* ---------------------------- Availabilty ------------------------- */

    const AVAILABILITY_FIELDS = [
        "admission_date",
        "admission_end_date",
        "course_start_date",
        "course_end_date",
        "last_joinning_date",
        "type",
    ];

    wireForm({
        modalId: "cretionAvailabilityAddModal",
        formId: "cretionAvailabilityAddForm",
        buttonId: "crationAvailabilitySave",
        postRoute: "course.creation.availability.store",
        mode: "add",
        noun: "Availabilty",
        onDone: () => availabilityTab.rebuild(),
        keepFields: ["course_creation_id"],
    });

    wireForm({
        modalId: "cretionAvailabilityEditModal",
        formId: "cretionAvailabilityEditForm",
        buttonId: "crationAvailabilityUpdate",
        postRoute: "course.creation.availability.update",
        mode: "edit",
        noun: "Availabilty",
        onDone: () => availabilityTab.rebuild(),
    });

    wireEditLoader({
        rootSelector: "#courseCreationAvailibilityTableId",
        buttonClass: "edit_btn",
        editRoute: "course.creation.availability.edit",
        formId: "cretionAvailabilityEditForm",
        fields: AVAILABILITY_FIELDS,
    });

    /* ------------------------------ Instance -------------------------- */

    const INSTANCE_FIELDS = [
        "academic_year_id",
        "start_date",
        "end_date",
        "total_teaching_week",
        "fees",
        "reg_fees",
        "university_commission",
    ];

    wireForm({
        modalId: "addCourseCreationInstModal",
        formId: "addCourseCreationInstForm",
        buttonId: "saveCCIN",
        postRoute: "course.creation.instance.store",
        mode: "add",
        noun: "Instance",
        onDone: () => loadInstances(),
        keepFields: ["course_creation_id"],
    });

    wireForm({
        modalId: "editCourseCreationInstModal",
        formId: "editCourseCreationInstForm",
        buttonId: "updateCCIN",
        postRoute: "course.creation.instance.update",
        mode: "edit",
        noun: "Instance",
        onDone: () => loadInstances(),
    });

    wireEditLoader({
        rootSelector: "#courseCreationInstList",
        buttonClass: "inst_edit_btn",
        editRoute: "course.creation.instance.edit",
        formId: "editCourseCreationInstForm",
        fields: INSTANCE_FIELDS,
    });

    /* --------------------------- Instance terms ----------------------- */

    const TERM_FIELDS = [
        "term_declaration_id",
        "session_term",
        "total_teaching_weeks",
        "start_date",
        "end_date",
        "teaching_start_date",
        "teaching_end_date",
        "revision_start_date",
        "revision_end_date",
    ];

    // "Add Term" belongs to a row, so the parent id is stamped onto the form
    // before the modal opens.
    $("#courseCreationInstList").on("click", "[data-cm-term-add]", function () {
        const instanceId = $(this).attr("data-id");
        $('#instancetermAddForm input[name="course_creation_instance_id"]').val(instanceId);
        tailwind.Modal.getOrCreateInstance(document.querySelector("#instancetermAddModal")).show();
    });

    wireForm({
        modalId: "instancetermAddModal",
        formId: "instancetermAddForm",
        buttonId: "saveInstanceTerm",
        postRoute: "instance.term.store",
        mode: "add",
        noun: "Term",
        onDone: () => refreshOpenInstances(),
        keepFields: ["course_creation_instance_id"],
    });

    wireForm({
        modalId: "instancetermEditModal",
        formId: "instancetermEditForm",
        buttonId: "updateInstanceTerm",
        postRoute: "instance.term.update",
        mode: "edit",
        noun: "Term",
        onDone: () => refreshOpenInstances(),
    });

    wireEditLoader({
        rootSelector: "#courseCreationInstList",
        buttonClass: "term_edit_btn",
        editRoute: "instance.term.edit",
        formId: "instancetermEditForm",
        fields: TERM_FIELDS,
    });

    /** Re-fetches terms for every expanded instance. */
    function refreshOpenInstances() {
        document.querySelectorAll(".cm-inst.is-open").forEach(loadTerms);
    }

    /* ----------------------------- Datafuture ------------------------- */

    const DF_FIELDS = ["field_name", "field_type", "field_value", "field_desc"];

    wireForm({
        modalId: "addCourseCreationDataFutureModal",
        formId: "addCourseCreationDataFutureForm",
        buttonId: "saveCCDF",
        postRoute: "course.creation.datafuture.store",
        mode: "add",
        noun: "Field",
        onDone: () => datafutureTab.rebuild(),
        keepFields: ["course_creation_id"],
    });

    wireForm({
        modalId: "editCourseCreationDataFutureModal",
        formId: "editCourseCreationDataFutureForm",
        buttonId: "updateCCDF",
        postRoute: "course.creation.datafuture.update",
        mode: "edit",
        noun: "Field",
        onDone: () => datafutureTab.rebuild(),
    });

    wireEditLoader({
        rootSelector: "#courseCreationDataFutureTableId",
        buttonClass: "edit_btn",
        editRoute: "course.creation.datafuture.edit",
        formId: "editCourseCreationDataFutureForm",
        fields: DF_FIELDS,
    });

    /* ------------------------------------------------------------------ *
     * Confirm — one dialog for every table on the page
     * ------------------------------------------------------------------ */

    const CONFIRM = {
        DF: {
            noun: "Field",
            after: () => datafutureTab.rebuild(),
            routes: { DELETE: "course.creation.datafuture.destory", RESTORE: "course.creation.datafuture.restore" },
        },
        INST: {
            noun: "Instance",
            after: () => loadInstances(),
            routes: { DELETE: "course.creation.instance.destory", RESTORE: "course.creation.instance.restore" },
        },
        TERM: {
            noun: "Term",
            after: () => refreshOpenInstances(),
            routes: { DELETE: "instance.term.destory", RESTORE: "instance.term.restore" },
        },
    };

    const askDelete = (group, id, label, nounLower) =>
        openConfirm({
            id,
            action: `${group}:DELETE`,
            title: `Delete this ${nounLower}?`,
            message: `“${label}” will be moved to the archive and hidden from this list.`,
            confirmLabel: "Delete",
            tone: "danger",
        });

    $("#courseCreationDataFutureTableId").on("click", ".delete_btn", function () {
        askDelete("DF", $(this).attr("data-id"), rowName(this, "field_name"), "field");
    });

    $("#courseCreationDataFutureTableId").on("click", ".restore_btn", function () {
        openConfirm({
            id: $(this).attr("data-id"),
            action: "DF:RESTORE",
            title: "Restore this field?",
            message: `“${rowName(this, "field_name")}” will be returned to the active list.`,
            confirmLabel: "Restore",
            tone: "safe",
        });
    });

    $("#courseCreationInstList").on("click", ".inst_delete_btn", function () {
        const year = $(this).closest(".cm-inst").find(".cm-inst__year").text().trim();
        askDelete("INST", $(this).attr("data-id"), year || "This instance", "instance");
    });

    $("#courseCreationInstList").on("click", ".term_delete_btn", function () {
        const name = $(this).closest(".cm-terms__row").find("span").eq(1).text().trim();
        askDelete("TERM", $(this).attr("data-id"), name || "This term", "term");
    });

    $("#confirmModal .agreeWith").on("click", function () {
        const $agree = $(this);
        const recordID = $agree.attr("data-id");
        const [group, verb] = ($agree.attr("data-action") || "").split(":");

        const config = CONFIRM[group];
        if (!config || !config.routes[verb]) return;

        $("#confirmModal button").attr("disabled", "disabled");

        axios({
            method: verb === "DELETE" ? "delete" : "post",
            url: route(config.routes[verb], recordID),
            headers: csrfHeaders(),
        })
            .then((response) => {
                $("#confirmModal button").removeAttr("disabled");

                if (response.status == 200) {
                    hideConfirm();
                    showSuccess(
                        verb === "DELETE" ? `${config.noun} deleted` : `${config.noun} restored`,
                        verb === "DELETE"
                            ? "The record has been moved to the archive."
                            : "The record has been returned to the active list.",
                    );
                }

                config.after();
            })
            .catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
    });
})();
