<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccTransaction;
use App\Models\BudgetRequisitionTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Accounts transactions, for the Operations Budget Management module.
 *
 * Operations raises its own requisitions but the money still lands here, so
 * settling one means claiming the transactions that paid it. Claims are written
 * into budget_requisition_transactions alongside this system's own, which is
 * what keeps AccTransaction::requisition() — and therefore the "not already
 * linked" filter both systems search through — telling the truth.
 */
class BudgetTransactionController extends Controller
{
    /** Transactions available to be claimed, matched on transaction code. */
    public function search(Request $request)
    {
        $data = $request->validate([
            'q'     => ['required', 'string', 'min:2', 'max:64'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $rows = AccTransaction::query()
            ->with(['category:id,category_name', 'bank:id,bank_name'])
            ->whereDoesntHave('requisition')          // not already spent, by either system
            ->where('parent', '0')                    // top-level only, never a split child
            ->where('transaction_code', 'LIKE', '%' . $data['q'] . '%')
            ->orderBy('transaction_code')
            ->limit($data['limit'] ?? 20)
            ->get();

        return response()->json([
            'data' => $rows->map(fn (AccTransaction $t) => $this->shape($t))->values(),
        ]);
    }

    /** One transaction, re-checked as claimable at the moment of selection. */
    public function show(Request $request, $id)
    {
        $transaction = AccTransaction::with(['category:id,category_name', 'bank:id,bank_name'])
            ->findOrFail((int) $id);

        return response()->json([
            'data'      => $this->shape($transaction),
            'available' => ! $transaction->requisition()->exists(),
        ]);
    }

    /**
     * Claim transactions for an Operations requisition.
     *
     * Idempotent per transaction and safe against two people settling at once:
     * the availability check and the insert happen inside one locking
     * transaction, so a transaction claimed a moment ago is reported back as a
     * conflict rather than being linked twice.
     */
    public function link(Request $request)
    {
        $data = $request->validate([
            'reference'        => ['required', 'string', 'max:32'],
            'transaction_ids'  => ['required', 'array', 'min:1'],
            'transaction_ids.*'=> ['integer'],
        ]);

        $wanted   = collect($data['transaction_ids'])->map(fn ($id) => (int) $id)->unique();
        $linked   = [];
        $conflict = [];

        DB::transaction(function () use ($wanted, $data, &$linked, &$conflict) {
            foreach ($wanted as $id) {
                $transaction = AccTransaction::lockForUpdate()->find($id);

                if (! $transaction) {
                    $conflict[] = ['id' => $id, 'reason' => 'No such transaction.'];
                    continue;
                }

                $existing = BudgetRequisitionTransaction::where('acc_transaction_id', $id)->first();

                if ($existing) {
                    // Re-sending the same claim is not an error; the caller may
                    // simply be retrying after a dropped response.
                    if ($existing->source === 'operations' && $existing->ops_requisition_ref === $data['reference']) {
                        $linked[] = $id;
                        continue;
                    }

                    $conflict[] = [
                        'id'     => $id,
                        'reason' => 'Already linked to ' . ($existing->ops_requisition_ref ?: 'a requisition in this system') . '.',
                    ];
                    continue;
                }

                BudgetRequisitionTransaction::create([
                    'budget_requisition_id' => null,
                    'source'                => 'operations',
                    'ops_requisition_ref'   => $data['reference'],
                    'acc_transaction_id'    => $id,
                ]);

                $linked[] = $id;
            }
        });

        /* The authoritative rows come back with the response so the caller
           records what this system holds, rather than trusting whatever the
           browser posted for amount and description. */
        $rows = AccTransaction::with(['category:id,category_name', 'bank:id,bank_name'])
            ->whereIn('id', $linked)
            ->get()
            ->map(fn (AccTransaction $t) => $this->shape($t))
            ->values();

        return response()->json([
            'linked'       => $linked,
            'conflicts'    => $conflict,
            'transactions' => $rows,
        ], $conflict ? 409 : 200);
    }

    /** Release transactions — used when a settlement is reversed. */
    public function unlink(Request $request)
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:32'],
        ]);

        $removed = BudgetRequisitionTransaction::where('source', 'operations')
            ->where('ops_requisition_ref', $data['reference'])
            ->delete();

        return response()->json(['released' => $removed]);
    }

    /* ------------------------------------------------------------------ */

    protected function shape(AccTransaction $t): array
    {
        return [
            'id'       => (int) $t->id,
            'code'     => $t->transaction_code,
            'date'     => $t->transaction_date_2,
            'detail'   => $t->detail,
            'category' => $t->category->category_name ?? null,
            'bank'     => $t->bank->bank_name ?? null,
            'amount'   => (float) $t->transaction_amount,
        ];
    }
}
