import Tabulator from "tabulator-tables";
import { createIcons, icons } from "lucide";

/*
 * Site settings > Task Automation.
 *
 * The condition builder is driven by the catalogue the page embeds
 * (#taskRuleCatalogue): each field carries its type, the operators that
 * type allows and, for selects, the option list. Rows are posted as JSON,
 * so a list value ("is any of") and a scalar travel the same way.
 *
 * The page is either the applicant or the student half (data-taskrule-subject,
 * ?subject=student): every request carries it, and the server sends that
 * half's tasks, triggers and catalogue. In the dialog the Process select
 * narrows "Assign task" to that process's tasks (the rule stores the task).
 */
(function () {
    "use strict";

    const catalogueEl = document.getElementById("taskRuleCatalogue");
    const tableEl = document.getElementById("ruleTableId");
    if (!catalogueEl || !tableEl) {
        return;
    }

    const CATALOGUE = JSON.parse(catalogueEl.textContent || "{}");
    const pageEl = document.getElementById("siteSettingsPage");
    const SUBJECT = (pageEl && pageEl.getAttribute("data-taskrule-subject")) === "student" ? "student" : "applicant";
    const IS_STUDENT = SUBJECT === "student";
    const FIELDS = Object.values(CATALOGUE);
    const csrf = { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") };

    const refreshIcons = () => createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
    const esc = (v) => $("<i></i>").text(v === null || v === undefined ? "" : String(v)).html();

    /* ---------- list ---------------------------------------------------- */
    const ruleTable = (function () {
        let table = null;
        const build = () => {
            table = new Tabulator("#ruleTableId", {
                ajaxURL: route("taskrules.list"),
                ajaxParams: { querystr: $("#query").val() || "", status: $("#status").val() || "1", subject: SUBJECT },
                /* The endpoint answers in the settings-wide {last_page, data}
                   envelope. Without remote pagination Tabulator expects a bare
                   array, so unwrap it - a few dozen rules never need paging. */
                ajaxResponse: (url, params, response) => response.data || [],
                layout: "fitColumns",
                responsiveLayout: "collapse",
                placeholder: IS_STUDENT ? "No student rules yet. Add one to assign a task when a student is created or their status changes." : "No rules yet. Add one to assign a task automatically on submission.",
                columns: [
                    { title: "Task", field: "task", minWidth: 190, formatter: (cell) => {
                        const d = cell.getData();
                        return `<div class="ss-cell-wrap"><strong>${esc(d.task || "—")}</strong><span class="ss-cell-muted">${d.process ? esc(d.process) + " · " : ""}${esc(d.trigger)}</span></div>`;
                    } },
                    { title: "Conditions", field: "conditions", minWidth: 240, headerSort: false, formatter: (cell) => {
                        const list = cell.getValue() || [];
                        const d = cell.getData();
                        const then = d.job ? `<span class="ss-rule-pill ss-rule-pill--job">then: ${esc(d.job)}</span>` : "";
                        if (!list.length) return `<div class="ss-rule-pills"><span class="ss-rule-pill ss-rule-pill--always">Always</span>${then}</div>`;
                        return `<div class="ss-rule-pills">${list.map((c) => `<span class="ss-rule-pill">${esc(c)}</span>`).join("")}${then}</div>`;
                    } },
                    { title: "Group", field: "exclusive_group", width: 120, formatter: (cell) => cell.getValue() ? `<span class="ss-rule-group" title="${esc(cell.getValue())}">${esc(cell.getValue())}</span>` : `<span class="ss-cell-muted">—</span>` },
                    { title: "Priority", field: "priority", width: 90, hozAlign: "center" },
                    /* Student half: does the rule's action complete the task while the applicant is
                       moved to students (config tasks.conversion_auto_complete_jobs)? */
                    ...(IS_STUDENT ? [{ title: "Auto Complete", field: "auto_complete", width: 130, hozAlign: "center", headerHozAlign: "center", formatter: (cell) =>
                        cell.getValue()
                            ? `<span class="ss-status-pill ss-status-pill--on" title="Its action completes the task during the conversion">Yes</span>`
                            : `<span class="ss-status-pill ss-status-pill--off" title="Staff complete the task, or its action waits for other tasks">No</span>` }] : []),
                    { title: "Active", field: "active", width: 100, hozAlign: "center", formatter: (cell) =>
                        cell.getValue() ? `<span class="ss-status-pill ss-status-pill--on">Active</span>` : `<span class="ss-status-pill ss-status-pill--off">Inactive</span>` },
                    { title: "Actions", field: "id", width: 100, headerSort: false, hozAlign: "right", headerHozAlign: "right", formatter: (cell) => {
                        const d = cell.getData();
                        if (d.deleted_at) {
                            return `<button data-id="${d.id}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore rule"><i data-lucide="rotate-ccw"></i></button>`;
                        }
                        return `<button data-id="${d.id}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit rule"><i data-lucide="pencil"></i></button>`
                             + `<button data-id="${d.id}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Archive rule"><i data-lucide="trash-2"></i></button>`;
                    } },
                ],
                renderComplete() { refreshIcons(); },
            });
        };
        return { init: () => { if (table) { table.destroy(); } build(); } };
    })();

    ruleTable.init();
    $("#tabulator-html-filter-go").on("click", () => ruleTable.init());
    $("#tabulator-html-filter-reset").on("click", () => { $("#query").val(""); $("#status").val("1"); ruleTable.init(); });

    /* ---------- modal + condition builder --------------------------------- */
    const ruleModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#ruleModal"));
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const $form = $("#ruleForm");
    const $rows = $("#ruleConditions");

    const showSuccess = (title, message) => {
        $("#successModal .successModalTitle").html(title);
        $("#successModal .successModalDesc").html(message);
        succModal.show();
    };

    const setSaving = (on) => {
        const b = document.getElementById("saveRule");
        b.toggleAttribute("disabled", on);
        b.querySelector("svg").style.display = on ? "inline-block" : "none";
    };

    const clearValidation = () => {
        $form.find(".acc__input-error").html("");
        $form.find(".border-danger").removeClass("border-danger");
        $rows.find(".ss-rule-row__error").html("");
    };

    const updateEmpty = () => $form.find("[data-rule-empty]").toggle($rows.children().length === 0);

    /* Process → its tasks. The full task list is kept aside and the select rebuilt from it. */
    const $process = $form.find("[data-rule-process]");
    const $task = $form.find('[name="task_list_id"]');
    const TASK_OPTIONS = $task.find("option[data-process]").map(function () {
        return { id: this.value, name: $(this).text(), process: this.getAttribute("data-process") };
    }).get();
    const fillTasks = (processId, selected) => {
        const list = TASK_OPTIONS.filter((t) => processId && t.process === String(processId));
        const first = processId ? (list.length ? "Please Select" : "No tasks in this process") : "Choose a process first";
        $task.html(`<option value="">${esc(first)}</option>` + list.map((t) => `<option value="${esc(t.id)}" data-process="${esc(t.process)}">${esc(t.name)}</option>`).join(""));
        $task.prop("disabled", !processId || !list.length);
        if (selected) { $task.val(String(selected)); }
    };
    $process.on("change", function () { fillTasks(this.value, null); $form.find(".error-task_list_id").html(""); });

    const fieldSelect = (selected) => {
        const groups = {};
        FIELDS.forEach((f) => { (groups[f.group] = groups[f.group] || []).push(f); });
        let html = `<select class="ss-rule-row__field" aria-label="Field">`;
        Object.keys(groups).forEach((g) => {
            html += `<optgroup label="${esc(g)}">` + groups[g].map((f) => `<option value="${f.key}" ${f.key === selected ? "selected" : ""}>${esc(f.label)}</option>`).join("") + `</optgroup>`;
        });
        return html + `</select>`;
    };

    const operatorSelect = (field, selected) => {
        const ops = CATALOGUE[field].operators;
        return `<select class="ss-rule-row__operator" aria-label="Comparison">` +
            Object.keys(ops).map((k) => `<option value="${k}" ${k === selected ? "selected" : ""}>${esc(ops[k])}</option>`).join("") + `</select>`;
    };

    const valueControl = (field, operator, value) => {
        const f = CATALOGUE[field];
        const isList = operator === "in" || operator === "not_in";
        const vals = Array.isArray(value) ? value.map(String) : (value === null || value === undefined ? [] : [String(value)]);
        if (f.type === "bool") {
            const v = vals.length ? (vals[0] === "1" || vals[0] === "true" ? "1" : "0") : "1";
            return `<select class="ss-rule-row__value" aria-label="Value"><option value="1" ${v === "1" ? "selected" : ""}>Yes</option><option value="0" ${v === "0" ? "selected" : ""}>No</option></select>`;
        }
        if (f.type === "number") {
            return `<input type="number" class="ss-rule-row__value" aria-label="Value" value="${esc(vals[0] || "")}" placeholder="e.g. 21">`;
        }
        const opts = f.options || {};
        const options = Object.keys(opts).map((k) => `<option value="${esc(k)}" ${vals.includes(String(k)) ? "selected" : ""}>${esc(opts[k])}</option>`).join("");
        return isList
            ? `<select class="ss-rule-row__value" multiple size="5" aria-label="Values">${options}</select>`
            : `<select class="ss-rule-row__value" aria-label="Value"><option value="">Please Select</option>${options}</select>`;
    };

    const addRow = (cond) => {
        const field = cond && CATALOGUE[cond.field] ? cond.field : FIELDS[0].key;
        const ops = Object.keys(CATALOGUE[field].operators);
        const operator = cond && ops.includes(cond.operator) ? cond.operator : ops[0];
        const $row = $(`<div class="ss-rule-row" role="group">
            <div class="ss-rule-row__cell ss-rule-row__cell--field">${fieldSelect(field)}</div>
            <div class="ss-rule-row__cell ss-rule-row__cell--operator">${operatorSelect(field, operator)}</div>
            <div class="ss-rule-row__cell ss-rule-row__cell--value">${valueControl(field, operator, cond ? cond.value : null)}</div>
            <button type="button" class="ss-rule-row__remove" aria-label="Remove condition"><i data-lucide="x"></i></button>
            <div class="ss-rule-row__error"></div>
        </div>`);
        $rows.append($row);
        updateEmpty();
        refreshIcons();
    };

    $rows.on("change", ".ss-rule-row__field", function () {
        const $row = $(this).closest(".ss-rule-row");
        const field = this.value;
        const operator = Object.keys(CATALOGUE[field].operators)[0];
        $row.find(".ss-rule-row__cell--operator").html(operatorSelect(field, operator));
        $row.find(".ss-rule-row__cell--value").html(valueControl(field, operator, null));
        $row.find(".ss-rule-row__error").html("");
    });

    $rows.on("change", ".ss-rule-row__operator", function () {
        const $row = $(this).closest(".ss-rule-row");
        const field = $row.find(".ss-rule-row__field").val();
        const current = $row.find(".ss-rule-row__value").val();
        $row.find(".ss-rule-row__cell--value").html(valueControl(field, this.value, current));
        $row.find(".ss-rule-row__error").html("");
    });

    $rows.on("click", ".ss-rule-row__remove", function () {
        $(this).closest(".ss-rule-row").remove();
        updateEmpty();
    });

    $("#addConditionBtn").on("click", () => addRow(null));

    const serialise = () => ({
        id: $form.find('input[name="id"]').val(),
        task_list_id: $form.find('[name="task_list_id"]').val(),
        trigger: $form.find('[name="trigger"]').val(),
        exclusive_group: $form.find('[name="exclusive_group"]').val(),
        priority: $form.find('[name="priority"]').val(),
        active: $form.find('[name="active"]').prop("checked") ? 1 : 0,
        notes: $form.find('[name="notes"]').val(),
        after_assign_job: $form.find('[name="after_assign_job"]').val() || "",
        conditions: $rows.children().map(function () {
            const $r = $(this);
            const $v = $r.find(".ss-rule-row__value");
            return {
                field: $r.find(".ss-rule-row__field").val(),
                operator: $r.find(".ss-rule-row__operator").val(),
                value: $v.prop("multiple") ? ($v.val() || []) : $v.val(),
            };
        }).get(),
    });

    const paintErrors = (error) => {
        if (!error.response || error.response.status !== 422 || !error.response.data.errors) {
            showSuccess("Something went wrong", (error.response && error.response.data && error.response.data.message) || "Please try again.");
            return;
        }
        Object.entries(error.response.data.errors).forEach(([key, val]) => {
            const m = key.match(/^conditions\.(\d+)\./);
            if (m) {
                $rows.children().eq(Number(m[1])).find(".ss-rule-row__error").html(val);
                return;
            }
            $form.find(`.${key}`).addClass("border-danger");
            $form.find(`.error-${key}`).html(val);
        });
    };

    const resetForm = () => {
        clearValidation();
        $form.find('input[name="id"]').val("0");
        // one process (the applicant side today): pick it straight away
        const processes = $process.find("option").filter(function () { return this.value !== ""; });
        $process.val(processes.length === 1 ? processes.first().val() : "");
        fillTasks($process.val(), null);
        $form.find('[name="trigger"]').val(Object.keys(CATALOGUE).length ? $form.find('[name="trigger"] option').first().val() : "");
        $form.find('[name="exclusive_group"]').val("");
        $form.find('[name="priority"]').val("100");
        $form.find('[name="active"]').prop("checked", true);
        $form.find('[name="notes"]').val("");
        $form.find('[name="after_assign_job"]').val("");
        $rows.empty();
        updateEmpty();
    };

    $("#addRuleBtn").on("click", () => {
        resetForm();
        $("[data-rule-modal-title]").text("Add rule");
        ruleModal.show();
    });

    $("#ruleTableId").on("click", ".edit_btn", function () {
        const id = $(this).attr("data-id");
        resetForm();
        axios.get(route("taskrules.edit", id), { headers: csrf }).then((r) => {
            const d = r.data;
            $("[data-rule-modal-title]").text("Edit rule");
            $form.find('input[name="id"]').val(d.id);
            const owner = TASK_OPTIONS.find((t) => t.id === String(d.task_list_id));
            $process.val(owner ? owner.process : "");
            fillTasks(owner ? owner.process : "", d.task_list_id);
            $form.find('[name="trigger"]').val(d.trigger);
            $form.find('[name="exclusive_group"]').val(d.exclusive_group || "");
            $form.find('[name="priority"]').val(d.priority);
            $form.find('[name="active"]').prop("checked", !!d.active);
            $form.find('[name="notes"]').val(d.notes || "");
            $form.find('[name="after_assign_job"]').val(d.after_assign_job || "");
            (d.conditions || []).forEach(addRow);
            ruleModal.show();
        }).catch(() => showSuccess("Something went wrong", "The rule could not be loaded."));
    });

    $form.on("submit", function (e) {
        e.preventDefault();
        clearValidation();
        setSaving(true);
        const payload = serialise();
        const isEdit = payload.id && payload.id !== "0";
        axios.post(route(isEdit ? "taskrules.update" : "taskrules.store"), payload, { headers: csrf })
            .then(() => {
                setSaving(false);
                ruleModal.hide();
                showSuccess("Saved", isEdit ? "The rule has been updated." : "The rule has been added.");
                ruleTable.init();
            })
            .catch((err) => { setSaving(false); paintErrors(err); });
    });

    /* ---------- archive / restore ---------------------------------------- */
    $("#ruleTableId").on("click", ".delete_btn", function () {
        $("#confirmModal .confModTitle").html("Archive this rule?");
        $("#confirmModal .confModDesc").html("It will stop applying. You can restore it from the archived list.");
        $("#confirmModal .agreeWith").attr("data-id", $(this).attr("data-id")).attr("data-action", "DELETE");
        confModal.show();
    });

    $("#ruleTableId").on("click", ".restore_btn", function () {
        $("#confirmModal .confModTitle").html("Restore this rule?");
        $("#confirmModal .confModDesc").html("It will start applying again if it is active.");
        $("#confirmModal .agreeWith").attr("data-id", $(this).attr("data-id")).attr("data-action", "RESTORE");
        confModal.show();
    });

    $("#confirmModal .agreeWith").on("click", function () {
        const id = $(this).attr("data-id");
        const action = $(this).attr("data-action");
        $("#confirmModal button").attr("disabled", "disabled");
        const req = action === "DELETE"
            ? axios.delete(route("taskrules.destroy", id), { headers: csrf })
            : axios.post(route("taskrules.restore", id), {}, { headers: csrf });
        req.then(() => {
            $("#confirmModal button").removeAttr("disabled");
            confModal.hide();
            showSuccess("Done", action === "DELETE" ? "The rule has been archived." : "The rule has been restored.");
            ruleTable.init();
        }).catch(() => {
            $("#confirmModal button").removeAttr("disabled");
            showSuccess("Something went wrong", "Please try again.");
        });
    });

    /* ---------- preview (dry run) ---------------------------------------- */
    const OUTCOME = {
        assign: ["Would assign", "ok"], exists: ["Already has it", "muted"], removed_by_staff: ["Removed by staff - left alone", "warn"],
        lost_group: ["Lost its group", "muted"], no_match: ["Conditions not met", "muted"], duplicate: ["Same task as another rule", "muted"],
    };
    const factText = (v) => v === true ? "yes" : v === false ? "no" : (v === null || v === undefined || v === "" ? "—" : String(v));

    $("#previewForm").on("submit", function (e) {
        e.preventDefault();
        const ref = $("#previewApplicant").val().trim();
        const $out = $("#previewResult");
        if (!ref) { return; }
        $out.prop("hidden", false).html('<div class="ss-preview-loading">Evaluating…</div>');
        const params = IS_STUDENT
            ? { subject: "student", student: ref, trigger: $("#previewTrigger").val() || "" }
            : { applicant: ref };
        axios.get(route("taskrules.preview"), { params, headers: csrf }).then((r) => {
            const d = r.data;
            const head = IS_STUDENT
                ? `<strong>${esc(d.student.name || "Student")}</strong> <span class="ss-cell-muted">#${d.student.id} · reg ${esc(d.student.registration_no || "—")} · status ${esc(d.student.status || d.student.status_id || "—")}</span>`
                : `<strong>${esc(d.applicant.name || "Applicant")}</strong> <span class="ss-cell-muted">#${d.applicant.id} · ref ${esc(d.applicant.application_no || "—")} · status ${esc(d.applicant.status_id)}${d.applicant.submitted ? " · submitted " + esc(d.applicant.submitted) : ""}</span>`;
            const facts = Object.keys(d.facts).map((k) => `<div class="ss-preview-fact"><span>${esc(d.labels[k] || k)}</span><b>${esc(factText(d.facts[k]))}</b></div>`).join("");
            const rules = d.evaluations.length ? d.evaluations.map((ev) => {
                const [label, tone] = OUTCOME[ev.outcome] || [ev.outcome, "muted"];
                const conds = ev.conditions.length
                    ? ev.conditions.map((c) => `<span class="ss-rule-pill ${c.ok ? "ss-rule-pill--ok" : "ss-rule-pill--bad"}">${c.ok ? "✓" : "✗"} ${esc(c.text)}</span>`).join("")
                    : `<span class="ss-rule-pill ss-rule-pill--always">Always</span>`;
                return `<div class="ss-preview-rule ss-preview-rule--${tone}">
                    <div class="ss-preview-rule__task"><strong>${esc(ev.task || "—")}</strong>${ev.group ? `<span class="ss-rule-group">${esc(ev.group)}</span>` : ""}<span class="ss-cell-muted">rule #${ev.rule_id} · priority ${ev.priority}</span>${ev.job_label ? `<span class="ss-rule-pill ss-rule-pill--job">then: ${esc(ev.job_label)}${ev.job_status ? " · " + esc(ev.job_status) : ""}</span>` : ""}</div>
                    <div class="ss-rule-pills">${conds}</div>
                    <div class="ss-preview-rule__outcome">${esc(label)}</div>
                </div>`;
            }).join("") : `<div class="ss-rule-conditions__empty">No active rules for this trigger.</div>`;
            const would = d.would_assign.length ? d.would_assign.map((t) => `<span class="ss-rule-pill ss-rule-pill--ok">${esc(t.task)}</span>`).join("") : `<span class="ss-cell-muted">nothing</span>`;
            const existing = d.existing.length ? d.existing.map((t) => `<span class="ss-rule-pill">${esc(t.task)} · ${esc(t.status)} · ${esc(t.via)}</span>`).join("") : `<span class="ss-cell-muted">none</span>`;
            $out.html(`
                <div class="ss-preview-head">
                    <div>${head}</div>
                    <div class="ss-preview-summary"><span>Would assign:</span> ${would}</div>
                </div>
                <details class="ss-preview-facts"><summary>Facts the rules saw (${Object.keys(d.facts).length})</summary><div class="ss-preview-facts__grid">${facts}</div></details>
                <div class="ss-preview-rules">${rules}</div>
                <div class="ss-preview-existing"><span>Tasks already on the ${IS_STUDENT ? "student" : "applicant"}:</span> ${existing}</div>
            `);
            refreshIcons();
        }).catch((err) => {
            $out.html(`<div class="ss-rule-conditions__empty">${esc((err.response && err.response.data && err.response.data.message) || "The preview could not be run.")}</div>`);
        });
    });

    /* ---------- recent runs ----------------------------------------------- */
    const loadRuns = () => {
        axios.get(route("taskrules.runs"), { params: { subject: SUBJECT }, headers: csrf }).then((r) => {
            const rows = r.data.data || [];
            $("#runsEmpty").prop("hidden", rows.length > 0);
            $("#runsBody").html(rows.map((x) => `<div class="ss-runs-row" role="row">
                <span class="ss-cell-muted">${esc(x.when)}</span>
                <span><strong>${esc(x.applicant)}</strong> <span class="ss-cell-muted">#${x.applicant_id}${x.application_no ? " · " + esc(x.application_no) : ""}</span></span>
                <span><span class="ss-status-pill ${x.mode === "live" ? "ss-status-pill--on" : "ss-status-pill--off"}">${x.mode === "live" ? "Live" : "Dry run"}</span></span>
                <span>${x.rules_matched} of ${x.rules_total}</span>
                <span>${x.assigned.length ? x.assigned.map((t) => `<span class="ss-rule-pill ss-rule-pill--ok">${esc(t)}</span>`).join(" ") : `<span class="ss-cell-muted">—</span>`}${x.mode === "live" ? ` <span class="ss-cell-muted">(${x.tasks_created} created)</span>` : ""}</span>
            </div>`).join(""));
        }).catch(() => { $("#runsEmpty").prop("hidden", false).text("Runs could not be loaded."); });
    };
    $("#refreshRunsBtn").on("click", loadRuns);
    loadRuns();

    refreshIcons();
})();
