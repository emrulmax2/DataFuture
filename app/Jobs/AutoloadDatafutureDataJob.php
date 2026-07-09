<?php

namespace App\Jobs;

use App\Http\Controllers\Reports\DatafutureReportController;
use App\Models\DatafutureAutoloadJob as AutoloadRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoloadDatafutureDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $autoload_id;

    public $timeout = 7200;

    public function __construct($autoload_id)
    {
        $this->autoload_id = $autoload_id;
    }

    public function handle(): void
    {
        $record = AutoloadRecord::find($this->autoload_id);

        if (!$record) {
            return;
        }

        try {
            $record->update([
                'status' => 'processing',
                'progress' => 0,
            ]);

            // The heavy reset + reload loop lives in the controller so the
            // existing resetCourseSessions() / autoLoadStudentStuloads() helpers
            // stay reusable. It updates progress on the record as it goes.
            $count = app(DatafutureReportController::class)->processAutoload($record);

            $record->update([
                'status' => 'completed',
                'progress' => 100,
                'message' => '<strong>' . $count . '</strong> students data successfully autoloaded.',
            ]);
        } catch (\Throwable $e) {
            $record->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Mark the record failed if the job errors out / times out entirely.
     */
    public function failed(\Throwable $e): void
    {
        $record = AutoloadRecord::find($this->autoload_id);
        if ($record && $record->status !== 'completed') {
            $record->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
