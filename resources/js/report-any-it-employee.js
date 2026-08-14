import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import IMask from 'imask';
import TomSelect from "tom-select";
import Dropzone from "dropzone";
 
("use strict");

// Cell contents are built as HTML strings, so anything coming off the record
// has to be escaped before it is concatenated in.
const esc = (value) =>
    String(value == null ? "" : value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");

// `photo_url` never comes back empty: both Employee and Student fall back to
// `App\Support\Avatar::initials()`, which is a data-URI SVG in its own
// multi-colour palette. That data: prefix is the codebase's own test for
// "no photo uploaded" (see `getBrandPhotoUrlAttribute`), so it is what
// decides between the real picture and our green-and-gold disc.
const hasPhoto = (url) => !!url && !String(url).startsWith("data:");

// Fallback for the avatar when a record carries no photo.
const initials = (name) =>
    String(name || "")
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0].toUpperCase())
        .join("") || "?";

// Inline rather than `data-lucide`, so the row action buttons are painted on
// the first render instead of waiting for the icon pass in `renderComplete`.
const ICON_EDIT =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16.5 4.5a2.1 2.1 0 0 1 3 3L9 18l-4 1 1-4 10.5-10.5z"></path></svg>';
const ICON_DELETE =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 7h15M9.5 7V5h5v2M6.5 7l1 13h9l1-13"></path></svg>';
const ICON_RESTORE =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.5A8 8 0 1 1 17.6 6"></path><path d="M20 4v5h-5"></path></svg>';

