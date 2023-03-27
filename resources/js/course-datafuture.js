import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var courseDFListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-02").val() != "" ? $("#query-02").val() : "";
        let status = $("#status-02").val() != "" ? $("#status-02").val() : "";
        let course = $("#courseDataFutureTableId").attr('data-courseid') != "" ? $("#courseDataFutureTableId").attr('data-courseid') : "0";

        let tableContent = new Tabulator("#courseDataFutureTableId", {
            ajaxURL: route("course.datafuture.list"),
            ajaxParams: { querystr: querystr, status: status, course: course},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [5, 10, 20, 30, 40],
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
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#courseDataFutureEditModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="edit-3" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="trash" class="w-4 h-4"></i></button>';
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
                sheetName: "Source of Tuition Fees",
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
    if ($("#courseDataFutureTableId").length) {
        // Init Table
        courseDFListTable.init();

        // Filter function
        function filterHTMLForm() {
            courseDFListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm02")[0].addEventListener(
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
        $("#tabulator-html-filter-go-02").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-02").on("click", function (event) {
            $("#query-02").val("");
            $("#status-02").val("1");
            filterHTMLForm();
        });


        const courseDataFutureAddModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#courseDataFutureAddModal"));
        const courseDataFutureEditModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#courseDataFutureEditModal"));
        const succModalDF = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confModalDF = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalDF"));

        let confModalDelTitleDF = 'Are you sure?';
        let confModalDelDescriptionDF = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescriptionDF = 'Do you really want to re-store these records? Click agree to continue.';

        const courseDataFutureAddModalEl = document.getElementById('courseDataFutureAddModal')
        courseDataFutureAddModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#courseDataFutureAddModal .acc__input-error').html('');
            $('#courseDataFutureAddModal input[type="text"]').val('');
            $('#courseDataFutureAddModal textarea').val('');
            $('#courseDataFutureAddModal select').val('');
        });
        
        const courseDataFutureEditModalEl = document.getElementById('courseDataFutureEditModal')
        courseDataFutureEditModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#courseDataFutureEditModal .acc__input-error').html('');
            $('#courseDataFutureEditModal input[type="text"]').val('');
            $('#courseDataFutureEditModal select').val('');
            $('#courseDataFutureEditModal textarea').val('');
            $('#courseDataFutureEditModal input[name="id"]').val('0');
        });

        const confModalDFEL = document.getElementById('confirmModalDF');
        confModalDFEL.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModalDF .agreeWithDF').attr('data-id', '0');
            $('#confirmModalDF .agreeWithDF').attr('data-action', 'none');
        });

        // Delete Course
        $('#courseDataFutureTableId').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModalDF.show();
            document.getElementById('confirmModalDF').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalDF .confModTitleDF').html(confModalDelTitleDF);
                $('#confirmModalDF .confModDescDF').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModalDF .agreeWithDF').attr('data-id', rowID);
                $('#confirmModalDF .agreeWithDF').attr('data-action', 'DELETE');
            });
        });

        $('#courseDataFutureTableId').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confModalDF.show();
            document.getElementById('confirmModalDF').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalDF .confModTitleDF').html(confModalDelTitleDF);
                $('#confirmModalDF .confModDescDF').html('Do you really want to restore these record?');
                $('#confirmModalDF .agreeWithDF').attr('data-id', courseID);
                $('#confirmModalDF .agreeWithDF').attr('data-action', 'RESTORE');
            });
        });

        // Confirm Modal Action
        $('#confirmModalDF .agreeWithDF').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModalDF button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('course.datafuture.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalDF button').removeAttr('disabled');
                        confModalDF.hide();

                        succModalDF.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Course base datafuture data successfully deleted.');
                        });
                    }
                    courseDFListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('course.datafuture.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalDF button').removeAttr('disabled');
                        confModalDF.hide();

                        succModalDF.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Course Base Datafuture Data Successfully Restored!');
                        });
                    }
                    courseDFListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $("#courseDataFutureTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("course.datafuture.edit", editId),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#courseDataFutureEditModal input[name="field_name"]').val(dataset.field_name ? dataset.field_name : '');
                    $('#courseDataFutureEditModal select[name="field_type"]').val(dataset.field_type ? dataset.field_type : '');
                    $('#courseDataFutureEditModal input[name="field_value"]').val(dataset.field_value ? dataset.field_value : '');
                    $('#courseDataFutureEditModal textarea[name="field_desc"]').val(dataset.field_desc ? dataset.field_desc : '');
                    

                    $('#courseDataFutureEditModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $('#courseDataFutureEditForm').on('submit', function(e){
            e.preventDefault();
            const formDF = document.getElementById('courseDataFutureEditForm');

            $('#courseDataFutureEditForm').find('input').removeClass('border-danger')
            $('#courseDataFutureEditForm').find('.acc__input-error').html('')

            document.querySelector('#updateBaseDF').setAttribute('disabled', 'disabled');
            document.querySelector('#updateBaseDF svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(formDF);

            axios({
                method: "post",
                url: route('course.datafuture.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateBaseDF').removeAttribute('disabled');
                document.querySelector('#updateBaseDF svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    courseDataFutureEditModal.hide();
                    courseDFListTable.init();
                    
                    succModalDF.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Course Base Datafuture Field Data Successfully Updated.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#updateBaseDF').removeAttribute('disabled');
                document.querySelector('#updateBaseDF svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#courseDataFutureEditForm .${key}`).addClass('border-danger')
                            $(`#courseDataFutureEditForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });


        $('#courseDataFutureAddForm').on('submit', function(e){
            e.preventDefault();
            const formDF = document.getElementById('courseDataFutureAddForm');

            $('#courseDataFutureAddForm').find('input').removeClass('border-danger')
            $('#courseDataFutureAddForm').find('.acc__input-error').html('')

            document.querySelector('#saveBaseDF').setAttribute('disabled', 'disabled');
            document.querySelector('#saveBaseDF svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(formDF);

            axios({
                method: "post",
                url: route('course.datafuture.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveBaseDF').removeAttribute('disabled');
                document.querySelector('#saveBaseDF svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    courseDataFutureAddModal.hide();
                    courseDFListTable.init();
                    
                    succModalDF.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Course Base Databuture Field Data Successfully Inserted.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#saveBaseDF').removeAttribute('disabled');
                document.querySelector('#saveBaseDF svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#courseDataFutureAddForm .${key}`).addClass('border-danger')
                            $(`#courseDataFutureAddForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });

    }
})()