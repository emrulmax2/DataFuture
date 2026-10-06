/*
 * Policy Assessments: the Exam level part of the two Assign modals (Assignments
 * page and the employee profile tab). Imported by their page scripts; not a
 * Vite input.
 *
 * The modal posts `level` from three radio cards (partials/results-level-choice.blade.php),
 * each showing its exam's question pattern. An exam is never drawn from one level
 * alone: it needs its share of Beginner, Intermediate and Expert questions, so a
 * policy is ready for an exam only when the bank can fill every share. The server
 * works that out (PolicyDocument::examReady()) and ships it with each policy
 * <option> (partials/results-policy-options.blade.php: data-exam, JSON per exam
 * level { ready, short, why }; TomSelect keeps it as `exam`). Nothing about the
 * pattern is worked out or hard-coded here.
 *
 * When the level changes the picker re-labels the policies whose exam at that
 * level is not ready ("(exam not ready: needs 1 more Expert question)") and a
 * note under the picker covers the chosen policies that are not ready. The Role
 * picker (assign-role-picker.js) is told the level through `onLevel`, so each
 * role can say how many of its policies are not ready. Assigning them is still
 * allowed: the member of staff just sees "Test not available yet" until HR
 * activates enough questions.
 *
 * All text goes in through textContent / TomSelect's escaping.
 */
import { BADGE_NAMES, LEVEL_LABELS, isLevel, levelPill } from "./levels";

const DEFAULT_LEVEL = "beginner";

/* Parsed data-exam per option value: the attribute is JSON text and never changes. */
const examCache = new Map();

/** { beginner: { ready, short, why }, ... } for one TomSelect option's data ({} when missing). */
const examOf = (data) => {
    const key = String(data?.value ?? "");

    if (examCache.has(key)) {
        return examCache.get(key);
    }

    let exam = {};

    try {
        const parsed = typeof data?.exam === "string" ? JSON.parse(data.exam) : data?.exam;

        if (parsed && typeof parsed === "object" && !Array.isArray(parsed)) {
            exam = parsed;
        }
    } catch (e) {
        exam = {};
    }

    examCache.set(key, exam);

    return exam;
};

/** Whether the policy's exam at `level` can be drawn in full. Unknown counts as ready: the server decides. */
const readyAt = (data, level) => {
    const tier = examOf(data)[level];

    return !tier || tier.ready !== false;
};

/** Why the exam at `level` is not ready, e.g. "needs 1 more Expert question" ("" when ready or unknown). */
const whyAt = (data, level) => {
    const tier = examOf(data)[level];

    return tier && tier.ready === false && typeof tier.why === "string" ? tier.why : "";
};

/** "a Beginner exam", "an Intermediate exam", "an Expert exam". */
export const examName = (level) => {
    const label = LEVEL_LABELS[isLevel(level) ? level : DEFAULT_LEVEL];

    return `${/^[AEIOU]/.test(label) ? "an" : "a"} ${label} exam`;
};

/** The policy's own title, without any hint added to its label. */
const baseTitle = (data) => String(data.policyTitle ?? data.text ?? "");

/** "Title", or "Title (exam not ready: needs 1 more Expert question)". Same text as the Blade partial writes. */
const optionLabel = (data, level) => {
    const title = baseTitle(data);

    if (readyAt(data, level)) {
        return title;
    }

    const why = whyAt(data, level);

    return `${title} (exam not ready${why ? `: ${why}` : ""})`;
};

/**
 * @param {HTMLFormElement} form      the Assign form (holds the level radios)
 * @param {TomSelect}       tomSelect the policies picker
 * @param {(level: string) => void} [onLevel] called with the level whenever the picker is refreshed
 * @returns {{ level: () => string, refresh: () => void, reset: () => void }}
 */
export const initLevelPicker = (form, tomSelect, onLevel = null) => {
    if (!form || !tomSelect) {
        return { level: () => DEFAULT_LEVEL, refresh: () => {}, reset: () => {} };
    }

    const note = form.querySelector("[data-level-note]");

    const level = () => {
        const checked = form.querySelector('input[name="level"]:checked');

        return checked && isLevel(checked.value) ? checked.value : DEFAULT_LEVEL;
    };

    /* Re-label every option for the level (updateOption also redraws a chosen chip). */
    const relabel = (current) => {
        Object.keys(tomSelect.options).forEach((key) => {
            const data = tomSelect.options[key];
            const text = optionLabel(data, current);

            if (data.text !== text) {
                tomSelect.updateOption(key, { ...data, text });
            }
        });
    };

    /* What is in the picker is what gets assigned, whether a role put it there or HR did. */
    const refreshNote = (current) => {
        if (!note) {
            return;
        }

        const missing = tomSelect.items
            .map((key) => tomSelect.options[key])
            .filter((data) => data && !readyAt(data, current));
        const text = note.querySelector("span") || note;

        if (!missing.length) {
            text.textContent = "";
            note.hidden = true;
            return;
        }

        if (missing.length === 1) {
            const why = whyAt(missing[0], current);

            text.textContent = `${baseTitle(missing[0])} is not ready for ${examName(current)} yet${why ? ` (it ${why})` : ""}, so that test cannot be started until more questions are activated.`;
        } else {
            text.textContent = `${missing.length} of the chosen policies are not ready for ${examName(current)} yet, so those tests cannot be started until more questions are activated. Every exam needs enough active questions at all three levels.`;
        }

        note.hidden = false;
    };

    const refresh = () => {
        const current = level();

        relabel(current);

        if (typeof onLevel === "function") {
            onLevel(current);
        }

        refreshNote(current);
    };

    const reset = () => {
        const beginner = form.querySelector(`input[name="level"][value="${DEFAULT_LEVEL}"]`);

        if (beginner) {
            beginner.checked = true;
        }

        refresh();
    };

    form.addEventListener("change", (event) => {
        const target = event.target;

        if (target && target.name === "level") {
            refresh();
        }
    });
    tomSelect.on("change", () => refreshNote(level()));

    refresh();

    return { level, refresh, reset };
};

/**
 * First line of the "Policies assigned" message: the level the exams were set at
 * and the badge a pass earns. `level` comes from the server's reply.
 */
export const assignedLevelHtml = (level) => {
    const key = isLevel(level) ? level : DEFAULT_LEVEL;

    return `<p class="pa-result-lead">${levelPill(key)}<span>exams &middot; meeting the target earns the ${BADGE_NAMES[key]} badge</span></p>`;
};
