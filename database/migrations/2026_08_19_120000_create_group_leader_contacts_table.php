<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contact the group leader has made with a student about their attendance.
 *
 * This is the record behind "Not contacted" on the worklist: a student below
 * the threshold with no row here is one nobody has chased yet. Kept per group
 * and term so the same student in two groups is chased by each leader
 * independently.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('group_leader_contacts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('student_id')->unsigned();
            $table->bigInteger('group_id')->unsigned();
            $table->bigInteger('term_declaration_id')->unsigned();

            $table->string('method')->nullable();
            $table->string('reason')->nullable();
            $table->text('note')->nullable();
            $table->date('follow_up_date')->nullable();

            // Denormalised so the log still reads after an account is disabled,
            // the same reasoning as group_leader_logs.
            $table->bigInteger('logged_by')->unsigned()->nullable();
            $table->string('logged_by_name')->nullable();

            $table->timestamps();

            $table->index(['group_id', 'term_declaration_id'], 'glc_group_term_index');
            $table->index(['student_id'], 'glc_student_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('group_leader_contacts');
    }
};
