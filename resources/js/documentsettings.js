import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var table = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#documentsettingsTableId", {
            ajaxURL: route("documentsettings.list"),
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
                    width: "180",
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Type",
                    field: "type",
                    headerHozAlign: "left",
                },
                {
                    title: "Application",
                    field: "application",
                    headerHozAlign: "left",
                },
                {
                    title: "Admission",
                    field: "admission",
                    headerHozAlign: "left",
                },
                {
                    title: "Registration",
                    field: "registration",
                    headerHozAlign: "left",
                },
                {
                    title: "Live",
                    field: "live",
                    headerHozAlign: "left",
                },
                {
                    title: "Student Profile",
                    field: "student_profile",
                    headerHozAlign: "left",
                },
                {
                    title: "Staff",
                    field: "staff",
                    headerHozAlign: "left",
                },
                {
                    title: "Agent",
                    field: "agent",
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
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '" data-tw-toggle="modal" data-tw-target="#editModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
                sheetName: "Document Settings Details",
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
    if ($("#documentsettingsTableId").length) {
        // Init Table
        table.init();

        // Filter function
        function filterHTMLForm() {
            table.init();
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

        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        let confModalDelTitle = 'Are you sure?';

        const addModalEl = document.getElementById('addModal')
        addModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addModal .acc__input-error').html('');
            $('#addModal input:not([type="radio"]):not([type="checkbox"])').val('');
        });
        
        const editModalEl = document.getElementById('editModal')
        editModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editModal .acc__input-error').html('');
            $('#editModal input:not([type="radio"]):not([type="checkbox"])').val('');
            $('#editModal input[name="id"]').val('0');
        });

        $('#addForm').on('submit', function(e){
            const addModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));
            e.preventDefault();
            const form = document.getElementById('addForm');
        
            document.querySelector('#save').setAttribute('disabled', 'disabled');
            document.querySelector("#save svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('documentsettings.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    document.querySelector('#save').removeAttribute('disabled');
                    document.querySelector("#save svg").style.cssText = "display: none;";
                    $('#addForm #name').val('');
                    addModal.hide();
                    succModal.show();
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html(
                                "Success!"
                            );
                            $("#successModal .successModalDesc").html('Data Inserted');
                        });                
                        
                }
                table.init();
            }).catch(error => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addForm .${key}`).addClass('border-danger')
                            $(`#addForm  .error-${key}`).html(val)
                        }
                        $('#addForm #name').val('');
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#documentsettingsTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("documentsettings.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        
                        if(dataset.type == "optional"){
                            document.querySelector('#editModal #type-optional').checked = true;
                        }else{
                            document.querySelector('#editModal #type-mandatory').checked = true;
                        }
                        
                        if(dataset.application == 1){
                            document.querySelector('#editModal #application').checked = true;
                        }else{
                            document.querySelector('#editModal #application').checked = false;
                        }
                        
                        if(dataset.admission == 1){
                            document.querySelector('#editModal #admission').checked = true;
                        }else{
                            document.querySelector('#editModal #admission').checked = false;
                        }

                        if(dataset.registration == 1){
                            document.querySelector('#editModal #registration').checked = true;
                        }else{
                            document.querySelector('#editModal #registration').checked = false;
                        }

                        if(dataset.live == 1){
                            document.querySelector('#editModal #live').checked = true;
                        }else{
                            document.querySelector('#editModal #live').checked = false;
                        }

                        if(dataset.student_profile == 1){
                            document.querySelector('#editModal #student_profile').checked = true;
                        }else{
                            document.querySelector('#editModal #student_profile').checked = false;
                        }

                        if(dataset.staff == 1){
                            document.querySelector('#editModal #staff').checked = true;
                        }else{
                            document.querySelector('#editModal #staff').checked = false;
                        }
                        
                        if(dataset.agent == 1){
                            document.querySelector('#editModal #agent').checked = true;
                        }else{
                            document.querySelector('#editModal #agent').checked = false;
                        }

                        $('#editModal input[name="id"]').val(editId);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editForm").on("submit", function (e) {
            let editId = $('#editModal input[name="id"]').val();
            const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
            const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

            e.preventDefault();
            const form = document.getElementById("editForm");

            document.querySelector('#update').setAttribute('disabled', 'disabled');
            document.querySelector('#update svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("documentsettings.update", editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        document.querySelector("#update").removeAttribute("disabled");
                        document.querySelector("#update svg").style.cssText = "display: none;";
                        editModal.hide();

                        succModal.show();
                        document.getElementById("successModal")
                            .addEventListener("shown.tw.modal", function (event) {
                                $("#successModal .successModalTitle").html(
                                    "Success!"
                                );
                                $("#successModal .successModalDesc").html('Data Updated');
                            });
                    }
                    table.init();
                })
                .catch((error) => {
                    document
                        .querySelector("#update")
                        .removeAttribute("disabled");
                    document.querySelector("#update svg").style.cssText =
                        "display: none;";
                    if (error.response) {
                        if (error.response.status == 422) {
                            for (const [key, val] of Object.entries(error.response.data.errors)) {
                                $(`#editForm .${key}`).addClass('border-danger')
                                $(`#editForm  .error-${key}`).html(val)
                            }
                        }else if (error.response.status == 304) {
                            editModal.hide();

                            let message = error.response.statusText;
                            succModal.show();
                            document.getElementById("successModal")
                                .addEventListener("shown.tw.modal", function (event) {
                                    $("#successModal .successModalTitle").html(
                                        "No Data Change!"
                                    );
                                    $("#successModal .successModalDesc").html(message);
                                });
                        } else {
                            console.log("error");
                        }
                    }
                });
        });

        // Confirm Modal Action
        $('#confirmModal .agreeWith').on('click', function(){
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
                $('#confirmModal .agreeWith').attr('data-id', '0');
                $('#confirmModal .agreeWith').attr('data-action', 'none');
            });
            
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('documentsettings.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Data Deleted!');
                        });
                    }
                    table.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('documentsettings.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Data Successfully Restored!');
                        });
                    }
                    table.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#documentsettingsTableId').on('click', '.delete_btn', function(){
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
                $('#confirmModal .agreeWith').attr('data-id', '0');
                $('#confirmModal .agreeWith').attr('data-action', 'none');
            });
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record?');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });

        // Restore Course
        $('#documentsettingsTableId').on('click', '.restore_btn', function(){
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
                $('#confirmModal .agreeWith').attr('data-id', '0');
                $('#confirmModal .agreeWith').attr('data-action', 'none');
            });
            let $statusBTN = $(this);
            let dataID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record?');
                $('#confirmModal .agreeWith').attr('data-id', dataID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
        });
    }
})();