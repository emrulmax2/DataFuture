<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessAddressLsoa21;

class ProcessAddressLsoa21Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'address:process-lsoa21';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch ProcessAddressLsoa21 job to update lsoa_21 for addresses';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        ProcessAddressLsoa21::dispatch();
        $this->info('ProcessAddressLsoa21 dispatched.');
        return 0;
    }
}
