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
use Illuminate\Http\Client\RequestException;

class ProcessAddressLsoa21 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Optional address id to process. If null, the job will enqueue per-address jobs.
     * @var int|null
     */
    public $addressId;

    protected function processSingle(int $addressId): void
    {
        // keep backward-compatible behaviour: use DB lookup if postcode wasn't passed in
        $this->processSingleReturn($addressId, null);
    }

    /**
     * Process a single address and return the resolved LSOA value (or null).
     * This method allows callers to reuse the resolved value (cache) when running sync.
     *
     * @param int $addressId
     * @param string|null $providedPostcode
     * @return string|null
     */
    protected function processSingleReturn(int $addressId, ?string $providedPostcode = null): ?string
    {
        try {
            if ($providedPostcode !== null) {
                $postcode = preg_replace('/\s+/', '', (string)$providedPostcode);
            } else {
                $address = Address::find($addressId);
                if (!$address || empty($address->post_code)) {
                    return null;
                }
                $postcode = preg_replace('/\s+/', '', (string)$address->post_code);
            }

            if (empty($postcode)) {
                return null;
            }

            $url = "https://api.postcodes.io/postcodes/".urlencode($postcode);

            try {
                $response = Http::timeout(5)->retry(3, 100)->get($url);
            } catch (RequestException $e) {
                $status = $e->response?->status() ?? null;
                Log::warning('Postcode lookup failed (exception)', ['address_id' => $addressId, 'postcode' => $postcode, 'status' => $status, 'message' => $e->getMessage()]);
                return null;
            }

            if ($response->successful() && $response->json('status') === 200) {
                $result = $response->json('result');
                $losa = $result['codes']['lsoa21'] ?? ($result['lsoa21'] ?? null);

                // only update if we have a value
                if (!is_null($losa)) {
                    try {
                        Address::where('id', $addressId)->update(['lsoa_21' => $losa]);
                    } catch (\Throwable $e) {
                        Log::warning('Failed to update address', ['address_id' => $addressId, 'postcode' => $postcode, 'error' => $e->getMessage()]);
                    }
                    return $losa;
                }
            } else {
                Log::warning('Postcode lookup failed', ['address_id' => $addressId, 'postcode' => $postcode, 'status' => $response->status()]);
            }
        } catch (\Throwable $e) {
            Log::error('Error processing address lsoa_21', ['address_id' => $addressId, 'error' => $e->getMessage()]);
            return null;
        }

        return null;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(?int $addressId = null, ?string $postCode = null)
    {
        $this->addressId = $addressId;
        $this->postCode = $postCode;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // If an address id was provided, process that single address.
        // Otherwise dispatch one job per address missing `lsoa_21`.
        if (!empty($this->addressId)) {
            $this->processSingle((int)$this->addressId);
            return;
        }

        // Dispatch jobs for all addresses that have a postcode but missing `lsoa_21`
        $query = Address::whereNotNull('post_code')
            ->where(function($q){
                $q->whereNull('lsoa_21')->orWhere('lsoa_21', '');
            })
            ->select(['id','post_code']);

        $isSync = config('queue.default') === 'sync';

        // in-memory cache for synchronous runs to avoid duplicate postcode lookups
        $cache = [];

        $query->chunkById(100, function($addresses) use ($isSync, &$cache) {
            foreach($addresses as $a){
                $rawPost = $a->post_code ?? '';
                $normalized = preg_replace('/\s+/', '', (string)$rawPost);

                if (empty($normalized)) {
                    continue;
                }

                if ($isSync) {
                    // process inline; reuse cache if same postcode seen earlier in this run
                    if (array_key_exists($normalized, $cache)) {
                        // we already have a value or null recorded; if value exists update DB
                        $losa = $cache[$normalized];
                            if (!is_null($losa)) {
                            try {
                                Address::where('id', $a->id)->update(['lsoa_21' => $losa]);
                            } catch (\Throwable $e) {
                                Log::warning('Failed to update address with cached lsoa', ['address_id' => $a->id, 'postcode' => $normalized, 'error' => $e->getMessage()]);
                            }
                        }
                        continue;
                    }

                    // call processSingle synchronously and cache result
                    try {
                        $losa = $this->processSingleReturn($a->id, $normalized);
                        $cache[$normalized] = $losa;
                    } catch (\Throwable $e) {
                        Log::warning('Synchronous processing failed for address', ['address_id' => $a->id, 'postcode' => $normalized, 'error' => $e->getMessage()]);
                        $cache[$normalized] = null;
                    }
                } else {
                    // dispatch per-address job, passing postcode to avoid extra DB lookup in worker
                    try {
                        self::dispatch($a->id, $normalized);
                    } catch (\Throwable $e) {
                        Log::warning('Failed to dispatch per-address job', ['address_id' => $a->id, 'error' => $e->getMessage()]);
                        // continue with next address
                    }
                }
            }
        });
    }

    // processSingle now delegates to processSingleReturn for unified behaviour
}
