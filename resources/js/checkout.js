//import { loadStripe } from '@stripe/stripe-js';
import { createIcons, icons } from "lucide";

("use strict");

// const amountInput = document.getElementById('amount');
// const amount = parseInt(amountInput.value);

// const currencyInput = document.getElementById('currency');
// const currency = currencyInput.value;

// const initStripe = async () => {
//     const stripe = await loadStripe(import.meta.env.VITE_STRIPE_KEY);
    
//     const response = await fetch('/api/create-payment-intent', {
//       method: 'POST',
//       headers: {
//         'Content-Type': 'application/json',
//         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
//       },
//       body: JSON.stringify({
//         amount: amount,
//         currency: currency,
//       })
//     });
  
//     const { clientSecret } = await response.json();
  
//     const elements = stripe.elements();
//     const cardElement = elements.create('card');
//     cardElement.mount('#card-element');
  
//     document.querySelector('#payment-form').addEventListener('submit', async (e) => {
//       e.preventDefault();
  
//       const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
//         payment_method: {
//           card: cardElement,
//         }
//       });
  
//       if (error) {
//         alert(error.message);
//       } else if (paymentIntent.status === 'succeeded') {
//         window.location.href = '/checkout/success';
//       }
      
//     });
//   };

(function(){

    const succModal = tailwind.Modal.getOrCreateInstance(document.getElementById("successModal"));
    const errorModal = tailwind.Modal.getOrCreateInstance(document.getElementById("errorModal"));
    
    // $("#checkoutForm input[name='payment_method']").on('change', function(e){
    //       e.preventDefault();
    //       const $choosed = $(this).prop('checked', true).val();
          
    //       if($choosed == 'Card') {
    //         initStripe();
    //       }
    // }); 
    $('#checkoutForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('checkoutForm');
        // Disable the button and show loading spinner
        const $btn = $("#saveBtn");
        $btn.attr('disabled', 'disabled');
        $btn.find('svg').css('display', 'inline-block');

        let form_data = new FormData(form);
        
        axios({
            method: "post",
            url: route('students.order.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            // Enable the button and hide the loading spinner
            $btn.removeAttr('disabled');
            $btn.find('svg').css('display', 'none');
            
            if (response.status == 200) {
                //console.log(response);
                //agentRulesModal.hide();
                
                //succModal.show();
                //$viewBtn.removeClass('hidden');

                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!");
                    $("#successModal .successModalDesc").html(response.data.message);
                });    

                if(response.data.order) {
                  document.getElementById('invoice_number').value = response.data.order.invoice_number;
                    
                    
                    

                    const selected = document.querySelector('input[name="payment_method"]:checked');

                    if (selected.value == 'Card') $("#payButton").trigger("click");
                    else $("#paypalButton").trigger("click");

                }
                
                // setTimeout(() => {
                //     succModal.hide();
                //     location.href = route('students.document-request-form.index');
                // }, 2000);
                    
            }
        }).catch(error => {
            $btn.removeAttr('disabled');
            $btn.find('svg:nth-of-type(2)').css('display', 'none');
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#checkoutForm .${key}`).addClass('border-danger')
                        $(`#checkoutForm  .error-${key}`).html(val)
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
    


})();

