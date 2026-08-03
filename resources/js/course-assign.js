/*
 * Assign / Deassign Students — the redesigned screen.
 *
 * Replaces the legacy `assign.js` + `unsigned.js` pair. Every endpoint, request
 * payload and response shape is unchanged; what moved is the markup the
 * endpoints emit (see AssignController's row helpers) and the selection model:
 *
 *   - a student row is a <li class="cm-stu"> carrying `data-cm-student`, and it
 *     is selected when the <li> has `.is-picked`. Rows the user must not move
 *     carry `.is-locked` and ship with a disabled button;
 *   - `.cm-stugroup` rows caption a status group, `.cm-stunotice` rows report a
 *     search that returned too much. Neither is selectable.
 *
 * No hook on this screen reuses a legacy class name. `datafuture.css` is
 * loaded app-wide and still styles the old screen — `.addRemoveBtns` is
 * absolutely positioned over the page, `.assignStudentsList li` repaints every
 * row — and those selectors outrank single-class `cm-` rules whatever the load
 * order, so the names had to change rather than be fought with `!important`.
 *
 * The three transfer actions all read the same two things: the ticked modules
 * in "Assigned To", and the selected rows in whichever column they act on.
 */

import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import {
    PAGINATION_LANGS,
    PAGINATION_SIZES,
    createRangePainter,
    csrfHeaders,
    refreshIcons,
    setBusy,
    showSuccess,
} from "./course-table-kit";

