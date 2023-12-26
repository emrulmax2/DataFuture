import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var contactListTable = (function () {
    var _tableGen = function () {
        let startdate = $("#startdate-contact").val() != "" ? $("#startdate-contact").val() : "";
        let enddate = $("#enddate-contact").val() != "" ? $("#enddate-contact").val() : "";
        let worktype = $("#employee_work_type_id-contact").val() != "" ? $("#employee_work_type_id-contact").val() : "";
        let department = $("#department_id-contact").val() != "" ? $("#department_id-contact").val() : "";
        let ethnicity = $("#ethnicity-contact").val() != "" ? $("#ethnicity-contact").val() : "";
        let nationality = $("#nationality-contact").val() != "" ? $("#nationality-contact").val() : "";
        let gender = $("#gender-contact").val() != "" ? $("#gender-contact").val() : "";
        let status = $("#status_id-contact").val() != "" ? $("#status_id-contact").val() : "";

        let tableContent = new Tabulator("#contactListTable", {
            ajaxURL: route("hr.portal.reports.contactdetail.list"),
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
                    title:"", field:"name",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        return '<div class="col-sfont-bold whitespace-normal text-sm">Name: '+cell.getData().name+'</div>';
                    }
                },
                {
                    title:"", field:"address",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        return '<div class="col-sfont-bold whitespace-normal text-sm">Address: '+cell.getData().address+'</div>';
                    }
                },
                {
                    title:"", field:"post_code",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        return '<div class="col-sfont-bold whitespace-normal text-sm">Post Code: '+cell.getData().post_code+'</div>';
                    }
                },
                {
                    title:"", field:"telephone",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        return '<div class="col-sfont-bold whitespace-normal text-sm">Telephone: '+cell.getData().telephone+'</div>';
                    }
                },
                {
                    title:"", field:"mobile",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        return '<div class="col-sfont-bold whitespace-normal text-sm">Mobile: '+cell.getData().mobile+'</div>';
                    }
                },
                {
                    title:"", field:"email",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        return '<div class="col-sfont-bold whitespace-normal text-sm">Email: '+cell.getData().email+'</div>';
                    }
                },
                {
                    title:"", field:"emergency_telephone",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        return '<div class="col-sfont-bold whitespace-normal text-sm">E. Telephone: '+cell.getData().emergency_telephone+'</div>';
                    }
                },
                {
                    title:"", field:"emergency_mobile",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        return '<div class="col-sfont-bold whitespace-normal text-sm">E. Mobile: '+cell.getData().emergency_mobile+'</div>';
                    }
                },
                {
                    title:"", field:"emergency_email",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        return '<div class="col-sfont-bold whitespace-normal text-sm">E. Email: '+cell.getData().emergency_email+'</div>';
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

    // On reset filter form
    $("#tabulator-html-filter-reset-ECD").on("click", function (event) {
        $("div .contactAllData").show();
        $("div .contactBySearchData").hide();
        $("#employee_work_type_id-contact").val('');
        $("#department_id-contact").val('');
        $("#ethnicity-contact").val('');
        $("#nationality-contact").val('');
        $("#gender-contact").val('');
        $("#startdate-contact").val('');
        $("#enddate-contact").val('');
        $("#status_id-contact").val('1');
        document.getElementById("allContactExcelBtn").style.display="block";
        document.getElementById("allContactPdfBtn").style.display="block";

        document.getElementById("contactbySearchExcelBtn").style.display="none";
        document.getElementById("contactbySearchPdfBtn").style.display="none";
    });

    $("#contactbySearchExcelBtn").on("click", function (e) {      
        e.preventDefault();
        let startdate = $("#startdate-contact").val() != "" ? $("#startdate-contact").val() : "";
        let enddate = $("#enddate-contact").val() != "" ? $("#enddate-contact").val() : "";
        let worktype = $("#employee_work_type_id-contact").val() != "" ? $("#employee_work_type_id-contact").val() : "";
        let department = $("#department_id-contact").val() != "" ? $("#department_id-contact").val() : "";
        let ethnicity = $("#ethnicity-contact").val() != "" ? $("#ethnicity-contact").val() : "";
        let nationality = $("#nationality-contact").val() != "" ? $("#nationality-contact").val() : "";
        let gender = $("#gender-contact").val() != "" ? $("#gender-contact").val() : "";
        let status = $("#status_id-contact").val() != "" ? $("#status_id-contact").val() : "";
        
        axios({
            method: "get",
            url: route("hr.portal.reports.contactbysearch.excel"),
            params: {
                ethnicity:ethnicity, nationality:nationality, startdate:startdate, worktype:worktype, department:department, gender:gender, enddate:enddate, status:status
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
                link.setAttribute('download', 'Contact_Details.xlsx'); 
                document.body.appendChild(link);
                link.click();
                
        })
        .catch((error) => {
                console.log(error);
        });
    });

    $("#contactbySearchPdfBtn").on("click", function (e) {      
        e.preventDefault();
        let startdate = $("#startdate-contact").val() != "" ? $("#startdate-contact").val() : "";
        let enddate = $("#enddate-contact").val() != "" ? $("#enddate-contact").val() : "";
        let worktype = $("#employee_work_type_id-contact").val() != "" ? $("#employee_work_type_id-contact").val() : "";
        let department = $("#department_id-contact").val() != "" ? $("#department_id-contact").val() : "";
        let ethnicity = $("#ethnicity-contact").val() != "" ? $("#ethnicity-contact").val() : "";
        let nationality = $("#nationality-contact").val() != "" ? $("#nationality-contact").val() : "";
        let gender = $("#gender-contact").val() != "" ? $("#gender-contact").val() : "";
        let status = $("#status_id-contact").val() != "" ? $("#status_id-contact").val() : "";
        
        axios({
            method: "get",
            url: route("hr.portal.reports.contactbysearch.pdf"),
            params: {
                ethnicity:ethnicity, nationality:nationality, startdate:startdate, worktype:worktype, department:department, gender:gender, enddate:enddate, status:status
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
                link.setAttribute('download', 'Contact_Details.pdf');
                document.body.appendChild(link);
                link.click();
                
        })
        .catch((error) => {
                console.log(error);
        });
    });

    $("#tabulator-html-filter-go-ECD").on("click", function (e) {      
        e.preventDefault();
        let startdate = $("#startdate-contact").val() != "" ? $("#startdate-contact").val() : "";
        let enddate = $("#enddate-contact").val() != "" ? $("#enddate-contact").val() : "";
        let worktype = $("#employee_work_type_id-contact").val() != "" ? $("#employee_work_type_id-contact").val() : "";
        let department = $("#department_id-contact").val() != "" ? $("#department_id-contact").val() : "";
        let ethnicity = $("#ethnicity-contact").val() != "" ? $("#ethnicity-contact").val() : "";
        let nationality = $("#nationality-contact").val() != "" ? $("#nationality-contact").val() : "";
        let gender = $("#gender-contact").val() != "" ? $("#gender-contact").val() : "";
        let status = $("#status_id-contact").val() != "" ? $("#status_id-contact").val() : "";
        
        axios({
            method: "get",
            url: route("hr.portal.reports.contactdetail.list"),
            params: {
                startdate: startdate, worktype:worktype, department:department, ethnicity:ethnicity, nationality:nationality, gender:gender, enddate:enddate, status:status
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        })
        .then((response) => {
            $("div .contactAllData").hide();
            document.getElementById("contactBySearchData").style.display="block";
            document.getElementById("allContactExcelBtn").style.display="none";
            document.getElementById("allContactPdfBtn").style.display="none";

            document.getElementById("contactbySearchExcelBtn").style.display="block";
            document.getElementById("contactbySearchPdfBtn").style.display="block";

            let dataset = response.data.res;
            let html = "";
            dataset.forEach((item, index) => {
                for (let key in item) {
                    //console.log(key, item[key]);
                    html +=`<div class="col-span-12 sm:col-span-4">
                                <div class="grid grid-cols-12 gap-0">
                                    <div class="col-span-4 text-slate-500 font-medium">${
                                        key
                                    }</div>
                                    <div class="col-span-8 font-medium">${
                                        (item[key]) ? item[key] : ""
                                    }</div>
                                </div>
                            </div>`;
                }

                html +=`<div class="col-span-12">
                            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                        </div>`;
                
            }); 
            $("#contactBySearchDataGrid").html(html);
        })
        .catch((error) => {
            console.log(error);
        });
    });
})();