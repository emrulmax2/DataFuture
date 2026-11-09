<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * One row per time the task engine ran for an applicant: the facts it saw,
 * what every rule decided and why, and which applicant_tasks rows it
 * created. A dry run writes a row too (mode = dry_run) and creates nothing,
 * which is how shadow mode and the settings preview work. The answer to
 * "why did this applicant get task 6?" lives here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignment_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->string('trigger', 64);
            $table->string('mode', 16); /* live | dry_run */
            $table->json('facts');
            $table->json('evaluations');
            $table->json('created_task_ids')->nullable();
            $table->unsignedInteger('tasks_created')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['applicant_id', 'created_at']);
        });

        /* An applicant task now says how it got there. Both nullable: every
           existing row was assigned by hand, and stays that way. */
        Schema::table('applicant_tasks', function (Blueprint $table) {
            $table->string('assigned_via', 16)->nullable()->after('task_status_id');
            $table->unsignedBigInteger('rule_id')->nullable()->after('assigned_via');
        });
    }

    public function down(): void
    {
        Schema::table('applicant_tasks', function (Blueprint $table) {
            $table->dropColumn(['assigned_via', 'rule_id']);
        });
        Schema::dropIfExists('task_assignment_runs');
    }
};
