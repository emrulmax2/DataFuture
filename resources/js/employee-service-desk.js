/*
 * Service Desk tickets on the employee notes page.
 *
 * The tickets an employee has been tagged on in Operations, drawn as the same
 * Tabulator table as the Notes list above it so the two cards on the page are
 * one design. The rows arrive with the page; nothing is fetched to draw them.
 *
 * Opening a ticket — the panel, and the PIN in front of a confidential one — is
 * student-service-desk.js, imported here rather than copied: it is the same
 * rule on both records, and should stay one piece of code. It opens whatever
 * row carries `.sd-ticket-row` and a ticket id, which is what rowFormatter
 * below gives each row.
 */
import Tabulator from "tabulator-tables";
import { createIcons, icons } from "lucide";
import "./student-service-desk.js";

("use strict");

(function () {
    const tableEl = document.getElementById("employeeServiceDeskTable");

    if (!tableEl) {
        return;
    }

    let tickets = [];
    try {
        tickets = JSON.parse(tableEl.dataset.tickets || "[]");
    } catch (error) {
        tickets = [];
    }

    const renderLucideIcons = () => {
        createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
    };

    /* Subjects and names are typed by people in another system. Everything
       built into a cell's HTML goes through this first. */
    const esc = (value) => String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

    const pluralize = (count, singular) => singular + (count === 1 ? "" : "s");

    /* The Operations statuses in this profile's own badge colours. */
    const statusBadge = (status) => ({
        new: "ep-doc-badge--blue",
        assigned: "ep-doc-badge--blue",
        in_progress: "ep-doc-badge--amber",
        resolved: "ep-doc-badge--green",
        reopened: "ep-doc-badge--red",
    }[status] || "ep-doc-badge--slate");

    const buildSubjectCell = (data) => {
        let meta = "";

        if (data.is_confidential) {
            /* The subject is listed; the conversation behind it is not until
               the reader has given their PIN. */
            meta = `<span class="ep-doc-badge ep-doc-badge--slate ep-sd-lock"><i data-lucide="lock" class="w-3 h-3"></i>Confidential</span>`;
        } else if (Number(data.messages_count) > 0) {
            meta = `<span class="ep-sd-count">${Number(data.messages_count)} ${pluralize(Number(data.messages_count), "message")}</span>`;
        }

        return `
            <div class="ep-sd-subject">
                <div class="ep-doc-note-preview">${esc(data.subject)}</div>
                ${meta}
            </div>
        `;
    };

    const buildSentToCell = (data) => `
        <div class="ep-doc-usercell">
            <div class="ep-doc-usercell__name">${esc(data.department || data.target || "—")}</div>
            ${data.issue_type ? `<div class="ep-doc-usercell__meta">${esc(data.issue_type)}</div>` : ""}
        </div>
    `;

    const buildRaisedByCell = (data) => `
        <div class="ep-doc-usercell">
            <div class="ep-doc-usercell__name">${esc(data.raised_by || "—")}</div>
            <div class="ep-doc-usercell__meta">${esc(data.raised_ago || "")}</div>
        </div>
    `;

    const buildStatusCell = (data) => {
        const badges = [`<span class="ep-doc-badge ${statusBadge(data.status)}">${esc(data.status_label)}</span>`];

        if (data.is_overdue) {
            badges.push(`<span class="ep-doc-badge ep-doc-badge--red">Overdue</span>`);
        }

        return `<div class="ep-sd-badges">${badges.join("")}</div>`;
    };

    /* A button for the row's one action, as the Notes table has — though the
       whole row opens the ticket too. */
    const buildActions = (data) => `
        <div class="ep-doc-action-group">
            <button type="button" class="ep-doc-action-btn ep-doc-action-btn--view"
                    title="${data.is_confidential ? "Open with your document PIN" : "View ticket"}">
                <i data-lucide="${data.is_confidential ? "lock-keyhole" : "eye"}" class="w-4 h-4"></i>
            </button>
        </div>
    `;

    const updateSummary = (total) => {
        const summaryEl = document.getElementById("employeeServiceDeskSummary");

        if (summaryEl && total > 0) {
            const open = tickets.filter((ticket) => ticket.is_open).length;

            summaryEl.textContent = `${total} ${pluralize(total, "ticket")} tagged to this employee`
                + (open > 0 ? ` · ${open} still open` : "");
        }
    };

    /* "Showing 1-10 of 23 tickets", beside the page buttons, as on Notes. */
    const updateFooterMeta = (table) => {
        /* Guarded the way the Notes table guards it: the installed Tabulator
           keeps its root on `.element` and has no footer-contents wrapper, so
           on this version there is simply no counter — on either table. */
        const rootEl = typeof table.getElement === "function" ? table.getElement() : table.element;
        const footerEl = rootEl ? rootEl.querySelector(".tabulator-footer .tabulator-footer-contents") : null;

        if (!footerEl) {
            return;
        }

        let counterEl = footerEl.querySelector(".tabulator-page-counter");

        if (!counterEl) {
            counterEl = document.createElement("div");
            counterEl.className = "tabulator-page-counter";
            footerEl.prepend(counterEl);
        }

        const total = tickets.length;
        const size = Number(table.getPageSize()) || total;
        const page = Number(table.getPage()) || 1;
        const start = total === 0 ? 0 : ((page - 1) * size) + 1;
        const end = Math.min(total, page * size);

        counterEl.textContent = total > 0
            ? `Showing ${start}-${end} of ${total} ${pluralize(total, "ticket")}`
            : "Showing 0 of 0 tickets";
    };

    const table = new Tabulator("#employeeServiceDeskTable", {
        data: tickets,
        pagination: "local",
        paginationSize: 10,
        layout: "fitColumns",
        responsiveLayout: "collapse",
        placeholder: "No tickets have been tagged to this employee",
        columns: [
            {
                title: "Reference",
                field: "ref",
                headerHozAlign: "left",
                width: 150,
                /* The class the panel script reads a row's reference from. */
                cssClass: "is-ref",
            },
            {
                title: "Subject",
                field: "subject",
                headerHozAlign: "left",
                formatter(cell) {
                    return buildSubjectCell(cell.getData());
                },
            },
            {
                title: "Sent To",
                field: "department",
                headerHozAlign: "left",
                width: 210,
                formatter(cell) {
                    return buildSentToCell(cell.getData());
                },
            },
            {
                title: "Status",
                field: "status_label",
                headerHozAlign: "left",
                width: 170,
                formatter(cell) {
                    return buildStatusCell(cell.getData());
                },
            },
            {
                title: "Raised By",
                field: "raised_by",
                headerHozAlign: "left",
                width: 170,
                formatter(cell) {
                    return buildRaisedByCell(cell.getData());
                },
            },
            {
                title: "Updated",
                field: "updated_at",
                headerHozAlign: "left",
                width: 140,
                formatter(cell) {
                    return `<span class="ep-sd-when">${esc(cell.getData().updated_ago || "")}</span>`;
                },
            },
            {
                title: "Actions",
                field: "id",
                headerSort: false,
                hozAlign: "right",
                headerHozAlign: "right",
                width: 96,
                download: false,
                formatter(cell) {
                    return buildActions(cell.getData());
                },
            },
        ],
        rowFormatter(row) {
            const data = row.getData();
            const el = row.getElement();

            el.classList.add("sd-ticket-row");
            el.dataset.ticket = data.id;

            if (data.is_confidential) {
                el.dataset.confidential = "1";
                el.classList.add("sd-ticket-row--locked");
            }
        },
        renderComplete() {
            renderLucideIcons();
            updateFooterMeta(this);
            updateSummary(tickets.length);
        },
    });

    window.addEventListener("resize", () => {
        table.redraw();
        renderLucideIcons();
    });
})();
