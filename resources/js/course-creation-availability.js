import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import IMask from 'imask';

("use strict");
var courseCreationAvailabilityTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let coursecreationid = $("#courseCreationAvailibilityTableId").attr('data-coursecreationid') != "" ? $("#courseCreationAvailibilityTableId").attr('data-coursecreationid') : "0";

        let tableContent = new Tabulator("#courseCreationAvailibilityTableId", {
            ajaxURL: route("course.creation.availability.list"),
            ajaxParams: { coursecreationid: coursecreationid},
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
                    width: "80",
                },
                {
                    title: "Admission Start Date",
                    field: "admission_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Admission End Date",
                    field: "admission_end_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Course Start Date",
                    field: "course_start_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Course End Date",
                    field: "course_end_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Last Joinning Date",
                    field: "last_joinning_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Type",
                    field: "type",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "120",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#cretionAvailabilityEditModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            /*btns +='<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';*/
                        }  else if (cell.getData().deleted_at != null) {
                            btns +='<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
                sheetName: "Course Creation Availibility",
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
    if($('#courseCreationAvailibilityTableId').length > 0){
        // Init Table
        courseCreationAvailabilityTable.init();

        // Filter function
        function filterHTMLForm() {
            courseCreationAvailabilityTable.init();
        }


        $(".datepicker").each(function () {
            var maskOptions = {
                mask: '00-00-0000'
            };
            var mask = IMask(this, maskOptions);
        });

        const cretionAvailabilityAddModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#cretionAvailabilityAddModal"));
        const cretionAvailabilityEditModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#cretionAvailabilityEditModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModalCCA = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalCCA"));

        let confModalDelTitleCCA = 'Are you sure?';
        let confModalDelDescriptionCCA = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescriptionCCA = 'Do you really want to re-store these records? Click agree to continue.';

        const cretionAvailabilityAddModalEl = document.getElementById('cretionAvailabilityAddModal')
        cretionAvailabilityAddModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#cretionAvailabilityAddModal .acc__input-error').html('');
            $('#cretionAvailabilityAddModal  .modal-body input').val('');
            $('#cretionAvailabilityAddModal  .modal-body select').val('');
        });
        
        const cretionAvailabilityEditModalEl = document.getElementById('cretionAvailabilityEditModal')
        cretionAvailabilityEditModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#cretionAvailabilityEditModal .acc__input-error').html('');
            $('#cretionAvailabilityEditModal .modal-body input').val('');
            $('#cretionAvailabilityEditModal .modal-body select').val('');
        });


        $("#courseCreationAvailibilityTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("course.creation.availability.edit", editId),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#cretionAvailabilityEditModal input[name="admission_date"]').val(dataset.admission_date ? dataset.admission_date : '');
                    $('#cretionAvailabilityEditModal input[name="admission_end_date"]').val(dataset.admission_end_date ? dataset.admission_end_date : '');
                    $('#cretionAvailabilityEditModal input[name="course_start_date"]').val(dataset.course_start_date ? dataset.course_start_date : '');
                    $('#cretionAvailabilityEditModal input[name="course_end_date"]').val(dataset.course_end_date ? dataset.course_end_date : '');
                    $('#cretionAvailabilityEditModal input[name="last_joinning_date"]').val(dataset.last_joinning_date ? dataset.last_joinning_date : '');
                    $('#cretionAvailabilityEditModal select[name="type"]').val(dataset.type ? dataset.type : '');

                    $('#cretionAvailabilityEditModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $('#cretionAvailabilityEditForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('cretionAvailabilityEditForm');
        
            document.querySelector('#crationAvailabilityUpdate').setAttribute('disabled', 'disabled');
            document.querySelector("#crationAvailabilityUpdate svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('course.creation.availability.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#crationAvailabilityUpdate').removeAttribute('disabled');
                document.querySelector("#crationAvailabilityUpdate svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    cretionAvailabilityEditModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Course creation Availability data successfully updated.');
                    });                
                        
                }
                courseCreationAvailabilityTable.init();
            }).catch(error => {
                document.querySelector('#crationAvailabilityUpdate').removeAttribute('disabled');
                document.querySelector("#crationAvailabilityUpdate svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#cretionAvailabilityEditForm .${key}`).addClass('border-danger')
                            $(`#cretionAvailabilityEditForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });


        $('#cretionAvailabilityAddForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('cretionAvailabilityAddForm');
        
            document.querySelector('#crationAvailabilitySave').setAttribute('disabled', 'disabled');
            document.querySelector("#crationAvailabilitySave svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('course.creation.availability.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#crationAvailabilitySave').removeAttribute('disabled');
                document.querySelector("#crationAvailabilitySave svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    cretionAvailabilityAddModal.hide();
                    $('#cretionAvailabilityAddHook').fadeOut(100);

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Course creation Availability data successfully inserted.');
                    });                
                        
                }
                courseCreationAvailabilityTable.init();
            }).catch(error => {
                document.querySelector('#crationAvailabilitySave').removeAttribute('disabled');
                document.querySelector("#crationAvailabilitySave svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#cretionAvailabilityAddForm .${key}`).addClass('border-danger')
                            $(`#cretionAvailabilityAddForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });
    }

})()