import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var courseCreationListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-01").val() != "" ? $("#query-01").val() : "";
        let status = $("#status-01").val() != "" ? $("#status-01").val() : "";
        let course = $("#course-01").val() != "" ? $("#course-01").val() : "";
        let semester = $("#semester-01").val() != "" ? $("#semester-01").val() : "";

        let tableContent = new Tabulator("#courseCreationTableId", {
            ajaxURL: route("course.creation.list"),
            ajaxParams: { querystr: querystr, status: status, course: course, semester: semester},
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
                    width: "180",
                },
                {
                    title: "Course",
                    field: "course",
                    headerHozAlign: "left",
                },
                {
                    title: "Qualification",
                    field: "qualification",
                    headerHozAlign: "left",
                },
                {
                    title: "Semester",
                    field: "semester",
                    headerHozAlign: "left",
                },
                {
                    title: "Duration",
                    field: "duration",
                    headerHozAlign: "left",
                },
                {
                    title: "Unit Length",
                    field: "unit_length",
                    headerHozAlign: "left",
                },
                {
                    title: "SLC Code",
                    field: "slc_code",
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
                        if (cell.getData().deleted_at == null) {
                            btns +='<a href="'+route('course.creation.show', cell.getData().id)+'" class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="EyeOff" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editCourseCreationModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                sheetName: "Course Creations",
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

(function () {
    if($('#courseCreationTableId').length > 0){
        // Init Table
        courseCreationListTable.init();

        // Filter function
        function filterHTMLForm() {
            courseCreationListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-01")[0].addEventListener(
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
        $("#tabulator-html-filter-go-01").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-01").on("click", function (event) {
            $("#query-01").val("");
            $("#course-01").val("");
            $("#semester-01").val("");
            $("#status-01").val("1");
            filterHTMLForm();
        });


        const addCourseCreationModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addCourseCreationModal"));
        const editCourseCreationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editCourseCreationModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

        let confModalDelTitle = 'Are you sure?';
        let confModalDelDescription = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescription = 'Do you really want to re-store these records? Click agree to continue.';

        const addCourseCreationModalEl = document.getElementById('addCourseCreationModal')
        addCourseCreationModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addCourseCreationModal .acc__input-error').html('');
            $('#addCourseCreationModal input').val('');
            $('#addCourseCreationModal select').val('');
        });
        
        const editCourseCreationModalEl = document.getElementById('editCourseCreationModal')
        editCourseCreationModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editCourseCreationModal .acc__input-error').html('');
            $('#editCourseCreationModal input').val('');
            $('#editCourseCreationModal select').val('');
            $('#editCourseCreationModal input[name="id"]').val('0');
        });


        $('#courseCreationTableId').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });

        $('#courseCreationTableId').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record?');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
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
                    url: route('course.creation.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Course creation data successfully deleted.');
                        });
                    }
                    courseCreationListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('course.creation.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Course Creation Data Successfully Restored!');
                        });
                    }
                    courseCreationListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })


        $("#courseCreationTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("course.creation.edit", editId),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editCourseCreationModal input[name="duration"]').val(dataset.duration ? dataset.duration : '');
                    $('#editCourseCreationModal select[name="semester_id"]').val(dataset.semester_id ? dataset.semester_id : '');
                    $('#editCourseCreationModal select[name="course_creation_qualification_id"]').val(dataset.course_creation_qualification_id ? dataset.course_creation_qualification_id : '');
                    $('#editCourseCreationModal select[name="course_id"]').val(dataset.course_id ? dataset.course_id : '');
                    $('#editCourseCreationModal select[name="unit_length"]').val(dataset.unit_length ? dataset.unit_length : '');
                    $('#editCourseCreationModal input[name="slc_code"]').val(dataset.slc_code ? dataset.slc_code : '');
                    

                    $('#editCourseCreationModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $('#editCourseCreationForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('editCourseCreationForm');
        
            document.querySelector('#updateCourseCreation').setAttribute('disabled', 'disabled');
            document.querySelector("#updateCourseCreation svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('course.creation.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateCourseCreation').removeAttribute('disabled');
                document.querySelector("#updateCourseCreation svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    editCourseCreationModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Course creation data successfully updated.');
                    });                
                        
                }
                courseCreationListTable.init();
            }).catch(error => {
                document.querySelector('#updateCourseCreation').removeAttribute('disabled');
                document.querySelector("#updateCourseCreation svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editCourseCreationForm .${key}`).addClass('border-danger')
                            $(`#editCourseCreationForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $('#addCourseCreationForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addCourseCreationForm');
        
            document.querySelector('#saveCourseCreation').setAttribute('disabled', 'disabled');
            document.querySelector("#saveCourseCreation svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('course.creation.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveCourseCreation').removeAttribute('disabled');
                document.querySelector("#saveCourseCreation svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addCourseCreationModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Course creation data successfully inserted.');
                    });                
                        
                }
                courseCreationListTable.init();
            }).catch(error => {
                document.querySelector('#saveCourseCreation').removeAttribute('disabled');
                document.querySelector("#saveCourseCreation svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addCourseCreationForm .${key}`).addClass('border-danger')
                            $(`#addCourseCreationForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });
    }
})()