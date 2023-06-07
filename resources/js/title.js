import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var titleListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-Title").val() != "" ? $("#query-Title").val() : "";
        let status = $("#status-Title").val() != "" ? $("#status-Title").val() : "";
        let tableContent = new Tabulator("#titleListTable", {
            ajaxURL: route("titles.list"),
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
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="edit-3" class="w-4 h-4"></i></a>';
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
    if ($("#titleListTable").length) {
        // Init Table
        titleListTable.init();

        // Filter function
        function filterHTMLForm() {
            titleListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-Title")[0].addEventListener(
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
        $("#tabulator-html-filter-go-Title").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-Title").on("click", function (event) {
            $("#query-Title").val("");
            $("#status-Title").val("1");
            filterHTMLForm();
        });

        const addTitleModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addTitleModal"));
        const editTitleModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editTitleModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal-TTL"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal-TTL"));
        let confModalDelTitle = 'Are you sure?';

        const addTitleModalEl = document.getElementById('addTitleModal')
        addTitleModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addTitleModal .acc__input-error').html('');
            $('#addTitleModal .modal-body input').val('');
        });
        
        const editTitleModalEl = document.getElementById('editTitleModal')
        editTitleModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editTitleModal .acc__input-error').html('');
            $('#editTitleModal .modal-body input').val('');
            $('#editTitleModal input[name="id"]').val('0');
        });
        
        $('#addTitleForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addTitleForm .hesa_code_area').fadeIn('fast', function(){
                    $('.hesa_code_area input').val('');
                })
            }else{
                $('#addTitleForm .hesa_code_area').fadeOut('fast', function(){
                    $('.hesa_code_area input').val('');
                })
            }
        })
        
        $('#addTitleForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addTitleForm .df_code_area').fadeIn('fast', function(){
                    $('.df_code_area input').val('');
                })
            }else{
                $('#addTitleForm .df_code_area').fadeOut('fast', function(){
                    $('.df_code_area input').val('');
                })
            }
        })

        $('#addTitleForm').on('submit', function(e){
            const addModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addTitleForm"));
            e.preventDefault();
            const form = document.getElementById('addTitleForm');
        
            document.querySelector('#saveTitle').setAttribute('disabled', 'disabled');
            document.querySelector("#saveTitle svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('title.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveTitle').removeAttribute('disabled');
                document.querySelector("#saveTitle svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    document.querySelector('#saveTitle').removeAttribute('disabled');
                    document.querySelector("#saveTitle svg").style.cssText = "display: none;";
                    
                    addTitleModal.hide();
                    succModal.show();
                    document.getElementById("successModal-TTL").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal-TTL .successModal-TTLTitle").html( "Congratulations!" );
                            $("#successModal-TTL .successModal-TTLDesc").html('Data Successfully inserted.');
                    });     
                }
                titleListTable.init();
            }).catch(error => {
                document.querySelector('#saveTitle').removeAttribute('disabled');
                document.querySelector("#saveTitle svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addTitleForm .${key}`).addClass('border-danger');
                            $(`#addTitleForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#titleListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("title.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editTitleModal input[name="name"]').val(dataset.name ? dataset.name : '');

                        $('#editTitleModal input[name="id"]').val(editId);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editTitleForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editTitleForm input[name="id"]').val();
            const form = document.getElementById("editTitleForm");

            document.querySelector('#updateTitle').setAttribute('disabled', 'disabled');
            document.querySelector('#updateTitle svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("title.update", editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateTitle").removeAttribute("disabled");
                    document.querySelector("#updateTitle svg").style.cssText = "display: none;";
                    editTitleModal.hide();

                    succModal.show();
                    document.getElementById("successModal-TTL").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal-TTL .successModalTitle-TTL").html("Success!");
                        $("#successModal-TTL .successModalDesc-TTL").html('Data Updated');
                    });
                }
                titleListTable.init();
            }).catch((error) => {
                document.querySelector("#updateTitle").removeAttribute("disabled");
                document.querySelector("#updateTitle svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editTitleForm .${key}`).addClass('border-danger')
                            $(`#editTitleForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editModal.hide();

                        let message = error.response.statusText;
                        succModal.show();
                        document.getElementById("successModal-TTL").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal-TTL .successModal-TTL").html("Oops!");
                            $("#successModal-TTL .successModal-TTL").html('No data change found!');
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
                    url: route('awardingbody.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal-TTL').addEventListener('shown.tw.modal', function(event){
                            $('#successModal-TTL .successModal-TTLTitle').html('Done!');
                            $('#successModal-TTL .successModal-TTLDesc').html('Data Deleted!');
                        });
                    }
                    titleListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('awardingbody.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal-TTL').addEventListener('shown.tw.modal', function(event){
                            $('#successModal-TTL .successModal-TTLTitle').html('Success!');
                            $('#successModal-TTL .successModal-TTLDesc').html('Data Successfully Restored!');
                        });
                    }
                    titleListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#awardingbodyTableId').on('click', '.delete_btn', function(){
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
        $('#awardingbodyTableId').on('click', '.restore_btn', function(){
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
                $('#confirmModal .agreeWith').attr('data-id', '0');
                $('#confirmModal .agreeWith').attr('data-action', 'none');
            });
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record?');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
        });
    }
})();