(function () {
    const page = document.querySelector(".cm-asg");
    if (!page) return;

    const modal = (id) => tailwind.Modal.getOrCreateInstance(document.querySelector(`#${id}`));

    /* The statuses the unassigned list opens on. Reset restores exactly these,
     * so the button returns the screen to its first-load state. */
    const DEFAULT_STATUSES = ["18", "23", "24", "28", "29"];

    const tomOptions = {
        plugins: { dropdown_input: {} },
        placeholder: "Please Select",
        // The search card and the modal both clip their own overflow, so the
        // list has to hang off <body> to stay whole.
        dropdownParent: "body",
        dropdownClass: "ts-dropdown cm-tom-dropdown",
        allowEmptyOption: true,
        maxOptions: 1000,
    };

    /* `hidePlaceholder` defaults to false for a multi, so "Add status…" keeps
     * its place beside the chosen chips rather than disappearing at the first
     * selection — which is what the design shows. */
    const tomMulti = {
        ...tomOptions,
        placeholder: "Add status…",
        plugins: { ...tomOptions.plugins, remove_button: { title: "Remove this status" } },
    };

    const unsignedTerm = new TomSelect("#unsigned_term", tomOptions);
    const unsignedStatuses = new TomSelect("#unsigned_statuses", tomMulti);
    const potentialTermDeclaration = new TomSelect("#potentialTermDeclaration", tomOptions);
    const potentialGroups = new TomSelect("#potentialGroups", tomOptions);
    const potentialModules = new TomSelect("#potentialModules", tomOptions);
    const newGroupId = new TomSelect("#new_group_id", tomOptions);

    potentialGroups.disable();
    potentialModules.disable();

    /* ------------------------------------------------------------------ *
     * Shared helpers
     * ------------------------------------------------------------------ */

    const $existing = () => $('[data-cm-list="existing"]');
    const $potential = () => $('[data-cm-list="potential"]');

    const idsOf = ($rows) =>
        $rows
            .map(function () {
                return this.getAttribute("data-cm-student");
            })
            .get();

    /** Ids of every student already in the Existing column. */
    const existingIds = () => idsOf($existing().find("li.cm-stu"));

    /** Values of the ticked module boxes — the scope of every add and remove. */
    function checkedModules() {
        return $("[data-cm-module]:checked")
            .map(function () {
                return this.value;
            })
            .get();
    }

    /** Toggles a field wrapper and the control inside it in one step. */
    function showField(name, on, control) {
        const field = document.querySelector(`[data-cm-field="${name}"]`);
        if (field) field.hidden = !on;

        if (!control) return;
        if (on) {
            control.enable();
        } else {
            control.clear(true);
            control.clearOptions();
            control.disable();
        }
    }

    /** The mark beside a cascading field's label while its options are fetched. */
    function setFieldSpinner(selectId, on) {
        const field = document.querySelector(selectId).closest(".cm-field");
        const spinner = field && field.querySelector("[data-cm-field-spinner]");
        if (spinner) spinner.style.display = on ? "inline-block" : "none";
    }

    /* ------------------------------------------------------------------ *
     * Inline notices
     *
     * Add and remove report inline rather than in a dialog: the user is mid
     * transfer and the two columns behind the notice are the result.
     * ------------------------------------------------------------------ */

    const resultWrap = document.querySelector("[data-cm-result-wrap]");
    let noticeTimer = null;

    function clearNotice() {
        window.clearTimeout(noticeTimer);
        resultWrap.innerHTML = "";
        resultWrap.hidden = true;
    }

    function notice(tone, message, detailHtml = "") {
        const ok = tone === "ok";
        const glyph = ok
            ? '<path d="M20 6L9 17l-5-5"></path>'
            : '<circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path>';

        resultWrap.innerHTML =
            `<div class="cm-note cm-note--${ok ? "ok" : "bad"}">` +
            `<span class="cm-note__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">${glyph}</svg></span>` +
            `<div class="cm-note__copy"><p>${message}</p>${detailHtml}</div>` +
            '<button type="button" class="cm-note__close" data-cm-note-close aria-label="Dismiss">' +
            '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>' +
            "</button></div>";
        resultWrap.hidden = false;

        window.clearTimeout(noticeTimer);
        // Failures carry detail worth reading, so they stay put; a plain
        // confirmation clears itself.
        if (ok) noticeTimer = window.setTimeout(clearNotice, 6000);
    }

    $(resultWrap).on("click", "[data-cm-note-close]", clearNotice);

    $(resultWrap).on("click", "[data-cm-note-more]", function () {
        const list = this.parentElement.querySelector(".cm-note__detail");
        if (!list) return;

        list.hidden = !list.hidden;
        this.textContent = list.hidden ? "Show details" : "Hide details";
    });

    /* ------------------------------------------------------------------ *
     * Column chrome — counts, selection readouts, empty states
     * ------------------------------------------------------------------ */

    /* A status caption counts the rows under it, so splicing students in or out
     * of the Potential column cannot leave a stale number behind. */
    function recountGroups() {
        $potential()
            .find("li.cm-stugroup")
            .each(function () {
                let rows = 0;
                let node = this.nextElementSibling;
                while (node && !node.classList.contains("cm-stugroup")) {
                    if (node.classList.contains("cm-stu")) rows += 1;
                    node = node.nextElementSibling;
                }

                const label = this.querySelector("[data-cm-group-count]");
                if (label) label.textContent = rows;

                // A group whose last student moved across has nothing to caption.
                this.hidden = rows === 0;
            });
    }

    function paintExisting() {
        const total = $existing().find("li.cm-stu").length;
        const picked = $existing().find("li.cm-stu.is-picked").length;

        $("[data-cm-existing-count]").text(total);
        $("[data-cm-existing-sel]").text(picked > 0 ? `${picked} selected` : "");

        const empty = document.querySelector("[data-cm-existing-empty]");
        if (empty) empty.hidden = total > 0;

        $('[data-cm-act="remove"]').prop("disabled", picked === 0);
        // Re-assign moves one student's whole timetable, so it is deliberately
        // single-select.
        $('[data-cm-act="reassign"]').prop("disabled", picked !== 1);
    }

    function paintPotential() {
        const rows = $potential().find("li.cm-stu");
        const selectable = rows.not(".is-locked");
        const picked = rows.filter(".is-picked").length;

        $("[data-cm-potential-count]").text(rows.length);
        $('[data-cm-act="add"]').prop("disabled", picked === 0);

        const empty = document.querySelector("[data-cm-potential-empty]");
        if (empty) empty.hidden = rows.length > 0 || $potential().find("li.cm-stunotice").length > 0;

        $("[data-cm-select-all]")
            .prop("hidden", selectable.length === 0)
            .text(picked > 0 && picked === selectable.length ? "Clear selection" : "Select all");

        recountGroups();
    }

    function paintModuleToggle() {
        const all = $("[data-cm-module]").length;
        const on = $("[data-cm-module]:checked").length;

        $("[data-cm-mods-toggle]").text(on === all && all > 0 ? "Clear all modules" : "Select all modules");

        // A checkbox cannot tint its own label, so the state is mirrored onto
        // the row; that is what turns the bar blue-and-tick or red-and-cross.
        $("[data-cm-module]").each(function () {
            this.closest(".cm-modrow").classList.toggle("is-on", this.checked);
        });
    }

    /* ------------------------------------------------------------------ *
     * Assigned To — the module scope
     * ------------------------------------------------------------------ */

    $("[data-cm-mods-toggle]").on("click", function () {
        const all = $("[data-cm-module]").length;
        const on = $("[data-cm-module]:checked").length;

        $("[data-cm-module]").prop("checked", !(on === all && all > 0));
        paintModuleToggle();
        reloadExisting();
    });

    $("[data-cm-module]").on("change", function () {
        paintModuleToggle();
        reloadExisting();
    });

    /** Re-reads the Existing column for whatever modules are currently ticked. */
    function reloadExisting() {
        const moduleIds = checkedModules();

        if (moduleIds.length === 0) {
            $existing().removeClass("is-loading").html("");
            paintExisting();

            return;
        }

        $existing().addClass("is-loading");
        axios({
            method: "post",
            url: route("assign.get.existing.student.list.by.module"),
            data: { moduleids: moduleIds },
            headers: csrfHeaders(),
        })
            .then((response) => {
                $existing().removeClass("is-loading").html(response.data.res.htm || "");
                paintExisting();
                refreshIcons();
            })
            .catch(() => {
                $existing().removeClass("is-loading").html("");
                paintExisting();
                notice("bad", "The existing student list could not be reloaded.");
            });
    }

    /* ------------------------------------------------------------------ *
     * Existing column
     * ------------------------------------------------------------------ */

    $(page).on("click", '[data-cm-list="existing"] .cm-stu__row', function () {
        $(this).closest("li").toggleClass("is-picked");
        paintExisting();
    });

    $("[data-cm-existing-filter]").on("input search", function () {
        const value = this.value.toLowerCase().trim();

        $existing()
            .find("li.cm-stu")
            .each(function () {
                const reg = (this.getAttribute("data-cm-reg") || "").toLowerCase();
                const name = (this.getAttribute("data-cm-name") || "").toLowerCase();
                this.hidden = value !== "" && reg.indexOf(value) === -1 && name.indexOf(value) === -1;
            });
    });

    /* The "(n)" chip on a row — the class plans that student sits on. */
    $(page).on("click", "[data-cm-modules]", function () {
        const $row = $(this).closest("li");

        $("[data-cm-modules-eyebrow]").text($row.attr("data-cm-reg") || "Student");
        $("#showAllModulesModal .cm-modal__body").html("");
        modal("showAllModulesModal").show();

        axios({
            method: "post",
            url: route("assign.get.module.list.html"),
            data: { ids: this.getAttribute("data-cm-modules") },
            headers: csrfHeaders(),
        })
            .then((response) => {
                $("#showAllModulesModal .cm-modal__body").html(response.data.res);
                refreshIcons();
            })
            .catch(() => {
                modal("showAllModulesModal").hide();
                notice("bad", "That student's module list could not be loaded.");
            });
    });

    /* ------------------------------------------------------------------ *
     * Potential column
     * ------------------------------------------------------------------ */

    $(page).on("click", '[data-cm-list="potential"] .cm-stu__row', function () {
        const $row = $(this).closest("li");
        if ($row.hasClass("is-locked")) return;

        $row.toggleClass("is-picked");
        paintPotential();
    });

    $("[data-cm-select-all]").on("click", function () {
        const rows = $potential().find("li.cm-stu").not(".is-locked");
        const picked = rows.filter(".is-picked").length;

        rows.toggleClass("is-picked", picked !== rows.length);
        paintPotential();
    });

    /* ------------------------------------------------------------------ *
     * Search — free text, or term → group → module
     * ------------------------------------------------------------------ */

    let searchTimer = null;

    $("#potentialStudentSearch").on("input search", function () {
        const theValue = this.value;

        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(function () {
            // A keyword search and the cascading one are two ways of filling the
            // same column, so starting one abandons the other.
            resetTermSearch();

            if (theValue.trim() === "") {
                $potential().removeClass("is-loading").html("");
                paintPotential();

                return;
            }

            $potential().addClass("is-loading").html("");
            axios({
                method: "post",
                url: route("assign.get.potential.student.list.by.search"),
                data: {
                    theValue: theValue,
                    existingStudents: existingIds(),
                    assignToCourseId: $("#assignToCourseId").val(),
                },
                headers: csrfHeaders(),
            })
                .then((response) => {
                    $potential().removeClass("is-loading").html(response.data.res.htm || "");
                    paintPotential();
                    refreshIcons();
                })
                .catch(() => {
                    $potential().removeClass("is-loading").html("");
                    paintPotential();
                    notice("bad", "The student search failed. Please try again.");
                });
        }, 350);
    });

    $("#potentialTermDeclaration").on("change", function () {
        const termDeclarationId = this.value;

        $("#potentialStudentSearch").val("");
        clearPotential();
        showField("groups", false, potentialGroups);
        showField("modules", false, potentialModules);
        hideSideModules();

        if (!(termDeclarationId > 0)) return;

        setFieldSpinner("#potentialTermDeclaration", true);
        axios({
            method: "post",
            url: route("assign.get.group.list"),
            data: { termDeclarationId: termDeclarationId, assignToCourseId: $("#assignToCourseId").val() },
            headers: csrfHeaders(),
        })
            .then((response) => {
                setFieldSpinner("#potentialTermDeclaration", false);
                showField("groups", true, potentialGroups);

                potentialGroups.addOption({ value: "", text: "Please Select" });
                // The endpoint numbers its rows from 1 in PHP, so the JSON is an
                // object rather than an array.
                Object.values(response.data.res || {}).forEach((row) => {
                    potentialGroups.addOption({ value: row.id, text: row.name });
                });
                potentialGroups.refreshOptions(false);
            })
            .catch(() => {
                setFieldSpinner("#potentialTermDeclaration", false);
                notice("bad", "The group list could not be loaded for that term.");
            });
    });

    $("#potentialGroups").on("change", function () {
        const assignGroupId = this.value;

        $("#potentialStudentSearch").val("");
        clearPotential();
        showField("modules", false, potentialModules);
        hideSideModules();

        if (!(assignGroupId > 0)) return;

        $potential().addClass("is-loading");
        setFieldSpinner("#potentialGroups", true);
        axios({
            method: "post",
            url: route("assign.get.module.student.list"),
            data: {
                termDeclarationId: $("#potentialTermDeclaration").val(),
                assignToCourseId: $("#assignToCourseId").val(),
                assignGroupId: assignGroupId,
                existingStudents: existingIds(),
            },
            headers: csrfHeaders(),
        })
            .then((response) => {
                const res = response.data.res;

                setFieldSpinner("#potentialGroups", false);
                $potential().removeClass("is-loading").html(res.students.htm || "");
                paintPotential();

                const modules = Object.values(res.modules || {});
                if (modules.length > 0) {
                    showField("modules", true, potentialModules);
                    potentialModules.addOption({ value: "", text: "Please Select" });
                    modules.forEach((row) => potentialModules.addOption({ value: row.id, text: row.name }));
                    potentialModules.refreshOptions(false);
                }

                showSideModules(res.module_html || "", modules.length);
                refreshIcons();
            })
            .catch(() => {
                setFieldSpinner("#potentialGroups", false);
                $potential().removeClass("is-loading");
                paintPotential();
                notice("bad", "That group's students could not be loaded.");
            });
    });

    $("#potentialModules").on("change", function () {
        const assignModuleId = this.value;

        // Picking one module dims the rest of the side list rather than removing
        // them, so the group's shape stays visible.
        if (assignModuleId > 0) {
            $("[data-cm-sidemod]").removeClass("is-on");
            $(`[data-cm-sidemod="${assignModuleId}"]`).addClass("is-on");
        } else {
            $("[data-cm-sidemod]").addClass("is-on");
        }

        $("#potentialStudentSearch").val("");
        $potential().addClass("is-loading").html("");
        $('[data-cm-act="add"]').prop("disabled", true);
        setFieldSpinner("#potentialModules", true);

        axios({
            method: "post",
            url: route("assign.get.student.list.by.module"),
            data: {
                termDeclarationId: $("#potentialTermDeclaration").val(),
                assignToCourseId: $("#assignToCourseId").val(),
                assignGroupId: $("#potentialGroups").val(),
                assignModuleId: assignModuleId,
                existingStudents: existingIds(),
            },
            headers: csrfHeaders(),
        })
            .then((response) => {
                setFieldSpinner("#potentialModules", false);
                $potential().removeClass("is-loading").html(response.data.res.htm || "");
                paintPotential();
                refreshIcons();
            })
            .catch(() => {
                setFieldSpinner("#potentialModules", false);
                $potential().removeClass("is-loading").html("");
                paintPotential();
                notice("bad", "That module's students could not be loaded.");
            });
    });

    function clearPotential() {
        $potential().removeClass("is-loading").html("");
        paintPotential();
    }

    function resetTermSearch() {
        potentialTermDeclaration.clear(true);
        showField("groups", false, potentialGroups);
        showField("modules", false, potentialModules);
        hideSideModules();
    }

    function showSideModules(html, count) {
        const box = document.querySelector("[data-cm-sidemods]");
        if (!box) return;

        if (html === "") {
            hideSideModules();

            return;
        }

        document.querySelector("[data-cm-sidemods-body]").innerHTML = html;
        const badge = box.querySelector("[data-cm-sidemod-count]");
        if (badge) badge.textContent = count;
        box.hidden = false;
    }

    function hideSideModules() {
        const box = document.querySelector("[data-cm-sidemods]");
        if (!box) return;

        box.hidden = true;
        document.querySelector("[data-cm-sidemods-body]").innerHTML = "";
    }

    /* ------------------------------------------------------------------ *
     * Add — assign the selected potential students to the ticked modules
     * ------------------------------------------------------------------ */

    $('[data-cm-act="add"]').on("click", function () {
        const plans_id = checkedModules();
        const students_id = idsOf($potential().find("li.cm-stu.is-picked"));

        if (plans_id.length === 0 || students_id.length === 0) {
            notice("bad", "Tick at least one module and select at least one potential student.");

            return;
        }

        setBusy('[data-cm-act="add"]', true);
        $potential().addClass("is-loading");

        axios({
            method: "post",
            url: route("assign.students.to.plan"),
            data: {
                term_declaration: $("#assignToTermDeclarationId").val(),
                plans_id: plans_id,
                students_id: students_id,
            },
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy('[data-cm-act="add"]', false);
                $potential().removeClass("is-loading");
                $potential().find("li.cm-stu").removeClass("is-picked");

                const success = response.data.success;
                const errors = response.data.errors;

                if (success.ids.length > 0) {
                    // The moved rows leave the Potential column outright rather
                    // than being greyed: they are now on the left.
                    success.ids.forEach((id) => {
                        $potential().find(`li[data-cm-student="${id}"]`).remove();
                    });
                    if (success.htm) $existing().prepend(success.htm);
                }

                if (errors.ids.length > 0) {
                    let detail = '<button type="button" class="cm-note__more" data-cm-note-more>Show details</button>';
                    detail += '<ul class="cm-note__detail" hidden>';
                    Object.entries(errors.mod_ids).forEach(([moduleName, regs]) => {
                        detail += `<li><strong>${moduleName}</strong><span>${regs.join(", ")}</span></li>`;
                    });
                    detail += "</ul>";

                    notice(
                        "bad",
                        success.ids.length > 0
                            ? `${success.ids.length} assigned, ${errors.ids.length} were already on one of the ticked modules.`
                            : "Those students are already assigned to the ticked modules.",
                        detail
                    );
                } else if (success.ids.length > 0) {
                    notice("ok", `${success.ids.length} ${success.ids.length === 1 ? "student" : "students"} assigned to the ticked modules.`);
                } else {
                    notice("bad", "Nothing was assigned.");
                }

                paintExisting();
                paintPotential();
                refreshIcons();
            })
            .catch(() => {
                setBusy('[data-cm-act="add"]', false);
                $potential().removeClass("is-loading");
                notice("bad", "Something went wrong. Please try again, or contact the administrator.");
            });
    });

    /* ------------------------------------------------------------------ *
     * Remove — take the selected existing students off the ticked modules
     * ------------------------------------------------------------------ */

    $('[data-cm-act="remove"]').on("click", function () {
        const plans_id = checkedModules();
        const students_id = idsOf($existing().find("li.cm-stu.is-picked"));

        if (plans_id.length === 0 || students_id.length === 0) {
            notice("bad", "Tick at least one module and select at least one existing student.");

            return;
        }

        setBusy('[data-cm-act="remove"]', true);
        $existing().addClass("is-loading");

        axios({
            method: "post",
            url: route("assign.remove.students.from.plan"),
            data: {
                term_declaration: $("#assignToTermDeclarationId").val(),
                plans_id: plans_id,
                students_id: students_id,
            },
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy('[data-cm-act="remove"]', false);
                $existing().removeClass("is-loading");

                const res = response.data.res || {};

                // Each removed student reappears in the Potential column under
                // their status, so they can be put back without a fresh search.
                Object.entries(res).forEach(([status, row]) => {
                    let $head = $potential().find(`li.cm-stugroup[data-cm-group="${status}"]`);
                    if ($head.length === 0) {
                        $potential().append(row.heading);
                        $head = $potential().find(`li.cm-stugroup[data-cm-group="${status}"]`);
                    }

                    Object.entries(row.htm).forEach(([studentId, html]) => {
                        $existing().find(`li[data-cm-student="${studentId}"]`).remove();
                        $potential().find(`li[data-cm-student="${studentId}"]`).remove();
                        $head.after(html);
                    });
                });

                notice("ok", `${students_id.length} ${students_id.length === 1 ? "student" : "students"} removed from the ticked modules.`);
                paintExisting();
                paintPotential();
                refreshIcons();
            })
            .catch(() => {
                setBusy('[data-cm-act="remove"]', false);
                $existing().removeClass("is-loading");
                notice("bad", "Something went wrong. Please try again, or contact the administrator.");
            });
    });

    /* ------------------------------------------------------------------ *
     * Re-Assign — move one student's whole timetable to another group
     * ------------------------------------------------------------------ */

    $('[data-cm-act="reassign"]').on("click", function () {
        const $row = $existing().find("li.cm-stu.is-picked").first();
        if ($row.length !== 1) return;

        $('#studentReAssignModal input[name="student_id"]').val($row.attr("data-cm-student"));
        $("[data-cm-reassign-student]").text(`${$row.attr("data-cm-reg")} · ${$row.attr("data-cm-name")}`);
        modal("studentReAssignModal").show();
    });

    // Native listener, not jQuery: the theme dispatches a CustomEvent whose
    // *type* contains the dots, so `.on("hide.tw.modal")` would be read as the
    // event "hide" in the "tw.modal" namespace and never fire.
    document.querySelector("#studentReAssignModal").addEventListener("hide.tw.modal", function () {
        $('#studentReAssignModal input[name="student_id"]').val("0");
        $("[data-cm-reassign-student]").html("&mdash;");
        newGroupId.clear(true);

        $("#reAssignStdBtn").attr("disabled", "disabled");
        document.querySelector("[data-cm-swap]").hidden = true;
        $("[data-cm-oldgroup], [data-cm-newgroup]").html("");
        $("[data-cm-oldmods], [data-cm-newmods]").html("");
    });

    $("#new_group_id").on("change", function () {
        const new_group_id = this.value;
        const swap = document.querySelector("[data-cm-swap]");

        if (!(new_group_id > 0)) {
            $("#reAssignStdBtn").attr("disabled", "disabled");
            swap.hidden = true;

            return;
        }

        axios({
            method: "post",
            url: route("assigns.get.modules.for.reassign"),
            data: {
                academic_year_id: $('#studentReAssignModal [name="academic_year_id"]').val(),
                term_declaration_id: $('#studentReAssignModal [name="term_declaration_id"]').val(),
                course_id: $('#studentReAssignModal [name="course_id"]').val(),
                old_group_id: $('#studentReAssignModal [name="group_id"]').val(),
                new_group_id: new_group_id,
            },
            headers: csrfHeaders(),
        })
            .then((response) => {
                $("#reAssignStdBtn").removeAttr("disabled");
                $("[data-cm-oldgroup]").text(response.data.og_name);
                $("[data-cm-oldmods]").html(response.data.oldModules);
                $("[data-cm-newgroup]").text(response.data.ng_name);
                $("[data-cm-newmods]").html(response.data.newModules);
                swap.hidden = false;
            })
            .catch(() => {
                $("#reAssignStdBtn").attr("disabled", "disabled");
                swap.hidden = true;
                showSuccess("Something went wrong", "That group's modules could not be loaded.", "warn");
            });
    });

    $("#studentReAssignForm").on("submit", function (event) {
        event.preventDefault();

        const group = $("#new_group_id").val();
        const newModules = $(this).find("[data-cm-newmod]:checked").length;

        if (!(group > 0) || newModules === 0) {
            showSuccess("Nothing to do", "Choose a group and tick at least one module to move the student onto.", "warn");

            return;
        }

        setBusy("#reAssignStdBtn", true);
        axios({
            method: "post",
            url: route("assigns.re.assign.students.new.group"),
            data: new FormData(this),
            headers: csrfHeaders(),
        })
            .then(() => {
                setBusy("#reAssignStdBtn", false);
                modal("studentReAssignModal").hide();
                showSuccess("Re-assigned", "The student was moved to the selected group.");

                window.setTimeout(() => window.location.reload(), 1200);
            })
            .catch((error) => {
                setBusy("#reAssignStdBtn", false);

                // 422 carries the attendance / class-type mismatch explanation,
                // which is the whole reason the move was refused.
                const message =
                    error.response && error.response.status === 422
                        ? error.response.data.message
                        : "Something went wrong. Please try again, or contact the administrator.";

                showSuccess("The student was not moved", message, "warn");
            });
    });

    /* ------------------------------------------------------------------ *
     * Unassigned students
     * ------------------------------------------------------------------ */

    let unsignedTable = null;
    let unsignedTotal = 0;

    const paintUnsignedRange = createRangePainter(
        "#unsignedStudentList",
        () => unsignedTable,
        () => unsignedTotal
    );

    /*
     * Status badge tint. A student status is a stage, not a pass/fail, so the
     * column reads as a palette rather than green-or-red: anything that stops
     * the student is red, anything provisional is amber, a registration is
     * violet, and a normal standing is green.
     */
    function statusTone(value) {
        if (/discard|withdraw|suspend|terminat|fail|defer|cancel/i.test(value)) return "is-bad";
        if (/extend|hold|pending|await|interrupt|break/i.test(value)) return "is-hold";
        if (/register/i.test(value)) return "is-reg";
        if (/\bnew\b|applic|offer/i.test(value)) return "is-new";

        return "";
    }

    function eveningGlyph(value) {
        if (value !== "Yes" && value !== "No") return "";

        const on = value === "Yes";

        return (
            `<span class="cm-eve ${on ? "is-eve" : ""}" title="${on ? "Evening & weekend" : "Weekdays"}">` +
            (on
                ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 10V2M4.93 10.93l1.41 1.41M2 18h2M20 18h2M19.07 10.93l-1.41 1.41M22 22H2M16 6l-4 4-4-4M16 18a4 4 0 0 0-8 0"></path></svg>'
                : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path></svg>') +
            "</span>"
        );
    }

    function buildUnsignedTable() {
        unsignedTable = new Tabulator("#unsignedStudentList", {
            ajaxURL: route("assign.unsignned.list"),
            ajaxParams: {
                unsignedTerm: $("#unsigned_term").val() || "",
                unsignedStatuses: $("#unsigned_statuses").val() || "",
                unsigned_course_id: $("#unsigned_course_id").val() || "0",
            },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: PAGINATION_SIZES,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No unassigned students for that term and status",
            langs: PAGINATION_LANGS,
            selectable: true,
            columns: [
                {
                    formatter: "rowSelection",
                    titleFormatter: "rowSelection",
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: 46,
                    headerSort: false,
                    download: false,
                    cssClass: "cm-cell--check",
                    cellClick(e, cell) {
                        cell.getRow().toggleSelect();
                    },
                },
                {
                    // Photo, registration number and name in one cell: they
                    // identify the same person, so splitting them across two
                    // columns only spread one fact over more width.
                    title: "Student",
                    field: "s_registration_no",
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 264,
                    cssClass: "cm-cell--student",
                    formatter(cell) {
                        const d = cell.getData();
                        const first = d.s_first_name || "";
                        const last = d.s_last_name || "";
                        const name = `${first} ${last}`.trim();
                        const initials = `${first[0] || ""}${last[0] || ""}`.toUpperCase();

                        const avatar = d.s_photo
                            ? `<span class="cm-avatar"><img src="${d.s_photo}" alt="" loading="lazy"></span>`
                            // Tone keyed off the id, so a student keeps the
                            // same colour every time the list is rebuilt.
                            : `<span class="cm-avatar" data-cm-tone="${(Number(d.s_id) || 0) % 6}">${initials}</span>`;

                        return (
                            '<span class="cm-studentcell">' +
                            avatar +
                            '<span class="cm-stack">' +
                            `<span class="cm-stack__name">${cell.getValue() || ""}</span>` +
                            `<span class="cm-stack__sub">${name || "&mdash;"}</span>` +
                            "</span></span>"
                        );
                    },
                },
                {
                    title: "Course",
                    field: "c_name",
                    headerHozAlign: "left",
                    widthGrow: 2,
                    minWidth: 220,
                    cssClass: "cm-cell--clamp",
                },
                {
                    title: "Evening / Weekend",
                    field: "std_ev_wk",
                    headerHozAlign: "left",
                    headerSort: false,
                    width: 160,
                    formatter: (cell) => eveningGlyph(cell.getValue()),
                },
                {
                    title: "Group",
                    field: "group",
                    headerHozAlign: "left",
                    headerSort: false,
                    widthGrow: 1,
                    minWidth: 140,
                },
                {
                    title: "Group Eve / Weekend",
                    field: "group_ev_wk",
                    headerHozAlign: "left",
                    headerSort: false,
                    width: 176,
                    formatter: (cell) => eveningGlyph(cell.getValue()),
                },
                {
                    title: "Status",
                    field: "sts_name",
                    headerHozAlign: "left",
                    widthGrow: 1,
                    minWidth: 170,
                    formatter(cell) {
                        const v = String(cell.getValue() || "");
                        // The student id rides along so "Move to Potential" can
                        // read it straight off the selected rows.
                        const carrier = `<input type="hidden" data-cm-sid value="${cell.getData().s_id}">`;
                        if (!v) return carrier;

                        return `<span class="cm-statusbadge ${statusTone(v)}">${v}</span>${carrier}`;
                    },
                },
            ],
            ajaxResponse(url, params, response) {
                unsignedTotal = response.total_rows > 0 ? response.total_rows : 0;
                paintUnsignedCount(0);

                return response;
            },
            renderComplete() {
                refreshIcons();
                paintUnsignedRange();

                // fitColumns leaves a sub-pixel overflow that shows as a
                // horizontal scrollbar; every table in this module trims the
                // last column by 1px to absorb it.
                const cols = this.getColumns();
                if (cols.length > 0) {
                    const last = cols[cols.length - 1];
                    last.setWidth(last.getWidth() - 1);
                }
            },
            rowSelectionChanged(data, rows) {
                paintUnsignedCount(rows.length);
                document.querySelector("[data-cm-move]").hidden = rows.length === 0;
            },
            selectableCheck: (row) => row.getData().s_id > 0,
        });
    }

    function paintUnsignedCount(picked) {
        const label = document.querySelector("[data-cm-unsigned-count]");
        label.setAttribute("data-total", unsignedTotal);

        if (unsignedTotal === 0) {
            label.textContent = "";

            return;
        }

        label.textContent = picked > 0 ? `${picked} of ${unsignedTotal} selected` : `${unsignedTotal} rows`;
    }

    $("[data-cm-unsigned-go]").on("click", function () {
        // Reported in the dialog rather than inline: the inline notice sits
        // below the "Assigned To" card, well away from these two fields.
        if (!$("#unsigned_term").val() || !($("#unsigned_statuses").val() || []).length) {
            showSuccess("Nothing to list", "Choose a term declaration and at least one student status.", "warn");

            return;
        }

        document.querySelector("[data-cm-unsigned-empty]").hidden = true;
        document.querySelector("[data-cm-unsigned-wrap]").hidden = false;
        buildUnsignedTable();
    });

    $("[data-cm-unsigned-reset]").on("click", function () {
        unsignedTerm.clear(true);
        unsignedStatuses.clear(true);
        DEFAULT_STATUSES.forEach((id) => unsignedStatuses.addItem(id, true));

        unsignedTotal = 0;
        paintUnsignedCount(0);

        document.querySelector("[data-cm-move]").hidden = true;
        document.querySelector("[data-cm-unsigned-wrap]").hidden = true;
        document.querySelector("[data-cm-unsigned-empty]").hidden = false;

        if (unsignedTable) {
            unsignedTable.destroy();
            unsignedTable = null;
        }
    });

    $("[data-cm-move]").on("click", function () {
        const ids = $("#unsignedStudentList")
            .find(".tabulator-row.tabulator-selected [data-cm-sid]")
            .map(function () {
                return this.value;
            })
            .get();

        if (ids.length === 0) return;

        $potential().addClass("is-loading").html("");
        axios({
            method: "post",
            url: route("assign.generage.potential.list.from.unsigned.list"),
            data: {
                student_ids: ids,
                existingStudents: existingIds(),
                assignToCourseId: $("#assignToCourseId").val(),
            },
            headers: csrfHeaders(),
        })
            .then((response) => {
                $potential().removeClass("is-loading").html(response.data.res.htm || "");
                paintPotential();
                refreshIcons();

                // A keyword search would contradict what was just moved across.
                $("#potentialStudentSearch").val("");
                resetTermSearch();

                document.querySelector("[data-cm-move]").hidden = true;
                if (unsignedTable) unsignedTable.deselectRow();

                $("html, body").animate(
                    { scrollTop: $("[data-cm-transfer]").offset().top - 90 },
                    600
                );
            })
            .catch(() => {
                $potential().removeClass("is-loading").html("");
                paintPotential();
                notice("bad", "Those students could not be moved to the potential list.");
            });
    });

    window.addEventListener("resize", () => {
        if (!unsignedTable) return;
        unsignedTable.redraw();
        refreshIcons();
    });

    /* ------------------------------------------------------------------ *
     * First paint
     * ------------------------------------------------------------------ */

    paintExisting();
    paintPotential();
    paintModuleToggle();
    refreshIcons();
})();
