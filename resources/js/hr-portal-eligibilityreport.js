import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";


("use strict");
var visaExpiryListTable = (function () {
    var _tableGen = function () {
        let querystr = $("#query-VEXR").val() != "" ? $("#query-VEXR").val() : "";
        let tableContent = new Tabulator("#visaExpiryListTable", {
            ajaxURL: route("hr.portal.reports.eligibilityreport.visaexpirylist"),
            ajaxParams: { querystr: querystr},
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
                    title: "Employee Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Work Permit Number",
                    field: "workpermit_number",
                    headerHozAlign: "left",
                },
                {
                    title: "Work Permit Expiry Date",
                    field: "workpermit_expire",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "days_remained",
                    headerHozAlign: "left",
                    width: 150,
                }
            ],
            renderStarted:function(){
                $("#visaExpiryListTable .tabulator-footer").hide();
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

        $("#tabulator-export-xlsx-VEXR").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "Visa_Expiry_Report.xlsx", {
                sheetName: "Visa Expiry Report",
            });
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

var passportExpiryListTable = (function () {
    var _tableGen = function () {
        let querystr = $("#query-PEXR").val() != "" ? $("#query-PEXR").val() : "";
        let tableContent = new Tabulator("#passportExpiryListTable", {
            ajaxURL: route("hr.portal.reports.eligibilityreport.passportexpirylist"),
            ajaxParams: { querystr: querystr},
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
                    title: "Employee Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Document Type",
                    field: "document_type",
                    headerHozAlign: "left",
                },
                {
                    title: "Document Number",
                    field: "doc_number",
                    headerHozAlign: "left",
                },
                {
                    title: "Document Expiry Date",
                    field: "doc_expire",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "days_remained",
                    headerHozAlign: "left",
                    width: 150,
                }
            ],
            renderStarted:function(){
                $("#passportExpiryListTable .tabulator-footer").hide();
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

        $("#tabulator-export-xlsx-PEXR").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "Passport_Expiry_Report.xlsx", {
                sheetName: "Passport Expiry Report",
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
    if ($("#visaExpiryListTable").length) {
        visaExpiryListTable.init();
        

        // Filter function
        function filterTitleHTMLFormVEXR() {
            visaExpiryListTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-VEXR").on("click", function (event) {
            filterTitleHTMLFormVEXR();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-VEXR").on("click", function (event) {
            $("#query-VEXR").val("");
            filterTitleHTMLFormVEXR();
        });
    }

    if ($("#passportExpiryListTable").length) {
        passportExpiryListTable.init();
        

        // Filter function
        function filterTitleHTMLFormPEXR() {
            passportExpiryListTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-PEXR").on("click", function (event) {
            filterTitleHTMLFormPEXR();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-PEXR").on("click", function (event) {
            $("#query-PEXR").val("");
            filterTitleHTMLFormPEXR();
        });
    }
})();