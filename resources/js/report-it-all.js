import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import IMask from 'imask';
import TomSelect from "tom-select";
 
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
const ICON_VIEW =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
const ICON_DELETE =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 7h15M9.5 7V5h5v2M6.5 7l1 13h9l1-13"></path></svg>';
const ICON_RESTORE =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.5A8 8 0 1 1 17.6 6"></path><path d="M20 4v5h-5"></path></svg>';

var table = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let reportFrom = $("#reportFrom").val() != "" ? $("#reportFrom").val() : "";
        let statuses = $("#statuses").val() != "" ? $("#statuses").val() : "";
        let issueTypeId = $("#issue_type_id").val() != "" ? $("#issue_type_id").val() : "";
        let tableContent = new Tabulator("#reportItAllTableId", {
            ajaxURL: route("report.it.all.list"),
            ajaxParams: { querystr: querystr, status: status, reportFrom: reportFrom, statuses: statuses, issue_type_id: issueTypeId },
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
                        // This endpoint returns the raw status, so the lookup is
                        // case-insensitive and "In Progress" gets a chip of its own.
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
                            btns +=
                                '<a href="' + esc(route("report.it.all.show", row.id)) + '" title="View details" class="rit-iconbtn rit-iconbtn--view">' +
                                ICON_VIEW +
                                "</a>";
                            btns +=
                                '<button data-id="' + id + '" type="button" title="Delete" class="delete_btn rit-iconbtn rit-iconbtn--delete">' +
                                ICON_DELETE +
                                "</button>";
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

        let statuses = new TomSelect('#statuses', accTomOptionsMul);
        let reportFrom = new TomSelect('#reportFrom', accTomOptionsMul);
        let issueType = new TomSelect('#issue_type_id', accTomOptions);
        let status = new TomSelect('#status', accTomOptions);
        

        //let EditVenue = new TomSelect('#edit_venue_id', accTomOptions);
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
        const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';
        let confPermanentModalDelTitle = 'Permanently Delete Alert';


        
        const editModalEl = document.getElementById('editModal')
        editModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editModal .acc__input-error').html('');
            $('#editModal input[name="id"]').val('0');
            $('#editForm input[name="status"]').prop('checked', false);
            $('#editModal input').not('input[type=hidden]').val('');
            $('#editModal select').not('select[type=hidden]').val('');
            $('#editModal textarea').val('');
            
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


        // $('#addForm').on('submit', function(e){
        //     e.preventDefault();
        //     const form = document.getElementById('addForm');
        
        //     document.querySelector('#save').setAttribute('disabled', 'disabled');
        //     document.querySelector("#save svg").style.cssText ="display: inline-block;";

        //     let form_data = new FormData(form);
        //     axios({
        //         method: "post",
        //         url: route('report.it.all.store'),
        //         data: form_data,
        //         headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        //     }).then(response => {
        //         document.querySelector('#save').removeAttribute('disabled');
        //         document.querySelector("#save svg").style.cssText = "display: none;";
                
        //         if (response.status == 200) {
        //             document.querySelector('#save').removeAttribute('disabled');
        //             document.querySelector("#save svg").style.cssText = "display: none;";
        //             addModal.hide();

        //             succModal.show();
        //             document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
        //                 $("#successModal .successModalTitle").html("Congratulations!");
        //                 $("#successModal .successModalDesc").html('Report any IT\'s data successfully inserted.');
        //             });         
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
        //             } else {
        //                 console.log('error');
        //             }
        //         }
        //     });
        // });

        $("#reportItAllTableId").on("click", ".edit_btn", function (e) {  
            $('#editForm .Employee_class').hide();
            $('#editForm .Student_class').hide();    
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
                    if(dataset.employee_id != null){
                        $('#editForm .Employee_class').fadeIn();
                        $('#editForm .Student_class').hide();
                    } else if(dataset.student_id != null){
                        $('#editForm .Student_class').fadeIn();
                        $('#editForm .Employee_class').hide();
                    }
                    $('#editForm select[name="issue_type_id"]').val(dataset.issue_type_id);
                    $('#editForm input[name="employee_id"]').val(dataset.employee_id);
                    $('#editForm input[name="student_id"]').val(dataset.student_id);
                    //implement radio button selection
                    
                    $('#editForm #edit_issue_type_id_'+dataset.issue_type_id).prop('checked', true);
                    $('#editForm input[name="status"][value="'+dataset.status+'"]').prop('checked', true);
                    
                    $('#editForm input[name="location"]').val(dataset.location);

                    $('#editForm textarea[name="description"]').val(dataset.description);

                    $('#editModal input[name="id"]').val(editId);
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