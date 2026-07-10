import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

// ── Helpers ──────────────────────────────────────────────────────────────────

const renderLucideIcons = () => {
    createIcons({
        icons,
        "stroke-width": 1.5,
        nameAttr: "data-lucide",
    });
};

const logValue = (value) => {
    return (value !== null && value !== undefined && String(value).trim() !== "") ? value : "&mdash;";
};

function actorTypeBadge(type) {
    if (type === "user") {
        return '<span class="ep-doc-badge ep-doc-badge--blue">Staff</span>';
    }
    if (type === "student_user") {
        return '<span class="ep-doc-badge ep-doc-badge--gold">Student</span>';
    }
    return logValue(type);
}

function reasonBadge(reason) {
    if (!reason) {
        return '<span class="ep-doc-badge ep-doc-badge--green">Active</span>';
    }
    const map = {
        manual_logout:       ["ep-doc-badge--slate", "Manual Logout"],
        session_timeout:     ["ep-doc-badge--amber", "Timeout"],
        session_invalidated: ["ep-doc-badge--red", "Invalidated"],
    };
    const [cls, label] = map[reason] || ["ep-doc-badge--slate", reason];
    return `<span class="ep-doc-badge ${cls}">${label}</span>`;
}

// ── Table ────────────────────────────────────────────────────────────────────

var loginLogTable = (function () {
    let currentTotalRows = 0;
    var tableContent;

    var _tableGen = function () {
        var actor_id      = $("#actor_id").val()      || "";
        var actor_type    = $("#actor_type").val()    || "";
        var logout_reason = $("#logout_reason").val() || "";
        var date_from     = $("#date_from").val()     || "";
        var date_to       = $("#date_to").val()       || "";

        tableContent = new Tabulator("#loginLogTable", {
            ajaxURL: route("login-log.list.by.actor"),
            ajaxParams: {
                actor_id:      actor_id,
                actor_type:    actor_type,
                logout_reason: logout_reason,
                date_from:     date_from,
                date_to:       date_to,
            },
            ajaxFiltering:       true,
            ajaxSorting:         true,
            printAsHtml:         true,
            printStyled:         true,
            pagination:          "remote",
            paginationSize:      20,
            paginationSizeSelector: [true, 10, 20, 50, 100],
            layout:              "fitColumns",
            responsiveLayout:    false,
            placeholder:         "No matching records found",
            ajaxResponse(url, params, response) {
                currentTotalRows = Number(response.total_rows || 0);
                const summaryEl = document.querySelector("#loginLogSummary");
                if (summaryEl) {
                    summaryEl.textContent = currentTotalRows > 0
                        ? `${currentTotalRows} session${currentTotalRows === 1 ? "" : "s"} on record`
                        : "Sign-in history and active sessions recorded for this employee.";
                }
                return response;
            },
            columns: [
                {
                    title: "#",
                    field: "sl",
                    width: 48,
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Actor",
                    field: "actor_name",
                    headerHozAlign: "left",
                    minWidth: 160,
                    formatter(cell) {
                        var d = cell.getData();
                        return `
                            <div class="ep-doc-usercell">
                                <div class="ep-doc-usercell__name">${logValue(d.actor_name)}</div>
                                <div class="ep-doc-usercell__meta">${logValue(d.actor_email)}</div>
                            </div>
                        `;
                    },
                },
                {
                    title: "Type",
                    field: "actor_type",
                    width: 92,
                    headerHozAlign: "center",
                    hozAlign: "center",
                    headerSort: false,
                    formatter(cell) {
                        return actorTypeBadge(cell.getValue());
                    },
                },
                {
                    title: "Login Time",
                    field: "login_at",
                    headerHozAlign: "left",
                    width: 145,
                    formatter(cell) {
                        return `<span class="ep-doc-arccell__field">${logValue(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Logout Time",
                    field: "logout_at",
                    headerHozAlign: "left",
                    width: 145,
                    formatter(cell) {
                        var v = cell.getValue();
                        return v
                            ? `<span class="ep-doc-arccell__field">${v}</span>`
                            : '<span class="ep-doc-badge ep-doc-badge--green">Online</span>';
                    },
                },
                {
                    title: "Duration",
                    field: "duration",
                    headerSort: false,
                    width: 84,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    formatter(cell) {
                        return `<span class="ep-doc-usercell__meta">${logValue(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Status / Reason",
                    field: "logout_reason",
                    headerSort: false,
                    width: 130,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    formatter(cell) {
                        return reasonBadge(cell.getValue());
                    },
                },
                {
                    title: "IP Address",
                    field: "ip_address",
                    headerHozAlign: "left",
                    width: 118,
                    formatter(cell) {
                        return `<span class="ep-doc-arccell__field">${logValue(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Device & Browser",
                    field: "device",
                    headerHozAlign: "left",
                    minWidth: 150,
                    headerSort: false,
                    formatter(cell) {
                        var d = cell.getData();
                        if (!d.device && !d.platform && !d.browser) return '<span class="ep-doc-usercell__meta">&mdash;</span>';
                        var main = d.device ? `<div class="ep-doc-usercell__name">${d.device}</div>` : "";
                        var subParts = [d.platform, d.browser].filter(Boolean).join(" &middot; ");
                        var sub = subParts ? `<div class="ep-doc-usercell__meta">${subParts}</div>` : "";
                        return `<div class="ep-doc-usercell">${main}${sub}</div>`;
                    },
                },
                {
                    title: "Location",
                    field: "country",
                    headerHozAlign: "left",
                    minWidth: 120,
                    headerSort: false,
                    formatter(cell) {
                        var d = cell.getData();
                        if (!d.country && !d.city) return '<span class="ep-doc-usercell__meta">&mdash;</span>';
                        var parts = [d.city, d.country].filter(Boolean).join(", ");
                        return `<span class="ep-doc-arccell__field">${parts}</span>`;
                    },
                },
            ],
            renderComplete() {
                renderLucideIcons();
            },
        });

        // ── Export & Print ──
        $("#tabulator-export-csv").on("click", function () {
            tableContent.download("csv", "login-log.csv");
        });

        $("#tabulator-export-xlsx").on("click", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "login-log.xlsx", {
                sheetName: "Login Log",
            });
        });

        $("#tabulator-print").on("click", function () {
            tableContent.print();
        });
    };

    return {
        init: function () {
            _tableGen();
        },
    };
})();

// ── Boot ─────────────────────────────────────────────────────────────────────

(function () {
    if ($("#loginLogTable").length) {
        loginLogTable.init();

        function filterHTMLForm() {
            loginLogTable.init();
        }

        // Enter key in filter form
        $("#tabulatorFilterForm")[0].addEventListener("keypress", function (e) {
            var keycode = e.keyCode ? e.keyCode : e.which;
            if (keycode == "13") {
                e.preventDefault();
                filterHTMLForm();
            }
        });

        // Go button
        $("#tabulator-html-filter-go").on("click", function () {
            filterHTMLForm();
        });

        // Reset button
        $("#tabulator-html-filter-reset").on("click", function () {
            $("#querystr").val("");
            $("#logout_reason").val("");
            $("#date_from").val("");
            $("#date_to").val("");
            filterHTMLForm();
        });
    }
})();
