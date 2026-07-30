import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

const escapeHtml = (value) => {
    const div = document.createElement("div");
    div.textContent = value == null ? "" : String(value);
    return div.innerHTML;
};

const avatarPalette = ["#0d7a76", "#6d4bb0", "#2f6ea5", "#2f8351", "#cf5926", "#9f2d47"];
const courseTones = ["is-teal", "is-red", "is-blue", "is-green", "is-gold"];

const initials = (value) => {
    const parts = String(value || "Student")
        .replace(/^Mr\s+|^Mrs\s+|^Miss\s+|^Ms\s+/i, "")
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    if (parts.length === 0) return "ST";

    return `${parts[0][0] || ""}${(parts[1] || parts[0])[0] || ""}`.toUpperCase();
};

const hashIndex = (value, length) => {
    const source = String(value || "");
    let hash = 0;

    for (let index = 0; index < source.length; index += 1) {
        hash = source.charCodeAt(index) + ((hash << 5) - hash);
    }

    return Math.abs(hash) % length;
};

const avatarColor = (value) => avatarPalette[hashIndex(value || "student", avatarPalette.length)];
const courseTone = (value) => courseTones[hashIndex(value || "course", courseTones.length)];

const refreshIcons = () => {
    createIcons({
        icons,
        "stroke-width": 1.8,
        nameAttr: "data-lucide",
    });
};

const renderStudentRef = (row) => `
    <div class="agm-commission-remit-ref">
        <strong>${escapeHtml(row.application_no || "Not set")}</strong>
        <small>${escapeHtml(row.registration_no || "")}</small>
    </div>
`;

const renderStudent = (row) => {
    const name = row.full_name || "Unknown Student";
    const photo = String(row.photo_url || "");
    const avatar = photo && !photo.startsWith("data:")
        ? `<span class="agm-commission-remit-avatar agm-commission-remit-avatar--photo" style="background:${avatarColor(name)}">
                <img src="${escapeHtml(photo)}" alt="${escapeHtml(name)}" onerror="this.remove();this.parentElement.classList.remove('agm-commission-remit-avatar--photo');this.parentElement.textContent='${escapeHtml(initials(name))}';">
           </span>`
        : `<span class="agm-commission-remit-avatar" style="background:${avatarColor(name)}">${escapeHtml(initials(name))}</span>`;

    return `
        <div class="agm-commission-remit-student">
            ${avatar}
            <strong>${escapeHtml(name)}</strong>
        </div>
    `;
};

const renderCourse = (row) => {
    const course = row.course || "Course not set";
    const tone = courseTone(course);

    return `
        <span class="agm-commission-course-pill ${tone}" title="${escapeHtml(course)}">
            <i></i>
            <span>${escapeHtml(course)}</span>
        </span>
    `;
};

const renderAmount = (row) => {
    const refund = String(row.comission_for || "").toLowerCase() === "refund";

    return `
        <span class="agm-commission-remit-money ${refund ? "is-refund" : ""}">
            ${escapeHtml(row.amount || "£0.00")}
        </span>
    `;
};

const renderReceiptRef = (value) => `
    <span class="agm-commission-receipt-ref">${escapeHtml(value || "Not set")}</span>
`;

const renderReceiptAmount = (value) => `
    <span class="agm-commission-remit-money is-receipt">${escapeHtml(value || "£0.00")}</span>
`;

