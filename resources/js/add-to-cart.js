import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import {createApp} from 'vue'

import IMask from 'imask';
import { set } from "lodash";

("use strict");


(function(){

    const succModal = tailwind.Modal.getOrCreateInstance(document.getElementById("successModal"));
    const errorModal = tailwind.Modal.getOrCreateInstance(document.getElementById("errorModal"));
    //const agentRulesModal = tailwind.Modal.getOrCreateInstance(document.getElementById("agentRulesModal"));
     
    $('.add-topaid-cart').on('click', function(e){
        e.preventDefault();
        const form = $(this).closest('form'); // Finds the closest form element to the button

        
    
        // Disable the button and show loading spinner
        const $btn = $(this);
        $btn.attr('disabled', 'disabled');
        $btn.find('svg').css('display', 'inline-block');

        let service_type = $btn.data('service-type');
        let form_data = new FormData(form[0]);
        
        form_data.append("service_type", service_type);
        form_data.append("product_type", "Paid");
        axios({
            method: "post",
            url: route('students.shopping.cart.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            // Enable the button and hide the loading spinner
            $btn.removeAttr('disabled');
            $btn.find('svg').css('display', 'none');
            
            if (response.status == 200) {
                console.log(response);
                //agentRulesModal.hide();
                
                succModal.show();
                //$viewBtn.removeClass('hidden');

                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!");
                    $("#successModal .successModalDesc").html(response.data.message);
                });    
                
                setTimeout(() => {
                    succModal.hide();
                    window.location.reload();
                }, 2000);
                    
            }
        }).catch(error => {
            $btn.removeAttr('disabled');
            $btn.find('svg:nth-of-type(2)').css('display', 'none');
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#agentRulesForm .${key}`).addClass('border-danger')
                        $(`#agentRulesForm  .error-${key}`).html(val)
                    }
                }else if (error.response.status == 409) {
                    errorModal.show();
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .errorModalTitle").html("Error!");
                        $("#errorModal .errorModalDesc").html(error.response.data.message);
                    });
                } else {
                    console.log('error');
                }
            }
        });
    });
    

    $('.add-tofree-cart').on('click', function(e){
        e.preventDefault();
        const form = $(this).closest('form'); // Finds the closest form element to the button

        
    
        // Disable the button and show loading spinner
        const $btn = $(this);
        $btn.attr('disabled', 'disabled');
        $btn.find('svg:nth-of-type(2)').css('display', 'inline-block');

        let form_data = new FormData(form[0]);
        let service_type = $btn.data('service_type');
        form_data.append("service_type", service_type);
        form_data.append("product_type", "Free");
        
        axios({
            method: "post",
            url: route('students.shopping.cart.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            // Enable the button and hide the loading spinner
            $btn.removeAttr('disabled');
            $btn.find('svg:nth-of-type(2)').css('display', 'none');
            
            if (response.status == 200) {
                console.log(response);
                //agentRulesModal.hide();
                
                succModal.show();
                //$viewBtn.removeClass('hidden');

                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!");
                    $("#successModal .successModalDesc").html(response.data.message);
                });    
                
                setTimeout(() => {
                    succModal.hide();
                    window.location.reload();
                }, 2000);
                    
            }
        }).catch(error => {
            $btn.removeAttr('disabled');
            $btn.find('svg:nth-of-type(2)').css('display', 'none');
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(` .${key}`,form).addClass('border-danger')
                        $(`  .error-${key}`,form).html(val)
                    }
                }
                if(error.response.status == 400) {
                    errorModal.show();
                    document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                        $("#errorModal .errorModalTitle").html("Error!");
                        $("#errorModal .errorModalDesc").html(error.response.data.message);
                    });
                } else {
                    console.log('error');
                }
            }
        });
    });
})();

