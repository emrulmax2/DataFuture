import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var resultGradeListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-GRDS").val() != "" ? $("#query-GRDS").val() : "";
        let status = $("#status-GRDS").val() != "" ? $("#status-GRDS").val() : "";
        let tableContent = new Tabulator("#resultGradeListTable", {
            ajaxURL: route("result.grade.list"),
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
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Code",
                    field: "code",
                    headerHozAlign: "left",
                },
                {
                    title: "Turnitin Grade",
                    field: "turnitin_grade",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "active",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="form-check form-switch"><input data-id="'+cell.getData().id+'" '+(cell.getData().active == 1 ? 'Checked' : '')+' value="'+cell.getData().active+'" type="checkbox" class="status_updater form-check-input"> </div>';
                    }
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editResGradeModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-GRDS").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-GRDS").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-GRDS").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Result Grade Details",
            });
        });

        $("#tabulator-export-html-GRDS").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-GRDS").on("click", function (event) {
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
    if ($("#resultGradeListTable").length) {
        // Init Table
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'resultGradeListTable'){
                resultGradeListTable.init();
            }
        });


        // Filter function
        function filterHTMLFormRGRDS() {
            resultGradeListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-GRDS")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormRGRDS();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-GRDS").on("click", function (event) {
            filterHTMLFormRGRDS();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-GRDS").on("click", function (event) {
            $("#query-GRDS").val("");
            $("#status-GRDS").val("1");
            filterHTMLFormRGRDS();
        });

        const addResGradeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addResGradeModal"));
        const editResGradeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editResGradeModal"));
        const resultGradeImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#resultGradeImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addResGradeModalEl = document.getElementById('addResGradeModal')
        addResGradeModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addResGradeModal .acc__input-error').html('');
            $('#addResGradeModal .modal-body input:not([type="checkbox"])').val('');

            $('#addResGradeModal input[name="active"]').prop('checked', true);
        });
        
        const editResGradeModalEl = document.getElementById('editResGradeModal')
        editResGradeModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editResGradeModal .acc__input-error').html('');
            $('#editResGradeModal .modal-body input:not([type="checkbox"])').val('');
            $('#editResGradeModal input[name="id"]').val('0');

            
            $('#addResGradeModal input[name="active"]').prop('checked', false);
        });
        
        

        $('#addResGradeForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addResGradeForm');
        
            document.querySelector('#saveResGradeBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#saveResGradeBtn svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('result.grade.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveResGradeBtn').removeAttribute('disabled');
                document.querySelector("#saveResGradeBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addResGradeModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                resultGradeListTable.init();
            }).catch(error => {
                document.querySelector('#saveResGradeBtn').removeAttribute('disabled');
                document.querySelector("#saveResGradeBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addResGradeForm .${key}`).addClass('border-danger');
                            $(`#addResGradeForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#resultGradeListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("result.grade.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editResGradeModal input[name="name"]').val(dataset.name ? dataset.name : '');
                    $('#editResGradeModal input[name="code"]').val(dataset.code ? dataset.code : '');
                    $('#editResGradeModal input[name="turnitin_grade"]').val(dataset.turnitin_grade ? dataset.turnitin_grade : '');
                    
                    $('#editResGradeModal input[name="id"]').val(editId);
                    if(dataset.active == 1){
                        $('#editResGradeModal input[name="active"]').prop('checked', true);
                    }else{
                        $('#editResGradeModal input[name="active"]').prop('checked', false);
                    }
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        // Update Course Data
        $("#editResGradeForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editResGradeForm input[name="id"]').val();
            const form = document.getElementById("editResGradeForm");

            document.querySelector('#updateResGradeBtn').setAttribute('disabled', 'disabled');
            document.querySelector('#updateResGradeBtn svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("result.grade.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateResGradeBtn").removeAttribute("disabled");
                    document.querySelector("#updateResGradeBtn svg").style.cssText = "display: none;";
                    editResGradeModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                resultGradeListTable.init();
            }).catch((error) => {
                document.querySelector("#updateResGradeBtn").removeAttribute("disabled");
                document.querySelector("#updateResGradeBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editResGradeForm .${key}`).addClass('border-danger')
                            $(`#editResGradeForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editResGradeModal.hide();

                        let message = error.response.statusText;
                        succModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModal").html("Oops!");
                            $("#successModal .successModal").html('No data change found!');
                        });
                    } else {
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
            if(action == 'DELETERGRDS'){
                axios({
                    method: 'delete',
                    url: route('result.grade.destory', recordID),
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
                    resultGradeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORERGRDS'){
                axios({
                    method: 'post',
                    url: route('result.grade.restore', recordID),
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
                    resultGradeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATRGRDS'){
                axios({
                    method: 'post',
                    url: route('result.grade.update.status', recordID),
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
                    }
                    resultGradeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#resultGradeListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATRGRDS');
            });
        });

        // Delete Course
        $('#resultGradeListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETERGRDS');
            });
        });

        // Restore Course
        $('#resultGradeListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORERGRDS');
            });
        });

        $('#resultGradeImportModal').on('click','#saveResGradeBtnrelation',function(e) {
            e.preventDefault();
            $('#resultGradeImportModal .dropzone').get(0).dropzone.processQueue();
            resultGradeImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);          
        });
    }
})();