import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var venueDFListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-VCBDF").val() != "" ? $("#query-VCBDF").val() : "";
        let status = $("#status-VCBDF").val() != "" ? $("#status-VCBDF").val() : "";
        let venue = $("#venueDataFutureTableId").attr('data-venueid') != "" ? $("#venueDataFutureTableId").attr('data-venueid') : "0";

        let tableContent = new Tabulator("#venueDataFutureTableId", {
            ajaxURL: route("venue.datafuture.list"),
            ajaxParams: { querystr: querystr, status: status, venue: venue},
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
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#venueDataFutureEditModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-VCBDF").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-xlsx-VCBDF").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Source of Tuition Fees",
            });
        });

        // Print
        $("#tabulator-print-VCBDF").on("click", function (event) {
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
    if ($("#venueDataFutureTableId").length) {
        // Init Table
        venueDFListTable.init();

        // Filter function
        function filterHTMLForm() {
            venueDFListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-VCBDF")[0].addEventListener(
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
        $("#tabulator-html-filter-go-VCBDF").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-VCBDF").on("click", function (event) {
            $("#query-VCBDF").val("");
            $("#status-VCBDF").val("1");
            filterHTMLForm();
        });

        let tomOptionsCBDF = {
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
        var venue_datafuture_field_id = new TomSelect('#venue_datafuture_field_id', tomOptionsCBDF);
        var edit_venue_datafuture_field_id = new TomSelect('#edit_venue_datafuture_field_id', tomOptionsCBDF);

        const venueDataFutureAddModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#venueDataFutureAddModal"));
        const venueDataFutureEditModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#venueDataFutureEditModal"));
        const succModalDF = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confModalDF = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalVCBDF"));

        let confModalDelTitleDF = 'Are you sure?';
        let confModalDelDescriptionDF = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescriptionDF = 'Do you really want to re-store these records? Click agree to continue.';

        const venueDataFutureAddModalEl = document.getElementById('venueDataFutureAddModal')
        venueDataFutureAddModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#venueDataFutureAddModal .acc__input-error').html('');
            $('#venueDataFutureAddModal input[type="text"]').val('');
            $('#venueDataFutureAddModal select').val('');

            venue_datafuture_field_id.clear(true);
        });
        
        const venueDataFutureEditModalEl = document.getElementById('venueDataFutureEditModal')
        venueDataFutureEditModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#venueDataFutureEditModal .acc__input-error').html('');
            $('#venueDataFutureEditModal input[type="text"]').val('');
            $('#venueDataFutureEditModal select').val('');
            $('#venueDataFutureEditModal input[name="id"]').val('0');

            edit_venue_datafuture_field_id.clear(true);
        });

        const confirmModalVCBDFEL = document.getElementById('confirmModalVCBDF');
        confirmModalVCBDFEL.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModalVCBDF .agreeWithVCBDF').attr('data-id', '0');
            $('#confirmModalVCBDF .agreeWithVCBDF').attr('data-action', 'none');
        });

        // Delete Course
        $('#venueDataFutureTableId').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModalDF.show();
            document.getElementById('confirmModalVCBDF').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalVCBDF .confModTitleDF').html(confModalDelTitleDF);
                $('#confirmModalVCBDF .confModDescDF').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModalVCBDF .agreeWithVCBDF').attr('data-id', rowID);
                $('#confirmModalVCBDF .agreeWithVCBDF').attr('data-action', 'DELETE');
            });
        });

        $('#venueDataFutureTableId').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confModalDF.show();
            document.getElementById('confirmModalVCBDF').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalVCBDF .confModTitleDF').html(confModalDelTitleDF);
                $('#confirmModalVCBDF .confModDescDF').html('Do you really want to restore these record?');
                $('#confirmModalVCBDF .agreeWithVCBDF').attr('data-id', courseID);
                $('#confirmModalVCBDF .agreeWithVCBDF').attr('data-action', 'RESTORE');
            });
        });

        // Confirm Modal Action
        $('#confirmModalVCBDF .agreeWithVCBDF').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModalVCBDF button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('venue.datafuture.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalVCBDF button').removeAttr('disabled');
                        confModalDF.hide();

                        succModalDF.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Course base datafuture data successfully deleted.');
                        });
                    }
                    venueDFListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('venue.datafuture.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalVCBDF button').removeAttr('disabled');
                        confModalDF.hide();

                        succModalDF.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Course Base Datafuture Data Successfully Restored!');
                        });
                    }
                    venueDFListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $("#venueDataFutureTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("venue.datafuture.edit", editId),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    let datafuture_field_id = dataset.datafuture_field_id ? dataset.datafuture_field_id : '';
                    $('#venueDataFutureEditModal input[name="field_value"]').val(dataset.field_value ? dataset.field_value : '');

                    if(datafuture_field_id != ''){
                        edit_venue_datafuture_field_id.setValue(datafuture_field_id);
                    }
                    

                    $('#venueDataFutureEditModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $('#venueDataFutureEditForm').on('submit', function(e){
            e.preventDefault();
            const formDF = document.getElementById('venueDataFutureEditForm');

            $('#venueDataFutureEditForm').find('input').removeClass('border-danger')
            $('#venueDataFutureEditForm').find('.acc__input-error').html('')

            document.querySelector('#updateBaseDF').setAttribute('disabled', 'disabled');
            document.querySelector('#updateBaseDF svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(formDF);

            axios({
                method: "post",
                url: route('venue.datafuture.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateBaseDF').removeAttribute('disabled');
                document.querySelector('#updateBaseDF svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    venueDataFutureEditModal.hide();
                    venueDFListTable.init();
                    
                    succModalDF.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Venue Base Datafuture Field Data Successfully Updated.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#updateBaseDF').removeAttribute('disabled');
                document.querySelector('#updateBaseDF svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#venueDataFutureEditForm .${key}`).addClass('border-danger')
                            $(`#venueDataFutureEditForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });


        $('#venueDataFutureAddForm').on('submit', function(e){
            e.preventDefault();
            const formDF = document.getElementById('venueDataFutureAddForm');

            $('#venueDataFutureAddForm').find('input').removeClass('border-danger')
            $('#venueDataFutureAddForm').find('.acc__input-error').html('')

            document.querySelector('#saveBaseDF').setAttribute('disabled', 'disabled');
            document.querySelector('#saveBaseDF svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(formDF);

            axios({
                method: "post",
                url: route('venue.datafuture.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveBaseDF').removeAttribute('disabled');
                document.querySelector('#saveBaseDF svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    venueDataFutureAddModal.hide();
                    venueDFListTable.init();
                    
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
                            $(`#venueDataFutureAddForm .${key}`).addClass('border-danger')
                            $(`#venueDataFutureAddForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });

    }
})()