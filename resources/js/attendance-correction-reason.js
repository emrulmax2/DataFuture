/*
 * Attendance Integrity: the "Reason for late correction" box.
 *
 * When attendance is changed more than the correction deadline after a class
 * ended (Attendance Integrity > Settings), the save answers 422 with
 * `requires_reason`. The screen shows this box, then sends the same request
 * again with `change_reason`. The reason is kept in the attendance history.
 *
 * Self-contained (its own markup and styles) so it looks the same on the tutor
 * register, the office register and the student profile.
 */

const STYLE_ID = "acr-style";

const css = `
.acr-overlay{position:fixed;inset:0;z-index:100000;background:rgba(15,23,42,.55);display:flex;align-items:center;justify-content:center;padding:16px;font-family:inherit}
.acr-box{background:#fff;border-radius:10px;box-shadow:0 20px 50px rgba(15,23,42,.3);width:100%;max-width:520px;overflow:hidden}
.acr-head{display:flex;gap:12px;align-items:flex-start;padding:18px 20px 6px}
.acr-icon{flex:0 0 36px;height:36px;border-radius:9999px;display:flex;align-items:center;justify-content:center;background:#fef3c7;color:#b45309;font-weight:700;font-size:18px}
.acr-icon.is-stop{background:#fee2e2;color:#b91c1c}
.acr-title{font-size:16px;font-weight:600;color:#0f172a;margin:6px 0 4px}
.acr-text{font-size:13px;line-height:1.5;color:#475569}
.acr-body{padding:6px 20px 0 68px}
.acr-body textarea{width:100%;min-height:96px;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;font-size:13px;color:#0f172a;resize:vertical}
.acr-body textarea:focus{outline:none;border-color:#164e63;box-shadow:0 0 0 3px rgba(22,78,99,.15)}
.acr-meta{display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-top:4px}
.acr-error{color:#b91c1c}
.acr-foot{display:flex;justify-content:flex-end;gap:8px;padding:16px 20px 18px}
.acr-btn{border-radius:6px;padding:8px 16px;font-size:13px;font-weight:600;border:1px solid #cbd5e1;background:#fff;color:#334155;cursor:pointer}
.acr-btn--primary{background:#164e63;border-color:#164e63;color:#fff}
.acr-btn[disabled]{opacity:.5;cursor:not-allowed}
@media (max-width:520px){.acr-body{padding-left:20px}}
`;

function ensureStyle() {
    if (document.getElementById(STYLE_ID)) return;
    const style = document.createElement("style");
    style.id = STYLE_ID;
    style.textContent = css;
    document.head.appendChild(style);
}

function el(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
}

/** Ask for the reason. Resolves with the text, or null when cancelled. */
export function askCorrectionReason(message, minLength = 10) {
    ensureStyle();
    return new Promise((resolve) => {
        const overlay = el("div", "acr-overlay");
        overlay.setAttribute("role", "dialog");
        overlay.setAttribute("aria-modal", "true");
        const box = el("div", "acr-box");
        const head = el("div", "acr-head");
        head.appendChild(el("div", "acr-icon", "!"));
        const heading = el("div");
        heading.appendChild(el("div", "acr-title", "Reason for late correction"));
        heading.appendChild(el("div", "acr-text", message || "Please give the reason for this change."));
        head.appendChild(heading);

        const body = el("div", "acr-body");
        const textarea = el("textarea");
        textarea.setAttribute("aria-label", "Reason for the change");
        textarea.placeholder = "For example: the student was in class but marked absent by mistake; confirmed with the tutor.";
        textarea.maxLength = 2000;
        const meta = el("div", "acr-meta");
        const hint = el("span", "", `At least ${minLength} characters.`);
        const count = el("span", "", "0");
        meta.appendChild(hint);
        meta.appendChild(count);
        body.appendChild(textarea);
        body.appendChild(meta);

        const foot = el("div", "acr-foot");
        const cancel = el("button", "acr-btn", "Cancel");
        cancel.type = "button";
        const ok = el("button", "acr-btn acr-btn--primary", "Save with this reason");
        ok.type = "button";
        ok.disabled = true;
        foot.appendChild(cancel);
        foot.appendChild(ok);

        box.appendChild(head);
        box.appendChild(body);
        box.appendChild(foot);
        overlay.appendChild(box);
        document.body.appendChild(overlay);
        setTimeout(() => textarea.focus(), 30);

        const close = (value) => {
            document.removeEventListener("keydown", onKey);
            overlay.remove();
            resolve(value);
        };
        const onKey = (event) => { if (event.key === "Escape") close(null); };
        document.addEventListener("keydown", onKey);

        textarea.addEventListener("input", () => {
            const length = textarea.value.trim().length;
            count.textContent = String(length);
            ok.disabled = length < minLength;
            hint.className = length > 0 && length < minLength ? "acr-error" : "";
        });
        cancel.addEventListener("click", () => close(null));
        ok.addEventListener("click", () => {
            const value = textarea.value.trim();
            if (value.length >= minLength) close(value);
        });
    });
}

/** A plain message (for example when the save was refused). */
export function showAttendanceNotice(title, message) {
    ensureStyle();
    return new Promise((resolve) => {
        const overlay = el("div", "acr-overlay");
        overlay.setAttribute("role", "alertdialog");
        const box = el("div", "acr-box");
        const head = el("div", "acr-head");
        head.appendChild(el("div", "acr-icon is-stop", "!"));
        const heading = el("div");
        heading.appendChild(el("div", "acr-title", title));
        heading.appendChild(el("div", "acr-text", message));
        head.appendChild(heading);
        const foot = el("div", "acr-foot");
        const ok = el("button", "acr-btn acr-btn--primary", "OK");
        ok.type = "button";
        foot.appendChild(ok);
        box.appendChild(head);
        box.appendChild(foot);
        overlay.appendChild(box);
        document.body.appendChild(overlay);
        setTimeout(() => ok.focus(), 30);
        ok.addEventListener("click", () => { overlay.remove(); resolve(); });
    });
}

/** The save answered "give a reason" (422 with requires_reason). */
export function needsReason(status, data) {
    return status === 422 && data && data.requires_reason === true;
}

/** The save was refused with a message to show (403, 404, or a 422 that is not a field error). */
export function refusalMessage(status, data) {
    if (!data || !data.message) return null;
    if (status === 403 || status === 404) return data.message;
    if (status === 422 && data.errors && data.errors.permission) return data.message;
    return null;
}
