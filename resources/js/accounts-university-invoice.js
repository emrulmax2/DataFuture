import IMask from 'imask';
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import Litepicker from "litepicker";

("use strict");
var invoiceStudentListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let semester_id = $("#intakeSemester").val() != "" ? $("#intakeSemester").val() : "";
        let course_id = $("#course").val() != "" ? $("#course").val() : "";
        let status_id = $("#studentStatus").val() != "" ? $("#studentStatus").val() : "";

        let tableContent = new Tabulator("#invoiceStudentListTable", {
            ajaxURL: route("university.claims.student.list"),
            ajaxParams: { semester_id: semester_id, course_id: course_id, status_id : status_id },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [true, 50, 100, 150, 200, 500],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            selectable: true,
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
                    title: "Name",
                    field: "full_name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var html = '<div class="inline-block relative">';
                                html += '<div class="text-xs font-medium text-slate-400 whitespace-nowrap uppercase">' +cell.getData().registration_no +'</div>';
                                html += '<div class="font-medium whitespace-nowrap uppercase">' +cell.getData().full_name +'</div>';
                            html += '</div>';
                        html += '<input type="hidden" class="student_ids" name="student_ids[]" value="'+cell.getData().student_id+'"/>';
                        return html;
                    },
                },
                {
                    title: "Course & Semester",
                    field: "semester",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var html = '<div class="inline-block relative">';
                                html += '<div class="text-xs font-medium text-slate-400 whitespace-nowrap uppercase">' +cell.getData().course +'</div>';
                                html += '<div class="font-medium whitespace-nowrap uppercase">' +cell.getData().semester +'</div>';
                            html += '</div>';
                        return html;
                    },
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                },
                {
                    title: "Installment",
                    field: "id",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var html = '<div class="inline-block relative">';
                                html += '<div class="text-xs font-medium text-slate-400 whitespace-normal">' +cell.getData().installment_date +'</div>';
                                html += '<div class="font-medium whitespace-nowrap uppercase">' +cell.getData().amount_html +'</div>';
                            html += '</div>';
                        html += '<input type="hidden" class="installment_ids" name="installment_ids[]" value="'+cell.getData().id+'"/>';
                        html += '<input type="hidden" class="amounts" name="amounts" value="'+cell.getData().amount+'"/>';
                        return html;
                    },
                }
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 5];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                } 

                $('#generateInvoiceList').removeAttr('disabled');
                $("#generateInvoiceList .theLoader").fadeOut();
            },
            rowSelectionChanged:function(data, rows){
                var ids = [];
                if(rows.length > 0){
                    $('#claimInvoiceAmount').fadeIn();
                }else{
                    $('#claimInvoiceAmount').fadeOut();
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
    let pickerOptions = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: true,

        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let tomOptionsMul = {
        ...tomOptions,
        plugins: {
            ...tomOptions.plugins,
            remove_button: {
                title: 'Remove this item',
            },
        },
    };
    
    let vendor_id = new TomSelect('#vendor_id', tomOptions);
    let acc_bank_id = new TomSelect('#acc_bank_id', tomOptions);
    let intakeSemester = new TomSelect('#intakeSemester', tomOptions);
    let course = new TomSelect('#course', tomOptions);
    course.clear(true);
    course.clearOptions(true);
    course.disable();
    let studentStatus = new TomSelect('#studentStatus', tomOptionsMul);

    const claimInvoiceAmountModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#claimInvoiceAmountModal"));
    const addBudgetVendorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addBudgetVendorModal"));

    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    const successModalEl = document.getElementById('successModal')
    successModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#successModal button.successCloser').attr('data-action', 'none').attr('data-redirect', 'NONE');
    });

    $(document).on('click', '.successCloser', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            if($(this).attr('data-redirect') != 'NONE'){
                window.location.href = $(this).attr('data-redirect');
            }else{
                window.location.reload();
            }
        }else{
            successModal.hide();
        }
    })

    const claimInvoiceAmountModalEl = document.getElementById('claimInvoiceAmountModal')
    claimInvoiceAmountModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#claimInvoiceAmountModal .acc__input-error').html('');
        $('#claimInvoiceAmountModal .modal-body input:not([type="checkbox"])').val('');
        $('#claimInvoiceAmountModal input[name="student_ids"]').val('');
        $('#claimInvoiceAmountModal input[name="slc_installment_ids"]').val('');
        $('#claimInvoiceAmountModal input[name="semester_id"]').val('');
        $('#claimInvoiceAmountModal input[name="course_id"]').val('');

        $('#claimInvoiceAmountModal .studentsCounts').text('');
        $('#claimInvoiceAmountModal .totalAmount').text('');

        vendor_id.clear(true)
        $('.vendorDetailsWrap').fadeOut().html('')
        acc_bank_id.clear(true)
        $('.bankDetailsWrap').fadeOut().html('')
    });

    const addBudgetVendorModalEl = document.getElementById('addBudgetVendorModal')
    addBudgetVendorModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addBudgetVendorModal .acc__input-error').html('');
        $('#addBudgetVendorModal .modal-body input:not([type="checkbox"])').val('');
        $('#addBudgetVendorModal .modal-body textarea').val('');
        $('#addBudgetVendorModal input[name="active"]').prop('checked', true);
        $('#addBudgetVendorModal input[name="modal_id"]').val('');
        $('#addBudgetVendorModal input[name="vendor_for"]').val('2');
    });

    $("#intakeSemester").on("change", function (event) {
        let $intakeSemester = $(this)
        let semester_id = $intakeSemester.val()
        course.clear(true);
        course.clearOptions(true);
        course.disable();

        if(semester_id > 0) {
            document.querySelector(".theLoaders").style.cssText = "display: inline-block;";
            axios({
                method: "post",
                url: route('university.claims.get.courses'),
                data: {semester_id : semester_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                course.enable();
                document.querySelector(".theLoaders").style.cssText = "display: none;";
        
                if(response.status == 200){   
                    $.each(response.data.rows, function(index, row) {
                        course.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    course[1].refreshOptions()
                }
            }).catch(error => {
                course.enable();
                document.querySelector(".theLoaders").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 304) {
                        console.log('content not found');
                    } else {
                        console.log('error');
                    }
                }
            });
        } else {
            document.querySelector(".theLoaders").style.cssText = "display: none;";
            $('#claimInvoiceAmount').fadeOut();
            $('.invoiceStudentListWrap').fadeOut('fast', function(){
                $('#invoiceStudentListTable').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role');
            })
        }
    });
    $("#course").on("change", function (event) {
        let $course = $(this)
        let course_id = $course.val()

        if(course_id == 0 || course_id == ''){
            $('#claimInvoiceAmount').fadeOut();
            $('.invoiceStudentListWrap').fadeOut('fast', function(){
                $('#invoiceStudentListTable').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role');
            })
        }
    })

    $('#generateInvoiceList').on('click', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        let semester_id = $("#intakeSemester").val();
        let course_id = $("#course").val();

        $(`.acc__input-error`).html('');
        $('#generateInvoiceList').attr('disabled', 'disabled');
        $("#generateInvoiceList .theLoader").fadeIn();

        if(semester_id > 0 && course_id > 0){
            $('.invoiceStudentListWrap').fadeIn('fast', function(){
                invoiceStudentListTable.init();
            })
        }else{
            $('#generateInvoiceList').removeAttr('disabled');
            $("#generateInvoiceList .theLoader").fadeOut();
            if(!semester_id){
                $(`.error-intakeSemester`).html('This field is required.');
            }
            if(!course_id){
                $(`.error-course`).html('This field is required.');
            }

            $('.invoiceStudentListWrap').fadeOut('fast', function(){
                $('#invoiceStudentListTable').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role');
            })

            setTimeout(() => {
                $(`.acc__input-error`).html('');
            }, 3000);
        }     
    })

    $('#claimInvoiceAmount').on('click', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        var student_ids = [];
        var installment_ids = [];
        var amount = 0;
        
        $('#invoiceStudentListTable').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            student_ids.push($row.find('.student_ids').val());
            installment_ids.push($row.find('.installment_ids').val());
            amount += $row.find('.amounts').val() * 1;
        });

        if(student_ids.length > 0){
            claimInvoiceAmountModal.show();
            document.getElementById("claimInvoiceAmountModal").addEventListener("shown.tw.modal", function (event) {
                $('#claimInvoiceAmountModal [name="student_ids"]').val(student_ids.join(','));
                $('#claimInvoiceAmountModal [name="slc_installment_ids"]').val(installment_ids.join(','));
                $('#claimInvoiceAmountModal .studentsCounts').text(installment_ids.length);
                $('#claimInvoiceAmountModal .totalAmount').text('£'+amount.toFixed(2));
                $('#claimInvoiceAmountModal [name="semester_id"]').val($("#intakeSemester").val());
                $('#claimInvoiceAmountModal [name="course_id"]').val($("#course").val());
            });
        }else{
            warningModal.show();
            document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                $("#warningModal .warningModalTitle").html("Error Found!");
                $("#warningModal .warningModalDesc").html('Selected students not foudn. Please select some students first or contact with the site administrator.');
            });
        }
    })

    $('#vendor_id').on('change', function(){
        var theVendor = $('#vendor_id').val();
        if(theVendor > 0){
            axios({
                method: "get",
                url: route("budget.settings.vendors.edit", theVendor),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    let row = response.data;
                    var html = '';
                        html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                            html += '<div class="col-span-4 text-slate-500 font-medium">Name</div>';
                            html += '<div class="col-span-8 font-medium">'+row.name+'</div>';
                        html += '</div>';
                        if(row.email != '' && row.email != null){
                            html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                                html += '<div class="col-span-4 text-slate-500 font-medium">Email</div>';
                                html += '<div class="col-span-8 font-medium">'+row.email+'</div>';
                            html += '</div>';
                        }
                        if(row.phone != '' && row.phone != null){
                            html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                                html += '<div class="col-span-4 text-slate-500 font-medium">Phone</div>';
                                html += '<div class="col-span-8 font-medium">'+row.phone+'</div>';
                            html += '</div>';
                        }
                        if(row.address != '' && row.address != null){
                            html += '<div class="grid grid-cols-12 gap-0">';
                                html += '<div class="col-span-4 text-slate-500 font-medium">Address</div>';
                                html += '<div class="col-span-8 font-medium">'+row.address+'</div>';
                            html += '</div>';
                        }
    
                    $('#claimInvoiceAmountModal .vendorDetailsWrap').html(html).fadeIn();
                }
            }).catch(error => {
                $('#claimInvoiceAmountModal .vendorDetailsWrap').html('').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $('#claimInvoiceAmountModal .vendorDetailsWrap').html('').fadeOut();
        }
    });

    $(document).on('click', '.add_vendor', function(e){
        var $theBtn = $(this);
        var modal_id = $theBtn.attr('data-modal');
        $('#addBudgetVendorModal input[name="modal_id"]').val(modal_id);
    });

    $('#addBudgetVendorForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        let modal_id = '#'+$form.find('input[name="modal_id"]').val();
        const form = document.getElementById('addBudgetVendorForm');
    
        document.querySelector('#saveVenBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveVenBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('budget.settings.vendors.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveVenBtn').removeAttribute('disabled');
            document.querySelector("#saveVenBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addBudgetVendorModal.hide();
                var row = response.data.row;
                if(row){
                    vendor_id.addOption({
                        value: row.id,
                        text: row.name,
                    });

                    vendor_id.addItem(row.id);
                    var html = '';
                    html += '<div class="grid grid-cols-12 gap-0">';
                        html += '<div class="col-span-4 text-slate-500 font-medium">Vendor Name</div>';
                        html += '<div class="col-span-8 font-medium">'+row.name+'</div>';
                    html += '</div>';
                    if(row.email != '' && row.email != null){
                        html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                            html += '<div class="col-span-4 text-slate-500 font-medium">Email</div>';
                            html += '<div class="col-span-8 font-medium">'+row.email+'</div>';
                        html += '</div>';
                    }
                    if(row.phone != '' && row.phone != null){
                        html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                            html += '<div class="col-span-4 text-slate-500 font-medium">Phone</div>';
                            html += '<div class="col-span-8 font-medium">'+row.phone+'</div>';
                        html += '</div>';
                    }
                    if(row.address != '' && row.address != null){
                        html += '<div class="grid grid-cols-12 gap-0">';
                            html += '<div class="col-span-4 text-slate-500 font-medium">Address</div>';
                            html += '<div class="col-span-8 font-medium">'+row.address+'</div>';
                        html += '</div>';
                    }

                    $(modal_id+' .vendorDetailsWrap').html(html).fadeIn();
                }
            }
        }).catch(error => {
            document.querySelector('#saveVenBtn').removeAttribute('disabled');
            document.querySelector("#saveVenBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addBudgetVendorForm .${key}`).addClass('border-danger');
                        $(`#addBudgetVendorForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#acc_bank_id').on('change', function(){
        var theBank = $('#acc_bank_id').val();
        if(theBank > 0){
            axios({
                method: "post",
                url: route("site.settings.banks.edit"),
                data: {row_id : theBank},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    let row = response.data;
                    var html = '';
                        html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                            html += '<div class="col-span-4 text-slate-500 font-medium">Bank</div>';
                            html += '<div class="col-span-8 font-medium">'+row.bank_name+'</div>';
                        html += '</div>';
                        if(row.ac_name != '' && row.ac_name != null){
                            html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                                html += '<div class="col-span-4 text-slate-500 font-medium">AC. Name</div>';
                                html += '<div class="col-span-8 font-medium">'+row.ac_name+'</div>';
                            html += '</div>';
                        }
                        if(row.sort_code != '' && row.sort_code != null){
                            html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                                html += '<div class="col-span-4 text-slate-500 font-medium">Sortcode</div>';
                                html += '<div class="col-span-8 font-medium">'+row.sort_code+'</div>';
                            html += '</div>';
                        }
                        if(row.ac_number != '' && row.ac_number != null){
                            html += '<div class="grid grid-cols-12 gap-0">';
                                html += '<div class="col-span-4 text-slate-500 font-medium">AC. Number</div>';
                                html += '<div class="col-span-8 font-medium">'+row.ac_number+'</div>';
                            html += '</div>';
                        }
    
                    $('#claimInvoiceAmountModal .bankDetailsWrap').html(html).fadeIn();
                }
            }).catch(error => {
                $('#claimInvoiceAmountModal .bankDetailsWrap').html('').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $('#claimInvoiceAmountModal .bankDetailsWrap').html('').fadeOut();
        }
    });

    $('#claimInvoiceAmountForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('claimInvoiceAmountForm');
    
        document.querySelector('#addClaim').setAttribute('disabled', 'disabled');
        document.querySelector("#addClaim svg").style.cssText ="display: inline-block;";
            
        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('university.claims.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#addClaim').removeAttribute('disabled');
            document.querySelector("#addClaim svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                claimInvoiceAmountModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('University payment claim successfully submitted.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD').attr('data-redirect', response.data.red);
                });  
                
                setTimeout(() => {
                    successModal.hide();
                    if(response.data.red && response.data.red != ''){
                        window.location.href = response.data.red
                    }else{
                        window.location.reload();
                    }
                }, 2000);
            }
            $('.invoiceStudentListWrap').fadeIn('fast', function(){
                invoiceStudentListTable.init();
            })
        }).catch(error => {
            document.querySelector('#addClaim').removeAttribute('disabled');
            document.querySelector("#addClaim svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#claimInvoiceAmountForm .${key}`).addClass('border-danger');
                        $(`#claimInvoiceAmountForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

})()