import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

(function(){
    let dueTomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let dueTomOptionsMul = {
        ...dueTomOptions,
        plugins: {
            ...dueTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var attendance_code_id = new TomSelect('#attendance_code_id', dueTomOptions);
    var attendance_year = new TomSelect('#attendance_year', dueTomOptions);
    var term_declaration_id = new TomSelect('#term_declaration_id', dueTomOptionsMul);
    
    $('#attendanceSLCReportForm').on('submit', function(e){

        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('attendanceSLCReportForm');

        let date_range = $form.find('#date_range').val();
        
        if(date_range.length > 0){
            $form.find('.error-atn_semester_id').html('')
            document.querySelector('#excelSubmitBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#excelSubmitBtn svg").style.cssText ="display: inline-block;";
            
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('reports.slc.attendance.excel.export'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                responseType: 'blob',
            }).then(response => {
                document.querySelector('#excelSubmitBtn').removeAttribute('disabled');
                document.querySelector("#excelSubmitBtn svg").style.cssText = "display: none;";

                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'slc_attendace_report.xlsx'); 
                document.body.appendChild(link);
                link.click();

            }).catch(error => {
                document.querySelector('#excelSubmitBtn').removeAttribute('disabled');
                document.querySelector("#excelSubmitBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    console.log('error');
                }
            });
            
        }else{
            $form.find('.error-date_range').html('Date Range can not be empty.');
            
        }
    });

    

    var academic_year_id = new TomSelect('#academic_year_id', dueTomOptions);
    var registration_year = new TomSelect('#registration_year', dueTomOptions);
    var slc_registration_status_id = new TomSelect('#slc_registration_status_id', dueTomOptions);

    $('#registrationSLCForm').on('submit', function(e) {

        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('registrationSLCForm');

        let date_range = $form.find('#date_range1').val();
        
        if(date_range.length > 0) {

            $form.find('.error-atn_semester_id').html('')
             document.querySelector('#excelSubmitBtn1').setAttribute('disabled', 'disabled');
             document.querySelector("#excelSubmitBtn1 svg").style.cssText ="display: inline-block;";
            

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('reports.slc.register.excel.export'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                responseType: 'blob',
            }).then(response => {
                document.querySelector('#excelSubmitBtn1').removeAttribute('disabled');
                document.querySelector("#excelSubmitBtn1 svg").style.cssText = "display: none;";

                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'slc_registration_report.xlsx'); 
                document.body.appendChild(link);
                link.click();

            }).catch(error => {
                document.querySelector('#excelSubmitBtn1').removeAttribute('disabled');
                document.querySelector("#excelSubmitBtn1 svg").style.cssText = "display: none;";
                if (error.response) {
                    console.log('error');
                }
            });
            
        }else{
            $form.find('.error-date_range').html('Date Range can not be empty.');
            
        }
    });


    var coc_type = new TomSelect('#coc_type', dueTomOptions);
    var actioned = new TomSelect('#actioned', dueTomOptions);

    $('#SLCcocReportForm').on('submit', function(e) {

        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('SLCcocReportForm');

        let date_range = $form.find('#date_range2').val();
        
        if(date_range.length > 0) {

             document.querySelector('#excelSubmitBtn3').setAttribute('disabled', 'disabled');
             document.querySelector("#excelSubmitBtn3 svg").style.cssText ="display: inline-block;";
            

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('reports.slc.coc.excel.export'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                responseType: 'blob',
            }).then(response => {
                document.querySelector('#excelSubmitBtn3').removeAttribute('disabled');
                document.querySelector("#excelSubmitBtn3 svg").style.cssText = "display: none;";

                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'slc_coc_history_report.xlsx'); 
                document.body.appendChild(link);
                link.click();

            }).catch(error => {
                document.querySelector('#excelSubmitBtn3').removeAttribute('disabled');
                document.querySelector("#excelSubmitBtn3 svg").style.cssText = "display: none;";
                if (error.response) {
                    console.log('error');
                }
            });
            
        }else{
            $form.find('.error-date_range').html('Date Range can not be empty.');
            
        }
    });


})()