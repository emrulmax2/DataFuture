import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var consentPolicyListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#consentPolicyListTable", {
            ajaxURL: route("consent.list"),
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
                    width: "100",
                },
                {
                    title: "Name",
                    field: "name",
                    width: '200',
                    headerHozAlign: "left",
                },
                {
                    title: "Description",
                    field: "description",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        return '<div class="whitespace-normal">'+cell.getData().description+'</div>';
                    }
                },
                {
                    title: "Department",
                    field: "department",
                    width: "250",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        return '<div class="whitespace-normal">'+cell.getData().department+'</div>';
                    }
                },
                {
                    title: "Required",
                    field: "is_required",
                    width: "120",
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editConsentPolicyModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Consent Policy Details",
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
    // Tabulator
    if ($("#consentPolicyListTable").length) {
        // Init Table
        consentPolicyListTable.init();

        // Filter function
        function filterHTMLForm() {
            consentPolicyListTable.init();
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

        const addConsentPolicyModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addConsentPolicyModal"));
        const editConsentPolicyModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editConsentPolicyModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addConsentPolicyModalEl = document.getElementById('addConsentPolicyModal')
        addConsentPolicyModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addConsentPolicyModal .acc__input-error').html('');
            $('#addConsentPolicyModal .modal-body input').val('');
            $('#addConsentPolicyModal .modal-body textarea').val('');
            $('#addConsentPolicyModal .modal-body select[name="is_required"]').val('No');
            $('#addConsentPolicyModal .modal-body select[name="department_id"]').val('');
        });
        
        const editConsentPolicyModalEl = document.getElementById('editConsentPolicyModal')
        editConsentPolicyModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editConsentPolicyModal .acc__input-error').html('');
            $('#editConsentPolicyModal .modal-body input').val('');
            $('#editConsentPolicyModal .modal-body textarea').val('');
            $('#editConsentPolicyModal .modal-body select[name="is_required"]').val('No');
            $('#editConsentPolicyModal .modal-body select[name="department_id"]').val('');
            $('#editConsentPolicyModal input[name="id"]').val('0');
        });
        

        $('#addConsentPolicyForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addConsentPolicyForm');
        
            document.querySelector('#saveCP').setAttribute('disabled', 'disabled');
            document.querySelector("#saveCP svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('consent.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveCP').removeAttribute('disabled');
                document.querySelector("#saveCP svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addConsentPolicyModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Consent Policy Item Successfully inserted.');
                    });     
                }
                consentPolicyListTable.init();
            }).catch(error => {
                document.querySelector('#saveCP').removeAttribute('disabled');
                document.querySelector("#saveCP svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addConsentPolicyForm .${key}`).addClass('border-danger');
                            $(`#addConsentPolicyForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#consentPolicyListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("consent.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editConsentPolicyModal input[name="name"]').val(dataset.name ? dataset.name : '');
                    $('#editConsentPolicyModal textarea[name="description"]').val(dataset.description ? dataset.description : '');
                    $('#editConsentPolicyModal select[name="is_required"]').val(dataset.is_required ? dataset.is_required : '');
                    $('#editConsentPolicyModal select[name="department_id"]').val(dataset.department_id ? dataset.department_id : '');
                    
                    $('#editConsentPolicyModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        // Update Course Data
        $("#editConsentPolicyForm").on("submit", function (e) {
            e.preventDefault();
            const form = document.getElementById("editConsentPolicyForm");

            document.querySelector('#updateCP').setAttribute('disabled', 'disabled');
            document.querySelector('#updateCP svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("consent.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateCP").removeAttribute("disabled");
                    document.querySelector("#updateCP svg").style.cssText = "display: none;";
                    editConsentPolicyModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Consent Policy item data successfully updated.');
                    });
                }
                consentPolicyListTable.init();
            }).catch((error) => {
                document.querySelector("#updateCP").removeAttribute("disabled");
                document.querySelector("#updateCP svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editConsentPolicyForm .${key}`).addClass('border-danger')
                            $(`#editConsentPolicyForm  .error-${key}`).html(val)
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
                    url: route('consent.destory', recordID),
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
                    }
                    consentPolicyListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('consent.restore', recordID),
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
                    }
                    consentPolicyListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#consentPolicyListTable').on('click', '.delete_btn', function(){
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
        $('#consentPolicyListTable').on('click', '.restore_btn', function(){
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
})();