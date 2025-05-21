import { loadStripe } from "https://esm.sh/@stripe/stripe-js";
import { createIcons, icons } from "lucide";

// ("use strict");

// export async function initStripeCheckout(buttonId) {
//     const stripe = await loadStripe(import.meta.env.VITE_STRIPE_KEY);

//     const payButton = document.getElementById(buttonId);
//     if (!payButton) return console.error(`No button found with ID: ${buttonId}`);
    
//     payButton.addEventListener("click", async () => {
//         try {
//             const res = await fetch(route('students.checkout.stripe.session'), {
//                 method: "POST",
//                 headers: {
//                     "Content-Type": "application/json",
//                     "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
//                 },
//                 body: JSON.stringify({
//                     amount: payButton.dataset.amount,
//                     currency: payButton.dataset.currency,
//                     quantity: payButton.dataset.quantityWihoutFree,
//                     invoice_number: payButton.dataset.invoiceNumber,
//                 })
//             });

//             const data = await res.json();
//             if (data.id) {
//                 await stripe.redirectToCheckout({ sessionId: data.id });
//             } else {
//                 console.error("Stripe session ID not found.");
//             }
//         } catch (err) {
//             console.error("Stripe Checkout Error:", err);
//         }
//     });
// }
  
// (function(){

//     $(".payByCard").on("click", function (event) {
//         event.preventDefault();
//         const $this = $(this);
//         const buttonId = $this.attr("id");
//         $('.loadingIcon',$this).removeClass('hidden');
//         initStripeCheckout(buttonId);
        

//     });

// })();

("use strict");

(async function() {
    const stripe = await loadStripe(import.meta.env.VITE_STRIPE_KEY);

    // Attach click event to all buttons with class "payByCard"
    document.querySelectorAll(".payByCard").forEach((payButton) => {
        payButton.addEventListener("click", async (event) => {
            event.preventDefault();

            // Optional: show loading icon
            const loadingIcon = payButton.querySelector(".loadingIcon");
            if (loadingIcon) loadingIcon.classList.remove("hidden");

            try {
                const res = await fetch(route('students.checkout.stripe.session'), {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        amount: payButton.dataset.amount,
                        currency: payButton.dataset.currency,
                        quantity: payButton.dataset.quantityWihoutFree,  // note: typo? see below
                        invoice_number: payButton.dataset.invoiceNumber,
                    })
                });

                const data = await res.json();
                if (data.id) {
                    await stripe.redirectToCheckout({ sessionId: data.id });
                } else {
                    console.error("Stripe session ID not found.");
                }
            } catch (err) {
                console.error("Stripe Checkout Error:", err);
            }
        });
    });
})();
