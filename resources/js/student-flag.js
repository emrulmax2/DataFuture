import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
 
("use strict");

function flagColorLabel(value) {
    const labels = {
        Success: "Green",
        Warning: "Yellow",
        Danger: "Red",
    };

    return labels[value] || "Not set";
}

function flagColorTone(value) {
    return ["Success", "Warning", "Danger"].includes(value) ? value.toLowerCase() : "none";
}

var settingsListTable = (function () {
    var _tableGen = function () {
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#settingsListTable", {
            ajaxURL: route("flags.list"),
            ajaxParams: { querystr: querystr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50, 100],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: 110,
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 240,
                },
                {
                    title: "Color",
                    field: "color",
                    headerHozAlign: "left",
                    minWidth: 150,
                    formatter(cell) {
                        const color = cell.getData().color;

                        return `<span class="ss-flag-color-pill is-${flagColorTone(color)}"><span></span>${flagColorLabel(color)}</span>`;
                    }
                },
                {
                    title: "Raisers",
                    field: "raisers",
                    headerHozAlign: "left",
                    headerSort: false,
                    minWidth: 320,
                    formatter(cell) {
                        return `<div class="ss-flag-raisers">${cell.getData().raisers || '<span class="ss-cell-muted">No clearers assigned</span>'}</div>`;
                    }
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 170,
                    minWidth: 170,
                    download: false,
                    formatter(cell) {
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit flag"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete flag"><i data-lucide="trash-2"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore flag"><i data-lucide="rotate-cw"></i></button>';
                        }
                        
                        return btns;
                    },
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.7,
                    nameAttr: "data-lucide",
                });
            },
        });

        window.addEventListener("resize", () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });

        $("#tabulator-export-csv").on("click", function () {
            tableContent.download("csv", "student-flags.csv");
        });

        $("#tabulator-export-json").on("click", function () {
            tableContent.download("json", "student-flags.json");
        });

        $("#tabulator-export-xlsx").on("click", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "student-flags.xlsx", {
                sheetName: "Student Flags",
            });
        });

        $("#tabulator-export-html").on("click", function () {
            tableContent.download("html", "student-flags.html", {
                style: true,
            });
        });

        $("#tabulator-print").on("click", function () {
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

        let tomOptionsFlag = {
            plugins: {
                dropdown_input: {}
            },
            placeholder: "Search users...",
            dropdownParent: 'body',
            dropdownClass: 'ts-dropdown ss-settings-tom-dropdown',
            maxOptions: null,
            create: false,
            allowEmptyOption: true,
            onDelete: function (values) {
                return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
            },
        };

        let multiTomOptFlag = {
            dropdownParent: 'body',
            dropdownClass: 'ts-dropdown ss-settings-tom-dropdown',
            
            ...tomOptionsFlag,
            plugins: {
                ...tomOptionsFlag.plugins,
                remove_button: {
                    title: "Remove this item",
                },
            }
        };
    
        let user_ids = new TomSelect('#user_ids', multiTomOptFlag);
        let edit_user_ids = new TomSelect('#edit_user_ids', multiTomOptFlag);

        const addSettingsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addSettingsModal"));
        const editSettingsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editSettingsModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        function resetModalForm($modal, tomSelect, resetId = false) {
            $modal.find(".acc__input-error").html("");
            $modal.find(".border-danger").removeClass("border-danger");
            $modal.find(".ss-settings-modal__body input[type='text']").val("");
            $modal.find(".ss-settings-modal__body select[name='color']").val("");

            if (resetId) {
                $modal.find('input[name="id"]').val("0");
            }

            tomSelect.clear(true);
        }

        const addSettingsModalEl = document.getElementById('addSettingsModal');
        addSettingsModalEl.addEventListener('show.tw.modal', function() {
            resetModalForm($('#addSettingsModal'), user_ids);
        });

        addSettingsModalEl.addEventListener('hide.tw.modal', function(event) {
            resetModalForm($('#addSettingsModal'), user_ids);
        });
        
        const editSettingsModalEl = document.getElementById('editSettingsModal');
        editSettingsModalEl.addEventListener('hide.tw.modal', function(event) {
            resetModalForm($('#editSettingsModal'), edit_user_ids, true);
        });

        $('#addSettingsForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addSettingsForm');
            const $form = $(form);

            $form.find(".acc__input-error").html("");
            $form.find(".border-danger").removeClass("border-danger");
        
            document.querySelector('#saveSettings').setAttribute('disabled', 'disabled');
            document.querySelector("#saveSettings svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('flags.store'),
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
                            $("#successModal .successModalDesc").html('Flag Item Successfully inserted.');
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

            resetModalForm($('#editSettingsModal'), edit_user_ids, true);

            axios({
                method: "get",
                url: route("flags.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editSettingsModal input[name="name"]').val(dataset.name ? dataset.name : '');
                    $('#editSettingsModal select[name="color"]').val(dataset.color ? dataset.color : '');
                    
                    $('#editSettingsModal input[name="id"]').val(editId);
                    
                    if(dataset.raiser_ids){
                        edit_user_ids.clear(true);

                        $.each(dataset.raiser_ids, function(index, id) {
                            edit_user_ids.addItem(id); 
                        });
                    }else{
                        edit_user_ids.clear(true);
                    }

                    editSettingsModal.show();
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        
        $("#editSettingsForm").on("submit", function (e) {
            e.preventDefault();
            const form = document.getElementById("editSettingsForm");
            const $form = $(form);

            $form.find(".acc__input-error").html("");
            $form.find(".border-danger").removeClass("border-danger");

            document.querySelector('#updateSettings').setAttribute('disabled', 'disabled');
            document.querySelector('#updateSettings svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("flags.update"),
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
                        $("#successModal .successModalDesc").html('Flags data successfully updated.');
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
                            $("#successModal .successModalTitle").html("Oops!");
                            $("#successModal .successModalDesc").html(message || 'No data change found!');
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
                    url: route('flags.destory', recordID),
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
                    url: route('flags.restore', recordID),
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
