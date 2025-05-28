import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select"; 
import tippy, { roundArrow } from "tippy.js";

("use strict");
var budgetSetListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let budget_year_id = $("#search_budget_year_id").val() != "" ? $("#search_budget_year_id").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#budgetSetListTable", {
            ajaxURL: route("budget.settings.set.list"),
            ajaxParams: { budget_year_id: budget_year_id, status: status },
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
                    width: '80',
                },
                {
                    title: "Year",
                    field: "budget_year_id",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Total Budget",
                    field: "amount",
                    headerSort: false,
                    headerHozAlign: "left",
                },
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editBudgetSetModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                $(".tabltooltip").each(function () {
                    let tipyyoptions = {
                        content: $(this).attr("alt"),
                    };
                    tippy(this, {
                        arrow: roundArrow,
                        animation: "shift-away",
                        ...tipyyoptions,
                    });
                })
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
            $(".tabltooltip").each(function () {
                let tipyyoptions = {
                    content: $(this).attr("alt"),
                };
                tippy(this, {
                    arrow: roundArrow,
                    animation: "shift-away",
                    ...tipyyoptions,
                });
            })
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

    let search_budget_year_id = new TomSelect('#search_budget_year_id', tomOptions);

    let budget_year_id = new TomSelect('#budget_year_id', tomOptions);
    let edit_budget_year_id = new TomSelect('#edit_budget_year_id', tomOptions);

    // Tabulator
    budgetSetListTable.init();
    
    // Filter function
    function filterHTMLForm() {
        budgetSetListTable.init();
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
        search_budget_year_id.clear(true)
        $("#status").val("1");
        filterHTMLForm();
    });

    const addBudgetSetModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addBudgetSetModal"));
    const editBudgetSetModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editBudgetSetModal"));
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    const addBudgetSetModalEl = document.getElementById('addBudgetSetModal')
    addBudgetSetModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addBudgetSetModal .acc__input-error').html('');
        $('#addBudgetSetModal .modal-body input:not([type="checkbox"])').val('');

        budget_year_id.clear(true);
        $('#addBudgetSetModal .budgetNameWrap').fadeOut('fast', function(){
            $('.budget_name_id', this).prop('checked', false);
        });
        $('#addBudgetSetModal .budgetTableWrap').fadeOut('fast', function(){
            $('#addBudgetSetModal .budgetTableWrap .theBudgetTable tbody').html('<tr class="noticeRow"><td colspan="2"><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Please select some budget name.</div></td></tr>');
        });

        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    });

    const editBudgetSetModalEl = document.getElementById('editBudgetSetModal')
    editBudgetSetModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editBudgetSetModal .acc__input-error').html('');
        $('#editBudgetSetModal .modal-body input:not([type="checkbox"])').val('');

        budget_year_id.clear(true);
        $('#editBudgetSetModal .budgetNameWrap').fadeOut('fast', function(){
            $('.budget_name_id', this).prop('checked', false);
        });
        $('#editBudgetSetModal .budgetTableWrap').fadeOut('fast', function(){
            $('#editBudgetSetModal .budgetTableWrap .theBudgetTable tbody').html('<tr class="noticeRow"><td colspan="2"><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Please select some budget name.</div></td></tr>');
        });

        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
        $('#editBudgetSetModal input[name="id"]').val('0');
    });

    $('#budget_year_id').on('change', function(){
        var $theYear = $(this);
        var theYear = $theYear.val();

        if(theYear > 0){
            $('#addBudgetSetModal .budgetNameWrap').fadeIn('fast', function(){
                $('.budget_name_id', this).prop('checked', false);
            });
            $('#addBudgetSetModal .budgetTableWrap').fadeOut('fast', function(){
                $('#addBudgetSetModal .budgetTableWrap .theBudgetTable tbody').html('<tr class="noticeRow"><td colspan="2"><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Please select some budget name.</div></td></tr>');
            });
        }else{
            $('#addBudgetSetModal .budgetNameWrap').fadeOut('fast', function(){
                $('.budget_name_id', this).prop('checked', false);
            });
            $('#addBudgetSetModal .budgetTableWrap').fadeOut('fast', function(){
                $('#addBudgetSetModal .budgetTableWrap .theBudgetTable tbody').html('<tr class="noticeRow"><td colspan="2"><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Please select some budget name.</div></td></tr>');
            });
        }
    
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    });

    $('#edit_budget_year_id').on('change', function(){
        var $theYear = $(this);
        var theYear = $theYear.val();

        if(theYear > 0){
            $('#editBudgetSetModal .budgetNameWrap').fadeIn('fast', function(){
                $('.budget_name_id', this).prop('checked', false);
            });
            $('#editBudgetSetModal .budgetTableWrap').fadeOut('fast', function(){
                $('#editBudgetSetModal .budgetTableWrap .theBudgetTable tbody').html('<tr class="noticeRow"><td colspan="2"><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Please select some budget name.</div></td></tr>');
            });
        }else{
            $('#editBudgetSetModal .budgetNameWrap').fadeOut('fast', function(){
                $('.budget_name_id', this).prop('checked', false);
            });
            $('#editBudgetSetModal .budgetTableWrap').fadeOut('fast', function(){
                $('#editBudgetSetModal .budgetTableWrap .theBudgetTable tbody').html('<tr class="noticeRow"><td colspan="2"><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Please select some budget name.</div></td></tr>');
            });
        }
    
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    });

    $('#addBudgetSetModal .budget_name_id').on('change', function(e){
        var $theYear = $('#addBudgetSetModal #budget_year_id');
        var theYear = $theYear.val();
        var $theBudgetName = $(this);
        var theBudgetNameId = $theBudgetName.val();
        var checkedBudgetNames = $('#addBudgetSetModal .budget_name_id:checked').length;

        if($theBudgetName.prop('checked')){
            axios({
                method: "post",
                url: route('budget.settings.set.get.budget'),
                data: {theYear : theYear, theBudgetNameId : theBudgetNameId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#addBudgetSetModal .budgetTableWrap').fadeIn('fast', function(){
                        $('#addBudgetSetModal .budgetTableWrap .theBudgetTable tbody tr.noticeRow').remove();
                        $('#addBudgetSetModal .budgetTableWrap .theBudgetTable tbody').append(response.data.html);
                    });
                }
            }).catch(error => {
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            if(checkedBudgetNames > 0){
                $('#addBudgetSetModal .budgetTableWrap .theBudgetTable tbody #budget_row_'+theBudgetNameId).remove();
            }else{
                $('#addBudgetSetModal .budgetTableWrap .theBudgetTable tbody #budget_row_'+theBudgetNameId).remove();
                $('#addBudgetSetModal .budgetTableWrap').fadeOut('fast', function(){
                    $('#addBudgetSetModal .budgetTableWrap .theBudgetTable tbody').html('<tr class="noticeRow"><td colspan="2"><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Please select some budget name.</div></td></tr>');
                });
            }
        }
    
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    });

    $('#editBudgetSetModal .budget_name_id').on('change', function(e){
        var $theYear = $('#editBudgetSetModal #edit_budget_year_id');
        var theYear = $theYear.val();
        var $theBudgetName = $(this);
        var theBudgetNameId = $theBudgetName.val();
        var checkedBudgetNames = $('#editBudgetSetModal .budget_name_id:checked').length;

        if($theBudgetName.prop('checked')){
            axios({
                method: "post",
                url: route('budget.settings.set.get.budget'),
                data: {theYear : theYear, theBudgetNameId : theBudgetNameId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#editBudgetSetModal .budgetTableWrap').fadeIn('fast', function(){
                        $('#editBudgetSetModal .budgetTableWrap .theBudgetTable tbody tr.noticeRow').remove();
                        $('#editBudgetSetModal .budgetTableWrap .theBudgetTable tbody').append(response.data.html);
                    });
                }
            }).catch(error => {
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            if(checkedBudgetNames > 0){
                $('#editBudgetSetModal .budgetTableWrap .theBudgetTable tbody #budget_row_'+theBudgetNameId).remove();
            }else{
                $('#editBudgetSetModal .budgetTableWrap .theBudgetTable tbody #budget_row_'+theBudgetNameId).remove();
                $('#editBudgetSetModal .budgetTableWrap').fadeOut('fast', function(){
                    $('#editBudgetSetModal .budgetTableWrap .theBudgetTable tbody').html('<tr class="noticeRow"><td colspan="2"><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Please select some budget name.</div></td></tr>');
                });
            }
        }
    
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    });

    $('#addBudgetSetForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addBudgetSetForm');
    
        document.querySelector('#saveSetBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveSetBtn svg").style.cssText ="display: inline-block;";

        var errorCount = 0;
        if($('#addBudgetSetForm .theBudgetTable .budget_row').length > 0){
            $('#addBudgetSetForm .theBudgetTable .budget_row .budget_amount').each(function(){
                if($(this).val() == ''){
                    errorCount += 1;
                }
            })
        }

        if(errorCount > 0){
            document.querySelector('#saveSetBtn').removeAttribute('disabled');
            document.querySelector("#saveSetBtn svg").style.cssText = "display: none;";

            $('#addBudgetSetForm .error-budget_details').html('Please fill out budget details.');
        }else{
            $('#addBudgetSetForm .error-budget_details').html('');
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('budget.settings.set.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveSetBtn').removeAttribute('disabled');
                document.querySelector("#saveSetBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addBudgetSetModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Budget Data Successfully inserted.');
                    });  
                    
                    setTimeout(() => {
                        succModal.hide();
                    }, 2000);
                }
                budgetSetListTable.init();
            }).catch(error => {
                document.querySelector('#saveSetBtn').removeAttribute('disabled');
                document.querySelector("#saveSetBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addBudgetSetForm .${key}`).addClass('border-danger');
                            $(`#addBudgetSetForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        }
    });

    $("#budgetSetListTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("budget.settings.set.edit", editId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                if(dataset.budget_year_id > 0){
                    edit_budget_year_id.addItem(dataset.budget_year_id);
                }else{
                    edit_budget_year_id.clear(true);
                }
                $('#editBudgetSetModal input[name="id"]').val(editId);



                if(dataset.details){
                    $('#editBudgetSetModal .budgetNameWrap').fadeIn('fast', function(){
                        $.each(dataset.details, function(index, row) {
                            $('#editBudgetSetModal .budgetNameWrap #edit_budget_name_'+row.budget_name_id).prop('checked', true);
                        });
                    })
                    $('#editBudgetSetModal .budgetTableWrap').fadeIn('fast', function(){
                        $('#editBudgetSetModal .budgetTableWrap .theBudgetTable tbody').html(dataset.details_html);
                    })
                }else{
                    $('#editBudgetSetModal .budgetNameWrap').fadeOut('fast', function(){
                        $('#editBudgetSetModal .budgetNameWrap .budget_name_id').prop('checked', false);
                    })
                    $('#editBudgetSetModal .budgetTableWrap').fadeOut('fast', function(){
                        $('#editBudgetSetModal .budgetTableWrap .theBudgetTable tbody').html('<tr class="noticeRow"><td colspan="2"><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Please select some budget name.</div></td></tr>');
                    });
                }
    
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

    $('#editBudgetSetForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editBudgetSetForm');
    
        document.querySelector('#updateSetBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#updateSetBtn svg").style.cssText ="display: inline-block;";

        var errorCount = 0;
        if($('#editBudgetSetForm .theBudgetTable .budget_row').length > 0){
            $('#editBudgetSetForm .theBudgetTable .budget_row .budget_amount').each(function(){
                if($(this).val() == ''){
                    errorCount += 1;
                }
            })
        }

        if(errorCount > 0){
            document.querySelector('#updateSetBtn').removeAttribute('disabled');
            document.querySelector("#updateSetBtn svg").style.cssText = "display: none;";

            $('#editBudgetSetForm .error-budget_details').html('Please fill out budget details.');
        }else{
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('budget.settings.set.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateSetBtn').removeAttribute('disabled');
                document.querySelector("#updateSetBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    editBudgetSetModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Budget Data Successfully updated.');
                    });  
                    
                    setTimeout(() => {
                        succModal.hide();
                    }, 2000);
                }
                budgetSetListTable.init();
            }).catch(error => {
                document.querySelector('#updateSetBtn').removeAttribute('disabled');
                document.querySelector("#updateSetBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editBudgetSetForm .${key}`).addClass('border-danger');
                            $(`#editBudgetSetForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        }
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
                url: route('budget.settings.set.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                    });
                
                    setTimeout(() => {
                        succModal.hide();
                    }, 2000);
                }
                budgetSetListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('budget.settings.set.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Record Successfully Restored!');
                    });
                
                    setTimeout(() => {
                        succModal.hide();
                    }, 2000);
                }
                budgetSetListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'CHANGESTAT'){
            axios({
                method: 'post',
                url: route('budget.settings.set.update.status', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Record status successfully updated!');
                    });
                
                    setTimeout(() => {
                        succModal.hide();
                    }, 2000);
                }
                budgetSetListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

    // Delete Course
    $('#budgetSetListTable').on('click', '.status_updater', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTAT');
        });
    });

    // Delete Course
    $('#budgetSetListTable').on('click', '.delete_btn', function(){
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
    $('#budgetSetListTable').on('click', '.restore_btn', function(){
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

})();