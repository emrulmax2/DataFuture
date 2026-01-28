<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessAddressLosa21;

class ProcessAddressLosa21Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'address:process-losa21';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch ProcessAddressLosa21 job to update losa_21 for addresses';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        ProcessAddressLosa21::dispatch();
        $this->info('ProcessAddressLosa21 dispatched.');
        return 0;
    }
}
