import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var diversityListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let startdate = $("#startdate-DR").val() != "" ? $("#startdate-DR").val() : "";
        let enddate = $("#enddate-DR").val() != "" ? $("#enddate-DR").val() : "";
        let worktype = $("#employee_work_type_id-diversity").val() != "" ? $("#employee_work_type_id-diversity").val() : "";
        let department = $("#department_id-diversity").val() != "" ? $("#department_id-diversity").val() : "";
        let ethnicity = $("#ethnicity-DR").val() != "" ? $("#ethnicity-DR").val() : "";
        let nationality = $("#nationality-DR").val() != "" ? $("#nationality-DR").val() : "";
        let gender = $("#gender-DR").val() != "" ? $("#gender-DR").val() : "";
        let status = $("#status_id-DR").val() != "" ? $("#status_id-DR").val() : "";

        let tableContent = new Tabulator("#diversityListTable", {
            ajaxURL: route("hr.portal.reports.diversityreport.list"),
            ajaxParams: { ethnicity:ethnicity, nationality:nationality, startdate:startdate, worktype:worktype, department:department, gender:gender, enddate:enddate, status:status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printCopyStyle: true,
            printStyled: true,
            pagination:false,
            pagination: "remote",
            paginationSize: 10,
            //paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            paginationSizeSelector:false,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Works Number",
                    field: "works_no",
                    headerHozAlign: "left",
                },
                {
                    title: "Gender",
                    field: "gender",
                    headerHozAlign: "left",
                },
                {
                    title: "Ethnicity",
                    field: "ethnicity",
                    headerHozAlign: "left",
                },
                {
                    title: "Nationality",
                    field: "nationality",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status",
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

        $("#tabulator-export-xlsx-DR").on("click", function (event) {  
            event.preventDefault();
            window.XLSX = xlsx;
            tableContent.download("xlsx", "Diversity_Information.xlsx", {
                sheetName: "Diversity Information",
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

    $("#tabulator-html-filter-go-DR").on("click", function (event) {      
        event.preventDefault();

        var startdateDR = document.getElementById("startdate-DR").value;
        var worktypeDR = document.getElementById("employee_work_type_id-diversity").value;
        var departmentDR = document.getElementById("department_id-diversity").value;
        var ethnicityDR = document.getElementById("ethnicity-DR").value;
        var nationalityDR = document.getElementById("nationality-DR").value;
        var genderDR = document.getElementById("gender-DR").value;
        var enddateDR = document.getElementById("enddate-DR").value;
        var statusDR = document.getElementById("status_id-DR").value;

        if(startdateDR !="" || worktypeDR !="" || departmentDR !="" || ethnicityDR !="" || nationalityDR !="" || genderDR !="" || enddateDR !="" || statusDR !=1) {           
            diversityListTable.init();
            document.getElementById("allDiversityReportPdf").style.display="none";
            document.getElementById("diversitybySearchPdfBtn").style.display="block";
        } else {
            diversityListTable.init();
            document.getElementById("allDiversityReportPdf").style.display="block";
            document.getElementById("diversitybySearchPdfBtn").style.display="none";
        }

        // Filter function
        function filterHTMLFormDR() {
            diversityListTable.init();
        }
    });

    $("#tabulator-html-filter-reset-DR").on("click", function (event) {    
        $("#startdate-DR").val('');
        $("#employee_work_type_id-diversity").val('');
        $("#department_id-diversity").val('');
        $("#ethnicity-DR").val('');
        $("#nationality-DR").val('');
        $("#gender-DR").val('');
        $("#enddate-DR").val('');
        $("#status_id-DR").val('1');
        document.getElementById("allDiversityReportPdf").style.display="block";
        document.getElementById("diversitybySearchPdfBtn").style.display="none";
        diversityListTable.init();
    });

    $("#diversitybySearchPdfBtn").on("click", function (e) {      
        e.preventDefault();
        let startdate = $("#startdate-DR").val() != "" ? $("#startdate-DR").val() : "";
        let enddate = $("#enddate-DR").val() != "" ? $("#enddate-DR").val() : "";
        let worktype = $("#employee_work_type_id-diversity").val() != "" ? $("#employee_work_type_id-diversity").val() : "";
        let department = $("#department_id-diversity").val() != "" ? $("#department_id-diversity").val() : "";
        let ethnicity = $("#ethnicity-DR").val() != "" ? $("#ethnicity-DR").val() : "";
        let nationality = $("#nationality-DR").val() != "" ? $("#nationality-DR").val() : "";
        let gender = $("#gender-DR").val() != "" ? $("#gender-DR").val() : "";
        let status = $("#status_id-DR").val() != "" ? $("#status_id-DR").val() : "";
        
        axios({
            method: "get",
            url: route("hr.portal.reports.diversitybysearch.pdf"),
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
                link.setAttribute('download', 'Diversity Information.pdf');
                document.body.appendChild(link);
                link.click();
                
        })
        .catch((error) => {
            console.log(error);
        });
    });
})();