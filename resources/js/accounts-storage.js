import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var storageTransList = (function () {
    var _tableGen = function () {
        let storage = $('#storageTransList').attr('data-storage');
        let queryStr = $('#searchTransaction').val();

        let tableContent = new Tabulator("#storageTransList", {
            ajaxURL: route("accounts.storage.trans.list"),
            ajaxParams: { storage: storage, queryStr : queryStr },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 20,
            paginationSizeSelector: [true, 20, 50, 100],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Date",
                    field: "transaction_date_2",
                    width: '160',
                    formatter(cell, formatterParams) { 
                        var html = '<div class="block relative">';
                                html += '<div class="font-medium whitespace-nowrap">';
                                    if(cell.getData().can_eidt == 1){
                                        html += '<a data-id="'+cell.getData().id+'" href="javascript:void(0);" class="editTransaction text-success underline">';
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
                                    html += cell.getData().transaction_code;
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
                                if(cell.getData().detail != ''){
                                    html += '<div class="whitespace-normal">'+cell.getData().detail+'</div>';
                                }
                                if(cell.getData().description != '' || cell.getData().invoice_no != ''){
                                    html += '<div class="whitespace-normal">';
                                        html += (cell.getData().invoice_no != '' ? cell.getData().invoice_no : '');
                                        html += (cell.getData().invoice_no != '' && cell.getData().description != '' ? ' - ' : '');
                                        html += (cell.getData().description != '' ? cell.getData().description : '');
                                    html += '</div>';
                                }
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
                    title: "Withdrawl",
                    field: "out",
                    headerHozAlign: "right",
                    hozAlign: "right",
                    headerSort: false,
                    width: '140',
                    formatter(cell, formatterParams) { 
                        var html = '';
                        if(cell.getData().flow == 1){
                            html = '<div class="block relative">';
                                html += '<div class="font-medium whitespace-nowrap">'+cell.getData().transaction_amount+'</div>';
                            html += '</div>';
                        }
                        return html;
                    }
                },
                {
                    title: "Deposit",
                    field: "in",
                    headerHozAlign: "right",
                    hozAlign: "right",
                    headerSort: false,
                    width: '140',
                    formatter(cell, formatterParams) { 
                        var html = '';
                        if(cell.getData().flow != 1){
                            html = '<div class="block relative">';
                                html += '<div class="font-medium whitespace-nowrap">'+cell.getData().transaction_amount+'</div>';
                            html += '</div>';
                        }
                        return html;
                    }
                },
                {
                    title: "Balance",
                    field: "balance",
                    headerHozAlign: "right",
                    hozAlign: "right",
                    headerSort: false,
                    width: '140',
                    visible: (queryStr == '' ? true : false),
                    formatter(cell, formatterParams) { 
                        var html = '<div class="block relative">';
                                html += '<div class="font-medium whitespace-nowrap">'+cell.getData().balance+'</div>';
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

        // Export
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Status Details",
            });
        });

        // Print
        $("#tabulator-print").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function(){
    if ($("#storageTransList").length) {
        // Init Table
        storageTransList.init();

        // Filter function
        function filterHTMLForm() {
            storageTransList.init();
        }


        // On click go button
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("1");
            filterHTMLForm();
        });

        $('#searchTransaction').on('keyup paste change', function(){
            let $theInput = $(this);
            let theQuery = $theInput.val();
            if(theQuery.length > 0){
                $('#storageExportBtn').fadeIn();
            }else{
                $('#storageExportBtn').fadeOut();
            }
            storageTransList.init();
        });
    }


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            window.location.reload();
        }else{
            successModal.hide();
        }
    })



    $('#income').on('keyup paste change', function(){
        $('#expense').val('');
    });
    $('#expense').on('keyup paste change', function(){
        $('#income').val('');
    });

    $('#trans_type').on('change', function(e){
        let $trans_type = $(this);
        let trans_type = $trans_type.val();

        if(trans_type == 2){
            $('#acc_category_id_in, #acc_category_id_out').val('').fadeOut('fast', function(){
                $('#acc_bank_id').fadeIn().val('');
            });
        }else if(trans_type == 1){
            $('#acc_category_id_in, #acc_bank_id').val('').fadeOut('fast', function(){
                $('#acc_category_id_out').fadeIn().val('');
            });
        }else{
            $('#acc_category_id_out, #acc_bank_id').val('').fadeOut('fast', function(){
                $('#acc_category_id_in').fadeIn().val('');
            });
        }
    });

    $('#addTransactionToggle').on('click', function(e){
        e.preventDefault();
        let $theBtn = $(this);

        if($theBtn.hasClass('active')){
            $theBtn.removeClass('active');
            $('#storageTransactionForm').fadeOut('fast', function(){
                $('#storageTransactionForm input:not([type="checkbox"]):not("#transaction_date"):not([type="file"])').val('');
                $('#transaction_document').val('');
                $('#storageTransactionForm input[type="checkbox"]').prop('checked', true);
                $('#trans_type').val('0');
                $('#acc_category_id_out, #acc_bank_id').val('').fadeOut();
                $('#acc_category_id_in').fadeIn().val('');

                $('#income').val('');
                $('#expense').val('');
                $('#storageTransactionForm #transaction_id').val('0');
                $('#storageTransactionForm #deleteTransaction').fadeOut().attr('data-id', '0')
            })
        }else{
            $theBtn.addClass('active');
            $('#storageTransactionForm').fadeIn();
        }
    });

    $('#storageExportBtn').on('click', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        let queryStr = $('#searchTransaction').val();
        let storage_id = $('#export_storage_id').val();

        if(queryStr != '' && storage_id != ''){
            window.location.href = route('accounts.storage.trans.export', [queryStr, storage_id]);
        }
    })

    $("#storageTransactionForm").on("submit", function (e) {
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById("storageTransactionForm");
        let theId = $('#storageTransactionForm #transaction_id').val();
        let url = 'accounts.storage.trans.store';
        if(theId != '' && theId != undefined && theId > 0){
            url = 'accounts.storage.trans.update';
        }

        document.querySelector('#storeTransaction').setAttribute('disabled', 'disabled');
        document.querySelector('#storeTransaction svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);
        form_data.append('file', $('#storageTransactionForm input[name="document"]')[0].files[0]); 
        axios({
            method: "post",
            url: route(url),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let msg = response.data.msg;
                document.querySelector("#storeTransaction").removeAttribute("disabled");
                document.querySelector("#storeTransaction svg").style.cssText = "display: none;";

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!");
                    $("#successModal .successModalDesc").html(msg);
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });

                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000)
            }
        }).catch((error) => {
            document.querySelector("#storeTransaction").removeAttribute("disabled");
            document.querySelector("#storeTransaction svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#storageTransactionForm #${key}`).addClass('border-danger')
                    }
                }else {
                    console.log("error");
                }
            }
        });
    });

    $('#deleteTransaction').on('click', function(e){
        e.preventDefault();
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETETRNS');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETETRNS'){
            axios({
                method: 'delete',
                url: route('accounts.storage.trans.destroy', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000)
                }
            }).catch(error =>{
                console.log(error)
            });
        }
    });

    $('#storageTransList').on('click', '.editTransaction', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('accounts.storage.trans.edit'),
            data: {transaction_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200){
                let row = response.data.res;

                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});
                
                $('#addTransactionToggle').addClass('active');
                $('#storageTransactionForm').fadeIn('fast', function(){
                    $('#storageTransactionForm #transaction_date').val(row.transaction_date_2);
                    $('#storageTransactionForm #detail').val(row.detail);
                    $('#storageTransactionForm #trans_type').val(row.transaction_type);

                    $('#storageTransactionForm #deleteTransaction').fadeIn().attr('data-id', row.id)
                    if(row.transaction_type == 0){
                        $('#expense').val((row.flow == 1 ? row.transaction_amount : ''));
                        $('#income').val((row.flow == 0 ? row.transaction_amount : ''));

                        $('#acc_category_id_in').fadeIn().val(row.acc_category_id);
                        $('#acc_category_id_out').fadeOut().val('');
                        $('#acc_bank_id').fadeOut().val('');

                        $('#storeTransaction').fadeIn();
                    }else if(row.transaction_type == 1){
                        $('#expense').val((row.flow == 1 ? row.transaction_amount : ''));
                        $('#income').val((row.flow == 0 ? row.transaction_amount : ''));

                        $('#acc_category_id_in').fadeOut().val('');
                        $('#acc_category_id_out').fadeIn().val(row.acc_category_id);
                        $('#acc_bank_id').fadeOut().val('');

                        $('#storeTransaction').fadeIn();
                    }else if(row.transaction_type == 2){
                        $('#expense').val((row.flow == 1 ? row.transaction_amount : ''));
                        $('#income').val((row.flow == 0 ? row.transaction_amount : ''));

                        $('#acc_category_id_in').fadeOut().val('');
                        $('#acc_category_id_out').fadeOut().val('');
                        $('#acc_bank_id').fadeIn().val(row.transfer_bank_id);

                        $('#storeTransaction').fadeOut();
                    }
                    $('#storageTransactionForm #invoice_no').val(row.invoice_no);
                    $('#storageTransactionForm #invoice_date').val(row.invoice_date);
                    $('#storageTransactionForm #description').val(row.description);
                    if(row.audit_status == 1){
                        $('#storageTransactionForm #audit_status').prop('checked', true);
                    }else{
                        $('#storageTransactionForm #audit_status').prop('checked', false);
                    }
                    $('#storageTransactionForm #transaction_id').val(row.id);
                });
            } 
        }).catch(error => {
            if(error.response){
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});
                console.log('error');
            }
        });
    });

    $('#storageTransList').on('click', '.downloadTransDoc', function(e){
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

})()