("use strict");

/*
 * My HR -> Policy Assessments (staff).
 *
 * One entry for all four pages; each block runs only when its root is present.
 *   #myhrPolicyIndex  the "My badges" shelf, then the assigned tests grouped
 *                     by category (one card per policy and level).
 *   #pexamBriefing    step 1 of a test: the rules and the read declaration.
 *                     A plain form post; nothing is drawn or timed until it
 *                     is sent.
 *   #pexamTest        steps 2 and 3: the questions, one per screen, with a
 *                     navigator, flags and keyboard shortcuts, then the
 *                     review list and the submit confirmation. A timed test
 *                     counts down in the header and sends itself in at zero.
 *                     Leaving the tab or window is logged and said so.
 *   #pexamResult      step 4: drawn by the server; the script only tidies up.
 *
 * Every question is rendered by the server. Nothing here knows which answer
 * is right: the reply to a submission is the score and pass/fail, and the
 * page then goes to the Result page.
 */

const escapeHtml = (value) =>
    String(value === null || value === undefined ? "" : value).replace(/[&<>"'`]/g, (ch) => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
        "`": "&#96;",
    })[ch]);

/* What an attempt keeps in this tab until it is marked: the letters picked,
 * the flags, and where the member of staff had got to. */
const storeKey = (attemptId) => "pexam:" + attemptId;

/* ------------------------------------------------------------------ list */
(function () {
    const root = document.getElementById("myhrPolicyIndex");
    if (!root) {
        return;
    }

    /* A thumbnail that will not load falls back to the plain cover under it. */
    root.querySelectorAll(".myhr-policy-cover img").forEach((img) => {
        const fallBack = () => {
            const cover = img.closest(".myhr-policy-cover");
            if (cover) {
                cover.classList.remove("has-image");
            }
            img.remove();
        };
        if (img.complete && img.naturalWidth === 0) {
            fallBack();
        } else {
            img.addEventListener("error", fallBack, { once: true });
        }
    });

    /* The badge shelf shows the first few badges; the rest fold away behind
     * "Show all". The server renders every badge, so without this script
     * they all stay on show. */
    const shelfToggle = root.querySelector("[data-shelf-toggle]");
    const shelfExtras = Array.from(root.querySelectorAll("[data-shelf-extra]"));
    if (shelfToggle && shelfExtras.length > 0) {
        const shelfLabel = shelfToggle.querySelector("[data-shelf-toggle-label]");
        const setShelf = (expanded) => {
            shelfExtras.forEach((item) => {
                item.hidden = !expanded;
            });
            shelfToggle.setAttribute("aria-expanded", expanded ? "true" : "false");
            shelfToggle.classList.toggle("is-expanded", expanded);
            if (shelfLabel) {
                shelfLabel.textContent = expanded ? shelfToggle.dataset.labelLess || "Show fewer badges" : shelfToggle.dataset.labelMore || "Show all badges";
            }
        };
        setShelf(false);
        shelfToggle.hidden = false;
        shelfToggle.addEventListener("click", () => {
            const expanded = shelfToggle.getAttribute("aria-expanded") !== "true";
            setShelf(expanded);
            if (expanded && shelfExtras[0]) {
                shelfExtras[0].scrollIntoView({ behavior: "smooth", block: "nearest" });
            }
        });
    }

    /* "Read policy" opens in a new tab; show it on the card straight away. */
    root.addEventListener("click", (event) => {
        const link = event.target.closest("[data-policy-read]");
        if (!link) {
            return;
        }
        const card = link.closest("[data-policy-card]");
        const label = card ? card.querySelector("[data-policy-opened] span") : null;
        if (label && /not opened/i.test(label.textContent)) {
            label.textContent = "Policy opened today";
        }
    });
})();

/* ------------------------------------------------------ step 1: briefing */
(function () {
    const root = document.getElementById("pexamBriefing");
    if (!root) {
        return;
    }

    const form = document.getElementById("pexamStartForm");
    const gate = document.getElementById("pexamReadGate");
    const startBtn = document.getElementById("pexamStartBtn");
    if (!form || !gate || !startBtn) {
        return;
    }

    const startLabel = startBtn.querySelector(".pexam-start__label");
    let starting = false;

    /* The server checks the declaration too. Start is disabled in the markup
     * and only this script enables it: a browser that cannot run the test
     * cannot start one. It is also kept from being sent twice. */
    const sync = () => {
        startBtn.disabled = starting || !gate.checked;
        startBtn.classList.toggle("is-busy", starting);
        startBtn.setAttribute("aria-busy", starting ? "true" : "false");
        if (startLabel) {
            startLabel.textContent = starting ? "Starting…" : "Start test";
        }
    };

    gate.addEventListener("change", sync);

    form.addEventListener("submit", (event) => {
        if (starting || !gate.checked) {
            event.preventDefault();
            if (!gate.checked) {
                gate.focus();
            }
            return;
        }
        starting = true;
        sync();
    });

    /* Coming back with the Back button: the page may be shown as it was left. */
    window.addEventListener("pageshow", () => {
        starting = false;
        sync();
    });

    sync();
})();

