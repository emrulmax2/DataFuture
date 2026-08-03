import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

const escapeHtml = (value) => {
    const div = document.createElement("div");
    div.textContent = value == null ? "" : String(value);
    return div.innerHTML;
};

const statusTone = (status) => {
    const normalized = String(status || "").toLowerCase();

    if (/(completed|active|progress|current)/.test(normalized)) {
        return "is-green";
    }

    if (/(refused|withdrawn|discard|reject|cancel)/.test(normalized)) {
        return "is-red";
    }

    return "is-slate";
};

const amountWithCount = (amount, count) => {
    const safeAmount = escapeHtml(amount || "£0.00");
    const safeCount = Number(count || 0);

    return `${safeAmount}${safeCount > 0 ? ` <small>(${safeCount})</small>` : ""}`;
};

var agentComissionListTable = (function () {
    var _tableGen = function () {
        const $table = $("#agentComissionListTable");
        const semester_id = $table.attr("data-semester") || "";
        const agent_id = $table.attr("data-agent") || "";
        const code = $table.attr("data-code") || "";
        const listUrl = route("agent.management.comission.list");
        let totalRows = 0;

        const getParams = () => ({
            semester_id,
            agent_id,
            code,
            query: ($("#query").val() || "").trim(),
        });

        let tableContent;

        const syncFooter = () => {
            window.requestAnimationFrame(() => {
                const tableElement = $table.get(0);
                if (!tableElement || !tableContent) return;

                const paginator = tableElement.querySelector(".tabulator-paginator");
                const label = paginator?.querySelector("label");
                const pageSize = paginator?.querySelector(".tabulator-page-size");

                if (!paginator || !label || !pageSize) return;

                label.textContent = "Rows";

                let range = paginator.querySelector(".agm-commission-page-range");
                if (!range) {
                    range = document.createElement("span");
                    range.className = "agm-commission-page-range";
                    pageSize.insertAdjacentElement("afterend", range);
                }

                const currentPage = Number(tableContent.getPage ? tableContent.getPage() : 1) || 1;
                const rawSize = tableContent.getPageSize ? tableContent.getPageSize() : 10;
                const pageSizeValue = rawSize === true ? totalRows : Number(rawSize) || totalRows || 10;
                const start = totalRows > 0 ? ((currentPage - 1) * pageSizeValue) + 1 : 0;
                const end = totalRows > 0 ? Math.min(currentPage * pageSizeValue, totalRows) : 0;

                range.textContent = `${start}–${end} of ${totalRows}`;
            });
        };

        tableContent = new Tabulator("#agentComissionListTable", {
            ajaxURL: listUrl,
            ajaxParams: getParams(),
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50, 100, true],
            layout: "fitColumns",
            responsiveLayout: false,
            placeholder: "No matching records found",
            selectable: true,
            columns: [
                {
                    formatter: "rowSelection",
                    titleFormatter: "rowSelection",
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: 56,
                    headerSort: false,
                    download: false,
                    cellClick: function (e, cell) {
                        cell.getRow().toggleSelect();
                    },
                },
                {
                    title: "#ID",
                    field: "id",
                    width: 72,
                    formatter(cell) {
                        const id = escapeHtml(cell.getData().id);

                        return `<span class="agm-commission-id">${id}<input type="hidden" name="ids" class="ids" value="${id}"></span>`;
                    },
                },
                {
                    title: "REG. No",
                    field: "registration_no",
                    minWidth: 145,
                    widthGrow: 1,
                    formatter(cell) {
                        const row = cell.getData();

                        return `
                            <div class="agm-commission-main">
                                <strong>${escapeHtml(row.registration_no)}</strong>
                                <small>${escapeHtml(row.application_no)}</small>
                            </div>
                        `;
                    },
                },
                {
                    title: "Student",
                    field: "full_name",
                    headerSort: false,
                    minWidth: 205,
                    widthGrow: 2,
                    formatter(cell) {
                        const row = cell.getData();

                        return `
                            <div class="agm-commission-main">
                                <strong>${escapeHtml(row.full_name)}</strong>
                                <small>${escapeHtml(row.date_of_birth)}</small>
                            </div>
                        `;
                    },
                },
                {
                    title: "SSN",
                    field: "ssn_no",
                    minWidth: 128,
                    widthGrow: 1,
                    formatter(cell) {
                        return `<span class="agm-commission-muted">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Course / Status",
                    field: "status",
                    headerSort: false,
                    minWidth: 260,
                    widthGrow: 2,
                    formatter(cell) {
                        const row = cell.getData();
                        const status = escapeHtml(row.status);

                        return `
                            <div class="agm-commission-course">
                                <span class="agm-commission-status ${statusTone(row.status)}"><i></i>${status}</span>
                                <small>${escapeHtml(row.course)}</small>
                            </div>
                        `;
                    },
                },
                {
                    title: "Course Fees",
                    field: "course_fees",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    minWidth: 105,
                    widthGrow: 1,
                    formatter(cell) {
                        return `<span class="agm-commission-money">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Claimed",
                    field: "claimed_amount",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    minWidth: 118,
                    widthGrow: 1,
                    formatter(cell) {
                        const row = cell.getData();

                        return `<span class="agm-commission-money agm-commission-money--strong">${amountWithCount(row.claimed_amount, row.claimed_count)}</span>`;
                    },
                },
                {
                    title: "Received",
                    field: "receipt_amount",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    minWidth: 118,
                    widthGrow: 1,
                    formatter(cell) {
                        const row = cell.getData();

                        return `<span class="agm-commission-money agm-commission-money--green">${amountWithCount(row.receipt_amount, row.receipt_count)}</span>`;
                    },
                },
            ],
            ajaxResponse: function (url, params, response) {
                totalRows = response.all_rows && response.all_rows > 0 ? response.all_rows : 0;

                $("#noOfStdCount")
                    .attr("data-total", totalRows)
                    .html(totalRows > 0 ? `${totalRows} Students` : "0");

                return response;
            },
            pageLoaded() {
                syncFooter();
            },
            renderComplete() {
                syncFooter();
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            },
            rowSelectionChanged: function (data, rows) {
                const $button = $("#generateComissionBtn");

                if (rows.length > 0) {
                    $button.css("display", "inline-flex");
                } else {
                    $button.hide();
                }
            },
            selectableCheck: function (row) {
                return row.getData().id > 0;
            },
        });

        const reloadTable = () => {
            const $button = $("#tabulator-html-filter-go");

            $button.attr("disabled", "disabled");
            $button.find("svg.theLoader").fadeIn();

            const request = tableContent.setData(listUrl, getParams());
            const resetButton = () => {
                $button.removeAttr("disabled");
                $button.find("svg.theLoader").fadeOut();
            };

            if (request && typeof request.finally === "function") {
                request.finally(resetButton);
            } else {
                resetButton();
            }
        };

        $("#tabulatorFilterForm").on("submit", function (event) {
            event.preventDefault();
            reloadTable();
        });

        $("#tabulator-html-filter-reset").on("click", function (event) {
            event.preventDefault();
            $("#query").val("");
            reloadTable();
        });

        window.addEventListener("resize", () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });
    };

    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function () {
    agentComissionListTable.init();

    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const comissionGenerateModalEl = document.getElementById("comissionGenerateModal");

    if (comissionGenerateModalEl) {
        comissionGenerateModalEl.addEventListener("hide.tw.modal", function () {
            $("#comissionGenerateModal .acc__input-error").html("");
            $("#comissionGenerateModal #comissionsPaymentTable tbody").html("");
            $("#comissionGenerateModal [name=\"agent_comission_rule_id\"]").val("0");
        });
    }

    $("#generateComissionBtn").on("click", function (e) {
        e.preventDefault();
        var $theBtn = $(this);
        var agentcomissionruleid = $theBtn.attr("data-comissionruleid");
        $theBtn.find("svg.theLoader").fadeIn();
        $theBtn.attr("disabled", "disabled");

        var studentids = [];
        $("#agentComissionListTable").find(".tabulator-row.tabulator-selected").each(function () {
            var $row = $(this);
            studentids.push($row.find(".ids").val());
        });

        if (studentids.length > 0) {
            axios({
                method: "post",
                url: route("agent.management.get.payable.comissions"),
                data: { agentcomissionruleid: agentcomissionruleid, studentids: studentids },
                headers: {
                    "X-CSRF-TOKEN": $("meta[name=\"csrf-token\"]").attr("content"),
                },
            }).then((response) => {
                $theBtn.find("svg.theLoader").fadeOut();
                $theBtn.removeAttr("disabled");
                if (response.status == 200) {
                    window.location.href = response.data.url;
                }
            }).catch((error) => {
                $theBtn.find("svg.theLoader").fadeOut();
                $theBtn.removeAttr("disabled");
                if (error.response) {
                    if (error.response.status == 422) {
                        warningModal.show();
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function () {
                            $("#warningModal .warningModalTitle").html("Error!");
                            $("#warningModal .warningModalDesc").html(error.response.data.msg);
                        });

                        setTimeout(() => {
                            warningModal.hide();
                        }, 2000);
                    } else {
                        console.log("error");
                    }
                }
            });
        } else {
            $theBtn.find("svg.theLoader").fadeOut();
            $theBtn.removeAttr("disabled");
        }
    });
})();
