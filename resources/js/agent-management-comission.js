import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var agentComissionListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let semester_id = $("#agentComissionListTable").attr('data-semester') != "" ? $("#agentComissionListTable").attr('data-semester') : "";
        let agent_id = $("#agentComissionListTable").attr('data-agent') != "" ? $("#agentComissionListTable").attr('data-agent') : "";
        let code = $("#agentComissionListTable").attr('data-code') != "" ? $("#agentComissionListTable").attr('data-code') : "";

        let tableContent = new Tabulator("#agentComissionListTable", {
            ajaxURL: route("agent.management.comission.list"),
            ajaxParams: { semester_id: semester_id, agent_id: agent_id, code: code },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: "80",
                },
                {
                    title: "REG. No",
                    field: "registration_no",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().registration_no+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().application_no+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Student",
                    field: "full_name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().full_name+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().date_of_birth+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "SSN",
                    field: "ssn_no",
                    headerHozAlign: "left",
                },
                {
                    title: "Course / Status",
                    field: "status",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().status+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-normal">'+cell.getData().course+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Course Fees",
                    field: "course_fees",
                    headerHozAlign: "left",
                },
                {
                    title: "Claimed",
                    field: "claimed_amount",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = cell.getData().claimed_amount;
                        if(cell.getData().claimed_count > 0){
                            html += ' ('+cell.getData().claimed_count+')';
                        }
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
    agentComissionListTable.init();
})()