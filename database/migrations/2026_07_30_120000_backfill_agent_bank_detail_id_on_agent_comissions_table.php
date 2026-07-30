<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backfills `agent_comissions.agent_bank_detail_id`, added by
     * 2026_07_28_113506, with each agent's currently active bank account.
     *
     * This is a one-off for the commissions raised before the column existed —
     * new commissions are given the account when they are created.
     *
     * Two deliberate choices:
     *
     *  - Only rows that are still NULL are touched, so an account already
     *    chosen for a commission is never overwritten.
     *  - The account is picked with MAX(id) inside a derived table rather than
     *    joined straight onto `agent_bank_details`. Nothing stops an agent
     *    having two rows flagged active, and a plain UPDATE ... JOIN would
     *    then take whichever one MySQL reached first; this always takes the
     *    newest and gives the same result on every run.
     *
     * Table names carry no database prefix on purpose — the statement runs
     * against whichever database the connection points at, which differs
     * between local and the server.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('agent_comissions', 'agent_bank_detail_id')) {
            return;
        }

        $filled = DB::affectingStatement("
            UPDATE agent_comissions ac
            JOIN (
                SELECT agent_id, MAX(id) AS agent_bank_detail_id
                FROM   agent_bank_details
                WHERE  active     = 1
                  AND  deleted_at IS NULL
                GROUP  BY agent_id
            ) abd ON abd.agent_id = ac.agent_id
            SET    ac.agent_bank_detail_id = abd.agent_bank_detail_id
            WHERE  ac.agent_bank_detail_id IS NULL
        ");

        // Agents with no active account leave their commissions null, which is
        // what the column is nullable for. Reported rather than left silent, so
        // the run says how much of the table it could not fill.
        $remaining = DB::table('agent_comissions')->whereNull('agent_bank_detail_id')->count();

        echo sprintf(
            "  Linked %d commission%s to an agent bank account; %d still without one (agent has no active account).%s",
            $filled,
            $filled === 1 ? '' : 's',
            $remaining,
            PHP_EOL
        );
    }

    /**
     * Not reversible: once the column is filled there is no record of which
     * rows were null beforehand, and nulling every match would also wipe the
     * accounts set when a commission was created. Rolling back leaves the data
     * as it is.
     */
    public function down(): void
    {
        //
    }
};
