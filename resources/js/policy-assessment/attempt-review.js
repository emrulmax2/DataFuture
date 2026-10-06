/*
 * Policy Assessments: how HR sees attempts — the attempts table and the attempt
 * review. Imported by the three HR page scripts (Assignments, Overview & Results,
 * employee profile tab), so an attempt reads the same wherever it is opened; not
 * a Vite input.
 *
 * Data comes from policy.assessment.results.employee (attempt rows) and
 * policy.assessment.attempt.show (the review). Every attempt row carries, ready
 * to show: time_limit_label ("15 min" / "Untimed"), time_taken_label ("4:05",
 * "1h 02m"), timed_out and answered_label ("6 of 10 answered", only when a
 * submitted attempt left questions unanswered). The review also carries the
 * questions the exam drew by level (mix, mix_label) and each question's own level.
 * Nothing about the exam pattern is worked out or hard-coded here.
 *
 * Tab activity: every attempt row also says how often the test tab or window was
 * left (tab_exits, away_label, and tab_exits_label "3 tab exits · 1m 12s", empty
 * when it never was), and the review lists each exit (tab_activity: at, where,
 * type_label, seconds_label, open), oldest first.
 *
 * HR-only: the review shows the correct answers. Everything HR or staff typed is
 * escaped before it goes into HTML.
 */
import { LEVELS, LEVEL_LABELS, escapeHtml, isLevel, levelLabel, levelPill } from "./levels";

/** 80 -> "80%", 66.67 -> "66.67%", null -> "—". */
export const formatScore = (value) => {
    if (value === null || value === undefined || value === "") {
        return "—";
    }

    const number = Number(value);

    return `${Number.isInteger(number) ? number : number.toFixed(2).replace(/\.?0+$/, "")}%`;
};

const isSubmitted = (attempt) => attempt.status == "submitted";

/** "15 min limit" / "Untimed". */
const limitText = (attempt) => {
    const label = attempt.time_limit_label || "Untimed";

    return attempt.time_limit_minutes ? `${label} limit` : label;
};

const timedOutTag = (attempt) => (attempt.timed_out ? '<em class="pa-tag pa-tag--amber">Timed out</em>' : "");

const answeredTag = (attempt) => (attempt.answered_label ? `<em class="pa-tag pa-tag--muted">${escapeHtml(attempt.answered_label)}</em>` : "");

const exitCount = (attempt) => {
    const exits = Number(attempt.tab_exits || 0);

    return exits > 0 ? exits : 0;
};

const exitWord = (count) => (count == 1 ? "tab exit" : "tab exits");

/* "3 tab exits · 1m 12s"; nothing when the test tab was never left. */
const exitsTag = (attempt) => {
    const exits = exitCount(attempt);

    if (!exits) {
        return "";
    }

    const label = attempt.tab_exits_label || `${exits} ${exitWord(exits)}${attempt.away_label ? ` · ${attempt.away_label}` : ""}`;

    return `<em class="pa-tag pa-tag--exits" title="Times the test tab or window lost focus, and the total time away">${escapeHtml(label)}</em>`;
};

const TAB_ACTIVITY_NOTE = "An exit means the test tab or window lost focus. It is a record to consider alongside the result, not proof of anything by itself.";

/*
 * "Tab activity" in the review: either the line saying the test was never left, or
 * the count, the total time away and each exit (time, where, what happened, how
 * long), oldest first. An exit that is still open has no duration yet.
 */
const tabActivityHtml = (payload) => {
    const attempt = payload.attempt || {};
    const events = Array.isArray(payload.tab_activity) ? payload.tab_activity : [];
    const exits = exitCount(attempt);
    const title = '<h3 class="pa-tab-activity__title">Tab activity</h3>';

    if (!exits && !events.length) {
        const clear = isSubmitted(attempt) ? "The test tab stayed in focus for the whole attempt." : "The test tab has stayed in focus so far.";

        return `<section class="pa-tab-activity is-clear" aria-label="Tab activity"><div class="pa-tab-activity__head">${title}<p class="pa-tab-activity__clear"><i data-lucide="check-circle"></i><span>${clear}</span></p></div></section>`;
    }

    const awayNow = events.some((event) => event.open);
    const totals = [];

    if (exits) {
        totals.push(`<span class="pa-tab-activity__total"><strong>${escapeHtml(exits)} ${exitWord(exits)}</strong>${attempt.away_label ? ` <span>${escapeHtml(attempt.away_label)} away in total</span>` : ""}</span>`);
    }

    if (awayNow) {
        totals.push('<span class="pa-tab-activity__total pa-tab-activity__total--open"><strong>Away from the test now</strong></span>');
    }

    const rows = events.map((event) => {
        const away = event.open ? '<em class="pa-tab-activity__open">Still away</em>' : escapeHtml(event.seconds_label || "—");

        return `<li class="pa-tab-activity__row${event.open ? " is-open" : ""}">
            <span class="pa-tab-activity__at">${escapeHtml(event.at || "—")}</span>
            <span class="pa-tab-activity__where">${escapeHtml(event.where || "—")}</span>
            <span class="pa-tab-activity__what">${escapeHtml(event.type_label || "Left the test")}</span>
            <span class="pa-tab-activity__away">${away}</span>
        </li>`;
    }).join("");
    const list = rows
        ? `<div class="pa-tab-activity__table"><div class="pa-tab-activity__cols" aria-hidden="true"><span>Time</span><span>Where</span><span>What happened</span><span>Away for</span></div><ol class="pa-tab-activity__list">${rows}</ol></div>`
        : "";

    return `<section class="pa-tab-activity has-exits" aria-label="Tab activity">
        <div class="pa-tab-activity__head">${title}<div class="pa-tab-activity__totals">${totals.join("")}</div></div>
        ${list}
        <p class="pa-tab-activity__note">${TAB_ACTIVITY_NOTE}</p>
    </section>`;
};

