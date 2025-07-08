import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var studyModeListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-STMOD").val() != "" ? $("#query-STMOD").val() : "";
        let status = $("#status-STMOD").val() != "" ? $("#status-STMOD").val() : "";
        let tableContent = new Tabulator("#studyModeListTable", {
            ajaxURL: route("study.mode.list"),
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
                    title: "Hesa Code",
                    field: "hesa_code",
                    headerHozAlign: "left",
                },
                {
                    title: "DF Code",
                    field: "df_code",
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editStudyModeModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-STMOD").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-STMOD").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-STMOD").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Title Details",
            });
        });

        $("#tabulator-export-html-STMOD").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-STMOD").on("click", function (event) {
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
    if ($("#studyModeListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'studyModeListTable'){
                studyModeListTable.init();
            }
        });
        

        // Filter function
        function filterTitleHTMLForm() {
            studyModeListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-STMOD")[0].addEventListener(
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
        $("#tabulator-html-filter-go-STMOD").on("click", function (event) {
            filterTitleHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-STMOD").on("click", function (event) {
            $("#query-STMOD").val("");
            $("#status-STMOD").val("1");
            filterTitleHTMLForm();
        });

        const addStudyModeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addStudyModeModal"));
        const editStudyModeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editStudyModeModal"));
        const studyModeImportModal = tailwind.Modal.getOrCreateInstance("#studyModeImportModal");
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addStudyModeModalEl = document.getElementById('addStudyModeModal')
        addStudyModeModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addStudyModeModal .acc__input-error').html('');
            $('#addStudyModeModal .modal-body input:not([type="checkbox"])').val('');

            $('#addStudyModeModal input[name="is_hesa"]').prop('checked', false);
            $('#addStudyModeModal .hesa_code_area').fadeOut('fast', function(){
                $('#addStudyModeModal .hesa_code_area input').val('');
            });
            $('#addStudyModeModal input[name="is_df"]').prop('checked', false);
            $('#addStudyModeModal .df_code_area').fadeOut('fast', function(){
                $('#addStudyModeModal .df_code_area input').val('');
            });
            $('#addStudyModeModal input[name="active"]').prop('checked', true);
        });
        
        const editStudyModeModalEl = document.getElementById('editStudyModeModal')
        editStudyModeModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editStudyModeModal .acc__input-error').html('');
            $('#editStudyModeModal .modal-body input:not([type="checkbox"])').val('');
            $('#editStudyModeModal input[name="id"]').val('0');

            $('#editStudyModeModal input[name="is_hesa"]').prop('checked', false);
            $('#editStudyModeModal .hesa_code_area').fadeOut('fast', function(){
                $('#editStudyModeModal .hesa_code_area input').val('');
            });
            $('#editStudyModeModal input[name="is_df"]').prop('checked', false);
            $('#editStudyModeModal .df_code_area').fadeOut('fast', function(){
                $('#editStudyModeModal .df_code_area input').val('');
            })
            $('#editStudyModeModal input[name="active"]').prop('checked', false);
        });
        
        $('#addStudyModeForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addStudyModeForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addStudyModeForm .hesa_code_area input').val('');
                })
            }else{
                $('#addStudyModeForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addStudyModeForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addStudyModeForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addStudyModeForm .df_code_area').fadeIn('fast', function(){
                    $('#addStudyModeForm .df_code_area input').val('');
                })
            }else{
                $('#addStudyModeForm .df_code_area').fadeOut('fast', function(){
                    $('#addStudyModeForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editStudyModeForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editStudyModeForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editStudyModeForm .hesa_code_area input').val('');
                })
            }else{
                $('#editStudyModeForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editStudyModeForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editStudyModeForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editStudyModeForm .df_code_area').fadeIn('fast', function(){
                    $('#editStudyModeForm .df_code_area input').val('');
                })
            }else{
                $('#editStudyModeForm .df_code_area').fadeOut('fast', function(){
                    $('#editStudyModeForm .df_code_area input').val('');
                })
            }
        })

        $('#addStudyModeForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addStudyModeForm');
        
            document.querySelector('#saveStudyMode').setAttribute('disabled', 'disabled');
            document.querySelector("#saveStudyMode svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('study.mode.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveStudyMode').removeAttribute('disabled');
                document.querySelector("#saveStudyMode svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addStudyModeModal.hide();

                    succModal.show();
                    studyModeListTable.init();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                
            }).catch(error => {
                studyModeListTable.init();
                document.querySelector('#saveStudyMode').removeAttribute('disabled');
                document.querySelector("#saveStudyMode svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addStudyModeForm .${key}`).addClass('border-danger');
                            $(`#addStudyModeForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#studyModeListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("study.mode.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editStudyModeModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editStudyModeModal input[name="is_hesa"]').prop('checked', true);
                            $('#editStudyModeModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editStudyModeModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editStudyModeModal input[name="is_hesa"]').prop('checked', false);
                            $('#editStudyModeModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editStudyModeModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editStudyModeModal input[name="is_df"]').prop('checked', true);
                            $('#editStudyModeModal .df_code_area').fadeIn('fast', function(){
                                $('#editStudyModeModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editStudyModeModal input[name="is_df"]').prop('checked', false);
                            $('#editStudyModeModal .df_code_area').fadeOut('fast', function(){
                                $('#editStudyModeModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editStudyModeModal input[name="id"]').val(editId);

                        if(dataset.active == 1){
                            $('#editStudyModeModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editStudyModeModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editStudyModeForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editStudyModeForm input[name="id"]').val();
            const form = document.getElementById("editStudyModeForm");

            document.querySelector('#updateStudyMode').setAttribute('disabled', 'disabled');
            document.querySelector('#updateStudyMode svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("study.mode.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateStudyMode").removeAttribute("disabled");
                    document.querySelector("#updateStudyMode svg").style.cssText = "display: none;";
                    editStudyModeModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                studyModeListTable.init();
            }).catch((error) => {
                document.querySelector("#updateStudyMode").removeAttribute("disabled");
                document.querySelector("#updateStudyMode svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editStudyModeForm .${key}`).addClass('border-danger')
                            $(`#editStudyModeForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editStudyModeModal.hide();

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
            if(action == 'DELETESTMOD'){
                axios({
                    method: 'delete',
                    url: route('study.mode.destory', recordID),
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
                    studyModeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORESTMOD'){
                axios({
                    method: 'post',
                    url: route('study.mode.restore', recordID),
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
                    studyModeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'CHANGESTATSTMOD'){
                axios({
                    method: 'post',
                    url: route('study.mode.update.status', recordID),
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
                    studyModeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#studyModeListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATSTMOD');
            });
        });

        // Delete Course
        $('#studyModeListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETESTMOD');
            });
        });

        // Restore Course
        $('#studyModeListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORESTMOD');
            });
        });

        $('#studyModeImportModal').on('click','#saveStudyMode',function(e) {
            e.preventDefault();
            $('#studyModeImportModal .dropzone').get(0).dropzone.processQueue();
            studyModeImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);          
        });
    }
})();