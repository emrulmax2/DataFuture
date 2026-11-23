<?php

namespace App\Console\Commands;

use App\Models\StudentOrder;
use App\Services\DocumentRequestOrderStatus;
use Illuminate\Console\Command;

/**
 * One-off catch-up for student portal orders whose requests were all dealt with in Task Manager
 * before the order followed its tasks (DocumentRequestOrderStatus, 23 Nov 2026). Lists by
 * default; --apply writes.
 */
class CompleteDocumentRequestOrders extends Command
{
    protected $signature = 'document-requests:complete-orders {--apply : Mark the listed orders Completed}';

    protected $description = 'Mark student portal orders Completed when none of their document-request tasks is still open';

    public function handle(): int
    {
        $due = StudentOrder::where('status', '!=', 'Completed')->orderBy('id')->get(['id', 'invoice_number', 'status'])
            ->filter(fn ($order) => DocumentRequestOrderStatus::isDue((int) $order->id))
            ->values();

        if ($due->isEmpty()) {
            $this->info('No orders to complete.');
            return self::SUCCESS;
        }

        $this->table(['Order', 'Invoice', 'Status now'], $due->map(fn ($o) => [$o->id, $o->invoice_number, $o->status])->all());

        if (!$this->option('apply')) {
            $this->info($due->count().' order(s) would be marked Completed. Run again with --apply to write.');
            return self::SUCCESS;
        }

        $done = $due->filter(fn ($order) => DocumentRequestOrderStatus::syncOrder((int) $order->id))->count();
        $this->info($done.' order(s) marked Completed.');

        return self::SUCCESS;
    }
}
