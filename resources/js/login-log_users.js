import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

// ── Helpers ──────────────────────────────────────────────────────────────────

function actorTypeBadge(type) {
    if (type === "user") {
        return '<span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">Staff</span>';
    }
    if (type === "student_user") {
        return '<span class="px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">Student</span>';
    }
    return type;
}

function reasonBadge(reason) {
    if (!reason) {
        return '<span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Active</span>';
    }
    const map = {
        manual_logout:        ['bg-gray-100 text-gray-700', 'Manual Logout'],
        session_timeout:      ['bg-yellow-100 text-yellow-700', 'Timeout'],
        session_invalidated:  ['bg-red-100 text-red-700', 'Invalidated'],
    };
    const [cls, label] = map[reason] || ['bg-gray-100 text-gray-600', reason];
    return `<span class="px-2 py-0.5 rounded text-xs font-medium ${cls}">${label}</span>`;
}

// ── Table ────────────────────────────────────────────────────────────────────

var loginLogTable = (function () {
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
            responsiveLayout:    "collapse",
            placeholder:         "No records found",
            columns: [
                {
                    title: "#",
                    field: "sl",
                    width: 55,
                    headerSort: false,
                },
                {
                    title: "Actor",
                    field: "actor_name",
                    headerHozAlign: "left",
                    width: 250,
                    formatter(cell) {
                        var d = cell.getData();
                        return (
                            '<div class="font-medium whitespace-nowrap">' + d.actor_name + '</div>' +
                            '<div class="text-slate-400 text-xs">' + d.actor_email + '</div>'
                        );
                    },
                },
                {
                    title: "Type",
                    field: "actor_type",
                    width: 100,
                    headerHozAlign: "center",
                    hozAlign: "center",
                    formatter(cell) {
                        return actorTypeBadge(cell.getValue());
                    },
                },
                {
                    title: "Login Time",
                    field: "login_at",
                    headerHozAlign: "left",
                    width: 160,
                },
                {
                    title: "Logout Time",
                    field: "logout_at",
                    headerHozAlign: "left",
                    width: 160,
                    formatter(cell) {
                        return cell.getValue() || '<span class="text-green-600 font-medium">Online</span>';
                    },
                },
                {
                    title: "Duration",
                    field: "duration",
                    headerSort: false,
                    width: 90,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    formatter(cell) {
                        return cell.getValue() || "—";
                    },
                },
                {
                    title: "Status / Reason",
                    field: "logout_reason",
                    headerSort: false,
                    width: 150,
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
                    width: 130,
                },
                {
                    title: "Device & Browser",
                    field: "device",
                    headerHozAlign: "left",
                    minWidth: 160,
                    headerSort: false,
                    formatter(cell) {
                        var d = cell.getData();
                        var lines = [];
                        if (d.device)   lines.push('<div class="font-medium">'   + d.device   + '</div>');
                        if (d.platform) lines.push('<div class="text-xs text-slate-500">' + d.platform + '</div>');
                        if (d.browser)  lines.push('<div class="text-xs text-slate-400">' + d.browser  + '</div>');
                        return lines.length ? lines.join('') : '—';
                    },
                },
                {
                    title: "Location",
                    field: "country",
                    headerHozAlign: "left",
                    minWidth: 130,
                    headerSort: false,
                    formatter(cell) {
                        var d = cell.getData();
                        if (!d.country && !d.city) return '—';
                        var parts = [d.city, d.country].filter(Boolean);
                        return '<div class="whitespace-nowrap">' + parts.join(', ') + '</div>';
                    },
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
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
            $("#actor_type").val("");
            $("#logout_reason").val("");
            $("#date_from").val("");
            $("#date_to").val("");
            filterHTMLForm();
        });
    }
})();
