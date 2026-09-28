<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes the columns every staff dashboard filters class plans by.
 *
 * `plans_date_lists` had no index on `date`, so "classes on this day" — the
 * first thing the personal tutor, tutor and programme dashboards ask — was a
 * full scan of every class date ever scheduled. `plans` had none on the tutor
 * columns or on `parent_id`, so the Personal Tutor attendance-tracking query
 * (`whereHas('plan', tutor OR personal tutor OR has tutorial child)`) scanned
 * the whole plans table once per candidate row. Several of those running at
 * once held MySQL at full CPU for 90+ seconds each.
 *
 * Additive and reversible: no data changes, and existing queries can only get
 * faster.
 */
return new class extends Migration
{
    private array $indexes = [
        'plans_date_lists' => [
            'plans_date_lists_date_plan_index' => ['date', 'plan_id'],
        ],
        'plans' => [
            'plans_parent_class_type_index' => ['parent_id', 'class_type'],
            'plans_personal_tutor_index' => ['personal_tutor_id'],
            'plans_tutor_index' => ['tutor_id'],
        ],
    ];

    public function up()
    {
        foreach ($this->indexes as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                if ($this->indexExists($table, $name)) {
                    continue;
                }

                Schema::table($table, function ($blueprint) use ($columns, $name) {
                    $blueprint->index($columns, $name);
                });
            }
        }
    }

    public function down()
    {
        foreach ($this->indexes as $table => $indexes) {
            foreach (array_keys($indexes) as $name) {
                if (!$this->indexExists($table, $name)) {
                    continue;
                }

                Schema::table($table, function ($blueprint) use ($name) {
                    $blueprint->dropIndex($name);
                });
            }
        }
    }

    private function indexExists(string $table, string $name): bool
    {
        return !empty(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$name]));
    }
};
