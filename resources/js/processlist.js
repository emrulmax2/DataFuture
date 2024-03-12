import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Dropzone from "dropzone";
 
("use strict");
var table = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#processlistTableId", {
            ajaxURL: route("processlist.list"),
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
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div class="flex lg:justify-start items-center">';
                            html += '<div class="intro-x w-10 h-10 image-fit mr-3">';
                                html += '<img alt="'+cell.getData().name+'" class="rounded-full" src="'+cell.getData().image_url+'">';
                            html += '</div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().name+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Phase",
                    field: "phase",
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
                                '" data-tw-toggle="modal" data-tw-target="#editProcessModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                sheetName: "Process Details",
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
    if ($("#processlistTableId").length) {
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
            $("#status").val("1");
            filterHTMLForm();
        });

        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        let confModalDelTitle = 'Are you sure?';

        const addModalEl = document.getElementById('addProcessModal')
        addModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addProcessModal .acc__input-error').html('');
            $('#addProcessModal input:not([type="radio"])').val('');
            $('#addProcessModal select').val('');
            $('#addProcessModal .autoFeedWrap').fadeOut('fast', function(){
                $('#addProcessModal #auto_feed-no').prop('checked', true);
            });

            var placeholder = $('#addProcessModal .processImageAddShow').attr('data-placeholder');
            $('#addProcessModal .processImageAddShow').attr('src', placeholder);
        });
        
        const editModalEl = document.getElementById('editProcessModal')
        editModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editProcessModal .acc__input-error').html('');
            $('#editProcessModal input:not([type="radio"])').val('');
            $('#editProcessModal select').val('');
            $('#editProcessModal input[name="id"]').val('0');

            $('#editProcessForm .autoFeedWrap').fadeOut('fast', function(){
                $('#editProcessForm #edit_auto_feed-no').prop('checked', true);
            });

            var placeholder = $('#editProcessModal .processImageEditShow').attr('data-placeholder');
            $('#editProcessModal .processImageEditShow').attr('src', placeholder);
        });

        $('#addProcessForm').on('change', '#processImageAdd', function(){
            showPreview('processImageAdd', 'processImageAddShow')
        })

        $('#editProcessForm').on('change', '#processImageEdit', function(){
            showPreview('processImageEdit', 'processImageEditShow')
        })

        $('#addProcessForm [name="phase"]').on('change', function(e){
            var $phase = $(this);
            var phase = $phase.val();

            if(phase == 'Live'){
                $('#addProcessForm .autoFeedWrap').fadeIn('fast', function(){
                    $('#addProcessForm #auto_feed-no').prop('checked', true);
                })
            }else{
                $('#addProcessForm .autoFeedWrap').fadeOut('fast', function(){
                    $('#addProcessForm #auto_feed-no').prop('checked', true);
                })
            }
        })

        $('#editProcessForm [name="phase"]').on('change', function(e){
            var $phase = $(this);
            var phase = $phase.val();

            if(phase == 'Live'){
                $('#editProcessForm .autoFeedWrap').fadeIn('fast', function(){
                    $('#editProcessForm #edit_auto_feed-no').prop('checked', true);
                })
            }else{
                $('#editProcessForm .autoFeedWrap').fadeOut('fast', function(){
                    $('#editProcessForm #edit_auto_feed-no').prop('checked', true);
                })
            }
        })

        $('#addProcessForm').on('submit', function(e){
            e.preventDefault();
            const addModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addProcessModal"));
            const form = document.getElementById('addProcessForm');
        
            document.querySelector('#save').setAttribute('disabled', 'disabled');
            document.querySelector("#save svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            form_data.append('file', $('#addProcessForm input[name="photo"]')[0].files[0]); 
            axios({
                method: "post",
                url: route('processlist.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    document.querySelector('#save').removeAttribute('disabled');
                    document.querySelector("#save svg").style.cssText = "display: none;";
                    $('#addProcessForm input[type="text"]').val('');
                    $('#addProcessForm select[name="phase"]').val('');
                    addModal.hide();
                    succModal.show();
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html("Success!");
                            $("#successModal .successModalDesc").html('Process list item successfully inserted');
                        });                
                        
                }
                table.init();
            }).catch(error => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addProcessForm .${key}`).addClass('border-danger')
                            $(`#addProcessForm  .error-${key}`).html(val)
                        }
                        $('#addProcessForm input[type="text"]').val('');
                        $('#addProcessForm select[name="phase"]').val('');
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#processlistTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("processlist.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        let placeholder = $('#editProcessModal .processImageEditShow').attr('data-placeholder');
                        $('#editProcessModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        $('#editProcessModal select[name="phase"]').val(dataset.phase ? dataset.phase : '');
                        $('#editProcessModal .processImageEditShow').attr('src', dataset.image_url ? dataset.image_url : placeholder);

                        $('#editProcessModal input[name="id"]').val(editId);
                        if(dataset.phase == 'Live'){
                            $('#editProcessForm .autoFeedWrap').fadeIn('fast', function(){
                                if(dataset.auto_feed == 'Yes'){
                                    $('#editProcessForm #edit_auto_feed-yes').prop('checked', true);
                                }else{
                                    $('#editProcessForm #edit_auto_feed-no').prop('checked', true);
                                }
                            })
                        }else{
                            $('#editProcessForm .autoFeedWrap').fadeOut('fast', function(){
                                $('#editProcessForm #edit_auto_feed-no').prop('checked', true);
                            })
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editProcessForm").on("submit", function (e) {
            let editId = $('#editProcessModal input[name="id"]').val();
            const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editProcessModal"));
            const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

            e.preventDefault();
            const form = document.getElementById("editProcessForm");

            document.querySelector('#update').setAttribute('disabled', 'disabled');
            document.querySelector('#update svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);
            form_data.append('file', $('#editProcessForm input[name="photo"]')[0].files[0]); 

            axios({
                method: "post",
                url: route("processlist.update", editId),
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
                                $("#successModal .successModalTitle").html("Success!");
                                $("#successModal .successModalDesc").html('Process List Item Data successfully Updated');
                            });
                    }
                    table.init();
                })
                .catch((error) => {
                    document.querySelector("#update").removeAttribute("disabled");
                    document.querySelector("#update svg").style.cssText = "display: none;";
                    if (error.response) {
                        if (error.response.status == 422) {
                            for (const [key, val] of Object.entries(error.response.data.errors)) {
                                $(`#editProcessForm .${key}`).addClass('border-danger')
                                $(`#editProcessForm  .error-${key}`).html(val)
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
                    url: route('processlist.destory', recordID),
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
                    url: route('processlist.restore', recordID),
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
         $('#processlistTableId').on('click', '.delete_btn', function(){
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
        $('#processlistTableId').on('click', '.restore_btn', function(){
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
    }
})();