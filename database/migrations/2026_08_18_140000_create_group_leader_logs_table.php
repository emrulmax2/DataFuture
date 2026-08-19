<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail for group leader assignment.
 *
 * Deliberately carries no foreign keys: `group_leaders` rows are removed
 * outright when someone is deassigned, and a cascade from there (or from a
 * deleted user) would take the history with it — which is the one thing an
 * audit trail must not do.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('group_leader_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('academic_year_id')->unsigned()->nullable();
            $table->bigInteger('term_declaration_id')->unsigned();
            $table->bigInteger('course_id')->unsigned()->nullable();
            $table->bigInteger('group_id')->unsigned();

            $table->enum('action', ['Assigned', 'Deassigned'])->default('Assigned');

            // Kept alongside the ids so the log still reads correctly if a
            // group is renamed or a staff account is later disabled.
            $table->string('user_name')->nullable();
            $table->string('group_name')->nullable();

            $table->bigInteger('performed_by')->unsigned()->nullable();
            $table->string('performed_by_name')->nullable();

            $table->timestamps();

            $table->index(['group_id', 'term_declaration_id'], 'group_leader_logs_group_term_index');
            $table->index(['user_id'], 'group_leader_logs_user_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('group_leader_logs');
    }
};
