<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Group leaders are assigned from the Class Plan tree, on a group node.
 *
 * The tree treats a group as (course + term + name), and the same name can
 * exist as several `groups` rows, so a leader assignment is written once per
 * matching group id — the same way `plan_participants` is written once per
 * plan id when a Manager or Audit User is assigned.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('group_leaders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('academic_year_id')->unsigned();
            $table->bigInteger('term_declaration_id')->unsigned();
            $table->bigInteger('course_id')->unsigned();
            $table->bigInteger('group_id')->unsigned();

            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();

            // The dashboard reads "which groups does this user lead this term",
            // which is exactly this prefix.
            $table->index(['user_id', 'term_declaration_id'], 'group_leaders_user_term_index');
            $table->index(['group_id'], 'group_leaders_group_index');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('group_leaders');
    }
};
