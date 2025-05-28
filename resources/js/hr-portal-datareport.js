import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";


("use strict");
var starterListTable = (function () {
    var _tableGen = function () {
        let startdate = $("#startdate-starter").val() != "" ? $("#startdate-starter").val() : "";
        let enddate = $("#enddate-starter").val() != "" ? $("#enddate-starter").val() : "";
        let worktype = $("#employee_work_type_id-starter").val() != "" ? $("#employee_work_type_id-starter").val() : "";
        let department = $("#department_id-starter").val() != "" ? $("#department_id-starter").val() : "";
        let ethnicity = $("#ethnicity-starter").val() != "" ? $("#ethnicity-starter").val() : "";
        let nationality = $("#nationality-starter").val() != "" ? $("#nationality-starter").val() : "";
        let gender = $("#gender-starter").val() != "" ? $("#gender-starter").val() : "";
        let status = $("#status_id-starter").val() != "" ? $("#status_id-starter").val() : "";

        let tableContent = new Tabulator("#starterListTable", {
            ajaxURL: route("hr.portal.reports.starterreport.list"),
            ajaxParams: { ethnicity:ethnicity, nationality:nationality, startdate:startdate, worktype:worktype, department:department, gender:gender, enddate:enddate, status:status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printCopyStyle: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: true,
            paginationSizeSelector: false,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Surname",
                    field: "last_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Fore Name",
                    field: "first_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Works Number",
                    field: "works_number",
                    headerHozAlign: "left",
                },
                {
                    title: "Start Date",
                    field: "started_on",
                    headerHozAlign: "left",
                    width: 200,
                }
            ],
            renderStarted:function(){
                $("#starterListTable .tabulator-footer").hide();
            },
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

        $("#tabulator-export-xlsx-SR").on("click", function (event) {
            event.preventDefault();
            window.XLSX = xlsx;
            tableContent.download("xlsx", "Employee_Starter.xlsx", {
                sheetName: "Employee Starter Report",
            });
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function(){

    $('.checkedAlls').on('click', function () {
        var $this = $(this);
        var parent = $this.attr('data-parent');
    
        if ($this.hasClass('active')) {
            $('.' + parent + ' input.cus-check').prop('checked', false);
            $this.removeClass('active btn-success');
        } else {
            $('.' + parent + ' input.cus-check').prop('checked', true);
            $this.addClass('active btn-success');
        }
    });

    let admissionDatepickerOpt = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: true,
        format: "DD-MM-YYYY",
        maxDate: new Date(),
        dropdowns: {
            minYear: 1900,
            maxYear: null,
            months: true,
            years: true,
        },
    };

    

    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    $('.addmissionLccTom').each(function(){
        if ($(this).attr("multiple") !== undefined) {
            tomOptions = {
                ...tomOptions,
                plugins: {
                    ...tomOptions.plugins,
                    remove_button: {
                        title: "Remove this item",
                    },
                }
            };
        }
        new TomSelect(this, tomOptions);
    });

    if($('#employeeDataReportForm').length > 0){

        $('.lccTom').each(function(){
            if ($(this).attr("multiple") !== undefined) {
                tomOptions = {
                    ...tomOptions,
                    plugins: {
                        ...tomOptions.plugins,
                        remove_button: {
                            title: "Remove this item",
                        },
                    }
                };
            }
            new TomSelect(this, tomOptions);
        });

        $('.admissionDatepicker').each(function(){
            new Litepicker({
                element: this,
                ...admissionDatepickerOpt,
            });
        })
    }
    // On reset filter form
    // $("#tabulator-html-filter-reset-SR").on("click", function (event) {
    //     $("#startdate-starter").val('');
    //     $("#enddate-starter").val('');
    //     let employeeWork = document.getElementById('employee_work_type_id-starter');
    //     employeeWork.tomselect.setValue("");
        
    //     let departmentId = document.getElementById('department_id-starter');
    //     departmentId.tomselect.setValue("");
        
    //     let ethnicity = document.getElementById('ethnicity-starter');
    //     ethnicity.tomselect.setValue("");

    //     let nationality = document.getElementById('nationality-starter');
    //     nationality.tomselect.setValue("");
        
    //     let gender = document.getElementById('gender-starter');
    //     gender.tomselect.setValue("");

    //     let statusIdContact = document.getElementById('status_id-starter');
    //     statusIdContact.tomselect.setValue("1");

    //     document.getElementById("starterreportPdfBtn").style.display="block";
    //     document.getElementById("starterreportbySearchPdfBtn").style.display="none";
    //     starterListTable.init();
    // });
    $('#employeeDataReportForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('employeeDataReportForm');
    
        document.querySelector('#saveNote').setAttribute('disabled', 'disabled');
        document.querySelector("#saveNote svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('employee.hr.datareport'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            responseType: 'blob',
        }).then((response) => {
            document.querySelector('#saveNote').removeAttribute('disabled');
            document.querySelector("#saveNote svg").style.cssText = "display: none;";
            //console.log(response.data.message);
            //return false;
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'Employee_data_report.xlsx'); 
            document.body.appendChild(link);
            link.click();

        }).catch(error => {
            document.querySelector('#saveNote').removeAttribute('disabled');
            document.querySelector("#saveNote svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addNoteForm .${key}`).addClass('border-danger');
                        $(`#addNoteForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

})();