/**
 * The attempts of one assignment, newest first as the server sends them.
 * options.emptyClass  class of the "No attempts yet." note
 * options.iconClass   extra classes for the Review button's icon (the employee tab sizes icons with utilities)
 */
export const attemptsTableHtml = (attempts, options = {}) => {
    const emptyClass = options.emptyClass || "pa-empty-note";
    const iconClass = options.iconClass ? ` class="${escapeHtml(options.iconClass)}"` : "";

    if (!attempts || !attempts.length) {
        return `<p class="${escapeHtml(emptyClass)}">No attempts yet.</p>`;
    }

    const rows = attempts.map((attempt) => {
        const submitted = isSubmitted(attempt);
        const pill = submitted
            ? `<span class="pa-state-pill pa-state-pill--${attempt.passed ? "passed" : "failed"}"><span></span>${attempt.passed ? "Target met" : "Target not met"}</span>`
            : '<span class="pa-state-pill pa-state-pill--in_progress"><span></span>In progress</span>';
        const score = submitted ? `${escapeHtml(formatScore(attempt.score))} <small>(${escapeHtml(attempt.correct_count)} of ${escapeHtml(attempt.total_questions)})</small>` : "—";
        const time = `${submitted && attempt.time_taken_label ? escapeHtml(attempt.time_taken_label) : "—"} <small>${escapeHtml(limitText(attempt))}</small>`;

        return `<tr>
            <td>${escapeHtml(attempt.attempt_no)}</td>
            <td>${escapeHtml(attempt.started_at || "—")}</td>
            <td>${escapeHtml(attempt.submitted_at || "—")}</td>
            <td class="pa-attempts-table__time">${time}</td>
            <td>${score}</td>
            <td class="pa-attempts-table__result-cell"><span class="pa-attempts-table__result">${pill}${timedOutTag(attempt)}${answeredTag(attempt)}${exitsTag(attempt)}</span></td>
            <td class="pa-attempts-table__action"><button type="button" class="pa-link-btn review_attempt_btn" data-id="${escapeHtml(attempt.id)}"><i data-lucide="file-search"${iconClass}></i>Review</button></td>
        </tr>`;
    }).join("");

    return `<div class="pa-attempts-table-wrap"><table class="pa-attempts-table">
        <thead><tr><th>#</th><th>Started</th><th>Submitted</th><th>Time taken</th><th>Score</th><th>Result</th><th></th></tr></thead>
        <tbody>${rows}</tbody>
    </table></div>`;
};

/* "Beginner exam: 7 Beginner · 2 Intermediate · 1 Expert", the counts as level pills. */
const mixHtml = (payload) => {
    const mix = payload.mix && typeof payload.mix === "object" ? payload.mix : null;
    const examLabel = payload.level_label || levelLabel(payload.level);

    if (!mix || !examLabel) {
        return payload.mix_label ? `<p class="pa-review-mix">${escapeHtml(payload.mix_label)}</p>` : "";
    }

    const parts = LEVELS.map((level) => levelPill(level, `${Number(mix[level] || 0)} ${LEVEL_LABELS[level]}`)).join("");
    const aria = payload.mix_label ? ` aria-label="${escapeHtml(payload.mix_label)}"` : "";

    return `<p class="pa-review-mix"${aria}><strong>${escapeHtml(examLabel)} exam:</strong><span class="pa-review-mix__parts">${parts}</span><small>questions drawn</small></p>`;
};

