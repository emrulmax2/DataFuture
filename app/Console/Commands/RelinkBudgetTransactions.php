<?php

namespace App\Console\Commands;

use App\Services\OperationsBudgetClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Re-points transaction links at the requisitions now held in Operations.
 *
 * Budget Management moved. The transaction links written here still name this
 * system's requisitions, so the briefcase on a settled transaction opens the
 * old record — which nobody maintains any more. This hands those links to
 * Operations by reference.
 *
 * Deliberately non-destructive: `budget_requisition_id` is left exactly as it
 * was. `source` is what decides which system owns a link, so the original id
 * stays as provenance and --rollback puts everything back without needing the
 * database backup. Run after the budget data has been imported into Operations.
 */
class RelinkBudgetTransactions extends Command
{
    protected $signature = 'budget:relink-transactions
        {--dry-run : Report what would change without writing}
        {--rollback : Hand the links back to this system}';

    protected $description = 'Point settled transaction links at the requisitions now held in Operations';

    public function handle(OperationsBudgetClient $client): int
    {
        return $this->option('rollback') ? $this->rollback() : $this->convert($client);
    }

    /* ------------------------------------------------------------------ */

    protected function convert(OperationsBudgetClient $client): int
    {
        $this->info('Reading the requisition references Operations holds…');

        $references = $client->references();

        if ($references === null) {
            $this->error('Operations could not be reached — nothing has been changed.');
            $this->line('Check OPERATIONS_BASE_URL and OPERATIONS_API_KEY, then try again.');

            return self::FAILURE;
        }

        // Compared case-insensitively: references are quoted to vendors by hand
        // and a difference in case is not a different requisition.
        $known = collect($references)->mapWithKeys(fn ($r) => [mb_strtolower(trim((string) $r)) => (string) $r]);

        $this->line('  ' . $known->count() . ' references available.');

        $links = DB::table('budget_requisition_transactions as brt')
            ->leftJoin('budget_requisitions as r', 'r.id', '=', 'brt.budget_requisition_id')
            ->where('brt.source', 'datafuture')
            ->get(['brt.id', 'brt.acc_transaction_id', 'brt.budget_requisition_id', 'r.reference_no']);

        if ($links->isEmpty()) {
            $this->info('Nothing to convert — every link already belongs to Operations.');

            return self::SUCCESS;
        }

        $convert = [];
        $orphan  = [];   // link points at a requisition this system no longer has
        $missing = [];   // reference has no counterpart in Operations

        foreach ($links as $link) {
            $reference = trim((string) $link->reference_no);

            if ($reference === '') {
                $orphan[] = $link->id;
                continue;
            }

            $match = $known[mb_strtolower($reference)] ?? null;

            if (! $match) {
                $missing[$reference] = ($missing[$reference] ?? 0) + 1;
                continue;
            }

            $convert[] = ['id' => $link->id, 'reference' => $match];
        }

        $this->newLine();
        $this->table(['What', 'Links'], [
            ['ready to convert', count($convert)],
            ['no matching requisition in Operations', array_sum($missing)],
            ['link has no requisition here', count($orphan)],
        ]);

        foreach (array_slice(array_keys($missing), 0, 10) as $reference) {
            $this->warn("  {$reference} is not in Operations — left as it is.");
        }

        if (! $convert) {
            $this->warn('Nothing could be converted.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->comment('Dry run — nothing was written.');

            return self::SUCCESS;
        }

        if ($this->input->isInteractive()
            && ! $this->confirm(count($convert) . ' link(s) will be handed to Operations. Continue?', false)) {
            $this->line('Cancelled.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($convert) {
            foreach ($convert as $row) {
                DB::table('budget_requisition_transactions')
                    ->where('id', $row['id'])
                    ->update([
                        'source'              => 'operations',
                        'ops_requisition_ref' => $row['reference'],
                        // budget_requisition_id is left alone on purpose.
                        'updated_at'          => now(),
                    ]);
            }
        });

        $this->info(count($convert) . ' link(s) now point at Operations.');
        $this->comment('Reversible with: php artisan budget:relink-transactions --rollback');

        return self::SUCCESS;
    }

    /**
     * Hand the links back. Only rows that still carry their original
     * budget_requisition_id can be returned — a link created *by* Operations
     * never had one, and is left alone.
     */
    protected function rollback(): int
    {
        $query = DB::table('budget_requisition_transactions')
            ->where('source', 'operations')
            ->whereNotNull('budget_requisition_id');

        $count = (clone $query)->count();

        $native = DB::table('budget_requisition_transactions')
            ->where('source', 'operations')
            ->whereNull('budget_requisition_id')
            ->count();

        if ($count === 0) {
            $this->info('Nothing to roll back.');

            if ($native) {
                $this->line("  ({$native} link(s) were raised in Operations and have no requisition here.)");
            }

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->comment("Dry run — {$count} link(s) would be returned to this system.");

            return self::SUCCESS;
        }

        if ($this->input->isInteractive() && ! $this->confirm("Return {$count} link(s) to this system?", false)) {
            $this->line('Cancelled.');

            return self::SUCCESS;
        }

        $query->update([
            'source'              => 'datafuture',
            'ops_requisition_ref' => null,
            'updated_at'          => now(),
        ]);

        $this->info("{$count} link(s) returned.");

        if ($native) {
            $this->line("  {$native} link(s) raised in Operations were left as they are.");
        }

        return self::SUCCESS;
    }
}
