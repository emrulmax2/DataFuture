<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Rules for assigning admission tasks automatically.
 *
 * Until now every applicant's task list was a human decision - a checklist
 * diffed by hand some 3,400 times. A rule says "assign this task when this
 * happens", with the applicant-facing conditions in the sibling table. A rule
 * with no conditions always applies. Rules sharing an `exclusive_group`
 * assign at most one task between them, in priority order - how
 * "Course Entry Documentation (Academic)" and "(Mature)" stay exclusive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignment_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_list_id');
            /* What fires the rule. Only application_submitted today. */
            $table->string('trigger', 64)->default('application_submitted');
            $table->string('exclusive_group', 64)->nullable();
            $table->unsignedInteger('priority')->default(100);
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('task_list_id')->references('id')->on('task_lists');
            $table->index(['trigger', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignment_rules');
    }
};
