import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var HeapesPopulationListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-HEAPESPOP").val() != "" ? $("#query-HEAPESPOP").val() : "";
        let status = $("#status-HEAPESPOP").val() != "" ? $("#status-HEAPESPOP").val() : "";
        let tableContent = new Tabulator("#HeapesPopulationListTable", {
            ajaxURL: route("heapes.population.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editHeapesPopulationModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-HEAPESPOP").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-HEAPESPOP").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-HEAPESPOP").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Funding Length Details",
            });
        });

        $("#tabulator-export-html-HEAPESPOP").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-HEAPESPOP").on("click", function (event) {
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
    if ($("#HeapesPopulationListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'HeapesPopulationListTable'){
                HeapesPopulationListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormHEAPESPOP() {
            HeapesPopulationListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-HEAPESPOP")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormHEAPESPOP();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-HEAPESPOP").on("click", function (event) {
            filterHTMLFormHEAPESPOP();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-HEAPESPOP").on("click", function (event) {
            $("#query-HEAPESPOP").val("");
            $("#status-HEAPESPOP").val("1");
            filterHTMLFormHEAPESPOP();
        });

        const addHeapesPopulationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addHeapesPopulationModal"));
        const editHeapesPopulationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editHeapesPopulationModal"));
        const heapesPopulationImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#heapesPopulationImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addHeapesPopulationModalEl = document.getElementById('addHeapesPopulationModal')
        addHeapesPopulationModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addHeapesPopulationModal .acc__input-error').html('');
            $('#addHeapesPopulationModal .modal-body input:not([type="checkbox"])').val('');

            $('#addHeapesPopulationModal input[name="is_hesa"]').prop('checked', false);
            $('#addHeapesPopulationModal .hesa_code_area').fadeOut('fast', function(){
                $('#addHeapesPopulationModal .hesa_code_area input').val('');
            });
            $('#addHeapesPopulationModal input[name="is_df"]').prop('checked', false);
            $('#addHeapesPopulationModal .df_code_area').fadeOut('fast', function(){
                $('#addHeapesPopulationModal .df_code_area input').val('');
            })
            $('#addHeapesPopulationModal input[name="active"]').prop('checked', true);
        });
        
        const editHeapesPopulationModalEl = document.getElementById('editHeapesPopulationModal')
        editHeapesPopulationModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editHeapesPopulationModal .acc__input-error').html('');
            $('#editHeapesPopulationModal .modal-body input:not([type="checkbox"])').val('');
            $('#editHeapesPopulationModal input[name="id"]').val('0');

            $('#editHeapesPopulationModal input[name="is_hesa"]').prop('checked', false);
            $('#editHeapesPopulationModal .hesa_code_area').fadeOut('fast', function(){
                $('#editHeapesPopulationModal .hesa_code_area input').val('');
            });
            $('#editHeapesPopulationModal input[name="is_df"]').prop('checked', false);
            $('#editHeapesPopulationModal .df_code_area').fadeOut('fast', function(){
                $('#editHeapesPopulationModal .df_code_area input').val('');
            })
            $('#editHeapesPopulationModal input[name="active"]').prop('checked', false);
        });
        
        $('#addHeapesPopulationForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addHeapesPopulationForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addHeapesPopulationForm .hesa_code_area input').val('');
                })
            }else{
                $('#addHeapesPopulationForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addHeapesPopulationForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addHeapesPopulationForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addHeapesPopulationForm .df_code_area').fadeIn('fast', function(){
                    $('#addHeapesPopulationForm .df_code_area input').val('');
                })
            }else{
                $('#addHeapesPopulationForm .df_code_area').fadeOut('fast', function(){
                    $('#addHeapesPopulationForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editHeapesPopulationForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editHeapesPopulationForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editHeapesPopulationForm .hesa_code_area input').val('');
                })
            }else{
                $('#editHeapesPopulationForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editHeapesPopulationForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editHeapesPopulationForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editHeapesPopulationForm .df_code_area').fadeIn('fast', function(){
                    $('#editHeapesPopulationForm .df_code_area input').val('');
                })
            }else{
                $('#editHeapesPopulationForm .df_code_area').fadeOut('fast', function(){
                    $('#editHeapesPopulationForm .df_code_area input').val('');
                })
            }
        })

        $('#addHeapesPopulationForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addHeapesPopulationForm');
        
            document.querySelector('#saveHeapesPopulation').setAttribute('disabled', 'disabled');
            document.querySelector("#saveHeapesPopulation svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('heapes.population.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveHeapesPopulation').removeAttribute('disabled');
                document.querySelector("#saveHeapesPopulation svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addHeapesPopulationModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                HeapesPopulationListTable.init();
            }).catch(error => {
                document.querySelector('#saveHeapesPopulation').removeAttribute('disabled');
                document.querySelector("#saveHeapesPopulation svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addHeapesPopulationForm .${key}`).addClass('border-danger');
                            $(`#addHeapesPopulationForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#HeapesPopulationListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("heapes.population.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editHeapesPopulationModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editHeapesPopulationModal input[name="is_hesa"]').prop('checked', true);
                            $('#editHeapesPopulationModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editHeapesPopulationModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editHeapesPopulationModal input[name="is_hesa"]').prop('checked', false);
                            $('#editHeapesPopulationModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editHeapesPopulationModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editHeapesPopulationModal input[name="is_df"]').prop('checked', true);
                            $('#editHeapesPopulationModal .df_code_area').fadeIn('fast', function(){
                                $('#editHeapesPopulationModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editHeapesPopulationModal input[name="is_df"]').prop('checked', false);
                            $('#editHeapesPopulationModal .df_code_area').fadeOut('fast', function(){
                                $('#editHeapesPopulationModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editHeapesPopulationModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editHeapesPopulationModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editHeapesPopulationModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editHeapesPopulationForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editHeapesPopulationForm input[name="id"]').val();
            const form = document.getElementById("editHeapesPopulationForm");

            document.querySelector('#updateHeapesPopulation').setAttribute('disabled', 'disabled');
            document.querySelector('#updateHeapesPopulation svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("heapes.population.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateHeapesPopulation").removeAttribute("disabled");
                    document.querySelector("#updateHeapesPopulation svg").style.cssText = "display: none;";
                    editHeapesPopulationModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                HeapesPopulationListTable.init();
            }).catch((error) => {
                document.querySelector("#updateHeapesPopulation").removeAttribute("disabled");
                document.querySelector("#updateHeapesPopulation svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editHeapesPopulationForm .${key}`).addClass('border-danger')
                            $(`#editHeapesPopulationForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editHeapesPopulationModal.hide();

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
            if(action == 'DELETEHEAPESPOP'){
                axios({
                    method: 'delete',
                    url: route('heapes.population.destory', recordID),
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
                    HeapesPopulationListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREHEAPESPOP'){
                axios({
                    method: 'post',
                    url: route('heapes.population.restore', recordID),
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
                    HeapesPopulationListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATHEAPESPOP'){
                axios({
                    method: 'post',
                    url: route('heapes.population.update.status', recordID),
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
                    HeapesPopulationListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#HeapesPopulationListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATHEAPESPOP');
            });
        });

        // Delete Course
        $('#HeapesPopulationListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEHEAPESPOP');
            });
        });

        // Restore Course
        $('#HeapesPopulationListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREHEAPESPOP');
            });
        });

        $('#heapesPopulationImportModal').on('click','#saveHeapesPopulation',function(e) {
            e.preventDefault();
            $('#heapesPopulationImportModal .dropzone').get(0).dropzone.processQueue();
            heapesPopulationImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);        
        });
    }
})();