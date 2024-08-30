import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var courseModuleListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let course = $("#courseModuleTableId").attr('data-courseid') != "" ? $("#courseModuleTableId").attr('data-courseid') : "0";

        let tableContent = new Tabulator("#courseModuleTableId", {
            ajaxURL: route("course.module.list"),
            ajaxParams: { querystr: querystr, status: status, course: course},
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
                    title: "Module",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Level",
                    field: "level",
                    headerHozAlign: "left",
                },
                {
                    title: "Code",
                    field: "code",
                    headerHozAlign: "left",
                },
                {
                    title: "Credit Value",
                    field: "credit_value",
                    headerHozAlign: "left",
                },
                {
                    title: "Unit Value",
                    field: "unit_value",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                },
                {
                    title: "Class Type",
                    field: "class_type",
                    headerHozAlign: "left",
                },
                {
                    title:"Active", 
                    field:"active",
                    headerSort: false,
                    hozAlign: 'left',
                    headerHozAlign: 'left',
                    download: false,
                    formatter(cell, formatterParams){
                        if(cell.getData().active == 1){
                            return '<div class="form-check form-switch"><input data-id="'+cell.getData().id+'" checked value="'+cell.getData().active+'" type="checkbox" class="active_updater form-check-input"> </div>';
                        }else{
                            return '<div class="form-check form-switch"> <input data-id="'+cell.getData().id+'" value="'+cell.getData().active+'" type="checkbox" class="active_updater form-check-input"> </div>';
                        }
                    }
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
                            btns +='<a href="'+route('course.module.show', cell.getData().id)+'" class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#courseModuleEditModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                sheetName: "Course Module Details",
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
    if ($("#courseModuleTableId").length) {
        // Init Table
        courseModuleListTable.init();

        // Filter function
        function filterHTMLForm() {
            courseModuleListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm")[0].addEventListener(
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
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("");
            filterHTMLForm();
        });

        const courseModuleAddModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#courseModuleAddModal"));
        const courseModuleEditModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#courseModuleEditModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalMD"));

        let confModalDelTitle = 'Are you sure?';
        let confModalDelDescription = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescription = 'Do you really want to re-store these records? Click agree to continue.';

        const courseModuleAddModalEl = document.getElementById('courseModuleAddModal')
        courseModuleAddModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#courseModuleAddModal .acc__input-error').html('');
            $('#courseModuleAddModal input[type="text"]').val('');
            $('#courseModuleAddModal select').val('');
        });
        
        const courseModuleEditModalEl = document.getElementById('courseModuleEditModal')
        courseModuleEditModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#courseModuleEditModal .acc__input-error').html('');
            $('#courseModuleEditModal input[type="text"]').val('');
            $('#courseModuleEditModal select').val('');
            $('#courseModuleEditModal input[name="id"]').val('0');
        });

        const confModalEL = document.getElementById('confirmModalMD');
        confModalEL.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModalMD .agreeWithMD').attr('data-id', '0');
            $('#confirmModalMD .agreeWithMD').attr('data-action', 'none');
        });


        // Delete Course
        $('#courseModuleTableId').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModalMD').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalMD .confModTitleMD').html(confModalDelTitle);
                $('#confirmModalMD .confModDescMD').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModalMD .agreeWithMD').attr('data-id', rowID);
                $('#confirmModalMD .agreeWithMD').attr('data-action', 'DELETE');
            });
        });

        $('#courseModuleTableId').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModalMD').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalMD .confModTitleMD').html(confModalDelTitle);
                $('#confirmModalMD .confModDescMD').html('Do you really want to restore these record?');
                $('#confirmModalMD .agreeWithMD').attr('data-id', courseID);
                $('#confirmModalMD .agreeWithMD').attr('data-action', 'RESTORE');
            });
        });

        // Confirm Modal Action
        $('#confirmModalMD .agreeWithMD').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModalMD button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('course.module.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalMD button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Data Deleted!');
                        });
                    }
                    courseModuleListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('course.module.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalMD button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Course Module Data Successfully Restored!');
                        });
                    }
                    courseModuleListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $("#courseModuleTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("course.module.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#courseModuleEditModal input[name="name"]').val(dataset.name ? dataset.name : '');
                    $('#courseModuleEditModal select[name="module_level_id"]').val(dataset.module_level_id ? dataset.module_level_id : '');
                    $('#courseModuleEditModal input[name="code"]').val(dataset.code ? dataset.code : '');
                    $('#courseModuleEditModal input[name="credit_value"]').val(dataset.credit_value ? dataset.credit_value : '');
                    $('#courseModuleEditModal input[name="unit_value"]').val(dataset.unit_value ? dataset.unit_value : '');
                    $('#courseModuleEditModal select[name="status"]').val(dataset.status ? dataset.status : '');
                    $('#courseModuleEditModal select[name="class_type"]').val(dataset.class_type ? dataset.class_type : '');
                    if(dataset.active == 1){
                        document.querySelector('#courseModuleEditModal input[name="active"]').checked=true;
                    }else{
                        document.querySelector('#courseModuleEditModal input[name="active"]').checked=false;
                    }

                    $('#courseModuleEditModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });


        $('#courseModuleTableId').on('change', '.active_updater', function(){
            let $statusBTN = $(this);
            let moduleID = $statusBTN.attr('data-id');
            let status = (this.checked ? 1 : 0);
    
            $statusBTN.attr('disabled', 'disabled');
    
            axios({
                method: "post",
                url: route('course.module.status.update'),
                data: {id : moduleID, status : status},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $statusBTN.removeAttr('disabled');
    
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Course Module Item Status Successfully Updated!');
                    });
                }
                courseModuleListTable.init();
            }).catch(error =>{
                console.log(error)
            });
    
            courseModuleListTable.init();
        });

        $('#courseModuleAddForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('courseModuleAddForm');

            $('#courseModuleAddForm').find('input').removeClass('border-danger')
            $('#courseModuleAddForm').find('.acc__input-error').html('')

            document.querySelector('#saveModule').setAttribute('disabled', 'disabled');
            document.querySelector('#saveModule svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('course.module.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveModule').removeAttribute('disabled');
                document.querySelector('#saveModule svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    courseModuleAddModal.hide();
                    courseModuleListTable.init();
                    
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Course Module data successfully inserted.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#saveModule').removeAttribute('disabled');
                document.querySelector('#saveModule svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#courseModuleAddForm .${key}`).addClass('border-danger')
                            $(`#courseModuleAddForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });
        
        $('#courseModuleEditForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('courseModuleEditForm');

            $('#courseModuleEditForm').find('input').removeClass('border-danger')
            $('#courseModuleEditForm').find('.acc__input-error').html('')

            document.querySelector('#updateModule').setAttribute('disabled', 'disabled');
            document.querySelector('#updateModule svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('course.module.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateModule').removeAttribute('disabled');
                document.querySelector('#updateModule svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    courseModuleEditModal.hide();
                    courseModuleListTable.init();
                    
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Course Module data successfully updated.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#updateModule').removeAttribute('disabled');
                document.querySelector('#updateModule svg').style.cssText = 'display: none;';
                if(error.response){
                    if(error.response.status == 422){
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#courseModuleEditForm .${key}`).addClass('border-danger')
                            $(`#courseModuleEditForm  .error-${key}`).html(val)
                        }
                    }else{
                        console.log('error');
                    }
                }
            });

        });
    }
})()