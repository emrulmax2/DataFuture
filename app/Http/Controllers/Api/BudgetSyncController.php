<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Exports the whole Budget Management graph for the Operations rebuild.
 *
 * One payload rather than an endpoint per table: the parts only make sense
 * together (a requisition needs its year, line, vendor and people to be
 * meaningful), and the whole set is a few hundred rows.
 *
 * Users are emitted as a lookup of id => name/email because email is how the
 * two systems agree on identity — Operations has its own user ids.
 *
 * Soft-deleted rows are included with their deleted_at so the destination can
 * mirror the deletion rather than silently resurrect cancelled paperwork.
 */
class BudgetSyncController extends Controller
{
    /** Where requisition documents live on the local disk. */
    private const DOC_ROOT = 'public/requisitions/';

    public function index(Request $request)
    {
        $years = DB::table('budget_years')->get();
        $names = DB::table('budget_names')->get();

        /* budget_sets exist only to bind a year to its per-line amounts, and
           Operations models that as a flat allocation, so the set is flattened
           away here rather than exported as a concept of its own. */
        $sets = DB::table('budget_sets')->get()->keyBy('id');

        $allocations = DB::table('budget_set_details')
            ->get()
            ->map(fn ($d) => [
                'id'             => (int) $d->id,
                'budget_year_id' => (int) ($sets[$d->budget_set_id]->budget_year_id ?? 0),
                'budget_name_id' => (int) $d->budget_name_id,
                'amount'         => (float) $d->amount,
                'deleted_at'     => $d->deleted_at,
            ])
            ->filter(fn ($a) => $a['budget_year_id'] > 0)
            ->values();

        $requisitions = DB::table('budget_requisitions')->get();

        /* The line a requisition draws on is reachable only through the set
           detail it was raised against. Resolved here so the destination does
           not need to understand budget_sets at all. */
        $detailToName = DB::table('budget_set_details')->pluck('budget_name_id', 'id');

        $roles = [
            'holders'    => DB::table('budget_name_holders')->get(),
            'requesters' => DB::table('budget_name_requesters')->get(),
            'approvers'  => DB::table('budget_name_approvers')->get(),
        ];

        return response()->json([
            'generated_at' => now()->toIso8601String(),

            'users' => $this->users($requisitions, $roles),

            'years' => $years->map(fn ($y) => [
                'id'         => (int) $y->id,
                'title'      => $y->title,
                'start_date' => $y->start_date,
                'end_date'   => $y->end_date,
                'active'     => (int) $y->active,
                'deleted_at' => $y->deleted_at,
            ])->values(),

            'lines' => $names->map(fn ($n) => [
                'id'         => (int) $n->id,
                'name'       => $n->name,
                'code'       => $n->code,
                'active'     => (int) $n->active,
                'deleted_at' => $n->deleted_at,
            ])->values(),

            'line_roles' => collect($roles)->map(
                fn (Collection $rows) => $rows->map(fn ($r) => [
                    'budget_name_id' => (int) $r->budget_name_id,
                    'user_id'        => (int) $r->user_id,
                ])->values()
            ),

            'allocations' => $allocations,

            // vendor_for = 1 is the budget payee directory; other values belong
            // to unrelated parts of the SMS.
            'vendors' => DB::table('vendors')->where('vendor_for', 1)->get()->map(fn ($v) => [
                'id'         => (int) $v->id,
                'name'       => $v->name,
                'email'      => $v->email,
                'phone'      => $v->phone,
                'address'    => $v->address,
                'active'     => (int) $v->active,
                'deleted_at' => $v->deleted_at,
            ])->values(),

            'requisitions' => $requisitions->map(fn ($r) => [
                'id'                  => (int) $r->id,
                'reference_no'        => $r->reference_no,
                'budget_year_id'      => (int) $r->budget_year_id,
                'budget_name_id'      => (int) ($detailToName[$r->budget_set_detail_id] ?? 0),
                'vendor_id'           => (int) $r->vendor_id,
                'requisitioner'       => (int) $r->requisitioner,
                'first_approver'      => (int) $r->first_approver,
                'final_approver'      => (int) $r->final_approver,
                'venue_id'            => $r->venue_id ? (int) $r->venue_id : null,
                'date'                => $r->date,
                'required_by'         => $r->required_by,
                'note'                => $r->note,
                'active'              => (int) $r->active,
                'is_force_complete'   => (int) $r->is_force_complete,
                'force_completed_by'  => $r->force_completed_by ? (int) $r->force_completed_by : null,
                'force_completed_at'  => $r->force_completed_at,
                'created_at'          => $r->created_at,
                'updated_at'          => $r->updated_at,
                'deleted_at'          => $r->deleted_at,
            ])->values(),

            'items' => DB::table('budget_requisition_items')->get()->map(fn ($i) => [
                'id'                    => (int) $i->id,
                'budget_requisition_id' => (int) $i->budget_requisition_id,
                'description'           => $i->description,
                'quantity'              => (float) $i->quantity,
                'price'                 => (float) $i->price,
                'total'                 => (float) $i->total,
                'deleted_at'            => $i->deleted_at,
            ])->values(),

            /* `has_file` is stated rather than left to be discovered: a database
               copy without the uploads behind it would otherwise make the
               importer request every file one by one just to be told 404. */
            'documents' => DB::table('budget_requisition_documents')->get()->map(function ($d) {
                $stat = $this->stat($d);

                return [
                    'id'                    => (int) $d->id,
                    'budget_requisition_id' => (int) $d->budget_requisition_id,
                    'display_file_name'     => $d->display_file_name,
                    'current_file_name'     => $d->current_file_name,
                    'doc_type'              => $d->doc_type,
                    'has_file'              => $stat['exists'],
                    'size'                  => $stat['size'],
                    'created_by'            => $d->created_by ? (int) $d->created_by : null,
                    'created_at'            => $d->created_at,
                    'deleted_at'            => $d->deleted_at,
                ];
            })->values(),

            /* `approver` is a STAGE flag here (1 = first approver, 2 = final),
               not a user id — the person who acted is `created_by`. Emitted
               under distinct names so the far side cannot confuse the two. */
            'history' => DB::table('budget_requisition_histories')->orderBy('id')->get()->map(fn ($h) => [
                'id'                    => (int) $h->id,
                'budget_requisition_id' => (int) $h->budget_requisition_id,
                'stage'                 => $h->approver ? (int) $h->approver : null,
                'status'                => (int) $h->status,
                'note'                  => $h->note,
                'actor_id'              => $h->created_by ? (int) $h->created_by : null,
                'created_at'            => $h->created_at,
            ])->values(),

            /* The payment step is never written to the history table — 
               markAsCompleted() only flips the status — so it is reconstructed
               here from who linked the transactions, or who forced it through.
               Without this a paid requisition shows no record of being paid. */
            'settlements' => $this->settlements(),

            // Settled requisitions carry their accounts transactions with them.
            'transactions' => DB::table('budget_requisition_transactions as brt')
                ->join('acc_transactions as t', 't.id', '=', 'brt.acc_transaction_id')
                ->leftJoin('acc_categories as c', 'c.id', '=', 't.acc_category_id')
                ->leftJoin('acc_banks as b', 'b.id', '=', 't.acc_bank_id')
                ->whereNotNull('brt.budget_requisition_id')
                ->get([
                    'brt.budget_requisition_id', 't.id as transaction_id', 't.transaction_code',
                    't.transaction_date_2', 't.detail', 't.transaction_amount',
                    'c.category_name', 'b.bank_name',
                ])
                ->map(fn ($t) => [
                    'budget_requisition_id' => (int) $t->budget_requisition_id,
                    'transaction_id'        => (int) $t->transaction_id,
                    'code'                  => $t->transaction_code,
                    'date'                  => $t->transaction_date_2,
                    'detail'                => $t->detail,
                    'category'              => $t->category_name,
                    'bank'                  => $t->bank_name,
                    'amount'                => (float) $t->transaction_amount,
                ])->values(),
        ]);
    }

