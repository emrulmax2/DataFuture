import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var agentComissionDetailsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let comission_id = $("#agentComissionDetailsListTable").attr('data-comission') != "" ? $("#agentComissionDetailsListTable").attr('data-comission') : "";

        let tableContent = new Tabulator("#agentComissionDetailsListTable", {
            ajaxURL: route("agent.management.comission.details.list"),
            ajaxParams: { comission_id: comission_id},
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
                {
                    title: "Money Receipt Ref.",
                    field: "invoice_no",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Receipt Amount",
                    field: "receipt_amount",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Receipt Date",
                    field: "payment_date",
                    headerHozAlign: "left",
                    headerSort: false,
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
    if($('#agentComissionDetailsListTable').length > 0){
        agentComissionDetailsListTable.init();
    }
})()