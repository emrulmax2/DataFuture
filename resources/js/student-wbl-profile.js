import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var studentWBLProfileTable = (function () {
    var _tableGen = function () {
        let student_id = $('#studentWBLProfileTable').attr('data-student');
        let status = $("#status-WBL").val() != "" ? $("#status-WBL").val() : "";

        let tableContent = new Tabulator("#studentWBLProfileTable", {
            ajaxURL: route("student.wbl.profile.list"),
            ajaxParams: { status: status, student_id : student_id},
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
                    title: "Company",
                    field: "company",
                    headerHozAlign: "left",
                    minWidth: 80,
                },
                {
                    title: "WEIF form provided",
                    field: "weif_form_provided_date",
                    headerHozAlign: "left",
                    minWidth: 100,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            if(cell.getData().weif_form_provided_date != ''){ html += '<div class="font-medium whitespace-nowrap">'+cell.getData().weif_form_provided_date+'</div>'; }
                            if(cell.getData().weif_form_provided_status != ''){ html += '<div>'+cell.getData().weif_form_provided_status+'</div>'; }
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Received completed WEIF form",
                    field: "received_completed_weif_form_date",
                    headerHozAlign: "left",
                    minWidth: 100,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            if(cell.getData().received_completed_weif_form_date != ''){ html += '<div class="font-medium whitespace-nowrap">'+cell.getData().received_completed_weif_form_date+'</div>'; }
                            if(cell.getData().received_completed_weif_form_status != ''){ html += '<div>'+cell.getData().received_completed_weif_form_status+'</div>'; }
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Work hours update by terms",
                    field: "work_hour_update_term_date",
                    headerHozAlign: "left",
                    minWidth: 100,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            if(cell.getData().work_hour_update_term_date != ''){ html += '<div class="font-medium whitespace-nowrap">'+cell.getData().work_hour_update_term_date+'</div>'; }
                            if(cell.getData().work_hour_update_term_status != ''){ html += '<div>'+cell.getData().work_hour_update_term_status+'</div>'; }
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Work experience handbook completed",
                    field: "work_exp_handbook_complete_date",
                    headerHozAlign: "left",
                    minWidth: 100,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            if(cell.getData().work_exp_handbook_complete_date != ''){ html += '<div class="font-medium whitespace-nowrap">'+cell.getData().work_exp_handbook_complete_date+'</div>'; }
                            if(cell.getData().work_exp_handbook_complete_status != ''){ html += '<div>'+cell.getData().work_exp_handbook_complete_status+'</div>'; }
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Work experience handbook checked",
                    field: "work_exp_handbook_checked_date",
                    headerHozAlign: "left",
                    minWidth: 100,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            if(cell.getData().work_exp_handbook_checked_date != ''){ html += '<div class="font-medium whitespace-nowrap">'+cell.getData().work_exp_handbook_checked_date+'</div>'; }
                            if(cell.getData().work_exp_handbook_checked_status != ''){ html += '<div>'+cell.getData().work_exp_handbook_checked_status+'</div>'; }
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Employer handbook sent",
                    field: "emp_handbook_sent_date",
                    headerHozAlign: "left",
                    minWidth: 100,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            if(cell.getData().emp_handbook_sent_date != ''){ html += '<div class="font-medium whitespace-nowrap">'+cell.getData().emp_handbook_sent_date+'</div>'; }
                            if(cell.getData().emp_handbook_sent_status != ''){ html += '<div>'+cell.getData().emp_handbook_sent_status+'</div>'; }
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Employers letter sent",
                    field: "emp_letter_sent_date",
                    headerHozAlign: "left",
                    minWidth: 100,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            if(cell.getData().emp_letter_sent_date != ''){ html += '<div class="font-medium whitespace-nowrap">'+cell.getData().emp_letter_sent_date+'</div>'; }
                            if(cell.getData().emp_letter_sent_status != ''){ html += '<div>'+cell.getData().emp_letter_sent_status+'</div>'; }
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Employers confirmation received",
                    field: "emp_confirm_rec_date",
                    headerHozAlign: "left",
                    minWidth: 100,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            if(cell.getData().emp_confirm_rec_date != ''){ html += '<div class="font-medium whitespace-nowrap">'+cell.getData().emp_confirm_rec_date+'</div>'; }
                            if(cell.getData().emp_confirm_rec_status != ''){ html += '<div>'+cell.getData().emp_confirm_rec_status+'</div>'; }
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Company visit",
                    field: "company_visit_date",
                    headerHozAlign: "left",
                    minWidth: 100,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            if(cell.getData().company_visit_date != ''){ html += '<div class="font-medium whitespace-nowrap">'+cell.getData().company_visit_date+'</div>'; }
                            if(cell.getData().company_visit_status != ''){ html += '<div>'+cell.getData().company_visit_status+'</div>'; }
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Record of student meetings",
                    field: "record_std_meeting_date",
                    headerHozAlign: "left",
                    minWidth: 100,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            if(cell.getData().record_std_meeting_date != ''){ html += '<div class="font-medium whitespace-nowrap">'+cell.getData().record_std_meeting_date+'</div>'; }
                            if(cell.getData().record_std_meeting_status != ''){ html += '<div>'+cell.getData().record_std_meeting_status+'</div>'; }
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Record of all contacts to student",
                    field: "record_all_contact_student_date",
                    headerHozAlign: "left",
                    minWidth: 100,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            if(cell.getData().record_all_contact_student_date != ''){ html += '<div class="font-medium whitespace-nowrap">'+cell.getData().record_all_contact_student_date+'</div>'; }
                            if(cell.getData().record_all_contact_student_status != ''){ html += '<div>'+cell.getData().record_all_contact_student_status+'</div>'; }
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Email sent to employer",
                    field: "email_sent_emp_date",
                    headerHozAlign: "left",
                    minWidth: 100,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            if(cell.getData().email_sent_emp_date != ''){ html += '<div class="font-medium whitespace-nowrap">'+cell.getData().email_sent_emp_date+'</div>'; }
                            if(cell.getData().email_sent_emp_status != ''){ html += '<div>'+cell.getData().email_sent_emp_status+'</div>'; }
                        html += '</div>';

                        return html;
                    }
                },



                {
                    title: "Created",
                    field: "created_by",
                    headerHozAlign: "left",
                    minWidth: 100,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().created_by+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().created_at+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "120",
                    download: false,
                    minWidth: 120,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editWBLProfileModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns +='<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }
                        
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

        // Export
        $("#tabulator-export-csv-WBL").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-WBL").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-WBL").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Semester Details",
            });
        });

        $("#tabulator-export-html-WBL").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-WBL").on("click", function (event) {
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
    if ($("#studentWBLProfileTable").length) {
        // Init Table
        studentWBLProfileTable.init();

        // Filter function
        function filterHTMLFormWBL() {
            studentWBLProfileTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-WBL")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormWBL();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-WBL").on("click", function (event) {
            filterHTMLFormWBL();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-WBL").on("click", function (event) {
            $("#status-WBL").val("1");
            filterHTMLFormWBL();
        });
    }


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    const addWBLProfileModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addWBLProfileModal"));
    const editWBLProfileModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editWBLProfileModal"));

    const addWBLProfileModalEl = document.getElementById('addWBLProfileModal')
    addWBLProfileModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addWBLProfileModal .acc__input-error').html('');
        $('#addWBLProfileModal .modal-body input:not([type="radio"])').val('');
        $('#addWBLProfileModal .modal-body input[type="radio"]').prop('checked', false);
        $('#addWBLProfileModal .modal-footer [name="student_work_placement_id"]').val('');
    });
    const editWBLProfileModalEl = document.getElementById('editWBLProfileModal')
    editWBLProfileModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editWBLProfileModal .acc__input-error').html('');
        $('#editWBLProfileModal .modal-body input:not([type="radio"])').val('');
        $('#editWBLProfileModal .modal-body input[type="radio"]').prop('checked', false);
        $('#editWBLProfileModal .modal-footer [name="id"]').val('0');
    });


    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    $('#warningModal .warningCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            warningModal.hide();
            window.location.reload();
        }else{
            warningModal.hide();
        }
    })


    $('#student_work_placement_id').on('change', function(){
        var $thePlacement = $(this);
        var thePlacement = $thePlacement.val();

        if(thePlacement > 0){
            $('.addWBLProfileBtn').fadeIn();
        }else{
            $('.addWBLProfileBtn').fadeOut();
        }
    });

    $('.addWBLProfileBtn').on('click', function(e){
        e.preventDefault();
        var thePlacement = $('#student_work_placement_id').val();

        $('#addWBLProfileModal [name="student_work_placement_id"]').val(thePlacement);
    });

    $('#addWBLProfileForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('addWBLProfileForm');

        var datepickers = 0;
        var radios = $('input[type="radio"]:checked', $form).length;
        $('input.datepicker').each(function(){
            datepickers += ($(this).val() != '' ? 1 : 0);
        })
    
        document.querySelector('#addWBL').setAttribute('disabled', 'disabled');
        document.querySelector("#addWBL svg").style.cssText ="display: inline-block;";

        if(datepickers > 0 && radios > 0){
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('student.store.wbl.profile'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#addWBL').removeAttribute('disabled');
                document.querySelector("#addWBL svg").style.cssText = "display: none;";

                if (response.status == 200) {
                    addWBLProfileModal.hide();

                    successModal.show(); 
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Student work placement WBL profile successfully inserted.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });  
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#addWBL').removeAttribute('disabled');
                document.querySelector("#addWBL svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addWBLProfileForm .${key}`).addClass('border-danger');
                            $(`#addWBLProfileForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        }else{
            document.querySelector('#addWBL').removeAttribute('disabled');
            document.querySelector("#addWBL svg").style.cssText = "display: none;";

            $form.find('.alert').remove();
            $('.modal-content', $form).prepend('<div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> You can not submit a blank form. Please fill out at least one field and checkd that field status.</div>')
        
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });

            setTimeout(function(){
                $form.find('.alert').remove();
            }, 3000)
        }
    });

    $('#studentWBLProfileTable').on('click', '.edit_btn', function(){
        let $editBtn = $(this);
        let row_id = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("student.edit.wbl.profile", row_id),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data.res;

                $.each(response.data.res, function(field, value){
                    if(field != 'id' && field != 'student_work_placement_id' && field != 'company_id' && field != 'created_by' && field != 'updated_by'){
                        if(field.includes('_date')){
                            $('#editWBLProfileModal input[name="'+field+'"]').val(value);
                        }else if(field.includes('_status')){
                            $('#editWBLProfileModal input[name="'+field+'"][value="'+value+'"]').prop('checked', true);
                        }
                    }
                })
                

                $('#editWBLProfileModal [name="id"]').val(row_id ? row_id : '');
                
            }
        }).catch((error) => {
            console.log(error);
        });
    })

    $('#editWBLProfileForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('editWBLProfileForm');

        var datepickers = 0;
        var radios = $('input[type="radio"]:checked', $form).length;
        $('input.datepicker').each(function(){
            datepickers += ($(this).val() != '' ? 1 : 0);
        })
    
        document.querySelector('#updateWBL').setAttribute('disabled', 'disabled');
        document.querySelector("#updateWBL svg").style.cssText ="display: inline-block;";

        if(datepickers > 0 && radios > 0){
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('student.update.wbl.profile'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateWBL').removeAttribute('disabled');
                document.querySelector("#updateWBL svg").style.cssText = "display: none;";

                if (response.status == 200) {
                    editWBLProfileModal.hide();

                    successModal.show(); 
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Student work placement WBL profile successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });  
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#updateWBL').removeAttribute('disabled');
                document.querySelector("#updateWBL svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editWBLProfileForm .${key}`).addClass('border-danger');
                            $(`#editWBLProfileForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        }else{
            document.querySelector('#updateWBL').removeAttribute('disabled');
            document.querySelector("#updateWBL svg").style.cssText = "display: none;";

            $form.find('.alert').remove();
            $('.modal-content', $form).prepend('<div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> You can not submit a blank form. Please fill out at least one field and checkd that field status.</div>')
        
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });

            setTimeout(function(){
                $form.find('.alert').remove();
            }, 3000)
        }
    });

    $('#studentWBLProfileTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let row_id = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', row_id);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETEWBL');
        });
    });

    // Restore Course
    $('#studentWBLProfileTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let row_id = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', row_id);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTOREWBL');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETEWBL'){
            axios({
                method: 'delete',
                url: route('student.destroy.wbl.profile', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTOREWBL'){
            axios({
                method: 'post',
                url: route('student.restore.wbl.profile'),
                data: {row_id : recordID},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record Successfully Restored!');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            confirmModal.hide();
        }
    })

})()