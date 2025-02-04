import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Litepicker from "litepicker";


("use strict");
var agentRemittanceListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "1";

        let tableContent = new Tabulator("#agentRemittanceListTable", {
            ajaxURL: route("agent.management.remittances.payment.list"),
            ajaxParams: { querystr: querystr, status: status },
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
            selectable:true,
            columns: [
                {
                    formatter: "rowSelection", 
                    titleFormatter: "rowSelection", 
                    hozAlign: "left", 
                    headerHozAlign: "left",
                    width: "60",
                    headerSort: false, 
                    download: false,
                    cellClick:function(e, cell){
                        cell.getRow().toggleSelect();
                    }
                },
                {
                    title: "#ID",
                    field: "id",
                    width: "80",
                },
                {
                    title: "Remittance Ref.",
                    field: "remittance_ref",
                    headerHozAlign: "left",
                },
                {
                    title: "Created Date",
                    field: "entry_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Intake",
                    field: "semester",
                    headerHozAlign: "left",
                },
                {
                    title: "Refferal Name",
                    field: "agent_name",
                    headerHozAlign: "left",
                    headerSort: false,
                    headerSort: false,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().agent_name+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().organization+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Total Amount",
                    field: "amount_html",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                /*{
                    title: "Transaction",
                    field: "transaction_code",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        var html = '';
                        if(cell.getData().acc_transaction_id > 0 && cell.getData().transaction_code != '' && cell.getData().transaction_date != ''){
                            html += '<div>';
                                html += '<div class="font-medium whitespace-nowrap">'+cell.getData().transaction_code+'</div>';
                                html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().transaction_date+'</div>';
                            html += '</div>';
                        }

                        return html;
                    }
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        if(cell.getData().status == 1){
                            return '<button data-id="'+cell.getData().id+'" data-amount="'+cell.getData().amount+'" type="button" data-tw-toggle="modal" data-tw-target="#linkTransactionModal" class="linked_trans_btn btn btn-xs btn-warning text-white px-2 py-0 rounded-sm">Unpaid</button>';
                        }else{
                            return '<span class="btn btn-xs btn-success text-white px-2 py-0 rounded-sm">Paid</span>';
                        }
                    }
                },*/
                {
                    title: "Status",
                    field: "payment_status",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        if(cell.getData().payment_status == 1){
                            return '<span class=" btn btn-xs btn-linkedin text-white px-2 py-0 text-xs rounded-sm">Scheduled</span>';
                        }else if(cell.getData().payment_status == 2){
                            return '<span class=" btn btn-xs btn-success text-white px-2 py-0 text-xs rounded-sm">Paid</span>';
                        }else if(cell.getData().payment_status == 3){
                            return '<span class=" btn btn-xs btn-danger text-white px-2 py-0 text-xs rounded-sm">Canceled</span>';
                        }else{
                            return '<span class="btn btn-xs btn-facebook text-white px-2 py-0 text-xs rounded-sm">New</span>';
                        }
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "180",
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        btns +='<a href="'+cell.getData().url+'" class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
                        btns += '<div class="dropdown inline-flex ml-1">\
                                    <button class="dropdown-toggle btn-rounded btn btn-success text-white p-0 w-9 h-9" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="cloud-lightning" class="w-4 h-4"></i></button>\
                                    <div class="dropdown-menu w-40">\
                                        <ul class="dropdown-content">\
                                            <li>\
                                                <a href="'+route('agent.management.remittance.export', cell.getData().id)+'" class="dropdown-item"><i data-lucide="file-text" class="w-4 h-4 mr-2 text-success"></i> Download Excel</a>\
                                            </li>\
                                            <li>\
                                                <a href="'+route('agent.management.remittance.print', cell.getData().id)+'" class="dropdown-item"><i data-lucide="printer" class="w-4 h-4 mr-2 text-success"></i> Download PDF</a>\
                                            </li>\
                                        </ul>\
                                    </div>\
                                </div>';
                        btns += '<button data-id="' +cell.getData().id +'" class="send_email btn btn-primary text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="mail" class="w-4 h-4"></i></button>';
                        btns += '<input type="hidden" class="agent_comission_ids" name="agent_comission_ids" value="' +cell.getData().id +'"/>';
                        btns += '<input type="hidden" class="agent_ids" name="agent_ids" value="' +cell.getData().agent_id +'"/>';
                        return btns;
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
            rowSelectionChanged:function(data, rows){
                var ids = [];
                if(rows.length > 0){
                    $('#scheduleRemitPaymentBtn').fadeIn();
                }else{
                    $('#scheduleRemitPaymentBtn').fadeOut();
                }
            },
            selectableCheck:function(row){
                return row.getData().id > 0;
            }
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