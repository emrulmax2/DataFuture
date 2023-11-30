import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var employeeListTable = (function () {
    var _tableGen = function () {
        
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#employeeListTable", {
            ajaxURL: route("employee.list"),
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
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<div class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-5 inline-block">';
                                    html += '<img alt="'+cell.getData().name+'" class="rounded-full shadow" src="'+cell.getData().photourl+'">';
                                html += '</div>';
                                html += '<div class="inline-block relative" style="top: -5px;">';
                                    html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().name+'</div>';
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+(cell.getData().jobtitle != '' ? cell.getData().jobtitle : 'Unknown')+'</div>';
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Department",
                    field: "department",
                    headerHozAlign: "left",
                },
                {
                    title: "Work Type",
                    field: "work_type",
                    headerHozAlign: "left",
                },
                {
                    title: "Work Number",
                    field: "works_number",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return (cell.getData().status == 1 ? '<span class="btn inline-flex btn-success w-auto px-2 text-white py-0 rounded-0">Active</span>' : '<span class="btn inline-flex btn-danger w-auto px-2 text-white py-0 rounded-0">Inactive</span>');
                    }
                }
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            },
            rowClick:function(e, row){
                window.open(row.getData().url, '_blank');
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

        // Export
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Title Details",
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

(function(){
    if ($("#employeeListTable").length) {
        employeeListTable.init();
        

        // Filter function
        function filterTitleHTMLForm() {
            employeeListTable.init();
        }

        $("#tabulatorFilterForm #query").on('keypress', function(e){
            var key = e.keyCode || e.which;
            if(key === 13){
                e.preventDefault(); // Ensure it is only this code that runs
    
                filterTitleHTMLForm();
            }
        })

        // On click go button
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterTitleHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("1");
            filterTitleHTMLForm();
        });
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const absentUpdateModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#absentUpdateModal"));

    const absentUpdateModalEl = document.getElementById('absentUpdateModal')
    absentUpdateModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#absentUpdateModal .modal-body select').val('');
        $('#absentUpdateModal .modal-body input').val('');
        $('#absentUpdateModal .modal-body textarea').val('');

        $('#absentUpdateModal input[name="employee_id"]').html('0');
        $('#absentUpdateModal input[name="minutes"]').html('0');
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    });


    $('.absentToday').on('click', function(e){
        e.preventDefault();
        var $this = $(this);
        var employee = $this.attr('data-emloyee');
        var minute = $this.attr('data-minute');
        var hourminute = $this.attr('data-hour-min');

        $('#absentUpdateForm input[name="hour"]').val(hourminute);
        $('#absentUpdateForm input[name="employee_id"]').val(employee)
        $('#absentUpdateForm input[name="minutes"]').val(minute)
    });

    $('#absentUpdateForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('absentUpdateForm');
    
        document.querySelector('#updateAbsent').setAttribute('disabled', 'disabled');
        document.querySelector("#updateAbsent svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('hr.portal.update.absent'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateAbsent').removeAttribute('disabled');
            document.querySelector("#updateAbsent svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                absentUpdateModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Absent details successfully updated .');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });     
            }
        }).catch(error => {
            document.querySelector('#updateAbsent').removeAttribute('disabled');
            document.querySelector("#updateAbsent svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#absentUpdateForm .${key}`).addClass('border-danger');
                        $(`#absentUpdateForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
})();