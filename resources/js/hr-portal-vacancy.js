import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var vacancyListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#vacancyListTable", {
            ajaxURL: route("hr.portal.vacancy.list"),
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
                    title: "Title",
                    field: "title",
                    headerHozAlign: "left",
                },
                {
                    title: "Type",
                    field: "type",
                    headerHozAlign: "left",
                },
                {
                    title: "Date",
                    field: "date",
                    headerHozAlign: "left",
                    headerSort: false,
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
                    title: "Created By",
                    field: "created_by",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().created_by+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().created_at+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            if(cell.getData().link != ''){
                                btns +='<a href="'+cell.getData().link+'" target="_blank" class="btn-rounded btn btn-twitter text-white p-0 w-9 h-9 ml-1"><i data-lucide="link" class="w-4 h-4"></i></a>';
                            }
                            if(cell.getData().document_url != ''){
                                btns +='<a href="'+cell.getData().document_url+'" target="_blank" class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="cloud-lightning" class="w-4 h-4"></i></a>';
                            }
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editVacancyModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                sheetName: "Status Details",
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
    /* List Table INIT */
    vacancyListTable.init();

    // Filter function
    function filterHTMLForm() {
        vacancyListTable.init();
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
    /* List Table INIT */


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const addVacancyModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addVacancyModal"));
    const editVacancyModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editVacancyModal"));

    const addVacancyModalEl = document.getElementById('addVacancyModal')
    addVacancyModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addVacancyModal .acc__input-error').html('');
        $('#addVacancyModal .modal-body input:not([type="checkbox"])').val('');
        $('#addVacancyModal .modal-body select').val('');
        $('#addVacancyModal input[name="active"]').prop('checked', true);
        $('#addVacancyModal #addVacanDocumentName').html('');
    });

    const editVacancyModalEl = document.getElementById('editVacancyModal')
    editVacancyModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editVacancyModal .acc__input-error').html('');
        $('#editVacancyModal .modal-body input:not([type="checkbox"])').val('');
        $('#editVacancyModal .modal-body select').val('');
        $('#editVacancyModal input[name="active"]').prop('checked', true);
        $('#editVacancyModal #editVacanDocumentName').html('');
        $('#editVacancyModal input[name="id"]').val('0');
    });

    $('#addVacancyForm #addVacanDocument').on('change', function(){
        var inputs = document.getElementById('addVacanDocument');
        var html = '';
        for (var i = 0; i < inputs.files.length; ++i) {
            var name = inputs.files.item(i).name;
            html += '<div class="mb-1 text-primary font-medium flex justify-start items-center"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>'+name+'</div>';
        }

        $('#addVacancyForm .documentVacanName').fadeIn().html(html);
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    });

    $('#editVacancyForm #editVacanDocument').on('change', function(){
        var inputs = document.getElementById('editVacanDocument');
        var html = '';
        for (var i = 0; i < inputs.files.length; ++i) {
            var name = inputs.files.item(i).name;
            html += '<div class="mb-1 text-primary font-medium flex justify-start items-center"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>'+name+'</div>';
        }

        $('#editVacancyForm .documentVacanName').fadeIn().html(html);
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    });

    $('#addVacancyForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addVacancyForm');
    
        document.querySelector('#addVacancyBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#addVacancyBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#addVacancyForm input[name="document"]')[0].files[0]); 
        axios({
            method: "post",
            url: route('hr.portal.vacancy.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#addVacancyBtn').removeAttribute('disabled');
            document.querySelector("#addVacancyBtn svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addVacancyModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Vacancy successfully created.');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            vacancyListTable.init();
        }).catch(error => {
            document.querySelector('#addVacancyBtn').removeAttribute('disabled');
            document.querySelector("#addVacancyBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addVacancyForm .${key}`).addClass('border-danger');
                        $(`#addVacancyForm  .error-${key}`).html(val);
                    }
                } else if(error.response.status == 304){
                    addVacancyModal.hide();

                    warningModal.show(); 
                    document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html("Error!" );
                        $("#warningModal .warningModalDesc").html(error.response.data.msg);
                    });
                
                    setTimeout(function(){
                        warningModal.hide();
                    }, 2000);
                } else {
                    console.log('error');
                }
            }
        });
    });


    $("#vacancyListTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("hr.portal.vacancy.edit", editId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let row = response.data.row;
                $('#editVacancyModal [name="title"]').val(row.title ? row.title : '');
                $('#editVacancyModal [name="hr_vacancy_type_id"]').val(row.hr_vacancy_type_id ? row.hr_vacancy_type_id : '');
                $('#editVacancyModal [name="link"]').val(row.link ? row.link : '');
                $('#editVacancyModal [name="date"]').val(row.date ? row.date : '');
                $('#editVacancyModal [name="id"]').val(editId);

                if(row.active == 1){
                    $('#editVacancyModal input[name="active"]').prop('checked', true);
                }else{
                    $('#editVacancyModal input[name="active"]').prop('checked', false);
                }
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editVacancyForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editVacancyForm');
    
        document.querySelector('#editVacancyBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#editVacancyBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#editVacancyForm input[name="document"]')[0].files[0]); 
        axios({
            method: "post",
            url: route('hr.portal.vacancy.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#editVacancyBtn').removeAttribute('disabled');
            document.querySelector("#editVacancyBtn svg").style.cssText = "display: none;";

            if (response.status == 200) {
                editVacancyModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Vacancy successfully updated.');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            vacancyListTable.init();
        }).catch(error => {
            document.querySelector('#editVacancyBtn').removeAttribute('disabled');
            document.querySelector("#editVacancyBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editVacancyForm .${key}`).addClass('border-danger');
                        $(`#editVacancyForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    // Update Status
    $('#vacancyListTable').on('click', '.status_updater', function(){
        let $statusBTN = $(this);
        let row_id = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', row_id);
            $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTAT');
        });
    });

    // Delete
    $('#vacancyListTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let row_id = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', row_id);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
        });
    });

    // Restore
    $('#vacancyListTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let row_id = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', row_id);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
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
                url: route('hr.portal.vacancy.destory', recordID),
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
                }
                vacancyListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('hr.portal.vacancy.restore', recordID),
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
                }
                vacancyListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'CHANGESTAT'){
            axios({
                method: 'post',
                url: route('hr.portal.vacancy.update.status', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record status successfully updated!');
                    });
                }
                vacancyListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

})();