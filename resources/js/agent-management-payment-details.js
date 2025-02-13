import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var agentRemittPaymenDetailstsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let transactionid = $("#agentRemittPaymenDetailstsListTable").attr('data-transactionid') != "" ? $("#agentRemittPaymenDetailstsListTable").attr('data-transactionid') : "";

        let tableContent = new Tabulator("#agentRemittPaymenDetailstsListTable", {
            ajaxURL: route("agent.management.remittances.payment.details.list"),
            ajaxParams: { transactionid: transactionid},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: true,
            paginationSizeSelector: [true, 50, 100, 150, 200, 300, 400, 500],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: "80",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        var html = cell.getData().id;

                        return html;
                    }
                },
                {
                    title: "Remittance Ref",
                    field: "remittance_ref",
                    headerSort: false,
                    width: "160",
                    formatter(cell, formatterParams){
                        var html = cell.getData().remittance_ref;

                        return html;
                    }
                },
                {
                    title: "Student Ref",
                    field: "application_no",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().application_no+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().registration_no+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Name",
                    field: "full_name",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Course",
                    field: "course",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="text-slate-500 text-xs whitespace-normal break-all">'+cell.getData().course+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Amount",
                    field: "amount",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap '+(cell.getData().comission_for == 'Refund' ? 'text-danger' : '')+'">'+cell.getData().amount+'</div>';
                        html += '</div>';

                        return html;
                    }
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

        // Redraw table onresize
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

(function(){
    agentRemittPaymenDetailstsListTable.init();
})();