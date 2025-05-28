import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var courseCreationDFListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-03").val() != "" ? $("#query-03").val() : "";
        let status = $("#status-03").val() != "" ? $("#status-03").val() : "";
        let creationid = $("#courseCreationDFTable").attr('data-creationid') != "" ? $("#courseCreationDFTable").attr('data-creationid') : "0";

        let tableContent = new Tabulator("#courseCreationDFTable", {
            ajaxURL: route("course.creation.datafuture.list"),
            ajaxParams: { querystr: querystr, status: status, creationid: creationid},
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
                    title: "Field Name",
                    field: "field_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Field Type",
                    field: "field_type",
                    headerHozAlign: "left",
                },
                {
                    title: "Field Value",
                    field: "field_value",
                    headerHozAlign: "left",
                },
                {
                    title: "Description",
                    field: "field_desc",
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
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editCourseCreationDataFutureModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
            const actionColumn = this.getColumn("id");
                if (actionColumn) {
                    const currentWidth = actionColumn.getWidth();
                    actionColumn.setWidth(currentWidth - 1);
                }
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
                sheetName: "Course Creation Datafuture",
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
    if ($("#courseCreationDFTable").length) {
        // Init Table
        courseCreationDFListTable.init();

        // Filter function
        function filterHTMLForm() {
            courseCreationDFListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-03")[0].addEventListener(
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
        $("#tabulator-html-filter-go-03").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-03").on("click", function (event) {
            $("#query-03").val("");
            $("#status-03").val("1");
            filterHTMLForm();
        });


        const addCourseCreationDataFutureModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addCourseCreationDataFutureModal"));
        const editCourseCreationDataFutureModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editCourseCreationDataFutureModal"));
        const succModalCCDF = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModalCCDF = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalCCDF"));

        let confModalDelTitleCCDF = 'Are you sure?';
        let confModalDelDescriptionCCDF = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescriptionCCDF = 'Do you really want to re-store these records? Click agree to continue.';

        const addCourseCreationDataFutureModalEl = document.getElementById('addCourseCreationDataFutureModal')
        addCourseCreationDataFutureModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addCourseCreationDataFutureModal .acc__input-error').html('');
            $('#addCourseCreationDataFutureModal .modal-body input[type="text"]').val('');
            $('#addCourseCreationDataFutureModal .modal-body  textarea').val('');
            $('#addCourseCreationDataFutureModal .modal-body  select').val('');
        });
        
        const editCourseCreationDataFutureModalEl = document.getElementById('editCourseCreationDataFutureModal')
        editCourseCreationDataFutureModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editCourseCreationDataFutureModal .acc__input-error').html('');
            $('#editCourseCreationDataFutureModal .modal-body  input[type="text"]').val('');
            $('#editCourseCreationDataFutureModal .modal-body  select').val('');
            $('#editCourseCreationDataFutureModal .modal-body  textarea').val('');
            $('#editCourseCreationDataFutureModal input[name="id"]').val('0');
        });

        const confirmModalCCDFEL = document.getElementById('confirmModalCCDF');
        confirmModalCCDFEL.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModalCCDF .agreeWithCCDF').attr('data-id', '0');
            $('#confirmModalCCDF .agreeWithCCDF').attr('data-action', 'none');
        });

        // Delete Course
        $('#courseCreationDFTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModalCCDF.show();
            document.getElementById('confirmModalCCDF').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalCCDF .confModTitleMDF').html(confModalDelTitleCCDF);
                $('#confirmModalCCDF .confModDescMDF').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModalCCDF .agreeWithCCDF').attr('data-id', rowID);
                $('#confirmModalCCDF .agreeWithCCDF').attr('data-action', 'DELETE');
            });
        });

        $('#courseCreationDFTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModalCCDF.show();
            document.getElementById('confirmModalCCDF').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalCCDF .confModTitleMDF').html(confModalDelTitleCCDF);
                $('#confirmModalCCDF .confModDescMDF').html('Do you really want to restore these record?');
                $('#confirmModalCCDF .agreeWithCCDF').attr('data-id', courseID);
                $('#confirmModalCCDF .agreeWithCCDF').attr('data-action', 'RESTORE');
            });
        });

        // Confirm Modal Action
        $('#confirmModalCCDF .agreeWithCCDF').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModalCCDF button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('course.creation.datafuture.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalCCDF button').removeAttr('disabled');
                        confirmModalCCDF.hide();

                        succModalCCDF.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Congratulation!');
                            $('#successModal .successModalDesc').html('Module datafuture data successfully deleted.');
                        });
                    }
                    courseCreationDFListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('course.creation.datafuture.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalCCDF button').removeAttr('disabled');
                        confirmModalCCDF.hide();

                        succModalCCDF.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Module Datafuture Data Successfully Restored!');
                        });
                    }
                    courseCreationDFListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $("#courseCreationDFTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("course.creation.datafuture.edit", editId),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editCourseCreationDataFutureModal input[name="field_name"]').val(dataset.field_name ? dataset.field_name : '');
                    $('#editCourseCreationDataFutureModal select[name="field_type"]').val(dataset.field_type ? dataset.field_type : '');
                    $('#editCourseCreationDataFutureModal input[name="field_value"]').val(dataset.field_value ? dataset.field_value : '');
                    $('#editCourseCreationDataFutureModal textarea[name="field_desc"]').val(dataset.field_desc ? dataset.field_desc : '');
                    

                    $('#editCourseCreationDataFutureModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $('#editCourseCreationDataFutureForm').on('submit', function(e){
            e.preventDefault();
            const formDF = document.getElementById('editCourseCreationDataFutureForm');

            $('#editCourseCreationDataFutureForm').find('input').removeClass('border-danger')
            $('#editCourseCreationDataFutureForm').find('.acc__input-error').html('')

            document.querySelector('#updateCCDF').setAttribute('disabled', 'disabled');
            document.querySelector('#updateCCDF svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(formDF);

            axios({
                method: "post",
                url: route('course.creation.datafuture.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateCCDF').removeAttribute('disabled');
                document.querySelector('#updateCCDF svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    editCourseCreationDataFutureModal.hide();
                    courseCreationDFListTable.init();
                    
                    succModalCCDF.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Course Creation Datafuture Field Data Successfully Updated.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#updateCCDF').removeAttribute('disabled');
                document.querySelector('#updateCCDF svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editCourseCreationDataFutureForm .${key}`).addClass('border-danger')
                            $(`#editCourseCreationDataFutureForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });


        $('#addCourseCreationDataFutureForm').on('submit', function(e){
            e.preventDefault();
            const formDF = document.getElementById('addCourseCreationDataFutureForm');

            $('#addCourseCreationDataFutureForm').find('input').removeClass('border-danger')
            $('#addCourseCreationDataFutureForm').find('.acc__input-error').html('')

            document.querySelector('#saveCCDF').setAttribute('disabled', 'disabled');
            document.querySelector('#saveCCDF svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(formDF);

            axios({
                method: "post",
                url: route('course.creation.datafuture.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveCCDF').removeAttribute('disabled');
                document.querySelector('#saveCCDF svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    addCourseCreationDataFutureModal.hide();
                    courseCreationDFListTable.init();
                    
                    succModalCCDF.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Course Creation Databuture Field Data Successfully Inserted.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#saveCCDF').removeAttribute('disabled');
                document.querySelector('#saveCCDF svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addCourseCreationDataFutureForm .${key}`).addClass('border-danger')
                            $(`#addCourseCreationDataFutureForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });

    }
})()