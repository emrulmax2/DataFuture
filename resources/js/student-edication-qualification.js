import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import { min } from "lodash";

("use strict");
var studentEducationQualTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let student_id = $("#studentEducationQualTable").attr('data-student') != "" ? $("#studentEducationQualTable").attr('data-student') : "0";
        let querystr = $("#query-SEQ").val() != "" ? $("#query-SEQ").val() : "";
        let status = $("#status-SEQ").val() != "" ? $("#status-SEQ").val() : "";

        let tableContent = new Tabulator("#studentEducationQualTable", {
            ajaxURL: route("student.qualification.list"),
            ajaxParams: { student_id: student_id, querystr: querystr, status: status},
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
                    title: "#SL",
                    field: "sl",
                    minWidth: 80,
                },
                {
                    title: "Awarding Body",
                    field: "awarding_body",
                    headerHozAlign: "left",
                    minWidth: 150,
                    formatter(cell, formatterParams) {
                        return `<div class="whitespace-normal">${cell.getData().awarding_body}</div>`;
                    }
                },
                {
                    title: "Highest Academic Qualification",
                    field: "highest_academic",
                    headerHozAlign: "left",
                    minWidth: 200,
                    formatter(cell, formatterParams) {
                        return `<div class="whitespace-normal">${cell.getData().highest_academic}</div>`;
                    }
                },
                {
                    title: "Subjects",
                    field: "subjects",
                    headerHozAlign: "left",
                    minWidth: 100,
                },
                /*{
                    title: "Result",
                    field: "result",
                    headerHozAlign: "left",
                },*/
                {
                    title: "Result",
                    field: "qualification_grade_id",
                    headerHozAlign: "left",
                    minWidth: 100,
                },
                {
                    title: "Award Date",
                    field: "degree_award_date",
                    headerHozAlign: "left",
                    minWidth: 100,
                },
                {
                    title: "HIghest Qualification Entry",
                    field: "highest_qualification_on_entry_id",
                    headerHozAlign: "left",
                    minWidth: 200,
                    formatter(cell, formatterParams) {
                        return `<div class="whitespace-normal">${cell.getData().highest_qualification_on_entry_id}</div>`;
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    download: false,
                    minWidth: 120,
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
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                }   
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
        $("#tabulator-export-csv-SEQ").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-SEQ").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-SEQ").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Student Education Qualification Details",
            });
        });

        $("#tabulator-export-html-SEQ").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-SEQ").on("click", function (event) {
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
    if($('#studentEducationQualTable').length > 0){
        if($('#studentEducationQualTable').hasClass('activeTable')){
            studentEducationQualTable.init();
        }
        // Filter function
        function filterHTMLFormSEQ() {
            studentEducationQualTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-SEQ").on("click", function (event) {
            filterHTMLFormSEQ();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-SEQ").on("click", function (event) {
            $("#query-SEQ").val("");
            $("#status-SEQ").val("1");
            filterHTMLFormSEQ();
        });

    }

    const editStudentQualStatusModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editStudentQualStatusModal"));
    const addQualificationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addQualificationModal"));
    const editQualificationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editQualificationModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    let confModalDelTitle = 'Are you sure?';

    const addQualificationModalEl = document.getElementById('addQualificationModal')
    addQualificationModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addQualificationModal .acc__input-error').html('');
        $('#addQualificationModal .modal-body input').val('');
        $('#addQualificationModal .modal-body select').val('');
        previous_provider_id1.clear();
        qualification_type_identifier_id1.clear();
        hesa_qualification_subject_id1.clear();
        highest_qualification_on_entry_id1.clear();
        hesa_exam_sitting_venue_id1.clear()
        qualification_grade_id.clear()
    });

    const editQualificationModalEl = document.getElementById('editQualificationModal')
    editQualificationModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editQualificationModal .acc__input-error').html('');
        $('#editQualificationModal .modal-body input').val('');
        $('#editQualificationModal .modal-body select').val('');
        $('#editQualificationModal .modal-footer input[name="id"]').val('0');
        previous_provider_id.clear(true);
        qualification_type_identifier_id.clear(true);
        hesa_qualification_subject_id.clear(true);
        highest_qualification_on_entry_id.clear(true);
        hesa_exam_sitting_venue_id.clear(true);
        edit_qualification_grade_id.clear(true);
    });

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

    var previous_provider_id = new TomSelect('#previous_provider_id', tomOptions);
    var qualification_type_identifier_id = new TomSelect('#qualification_type_identifier_id', tomOptions);
    var hesa_qualification_subject_id = new TomSelect('#hesa_qualification_subject_id', tomOptions);
    var highest_qualification_on_entry_id = new TomSelect('#highest_qualification_on_entry_id', tomOptions);
    var hesa_exam_sitting_venue_id = new TomSelect('#hesa_exam_sitting_venue_id', tomOptions);

    var previous_provider_id1 = new TomSelect('#previous_provider_id1', tomOptions);
    var qualification_type_identifier_id1 = new TomSelect('#qualification_type_identifier_id1', tomOptions);
    var hesa_qualification_subject_id1 = new TomSelect('#hesa_qualification_subject_id1', tomOptions);
    var highest_qualification_on_entry_id1 = new TomSelect('#highest_qualification_on_entry_id1', tomOptions);
    var hesa_exam_sitting_venue_id1 = new TomSelect('#hesa_exam_sitting_venue_id1', tomOptions);

    var qualification_grade_id = new TomSelect('#qualification_grade_id', tomOptions);
    var edit_qualification_grade_id = new TomSelect('#edit_qualification_grade_id', tomOptions);

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    });
    
    /* Update Education Qualification Status */
    $('#editStudentQualStatusForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editStudentQualStatusForm');
    
        document.querySelector('#updateSQS').setAttribute('disabled', 'disabled');
        document.querySelector("#updateSQS svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.qualification.status.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                document.querySelector('#updateSQS').removeAttribute('disabled');
                document.querySelector("#updateSQS svg").style.cssText = "display: none;";
                
                editStudentQualStatusModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student education qualification status successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#updateSQS').removeAttribute('disabled');
            document.querySelector("#updateSQS svg").style.cssText = "display: none;";
            if (error.response) {
                console.log('error');
            }
        });
    });
    /* Update Education Qualification Status */

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
            url: route('student.qualification.store'),
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
                    $("#successModal .successModalDesc").html('Student Eucation Qualification date successfully inserted.');
                });                
                    
            }
            studentEducationQualTable.init();
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

    $("#studentEducationQualTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("student.qualification.edit", editId),
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
                    //$('#editQualificationModal input[name="result"]').val(dataset.result ? dataset.result : '');
                    $('#editQualificationModal input[name="degree_award_date"]').val(dataset.degree_award_date ? dataset.degree_award_date : '');
                    previous_provider_id.clear(true);
                    qualification_type_identifier_id.clear(true);
                    hesa_qualification_subject_id.clear(true);
                    highest_qualification_on_entry_id.clear(true);
                    edit_qualification_grade_id.clear(true);

                    previous_provider_id.addItem(dataset.previous_provider_id);
                    qualification_type_identifier_id.addItem(dataset.qualification_type_identifier_id);
                    hesa_qualification_subject_id.addItem(dataset.hesa_qualification_subject_id);
                    highest_qualification_on_entry_id.addItem(dataset.highest_qualification_on_entry_id);
                    hesa_exam_sitting_venue_id.addItem(dataset.hesa_exam_sitting_venue_id);
                    edit_qualification_grade_id.addItem(dataset.qualification_grade_id);
                    

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
            url: route("student.qualification.update"),
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
            studentEducationQualTable.init();
        }).catch((error) => {
            document.querySelector("#updateEducationQualification").removeAttribute("disabled");
            document.querySelector("#updateEducationQualification svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editQualificationForm .${key}`).addClass('border-danger')
                        $(`#editQualificationForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log("error");
                }
            }
        });
    });

    // Delete Course
    $('#studentEducationQualTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETESEQ');
        });
    });

    // Restore Course
    $('#studentEducationQualTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let courseID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', courseID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTORESEQ');
        });
    });

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETESEQ'){
            axios({
                method: 'delete',
                url: route('student.qualification.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Student Education Qualification successfull deleted.');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000)
                }
                studentEducationQualTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORESEQ'){
            axios({
                method: 'post',
                url: route('student.qualification.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Student Education Qualification successfull restored.');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000)
                }
                studentEducationQualTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })
})();