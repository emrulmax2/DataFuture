
import TomSelect from "tom-select";
import { saveAs } from 'file-saver';
("use strict");


(function(){
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const xmlExportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#xmlExportModal"));

    let stdDFLitepicker = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        format: "YYYY-MM-DD",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    let tomOptionsSDF = {
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

    let tomOptionsSDFNew = {
        ...tomOptionsSDF,
        allowEmptyOption: false,
        plugins: {
            ...tomOptionsSDF.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    }


    const termsDeclarationId = new TomSelect('#terms_declaration_id', tomOptionsSDFNew);

    // To get selected value(s)
    let selectedValues = termsDeclarationId.getValue(); // returns value or array



    if($('#from_date').length > 0){
        stdDFLitepicker.format = 'DD-MM-YYYY';
        $('#from_date').each(function(){
            new Litepicker({
                element: this,
                ...stdDFLitepicker,
            });
        })
    }

    if($('#to_date').length > 0){
        stdDFLitepicker.format = 'DD-MM-YYYY';
        $('#to_date').each(function(){
            new Litepicker({
                element: this,
                ...stdDFLitepicker,
            });
        })
    }

    

    $('#xmlExportForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('xmlExportForm');

        let term_declaration_ids = $('#terms_declaration_id', $form).val();
        let from_date = $('#from_date', $form).val();
        let to_date = $('#to_date', $form).val();

        $('#xmlDownloadWrap').addClass('hidden').attr('href', '#');
        $('#xmlProgressWrap').addClass('hidden');
        $('#xmlProgressText').text('0%');
        $('#xmlProgressBar').css('width', '0%');
        document.querySelector('#xmlDownCancelBtn').setAttribute('disabled', 'disabled');
        document.querySelector('#xmlDownBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#xmlDownBtn .theLoader").style.cssText ="display: inline-block;";

        if((term_declaration_ids.length  > 0 || (from_date != '' && to_date != ''))){
            let form_data = new FormData(form);
            axios({
                method: 'post',
                url: route('reports.datafuture.start.process.multiple.student'),
                data: form_data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }).then(response => {
                console.log(response.data);

                $('#xmlProgressWrap').removeClass('hidden');
                if(response.data.export_id){
                    startCheckingFile(response.data.export_id);
                }

            }).catch(error => {
                $('#xmlDownloadWrap').addClass('hidden').attr('href', '#');
                $('#xmlProgressWrap').addClass('hidden');
                $('#xmlProgressText').text('0%');
                $('#xmlProgressBar').css('width', '0%');
                document.querySelector('#xmlDownCancelBtn').removeAttribute('disabled');
                document.querySelector('#xmlDownBtn').removeAttribute('disabled');
                document.querySelector("#xmlDownBtn .theLoader").style.cssText = "display:none;";

                console.log(error);
            });
        }else{
            document.querySelector('#xmlDownCancelBtn').removeAttribute('disabled');
            document.querySelector('#xmlDownBtn').removeAttribute('disabled');
            document.querySelector("#xmlDownBtn .theLoader").style.cssText = "display: none;";

            $('#xmlExportModal .modal-content .submissionError').remove();
            $('#xmlExportModal .modal-content').prepend('<div class="alert submissionError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <span><strong>Validation Error</strong>. Select Term declaration or insert Form & To date.</span></div>');

            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
            setTimeout(function(){
                $('#xmlExportModal .modal-content .submissionError').remove();
            }, 2000)
        }
    });

    function startCheckingFile(export_id){
        let checkingInterval = setInterval(() => {
            axios.post(route('reports.datafuture.check.multiple.students.xml.status', export_id), {
                export_id: export_id
            }).then(response => {
                let progress = response.data.progress ?? 0;
                $('#xmlProgressBar').css('width', progress + '%');
                $('#xmlProgressText').text(progress + '%');

                if(response.data.status === 'completed'){
                    clearInterval(checkingInterval);

                    $('#xmlProgressWrap').addClass('hidden');
                    $('#xmlProgressText').text('0%');

                    $('#xmlDownloadWrap').removeClass('hidden');
                    $('#xmlDownloadBtn').attr('href', response.data.file);

                    document.querySelector('#xmlDownCancelBtn').removeAttribute('disabled');
                    document.querySelector('#xmlDownBtn').removeAttribute('disabled');
                    document.querySelector("#xmlDownBtn .theLoader").style.cssText = "display:none;";
                }else if(response.data.status === 'failed'){
                    $('#xmlDownloadWrap').addClass('hidden').attr('href', '#');
                    $('#xmlProgressWrap').addClass('hidden');
                    $('#xmlProgressText').text('0%');
                    $('#xmlProgressBar').css('width', '0%');
                    document.querySelector('#xmlDownCancelBtn').removeAttribute('disabled');
                    document.querySelector('#xmlDownBtn').removeAttribute('disabled');
                    document.querySelector("#xmlDownBtn .theLoader").style.cssText = "display:none;";

                    $('#xmlExportModal .modal-content .submissionError').remove();
                    $('#xmlExportModal .modal-content').prepend('<div class="alert submissionError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <span><strong>Oops</strong>. Something went wrong. Please try again later or contact with the administrator.</span></div>');

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                    setTimeout(function(){
                        $('#xmlExportModal .modal-content .submissionError').remove();
                    }, 2000)
                }
            }).catch(error => {
                console.log(error);

                $('#xmlDownloadWrap').addClass('hidden').attr('href', '#');
                $('#xmlProgressWrap').addClass('hidden');
                $('#xmlProgressText').text('0%');
                $('#xmlProgressBar').css('width', '0%');
                document.querySelector('#xmlDownCancelBtn').removeAttribute('disabled');
                document.querySelector('#xmlDownBtn').removeAttribute('disabled');
                document.querySelector("#xmlDownBtn .theLoader").style.cssText = "display:none;";

                $('#xmlExportModal .modal-content .submissionError').remove();
                $('#xmlExportModal .modal-content').prepend('<div class="alert submissionError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <span><strong>Oops</strong>. Something went wrong. Please try again later or contact with the administrator.</span></div>');

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                setTimeout(function(){
                    $('#xmlExportModal .modal-content .submissionError').remove();
                }, 2000)
            });

        }, 3000);
    }
})();