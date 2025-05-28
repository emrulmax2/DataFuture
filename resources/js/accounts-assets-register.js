import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Litepicker from "litepicker";

("use strict");
var assetsRegisterListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let type = $("#acc_asset_type_id").val() != "" ? $("#acc_asset_type_id").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let queryDate = $('#query_date').val() != "" ? $("#query_date").val() : "";

        let tableContent = new Tabulator("#assetsRegisterListTable", {
            ajaxURL: route("accounts.assets.register.list"),
            ajaxParams: { querystr: querystr, status: status, type: type, queryDate : queryDate},
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
                /*{
                    title: "#ID",
                    field: "id",
                },*/
                {
                    title: "Purchase",
                    field: "id",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<div class="block relative">';
                                html += '<div class="font-medium whitespace-nowrap">';
                                    html += '<span class="text-success">'+cell.getData().transaction_date_2+'</span>';
                                html += '</div>';
                                html += '<div class="text-slate-500 text-xs whitespace-nowrap mt-0.5 flex justify-start items-center">';
                                    if(cell.getData().transaction_doc_name != ''){
                                        html += '<a data-id="'+cell.getData().acc_transaction_id+'" href="javascript:void(0);" target="_blank" class="downloadTransDoc text-success mr-2" style="position: relative; top: -1px;"><i data-lucide="hard-drive-download" class="w-4 h-4"></i></a>';
                                    }
                                    html += cell.getData().transaction_code;
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Supplier",
                    field: "detail",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<div class="relative whitespace-normal">';
                                html += cell.getData().detail;
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Price",
                    field: "transaction_amount",
                    headerHozAlign: "left",
                },
                {
                    title: "Type",
                    field: "acc_asset_type_id",
                    headerHozAlign: "left",
                },
                {
                    title: "Description",
                    field: "description",
                    headerHozAlign: "left",
                },
                {
                    title: "Location",
                    field: "location",
                    headerHozAlign: "left",
                },
                {
                    title: "Serial",
                    field: "serial",
                    headerHozAlign: "left",
                },
                {
                    title: "Barcode",
                    field: "barcode",
                    headerHozAlign: "left",
                },
                {
                    title: "Life Span",
                    field: "life",
                    headerHozAlign: "left",
                },
                /*{
                    title: "Status",
                    field: "active",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="form-check form-switch"><input data-id="'+cell.getData().id+'" '+(cell.getData().active == 1 ? 'Checked' : '')+' value="'+cell.getData().active+'" type="checkbox" class="status_updater form-check-input"> </div>';
                    }
                },*/
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "120",
                    download:false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editAssetRegistryModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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

        // Redraw table onresize
        window.addEventListener("resize", () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });

        
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function () {
    assetsRegisterListTable.init();

    // Filter function
    function filterHTMLForm() {
        assetsRegisterListTable.init();
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
        $("#query_date").val("");
        $("#query").val("");
        $("#acc_asset_type_id").val("");
        $("#status").val("2");

        filterHTMLForm();
    });

    
    let pickerOptions = {
        autoApply: true,
        singleMode: false,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: true,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    let theQueryDate = new Litepicker({
        element: document.getElementById('query_date'),
        ...pickerOptions,
        setup: (picker) => {
            picker.on('selected', (date1, date2) => {
                let theDates = $('#query_date').val();
                if(theDates != '' && theDates.length == 23){
                    assetsRegisterListTable.init();
                }
            });
        }
    });

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const editAssetRegistryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAssetRegistryModal"));
    let confModalDelTitle = 'Are you sure?';
        
    const editAssetRegistryModalEl = document.getElementById('editAssetRegistryModal')
    editAssetRegistryModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editAssetRegistryModal .acc__input-error').html('');
        $('#editAssetRegistryModal .modal-body input:not([type="checkbox"])').val('');
        $('#editAssetRegistryModal .modal-body select').val('');
        $('#editAssetRegistryModal .modal-body textarea').val('');
        $('#editAssetRegistryModal input[name="active"]').val('0');
        $('#editAssetRegistryModal input[name="id"]').val('0');
    });


    $("#editAssetRegistryForm").on("submit", function (e) {
        e.preventDefault();
        const form = document.getElementById("editAssetRegistryForm");

        document.querySelector('#updateAssetsBtn').setAttribute('disabled', 'disabled');
        document.querySelector('#updateAssetsBtn svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route("accounts.assets.register.update.single"),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                document.querySelector("#updateAssetsBtn").removeAttribute("disabled");
                document.querySelector("#updateAssetsBtn svg").style.cssText = "display: none;";
                editAssetRegistryModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Assets Register Item data successfully updated.');
                });
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            assetsRegisterListTable.init();
        }).catch((error) => {
            document.querySelector("#updateAssetsBtn").removeAttribute("disabled");
            document.querySelector("#updateAssetsBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editAssetRegistryForm .${key}`).addClass('border-danger')
                        $(`#editAssetRegistryForm  .error-${key}`).html(val)
                    }
                }else {
                    console.log("error");
                }
            }
        });
    });


    $("#assetsRegisterListTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "post",
            url: route("accounts.assets.register.edit"),
            data: {row_id : editId},
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data.row;
                $('#editAssetRegistryModal [name="description"]').val(dataset.description ? dataset.description : '');
                $('#editAssetRegistryModal [name="acc_asset_type_id"]').val(dataset.acc_asset_type_id ? dataset.acc_asset_type_id : '');
                $('#editAssetRegistryModal [name="location"]').val(dataset.location ? dataset.location : '');
                $('#editAssetRegistryModal [name="serial"]').val(dataset.serial ? dataset.serial : '');
                $('#editAssetRegistryModal [name="barcode"]').val(dataset.barcode ? dataset.barcode : '');
                $('#editAssetRegistryModal [name="life"]').val(dataset.life ? dataset.life : '');
                $('#editAssetRegistryModal [name="active"]').val(dataset.active ? dataset.active : '');
                
                $('#editAssetRegistryModal [name="id"]').val(editId);
            }
        }).catch((error) => {
            console.log(error);
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
                url: route('accounts.assets.register.destory', recordID),
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
                
                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
                assetsRegisterListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('accounts.assets.register.restore', recordID),
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
                
                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
                assetsRegisterListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

    // Delete Course
    $('#assetsRegisterListTable').on('click', '.delete_btn', function(){
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
    $('#assetsRegisterListTable').on('click', '.restore_btn', function(){
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

    $('#assetsRegisterListTable').on('click', '.downloadTransDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('accounts.storage.trans.download.link'),
            data: {row_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200){
                let res = response.data.res;
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});

                if(res != ''){
                    window.open(res, '_blank');
                }
            } 
        }).catch(error => {
            if(error.response){
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});
                console.log('error');
            }
        });
    });

    $('#tabulator-export-xl').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var $theLoader = $theBtn.find('.loadingIcon');

        if(!$theBtn.hasClass('disabled')){
            let querystr = $("#query").val() != "" ? $("#query").val() : "";
            let type = $("#acc_asset_type_id").val() != "" ? $("#acc_asset_type_id").val() : "";
            let status = $("#status").val() != "" ? $("#status").val() : "";
            let queryDate = $('#query_date').val() != "" ? $("#query_date").val() : "";

            $theBtn.addClass('disabled');
            $theLoader.removeClass('hidden');

            axios({
                method: "post",
                url: route("accounts.assets.register.export"),
                params:{ querystr: querystr, type: type, status: status, queryDate: queryDate },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                responseType: 'blob',
            }).then((response) => {
                $theBtn.removeClass('disabled');
                $theLoader.addClass('hidden');
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'Assets_register.xlsx'); 
                document.body.appendChild(link);
                link.click();
            }).catch((error) => {
                $theBtn.removeClass('disabled');
                $theLoader.addClass('hidden');
                console.log(error);
            });
        }
    })

    $('#tabulator-print-pdf').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var $theLoader = $theBtn.find('.loadingIcon');

        if(!$theBtn.hasClass('disabled')){
            let querystr = $("#query").val() != "" ? $("#query").val() : "";
            let type = $("#acc_asset_type_id").val() != "" ? $("#acc_asset_type_id").val() : "";
            let status = $("#status").val() != "" ? $("#status").val() : "";
            let queryDate = $('#query_date').val() != "" ? $("#query_date").val() : "";

            $theBtn.addClass('disabled');
            $theLoader.removeClass('hidden');

            axios({
                method: "post",
                url: route("accounts.assets.register.print"),
                params:{ querystr: querystr, type: type, status: status, queryDate: queryDate },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                responseType: 'blob',
            }).then((response) => {
                $theBtn.removeClass('disabled');
                $theLoader.addClass('hidden');
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'Assets_register.pdf'); 
                document.body.appendChild(link);
                link.click();
            }).catch((error) => {
                $theBtn.removeClass('disabled');
                $theLoader.addClass('hidden');
                console.log(error);
            });
        }
    })
})()