/** The body of the attempt review modal, from policy.assessment.attempt.show. */
export const attemptReviewHtml = (payload) => {
    const attempt = payload.attempt || {};
    const submitted = isSubmitted(attempt);
    const chips = [levelPill(payload.level || attempt.level)];

    if (submitted) {
        chips.push(`<span class="pa-review-chip pa-review-chip--${attempt.passed ? "green" : "red"}">${attempt.passed ? "Target met" : "Target not met"}</span>`);

        if (attempt.timed_out) {
            chips.push('<span class="pa-review-chip pa-review-chip--amber">Timed out</span>');
        }

        chips.push(`<span class="pa-review-chip"><strong>${escapeHtml(formatScore(attempt.score))}</strong> score</span>`);
        chips.push(`<span class="pa-review-chip"><strong>${escapeHtml(attempt.correct_count)} of ${escapeHtml(attempt.total_questions)}</strong> correct</span>`);

        if (attempt.answered_label) {
            chips.push(`<span class="pa-review-chip pa-review-chip--amber"><strong>${escapeHtml(attempt.answered_label)}</strong></span>`);
        }
    } else {
        chips.push('<span class="pa-review-chip pa-review-chip--blue">In progress</span>');
    }

    chips.push(`<span class="pa-review-chip">Target score <strong>${escapeHtml(attempt.pass_mark)}%</strong></span>`);
    chips.push(`<span class="pa-review-chip">Time limit <strong>${escapeHtml(attempt.time_limit_label || "Untimed")}</strong></span>`);

    if (submitted && attempt.time_taken_label) {
        chips.push(`<span class="pa-review-chip">Time taken <strong>${escapeHtml(attempt.time_taken_label)}</strong></span>`);
    }

    chips.push(`<span class="pa-review-chip">Started <strong>${escapeHtml(attempt.started_at || "—")}</strong></span>`);

    if (attempt.submitted_at) {
        chips.push(`<span class="pa-review-chip">Submitted <strong>${escapeHtml(attempt.submitted_at)}</strong></span>`);
    }

    const letters = "ABCDEFGHIJ";
    const questions = (payload.answers || []).map((answer) => {
        /* Once submitted, a question left unanswered is marked wrong. */
        const state = answer.answered ? (answer.is_correct ? "correct" : "wrong") : "unanswered";
        const missed = state == "unanswered" && submitted;
        const mark = state == "correct"
            ? '<span class="pa-review-q__mark is-correct" title="Correct"><i data-lucide="check"></i></span>'
            : state == "wrong"
                ? '<span class="pa-review-q__mark is-wrong" title="Wrong"><i data-lucide="x"></i></span>'
                : `<span class="pa-review-q__mark is-unanswered${missed ? " is-missed" : ""}" title="${missed ? "Not answered, marked wrong" : "Not answered"}"><i data-lucide="minus"></i></span>`;
        const options = (answer.options || []).map((option, index) => {
            const classes = ["pa-review-option"];
            const tags = [];

            if (option.correct) {
                classes.push("is-correct");
                tags.push('<em class="pa-tag pa-tag--green">Correct answer</em>');
            }

            if (option.selected) {
                classes.push("is-selected");
                tags.push(`<em class="pa-tag ${option.correct ? "pa-tag--green" : "pa-tag--red"}">Their answer</em>`);
            }

            return `<li class="${classes.join(" ")}"><span class="pa-review-option__letter">${letters.charAt(index) || index + 1}</span><span class="pa-review-option__text">${escapeHtml(option.text)}</span>${tags.length ? `<span class="pa-review-option__tags">${tags.join("")}</span>` : ""}</li>`;
        }).join("");
        const meta = [];

        if (isLevel(answer.question_level)) {
            meta.push(levelPill(answer.question_level));
        }

        if (missed) {
            meta.push('<span class="pa-review-q__missed"><i data-lucide="alert-circle"></i>Not answered <small>marked wrong</small></span>');
        }

        return `<li class="pa-review-q is-${state}${missed ? " is-missed" : ""}">
            <div class="pa-review-q__head"><span class="pa-review-q__num">${escapeHtml(answer.n)}</span><p>${escapeHtml(answer.question_text)}</p>${mark}</div>
            ${meta.length ? `<div class="pa-review-q__meta">${meta.join("")}</div>` : ""}
            <ul class="pa-review-options">${options}</ul>
        </li>`;
    }).join("");

    const note = submitted ? "" : '<p class="pa-empty-note">This attempt has not been submitted yet, so no answers are recorded.</p>';

    return `${mixHtml(payload)}<div class="pa-review-chips">${chips.join("")}</div>${tabActivityHtml(payload)}${note}<ol class="pa-review-list">${questions || '<li class="pa-empty-note">No questions recorded for this attempt.</li>'}</ol>`;
};