var table = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#reportItAllTableId", {
            ajaxURL: route("report.any.it.employee.list"),
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
                    field: "report_number",
                    width: "170",
                    formatter(cell) {
                        return '<span class="rit-cell-id">' + esc(cell.getValue()) + "</span>";
                    },
                },
                {
                    title: "Issue Type",
                    field: "issue_type",
                    headerHozAlign: "left",
                    minWidth: 170,
                    cssClass: "rit-col-wrap",
                },
                {
                    title: "Campus",
                    field: "venue",
                    headerHozAlign: "left",
                    minWidth: 130,
                    cssClass: "rit-col-wrap",
                },
                {
                    title: "Location",
                    field: "location",
                    headerHozAlign: "left",
                    minWidth: 120,
                    cssClass: "rit-col-wrap",
                },
                {
                    title: "Report Form",
                    field: "report_form",
                    headerHozAlign: "left",
                    width: "120",
                },
                {
                    title: "Description",
                    field: "description",
                    headerHozAlign: "left",
                    minWidth: 180,
                    cssClass: "rit-col-wrap",
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    width: "130",
                    cssClass: "rit-col-loose",
                    formatter(cell) {
                        // The status arrives already ucfirst-ed from the list
                        // endpoint; the key is slugged so "In Progress" lands
                        // on a modifier class of its own.
                        const status = cell.getValue() || "";
                        const kinds = {
                            pending: "rit-chip--pending",
                            "in progress": "rit-chip--progress",
                            resolved: "rit-chip--resolved",
                            rejected: "rit-chip--rejected",
                        };
                        const kind = kinds[status.toLowerCase()] || "";
                        return (
                            '<span class="rit-chip ' + kind + '">' + esc(status) + "</span>"
                        );
                    },
                },
                {
                    title: "Reported By",
                    field: "full_name",
                    headerHozAlign: "left",
                    minWidth: 220,
                    cssClass: "rit-col-loose",
                    formatter(cell) {
                        const row = cell.getData();
                        const name = row.full_name || "";
                        const role = row.ejt_name ? row.ejt_name : "Unknown";
                        const avatar = hasPhoto(row.photourl)
                            ? '<img alt="' + esc(name) + '" src="' + esc(row.photourl) + '">'
                            : esc(initials(name));

                        return (
                            '<span class="rit-person">' +
                            '<span class="rit-person__avatar">' + avatar + "</span>" +
                            '<span class="rit-person__body">' +
                            '<span class="rit-person__name">' + esc(name) + "</span>" +
                            '<span class="rit-person__role">' + esc(role) + "</span>" +
                            "</span></span>"
                        );
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "140",
                    download: false,
                    formatter(cell) {
                        const row = cell.getData();
                        const id = esc(row.id);
                        let btns = "";

                        if (row.deleted_at == null) {
                            if (row.status == "Pending") {
                                btns +=
                                    '<button data-id="' + id + '" data-tw-toggle="modal" data-tw-target="#editModal" type="button" title="Edit" class="edit_btn rit-iconbtn rit-iconbtn--edit">' +
                                    ICON_EDIT +
                                    "</button>";
                                btns +=
                                    '<button data-id="' + id + '" type="button" title="Delete" class="delete_btn rit-iconbtn rit-iconbtn--delete">' +
                                    ICON_DELETE +
                                    "</button>";
                            } else {
                                return '<span class="rit-noaction">No action available</span>';
                            }
                        } else {
                            btns +=
                                '<button data-id="' + id + '" type="button" title="Restore" class="restore_btn rit-iconbtn rit-iconbtn--restore">' +
                                ICON_RESTORE +
                                "</button>";
                        }

                        return '<span class="rit-actions">' + btns + "</span>";
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
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Academic Years Details",
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
    let accTomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        dropdownParent: 'body',
        dropdownClass: 'ts-dropdown lcc-tom-float',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };
    let accTomOptionsMul = {
        dropdownParent: 'body',
        dropdownClass: 'ts-dropdown lcc-tom-float',
        
        ...accTomOptions,
        plugins: {
            ...accTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    // Tabulator
    if ($("#reportItAllTableId").length) {

        const statuses = new TomSelect('#statuses', accTomOptionsMul);
        const status = new TomSelect('#status', accTomOptions);

        const EditVenue = new TomSelect('#edit_venue_id', accTomOptions);
        const AddVenue = new TomSelect('#add_venue_id', accTomOptions);


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

        $(".datepicker").each(function () {
            var maskOptions = {
                dropdownParent: 'body',
                dropdownClass: 'ts-dropdown lcc-tom-float',
                
                mask: '00-00-0000'
            };
            var mask = IMask(this, maskOptions);
        });

        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const addModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));
        const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        const uploadModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#uploadDocumentModal"));
        let confModalDelTitle = 'Are you sure?';
        let confPermanentModalDelTitle = 'Permanently Delete Alert';

        /* Start Dropzone */
        if($("#uploadDocumentModal").length > 0){
            let dzErrors = false;
            Dropzone.autoDiscover = false;
            Dropzone.options.addReportITUploadForm = {
                autoProcessQueue: false,
                maxFiles: 10,
                maxFilesize: 20,
                parallelUploads: 10,
                //.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar,.mp4,.mkv,.avi
                acceptedFiles: ".jpeg,.jpg,.png",
               
                addRemoveLinks: true,
                thumbnailWidth: 100,
                thumbnailHeight: 100,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            };
    
            let options = {
                accept: (file, done) => {
                    console.log("Uploaded");
                    done();
                },
            };
    
    
            var drzn1 = new Dropzone('#addReportITUploadForm', options);
    
            drzn1.on("addedfile", function (file) {
                //add checking for file name cannot be over 16 character

                if (file.name.match(/[`!@#$%^&*+\=\[\]{};':"\\|,<>\/?~]/)) {
                    $("#uploadDocumentModal .modal-content .uploadError").remove();
                    $("#uploadDocumentModal .modal-content").prepend(
                        '<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! One of your selected file name has special characters. Please upload file with a valid name.</div>'
                    );
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                    drzn1.removeFile(file);

                    setTimeout(function () {
                        $(
                            "#uploadDocumentModal .modal-content .uploadError"
                        ).remove();
                    }, 5000);
                }

                // if(file.name.length >20) {
                //     $("#uploadDocumentModal .modal-content .uploadError").remove();
                //     $("#uploadDocumentModal .modal-content").prepend(
                //         '<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> File name cannot be over 16 character.</div>'
                //     );
                //     createIcons({
                //         icons,
                //         "stroke-width": 1.5,
                //         nameAttr: "data-lucide",
                //     });
                //     drzn1.removeFile(file);

                //     setTimeout(function () {
                //         $(
                //             "#uploadDocumentModal .modal-content .uploadError"
                //         ).remove();
                //     }, 5000);
                // }
            });
            drzn1.on("maxfilesexceeded", (file) => {
                $('#uploadDocumentModal .modal-content .uploadError').remove();
                $('#uploadDocumentModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
                drzn1.removeFile(file);
                setTimeout(function(){
                    $('#uploadDocumentModal .modal-content .uploadError').remove();
                }, 2000)
            });
    
            drzn1.on("error", function(file, response){
                dzErrors = true;
            });
    
            drzn1.on("success", function(file, response){


                drzn1.removeFile(file);
                let data = response.reportItUpload;
                
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {

                    if(response.status == 'success'){
                        $("#successModal .successModalTitle").html("Uploaded!");
                        $("#successModal .successModalDesc").html('Files successfully uploaded.');
                    } else {
                        $("#successModal .successModalTitle").html("Error!");
                        $("#successModal .successModalDesc").html(response.message);
                    }
                });
                let previewElement = `<div class="col-span-5 h-28 relative image-fit cursor-pointer zoom-in">
                                    <img class="rounded-md w-full h-full object-cover" alt="${data.file_name}" src="${response.fileUrl}">
                                    <div title="Remove this image?" class="tooltip w-5 h-5 flex items-center justify-center absolute rounded-full text-white bg-danger right-0 top-0 -mr-2 -mt-2">
                                        <i id="remove-${data.id}" data-id="${data.id}" data-lucide="x" class="removeFileIcon w-4 h-4"></i>
                                    </div>
                                </div>`;
                // Check which modal is actually visible in the DOM
                if ($('#addModal').hasClass('show')) {
                    
                    $('#addItems').removeClass('hidden');
                    $('#AddItemBox').append(previewElement);

                    if (!$("#addDocumenthiddenInput input[name='documents[]'][value='" +data.id + "']").length) {
                            $("#addDocumenthiddenInput").append('<input type="hidden" name="documents[]" value="' + data.id + '">');
                    }
                }else if ($('#editModal').hasClass('show')) {

                    if (!$("#editDocumenthiddenInput input[name='documents[]'][value='" + data.id + "']").length) {
                            $("#editDocumenthiddenInput").append('<input type="hidden" name="documents[]" value="' + data.id + '">');
                    }
                    $('#editItems').removeClass('hidden');
                    $('#editItemBox').append(previewElement);
                }
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            });
    
            drzn1.on("complete", function(file) {
                //get all uploaded file ids
                let documents = [];
                // Use DOM visibility to determine which modal is open
                if ($('#addModal').hasClass('show')) {
                    // get all uploaded file ids from #addDocumenthiddenInput input[name='documents[]']
                    $("#addDocumenthiddenInput input[name='documents[]']").each(function(){
                        documents.push($(this).val());
                    });
                    for (let i = 0; i < documents.length; i++) {
                        if (!$("#addDocumenthiddenInput input[name='documents[]'][value='" + documents[i] + "']").length) {
                            $("#addDocumenthiddenInput").append('<input type="hidden" name="documents[]" value="' + documents[i] + '">');
                        }
                    }
                } else if ($('#editModal').hasClass('show')) {
                    // get all uploaded file ids from #editDocumenthiddenInput input[name='documents[]']
                    $("#editDocumenthiddenInput input[name='documents[]']").each(function(){
                        documents.push($(this).val());
                    });
                    for (let i = 0; i < documents.length; i++) {
                        if (!$("#editDocumenthiddenInput input[name='documents[]'][value='" + documents[i] + "']").length) {
                            $("#editDocumenthiddenInput").append('<input type="hidden" name="documents[]" value="' + documents[i] + '">');
                        }
                    }
                }

                uploadModal.hide();
                drzn1.removeFile(file);
            }); 
    
            drzn1.on('queuecomplete', function(){

                $('#uploadBtn').removeAttr('disabled');
                document.querySelector("#uploadBtn svg").style.cssText ="display: none;";
    
                if(!dzErrors){

                    drzn1.removeAllFiles();
    
                    $('#uploadDocumentModal .modal-content .uploadError').remove();
                    $('#uploadDocumentModal .modal-content').prepend('<div class="alert uploadError alert-success-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Successfully uploaded.</div>');
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
    
                    setTimeout(function(){
                        $('#uploadDocumentModal .modal-content .uploadError').remove();
                        
                    }, 2000);
                }else{
                    $('#uploadDocumentModal .modal-content .uploadError').remove();
                    $('#uploadDocumentModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Error in uploaded files.</div>');
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                    
                    setTimeout(function(){
                        $('#uploadDocumentModal .modal-content .uploadError').remove();
                    }, 2000);
                }
            })
    
            $('#uploadBtn').on('click', function(e){


                e.preventDefault();
                var acceptedFiles = drzn1.getAcceptedFiles().length;
                
                if(acceptedFiles > 0){
                document.querySelector('#uploadBtn').setAttribute('disabled', 'disabled');
                document.querySelector("#uploadBtn svg").style.cssText ="display: inline-block;";


                drzn1.processQueue();
                } else {
                    $('#uploadDocumentModal .modal-content .uploadError').remove();
                    $('#uploadDocumentModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Please select at least one file for upload.</div>');
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                    setTimeout(function(){
                        $('#uploadDocumentModal .modal-content .uploadError').remove();
                    }, 2000);
                }
            });
        }
        /* End Dropzone */

        const addModalEl = document.getElementById('addModal')
        addModalEl.addEventListener('hide.tw.modal', function(event) {
            // do not remove these hidden input values

            $('#addModal .acc__input-error').html('');
            $('#addModal input[type=radio]').prop('checked', false);
            AddVenue.clear();
            $('#addModal input[name="student_id"]').val($('#student_id').val());
            $('#addModal input[name="employee_id"]').val($('#employee_id').val());
            $('#addModal input[name="status"]').val('Pending');
            $('#addModal input[name="created_by"]').val($('#created_by').val());
            $('#addModal input[name="location"]').val('');
            $('#addModal textarea[name="description"]').val('');
            $('#addModal input').not('input[type=hidden]').not('input[name=issue_type_id]').val('');
            $('#addModal select').not('select[type=hidden]').val('');
            
            $('#addDocumenthiddenInput').html('');
            $('#addItems').addClass('hidden');
            $('#addItemBox').html('');
            
        });
        
        const editModalEl = document.getElementById('editModal')
        editModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editModal .acc__input-error').html('');
            $('#editModal input[name="id"]').val('0');

            $('#editModal input').not('input[type=hidden]').not('input[name=issue_type_id]').val('');
            $('#editModal select').not('select[type=hidden]').val('');
            $('#editModal textarea').val('');
            $('#editDocumenthiddenInput').html('');
            $('#editItems').addClass('hidden');
            $('#editItemBox').html('');
            EditVenue.clear();
            $('#editModal input[name="location"]').val('');
            $('#editModal input[name="student_id"]').val($('#edit_student_id').val());
            $('#editModal input[name="employee_id"]').val($('#edit_employee_id').val());
            $('#editModal input[name="updated_by"]').val($('#edit_updated_by').val());
            
        });

        const confirmModalEl = document.getElementById('confirmModal')
        confirmModalEl.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModal .agreeWith').attr('data-id', '0');
            $('#confirmModal .agreeWith').attr('data-action', 'none');
        });


        $('#addForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addForm');
        
            document.querySelector('#save').setAttribute('disabled', 'disabled');
            document.querySelector("#save svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('report.it.all.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    document.querySelector('#save').removeAttribute('disabled');
                    document.querySelector("#save svg").style.cssText = "display: none;";
                    addModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Report any IT\'s data successfully inserted.');
                    });         
                }
                table.init();
            }).catch(error => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addForm .${key}`).addClass('border-danger')
                            $(`#addForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#reportItAllTableId").on("click", ".edit_btn", function (e) {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");
            e.preventDefault();
            $('#editForm input').attr('disabled', 'disabled');
            $('#editForm select').attr('disabled', 'disabled');
            $('.editLoading').show();
            axios({
                method: "get",
                url: route("report.it.all.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) { 
                $('#editForm input').removeAttr('disabled');
                $('#editForm select').removeAttr('disabled');
                    $('.editLoading').hide();
                    let dataset = response.data;
                    $('#editForm input[name="employee_id"]').val(dataset.employee_id);
                    $('#editForm select[name="venue_id"]').val(dataset.venue_id);
                    $('#editForm input[name="location"]').val(dataset.location);
                    $('#editForm input[name="student_id"]').val(dataset.student_id);
                    $('#editForm textarea[name="description"]').val(dataset.description);
                    
                    
                    EditVenue.setValue(dataset.venue_id);
                    $('#editForm #edit_issue_type_id_'+dataset.issue_type_id).prop('checked', true);
                    $('#editModal input[name="id"]').val(editId);

                    const documents = dataset.uploads;
                    if(documents.length > 0){
                        for (let i = 0; i < documents.length; i++) {
                            //check if document value already exists
                            if (!$("#editDocumenthiddenInput input[name='documents[]'][value='" + documents[i].id + "']").length) {
                                $("#editDocumenthiddenInput").append('<input type="hidden" name="documents[]" value="' + documents[i].id + '">');
                            }

                                let previewElement = `<div class="col-span-5 h-28 relative image-fit cursor-pointer zoom-in">
                                                <img class="rounded-md w-full h-full object-cover" alt="${documents[i].file_name}" src="${documents[i].fileUrl}">
                                                <div title="Remove this image?" class="tooltip w-5 h-5 flex items-center justify-center absolute rounded-full text-white bg-danger right-0 top-0 -mr-2 -mt-2">
                                                    <i id="remove-${documents[i].id}" data-id="${documents[i].id}" data-lucide="x" class="removeFileIcon w-4 h-4"></i>
                                                </div>
                                            </div>`;
                            $('#editItems').removeClass('hidden');
                            $('#editItemBox').append(previewElement);
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });
                            
                        }
                    }
                }
            })
            .catch((error) => {
                
                $('#editForm input').removeAttr('disabled');
                $('#editForm select').removeAttr('disabled');
                console.log(error);
            });
        });

        // Update Course Data
        $("#editForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editModal input[name="id"]').val();

            const form = document.getElementById("editForm");

            document.querySelector('#update').setAttribute('disabled', 'disabled');
            document.querySelector('#update svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("report.it.all.update", editId),
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
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Report any IT\'s data successfully updated.');
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
                            $("#successModal .successModalTitle").html("Oops!");
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
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('report.it.all.destroy', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Report any IT successfully deleted!');
                        });
                    }
                    table.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('report.it.all.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Academic Year Data Successfully Restored!');
                        });
                    }
                    table.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $(document).on('click', '.removeFileIcon', function() {

            let fileID = $(this).attr('data-id');
            $(this).closest('.col-span-5').remove();
            //remove hidden input also
            $(`#addForm input[name='documents[]'][value='${fileID}']`).remove();
            $(`#addDocumenthiddenInput input[name='documents[]'][value='${fileID}']`).remove();
            $(`#editForm input[name='documents[]'][value='${fileID}']`).remove();
            $(`#editDocumenthiddenInput input[name='documents[]'][value='${fileID}']`).remove();
            //if no more items then hide the box
            if($('#AddItemBox .col-span-5').length == 0){
                $('#addItems').addClass('hidden');
            }
            if($('#editItemBox .col-span-5').length == 0){
                $('#editItems').addClass('hidden');
            }
            //make an axios call to remove the file from server
            axios({
                method: 'post',
                url: route('report.it.all.remove.upload'),
                data: {file_id: fileID},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    //file removed from server
                }
            }).catch(error =>{
                console.log(error)
            });
            
        });
        // Delete Course
        $('#reportItAllTableId').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });

        // delete Final Btn
        $('#reportItAllTableId').on('click', '.delete_final_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to remove these record from system? This action is final and no turning back from it.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });

        
        // Restore Course
        $('#reportItAllTableId').on('click', '.restore_btn', function(){
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
                $('#confirmModal .confModDesc').html('Want to restore this Report any IT from the trash? Please click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
        });
    }
})();