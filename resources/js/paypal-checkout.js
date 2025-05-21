import { loadStripe } from "@stripe/stripe-js";
import { createIcons, icons } from "lucide";

("use strict");


  
(function(){

    //const succModal = tailwind.Modal.getOrCreateInstance(document.getElementById("successModal"));
    //const errorModal = tailwind.Modal.getOrCreateInstance(document.getElementById("errorModal"));
    //const agentRulesModal = tailwind.Modal.getOrCreateInstance(document.getElementById("agentRulesModal"));
     
    
    document.querySelector('#paypalButton').addEventListener('click', async function () {
        // const selected = document.querySelector('input[name="payment_method"]:checked');
        // if (!selected || selected.value !== 'paypal') return;

        const response = await fetch(route('students.checkout.paypal.session'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ 
                    amount: document.getElementById("amount").value,
                    currency: document.getElementById("currency").value,
                    quantity: document.getElementById("quantity_without_free").value,
                    invoice_number: document.getElementById("invoice_number").value,


            }) // Use dynamic value as needed
        });

        const data = await response.json();
        if (data.approval_url) {
            window.location.href = data.approval_url;
        } else {
            alert('PayPal order creation failed.');
        }
    });
    

    


})();

