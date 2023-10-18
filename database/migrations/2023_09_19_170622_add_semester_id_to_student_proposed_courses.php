<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_proposed_courses', function (Blueprint $table) {
            
            $table->bigInteger('semester_id')->unsigned()->nullable()->after("course_creation_id");
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('set null')->onUpdate('set null');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_proposed_courses', function (Blueprint $table) {

            $table->dropForeign(['semester_id']);
            $table->dropColumn(['semester_id']);
        
        });
    }
};
