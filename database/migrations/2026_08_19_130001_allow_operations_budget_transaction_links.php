<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Let the Operations Budget Management module claim transactions here.
 *
 * Operations requisitions do not exist in this database, so the foreign key on
 * budget_requisition_id has to go and the column has to accept null. The link
 * row is still the single record of "this transaction has been spent", which
 * is what keeps AccTransaction::requisition() — and therefore the
 * whereDoesntHave('requisition') filter on the transaction search — correct
 * for links made from either system.
 *
 * `source` tells the two apart; `ops_requisition_ref` carries the Operations
 * reference (e.g. SE929241) so a link can be traced back without a join that
 * crosses databases.
 */
return new class extends Migration
{
    public function up(): void
    {
        /* The constraint is looked up rather than named: a hardcoded name that
           does not match on the target database aborts the deploy mid-migration,
           and the name is not guaranteed to be Laravel's convention everywhere. */
        if ($name = $this->foreignKeyName('budget_requisition_id')) {
            Schema::table('budget_requisition_transactions', function (Blueprint $table) use ($name) {
                $table->dropForeign($name);
            });
        }

        DB::statement('ALTER TABLE budget_requisition_transactions MODIFY budget_requisition_id BIGINT UNSIGNED NULL');

        // Guarded so a partial run can be repeated safely.
        Schema::table('budget_requisition_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('budget_requisition_transactions', 'source')) {
                $table->string('source', 24)->default('datafuture')->after('budget_requisition_id');
                $table->index('source');
            }

            if (! Schema::hasColumn('budget_requisition_transactions', 'ops_requisition_ref')) {
                $table->string('ops_requisition_ref', 32)->nullable()->after('source');
                $table->index('ops_requisition_ref');
            }
        });

        // Everything that already exists was made here.
        DB::table('budget_requisition_transactions')->whereNull('source')->orWhere('source', '')
            ->update(['source' => 'datafuture']);
    }

    /** The actual name of the foreign key on a column, or null if there is none. */
    protected function foreignKeyName(string $column): ?string
    {
        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1',
            ['budget_requisition_transactions', $column]
        );

        return $row->CONSTRAINT_NAME ?? null;
    }

    public function down(): void
    {
        // Operations links point at requisitions this database knows nothing
        // about, so they cannot survive the foreign key being restored.
        DB::table('budget_requisition_transactions')->where('source', 'operations')->delete();

        Schema::table('budget_requisition_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('budget_requisition_transactions', 'source')) {
                $table->dropIndex(['source']);
                $table->dropColumn('source');
            }

            if (Schema::hasColumn('budget_requisition_transactions', 'ops_requisition_ref')) {
                $table->dropIndex(['ops_requisition_ref']);
                $table->dropColumn('ops_requisition_ref');
            }
        });

        DB::statement('ALTER TABLE budget_requisition_transactions MODIFY budget_requisition_id BIGINT UNSIGNED NOT NULL');

        Schema::table('budget_requisition_transactions', function (Blueprint $table) {
            $table->foreign('budget_requisition_id')->references('id')->on('budget_requisitions');
        });
    }
};
