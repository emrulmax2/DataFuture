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
            pagination: "remote",
            paginationSize: true,
            paginationSizeSelector: false,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Name",
                    field: "name",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Works Number",
                    field: "works_no",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Gender",
                    field: "gender",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Ethnicity",
                    field: "ethnicity",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Nationality",
                    field: "nationality",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: 150,
                }
            ],
            renderStarted:function(){
                $("#diversityListTable .tabulator-footer").hide();
                //$(".tabulator-headers").css('height',0);  
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
        $("#enddate-DR").val('');
        let employeeWork = document.getElementById('employee_work_type_id-diversity');
        employeeWork.tomselect.setValue("");
        
        let departmentId = document.getElementById('department_id-diversity');
        departmentId.tomselect.setValue("");
        
        let ethnicity = document.getElementById('ethnicity-DR');
        ethnicity.tomselect.setValue("");

        let nationality = document.getElementById('nationality-DR');
        nationality.tomselect.setValue("");
        
        let gender = document.getElementById('gender-DR');
        gender.tomselect.setValue("");

        let statusIdContact = document.getElementById('status_id-DR');
        statusIdContact.tomselect.setValue("1");

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