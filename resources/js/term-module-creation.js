import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import IMask from 'imask';



("use strict");
var termModuleCreationsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let courses = $("#courses").val() != "" ? $("#courses").val() : "";
        let instance_term = $("#instance_term-mc").val() != "" ? $("#instance_term-mc").val() : "";

        let tableContent = new Tabulator("#termModuleCreationsListTable", {
            ajaxURL: route("term.module.creation.list"),
            ajaxParams: { courses: courses, instance_term: instance_term },
            ajaxFiltering: true,
            ajaxSorting: false,
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
                    title: "Course",
                    field: "course_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Name",
                    field: "term_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Term",
                    field: "term",
                    headerHozAlign: "left",
                },
                {
                    title: "Session",
                    field: "session_term",
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
                    title: "Modules",
                    field: "modules_count",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if(cell.getData().modules_count > 0) {
                            btns += '<a href="'+route('term.module.creation.show', cell.getData().id)+'" class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
                        } else {
                            btns += '<a href="'+route('term.module.creation.add', { instanceTermId : cell.getData().id, courseId : cell.getData().course_id})+'" class="btn btn-success text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="plus-circle" class="w-4 h-4"></i></a>';
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
            },
        });

        // Export
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Term Module Creation",
            });
        });

        $("#tabulator-export-html").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
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


var termModuleListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-TMC").val() != "" ? $("#query-TMC").val() : "";
        let status = $("#status-TMC").val() != "" ? $("#status-TMC").val() : "";
        let terminstanceid = $("#termModuleListTable").attr('data-terminstanceid') != "" ? $("#termModuleListTable").attr('data-terminstanceid') : "";

        let tableContent = new Tabulator("#termModuleListTable", {
            ajaxURL: route("term.module.creation.module.list"),
            ajaxParams: { terminstanceid: terminstanceid, querystr: querystr, status: status },
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
                    title: "Module",
                    field: "module_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Code",
                    field: "code",
                    headerHozAlign: "left",
                },
                {
                    title: "Credit",
                    field: "credit_value",
                    headerHozAlign: "left",
                },
                {
                    title: "Unit",
                    field: "unit_value",
                    headerHozAlign: "left",
                },
                {
                    title: "Key",
                    field: "moodle_enrollment_key",
                    headerHozAlign: "left",
                },
                {
                    title: "Submission",
                    field: "submission_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Type",
                    field: "class_type",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "280",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if(cell.getData().assessment_count > 0){
                            btns += '<a data-modulecreationid="'+cell.getData().id+'" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#viewModuleAssessmentModal" class="view_assessment btn-round btn btn-linkedin text-xs text-white px-2 py-1 ml-1"><i data-lucide="eye-off" class="w-4 h-4 mr-2"></i> Assessment</a>';
                        }else{
                            btns += '<a data-modulecreationid="'+cell.getData().id+'" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#addModuleAssessmentModal" class="add_assessment btn-round btn btn-success text-xs text-white px-2 py-1 ml-1"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Assessment</a>';
                        }
                        btns += '<a data-modulecreationid="'+cell.getData().id+'" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#editModuleCreationModal" class="eidt_module btn btn-primary text-white btn-round text-xs ml-1 px-2 py-1"><i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Module</a>';
                        
                        
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
        });

        // Export
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Term Module Creation",
            });
        });

        $("#tabulator-export-html").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
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
    $(".datepicker").each(function () {
        var maskOptions = {
            mask: '00-00-0000'
        };
        var mask = IMask(this, maskOptions);
    });

    if($('#termModuleListTable').length > 0){
        // Init Table
        termModuleListTable.init();

        // Filter function
        function moduleFilterHTMLForm() {
            termModuleListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-TMC")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    moduleFilterHTMLForm();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-TMC").on("click", function (event) {
            moduleFilterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-TMC").on("click", function (event) {
            $("#query-TMC").val("");
            $("#status-TMC").val("1");
            moduleFilterHTMLForm();
        });

        const successModalMCAS = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModalMCAS"));
        const viewModuleAssessmentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewModuleAssessmentModal"));
        const addModuleAssessmentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModuleAssessmentModal"));
        const editModuleCreationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModuleCreationModal"));

        const editModuleCreationModalEl = document.getElementById('editModuleCreationModal')
        editModuleCreationModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editModuleCreationModal .acc__input-error').html('');
            $('#editModuleCreationModal .modal-body input[type="text"]').val('');
            $('#editModuleCreationModal .modal-body select').val('');
            $('#courseModuleEditModal input[name="id"]').val('0');
        });

        const viewModuleAssessmentModalEl = document.getElementById('viewModuleAssessmentModal')
        viewModuleAssessmentModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#viewModuleAssessmentModal .moduleName').text('Module Name');
            $('#viewModuleAssessmentModal .theContent').fadeOut('fast', function(){
                $('#viewModuleAssessmentModal .modal-body .theContent').html('');
                $('#viewModuleAssessmentModal .theLoader').css('display', 'flex');
            });
            $('#viewModuleAssessmentModal input[name="module_creation_id"]').val(0);
        });

        const addModuleAssessmentModalEl = document.getElementById('addModuleAssessmentModal')
        addModuleAssessmentModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addModuleAssessmentModal .moduleName').text('Module Name');
            $('#addModuleAssessmentModal .theContent').fadeOut('fast', function(){
                $('#addModuleAssessmentModal .modal-body .theContent').html('');
                $('#addModuleAssessmentModal .theLoader').css('display', 'flex');
            });
            $('#addModuleAssessmentModal input[name="module_creation_id"]').val(0);
        });

        $('#termModuleListTable').on('click', '.view_assessment', function(e){
            var $btn = $(this);
            var moduleCreationId = $btn.attr('data-modulecreationid');

            axios({
                method: "post",
                url: route('term.module.creation.module.view.assessments'),
                data: {moduleCreationId : moduleCreationId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#viewModuleAssessmentModal .moduleName').text(response.data.moduleName);
                    $('#viewModuleAssessmentModal .theLoader').css('display', 'none');
                    $('#viewModuleAssessmentModal .theContent').fadeIn('fast', function(){
                        $('#viewModuleAssessmentModal .modal-body .theContent').html(response.data.html);
                    });
                    $('#viewModuleAssessmentModal input[name="module_creation_id"]').val(moduleCreationId);
                }
            }).catch(error => {
                console.log('error');
            });
        });

        $('#viewModuleAssessmentForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('viewModuleAssessmentForm');
    
            document.querySelector('#updateAssessments').setAttribute('disabled', 'disabled');
            document.querySelector('#updateAssessments svg').style.cssText = 'display: inline-block;';
    
            let form_data = new FormData(form);
    
            var assessmentLength = $form.find('input.cmb_assessment:checked').length;
            if(assessmentLength > 0){
                axios({
                    method: "post",
                    url: route('assessment.update'),
                    data: form_data,
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    document.querySelector('#updateAssessments').removeAttribute('disabled');
                    document.querySelector('#updateAssessments svg').style.cssText = 'display: none;';
                    
                    if (response.status == 200) {
                        viewModuleAssessmentModal.hide();
                        termModuleListTable.init();
                        
                        successModalMCAS.show();
                        document.getElementById('successModalMCAS').addEventListener('shown.tw.modal', function(event){
                            $('#successModalMCAS .successModalTitleMCAS').html('Congratulations!');
                            $('#successModalMCAS .successModalDescMCAS').html('Module Creation Assessments data successfully updated.');
                        });

                        setTimeout(function(){
                            successModalMCAS.hide();
                        }, 5000)
                    }
                    
                }).catch(error => {
                    document.querySelector('#updateAssessments').removeAttribute('disabled');
                    document.querySelector('#updateAssessments svg').style.cssText = 'display: none;';
                    if (error.response) {
                        if (error.response.status == 422) {
                            $('.df_alert', $form).remove();
                            $('.modal-content', $form).prepend('<div class="df_alert alert alert-danger-soft show flex items-start" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> &nbsp; Something went wrong please try later or contact with the administrator.</div>')
                            
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });
        
                            setTimeout(function(){
                                $('.df_alert', $form).remove();
                            }, 5000);
                        } else {
                            console.log('error');
                        }
                    }
                });
            }else{
                $('.df_alert', $form).remove();
                $('.modal-content', $form).prepend('<div class="df_alert alert alert-danger-soft show flex items-start" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> &nbsp; Assessments are required. Please checked at least 1 assessment for this module.</div>')
                
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
    
                setTimeout(function(){
                    $('.df_alert', $form).remove();
                }, 5000);
            }
        });
        

        $('#termModuleListTable').on('click', '.add_assessment', function(e){
            var $btn = $(this);
            var moduleCreationId = $btn.attr('data-modulecreationid');

            axios({
                method: "post",
                url: route('term.module.creation.module.add.assessments'),
                data: {moduleCreationId : moduleCreationId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#addModuleAssessmentModal .moduleName').text(response.data.moduleName);
                    $('#addModuleAssessmentModal .theLoader').css('display', 'none');
                    $('#addModuleAssessmentModal .theContent').fadeIn('fast', function(){
                        $('#addModuleAssessmentModal .modal-body .theContent').html(response.data.html);
                    });
                    $('#addModuleAssessmentModal input[name="module_creation_id"]').val(moduleCreationId);
                }
            }).catch(error => {
                console.log('error');
            });
        });

        $('#addModuleAssessmentForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('addModuleAssessmentForm');
    
            document.querySelector('#addMAssessments').setAttribute('disabled', 'disabled');
            document.querySelector('#addMAssessments svg').style.cssText = 'display: inline-block;';
    
            let form_data = new FormData(form);
    
            var assessmentLength = $form.find('input.cmb_assessment:checked').length;
            if(assessmentLength > 0){
                axios({
                    method: "post",
                    url: route('assessment.store'),
                    data: form_data,
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    document.querySelector('#addMAssessments').removeAttribute('disabled');
                    document.querySelector('#addMAssessments svg').style.cssText = 'display: none;';
                    
                    if (response.status == 200) {
                        addModuleAssessmentModal.hide();
                        termModuleListTable.init();
                        
                        successModalMCAS.show();
                        document.getElementById('successModalMCAS').addEventListener('shown.tw.modal', function(event){
                            $('#successModalMCAS .successModalTitleMCAS').html('Congratulations!');
                            $('#successModalMCAS .successModalDescMCAS').html('Module Creation Assessments data successfully Inserted.');
                        });

                        setTimeout(function(){
                            successModalMCAS.hide();
                        }, 5000)
                    }
                    
                }).catch(error => {
                    document.querySelector('#addMAssessments').removeAttribute('disabled');
                    document.querySelector('#addMAssessments svg').style.cssText = 'display: none;';
                    if (error.response) {
                        if (error.response.status == 422) {
                            $('.df_alert', $form).remove();
                            $('.modal-content', $form).prepend('<div class="df_alert alert alert-danger-soft show flex items-start" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> &nbsp; Something went wrong please try later or contact with the administrator.</div>')
                            
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });
        
                            setTimeout(function(){
                                $('.df_alert', $form).remove();
                            }, 5000);
                        } else {
                            console.log('error');
                        }
                    }
                });
            }else{
                $('.df_alert', $form).remove();
                $('.modal-content', $form).prepend('<div class="df_alert alert alert-danger-soft show flex items-start" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> &nbsp; Assessments are required. Please checked at least 1 assessment for this module.</div>')
                
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
    
                setTimeout(function(){
                    $('.df_alert', $form).remove();
                }, 5000);
            }
        });


        $("#termModuleListTable").on("click", ".eidt_module", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-modulecreationid");

            axios({
                method: "get",
                url: route("term.module.creation.edit", editId),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editModuleCreationModal input[name="module_name"]').val(dataset.module_name ? dataset.module_name : '');
                    $('#editModuleCreationModal select[name="status"]').val(dataset.status ? dataset.status : '');
                    $('#editModuleCreationModal input[name="code"]').val(dataset.code ? dataset.code : '');
                    $('#editModuleCreationModal input[name="credit_value"]').val(dataset.credit_value ? dataset.credit_value : '');
                    $('#editModuleCreationModal input[name="unit_value"]').val(dataset.unit_value ? dataset.unit_value : '');
                    $('#editModuleCreationModal input[name="moodle_enrollment_key"]').val(dataset.moodle_enrollment_key ? dataset.moodle_enrollment_key : '');
                    $('#editModuleCreationModal input[name="submission_date"]').val(dataset.submission_date ? dataset.submission_date : '');
                    $('#editModuleCreationModal select[name="class_type"]').val(dataset.class_type ? dataset.class_type : '');
                    
                    $('#editModuleCreationModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $('#editModuleCreationForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('editModuleCreationForm');

            $('#editModuleCreationForm').find('input').removeClass('border-danger')
            $('#editModuleCreationForm').find('.acc__input-error').html('')

            document.querySelector('#updateModuleCreation').setAttribute('disabled', 'disabled');
            document.querySelector('#updateModuleCreation svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('term.module.creation.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateModuleCreation').removeAttribute('disabled');
                document.querySelector('#updateModuleCreation svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    editModuleCreationModal.hide();
                    termModuleListTable.init();
                    
                    successModalMCAS.show();
                    document.getElementById('successModalMCAS').addEventListener('shown.tw.modal', function(event){
                        $('#successModalMCAS .successModalTitleMCAS').html('Congratulations!');
                        $('#successModalMCAS .successModalDescMCAS').html('Module Creation data successfully updated.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#updateModuleCreation').removeAttribute('disabled');
                document.querySelector('#updateModuleCreation svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editModuleCreationForm .${key}`).addClass('border-danger')
                            $(`#editModuleCreationForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });
    }


    if($('#termModulCreationsStepWizard').length > 0){
        const warningModalMCRD = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModalMCRD"));
        const successModalMCRD = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModalMCRD"));


        $('.form-wizard-next-btn').on('click', function () {
            var parentFieldset = $(this).parents('.wizard-fieldset');
            //var currentActiveStep = $(this).parents('.form-wizard').find('.form-wizard-steps .active');
            var next = $(this);
            var nextWizardStep = true;

            next.attr('disabled', 'disabled');
            $('svg', next).fadeIn('fast');

            var assessmentLength = parentFieldset.find('input.cmb_assessment:checked').length;
            if(assessmentLength  == 0){
                nextWizardStep = false;
                parentFieldset.find('.assessmentError').fadeIn().html('Assessments are required. Please checked at least 1 assessment for this module.');
                next.removeAttr('disabled', 'disabled');
                $('svg', next).fadeOut('fast');
            }else{
                parentFieldset.find('.assessmentError').fadeOut().html('');
            }
            
            if (nextWizardStep) {
                /* Save the step form */
                let currentStepFormID = parentFieldset.find('form').attr('id');
                const form = document.getElementById(currentStepFormID);

                let form_data = new FormData(form);

                axios({
                    method: "post",
                    url: route('assessment.store'),
                    data: form_data,
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    next.removeAttr('disabled', 'disabled');
                    $('svg', next).fadeOut('fast');
                    console.log(response.data)
                }).catch(error => {
                    nextWizardStep = false;

                    next.removeAttr('disabled', 'disabled');
                    $('svg', next).fadeOut('fast');

                    if (error.response) {
                        if (error.response.status == 422) {
                            warningModalMCRD.show();
                            document.getElementById('warningModalMCRD').addEventListener('shown.tw.modal', function(event){
                                $('#warningModalMCRD .warningModalTitleMCRD').html('Oops!');
                                $('#warningModalMCRD .warningModalDescMCRD').html('Something went wrong! Please try later or contact with administrator.');
                            });

                            setTimeout(function(){
                                warningModalMCRD.hide();
                            }, 5000);
                        } else {
                            console.log('error');
                        }
                    }
                });

                if(nextWizardStep){
                    if(parentFieldset.hasClass('wizard-last-step')){successModalMCRD
                        successModalMCRD.show();
                        document.getElementById('successModalMCRD').addEventListener('shown.tw.modal', function(event){
                            $('#successModalMCRD .successModalTitleMCRD').html('Congratulations!');
                            $('#successModalMCRD .successModalDescMCRD').html('Assessments are successfully add inserted. Click ok to get back to the list.');
                            $('#successModalMCRD a').attr('href', response.data.red);
                        });

                        setTimeout(function(){
                            window.location.href = route('term.module.creation');
                        }, 3000);
                    }else{
                        next.parents('.wizard-fieldset').removeClass("show");
                        next.parents('.wizard-fieldset').next('.wizard-fieldset').addClass("show");
                        $(document).find('.wizard-fieldset').each(function () {
                            if ($(this).hasClass('show')) {
                                var formAtrr = $(this).attr('data-tab-content');
                                
                            }
                        });
                    }
                        
                }
            }
        });
        //click on previous button
        $('.form-wizard-previous-btn').on('click', function () {
            var counter = parseInt($(".wizard-counter").text());
            ;
            var prev = $(this);
            prev.parents('.wizard-fieldset').removeClass("show");
            prev.parents('.wizard-fieldset').prev('.wizard-fieldset').addClass("show");
            $(document).find('.wizard-fieldset').each(function () {
                if ($(this).hasClass('show')) {
                    var formAtrr = $(this).attr('data-tab-content');
                }
            });
        });
        

    }
    
    if($('#termModuleCreationFormStp1').length > 0){
        const successModalMCR = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModalMCR"));
        const warningModalMCR = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModalMCR"));

        $('.availableModules .singleModule').on('dblclick', function(){
            let baseEL = $(this);
            if(!baseEL.hasClass('alreadySelected')){
                let moduleId = baseEL.attr('data-modid');
                var inputs = '<input type="hidden" name="moduleid[]" value="'+moduleId+'"/>';
                let copiedEL = baseEL.clone(true);
                copiedEL.append(inputs);

                $('.selectedElements .seError').fadeOut('fast', function(){
                    $('.selectedElements').append(copiedEL);
                    baseEL.addClass('alreadySelected');
                    $('#saveandcontinue').removeAttr('disabled');
                });


                let abailableModule = $('.availableModules .singleModule:not(.alreadySelected)').length;
                if(abailableModule == 0){
                    $('.availableModules .baseError').fadeIn('fast');
                }
            }
        });

        $('.selectedElements').on('dblclick', '.singleModule', function(){
            let selectEl = $(this);
            var terminstantid = selectEl.attr('data-terminstantid');
            var modid = selectEl.attr('data-modid');

            var cls = '.singleModule_'+terminstantid+'_'+modid;

            $('.selectedElements .singleModule'+cls).remove();
            $('.availableModules .singleModule'+cls).removeClass('alreadySelected');

            let abailableModule = $('.availableModules .singleModule:not(.alreadySelected)').length;
            if(abailableModule > 0){
                $('.availableModules .baseError').fadeOut('fast');
            }

            var selectedLength = $('.selectedElements .singleModule').length;
            if(selectedLength == 0){
                $('.selectedElements .seError').fadeIn('fast', function(){
                    $('#saveandcontinue').attr('disabled', 'disabled');
                })
            }
        })
        
        $('#termModuleCreationFormStp1').on('submit', function(e){
            e.preventDefault();
            let $form = $(this);
            const form = document.getElementById('termModuleCreationFormStp1');

            $('#termModuleCreationFormStp1').find('input').removeClass('border-danger')
            $('#termModuleCreationFormStp1').find('.acc__input-error').html('')

            $('#saveandcontinue').attr('disabled', 'disabled');
            $('#saveandcontinue svg').fadeIn('fast');

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('term.module.creation.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('#saveandcontinue').removeAttr('disabled', 'disabled');
                $('#saveandcontinue svg').fadeOut('fast');
                
                if (response.status == 200) {
                    successModalMCR.show();
                    document.getElementById('successModalMCR').addEventListener('shown.tw.modal', function(event){
                        $('#successModalMCR .successModalTitleMCR').html('Congratulations!');
                        $('#successModalMCR .successModalDescMCR').html(response.data.message);
                        $('#successModalMCR .mcrRedirect').attr('href', response.data.red);
                    });

                    setTimeout(function(){
                        window.location.href = response.data.red;
                    }, 5000);
                }
                
            }).catch(error => {
                $('#saveandcontinue').removeAttr('disabled', 'disabled');
                $('#saveandcontinue svg').fadeOut('fast');
                if (error.response) {
                    if (error.response.status == 422) {
                        warningModalMCR.show();
                        document.getElementById('warningModalMCR').addEventListener('shown.tw.modal', function(event){
                            $('#warningModalMCR .warningModalTitle').html('Oops!');
                            $('#warningModalMCR .warningModalDesc').html('Something went wrong! Please try later or contact with administrator.');
                        });

                        setTimeout(function(){
                            warningModalMCR.hide();
                        }, 5000)
                    } else {
                        console.log('error');
                    }
                }
            });

        });
    }


    if($("#termModuleCreationsListTable").length) {
        // Init Table
        termModuleCreationsListTable.init();

        // Filter function
        function filterHTMLForm() {
            termModuleCreationsListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-mc")[0].addEventListener(
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
        $("#tabulator-html-filter-go-mc").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-mc").on("click", function (event) {
            $("#query-mc").val("");
            $("#status-mc").val("1");
            $("#instance_term-mc").val("");
            $("#course_module-mc").val("");
            $("#module_level-mc").val("");
            filterHTMLForm();
        });


        /*const addModuleCreationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModuleCreationModal"));
        const editModuleCreationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModuleCreationModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModalMCR = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalMCR"));

        let confModalDelTitle = 'Are you sure?';
        let confModalDelDescription = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescription = 'Do you really want to re-store these records? Click agree to continue.';

        const addModuleCreationModalEl = document.getElementById('addModuleCreationModal')
        addModuleCreationModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addModuleCreationModal .acc__input-error').html('');
            $('#addModuleCreationModal .modal-body input[type="text"]').val('');
            $('#addModuleCreationModal .modal-body select').val('');
        });
        
        const editModuleCreationModalEl = document.getElementById('editModuleCreationModal')
        editModuleCreationModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editModuleCreationModal .acc__input-error').html('');
            $('#editModuleCreationModal .modal-body input[type="text"]').val('');
            $('#editModuleCreationModal .modal-body select').val('');
            $('#courseModuleEditModal input[name="id"]').val('0');
        });

        const confirmModalMCREL = document.getElementById('confirmModalMCR');
        confirmModalMCREL.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModalMCR .agreeWithMCR').attr('data-id', '0');
            $('#confirmModalMCR .agreeWithMCR').attr('data-action', 'none');
        });*/

    }
})();