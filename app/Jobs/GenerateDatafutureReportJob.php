<?php

namespace App\Jobs;

use App\Http\Controllers\Reports\DatafutureReportController;
use App\Models\DatafutureReportExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateDatafutureReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $export_id;

    public $timeout = 7200;

    // Don't silently re-run this expensive report on transient release/failure.
    public $tries = 1;

    public function __construct($export_id)
    {
        $this->export_id = $export_id;
    }

    /**
     * Ensures the export row isn't left stuck on "processing" if the job
     * fails terminally (timeout, MaxAttemptsExceeded, uncaught error, etc.).
     */
    public function failed(\Throwable $e): void
    {
        $export = DatafutureReportExport::find($this->export_id);

        if ($export && $export->status !== 'completed') {
            $export->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function handle(): void
    {
        $export = DatafutureReportExport::find($this->export_id);

        if(!$export){
            return;
        }

        try {

            $export->update([
                'status' => 'processing',
                'progress' => 10
            ]);

            $payload = $export->payload;

            // your XML logic
            $xmlContent = '<?xml version="1.0"?>';
            $xmlData = app(DatafutureReportController::class)->getMultipleStudentXml($payload);
            $xmlContent .= preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $xmlData);

            for($i = 10; $i <= 90; $i += 10){

                sleep(1);

                $export->update([
                    'progress' => $i
                ]);
            }

            $path = 'temp_xml/'.$export->file_name;

            Storage::disk('public')->put($path, $xmlContent);

            $export->update([
                'status' => 'completed',
                'progress' => 100,
                'file_path' => $path
            ]);

        } catch (\Throwable $e) {

            $export->update([
                'status' => 'failed',
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}