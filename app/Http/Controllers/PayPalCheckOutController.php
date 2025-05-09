<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PayPalCheckOutController extends Controller
{
    private $clientId;
    private $secret;
    private $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->secret = config('services.paypal.secret');
        $this->baseUrl = config('services.paypal.sandbox') ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }

    private function getAccessToken()
    {
        $response = Http::withBasicAuth($this->clientId, $this->secret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        return $response['access_token'];
    }

    public function createOrder(Request $request)
    {
        $accessToken = $this->getAccessToken();
        $invoiceNumber = $request->invoice_number; // e.g. "INV-1001"
        $amount = $request->amount /100 ;               // e.g. "49.99"
        $quantity = $request->quantity;    
        $currency = $request->currency ;                     // e.g. 2
        $itemName = "Student Document Request";          // e.g. "My Product"

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                            'purchase_units' => [[
                                'invoice_id' => $invoiceNumber, // Add invoice number
                                'items' => [[
                                    'name' => $itemName,
                                    'quantity' => (string) $quantity,
                                    'unit_amount' => [
                                        'currency_code' => 'GBP',
                                        'value' => 10.00 // unit price
                                    ]
                                ]],
                                'amount' => [
                                    'currency_code' => 'GBP',
                                    'value' => $amount,
                                    'breakdown' => [
                                        'item_total' => [
                                            'currency_code' => 'GBP',
                                            'value' => $amount
                                        ]
                                    ]
                                ]
                            ]],
                'application_context' => [
                    'return_url' => route('students.checkout.paypal.success'),
                    'cancel_url' => route('students.checkout.paypal.cancel')
                ]
            ]);

        foreach ($response['links'] as $link) {
            if ($link['rel'] === 'approve') {
                return response()->json(['approval_url' => $link['href']]);
            }
        }

        return response()->json(['error' => 'Unable to create PayPal order'], 500);
    }

    public function success(Request $request)
    {
        $accessToken = $this->getAccessToken();

        $orderId = $request->query('token');

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

        // You should verify and store transaction details here
        return view('checkout.success', ['details' => $response->json()]);
    }

    public function cancel()
    {
        return view('checkout.cancel');
    }
}
