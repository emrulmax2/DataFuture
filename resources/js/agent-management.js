import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";


(function(){
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        maxOptions: null,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let semister_id = new TomSelect('#semister_id', tomOptions);

    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const agentRulesModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#agentRulesModal"));

    const agentRulesModalEl = document.getElementById('agentRulesModal')
    agentRulesModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#agentRulesModal .acc__input-error').html('');
        $('#agentRulesModal .modal-body input:not([type="checkbox"]):not([type="radio"])').val('');
        $('#agentRulesModal .modal-body select').val('');
        $('#agentRulesModal [name="payment_type"]').prop('checked', false);

        $('#agentRulesModal .fixedAmountWrap').fadeOut('fast', function(e){
            $('input', this).val('');
        })
        $('#agentRulesModal .percentageWrap').fadeOut('fast', function(e){
            $('input', this).val('');
        })

        $('#agentRulesModal input[name="agent_user_id"]').val('0');
        $('#agentRulesModal input[name="code"]').val('');
        $('#agentRulesModal input[name="semester_id"]').val('0');
    });

    $('#tabulator-html-filter-reset').on('click', function(e){
        e.preventDefault();
        semister_id.clear(true);
        $('.agentRefListWrap').fadeOut().html('')
    })

    $('#tabulator-html-filter-go').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var semester_id = $('#semister_id').val();

        $theBtn.attr('disabled', 'disabled');
        $theBtn.find('svg.theLoader').fadeIn();

        if(semester_id > 0){
            axios({
                method: "post",
                url: route('agent.management.list.details'),
                data: {semester_id : semester_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $theBtn.removeAttr('disabled');
                $theBtn.find('svg.theLoader').fadeOut();
                
                if (response.status == 200) {
                    $('.agentRefListWrap').fadeIn().html(response.data.html);

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error => {
                $theBtn.removeAttr('disabled');
                $theBtn.find('svg.theLoader').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $theBtn.removeAttr('disabled');
            $theBtn.find('svg.theLoader').fadeOut();
            $('.agentRefListWrap').fadeOut().html('')
        }
    });

    $('.agentRefListWrap').on('click', '#referralCountTable tr.result_row', function(e){
        e.preventDefault();
        var $theTr = $(this);
        var semester_id = $theTr.attr('data-semester');

        $('.agentRefListWrap').addClass('loading');
        axios({
            method: "post",
            url: route('agent.management.list.details'),
            data: {semester_id : semester_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('.agentRefListWrap').removeClass('loading');
            
            if (response.status == 200) {
                $('.agentRefListWrap').html(response.data.html)

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }
        }).catch(error => {
            $('.agentRefListWrap').removeClass('loading');
            if (error.response) {
                console.log('error');
            }
        });
    })

    $('.agentRefListWrap').on('click', '.theRuleBtn', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var code = $theBtn.attr('data-code');
        var agent_user_id = $theBtn.attr('data-agent');
        var semester_id = $theBtn.attr('data-semester');

        axios({
            method: "post",
            url: route("agent.management.get.rule"),
            data: {code : code, agent_user_id : agent_user_id, semester_id : semester_id},
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let row = response.data.row;
                
                agentRulesModal.show();
                document.getElementById("agentRulesModal").addEventListener("shown.tw.modal", function (event) {
                    $('#agentRulesModal [name="comission_mode"]').val(row.comission_mode ? row.comission_mode : '');
                    if(row.comission_mode == 1){
                        $('#agentRulesModal .fixedAmountWrap').fadeOut('fast', function(e){
                            $('input', this).val('');
                        })
                        $('#agentRulesModal .percentageWrap').fadeIn('fast', function(e){
                            $('input', this).val(row.percentage ? row.percentage : '');
                        })

                        $('#agentRulesModal #payment_type_2').removeAttr('disabled').prop('checked', true);
                        $('#agentRulesModal #payment_type_1').attr('disabled', 'disabled');
                    }else if(row.comission_mode == 2){
                        $('#agentRulesModal .percentageWrap').fadeOut('fast', function(e){
                            $('input', this).val('');
                        })
                        $('#agentRulesModal .fixedAmountWrap').fadeIn('fast', function(e){
                            $('input', this).val(row.amount ? row.amount : '');
                        })
                        $('#agentRulesModal #payment_type_1').removeAttr('disabled').prop('checked', true);
                        $('#agentRulesModal #payment_type_2').attr('disabled', 'disabled');
                    }else{
                        $('#agentRulesModal .fixedAmountWrap').fadeOut('fast', function(e){
                            $('input', this).val('');
                        })
                        $('#agentRulesModal .percentageWrap').fadeOut('fast', function(e){
                            $('input', this).val('');
                        })
                        $('#agentRulesModal input[name="payment_type"]').prop('checked', false).attr('disabled', 'disabled');
                    }
                    $('#agentRulesModal [name="period"]').val(row.period ? row.period : '');

                    $('#agentRulesModal input[name="agent_user_id"]').val(agent_user_id);
                    $('#agentRulesModal input[name="code"]').val(code);
                    $('#agentRulesModal input[name="semester_id"]').val(semester_id);
                });
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#agentRulesModal #comission_mode').on('change', function(e){
        var $theMode = $(this);
        var theMode = $theMode.val();

        if(theMode == 1){
            $('#agentRulesModal .fixedAmountWrap').fadeOut('fast', function(e){
                $('input', this).val('');
            })
            $('#agentRulesModal .percentageWrap').fadeIn('fast', function(e){
                $('input', this).val('');
            })

            $('#agentRulesModal #payment_type_2').removeAttr('disabled').prop('checked', true);
            $('#agentRulesModal #payment_type_1').attr('disabled', 'disabled');
        }else if(theMode == 2){
            $('#agentRulesModal .percentageWrap').fadeOut('fast', function(e){
                $('input', this).val('');
            })
            $('#agentRulesModal .fixedAmountWrap').fadeIn('fast', function(e){
                $('input', this).val('');
            });
            $('#agentRulesModal #payment_type_1').removeAttr('disabled').prop('checked', true);
            $('#agentRulesModal #payment_type_2').attr('disabled', 'disabled');
        }else{
            $('#agentRulesModal .fixedAmountWrap').fadeOut('fast', function(e){
                $('input', this).val('');
            })
            $('#agentRulesModal .percentageWrap').fadeOut('fast', function(e){
                $('input', this).val('');
            });
            $('#agentRulesModal input[name="payment_type"]').prop('checked', false).attr('disabled', 'disabled');
        }
    });

    $('#agentRulesForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('agentRulesForm');

        var agent_user_id = $form.find('[name="agent_user_id"]').val();
        var semester_id = $form.find('[name="semester_id"]').val();
        var $viewBtn = $('.agentRefListWrap').find('#comission_view_'+semester_id+'_'+agent_user_id);
    
        document.querySelector('#saveRuleBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveRuleBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('agent.management.store.rule'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveRuleBtn').removeAttribute('disabled');
            document.querySelector("#saveRuleBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                agentRulesModal.hide();
                $viewBtn.removeClass('hidden');

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!");
                    $("#successModal .successModalDesc").html('Agent comission rule successfully stored.');
                });                
                    
            }
        }).catch(error => {
            document.querySelector('#saveRuleBtn').removeAttribute('disabled');
            document.querySelector("#saveRuleBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#agentRulesForm .${key}`).addClass('border-danger')
                        $(`#agentRulesForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
})()