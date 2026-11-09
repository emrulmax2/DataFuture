<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * "Class Routine (ICP)" on a task list: its Task Manager page offers "Send ICP & Mark as
 * Complete" (App\Services\ClassRoutine\ClassRoutineSender). User, 28 Oct 2026.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('task_lists', 'class_routine')) {
            Schema::table('task_lists', function (Blueprint $table) {
                $table->enum('class_routine', ['Yes', 'No'])->default('No')->after('hesa_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('task_lists', 'class_routine')) {
            Schema::table('task_lists', function (Blueprint $table) {
                $table->dropColumn('class_routine');
            });
        }
    }
};
