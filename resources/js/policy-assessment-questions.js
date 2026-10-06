import xlsx from "xlsx";
import { createElement, createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import { LEVELS, badgeSvg, escapeHtml, isLevel, levelLabel, levelPill } from "./policy-assessment/levels";

("use strict");

/*
 * Policy Assessments › Question Bank › one policy's questions (HR).
 *
 * Each row is a review card: the question, its level, its options with the
 * correct one marked, where it came from, and a collapsible "Why" with the
 * explanation and the policy excerpt. The switch on each card activates /
 * deactivates it in place, without reloading the list, so HR can work down a
 * page of drafts.
 *
 * Every question has a level (Beginner / Intermediate / Expert). An exam never
 * draws from one level alone: it takes a set share of each level of the
 * policy's questions per test. So the header shows whether each of the three
 * exams is ready (what it needs from every level against what is active),
 * each level's own counts, and the pattern itself. The list can be narrowed to
 * one level (Level filter, or a click on a level card), and "Activate all
 * drafts" only touches the level shown.
 *
 * The server works out and words all of it (counts.exams, counts.levels,
 * counts.pattern); the percentages are never spelt out here.
 */
(function () {
    const tableNode = document.querySelector("#policyQuestionTable");

    if (!tableNode) {
        return;
    }

    const policyId = tableNode.getAttribute("data-policy");
    const policyTitle = tableNode.getAttribute("data-title") || "policy";
    const MIN_OPTIONS = 2;
    const MAX_OPTIONS = 6;
    const DEFAULT_OPTIONS = 4;
    const STATE_ICONS = { ready: "check-circle", short: "alert-triangle", blocked: "x-circle" };
    const EXAM_ICONS = { ready: "check-circle", blocked: "x-circle" };

    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const questionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#questionModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const form = document.getElementById("questionForm");
    const $form = $("#questionForm");
    const optionList = document.getElementById("optionList");
    const levelChoice = document.getElementById("questionLevelChoice");
    let tableContent = null;
    let whyOpen = false;
    /* Ids whose "Why" is open, so a redraw (resize, in-place update) keeps them open. */
    const openWhy = new Set();
    /* The level the list is showing ("" = every level): what "Activate all drafts" acts on. */
    let currentLevel = "";
    /* Header numbers as the page rendered them, then as each response returns them. */
    let lastCounts = (() => {
        try {
            return JSON.parse(tableNode.getAttribute("data-counts") || "null");
        } catch (error) {
            return null;
        }
    })();

    /** The Level filter's value, read safely: a known level or "". */
    const filterLevel = () => {
        const value = $("#level").val();

        return isLevel(value) ? value : "";
    };

    const refreshIcons = () => {
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    };

    /*
     * Inline SVG for markup built in JS. Tabulator can re-run formatters on a
     * redraw (e.g. a window resize) without firing renderComplete, so table
     * cells must not rely on createIcons() swapping <i data-lucide> later.
     */
    const iconSvg = (name, extraClass = "") => {
        const key = String(name).split("-").map((part) => part.charAt(0).toUpperCase() + part.slice(1)).join("");
        const node = icons[key];

        if (!node) {
            return "";
        }

        const svg = createElement(node);

        svg.setAttribute("class", `lucide lucide-${name}${extraClass ? " " + extraClass : ""}`);
        svg.setAttribute("stroke-width", "1.7");
        svg.setAttribute("aria-hidden", "true");

        return svg.outerHTML;
    };

    const plural = (count, one, many) => `${count} ${count === 1 ? one : many}`;

    const setBusy = (selector, busy) => {
        const button = document.querySelector(selector);

        if (!button) {
            return;
        }

        button.disabled = busy;
        const spinner = button.querySelector(".ss-spinner");

        if (spinner) {
            spinner.style.cssText = busy ? "display: inline-block;" : "display: none;";
        }
    };

    const showSuccess = (title, description) => {
        $("#successModal .successModalTitle").text(title);
        $("#successModal .successModalDesc").text(description);
        successModal.show();
    };

    const showWarning = (title, description) => {
        $("#warningModal .successModalTitle").text(title);
        $("#warningModal .successModalDesc").text(description);
        warningModal.show();
    };

    const errorMessage = (error, fallback) => {
        return error?.response?.data?.message || fallback;
    };

    const showConfirm = (title, description, action, recordID, positive) => {
        const $agree = $("#confirmModal .agreeWith");

        $("#confirmModal .confModTitle").text(title);
        $("#confirmModal .confModDesc").text(description);
        $agree.attr("data-id", recordID);
        $agree.attr("data-action", action);
        $agree.toggleClass("ss-btn--danger", !positive).toggleClass("ss-btn--success", !!positive);
        confirmModal.show();
    };

    /* ------------------------------------------------------------------
     * Header: counts, exam readiness, questions by level, exam pattern,
     * bank size, "Activate" button. The server words each exam
     * (counts.exams[level]: ready, state ready|blocked, state_label, needed /
     * available / short per question level, note, summary) and each level
     * (counts.levels[level]: active, total, drafts, state ready|short|blocked,
     * state_label, summary, note), so this and the Blade header always say
     * the same thing. Every response carries fresh counts, so switching a
     * question on or off, "Activate all drafts" and a change to the policy's
     * questions per test all show here straight away.
     * ------------------------------------------------------------------ */

    /** Drafts at the level the list is showing (every level when unfiltered). */
    const draftsInView = () => {
        if (!lastCounts) {
            return Number($('#policyHead [data-count="drafts"]').text() || 0);
        }

        return currentLevel
            ? Number(lastCounts.levels?.[currentLevel]?.drafts || 0)
            : Number(lastCounts.drafts || 0);
    };

    /** "Activate all drafts" / "Activate Expert drafts", off when there is nothing to activate. */
    const syncActivateButton = () => {
        const label = levelLabel(currentLevel);
        const none = draftsInView() <= 0;
        const $button = $("#activateAllBtn");

        $button.find("[data-activate-label]").text(label ? `Activate ${label} drafts` : "Activate all drafts");
        $button.prop("disabled", none).attr("title", none ? (label ? `No ${label} drafts to activate` : "No drafts to activate") : null);
    };

    /** One level's card. Same markup as the loop in questions.blade.php. */
    const levelCard = (level, info) => {
        const data = info || {};
        const state = Object.prototype.hasOwnProperty.call(STATE_ICONS, data.state) ? data.state : "blocked";
        const label = levelLabel(level);
        const active = Number(data.active || 0);
        const total = Number(data.total || 0);
        const bar = total > 0 ? Math.round((active / total) * 100) : 0;
        const selected = currentLevel === level;
        const aria = `${label} questions — ${data.summary || `${active} active of ${total}`}. ${data.note || ""}`.trim();

        return `<button type="button" class="pa-level-ready pa-level-ready--${level} is-${state}${selected ? " is-selected" : ""}" data-level="${level}" aria-pressed="${selected ? "true" : "false"}" aria-label="${escapeHtml(aria)}" title="${escapeHtml(selected ? "Show every level" : `Show only ${label} questions`)}">`
            + `<span class="pa-level-ready__top">${levelPill(level)}<span class="pa-level-state pa-readiness--${state}">${iconSvg(STATE_ICONS[state])}${escapeHtml(data.state_label || "")}</span></span>`
            + `<span class="pa-level-ready__count"><strong>${active}</strong> active of ${total}</span>`
            + `<span class="pa-level-ready__bar" aria-hidden="true"><span style="width: ${bar}%"></span></span>`
            + `<span class="pa-level-ready__note">${escapeHtml(data.note || "")}</span>`
            + `</button>`;
    };

    const renderLevels = (levels) => {
        const grid = document.getElementById("levelReadiness");

        if (!grid || !levels) {
            return;
        }

        /* Re-rendering replaces the buttons: keep keyboard focus on the same level. */
        const focused = grid.contains(document.activeElement) ? document.activeElement.getAttribute("data-level") : null;

        grid.innerHTML = LEVELS.map((level) => levelCard(level, levels[level])).join("");

        if (focused) {
            grid.querySelector(`[data-level="${focused}"]`)?.focus();
        }
    };

    /** One exam's readiness card. Same markup as partials/bank-exam-readiness.blade.php. */
    const examCard = (level, info) => {
        const data = info || {};
        const state = Object.prototype.hasOwnProperty.call(EXAM_ICONS, data.state) ? data.state : "blocked";
        const name = data.name || `${levelLabel(level)} exam`;
        const rows = LEVELS.map((questionLevel) => {
            const needed = Number(data.needed?.[questionLevel] || 0);
            const available = Number(data.available?.[questionLevel] || 0);
            const short = Number(data.short?.[questionLevel] || 0);

            return `<div class="pa-exam__row${short > 0 ? " is-short" : ""}" data-level="${questionLevel}" data-needed="${needed}" data-available="${available}" data-short="${short}">`
                + levelPill(questionLevel)
                + `<span class="pa-exam__need">needs <strong>${needed}</strong></span>`
                + `<span class="pa-exam__have">has <strong>${available}</strong></span>`
                + `<span class="pa-exam__mark" aria-hidden="true">${iconSvg(short > 0 ? "x" : "check")}</span>`
                + `</div>`;
        }).join("");

        return `<div class="pa-exam pa-exam--${level} is-${state}" data-exam="${level}" data-ready="${data.ready ? 1 : 0}" role="group" aria-label="${escapeHtml(data.summary || name)}">`
            + `<div class="pa-exam__top"><span class="pa-exam__name"><span class="pa-exam__medal" aria-hidden="true">${badgeSvg(level, "sm")}</span><strong>${escapeHtml(name)}</strong></span>`
            + `<span class="pa-level-state pa-readiness--${state}">${iconSvg(EXAM_ICONS[state])}${escapeHtml(data.state_label || "")}</span></div>`
            + `<div class="pa-exam__rows">${rows}</div>`
            + `<p class="pa-exam__note">${escapeHtml(data.note || "")}</p>`
            + `</div>`;
    };

    const renderExams = (counts) => {
        const grid = document.getElementById("examReadiness");

        if (!grid || !counts.exams) {
            return;
        }

        grid.innerHTML = LEVELS.map((level) => examCard(level, counts.exams[level])).join("");

        /* A live region: only touched when the tally changes, so it is not read out on every click. */
        const tally = document.getElementById("examsReadyNote");

        if (tally) {
            const ready = Number(counts.exams_ready || 0);
            const text = `${ready} of ${LEVELS.length} exams ready`;

            if (tally.textContent !== text) {
                tally.textContent = text;
            }

            tally.classList.toggle("is-ready", ready >= LEVELS.length);
        }
    };

    /** The "Exam pattern" rows. Same markup as partials/bank-exam-pattern.blade.php. */
    const patternRows = (pattern) => LEVELS.map((exam) => {
        const row = (pattern.exams && pattern.exams[exam]) || {};
        const cells = LEVELS.map((level) => {
            const percent = Number(row.mix?.[level] || 0);
            const quota = Number(row.quota?.[level] || 0);

            return `<td class="${quota === 0 ? "is-none" : ""}" data-level="${level}" data-percent="${percent}" data-quota="${quota}"><strong>${quota}</strong> <span>${quota === 1 ? "question" : "questions"} · ${percent}%</span></td>`;
        }).join("");

        return `<tr data-exam="${exam}"><th scope="row">${escapeHtml(row.name || `${levelLabel(exam)} exam`)}</th>${cells}</tr>`;
    }).join("");

    const renderPattern = (pattern) => {
        const body = document.getElementById("examPatternRows");

        if (!body || !pattern || !pattern.exams) {
            return;
        }

        const perTest = Number(pattern.per_test || 0);

        $("#examPattern [data-pattern-per-test]").text(perTest);
        $("#examPattern [data-pattern-per-test-word]").text(perTest === 1 ? "question" : "questions");
        body.innerHTML = patternRows(pattern);
    };

    /** The test rules in the header that the exam numbers depend on. */
    const renderFacts = (counts) => {
        const perTest = Number(counts.questions_per_attempt || 0);
        const timed = counts.time_limit_minutes !== null && counts.time_limit_minutes !== undefined && Number(counts.time_limit_minutes) > 0;

        if (perTest > 0) {
            $('#policyHead [data-fact="per-test"]').text(`${plural(perTest, "question", "questions")} per test`);
        }

        if (counts.time_limit_label) {
            $('#policyHead [data-fact="time-limit"]').text(timed ? `${counts.time_limit_label} time limit` : counts.time_limit_label);
        }
    };

    /** Only the selected state changes (filter switched): no rebuild, so focus stays put. */
    const markSelectedLevel = () => {
        document.querySelectorAll("#levelReadiness [data-level]").forEach((card) => {
            const level = card.getAttribute("data-level");
            const selected = level === currentLevel;

            card.classList.toggle("is-selected", selected);
            card.setAttribute("aria-pressed", selected ? "true" : "false");
            card.setAttribute("title", selected ? "Show every level" : `Show only ${levelLabel(level)} questions`);
        });
    };

    const renderBankSize = (counts) => {
        const note = document.getElementById("bankSizeNote");

        if (!note) {
            return;
        }

        const total = Number(counts.total || 0);
        const target = Number(counts.bank_target || 0);
        const below = target > 0 && total < target;

        note.classList.toggle("is-below", below);
        note.innerHTML = iconSvg(below ? "alert-triangle" : "library")
            + `<span>${below ? `${plural(total, "question", "questions")} in the bank — below the ${target}-question target.` : `${total} questions in the bank.`}</span>`;
    };

    const applyCounts = (counts) => {
        if (!counts) {
            return;
        }

        lastCounts = counts;
        $('#policyHead [data-count="active"]').text(Number(counts.active || 0));
        $('#policyHead [data-count="drafts"]').text(Number(counts.drafts || 0));
        $('#policyHead [data-count="archived"]').text(Number(counts.archived || 0));
        renderFacts(counts);
        renderExams(counts);
        renderLevels(counts.levels);
        renderPattern(counts.pattern);
        renderBankSize(counts);
        syncActivateButton();
    };

    /* ------------------------------------------------------------------
     * Review card
     * ------------------------------------------------------------------ */

    const switchHtml = (data) => {
        const on = data.is_active == 1;
        const incomplete = data.well_formed != 1;
        const title = on
            ? "Active: click to move back to drafts"
            : incomplete
                ? "Incomplete: edit this question before switching it on"
                : "Draft: click to make active";

        return `<button type="button" role="switch" aria-checked="${on ? "true" : "false"}" data-id="${escapeHtml(data.id)}" class="q_status_toggle pa-switch ${on ? "is-on" : ""}" title="${title}"><span class="pa-switch__track"><span class="pa-switch__thumb"></span></span><span class="pa-switch__label">${on ? "Active" : "Draft"}</span></button>`;
    };

    /*
     * Review aid: true when the correct option is the only longest one and at
     * least 15% longer than the average wrong option, so a guesser could pick
     * it on length alone. Only for questions with exactly one correct option.
     */
    const answerStandsOut = (options) => {
        const lengths = (options || []).map((option) => ({ correct: option.is_correct == 1, length: String(option.text ?? "").trim().length }));
        const correct = lengths.filter((option) => option.correct);
        const wrong = lengths.filter((option) => !option.correct);

        if (correct.length !== 1 || wrong.length < 1) {
            return false;
        }

        const longestWrong = Math.max(...wrong.map((option) => option.length));
        const meanWrong = wrong.reduce((sum, option) => sum + option.length, 0) / wrong.length;

        return correct[0].length > longestWrong && correct[0].length >= meanWrong * 1.15;
    };

    const cardFormatter = (cell) => {
        const data = cell.getData();
        const archived = data.deleted_at != null;
        const active = data.is_active == 1;
        const state = archived ? "is-archived" : active ? "is-active" : "is-draft";
        const badges = [levelPill(data.level)];

        if (archived) {
            badges.push(`<span class="pa-badge pa-badge--archived">Archived</span>`);
        } else if (active) {
            badges.push(`<span class="pa-badge pa-badge--active">${iconSvg("check-circle-2")}Active</span>`);
        } else {
            badges.push(`<span class="pa-badge pa-badge--draft">Draft · not in tests</span>`);
        }

        if (data.well_formed != 1 && data.issue) {
            badges.push(`<span class="pa-badge pa-badge--issue">${iconSvg("alert-triangle")}${escapeHtml(data.issue)}</span>`);
        }

        const options = (data.options || []).map((option) => {
            const correct = option.is_correct == 1;

            return `<li class="pa-qopt ${correct ? "is-correct" : ""}"><span class="pa-qopt__letter">${escapeHtml(option.letter)}</span><span class="pa-qopt__text">${escapeHtml(option.text)}</span>${correct ? `<span class="pa-qopt__mark">${iconSvg("check")}Correct</span>` : ""}</li>`;
        }).join("");

        const lengthHint = !archived && answerStandsOut(data.options)
            ? `<p class="pa-qcard__hint">${iconSvg("ruler")}<span><strong>Answer stands out:</strong> the correct option is the longest. Consider balancing the options.</span></p>`
            : "";

        let why = "";

        if (data.explanation || data.source_excerpt) {
            why = `<details class="pa-why" data-id="${escapeHtml(data.id)}" ${whyOpen || openWhy.has(String(data.id)) ? "open" : ""}><summary>${iconSvg("lightbulb")}<span>Why this answer</span>${iconSvg("chevron-down", "pa-why__chevron")}</summary><div class="pa-why__body">`
                + (data.explanation ? `<p class="pa-why__text">${escapeHtml(data.explanation)}</p>` : "")
                + (data.source_excerpt ? `<blockquote class="pa-why__quote"><span>From the policy</span>${escapeHtml(data.source_excerpt)}</blockquote>` : "")
                + `</div></details>`;
        } else {
            why = `<p class="pa-why pa-why--empty">No explanation or policy excerpt recorded.</p>`;
        }

        const actions = archived
            ? `<button data-id="${escapeHtml(data.id)}" type="button" class="q_restore_btn ss-row-action ss-row-action--restore" aria-label="Restore question" title="Restore">${iconSvg("rotate-cw")}</button>`
            : `${switchHtml(data)}<button data-id="${escapeHtml(data.id)}" type="button" class="q_edit_btn ss-row-action ss-row-action--edit" aria-label="Edit question" title="Edit">${iconSvg("pencil")}</button><button data-id="${escapeHtml(data.id)}" type="button" class="q_delete_btn ss-row-action ss-row-action--delete" aria-label="Archive question" title="Archive">${iconSvg("trash-2")}</button>`;

        return `<article class="pa-qcard ${state}">`
            + `<header class="pa-qcard__head"><span class="pa-qcard__num">Q${escapeHtml(data.sl)}</span><span class="pa-qcard__badges">${badges.join("")}</span><span class="pa-qcard__actions">${actions}</span></header>`
            + `<p class="pa-qcard__question">${escapeHtml(data.question)}</p>`
            + (options ? `<ol class="pa-qcard__options">${options}</ol>` : `<p class="pa-why pa-why--empty">No options yet.</p>`)
            + lengthHint
            + why
            + `</article>`;
    };

    const emptyText = (status, level) => {
        const label = levelLabel(level);

        if (status === "drafts") {
            return label ? `No ${label} drafts left to review` : "No drafts left to review";
        }

        return label ? `No ${label} questions found` : "No questions found";
    };

    /*
     * "Download PDF" exports what the list is showing: the Level filter and,
     * for Active / Drafts, the status. Archived questions are never in the
     * PDF, so "All" and "Archived" both export every question. Without this
     * script the link still downloads the whole bank.
     */
    const syncPdfLink = (level, status) => {
        const link = document.getElementById("downloadPdfBtn");

        if (!link) {
            return;
        }

        const base = link.getAttribute("data-url") || String(link.getAttribute("href") || "").split("?")[0];
        const params = new URLSearchParams();

        if (level) {
            params.set("level", level);
        }

        if (status === "active") {
            params.set("status", "active");
        } else if (status === "drafts") {
            params.set("status", "draft");
        }

        const query = params.toString();

        link.setAttribute("href", query ? `${base}?${query}` : base);
    };

    const buildTable = () => {
        const querystr = $("#query").val() || "";
        const status = $("#status").val() || "all";
        const level = filterLevel();

        currentLevel = level;
        markSelectedLevel();
        syncActivateButton();
        syncPdfLink(level, status);

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#policyQuestionTable", {
            ajaxURL: route("policy.assessment.question.list", policyId),
            ajaxParams: { querystr, status, level },
            ajaxFiltering: true,
            ajaxSorting: true,
            pagination: "remote",
            paginationSize: 25,
            paginationSizeSelector: [10, 25, 50, 100],
            layout: "fitColumns",
            responsiveLayout: false,
            virtualDom: false,
            headerVisible: false,
            index: "id",
            placeholder: emptyText(status, level),
            ajaxResponse(url, params, response) {
                applyCounts(response.counts);

                return response;
            },
            columns: [
                {
                    title: "Question",
                    field: "question",
                    headerSort: false,
                    formatter: cardFormatter,
                },
                { title: "Level", field: "level_label", visible: false, download: true },
                { title: "Status", field: "status_label", visible: false, download: true },
                { title: "Options", field: "options_plain", visible: false, download: true },
                { title: "Correct answer", field: "correct_text", visible: false, download: true },
                { title: "Why this answer", field: "explanation", visible: false, download: true },
                { title: "Source excerpt", field: "source_excerpt", visible: false, download: true },
            ],
            renderComplete() {
                refreshIcons();
            },
        });
    };

    const updateRow = (rowData) => {
        if (!rowData || !tableContent) {
            return;
        }

        const row = tableContent.getRow(rowData.id);

        if (!row) {
            return;
        }

        row.update({ ...rowData, sl: row.getData().sl });
        row.reformat();
        refreshIcons();
    };

    /* ------------------------------------------------------------------
     * Add / edit modal: options repeater
     * ------------------------------------------------------------------ */

    const optionRows = () => Array.from(optionList.querySelectorAll("[data-option-row]"));
    const letterFor = (index) => String.fromCharCode(65 + index);

    const reindexOptions = () => {
        const rows = optionRows();

        rows.forEach((row, index) => {
            const letter = letterFor(index);
            const radio = row.querySelector('input[type="radio"]');
            const text = row.querySelector('[data-field="text"]');

            row.setAttribute("data-index", String(index));
            radio.value = String(index);
            radio.setAttribute("aria-label", `Option ${letter} is the correct answer`);
            row.querySelector(".pa-option-letter").textContent = letter;
            row.querySelector('[data-field="id"]').name = `options[${index}][id]`;
            text.name = `options[${index}][text]`;
            text.placeholder = `Option ${letter}`;
            text.setAttribute("aria-label", `Option ${letter}`);
            row.classList.toggle("is-correct", radio.checked);
            row.querySelector(".pa-option-remove").disabled = rows.length <= MIN_OPTIONS;
        });

        document.getElementById("addOptionBtn").disabled = rows.length >= MAX_OPTIONS;
    };

    const addOption = (option = {}, focus = false) => {
        if (optionRows().length >= MAX_OPTIONS) {
            return;
        }

        const row = document.createElement("div");

        row.className = "pa-option-row";
        row.setAttribute("data-option-row", "");
        row.innerHTML = `<label class="pa-option-correct" title="Mark as the correct answer"><input type="radio" name="correct" value=""><span class="pa-option-letter" aria-hidden="true"></span><span class="pa-option-correct__tick" aria-hidden="true">${iconSvg("check")}</span></label>`
            + `<input type="hidden" data-field="id" value="">`
            + `<div class="pa-option-row__input"><input type="text" data-field="text" maxlength="500" autocomplete="off" class="ss-modal-input pa-option-text"><div class="acc__input-error pa-option-error"></div></div>`
            + `<button type="button" class="pa-option-remove" aria-label="Remove option" title="Remove option">${iconSvg("x")}</button>`;

        /* Values are set as properties, never through markup. */
        row.querySelector('[data-field="id"]').value = option.id ? String(option.id) : "";
        row.querySelector('[data-field="text"]').value = option.text || "";
        row.querySelector('input[type="radio"]').checked = option.is_correct == 1;

        optionList.appendChild(row);
        reindexOptions();
        refreshIcons();

        if (focus) {
            row.querySelector('[data-field="text"]').focus();
        }
    };

    const clearErrors = () => {
        $form.find(".acc__input-error").text("");
        $form.find(".border-danger").removeClass("border-danger");
        optionList.classList.remove("has-error");
        levelChoice?.classList.remove("has-error");
    };

    /** Tick the level card for `level` (a known level), or clear the choice. */
    const setLevelChoice = (level) => {
        $form.find('input[name="level"]').each(function () {
            this.checked = isLevel(level) && this.value === level;
        });
    };

    /* Laravel keys like options.2.text point at the third row on screen. */
    const showErrors = (errors) => {
        clearErrors();

        Object.entries(errors || {}).forEach(([key, value]) => {
            const message = Array.isArray(value) ? value[0] : value;
            const match = key.match(/^options\.(\d+)\.(text|id)$/);

            if (match) {
                const row = optionList.querySelector(`[data-option-row][data-index="${match[1]}"]`);

                if (row) {
                    row.querySelector(".pa-option-text").classList.add("border-danger");
                    row.querySelector(".pa-option-error").textContent = message;
                } else {
                    $form.find(".error-options").text(message);
                }

                return;
            }

            if (key === "options" || key.indexOf("options.") === 0 || key === "correct") {
                optionList.classList.add("has-error");
                $form.find(key === "correct" ? ".error-correct" : ".error-options").text(message);
                return;
            }

            if (key === "level") {
                levelChoice?.classList.add("has-error");
                $form.find(".error-level").text(message);
                return;
            }

            if (/^[a-z_]+$/.test(key)) {
                $form.find(`.${key}`).addClass("border-danger");
                $form.find(`.error-${key}`).text(message);
            }
        });

        const firstError = form.querySelector(".border-danger, .has-error");

        firstError?.scrollIntoView({ block: "center", behavior: "smooth" });
    };

    const syncToggleCopy = () => {
        $form.find(".pa-active-toggle").each(function () {
            const checked = $(this).find("input").is(":checked");

            $(this).find("[data-on]").each(function () {
                $(this).text(checked ? $(this).attr("data-on") : $(this).attr("data-off"));
            });
        });
    };

    const resetQuestionForm = () => {
        clearErrors();
        form.reset();
        $form.find("textarea").val("");
        $form.find('input[name="id"]').val("0");
        $form.find('input[name="is_active"]').prop("checked", false);
        setLevelChoice("");
        optionList.innerHTML = "";
        syncToggleCopy();
    };

    const openAdd = () => {
        resetQuestionForm();
        $("#questionModalTitle").text("Add question");
        /* Filtered to a level: a new question most likely belongs to it. Otherwise HR picks. */
        setLevelChoice(currentLevel);

        for (let i = 0; i < DEFAULT_OPTIONS; i++) {
            addOption();
        }

        questionModal.show();
        refreshIcons();
    };

    const openEdit = (questionId) => {
        axios({
            method: "get",
            url: route("policy.assessment.question.edit", questionId),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            if (response.status != 200) {
                return;
            }

            const dataset = response.data || {};

            resetQuestionForm();
            $("#questionModalTitle").text("Edit question");
            $form.find('textarea[name="question"]').val(dataset.question || "");
            $form.find('textarea[name="explanation"]').val(dataset.explanation || "");
            $form.find('textarea[name="source_excerpt"]').val(dataset.source_excerpt || "");
            $form.find('input[name="is_active"]').prop("checked", dataset.is_active == 1);
            $form.find('input[name="id"]').val(String(dataset.id));
            setLevelChoice(dataset.level);

            (dataset.options || []).slice(0, MAX_OPTIONS).forEach((option) => addOption(option));

            while (optionRows().length < MIN_OPTIONS) {
                addOption();
            }

            syncToggleCopy();
            questionModal.show();
            refreshIcons();
        }).catch((error) => {
            showWarning("Could not open", errorMessage(error, "This question could not be loaded."));
        });
    };

    /* ------------------------------------------------------------------
     * Wiring
     * ------------------------------------------------------------------ */

    buildTable();
    refreshIcons();

    $("#tabulatorFilterForm").on("keypress.pa-questions", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#status, #level").on("change.pa-questions", buildTable);
    $("#tabulator-html-filter-go").on("click.pa-questions", buildTable);

    $("#tabulator-html-filter-reset").on("click.pa-questions", function () {
        $("#query").val("");
        $("#status").val("all");
        $("#level").val("");
        buildTable();
    });

    /* A level card filters the list to that level; clicking the selected one shows every level. */
    $("#levelReadiness").on("click.pa-questions", "[data-level]", function () {
        const level = $(this).attr("data-level");

        if (!isLevel(level)) {
            return;
        }

        $("#level").val(currentLevel === level ? "" : level);
        buildTable();
    });

    const exportName = () => `questions-${String(policyTitle).toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "").slice(0, 60) || "policy"}`;

    $("#tabulator-export-csv").on("click.pa-questions", function () {
        tableContent?.download("csv", `${exportName()}.csv`);
    });

    $("#tabulator-export-xlsx").on("click.pa-questions", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", `${exportName()}.xlsx`, {
            sheetName: "Questions",
        });
    });

    $("#expandWhyBtn").on("click.pa-questions", function () {
        whyOpen = !whyOpen;
        $(this).attr("aria-pressed", whyOpen ? "true" : "false");
        $(this).find("span").text(whyOpen ? "Hide all reasons" : "Show all reasons");
        if (!whyOpen) {
            openWhy.clear();
        }

        tableNode.querySelectorAll("details.pa-why").forEach((details) => {
            details.open = whyOpen;
        });
    });

    /* toggle does not bubble, so listen in the capture phase. */
    tableNode.addEventListener("toggle", (event) => {
        const details = event.target;

        if (!(details instanceof HTMLDetailsElement) || !details.classList.contains("pa-why")) {
            return;
        }

        const id = details.getAttribute("data-id");

        if (details.open) {
            openWhy.add(id);
        } else {
            openWhy.delete(id);
        }
    }, true);

    $("#addQuestionBtn").on("click.pa-questions", openAdd);

    $("#addOptionBtn").on("click.pa-questions", function () {
        addOption({}, true);
    });

    $(optionList).on("click.pa-questions", ".pa-option-remove", function () {
        if (optionRows().length <= MIN_OPTIONS) {
            return;
        }

        $(this).closest("[data-option-row]").remove();
        clearErrors();
        reindexOptions();
    });

    $(optionList).on("change.pa-questions", 'input[type="radio"]', function () {
        reindexOptions();
        $form.find(".error-correct").text("");
        optionList.classList.remove("has-error");
    });

    $(optionList).on("input.pa-questions", ".pa-option-text", function () {
        $(this).removeClass("border-danger");
        $(this).closest("[data-option-row]").find(".pa-option-error").text("");
    });

    $form.on("change.pa-questions", ".pa-active-toggle input", syncToggleCopy);

    $form.on("change.pa-questions", 'input[name="level"]', function () {
        levelChoice?.classList.remove("has-error");
        $form.find(".error-level").text("");
    });

    document.getElementById("questionModal")?.addEventListener("hidden.tw.modal", resetQuestionForm);

    document.getElementById("confirmModal")?.addEventListener("hidden.tw.modal", function () {
        const $agree = $("#confirmModal .agreeWith");

        $agree.attr("data-id", "0");
        $agree.attr("data-action", "none");
        $agree.attr("data-level", "");
        $agree.addClass("ss-btn--danger").removeClass("ss-btn--success");
        $("#confirmModal button").removeAttr("disabled");
    });

    $form.on("submit.pa-questions", function (event) {
        event.preventDefault();

        const questionId = Number($form.find('input[name="id"]').val() || 0);
        const isEdit = questionId > 0;

        clearErrors();
        setBusy("#saveQuestion", true);

        axios({
            method: "post",
            url: isEdit ? route("policy.assessment.question.update", questionId) : route("policy.assessment.question.store", policyId),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#saveQuestion", false);

            if (response.status != 200) {
                return;
            }

            questionModal.hide();
            applyCounts(response.data.counts);

            if (isEdit) {
                updateRow(response.data.row);
                showSuccess("Saved", "The question has been updated.");
            } else {
                buildTable();
                showSuccess("Saved", "The question has been added.");
            }
        }).catch((error) => {
            setBusy("#saveQuestion", false);

            if (error.response?.status == 422) {
                showErrors(error.response.data.errors);
                return;
            }

            showWarning("Not saved", errorMessage(error, "Something went wrong. Please try again."));
        });
    });

    $("#policyQuestionTable").on("click.pa-questions", ".q_edit_btn", function () {
        openEdit($(this).attr("data-id"));
    });

    $("#policyQuestionTable").on("click.pa-questions", ".q_status_toggle", function () {
        const $button = $(this);
        const questionId = $button.attr("data-id");
        const turnOn = $button.attr("aria-checked") !== "true";

        $button.prop("disabled", true).addClass("is-busy");

        axios({
            method: "post",
            url: route("policy.assessment.question.status", questionId),
            data: { is_active: turnOn ? 1 : 0 },
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            if (response.status == 200) {
                applyCounts(response.data.counts);
                updateRow(response.data.row);
            }
        }).catch((error) => {
            $button.prop("disabled", false).removeClass("is-busy");

            if (error.response?.status == 422) {
                showWarning("Cannot switch on yet", errorMessage(error, "This question is not complete."));
                return;
            }

            showWarning("Status not changed", errorMessage(error, "Something went wrong. Please try again."));
        });
    });

    $("#policyQuestionTable").on("click.pa-questions", ".q_delete_btn", function () {
        showConfirm("Archive this question?", "It will no longer be drawn into tests. Past attempts keep their copy. You can restore it later.", "DELETE", $(this).attr("data-id"), false);
    });

    $("#policyQuestionTable").on("click.pa-questions", ".q_restore_btn", function () {
        showConfirm("Restore this question?", "It comes back with the status it had before it was archived.", "RESTORE", $(this).attr("data-id"), true);
    });

    /*
     * Activates the drafts of the level the list is showing (every level when
     * it is not filtered). The level is pinned on the confirm button, so what
     * is posted is exactly what the message promised.
     */
    $("#activateAllBtn").on("click.pa-questions", function () {
        const level = currentLevel;
        const label = levelLabel(level);
        const drafts = draftsInView();
        const title = label ? `Activate all ${label} drafts?` : "Activate all drafts?";
        const description = label
            ? `This switches on the ${plural(drafts, `${label} draft`, `${label} drafts`)} for this policy that are complete (at least two options and one correct answer). Drafts at other levels are not touched. Please make sure you have read them. Incomplete drafts stay as drafts.`
            : `This switches on the ${plural(drafts, "draft", "drafts")} for this policy at every level (Beginner, Intermediate and Expert) that are complete (at least two options and one correct answer). Please make sure you have read them. Incomplete drafts stay as drafts.`;

        showConfirm(title, description, "ACTIVATEALL", policyId, true);
        $("#confirmModal .agreeWith").attr("data-level", level);
    });

    $("#confirmModal .agreeWith").on("click.pa-questions", function () {
        const recordID = $(this).attr("data-id");
        const action = $(this).attr("data-action");
        let request = null;

        if (action == "DELETE") {
            request = { method: "delete", url: route("policy.assessment.question.destroy", recordID) };
        } else if (action == "RESTORE") {
            request = { method: "post", url: route("policy.assessment.question.restore", recordID) };
        } else if (action == "ACTIVATEALL") {
            const level = $(this).attr("data-level");

            request = { method: "post", url: route("policy.assessment.question.activate.all", policyId), data: { level: isLevel(level) ? level : "" } };
        }

        if (!request) {
            return;
        }

        $("#confirmModal button").attr("disabled", "disabled");

        axios({
            ...request,
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            $("#confirmModal button").removeAttr("disabled");

            if (response.status != 200) {
                return;
            }

            confirmModal.hide();
            applyCounts(response.data.counts);
            buildTable();

            if (action == "ACTIVATEALL") {
                const activated = Number(response.data.activated || 0);

                showSuccess(activated > 0 ? `${plural(activated, "question", "questions")} activated` : "Nothing activated", response.data.message || "");
            } else {
                showSuccess("Done", action == "DELETE" ? "The question has been archived." : "The question has been restored.");
            }
        }).catch((error) => {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();
            showWarning("Not done", errorMessage(error, "Something went wrong. Please try again."));
        });
    });
})();
