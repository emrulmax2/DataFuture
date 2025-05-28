import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Litepicker from "litepicker";


("use strict");
var agentRemittPaymentsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "1";

        let tableContent = new Tabulator("#agentRemittPaymentsListTable", {
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
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: "80",
                },
                {
                    title: "Reference",
                    field: "reference",
                    headerHozAlign: "left",
                },
                {
                    title: "Date",
                    field: "date",
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
                    title: "Terms",
                    field: "semsters",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Remit Ref.",
                    field: "remittance_refs",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        var html = '<div class="whitespace-normal">'+cell.getData().remittance_refs+'</div>';

                        return html;
                    }
                },
                {
                    title: "Transaction",
                    field: "acc_transaction_id",
                    headerHozAlign: "left",
                    headerSort: false,
                    visible: (status == 2 ? true : false),
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
                            return '<span class=" btn btn-xs btn-linkedin text-white px-2 py-0 text-xs rounded-sm">Scheduled</span>';
                        }else if(cell.getData().status == 2){
                            return '<span class=" btn btn-xs btn-success text-white px-2 py-0 text-xs rounded-sm">Paid</span>';
                        }else if(cell.getData().status == 3){
                            return '<span class=" btn btn-xs btn-danger text-white px-2 py-0 text-xs rounded-sm">Canceled</span>';
                        }else{
                            return '';
                        }
                    }
                },
                {
                    title: "Amount",
                    field: "amount_html",
                    headerHozAlign: "left",
                    headerSort: false,
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
                        if(cell.getData().acc_transaction_id == 0){
                            btns +='<button data-id="'+cell.getData().id+'" data-amount="'+cell.getData().amount+'" type="button" data-tw-toggle="modal" data-tw-target="#linkTransactionModal" class="linked_trans_btn btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="link" class="w-4 h-4"></i></button>';
                        }
                        btns += '<button data-id="' +cell.getData().id +'" class="send_email btn btn-primary text-white btn-rounded ml-1 p-0 w-9 h-9 relative">';
                            btns += '<i data-lucide="mail" class="w-4 h-4 theIcon"></i>';
                            btns += '<svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 theLoader absolute l-0 r-0 t-0 b-0 m-auto"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg>'
                        btns += '</button>';
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
                const actionColumn = this.getColumn("id");
                if (actionColumn) {
                    const currentWidth = actionColumn.getWidth();
                    actionColumn.setWidth(currentWidth - 1);
                }
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
    if ($("#agentRemittPaymentsListTable").length) {
        agentRemittPaymentsListTable.init();

        // Filter function
        function filterTitleHTMLForm() {
            agentRemittPaymentsListTable.init();
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

    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const linkTransactionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#linkTransactionModal"));

    const linkTransactionModalEl = document.getElementById('linkTransactionModal')
    linkTransactionModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#comissionGenerateModal .acc__input-error').html('');
        $('#comissionGenerateModal #transaction_code').val('');
        $('#comissionGenerateModal #transaction_id').val('');
        $('#comissionGenerateModal .autoFillDropdown').html('').fadeOut();

        $('#comissionGenerateModal [name="agent_comission_payment_id"]').val('0');
        $('#comissionGenerateModal [name="agent_comission_total"]').val('0');
        $('#linkTransactionModal .modal-body .amountError').remove();

    });

    $('#agentRemittPaymentsListTable').on('click', '.linked_trans_btn', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var agent_comission_payment_id = $theBtn.attr('data-id');
        var agent_comission_total = $theBtn.attr('data-amount');

        $('#linkTransactionModal [name="agent_comission_payment_id"]').val(agent_comission_payment_id);
        $('#linkTransactionModal [name="agent_comission_total"]').val(agent_comission_total);
    })

    $('#linkTransactionModal #transaction_code').on('keyup', function(){
        var $theInput = $(this);
        var SearchVal = $theInput.val();

        if(SearchVal.length >= 3){
            axios({
                method: "post",
                url: route('agent.management.remittance.search.transaction'),
                data: {SearchVal : SearchVal},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $theInput.siblings('.autoFillDropdown').html(response.data.htm).fadeIn();
                }
            }).catch(error => {
                if (error.response) {
                    console.log('error');
                    $theInput.siblings('.autoFillDropdown').html('').fadeOut();
                }
            });
        }else{
            $theInput.siblings('.autoFillDropdown').html('').fadeOut();
        }
    });

    $('#linkTransactionModal .autoFillDropdown').on('click', 'li a:not(".disable")', function(e){
        e.preventDefault();
        var comission_total = $('#linkTransactionModal [name="agent_comission_total"]').val() * 1;
        var transaction_code = $(this).attr('href');
        var transaction_id = $(this).attr('data-id');
        var transaction_amount = $(this).attr('data-amount') * 1;
        $(this).parent('li').parent('ul.autoFillDropdown').siblings('.transaction_code').val(transaction_code);
        $(this).parent('li').parent('ul.autoFillDropdown').siblings('.transaction_id').val(transaction_id);
        $(this).parent('li').parent('.autoFillDropdown').html('').fadeOut();

        //console.log(comission_total.toFixed(2)+' - '+transaction_amount.toFixed(2));
        if(comission_total.toFixed(2) != transaction_amount.toFixed(2)){
            $('#linkTransactionModal .modal-body .amountError').remove();
            $('#linkTransactionModal .modal-body').append('<div class="amountError alert alert-pending-soft show flex items-center mt-5" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> <span><strong>Oops! </strong> The transaction amount does not match the remittance total.</span></div>')
            
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        }else{
            $('#linkTransactionModal .modal-body .amountError').remove();
            $('#linkTransactionModal .modal-body').append('<div class="amountError alert alert-success-soft show flex items-center mt-5" role="alert"><i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> <span><strong>WOW! </strong> The transaction amount matches the remittance total.</span></div>')
            
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        }
    });

    $('#linkTransactionForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('linkTransactionForm');
    
        document.querySelector('#linkTransBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#linkTransBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('agent.management.remittance.linked.transaction'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#linkTransBtn').removeAttribute('disabled');
            document.querySelector("#linkTransBtn svg").style.cssText = "display: none;";
            if (response.status == 200) {
                linkTransactionModal.hide();

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Transaction successfully linked with the payment.');
                });     

                setTimeout(() => {
                    succModal.hide();
                }, 2000);
            }
            agentRemittPaymentsListTable.init();
        }).catch(error => {
            document.querySelector('#linkTransBtn').removeAttribute('disabled');
            document.querySelector("#linkTransBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    warningModal.show();
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html( "ERROR!" );
                        $("#warningModal .warningModalDesc").html(error.response.data.msg);
                    });     

                    setTimeout(() => {
                        succModal.hide();
                    }, 2000);
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#agentRemittPaymentsListTable').on('click', '.send_email', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var the_id = $theBtn.attr('data-id');

        $theBtn.attr('disabled', 'disabled');
        $theBtn.find('svg.theIcon').css({'opacity' : '0'});
        $theBtn.find('svg.theLoader').fadeIn();

        axios({
            method: "post",
            url: route('agent.management.remittance.payment.send.mail'),
            data: {payment_id : the_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $theBtn.removeAttr('disabled');
            $theBtn.find('svg.theLoader').fadeOut();
            $theBtn.find('svg.theIcon').css({'opacity' : '1'});
            if (response.status == 200) {
                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Mail successfully sent to the agent.');
                });     

                setTimeout(() => {
                    succModal.hide();
                }, 2000);
            }
        }).catch(error => {
            $theBtn.removeAttr('disabled');
            $theBtn.find('svg.theLoader').fadeOut();
            $theBtn.find('svg.theIcon').css({'opacity' : '1'});
            if (error.response) {
                console.log('error');
            }
        });
    })
})()