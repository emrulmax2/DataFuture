import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var bankListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#bankListTable", {
            ajaxURL: route("site.settings.banks.list"),
            ajaxParams: { querystr: querystr, status: status },
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
                    width: "80",
                },
                {
                    title: "Name",
                    field: "bank_name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<div class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-5 inline-block">';
                                    html += '<img alt="'+cell.getData().bank_name+'" class="rounded-full shadow" src="'+cell.getData().image_url+'">';
                                html += '</div>';
                                html += '<div class="inline-block relative" style="top: -15px;">';
                                    html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().bank_name+'</div>';
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Opening Date",
                    field: "opening_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Opening Balance",
                    field: "opening_balance",
                    headerHozAlign: "left",
                },
                {
                    title: "Audit Status",
                    field: "audit_status",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        return (cell.getData().audit_status == 1 ? '<span class="btn inline-flex btn-success w-auto px-2 text-white py-0 rounded-0">Yes</span>' : '<span class="btn inline-flex btn-danger w-auto px-2 text-white py-0 rounded-0">No</span>');
                    }
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="form-check form-switch"><input data-id="'+cell.getData().id+'" '+(cell.getData().status == 1 ? 'Checked' : '')+' value="'+cell.getData().status+'" type="checkbox" class="status_updater form-check-input"> </div>';
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editBankModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Status Details",
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
    // Tabulator
    if ($("#bankListTable").length) {
        // Init Table
        bankListTable.init();

        // Filter function
        function filterHTMLForm() {
            bankListTable.init();
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
            $("#status").val("1");
            filterHTMLForm();
        });

        const addBankModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addBankModal"));
        const editBankModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editBankModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addBankModalEl = document.getElementById('addBankModal')
        addBankModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addBankModal .acc__input-error').html('');
            $('#addBankModal .modal-body input:not([type="checkbox"])').val('');
            $('#addBankModal input[name="status"]').prop('checked', true);
            $('#addBankModal #bankImageAdd').attr('src', $('#addBankModal #bankImageAdd').attr('data-placeholder'));
        });
        
        const editBankModalEl = document.getElementById('editBankModal')
        editBankModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editBankModal .acc__input-error').html('');
            $('#editBankModal .modal-body input:not([type="checkbox"])').val('');
            $('#editBankModal input[name="id"]').val('0');
            $('#editBankModal input[name="status"]').prop('checked', false);
            $('#editBankModal #bankImageEdit').attr('src', $('#editBankModal #bankImageEdit').attr('data-placeholder'));
        });

        $('#addBankModal').on('change', '#bankPhotoAdd', function(){
            showPreview('bankPhotoAdd', 'bankImageAdd');
        })
        $('#editBankModal').on('change', '#bankPhotoEdit', function(){
            showPreview('bankPhotoEdit', 'bankImageEdit');
        })

        $('#addBankForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addBankForm');
        
            document.querySelector('#saveBank').setAttribute('disabled', 'disabled');
            document.querySelector("#saveBank svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            form_data.append('file', $('#addBankForm input[name="photo"]')[0].files[0]); 
            axios({
                method: "post",
                url: route('site.settings.banks.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveBank').removeAttribute('disabled');
                document.querySelector("#saveBank svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addBankModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Bank Item Successfully inserted.');
                    }); 
                    
                    setTimeout(function(){
                        succModal.hide();
                    }, 2000);
                }
                bankListTable.init();
            }).catch(error => {
                document.querySelector('#saveBank').removeAttribute('disabled');
                document.querySelector("#saveBank svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addBankForm .${key}`).addClass('border-danger');
                            $(`#addBankForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#bankListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "post",
                url: route("site.settings.banks.edit"),
                data: {row_id : editId},
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editBankModal input[name="bank_name"]').val(dataset.bank_name ? dataset.bank_name : '');
                    $('#editBankModal input[name="opening_balance"]').val(dataset.opening_balance ? dataset.opening_balance.toFixed(2) : '');
                    $('#editBankModal input[name="opening_date"]').val(dataset.opening_date ? dataset.opening_date : '');

                    if(dataset.audit_status == 1){
                        $('#editBankModal [name="audit_status"]').prop('checked', true);
                    }else{
                        $('#editBankModal [name="audit_status"]').prop('checked', false);
                    }

                    if(dataset.status == 1){
                        $('#editBankModal [name="status"]').prop('checked', true);
                    }else{
                        $('#editBankModal [name="status"]').prop('checked', false);
                    }
                    
                    $('#editBankModal input[name="id"]').val(editId);
                    $('#editBankModal #bankImageEdit').attr('src', dataset.image_url);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        
        $("#editBankForm").on("submit", function (e) {
            e.preventDefault();
            const form = document.getElementById("editBankForm");

            document.querySelector('#updateBank').setAttribute('disabled', 'disabled');
            document.querySelector('#updateBank svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);
            form_data.append('file', $('#editBankForm input[name="photo"]')[0].files[0]);
            axios({
                method: "post",
                url: route("site.settings.banks.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateBank").removeAttribute("disabled");
                    document.querySelector("#updateBank svg").style.cssText = "display: none;";
                    editBankModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Bank data successfully updated.');
                    });
                    
                    setTimeout(function(){
                        succModal.hide();
                    }, 2000);
                }
                bankListTable.init();
            }).catch((error) => {
                document.querySelector("#updateBank").removeAttribute("disabled");
                document.querySelector("#updateBank svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editBankForm .${key}`).addClass('border-danger')
                            $(`#editBankForm  .error-${key}`).html(val)
                        }
                    }else {
                        console.log("error");
                    }
                }
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
                    url: route('site.settings.banks.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('WOW!');
                            $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                        });
                    
                        setTimeout(function(){
                            succModal.hide();
                        }, 2000);
                    }
                    bankListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('site.settings.banks.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('WOW!');
                            $('#successModal .successModalDesc').html('Record Successfully Restored!');
                        });
                    
                        setTimeout(function(){
                            succModal.hide();
                        }, 2000);
                    }
                    bankListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'CHANGESTAT'){
                axios({
                    method: 'post',
                    url: route('site.settings.banks.update.status', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('WOW!');
                            $('#successModal .successModalDesc').html('Record status successfully updated!');
                        });
                    
                        setTimeout(function(){
                            succModal.hide();
                        }, 2000);
                    }
                    bankListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#bankListTable').on('click', '.status_updater', function(){
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
        $('#bankListTable').on('click', '.delete_btn', function(){
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
        $('#bankListTable').on('click', '.restore_btn', function(){
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
    }

    function showPreview(inputId, targetImageId) {
        var src = document.getElementById(inputId);
        var target = document.getElementById(targetImageId);
        var title = document.getElementById('selected_image_title');
        var fr = new FileReader();
        fr.onload = function () {
            target.src = fr.result;
        }
        fr.readAsDataURL(src.files[0]);
    };
})();