import xlsx from "xlsx";
import { createElement, createIcons, icons,Minus,Plus } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var table = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        const minusIcon = createElement(Minus)
        minusIcon.setAttribute('stroke-width', '1.5')
        
        const plusIcon = createElement(Plus)
        plusIcon.setAttribute('stroke-width', '1.5')

        let tableContent = new Tabulator("#awardingbodyTableId", {
            ajaxURL: route("internal-link.list"),
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
            dataTree:true,
            dataTreeStartExpanded:true,
            dataTreeCollapseElement:minusIcon,
            dataTreeExpandElement:plusIcon,
            
            columns: [
                {
                    title: "",
                    field: "",
                    width: "80",
                    headerSort:false,
                   
                },
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
                    title: "Image",
                    field: "image",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {    
                        var html = '<div class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-5 inline-block">';
                                    html += '<img alt="'+cell.getData().name+'" class="rounded-full shadow" src="'+cell.getData().image+'">';
                                html += '</div>';
                                // html += '<div class="inline-block relative" style="top: -5px;">';
                                //     html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().first_name+'</div>';
                                //     html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+(cell.getData().ejt_name != '' ? cell.getData().ejt_name : 'Unknown')+'</div>';
                                // html += '</div>';
                            html += '</div>';
                        return html;
                    }, 
                },
                {
                    title: "Parent Category",
                    field: "parent_id",
                    headerHozAlign: "left",
                },
                {
                    title: "Link",
                    field: "link",
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                sheetName: "Awarding Body Details",
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
    if ($("#awardingbodyTableId").length) {
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
        const uploadEmployeeDocumentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#uploadEmployeeDocumentModal"));
        
        const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

        

        let confModalDelTitle = 'Are you sure?';

        const addModalEl = document.getElementById('uploadEmployeeDocumentModal')
        addModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#uploadEmployeeDocumentModal .acc__input-error').html('');
            $('#uploadEmployeeDocumentModal input').val('');
        });
        
        const editModalEl = document.getElementById('editModal')
        editModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editModal .acc__input-error').html('');
            $('#editModal input').val('');
            $('#editModal input[name="id"]').val('0');
        });

        $('#addForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addForm .hesa_code_area').fadeIn('fast', function(){
                    $('.hesa_code_area input').val('');
                })
            }else{
                $('#addForm .hesa_code_area').fadeOut('fast', function(){
                    $('.hesa_code_area input').val('');
                })
            }
        })
        
        $('#addForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addForm .df_code_area').fadeIn('fast', function(){
                    $('.df_code_area input').val('');
                })
            }else{
                $('#addForm .df_code_area').fadeOut('fast', function(){
                    $('.df_code_area input').val('');
                })
            }
        })

        $('#editForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editForm .hesa_code_area').fadeIn('fast', function(){
                    $('.hesa_code_area input').val('');
                })
            }else{
                $('#editForm .hesa_code_area').fadeOut('fast', function(){
                    $('.hesa_code_area input').val('');
                })
            }
        })
        
        $('#editForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editForm .df_code_area').fadeIn('fast', function(){
                    $('.df_code_area input').val('');
                })
            }else{
                $('#editForm .df_code_area').fadeOut('fast', function(){
                    $('.df_code_area input').val('');
                })
            }
        })
        $('#uploadEmployeeDocumentModal [name="name_status"]').on('keyup', function(){
            $('#uploadEmployeeDocumentModal [name="name"]').val($(this).val());
        })
        $('#uploadEmployeeDocumentModal [name="link_status"]').on('keyup', function(){
            $('#uploadEmployeeDocumentModal [name="link"]').val($(this).val());
        })

        $('#uploadEmployeeDocumentModal [name="parent_category"]').on('change', function(){
            $('#uploadEmployeeDocumentModal [name="parent_id"]').val($(this).val());
        })

        const uploadEmployeeDocumentModalEl = document.getElementById('uploadEmployeeDocumentModal')
        
    /* Start Dropzone */
    if($("#uploadDocumentForm").length > 0){
        
        let dzError = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.uploadDocumentForm = {
            autoProcessQueue: false,
            maxFiles: 10,
            maxFilesize: 20,
            parallelUploads: 10,
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.svg",
            addRemoveLinks: true,
            thumbnailWidth: 100,
            thumbnailHeight: 100,
        };

        let options = {
            accept: (file, done) => {
                console.log("Uploaded");
                done();
            },
        };


        var drzn1 = new Dropzone('#uploadDocumentForm', options);

        drzn1.on("maxfilesexceeded", (file) => {
            $('#uploadEmployeeDocumentModal .modal-content .uploadError').remove();
            $('#uploadEmployeeDocumentModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn1.removeFile(file);
            setTimeout(function(){
                $('#uploadEmployeeDocumentModal .modal-content .uploadError').remove();
            }, 2000)
        });

        drzn1.on("error", function(file, response){
            dzError = true;
        });

        drzn1.on("success", function(file, response){
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn1.on("complete", function(file) {
            //drzn1.removeFile(file);
        }); 

        drzn1.on('queuecomplete', function(){
            $('#uploadEmpDocBtn').removeAttr('disabled');
            document.querySelector("#uploadEmpDocBtn svg").style.cssText ="display: none;";

            uploadEmployeeDocumentModal.hide();
            if(!dzError){
                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Employee document successfully uploaded.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                
                setTimeout(function(){
                    succModal.hide();
                    window.location.reload();
                }, 2000);
            }else{
                warningModal.show();
                document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModal .warningModalTitle").html("Error Found!" );
                    $("#warningModal .warningModalDesc").html('Something went wrong. Please try later or contact administrator.');
                    $("#warningModal .warningCloser").attr('data-action', 'DISMISS');
                });
                setTimeout(function(){
                    warningModal.hide();
                    //window.location.reload();
                }, 2000);
            }
        })

        $('#uploadEmpDocBtn').on('click', function(e){
            e.preventDefault();
            document.querySelector('#uploadEmpDocBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#uploadEmpDocBtn svg").style.cssText ="display: inline-block;";
            
            if($('#uploadEmployeeDocumentModal [name="name_status"]').length > 0){
                
                    $('#uploadEmployeeDocumentModal [name="name"]').val($('#uploadEmployeeDocumentModal [name="name_status"]').val());
                    $('#uploadEmployeeDocumentModal [name="link"]').val($('#uploadEmployeeDocumentModal [name="link_status"]').val());
                    $('#uploadEmployeeDocumentModal [name="parent_id"]').val($('#uploadEmployeeDocumentModal [name="parent_category"]').val());

                drzn1.processQueue();
            }else{
                $('#uploadEmployeeDocumentModal .modal-content .uploadError').remove();
                $('#uploadEmployeeDocumentModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select the hard copy check status.</div>');
                
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#uploadEmployeeDocumentModal .modal-content .uploadError').remove();
                    document.querySelector('#uploadEmpDocBtn').removeAttribute('disabled', 'disabled');
                    document.querySelector("#uploadEmpDocBtn svg").style.cssText ="display: none;";
                }, 2000)
            }
            
        });
    }
    /* End Dropzone */
        // $('#addForm').on('submit', function(e){
        //     const addModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));
        //     e.preventDefault();
        //     const form = document.getElementById('addForm');
        
        //     document.querySelector('#save').setAttribute('disabled', 'disabled');
        //     document.querySelector("#save svg").style.cssText ="display: inline-block;";

        //     let form_data = new FormData(form);
        //     axios({
        //         method: "post",
        //         url: route('awardingbody.store'),
        //         data: form_data,
        //         headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        //     }).then(response => {
        //         document.querySelector('#save').removeAttribute('disabled');
        //         document.querySelector("#save svg").style.cssText = "display: none;";
                
        //         if (response.status == 200) {
        //             document.querySelector('#save').removeAttribute('disabled');
        //             document.querySelector("#save svg").style.cssText = "display: none;";
        //             $('#addForm #name').val('');
        //             addModal.hide();
        //             succModal.show();
        //             document.getElementById("successModal")
        //                 .addEventListener("shown.tw.modal", function (event) {
        //                     $("#successModal .successModalTitle").html(
        //                         "Success!"
        //                     );
        //                     $("#successModal .successModalDesc").html('Awarding body successfully inserted');
        //                 });                
                        
        //         }
        //         table.init();
        //     }).catch(error => {
        //         document.querySelector('#save').removeAttribute('disabled');
        //         document.querySelector("#save svg").style.cssText = "display: none;";
        //         if (error.response) {
        //             if (error.response.status == 422) {
        //                 for (const [key, val] of Object.entries(error.response.data.errors)) {
        //                     $(`#addForm .${key}`).addClass('border-danger')
        //                     $(`#addForm  .error-${key}`).html(val)
        //                 }
        //                 $('#addForm #name').val('');
        //             } else {
        //                 console.log('error');
        //             }
        //         }
        //     });
        // });

        $("#awardingbodyTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("awardingbody.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editModal input[name="name"]').val(dataset.name ? dataset.name : '');
                    
                    if(dataset.is_hesa == 1){
                        document.querySelector('#editModal #is_hesa').checked = true;
                        $('#editModal .hesa_code_area').fadeIn(500, function () {
                            $('#editModal input[name="hesa_code"]').val(dataset.hesa_code ? dataset.hesa_code : '');
                        });
                    }else{
                        document.querySelector('#editModal #is_hesa').checked = false;
                        $('#editModal .hesa_code_area').fadeOut(500, function () {
                            $('#editModal input[name="hesa_code"]').val('');
                        });
                    }
                    
                    if(dataset.is_df == 1){
                        document.querySelector('#editModal #is_df').checked = true;
                        $('#editModal .df_code_area').fadeIn(500, function () {
                            $('#editModal input[name="df_code"]').val(dataset.df_code ? dataset.df_code : '');
                        });
                    }else{
                        document.querySelector('#editModal #is_df').checked = false;
                        $('#editModal .df_code_area').fadeOut(500, function () {
                            $('#editModal input[name="df_code"]').val('');
                        });
                    }

                    $('#editModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        // Update Course Data
        $("#editForm").on("submit", function (e) {
            let editId = $('#editModal input[name="id"]').val();
            const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
            const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

            e.preventDefault();
            const form = document.getElementById("editForm");

            document.querySelector('#update').setAttribute('disabled', 'disabled');
            document.querySelector('#update svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("awardingbody.update", editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#update").removeAttribute("disabled");
                    document.querySelector("#update svg").style.cssText = "display: none;";
                    editModal.hide();

                    succModal.show();
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html(
                                "Success!"
                            );
                            $("#successModal .successModalDesc").html('Awarding body successfully updated');
                        });
                }
                table.init();
            }).catch((error) => {
                document.querySelector("#update").removeAttribute("disabled");
                document.querySelector("#update svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editForm .${key}`).addClass('border-danger')
                            $(`#editForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editModal.hide();

                        let message = error.response.statusText;
                        succModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html("No Data Change!");
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
                    url: route('internal-link.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Awarding body successfully deleted!');
                        });
                    }
                    table.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('internal-link.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Awarding body successfully restored!');
                        });
                    }
                    table.init();
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
                $('#confirmModal .confModDesc').html('Want to delete this Awarding Body from applicant list? Please click on agree to continue.');
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
                $('#confirmModal .confModDesc').html('Want to restore this Awarding Body from the trash? Please click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
        });
    }
})();