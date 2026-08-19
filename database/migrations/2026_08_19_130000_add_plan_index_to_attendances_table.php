<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes `attendances` by plan.
 *
 * The table has foreign-key indexes on student, feed status and date-list, but
 * none on `plan_id` — and "attendance for these plans" is how every staff
 * dashboard reads it (`whereIn('plan_id', ...)` in the personal tutor,
 * programme and group leader controllers alike). Against 1.5M rows each of
 * those was a full scan; the Group Leader group page spent 27 seconds in them.
 *
 * The status column rides along so the present/absent aggregates can be
 * answered from the index without touching the row, and `plan_id` on its own
 * still uses it as a prefix.
 *
 * Additive and reversible: no data changes, and existing queries can only get
 * faster. Building it on a table this size takes a few seconds.
 */
return new class extends Migration
{
    public function up()
    {
        if ($this->indexExists('attendances_plan_status_index')) {
            return;
        }

        Schema::table('attendances', function ($table) {
            $table->index(['plan_id', 'attendance_feed_status_id'], 'attendances_plan_status_index');
        });
    }

    public function down()
    {
        if (!$this->indexExists('attendances_plan_status_index')) {
            return;
        }

        Schema::table('attendances', function ($table) {
            $table->dropIndex('attendances_plan_status_index');
        });
    }

    private function indexExists(string $name): bool
    {
        return !empty(DB::select("SHOW INDEX FROM attendances WHERE Key_name = ?", [$name]));
    }
};
