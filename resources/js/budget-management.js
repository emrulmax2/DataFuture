import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select"; 
import tippy, { roundArrow } from "tippy.js";
import Litepicker from "litepicker";

("use strict");
var requisitionListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let date_range = $("#date_range").val() != "" && $("#date_range").val().length == 23 ? $("#date_range").val() : "";
        let budget_year_ids = $("#budget_year_ids").val() != "" ? $("#budget_year_ids").val() : "";
        let budget_name_ids = $("#budget_name_ids").val() != "" ? $("#budget_name_ids").val() : "";
        let req_active = $("#req_active").val() != "" ? $("#req_active").val() : "";

        let tableContent = new Tabulator("#requisitionListTable", {
            ajaxURL: route("budget.management.list"),
            ajaxParams: { date_range: date_range, budget_year_ids: budget_year_ids, budget_name_ids: budget_name_ids, req_active : req_active },
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
                    title: "Year",
                    field: "year",
                    headerHozAlign: "left",
                    headerSort: false,
                    width: '90'
                },
                {
                    title: "Vendor",
                    field: "vendor",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        return '<div class="whitespace-normal">'+cell.getData().vendor+'</div>';
                    }
                },
                {
                    title: "Date",
                    field: "date",
                    headerHozAlign: "left",
                    headerSort: false,
                    width: '115'
                },
                {
                    title: "By",
                    field: "required_by",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: '115'
                },
                {
                    title: "Requisitioner",
                    field: "requisitioners",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="whitespace-normal">'+cell.getData().requisitioners+'</div>';
                    }
                },
                {
                    title: "Budget Source",
                    field: "budget",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="whitespace-normal">'+cell.getData().budget+'</div>';
                    }
                },
                {
                    title: "Venue",
                    field: "venue",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="whitespace-normal">'+cell.getData().venue+'</div>';
                    }
                },
                {
                    title: "Status",
                    field: "active",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        if(cell.getData().active == 4){
                            return '<span class="btn btn-sm btn-success text-white px-2 py-1">Completed</span>';
                        }else if(cell.getData().active == 3){
                            return '<span class="btn btn-sm btn-primary text-white px-2 py-1">Approved</span>';
                        }else if(cell.getData().active == 2){
                            return '<span class="btn btn-sm btn-pending text-white px-2 py-1">First Approval</span>';
                        }else if(cell.getData().active == 1){
                            return '<span class="btn btn-sm btn-warning text-white px-2 py-1">Active</span>';
                        }else if(cell.getData().active == 0){
                            return '<span class="btn btn-sm btn-danger text-white px-2 py-1">Cancelled</span>';
                        }
                    }
                },
                {
                    title: "Total",
                    field: "total",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: '115',
                    formatter(cell, formatterParams){
                        return '<div class="font-medium">'+cell.getData().total+'</div>';
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "120",
                    download:false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<a href="'+route('budget.management.show.req', cell.getData().id)+'" class="btn-rounded btn btn-twitter text-white p-0 w-[30px] h-[30px] ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editRequisitionModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-[30px] h-[30px] ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }
                        
                        return btns;
                    },
                }
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
            rowFormatter:function(row){
                var data = row.getData();
                
                /*if(data.active == 1){
                    //row.getElement().style.backgroundColor = "#d977061a";
                    row.getElement().style.backgroundColor = "#FFFFFF";
                }else if(data.active == 2){
                    row.getElement().style.backgroundColor = "#0d6efd33";
                }else if(data.active == 3){
                    row.getElement().style.backgroundColor = "#0d948833";
                }else{
                    row.getElement().style.backgroundColor = "#b91c1c33";
                }*/
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
                sheetName: "Title Details",
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

(function(){
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        create: false,
        allowEmptyOption: false,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let tomOptionsMull = {
        ...tomOptions,
        plugins: {
            ...tomOptions.plugins,
            remove_button: {
                title: 'Remove this item',
            },
        },
    };

    let budget_year_ids = new TomSelect('#budget_year_ids', tomOptions);
    let budget_name_ids = new TomSelect('#budget_name_ids', tomOptions);

    let vendor_id = new TomSelect('#vendor_id', tomOptions);
    let add_budget_year_id = new TomSelect('#add_budget_year_id', tomOptions);
    let budget_set_detail_id = new TomSelect('#budget_set_detail_id', tomOptions);
    let venue_id = new TomSelect('#venue_id', tomOptions);
    let first_approver = new TomSelect('#first_approver', tomOptions);
    let final_approver = new TomSelect('#final_approver', tomOptions);

    let edit_vendor_id = new TomSelect('#edit_vendor_id', tomOptions);
    let edit_budget_year_id = new TomSelect('#edit_budget_year_id', tomOptions);
    let edit_budget_set_detail_id = new TomSelect('#edit_budget_set_detail_id', tomOptions);
    let edit_venue_id = new TomSelect('#edit_venue_id', tomOptions);
    let edit_first_approver = new TomSelect('#edit_first_approver', tomOptions);
    let edit_final_approver = new TomSelect('#edit_final_approver', tomOptions);

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
        element: document.getElementById('date_range'),
        ...pickerOptions,
        setup: (picker) => {
            picker.on('selected', (date1, date2) => {
                let theDates = $('#date_range').val();
                if(theDates != '' && theDates.length == 23){
                    requisitionListTable.init();
                }
            });
        }
    });

    /* START List Table INIT */
    requisitionListTable.init();

    // Filter function
    function filterHTMLForm() {
        requisitionListTable.init();
    }

    // On click go button
    $("#tabulator-html-filter-go").on("click", function (event) {
        filterHTMLForm();
    });

    // On reset filter form
    $("#tabulator-html-filter-reset").on("click", function (event) {
        $("#req_active").val("");
        $("#date_range").val("");
        budget_year_ids.clear(true);
        budget_name_ids.clear(true);

        filterHTMLForm();
    });
    /* End List Table INIT */

    const addBudgetVendorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addBudgetVendorModal"));
    const addRequisitionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addRequisitionModal"));
    const editRequisitionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editRequisitionModal"));
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    const addBudgetVendorModalEl = document.getElementById('addBudgetVendorModal')
    addBudgetVendorModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addBudgetVendorModal .acc__input-error').html('');
        $('#addBudgetVendorModal .modal-body input:not([type="checkbox"])').val('');
        $('#addBudgetVendorModal .modal-body textarea').val('');
        $('#addBudgetVendorModal input[name="active"]').prop('checked', true);
        $('#addBudgetVendorModal input[name="modal_id"]').val('');
    });

    const addRequisitionModalEl = document.getElementById('addRequisitionModal')
    addBudgetVendorModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addRequisitionModal .acc__input-error').html('');
        $('#addRequisitionModal .modal-body input:not([type="checkbox"])').val('');
        $('#addRequisitionModal .modal-body textarea').val('');

        vendor_id.clear(true);
        $('#addRequisitionModal .vendorDetailsWrap').html('').fadeOut();
        budget_set_detail_id.clear(true);
        venue_id.clear(true);
        first_approver.clear(true);
        final_approver.clear(true);

        $('#addRequisitionModal .requisitionItemsTable tbody tr.ajax_rows').remove();
        $('#addRequisitionModal .documentNoteName ').html('');
    });

    const editRequisitionModalEl = document.getElementById('editRequisitionModal')
    addBudgetVendorModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editRequisitionModal .acc__input-error').html('');
        $('#editRequisitionModal .modal-body input:not([type="checkbox"])').val('');
        $('#editRequisitionModal .modal-body textarea').val('');

        edit_vendor_id.clear(true);
        $('#editRequisitionModal .vendorDetailsWrap').html('').fadeOut();
        edit_budget_set_detail_id.clear(true);
        edit_venue_id.clear(true);
        edit_first_approver.clear(true);
        edit_final_approver.clear(true);

        $('#editRequisitionModal .requisitionItemsTable tbody tr').remove();
        $('#editRequisitionModal .documentNoteName ').html('');
        $('#editRequisitionModal input[name="id"]').val('0');
        $('#editRequisitionModal input[name="budget_set_id"]').val('0');
    });

    $('#add_budget_year_id').on('change', function(){
        let $budgetYear = $(this);
        let budget_year_id = $budgetYear.val();
        let $budgetSet = $('#addRequisitionForm [name="budget_set_id"]');
        let $budgetSetDetails = $('#addRequisitionForm #budget_set_detail_id');

        budget_set_detail_id.clear(true);
        budget_set_detail_id.clearOptions();
        $budgetSet.val('0');

        if(budget_year_id > 0){
            axios({
                method: "post",
                url: route("budget.management.get.budget.set"),
                data: { budget_year_id : budget_year_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    let row = response.data.row;
                    let budget_set_id = row.id;
                    let budgetDetails = row.details;

                    $budgetSet.val(budget_set_id);
                    budget_set_detail_id.enable();

                    $.each(budgetDetails, function(index, theRow) {
                        budget_set_detail_id.addOption({
                            value: theRow.id,
                            text: theRow.names.name + (theRow.names.code ? ' ('+theRow.names.code+')' : ''),
                        });
                    });
                    budget_set_detail_id.refreshOptions()
                }
            }).catch(error => {
                budget_set_detail_id.disable();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            budget_set_detail_id.disable();
        }
    });

    $('#edit_budget_year_id').on('change', function(){
        let $budgetYear = $(this);
        let budget_year_id = $budgetYear.val();
        let $budgetSet = $('#editRequisitionForm [name="budget_set_id"]');
        let $budgetSetDetails = $('#editRequisitionForm #edit_budget_set_detail_id');

        edit_budget_set_detail_id.clear(true);
        edit_budget_set_detail_id.clearOptions();
        $budgetSet.val('0');

        if(budget_year_id > 0){
            axios({
                method: "post",
                url: route("budget.management.get.budget.set"),
                data: { budget_year_id : budget_year_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    let row = response.data.row;
                    let budget_set_id = row.id;
                    let budgetDetails = row.details;

                    $budgetSet.val(budget_set_id);
                    edit_budget_set_detail_id.enable();

                    $.each(budgetDetails, function(index, theRow) {
                        edit_budget_set_detail_id.addOption({
                            value: theRow.id,
                            text: theRow.names.name + (theRow.names.code ? ' ('+theRow.names.code+')' : ''),
                        });
                    });
                    edit_budget_set_detail_id.refreshOptions()
                }
            }).catch(error => {
                edit_budget_set_detail_id.disable();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            budget_set_detail_id.disable();
        }
    });

    let theBlankRow = '<tr class="requisition_item_row ajax_rows">\
                        <td><input type="text" name="items[description][]" class="description form-control w-full"/></td>\
                        <td class="w-[160px]"><input type="number" step="1" name="items[quantity][]" class="quantity form-control w-full"/></td>\
                        <td class="w-[160px]"><input type="number" step="any" name="items[price][]" class="price form-control w-full"/></td>\
                        <td class="w-[160px] relative">\
                            <input readonly type="number" step="any" name="items[total][]" class="total form-control w-full"/>\
                            <button type="button" class="remove_req_row btn btn-danger w-[25px] h-[25px] btn-sm text-white rounded-full absolute t-0 r-0 b-0 m-auto p-0" style="margin-right: -4px;"><i data-lucide="trash-2" class="w-3 h-3"></i></button>\
                        </td>\
                    </tr>';
    $('#addRequisitionModal .addReqItem').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);

        $('#addRequisitionModal .requisitionItemsTable tbody').append(theBlankRow);
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    });

    $('#editRequisitionModal .addReqItem').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var theSerial = ($('#editRequisitionModal .requisitionItemsTable tbody tr').length > 0 ? parseInt($('#editRequisitionModal .requisitionItemsTable tbody tr').last().attr('data-serial')) : 0);
        theSerial += 1;
        let theBlankRow = '<tr class="requisition_item_row ajax_rows" data-serial="'+theSerial+'">\
                        <td><input type="text" name="items['+theSerial+'][description]" class="description form-control w-full"/></td>\
                        <td class="w-[160px]"><input type="number" step="1" name="items['+theSerial+'][quantity]" class="quantity form-control w-full"/></td>\
                        <td class="w-[160px]"><input type="number" step="any" name="items['+theSerial+'][price]" class="price form-control w-full"/></td>\
                        <td class="w-[160px] relative">\
                            <input readonly type="number" step="any" name="items['+theSerial+'][total]" class="total form-control w-full"/>\
                            <input type="hidden" name="items['+theSerial+'][id]" value="0" class="form-control w-full"/>\
                            <button type="button" class="remove_req_row btn btn-danger w-[25px] h-[25px] btn-sm text-white rounded-full absolute t-0 r-0 b-0 m-auto p-0" style="margin-right: -4px;"><i data-lucide="trash-2" class="w-3 h-3"></i></button>\
                        </td>\
                    </tr>';

        $('#editRequisitionModal .requisitionItemsTable tbody').append(theBlankRow);
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    });

    $('#addRequisitionModal').on('keyup paste', '.quantity', function(){
        calculate_total_items('addRequisitionModal');
    });

    $('#addRequisitionModal').on('keyup paste', '.price', function(){
        calculate_total_items('addRequisitionModal');
    });

    $('#addRequisitionModal').on('click', '.remove_req_row', function(){
        var $theBtn = $(this);
        $theBtn.closest('.requisition_item_row').remove();
        calculate_total_items('addRequisitionModal');
    });
    
    $('#addRequisitionForm').on('change', '#addRequiDocument', function(){
        showFileNames('addRequiDocument', 'addRequiDocumentName');
    });

    $('#editRequisitionModal').on('keyup paste', '.quantity', function(){
        calculate_total_items('editRequisitionModal');
    });

    $('#editRequisitionModal').on('keyup paste', '.price', function(){
        calculate_total_items('editRequisitionModal');
    });

    $('#editRequisitionModal').on('click', '.remove_req_row', function(){
        var $theBtn = $(this);
        $theBtn.closest('.requisition_item_row').remove();
        calculate_total_items('editRequisitionModal');
    });
    
    $('#editRequisitionModal').on('change', '#editRequiDocument', function(){
        showFileNames('editRequiDocument', 'editRequiDocumentName');
    });

    $('#vendor_id').on('change', function(){
        var theVendor = $('#vendor_id').val();
        if(theVendor > 0){
            axios({
                method: "get",
                url: route("budget.settings.vendors.edit", theVendor),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    let row = response.data;
                    var html = '';
                        html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                            html += '<div class="col-span-4 text-slate-500 font-medium">Vendor Name</div>';
                            html += '<div class="col-span-8 font-medium">'+row.name+'</div>';
                        html += '</div>';
                        if(row.email != '' && row.email != null){
                            html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                                html += '<div class="col-span-4 text-slate-500 font-medium">Email</div>';
                                html += '<div class="col-span-8 font-medium">'+row.email+'</div>';
                            html += '</div>';
                        }
                        if(row.phone != '' && row.phone != null){
                            html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                                html += '<div class="col-span-4 text-slate-500 font-medium">Phone</div>';
                                html += '<div class="col-span-8 font-medium">'+row.phone+'</div>';
                            html += '</div>';
                        }
                        if(row.address != '' && row.address != null){
                            html += '<div class="grid grid-cols-12 gap-0">';
                                html += '<div class="col-span-4 text-slate-500 font-medium">Address</div>';
                                html += '<div class="col-span-8 font-medium">'+row.address+'</div>';
                            html += '</div>';
                        }
    
                    $('#addRequisitionModal .vendorDetailsWrap').html(html).fadeIn();
                }
            }).catch(error => {
                $('#addRequisitionModal .vendorDetailsWrap').html(html).fadeIn();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $('#addRequisitionModal .vendorDetailsWrap').html('').fadeOut();
        }
    });

    $('#edit_vendor_id').on('change', function(){
        var theVendor = $('#edit_vendor_id').val();
        if(theVendor > 0){
            axios({
                method: "get",
                url: route("budget.settings.vendors.edit", theVendor),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    let row = response.data;
                    var html = '';
                        html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                            html += '<div class="col-span-4 text-slate-500 font-medium">Vendor Name</div>';
                            html += '<div class="col-span-8 font-medium">'+row.name+'</div>';
                        html += '</div>';
                        if(row.email != '' && row.email != null){
                            html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                                html += '<div class="col-span-4 text-slate-500 font-medium">Email</div>';
                                html += '<div class="col-span-8 font-medium">'+row.email+'</div>';
                            html += '</div>';
                        }
                        if(row.phone != '' && row.phone != null){
                            html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                                html += '<div class="col-span-4 text-slate-500 font-medium">Phone</div>';
                                html += '<div class="col-span-8 font-medium">'+row.phone+'</div>';
                            html += '</div>';
                        }
                        if(row.address != '' && row.address != null){
                            html += '<div class="grid grid-cols-12 gap-0">';
                                html += '<div class="col-span-4 text-slate-500 font-medium">Address</div>';
                                html += '<div class="col-span-8 font-medium">'+row.address+'</div>';
                            html += '</div>';
                        }
    
                    $('#editRequisitionModal .vendorDetailsWrap').html(html).fadeIn();
                }
            }).catch(error => {
                $('#editRequisitionModal .vendorDetailsWrap').html(html).fadeIn();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $('#editRequisitionModal .vendorDetailsWrap').html('').fadeOut();
        }
    });

    $('#addRequisitionForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addRequisitionForm');
    
        document.querySelector('#saveReqBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveReqBtn svg").style.cssText ="display: inline-block;";

        var errorCount = 0;
        if($('#addRequisitionForm .requisition_item_row').length > 0){
            $('#addRequisitionForm .requisition_item_row').each(function(){
                var $theRow = $(this);
                if($theRow.find('.description').val() == '' || $theRow.find('.quantity').val() == '' || $theRow.find('.price').val() == '' || $theRow.find('.total').val() == ''){
                    errorCount += 1;
                }
            });
        }else{
            errorCount = 1;
        }

        if(errorCount > 0){
            document.querySelector('#saveReqBtn').removeAttribute('disabled');
            document.querySelector("#saveReqBtn svg").style.cssText = "display: none;";
            $('#addRequisitionForm .error-requisition_ietems').html('Please fill out Item Informations with valid data.')
        }else{
            $('#addRequisitionForm .error-requisition_ietems').html('')
            let form_data = new FormData(form);
            form_data.append('file', $('#addRequisitionForm #addRequiDocument')[0].files[0]); 
            axios({
                method: "post",
                url: route('budget.management.store.req'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveReqBtn').removeAttribute('disabled');
                document.querySelector("#saveReqBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addRequisitionModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Requisition Successfully created.');
                    });  
                    
                    setTimeout(() => {
                        succModal.hide();
                    }, 2000);
                }
                requisitionListTable.init();
            }).catch(error => {
                document.querySelector('#saveReqBtn').removeAttribute('disabled');
                document.querySelector("#saveReqBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addRequisitionForm .${key}`).addClass('border-danger');
                            $(`#addRequisitionForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        }
    });

    $("#requisitionListTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "post",
            url: route("budget.management.edit.req"),
            data: {row_id : editId},
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let row = response.data.row;
                let budget_names = response.data.budget_names;
                if(budget_names){
                    $.each(budget_names, function(index, r){
                        edit_budget_set_detail_id.addOption({
                            value: r.id,
                            text: r.name,
                        })
                    });
                }else{
                    edit_budget_set_detail_id.clearOptions();
                }
                if(row.budget_year_id > 0){
                    edit_budget_year_id.addItem(row.budget_year_id, true);
                }else{
                    edit_budget_year_id.clear(true);
                }
                if(row.budget_set_detail_id > 0){
                    edit_budget_set_detail_id.addItem(row.budget_set_detail_id);
                }else{
                    edit_budget_set_detail_id.clear(true);
                }

                if(row.vendor_id > 0){
                    edit_vendor_id.addItem(row.vendor_id);
                    $('#edit_vendor_id').trigger('change');
                }else{
                    edit_vendor_id.clear(true);
                    $('#edit_vendor_id').trigger('change');
                }
                if(row.venue_id > 0){
                    edit_venue_id.addItem(row.venue_id);
                }else{
                    edit_venue_id.clear(true);
                }
                if(row.first_approver > 0){
                    edit_first_approver.addItem(row.first_approver);
                }else{
                    edit_first_approver.clear(true);
                }
                if(row.final_approver > 0){
                    edit_final_approver.addItem(row.final_approver);
                }else{
                    edit_final_approver.clear(true);
                }

                $('#editRequisitionModal input[name="required_by"]').val(row.required_by ? row.required_by : '');
                $('#editRequisitionModal textarea[name="note"]').val(row.note ? row.note : '');
                $('#editRequisitionModal input[name="id"]').val(editId);
                $('#editRequisitionModal input[name="budget_set_id"]').val(row.budget_set_id ? row.budget_set_id : '0');
                
                var html = '';
                var total = 0;
                if(row.items){
                    var serial = 1;
                    $.each(row.items, function(index, item){
                        total += item.total
                        html += '<tr class="requisition_item_row ajax_rows" data-serial="'+serial+'">';
                            html += '<td><input type="text" value="'+(item.description)+'" name="items['+serial+'][description]" class="description form-control w-full"/></td>';
                            html += '<td class="w-[160px]"><input type="number" value="'+(item.quantity)+'" step="1" name="items['+serial+'][quantity]" class="quantity form-control w-full"/></td>';
                            html += '<td class="w-[160px]"><input type="number" value="'+(item.price)+'" step="any" name="items['+serial+'][price]" class="price form-control w-full"/></td>';
                            html += '<td class="w-[160px] relative">';
                                html += '<input readonly type="number" step="any" value="'+(item.total)+'" name="items['+serial+'][total]" class="total form-control w-full"/>';
                                html += '<input type="hidden" name="items['+serial+'][id]" value="'+(item.id)+'" class="form-control w-full"/>';
                                html += '<button type="button" class="remove_req_row btn btn-danger w-[25px] h-[25px] btn-sm text-white rounded-full absolute t-0 r-0 b-0 m-auto p-0" style="margin-right: -4px;"><i data-lucide="trash-2" class="w-3 h-3"></i></button>';
                            html += '</td>';
                        html += '</tr>';

                        serial += 1;
                    });
                }
                $('#editRequisitionModal .requisitionItemsTable tbody').html(html);
                $('#editRequisitionModal .requisitionItemsTable tfoot .requisition_total').val(total > 0 ? total : 0);
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editRequisitionForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editRequisitionForm');
    
        document.querySelector('#updateReqBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#updateReqBtn svg").style.cssText ="display: inline-block;";

        var errorCount = 0;
        if($('#editRequisitionForm .requisition_item_row').length > 0){
            $('#editRequisitionForm .requisition_item_row').each(function(){
                var $theRow = $(this);
                if($theRow.find('.description').val() == '' || $theRow.find('.quantity').val() == '' || $theRow.find('.price').val() == '' || $theRow.find('.total').val() == ''){
                    errorCount += 1;
                }
            });
        }else{
            errorCount = 1;
        }

        if(errorCount > 0){
            document.querySelector('#updateReqBtn').removeAttribute('disabled');
            document.querySelector("#updateReqBtn svg").style.cssText = "display: none;";
            $('#editRequisitionForm .error-requisition_ietems').html('Please fill out Item Informations with valid data.')
        }else{
            $('#editRequisitionForm .error-requisition_ietems').html('')
            let form_data = new FormData(form);
            form_data.append('file', $('#editRequisitionForm #editRequiDocument')[0].files[0]); 
            axios({
                method: "post",
                url: route('budget.management.update.req'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateReqBtn').removeAttribute('disabled');
                document.querySelector("#updateReqBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    editRequisitionModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Requisition Successfully updated.');
                    });  
                    
                    setTimeout(() => {
                        succModal.hide();
                    }, 2000);
                }
                requisitionListTable.init();
            }).catch(error => {
                document.querySelector('#updateReqBtn').removeAttribute('disabled');
                document.querySelector("#updateReqBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editRequisitionForm .${key}`).addClass('border-danger');
                            $(`#editRequisitionForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        }
    });

    function showFileNames(inputId, targetPreviewId) {
        let fileInput = document.getElementById(inputId);
        let namePreview = document.getElementById(targetPreviewId);
        let fileName = '';
        if(fileInput.files.length > 0){
            fileName += '<ul class="m-0">';
            $.each(fileInput.files, function(index, file){
                fileName += '<li class="mb-1 text-primary flex items-center"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>'+file.name+'</li>';
            });
            fileName += '</ul>';
        }
        
        $('#'+targetPreviewId).html(fileName);
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });

        return false;
    };

    function calculate_total_items(theModalId){
        var $theModal = $('#'+theModalId);
        var $theTable = $theModal.find('.requisitionItemsTable');
        var $theTableTotal = $theTable.find('.requisition_total');

        var theTotal = 0;
        $theTable.find('tr.requisition_item_row').each(function(e){
            var $theRow = $(this);
            var theQuantity = $theRow.find('.quantity').val() != '' ? parseInt($theRow.find('.quantity').val(), 10) : 0;
            var thePrice = $theRow.find('.price').val() != '' ? $theRow.find('.price').val() * 1 : 0;
            
            if($theRow.find('.quantity').val() != '' && $theRow.find('.price').val() != ''){
                var theRowTotal = theQuantity * thePrice;
                $theRow.find('.total').val(theRowTotal.toFixed(2));

                theTotal += theRowTotal;
            }
        });

        if(theTotal > 0){
            $theTableTotal.val(theTotal.toFixed(2));
        }else{
            $theTableTotal.val('');
        }
    }

    $(document).on('click', '.add_vendor', function(e){
        var $theBtn = $(this);
        var modal_id = $theBtn.attr('data-modal');
        $('#addBudgetVendorModal input[name="modal_id"]').val(modal_id);
    });

    $('#addBudgetVendorForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        let modal_id = '#'+$form.find('input[name="modal_id"]').val();
        const form = document.getElementById('addBudgetVendorForm');
    
        document.querySelector('#saveVenBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveVenBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('budget.settings.vendors.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveVenBtn').removeAttribute('disabled');
            document.querySelector("#saveVenBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addBudgetVendorModal.hide();
                var row = response.data.row;
                if(row){
                    vendor_id.addOption({
                        value: row.id,
                        text: row.name,
                    });

                    vendor_id.addItem(row.id);
                    var html = '';
                    html += '<div class="grid grid-cols-12 gap-0">';
                        html += '<div class="col-span-4 text-slate-500 font-medium">Vendor Name</div>';
                        html += '<div class="col-span-8 font-medium">'+row.name+'</div>';
                    html += '</div>';
                    if(row.email != '' && row.email != null){
                        html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                            html += '<div class="col-span-4 text-slate-500 font-medium">Email</div>';
                            html += '<div class="col-span-8 font-medium">'+row.email+'</div>';
                        html += '</div>';
                    }
                    if(row.phone != '' && row.phone != null){
                        html += '<div class="grid grid-cols-12 gap-0 mb-2">';
                            html += '<div class="col-span-4 text-slate-500 font-medium">Phone</div>';
                            html += '<div class="col-span-8 font-medium">'+row.phone+'</div>';
                        html += '</div>';
                    }
                    if(row.address != '' && row.address != null){
                        html += '<div class="grid grid-cols-12 gap-0">';
                            html += '<div class="col-span-4 text-slate-500 font-medium">Address</div>';
                            html += '<div class="col-span-8 font-medium">'+row.address+'</div>';
                        html += '</div>';
                    }

                    $(modal_id+' .vendorDetailsWrap').html(html).fadeIn();
                }
            }
        }).catch(error => {
            document.querySelector('#saveVenBtn').removeAttribute('disabled');
            document.querySelector("#saveVenBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addBudgetVendorForm .${key}`).addClass('border-danger');
                        $(`#addBudgetVendorForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

})();