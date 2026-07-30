/*
 * Term module set detail — the modules created for one instance term.
 *
 * A port of the detail screen in the legacy `term-module-creation.js`. Same
 * endpoints and payloads throughout:
 *
 *   list            term.module.creation.module.list      (terminstanceid)
 *   add module      term.module.creation.store.individual
 *   edit / update   term.module.creation.edit / .update
 *   assessments     assessment.store (none yet) / assessment.update (has some)
 *
 * The two assessment modals are filled with server-rendered HTML, which now
 * comes from `partials/assessment-toggles.blade.php` instead of being
 * concatenated in the controller, so it matches the rest of the module.
 */

import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import {
    PAGINATION_LANGS,
    PAGINATION_SIZES,
    clearErrors,
    createRangePainter,
    csrfHeaders,
    paintCount,
    paintErrors,
    readFilters,
    refreshIcons,
    setBusy,
    showSuccess,
    wireFilters,
    wireResize,
} from "./course-table-kit";

const TABLE_ID = "#termModuleListTable";

const EDIT_FIELDS = [
    "module_name",
    "code",
    "status",
    "credit_value",
    "unit_value",
    "moodle_enrollment_key",
    "class_type",
    "submission_date",
];

(function () {
    const host = document.querySelector(TABLE_ID);
    if (!host) return;

    const instanceTermId = host.getAttribute("data-terminstanceid");

    let lastTotal = null;
    let table = null;

    const getTable = () => table;
    const paintRange = createRangePainter(TABLE_ID, getTable, () => lastTotal);

    /* ------------------------------------------------------------------ *
     * Selects
     * ------------------------------------------------------------------ */

    function enhanceSelects(scope) {
        scope.querySelectorAll("select.cm-select").forEach((select) => {
            if (select.tomselect) return;
            const blank = select.querySelector('option[value=""]');
            new TomSelect(select, {
                dropdownParent: "body",
                dropdownClass: "ts-dropdown cm-tom-dropdown",
                allowEmptyOption: true,
                plugins: { dropdown_input: {} },
                // `allowEmptyOption` stops TomSelect deriving a placeholder
                // from the first option, so it is passed explicitly.
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

    document
        .querySelectorAll("#addModuleCreationForm, #editModuleCreationForm")
        .forEach(enhanceSelects);

    /* ------------------------------------------------------------------ *
     * Table
     * ------------------------------------------------------------------ */

    function buildTable() {
        table = new Tabulator(TABLE_ID, {
            ajaxURL: route("term.module.creation.module.list"),
            ajaxParams: Object.assign({ terminstanceid: instanceTermId }, readFilters()),
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
                // Ten columns plus two labelled action buttons is a tight fit:
                // the minimums below total ~1036px against a content area of
                // ~1062px at 1440. Module names are the longest values by far,
                // so that column wraps rather than taking the width the rest of
                // the row needs.
                { title: "#ID", field: "id", width: 56 },
                {
                    title: "Module",
                    field: "module_name",
                    headerHozAlign: "left",
                    widthGrow: 2,
                    minWidth: 150,
                    variableHeight: true,
                    cssClass: "cm-cell--primary cm-cell--wrap",
                },
                { title: "Code", field: "code", headerHozAlign: "left", widthGrow: 1, minWidth: 92 },
                { title: "Credit", field: "credit_value", headerHozAlign: "left", width: 66 },
                { title: "Unit", field: "unit_value", headerHozAlign: "left", width: 58 },
                { title: "Key", field: "moodle_enrollment_key", headerHozAlign: "left", widthGrow: 1, minWidth: 80 },
                { title: "Submission", field: "submission_date", headerHozAlign: "left", widthGrow: 1, minWidth: 100 },
                { title: "Type", field: "class_type", headerHozAlign: "left", width: 84 },
                { title: "Status", field: "status", headerHozAlign: "left", width: 86 },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 264,
                    download: false,
                    formatter(cell) {
                        // Which assessment modal opens depends on whether this
                        // module already has any — same split as before.
                        const data = cell.getData();
                        const hasAssessments = data.assessment_count > 0;

                        return (
                            '<span class="cm-rowactions">' +
                            '<button type="button" data-modulecreationid="' +
                            data.id +
                            '" data-tw-toggle="modal" data-tw-target="' +
                            (hasAssessments ? "#viewModuleAssessmentModal" : "#addModuleAssessmentModal") +
                            '" class="' +
                            (hasAssessments ? "view_assessment" : "add_assessment") +
                            ' cm-pillbtn"><i data-lucide="' +
                            (hasAssessments ? "eye" : "plus") +
                            '"></i>Assessment</button>' +
                            '<button type="button" data-modulecreationid="' +
                            data.id +
                            '" data-tw-toggle="modal" data-tw-target="#editModuleCreationModal" class="eidt_module cm-pillbtn"><i data-lucide="pencil"></i>Edit Module</button>' +
                            "</span>"
                        );
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

    }

    buildTable();
    wireResize(getTable);
    wireFilters(buildTable);

    /* ------------------------------------------------------------------ *
     * Assessment modals — body is server-rendered HTML
     * ------------------------------------------------------------------ */

    function loadAssessments(modalId, listRoute, moduleCreationId) {
        const modal = document.querySelector(`#${modalId}`);
        const loader = modal.querySelector(".theLoader");
        const content = modal.querySelector(".theContent");

        loader.style.display = "";
        content.style.display = "none";
        content.innerHTML = "";
        modal.querySelector('input[name="module_creation_id"]').value = moduleCreationId;

        axios({
            method: "post",
            url: route(listRoute),
            data: { moduleCreationId },
            headers: csrfHeaders(),
        })
            .then((response) => {
                if (response.status != 200) return;

                modal.querySelectorAll(".moduleName").forEach((el) => {
                    el.textContent = response.data.moduleName || "Module";
                });
                content.innerHTML = response.data.html;
                loader.style.display = "none";
                content.style.display = "";
            })
            .catch(() => {
                loader.textContent = "The assesments could not be loaded.";
            });
    }

    $(TABLE_ID).on("click", ".view_assessment", function () {
        loadAssessments(
            "viewModuleAssessmentModal",
            "term.module.creation.module.view.assessments",
            $(this).attr("data-modulecreationid"),
        );
    });

    $(TABLE_ID).on("click", ".add_assessment", function () {
        loadAssessments(
            "addModuleAssessmentModal",
            "term.module.creation.module.add.assessments",
            $(this).attr("data-modulecreationid"),
        );
    });

    /** Both assessment forms post the same shape to different endpoints. */
    function wireAssessmentForm(formId, buttonId, postRoute, noun) {
        $(`#${formId}`).on("submit", function (e) {
            e.preventDefault();
            const modalEl = document.getElementById(formId).closest(".modal");
            const modal = tailwind.Modal.getOrCreateInstance(modalEl);

            setBusy(`#${buttonId}`, true);

            axios({
                method: "post",
                url: route(postRoute),
                data: new FormData(document.getElementById(formId)),
                headers: csrfHeaders(),
            })
                .then(() => {
                    setBusy(`#${buttonId}`, false);
                    modal.hide();
                    showSuccess(`Assessments ${noun}`, "The assesments have been saved successfully.");
                    buildTable();
                })
                .catch(() => {
                    setBusy(`#${buttonId}`, false);
                    showSuccess(
                        "Could not save",
                        "The assesments could not be saved. Please try again.",
                        "warn",
                    );
                });
        });
    }

    wireAssessmentForm("viewModuleAssessmentForm", "updateAssessments", "assessment.update", "updated");
    wireAssessmentForm("addModuleAssessmentForm", "addMAssessments", "assessment.store", "added");

    /* ------------------------------------------------------------------ *
     * Add module creation
     * ------------------------------------------------------------------ */

    const addForm = document.querySelector("#addModuleCreationForm");
    const indvWrap = addForm.querySelector("[data-cm-indv-wrap]");
    const indvList = addForm.querySelector("[data-cm-indv-list]");

    // Picking a module pulls that module's base assessments so they can be
    // ticked in the same step.
    addForm.querySelector("#creation_module_id").addEventListener("change", function () {
        const courseModuleId = this.value;

        if (!courseModuleId) {
            indvWrap.hidden = true;
            indvList.innerHTML = "";
            return;
        }

        axios({
            method: "post",
            url: route("term.module.get.base.assessment"),
            data: { course_module_id: courseModuleId },
            headers: csrfHeaders(),
        })
            .then((response) => {
                indvList.innerHTML = response.data.html || "";
                indvWrap.hidden = false;
            })
            .catch(() => {
                indvList.innerHTML = "";
                indvWrap.hidden = true;
            });
    });

    // Native listener, not jQuery: `.on("shown.tw.modal")` would be read as the
    // event "shown" in the "tw.modal" namespace and never fire, because the
    // theme dispatches a CustomEvent whose *type* contains the dots.
    document.querySelector("#addModuleCreationModal").addEventListener("shown.tw.modal", function () {
        clearErrors("#addModuleCreationForm");
        indvWrap.hidden = true;
        indvList.innerHTML = "";
        if (addForm.querySelector("#creation_module_id").tomselect) {
            addForm.querySelector("#creation_module_id").tomselect.clear(true);
        }
    });

    $("#addModuleCreationForm").on("submit", function (e) {
        e.preventDefault();
        const modal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModuleCreationModal"));

        clearErrors("#addModuleCreationForm");
        setBusy("#saveModuleCreation", true);

        axios({
            method: "post",
            url: route("term.module.creation.store.individual"),
            data: new FormData(addForm),
            headers: csrfHeaders(),
        })
            .then(() => {
                setBusy("#saveModuleCreation", false);
                modal.hide();
                showSuccess("Module added", "The module creation has been added to this term.");
                buildTable();
            })
            .catch((error) => {
                setBusy("#saveModuleCreation", false);
                if (error.response && error.response.status == 422) {
                    paintErrors("#addModuleCreationForm", error.response.data.errors);
                }
            });
    });

    /* ------------------------------------------------------------------ *
     * Edit module creation
     * ------------------------------------------------------------------ */

    $(TABLE_ID).on("click", ".eidt_module", function () {
        const editId = $(this).attr("data-modulecreationid");
        const form = document.getElementById("editModuleCreationForm");
        clearErrors("#editModuleCreationForm");

        axios({ method: "get", url: route("term.module.creation.edit", editId), headers: csrfHeaders() })
            .then((response) => {
                if (response.status != 200) return;

                const row = response.data;
                EDIT_FIELDS.forEach((field) => {
                    setFieldValue(form.querySelector(`[name="${field}"]`), row[field]);
                });
                form.querySelector('input[name="id"]').value = editId;
            })
            .catch((error) => {
                console.log(error);
            });
    });

    $("#editModuleCreationForm").on("submit", function (e) {
        e.preventDefault();
        const modal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModuleCreationModal"));

        clearErrors("#editModuleCreationForm");
        setBusy("#updateModuleCreation", true);

        axios({
            method: "post",
            // Takes no URL parameter — the id travels in the hidden field.
            url: route("term.module.creation.update"),
            data: new FormData(document.getElementById("editModuleCreationForm")),
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#updateModuleCreation", false);

                if (response.status == 200) {
                    modal.hide();
                    showSuccess("Module updated", "The changes have been saved successfully.");
                }
                buildTable();
            })
            .catch((error) => {
                setBusy("#updateModuleCreation", false);

                if (error.response) {
                    if (error.response.status == 422) {
                        paintErrors("#editModuleCreationForm", error.response.data.errors);
                    } else if (error.response.status == 304) {
                        modal.hide();
                        showSuccess("No change", "Nothing was modified on this record.");
                    }
                }
            });
    });
})();
