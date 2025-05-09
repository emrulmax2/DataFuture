import { createIcons, icons } from "lucide";

("use strict");

(async function() {


    // Attach click event to all buttons with class "payByCard"
    document.querySelectorAll(".payByPayPal").forEach((payButton) => {
        payButton.addEventListener("click", async (event) => {
            event.preventDefault();

            // Optional: show loading icon
            const loadingIcon = payButton.querySelector(".loadingIcon");
            if (loadingIcon) loadingIcon.classList.remove("hidden");

            try {
                const response = await fetch(route('students.checkout.paypal.session'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({ 
                            amount: payButton.dataset.amount,
                            currency: payButton.dataset.currency,
                            quantity: payButton.dataset.quantityWihoutFree,
                            invoice_number: payButton.dataset.invoiceNumber,
                    }) // Use dynamic value as needed
                });

                const data = await response.json();
                if (data.approval_url) {
                    window.location.href = data.approval_url;
                } else {
                    alert('PayPal order creation failed.');
                }

            } catch (err) {
                console.error("PayPal Checkout Error:", err);
            }
        });
    });
})();
