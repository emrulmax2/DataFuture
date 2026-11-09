/*
 * Applicant details (admission portal shell): the pieces the page adds on
 * top of admission.js / admission-global.js.
 *
 *  - "Change status" dialog: pick the next status (+ rejection reason), then
 *    hand over to the anchor admission-global.js already binds, so the
 *    confirm step, validation, status update and student conversion run
 *    exactly as before.
 *  - Tabulator grids inside closed <details> measure zero wide; opening one
 *    asks admission.js to redraw (its resize listener).
 */
(function () {
    "use strict";

    if (!document.body.classList.contains("adp-body")) return;

    /* ---------- status change dialog ---------------------------------------- */
    const openBtn = document.querySelector("[data-adp-status-open]");
    const modalEl = document.getElementById("changeStatusModal");
    if (openBtn && modalEl && window.tailwind && window.tailwind.Modal) {
        const modal = window.tailwind.Modal.getOrCreateInstance(modalEl);
        const sel = modalEl.querySelector("#adp_new_status");
        const help = modalEl.querySelector("[data-adp-status-help]");
        const reasonWrap = modalEl.querySelector("[data-adp-reason]");
        const reasonSel = modalEl.querySelector("#rejected_reason");
        const go = modalEl.querySelector("[data-adp-status-go]");
        const err = modalEl.querySelector("[data-adp-status-error]");
        const reasonErr = modalEl.querySelector("[data-adp-reason-error]");
        /* Accepted / Offer Placed send the offer form: rendered only while proof of ID or fee
           eligibility is missing, and then the change cannot go ahead (the server refuses too). */
        const offerBlock = modalEl.querySelector("[data-adp-offer-block]");
        const blocked = () => {
            const opt = sel.selectedOptions[0];
            return !!(offerBlock && opt && opt.hasAttribute("data-needs-proof"));
        };

        /* Offer Accepted saves proof of ID + fee eligibility with the conversion. The
           four controls admission-global.js posts (#sts_proof_type, #sts_proof_id,
           #sts_proof_expiredate, #sts_fee_eligibility_id) sit in this dialog, pre-filled
           from the record, and every one of them is required before the confirm step. */
        const proofWrap = modalEl.querySelector("[data-adp-proof]");
        const proofFields = Array.from(modalEl.querySelectorAll("[data-adp-proof-field]"));
        const proofInitial = proofFields.map((f) => f.value);
        const proofKey = (f) => f.getAttribute("data-adp-proof-field");
        const proofError = (f) => modalEl.querySelector('[data-adp-proof-error="' + proofKey(f) + '"]');
        const clearProofError = (f) => { const e = proofError(f); if (e) e.textContent = ""; f.classList.remove("adp-ctl--error"); };
        const resetProof = () => { proofFields.forEach((f, i) => { f.value = proofInitial[i]; clearProofError(f); }); };
        const validateProof = () => {
            let first = null;
            proofFields.forEach((f) => {
                const missing = !String(f.value || "").trim();
                const e = proofError(f);
                if (e) e.textContent = missing ? "Required" : "";
                f.classList.toggle("adp-ctl--error", missing);
                if (missing && !first) first = f;
            });
            return first;
        };
        proofFields.forEach((f) => {
            f.addEventListener("input", () => clearProofError(f));
            f.addEventListener("change", () => clearProofError(f));
        });

        const paint = () => {
            const opt = sel.selectedOptions[0];
            const id = sel.value;
            const danger = id === "8" || id === "46";
            go.textContent = id ? "Set to " + opt.textContent.trim() : "Continue";
            go.classList.toggle("adp-btn--danger", danger);
            go.classList.toggle("adp-btn--primary", !danger);
            const text = opt ? (opt.getAttribute("data-help") || "") : "";
            help.hidden = !text;
            help.textContent = text;
            help.className = "adp-notice" + (danger ? " adp-notice--bad" : (id === "7" ? " adp-notice--warn" : ""));
            if (reasonWrap) reasonWrap.hidden = id !== "8";
            if (proofWrap) proofWrap.hidden = id !== "7";
            if (id !== "7") proofFields.forEach(clearProofError);
            if (err) err.textContent = "";
            if (reasonErr) reasonErr.textContent = "";
            const stop = blocked();
            if (offerBlock) offerBlock.hidden = !stop;
            if (stop) go.setAttribute("disabled", "disabled"); else go.removeAttribute("disabled");
        };

        /* "Add proof of ID & fee eligibility": the editor on the details page, or go there */
        const fix = modalEl.querySelector("[data-adp-offer-fix]");
        if (fix) fix.addEventListener("click", () => {
            const proofEl = document.getElementById("editAdmissionProofModal");
            if (!proofEl) { window.location.href = fix.getAttribute("data-url"); return; }
            modal.hide();
            window.setTimeout(() => window.tailwind.Modal.getOrCreateInstance(proofEl).show(), 260);
        });

        sel.addEventListener("change", paint);
        openBtn.addEventListener("click", () => {
            sel.value = "";
            if (reasonSel) reasonSel.value = "";
            resetProof();
            paint();
            modal.show();
        });

        /* the confirm step (admission-global.js) names the change as the design does */
        const confirmEl = document.getElementById("statusConfirmModal");
        if (confirmEl) {
            const summary = confirmEl.querySelector("[data-adp-proof-summary]");
            const shownValue = (f) => (f.tagName === "SELECT" ? ((f.selectedOptions[0] || {}).textContent || "") : f.value);
            const fillSummary = () => {
                if (!summary) return;
                proofFields.forEach((f) => {
                    const cell = summary.querySelector('[data-adp-sum="' + proofKey(f) + '"]');
                    if (cell) cell.textContent = String(shownValue(f)).trim();
                });
            };
            confirmEl.addEventListener("shown.tw.modal", () => {
                /* after admission-global.js's own shown handler has written title + text */
                window.setTimeout(() => {
                    const agree = confirmEl.querySelector("#statusAgreement");
                    const id = agree ? (agree.getAttribute("data-statusid") || "") : "";
                    const opt = Array.from(sel.options).find((o) => o.value === id);
                    const item = confirmEl.querySelector("[data-adp-status-item]");
                    const current = (modalEl.querySelector(".adp-vrow .adp-pill") || {}).textContent || "";
                    if (item) {
                        item.hidden = !opt;
                        item.textContent = opt ? current.trim() + " → " + opt.textContent.trim() : "";
                    }
                    const title = confirmEl.querySelector(".confModTitle");
                    const desc = confirmEl.querySelector(".confModDesc");
                    if (desc && id === "8") desc.textContent = "Do you want to change the applicant status? Click agree to continue.";
                    if (id === "7") {
                        /* the proof / fee details were completed in the dialog, so the legacy
                           "Validation Error Found!" wording (a check of what is stored) does not apply */
                        if (title) title.textContent = "Are you sure?";
                        if (desc) desc.textContent = "This converts the applicant into a student and cannot be undone. Click agree to continue.";
                        fillSummary();
                    }
                    if (summary) summary.hidden = id !== "7";
                }, 0);
            });
            confirmEl.addEventListener("hide.tw.modal", () => { if (summary) summary.hidden = true; });
        }

        go.addEventListener("click", () => {
            const id = sel.value;
            if (!id) { if (err) err.textContent = "Choose a status."; return; }
            if (blocked()) return;
            if (id === "8" && reasonSel && !reasonSel.value) { if (reasonErr) reasonErr.textContent = "A rejection reason is required."; return; }
            if (id === "7") { const missing = validateProof(); if (missing) { missing.focus(); return; } }
            const anchor = document.querySelector('[data-adp-status-anchor="' + id + '"]');
            modal.hide();
            /* let the dialog finish closing before the confirm modal opens */
            window.setTimeout(() => { if (anchor) anchor.click(); }, 260);
        });
    }

    /* ---------- proof of ID dialog ------------------------------------------ */
    const proofForm = document.getElementById("editAdmissionProofForm");
    if (proofForm && window.tailwind && window.tailwind.Modal && window.axios) {
        const proofModalEl = document.getElementById("editAdmissionProofModal");
        const proofModal = window.tailwind.Modal.getOrCreateInstance(proofModalEl);
        /* sent here from another page's status dialog ("Add proof of ID & fee eligibility") */
        const params = new URLSearchParams(window.location.search);
        if (params.get("proof") === "1") {
            params.delete("proof");
            const qs = params.toString();
            window.history.replaceState(null, "", window.location.pathname + (qs ? "?" + qs : "") + window.location.hash);
            window.setTimeout(() => proofModal.show(), 300);
        }
        const saveBtn = document.getElementById("saveProof");
        const spinner = saveBtn ? saveBtn.querySelector(".adm-btn-loader") : null;
        const setBusy = (busy) => {
            if (saveBtn) { if (busy) saveBtn.setAttribute("disabled", "disabled"); else saveBtn.removeAttribute("disabled"); }
            if (spinner) spinner.style.display = busy ? "inline-block" : "none";
        };
        proofForm.addEventListener("submit", (e) => {
            e.preventDefault();
            proofForm.querySelectorAll(".acc__input-error").forEach((el) => { el.textContent = ""; });
            setBusy(true);
            window.axios.post(route("admission.update.proof.id"), new FormData(proofForm), {
                headers: { "X-CSRF-TOKEN": (document.querySelector('meta[name="csrf-token"]') || {}).content || "" },
            }).then(() => {
                setBusy(false);
                proofModal.hide();
                const successEl = document.getElementById("successModal");
                if (successEl) {
                    successEl.querySelector(".successModalTitle").textContent = "Proof of ID saved";
                    successEl.querySelector(".successModalDesc").textContent = "The applicant's proof of ID details were updated.";
                    const closer = successEl.querySelector(".successCloser");
                    if (closer) closer.setAttribute("data-action", "RELOAD");
                    window.tailwind.Modal.getOrCreateInstance(successEl).show();
                }
                window.setTimeout(() => window.location.reload(), 2000);
            }).catch((error) => {
                setBusy(false);
                const errors = error && error.response && error.response.status === 422 && error.response.data ? error.response.data.errors || {} : null;
                if (!errors) return;
                Object.entries(errors).forEach(([key, val]) => {
                    const slot = proofForm.querySelector(".error-" + key);
                    if (slot) slot.textContent = Array.isArray(val) ? val[0] : String(val);
                });
            });
        });
    }

    /* ---------- mobile verification (contact modal) ------------------------- */
    const mobileWrap = document.querySelector("[data-adp-mobile]");
    if (mobileWrap) {
        const input = mobileWrap.querySelector("#mobile");
        const stateBtn = mobileWrap.querySelector("#sendMobileVerifiCode");
        const flag = mobileWrap.querySelector(".mobile_verification");
        const action = mobileWrap.querySelector("[data-adp-mobile-action]");
        const okNotice = mobileWrap.querySelector("[data-adp-mobile-ok]");
        const codeGroup = document.querySelector("#editAdmissionContactDetailsForm .verifyCodeGroup");
        const codeError = document.querySelector("#editAdmissionContactDetailsForm .error-mobile_verification_error");

        const changeLink = mobileWrap.querySelector("[data-adp-mobile-change]");
        /* verified = the number on record (data-org) with its verified flag; a
           number being typed can never match until admission.js has seen it */
        const isVerified = () => { const val = input.value.trim(); return val !== "" && val === (input.getAttribute("data-org") || "").trim() && flag.value === "1"; };
        const codeVisible = () => !!codeGroup && window.getComputedStyle(codeGroup).display !== "none";
        const paint = () => {
            const val = input.value.trim();
            const verified = isVerified();
            /* admission.js paints the border red while the number is unverified; the design keeps it neutral */
            input.style.borderColor = "";
            if (verified) {
                input.readOnly = true;
                action.textContent = "Change";
                action.removeAttribute("disabled");
                okNotice.hidden = false;
                okNotice.textContent = "Verified — " + val + " is confirmed by SMS.";
                if (changeLink) changeLink.hidden = true;
            } else {
                const sent = codeVisible();
                action.textContent = sent ? "Resend code" : "Send code";
                if (val === "") action.setAttribute("disabled", "disabled"); else action.removeAttribute("disabled");
                okNotice.hidden = true;
                if (changeLink) changeLink.hidden = !sent;
            }
        };
        const startChange = () => {
            input.readOnly = false;
            input.value = "";
            stateBtn.removeAttribute("disabled");
            input.focus();
            input.dispatchEvent(new Event("keyup", { bubbles: true }));
            if (codeGroup) codeGroup.style.display = "none";
            if (codeError) codeError.textContent = "";
            paint();
        };

        action.addEventListener("click", () => {
            if (isVerified()) { startChange(); return; }
            if (input.value.trim() === "") return;
            /* admission.js leaves its button disabled after a send; a resend needs it live again */
            stateBtn.removeAttribute("disabled");
            stateBtn.click();
        });
        if (changeLink) changeLink.addEventListener("click", startChange);
        if (codeGroup) new MutationObserver(() => paint()).observe(codeGroup, { attributes: true, attributeFilter: ["style"] });

        ["keyup", "input", "paste", "change"].forEach((type) => input.addEventListener(type, () => window.setTimeout(paint, 0)));
        new MutationObserver(() => {
            /* admission.js marks the button verified on a successful SMS check of a
               new number: that number becomes the one on record */
            const val = input.value.trim();
            if (stateBtn.classList.contains("verified") && flag.value === "1" && val !== "" && val !== (input.getAttribute("data-org") || "").trim()) {
                input.setAttribute("data-org", val);
                flag.setAttribute("data-org", "1");
            }
            paint();
        }).observe(stateBtn, { attributes: true, childList: true, subtree: true, attributeFilter: ["class", "style"] });

        /* the design's wording for the verification outcome */
        if (codeError) {
            new MutationObserver(() => {
                const text = codeError.textContent.trim();
                if (/does not found|not found/i.test(text)) codeError.textContent = "We could not verify that code. Check the SMS and try again.";
                else if (/can not be empty|6 digit/i.test(text)) codeError.textContent = "Enter the 6-digit code from the SMS.";
            }).observe(codeError, { childList: true, subtree: true, characterData: true });
        }

        paint();
    }

    /* ---------- proposed course dialog -------------------------------------- */
    const courseSel = document.getElementById("course_creation_id");
    if (courseSel) {
        const info = document.querySelector("[data-adp-course-info]");
        const feeEl = document.querySelector("[data-adp-fee]");
        const startEl = document.querySelector("[data-adp-start]");
        const fmtDate = (d) => {
            const m = String(d || "").match(/^(\d{4})-(\d{2})-(\d{2})/);
            if (!m) return "—";
            return new Date(+m[1], +m[2] - 1, +m[3]).toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
        };
        const paintCourse = () => {
            const opt = courseSel.selectedOptions[0];
            const fees = opt ? parseFloat(opt.getAttribute("data-fees")) : NaN;
            const start = opt ? opt.getAttribute("data-start") : "";
            if (!opt || !opt.value) { if (info) info.hidden = true; return; }
            if (info) info.hidden = false;
            if (feeEl) feeEl.textContent = isNaN(fees) ? "—" : "£" + Math.round(fees).toLocaleString("en-GB") + " per year";
            if (startEl) startEl.textContent = start ? fmtDate(start) : "—";
        };
        courseSel.addEventListener("change", () => window.setTimeout(paintCourse, 0));

        /* question numbering follows whether SFE was applied to, as the design does */
        const sfe = document.getElementById("student_finance_england");
        const q3 = document.querySelector("[data-adp-q3]");
        const paintNumbers = () => { if (q3 && sfe) q3.textContent = sfe.checked ? "3" : "2"; };
        if (sfe) sfe.addEventListener("change", () => window.setTimeout(paintNumbers, 0));
        const loanSel = document.getElementById("student_loan");
        if (loanSel) loanSel.addEventListener("change", () => window.setTimeout(paintNumbers, 50));
        paintNumbers();

        /* admission.js marks the full-time box .onlyWeekends when the campus runs weekends only */
        const fullTime = document.getElementById("full_time");
        const ewHint = document.querySelector("[data-adp-ew-hint]");
        const paintEw = () => { if (ewHint && fullTime) ewHint.textContent = fullTime.classList.contains("onlyWeekends") ? "This campus runs evening/weekend classes only." : "This campus runs both weekday and evening/weekend timetables."; };
        if (fullTime) { new MutationObserver(paintEw).observe(fullTime, { attributes: true, attributeFilter: ["class"] }); paintEw(); }
    }

    /* ---------- grids inside accordions ------------------------------------- */
    document.querySelectorAll("details.adp-accordion").forEach((details) => {
        details.addEventListener("toggle", () => {
            if (details.open && details.querySelector(".tabulator")) {
                window.setTimeout(() => window.dispatchEvent(new Event("resize")), 30);
            }
        });
    });
})();
