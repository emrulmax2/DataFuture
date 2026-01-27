<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Address;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessAddressLosa21 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Optional address id to process. If null, the job will enqueue per-address jobs.
     *
     * @var int|null
     */
    public $addressId;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $addressId = null)
    {
        $this->addressId = $addressId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // If an address id was provided, process that single address.
        // Otherwise dispatch one job per address missing `losa_21`.
        if (!empty($this->addressId)) {
            $this->processSingle((int)$this->addressId);
            return;
        }

        // Dispatch jobs for all addresses that have a postcode but missing losa_21
        Address::whereNotNull('post_code')
            ->where(function($q){
                $q->whereNull('losa_21')->orWhere('losa_21', '');
            })
            ->select('id')
            ->chunkById(100, function($addresses){
                foreach($addresses as $a){
                    // dispatch a job for each address to be processed by the queue
                    self::dispatch($a->id);
                }
            });
    }

    /**
     * Process a single address id: call postcodes.io and update `losa_21`.
     */
    protected function processSingle(int $addressId): void
    {
        try {
            $address = Address::find($addressId);
            if (!$address || empty($address->post_code)) {
                return;
            }

            // remove all whitespace characters from the postcode (internal and external)
            $postcode = preg_replace('/\s+/', '', (string)$address->post_code);
            $url = "https://api.postcodes.io/postcodes/".urlencode($postcode);

            $response = Http::timeout(5)->retry(3, 100)->get($url);

            if ($response->successful() && $response->json('status') === 200) {
                $result = $response->json('result');
                $losa = $result['codes']['lsoa21'] ?? ($result['lsoa21'] ?? null);

                // only update if we have a value
                if (!is_null($losa)) {
                    $address->losa_21 = $losa;
                    $address->save();
                }
            } else {
                Log::warning('Postcode lookup failed', ['address_id' => $addressId, 'postcode' => $postcode, 'status' => $response->status()]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing address losa_21', ['address_id' => $addressId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
