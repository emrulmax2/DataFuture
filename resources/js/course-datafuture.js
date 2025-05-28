import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import Litepicker from "litepicker";

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
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#courseDataFutureEditModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
            $('#courseDataFutureAddModal select').val('');

            datafuture_field_id.clear(true);

            if(addPicker != null){
                editPicker.destroy();
            }
        });
        
        const courseDataFutureEditModalEl = document.getElementById('courseDataFutureEditModal')
        courseDataFutureEditModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#courseDataFutureEditModal .acc__input-error').html('');
            $('#courseDataFutureEditModal input[type="text"]').val('');
            $('#courseDataFutureEditModal select').val('');
            $('#courseDataFutureEditModal input[name="id"]').val('0');

            edit_datafuture_field_id.clear(true);

            if(editPicker != null){
                editPicker.destroy();
            }
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
                    

                    $('#courseDataFutureEditModal input[name="id"]').val(editId);
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