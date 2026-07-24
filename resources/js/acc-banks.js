import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import IMask from 'imask';
 
("use strict");

const escapeHtml = (value) => {
    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
};

// Banks are identified by their initials rather than a stored logo, so the list
// reads consistently whether or not an image was ever uploaded.
const bankInitials = (value) => {
    const words = String(value || "")
        .trim()
        .split(/\s+/)
        .map((word) => word.replace(/[^A-Za-z0-9]/g, ""))
        .filter(Boolean);

    if (words.length === 0) {
        return "BK";
    }

    if (words.length === 1) {
        return words[0].slice(0, 2).toUpperCase();
    }

    return `${words[0].charAt(0)}${words[1].charAt(0)}`.toUpperCase();
};

const bankAvatarTone = (row) => {
    const sequence = Number(row.sl || row.id || 1);

    return `is-tone-${((sequence - 1) % 4) + 1}`;
};

var bankListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#bankListTable", {
            ajaxURL: route("site.settings.banks.list"),
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
                    width: 70,
                },
                {
                    title: "Name",
                    field: "bank_name",
                    headerHozAlign: "left",
                    minWidth: 190,
                    formatter(cell, formatterParams) {
                        const data = cell.getData();
                        const name = escapeHtml(data.bank_name);

                        return '<span class="ss-process-cell">' +
                            '<span class="ss-process-cell__avatar ss-process-cell__avatar--initials ' + bankAvatarTone(data) + '" aria-hidden="true">' + escapeHtml(bankInitials(data.bank_name)) + '</span>' +
                            '<strong>' + name + '</strong>' +
                            '</span>';
                    }
                },
                {
                    title: "Opening Date",
                    field: "opening_date",
                    headerHozAlign: "left",
                    width: 130,
                },
                {
                    title: "Opening Balance",
                    field: "opening_balance",
                    headerHozAlign: "left",
                    width: 140,
                },
                {
                    title: "Audit Status",
                    field: "audit_status",
                    headerHozAlign: "left",
                    headerSort: false,
                    width: 120,
                    formatter(cell, formatterParams){
                        const isAudited = cell.getValue() == 1;

                        return '<span class="ss-status-pill ' + (isAudited ? 'is-active' : 'is-inactive') + '"><span></span>' + (isAudited ? 'Yes' : 'No') + '</span>';
                    }
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    width: 90,
                    formatter(cell, formatterParams){
                        const isOn = cell.getValue() == 1;

                        return '<button type="button" role="switch" aria-checked="' + (isOn ? 'true' : 'false') + '"' +
                            ' class="status_updater ss-table-switch ' + (isOn ? 'is-active' : 'is-inactive') + '"' +
                            ' data-id="' + cell.getData().id + '" aria-label="Toggle bank status">' +
                            '<i data-lucide="' + (isOn ? 'check' : 'x') + '"></i>' +
                            '</button>';
                    }
                },
                {
                    title: "Accounts",
                    field: "ac_name",
                    headerHozAlign: "left",
                    minWidth: 170,
                    formatter(cell, formatterParams) {
                        const data = cell.getData();

                        if (!data.ac_name) {
                            return '<span class="ss-cell-muted">&mdash;</span>';
                        }

                        const meta = [data.sort_code, data.ac_number]
                            .filter(Boolean)
                            .map((value) => '<span>' + escapeHtml(value) + '</span>')
                            .join('');

                        return '<span class="ss-bank-account">' +
                            '<strong>' + escapeHtml(data.ac_name) + '</strong>' +
                            (meta ? '<span class="ss-bank-account__meta">' + meta + '</span>' : '') +
                            '</span>';
                    }
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 110,
                    minWidth: 110,
                    download: false,
                    formatter(cell, formatterParams) {
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editBankModal" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit bank"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' +cell.getData().id +'" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete bank"><i data-lucide="trash-2"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore bank"><i data-lucide="rotate-cw"></i></button>';
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

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Status Details",
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
    if ($("#bankListTable").length) {
        // Init Table
        bankListTable.init();

        // Filter function
        function filterHTMLForm() {
            bankListTable.init();
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

        const addBankModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addBankModal"));
        const editBankModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editBankModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        // Keeps the redesigned status switches' copy in step with their checkboxes.
        const TOGGLE_COPY = {
            status: { on: ['Active', 'Selectable when recording transactions'], off: ['Inactive', 'Not selectable when recording transactions'] },
            audit_status: { on: ['Audited', 'Included in audit reporting'], off: ['Not audited', 'Excluded from audit reporting'] },
        };

        const syncToggle = function (input) {
            const copy = $(input).closest('.ss-status-toggle').find('.ss-status-toggle__copy');
            const text = TOGGLE_COPY[input.name];
            if (!copy.length || !text) return;

            const [strong, small] = input.checked ? text.on : text.off;
            copy.find('strong').text(strong);
            copy.find('small').text(small);
        };

        const setToggle = function (selector, name, checked) {
            $(selector + ' input[name="' + name + '"]').prop('checked', checked).each(function () {
                syncToggle(this);
            });
        };

        $(document).on('change', '.ss-status-toggle input[name="status"], .ss-status-toggle input[name="audit_status"]', function () {
            syncToggle(this);
        });

        const resetBankModal = function (selector, imageId, statusChecked) {
            $(selector + ' .acc__input-error').html('');
            $(selector + ' .border-danger').removeClass('border-danger');
            $(selector + ' input:not([type="checkbox"]):not([type="hidden"])').val('');
            $(selector + ' [data-ss-upload-name]').text('No file selected');
            $(selector + ' #' + imageId).attr('src', $(selector + ' #' + imageId).attr('data-placeholder'));
            setToggle(selector, 'status', statusChecked);
            setToggle(selector, 'audit_status', false);
        };

        const addBankModalEl = document.getElementById('addBankModal')
        addBankModalEl.addEventListener('hide.tw.modal', function(event) {
            resetBankModal('#addBankModal', 'bankImageAdd', true);
        });

        const editBankModalEl = document.getElementById('editBankModal')
        editBankModalEl.addEventListener('hide.tw.modal', function(event) {
            resetBankModal('#editBankModal', 'bankImageEdit', false);
            $('#editBankModal input[name="id"]').val('0');
        });

        $('#addBankModal').on('change', '#bankPhotoAdd', function(){
            showPreview('bankPhotoAdd', 'bankImageAdd');
            $('#addBankModal [data-ss-upload-name]').text(this.files?.[0]?.name || 'No file selected');
        })
        $('#editBankModal').on('change', '#bankPhotoEdit', function(){
            showPreview('bankPhotoEdit', 'bankImageEdit');
            $('#editBankModal [data-ss-upload-name]').text(this.files?.[0]?.name || 'No file selected');
        })

        $(".theSortcode").each(function () {
            var maskOptions = {
                mask: '00-00-00'
            };
            var mask = IMask(this, maskOptions);
        });

        $(".theAcNumber").each(function () {
            var maskOptions = {
                mask: '00000000'
            };
            var mask = IMask(this, maskOptions);
        });

        $('#addBankForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addBankForm');
        
            document.querySelector('#saveBank').setAttribute('disabled', 'disabled');
            document.querySelector("#saveBank svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            form_data.append('file', $('#addBankForm input[name="photo"]')[0].files[0]); 
            axios({
                method: "post",
                url: route('site.settings.banks.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveBank').removeAttribute('disabled');
                document.querySelector("#saveBank svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addBankModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Bank Item Successfully inserted.');
                    }); 
                    
                    setTimeout(function(){
                        succModal.hide();
                    }, 2000);
                }
                bankListTable.init();
            }).catch(error => {
                document.querySelector('#saveBank').removeAttribute('disabled');
                document.querySelector("#saveBank svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addBankForm .${key}`).addClass('border-danger');
                            $(`#addBankForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#bankListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "post",
                url: route("site.settings.banks.edit"),
                data: {row_id : editId},
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editBankModal input[name="bank_name"]').val(dataset.bank_name ? dataset.bank_name : '');
                    $('#editBankModal input[name="opening_balance"]').val(dataset.opening_balance ? dataset.opening_balance.toFixed(2) : '');
                    $('#editBankModal input[name="opening_date"]').val(dataset.opening_date ? dataset.opening_date : '');

                    setToggle('#editBankModal', 'audit_status', dataset.audit_status == 1);
                    setToggle('#editBankModal', 'status', dataset.status == 1);

                    $('#editBankModal input[name="ac_name"]').val(dataset.ac_name ? dataset.ac_name : '');
                    $('#editBankModal input[name="sort_code"]').val(dataset.sort_code ? dataset.sort_code : '');
                    $('#editBankModal input[name="ac_number"]').val(dataset.ac_number ? dataset.ac_number : '');
                    
                    $('#editBankModal input[name="id"]').val(editId);
                    $('#editBankModal #bankImageEdit').attr('src', dataset.image_url);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        
        $("#editBankForm").on("submit", function (e) {
            e.preventDefault();
            const form = document.getElementById("editBankForm");

            document.querySelector('#updateBank').setAttribute('disabled', 'disabled');
            document.querySelector('#updateBank svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);
            form_data.append('file', $('#editBankForm input[name="photo"]')[0].files[0]);
            axios({
                method: "post",
                url: route("site.settings.banks.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateBank").removeAttribute("disabled");
                    document.querySelector("#updateBank svg").style.cssText = "display: none;";
                    editBankModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Bank data successfully updated.');
                    });
                    
                    setTimeout(function(){
                        succModal.hide();
                    }, 2000);
                }
                bankListTable.init();
            }).catch((error) => {
                document.querySelector("#updateBank").removeAttribute("disabled");
                document.querySelector("#updateBank svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editBankForm .${key}`).addClass('border-danger')
                            $(`#editBankForm  .error-${key}`).html(val)
                        }
                    }else {
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
                    url: route('site.settings.banks.destory', recordID),
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
                    
                        setTimeout(function(){
                            succModal.hide();
                        }, 2000);
                    }
                    bankListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('site.settings.banks.restore', recordID),
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
                    
                        setTimeout(function(){
                            succModal.hide();
                        }, 2000);
                    }
                    bankListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'CHANGESTAT'){
                axios({
                    method: 'post',
                    url: route('site.settings.banks.update.status', recordID),
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
                    
                        setTimeout(function(){
                            succModal.hide();
                        }, 2000);
                    }
                    bankListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#bankListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTAT');
            });
        });

        // Delete Course
        $('#bankListTable').on('click', '.delete_btn', function(){
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
        $('#bankListTable').on('click', '.restore_btn', function(){
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
})();