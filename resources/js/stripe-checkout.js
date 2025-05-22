
import { createIcons, icons } from "lucide";

("use strict");

export async function initStripeCheckout(buttonId) {
    

    const payButton = document.getElementById(buttonId);
    if (!payButton) return console.error(`No button found with ID: ${buttonId}`);

    payButton.addEventListener("click", async () => {
        try {
            const res = await fetch(route('students.checkout.stripe.session'), {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    amount: document.getElementById("amount").value,
                    currency: document.getElementById("currency").value,
                    quantity: document.getElementById("quantity_without_free").value,
                    invoice_number: document.getElementById("invoice_number").value,
                })
            });

            const data = await res.json();
            const url = data.url;
            if (data.id) {
                
                window.location.href = url;
                //const stripe = await loadStripe(import.meta.env.VITE_STRIPE_KEY);
                //await stripe.redirectToCheckout({ sessionId: data.id });
            } else {
                console.error("Stripe session ID not found.");
            }
        } catch (err) {
            console.error("Stripe Checkout Error:", err);
        }
    });
}
  
(function(){

    const succModal = tailwind.Modal.getOrCreateInstance(document.getElementById("successModal"));
    const errorModal = tailwind.Modal.getOrCreateInstance(document.getElementById("errorModal"));
    //const agentRulesModal = tailwind.Modal.getOrCreateInstance(document.getElementById("agentRulesModal"));
    let payButton = document.getElementById('payButton')
    
    if(payButton != null || payButton != undefined){
        
        initStripeCheckout("payButton");
        console.log("initStripeCheckout");
    }
    

    


})();

