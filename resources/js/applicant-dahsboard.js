import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var applicantApplicantionList = (function () {
    var _tableGen = function () {
        // Setup Tabulator

        let tableContent = new Tabulator("#applicantApplicantionList", {
            ajaxURL: route("applicant.dashboard.applications.list"),
            ajaxParams: {},
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
                    field: "sl",
                    width: "110",
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "DOB",
                    field: "dob",
                    headerHozAlign: "left",
                },
                {
                    title: "Gender",
                    field: "gender",
                    headerHozAlign: "left",
                },
                {
                    title: "Course",
                    field: "course",
                    headerHozAlign: "left",
                },
                {
                    title: "Submission Date",
                    field: "submission_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {
                        var btns = "";
                        if (cell.getData().submission_date == '' && cell.getData().status_id == 1) {
                            btns += '<a href="'+route('applicant.application')+'" class="btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="'+cell.getData().id+'" type="button" class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }else{
                            btns += '<a href="'+route('applicant.application.show', cell.getData().id)+'" class="btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
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
            }
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
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function () {
    if($('#applicantApplicantionList').length > 0){
        applicantApplicantionList.init();

        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

        const confirmModalEl = document.getElementById('confirmModal');
        confirmModalEl.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModal .agreeWith').attr('data-id', '0');
        });

        // Open confirm modal for an incomplete application
        $('#applicantApplicantionList').on('click', '.delete_btn', function(){
            let rowID = $(this).attr('data-id');
            confModal.show();
            $('#confirmModal .agreeWith').attr('data-id', rowID);
        });

        // Confirm delete action
        $('#confirmModal .agreeWith').on('click', function(){
            let recordID = $(this).attr('data-id');
            if(!recordID || recordID == '0'){
                return;
            }

            $('#confirmModal button').attr('disabled', 'disabled');
            $('#confirmModal .agreeWith svg').css('display', 'inline-block');

            axios({
                method: 'delete',
                url: route('applicant.dashboard.applications.destroy', recordID),
                headers: {'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('#confirmModal button').removeAttr('disabled');
                $('#confirmModal .agreeWith svg').css('display', 'none');
                confModal.hide();

                succModal.show();
                document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                    $('#successModal .successModalTitle').html('Done!');
                    $('#successModal .successModalDesc').html(response.data.message ? response.data.message : 'Incomplete application successfully deleted.');
                });
                applicantApplicantionList.init();
            }).catch(error => {
                $('#confirmModal button').removeAttr('disabled');
                $('#confirmModal .agreeWith svg').css('display', 'none');
                confModal.hide();

                succModal.show();
                let message = (error.response && error.response.data && error.response.data.message) ? error.response.data.message : 'Unable to delete this application.';
                document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                    $('#successModal .successModalTitle').html('Oops!');
                    $('#successModal .successModalDesc').html(message);
                });
            });
        });
    }
})();