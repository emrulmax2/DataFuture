/*
 * Term Module Creations finder for the redesigned Course Management module.
 *
 * A port of the legacy `term-module-creation.js` list screen. Not a list page:
 * three cascading selects narrow down to one instance term, and the table below
 * shows that term's module set — or an action to create it.
 *
 * The legacy markup carried a fourth "Group" select and a submit button, but
 * the line that would reveal them is commented out in the original script
 * (`//$('#group__box').show();`), so neither was reachable. They are dropped
 * here rather than carried over as dead controls.
 */

import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import {
    PAGINATION_LANGS,
    PAGINATION_SIZES,
    csrfHeaders,
    hideConfirm,
    openConfirm,
    createRangePainter,
    paintCount,
    refreshIcons,
    showSuccess,
    wireConfirmReset,
} from "./course-table-kit";

const TOM_OPTIONS = {
    dropdownParent: "body",
    dropdownClass: "ts-dropdown cm-tom-dropdown",
    allowEmptyOption: true,
    plugins: { dropdown_input: {} },
};

(function () {
    const yearSelect = document.querySelector("#academic-year");
    if (!yearSelect) return;

    const termSelect = document.querySelector("#termDeclarationId");
    const courseSelect = document.querySelector("#course_creation_id");
    const termStep = document.querySelector('[data-cm-step="term"]');
    const courseStep = document.querySelector('[data-cm-step="course"]');
    const resultCard = document.querySelector("[data-cm-result]");
    const assignedBadge = document.querySelector("[data-cm-assigned]");

    let table = null;

    /* ------------------------------------------------------------------ *
     * Selects
     * ------------------------------------------------------------------ */

    [yearSelect, termSelect, courseSelect].forEach((select) => {
        const blank = select.querySelector('option[value=""]');
        new TomSelect(select, {
            ...TOM_OPTIONS,
            // `allowEmptyOption` stops TomSelect deriving a placeholder from
            // the first option, so it is passed explicitly.
            placeholder: blank && blank.textContent.trim() ? blank.textContent.trim() : "Please Select",
        });
    });

    /**
     * Normalises a cascade payload's `res`.
     *
     * These endpoints fill their array with a 1-based PHP counter, so `res`
     * serialises as an OBJECT — `{"1":{…},"2":{…}}` — not a JSON array. Only
     * the instance-term endpoint returns a real array. The legacy code walked
     * them with `$.each`, which iterates both shapes; `Array.prototype.forEach`
     * does not, so everything goes through here first.
     */
    function toRows(res) {
        if (Array.isArray(res)) return res;
        if (res && typeof res === "object") return Object.values(res);

        return [];
    }

    /** Repopulates a TomSelect from a normalised `[{id, name}]` list. */
    function fillSelect(select, rows) {
        const control = select.tomselect;
        control.clear(true);
        control.clearOptions();
        control.addOption({ value: "", text: "Please Select" });

        (rows || []).forEach((row) => {
            control.addOption({ value: String(row.id), text: row.name });
        });

        control.refreshOptions(false);
    }

    function setStep(step, visible) {
        if (step) step.hidden = !visible;
    }

    function resetFrom(level) {
        if (level <= 2) {
            fillSelect(courseSelect, []);
            setStep(courseStep, false);
        }
        if (level <= 1) {
            fillSelect(termSelect, []);
            setStep(termStep, false);
        }
        hideResult();
    }

    function hideResult() {
        if (resultCard) resultCard.hidden = true;
        if (assignedBadge) assignedBadge.hidden = true;
    }

    const busy = (select, on) => {
        if (!select.tomselect) return;
        if (on) select.tomselect.disable();
        else select.tomselect.enable();
    };

    /*
     * These three endpoints answer 200 with `{res: [...]}` or **304** with an
     * empty `res`. Axios rejects anything outside 2xx, so an empty list lands in
     * `catch` rather than `then` — both paths clear the dependent select.
     */
    function loadTerms(academicYear) {
        busy(termSelect, true);

        axios({
            method: "post",
            url: route("termdeclaration.list.by.academic.year"),
            data: { academicYear },
            headers: csrfHeaders(),
        })
            .then((response) => {
                fillSelect(termSelect, toRows(response.data.res));
                setStep(termStep, true);
            })
            .catch(() => {
                fillSelect(termSelect, []);
                setStep(termStep, true);
            })
            .then(() => busy(termSelect, false));
    }

    function loadCourses(academicYear, termDeclarationId) {
        busy(courseSelect, true);

        axios({
            method: "post",
            url: route("course.list.by.academic.term"),
            data: { academicYear, term_declaration_id: termDeclarationId },
            headers: csrfHeaders(),
        })
            .then((response) => {
                fillSelect(courseSelect, toRows(response.data.res));
                setStep(courseStep, true);
            })
            .catch(() => {
                fillSelect(courseSelect, []);
                setStep(courseStep, true);
            })
            .then(() => busy(courseSelect, false));
    }

    function loadInstanceTerm(academicYear, termDeclarationId, courseCreationId) {
        busy(courseSelect, true);

        axios({
            method: "post",
            url: route("instanceterm.list.by.academic.term.course"),
            data: {
                academicYear,
                term_declaration_id: termDeclarationId,
                course_creation_id: courseCreationId,
            },
            headers: csrfHeaders(),
        })
            .then((response) => {
                const terms = toRows(response.data.res);
                if (terms.length) buildTable(terms[0]);
                else hideResult();
            })
            .catch(() => {
                hideResult();
            })
            .then(() => busy(courseSelect, false));
    }

    yearSelect.addEventListener("change", function () {
        resetFrom(1);
        if (this.value) loadTerms(this.value);
    });

    termSelect.addEventListener("change", function () {
        resetFrom(2);
        if (this.value) loadCourses(yearSelect.value, this.value);
    });

    courseSelect.addEventListener("change", function () {
        hideResult();
        if (this.value) loadInstanceTerm(yearSelect.value, termSelect.value, this.value);
    });

    /* ------------------------------------------------------------------ *
     * Result table
     * ------------------------------------------------------------------ */

    function buildTable(instanceTerm) {
        if (resultCard) resultCard.hidden = false;

        // Every other list in the module shows the "1-10 of 134" readout; this
        // one was the only table that never injected it.
        let lastTotal = null;
        const paintRange = createRangePainter("#termModuleCreationsListTable", () => table, () => lastTotal);

        table = new Tabulator("#termModuleCreationsListTable", {
            ajaxURL: route("term.module.creation.list"),
            ajaxParams: { instance_term: instanceTerm },
            ajaxFiltering: true,
            ajaxSorting: false,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: PAGINATION_SIZES,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No module creation found for this combination",
            langs: PAGINATION_LANGS,
            ajaxResponse(url, params, response) {
                const total = typeof response.total === "number" ? response.total : null;
                lastTotal = total;
                paintCount(total);

                // The badge reflects whether the matched term already has a
                // module set, which is what the row's actions hinge on too.
                if (assignedBadge) {
                    const rows = toRows(response.data);
                    assignedBadge.hidden = !rows.some((r) => r.modules_count > 0);
                }

                return response;
            },
            columns: [
                { title: "#ID", field: "id", width: 74 },
                {
                    title: "Course",
                    field: "course_name",
                    headerHozAlign: "left",
                    widthGrow: 3,
                    minWidth: 180,
                    variableHeight: true,
                    cssClass: "cm-cell--primary cm-cell--wrap",
                },
                { title: "Name", field: "term_dec_name", headerHozAlign: "left", widthGrow: 1, minWidth: 120 },
                { title: "Type", field: "term_type", headerHozAlign: "left", widthGrow: 1, minWidth: 110 },
                { title: "Start", field: "start_date", headerHozAlign: "left", widthGrow: 1, minWidth: 110 },
                { title: "End", field: "end_date", headerHozAlign: "left", widthGrow: 1, minWidth: 110 },
                { title: "Modules", field: "modules_count", headerHozAlign: "left", width: 96 },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 120,
                    download: false,
                    formatter(cell) {
                        const data = cell.getData();
                        let btns = '<span class="cm-rowactions">';

                        if (data.modules_count > 0) {
                            btns +=
                                '<a href="' +
                                route("term.module.creation.show", data.id) +
                                '" title="View module set" class="cm-rowbtn cm-rowbtn--view"><i data-lucide="eye"></i></a>';
                            btns +=
                                '<button type="button" data-id="' +
                                data.id +
                                '" title="Re-assign module documents" class="reassign_btn cm-rowbtn cm-rowbtn--delete"><i data-lucide="rotate-ccw"></i></button>';
                        } else {
                            btns +=
                                '<a href="' +
                                route("term.module.creation.add", {
                                    instanceTermId: data.id,
                                    courseId: data.course_id,
                                }) +
                                '" title="Create module set" class="cm-rowbtn cm-rowbtn--edit"><i data-lucide="plus"></i></a>';
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
    }

    /* ------------------------------------------------------------------ *
     * Re-assign module documents
     * ------------------------------------------------------------------ */

    wireConfirmReset();

    $("#termModuleCreationsListTable").on("click", ".reassign_btn", function () {
        openConfirm({
            id: $(this).attr("data-id"),
            action: "REASSIGN",
            title: "Are you sure?",
            message: "The module related documents will be re-assigned for this term.",
            confirmLabel: "Yes, re-assign",
            tone: "safe",
        });
    });

    $("#confirmModal .agreeWith").on("click", function () {
        const $agree = $(this);
        const recordID = $agree.attr("data-id");
        if ($agree.attr("data-action") !== "REASSIGN") return;

        $("#confirmModal button").attr("disabled", "disabled");

        axios({
            method: "post",
            url: route("term.module.creation.plantask-update", recordID),
            headers: csrfHeaders(),
        })
            .then((response) => {
                $("#confirmModal button").removeAttr("disabled");

                if (response.status == 200) {
                    hideConfirm();
                    showSuccess("Re-assigned", "The module related documents have been re-assigned successfully.");
                }
                if (table) table.replaceData();
            })
            .catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
    });
})();