    /**
     * Who settled each paid requisition, and when.
     *
     * Preference order: the person who forced it complete, then whoever linked
     * the first transaction to it (that action *is* the settlement), then the
     * last person to touch the row.
     */
    protected function settlements(): array
    {
        $firstLink = DB::table('budget_requisition_transactions')
            ->select('budget_requisition_id', DB::raw('MIN(id) AS first_id'))
            ->groupBy('budget_requisition_id')
            ->pluck('first_id', 'budget_requisition_id');

        $links = DB::table('budget_requisition_transactions')
            ->whereIn('id', $firstLink->values()->all())
            ->get()
            ->keyBy('budget_requisition_id');

        return DB::table('budget_requisitions')
            ->where('active', 4)
            ->get()
            ->map(function ($r) use ($links) {
                $link = $links[$r->id] ?? null;

                return [
                    'budget_requisition_id' => (int) $r->id,
                    'by'     => (int) ($r->force_completed_by ?: ($link->created_by ?? $r->updated_by ?: 0)) ?: null,
                    'at'     => $r->force_completed_at ?: ($link->created_at ?? $r->updated_at),
                    'forced' => (bool) $r->is_force_complete,
                ];
            })
            ->values()
            ->all();
    }

    /** Stream one requisition document. */
    public function download(Request $request, int $id)
    {
        $doc = DB::table('budget_requisition_documents')->where('id', $id)->first();

        if (! $doc || ! $doc->current_file_name) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $relative = $this->path($doc);

        if (! Storage::disk('local')->exists($relative)) {
            return response()->json(['message' => 'File missing on disk.'], 404);
        }

        return response()->streamDownload(
            fn () => print(Storage::disk('local')->get($relative)),
            $doc->display_file_name ?: $doc->current_file_name
        );
    }

    /* ------------------------------------------------------------------ */

    /**
     * Everyone a requisition or a budget line names, keyed by SMS user id.
     * Email is the join key on the far side, so a user without one is still
     * emitted — the importer decides what to do about it.
     */
    protected function users(Collection $requisitions, array $roles): array
    {
        $ids = collect()
            ->merge($requisitions->pluck('requisitioner'))
            ->merge($requisitions->pluck('first_approver'))
            ->merge($requisitions->pluck('final_approver'))
            ->merge($requisitions->pluck('force_completed_by'))
            ->merge(DB::table('budget_requisition_histories')->pluck('approver'))
            ->merge(DB::table('budget_requisition_documents')->pluck('created_by'));

        foreach ($roles as $rows) {
            $ids = $ids->merge($rows->pluck('user_id'));
        }

        $ids = $ids->filter()->map(fn ($id) => (int) $id)->unique()->values();

        return DB::table('users')
            ->whereIn('id', $ids->all())
            ->get(['id', 'name', 'email'])
            ->mapWithKeys(fn ($u) => [(string) $u->id => [
                'name'  => $u->name,
                'email' => $u->email,
            ]])
            ->all();
    }

    protected function path(object $doc): string
    {
        return self::DOC_ROOT . $doc->budget_requisition_id . '/' . $doc->current_file_name;
    }

    /** One disk lookup answering both "is it there?" and "how big?". */
    protected function stat(object $doc): array
    {
        $relative = $this->path($doc);

        if (! Storage::disk('local')->exists($relative)) {
            return ['exists' => false, 'size' => 0];
        }

        return ['exists' => true, 'size' => (int) Storage::disk('local')->size($relative)];
    }
}
