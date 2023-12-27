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
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
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
                }
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

        $("#tabulator-export-xlsx-SR").on("click", function (event) {
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
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove this " + values.length + " item?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    if ($("#starterListTable").length > 0) {
        var worktypeSR = new TomSelect('#employee_work_type_id-starter', tomOptions);
        var departmentSR = new TomSelect('#department_id-starter', tomOptions);
        var ethnicitySR = new TomSelect('#ethnicity-starter', tomOptions);
        var nationalitySR = new TomSelect('#nationality-starter', tomOptions);
        var genderSR = new TomSelect('#gender-starter', tomOptions);

        starterListTable.init();
        
        // Filter function
        function filterTitleHTMLFormSR() {
            starterListTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-SR").on("click", function (event) {
            document.getElementById("starterreportPdfBtn").style.display="none";
            document.getElementById("starterreportbySearchPdfBtn").style.display="block";
            filterTitleHTMLFormSR();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-SR").on("click", function (event) {
            worktypeSR.clear(true);
            departmentSR.clear(true);
            ethnicitySR.clear(true);
            nationalitySR.clear(true);
            genderSR.clear(true);
            $("#startdate-starter").val('');
            $("#enddate-starter").val('');
            $("#status_id-starter").val('1');
            document.getElementById("starterreportPdfBtn").style.display="block";
            document.getElementById("starterreportbySearchPdfBtn").style.display="none";
            filterTitleHTMLFormSR();
        });
    }

    $("#starterreportbySearchPdfBtn").on("click", function (e) {      
        e.preventDefault();
        let startdate = $("#startdate-starter").val() != "" ? $("#startdate-starter").val() : "";
        let enddate = $("#enddate-starter").val() != "" ? $("#enddate-starter").val() : "";
        let worktype = $("#employee_work_type_id-starter").val() != "" ? $("#employee_work_type_id-starter").val() : "";
        let department = $("#department_id-starter").val() != "" ? $("#department_id-starter").val() : "";
        let ethnicity = $("#ethnicity-starter").val() != "" ? $("#ethnicity-starter").val() : "";
        let nationality = $("#nationality-starter").val() != "" ? $("#nationality-starter").val() : "";
        let gender = $("#gender-starter").val() != "" ? $("#gender-starter").val() : "";
        let status = $("#status_id-starter").val() != "" ? $("#status_id-starter").val() : "";
        
        axios({
            method: "get",
            url: route("hr.portal.reports.starterreportbysearch.pdf"),
            params: {
                startdate: startdate, worktype:worktype, department:department, ethnicity:ethnicity, nationality:nationality, gender:gender, enddate:enddate, status:status
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            responseType: 'blob',
        })
        .then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'Employee Starter.pdf');
                document.body.appendChild(link);
                link.click();
                
        })
        .catch((error) => {
            console.log(error);
        });
    });
})();