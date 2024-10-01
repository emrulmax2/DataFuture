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

    var rtn_semester_id = new TomSelect('#rtn_semester_id', dueTomOptionsMul);
    $('#rtn_semester_id').on('change', function(){
        $('#printPdfRetentionRateBtn, #exportRetentionRateBtn').attr('href', 'javascript:void(0);').fadeOut();
        $('#retentionRateWrap').fadeOut().html('');
    });

    $('#retentionRateSearchForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('retentionRateSearchForm');
        let rtn_semester_id = $form.find('#rtn_semester_id').val();
        
        if(rtn_semester_id.length > 0){
            $form.find('.error-rtn_semester_id').html('')
            document.querySelector('#retentionRateBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#retentionRateBtn svg").style.cssText ="display: inline-block;";
            $('#printPdfRetentionRateBtn, #exportRetentionRateBtn').attr('href', 'javascript:void(0);').fadeOut();
            $('#retentionRateWrap').fadeOut().html('');

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('reports.intake.performance.get.retention.report'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#retentionRateBtn').removeAttribute('disabled');
                document.querySelector("#retentionRateBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    let pdf_url = route('reports.intake.performance.print.retention.rate', rtn_semester_id.join('_'));
                    let excel_url = route('reports.intake.performance.export.retention.rate', rtn_semester_id.join('_'));
                    $('#retentionRateWrap').fadeIn().html(response.data.htm);
                    $('#printPdfRetentionRateBtn').attr('href', pdf_url).fadeIn();
                    $('#exportRetentionRateBtn').attr('href', excel_url).fadeIn();

                    setTimeout(() => {
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }, 10);
                }
            }).catch(error => {
                document.querySelector('#retentionRateBtn').removeAttribute('disabled');
                document.querySelector("#retentionRateBtn svg").style.cssText = "display: none;";
                $('#printPdfRetentionRateBtn, #exportRetentionRateBtn').attr('href', 'javascript:void(0);').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $form.find('.error-rtn_semester_id').html('Semesters can not be empty.');
            $('#retentionRateWrap').fadeOut().html('');
            $('#printPdfRetentionRateBtn, #exportRetentionRateBtn').attr('href', 'javascript:void(0);').fadeOut();
        }
    });

    
    $('#retentionRateWrap').on('click', '.semisterToggle', function(e){
        e.preventDefault();
        var semesterId = $(this).attr('data-semesterid');
        $('.courseRow_'+semesterId).fadeToggle();
    })
    

})()