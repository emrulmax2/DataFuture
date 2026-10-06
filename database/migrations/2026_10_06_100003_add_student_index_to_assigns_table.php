<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes `assigns` by student.
 *
 * The table was only indexed by `plan_id`, so "which class plans is this
 * student on" - asked by the student portal on most pages (current term,
 * assigned terms, the attendance excuse lists) and by the student app's
 * excusable sessions - read every assignment ever made to find one student's
 * handful of rows.
 *
 * Additive and reversible: no data changes, and existing queries can only get
 * faster.
 */
return new class extends Migration
{
    private string $table = 'assigns';
    private string $index = 'assigns_student_index';

    public function up()
    {
        if ($this->indexExists()) {
            return;
        }

        Schema::table($this->table, function ($blueprint) {
            $blueprint->index(['student_id'], $this->index);
        });
    }

    public function down()
    {
        if (!$this->indexExists()) {
            return;
        }

        Schema::table($this->table, function ($blueprint) {
            $blueprint->dropIndex($this->index);
        });
    }

    private function indexExists(): bool
    {
        return !empty(DB::select("SHOW INDEX FROM {$this->table} WHERE Key_name = ?", [$this->index]));
    }
};
