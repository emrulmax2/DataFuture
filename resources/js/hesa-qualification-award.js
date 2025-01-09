import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var HesaQualificationAwardListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-HESAQFAW").val() != "" ? $("#query-HESAQFAW").val() : "";
        let status = $("#status-HESAQFAW").val() != "" ? $("#status-HESAQFAW").val() : "";
        let tableContent = new Tabulator("#HesaQualificationAwardListTable", {
            ajaxURL: route("hesa.qualification.award.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editHesaQualificationAwardModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-HESAQFAW").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-HESAQFAW").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-HESAQFAW").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Funding Length Details",
            });
        });

        $("#tabulator-export-html-HESAQFAW").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-HESAQFAW").on("click", function (event) {
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
    if ($("#HesaQualificationAwardListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'HesaQualificationAwardListTable'){
                HesaQualificationAwardListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormHESAQFAW() {
            HesaQualificationAwardListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-HESAQFAW")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormHESAQFAW();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-HESAQFAW").on("click", function (event) {
            filterHTMLFormHESAQFAW();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-HESAQFAW").on("click", function (event) {
            $("#query-HESAQFAW").val("");
            $("#status-HESAQFAW").val("1");
            filterHTMLFormHESAQFAW();
        });

        const addHesaQualificationAwardModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addHesaQualificationAwardModal"));
        const editHesaQualificationAwardModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editHesaQualificationAwardModal"));
        const hesaQualificationAwardImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#hesaQualificationAwardImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addHesaQualificationAwardModalEl = document.getElementById('addHesaQualificationAwardModal')
        addHesaQualificationAwardModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addHesaQualificationAwardModal .acc__input-error').html('');
            $('#addHesaQualificationAwardModal .modal-body input:not([type="checkbox"])').val('');

            $('#addHesaQualificationAwardModal input[name="is_hesa"]').prop('checked', false);
            $('#addHesaQualificationAwardModal .hesa_code_area').fadeOut('fast', function(){
                $('#addHesaQualificationAwardModal .hesa_code_area input').val('');
            });
            $('#addHesaQualificationAwardModal input[name="is_df"]').prop('checked', false);
            $('#addHesaQualificationAwardModal .df_code_area').fadeOut('fast', function(){
                $('#addHesaQualificationAwardModal .df_code_area input').val('');
            })
            $('#addHesaQualificationAwardModal input[name="active"]').prop('checked', true);
        });
        
        const editHesaQualificationAwardModalEl = document.getElementById('editHesaQualificationAwardModal')
        editHesaQualificationAwardModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editHesaQualificationAwardModal .acc__input-error').html('');
            $('#editHesaQualificationAwardModal .modal-body input:not([type="checkbox"])').val('');
            $('#editHesaQualificationAwardModal input[name="id"]').val('0');

            $('#editHesaQualificationAwardModal input[name="is_hesa"]').prop('checked', false);
            $('#editHesaQualificationAwardModal .hesa_code_area').fadeOut('fast', function(){
                $('#editHesaQualificationAwardModal .hesa_code_area input').val('');
            });
            $('#editHesaQualificationAwardModal input[name="is_df"]').prop('checked', false);
            $('#editHesaQualificationAwardModal .df_code_area').fadeOut('fast', function(){
                $('#editHesaQualificationAwardModal .df_code_area input').val('');
            })
            $('#editHesaQualificationAwardModal input[name="active"]').prop('checked', false);
        });
        
        $('#addHesaQualificationAwardForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addHesaQualificationAwardForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addHesaQualificationAwardForm .hesa_code_area input').val('');
                })
            }else{
                $('#addHesaQualificationAwardForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addHesaQualificationAwardForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addHesaQualificationAwardForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addHesaQualificationAwardForm .df_code_area').fadeIn('fast', function(){
                    $('#addHesaQualificationAwardForm .df_code_area input').val('');
                })
            }else{
                $('#addHesaQualificationAwardForm .df_code_area').fadeOut('fast', function(){
                    $('#addHesaQualificationAwardForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editHesaQualificationAwardForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editHesaQualificationAwardForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editHesaQualificationAwardForm .hesa_code_area input').val('');
                })
            }else{
                $('#editHesaQualificationAwardForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editHesaQualificationAwardForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editHesaQualificationAwardForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editHesaQualificationAwardForm .df_code_area').fadeIn('fast', function(){
                    $('#editHesaQualificationAwardForm .df_code_area input').val('');
                })
            }else{
                $('#editHesaQualificationAwardForm .df_code_area').fadeOut('fast', function(){
                    $('#editHesaQualificationAwardForm .df_code_area input').val('');
                })
            }
        })

        $('#addHesaQualificationAwardForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addHesaQualificationAwardForm');
        
            document.querySelector('#saveHesaQualificationAward').setAttribute('disabled', 'disabled');
            document.querySelector("#saveHesaQualificationAward svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('hesa.qualification.award.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveHesaQualificationAward').removeAttribute('disabled');
                document.querySelector("#saveHesaQualificationAward svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addHesaQualificationAwardModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                HesaQualificationAwardListTable.init();
            }).catch(error => {
                document.querySelector('#saveHesaQualificationAward').removeAttribute('disabled');
                document.querySelector("#saveHesaQualificationAward svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addHesaQualificationAwardForm .${key}`).addClass('border-danger');
                            $(`#addHesaQualificationAwardForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#HesaQualificationAwardListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("hesa.qualification.award.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editHesaQualificationAwardModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editHesaQualificationAwardModal input[name="is_hesa"]').prop('checked', true);
                            $('#editHesaQualificationAwardModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editHesaQualificationAwardModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editHesaQualificationAwardModal input[name="is_hesa"]').prop('checked', false);
                            $('#editHesaQualificationAwardModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editHesaQualificationAwardModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editHesaQualificationAwardModal input[name="is_df"]').prop('checked', true);
                            $('#editHesaQualificationAwardModal .df_code_area').fadeIn('fast', function(){
                                $('#editHesaQualificationAwardModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editHesaQualificationAwardModal input[name="is_df"]').prop('checked', false);
                            $('#editHesaQualificationAwardModal .df_code_area').fadeOut('fast', function(){
                                $('#editHesaQualificationAwardModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editHesaQualificationAwardModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editHesaQualificationAwardModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editHesaQualificationAwardModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editHesaQualificationAwardForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editHesaQualificationAwardForm input[name="id"]').val();
            const form = document.getElementById("editHesaQualificationAwardForm");

            document.querySelector('#updateHesaQualificationAward').setAttribute('disabled', 'disabled');
            document.querySelector('#updateHesaQualificationAward svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("hesa.qualification.award.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateHesaQualificationAward").removeAttribute("disabled");
                    document.querySelector("#updateHesaQualificationAward svg").style.cssText = "display: none;";
                    editHesaQualificationAwardModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                HesaQualificationAwardListTable.init();
            }).catch((error) => {
                document.querySelector("#updateHesaQualificationAward").removeAttribute("disabled");
                document.querySelector("#updateHesaQualificationAward svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editHesaQualificationAwardForm .${key}`).addClass('border-danger')
                            $(`#editHesaQualificationAwardForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editHesaQualificationAwardModal.hide();

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
            if(action == 'DELETEHESAQFAW'){
                axios({
                    method: 'delete',
                    url: route('hesa.qualification.award.destory', recordID),
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
                    HesaQualificationAwardListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREHESAQFAW'){
                axios({
                    method: 'post',
                    url: route('hesa.qualification.award.restore', recordID),
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
                    HesaQualificationAwardListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATHESAQFAW'){
                axios({
                    method: 'post',
                    url: route('hesa.qualification.award.update.status', recordID),
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
                    HesaQualificationAwardListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#HesaQualificationAwardListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATHESAQFAW');
            });
        });

        // Delete Course
        $('#HesaQualificationAwardListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEHESAQFAW');
            });
        });

        // Restore Course
        $('#HesaQualificationAwardListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREHESAQFAW');
            });
        });

        $('#hesaQualificationAwardImportModal').on('click','#saveHesaQualificationAward',function(e) {
            e.preventDefault();
            $('#hesaQualificationAwardImportModal .dropzone').get(0).dropzone.processQueue();
            hesaQualificationAwardImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);        
        });
    }
})();