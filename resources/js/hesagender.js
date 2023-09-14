import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var gendersListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#gendersListTable", {
            ajaxURL: route("gender.list"),
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
                    title: "Title",
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
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editGendersModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="edit-3" class="w-4 h-4"></i></a>';
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
                sheetName: "Gender Details",
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
    if ($("#gendersListTable").length) {
        // Init Table
        gendersListTable.init();

        // Filter function
        function filterHTMLForm() {
            gendersListTable.init();
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

        const addGendersModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addGendersModal"));
        const editGendersModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editGendersModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addGendersModalEl = document.getElementById('addGendersModal')
        addGendersModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addGendersModal .acc__input-error').html('');
            $('#addGendersModal .modal-body input:not([type="checkbox"])').val('');

            $('#addGendersModal input[name="is_hesa"]').prop('checked', false);
            $('#addGendersModal .hesa_code_area').fadeOut('fast', function(){
                $('#addGendersModal .hesa_code_area input').val('');
            });
            $('#addGendersModal input[name="is_df"]').prop('checked', false);
            $('#addGendersModal .df_code_area').fadeOut('fast', function(){
                $('#addGendersModal .df_code_area input').val('');
            })
        });
        
        const editGendersModalEl = document.getElementById('editGendersModal')
        editGendersModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editGendersModal .acc__input-error').html('');
            $('#editGendersModal .modal-body input:not([type="checkbox"])').val('');
            $('#editGendersModal input[name="id"]').val('0');

            $('#editGendersModal input[name="is_hesa"]').prop('checked', false);
            $('#editGendersModal .hesa_code_area').fadeOut('fast', function(){
                $('#editGendersModal .hesa_code_area input').val('');
            });
            $('#editGendersModal input[name="is_df"]').prop('checked', false);
            $('#editGendersModal .df_code_area').fadeOut('fast', function(){
                $('#editGendersModal .df_code_area input').val('');
            })
        });
        
        $('#addGendersForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addGendersForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addGendersForm .hesa_code_area input').val('');
                })
            }else{
                $('#addGendersForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addGendersForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addGendersForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addGendersForm .df_code_area').fadeIn('fast', function(){
                    $('#addGendersForm .df_code_area input').val('');
                })
            }else{
                $('#addGendersForm .df_code_area').fadeOut('fast', function(){
                    $('#addGendersForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editGendersForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editGendersForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editGendersForm .hesa_code_area input').val('');
                })
            }else{
                $('#editGendersForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editGendersForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editGendersForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editGendersForm .df_code_area').fadeIn('fast', function(){
                    $('#editGendersForm .df_code_area input').val('');
                })
            }else{
                $('#editGendersForm .df_code_area').fadeOut('fast', function(){
                    $('#editGendersForm .df_code_area input').val('');
                })
            }
        })

        $('#addGendersForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addGendersForm');
        
            document.querySelector('#saveGenders').setAttribute('disabled', 'disabled');
            document.querySelector("#saveGenders svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('gender.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveGenders').removeAttribute('disabled');
                document.querySelector("#saveGenders svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addGendersModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                gendersListTable.init();
            }).catch(error => {
                document.querySelector('#saveGenders').removeAttribute('disabled');
                document.querySelector("#saveGenders svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addGendersForm .${key}`).addClass('border-danger');
                            $(`#addGendersForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#gendersListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("gender.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editGendersModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editGendersModal input[name="is_hesa"]').prop('checked', true);
                            $('#editGendersModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editGendersModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editGendersModal input[name="is_hesa"]').prop('checked', false);
                            $('#editGendersModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editGendersModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editGendersModal input[name="is_df"]').prop('checked', true);
                            $('#editGendersModal .df_code_area').fadeIn('fast', function(){
                                $('#editGendersModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editGendersModal input[name="is_df"]').prop('checked', false);
                            $('#editGendersModal .df_code_area').fadeOut('fast', function(){
                                $('#editGendersModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editGendersModal input[name="id"]').val(editId);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editGendersForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editGendersForm input[name="id"]').val();
            const form = document.getElementById("editGendersForm");

            document.querySelector('#updateGenders').setAttribute('disabled', 'disabled');
            document.querySelector('#updateGenders svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("gender.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateGenders").removeAttribute("disabled");
                    document.querySelector("#updateGenders svg").style.cssText = "display: none;";
                    editGendersModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                gendersListTable.init();
            }).catch((error) => {
                document.querySelector("#updateGenders").removeAttribute("disabled");
                document.querySelector("#updateGenders svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editGendersForm .${key}`).addClass('border-danger')
                            $(`#editGendersForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editGendersModal.hide();

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
                    url: route('gender.destory', recordID),
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
                    gendersListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('gender.restore', recordID),
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
                    gendersListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#gendersListTable').on('click', '.delete_btn', function(){
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
        $('#gendersListTable').on('click', '.restore_btn', function(){
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