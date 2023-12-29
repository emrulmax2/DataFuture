import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");

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
    $("#tabulator-html-filter-reset-RCD").on("click", function (event) {
        $("#employee_work_type_id-recordcard").val('');
        $("#department_id-recordcard").val('');
        $("#ethnicity-recordcard").val('');
        $("#nationality-recordcard").val('');
        $("#gender-recordcard").val('');
        $("#startdate-recordcard").val('');
        $("#enddate-recordcard").val('');
        $("#status_id-recordcard").val('1');
        document.getElementById("allRecordCardExcelBtn").style.display="block";
        document.getElementById("allRecordCardPdfBtn").style.display="block";

        document.getElementById("recordcardbySearchExcelBtn").style.display="none";
        document.getElementById("recordcardbySearchPdfBtn").style.display="none";
        $("div .recordcardBySearchData").hide();
        document.getElementById("tabulator-html-filter-go-RCD").click();
    });

    $("#tabulator-html-filter-go-RCD").on("click", function (e) {      
        e.preventDefault();
        let startdate = $("#startdate-recordcard").val() != "" ? $("#startdate-recordcard").val() : "";
        let enddate = $("#enddate-recordcard").val() != "" ? $("#enddate-recordcard").val() : "";
        let worktype = $("#employee_work_type_id-recordcard").val() != "" ? $("#employee_work_type_id-recordcard").val() : "";
        let department = $("#department_id-recordcard").val() != "" ? $("#department_id-recordcard").val() : "";
        let ethnicity = $("#ethnicity-recordcard").val() != "" ? $("#ethnicity-recordcard").val() : "";
        let nationality = $("#nationality-recordcard").val() != "" ? $("#nationality-recordcard").val() : "";
        let gender = $("#gender-recordcard").val() != "" ? $("#gender-recordcard").val() : "";
        let status = $("#status_id-recordcard").val() != "" ? $("#status_id-recordcard").val() : "";
        
        axios({
            method: "get",
            url: route("hr.portal.reports.recordcard.list"),
            params: {
                startdate: startdate, worktype:worktype, department:department, ethnicity:ethnicity, nationality:nationality, gender:gender, enddate:enddate, status:status
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        })
        .then((response) => {
            $("div .recordcardAllData").hide();
            document.getElementById("recordcardBySearchData").style.display="block";
            document.getElementById("allRecordCardExcelBtn").style.display="none";
            document.getElementById("allRecordCardPdfBtn").style.display="none";

            document.getElementById("recordcardbySearchExcelBtn").style.display="block";
            document.getElementById("recordcardbySearchPdfBtn").style.display="block";
            let dataset = response.data.res;
            let html = "";
            dataset.forEach((item, index) => {
                for (let key in item) {
                    //console.log(key, item[key]);
                    html +=`<div class="col-span-12">
                                <div class="grid grid-cols-12">
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
            $("#recordcardBySearchDataGrid").html(html);
        })
        .catch((error) => {
            console.log(error);
        });
    });

    $("#recordcardbySearchExcelBtn").on("click", function (e) {      
        e.preventDefault();
        let startdate = $("#startdate-recordcard").val() != "" ? $("#startdate-recordcard").val() : "";
        let enddate = $("#enddate-recordcard").val() != "" ? $("#enddate-recordcard").val() : "";
        let worktype = $("#employee_work_type_id-recordcard").val() != "" ? $("#employee_work_type_id-recordcard").val() : "";
        let department = $("#department_id-recordcard").val() != "" ? $("#department_id-recordcard").val() : "";
        let ethnicity = $("#ethnicity-recordcard").val() != "" ? $("#ethnicity-recordcard").val() : "";
        let nationality = $("#nationality-recordcard").val() != "" ? $("#nationality-recordcard").val() : "";
        let gender = $("#gender-recordcard").val() != "" ? $("#gender-recordcard").val() : "";
        let status = $("#status_id-recordcard").val() != "" ? $("#status_id-recordcard").val() : "";
        
        axios({
            method: "get",
            url: route("hr.portal.reports.recordcardbysearch.excel"),
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
                link.setAttribute('download', 'Employee Record Card.xlsx'); 
                document.body.appendChild(link);
                link.click();
                
        })
        .catch((error) => {
                console.log(error);
        });
    });

    $("#recordcardbySearchPdfBtn").on("click", function (e) {      
        e.preventDefault();
        let startdate = $("#startdate-recordcard").val() != "" ? $("#startdate-recordcard").val() : "";
        let enddate = $("#enddate-recordcard").val() != "" ? $("#enddate-recordcard").val() : "";
        let worktype = $("#employee_work_type_id-recordcard").val() != "" ? $("#employee_work_type_id-recordcard").val() : "";
        let department = $("#department_id-recordcard").val() != "" ? $("#department_id-recordcard").val() : "";
        let ethnicity = $("#ethnicity-recordcard").val() != "" ? $("#ethnicity-recordcard").val() : "";
        let nationality = $("#nationality-recordcard").val() != "" ? $("#nationality-recordcard").val() : "";
        let gender = $("#gender-recordcard").val() != "" ? $("#gender-recordcard").val() : "";
        let status = $("#status_id-recordcard").val() != "" ? $("#status_id-recordcard").val() : "";
        
        axios({
            method: "get",
            url: route("hr.portal.reports.recordcardbysearch.pdf"),
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
                link.setAttribute('download', 'Employee Record Card.pdf');
                document.body.appendChild(link);
                link.click();
                
        })
        .catch((error) => {
                console.log(error);
        });
    });
})();