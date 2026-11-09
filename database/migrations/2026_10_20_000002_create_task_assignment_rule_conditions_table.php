<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * One row per condition on a task assignment rule; all of a rule's rows
 * must hold. `field` is a key from the ApplicationFacts catalogue (never a
 * column name typed by staff), `operator` one the field's type allows, and
 * `value` JSON so a list ("is any of") and a scalar share one column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignment_rule_conditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rule_id');
            $table->string('field', 64);
            $table->string('operator', 16);
            $table->json('value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('rule_id')->references('id')->on('task_assignment_rules')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignment_rule_conditions');
    }
};
