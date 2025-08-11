import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var requisitionTransListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        var requisition_id = $("#requisitionTransListTable").attr('data-requisition');

        let tableContent = new Tabulator("#requisitionTransListTable", {
            ajaxURL: route("budget.management.req.trans.list"),
            ajaxParams: { requisition_id: requisition_id},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: true,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Date",
                    field: "transaction_date_2",
                    formatter(cell, formatterParams) { 
                        var html = '<div class="block relative">';
                                html += '<div class="font-medium whitespace-nowrap '+(cell.getData().audit_status != 1 ? 'text-danger' : 'text-success')+'">';
                                    if(cell.getData().can_eidt == 1){
                                        html += '<a href="javascript:void(0);" class="underline">';
                                    }
                                        html += cell.getData().transaction_date_2;
                                    if(cell.getData().can_eidt == 1){
                                        html += '</a>';
                                    }
                                html += '</div>';
                                html += '<div class="text-slate-500 text-xs whitespace-nowrap mt-0.5 flex justify-start items-center">';
                                    if(cell.getData().doc_url != ''){
                                        html += '<a data-id="'+cell.getData().id+'" href="javascript:void(0);" target="_blank" class="downloadTransDoc text-success mr-2" style="position: relative; top: -1px;"><i data-lucide="hard-drive-download" class="w-4 h-4"></i></a>';
                                    }
                                    if(cell.getData().has_assets != ''){
                                        html += '<span class="text-success mr-2" style="position: relative; top: -1px;"><i data-lucide="package-check" class="w-4 h-4"></i></span>';
                                    }
                                    html += cell.getData().transaction_code;
                                    if(cell.getData().connected == 1){
                                        html += '<a href="'+route('reports.accounts.transaction.connection', cell.getData().id)+'" class="text-success ml-2" style="position: relative; top: -1px;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right-left" class="lucide lucide-arrow-right-left w-4 h-4"><path d="m16 3 4 4-4 4"></path><path d="M20 7H4"></path><path d="m8 21-4-4 4-4"></path><path d="M4 17h16"></path></svg></a>';
                                    }
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Details",
                    field: "detail",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<div class="relative">';
                                var txts = '';
                                if(cell.getData().detail != ''){
                                    txts += cell.getData().detail;
                                    //html += '<div class="whitespace-normal">'+cell.getData().detail+'</div>';
                                }
                                if(cell.getData().description != '' || cell.getData().invoice_no != ''){
                                    //html += '<div class="whitespace-normal">';
                                        //html += (cell.getData().invoice_no != '' ? cell.getData().invoice_no : '');
                                        txts += (cell.getData().invoice_no != '' ? cell.getData().invoice_no : '');

                                        //html += (cell.getData().invoice_no != '' && cell.getData().description != '' ? ' - ' : '');
                                        txts += (cell.getData().invoice_no != '' && cell.getData().description != '' ? ' - ' : '');
                                        //html += (cell.getData().description != '' ? cell.getData().description : '');
                                        txts += (cell.getData().description != '' ? cell.getData().description : '');
                                    //html += '</div>';
                                }
                                if(txts.length > 150){
                                    html += '<div class="whitespace-normal">';
                                        html += txts.substr(0, 150);
                                        html += '<span class="showHidTexts hidden">'+txts+'</span>';
                                        html += '&nbsp;&nbsp;<a href="javascript:void(0);" class="text-primary showMoreLess font-medium">Show More</a>';
                                    html +='</div>';
                                }else{
                                    html += '<div class="whitespace-normal">'+txts+'</div>';
                                }
                                //html += '<div class="whitespace-normal">'+txts+'</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Storage",
                    field: "bank_name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '';
                        html += '<div class="relative">';
                            html += '<div class="font-medium whitespace-normal">'+cell.getData().bank_name+'</div>';
                        html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Category",
                    field: "acc_category_id",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '';
                        if(cell.getData().transfer_bank_id > 0 && cell.getData().transaction_type == 2){
                            html += '<div class="relative">';
                                html += '<div class="font-medium whitespace-normal">';
                                    if(cell.getData().flow == 0){
                                        html += '<span class="btn btn-linkedin p-0 rounded-0 mr-2"><i data-lucide="arrow-right" class="w-3 h-3"></i></span>';
                                    }else if(cell.getData().flow == 1){
                                        html += '<span class="btn btn-linkedin p-0 rounded-0 mr-2"><i data-lucide="arrow-left" class="w-3 h-3"></i></span>';
                                    }
                                    html += cell.getData().transfer_bank_name
                                html += '</div>';
                            html += '</div>';
                        }else if(cell.getData().acc_category_id > 0){
                            html += '<div class="relative">';
                                html += '<div class="font-medium whitespace-normal">'+cell.getData().category_name+'</div>';
                            html += '</div>';
                        }
                        return html;
                    }
                },
                {
                    title: "amount",
                    field: "transaction_amount",
                    headerHozAlign: "right",
                    hozAlign: "right",
                    headerSort: false,
                    width: '140',
                    formatter(cell, formatterParams) { 
                        var html = '';
                        html = '<div class="block relative">';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().transaction_amount+'</div>';
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
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
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

    if($('#requisitionTransListTable').length > 0){
        requisitionTransListTable.init();
    }
    
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const approverConfirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#approverConfirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const markRequisitionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#markRequisitionModal"));
    let confModalDelTitle = 'Are you sure?';
    let submission_status = false;

    const successModalEl = document.getElementById('successModal')
    successModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#successModal .successCloser').attr('data-action', 'NONE');
    });

    const approverConfirmModalEl = document.getElementById('approverConfirmModal')
    approverConfirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#approverConfirmModal .agreeWith').attr('data-approver', '0');
        $('#approverConfirmModal .agreeWith').attr('data-status', '0');
        $('#approverConfirmModal .agreeWith').attr('data-id', '0');
        $('#approverConfirmModal .agreeWith').attr('data-action', 'none');
        $('#approverConfirmModal [name="note"]').val('');
    });

    const markRequisitionModalEl = document.getElementById('markRequisitionModal')
    markRequisitionModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#markRequisitionModal .modal-body input').val('');
        $('#markRequisitionModal .autoFillDropdown').html('').fadeOut();

        $('#markRequisitionModal .transactionsTable tbody tr.transaction_row').remove();
        $('#markRequisitionModal .transactionsTable tbody tr.initRow').fadeIn();
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            window.location.reload();
        }else{
            succModal.hide();
        }
    });

    $('#markRequisitionForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('markRequisitionForm');
        var $theForm = $(this);
        var $theTable = $theForm.find('.transactionsTable');
    
        document.querySelector('#markCompBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#markCompBtn svg").style.cssText ="display: inline-block;";

        var transactionLength = $theTable.find('.transaction_row').length;
        var total_balance = $theForm.find('[name="total_balance"]').val();
        var total_transactions = calculationTransanctionAmount();
        if(transactionLength == 0){
            $('#markRequisitionForm .modal-content .transAlert').remove();
            $('#markRequisitionForm .modal-content').prepend('<div class="transAlert alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Please select some transactions first.</div>');
            submission_status = false;
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });

            setTimeout(() => {
                $('#markRequisitionForm .modal-content .transAlert').remove();
            }, 2000);

            document.querySelector('#markCompBtn').removeAttribute('disabled');
            document.querySelector("#markCompBtn svg").style.cssText = "display: none;";
        }else if(!submission_status && (total_transactions > total_balance || total_transactions < total_balance)){
            if(total_transactions > total_balance){
                $('#markRequisitionForm .modal-content .transAlert').remove();
                $('#markRequisitionForm .modal-content').prepend('<div class="transAlert alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Total transaction amount grater than requisition amount. Do you still want to continue? Then click the Save Button again.</div>')
            }else{
                $('#markRequisitionForm .modal-content .transAlert').remove();
                $('#markRequisitionForm .modal-content').prepend('<div class="transAlert alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Total transaction amount less than requisition amount. Do you still want to continue? Then click the Save Button again.</div>')
            }
            submission_status = true;
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });

            setTimeout(() => {
                $('#markRequisitionForm .modal-content .transAlert').remove();
            }, 2000);

            document.querySelector('#markCompBtn').removeAttribute('disabled');
            document.querySelector("#markCompBtn svg").style.cssText = "display: none;";
        }else{
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('budget.management.req.mark.completed'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#markCompBtn').removeAttribute('disabled');
                document.querySelector("#markCompBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    markRequisitionModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Requisition Successfully synced with transactions and the status has been updated to Completed.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });  
                    
                    setTimeout(() => {
                        succModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#markCompBtn').removeAttribute('disabled');
                document.querySelector("#markCompBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#markRequisitionForm .${key}`).addClass('border-danger');
                            $(`#markRequisitionForm  .error-${key}`).html(val);
                        }
                    }if (error.response.status == 304) {
                        warningModal.show();
                        document.getElementById('warningModal').addEventListener('shown.tw.modal', function(event){
                            $('#warningModal .warningModalTitle').html('Error Found!');
                            $('#warningModal .warningModalDesc').html('Something went wrong. Please try again later or contact with the administrator.');
                        });
    
                        setTimeout(() => {
                            warningModal.hide();
                        }, 2000);
                    } else {
                        console.log('error');
                    }
                }
            });
        }
    });

    $(document).on('click', '.statusUpdater', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var status = $theLink.attr('data-active');
        var approver = $theLink.attr('data-approver');
        var requisition_id = $theLink.attr('data-id');

        var message = 'Do you really want to change status of this record? If yes then please click on the agree btn.';
        if(status == 2 || status == 3){
            message = '';
            confModalDelTitle = 'Click Yes to confirm authorisation';
        }else if(status == 0){
            confModalDelTitle = 'Do you wish to reject this requisition';
            message = '';
        }

        approverConfirmModal.show();
        document.getElementById('approverConfirmModal').addEventListener('shown.tw.modal', function(event){
            $('#approverConfirmModal .approverConfModTitle').html(confModalDelTitle);
            $('#approverConfirmModal .approverConfModDesc').html(message);
            $('#approverConfirmModal .agreeWith').attr('data-id', requisition_id);
            $('#approverConfirmModal .agreeWith').attr('data-status', status);
            $('#approverConfirmModal .agreeWith').attr('data-approver', approver);
            $('#approverConfirmModal .agreeWith').attr('data-action', 'CHANGESTATRIQ');
        });
    });

    // Confirm Modal Action
    $('#approverConfirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let status = $agreeBTN.attr('data-status');
        let approver = $agreeBTN.attr('data-approver');
        let action = $agreeBTN.attr('data-action');
        let note = $('#approverConfirmModal [name="note"]').val();

        $('#approverConfirmModal button').attr('disabled', 'disabled');
        if(action == 'CHANGESTATRIQ'){
            axios({
                method: 'post',
                url: route('budget.management.update.req.status'),
                data: {record_id : recordID, status : status, approver : approver, note : note},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#approverConfirmModal button').removeAttr('disabled');
                    approverConfirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulation!');
                        $('#successModal .successModalDesc').html('Requisition status successfully updated.');
                        $('#successModal .agreeWith').html('RELOAD');
                    });

                    setTimeout(() => {
                        succModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                if (error.response.status == 422) {
                    warningModal.show();
                    document.getElementById('warningModal').addEventListener('shown.tw.modal', function(event){
                        $('#warningModal .warningModalTitle').html('Error Found!');
                        $('#warningModal .warningModalDesc').html('Something went wrong. Please try again later or contact with the administrator.');
                    });

                    setTimeout(() => {
                        warningModal.hide();
                    }, 2000);
                } else {
                    console.log('error');
                }
            });
        }
    });

    $('#markRequisitionModal #transaction_no').on('keyup', function () {
        var $theInput = $(this);
        var SearchVal = $theInput.val();

        if (SearchVal.length >= 3) {
            axios({
                method: 'post',
                url: route('budget.management.get.filtered.transactions'),
                data: { SearchVal: SearchVal },
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            }).then((response) => {
                if (response.status == 200) {
                    $theInput.siblings('.autoFillDropdown').html(response.data.htm).fadeIn();
                }
            }).catch((error) => {
                if (error.response) {
                    $theInput.siblings('.autoFillDropdown').html('').fadeOut();
                }
            });
        } else {
            $theInput.siblings('.autoFillDropdown').html('').fadeOut();
        }
    });

    $('#markRequisitionModal .autoFillDropdown').on('click', 'li a:not(".disable")', function(e){
        e.preventDefault();
        var transaction_id = $(this).attr('data-id');
        var transaction_code = $(this).attr('data-transactioncode');

        var exist_row = $('#markRequisitionModal .transactionsTable tbody #transaction_row_'+transaction_id).length;
        if(exist_row == 0){
            axios({
                method: 'post',
                url: route('budget.management.get.transaction'),
                data: { transaction_id: transaction_id, transaction_code : transaction_code },
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            }).then((response) => {
                if (response.status == 200) {
                    $('#markRequisitionModal .transactionsTable tbody tr.initRow').fadeOut();
                    $('#markRequisitionModal .transactionsTable tbody').append(response.data.htm);

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch((error) => {
                if (error.response) {
                    console.log(error);
                }
            });
        }

        $(this).parent('li').parent('ul.autoFillDropdown').siblings('#transaction_no').val('');
        $(this).parent('li').parent('.autoFillDropdown').html('').fadeOut();
    });


    $('#markRequisitionModal .transactionsTable tbody').on('click', '.remove_trans_row', function(e){
        e.preventDefault();
        var $theLink = $(this);
        
        $theLink.closest('.transaction_row').remove();
        var transLength = $('#markRequisitionModal .transactionsTable tbody tr.transaction_row').length;
        if(transLength == 0){
            $('#markRequisitionModal .transactionsTable tbody tr.initRow').fadeIn();
        }else{
            $('#markRequisitionModal .transactionsTable tbody tr.initRow').fadeOut();
        }
    });

    
    const descriptionShowHideModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#descriptionShowHideModal"));
    document.getElementById('descriptionShowHideModal').addEventListener('hidden.tw.modal', function(event){
        $('#descriptionShowHideModal .modal-body').html('');
    });

    $('#requisitionTransListTable').on('click', '.showMoreLess', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var fullText = $theLink.siblings('.showHidTexts').html();

        descriptionShowHideModal.show();
        document.getElementById("descriptionShowHideModal").addEventListener("shown.tw.modal", function (event) {
            $("#descriptionShowHideModal .modal-body").html(fullText);
        }); 
    });

    $('#requisitionTransListTable').on('click', '.downloadTransDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('accounts.storage.trans.download.link'),
            data: {row_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200){
                let res = response.data.res;
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});

                if(res != ''){
                    window.open(res, '_blank');
                }
            } 
        }).catch(error => {
            if(error.response){
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});
                console.log('error');
            }
        });
    });

    function calculationTransanctionAmount(){
        var $theModal = $('#markRequisitionModal');
        var $theTable = $theModal.find('.transactionsTable');

        var total_amount = 0;
        $theTable.find('.transaction_row').each(function(){
            var $theRow = $(this);
            var amount = $theRow.find('.theAmount').val() * 1;

            total_amount += amount;
        });

        return total_amount;
    }
})()