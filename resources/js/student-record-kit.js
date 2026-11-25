import xlsx from "xlsx";

/*
 * Small helpers shared by the redesigned student record cards (Notes,
 * Documents): escaping and search highlighting, dates, initials avatars, the
 * toast, and the print / CSV / XLSX output each card offers for what it is
 * currently showing.
 */
("use strict");

export const esc = (s) =>
    String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));

/** "2026-10-06" → "6 Oct 2026"; "" for anything that is not a date. */
export const fmtDate = (d) => {
    if (!d) return "";
    const dt = new Date(d + "T00:00:00");
    return isNaN(dt) ? "" : dt.toLocaleDateString("en-GB", { day: "numeric", month: "short", year: "numeric" });
};

export const plural = (n, w) => `${n} ${w}${n === 1 ? "" : "s"}`;

export const initials = (n) => {
    const p = String(n || "").split(/\s+/).filter((w) => w.length > 1);
    if (!p.length) return "?";
    return (p[0][0] + (p.length > 1 ? p[p.length - 1][0] : "")).toUpperCase();
};

const TINTS = [["#e4f3f4", "#0f7c86"], ["#fdf1d7", "#8a5a00"], ["#ece9fb", "#5b46b5"], ["#e3eefb", "#1f5fa8"], ["#fbe9ee", "#9b1c3a"]];

/** A round initials badge, tinted the same way for the same name everywhere. */
export const avatar = (name, className) => {
    const t = TINTS[[...String(name)].reduce((a, c) => a + c.charCodeAt(0), 0) % TINTS.length];
    return `<span class="${className}" aria-hidden="true" style="background:${t[0]};color:${t[1]}">${esc(initials(name))}</span>`;
};

/** Escape text and wrap each match of the (lower-cased) query in <mark>. */
export const hl = (text, q) => {
    text = String(text ?? "");
    if (!q) return esc(text);
    const low = text.toLowerCase();
    let out = "", i = 0, j;
    while ((j = low.indexOf(q, i)) !== -1) {
        out += esc(text.slice(i, j)) + "<mark>" + esc(text.slice(j, j + q.length)) + "</mark>";
        i = j + q.length;
    }
    return out + esc(text.slice(i));
};

/** localStorage under a namespace; quietly does nothing where storage is blocked. */
export const memory = (ns) => (k, v) => {
    try {
        if (v === undefined) return localStorage.getItem(ns + "." + k);
        localStorage.setItem(ns + "." + k, v);
    } catch (e) {
        return null;
    }
    return null;
};

/** toast(msg, isError) on a fixed element styled with .is-on / .is-error. */
export const makeToast = (el) => {
    let timer;
    return (msg, isError) => {
        if (!el) return;
        el.textContent = msg;
        el.classList.toggle("is-error", !!isError);
        el.classList.add("is-on");
        clearTimeout(timer);
        timer = setTimeout(() => el.classList.remove("is-on"), isError ? 4000 : 2200);
    };
};

/**
 * Print a plain table in a hidden frame, so only the list prints and not the
 * page around it. `rows` hold already-escaped HTML cells; `wrapCols` are the
 * column indexes whose text keeps its line breaks.
 */
export function printTable({ title, subtitle, headers, rows, wrapCols = [] }) {
    const body = rows.map((r) => `<tr>${r.map((c, i) => `<td${wrapCols.includes(i) ? ' class="t"' : ""}>${c}</td>`).join("")}</tr>`).join("");
    const html = `<!doctype html><html><head><meta charset="utf-8"><title>${esc(title)}</title><style>
        body{font:12px/1.45 "Public Sans",Arial,sans-serif;color:#1c2530;margin:24px}
        h1{font-size:16px;margin:0 0 2px}p{margin:0 0 14px;color:#5f6b78}
        table{width:100%;border-collapse:collapse}th,td{border:1px solid #d9e0e4;padding:6px 8px;text-align:left;vertical-align:top}
        th{background:#f3f6f7;font-size:10.5px;text-transform:uppercase;letter-spacing:.05em}
        td.t{white-space:pre-line}.m{margin-top:4px;color:#5f6b78;font-size:11px}
        tr{page-break-inside:avoid}</style></head><body>
        <h1>${esc(title)}</h1><p>${esc(subtitle)} · printed ${esc(new Date().toLocaleString("en-GB"))}</p>
        <table><thead><tr>${headers.map((h) => `<th>${esc(h)}</th>`).join("")}</tr></thead><tbody>${body}</tbody></table></body></html>`;
    const frame = document.createElement("iframe");
    frame.setAttribute("aria-hidden", "true");
    frame.style.cssText = "position:fixed;right:0;bottom:0;width:0;height:0;border:0;";
    document.body.appendChild(frame);
    const doc = frame.contentWindow.document;
    doc.open();
    doc.write(html);
    doc.close();
    const cleanup = () => setTimeout(() => frame.remove(), 500);
    frame.contentWindow.addEventListener("afterprint", cleanup);
    setTimeout(() => {
        frame.contentWindow.focus();
        frame.contentWindow.print();
        setTimeout(cleanup, 60000);
    }, 150);
}

/**
 * Save rows (plain objects, keys are the column headings) as CSV or XLSX.
 * Written to memory and saved through a link: xlsx.writeFile reaches for
 * Node's fs first in this bundle.
 */
export function downloadSheet({ rows, sheet, base, kind }) {
    const wb = xlsx.utils.book_new();
    xlsx.utils.book_append_sheet(wb, xlsx.utils.json_to_sheet(rows), sheet.slice(0, 31));
    const csv = kind === "csv";
    const blob = csv
        ? new Blob(["﻿" + xlsx.utils.sheet_to_csv(wb.Sheets[wb.SheetNames[0]])], { type: "text/csv;charset=utf-8" })
        : new Blob([xlsx.write(wb, { bookType: "xlsx", type: "array" })], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `${base}.${csv ? "csv" : "xlsx"}`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}

/** Page buttons: Prev, 1 … p-1 p p+1 … last, Next. */
export function pagerHtml(page, pages, gapClass) {
    if (pages <= 1) return "";
    const nums = [...new Set([1, pages, page - 1, page, page + 1].filter((x) => x >= 1 && x <= pages))].sort((a, b) => a - b);
    let html = `<button type="button" data-page="${page - 1}" ${page <= 1 ? "disabled" : ""}>Prev</button>`;
    let last = 0;
    nums.forEach((x) => {
        if (x - last > 1) html += `<span class="${gapClass}" aria-hidden="true">…</span>`;
        html += `<button type="button" data-page="${x}"${x === page ? ' aria-current="page"' : ""} aria-label="Page ${x}">${x}</button>`;
        last = x;
    });
    return html + `<button type="button" data-page="${page + 1}" ${page >= pages ? "disabled" : ""}>Next</button>`;
}

/** A small popover menu (Export, Add Document): toggles, closes outside / on Escape. */
export function bindMenu(button, menu) {
    const set = (open) => {
        menu.hidden = !open;
        button.setAttribute("aria-expanded", String(open));
        if (open) menu.querySelector("button")?.focus();
    };
    button.addEventListener("click", (e) => { e.stopPropagation(); set(menu.hidden); });
    document.addEventListener("click", (e) => { if (!menu.hidden && !menu.contains(e.target) && e.target !== button) set(false); });
    menu.addEventListener("keydown", (e) => { if (e.key === "Escape") { set(false); button.focus(); } });
    return { close: () => set(false) };
}
