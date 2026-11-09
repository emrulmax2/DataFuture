<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/* A rule may name a job to run once it has created its task - the class
   name of one registered in config('tasks.assignment_jobs'). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_assignment_rules', function (Blueprint $table) {
            $table->string('after_assign_job', 191)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('task_assignment_rules', function (Blueprint $table) {
            $table->dropColumn('after_assign_job');
        });
    }
};
