
import TomSelect from "tom-select";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

import dayjs from "dayjs";
import Litepicker from "litepicker";

import IMask from 'imask';


("use strict");
var educationQualTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId = $("#educationQualTable").attr('data-applicant') != "" ? $("#educationQualTable").attr('data-applicant') : "0";
        let querystr = $("#query-EQ").val() != "" ? $("#query-EQ").val() : "";
        let status = $("#status-EQ").val() != "" ? $("#status-EQ").val() : "";

        let tableContent = new Tabulator("#educationQualTable", {
            ajaxURL: route("applicant.qualification.list"),
            ajaxParams: { applicantId: applicantId, querystr: querystr, status: status},
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
                    width: "110",
                },
                {
                    title: "Awarding Body",
                    field: "awarding_body",
                    headerHozAlign: "left",
                },
                {
                    title: "Highest Academic Qualification",
                    field: "highest_academic",
                    headerHozAlign: "left",
                },
                {
                    title: "Subjects",
                    field: "subjects",
                    headerHozAlign: "left",
                },
                {
                    title: "Result",
                    field: "result",
                    headerHozAlign: "left",
                },
                {
                    title: "Award Date",
                    field: "degree_award_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editQualificationModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv-EQ").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-EQ").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-EQ").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Venues Details",
            });
        });

        $("#tabulator-export-html-EQ").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-EQ").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();
var employmentHistoryTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId = $("#employmentHistoryTable").attr('data-applicant') != "" ? $("#employmentHistoryTable").attr('data-applicant') : "0";
        let querystr = $("#query-EH").val() != "" ? $("#query-EH").val() : "";
        let status = $("#status-EH").val() != "" ? $("#status-EH").val() : "";

        let tableContent = new Tabulator("#employmentHistoryTable", {
            ajaxURL: route("applicant.employment.list"),
            ajaxParams: { applicantId: applicantId, querystr: querystr, status: status},
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
                    title: "Organization",
                    field: "company_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Phone",
                    field: "company_phone",
                    headerHozAlign: "left",
                },
                {
                    title: "Position",
                    field: "position",
                    headerHozAlign: "left",
                },
                {
                    title: "Start",
                    field: "start_date",
                    headerHozAlign: "left",
                },
                {
                    title: "End",
                    field: "end_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Address",
                    field: "address",
                    headerHozAlign: "left",
                    width: "180",
                    formatter(cell, formatterParams) {   
                        return '<div class="whitespace-nowrap">'+cell.getData().address+'</div>';
                    }
                },
                {
                    title: "Contact Person",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Position",
                    field: "contact_position",
                    headerHozAlign: "left",
                },
                {
                    title: "Phone",
                    field: "contact_phone",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editEmployementHistoryModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv-EH").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-EH").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-EH").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Applicant Details",
            });
        });

        $("#tabulator-export-html-EH").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-EH").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function () {
    if($('#educationQualTable').length > 0){
        if($('#educationQualTable').hasClass('activeTable')){
            educationQualTable.init();
        }
        // Filter function
        function filterHTMLFormEQ() {
            educationQualTable.init();
        }

        // On submit filter form
        /*$("#tabulatorFilterForm-EQ")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormEQ();
                }
            }
        );*/

        // On click go button
        $("#tabulator-html-filter-go-EQ").on("click", function (event) {
            filterHTMLFormEQ();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-EQ").on("click", function (event) {
            $("#query-EQ").val("");
            $("#status-EQ").val("1");
            filterHTMLFormEQ();
        });
    }
    if($('#employmentHistoryTable').length > 0){
        if($('#employmentHistoryTable').hasClass('activeTable')){
            employmentHistoryTable.init();
        }

        // Filter function
        function filterHTMLFormEH() {
            employmentHistoryTable.init();
        }

        // On submit filter form
        /*$("#tabulatorFilterForm-EH")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormEH();
                }
            }
        );*/

        // On click go button
        $("#tabulator-html-filter-go-EH").on("click", function (event) {
            filterHTMLFormEH();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-EH").on("click", function (event) {
            $("#query-EH").val("");
            $("#status-EH").val("1");
            filterHTMLFormEH();
        });
    }


    const addQualificationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addQualificationModal"));
    const editQualificationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editQualificationModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const errorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#errorModal"));
    const addressModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addressModal"));

    const addEmployementHistoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addEmployementHistoryModal"));
    const editEmployementHistoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEmployementHistoryModal"));
            
    let confModalDelTitle = 'Are you sure?';

    const addQualificationModalEl = document.getElementById('addQualificationModal')
    addQualificationModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addQualificationModal .acc__input-error').html('');
        $('#addQualificationModal .modal-body input').val('');
        $('#addQualificationModal .modal-body select').val('');
    });

    const editQualificationModalEl = document.getElementById('editQualificationModal')
    editQualificationModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editQualificationModal .acc__input-error').html('');
        $('#editQualificationModal .modal-body input').val('');
        $('#editQualificationModal .modal-body select').val('');
        $('#editQualificationModal .modal-footer input[name="id"]').val('0');
    });

    const addEmployementHistoryModalEl = document.getElementById('addEmployementHistoryModal')
    addEmployementHistoryModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addEmployementHistoryModal .acc__input-error').html('');
        $('#addEmployementHistoryModal .modal-body input').val('').removeAttr('disabled');
        $('#addEmployementHistoryModal .modal-body input[type="checkbox"]').prop('checked', false);
        $('#addEmployementHistoryModal .modal-body select').val('');
        $('#addEmployementHistoryModal .addressWrap').fadeOut().removeClass('active').html('');
    });

    const editEmployementHistoryModalEl = document.getElementById('editEmployementHistoryModal')
    editEmployementHistoryModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editEmployementHistoryModal .acc__input-error').html('');
        $('#editEmployementHistoryModal .modal-body input').val('').removeAttr('disabled');
        $('#editEmployementHistoryModal .modal-body input[type="checkbox"]').prop('checked', false);
        $('#editEmployementHistoryModal .modal-body select').val('');
        $('#editEmployementHistoryModal .addressWrap').fadeOut().removeClass('active').html('');
        $('#editEmployementHistoryModal .modal-footer input[name="id"]').val('0');
        $('#editEmployementHistoryModal .modal-footer input[name="ref_id"]').val('0');
    });

    const addressModalEl = document.getElementById('addressModal')
    addressModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addressModal .acc__input-error').html('');
        $('#addressModal input').val('');
    });

    let applicationDatepickerOpt = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: true,
        format: "DD-MM-YYYY",
        maxDate: new Date(),
        dropdowns: {
            minYear: 1900,
            maxYear: null,
            months: true,
            years: true,
        },
    };

    $('.applicationDatepicker').each(function(){
        new Litepicker({
            element: this,
            ...applicationDatepickerOpt,
        });
    })

    let tomOptions = {
        plugins: {
            dropdown_input: {},
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    var course_creation_id = new TomSelect('#course_creation_id', tomOptions);
    var employment_status = new TomSelect('#employment_status', tomOptions);
    var student_loan = new TomSelect('#student_loan', tomOptions);

    $('.applicationLccTom').each(function(){
        new TomSelect(this, tomOptions);
    })

    if($('.applicationPhoneMask').length > 0){
        $('.applicationPhoneMask').each(function(){
            IMask(
                this, {
                  mask: '00000000000'
                }
            )
        })
    }

    // click on next button
    $('#is_applicant_agree').on('change', function(e){
        if($(this).prop('checked')){
            $('.form-wizard .wizard-fieldset.wizard-last-step .form-wizard-next-btn').removeAttr('disabled');
        }else{
            $('.form-wizard .wizard-fieldset.wizard-last-step .form-wizard-next-btn').attr('disabled', 'disabled');
        }
    })
    $('.form-wizard-next-btn').on('click', function () {
        var parentFieldset = $(this).parents('.wizard-fieldset');
        var parentForm = $(this).parents('.wizard-step-form');
        var currentActiveStep = $(this).parents('.form-wizard').find('.form-wizard-steps .active');
        var next = $(this);
        let nextWizardStep = true;

        /* Form Submission Start*/
        var formID = parentForm.attr('id');
        const form = document.getElementById(formID);
    
        $('.form-wizard-next-btn, .form-wizard-previous-btn', parentForm).attr('disabled', 'disabled');
        $('.form-wizard-next-btn svg', parentForm).fadeIn();

        let form_data = new FormData(form);
        let applicantId = $('[name="applicant_id"]', parentForm).val();
        let url, redURL;
        if(parentFieldset.index() == 2){
            url = route('applicant.application.store.course');
        }else if(parentFieldset.index() == 3){
            url = route('applicant.application.store.submission');
            redURL = $('input[name="url"]', parentForm).val();
        }else{
            url = route('applicant.application.store.personal');
        }

        $.ajax({
            method: 'POST',
            url: url,
            data: form_data,
            dataType: 'json',
            async: false,
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            cache: false,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            success: function(res, textStatus, xhr){
                $('.acc__input-error', parentForm).html('');
                $('.form-wizard-next-btn, .form-wizard-previous-btn', parentForm).removeAttr('disabled');
                $('.form-wizard-next-btn svg', parentForm).fadeOut(); 
                if(xhr.status == 200){
                    if(parentFieldset.index() == 1){
                        $(document.body).find('input[name="applicant_id"]').val(res.applicant_id);
                        $('#educationQualTable, #employmentHistoryTable').attr('data-applicant', res.applicant_id);
                        $('#varifiedReferral').attr('data-applicant-id', res.applicant_id);
                    } else if(parentFieldset.index() == 2){
                        $('.reviewContentWrap').attr('data-review-id', res.applicant_id);
                    } else if(parentFieldset.index() == 3){
                        window.location.href = redURL;
                    }
                }
                nextWizardStep = true;
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('.form-wizard-next-btn, .form-wizard-previous-btn', parentForm).removeAttr('disabled');
                $('.form-wizard-next-btn svg', parentForm).fadeOut();
                if(jqXHR.status == 422){
                    for (const [key, val] of Object.entries(jqXHR.responseJSON.errors)) {
                        $(`#${formID} .${key}`).addClass('border-danger');
                        $(`#${formID}  .error-${key}`).html(val);
                    }
                }else{
                    console.log(textStatus+' => '+errorThrown);
                }
                nextWizardStep = false;
            }
        });
        //console.log(nextWizardStep);
        //nextWizardStep = false;
        /* Form Submission End*/
         
        if (nextWizardStep) {
            next.parents('.wizard-fieldset').removeClass("show");
            currentActiveStep.removeClass('active').addClass('activated').next().addClass('active');
            next.parents('.wizard-fieldset').next('.wizard-fieldset').addClass("show");
            $(document).find('.wizard-fieldset').each(function () {
                if ($(this).hasClass('show')) {
                    var activeIndex = $(this).index();
                    var indexCount = 1;
                    $(document).find('.form-wizard-steps .form-wizard-step-item').each(function () {
                        if (activeIndex == indexCount) {
                            $(this).addClass('active');
                        } else {
                            $(this).removeClass('active');
                        }
                        indexCount++;
                    });
                    
                    /* Check If Last Step */
                    var $lastStep = $(this);
                    if($lastStep.hasClass('wizard-last-step') && $('.reviewContentWrap', $lastStep).length > 0){
                        var applicant_id = $('.reviewContentWrap', $lastStep).attr('data-review-id');
                        axios({
                            method: "post",
                            url: route('applicant.application.review'),
                            data: {applicant_id : applicant_id},
                            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                        }).then(response => {
                            if (response.status == 200) {
                                $('.reviewLoader', $lastStep).fadeOut('fast', function(){
                                    $('.reviewContentWrap', $lastStep).fadeIn('fast', function(){
                                        $('.reviewContent', $lastStep).html(response.data.htmls);
                                        const applicantReviewAccordion = tailwind.Accordion.getOrCreateInstance(document.querySelector("#applicantReviewAccordion"));
                                        createIcons({
                                            icons,
                                            "stroke-width": 1.5,
                                            nameAttr: "data-lucide",
                                        });
                                    })
                                })
                            }
                        }).catch(error => {
                            if (error.response) {
                                console.log('error');
                            }
                        });
                    }
                }
            });
        }
    });
    //click on previous button
    $('.form-wizard-previous-btn').on('click', function () {
        var counter = parseInt($(".wizard-counter").text());
        
        var prev = $(this);
        var currentActiveStep = $(this).parents('.form-wizard').find('.form-wizard-steps .active');
        prev.parents('.wizard-fieldset').removeClass("show");
        prev.parents('.wizard-fieldset').prev('.wizard-fieldset').addClass("show");
        currentActiveStep.removeClass('active').prev().removeClass('activated').addClass('active');
        $(document).find('.wizard-fieldset').each(function () {
            if ($(this).hasClass('show')) {
                var activeIndex = $(this).index();
                var indexCount = 1;
                $(document).find('.form-wizard-steps .form-wizard-step-item').each(function () {
                    if (activeIndex == indexCount) {
                        $(this).addClass('active');
                    } else {
                        $(this).removeClass('active');
                    }
                    indexCount++;
                });
            }
        });
    });

    

    $('#disability_status').on('change', function(){
        if($('#disability_status').prop('checked')){
            $('.disabilityItems').fadeIn('fast', function(){
                $('.disabilityItems input[type="checkbox"]').prop('checked', false);
                $('.disabilityAllowance').fadeOut();
                $('.disabilityAllowance input[type="checkbox"]').prop('checked', false);
            });
        }else{
            $('.disabilityItems').fadeOut('fast', function(){
                $('.disabilityItems input[type="checkbox"]').prop('checked', false);
                $('.disabilityAllowance').fadeOut();
                $('.disabilityAllowance input[type="checkbox"]').prop('checked', false);
            });
        }
    });

    $('.disabilityItems input[type="checkbox"]').on('change', function(){
        if($('.disabilityItems input[type="checkbox"]:checked').length > 0){
            $('.disabilityAllowance').fadeIn('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            });
        }else{
            $('.disabilityAllowance').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            });
        }
    });

    $('#is_edication_qualification').on('change', function(){
        if($(this).prop('checked')){
            $('.qualificationAdder').fadeIn('fast');
            $('.educationQualificationTableWrap').fadeIn('fast', function(){
                educationQualTable.init();
            });
        }else{
            $('.qualificationAdder').fadeOut('fast');
            $('.educationQualificationTableWrap').fadeOut('fast', function(){
                $("#query-EQ").val("");
                $("#status-EQ").val("1");
                educationQualTable.init();
            });
        }
    });

    $('#addQualificationForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('addQualificationForm');
    
        document.querySelector('#saveEducationQualification').setAttribute('disabled', 'disabled');
        document.querySelector("#saveEducationQualification svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        let applicantId = $('[name="applicant_id"]', $form).val();
        axios({
            method: "post",
            url: route('applicant.qualification.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                document.querySelector('#saveEducationQualification').removeAttribute('disabled');
                document.querySelector("#saveEducationQualification svg").style.cssText = "display: none;";

                addQualificationModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Eucation Qualification date successfully added.');
                });                
                    
            }
            educationQualTable.init();
        }).catch(error => {
            document.querySelector('#saveEducationQualification').removeAttribute('disabled');
            document.querySelector("#saveEducationQualification svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addQualificationForm .${key}`).addClass('border-danger');
                        $(`#addQualificationForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $("#educationQualTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("applicant.qualification.edit", editId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editQualificationModal input[name="name"]').val(dataset.name ? dataset.name : '');
                    $('#editQualificationModal input[name="highest_academic"]').val(dataset.highest_academic ? dataset.highest_academic : '');
                    $('#editQualificationModal input[name="awarding_body"]').val(dataset.awarding_body ? dataset.awarding_body : '');
                    $('#editQualificationModal input[name="subjects"]').val(dataset.subjects ? dataset.subjects : '');
                    $('#editQualificationModal input[name="result"]').val(dataset.result ? dataset.result : '');
                    $('#editQualificationModal input[name="degree_award_date"]').val(dataset.degree_award_date ? dataset.degree_award_date : '');
                    
                    $('#editQualificationModal input[name="id"]').val(editId);
                }
            })
            .catch((error) => {
                console.log(error);
            });
    });

    $("#editQualificationForm").on("submit", function (e) {
        e.preventDefault();
        let editId = $('#editQualificationForm input[name="id"]').val();
        const form = document.getElementById("editQualificationForm");

        document.querySelector('#updateEducationQualification').setAttribute('disabled', 'disabled');
        document.querySelector('#updateEducationQualification svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route("applicant.qualification.update"),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                document.querySelector("#updateEducationQualification").removeAttribute("disabled");
                document.querySelector("#updateEducationQualification svg").style.cssText = "display: none;";
                editQualificationModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Education Qualification data successfully updated.');
                });
            }
            educationQualTable.init();
        }).catch((error) => {
            document.querySelector("#updateEducationQualification").removeAttribute("disabled");
            document.querySelector("#updateEducationQualification svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editQualificationForm .${key}`).addClass('border-danger')
                        $(`#editQualificationForm  .error-${key}`).html(val)
                    }
                }else if (error.response.status == 304) {
                    editQualificationModal.hide();

                    errorModal.show();
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .errorModalTitle").html("Oops!");
                        $("#errorModal .errorModalDesc").html('No data change found!');
                    });
                } else {
                    console.log("error");
                }
            }
        });
    });

    // Delete Course
    $('#educationQualTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
        });
    });

    // Restore Course
    $('#educationQualTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let courseID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', courseID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
        });
    });

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETE'){
            axios({
                method: 'delete',
                url: route('applicant.qualification.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                    });
                }
                educationQualTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('applicant.qualification.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record Successfully Restored!');
                    });
                }
                educationQualTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

    $('#employment_status').on('change', function(){
        if($(this).val() == 'Unemployed' || $(this).val() == 'Contractor' || $(this).val() == 'Consultant' || $(this).val() == 'Office Holder' || $(this).val() == ''){
            $('.employmentHistoryAdder').fadeOut('fast');
            $('.educationEmploymentTableWrap').fadeOut();
        }else{
            $('.employmentHistoryAdder').fadeIn('fast');
            $('.educationEmploymentTableWrap').fadeIn();
            employmentHistoryTable.init();
        }
    });

    $('.addressPopupToggler').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var wrapid = $btn.attr('data-address-wrap');
        var prefix = $btn.attr('data-prefix');

        $('#addressModal input[name="place"]').val(wrapid);
        $('#addressModal input[name="prefix"]').val(prefix);
        if($(wrapid).hasClass('active')){
            $('#addressModal #student_address_address_line_1').val($(wrapid+' input[name="'+prefix+'_address_line_1"]').val());
            $('#addressModal #student_address_address_line_2').val($(wrapid+' input[name="'+prefix+'_address_line_2"]').val());
            $('#addressModal #student_address_city').val($(wrapid+' input[name="'+prefix+'_address_city"]').val());
            $('#addressModal #student_address_state_province_region').val($(wrapid+' input[name="'+prefix+'_address_state"]').val());
            $('#addressModal #student_address_postal_zip_code').val($(wrapid+' input[name="'+prefix+'_address_postal_zip_code"]').val());
            $('#addressModal #student_address_country').val($(wrapid+' input[name="'+prefix+'_address_country"]').val());
        }
    });

    $('#addressForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        var wrapid = $('input[name="place"]', $form).val();
        var prefix = $('input[name="prefix"]', $form).val();

        document.querySelector('#insertAddress').setAttribute('disabled', 'disabled');
        document.querySelector('#insertAddress svg').style.cssText = 'display: inline-block;';

        var err = 0;
        $('input.required', $form).each(function(){
            if($(this).val() == ''){
                $(this).siblings('.acc__input-error').html('This field is required.');
                err += 1;
            }else{
                $(this).siblings('.acc__input-error').html('');
            }
        });

        if(err > 0){
            document.querySelector('#insertAddress').removeAttribute('disabled');
            document.querySelector('#insertAddress svg').style.cssText = 'display: none;';
        }else{
            document.querySelector('#insertAddress').removeAttribute('disabled');
            document.querySelector('#insertAddress svg').style.cssText = 'display: none;';

            var htmls = '';
            htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_address_line_1', $form).val()+'</span><br/>';
            if($('#student_address_address_line_2', $form).val() != ''){
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_address_line_2', $form).val()+'</span><br/>';
            }
            htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_city', $form).val()+'</span>, ';
            if($('#student_address_state_province_region', $form).val() != ''){
                htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_state_province_region', $form).val()+'</span>, <br/>';
            }else{
                htmls += '<br/>';
            }
            htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_postal_zip_code', $form).val()+'</span>,<br/>';
            htmls += '<span class="text-slate-600 font-medium">'+$('#student_address_country', $form).val()+'</span><br/>';

            htmls += '<input type="hidden" name="'+prefix+'_address" value="'+$('#student_address_address_line_1', $form).val()+'"/>';
            htmls += '<input type="hidden" name="'+prefix+'_address_line_1" value="'+($('#student_address_address_line_1', $form).val() != '' ? $('#student_address_address_line_1', $form).val() : '')+'"/>';
            htmls += '<input type="hidden" name="'+prefix+'_address_line_2" value="'+($('#student_address_address_line_2', $form).val() != '' ? $('#student_address_address_line_2', $form).val() : '')+'"/>';
            htmls += '<input type="hidden" name="'+prefix+'_address_city" value="'+($('#student_address_city', $form).val() != '' ? $('#student_address_city', $form).val() : '')+'"/>';
            htmls += '<input type="hidden" name="'+prefix+'_address_state" value="'+($('#student_address_state_province_region', $form).val() != '' ? $('#student_address_state_province_region', $form).val() : '')+'"/>';
            htmls += '<input type="hidden" name="'+prefix+'_address_postal_zip_code" value="'+($('#student_address_postal_zip_code', $form).val() != '' ? $('#student_address_postal_zip_code', $form).val() : '')+'"/>';
            htmls += '<input type="hidden" name="'+prefix+'_address_country" value="'+($('#student_address_country', $form).val() != '' ? $('#student_address_country', $form).val() : '')+'"/>';

            addressModal.hide();
            $(wrapid).fadeIn().html(htmls).addClass('active');
            $('button[data-address-wrap="'+wrapid+'"] span').html('Update Address')
        }
    });


    $('#addEmployementHistoryForm input[name="continuing"]').on('change', function(){
        if($(this).prop('checked')){
            $('#addEmployementHistoryForm input[name="end_date"]').val('').attr('disabled', 'disabled');
        }else{
            $('#addEmployementHistoryForm input[name="end_date"]').val('').removeAttr('disabled');
        }
    })

    $('#editEmployementHistoryModal input[name="continuing"]').on('change', function(){
        if($(this).prop('checked')){
            $('#editEmployementHistoryModal input[name="end_date"]').val('').attr('disabled', 'disabled');
        }else{
            $('#editEmployementHistoryModal input[name="end_date"]').val('').removeAttr('disabled');
        }
    })


    $('#addEmployementHistoryForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('addEmployementHistoryForm');
    
        document.querySelector('#saveEmpHistory').setAttribute('disabled', 'disabled');
        document.querySelector("#saveEmpHistory svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        let applicantId = $('[name="applicant_id"]', $form).val();
        axios({
            method: "post",
            url: route('applicant.employment.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => { 
            if (response.status == 200) {
                document.querySelector('#saveEmpHistory').removeAttribute('disabled');
                document.querySelector("#saveEmpHistory svg").style.cssText = "display: none;";

                addEmployementHistoryModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Employment History date successfully added.');
                });                
                    
            }
            employmentHistoryTable.init();
        }).catch(error => {
            document.querySelector('#saveEmpHistory').removeAttribute('disabled');
            document.querySelector("#saveEmpHistory svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addEmployementHistoryForm .${key}`).addClass('border-danger');
                        $(`#addEmployementHistoryForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $("#employmentHistoryTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("applicant.employment.edit", editId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;

                $('#editEmployementHistoryModal input[name="company_name"]').val(dataset.company_name ? dataset.company_name : '');
                $('#editEmployementHistoryModal input[name="company_phone"]').val(dataset.company_phone ? dataset.company_phone : '');
                $('#editEmployementHistoryModal input[name="position"]').val(dataset.position ? dataset.position : '');
                $('#editEmployementHistoryModal input[name="start_date"]').val(dataset.start_date ? dataset.start_date : '');
                if(dataset.continuing && dataset.continuing == 1){
                    $('#editEmployementHistoryModal input[name="continuing"]').prop('checked', true);
                    $('#editEmployementHistoryModal input[name="end_date"]').val('').attr('disabled', 'disabled');
                }else{
                    $('#editEmployementHistoryModal input[name="continuing"]').prop('checked', false);
                    $('#editEmployementHistoryModal input[name="end_date"]').val(dataset.end_date ? dataset.end_date : '').removeAttr('disabled');
                }

                if(dataset.address_line_1 != '' || dataset.city != '' || dataset.post_code != '' || dataset.country != ''){
                    var htmls = '';
                    htmls += '<span class="text-slate-600 font-medium">'+dataset.address_line_1+'</span><br/>';
                    if(dataset.address_line_2 != ''){
                        htmls += '<span class="text-slate-600 font-medium">'+dataset.address_line_2+'</span><br/>';
                    }
                    htmls += '<span class="text-slate-600 font-medium">'+dataset.city+'</span>, ';
                    if(dataset.state != ''){
                        htmls += '<span class="text-slate-600 font-medium">'+dataset.state+'</span>, <br/>';
                    }else{
                        htmls += '<br/>';
                    }
                    htmls += '<span class="text-slate-600 font-medium">'+dataset.post_code+'</span>,<br/>';
                    htmls += '<span class="text-slate-600 font-medium">'+dataset.country+'</span><br/>';

                    htmls += '<input type="hidden" name="employment_address" value="'+dataset.address_line_1+'"/>';
                    htmls += '<input type="hidden" name="employment_address_line_1" value="'+(dataset.address_line_1 != '' ? dataset.address_line_1 : '')+'"/>';
                    htmls += '<input type="hidden" name="employment_address_line_2" value="'+(dataset.address_line_2 != '' ? dataset.address_line_2 : '')+'"/>';
                    htmls += '<input type="hidden" name="employment_address_city" value="'+(dataset.city != '' ? dataset.city : '')+'"/>';
                    htmls += '<input type="hidden" name="employment_address_state" value="'+(dataset.state != '' ? dataset.state : '')+'"/>';
                    htmls += '<input type="hidden" name="employment_address_postal_zip_code" value="'+(dataset.post_code != '' ? dataset.post_code : '')+'"/>';
                    htmls += '<input type="hidden" name="employment_address_country" value="'+(dataset.country != '' ? dataset.country : '')+'"/>';

                    $('#editEmpHistoryAddress').fadeIn().html(htmls).addClass('active');
                    $('#editEmployementHistoryModal .addressPopupToggler span').html('Update Address');
                }else{
                    $('#editEmpHistoryAddress').fadeOut().html('').removeClass('active');
                    $('#editEmployementHistoryModal .addressPopupToggler span').html('Add Address');
                }

                $('#editEmployementHistoryModal input[name="contact_name"]').val(dataset.reference[0].name ? dataset.reference[0].name : '');
                $('#editEmployementHistoryModal input[name="contact_position"]').val(dataset.reference[0].position ? dataset.reference[0].position : '');
                $('#editEmployementHistoryModal input[name="contact_phone"]').val(dataset.reference[0].phone ? dataset.reference[0].phone : '');
                $('#editEmployementHistoryModal input[name="contact_email"]').val(dataset.reference[0].email ? dataset.reference[0].email : '');
                $('#editEmployementHistoryModal input[name="ref_id"]').val(dataset.reference[0].id ? dataset.reference[0].id : '');
                
                $('#editEmployementHistoryModal input[name="id"]').val(editId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editEmployementHistoryForm").on("submit", function (e) {
        e.preventDefault();
        let editId = $('#editEmployementHistoryForm input[name="id"]').val();
        const form = document.getElementById("editEmployementHistoryForm");

        document.querySelector('#updateEmpHistory').setAttribute('disabled', 'disabled');
        document.querySelector('#updateEmpHistory svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route("applicant.employment.update"),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                document.querySelector("#updateEmpHistory").removeAttribute("disabled");
                document.querySelector("#updateEmpHistory svg").style.cssText = "display: none;";
                editEmployementHistoryModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Employment History data successfully updated.');
                });
            }
            employmentHistoryTable.init();
        }).catch((error) => {
            document.querySelector("#updateEmpHistory").removeAttribute("disabled");
            document.querySelector("#updateEmpHistory svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editEmployementHistoryForm .${key}`).addClass('border-danger')
                        $(`#editEmployementHistoryForm  .error-${key}`).html(val)
                    }
                }else if (error.response.status == 304) {
                    editEmployementHistoryModal.hide();

                    errorModal.show();
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .errorModalTitle").html("Oops!");
                        $("#errorModal .errorModalDesc").html('No data change found!');
                    });
                } else {
                    console.log("error");
                }
            }
        });
    });

    // Delete Course
    $('#employmentHistoryTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETEEH');
        });
    });

    // Restore Course
    $('#employmentHistoryTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let courseID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', courseID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTOREEH');
        });
    });

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETEEH'){
            axios({
                method: 'delete',
                url: route('applicant.employment.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                    });
                }
                employmentHistoryTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTOREEH'){
            axios({
                method: 'post',
                url: route('applicant.employment.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record Successfully Restored!');
                    });
                }
                employmentHistoryTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })
    

    $('#referral_code').on('keyup paste', function(){
        var $input = $(this);
        var $btn = $(this).siblings('#varifiedReferral');
        var $orgStatusInput = $(this).siblings('.is_referral_varified');

        var orgCode = $input.attr('data-org');
        var code = $input.val();
        var orgStatus = $orgStatusInput.attr('data-org');
        var status = $orgStatusInput.val();
        if(code != orgCode){
            $btn.css({'display': 'inline-flex'});
            $input.css({'border-color': 'red'});
            $input.closest('form').find('.form-wizard-next-btn').attr('disabled', 'disabled');
            $orgStatusInput.val('0');
            status = 0;
        }else if(code == orgCode){
            $btn.fadeOut();
            $input.css({'border-color': 'rgba(226, 232, 240, 1)'});
            $input.closest('form').find('.form-wizard-next-btn').removeAttr('disabled');
            $orgStatusInput.val(orgStatus);
            status = orgStatus;
        }else{
            $input.val(orgCode)
            $btn.fadeOut();
            $input.css({'border-color': 'rgba(226, 232, 240, 1)'});
            $input.closest('form').find('.form-wizard-next-btn').removeAttr('disabled');
            $orgStatusInput.val(orgStatus);
            status = orgStatus;
        }
    });

    $('#varifiedReferral').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var $input = $btn.siblings('#referral_code');
        var $orgStatusInput = $btn.siblings('.is_referral_varified');

        if(!$btn.hasClass('verified')){
            var applicantId = $btn.attr('data-applicant-id');
            var code = $btn.siblings('#referral_code').val();
            $btn.attr('disabled', 'disabled');

            axios({
                method: 'POST',
                url: route('applicant.application.verify.referral.code'),
                data: {applicantId : applicantId, code : code},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $input.closest('form').find('.form-wizard-next-btn').removeAttr('disabled');
                    if(response.data.msg.suc == 1){
                        $btn.removeAttr('disabled').html('<i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Verified').removeClass('btn-danger').addClass('btn-primary verified');
                        $input.css({'border-color': 'rgba(226, 232, 240, 1)'}).attr('data-org', response.data.msg.code).attr('readonly', 'readonly');
                        $orgStatusInput.val(response.data.msg.is_referral_varified).attr('data-org', response.data.msg.is_referral_varified);
                        $input.parent('.validationGroup').siblings('.error-verificationError').html('');

                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }else{
                        $btn.fadeOut().removeAttr('disabled');
                        $input.val('').css({'border-color': 'rgba(226, 232, 240, 1)'});
                        $orgStatusInput.val('0');
                        $input.parent('.validationGroup').siblings('.error-verificationError').html('Referral code does not match. Please insert a valid one.')
                    }

                    setTimeout(function(){
                        $input.parent('.validationGroup').siblings('.error-verificationError').html('');
                    }, 2000)
                }
            }).catch(error =>{
                if (error.response){
                    console.log(error)
                }
            });
        }
    })

    $('#student_loan').on('change', function(){
        var $this = $(this);
        if($this.val() == 'Others'){
            $('.studentLoanEnglandFunding').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.studentLoanFundReceipt').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.studentLoanApplied').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.otherFundings').fadeIn('fast', function(){
                $('input', this).val('');
            })
        }else if($this.val() == 'Student Loan'){
            $('.studentLoanEnglandFunding').fadeIn('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.studentLoanFundReceipt').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.studentLoanApplied').fadeIn('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.otherFundings').fadeOut('fast', function(){
                $('input', this).val('');
            })
        }else{
            $('.studentLoanEnglandFunding').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.studentLoanFundReceipt').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.studentLoanApplied').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
            $('.otherFundings').fadeOut('fast', function(){
                $('input', this).val('');
            })
        }
    })

    $('#student_finance_england').on('change', function(){
        if($(this).prop('checked')){
            $('.studentLoanFundReceipt').fadeIn('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
        }else{
            $('.studentLoanFundReceipt').fadeOut('fast', function(){
                $('input[type="checkbox"]', this).prop('checked', false);
            })
        }
    })
})();