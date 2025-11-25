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
            ajaxURL: route("university.claims.agreement.student.list"),
            ajaxParams: { semester_id: semester_id, course_id: course_id, status_id: status_id },
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
                        html += '<input type="hidden" class="student_ids" name="student_ids[]" value="'+cell.getData().id+'"/>';
                        return html;
                    },
                },
                {
                    title: "Semester",
                    field: "semester",
                    headerHozAlign: "left",
                },
                {
                    title: "Course",
                    field: "course",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        return '<div class="whitespace-normal text-sm">'+cell.getData().course+'</div>';
                    }
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
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
                    $('#createBulkAgreements').fadeIn();
                }else{
                    $('#createBulkAgreements').fadeOut();
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

    let intakeSemester = new TomSelect('#intakeSemester', tomOptions);
    let course = new TomSelect('#course', tomOptions);
    let studentStatus = new TomSelect('#studentStatus', tomOptionsMul);

    const addAgreementModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addAgreementModal"));

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

    const addAgreementModalEl = document.getElementById('addAgreementModal')
    addAgreementModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addAgreementModal .acc__input-error').html('');
        $('#addAgreementModal .modal-body select').val('');
        $('#addAgreementModal .modal-body input:not([type="checkbox"])').val('');
        $('#addAgreementModal .modal-body input[type="checkbox"]').prop('checked', false);

        $('#addAgreementModal .modal-footer input[name="semester_id"]').val('0');
        $('#addAgreementModal .modal-footer input[name="course_id"]').val('0');
        $('#addAgreementModal .modal-footer input[name="student_ids"]').val('');

        $('#agr_add_course_creation_instance_id').empty();
        $('#agr_add_course_creation_instance_id').append('<option value="">Please Select</option>');

        $('#addAgreementModal .feesError').addClass('hidden').html('');
        $('#addAgreementModal .acc__input-error').html('');

        $('#addAgreementForm .installMentWraper').fadeOut('fast', function(){
            $('#addAgreementForm .installMentWraper table tr.newRow').remove();
            $('#addAgreementForm .installMentWraper table').find('input, select').val('');
        })
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
        }
    });

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

    $('#createBulkAgreements').on('click', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        var ids = [];
        
        $('#invoiceStudentListTable').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            ids.push($row.find('.student_ids').val());
        });

        if(ids.length > 0){
            addAgreementModal.show();
            document.getElementById("addAgreementModal").addEventListener("shown.tw.modal", function (event) {
                $('#addAgreementModal [name="student_ids"]').val(ids.join(','));
                $('#addAgreementModal .modal-footer input[name="semester_id"]').val($('#intakeSemester').val());
                $('#addAgreementModal .modal-footer input[name="course_id"]').val($('#course').val());

                axios({
                    method: "post",
                    url: route('university.claims.get.instances'),
                    data: {
                        semester_id: $('#intakeSemester').val(),
                        course_id: $('#course').val(),
                    },
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    let $select = $('#agr_add_course_creation_instance_id');
                    $select.empty();
                    $select.append('<option value="">Please Select</option>');

                    $.each(response.data.rows, function(index, item) {
                        $select.append(`<option value="${item.id}">${item.name}</option>`);
                    });
                }).catch(error => {
                    console.error(error);
                });
            });
        }else{
            warningModal.show();
            document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                $("#warningModal .warningModalTitle").html("Error Found!");
                $("#warningModal .warningModalDesc").html('Selected students not foudn. Please select some students first or contact with the site administrator.');
            });
        }
    })

    $('#addAgreementForm [name="course_creation_instance_id"]').on('change', function(){
        var $select = $(this);
        var course_creation_instance_id = $select.val();

        if(course_creation_instance_id > 0 && course_creation_instance_id != ''){
            axios({
                method: "post",
                url: route('university.claims.get.instance'),
                data: {course_creation_instance_id : course_creation_instance_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                var fees = response.data.fees;
                var commission = response.data.commission;
                var percentage = response.data.percentage;
                if(percentage > 0){
                    $('#addAgreementForm .universityCommissionWrap').fadeIn('fast', function(){
                        $('#addAgreementForm .percntage').html(percentage+'%')
                        $('#addAgreementForm input[name="commission_amount"]').val(commission.toFixed(2))
                    });
                }else{
                    $('#addAgreementForm .universityCommissionWrap').fadeOut('fast', function(){
                        $('#addAgreementForm .percntage').html('')
                        $('#addAgreementForm input[name="commission_amount"]').val('')
                    })
                }

                if(fees > 0){
                    $('#addAgreementForm .installMentWraper').fadeIn('fast', function(){
                        $('#addAgreementForm .installMentWraper table tr.newRow').remove();
                        $('#addAgreementForm .installMentWraper table').find('input, select').val('');
                    })
                }else{
                    $('#addAgreementForm .installMentWraper').fadeOut('fast', function(){
                        $('#addAgreementForm .installMentWraper table tr.newRow').remove();
                        $('#addAgreementForm .installMentWraper table').find('input, select').val('');
                    })
                }

                $('#addAgreementForm input[name="fees"]').val(fees);
            }).catch(error => {
                if (error.response.status == 422) {
                    console.log('error');
                }
            });
        }else{
            $('#addAgreementForm input[name="fees"]').val('');
            $('#addAgreementForm .universityCommissionWrap').fadeOut('fast', function(){
                $('#addAgreementForm .percntage').html('')
                $('#addAgreementForm input[name="commission_amount"]').val('')
            })
        }
    });

    $('#addAgreementForm input[name="fees"]').on('input', function(){
        let $fees = $(this);
        let fees = $fees.val();

        if(fees != '' && fees > 0){
            $('#addAgreementForm .installMentWraper').fadeIn('fast', function(){
                $('#addAgreementForm .installMentWraper table tr.newRow').remove();
                $('#addAgreementForm .installMentWraper table').find('input, select').val('');
            })
        }else{
            $('#addAgreementForm .installMentWraper').fadeOut('fast', function(){
                $('#addAgreementForm .installMentWraper table tr.newRow').remove();
                $('#addAgreementForm .installMentWraper table').find('input, select').val('');
            })
        }
    })

    $('#addInstallmentRow').on('click', function (e) {
        e.preventDefault();
        let $newRow = $('#installmentTable .defaultRow').first().clone();

        $newRow.removeClass('defaultRow').addClass('newRow');
        $newRow.find('input, select').each(function () {
            if ($(this).is('input')) {
                $(this).val('');
            }
            if ($(this).is('select')) {
                $(this).prop('selectedIndex', 0);
            }
        });
        $newRow.find('.delete_btn').removeClass('hidden');
        $newRow.find('.datepicker').each(function(){
            new Litepicker({
                element: this,
                ...pickerOptions
            });
        })

        $('#installmentTable tbody').append($newRow);
    });

    $('#installmentTable').on('click', '.delete_btn', function () {
        $(this).closest('tr').remove();
    });

    $('#addAgreementForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('addAgreementForm');
        let $theBtn = $form.find('#addAgre');

        var fees = $('[name="fees"]', $form).val();
        var installmentAmount = 0;
        var installmentCount = 0;
        if(!$('.installMentWraper').is(':hidden')){
            installmentCount += $('#installmentTable tbody .installmentRow').length;
        }
        $('#installmentTable tbody .installmentRow').each(function(){
            let $row = $(this);
            let rowAmount = $row.find('.installmentAmount').val();
            installmentAmount += (rowAmount != '' && rowAmount > 0 ? rowAmount * 1 : 0);
        })
    
        $form.find('.feesError').addClass('hidden').html('');
        $form.find('.acc__input-error').html('');
        $theBtn.attr('disabled', 'disabled');
        $theBtn.find("svg").fadeIn();

        let errors = 0;
        $form.find('.require').each(function(){
            if($(this).val() == ''){
                errors += 1;
                $(this).siblings('.acc__input-error.error-'+$(this).attr('name')).html('This field is required.')
            }
        })
        if(installmentCount == 0){
            errors += 1;
            $form.find('.feesError').removeClass('hidden').html('Please add at lease one installment.')
        }else if(installmentCount > 0 && (installmentAmount == 0 || installmentAmount > fees)){
            errors += 1;
            $form.find('.feesError').removeClass('hidden').html('Total installment amount can not be 0 or grater than the fees.');
        }else{
            let rowsError = 0;
            $('#installmentTable tbody .installmentRow .rowRequire').each(function(){
                if($(this).val() == ''){
                    rowsError += 1;
                }
            })

            if(rowsError > 0){
                errors += 1;
                $form.find('.feesError').removeClass('hidden').html('Please fill out all fields of each installment rows.');
            }
        }

        if(errors > 0){
            $theBtn.removeAttr('disabled');
            $theBtn.find("svg").fadeOut();

            setTimeout(() => {
                $('#addAgreementModal .feesError').addClass('hidden').html('');
                $('#addAgreementModal .acc__input-error').html('');
            }, 3000);
        }else{
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('university.claims.store.agreement'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $theBtn.removeAttr('disabled');
                $theBtn.find("svg").fadeOut();

                if (response.status == 200) {
                    addAgreementModal.hide();

                    successModal.show(); 
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Selected Student\'s SLC Agreement successfully added.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });  
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                $theBtn.removeAttr('disabled');
                $theBtn.find("svg").fadeOut();
                console.log(error.response)
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addAgreementForm .${key}`).addClass('border-danger');
                            $(`#addAgreementForm  .error-${key}`).html(val);
                        }
                    }else if(error.response.status == 500){
                        addAgreementModal.hide();
                        
                        warningModal.show();
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModal .warningModalTitle").html("Error Found!");
                            $("#warningModal .warningModalDesc").html(error.response.data.message);
                        });
                    }else {
                        console.log('error');
                    }
                }
            });
        }
    });
})()