/* ------------------------------------- steps 2 and 3: questions, review */
(function () {
    const root = document.getElementById("pexamTest");
    if (!root) {
        return;
    }

    const byId = (id) => document.getElementById(id);
    const form = byId("pexamForm");
    const stepTest = byId("pexamStepTest");
    const stepReview = byId("pexamStepReview");
    const gridEl = byId("pexamGrid");
    const answeredEl = byId("pexamAnswered");
    const exitsEl = byId("pexamExits");
    const tallyEl = byId("pexamTally");
    const rowsEl = byId("pexamRows");
    const reviewTitle = byId("pexamReviewTitle");
    const actEl = byId("pexamAct");
    const submitBtn = byId("pexamSubmitBtn");
    const confirmEl = byId("pexamConfirm");
    const confirmText = byId("pexamConfirmText");
    const confirmBtn = byId("pexamConfirmBtn");
    const cancelBtn = byId("pexamCancelBtn");
    const errorEl = byId("pexamSubmitError");
    const errorText = byId("pexamSubmitErrorText");
    const noticeEl = byId("pexamNotice");
    const liveEl = byId("pexamLive");
    const barEl = byId("pexamBar");
    const stepsEl = byId("pexamSteps");
    const timerEl = byId("pexamTimer");
    const panels = Array.from(root.querySelectorAll("[data-question]"));
    const total = panels.length;
    if (!form || !stepTest || !stepReview || !gridEl || !rowsEl || !confirmEl || !confirmBtn || total === 0) {
        return;
    }

    const attemptId = root.dataset.attempt;
    const attemptText = root.dataset.attemptText || "this attempt";
    const submitUrl = root.dataset.submitUrl;
    const eventUrl = root.dataset.eventUrl;
    const resultUrl = root.dataset.resultUrl;
    const indexUrl = root.dataset.indexUrl;
    const timed = root.dataset.timed === "1" && !!timerEl;
    const csrf = () => {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute("content") || "" : "";
    };

    /* One entry per question, in the order drawn. */
    const questions = panels.map((panel, index) => {
        const inputs = Array.from(panel.querySelectorAll('input[type="radio"]'));
        return {
            index: index,
            number: index + 1,
            panel: panel,
            inputs: inputs,
            name: inputs.length > 0 ? inputs[0].name : "",
            heading: panel.querySelector(".pexam-qtext"),
            flagBtn: panel.querySelector('[data-act="flag"]'),
            flagLabel: panel.querySelector("[data-flag-label]"),
        };
    });

    /* step: "test" or "review". flags[i]: false, true (flagged by hand) or
     * "auto" (left unanswered; lifts itself when the question is answered). */
    const state = {
        step: "test",
        current: 0,
        flags: questions.map(() => false),
        confirm: false,
    };
    let submitting = false;
    let submitted = false;
    /* The clock has reached zero: the inputs are locked and the page sends in
     * whatever was answered. */
    let timeUp = false;
    let timeUpData = null;
    let stopTimer = () => {};
    /* What the confirm button does after a failed submit: "submit" again,
     * "reload" (signed out) or "leave" (the test is gone). */
    let retryMode = "submit";

    const chosen = (question) => question.inputs.find((input) => input.checked) || null;
    const isAnswered = (index) => chosen(questions[index]) !== null;
    const answeredCount = () => questions.filter((question) => chosen(question) !== null).length;
    const flaggedCount = () => state.flags.filter(Boolean).length;
    const plural = (n, one, many) => n + " " + (n === 1 ? one : many);
    const exitsLabel = (n) => plural(n, "tab exit", "tab exits");
    const awayLabel = (seconds) => {
        const s = Math.max(1, Math.round(seconds));
        return s < 60 ? s + "s" : Math.floor(s / 60) + "m " + String(s % 60).padStart(2, "0") + "s";
    };

    /* Announcements are cleared first, so saying the same thing twice is still heard. */
    const announce = (text) => {
        if (!liveEl) {
            return;
        }
        liveEl.textContent = "";
        window.setTimeout(() => {
            liveEl.textContent = text;
        }, 60);
    };

    /* ----------------------------------------------- kept across a reload */
    const save = () => {
        if (submitted) {
            return;
        }
        try {
            const picked = {};
            questions.forEach((question) => {
                const input = chosen(question);
                if (input) {
                    picked[question.name] = input.value;
                }
            });
            window.sessionStorage.setItem(storeKey(attemptId), JSON.stringify({ answers: picked, flags: state.flags, current: state.current, step: state.step }));
        } catch (e) {
            /* storage is unavailable: the answers stay on the page only */
        }
    };
    const restore = () => {
        let kept = null;
        try {
            kept = JSON.parse(window.sessionStorage.getItem(storeKey(attemptId)) || "null");
        } catch (e) {
            kept = null;
        }
        if (!kept || typeof kept !== "object") {
            return;
        }
        const picked = kept.answers && typeof kept.answers === "object" ? kept.answers : {};
        questions.forEach((question) => {
            if (!Object.prototype.hasOwnProperty.call(picked, question.name)) {
                return;
            }
            question.inputs.forEach((input) => {
                if (input.value === picked[question.name]) {
                    input.checked = true;
                }
            });
        });
        if (Array.isArray(kept.flags)) {
            state.flags = questions.map((question, index) => (kept.flags[index] === true || kept.flags[index] === "auto" ? kept.flags[index] : false));
        }
        const current = parseInt(kept.current, 10);
        if (Number.isFinite(current) && current >= 0 && current < total) {
            state.current = current;
        }
        if (kept.step === "review") {
            state.step = "review";
        }
    };
    const forget = () => {
        try {
            window.sessionStorage.removeItem(storeKey(attemptId));
        } catch (e) {
            /* nothing to clear */
        }
    };

    /* Closing the tab would lose the answers (they are kept for this tab
     * only), so the browser asks first. Not once the test is marked. */
    const onBeforeUnload = (event) => {
        if (submitted || answeredCount() === 0) {
            return undefined;
        }
        event.preventDefault();
        event.returnValue = "";
        return "";
    };
    window.addEventListener("beforeunload", onBeforeUnload);

    /* -------------------------------------------------------------- paint */
    const paintChrome = () => {
        const at = state.step === "review" ? 3 : 2;
        if (stepsEl) {
            const items = stepsEl.querySelectorAll("[data-step]");
            items.forEach((item) => {
                const number = parseInt(item.dataset.step, 10);
                item.classList.toggle("is-done", number < at);
                item.classList.toggle("is-now", number === at);
                item.classList.toggle("is-todo", number > at);
                if (number === at) {
                    item.setAttribute("aria-current", "step");
                } else {
                    item.removeAttribute("aria-current");
                }
                const dot = item.querySelector(".pexam-step__dot");
                if (dot) {
                    dot.textContent = number < at ? "✓" : String(number);
                }
                /* the same in words, for screen readers */
                const spoken = item.querySelector(".pexam-step__label .pexam-sr");
                if (spoken) {
                    spoken.textContent = "Step " + number + " of " + items.length + (number < at ? ", done" : "") + ": ";
                }
            });
        }
        const done = answeredCount();
        if (barEl) {
            barEl.style.width = (total > 0 ? (done / total) * 100 : 0).toFixed(2) + "%";
        }
        if (answeredEl) {
            answeredEl.textContent = done + "/" + total + " answered";
        }
    };

    const paintQuestion = (question) => {
        question.inputs.forEach((input) => {
            const label = input.closest(".pexam-opt");
            if (label) {
                label.classList.toggle("is-on", input.checked);
            }
        });
        const flagged = !!state.flags[question.index];
        if (question.flagBtn) {
            question.flagBtn.setAttribute("aria-pressed", flagged ? "true" : "false");
        }
        if (question.flagLabel) {
            question.flagLabel.textContent = flagged ? "Flagged" : "Flag for review";
        }
    };

    /* The navigator: one button per question, built once and then repainted. */
    const navButtons = [];
    const buildGrid = () => {
        gridEl.innerHTML = questions
            .map((question) => '<li><button type="button" class="pexam-nb" data-act="go" data-i="' + question.index + '">' + question.number + "</button></li>")
            .join("");
        gridEl.querySelectorAll("button").forEach((button) => navButtons.push(button));
    };
    const paintGrid = () => {
        navButtons.forEach((button, index) => {
            const answered = isAnswered(index);
            const flagged = !!state.flags[index];
            const current = state.step === "test" && index === state.current;
            button.className = "pexam-nb" + (answered ? " is-ans" : "") + (flagged ? " is-flg" : "") + (current ? " is-cur" : "");
            button.setAttribute("aria-label", "Question " + (index + 1) + (answered ? ", answered" : ", not answered") + (flagged ? ", flagged" : ""));
            if (current) {
                button.setAttribute("aria-current", "true");
            } else {
                button.removeAttribute("aria-current");
            }
        });
    };

    let exits = Math.max(0, parseInt(root.dataset.exits, 10) || 0);
    const paintExits = () => {
        if (!exitsEl) {
            return;
        }
        exitsEl.hidden = exits < 1;
        exitsEl.textContent = exitsLabel(exits) + " logged";
    };

    const paintReview = () => {
        const done = answeredCount();
        const missing = total - done;
        const flagged = flaggedCount();

        if (tallyEl) {
            tallyEl.innerHTML =
                '<span class="pexam-pill pexam-pill--ok">' + done + " answered</span>" +
                '<span class="pexam-pill' + (missing ? " pexam-pill--warn" : "") + '">' + missing + " not answered</span>" +
                '<span class="pexam-pill' + (flagged ? " pexam-pill--warn" : "") + '">' + flagged + " flagged</span>" +
                (exits > 0 ? '<span class="pexam-pill pexam-pill--bad">' + escapeHtml(exitsLabel(exits)) + " logged</span>" : "");
        }

        rowsEl.innerHTML = questions
            .map((question) => {
                const input = chosen(question);
                const flaggedHere = !!state.flags[question.index];
                let answer = '<span class="pexam-pill pexam-pill--warn">Not answered</span>';
                if (input) {
                    const label = input.closest(".pexam-opt");
                    const letter = label ? label.querySelector(".pexam-key") : null;
                    const text = label ? label.querySelector(".pexam-opt__text") : null;
                    answer = "<span>" + escapeHtml(letter ? letter.textContent.trim() : "") + ". " + escapeHtml(text ? text.textContent.trim() : "") + "</span>";
                }
                return (
                    '<li class="pexam-row">' +
                        '<span class="pexam-num' + (flaggedHere ? " is-flg" : input ? " is-ans" : "") + '" aria-hidden="true">' + question.number + "</span>" +
                        '<div class="pexam-row__body">' +
                            '<p class="pexam-row__q"><span class="pexam-sr">Question ' + question.number + ": </span>" + escapeHtml(question.heading ? question.heading.textContent.trim() : "") + "</p>" +
                            '<p class="pexam-row__a">' + answer + (flaggedHere ? '<span class="pexam-pill pexam-pill--warn">Flagged</span>' : "") + "</p>" +
                        "</div>" +
                        '<button type="button" class="pexam-btn pexam-btn--sm" data-act="go" data-i="' + question.index + '" aria-label="' + (input ? "Change the answer to" : "Answer") + " question " + question.number + '">' +
                            (input ? "Change" : "Answer") +
                        "</button>" +
                    "</li>"
                );
            })
            .join("");

        if (confirmText) {
            confirmText.textContent =
                "You cannot change your answers afterwards." +
                (missing ? " " + plural(missing, "unanswered question", "unanswered questions") + " will be marked wrong." : "");
        }
        const asking = state.confirm;
        confirmEl.hidden = !asking;
        if (actEl) {
            actEl.hidden = asking;
        }
    };

    const showError = (text) => {
        if (!errorEl || !errorText) {
            return;
        }
        if (text) {
            errorText.textContent = text;
        }
        errorEl.hidden = !text;
    };

    const paintBusy = () => {
        confirmBtn.disabled = submitting;
        confirmBtn.classList.toggle("is-busy", submitting);
        confirmBtn.setAttribute("aria-busy", submitting ? "true" : "false");
        if (cancelBtn) {
            cancelBtn.disabled = submitting;
            cancelBtn.hidden = retryMode === "leave";
        }
        const labels = { submit: errorEl && !errorEl.hidden ? "Try again" : "Submit now", reload: "Reload and sign in", leave: "Back to my policy assessments" };
        confirmBtn.textContent = submitting ? "Submitting…" : labels[retryMode];
    };

    /* Show the current step; `focus` is the element that should receive the
     * keyboard focus, `top` scrolls the page back to the top. */
    const render = (focus, top) => {
        const reviewing = state.step === "review";
        const stale = timeUp;
        stepTest.hidden = reviewing || stale;
        stepReview.hidden = !reviewing || stale;
        questions.forEach((question) => {
            question.panel.hidden = question.index !== state.current;
            paintQuestion(question);
        });
        paintChrome();
        paintGrid();
        paintExits();
        if (reviewing) {
            paintReview();
            paintBusy();
        }
        if (top) {
            window.scrollTo(0, 0);
        }
        if (focus && typeof focus.focus === "function") {
            focus.focus({ preventScroll: !!top });
        }
        save();
    };

    /* ------------------------------------------------------------ actions */
    const autoFlag = (index) => {
        if (!isAnswered(index) && !state.flags[index]) {
            state.flags[index] = "auto";
        }
    };
    const clearAutoFlag = (index) => {
        if (state.flags[index] === "auto") {
            state.flags[index] = false;
        }
    };

    const goTo = (index) => {
        const target = Math.max(0, Math.min(total - 1, index));
        if (state.step === "test" && target !== state.current) {
            autoFlag(state.current);
        }
        state.current = target;
        state.step = "test";
        state.confirm = false;
        showError("");
        render(questions[target].heading, false);
    };

    const toReview = () => {
        questions.forEach((question) => autoFlag(question.index));
        state.step = "review";
        state.confirm = false;
        retryMode = "submit";
        showError("");
        render(reviewTitle, true);
    };

    const next = () => {
        if (state.current === total - 1) {
            toReview();
        } else {
            goTo(state.current + 1);
        }
    };
    const previous = () => {
        if (state.current > 0) {
            goTo(state.current - 1);
        }
    };

    /* An answer was given to the question on screen (a click, a shortcut or
     * the arrow keys inside the options). */
    const answered = (question) => {
        clearAutoFlag(question.index);
        paintQuestion(question);
        paintChrome();
        paintGrid();
        save();
    };

    const pick = (position) => {
        const question = questions[state.current];
        const input = question.inputs[position];
        if (!input || input.disabled) {
            return;
        }
        input.checked = true;
        answered(question);
        const key = input.closest(".pexam-opt");
        const letter = key ? key.querySelector(".pexam-key") : null;
        announce("Answer " + (letter ? letter.textContent.trim() : position + 1) + " selected.");
    };

    const toggleFlag = (keepFocus) => {
        const question = questions[state.current];
        state.flags[question.index] = !state.flags[question.index];
        paintQuestion(question);
        paintGrid();
        save();
        announce(state.flags[question.index] ? "Question " + question.number + " flagged for review." : "Flag removed from question " + question.number + ".");
        if (keepFocus && question.flagBtn) {
            question.flagBtn.focus();
        }
    };

    /* ----------------------------------------------------------- sending */
    const collect = () => {
        const data = new FormData();
        data.append("_token", csrf());
        questions.forEach((question) => {
            const input = chosen(question);
            if (input) {
                data.append(question.name, input.value);
            }
        });
        return data;
    };

    /* Resolves with { kind, data, message }: "ok", or why it was not marked.
     * A signed-out session is redirected to the sign-in page, which the
     * browser follows and hands back as a page rather than as a result. */
    const post = (url, data) =>
        window
            .fetch(url, {
                method: "POST",
                body: data,
                credentials: "same-origin",
                headers: { "X-CSRF-TOKEN": csrf(), "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
            })
            .then((response) =>
                response.text().then((text) => {
                    let body = null;
                    try {
                        body = JSON.parse(text);
                    } catch (e) {
                        body = null;
                    }
                    const isObject = body !== null && typeof body === "object";
                    if (response.status === 200 && isObject && typeof body.passed === "boolean") {
                        return { kind: "ok", data: body };
                    }
                    if (response.status === 419 || response.status === 401 || (response.status === 200 && /\/login/i.test(response.url || ""))) {
                        return { kind: "signedout" };
                    }
                    if (response.status === 422) {
                        let message = isObject && body.message ? String(body.message) : "";
                        if (isObject && body.errors && typeof body.errors === "object") {
                            const first = Object.values(body.errors)[0];
                            message = (Array.isArray(first) ? first[0] : first) || message;
                        }
                        return { kind: "invalid", message: message };
                    }
                    if (response.status === 404) {
                        return { kind: "gone" };
                    }
                    return { kind: "server" };
                })
            )
            .catch(() => ({ kind: "network" }));

    /* The test is marked: nothing is kept in this tab any more, and the
     * Result page takes the place of this one in the history. */
    const finish = (data) => {
        submitted = true;
        stopTimer();
        forget();
        window.removeEventListener("beforeunload", onBeforeUnload);
        window.location.replace(data && typeof data.result_url === "string" && data.result_url !== "" ? data.result_url : resultUrl);
    };

    const leavePage = (url) => {
        save();
        window.removeEventListener("beforeunload", onBeforeUnload);
        if (url) {
            window.location.href = url;
        } else {
            window.location.reload();
        }
    };

    const FAILURES = {
        network: "We could not reach the server, so your test has not been submitted. Check your connection, then try again. Your answers are still here.",
        signedout: "You have been signed out, so your test has not been submitted. Your answers are kept in this tab: reload the page, sign in, and open this test again to carry on.",
        gone: "This test is no longer available, so it could not be submitted.",
        server: "Something went wrong and your test has not been submitted. Please try again in a moment. Your answers are still here.",
        invalid: "Your test could not be submitted. Please check your answers and try again.",
    };

    const submitNow = () => {
        if (submitting || submitted || timeUp) {
            return;
        }
        if (retryMode === "reload") {
            leavePage("");
            return;
        }
        if (retryMode === "leave") {
            leavePage(indexUrl);
            return;
        }

        const data = collect();
        if (answeredCount() < total) {
            data.append("confirm_incomplete", "1");
        }
        submitting = true;
        showError("");
        paintBusy();
        post(submitUrl, data).then((outcome) => {
            submitting = false;
            if (outcome.kind === "ok") {
                finish(outcome.data);
                return;
            }
            /* The clock ran out while this was on its way. */
            if (timeUp) {
                sendTimeUp(1);
                return;
            }
            retryMode = outcome.kind === "signedout" ? "reload" : outcome.kind === "gone" ? "leave" : "submit";
            showError(outcome.kind === "invalid" && outcome.message ? outcome.message : FAILURES[outcome.kind] || FAILURES.server);
            paintBusy();
            confirmBtn.focus();
        });
    };

    /* ------------------------------------------------- when the time is up */
    const timeUpEl = byId("pexamTimeUp");
    const timeUpTitle = byId("pexamTimeUpTitle");
    const timeUpText = byId("pexamTimeUpText");
    const timeUpRetry = byId("pexamTimeUpRetry");
    const timeUpBack = byId("pexamTimeUpBack");
    const RETRY_DELAY_MS = 3000;

    /* The page hands over to "Time is up": first while the answers are being
     * sent, then - only if that fails twice - with what went wrong and a
     * button to try again. */
    const showTimeUp = (text, canRetry, final) => {
        if (!timeUpEl) {
            return;
        }
        if (timeUpText) {
            timeUpText.textContent = text;
        }
        if (timeUpRetry) {
            timeUpRetry.hidden = !canRetry;
        }
        if (timeUpBack) {
            timeUpBack.hidden = !final;
        }
        timeUpEl.classList.toggle("is-failed", !!(canRetry || final));
        timeUpEl.hidden = false;
    };

    const TIMEUP_FAILURES = {
        network: "Your answers could not be sent: we could not reach the server. Check your connection, then try again straight away.",
        signedout: "You have been signed out, so your answers could not be sent. Sign in again and open this test from My HR.",
        gone: "This test is no longer available.",
        server: "Your answers could not be sent. Please try again straight away.",
    };

    /* One automatic retry, then it is left to the "Try again" button. */
    function sendTimeUp(retriesLeft) {
        submitting = true;
        showTimeUp("Sending your answers…", false, false);
        post(submitUrl, timeUpData).then((outcome) => {
            if (outcome.kind === "ok") {
                submitting = false;
                finish(outcome.data);
                return;
            }
            /* The server says the time is not up yet (this device's clock ran
             * ahead): open the page again, which starts the countdown from
             * the server's own figure. The answers are kept. */
            if (outcome.kind === "invalid") {
                submitting = false;
                leavePage("");
                return;
            }
            const final = outcome.kind === "gone" || outcome.kind === "signedout";
            if (retriesLeft > 0 && !final) {
                window.setTimeout(() => sendTimeUp(retriesLeft - 1), RETRY_DELAY_MS);
                return;
            }
            submitting = false;
            showTimeUp(TIMEUP_FAILURES[outcome.kind] || TIMEUP_FAILURES.server, !final, final);
        });
    }

    const onTimeUp = () => {
        if (submitted || timeUp) {
            return;
        }
        timeUp = true;

        /* Read the answers before locking the inputs. */
        timeUpData = collect();
        timeUpData.append("timed_out", "1");
        save();
        questions.forEach((question) => {
            question.inputs.forEach((input) => {
                input.disabled = true;
            });
        });
        stepTest.hidden = true;
        stepReview.hidden = true;
        if (noticeEl) {
            noticeEl.hidden = true;
        }
        showTimeUp("Sending your answers…", false, false);
        window.scrollTo(0, 0);
        if (timeUpTitle) {
            timeUpTitle.focus({ preventScroll: true });
        }

        /* A submission already on its way is left to finish; if it fails, its
         * handler sends the answers in from here. */
        if (!submitting) {
            sendTimeUp(1);
        }
    };

    /* -------------------------------------------- leaving the tab or window */
    /* A record, not a block: "left" is logged when the tab is hidden (a tab
     * switch, a minimised browser) or the window loses focus (a click outside
     * it), and closed when the member of staff is back. One exit is open at a
     * time. Nothing here may stop the test: every request is best effort. */
    const away = { at: null, hidden: false, question: 0, sent: "" };
    const isLive = () => !submitted && !timeUp && (state.step === "test" || state.step === "review");

    const eventData = (phase, type) => {
        const data = new FormData();
        data.append("_token", csrf());
        data.append("phase", phase);
        data.append("type", type);
        data.append("question", String(away.question));
        return data;
    };

    /* "Left" goes as a beacon, which the browser still sends from a tab that
     * is being hidden; where that is not possible, as a keep-alive request. */
    const sendLeave = (type) => {
        away.sent = type;
        if (!eventUrl) {
            return;
        }
        try {
            const data = eventData("leave", type);
            if (typeof navigator.sendBeacon === "function" && navigator.sendBeacon(eventUrl, data)) {
                return;
            }
            window.fetch(eventUrl, { method: "POST", body: data, credentials: "same-origin", keepalive: true, headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" } }).catch(() => {});
        } catch (e) {
            /* the exit is rebuilt from the stopwatch when they come back */
        }
    };

    /* Resolves with the server's totals, or null if they could not be had. */
    const sendReturn = (type, awayMs) => {
        if (!eventUrl) {
            return Promise.resolve(null);
        }
        try {
            const data = eventData("return", type);
            data.append("away_ms", String(Math.max(0, Math.round(awayMs))));
            const request = window
                .fetch(eventUrl, { method: "POST", body: data, credentials: "same-origin", headers: { "X-CSRF-TOKEN": csrf(), "X-Requested-With": "XMLHttpRequest", Accept: "application/json" } })
                .then((response) => (response.ok ? response.json() : null))
                .then((body) => (body && typeof body === "object" && Number.isFinite(Number(body.exits)) ? body : null))
                .catch(() => null);
            /* The notice does not wait long for the reply. */
            const patience = new Promise((resolve) => window.setTimeout(() => resolve(null), 2500));
            return Promise.race([request, patience]);
        } catch (e) {
            return Promise.resolve(null);
        }
    };

    const showNotice = (seconds, question) => {
        if (!noticeEl || !isLive()) {
            return;
        }
        noticeEl.innerHTML =
            '<div class="pexam-notice__in">' +
                "<p><strong>Leaving the test was logged.</strong> Away for " + escapeHtml(awayLabel(seconds)) + " on " +
                    (question > 0 ? "question " + escapeHtml(question) : "the review screen") + ". " +
                    escapeHtml(exitsLabel(exits)) + " this attempt, shown with your result.</p>" +
                '<button type="button" class="pexam-btn pexam-btn--sm" data-act="dismiss">Dismiss</button>' +
            "</div>";
        noticeEl.hidden = false;
    };

    const leaveStart = () => {
        if (!isLive()) {
            return;
        }
        const hidden = document.hidden === true;
        if (hidden) {
            away.hidden = true;
        }
        if (away.at === null) {
            away.at = Date.now();
            away.question = state.step === "review" ? 0 : state.current + 1;
            sendLeave(hidden ? "tab" : "window");
        } else if (hidden && away.sent !== "tab") {
            /* A click outside the window that turned into a tab switch. */
            sendLeave("tab");
        }
    };

    const leaveEnd = () => {
        if (away.at === null || document.hidden === true || !document.hasFocus()) {
            return;
        }
        const awayMs = Math.max(0, Date.now() - away.at);
        const type = away.hidden ? "tab" : "window";
        const question = away.question;
        away.at = null;
        away.hidden = false;
        away.sent = "";
        if (!isLive()) {
            return;
        }
        /* Counted here straight away; the server's own figures replace these
         * when its reply arrives. */
        exits += 1;
        sendReturn(type, awayMs).then((reply) => {
            let seconds = awayMs / 1000;
            if (reply) {
                exits = Math.max(0, parseInt(reply.exits, 10) || 0);
                if (Number.isFinite(Number(reply.seconds)) && Number(reply.seconds) > 0) {
                    seconds = Number(reply.seconds);
                }
            }
            paintExits();
            if (state.step === "review" && !submitting) {
                paintReview();
            }
            showNotice(seconds, question);
        });
    };

    document.addEventListener("visibilitychange", () => {
        if (document.hidden) {
            leaveStart();
        } else {
            leaveEnd();
        }
    });
    window.addEventListener("blur", leaveStart);
    window.addEventListener("focus", leaveEnd);

    /* ------------------------------------------------------------- events */
    const ACTIONS = {
        go: (button) => goTo(parseInt(button.dataset.i, 10) || 0),
        prev: previous,
        next: next,
        flag: () => toggleFlag(true),
        review: toReview,
        back: () => goTo(state.current),
        ask: () => {
            state.confirm = true;
            retryMode = "submit";
            showError("");
            render(confirmBtn, false);
        },
        cancel: () => {
            if (submitting) {
                return;
            }
            state.confirm = false;
            retryMode = "submit";
            showError("");
            render(submitBtn, false);
        },
        submit: submitNow,
        dismiss: () => {
            if (noticeEl) {
                noticeEl.hidden = true;
            }
            if (state.step === "review" && reviewTitle) {
                reviewTitle.focus({ preventScroll: true });
            } else if (questions[state.current].heading) {
                questions[state.current].heading.focus({ preventScroll: true });
            }
        },
        "timeup-retry": () => {
            if (submitted || submitting || !timeUp) {
                return;
            }
            sendTimeUp(0);
        },
    };

    document.addEventListener("click", (event) => {
        const button = event.target.closest("[data-act]");
        if (!button || button.disabled || !ACTIONS[button.dataset.act]) {
            return;
        }
        if (submitted || (timeUp && button.dataset.act !== "timeup-retry")) {
            return;
        }
        ACTIONS[button.dataset.act](button);
    });

    form.addEventListener("submit", (event) => event.preventDefault());

    form.addEventListener("change", (event) => {
        const input = event.target;
        if (!input || !input.matches('input[type="radio"]')) {
            return;
        }
        const question = questions.find((item) => item.name === input.name);
        if (question) {
            answered(question);
        }
    });

    /* A-F or 1-6 pick an option; F flags (when the question has no option F);
     * Enter goes on; the left and right arrows move between questions. Up and
     * down stay with the browser, which moves between the options. */
    document.addEventListener("keydown", (event) => {
        /* Escape closes the submit confirmation, like "Keep reviewing". */
        if (event.key === "Escape" && state.step === "review" && state.confirm && !submitting && !submitted && !timeUp) {
            event.preventDefault();
            ACTIONS.cancel();
            return;
        }
        if (state.step !== "test" || submitted || timeUp || event.defaultPrevented || event.metaKey || event.ctrlKey || event.altKey) {
            return;
        }
        const target = event.target;
        const tag = target && target.tagName ? target.tagName : "";
        if (tag === "TEXTAREA" || tag === "SELECT" || (tag === "INPUT" && target.type !== "radio")) {
            return;
        }
        const key = String(event.key || "").toLowerCase();
        const question = questions[state.current];
        let position = key.length === 1 ? "abcdef".indexOf(key) : -1;
        if (key.length === 1 && position < 0) {
            position = "123456".indexOf(key);
        }

        if (position >= 0 && position < question.inputs.length) {
            event.preventDefault();
            pick(position);
        } else if (key === "f") {
            event.preventDefault();
            toggleFlag(false);
        } else if (key === "enter" && tag !== "BUTTON" && tag !== "A") {
            event.preventDefault();
            next();
        } else if (key === "arrowright") {
            event.preventDefault();
            next();
        } else if (key === "arrowleft") {
            event.preventDefault();
            previous();
        }
    });

    /* ----------------------------------------------------------- countdown */
    const startTimer = () => {
        const clockEl = byId("pexamClock");
        const stateEl = byId("pexamTimerState");

        const number = (value) => {
            const n = parseInt(value, 10);
            return Number.isFinite(n) && n > 0 ? n : 0;
        };
        /* All of these come from the server: the seconds left when it built
         * the page, and when the clock turns amber and red. */
        const initial = number(timerEl.dataset.seconds);
        const warn = number(timerEl.dataset.warn);
        const danger = number(timerEl.dataset.danger);
        const marks = String(timerEl.dataset.announce || "")
            .split(",")
            .map(number)
            .filter((mark) => mark > 0 && mark < initial)
            .sort((a, b) => b - a);

        /* Words as well as colour. */
        const STATE_TEXT = { ok: "Time left", warn: "Running low", danger: "Almost out", done: "Time is up" };

        /* The deadline is never worked out from this device's clock: the page
         * only measures how long it has been open and takes that off the
         * server's figure. Counting starts from when the page began to arrive,
         * so a slow connection does not add time. Two measures are kept and
         * the larger wins: the monotonic one ignores a changed system clock,
         * the wall-clock one keeps counting while a device sleeps. */
        const hasPerformance = typeof performance !== "undefined" && typeof performance.now === "function";
        const ticks = () => (hasPerformance ? performance.now() : Date.now());
        let origin = ticks();
        if (hasPerformance && typeof performance.getEntriesByType === "function") {
            const navigation = performance.getEntriesByType("navigation")[0];
            if (navigation && navigation.responseStart > 0 && navigation.responseStart < origin) {
                origin = navigation.responseStart;
            }
        }
        const wallOrigin = Date.now() - (ticks() - origin);
        const secondsLeft = () => Math.max(0, initial - Math.max(ticks() - origin, Date.now() - wallOrigin) / 1000);

        const pad = (n) => (n < 10 ? "0" + n : String(n));
        const clockText = (seconds) => pad(Math.floor(seconds / 60)) + ":" + pad(seconds % 60);
        const spoken = (seconds) => {
            const minutes = Math.floor(seconds / 60);
            const rest = seconds % 60;
            const parts = [];
            if (minutes > 0) {
                parts.push(minutes + (minutes === 1 ? " minute" : " minutes"));
            }
            if (rest > 0 || minutes === 0) {
                parts.push(rest + (rest === 1 ? " second" : " seconds"));
            }
            return parts.join(" ");
        };

        let shown = -1;
        let current = "";
        let finished = false;
        let interval = null;
        let deadline = null;

        const stop = () => {
            finished = true;
            if (interval !== null) {
                window.clearInterval(interval);
            }
            if (deadline !== null) {
                window.clearTimeout(deadline);
            }
        };

        /* Read out now and then - at the marks the server lists (5 minutes and
         * 1 minute) - never every second: the ticking value itself is silent
         * to screen readers (role="timer"). */
        const tick = () => {
            if (finished) {
                return;
            }
            const left = secondsLeft();
            const whole = Math.ceil(left);
            if (whole !== shown) {
                shown = whole;
                if (clockEl) {
                    clockEl.textContent = clockText(whole);
                }
                const nextState = whole <= 0 ? "done" : whole <= danger ? "danger" : whole <= warn ? "warn" : "ok";
                if (nextState !== current) {
                    current = nextState;
                    timerEl.dataset.state = nextState;
                    if (stateEl) {
                        stateEl.textContent = STATE_TEXT[nextState];
                    }
                }
                let crossed = null;
                while (marks.length > 0 && whole <= marks[0]) {
                    crossed = marks.shift();
                }
                if (crossed !== null && whole > 0) {
                    announce((crossed - whole <= 2 ? spoken(crossed) : "About " + spoken(whole)) + " remaining.");
                }
            }
            if (left <= 0) {
                stop();
                announce("Time is up. Your answers are being sent in.");
                onTimeUp();
            }
        };

        stopTimer = stop;

        tick();
        if (finished) {
            return;
        }
        interval = window.setInterval(tick, 250);
        /* A tab left in the background has its repeating timers slowed right
         * down; one plain timeout aimed at zero still fires on time. */
        deadline = window.setTimeout(tick, Math.ceil(secondsLeft() * 1000) + 30);
        document.addEventListener("visibilitychange", tick);
        window.addEventListener("focus", tick);
        window.addEventListener("pageshow", tick);

        /* Say once, shortly after the page opens, how long there is. */
        window.setTimeout(() => {
            if (!finished && !submitted) {
                announce("This test is timed. " + spoken(Math.ceil(secondsLeft())) + " remaining.");
            }
        }, 1500);
    };

    /* ---------------------------------------------------------------- boot */
    buildGrid();
    restore();
    if (state.step === "review") {
        render(reviewTitle, false);
    } else {
        render(questions[state.current].heading, true);
    }
    if (timed) {
        startTimer();
    }
})();

/* -------------------------------------------------------- step 4: result */
(function () {
    const root = document.getElementById("pexamResult");
    if (!root) {
        return;
    }

    /* The attempt is marked: whatever this tab still held for it goes. */
    try {
        window.sessionStorage.removeItem(storeKey(root.dataset.attempt));
    } catch (e) {
        /* nothing to clear */
    }

    const title = document.getElementById("pexamTitle");
    if (title) {
        title.focus({ preventScroll: true });
    }
})();
