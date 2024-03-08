import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
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
                    width: "80",
                    headerSort:false
                },
                {
                    title: "Name",
                    field: "image",
                    headerHozAlign: "left",
                    width: "180",
                    formatter(cell, formatterParams) {    
                        var html = '<div class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-5 inline-block">';
                                    html += '<img alt="'+cell.getData().name+'" class="rounded-full shadow" src="'+cell.getData().image+'">';
                                html += '</div>';
                                html += '<div class="inline-block relative" style="top: -5px;">';
                                    html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().name+'</div>';
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+((cell.getData().description != null) ? cell.getData().description : 'N/A')+'</div>';
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }, 
                },
                {
                    title: "Active",
                    field: "id",
                    width: "180",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {    
                        var html = '';
                        console.log(cell.getData().active)
                        if(cell.getData().active==1)
                            html += '<span class="btn inline-flex btn-success w-auto px-2 text-white py-0 rounded-0">Active</span>';
                        else
                            html += '<span class="btn inline-flex btn-warning w-auto px-2 text-white py-0 rounded-0">Inactive</span>';

                                
                        return html;
                    },
                },
                
                {
                    title: "Available To",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {    
                        var html = '';
                        if(cell.getData().available_staff==1)
                            html += '<span class="btn inline-flex btn-success w-auto px-2 text-white py-0 rounded-0 my-2 mr-1">Staff</span>';
                        if(cell.getData().available_student==1)
                            html += '<span class="btn inline-flex btn-success w-auto px-2 text-white py-0 rounded-0">Student</span>';
                        if(cell.getData().available_student!=1 && cell.getData().available_staff!=1)
                            html += '<span class="btn inline-flex btn-warning w-auto px-2 text-white py-0 rounded-0">No One Assigned</span>';
                                
                        return html;
                    },
                },
                
                {
                    title: "Link",
                    field: "link",
                    headerHozAlign: "left",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    download: false,
                    
                },
                {
                    title: "Started To End Date",
                    field: "id",
                    headerHozAlign: "left",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    download: false,
                    formatter(cell, formatterParams) {    
                        var html = '';

                            html += '<span class=" inline-flex w-auto px-2  py-0 rounded-0 my-2 mr-1">'+cell.getData().start_date+' - '+cell.getData().end_date+'</span>';
                        
                                
                        return html;
                    },
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
                            btns += '<button data-id="' +cell.getData().id +'" data-name="' +cell.getData().name +'" data-link="' +cell.getData().link +'" data-parent="' +cell.getData().parent_id +'" data-tw-toggle="modal" data-tw-target="#uploadEmployeeDocumentModalEdit" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        const uploadEmployeeDocumentModalEdit = tailwind.Modal.getOrCreateInstance(document.querySelector("#uploadEmployeeDocumentModalEdit"));
        
        const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

        

        let confModalDelTitle = 'Are you sure?';

        // let addEditor;
        // if($("#addEditor").length > 0){
        //     const el = document.getElementById('addEditor');
        //     ClassicEditor.create(el).then(newEditor => {
        //         addEditor = newEditor;
        //     }).catch((error) => {
        //         console.error(error);
        //     });
        // }

        // let editEditor;
        // if($("#editEditor").length > 0){
        //     const el = document.getElementById('editEditor');
        //     ClassicEditor.create(el).then(newEditor => {
        //         editEditor = newEditor;
        //     }).catch((error) => {
        //         console.error(error);
        //     });
        // }
        const addModalEl = document.getElementById('uploadEmployeeDocumentModal')
        addModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#uploadEmployeeDocumentModal .acc__input-error').html('');
            $('#uploadEmployeeDocumentModal input').val('');
            $('#uploadEmployeeDocumentModal select').val('');

            // if($("#addEditor").length > 0){
            //     addEditor.setData('');
            // }
        });

        const editModalEl = document.getElementById('uploadEmployeeDocumentModalEdit')
        editModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#uploadEmployeeDocumentModal .acc__input-error').html('');
            $('#uploadEmployeeDocumentModal input').val('');
            // if($("#editEditor").length > 0){
            //     editEditor.setData('');
            // }
        });
        




        $('#uploadEmployeeDocumentModal [name="name_status"]','#uploadEmployeeDocumentModalEdit [name="name_status"]').on('keyup', function(){
            $('#uploadEmployeeDocumentModal [name="name"]').val($(this).val());
            $('#uploadEmployeeDocumentModalEdit [name="name"]').val($(this).val());
        })
        $('#uploadEmployeeDocumentModal [name="link_status"]','#uploadEmployeeDocumentModalEdit [name="link_status"]').on('keyup', function(){
            $('#uploadEmployeeDocumentModal [name="link"]').val($(this).val());
            $('#uploadEmployeeDocumentModalEdit [name="link"]').val($(this).val());
        })

        $('#uploadEmployeeDocumentModal [name="parent_category"]','#uploadEmployeeDocumentModalEdit [name="parent_category"]').on('change', function(){
            $('#uploadEmployeeDocumentModal [name="parent_id"]').val($(this).val());
            $('#uploadEmployeeDocumentModalEdit [name="parent_id"]').val($(this).val());
        })

        $('#uploadEmployeeDocumentModal [name="available_staff_status"]','#uploadEmployeeDocumentModalEdit [name="available_staff_status"]').on('keyup', function(){
            $('#uploadEmployeeDocumentModal [name="available_staff"]').val($(this).val());
            $('#uploadEmployeeDocumentModalEdit [name="available_staff"]').val($(this).val());
        })

        
        $('#uploadEmployeeDocumentModal [name="available_student_status"]','#uploadEmployeeDocumentModalEdit [name="available_student_status"]').on('keyup', function(){
            $('#uploadEmployeeDocumentModal [name="available_student"]').val($(this).val());
            $('#uploadEmployeeDocumentModalEdit [name="available_student"]').val($(this).val());
        })

        
        $('#uploadEmployeeDocumentModal [name="description_status"]','#uploadEmployeeDocumentModalEdit [name="description_status"]').on('keyup', function(){
            $('#uploadEmployeeDocumentModal [name="description"]').val($(this).val());
            $('#uploadEmployeeDocumentModalEdit [name="description"]').val($(this).val());
        })

        
        $('#uploadEmployeeDocumentModal [name="start_date_status"]','#uploadEmployeeDocumentModalEdit [name="start_date_status"]').on('keyup', function(){
            $('#uploadEmployeeDocumentModal [name="start_date"]').val($(this).val());
            $('#uploadEmployeeDocumentModalEdit [name="start_date"]').val($(this).val());
        })

        
        $('#uploadEmployeeDocumentModal [name="end_date_status"]','#uploadEmployeeDocumentModalEdit [name="end_date_status"]').on('keyup', function(){
            $('#uploadEmployeeDocumentModal [name="end_date"]').val($(this).val());
            $('#uploadEmployeeDocumentModalEdit [name="end_date"]').val($(this).val());
        })

        
        $('#uploadEmployeeDocumentModal [name="active_status"]','#uploadEmployeeDocumentModalEdit [name="active_status"]').on('keyup', function(){
            $('#uploadEmployeeDocumentModal [name="active"]').val($(this).val());
            $('#uploadEmployeeDocumentModalEdit [name="active"]').val($(this).val());
        })

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
            drzn1.removeFile(file);
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
                    // window.location.reload();
                }, 2000);
                table.init();
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
                

                    $('#uploadEmployeeDocumentModal [name="available_staff"]').val($('#uploadEmployeeDocumentModal [name="available_staff_status"]').val());
                    $('#uploadEmployeeDocumentModal [name="available_student"]').val($('#uploadEmployeeDocumentModal [name="available_student_status"]').val());
                    $('#uploadEmployeeDocumentModal [name="description"]').val($('#uploadEmployeeDocumentModal [name="description_status"]').val());
                    $('#uploadEmployeeDocumentModal [name="start_date"]').val($('#uploadEmployeeDocumentModal [name="start_date_status"]').val());
                    $('#uploadEmployeeDocumentModal [name="end_date"]').val($('#uploadEmployeeDocumentModal [name="end_date_status"]').val());
                    $('#uploadEmployeeDocumentModal [name="active"]').val($('#uploadEmployeeDocumentModal [name="active_status"]').val());

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
    if($("#uploadDocumentFormEdit").length > 0){
        
        let dzError = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.uploadDocumentFormEdit = {
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


        var drzn2 = new Dropzone('#uploadDocumentFormEdit', options);

        drzn2.on("maxfilesexceeded", (file) => {
            $('#uploadEmployeeDocumentModalEdit .modal-content .uploadError').remove();
            $('#uploadEmployeeDocumentModalEdit .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn2.removeFile(file);
            setTimeout(function(){
                $('#uploadEmployeeDocumentModalEdit .modal-content .uploadError').remove();
            }, 2000)
        });

        drzn2.on("error", function(file, response){
            dzError = true;
        });

        drzn2.on("success", function(file, response){
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn2.on("complete", function(file) {
            drzn2.removeFile(file);
        }); 

        drzn2.on('queuecomplete', function(){
            $('#uploadEmpDocBtn').removeAttr('disabled');
            document.querySelector("#uploadEmpDocBtn svg").style.cssText ="display: none;";

            uploadEmployeeDocumentModalEdit.hide();
            if(!dzError){
                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Employee document successfully uploaded.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });      
                table.init();
                setTimeout(function(){
                    succModal.hide();
                    // window.location.reload();
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


        $('#uploadEmpDocBtnEdit').on('click', function(e){
            e.preventDefault();
            document.querySelector('#uploadEmpDocBtnEdit').setAttribute('disabled', 'disabled');
            document.querySelector("#uploadEmpDocBtnEdit svg").style.cssText ="display: inline-block;";
            
            if($('#uploadEmployeeDocumentModalEdit [name="name_status"]').length > 0){
                
                    $('#uploadEmployeeDocumentModalEdit [name="name"]').val($('#uploadEmployeeDocumentModalEdit [name="name_status"]').val());
                    $('#uploadEmployeeDocumentModalEdit [name="link"]').val($('#uploadEmployeeDocumentModalEdit [name="link_status"]').val());
                    $('#uploadEmployeeDocumentModalEdit [name="parent_id"]').val($('#uploadEmployeeDocumentModalEdit [name="parent_category"]').val());
                    
                    $('#uploadEmployeeDocumentModalEdit [name="available_staff"]').val($('#uploadEmployeeDocumentModalEdit [name="available_staff_status"]').val());
                    $('#uploadEmployeeDocumentModalEdit [name="available_student"]').val($('#uploadEmployeeDocumentModalEdit [name="available_student_status"]').val());
                    $('#uploadEmployeeDocumentModalEdit [name="description"]').val($('#uploadEmployeeDocumentModalEdit [name="description_status"]').val());
                    $('#uploadEmployeeDocumentModalEdit [name="start_date"]').val($('#uploadEmployeeDocumentModalEdit [name="start_date_status"]').val());
                    $('#uploadEmployeeDocumentModalEdit [name="end_date"]').val($('#uploadEmployeeDocumentModalEdit [name="end_date_status"]').val());
                    $('#uploadEmployeeDocumentModalEdit [name="active"]').val($('#uploadEmployeeDocumentModalEdit [name="active_status"]').val());

                drzn2.processQueue();
            }else{
                $('#uploadEmployeeDocumentModalEdit .modal-content .uploadError').remove();
                $('#uploadEmployeeDocumentModalEdit .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select the hard copy check status.</div>');
                
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#uploadEmployeeDocumentModalEdit .modal-content .uploadError').remove();
                    document.querySelector('#uploadEmpDocBtnEdit').removeAttribute('disabled', 'disabled');
                    document.querySelector("#uploadEmpDocBtnEdit svg").style.cssText ="display: none;";
                }, 2000)
            }
            
        });
    }
    /* End Dropzone */

        $("#awardingbodyTableId").on("click", ".edit_btn", function () {      

            let $editBtn = $(this);
            let internalLink = $editBtn.attr("data-id");
            let name = $editBtn.attr("data-name");
            let link = $editBtn.attr("data-link");
            let parent = $editBtn.attr("data-parent");

       
            

            axios({
                method: "get",
                url: route("internal-link.edit", internalLink),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {

                    let dataset = response.data;
                    $('#uploadDocumentFormEdit [name="name"]').val(dataset.name);
                    $('#uploadEmployeeDocumentModalEdit [name="name_status"]').val(dataset.name);
                    $('#uploadDocumentFormEdit [name="link"]').val(dataset.link? dataset.name : '');
                    $('#uploadEmployeeDocumentModalEdit [name="link_status"]').val(dataset.link? dataset.link : '');
                    $('#uploadDocumentFormEdit [name="parent_id"]').val(dataset.parent_id? dataset.parent_id : '');
                    $('#uploadEmployeeDocumentModalEdit [name="parent_category"]').val(dataset.parent_id? dataset.parent_id : '');
                    $('#uploadEmployeeDocumentModalEdit [name="id"]').val(dataset.id);
                    $('#uploadEmployeeDocumentModalEdit [name="description_status"]').val(dataset.description);
                    $('#uploadEmployeeDocumentModalEdit [name="start_date_status"]').val(dataset.start_date);
                    $('#uploadEmployeeDocumentModalEdit [name="end_date_status"]').val(dataset.end_date);
                    if(dataset.available_staff)
                        $('#uploadEmployeeDocumentModalEdit [name="available_staff_status"]').prop('checked',true);
                    else
                        $('#uploadEmployeeDocumentModalEdit [name="available_staff_status"]').prop('checked',false);
                    if(dataset.available_student)
                        $('#uploadEmployeeDocumentModalEdit [name="available_student_status"]').prop('checked',true);
                    else
                        $('#uploadEmployeeDocumentModalEdit [name="available_student_status"]').prop('checked',false);
                    
                    if(dataset.active)
                        $('#uploadEmployeeDocumentModalEdit [name="active_status"]').prop('checked',true);
                    else
                        $('#uploadEmployeeDocumentModalEdit [name="active_status"]').prop('checked',false);

                    $('#uploadEmployeeDocumentModalEdit [name="available_student"]').val(dataset.available_student);
                    $('#uploadEmployeeDocumentModalEdit [name="description"]').val(dataset.description);
                    $('#uploadEmployeeDocumentModalEdit [name="start_date"]').val(dataset.start_date);
                    $('#uploadEmployeeDocumentModalEdit [name="end_date"]').val(dataset.end_date);
                    $('#uploadEmployeeDocumentModalEdit [name="active"]').val(dataset.active);
                    $('#uploadEmployeeDocumentModalEdit [name="available_student"]').val(dataset.active);
                    $('#uploadEmployeeDocumentModalEdit [name="available_staff"]').val(dataset.active);


                }
            }).catch((error) => {
                console.log(error);
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
                    url: route('internal-link.destroy', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Internal link  successfully deleted!');
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
                $('#confirmModal .confModDesc').html('Want to delete this Internal link  from applicant list? Please click on agree to continue.');
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
                $('#confirmModal .confModDesc').html('Want to restore this Internal link from the trash? Please click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
        });
    }
})();