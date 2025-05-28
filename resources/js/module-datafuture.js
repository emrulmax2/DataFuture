import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var moduleDFListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-02").val() != "" ? $("#query-02").val() : "";
        let status = $("#status-02").val() != "" ? $("#status-02").val() : "";
        let module = $("#moduleDatafutureDataTable").attr('data-moduleid') != "" ? $("#moduleDatafutureDataTable").attr('data-moduleid') : "0";

        let tableContent = new Tabulator("#moduleDatafutureDataTable", {
            ajaxURL: route("module.datafuture.list"),
            ajaxParams: { querystr: querystr, status: status, module: module},
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
                    title: "Category",
                    field: "category",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Field Name",
                    field: "datafuture_field_id",
                    headerHozAlign: "left",
                },
                {
                    title: "Field Type",
                    field: "field_type",
                    headerSort: false,
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
                    headerSort: false,
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
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#moduleDataFutureEditModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                sheetName: "Module Datafuture",
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
    if ($("#moduleDatafutureDataTable").length) {
        // Init Table
        moduleDFListTable.init();

        // Filter function
        function filterHTMLForm() {
            moduleDFListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-02")[0].addEventListener(
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

        let dfLitepicker = {
            autoApply: true,
            singleMode: true,
            numberOfColumns: 1,
            numberOfMonths: 1,
            showWeekNumbers: false,
            format: "YYYY-MM-DD",
            dropdowns: {
                minYear: 1900,
                maxYear: 2050,
                months: true,
                years: true,
            },
        };
        let addPicker = null;
        let editPicker = null;

        let tomOptionsCBDF = {
            plugins: {
                dropdown_input: {}
            },
            placeholder: 'Search Here...',
            //persist: false,
            create: false,
            allowEmptyOption: true,
            onDelete: function (values) {
                return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
            },
        };
        var datafuture_field_id = new TomSelect('#datafuture_field_id', tomOptionsCBDF);
        var edit_datafuture_field_id = new TomSelect('#edit_datafuture_field_id', tomOptionsCBDF);


        const moduleDataFutureAddModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#moduleDataFutureAddModal"));
        const moduleDataFutureEditModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#moduleDataFutureEditModal"));
        const succModalMDF = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModalMDF = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalMDF"));

        let confModalDelTitleDF = 'Are you sure?';
        let confModalDelDescriptionDF = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescriptionDF = 'Do you really want to re-store these records? Click agree to continue.';

        const moduleDataFutureAddModalEl = document.getElementById('moduleDataFutureAddModal')
        moduleDataFutureAddModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#moduleDataFutureAddModal .acc__input-error').html('');
            $('#moduleDataFutureAddModal input[type="text"]').val('');
            $('#moduleDataFutureAddModal select').val('');

            datafuture_field_id.clear(true);

            if(addPicker != null){
                editPicker.destroy();
            }
        });
        
        const moduleDataFutureEditModalEl = document.getElementById('moduleDataFutureEditModal')
        moduleDataFutureEditModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#moduleDataFutureEditModal .acc__input-error').html('');
            $('#moduleDataFutureEditModal input[type="text"]').val('');
            $('#moduleDataFutureEditModal select').val('');
            $('#moduleDataFutureEditModal input[name="id"]').val('0');

            edit_datafuture_field_id.clear(true);

            if(editPicker != null){
                editPicker.destroy();
            }
        });

        const confirmModalMDFEL = document.getElementById('confirmModalMDF');
        confirmModalMDFEL.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModalMDF .agreeWithMDF').attr('data-id', '0');
            $('#confirmModalMDF .agreeWithMDF').attr('data-action', 'none');
        });

        // Delete Course
        $('#moduleDatafutureDataTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModalMDF.show();
            document.getElementById('confirmModalMDF').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalMDF .confModTitleMDF').html(confModalDelTitleDF);
                $('#confirmModalMDF .confModDescMDF').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModalMDF .agreeWithMDF').attr('data-id', rowID);
                $('#confirmModalMDF .agreeWithMDF').attr('data-action', 'DELETE');
            });
        });

        $('#moduleDatafutureDataTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModalMDF.show();
            document.getElementById('confirmModalMDF').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalMDF .confModTitleMDF').html(confModalDelTitleDF);
                $('#confirmModalMDF .confModDescMDF').html('Do you really want to restore these record?');
                $('#confirmModalMDF .agreeWithMDF').attr('data-id', courseID);
                $('#confirmModalMDF .agreeWithMDF').attr('data-action', 'RESTORE');
            });
        });

        // Confirm Modal Action
        $('#confirmModalMDF .agreeWithMDF').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModalMDF button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('module.datafuture.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalMDF button').removeAttr('disabled');
                        confirmModalMDF.hide();

                        succModalMDF.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Congratulation!');
                            $('#successModal .successModalDesc').html('Module datafuture data successfully deleted.');
                        });
                    }
                    moduleDFListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('module.datafuture.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalMDF button').removeAttr('disabled');
                        confirmModalMDF.hide();

                        succModalMDF.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Module Datafuture Data Successfully Restored!');
                        });
                    }
                    moduleDFListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $("#moduleDatafutureDataTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("module.datafuture.edit", editId),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;

                    let datafuture_field_id = dataset.datafuture_field_id ? dataset.datafuture_field_id : '';
                    //$('#courseDataFutureEditModal input[name="field_value"]').val(dataset.field_value ? dataset.field_value : '');
                    let theType = dataset.field.type ? dataset.field.type : 'text';

                    if(datafuture_field_id != ''){
                        edit_datafuture_field_id.setValue(datafuture_field_id);
                    }else{
                        edit_datafuture_field_id.clear(true);
                    }

                    if(theType == 'number'){
                        $('#edit_field_value').attr('type', 'number').attr('step', 'any').val(dataset.field_value ? dataset.field_value : '');
                    }else{
                        $('#edit_field_value').attr('type', 'text').removeAttr('step').val(dataset.field_value ? dataset.field_value : '');
                        if(theType == 'date'){
                            editPicker = new Litepicker({
                                element: document.getElementById('edit_field_value'),
                                ...dfLitepicker,
                            });
                        }else{
                            if(editPicker != null){
                                editPicker.destroy();
                            }
                        }
                    }
                    $('#moduleDataFutureEditModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $('#edit_datafuture_field_id').on('change', function(){
            var $theField = $(this);
            var theFieldId = $theField.val();

            if(theFieldId > 0){
                var theType = $('option:selected', $theField).attr('data-type');
                if(theType == 'number'){
                    $('#edit_field_value').attr('type', 'number').attr('step', 'any').val('');
                }else{
                    $('#edit_field_value').attr('type', 'text').removeAttr('step').val('');
                    if(theType == 'date'){
                        editPicker = new Litepicker({
                            element: document.getElementById('edit_field_value'),
                            ...dfLitepicker,
                        });
                    }else{
                        if(editPicker != null){
                            editPicker.destroy();
                        }
                    }
                }
            }else{
                $('#edit_field_value').attr('type', 'text').removeAttr('step').val('');
                if(editPicker != null){
                    editPicker.destroy();
                }
            }
        })

        $('#moduleDataFutureEditForm').on('submit', function(e){
            e.preventDefault();
            const formDF = document.getElementById('moduleDataFutureEditForm');

            $('#moduleDataFutureEditForm').find('input').removeClass('border-danger')
            $('#moduleDataFutureEditForm').find('.acc__input-error').html('')

            document.querySelector('#updateModuleDF').setAttribute('disabled', 'disabled');
            document.querySelector('#updateModuleDF svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(formDF);

            axios({
                method: "post",
                url: route('module.datafuture.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateModuleDF').removeAttribute('disabled');
                document.querySelector('#updateModuleDF svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    moduleDataFutureEditModal.hide();
                    moduleDFListTable.init();
                    
                    succModalMDF.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Module Datafuture Field Data Successfully Updated.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#updateModuleDF').removeAttribute('disabled');
                document.querySelector('#updateModuleDF svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#moduleDataFutureEditForm .${key}`).addClass('border-danger')
                            $(`#moduleDataFutureEditForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });

        $('#datafuture_field_id').on('change', function(){
            var $theField = $(this);
            var theFieldId = $theField.val();

            if(theFieldId > 0){
                var theType = $('option:selected', $theField).attr('data-type');
                console.log(theType);
                
                if(theType == 'number'){
                    $('#field_value').attr('type', 'number').attr('step', 'any').val('');
                }else{
                    $('#field_value').attr('type', 'text').removeAttr('step').val('');
                    if(theType == 'date'){
                        addPicker = new Litepicker({
                            element: document.getElementById('field_value'),
                            ...dfLitepicker,
                        });
                    }else{
                        if(addPicker != null){
                            addPicker.destroy();
                        }
                    }
                }
            }else{
                $('#field_value').attr('type', 'text').removeAttr('step').val('');
                if(addPicker != null){
                    addPicker.destroy();
                }
            }
        })

        $('#moduleDataFutureAddForm').on('submit', function(e){
            e.preventDefault();
            const formDF = document.getElementById('moduleDataFutureAddForm');

            $('#moduleDataFutureAddForm').find('input').removeClass('border-danger')
            $('#moduleDataFutureAddForm').find('.acc__input-error').html('')

            document.querySelector('#saveModuleDF').setAttribute('disabled', 'disabled');
            document.querySelector('#saveModuleDF svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(formDF);

            axios({
                method: "post",
                url: route('module.datafuture.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveModuleDF').removeAttribute('disabled');
                document.querySelector('#saveModuleDF svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    moduleDataFutureAddModal.hide();
                    moduleDFListTable.init();
                    
                    succModalMDF.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Module Databuture Field Data Successfully Inserted.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#saveModuleDF').removeAttribute('disabled');
                document.querySelector('#saveModuleDF svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#moduleDataFutureAddForm .${key}`).addClass('border-danger')
                            $(`#moduleDataFutureAddForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });

    }
})()