var agentComissionDetailsListTable = (function () {
    let tableContent;
    let totalRows = 0;

    const $table = $("#agentComissionDetailsListTable");
    const listUrl = route("agent.management.comission.details.list");

    const syncFooter = () => {
        window.requestAnimationFrame(() => {
            const tableElement = $table.get(0);
            if (!tableElement || !tableContent) return;

            const footerContents = tableElement.querySelector(".tabulator-footer .tabulator-footer-contents");
            const paginator = tableElement.querySelector(".tabulator-paginator");

            if (!footerContents || !paginator) return;

            tableElement.classList.add("agm-footer-synced");
            footerContents.classList.add("agm-commission-footer-layout");

            const label = footerContents.querySelector(".agm-commission-page-size-group label")
                || Array.from(paginator.children || []).find((child) => child.tagName === "LABEL")
                || footerContents.querySelector("label");
            const pageSize = footerContents.querySelector(".tabulator-page-size");

            if (!label || !pageSize) return;

            label.textContent = "Page Size";

            let footerLeft = footerContents.querySelector(".agm-commission-footer-left");
            if (!footerLeft) {
                footerLeft = document.createElement("span");
                footerLeft.className = "agm-commission-footer-left";
            }

            let footerRight = footerContents.querySelector(".agm-commission-footer-right");
            if (!footerRight) {
                footerRight = document.createElement("span");
                footerRight.className = "agm-commission-footer-right";
            }
            footerContents.append(footerLeft, footerRight);
            footerRight.append(paginator);

            let pageSizeGroup = footerContents.querySelector(".agm-commission-page-size-group");
            if (!pageSizeGroup) {
                pageSizeGroup = document.createElement("span");
                pageSizeGroup.className = "agm-commission-page-size-group";
            }
            pageSizeGroup.append(label, pageSize);

            let range = footerContents.querySelector(".agm-commission-page-range");
            if (!range) {
                range = document.createElement("span");
                range.className = "agm-commission-page-range";
            }
            footerLeft.append(pageSizeGroup, range);

            let pageControls = footerContents.querySelector(".agm-commission-page-controls");
            if (!pageControls) {
                pageControls = document.createElement("span");
                pageControls.className = "agm-commission-page-controls";
            }

            ["first", "prev"].forEach((page) => {
                const button = paginator.querySelector(`.tabulator-page[data-page="${page}"]`)
                    || pageControls.querySelector(`.tabulator-page[data-page="${page}"]`);
                if (button) pageControls.append(button);
            });

            const pages = paginator.querySelector(".tabulator-pages") || pageControls.querySelector(".tabulator-pages");
            if (pages) pageControls.append(pages);

            ["next", "last"].forEach((page) => {
                const button = paginator.querySelector(`.tabulator-page[data-page="${page}"]`)
                    || pageControls.querySelector(`.tabulator-page[data-page="${page}"]`);
                if (button) pageControls.append(button);
            });

            paginator.append(pageControls);

            const currentPage = Number(tableContent.getPage ? tableContent.getPage() : 1) || 1;
            const rawSize = tableContent.getPageSize ? tableContent.getPageSize() : 10;
            const pageSizeValue = rawSize === true ? totalRows : Number(rawSize) || totalRows || 10;
            const start = totalRows > 0 ? ((currentPage - 1) * pageSizeValue) + 1 : 0;
            const end = totalRows > 0 ? Math.min(currentPage * pageSizeValue, totalRows) : 0;

            range.innerHTML = `Showing <b>${start}-${end}</b> of ${totalRows} students`;
        });
    };

    const _tableGen = function () {
        const comission_id = $table.attr("data-comission") || "";

        tableContent = new Tabulator("#agentComissionDetailsListTable", {
            ajaxURL: listUrl,
            ajaxParams: { comission_id },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50, 100, true],
            layout: "fitColumns",
            responsiveLayout: false,
            placeholder: "No matching students found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: 72,
                    headerSort: false,
                    formatter(cell) {
                        return `<span class="agm-commission-id">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Student Ref",
                    field: "application_no",
                    width: 135,
                    minWidth: 135,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return renderStudentRef(cell.getData());
                    },
                },
                {
                    title: "Name",
                    field: "full_name",
                    minWidth: 290,
                    widthGrow: 2,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return renderStudent(cell.getData());
                    },
                },
                {
                    title: "Course",
                    field: "course",
                    minWidth: 300,
                    widthGrow: 2,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return renderCourse(cell.getData());
                    },
                },
                {
                    title: "Amount",
                    field: "amount",
                    width: 120,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    headerSort: false,
                    formatter(cell) {
                        return renderAmount(cell.getData());
                    },
                },
                {
                    title: "Money Receipt Ref.",
                    field: "invoice_no",
                    width: 165,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return renderReceiptRef(cell.getValue());
                    },
                },
                {
                    title: "Receipt Amount",
                    field: "receipt_amount",
                    width: 150,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    headerSort: false,
                    formatter(cell) {
                        return renderReceiptAmount(cell.getValue());
                    },
                },
                {
                    title: "Receipt Date",
                    field: "payment_date",
                    width: 145,
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell) {
                        return `<span class="agm-commission-muted">${escapeHtml(cell.getValue() || "Not set")}</span>`;
                    },
                },
            ],
            ajaxResponse(url, params, response) {
                totalRows = response.all_rows && response.all_rows > 0 ? response.all_rows : 0;
                $("#comissionStudentCount").text(totalRows);
                return response;
            },
            pageLoaded() {
                syncFooter();
            },
            renderComplete() {
                syncFooter();
                refreshIcons();
            },
        });

        window.addEventListener("resize", () => {
            tableContent.redraw();
            refreshIcons();
        });
    };

    return {
        init() {
            _tableGen();
        },
    };
})();

(function () {
    if ($("#agentComissionDetailsListTable").length > 0) {
        agentComissionDetailsListTable.init();
    }

    refreshIcons();
})();
