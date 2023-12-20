import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import {createApp} from 'vue'

("use strict");
var admissionListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let semesters = $("#semesters-ADM").val() != "" ? $("#semesters-ADM").val() : "";
        let courses = $("#courses-ADM").val() != "" ? $("#courses-ADM").val() : "";
        let statuses = $("#courses-ADM").val() != "" ? $("#statuses-ADM").val() : "";
        let refno = $("#refno-ADM").val() != "" ? $("#refno-ADM").val() : "";
        let firstname = $("#firstname-ADM").val() != "" ? $("#firstname-ADM").val() : "";
        let lastname = $("#lastname-ADM").val() != "" ? $("#lastname-ADM").val() : "";
        let dob = $("#dob-ADM").val() != "" ? $("#dob-ADM").val() : "";

        let tableContent = new Tabulator("#admissionListTable", {
            ajaxURL: route("admission.list"),
            ajaxParams: { semesters: semesters, courses: courses, statuses: statuses, refno: refno, firstname: firstname, lastname: lastname, dob: dob},
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
                    title: "Ref. No",
                    field: "application_no",
                    headerHozAlign: "left",
                },
                {
                    title: "First Name",
                    field: "first_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Last Name",
                    field: "last_name",
                    headerHozAlign: "left",
                },
                {
                    title: "DOB",
                    field: "date_of_birth",
                    headerHozAlign: "left",
                },
                {
                    title: "Gender",
                    field: "gender",
                    headerHozAlign: "left",
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
                },
                {
                    title: "Status",
                    field: "status_id",
                    headerHozAlign: "left",
                }
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            },
            rowClick:function(e, row){
                window.open(row.getData().url, '_blank');
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
        $("#tabulator-export-csv-ADM").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-ADM").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-ADM").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Admission Details",
            });
        });

        $("#tabulator-export-html-ADM").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-ADM").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

var educationQualTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId = $("#educationQualTable").attr('data-applicant') != "" ? $("#educationQualTable").attr('data-applicant') : "0";
        let querystr = $("#query-EQ").val() != "" ? $("#query-EQ").val() : "";
        let status = $("#status-EQ").val() != "" ? $("#status-EQ").val() : "";

        let tableContent = new Tabulator("#educationQualTable", {
            ajaxURL: route("qualification.list"),
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
                sheetName: "Education Qualification Details",
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
            ajaxURL: route("employment.list"),
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
                    title: "Company",
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
                sheetName: "Employment History Details",
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

(function(){
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    
    //var employment_status = new TomSelect('#employment_status', tomOptions);

    $('.addmissionLccTom').each(function(){
        if ($(this).attr("multiple") !== undefined) {
            tomOptions = {
                ...tomOptions,
                plugins: {
                    ...tomOptions.plugins,
                    remove_button: {
                        title: "Remove this item",
                    },
                }
            };
        }
        new TomSelect(this, tomOptions);
    })

    if($('#admissionListTable').length > 0){
        var semestersADM = new TomSelect('#semesters-ADM', tomOptions);
        var coursesADM = new TomSelect('#courses-ADM', tomOptions);
        var statusesADM = new TomSelect('#statuses-ADM', tomOptions);

        // Init Table
        admissionListTable.init();

        // Filter function
        function filterHTMLFormADM() {
            admissionListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-ADM")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormADM();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-ADM").on("click", function (event) {
            filterHTMLFormADM();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-ADM").on("click", function (event) {
            semestersADM.clear(true);
            coursesADM.clear(true);
            statusesADM.clear(true);

            $("#refno-ADM").val('');
            $("#firstname-ADM").val('');
            $("#lastname-ADM").val('');
            $("#dob-ADM").val('');

            filterHTMLFormADM();
        });
    }

    if($('#educationQualTable').length > 0){
        if($('#educationQualTable').hasClass('activeTable')){
            educationQualTable.init();
        }
        // Filter function
        function filterHTMLFormEQ() {
            educationQualTable.init();
        }

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

        const addQualificationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addQualificationModal"));
        const editQualificationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editQualificationModal"));
        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
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

        $('#addQualificationForm').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('addQualificationForm');
        
            document.querySelector('#saveEducationQualification').setAttribute('disabled', 'disabled');
            document.querySelector("#saveEducationQualification svg").style.cssText ="display: inline-block;";
    
            let form_data = new FormData(form);
            let applicantId = $('[name="applicant_id"]', $form).val();
            axios({
                method: "post",
                url: route('qualification.store'),
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
                url: route("qualification.edit", editId),
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
                url: route("qualification.update"),
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
                    url: route('qualification.destory', recordID),
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
                    url: route('qualification.restore', recordID),
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
    }

    if($('#employmentHistoryTable').length > 0){
        if($('#employmentHistoryTable').hasClass('activeTable')){
            employmentHistoryTable.init();
        }

        // Filter function
        function filterHTMLFormEH() {
            employmentHistoryTable.init();
        }

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

        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';
        const addEmployementHistoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addEmployementHistoryModal"));
        const editEmployementHistoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEmployementHistoryModal"));
        const confirmEmploymentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmEmploymentModal"));
        const confirmEmploymentModalEl = document.getElementById('confirmEmploymentModal')
        confirmEmploymentModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#confirmEmploymentModal .confModTitle').html('');
            $('#confirmEmploymentModal .confModDesc').html('');
            $('#confirmEmploymentModal .agreeWith').attr('data-status', 'none').removeAttr('disabled');
        });

        
        var employment_status = new TomSelect('#employment_status', tomOptions);


        $('#employment_status').on('change', function(){
            var employmentStatus = $(this).val();
            var applicantId = $(this).attr('data-applicant');
            
            confirmEmploymentModal.show();
            if($(this).val() == ''){
                $('#confirmEmploymentModal .confModTitle').html('Oops!');
                $('#confirmEmploymentModal .confModDesc').html('Employment status can not be empty. Please select a valid one.');
                $('#confirmEmploymentModal .agreeWith').attr('data-status', 'none').attr('disabled', 'disabled');
            }else if($(this).val() == 'Unemployed' || $(this).val() == 'Contractor' || $(this).val() == 'Consultant' || $(this).val() == 'Office Holder'){
                $('#confirmEmploymentModal .confModTitle').html('Are you sure?');
                $('#confirmEmploymentModal .confModDesc').html('You want to change students employment status? All existing employment history will be removed.');
                $('#confirmEmploymentModal .agreeWith').attr('data-status', employmentStatus);
            }else{
                $('#confirmEmploymentModal .confModTitle').html('Are you sure?');
                $('#confirmEmploymentModal .confModDesc').html('You want to change students employment status?  All existing employment will found under Archive status.');
                $('#confirmEmploymentModal .agreeWith').attr('data-status', employmentStatus);
            }
        });

        $('#confirmEmploymentModal .disAgreeWith').on('click', function(e){
            e.preventDefault();
            confirmEmploymentModal.hide();
            window.location.reload();
        });

        $('#confirmEmploymentModal .agreeWith').on('click', function(e){
            e.preventDefault();
            var $btn = $(this);
            var applicant = $btn.attr('data-applicant');
            var status = $btn.attr('data-status');

            $btn.attr('disabled', 'disabled');
            $btn.siblings('.disAgreeWith').attr('disabled', 'disabled');

            axios({
                method: "post",
                url: route('admission.update.employment.status'),
                data: {applicant : applicant, status : status},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $btn.removeAttr('disabled');
                    $btn.siblings('.disAgreeWith').removeAttr('disabled');

                    confirmEmploymentModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Student employment status successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.show();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                $btn.removeAttr('disabled');
                $btn.siblings('.disAgreeWith').removeAttr('disabled');

                if (error.response) {
                    console.log('error');
                }
            });
        });

        $('#successModal .successCloser').on('click', function(e){
            e.preventDefault();
            if($(this).attr('data-action') == 'RELOAD'){
                successModal.hide();
                window.location.reload();
            }else{
                successModal.hide();
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
                url: route('employment.store'),
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
                url: route("employment.edit", editId),
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
                url: route("employment.update"),
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
                    url: route('employment.destory', recordID),
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
                    url: route('employment.restore', recordID),
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
    }

    /*Address Modal*/
    if($('#addressModal').length > 0){
        const addressModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addressModal"));

        const addressModalEl = document.getElementById('addressModal')
        addressModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addressModal .acc__input-error').html('');
            $('#addressModal input').val('');
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
    }
    /*Address Modal*/

    /* Edit Personal Details */
    if($('#editAdmissionPersonalDetailsForm').length > 0){
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
                if(!$('.disabilityAllowance').is(':visible')){
                    $('.disabilityAllowance').fadeIn('fast', function(){
                        $('input[type="checkbox"]', this).prop('checked', false);
                    });
                }
            }else{
                $('.disabilityAllowance').fadeOut('fast', function(){
                    $('input[type="checkbox"]', this).prop('checked', false);
                });
            }
        });

        const editAdmissionPersonalDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAdmissionPersonalDetailsModal"));
        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        $('#editAdmissionPersonalDetailsForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('editAdmissionPersonalDetailsForm');
        
            document.querySelector('#savePD').setAttribute('disabled', 'disabled');
            document.querySelector("#savePD svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            let applicantId = $('[name="applicant_id"]', $form).val();
            axios({
                method: "post",
                url: route('admission.update.personal.details'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#savePD').removeAttribute('disabled');
                    document.querySelector("#savePD svg").style.cssText = "display: none;";

                    editAdmissionPersonalDetailsModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Personal Data successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#savePD').removeAttribute('disabled');
                document.querySelector("#savePD svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editAdmissionPersonalDetailsForm .${key}`).addClass('border-danger');
                            $(`#editAdmissionPersonalDetailsForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
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
    }
    /* Edit Personal Details*/

    /* Edit Contact Details */
    if($('#editAdmissionContactDetailsForm').length > 0){
        const editAdmissionContactDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAdmissionContactDetailsModal"));
        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        $('#editAdmissionContactDetailsForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('editAdmissionContactDetailsForm');
        
            document.querySelector('#saveCD').setAttribute('disabled', 'disabled');
            document.querySelector("#saveCD svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('admission.update.contact.details'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#saveCD').removeAttribute('disabled');
                    document.querySelector("#saveCD svg").style.cssText = "display: none;";

                    editAdmissionContactDetailsModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Contact Details Data successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.show();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#saveCD').removeAttribute('disabled');
                document.querySelector("#saveCD svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editAdmissionContactDetailsForm .${key}`).addClass('border-danger');
                            $(`#editAdmissionContactDetailsForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
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
    }
    /* Edit Contact Details*/

    /* Edit Kin Details */
    if($('#editAdmissionKinDetailsForm').length > 0) {
        
        const editAdmissionKinDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAdmissionKinDetailsModal"));
        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        $('#editAdmissionKinDetailsForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('editAdmissionKinDetailsForm');
        
            document.querySelector('#saveNOK').setAttribute('disabled', 'disabled');
            document.querySelector("#saveNOK svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('admission.update.kin.details'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#saveNOK').removeAttribute('disabled');
                    document.querySelector("#saveNOK svg").style.cssText = "display: none;";

                    editAdmissionKinDetailsModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Next of Kin Data successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.show();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#saveNOK').removeAttribute('disabled');
                document.querySelector("#saveNOK svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editAdmissionKinDetailsForm .${key}`).addClass('border-danger');
                            $(`#editAdmissionKinDetailsForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
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
    }
    /* Edit Kin Details*/

    /* Edit Course Details*/
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
    });

    if($('#editAdmissionCourseDetailsForm').length > 0){
        var course_creation_id = new TomSelect('#course_creation_id', tomOptions);
        var student_loan = new TomSelect('#student_loan', tomOptions);

        const editAdmissionCourseDetailsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAdmissionCourseDetailsModal"));
        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        $('#editAdmissionCourseDetailsForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('editAdmissionCourseDetailsForm');
        
            document.querySelector('#savePCP').setAttribute('disabled', 'disabled');
            document.querySelector("#savePCP svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('admission.update.course.details'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#savePCP').removeAttribute('disabled');
                    document.querySelector("#savePCP svg").style.cssText = "display: none;";

                    editAdmissionCourseDetailsModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Course & Programme Details successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.show();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#savePCP').removeAttribute('disabled');
                document.querySelector("#savePCP svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editAdmissionCourseDetailsForm .${key}`).addClass('border-danger');
                            $(`#editAdmissionCourseDetailsForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
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
    }
    /* Edit Course Details*/

    /* Edit Education Qualification Details*/
    if($('#applicantQualification').length > 0){
        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmEducationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmEducationModal"));
        const confirmEducationModalEl = document.getElementById('confirmEducationModal')
        confirmEducationModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#confirmEducationModal .confModTitle').html('');
            $('#confirmEducationModal .confModDesc').html('');
            $('#confirmEducationModal .agreeWith').attr('data-action', 'none');
        });
        $('#is_edication_qualification').on('change', function(e){
            var applicantId = $(this).attr('data-applicant');
            
            confirmEducationModal.show();
            if($(this).prop('checked')){
                $('#confirmEducationModal .confModTitle').html('Are you sure?');
                $('#confirmEducationModal .confModDesc').html('You want to enabled students education qualification status?  All existing qualification will found under Archive status.');
                $('#confirmEducationModal .agreeWith').attr('data-action', 1);
            }else{
                $('#confirmEducationModal .confModTitle').html('Are you sure?');
                $('#confirmEducationModal .confModDesc').html('You want to disabled students education qualification status? All existing qualification will be removed.');
                $('#confirmEducationModal .agreeWith').attr('data-action', 0);
            }
        });

        $('#confirmEducationModal .disAgreeWith').on('click', function(e){
            e.preventDefault();
            confirmEducationModal.hide();
            window.location.reload();
        });

        $('#confirmEducationModal .agreeWith').on('click', function(e){
            e.preventDefault();
            var $btn = $(this);
            var applicant = $btn.attr('data-applicant');
            var status = $btn.attr('data-action');

            $btn.attr('disabled', 'disabled');
            $btn.siblings('.disAgreeWith').attr('disabled', 'disabled');

            axios({
                method: "post",
                url: route('admission.update.qualification.status'),
                data: {applicant : applicant, status : status},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $btn.removeAttr('disabled');
                    $btn.siblings('.disAgreeWith').removeAttr('disabled');

                    confirmEducationModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Student education qualification status successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });      
                    
                    setTimeout(function(){
                        successModal.show();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                $btn.removeAttr('disabled');
                $btn.siblings('.disAgreeWith').removeAttr('disabled');

                if (error.response) {
                    console.log('error');
                }
            });
        });
    }
    /* Edit Education Qualification Details*/

    
})();

