import xlsx from "xlsx";

/*
 * Conversion log (admission portal shell).
 *
 * The page is rendered server-side (one table per conversion batch, newest
 * first); this script adds what the old Tabulator page offered on top:
 *  - search + status filter over the rendered rows (all batches),
 *  - "View error" → the stored exception in #viewConversionErrorModal (+ Copy),
 *  - Print (window.print) and Export CSV / XLSX of the rows left by the filter,
 *  - while the latest run still has queued / processing jobs, the rows and the
 *    summary chips refresh from admission.conversion.log.list every 5 s.
 */
(function () {
    "use strict";

    const root = document.querySelector("[data-adp-convlog]");
    if (!root) return;

    const PILL = { queued: ["Queued", "muted"], processing: ["Processing", "amber"], completed: ["Completed", "green"], failed: ["Failed", "red"], cancelled: ["Cancelled", "muted"] };
    const STATE = { running: ["Running", "amber"], failed: ["Stopped", "red"], completed: ["Completed", "green"], cancelled: ["Cancelled", "muted"] };
    const byId = (id) => document.getElementById(id);
    const text = (el) => (el ? el.textContent.replace(/\s+/g, " ").trim() : "");
    const tables = () => Array.from(root.querySelectorAll("[data-adp-convlog-table]"));
    const latestTable = root.querySelector("[data-adp-convlog-table]");

    /* ---------- filter ----------------------------------------------------- */
    const form = root.querySelector("[data-adp-filter]");
    const query = byId("query-CL");
    const status = byId("status-CL");
    const count = root.querySelector("[data-adp-filter-count]");
    const applyFilter = () => {
        const q = (query ? query.value : "").trim().toLowerCase();
        const st = status ? status.value : "";
        let shown = 0;
        let total = 0;
        tables().forEach((table) => {
            let visible = 0;
            table.querySelectorAll("[data-adp-row]").forEach((tr) => {
                const ok = (!st || tr.getAttribute("data-status") === st) && (!q || (tr.getAttribute("data-search") || "").indexOf(q) > -1);
                tr.hidden = !ok;
                if (ok) visible++;
                total++;
            });
            const none = table.querySelector("[data-adp-none]");
            if (none) none.hidden = visible > 0;
            shown += visible;
        });
        if (count) count.textContent = (q || st) ? shown + " of " + total + " jobs" : "";
    };
    if (form) {
        form.addEventListener("submit", (e) => { e.preventDefault(); applyFilter(); });
        const reset = byId("tabulator-html-filter-reset-CL");
        if (reset) reset.addEventListener("click", () => { if (query) query.value = ""; if (status) status.value = ""; applyFilter(); });
        if (status) status.addEventListener("change", applyFilter);
    }

    /* ---------- error dialog ---------------------------------------------- */
    const errModal = byId("viewConversionErrorModal");
    root.addEventListener("click", (e) => {
        const btn = e.target.closest ? e.target.closest("[data-adp-error]") : null;
        if (!btn || !errModal) return;
        const tpl = root.querySelector('template[data-adp-exception="' + btn.getAttribute("data-adp-error") + '"]');
        const title = byId("conversionErrorTitle");
        const body = byId("conversionErrorContent");
        if (title) title.textContent = btn.getAttribute("data-job") || "";
        if (body) body.textContent = tpl ? tpl.content.textContent.trim() : "No exception was stored for this job.";
        const tr = btn.closest("[data-adp-row]");
        const meta = errModal.querySelector("[data-adp-error-meta]");
        if (meta) {
            meta.textContent = tr ? "Attempt " + (text(tr.querySelector('[data-adp-cell="attempts"]')) || "?") + " · started " + (text(tr.querySelector('[data-adp-cell="started"]')) || "—") + " · failed " + (text(tr.querySelector('[data-adp-cell="finished"]')) || "—") : "";
        }
        if (window.tailwind && window.tailwind.Modal) window.tailwind.Modal.getOrCreateInstance(errModal).show();
    });
    const copyBtn = errModal ? errModal.querySelector("[data-adp-error-copy]") : null;
    if (copyBtn) {
        copyBtn.addEventListener("click", () => {
            const value = (byId("conversionErrorContent") || {}).textContent || "";
            const was = copyBtn.innerHTML;
            const done = () => { copyBtn.textContent = "Copied"; window.setTimeout(() => { copyBtn.innerHTML = was; }, 1500); };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(value).then(done, done);
            } else {
                const ta = document.createElement("textarea");
                ta.value = value;
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand("copy"); } catch (err) { /* nothing to do */ }
                ta.remove();
                done();
            }
        });
    }

    /* ---------- print / export -------------------------------------------- */
    const printBtn = byId("tabulator-print-CL");
    if (printBtn) printBtn.addEventListener("click", () => window.print());
    const exportRows = () => {
        const aoa = [["Batch", "#", "Job", "Class", "Status", "Message", "Attempts", "Started", "Finished"]];
        tables().forEach((table) => {
            const batch = table.getAttribute("data-batch") || "";
            table.querySelectorAll("[data-adp-row]").forEach((tr) => {
                if (tr.hidden) return;
                const cell = (sel) => text(tr.querySelector(sel));
                aoa.push([batch, cell(".adp-convlog__n"), cell(".adp-convlog__job b"), cell(".adp-convlog__job small"), cell('[data-adp-cell="status"]'), cell('[data-adp-cell="message"]'), cell('[data-adp-cell="attempts"]'), cell('[data-adp-cell="started"]'), cell('[data-adp-cell="finished"]')]);
            });
        });
        return aoa;
    };
    const download = (blob, name) => {
        const a = document.createElement("a");
        a.href = URL.createObjectURL(blob);
        a.download = name;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.setTimeout(() => URL.revokeObjectURL(a.href), 1000);
    };
    const csvBtn = byId("tabulator-export-csv-CL");
    if (csvBtn) csvBtn.addEventListener("click", () => download(new Blob([xlsx.utils.sheet_to_csv(xlsx.utils.aoa_to_sheet(exportRows()))], { type: "text/csv;charset=utf-8" }), "conversion-log.csv"));
    const xlsxBtn = byId("tabulator-export-xlsx-CL");
    if (xlsxBtn) xlsxBtn.addEventListener("click", () => {
        const wb = xlsx.utils.book_new();
        xlsx.utils.book_append_sheet(wb, xlsx.utils.aoa_to_sheet(exportRows()), "Conversion Log");
        xlsx.writeFile(wb, "conversion-log.xlsx");
    });

    /* ---------- live refresh while the latest run is still going ----------- */
    const sum = root.querySelector("[data-adp-sum-wrap]");
    const stopped = root.querySelector("[data-adp-stopped]");
    const pendingRows = () => (latestTable ? Array.from(latestTable.querySelectorAll("[data-adp-row]")).filter((tr) => ["queued", "processing"].indexOf(tr.getAttribute("data-status")) > -1).length : 0);
    const pad = (n) => String(n).padStart(2, "0");
    const parseStamp = (s) => { const m = /^(\d{2})-(\d{2})-(\d{4}) (\d{2}):(\d{2}):(\d{2})$/.exec(s || ""); return m ? new Date(+m[3], +m[2] - 1, +m[1], +m[4], +m[5], +m[6]) : null; };
    const fmtStamp = (d) => pad(d.getDate()) + "-" + pad(d.getMonth() + 1) + "-" + d.getFullYear() + " " + pad(d.getHours()) + ":" + pad(d.getMinutes()) + ":" + pad(d.getSeconds());
    const fmtDuration = (s) => (s < 60 ? s + " s" : Math.floor(s / 60) + " min " + (s % 60) + " s");
    const setPill = (el, label, tone) => {
        if (!el) return;
        el.className = el.className.replace(/\badp-pill--[\w-]+/g, "").replace(/\s+/g, " ").trim() + " adp-pill--" + tone;
        el.innerHTML = "<i></i>" + label;
    };
    const paintSummary = () => {
        if (!latestTable) return 0;
        const trs = Array.from(latestTable.querySelectorAll("[data-adp-row]"));
        const c = { queued: 0, processing: 0, completed: 0, failed: 0, cancelled: 0 };
        trs.forEach((tr) => { const s = tr.getAttribute("data-status"); c[s] = (c[s] || 0) + 1; });
        const pending = c.queued + c.processing;
        const state = pending ? "running" : (c.failed ? "failed" : (c.completed === trs.length ? "completed" : "cancelled"));
        if (sum) {
            setPill(sum.querySelector('[data-adp-sum="state"]'), STATE[state][0], STATE[state][1]);
            const done = sum.querySelector('[data-adp-sum="done"]');
            if (done) done.textContent = c.completed + " of " + trs.length + " jobs done";
            const failed = sum.querySelector('[data-adp-sum="failed"]');
            if (failed) { failed.hidden = !c.failed; failed.textContent = c.failed + " failed"; }
            const cancelled = sum.querySelector('[data-adp-sum="cancelled"]');
            if (cancelled) { cancelled.hidden = !c.cancelled; cancelled.textContent = c.cancelled + " cancelled"; }
            const pend = sum.querySelector('[data-adp-sum="pending"]');
            const pendText = sum.querySelector('[data-adp-sum="pending-text"]');
            if (pend) pend.hidden = !pending;
            if (pendText) pendText.textContent = pending + " to go · refreshing";
            let first = null;
            let last = null;
            trs.forEach((tr) => {
                const s = parseStamp(text(tr.querySelector('[data-adp-cell="started"]')));
                const f = parseStamp(text(tr.querySelector('[data-adp-cell="finished"]')));
                if (s && (!first || s < first)) first = s;
                if (f && (!last || f > last)) last = f;
            });
            const fin = sum.querySelector('[data-adp-sum="finished"]');
            if (fin) fin.textContent = last ? fmtStamp(last) : "—";
            const dur = sum.querySelector('[data-adp-sum="duration"]');
            if (dur) dur.textContent = first && last ? fmtDuration(Math.max(0, Math.round((last - first) / 1000))) : "—";
        }
        if (stopped) {
            stopped.hidden = state !== "failed";
            const job = stopped.querySelector("[data-adp-stopped-job]");
            const failedTr = trs.find((tr) => tr.getAttribute("data-status") === "failed");
            if (job && failedTr) job.textContent = text(failedTr.querySelector(".adp-convlog__job b"));
        }
        return pending;
    };
    const patchRow = (r) => {
        const tr = latestTable.querySelector('[data-adp-row][data-id="' + r.id + '"]');
        if (!tr) return;
        const pill = PILL[r.status] || [r.status, "muted"];
        tr.setAttribute("data-status", r.status);
        setPill(tr.querySelector('[data-adp-cell="status"]'), pill[0], pill[1]);
        const msg = tr.querySelector('[data-adp-cell="message"]');
        if (msg) { msg.textContent = r.message || "—"; msg.setAttribute("title", r.message || ""); }
        const attempts = tr.querySelector('[data-adp-cell="attempts"]');
        if (attempts) attempts.textContent = String(r.attempts);
        const started = tr.querySelector('[data-adp-cell="started"]');
        if (started) started.textContent = r.started_at || "—";
        const finished = tr.querySelector('[data-adp-cell="finished"]');
        if (finished) finished.textContent = r.finished_at || "—";
        let btn = tr.querySelector("[data-adp-error]");
        if (r.exception) {
            let tpl = root.querySelector('template[data-adp-exception="' + r.id + '"]');
            if (!tpl) {
                tpl = document.createElement("template");
                tpl.setAttribute("data-adp-exception", r.id);
                latestTable.parentElement.appendChild(tpl);
            }
            tpl.innerHTML = "";
            tpl.content.appendChild(document.createTextNode(r.exception));
            if (!btn && msg) {
                btn = document.createElement("button");
                btn.type = "button";
                btn.className = "adp-btn-link adp-btn-link--danger";
                btn.setAttribute("data-adp-error", r.id);
                btn.textContent = "View error";
                msg.parentElement.appendChild(document.createTextNode(" "));
                msg.parentElement.appendChild(btn);
            }
            if (btn) btn.setAttribute("data-job", r.job_name);
        } else if (btn) {
            btn.remove();
        }
        tr.setAttribute("data-search", [r.job_name, text(tr.querySelector(".adp-convlog__job small")), r.message || "", r.status].join(" ").toLowerCase());
    };
    const refresh = () => {
        const url = root.getAttribute("data-list-url");
        const applicant = root.getAttribute("data-applicant");
        if (!url || !latestTable || !window.axios) return Promise.resolve(pendingRows());
        return window.axios.get(url, { params: { applicantId: applicant, size: "true" } }).then((res) => {
            const data = (res.data && res.data.data) || [];
            const batch = latestTable.getAttribute("data-batch");
            data.filter((r) => r.batch_id === batch).forEach(patchRow);
            applyFilter();
            return paintSummary();
        }).catch(() => pendingRows());
    };
    let timer = null;
    const tick = () => {
        timer = null;
        refresh().then((pending) => { if (pending > 0) timer = window.setTimeout(tick, 5000); });
    };
    if (pendingRows() > 0) timer = window.setTimeout(tick, 5000);
    document.addEventListener("visibilitychange", () => {
        if (document.visibilityState === "visible" && !timer && pendingRows() > 0) tick();
    });
})();
