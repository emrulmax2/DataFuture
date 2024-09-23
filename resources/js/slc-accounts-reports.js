import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var slcPaymentHistoryListTable = (function () {
    var _tableGen = function () {
        let date_range = $("#payment_history_date_range").val() != "" ? $("#payment_history_date_range").val() : "";

        let tableContent = new Tabulator("#slcPaymentHistoryListTable", {
            ajaxURL: route("reports.account.payment.history.list"),
            ajaxParams: { date_range: date_range },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [true, 10, 20, 50, 100, 250],
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
                /*{
                    title: "#ID",
                    field: "id",
                    width: "180",
                },*/
                {
                    title: "Term",
                    field: "term_name",
                    headerHozAlign: "left",
                },
                {
                    title: "SSN",
                    field: "ssn",
                    headerHozAlign: "left",
                },
                {
                    title: "REG. NO",
                    field: "registration_no",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "DOB",
                    field: "dob",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Course",
                    field: "course_name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        if(cell.getData().errors != ''){
                            return '<span class="break-words whitespace-normal">'+cell.getData().course_name+'</span>';
                        }
                    }
                },
                {
                    title: "Code",
                    field: "course_code",
                    headerHozAlign: "left",
                },
                {
                    title: "Year",
                    field: "year",
                    headerHozAlign: "left",
                },
                {
                    title: "Remittance Date",
                    field: "transaction_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Amount",
                    field: "amount",
                    headerHozAlign: "left",
                },
                {
                    title: "Error",
                    field: "errors",
                    headerSort: false,
                    hozAlign: "left",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        if(cell.getData().errors != ''){
                            return '<span class="font-medium text-danger break-words whitespace-normal">'+cell.getData().errors+'</span>';
                        }
                    }
                },
                {
                    title: "Status",
                    field: "status",
                    headerSort: false,
                    hozAlign: "left",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '';
                        if(cell.getData().status == 1){
                            html += '<span class="btn btn-success btn-sm text-white">No Error</span>';
                        }else if(cell.getData().status == 2){
                            html += '<span class="btn btn-danger btn-sm text-white">Error</span>';
                        }else{
                            html += '<span class="btn btn-facebook btn-sm text-white">New</span>';
                        }
                        if(cell.getData().error_code == 4 && cell.getData().student_id > 0 && cell.getData().status != 1 && cell.getData().student_id > 0){
                            html += '<button type="button" data-regid="'+cell.getData().student_id+'" data-transid="'+cell.getData().id+'" class="btn btn-primary text-white btn-sm forceInsertBtn">Force Insert</button>';
                        }
                        html += '<input type="hidden" class="history_status" name="status[]" value="'+cell.getData().status+'">';
                        html += '<input type="hidden" class="history_ids" name="slc_payment_history_id[]" value="'+cell.getData().id+'">';

                        return html;
                    }
                }
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
                var hasErrorLength = 0;
                var noErrorLength = 0;

                if(rows.length > 0){
                    $.each(rows, function(index, row){
                        if(row.getData().status != 1){
                            hasErrorLength += 1;
                        }else{
                            noErrorLength += 1;
                        }
                    });

                    if(hasErrorLength > 0 ){
                        $('.slcPaymentHistoryListBtnWrap #recheck_errors').fadeIn();
                        $('.slcPaymentHistoryListBtnWrap #make_payments').fadeOut();
                    }else if(noErrorLength > 0 && hasErrorLength == 0){
                        $('.slcPaymentHistoryListBtnWrap #recheck_errors').fadeOut();
                        $('.slcPaymentHistoryListBtnWrap #make_payments').fadeIn();
                    }else{
                        $('.slcPaymentHistoryListBtnWrap #recheck_errors').fadeOut();
                        $('.slcPaymentHistoryListBtnWrap #make_payments').fadeOut();
                    }
                    
                }else{
                    $('.slcPaymentHistoryListBtnWrap #recheck_errors').fadeOut();
                    $('.slcPaymentHistoryListBtnWrap #make_payments').fadeOut();
                }
            },
            selectableCheck:function(row){
                return row.getData().id > 0; //allow selection of rows where the age is greater than 18
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
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    $('#accountsReportsAccordion .accordion-button').on('click', function(e){
        var $thebtn = $(this);
        var hash = $thebtn.attr('data-tw-target');
        window.location.hash = hash;
    });

    $(window).on('load', function(){
        if(window.location.hash){     
            $('#accountsReportsAccordion .accordion-button[data-tw-target="'+window.location.hash+'"]').removeClass('collapsed').attr('aria-expanded', 'true');
            $('#accountsReportsAccordion '+window.location.hash).addClass('show').show();
        }
    });

    $('#slcPaymentHistorySearchBtn').on('click', function(e){
        e.preventDefault();
        var date_range = $('#payment_history_date_range').val();
        $('#slcPaymentUploadListWrap').fadeOut('fast', function(){
            $('#slcPaymentUploadListForm').html('');
        });

        if(date_range != '' && date_range.length == 23){
            $('#slcPaymentHistoryListWrap').fadeIn('fast', function(){
                slcPaymentHistoryListTable.init();
            })
        }else{
            $('#slcPaymentHistoryListWrap').fadeOut('fast', function(){
                $('#slcPaymentHistoryListTable').removeAttr('tabulator-layout').removeAttr('role').removeClass('tabulator').html('');
            })
        }
    });

    $('#payment_file_csv').on('change', function() {
        $('#slcPaymentHistoryListWrap').fadeOut('fast', function(){
            $('#slcPaymentHistoryListTable').removeAttr('tabulator-layout').removeAttr('role').removeClass('tabulator').html('');
        })

        $('#slcPaymentDocUploadForm').trigger('submit');
    });

    $('#slcPaymentDocUploadForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('slcPaymentDocUploadForm');
        
        $form.find('.loaders').fadeIn();

        let form_data = new FormData(form);
        form_data.append('file', $('#slcPaymentDocUploadForm input[name="payment_file_csv"]')[0].files[0]); 
        axios({
            method: "post",
            url: route('reports.account.payment.upload.csv'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $form.find('.loaders').fadeOut();
            $('#payment_file_csv').val('');

            if (response.status == 200) {
                $('#slcPaymentUploadListWrap').fadeIn('fast', function(){
                    $('#slcPaymentUploadListForm').html(response.data.htm);
                });

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }
        }).catch(error => {
            $form.find('.loaders').fadeOut();
            $('#payment_file_csv').val('');
            if (error.response) {
                console.log('error');
            }
        });
    });

    $('#slcPaymentUploadListForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('slcPaymentUploadListForm');
    
        document.querySelector('#saveCSVTransBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveCSVTransBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('reports.account.payment.save.csv.transactions'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveCSVTransBtn').removeAttribute('disabled');
            document.querySelector("#saveCSVTransBtn svg").style.cssText = "display: none;";
            if (response.status == 200) {
                $('#slcPaymentUploadListWrap').fadeOut('fast', function(){
                    $('#slcPaymentUploadListForm').html('');
                });

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html(response.data.msg);
                });     

                setTimeout(() => {
                    successModal.hide();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#saveCSVTransBtn').removeAttribute('disabled');
            document.querySelector("#saveCSVTransBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    warningModal.show();
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html( "Error!" );
                        $("#warningModal .warningModalDesc").html(error.response.data.msg);
                    });   

                    setTimeout(() => {
                        warningModal.hide();
                    }, 2000);
                } else {
                    console.log('error');
                }
            }
        });
    });

})();