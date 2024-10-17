import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

(function(){
    let apAnlsTomOptions = {
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

    let apAnlsTomOptionsMul = {
        ...apAnlsTomOptions,
        plugins: {
            ...apAnlsTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var ap_an_semester_id = new TomSelect('#ap_an_semester_id', apAnlsTomOptions);
    $('#ap_an_semester_id').on('change', function(){
        $('#printPdfAplicntAnalysisBtn').attr('href', 'javascript:void(0);').fadeOut();
        $('#applicantAnalysisReptWrap').fadeOut().html('');
    });

    $('#applicantAnalysisReportForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('applicantAnalysisReportForm');
        let ap_an_semester_id = $form.find('#ap_an_semester_id').val();
        
        if(ap_an_semester_id > 0){
            $form.find('.error-ap_an_semester_id').html('')
            document.querySelector('#AplicntAnalysisReptBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#AplicntAnalysisReptBtn svg").style.cssText ="display: inline-block;";
            $('#printPdfAplicntAnalysisBtn').attr('href', 'javascript:void(0);').fadeOut();
            $('#applicantAnalysisReptWrap').fadeOut().html('');

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('reports.applicant.analysis.generate.report'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#AplicntAnalysisReptBtn').removeAttribute('disabled');
                document.querySelector("#AplicntAnalysisReptBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    //console.log(response.data);
                    //return false;
                    let pdf_url = route('reports.applicant.analysis.print.report', ap_an_semester_id);
                    $('#applicantAnalysisReptWrap').fadeIn().html(response.data.htm);
                    $('#printPdfAplicntAnalysisBtn').attr('href', pdf_url).fadeIn();

                    setTimeout(() => {
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }, 10);
                }
            }).catch(error => {
                document.querySelector('#AplicntAnalysisReptBtn').removeAttribute('disabled');
                document.querySelector("#AplicntAnalysisReptBtn svg").style.cssText = "display: none;";
                $('#printPdfAplicntAnalysisBtn').attr('href', 'javascript:void(0);').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $form.find('.error-ap_an_semester_id').html('Semesters can not be empty.');
            $('#applicantAnalysisReptWrap').fadeOut().html('');
            $('#printPdfAplicntAnalysisBtn').attr('href', 'javascript:void(0);').fadeOut();
        }
    });

})()