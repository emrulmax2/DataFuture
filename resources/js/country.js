import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var settingsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#settingsListTable", {
            ajaxURL: route("countries.list"),
            ajaxParams: { querystr: querystr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [5, 10, 20, 30, 40],
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
                    title: "ISO Code",
                    field: "iso_code",
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
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editSettingsModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="edit-3" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="trash" class="w-4 h-4"></i></button>';
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
                sheetName: "Course Details",
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
    if ($("#settingsListTable").length) {
        // Init Table
        settingsListTable.init();

        // Filter function
        function filterHTMLForm() {
            settingsListTable.init();
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

        const addSettingsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addSettingsModal"));
        const editSettingsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editSettingsModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addSettingsModalEl = document.getElementById('addSettingsModal')
        addSettingsModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addSettingsModal .acc__input-error').html('');
            $('#addSettingsModal .modal-body input:not([type="checkbox"])').val('');

            $('#addSettingsModal input[name="is_hesa"]').prop('checked', false);
            $('#addSettingsModal .hesa_code_area').fadeOut('fast', function(){
                $('#addSettingsModal .hesa_code_area input').val('');
            });
            $('#addSettingsModal input[name="is_df"]').prop('checked', false);
            $('#addSettingsModal .df_code_area').fadeOut('fast', function(){
                $('#addSettingsModal .df_code_area input').val('');
            })
        });
        
        const editSettingsModalEl = document.getElementById('editSettingsModal')
        editSettingsModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editSettingsModal .acc__input-error').html('');
            $('#editSettingsModal .modal-body input:not([type="checkbox"])').val('');
            $('#editSettingsModal input[name="id"]').val('0');

            $('#editSettingsModal input[name="is_hesa"]').prop('checked', false);
            $('#editSettingsModal .hesa_code_area').fadeOut('fast', function(){
                $('#editSettingsModal .hesa_code_area input').val('');
            });
            $('#editSettingsModal input[name="is_df"]').prop('checked', false);
            $('#editSettingsModal .df_code_area').fadeOut('fast', function(){
                $('#editSettingsModal .df_code_area input').val('');
            })
        });
        
        $('#addSettingsForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addSettingsForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addSettingsForm .hesa_code_area input').val('');
                })
            }else{
                $('#addSettingsForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addSettingsForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addSettingsForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addSettingsForm .df_code_area').fadeIn('fast', function(){
                    $('#addSettingsForm .df_code_area input').val('');
                })
            }else{
                $('#addSettingsForm .df_code_area').fadeOut('fast', function(){
                    $('#addSettingsForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editSettingsForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editSettingsForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editSettingsForm .hesa_code_area input').val('');
                })
            }else{
                $('#editSettingsForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editSettingsForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editSettingsForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editSettingsForm .df_code_area').fadeIn('fast', function(){
                    $('#editSettingsForm .df_code_area input').val('');
                })
            }else{
                $('#editSettingsForm .df_code_area').fadeOut('fast', function(){
                    $('#editSettingsForm .df_code_area input').val('');
                })
            }
        })

        $('#addSettingsForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addSettingsForm');
        
            document.querySelector('#saveSettings').setAttribute('disabled', 'disabled');
            document.querySelector("#saveSettings svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('countries.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveSettings').removeAttribute('disabled');
                document.querySelector("#saveSettings svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addSettingsModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                settingsListTable.init();
            }).catch(error => {
                document.querySelector('#saveSettings').removeAttribute('disabled');
                document.querySelector("#saveSettings svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addSettingsForm .${key}`).addClass('border-danger');
                            $(`#addSettingsForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#settingsListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("countries.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editSettingsModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        $('#editSettingsModal input[name="iso_code"]').val(dataset.iso_code ? dataset.iso_code : '');
                        if(dataset.is_hesa == 1){
                            $('#editSettingsModal input[name="is_hesa"]').prop('checked', true);
                            $('#editSettingsModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editSettingsModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editSettingsModal input[name="is_hesa"]').prop('checked', false);
                            $('#editSettingsModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editSettingsModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editSettingsModal input[name="is_df"]').prop('checked', true);
                            $('#editSettingsModal .df_code_area').fadeIn('fast', function(){
                                $('#editSettingsModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editSettingsModal input[name="is_df"]').prop('checked', false);
                            $('#editSettingsModal .df_code_area').fadeOut('fast', function(){
                                $('#editSettingsModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editSettingsModal input[name="id"]').val(editId);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editSettingsForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editSettingsForm input[name="id"]').val();
            const form = document.getElementById("editSettingsForm");

            document.querySelector('#updateSettings').setAttribute('disabled', 'disabled');
            document.querySelector('#updateSettings svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("countries.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateSettings").removeAttribute("disabled");
                    document.querySelector("#updateSettings svg").style.cssText = "display: none;";
                    editSettingsModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                settingsListTable.init();
            }).catch((error) => {
                document.querySelector("#updateSettings").removeAttribute("disabled");
                document.querySelector("#updateSettings svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editSettingsForm .${key}`).addClass('border-danger')
                            $(`#editSettingsForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editSettingsModal.hide();

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
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('countries.destory', recordID),
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
                    settingsListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('countries.restore', recordID),
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
                    settingsListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#settingsListTable').on('click', '.delete_btn', function(){
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
        $('#settingsListTable').on('click', '.restore_btn', function(){
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