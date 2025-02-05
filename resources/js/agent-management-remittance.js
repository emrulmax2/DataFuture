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
            ajaxURL: route("agent.management.remittance.list"),
            ajaxParams: { querystr: querystr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [true, 50, 100, 150, 200, 300, 400, 500],
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
                            var html = '<span class=" btn btn-xs btn-linkedin text-white px-2 py-0 text-xs rounded-sm">Scheduled</span>';
                                html += (cell.getData().payment_date != '' ? '<br/><span class="pt-1 text-slate-500 text-xs whitespace-nowrap">'+cell.getData().payment_date+'</span>' : '');

                            return html;
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
                                                <a href="'+route('agent.management.remittance.print', cell.getData().id)+'" class="dropdown-item"><i data-lucide="printer" class="w-4 h-4 mr-2 text-success"></i> Download PDF</a>\
                                            </li>\
                                            <li>\
                                                <a href="'+route('agent.management.remittance.export', cell.getData().id)+'" class="dropdown-item"><i data-lucide="file-text" class="w-4 h-4 mr-2 text-success"></i> Download Excel</a>\
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

(function(){
    if ($("#agentRemittanceListTable").length) {
        agentRemittanceListTable.init();

        // Filter function
        function filterTitleHTMLForm() {
            agentRemittanceListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLForm();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterTitleHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("1");
            filterTitleHTMLForm();
        });
    }

    let payDateOption = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        inlineMode: false,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const scheduleRemitPaymentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#scheduleRemitPaymentModal"));

    const scheduleRemitPaymentModalEl = document.getElementById('scheduleRemitPaymentModal')
    scheduleRemitPaymentModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#scheduleRemitPaymentModal .modal-body .theScheduleContent').fadeOut().html('');
        $('#scheduleRemitPaymentModal .modal-body .theScheduleLoader').fadeIn();

    });

    $(document).on('click', '#scheduleRemitPaymentBtn', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var agent_comission_ids = [];
        var agent_ids = [];

        $('#agentRemittanceListTable').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            agent_comission_ids.push($row.find('.agent_comission_ids').val());
            agent_ids.push($row.find('.agent_ids').val());
        });

        if(agent_comission_ids.length > 0 && agent_ids.length > 0){
            agent_ids = agent_ids.filter((value, index, array) => array.indexOf(value) === index);
            if(agent_ids.length > 1){
                scheduleRemitPaymentModal.hide();
                warningModal.show();
                document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModal .warningModalTitle").html( "ERROR!" );
                    $("#warningModal .warningModalDesc").html('You can not select multiple agents remittance at a time.');
                }); 

                setTimeout(() => {
                    warningModal.hide();
                    agentRemittanceListTable.init();
                }, 2000);
            }else{
                axios({
                    method: "post",
                    url: route('agent.management.remittances.details'),
                    data: {agent_comission_ids : agent_comission_ids},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        scheduleRemitPaymentModal.show();
                        document.getElementById("scheduleRemitPaymentModal").addEventListener("shown.tw.modal", function (event) {
                            $('#scheduleRemitPaymentModal .modal-body .theScheduleLoader').fadeOut('fast', function(){
                                $('#scheduleRemitPaymentModal .modal-body .theScheduleContent').fadeIn('fast').html(response.data.html);
                            });
                        });

                        setTimeout(() => {
                            $('#scheduleRemitPaymentModal .modal-body .theScheduleContent .datepickers').each(function(){
                                new Litepicker({
                                    element: this,
                                    ...payDateOption
                                });
                            })
                        }, 500);
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                        scheduleRemitPaymentModal.hide();
                        warningModal.show();
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModal .warningModalTitle").html( "ERROR!" );
                            $("#warningModal .warningModalDesc").html(error.response.data.msg);
                        });
                    }
                });
            }
        }else{
            scheduleRemitPaymentModal.hide();
            warningModal.show();
            document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                $("#warningModal .warningModalTitle").html( "ERROR!" );
                $("#warningModal .warningModalDesc").html('Please select some remittance first.');
            });  

            setTimeout(() => {
                warningModal.hide();
                agentRemittanceListTable.init();
            }, 2000);
        }
    });

    $('#scheduleRemitPaymentForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('scheduleRemitPaymentForm');
    
        $form.find('.acc__input-error').html('');
        document.querySelector('#schedulePayBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#schedulePayBtn svg").style.cssText ="display: inline-block;";

        let error = 0;
        $('#scheduleRemitPaymentForm .modal-body .theScheduleContent .datepickers').each(function(){
            if($(this).val() == ''){
                error += 1;
                $(this).siblings('.acc__input-error').html('This field is required.');
            }
        });

        if(error == 0){
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('agent.management.remittances.store.payment'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#schedulePayBtn').removeAttribute('disabled');
                document.querySelector("#schedulePayBtn svg").style.cssText = "display: none;";
                if (response.status == 200) {
                    scheduleRemitPaymentModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Agent comission remittances successfully secheduled for payment.');
                    });     

                    setTimeout(() => {
                        succModal.hide();
                    }, 2000);
                    agentRemittanceListTable.init();
                }
            }).catch(error => {
                document.querySelector('#schedulePayBtn').removeAttribute('disabled');
                document.querySelector("#schedulePayBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#scheduleRemitPaymentForm .${key}`).addClass('border-danger')
                            $(`#scheduleRemitPaymentForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        }else{
            document.querySelector('#schedulePayBtn').removeAttribute('disabled');
            document.querySelector("#schedulePayBtn svg").style.cssText = "display: none;";
        }
    });

})();