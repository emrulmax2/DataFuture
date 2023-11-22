import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var ELearningActivityList = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : 1;
        let tableContent = new Tabulator("#ELearningActivityList", {
            ajaxURL: route("elearning.list"),
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
                    title: "#",
                    field: "id",
                    headerSort:false,
                    width: "50",
                },
                {
                    title: "Label",
                    field: "name",
                    headerHozAlign: "left",
                    
                    width: "250",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div class="flex lg:justify-start items-center">';
                            html += '<div class="intro-x w-10 h-10 image-fit mr-3">';
                                html += '<img alt="'+cell.getData().name+'" class="rounded-full" src="'+cell.getData().logo_url+'">';
                            html += '</div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().name+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Category",
                    field: "category",
                    headerHozAlign: "left",
                    
                    width: "150",
                },
                {
                    title: "Repeat Weekly",
                    field: "has_week",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return (cell.getData().has_week == 1 ? 'Yes' : 'No');
                    }
                },
                {
                    title: "Mandatory",
                    field: "is_mandatory",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return (cell.getData().is_mandatory == 1 ? 'Yes' : 'No');
                    }
                },
                
                {
                    title: "Reminder",
                    field: "days_reminder",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return (cell.getData().days_reminder > 1 ? cell.getData().days_reminder+' Days' : cell.getData().days_reminder+' Day');
                    }
                    
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
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "120",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editELearningActivityModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                sheetName: "Users Details",
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
    if ($("#ELearningActivityList").length) {
        ELearningActivityList.init();

        // Filter function
        function filterTitleHTMLForm() {
            ELearningActivityList.init();
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
            filterTitleHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("1");
            filterTitleHTMLForm();
        });
    }

    const addELearningActivityModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addELearningActivityModal"));
    const editELearningActivityModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editELearningActivityModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    const addELearningActivityModalEl = document.getElementById('addELearningActivityModal')
    addELearningActivityModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addELearningActivityModal .acc__input-error').html('');
        $('#addELearningActivityModal .modal-body select').val('');
        $('#addELearningActivityModal input[name="has_week"]').prop('checked', false);
        $('#addELearningActivityModal input[name="active"]').prop('checked', true);
        $('#addELearningActivityModal .modal-body input[type="file"]').val('');

        var placeholder = $('#addELearningActivityModal .userImageAdd').attr('data-placeholder');
        $('#addELearningActivityModal .userImageAdd').attr('src', placeholder);
    });
    const editELearningActivityModalEl = document.getElementById('editELearningActivityModal')
    editELearningActivityModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editELearningActivityModal .acc__input-error').html('');
        $('#editELearningActivityModal .modal-body select').val('');
        $('#editELearningActivityModal input[name="has_week"]').prop('checked', false);
        $('#editELearningActivityModal input[name="active"]').prop('checked', false);
        $('#editELearningActivityModal .modal-body input[type="file"]').val('');

        var placeholder = $('#editELearningActivityModal .userImageEdit').attr('data-placeholder');
        $('#editELearningActivityModal .userImageEdit').attr('src', placeholder);
    });

    $('#addELearningActivityForm').on('change', '#userPhotoAdd', function(){
        showPreview('userPhotoAdd', 'userImageAdd')
    })
    $('#editELearningActivityForm').on('change', '#userPhotoEdit', function(){
        showPreview('userPhotoEdit', 'userImageEdit')
    })

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

    $('#addELearningActivityForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addELearningActivityForm');
    
        document.querySelector('#saveSettings').setAttribute('disabled', 'disabled');
        document.querySelector("#saveSettings svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#addELearningActivityForm input[name="logo"]')[0].files[0]); 
        axios({
            method: "post",
            url: route('elearning.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveSettings').removeAttribute('disabled');
            document.querySelector("#saveSettings svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addELearningActivityModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('E-Learning activity settings successfully added.');
                });     
            }
            ELearningActivityList.init();
        }).catch(error => {
            document.querySelector('#saveSettings').removeAttribute('disabled');
            document.querySelector("#saveSettings svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addELearningActivityForm .${key}`).addClass('border-danger');
                        $(`#addELearningActivityForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $("#ELearningActivityList").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "post",
            url: route("elearning.edit"),
            data: {editid : editId},
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                $('#editELearningActivityModal [name="category"]').val(dataset.category ? dataset.category : '');
                
                if(dataset.has_week == 1){
                    $('#editELearningActivityModal input[name="has_week"]').prop('checked', true);
                }else{
                    $('#editELearningActivityModal input[name="has_week"]').prop('checked', false);
                }

                $('#editELearningActivityModal input[name="id"]').val(editId);
                $('#editELearningActivityModal #userImageEdit').attr('src', dataset.logoUrl).attr('alt', dataset.category);

                if(dataset.active == 1){
                    $('#editELearningActivityModal input[name="active"]').prop('checked', true);
                }else{
                    $('#editELearningActivityModal input[name="active"]').prop('checked', false);
                }

                document.querySelector('#updateSettings').removeAttribute('disabled');
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    
    $("#editELearningActivityForm").on("submit", function (e) {
        e.preventDefault();
        const form = document.getElementById('editELearningActivityForm');
    
        document.querySelector('#updateSettings').setAttribute('disabled', 'disabled');
        document.querySelector("#updateSettings svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#editELearningActivityForm input[name="logo"]')[0].files[0]); 
        axios({
            method: "post",
            url: route('elearning.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateSettings').removeAttribute('disabled');
            document.querySelector("#updateSettings svg").style.cssText = "display: none;";

            if (response.status == 200) {
                editELearningActivityModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('E-Learning activity settings successfully updated.');
                });     
            }
            ELearningActivityList.init();
        }).catch(error => {
            document.querySelector('#updateSettings').removeAttribute('disabled');
            document.querySelector("#updateSettings svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editELearningActivityForm .${key}`).addClass('border-danger');
                        $(`#editELearningActivityForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
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
                url: route('elearning.destory', recordID),
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
                ELearningActivityList.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('elearning.restore', recordID),
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
                ELearningActivityList.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'CHANGESTAT'){
            axios({
                method: 'post',
                url: route('elearning.update.status', recordID),
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
                ELearningActivityList.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

    // Delete Course
    $('#ELearningActivityList').on('click', '.status_updater', function(){
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
    $('#ELearningActivityList').on('click', '.delete_btn', function(){
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
    $('#ELearningActivityList').on('click', '.restore_btn', function(){
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